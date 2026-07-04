<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\LoginOtpMail;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class OtpController extends Controller
{
    /**
     * Show the OTP input page.
     */
    public function show(Request $request): Response|RedirectResponse
    {
        // If already verified, redirect to dashboard
        if ($request->session()->get('otp_verified', false)) {
            return redirect()->intended(route('dashboard'));
        }

        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['superadmin', 'admin_event', 'admin'])) {
            return redirect()->route('login');
        }

        // If no OTP exists in the session (e.g., user was already logged in and redirected), generate and send one now
        if (!$request->session()->has('login_otp')) {
            $otp = rand(100000, 999999);
            $request->session()->put('login_otp', $otp);
            $request->session()->put('login_otp_expires_at', now()->addMinutes(5)->timestamp);

            try {
                Mail::to($user->email)->send(new LoginOtpMail($otp));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gagal mengirim email OTP pada load pertama: " . $e->getMessage());
            }
        }

        // Partially mask email for privacy (e.g. ad***@gmail.com)
        $email = $user->email;
        $parts = explode('@', $email);
        if (count($parts) === 2) {
            $name = $parts[0];
            $domain = $parts[1];
            $maskedName = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));
            $maskedEmail = $maskedName . '@' . $domain;
        } else {
            $maskedEmail = $email;
        }

        return Inertia::render('Auth/LoginOtp', [
            'email' => $maskedEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Verify the entered OTP.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['superadmin', 'admin_event', 'admin'])) {
            return redirect()->route('login');
        }

        $sessionOtp = $request->session()->get('login_otp');
        $expiresAt = $request->session()->get('login_otp_expires_at');

        if (!$sessionOtp || !$expiresAt || now()->timestamp > $expiresAt) {
            return back()->withErrors([
                'otp' => 'Kode OTP sudah kedaluwarsa. Silakan kirim ulang OTP baru.',
            ]);
        }

        if ($request->otp !== (string)$sessionOtp) {
            return back()->withErrors([
                'otp' => 'Kode OTP yang Anda masukkan salah.',
            ]);
        }

        // Mark OTP as verified in session
        $request->session()->put('otp_verified', true);
        
        // Clean up OTP session keys
        $request->session()->forget(['login_otp', 'login_otp_expires_at']);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Resend the OTP.
     */
    public function resend(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['superadmin', 'admin_event', 'admin'])) {
            return redirect()->route('login');
        }

        $otp = rand(100000, 999999);
        
        // Store in session
        $request->session()->put('login_otp', $otp);
        $request->session()->put('login_otp_expires_at', now()->addMinutes(5)->timestamp);

        // Send email
        Mail::to($user->email)->send(new LoginOtpMail($otp));

        return back()->with('status', 'Kode OTP baru telah dikirim ke email Anda.');
    }
}
