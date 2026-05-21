<?php

namespace App\Services;

use App\Models\Checkin;
use App\Models\Guest;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Process a guest scan via QR code.
     */
    public function processToken(string $qrCode, int $eventId, string $method = 'qr'): array
    {
        $guest = Guest::where('qr_code', $qrCode)
            ->where('event_id', $eventId)
            ->with('checkin')
            ->first();

        if (!$guest) {
            return [
                'success' => false,
                'status' => 'not_found',
                'message' => 'Tamu tidak ditemukan atau QR Code tidak valid.',
            ];
        }

        return $this->processGuest($guest, $method);
    }

    /**
     * Process a guest by ID (for manual search or scan result).
     */
    public function processGuest(Guest $guest, string $method = 'manual', ?string $customTime = null): array
    {
        $guest->load('event');
        $attendanceType = $guest->event->attendance_type ?? 'checkin_only';
        $timestamp = $customTime ? \Carbon\Carbon::parse($customTime) : now();

        return DB::transaction(function () use ($guest, $method, $attendanceType, $timestamp) {
            $checkin = $guest->checkin;

            // 1. Not yet checked in -> do check-in
            if (!$checkin || !$checkin->checkin_time) {
                $checkin = Checkin::updateOrCreate(
                    ['event_id' => $guest->event_id, 'guest_id' => $guest->id],
                    [
                        'checkin_time' => $timestamp, 
                        'status' => 'checkin',
                        'method' => $method
                    ]
                );

                return [
                    'success' => true,
                    'status' => 'checked_in',
                    'message' => "Check-in berhasil! Selamat datang, {$guest->name}.",
                    'guest' => $this->guestResponse($guest, $checkin),
                ];
            }

            // 2. Case: Check-in Only Mode
            if ($attendanceType === 'checkin_only') {
                return [
                    'success' => false,
                    'status' => 'already_checked_in',
                    'message' => "Tamu {$guest->name} sudah melakukan check-in sebelumnya.",
                    'guest' => $this->guestResponse($guest, $checkin),
                ];
            }

            // 3. Case: Check-in & Check-out Mode
            if ($checkin->status === 'checkin') {
                $checkin->update([
                    'checkout_time' => now(),
                    'status' => 'checkout'
                ]);

                return [
                    'success' => true,
                    'status' => 'checked_out',
                    'message' => "Check-out berhasil! Terima kasih, {$guest->name}.",
                    'guest' => $this->guestResponse($guest, $checkin->fresh()),
                ];
            }

            // 4. Already checked out
            return [
                'success' => false,
                'status' => 'already_done',
                'message' => "{$guest->name} sudah melakukan check-in dan check-out.",
                'guest' => $this->guestResponse($guest, $checkin),
            ];
        });
    }

    private function guestResponse(Guest $guest, Checkin $checkin): array
    {
        return [
            'id' => $guest->id,
            'name' => $guest->name,
            'type' => $guest->type,
            'table_number' => $guest->table_number,
            'checkin_time' => $checkin->checkin_time?->format('H:i:s'),
            'checkout_time' => $checkin->checkout_time?->format('H:i:s'),
            'status' => $checkin->status,
        ];
    }
}
