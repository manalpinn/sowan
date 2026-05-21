<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EventAdminMiddleware
{
    /**
     * Enforce event isolation for admin_event role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Superadmin bypasses all restrictions
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // If not superadmin and not admin_event, we block access to these routes
        if (!$user->isAdminEvent()) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        // Admin event must have an assigned event
        if (!$user->event_id) {
            abort(403, 'Akun Anda belum ditetapkan ke event manapun. Silakan hubungi Super Admin.');
        }

        // 1. If route binds an {event} parameter (e.g. from resource routes)
        $event = $request->route('event');
        if ($event && $event->id !== $user->event_id) {
            abort(403, 'Anda tidak memiliki akses ke event ini.');
        }

        // 2. If route binds a {guest} parameter, check if it belongs to the admin's event
        $guest = $request->route('guest');
        if ($guest && $guest->event_id !== $user->event_id) {
            abort(403, 'Anda tidak memiliki akses ke data tamu ini.');
        }

        return $next($request);
    }
}
