<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoUser = User::firstOrCreate(
            ['email' => 'demo'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('pakyudhatampan'),
                'is_demo' => true,
            ]
        );

        // Ensure 'admin' role exists
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

        // Ensure user has 'admin' role
        if (!$demoUser->hasRole('admin')) {
            $demoUser->assignRole($adminRole);
        }

        // Create 5 sample events for this user if they don't have any
        if ($demoUser->events()->count() < 5) {
            $events = Event::factory()->count(5)->create();
            $demoUser->events()->attach($events->pluck('id')->toArray());

            // Add guests and checkins to the events
            foreach ($events as $event) {
                $guests = \App\Models\Guest::factory()->count(rand(5, 15))->create([
                    'event_id' => $event->id,
                    'created_by' => $demoUser->id,
                ]);

                // Randomly check-in some guests
                foreach ($guests as $guest) {
                    if (rand(0, 1)) {
                        $checkinTime = now()->subHours(rand(1, 5));
                        $status = rand(0, 1) ? 'checkout' : 'checkin';
                        $checkoutTime = $status === 'checkout' ? (clone $checkinTime)->addHours(rand(1, 3)) : null;

                        \App\Models\Checkin::create([
                            'event_id' => $event->id,
                            'guest_id' => $guest->id,
                            'checkin_time' => $checkinTime,
                            'checkout_time' => $checkoutTime,
                            'status' => $status,
                            'method' => 'qr',
                        ]);
                    }
                }
            }
        }
    }
}
