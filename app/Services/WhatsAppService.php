<?php

namespace App\Services;

use App\Contracts\WhatsAppProviderInterface;
use App\Models\Guest;
use App\Jobs\SendWhatsAppInvitation;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function __construct(
        private readonly WhatsAppProviderInterface $provider
    ) {}

    /**
     * Dispatch WhatsApp invitation to a guest via queue.
     */
    public function sendInvitation(Guest $guest): void
    {
        $guest->update(['whatsapp_status' => 'pending']);
        SendWhatsAppInvitation::dispatch($guest);
    }

    /**
     * Send message directly (synchronous, for use within jobs).
     */
    public function sendDirect(Guest $guest): array
    {
        if (empty($guest->phone)) {
            Log::warning("Guest {$guest->id} has no phone number.");
            return ['success' => false, 'message' => 'Tamu tidak memiliki nomor WhatsApp.'];
        }

        // Ensure international format 628xxx
        $phone = $guest->phone;
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        // Convert 08xxx to 628xxx
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        // If it starts with 8xxx, add 62
        if (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }

        $message = $this->buildInvitationMessage($guest);
        $result = $this->provider->sendMessage($phone, $message);

        if ($result['success']) {
            $guest->update([
                'whatsapp_status' => 'delivered',
                'wa_sent_at' => now(),
            ]);
        } else {
            $guest->update([
                'whatsapp_status' => 'failed',
            ]);
        }

        return $result;
    }

    private function buildInvitationMessage(Guest $guest): string
    {
        $event = $guest->event;
        $checkinUrl = route('public.invitation', ['qr_code' => $guest->qr_code]);
        $eventDate = $event->start_date ? $event->start_date->translatedFormat('d F Y') : '-';

        return "Halo *{$guest->name}*,\n\n"
            . "Anda diundang ke *{$event->name}* 🎉\n\n"
            . "📅 *Tanggal:* {$eventDate}\n"
            . "📍 *Lokasi:* {$event->location}\n\n"
            . ($event->welcome_message ? "_{$event->welcome_message}_\n\n" : '')
            . "Silakan gunakan link berikut untuk check-in:\n"
            . "🔗 {$checkinUrl}\n\n"
            . "Sampai jumpa di acara! 😊";
    }
}
