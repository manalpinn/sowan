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
        $users = User::with(['event', 'roles'])
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
                'event' => $u->event ? ['id' => $u->event->id, 'name' => $u->event->name] : null,
                'created_at' => $u->created_at->format('d M Y'),
            ]);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only('search')
        ]);
    }

    public function create(): Response
    {
        $events = Event::select('id', 'name')->orderBy('name')->get();
        return Inertia::render('Users/Form', ['events' => $events]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:superadmin,admin_event',
            'event_id' => ['nullable', 'exists:events,id', Rule::requiredIf($request->role === 'admin_event')],
        ], [
            'email.unique' => 'Email sudah terdaftar, silakan gunakan email lain.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'event_id' => $validated['event_id'] ?? null,
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan!');
    }

    public function edit(User $user): Response
    {
        $events = Event::select('id', 'name')->orderBy('name')->get();
        return Inertia::render('Users/Form', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()?->name,
                'event_id' => $user->event_id,
            ],
            'events' => $events,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:superadmin,admin_event',
            'event_id' => ['nullable', 'exists:events,id'],
        ], [
            'email.unique' => 'Email sudah terdaftar, silakan gunakan email lain.',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'event_id' => $validated['event_id'] ?? null,
            ...($validated['password'] ? ['password' => Hash::make($validated['password'])] : []),
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $name = $user->name;
        $user->delete();
        return redirect()->route('users.index')->with('success', "Akun pengguna {$name} telah berhasil dihapus.");
    }
}
