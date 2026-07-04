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

    /**
     * Validate whether a phone number is registered on WhatsApp.
     *
     * @param  string  $number  Phone number to check
     * @return array{success: bool, registered: bool, message: string}
     */
    public function validateNumber(string $number): array;

    /**
     * Send bulk WhatsApp messages.
     *
     * @param  array  $data  Array of message data (target, message, delay)
     * @return array{success: bool, message: string, data?: mixed}
     */
    public function sendBulkMessage(array $data): array;

    /**
     * Send typing indicator.
     *
     * @param  string  $number Phone number
     * @param  int  $duration Duration in seconds
     * @return array{success: bool, message: string}
     */
    public function sendTyping(string $number, int $duration = 2): array;
}

