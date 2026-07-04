<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireOtpVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // Check if user has administrative roles
            if ($user->hasAnyRole(['superadmin', 'admin_event', 'admin'])) {
                // Bypass OTP for demo user
                if ($user->is_demo) {
                    return $next($request);
                }

                // If they haven't verified OTP for this session, redirect to OTP verification page
                if (!$request->session()->get('otp_verified', false)) {
                    return redirect()->route('login.otp');
                }
            }
        }

        return $next($request);
    }
}
