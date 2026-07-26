<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CheckinsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $checkins;

    public function __construct(Collection $checkins)
    {
        $this->checkins = $checkins;
    }

    public function collection()
    {
        return $this->checkins;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Tamu',
            'Tipe',
            'Token',
            'Event',
            'Waktu Check-in',
            'Waktu Check-out',
            'Status',
            'Metode',
        ];
    }

    public function map($checkin): array
    {
        static $index = 0;
        $index++;
        
        return [
            $index,
            $checkin->guest->name ?? '-',
            $checkin->guest->type ?? '-',
            $checkin->guest->qr_code ?? '-',
            $checkin->event->name ?? '-',
            $checkin->checkin_time ? $checkin->checkin_time->format('d/m/Y H:i') : '-',
            $checkin->checkout_time ? $checkin->checkout_time->format('d/m/Y H:i') : '-',
            $checkin->status === 'checkout' ? 'Keluar' : 'Masuk',
            $checkin->formatted_method,
        ];
    }
}
