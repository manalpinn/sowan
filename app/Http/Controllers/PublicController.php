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
            'meta' => [
                'title' => 'Undangan: ' . $guest->event->name . ' - Untuk ' . $guest->name,
                'description' => $guest->event->description ?? 'Anda diundang untuk menghadiri ' . $guest->event->name . '. Silakan buka tautan ini untuk informasi lebih lanjut dan konfirmasi kehadiran.',
                'image' => $guest->event->banner ? asset('storage/' . $guest->event->banner) : asset('default-meta.jpg'),
            ],
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
                'google_maps_embed_url' => $guest->event->google_maps_embed_url,
                'start_date' => $guest->event->start_date->format('l, d F Y'),
                'time_formatted' => ($guest->event->start_time && $guest->event->end_time) 
                    ? 'Pukul ' . \Carbon\Carbon::parse($guest->event->start_time)->format('H.i') . ' - ' . \Carbon\Carbon::parse($guest->event->end_time)->format('H.i') . ' WIB' 
                    : null,
                'welcome_message' => $guest->event->welcome_message,
                'invitation_template' => $guest->event->invitation_template,
                'theme_color' => $guest->event->theme_color,
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
            'plus_one' => 'integer|min:0|max:3',
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
