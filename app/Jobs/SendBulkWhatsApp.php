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

class SendBulkWhatsApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     *
     * @param array<int> $guestIds
     */
    public function __construct(public readonly array $guestIds) {}

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsAppService): void
    {
        $guests = Guest::whereIn('id', $this->guestIds)
            ->whereNull('wa_sent_at')
            ->get();

        Log::info("Starting bulk WhatsApp for " . $guests->count() . " guests.");

        foreach ($guests as $guest) {
            try {
                $whatsAppService->sendDirect($guest);
                // Sleep briefly to avoid rate limiting if needed (Fonnte handles this mostly)
                usleep(500000); // 0.5 seconds
            } catch (\Throwable $e) {
                Log::error("BulkWA error for guest #{$guest->id}: " . $e->getMessage());
            }
        }

        Log::info("Finished bulk WhatsApp process.");
    }
}
