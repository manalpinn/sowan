<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3) . ' Event',
            'description' => $this->faker->paragraph,
            'location' => $this->faker->address,
            'start_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+1 month', '+2 months'),
            'theme_color' => $this->faker->hexColor,
            'welcome_message' => 'Selamat datang di acara kami!',
            'is_active' => true,
        ];
    }
}
