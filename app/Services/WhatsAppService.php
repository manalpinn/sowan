<?php

namespace App\Services;

use App\Contracts\WhatsAppProviderInterface;
use App\Models\Guest;
use App\Jobs\SendWhatsAppJob;
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
        $guest->update(['whatsapp_status' => 'processing']);
        SendWhatsAppJob::dispatch($guest);
    }

    /**
     * Send message directly (synchronous, for use within jobs).
     */
    public function sendDirect(Guest $guest): array
    {
        if (empty($guest->phone)) {
            Log::warning("Guest {$guest->id} has no phone number.");
            return ['success' => false, 'message' => 'Tamu tidak memiliki nomor WhatsApp.', 'is_invalid_number' => true];
        }

        $phone = $this->normalizePhone($guest->phone);

        // Validasi keaktifan nomor WhatsApp menggunakan API Fonnte
        Log::info("WhatsAppService: Memvalidasi nomor WhatsApp tamu #{$guest->id} ({$guest->name}) - Nomor: {$phone}");
        $validation = $this->provider->validateNumber($phone);

        if ($validation['success']) {
            if (!$validation['registered']) {
                Log::warning("WhatsAppService: Nomor tamu #{$guest->id} ({$guest->name}) - {$phone} tidak terdaftar di WhatsApp. Pengiriman dibatalkan.");
                $guest->update([
                    'whatsapp_status' => 'failed',
                ]);
                return ['success' => false, 'message' => 'Nomor tidak terdaftar di WhatsApp.', 'is_invalid_number' => true];
            }
            Log::info("WhatsAppService: Nomor tamu #{$guest->id} ({$guest->name}) terdaftar di WhatsApp.");
        } else {
            // Jika validasi Fonnte API gagal (misal RTO atau limit), tetap mencoba kirim sebagai fallback
            Log::warning("WhatsAppService: Gagal memvalidasi nomor #{$guest->id} via API Fonnte: {$validation['message']}. Mencoba mengirim langsung.");
        }

        $message = $this->buildInvitationMessage($guest);
        
        // Simulasikan status "typing..." selama 2 detik sebelum pesan terkirim
        $this->provider->sendTyping($phone, 2);

        // PENTING: Gunakan nomor yang sudah dinormalisasi ($phone) saat mengirim, bukan data mentah
        $result = $this->provider->sendMessage($phone, $message);

        if ($result['success']) {
            $guest->update([
                'whatsapp_status' => 'sent',
                'wa_sent_at'      => now(),
            ]);
        }
        // Note: on failure, do NOT set 'failed' here.
        // The job's failed() hook handles that after all retries are exhausted.

        return $result;
    }

    /**
     * Send bulk invitations using Fonnte's bulk data parameter.
     *
     * @param  \Illuminate\Database\Eloquent\Collection|array  $guests
     */
    public function sendBulkInvitations($guests): array
    {
        $allData = [];
        $validGuests = [];

        foreach ($guests as $guest) {
            if (empty($guest->phone)) {
                Log::warning("Guest {$guest->id} has no phone number, skipping bulk send.");
                continue;
            }

            $phone = $this->normalizePhone($guest->phone);
            
            // Format phone number required by Fonnte: without '+' or spaces, start with 628...
            // normalizePhone already does this (e.g. 628...)

            $message = $this->buildInvitationMessage($guest);
            
            $allData[] = [
                'target' => $phone,
                'message' => $message,
                // Gunakan format random delay Fonnte dengan jeda panjang (10-20 detik)
                // Sangat penting untuk menghindari blokir anti-spam WhatsApp pada nomor baru (cold numbers)
                'delay' => '10-20',
            ];

            $validGuests[] = $guest;
        }

        if (empty($allData)) {
            return ['success' => false, 'message' => 'No valid phone numbers found for bulk sending.'];
        }

        Log::info("WhatsAppService: Mengirim bulk message ke " . count($allData) . " kontak.");
        
        $result = $this->provider->sendBulkMessage($allData);

        if ($result['success']) {
            // Update status untuk semua tamu yang valid
            foreach ($validGuests as $guest) {
                $guest->update([
                    'whatsapp_status' => 'sent',
                    'wa_sent_at'      => now(),
                ]);
            }
        } else {
            // Jika request bulk gagal, set status ke failed
            foreach ($validGuests as $guest) {
                $guest->update([
                    'whatsapp_status' => 'failed',
                ]);
            }
        }

        return $result;
    }



    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }
        return $phone;
    }

    public function buildInvitationMessage(Guest $guest): string
    {
        $event = $guest->event;
        $checkinUrl = route('public.invitation', ['qr_code' => $guest->qr_code]);
        $eventDate = $event->start_date ? $event->start_date->translatedFormat('d F Y') : '-';

        return "Halo *{$guest->name}*,\n\n"
            . "Anda diundang ke *{$event->name}* 🎉\n\n"
            . "📅 *Tanggal:* {$eventDate}\n"
            . "📍 *Lokasi:* {$event->location}\n"
            . (!empty($event->google_maps_link) ? "🗺️ *Google Maps:* {$event->google_maps_link}\n" : '') . "\n"
            . ($event->welcome_message ? "_{$event->welcome_message}_\n\n" : '')
            . "Silakan gunakan link berikut untuk check-in:\n"
            . "🔗 {$checkinUrl}\n\n"
            . "Sampai jumpa di acara! 😊";
    }
}
