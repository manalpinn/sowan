<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\LoginOtpMail;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // If the user has administrative roles, trigger OTP verification
        if ($user->hasAnyRole(['superadmin', 'admin_event', 'admin'])) {
            // Bypass OTP for demo user
            if ($user->is_demo) {
                $request->session()->put('otp_verified', true);
                return redirect()->intended(route('dashboard', absolute: false));
            }

            // Set otp_verified as false initially
            $request->session()->put('otp_verified', false);

            // Generate and send OTP
            $otp = rand(100000, 999999);
            $request->session()->put('login_otp', $otp);
            $request->session()->put('login_otp_expires_at', now()->addMinutes(5)->timestamp);

            try {
                Mail::to($user->email)->send(new LoginOtpMail($otp));
            } catch (\Exception $e) {
                // Log error but proceed so they can see the OTP verification page
                \Illuminate\Support\Facades\Log::error("Gagal mengirim email OTP: " . $e->getMessage());
            }

            return redirect()->route('login.otp');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
