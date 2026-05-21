<?php

namespace App\Services;

use App\Models\Guest;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    /**
     * Generate a unique secure token for a guest QR code.
     */
    public function generateToken(): string
    {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $qrCode = '';
            for ($i = 0; $i < 5; $i++) {
                $qrCode .= $chars[rand(0, strlen($chars) - 1)];
            }
        } while (Guest::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }

    /**
     * Generate a QR code SVG for a guest token.
     */
    public function generateQrSvg(Guest $guest): string
    {
        // QR only contains the 5-char token
        return QrCode::size(200)
            ->style('round')
            ->generate($guest->qr_code);
    }

    /**
     * Generate a QR code as base64 PNG (for PDF / download).
     */
    public function generateQrBase64(Guest $guest): string
    {
        // QR only contains the 5-char token
        $qr = QrCode::format('png')
            ->size(300)
            ->generate($guest->qr_code);

        return 'data:image/png;base64,' . base64_encode($qr);
    }

    public function getCheckinUrl(Guest $guest): string
    {
        return url("/checkin/{$guest->qr_code}");
    }

    /**
     * Generate and store a QR code if the guest doesn't have one.
     */
    public function ensureToken(Guest $guest): Guest
    {
        if (empty($guest->qr_code)) {
            $qrCode = $this->generateToken();
            $checkinUrl = url("/checkin/{$qrCode}");

            $guest->update([
                'qr_code' => $qrCode,
                'invitation_link' => $checkinUrl,
            ]);
        }

        return $guest->fresh();
    }
}
