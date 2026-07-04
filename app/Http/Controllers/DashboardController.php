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

        return $this->adminEventDashboard($user, $request);
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
                'status' => $e->is_active ? 'Aktif' : 'Selesai',
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

    private function adminEventDashboard($user, Request $request): Response
    {
        $managedIds = $user->getManagedEventIds();
        
        if (empty($managedIds)) {
            return Inertia::render('Dashboard', [
                'role' => 'admin_event',
                'stats' => null,
                'events' => null,
                'charts' => null,
            ]);
        }
        
        $stats = [
            'total_events' => count($managedIds),
            'total_guests' => Guest::whereIn('event_id', $managedIds)->count(),
            'total_checkins' => Checkin::whereIn('event_id', $managedIds)->whereIn('status', ['checkin', 'checkout'])->count(),
            'total_checkouts' => Checkin::whereIn('event_id', $managedIds)->where('status', 'checkout')->count(),
            'total_rsvp' => Guest::whereIn('event_id', $managedIds)->whereIn('rsvp_status', ['attending', 'declined'])->count(),
            'total_attending' => Guest::whereIn('event_id', $managedIds)->where('rsvp_status', 'attending')->sum(\DB::raw('plus_one_count + 1')),
        ];

        $events = Event::whereIn('id', $managedIds)
            ->withCount(['guests', 'checkins'])
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
                'status' => $e->is_active ? 'Aktif' : 'Selesai',
            ]);

        // 1. Jumlah Event per Bulan (Last 12 Months)
        $eventsPerMonth = Event::whereIn('id', $managedIds)
            ->selectRaw("DATE_FORMAT(start_date, '%Y-%m') as month, COUNT(*) as total")
            ->where('start_date', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($m) => [
                'month' => \Carbon\Carbon::parse($m->month . '-01')->format('M Y'),
                'total' => $m->total,
            ]);

        // 2. Kehadiran Tamu (Top 8 Events) - Total vs Hadir
        $guestAttendance = Event::whereIn('id', $managedIds)
            ->withCount(['guests', 'checkins'])
            ->orderBy('start_date', 'desc')
            ->take(8)
            ->get()
            ->map(fn($e) => [
                'name' => $e->name,
                'total' => $e->guests_count,
                'present' => $e->checkins_count,
            ]);

        // 3. Check-in vs Check-out Distribution (Scoped)
        $checkinOut = [
            'checkin' => $stats['total_checkins'],
            'checkout' => $stats['total_checkouts'],
        ];

        // 4. Guest Type Distribution (Scoped)
        $guestTypes = Guest::whereIn('event_id', $managedIds)
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->get();

        // 5. Recent Activity Feed (Scoped)
        $recentCheckins = Checkin::with(['guest', 'event'])
            ->whereIn('event_id', $managedIds)
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
            'role' => 'admin_event',
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
}
