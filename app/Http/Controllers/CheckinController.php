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
    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();
        $query = Checkin::query()->with(['guest', 'event']);

        // Scope to event for admin_event
        if ($user->isAdminEvent()) {
            $query->where('event_id', $user->event_id);
        }

        // Filters
        if ($request->filled('search')) {
            $query->whereHas('guest', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('event_id') && $user->isSuperAdmin()) {
            $query->where('event_id', $request->event_id);
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
                'method' => $c->method,
            ]);

        $events = $user->isSuperAdmin()
            ? Event::select('id', 'name')->orderBy('name')->get()
            : collect();

        $hasCheckout = true;
        if ($user->isAdminEvent()) {
            $hasCheckout = $user->event->attendance_type === 'checkin_checkout';
        }

        return Inertia::render('Checkins/Index', [
            'checkins'    => $checkins,
            'events'      => $events,
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
            $query->where('event_id', $user->event_id);
        }
        if ($request->filled('event_id') && $user->isSuperAdmin()) {
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
                'method'        => $c->method,
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
            $query->where('event_id', $user->event_id);
        }
        if ($request->filled('event_id') && $user->isSuperAdmin()) {
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
                    strtoupper($c->method ?? '-'),
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
            $query->where('event_id', $user->event_id);
        }
        if ($request->filled('event_id') && $user->isSuperAdmin()) {
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
            'method' => strtoupper($c->method ?? '-'),
        ]);

        $pdf = Pdf::loadView('pdf.attendance-log', [
            'checkins' => $checkins,
            'generated_at' => now()->format('d M Y, H:i'),
            'date_filter' => $request->date,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('log-kedatangan-' . now()->format('Ymd') . '.pdf');
    }
}
