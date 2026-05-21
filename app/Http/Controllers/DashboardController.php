<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Checkin;
use App\Models\Guest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return $this->superAdminDashboard();
        }

        return $this->adminEventDashboard($user);
    }

    private function superAdminDashboard(): Response
    {
        $stats = [
            'total_events' => Event::count(),
            'total_guests' => Guest::count(),
            'total_checkins' => Checkin::whereIn('status', ['checkin', 'checkout'])->count(),
            'total_checkouts' => Checkin::where('status', 'checkout')->count(),
            'total_rsvp' => Guest::whereIn('rsvp_status', ['attending', 'declined'])->count(),
            'total_attending' => Guest::where('rsvp_status', 'attending')->sum(\DB::raw('plus_one_count + 1')),
        ];

        $events = Event::withCount(['guests', 'checkins'])
            ->orderBy('start_date', 'desc')
            ->paginate(5, ['*'], 'events_page')
            ->withQueryString()
            ->through(fn($e) => [
                'id' => $e->id,
                'name' => $e->name,
                'date' => $e->start_date->format('d M Y'),
                'location' => $e->location,
                'theme_color' => $e->theme_color,
                'total_guests' => $e->guests_count,
                'total_checkins' => $e->checkins_count,
                'status' => $e->start_date->isFuture() || $e->start_date->isToday() ? 'Aktif' : 'Selesai',
            ]);


        // 1. Jumlah Event per Bulan (Last 12 Months)
        $eventsPerMonth = Event::selectRaw("DATE_FORMAT(start_date, '%Y-%m') as month, COUNT(*) as total")
            ->where('start_date', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($m) => [
                'month' => \Carbon\Carbon::parse($m->month . '-01')->format('M Y'),
                'total' => $m->total,
            ]);

        // 2. Kehadiran Tamu (Top 8 Events) - Total vs Hadir
        $guestAttendance = Event::withCount(['guests', 'checkins'])
            ->orderBy('start_date', 'desc')
            ->take(8)
            ->get()
            ->map(fn($e) => [
                'name' => $e->name,
                'total' => $e->guests_count,
                'present' => $e->checkins_count,
            ]);

        // 3. Check-in vs Check-out Distribution (Global)
        $checkinOut = [
            'checkin' => (int)Checkin::whereIn('status', ['checkin', 'checkout'])->count(),
            'checkout' => (int)Checkin::where('status', 'checkout')->count(),
        ];

        // 4. Guest Type Distribution (Global)
        $guestTypes = Guest::selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->get();

        // 5. Recent Activity Feed (Global)
        $recentCheckins = Checkin::with(['guest', 'event'])
            ->whereNotNull('checkin_time')
            ->orderBy('checkin_time', 'desc')
            ->take(6)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'guest_name' => $c->guest?->name,
                'event_name' => $c->event?->name,
                'time' => $c->checkin_time->diffForHumans(),
                'status' => $c->status,
            ]);

        return Inertia::render('Dashboard', [
            'role' => 'superadmin',
            'stats' => $stats,
            'events' => $events,
            'charts' => [
                'eventsPerMonth' => $eventsPerMonth,
                'guestAttendance' => $guestAttendance,
                'checkinOut' => $checkinOut,
                'guestTypes' => $guestTypes,
                'recentCheckins' => $recentCheckins,
            ]
        ]);
    }

    private function adminEventDashboard($user): Response
    {
        $eventId = $user->event_id;

        $total = Guest::where('event_id', $eventId)->count();
        $checkedIn = Checkin::where('event_id', $eventId)->whereIn('status', ['checkin', 'checkout'])->count();
        $checkedOut = Checkin::where('event_id', $eventId)->where('status', 'checkout')->count();
        
        $rsvp_attending = Guest::where('event_id', $eventId)->where('rsvp_status', 'attending')->count();
        $total_pax = Guest::where('event_id', $eventId)->where('rsvp_status', 'attending')->sum(\DB::raw('plus_one_count + 1'));
        $rsvp_declined = Guest::where('event_id', $eventId)->where('rsvp_status', 'declined')->count();

        $stats = [
            'total_guests' => $total,
            'checked_in' => $checkedIn,
            'not_arrived' => $total - $checkedIn,
            'checked_out' => $checkedOut,
            'rsvp_attending' => $rsvp_attending,
            'total_pax' => (int)$total_pax,
            'rsvp_declined' => $rsvp_declined,
        ];

        $guestTypes = Guest::where('event_id', $eventId)
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->get();

        $arrivalTrend = Checkin::where('event_id', $eventId)
            ->whereNotNull('checkin_time')
            ->selectRaw("DATE_FORMAT(checkin_time, '%H:00') as hour, COUNT(*) as total")
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $event = $user->event;

        return Inertia::render('Dashboard', [
            'role' => 'admin_event',
            'event' => $event ? [
                'id' => $event->id,
                'name' => $event->name,
                'date' => $event->start_date->format('d M Y'),
                'location' => $event->location,
                'theme_color' => $event->theme_color,
                'attendance_type' => $event->attendance_type,
            ] : null,
            'stats' => $stats,
            'charts' => [
                'guestTypes' => $guestTypes,
                'arrivalTrend' => $arrivalTrend,
            ],
        ]);
    }
}
