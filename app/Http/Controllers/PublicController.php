<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\JsonResponse;

class PublicController extends Controller
{
    /**
     * Show the public invitation / RSVP page.
     */
    public function invitation(string $qrCode): Response
    {
        $guest = Guest::where('qr_code', $qrCode)->with('event')->first();

        if (!$guest) {
            return Inertia::render('Public/NotFound');
        }

        return Inertia::render('Public/Invitation', [
            'guest' => [
                'id' => $guest->id,
                'name' => $guest->name,
                'type' => $guest->type,
                'rsvp_status' => $guest->rsvp_status,
                'plus_one_count' => $guest->plus_one_count,
                'qr_code' => $guest->qr_code,
            ],
            'event' => [
                'name' => $guest->event->name,
                'description' => $guest->event->description,
                'location' => $guest->event->location,
                'start_date' => $guest->event->start_date->format('l, d F Y'),
                'welcome_message' => $guest->event->welcome_message,
                'invitation_template' => $guest->event->invitation_template,
                'banner' => $guest->event->banner,
            ]
        ]);
    }

    /**
     * Submit RSVP.
     */
    public function rsvp(Request $request, string $qrCode): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:attending,declined',
            'plus_one' => 'integer|min:0|max:10',
        ]);

        $guest = Guest::where('qr_code', $qrCode)->firstOrFail();
        
        $guest->update([
            'rsvp_status' => $request->status,
            'plus_one_count' => $request->plus_one,
            'rsvp_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih! Konfirmasi kehadiran Anda telah kami simpan.',
            'rsvp_status' => $guest->rsvp_status
        ]);
    }
}
