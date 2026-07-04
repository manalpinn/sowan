<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::with(['events', 'roles'])
            ->whereHas('roles', function($q) {
                $q->where('name', '!=', 'superadmin');
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->roles->first()?->name ?? 'none',
                'events' => $u->events->map(fn($e) => [
                    'id' => $e->id, 
                    'name' => $e->name, 
                    'status' => $e->is_active ? 'Aktif' : 'Selesai',
                    'date' => $e->start_date ? $e->start_date->format('d M Y') : null
                ]),
                'created_at' => $u->created_at->format('d M Y'),
            ]);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only('search')
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'event_ids' => ['required', 'array', 'min:1'],
            'event_ids.*' => 'exists:events,id',
        ], [
            'email.unique' => 'Email sudah terdaftar, silakan gunakan email lain.',
            'event_ids.required' => 'Minimal pilih 1 event.',
            'event_ids.min' => 'Minimal pilih 1 event.',
        ]);

        $completedEvents = Event::whereIn('id', $validated['event_ids'])->get()->filter(function ($e) {
            return $e->event_status === 'Selesai';
        });

        if ($completedEvents->isNotEmpty()) {
            return back()->withErrors(['event_ids' => 'Tidak dapat menambahkan event yang sudah selesai.'])->withInput();
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('admin_event');

        if (!empty($validated['event_ids'])) {
            $user->events()->sync($validated['event_ids']);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan!');
    }

    public function edit(User $user): Response
    {
        if ($user->hasRole('superadmin')) {
            abort(403, 'Tidak dapat mengedit superadmin.');
        }

        return Inertia::render('Users/Form', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'event_ids' => $user->events->pluck('id')->toArray(),
                'initial_events' => $user->events->map(fn($e) => ['id' => $e->id, 'name' => $e->name])->toArray(),
            ]
        ]);
    }

    public function update(Request $request, User $user)
    {
        if ($user->hasRole('superadmin')) {
            abort(403, 'Tidak dapat mengubah data superadmin.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'event_ids' => ['required', 'array', 'min:1'],
            'event_ids.*' => 'exists:events,id',
        ], [
            'email.unique' => 'Email sudah terdaftar, silakan gunakan email lain.',
            'event_ids.required' => 'Minimal pilih 1 event.',
            'event_ids.min' => 'Minimal pilih 1 event.',
        ]);

        $existingEventIds = $user->events->pluck('id')->toArray();
        $newEventIds = array_diff($validated['event_ids'], $existingEventIds);
        
        if (!empty($newEventIds)) {
            $completedEvents = Event::whereIn('id', $newEventIds)->get()->filter(function ($e) {
                return $e->event_status === 'Selesai';
            });
            
            if ($completedEvents->isNotEmpty()) {
                return back()->withErrors(['event_ids' => 'Tidak dapat menambahkan event baru yang sudah selesai.'])->withInput();
            }
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            ...($validated['password'] ? ['password' => Hash::make($validated['password'])] : []),
        ]);

        if (!empty($validated['event_ids'])) {
            $user->events()->sync($validated['event_ids']);
        } else {
            $user->events()->detach();
        }

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        if ($user->hasRole('superadmin')) {
            abort(403, 'Tidak dapat menghapus superadmin.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $name = $user->name;
        $user->delete();
        return redirect()->route('users.index')->with('success', "Akun pengguna {$name} telah berhasil dihapus.");
    }

    public function syncEvents(Request $request, User $user)
    {
        if ($user->hasRole('superadmin')) {
            abort(403, 'Tidak dapat mengubah data superadmin.');
        }

        $validated = $request->validate([
            'event_ids' => ['required', 'array', 'min:1'],
            'event_ids.*' => 'exists:events,id',
        ], [
            'event_ids.required' => 'Minimal pilih 1 event.',
            'event_ids.min' => 'Minimal pilih 1 event.',
        ]);

        $existingEventIds = $user->events->pluck('id')->toArray();
        $newEventIds = array_diff($validated['event_ids'], $existingEventIds);
        
        if (!empty($newEventIds)) {
            $completedEvents = Event::whereIn('id', $newEventIds)->get()->filter(function ($e) {
                return $e->event_status === 'Selesai';
            });
            
            if ($completedEvents->isNotEmpty()) {
                return back()->withErrors(['event_ids' => 'Tidak dapat menambahkan event baru yang sudah selesai.']);
            }
        }

        $user->events()->sync($validated['event_ids']);

        return back()->with('success', 'Penugasan event berhasil diperbarui!');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        $idsToDelete = collect($validated['ids'])->reject(function ($id) {
            $user = User::find($id);
            return $id === auth()->id() || ($user && $user->hasRole('superadmin'));
        });

        if ($idsToDelete->isEmpty()) {
            return back()->with('error', 'Tidak ada pengguna valid yang dapat dihapus.');
        }

        User::whereIn('id', $idsToDelete)->get()->each(function ($user) {
            $user->delete();
        });

        return back()->with('success', count($idsToDelete) . ' pengguna berhasil dihapus.');
    }
}
