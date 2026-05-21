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
        $eventId = $user->isSuperAdmin() ? null : $user->event_id;

        $events = $user->isSuperAdmin()
            ? \App\Models\Event::select('id', 'name')->orderBy('name')->get()
            : collect();

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
        if ($user->isAdminEvent() && $eventId !== (int) $user->event_id) {
            return response()->json([
                'success' => false, 
                'message' => "Akses ditolak. Anda mencoba memproses event #{$eventId} namun akses Anda hanya untuk event #{$user->event_id}."
            ], 403);
        }

        $result = $this->attendanceService->processToken(
            $request->qr_code,
            $eventId
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
        if ($user->isAdminEvent() && $request->event_id != $user->event_id) {
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

        if ($user->isAdminEvent() && $guest->event_id !== $user->event_id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $result = $this->attendanceService->processGuest($guest, 'manual');

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Public check-in page via QR URL.
     */
    public function checkin(Request $request): Response
    {
        $qrCode = $request->route('qr_code');
        $guest = Guest::where('qr_code', $qrCode)->with(['event', 'checkin'])->first();

        return Inertia::render('Checkin/Index', [
            'guest' => $guest ? [
                'name' => $guest->name,
                'type' => $guest->type,
                'event_name' => $guest->event->name,
                'checkin_status' => $guest->checkin_status,
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
        if ($user->isAdminEvent() && $request->event_id != $user->event_id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $guests = Guest::where('event_id', $request->event_id)
            ->get(['id', 'name', 'qr_code', 'type', 'table_number']);

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
        if ($user->isAdminEvent() && $request->event_id != $user->event_id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $results = [];
        foreach ($request->checkins as $offlineData) {
            $guest = Guest::where('qr_code', $offlineData['qr_code'])
                ->where('event_id', $request->event_id)
                ->first();
            
            if ($guest) {
                $results[] = $this->attendanceService->processGuest(
                    $guest, 
                    'offline', 
                    $offlineData['time'] ?? null
                );
            }
        }

        return response()->json(['success' => true, 'results' => $results]);
    }
}
