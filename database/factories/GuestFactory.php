<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuestFactory extends Factory
{
    public function definition(): array
    {
        $qrService = app(QrCodeService::class);
        $token = $qrService->generateToken();

        return [
            'event_id' => Event::factory(),
            'created_by' => User::factory(),
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '62' . $this->faker->numerify('8##########'),
            'type' => $this->faker->randomElement(['VIP', 'Regular', 'VVIP', 'Vendor', 'Media']),
            'table_number' => (string) $this->faker->numberBetween(1, 50),
            'whatsapp_status' => 'pending',
            'qr_code' => $token,
            'invitation_link' => url("/checkin/{$token}"),
        ];
    }
}
