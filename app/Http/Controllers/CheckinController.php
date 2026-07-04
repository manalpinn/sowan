<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\Event;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CheckinController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Checkin::query()->with(['guest', 'event']);

        $activeEvent = null;
        $managedEventIds = $user->isAdminEvent() ? $user->getManagedEventIds() : [];

        if (!$request->filled('event_id')) {
            if ($user->isSuperAdmin()) {
                return redirect()->route('events.index')->with('error', 'Silakan pilih event terlebih dahulu.');
            } else {
                if (empty($managedEventIds)) {
                    return redirect()->route('dashboard')->with('error', 'Anda belum ditugaskan ke event mana pun.');
                }
                $eventId = $managedEventIds[0];
                return redirect()->route('checkins.index', array_merge($request->all(), ['event_id' => $eventId]));
            }
        }

        $eventId = $request->event_id;

        if ($user->isAdminEvent() && !in_array($eventId, $managedEventIds)) {
            abort(403, 'Anda tidak memiliki akses ke event ini.');
        }

        $query->where('event_id', $eventId);
        $activeEvent = Event::findOrFail($eventId);

        // Filters
        if ($request->filled('search')) {
            $query->whereHas('guest', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('checkin_time', $request->date);
        }

        $perPage = $request->get('per_page', 15);
        $checkins = $query->orderBy('updated_at', 'desc')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn($c) => [
                'id' => $c->id,
                'guest_name' => $c->guest->name,
                'guest_type' => $c->guest->type,
                'guest_token' => $c->guest->qr_code,
                'event_name' => $c->event->name,
                'checkin_time' => $c->checkin_time?->format('d M Y, H:i'),
                'checkout_time' => $c->checkout_time?->format('d M Y, H:i'),
                'time_range' => $c->checkout_time 
                    ? $c->checkin_time->format('H:i') . ' – ' . $c->checkout_time->format('H:i')
                    : null,
                'status' => $c->status,
                'method' => $c->formatted_method,
            ]);

        $events = $user->isSuperAdmin()
            ? Event::select('id', 'name')->orderBy('name')->get()
            : $user->events()->select('events.id', 'events.name')->orderBy('events.name')->get();

        $hasCheckout = true;
        if ($activeEvent) {
            $hasCheckout = $activeEvent->attendance_type === 'checkin_checkout';
        }

        return Inertia::render('Checkins/Index', [
            'checkins'    => $checkins,
            'events'      => $events,
            'active_event' => $activeEvent ? ['id' => $activeEvent->id, 'name' => $activeEvent->name] : null,
            'hasCheckout' => $hasCheckout,
            'filters'     => $request->only(['search', 'event_id', 'per_page', 'date']),
            'server_time' => now()->toISOString(), // titik awal polling (timezone server)
        ]);
    }

    /**
     * Lightweight polling endpoint — returns new/updated entries since a given timestamp.
     * Called by the frontend every ~8 seconds.
     */
    public function poll(Request $request)
    {
        $user = $request->user();
        $since = $request->get('since'); // ISO timestamp string

        $query = Checkin::query()->with(['guest', 'event']);

        if ($user->isAdminEvent()) {
            if (!$request->filled('event_id') || !in_array($request->event_id, $user->getManagedEventIds())) {
                abort(403, 'Event ID tidak valid atau akses ditolak.');
            }
            $query->where('event_id', $request->event_id);
        }
        if ($user->isSuperAdmin() && $request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('checkin_time', $request->date);
        }
        if ($request->filled('search')) {
            $query->whereHas('guest', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        // Total count (for badge indicator)
        $total = (clone $query)->count();

        // Only entries updated since last poll
        if ($since) {
            try {
                $query->where('updated_at', '>', \Illuminate\Support\Carbon::parse($since));
            } catch (\Exception $e) {
                // Fallback if date parsing fails
            }
        }

        $entries = $query->orderBy('updated_at', 'desc')->limit(50)->get()
            ->map(fn($c) => [
                'id'            => $c->id,
                'guest_name'    => $c->guest->name,
                'guest_type'    => $c->guest->type,
                'guest_token'   => $c->guest->qr_code,
                'event_name'    => $c->event->name,
                'checkin_time'  => $c->checkin_time?->format('d M Y, H:i'),
                'checkout_time' => $c->checkout_time?->format('d M Y, H:i'),
                'time_range'    => $c->checkout_time
                    ? $c->checkin_time->format('H:i') . ' – ' . $c->checkout_time->format('H:i')
                    : null,
                'status'        => $c->status,
                'method'        => $c->formatted_method,
                'updated_at'    => $c->updated_at->toISOString(),
            ]);

        return response()->json([
            'entries'    => $entries,
            'total'      => $total,
            'server_time' => now()->toISOString(),
        ]);
    }

    public function exportCsv(Request $request)
    {
        $user = $request->user();
        $query = Checkin::query()->with(['guest', 'event']);

        if ($user->isAdminEvent()) {
            if (!$request->filled('event_id') || !in_array($request->event_id, $user->getManagedEventIds())) {
                abort(403, 'Event ID tidak valid atau akses ditolak.');
            }
            $query->where('event_id', $request->event_id);
        }
        if ($user->isSuperAdmin() && $request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('checkin_time', $request->date);
        }

        $checkins = $query->orderBy('checkin_time', 'asc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="log-kedatangan-' . now()->format('Ymd-His') . '.csv"',
        ];

        $callback = function () use ($checkins) {
            $file = fopen('php://output', 'w');
            // BOM for Excel UTF-8 compatibility
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, ['No', 'Nama Tamu', 'Tipe', 'Token', 'Event', 'Waktu Check-in', 'Waktu Check-out', 'Status', 'Metode']);

            foreach ($checkins as $i => $c) {
                fputcsv($file, [
                    $i + 1,
                    $c->guest->name ?? '-',
                    $c->guest->type ?? '-',
                    $c->guest->qr_code ?? '-',
                    $c->event->name ?? '-',
                    $c->checkin_time?->format('d/m/Y H:i') ?? '-',
                    $c->checkout_time?->format('d/m/Y H:i') ?? '-',
                    $c->status === 'checkout' ? 'Keluar' : 'Masuk',
                    $c->formatted_method,
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $user = $request->user();
        $query = Checkin::query()->with(['guest', 'event']);

        if ($user->isAdminEvent()) {
            if (!$request->filled('event_id') || !in_array($request->event_id, $user->getManagedEventIds())) {
                abort(403, 'Event ID tidak valid atau akses ditolak.');
            }
            $query->where('event_id', $request->event_id);
        }
        if ($user->isSuperAdmin() && $request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('checkin_time', $request->date);
        }

        $checkins = $query->orderBy('checkin_time', 'asc')->get()->map(fn($c) => [
            'guest_name' => $c->guest->name ?? '-',
            'guest_type' => $c->guest->type ?? '-',
            'guest_token' => $c->guest->qr_code ?? '-',
            'event_name' => $c->event->name ?? '-',
            'checkin_time' => $c->checkin_time?->format('d M Y, H:i') ?? '-',
            'checkout_time' => $c->checkout_time?->format('d M Y, H:i') ?? '-',
            'status' => $c->status,
            'method' => $c->formatted_method,
        ]);

        $pdf = Pdf::loadView('pdf.attendance-log', [
            'checkins' => $checkins,
            'generated_at' => now()->format('d M Y, H:i'),
            'date_filter' => $request->date,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('log-kedatangan-' . now()->format('Ymd') . '.pdf');
    }
}
