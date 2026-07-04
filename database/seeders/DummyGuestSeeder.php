<?php

namespace Database\Seeders;

use App\Models\Checkin;
use App\Models\Guest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyGuestSeeder extends Seeder
{
    /**
     * 50 tamu dummy realistis untuk testing fitur Scan QR.
     * Event 9 (mancing - checkin_checkout) & Event 3 (checkin_checkout)
     */
    public function run(): void
    {
        $createdBy = 1; // Super Admin

        // Nama-nama Indonesia realistis
        $namaList = [
            'Budi Santoso', 'Siti Rahayu', 'Andi Wijaya', 'Dewi Kusuma', 'Reza Pratama',
            'Nurul Hidayah', 'Hendra Gunawan', 'Rina Wati', 'Agus Setiawan', 'Fitri Amalia',
            'Doni Kurniawan', 'Maya Sari', 'Fajar Nugroho', 'Laila Rahma', 'Teguh Prabowo',
            'Indah Lestari', 'Yusuf Hakim', 'Sri Mulyani', 'Bayu Aditya', 'Wulan Sari',
            'Rizky Firmansyah', 'Nadia Putri', 'Dimas Santoso', 'Ayu Permata', 'Wahyu Hidayat',
            'Citra Dewi', 'Fandi Ahmad', 'Ratna Sari', 'Gilang Ramadan', 'Putri Handayani',
            'Arif Rahman', 'Lilis Suryani', 'Eko Prasetyo', 'Mira Wulandari', 'Hadi Sutrisno',
            'Tiara Anggraini', 'Rudi Hartono', 'Elsa Novita', 'Bambang Irawan', 'Vera Kusumawati',
            'Iqbal Maulana', 'Dina Fitriana', 'Surya Perdana', 'Yanti Rahayu', 'Anton Budiman',
            'Siska Oktavia', 'Lukman Hakim', 'Ani Sulistyowati', 'Taufik Hidayat', 'Rahma Yunita',
        ];

        $tipeList  = ['Regular', 'VIP', 'VVIP', 'Vendor', 'Media'];
        $metodList = ['qr', 'manual', 'qr', 'qr', 'manual']; // QR lebih banyak

        // Event target: 9 dan 3 (keduanya checkin_checkout)
        $eventConfig = [
            ['event_id' => 9, 'count' => 30],
            ['event_id' => 3, 'count' => 20],
        ];

        $namaIndex = 0;
        $now       = Carbon::now();

        foreach ($eventConfig as $cfg) {
            $eventId = $cfg['event_id'];

            for ($i = 0; $i < $cfg['count']; $i++) {
                $nama  = $namaList[$namaIndex % count($namaList)];
                $namaIndex++;

                $emailSlug = Str::slug($nama, '.');
                $qrCode    = strtoupper(Str::random(5));
                $tipe      = $tipeList[array_rand($tipeList)];
                $metode    = $metodList[array_rand($metodList)];

                // Tentukan status: ~30% belum hadir, ~40% sudah check-in, ~30% sudah checkout
                $rand = $i % 10;
                if ($rand < 3) {
                    $status = 'not_arrived';
                } elseif ($rand < 7) {
                    $status = 'checkin';
                } else {
                    $status = 'checkout';
                }

                $guest = Guest::create([
                    'event_id'       => $eventId,
                    'created_by'     => $createdBy,
                    'name'           => $nama,
                    'email'          => $emailSlug . '@example.com',
                    'phone'          => '08' . rand(100000000, 999999999),
                    'type'           => $tipe,
                    'table_number'   => rand(1, 20),
                    'whatsapp_status' => 'pending',
                    'qr_code'        => $qrCode,
                    'rsvp_status'    => 'attending',
                    'plus_one_count' => rand(0, 2),
                    'invitation_link' => url('/invitation/' . $qrCode),
                ]);

                // Buat record checkin jika bukan 'not_arrived'
                if ($status !== 'not_arrived') {
                    $checkinTime = $now->copy()
                        ->subDays(rand(0, 1))
                        ->setHour(rand(8, 11))
                        ->setMinute(rand(0, 59))
                        ->setSecond(0);

                    $checkoutTime = null;
                    $checkinStatus = 'checkin';

                    if ($status === 'checkout') {
                        $checkoutTime  = $checkinTime->copy()->addHours(rand(1, 4))->addMinutes(rand(0, 59));
                        $checkinStatus = 'checkout';
                    }

                    Checkin::create([
                        'event_id'      => $eventId,
                        'guest_id'      => $guest->id,
                        'checkin_time'  => $checkinTime,
                        'checkout_time' => $checkoutTime,
                        'status'        => $checkinStatus,
                        'method'        => $metode,
                    ]);
                }
            }
        }

        $this->command->info('✅ 50 tamu dummy berhasil dibuat!');
        $this->command->info('   - Event ID 9 (mancing): 30 tamu');
        $this->command->info('   - Event ID 3: 20 tamu');
        $this->command->info('   - Status: ~30% belum hadir, ~40% check-in, ~30% checkout');
    }
}
