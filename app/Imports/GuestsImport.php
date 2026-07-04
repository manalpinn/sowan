<?php

namespace App\Imports;

use App\Models\Event;
use App\Models\Guest;
use App\Services\QrCodeService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GuestsImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public int $imported = 0;
    public int $skipped = 0;
    public array $importErrors = [];

    public function __construct(
        private readonly int $eventId,
        private readonly QrCodeService $qrCodeService
    ) {}

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new \Exception("File excel kosong.");
        }

        $firstRowKeys = $rows->first()->mapWithKeys(fn($value, $key) => [
            strtolower(trim(str_replace([' ', '-'], '_', $key))) => $value
        ])->keys()->toArray();

        $requiredColumns = ['nama', 'whatsapp', 'email', 'tipe', 'meja'];
        $missingColumns = array_diff($requiredColumns, $firstRowKeys);

        if (!empty($missingColumns)) {
            throw new \Exception("Format kolom tidak sesuai ketentuan. Kolom yang hilang: " . implode(', ', $missingColumns) . ". Pastikan header tabel di baris pertama persis: nama, whatsapp, email, tipe, meja.");
        }

        foreach ($rows as $rowIndex => $row) {
            $row = $row->mapWithKeys(fn($value, $key) => [
                strtolower(trim(str_replace([' ', '-'], '_', $key))) => $value
            ]);

            $name = trim($row->get('nama') ?? '');
            $phone = trim($row->get('whatsapp') ?? '');
            $email = trim($row->get('email') ?? '');
            $type  = trim($row->get('tipe') ?? 'Regular');
            $tableNumber = trim($row->get('meja') ?? '');

            if (empty($name)) {
                $this->skipped++;
                $this->importErrors[] = "Baris " . ($rowIndex + 2) . ": Nama tamu kosong, dilewati.";
                continue;
            }

            // Normalize phone
            if (!empty($phone)) {
                $phone = preg_replace('/[^0-9]/', '', $phone);
                if (str_starts_with($phone, '0')) $phone = '62' . substr($phone, 1);
                elseif (str_starts_with($phone, '8')) $phone = '62' . $phone;
            }

            // Validate type case-insensitively
            $typeInput = strtoupper(trim($type));
            $typeMap = [
                'VIP'     => 'VIP',
                'VVIP'    => 'VVIP',
                'REGULAR' => 'Regular',
                'VENDOR'  => 'Vendor',
                'MEDIA'   => 'Media',
            ];
            $type = $typeMap[$typeInput] ?? 'Regular';

            // Check for duplicates
            $exists = Guest::where('event_id', $this->eventId)
                ->where('name', $name)
                ->exists();

            if ($exists) {
                $this->skipped++;
                $this->importErrors[] = "Baris " . ($rowIndex + 2) . ": Tamu '{$name}' sudah terdaftar di event ini.";
                continue;
            }

            // Check for table duplicate for same type
            if (!empty($tableNumber)) {
                $tableExists = Guest::where('event_id', $this->eventId)
                    ->where('type', $type)
                    ->where('table_number', $tableNumber)
                    ->exists();
                
                if ($tableExists) {
                    $this->skipped++;
                    $this->importErrors[] = "Baris " . ($rowIndex + 2) . ": Nomor meja '{$tableNumber}' sudah terisi untuk tipe '{$type}'.";
                    continue;
                }
            }

            try {
                $qrCode = $this->qrCodeService->generateToken();
                Guest::create([
                    'event_id'    => $this->eventId,
                    'created_by'  => Auth::id(),
                    'name'        => $name,
                    'email'       => $email ?: null,
                    'phone'       => $phone ?: null,
                    'type'        => $type,
                    'table_number' => $tableNumber ?: null,
                    'qr_code'     => $qrCode,
                    'invitation_link' => route('public.invitation', $qrCode),
                ]);
                $this->imported++;
            } catch (\Throwable $e) {
                $this->skipped++;
                $this->importErrors[] = "Baris " . ($rowIndex + 2) . ": " . $e->getMessage();
            }
        }
    }
}
