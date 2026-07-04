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
    public function processToken(string $qrCode, int $eventId, string $method = 'qr', bool $bypassValidation = false): array
    {
        $query = Guest::where('qr_code', $qrCode)->with('checkin');

        // Superadmin ($bypassValidation) bisa scan QR tamu dari event manapun tanpa dibatasi event yang dipilih
        if (!$bypassValidation) {
            $query->where('event_id', $eventId);
        }

        $guest = $query->first();

        if (!$guest) {
            return [
                'success' => false,
                'status' => 'not_found',
                'message' => 'Tamu tidak ditemukan atau QR Code tidak valid.',
            ];
        }

        return $this->processGuest($guest, $method, null, $bypassValidation);
    }

    /**
     * Process a guest by ID (for manual search or scan result).
     */
    public function processGuest(Guest $guest, string $method = 'manual', ?string $customTime = null, bool $bypassValidation = false): array
    {
        $guest->load('event');
        $attendanceType = $guest->event->attendance_type ?? 'checkin_only';
        
        $timestamp = now();
        if ($customTime) {
            // Parse UTC ISO string and convert to application's timezone
            $timestamp = \Carbon\Carbon::parse($customTime)->setTimezone(config('app.timezone'));
        }

        $today = \Carbon\Carbon::today(config('app.timezone'));
        $eventStartDate = $guest->event->start_date;
        $eventEndDate = $guest->event->end_date ?? $guest->event->start_date;
        
        $startTime = $guest->event->start_time;
        $endTime = $guest->event->end_time;
        
        if (!$guest->event->is_active && !$bypassValidation) {
            return [
                'success' => false,
                'status' => 'inactive',
                'message' => 'Event sedang tidak aktif',
            ];
        }
        
        if ($eventStartDate && $eventEndDate && !$bypassValidation) {
            $eventStartCarbon = \Carbon\Carbon::parse($eventStartDate, config('app.timezone'))->startOfDay();
            if ($startTime) {
                $startParts = explode(':', $startTime);
                $eventStartCarbon->setHour((int)$startParts[0])->setMinute((int)$startParts[1])->setSecond((int)($startParts[2] ?? 0));
            }

            $eventEndCarbon = \Carbon\Carbon::parse($eventEndDate, config('app.timezone'))->endOfDay();
            if ($endTime) {
                $endParts = explode(':', $endTime);
                $eventEndCarbon->startOfDay()->setHour((int)$endParts[0])->setMinute((int)$endParts[1])->setSecond((int)($endParts[2] ?? 0));
            }

            if ($timestamp->lt($eventStartCarbon)) {
                return [
                    'success' => false,
                    'status' => 'not_started',
                    'message' => 'Check-in belum dibuka',
                ];
            }

            if ($timestamp->gt($eventEndCarbon)) {
                return [
                    'success' => false,
                    'status' => 'event_expired',
                    'message' => 'Event telah selesai',
                ];
            }
        }

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
                    'checkout_time' => $timestamp,
                    'status' => 'checkout',
                    'checkout_method' => $method
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
