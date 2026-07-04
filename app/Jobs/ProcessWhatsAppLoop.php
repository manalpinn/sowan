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

class ProcessWhatsAppLoop implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Waktu timeout job diset tinggi (1 jam) agar aman untuk loop yang sangat panjang.
     */
    public int $timeout = 3600;
    public int $tries = 1;

    /**
     * Buat instance job baru.
     *
     * @param array<int> $guestIds
     */
    public function __construct(
        public readonly array $guestIds
    ) {}

    /**
     * Eksekusi job pengiriman pesan.
     */
    public function handle(WhatsAppService $service): void
    {
        $guests = Guest::whereIn('id', $this->guestIds)->get();
        $totalGuests = $guests->count();

        Log::info("ProcessWhatsAppLoop: Memulai pengiriman untuk {$totalGuests} tamu dengan pola anti-spam (Human-like behavior).");

        $batchCount = 0;

        foreach ($guests as $index => $user) {
            $nomor = $user->phone;
            $pesan = $service->buildInvitationMessage($user); 

            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => env('FONNTE_TOKEN')
                ])->post('https://api.fonnte.com/send', [
                    'target' => $nomor,
                    'message' => $pesan,
                    'delay' => '2', // Delay bawaan Fonnte, biarkan agar diproses sistem fonnte dengan aman
                ]);

                if ($response->successful()) {
                    $user->update([
                        'whatsapp_status' => 'sent',
                        'wa_sent_at' => now(),
                    ]);
                    Log::info("ProcessWhatsAppLoop: Pesan terkirim untuk {$nomor}");
                } else {
                    $user->update(['whatsapp_status' => 'failed']);
                    Log::error("ProcessWhatsAppLoop: Gagal dari API Fonnte untuk {$nomor}. Status: " . $response->status());
                }
            } catch (\Exception $e) {
                $user->update(['whatsapp_status' => 'failed']);
                Log::error("ProcessWhatsAppLoop: Gagal mengirim pesan ke {$nomor}. Error: " . $e->getMessage());
            }
            
            $batchCount++;

            // Jika ini bukan tamu terakhir, kita hitung delay sebelum pesan berikutnya
            if ($index < $totalGuests - 1) {
                if ($batchCount >= 3) {
                    // 1 Batch = 3 pesan. Waktunya cooldown antar batch.
                    $batchCount = 0; // Reset counter batch

                    // Pilih rentang cooldown secara acak (dalam menit)
                    $cooldownRanges = [
                        [1, 3], // 1-3 menit
                        [2, 4], // 2-4 menit
                        [1, 5]  // 1-5 menit
                    ];
                    $selectedRange = $cooldownRanges[array_rand($cooldownRanges)];
                    
                    // Konversi menit ke detik (base cooldown)
                    $baseCooldownSeconds = rand($selectedRange[0] * 60, $selectedRange[1] * 60);
                    
                    // Randomisasi Lanjutan: Tambah 5 sampai 30 detik secara acak
                    // Ini menghindari cooldown yang selalu bulat di angka menit tertentu
                    $extraRandomSeconds = rand(5, 30);
                    
                    $totalCooldown = $baseCooldownSeconds + $extraRandomSeconds;

                    Log::info("ProcessWhatsAppLoop: [BATCH SELESAI] Memasuki masa cooldown selama {$totalCooldown} detik (~" . round($totalCooldown/60, 1) . " menit).");
                    sleep($totalCooldown);

                } else {
                    // Delay antar pesan (Per Message Delay) dalam satu batch
                    // Rentang 10-25 detik untuk menghindari deteksi spam dengan distribusi acak
                    // Menggunakan mt_rand untuk generator angka acak yang lebih baik/cepat
                    $perMessageDelay = mt_rand(10, 25);
                    
                    Log::info("ProcessWhatsAppLoop: Menunggu {$perMessageDelay} detik sebelum pesan berikutnya.");
                    sleep($perMessageDelay);
                }
            }
        }

        Log::info("ProcessWhatsAppLoop: Selesai memproses semua {$totalGuests} tamu dalam loop.");
    }
}
