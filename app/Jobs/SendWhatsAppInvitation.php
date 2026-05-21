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

class SendWhatsAppInvitation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public readonly Guest $guest) {}

    public function handle(WhatsAppService $whatsAppService): void
    {
        $guest = $this->guest->fresh(['event']);

        if (!$guest || empty($guest->phone)) {
            Log::warning("SendWhatsAppInvitation: Guest #{$this->guest->id} has no phone number.");
            return;
        }

        $result = $whatsAppService->sendDirect($guest);

        if (!$result['success']) {
            Log::error("SendWhatsAppInvitation: Failed for guest #{$guest->id}", $result);
            $this->fail($result['message']);
        }
    }
}
