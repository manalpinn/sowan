<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScanController extends Controller
{
    public function __construct(private readonly AttendanceService $attendanceService) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $eventId = $user->isSuperAdmin() ? null : ($user->getManagedEventIds()[0] ?? null);

        $events = $user->isSuperAdmin()
            ? \App\Models\Event::select('id', 'name', 'is_active', 'start_date', 'end_date', 'start_time', 'end_time')->orderBy('name')->get()
            : $user->events()->select('events.id', 'events.name', 'is_active', 'start_date', 'end_date', 'start_time', 'end_time')->get();

        $activeEvents = [];
        foreach ($events as $event) {
            $now = now(config('app.timezone'));
            
            $is_expired = false;
            $is_not_started = false;

            if ($event->start_date) {
                $startCarbon = \Carbon\Carbon::parse($event->start_date, config('app.timezone'))->startOfDay();
                if ($event->start_time) {
                    $startParts = explode(':', $event->start_time);
                    $startCarbon->setHour((int)$startParts[0])->setMinute((int)$startParts[1])->setSecond((int)($startParts[2] ?? 0));
                }
                $is_not_started = $now->lt($startCarbon);
            }

            $endDate = $event->end_date ?? $event->start_date;
            if ($endDate) {
                $endCarbon = \Carbon\Carbon::parse($endDate, config('app.timezone'))->endOfDay();
                if ($event->end_time) {
                    $endParts = explode(':', $event->end_time);
                    $endCarbon->startOfDay()->setHour((int)$endParts[0])->setMinute((int)$endParts[1])->setSecond((int)($endParts[2] ?? 0));
                }
                $is_expired = $now->gt($endCarbon);
            }
            
            $is_active_flag = (bool)$event->is_active;
            
            // Bypass frontend locks for superadmin so they can scan anytime? 
            // The user requested: "yang tidak aktif tidak perlu dicantumkan"
            // So we strictly hide inactive events for everyone.
            $is_inactive = $is_expired || $is_not_started || !$is_active_flag;

            if (!$is_inactive) {
                $activeEvents[] = [
                    'id' => $event->id,
                    'name' => $event->name,
                ];
            }
        }

        $events = $activeEvents;
        
        // Auto select the first active event
        if (empty($eventId) && count($events) > 0) {
            $eventId = $events[0]['id'];
        }
        
        // Ensure the selected event is actually in the list
        if ($eventId && !in_array($eventId, array_column($events, 'id'))) {
            $eventId = count($events) > 0 ? $events[0]['id'] : null;
        }

        return Inertia::render('Scanner/Index', [
            'eventId' => $eventId,
            'events' => $events,
        ]);
    }

    /**
     * Process a QR scan via qr_code.
     */
    public function scan(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'qr_code' => 'required|string|size:5|regex:/^[A-Z0-9]+$/',
            'event_id' => 'required|integer|exists:events,id',
            'method' => 'nullable|string'
        ], [
            'qr_code.required' => 'Token tidak boleh kosong.',
            'qr_code.size' => 'Token harus 5 karakter.',
            'qr_code.regex' => 'Token hanya boleh berisi huruf dan angka.',
            'event_id.required' => 'ID Event harus disertakan.',
            'event_id.exists' => 'Event yang dipilih tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Defensive check for event_id
        $eventId = (int) $request->event_id;

        // Admin event can only scan for their event
        if ($user->isAdminEvent() && !in_array($eventId, $user->getManagedEventIds())) {
            return response()->json([
                'success' => false, 
                'message' => "Akses ditolak. Anda mencoba memproses event #{$eventId} namun akses Anda tidak berlaku untuk event tersebut."
            ], 403);
        }

        $result = $this->attendanceService->processToken(
            $request->qr_code,
            $eventId,
            $request->method ?? 'qr',
            $user->isSuperAdmin()
        );

        // If success is false but reached here, return 422 with the service's message
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Search guest by name for manual attendance.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|min:2',
            'event_id' => 'required|integer|exists:events,id',
        ]);

        $user = $request->user();
        if ($user->isAdminEvent() && !in_array($request->event_id, $user->getManagedEventIds())) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $guests = Guest::where('event_id', $request->event_id)
            ->where('name', 'like', "%{$request->name}%")
            ->with('checkin')
            ->take(10)
            ->get()
            ->map(fn($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'type' => $g->type,
                'table_number' => $g->table_number,
                'checkin_status' => $g->checkin_status,
                'checkin_time' => $g->checkin?->checkin_time?->format('H:i'),
                'checkout_time' => $g->checkin?->checkout_time?->format('H:i'),
                'qr_code' => $g->qr_code, // Tambahkan qr_code agar bisa digunakan saat offline sync manual
            ]);

        return response()->json(['success' => true, 'guests' => $guests]);
    }

    /**
     * Process attendance manually by guest ID.
     */
    public function processManual(Request $request): JsonResponse
    {
        $request->validate(['guest_id' => 'required|integer|exists:guests,id']);

        $guest = Guest::with('checkin')->findOrFail($request->guest_id);
        $user = $request->user();

        if ($user->isAdminEvent() && !in_array($guest->event_id, $user->getManagedEventIds())) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        // We explicitly use 'manual' for the "Cari Nama Tamu" method
        $result = $this->attendanceService->processGuest($guest, 'manual', null, $user->isSuperAdmin());

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Public check-in page via QR URL.
     */
    public function checkin(Request $request): Response
    {
        $qrCode = $request->route('qr_code');
        $guest = Guest::where('qr_code', $qrCode)->with(['event', 'checkin'])->first();

        $isExpired = false;
        $isNotStarted = false;
        
        if ($guest && $guest->event) {
            $now = now(config('app.timezone'));
            $event = $guest->event;
            
            if ($event->start_date) {
                $startCarbon = \Carbon\Carbon::parse($event->start_date, config('app.timezone'))->startOfDay();
                if ($event->start_time) {
                    $startParts = explode(':', $event->start_time);
                    $startCarbon->setHour((int)$startParts[0])->setMinute((int)$startParts[1])->setSecond((int)($startParts[2] ?? 0));
                }
                $isNotStarted = $now->lt($startCarbon);
            }

            $endDate = $event->end_date ?? $event->start_date;
            if ($endDate) {
                $endCarbon = \Carbon\Carbon::parse($endDate, config('app.timezone'))->endOfDay();
                if ($event->end_time) {
                    $endParts = explode(':', $event->end_time);
                    $endCarbon->startOfDay()->setHour((int)$endParts[0])->setMinute((int)$endParts[1])->setSecond((int)($endParts[2] ?? 0));
                }
                $isExpired = $now->gt($endCarbon);
            }
        }

        return Inertia::render('Checkin/Index', [
            'meta' => [
                'title' => 'Tiket Masuk: ' . ($guest ? $guest->event->name : 'Acara'),
                'description' => 'Tunjukkan QR Code ini kepada resepsionis saat menghadiri ' . ($guest ? $guest->event->name : 'acara') . '.',
                'image' => ($guest && $guest->event->banner) ? asset('storage/' . $guest->event->banner) : asset('default-meta.jpg'),
            ],
            'guest' => $guest ? [
                'name' => $guest->name,
                'type' => $guest->type,
                'event_name' => $guest->event->name,
                'checkin_status' => $guest->checkin_status,
                'is_event_expired' => $isExpired,
                'is_event_not_started' => $isNotStarted,
                'is_event_inactive' => $isExpired || $isNotStarted,
            ] : null,
            'qrCode' => $qrCode,
            'guest_id' => $guest?->id,
        ]);
    }

    /**
     * API endpoint for the public self-checkin page.
     */
    public function selfCheckin(Request $request): JsonResponse
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $guest = Guest::where('qr_code', $request->qr_code)->with(['event', 'checkin'])->first();

        if (!$guest) {
            return response()->json(['success' => false, 'message' => 'QR Code tidak valid.'], 404);
        }

        $result = $this->attendanceService->processGuest($guest, 'qr');

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Public endpoint to get QR SVG.
     */
    public function publicQr(string $qrCode)
    {
        $guest = Guest::where('qr_code', $qrCode)->firstOrFail();
        
        $qrCodeService = app(\App\Services\QrCodeService::class);
        $svg = $qrCodeService->generateQrSvg($guest);

        return response($svg)->header('Content-Type', 'image/svg+xml');
    }

    /**
     * Download guest list for offline scanner.
     */
    public function downloadOfflineData(Request $request): JsonResponse
    {
        $request->validate(['event_id' => 'required|integer|exists:events,id']);
        
        $user = $request->user();
        if ($user->isAdminEvent() && !in_array($request->event_id, $user->getManagedEventIds())) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $guests = Guest::where('event_id', $request->event_id)
            ->with('checkin')
            ->get(['id', 'name', 'qr_code', 'type', 'table_number'])
            ->map(fn($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'qr_code' => $g->qr_code,
                'type' => $g->type,
                'table_number' => $g->table_number,
                'checkin_status' => $g->checkin_status,
                'checkin_time' => $g->checkin?->checkin_time?->format('H:i'),
                'checkout_time' => $g->checkin?->checkout_time?->format('H:i'),
            ]);

        return response()->json([
            'success' => true,
            'guests' => $guests,
            'attendance_type' => \App\Models\Event::find($request->event_id)->attendance_type
        ]);
    }

    /**
     * Sync offline check-ins.
     */
    public function syncOfflineData(Request $request): JsonResponse
    {
        $request->validate([
            'event_id' => 'required|integer|exists:events,id',
            'checkins' => 'required|array',
        ]);

        $user = $request->user();
        if ($user->isAdminEvent() && !in_array($request->event_id, $user->getManagedEventIds())) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $results = [];
        foreach ($request->checkins as $offlineData) {
            $guest = Guest::where('qr_code', $offlineData['qr_code'])
                ->where('event_id', $request->event_id)
                ->first();
            
            if ($guest) {
                // Determine method and append '_offline' to denote it was done offline
                $originalMethod = $offlineData['method'] ?? 'qr';
                $method = $originalMethod . '_offline';
                
                $results[] = $this->attendanceService->processGuest(
                    $guest, 
                    $method, 
                    $offlineData['time'] ?? null,
                    $user->isSuperAdmin()
                );
            }
        }

        return response()->json(['success' => true, 'results' => $results]);
    }
}
