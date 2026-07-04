<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Event::withCount(['guests', 'checkins']);

        if ($user->isAdminEvent()) {
            $query->whereIn('id', $user->getManagedEventIds());
        }

        // Search
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by Status (Simplified logic mapping to accessor)
        if ($status = $request->input('status')) {
            $now = now(config('app.timezone'));
            if ($status === 'Aktif') {
                $query->where('is_active', true)
                      ->where(function($q) use ($now) {
                          $q->whereNull('start_date')->orWhereDate('start_date', '<=', $now);
                      })
                      ->where(function($q) use ($now) {
                          $q->whereNull('end_date')->orWhereDate('end_date', '>=', $now);
                      });
            } elseif ($status === 'Akan Datang') {
                $query->where('is_active', true)
                      ->whereNotNull('start_date')
                      ->whereDate('start_date', '>', $now);
            } elseif ($status === 'Selesai') {
                $query->where(function($q) use ($now) {
                    $q->where('is_active', false)
                      ->orWhere(function($subq) use ($now) {
                          $subq->whereNotNull('end_date')->whereDate('end_date', '<', $now);
                      });
                });
            }
        }

        // Sort
        $sort = $request->input('sort', 'start_date');
        $direction = $request->input('direction', 'desc');
        // ensure valid sort columns
        if (!in_array($sort, ['name', 'start_date'])) {
            $sort = 'start_date';
        }
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }
        $query->orderBy($sort, $direction);

        $events = $query->paginate(10)
            ->withQueryString()
            ->through(fn($e) => [
                'id' => $e->id,
                'name' => $e->name,
                'date' => $e->start_date ? $e->start_date->format('d M Y') : '-',
                'time' => $e->start_time ? \Carbon\Carbon::parse($e->start_time)->format('H:i') . ' - ' . ($e->end_time ? \Carbon\Carbon::parse($e->end_time)->format('H:i') : 'Selesai') . ' WIB' : null,
                'location' => $e->location,
                'theme_color' => $e->theme_color,
                'total_guests' => $e->guests_count,
                'total_checkins' => $e->checkins_count,
                'status' => $e->event_status,
                'attendance_type' => $e->attendance_type,
            ]);

        return Inertia::render('Events/Index', [
            'events' => $events,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'sort' => $sort,
                'direction' => $direction,
            ],
            'role' => $user->hasRole('superadmin') ? 'superadmin' : 'admin_event'
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Events/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:events,name',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
            'location_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'google_maps_link' => 'nullable|string',
            'description' => 'nullable|string',
            'theme_color' => 'nullable|string|max:20',
            'welcome_message' => 'nullable|string',
            'attendance_type' => 'nullable|string|in:checkin_only,checkin_checkout',
            'invitation_template' => 'nullable|string|in:formal,wedding,corporate',
            'is_active' => 'boolean',
        ], [
            'name.unique' => 'Event dengan nama ini sudah terdaftar.',
            'start_date.after_or_equal' => 'Tanggal event tidak boleh kurang dari hari ini.',
            'end_time.after' => 'Waktu selesai harus lebih besar dari waktu mulai.',
        ]);

        if ($request->hasFile('banner')) {
            $validated['banner'] = $request->file('banner')->store('banners', 'public');
        }

        $event = Event::create($validated);

        $user = $request->user();
        if ($user->isAdminEvent()) {
            $user->events()->attach($event->id);
        }

        return redirect()->route('events.show', $event)->with('success', 'Event berhasil dibuat!');
    }

    public function show(Event $event): Response
    {
        return Inertia::render('Events/Show', [
            'event' => array_merge($event->toArray(), [
                'start_date' => $event->start_date->format('d M Y'),
                'end_date' => $event->end_date->format('d M Y'),
                'start_time' => $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('H:i') : null,
                'end_time' => $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('H:i') : null,
                'status' => $event->event_status,
                'stats' => [
                    'total_guests' => $event->total_guests,
                    'checked_in' => $event->total_checked_in,
                    'checked_out' => $event->total_checked_out,
                    'not_arrived' => $event->total_not_arrived,
                    'total_rsvp' => $event->guests()->whereIn('rsvp_status', ['attending', 'declined'])->count(),
                    'rsvp_attending' => $event->guests()->where('rsvp_status', 'attending')->count(),
                    'rsvp_pax' => $event->guests()->where('rsvp_status', 'attending')->sum(\Illuminate\Support\Facades\DB::raw('plus_one_count + 1')),
                ],
            ]),
        ]);
    }

    public function edit(Event $event)
    {
        // Event yang sudah selesai tetap bisa diakses untuk edit nama
        return Inertia::render('Events/Form', ['event' => $event]);
    }

    public function update(Request $request, Event $event)
    {
        $isFinished = $event->event_status === 'Selesai';

        $rules = [
            'name' => ['required', 'string', 'max:255', Rule::unique('events')->ignore($event->id)],
        ];

        if (!$isFinished) {
            $rules = array_merge($rules, [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'location' => 'required|string|max:255',
                'location_name' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'google_maps_link' => 'nullable|string',
                'description' => 'nullable|string',
                'theme_color' => 'nullable|string|max:20',
                'welcome_message' => 'nullable|string',
                'attendance_type' => 'nullable|string|in:checkin_only,checkin_checkout',
                'invitation_template' => 'nullable|string|in:formal,wedding,corporate',
                'is_active' => 'boolean',
            ]);
        }

        $validated = $request->validate($rules, [
            'name.unique' => 'Event dengan nama ini sudah terdaftar.',
            'end_time.after' => 'Waktu selesai harus lebih besar dari waktu mulai.',
        ]);

        if (!$isFinished && $request->hasFile('banner')) {
            $validated['banner'] = $request->file('banner')->store('banners', 'public');
        }

        $event->update($validated);

        return redirect()->route('events.show', $event)->with('success', 'Event berhasil diperbarui!');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index')->with('success', 'Event berhasil dihapus!');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:events,id'
        ]);

        $user = $request->user();
        if ($user->isAdminEvent()) {
            $managed = $user->getManagedEventIds();
            foreach ($request->ids as $id) {
                if (!in_array($id, $managed)) {
                    abort(403, 'Anda tidak memiliki akses untuk menghapus event ini.');
                }
            }
        }

        Event::whereIn('id', $request->ids)->delete();

        return redirect()->back()->with('success', count($request->ids) . ' event berhasil dihapus!');
    }

    public function search(Request $request)
    {
        $search = $request->query('q');
        $exclude = $request->query('exclude', []);
        
        $now = now(config('app.timezone'));
        $todayDate = $now->toDateString();
        $currentTime = $now->toTimeString();

        $events = Event::select('id', 'name')
            ->where('is_active', true)
            ->where(function ($q) use ($todayDate, $currentTime) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>', $todayDate)
                  ->orWhere(function ($q2) use ($todayDate, $currentTime) {
                      $q2->where('end_date', '=', $todayDate)
                         ->where(function ($q3) use ($currentTime) {
                             $q3->whereNull('end_time')
                                ->orWhere('end_time', '>', $currentTime);
                         });
                  });
            })
            ->when(!empty($exclude), function ($query) use ($exclude) {
                $query->whereNotIn('id', $exclude);
            })
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->take(20)
            ->get();
            
        return response()->json($events);
    }
}
