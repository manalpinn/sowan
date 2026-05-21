<?php

namespace App\Http\Controllers;

use App\Imports\GuestsImport;
use App\Models\Event;
use App\Models\Guest;
use App\Services\QrCodeService;
use App\Services\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class GuestController extends Controller
{
    public function __construct(
        private readonly QrCodeService $qrCodeService,
        private readonly WhatsAppService $whatsAppService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Guest::query()->with(['event', 'createdBy', 'checkin']);

        // Scope to event for admin_event
        if ($user->isAdminEvent()) {
            $query->where('event_id', $user->event_id);
        }

        // Filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('whatsapp_status', $request->status);
        }

        if ($request->filled('event_id') && $user->isSuperAdmin()) {
            $query->where('event_id', $request->event_id);
        }

        $perPage = $request->get('per_page', 10);
        $guests = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn($g) => $this->formatGuest($g));

        $events = $user->isSuperAdmin()
            ? Event::select('id', 'name')->orderBy('name')->get()
            : collect();

        return Inertia::render('Guests/Index', [
            'guests' => $guests,
            'events' => $events,
            'filters' => $request->only(['search', 'type', 'status', 'event_id', 'per_page']),
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();
        $events = $user->isSuperAdmin()
            ? Event::select('id', 'name')->orderBy('name')->get()
            : Event::where('id', $user->event_id)->get(['id', 'name']);

        return Inertia::render('Guests/Form', ['events' => $events]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'name' => [
                'required', 
                'string', 
                'max:255',
                Rule::unique('guests')->where(function ($query) use ($request, $user) {
                    return $query->where('event_id', $user->isAdminEvent() ? $user->event_id : $request->event_id);
                })
            ],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|min:10|max:16',
            'type' => 'required|in:VIP,Regular,VVIP,Vendor,Media',
            'table_number' => [
                'nullable', 
                'string', 
                'max:20',
                Rule::unique('guests')->where(function ($query) use ($request, $user) {
                    return $query->where('event_id', $user->isAdminEvent() ? $user->event_id : $request->event_id)
                                 ->where('type', $request->type);
                })
            ],
        ], [
            'name.unique' => 'Tamu dengan nama ini sudah terdaftar di event ini.',
            'phone.min' => 'Nomor HP minimal 10 digit.',
            'phone.max' => 'Nomor HP maksimal 16 digit.',
            'table_number.unique' => 'Nomor meja ini sudah terisi untuk tipe tamu yang sama.',
        ]);

        if ($user->isAdminEvent()) {
            $validated['event_id'] = $user->event_id;
        }

        $qrCode = $this->qrCodeService->generateToken();
        $validated['qr_code'] = $qrCode;
        $validated['invitation_link'] = route('public.invitation', $qrCode);
        $validated['created_by'] = $user->id;

        $guest = Guest::create($validated);

        return redirect()->route('guests.index')->with('success', 'Tamu berhasil ditambahkan!');
    }

    public function edit(Guest $guest): Response
    {
        $user = request()->user();
        $events = $user->isSuperAdmin()
            ? Event::select('id', 'name')->orderBy('name')->get()
            : Event::where('id', $user->event_id)->get(['id', 'name']);

        return Inertia::render('Guests/Form', [
            'guest' => $this->formatGuest($guest),
            'events' => $events,
        ]);
    }

    public function update(Request $request, Guest $guest)
    {
        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255',
                Rule::unique('guests')->where(function ($query) use ($guest) {
                    return $query->where('event_id', $guest->event_id);
                })->ignore($guest->id)
            ],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|min:10|max:16',
            'type' => 'required|in:VIP,Regular,VVIP,Vendor,Media',
            'table_number' => [
                'nullable', 
                'string', 
                'max:20',
                Rule::unique('guests')->where(function ($query) use ($request, $guest) {
                    return $query->where('event_id', $guest->event_id)
                                 ->where('type', $request->type);
                })->ignore($guest->id)
            ],
        ], [
            'name.unique' => 'Tamu dengan nama ini sudah terdaftar di event ini.',
            'phone.min' => 'Nomor HP minimal 10 digit.',
            'phone.max' => 'Nomor HP maksimal 16 digit.',
            'table_number.unique' => 'Nomor meja ini sudah terisi untuk tipe tamu yang sama.',
        ]);

        $guest->update($validated);

        return redirect()->route('guests.index')->with('success', 'Data tamu berhasil diperbarui!');
    }


    public function destroy(Guest $guest)
    {
        $name = $guest->name;
        $guest->delete();
        return redirect()->route('guests.index')->with('success', "Data tamu {$name} telah berhasil dihapus.");
    }

    public function sendWhatsApp(Guest $guest)
    {
        if (empty($guest->phone)) {
            return back()->with('error', 'Tamu tidak memiliki nomor WhatsApp.');
        }

        if ($guest->wa_sent_at) {
            return back()->with('error', 'Undangan sudah pernah dikirim ke tamu ini.');
        }

        $this->whatsAppService->sendInvitation($guest);

        return back()->with('success', "Undangan sedang dikirim ke {$guest->name}.");
    }

    public function bulkSendMessage(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:guests,id',
        ]);

        $guestIds = $validated['ids'];
        
        // Filter to only those not sent yet
        $toSend = Guest::whereIn('id', $guestIds)
            ->whereNull('wa_sent_at')
            ->pluck('id')
            ->toArray();

        if (empty($toSend)) {
            return back()->with('error', 'Semua tamu yang dipilih sudah pernah dikirimi undangan.');
        }

        // Mark as waiting
        Guest::whereIn('id', $toSend)->update(['whatsapp_status' => 'waiting']);

        \App\Jobs\SendBulkWhatsApp::dispatch($toSend);

        return back()->with('success', count($toSend) . " undangan sedang diproses di background.");
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:guests,id',
        ]);

        $ids = $validated['ids'];
        $count = Guest::whereIn('id', $ids)->delete();

        return back()->with('success', "{$count} data tamu berhasil dihapus.");
    }

    public function import(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'file' => 'required|file|max:5120',
            'event_id' => [Rule::requiredIf($user->isSuperAdmin()), 'nullable', 'exists:events,id'],
        ], [
            'file.required' => 'File import harus diunggah.',
            'file.max' => 'Ukuran file maksimal 5MB.',
            'event_id.required' => 'Event harus dipilih.',
        ]);

        try {
            $eventId = $user->isAdminEvent() ? $user->event_id : (int) $request->event_id;

            \Illuminate\Support\Facades\Log::info("Starting import for event ID: {$eventId}");

            $import = new GuestsImport($eventId, $this->qrCodeService);
            Excel::import($import, $request->file('file'));

            \Illuminate\Support\Facades\Log::info("Import finished. Imported: {$import->imported}, Skipped: {$import->skipped}");

            $message = "Import selesai: {$import->imported} tamu berhasil ditambahkan.";
            if ($import->skipped > 0) {
                $message .= " {$import->skipped} baris dilewati.";
            }

            if (!empty($import->importErrors)) {
                return redirect()->route('guests.index')
                    ->with('success', $message)
                    ->with('import_errors', $import->importErrors);
            }

            return redirect()->route('guests.index')->with('success', $message);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Import failed: " . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
            
            return redirect()->route('guests.index')->with('error', 'Gagal mengimport file: ' . $e->getMessage());
        }
    }

    public function generateQr(Guest $guest)
    {
        $guest = $this->qrCodeService->ensureToken($guest);
        $svg = $this->qrCodeService->generateQrSvg($guest);

        return response($svg)->header('Content-Type', 'image/svg+xml');
    }

    public function downloadPdf(Guest $guest)
    {
        $guest->load('event');
        $qrBase64 = $this->qrCodeService->generateQrBase64($guest);

        $pdf = Pdf::loadView('pdf.invitation', [
            'guest' => $guest,
            'event' => $guest->event,
            'qrCode' => $qrBase64,
        ])->setPaper('a4');

        return $pdf->download("undangan-{$guest->name}.pdf");
    }

    private function formatGuest(Guest $guest): array
    {
        return [
            'id' => $guest->id,
            'name' => $guest->name,
            'email' => $guest->email,
            'phone' => $guest->phone,
            'type' => $guest->type,
            'table_number' => $guest->table_number,
            'whatsapp_status' => $guest->whatsapp_status,
            'qr_code' => $guest->qr_code,
            'invitation_link' => $guest->qr_code ? route('public.invitation', ['qr_code' => $guest->qr_code]) : '#',
            'rsvp_status' => $guest->rsvp_status,
            'checkin_status' => $guest->checkin_status,
            'checkin_time' => $guest->checkin?->checkin_time?->format('H:i'),
            'checkout_time' => $guest->checkin?->checkout_time?->format('H:i'),
            'time_range' => $guest->checkin ? (
                $guest->checkin->checkout_time 
                ? $guest->checkin->checkin_time->format('H:i') . ' - ' . $guest->checkin->checkout_time->format('H:i')
                : $guest->checkin->checkin_time->format('H:i')
            ) : null,
            'wa_sent_at_formatted' => $guest->wa_sent_at?->translatedFormat('d M H:i'),
            'event' => $guest->event ? ['id' => $guest->event->id, 'name' => $guest->event->name] : null,
            'created_by_name' => $guest->createdBy?->name,
            'created_at' => $guest->created_at->format('d M Y'),
        ];
    }
}
