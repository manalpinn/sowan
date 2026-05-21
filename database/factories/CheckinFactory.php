<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;

class CheckinFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'guest_id' => Guest::factory(),
            'checkin_time' => $this->faker->dateTimeBetween('-1 day', 'now'),
            'status' => 'checkin',
            'method' => 'qr',
        ];
    }
}
