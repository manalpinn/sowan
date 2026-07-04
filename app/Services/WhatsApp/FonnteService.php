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
            $response = Http::asForm()->withHeaders([
                'Authorization' => $this->token,
            ])->post("{$this->baseUrl}/send", [
                'target'      => $number,
                'message'     => $message,
                'delay'       => '2',
                'countryCode' => '62',
            ]);

            $body = $response->json();

            if ($response->successful() && ($body['status'] ?? false)) {
                return ['success' => true, 'message' => 'Message sent successfully.', 'data' => $body];
            }

            $errorMessage = $body['reason'] ?? $body['detail'] ?? $body['message'] ?? 'Failed to send message.';
            Log::error('Fonnte send failed', [
                'phone' => $number,
                'status_code' => $response->status(),
                'response' => $body,
                'extracted_error' => $errorMessage
            ]);
            return ['success' => false, 'message' => $errorMessage];
        } catch (\Throwable $e) {
            Log::error('Fonnte exception', [
                'phone' => $number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function sendBulkMessage(array $data): array
    {
        if (empty($this->token)) {
            Log::warning('Fonnte token not configured.');
            return ['success' => false, 'message' => 'Fonnte token is not configured.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post("{$this->baseUrl}/send", [
                'data' => json_encode($data),
            ]);

            $body = $response->json();

            if ($response->successful() && ($body['status'] ?? false)) {
                return ['success' => true, 'message' => 'Bulk messages sent successfully.', 'data' => $body];
            }

            $errorMessage = $body['reason'] ?? $body['detail'] ?? $body['message'] ?? 'Failed to send bulk messages.';
            Log::error('Fonnte bulk send failed', [
                'status_code' => $response->status(),
                'response' => $body,
                'extracted_error' => $errorMessage
            ]);
            return ['success' => false, 'message' => $errorMessage];
        } catch (\Throwable $e) {
            Log::error('Fonnte bulk exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Validate whether a phone number is registered on WhatsApp.
     *
     * @param  string  $number  Phone number to check
     * @return array{success: bool, registered: bool, message: string}
     */
    public function validateNumber(string $number): array
    {
        if (empty($this->token)) {
            return ['success' => false, 'registered' => false, 'message' => 'Fonnte token is not configured.'];
        }

        try {
            $response = Http::asForm()->withHeaders([
                'Authorization' => $this->token,
            ])->post("{$this->baseUrl}/validate", [
                'target'      => $number,
                'countryCode' => '62',
            ]);

            $body = $response->json();

            if ($response->successful() && ($body['status'] ?? false)) {
                $registeredNumbers = $body['registered'] ?? [];
                
                // Normalisasi dan bandingkan
                $isRegistered = false;
                $normalizedTarget = preg_replace('/[^0-9]/', '', $number);
                foreach ($registeredNumbers as $regNum) {
                    if (preg_replace('/[^0-9]/', '', $regNum) === $normalizedTarget) {
                        $isRegistered = true;
                        break;
                    }
                }

                return [
                    'success' => true,
                    'registered' => $isRegistered,
                    'message' => $isRegistered ? 'Nomor terdaftar di WhatsApp.' : 'Nomor TIDAK terdaftar di WhatsApp.'
                ];
            }

            $errorMessage = $body['reason'] ?? $body['detail'] ?? $body['message'] ?? 'Failed to validate number.';
            Log::error('Fonnte number validation failed', [
                'phone' => $number,
                'status_code' => $response->status(),
                'response' => $body,
                'extracted_error' => $errorMessage
            ]);
            return ['success' => false, 'registered' => false, 'message' => $errorMessage];
        } catch (\Throwable $e) {
            Log::error('Fonnte validation exception', [
                'phone' => $number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'registered' => false, 'message' => $e->getMessage()];
        }
    }

    public function sendTyping(string $number, int $duration = 2): array
    {
        if (empty($this->token)) {
            return ['success' => false, 'message' => 'Fonnte token is not configured.'];
        }

        try {
            $response = Http::asForm()->withHeaders([
                'Authorization' => $this->token,
            ])->post("{$this->baseUrl}/typing", [
                'target'      => $number,
                'countryCode' => '62',
                'duration'    => $duration,
                'stop'        => 'false',
            ]);

            $body = $response->json();

            if ($response->successful() && ($body['status'] ?? false)) {
                return ['success' => true, 'message' => 'Typing indicator sent.'];
            }

            $errorMessage = $body['reason'] ?? $body['detail'] ?? $body['message'] ?? 'Failed to send typing indicator.';
            Log::warning('Fonnte typing API failed', ['phone' => $number, 'error' => $errorMessage]);
            return ['success' => false, 'message' => $errorMessage];
        } catch (\Throwable $e) {
            Log::warning('Fonnte typing API exception', ['phone' => $number, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
