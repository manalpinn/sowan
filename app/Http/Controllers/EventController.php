<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        $events = Event::withCount(['guests', 'checkins'])
            ->orderBy('start_date', 'desc')
            ->paginate(10)
            ->through(fn($e) => [
                'id' => $e->id,
                'name' => $e->name,
                'start_date' => $e->start_date->format('d M Y'),
                'end_date' => $e->end_date->format('d M Y'),
                'location' => $e->location,
                'is_active' => $e->is_active,
                'theme_color' => $e->theme_color,
                'total_guests' => $e->guests_count,
                'total_checkins' => $e->checkins_count,
            ]);

        return Inertia::render('Events/Index', ['events' => $events]);
    }

    public function create(): Response
    {
        return Inertia::render('Events/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:events,name',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'theme_color' => 'nullable|string|max:20',
            'welcome_message' => 'nullable|string',
            'attendance_type' => 'nullable|string|in:checkin_only,checkin_checkout',
            'invitation_template' => 'nullable|string|in:formal,wedding,corporate',
            'is_active' => 'boolean',
        ], [
            'name.unique' => 'Event dengan nama ini sudah terdaftar.',
        ]);

        if ($request->hasFile('banner')) {
            $validated['banner'] = $request->file('banner')->store('banners', 'public');
        }

        $event = Event::create($validated);

        return redirect()->route('events.show', $event)->with('success', 'Event berhasil dibuat!');
    }

    public function show(Event $event): Response
    {
        return Inertia::render('Events/Show', [
            'event' => array_merge($event->toArray(), [
                'start_date' => $event->start_date->format('d M Y'),
                'end_date' => $event->end_date->format('d M Y'),
                'stats' => [
                    'total_guests' => $event->total_guests,
                    'checked_in' => $event->total_checked_in,
                    'checked_out' => $event->total_checked_out,
                    'not_arrived' => $event->total_not_arrived,
                ],
            ]),
        ]);
    }

    public function edit(Event $event): Response
    {
        return Inertia::render('Events/Form', ['event' => $event]);
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('events')->ignore($event->id)],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'theme_color' => 'nullable|string|max:20',
            'welcome_message' => 'nullable|string',
            'attendance_type' => 'nullable|string|in:checkin_only,checkin_checkout',
            'invitation_template' => 'nullable|string|in:formal,wedding,corporate',
            'is_active' => 'boolean',
        ], [
            'name.unique' => 'Event dengan nama ini sudah terdaftar.',
        ]);

        if ($request->hasFile('banner')) {
            $validated['banner'] = $request->file('banner')->store('banners', 'public');
        }

        $event->update($validated);

        return redirect()->route('events.index')->with('success', 'Event berhasil diperbarui!');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index')->with('success', 'Event berhasil dihapus!');
    }
}
