<?php

namespace App\Jobs;

use App\Models\Guest;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah maksimum percobaan pengiriman.
     */
    public int $tries = 3;

    /**
     * Waktu timeout job (detik).
     */
    public int $timeout = 30;

    /**
     * Buat instance job baru.
     */
    public function __construct(
        public readonly Guest $guest
    ) {}

    /**
     * Definisikan waktu backoff jeda antar percobaan ulang (dalam detik).
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 60, 120];
    }

    /**
     * Eksekusi job pengiriman pesan.
     */
    public function handle(WhatsAppService $whatsAppService): void
    {
        // Jeda pengaman tambahan (3 detik) antar eksekusi job
        sleep(3);

        $guest = $this->guest->fresh(['event']);

        if (!$guest) {
            Log::warning("SendWhatsAppJob: Data tamu tidak ditemukan.");
            return;
        }

        // Set status ke 'processing' saat job mulai dijalankan
        $guest->update(['whatsapp_status' => 'processing']);

        // Validasi nomor WhatsApp (harus diawali 628, 08, atau 8, panjang 10-16 digit)
        $phone = preg_replace('/[^0-9]/', '', $guest->phone ?? '');
        if (empty($phone) || strlen($phone) < 10 || strlen($phone) > 16 || (!str_starts_with($phone, '628') && !str_starts_with($phone, '08') && !str_starts_with($phone, '8'))) {
            Log::warning("SendWhatsAppJob: Nomor HP tamu #{$guest->id} ({$guest->name}) kosong atau tidak valid: '{$guest->phone}'. Pengiriman dilewati.");
            $guest->update(['whatsapp_status' => 'failed']);
            
            // Catat ke file investigasi manual
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/whatsapp_investigation.log'),
            ])->warning("INVESTIGASI FORMAT NOMOR TIDAK VALID: Tamu #{$guest->id} ({$guest->name}) - Nomor: '{$guest->phone}' tidak sesuai standar format.");
            return;
        }

        Log::info("SendWhatsAppJob: Memulai pengiriman pesan ke #{$guest->id} ({$guest->name}) - Nomor: {$phone}, Percobaan ke-{$this->attempts()}");

        $result = $whatsAppService->sendDirect($guest);

        if (!$result['success']) {
            $errorMessage = $result['message'] ?? 'Unknown Fonnte API error';

            // Jika nomor tidak valid / tidak terdaftar di WhatsApp, jangan lakukan retry
            if ($result['is_invalid_number'] ?? false) {
                Log::warning("SendWhatsAppJob: Pengiriman dibatalkan permanen karena nomor tidak valid/aktif untuk Tamu #{$guest->id} ({$guest->name}). Alasan: {$errorMessage}");
                $guest->update(['whatsapp_status' => 'failed']);
                
                // Catat ke file investigasi manual
                Log::build([
                    'driver' => 'single',
                    'path' => storage_path('logs/whatsapp_investigation.log'),
                ])->warning("INVESTIGASI NOMOR TIDAK VALID: Tamu #{$guest->id} ({$guest->name}) - Nomor: '{$guest->phone}' tidak aktif/valid di WhatsApp. Alasan: {$errorMessage}");
                return;
            }

            Log::warning("SendWhatsAppJob: Pengiriman ke #{$guest->id} gagal pada percobaan ke-{$this->attempts()}. Alasan: {$errorMessage}");

            // Pastikan status tetap 'processing' agar UI tetap sinkron selama retry (Jangan set 'sent')
            $guest->update(['whatsapp_status' => 'processing']);

            // Lempar exception agar antrean otomatis melakukan retry (sampai $tries terlampaui)
            throw new \RuntimeException("Pengiriman Fonnte gagal untuk Tamu #{$guest->id}: {$errorMessage}");
        }

        Log::info("SendWhatsAppJob: Berhasil mengirim ke #{$guest->id} ({$guest->name})");
    }

    /**
     * Tangani kegagalan permanen setelah seluruh retry habis.
     */
    public function failed(\Throwable $exception): void
    {
        $guest = $this->guest->fresh();
        if ($guest) {
            $guest->update(['whatsapp_status' => 'failed']);
            
            // Catat kegagalan permanen ke file log investigasi khusus
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/whatsapp_investigation.log'),
            ])->error("INVESTIGASI GAGAL PERMANEN: Tamu #{$guest->id} ({$guest->name}) - Nomor: '{$guest->phone}' gagal setelah {$this->tries} percobaan. Error: " . $exception->getMessage());

            Log::error("SendWhatsAppJob: Gagal permanen untuk tamu #{$guest->id} ({$guest->name}) setelah {$this->tries} percobaan. Error: " . $exception->getMessage());
        }
    }
}
