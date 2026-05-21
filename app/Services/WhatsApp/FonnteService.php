<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsAppProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService implements WhatsAppProviderInterface
{
    private string $token;
    private string $baseUrl = 'https://api.fonnte.com';

    public function __construct()
    {
        $this->token = config('services.fonnte.token') ?? '';
    }

    public function sendMessage(string $number, string $message): array
    {
        if (empty($this->token)) {
            Log::warning('Fonnte token not configured.');
            return ['success' => false, 'message' => 'Fonnte token is not configured.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post("{$this->baseUrl}/send", [
                'target' => $number,
                'message' => $message,
                'countryCode' => '62',
            ]);

            $body = $response->json();

            if ($response->successful() && ($body['status'] ?? false)) {
                return ['success' => true, 'message' => 'Message sent successfully.', 'data' => $body];
            }

            Log::error('Fonnte send failed', ['body' => $body]);
            return ['success' => false, 'message' => $body['reason'] ?? 'Failed to send message.'];
        } catch (\Throwable $e) {
            Log::error('Fonnte exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
