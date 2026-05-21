<?php

namespace App\Contracts;

interface WhatsAppProviderInterface
{
    /**
     * Send a WhatsApp message to the given phone number.
     *
     * @param  string  $number  Phone number in international format (e.g., 628123456789)
     * @param  string  $message  Message text
     * @return array{success: bool, message: string, data?: mixed}
     */
    public function sendMessage(string $number, string $message): array;
}
