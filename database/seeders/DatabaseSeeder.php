<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Guest;
use App\Models\User;
use App\Models\Checkin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Setup Roles
        $superadminRole = Role::firstOrCreate(['name' => 'superadmin']);
        $adminEventRole = Role::firstOrCreate(['name' => 'admin_event']);

        // 2. Create 1 Super Admin Account
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        $superadmin->assignRole($superadminRole);

        // 3. Create 5-10 Events
        $events = Event::factory()->count(8)->create();

        // 4. Create 5-10 regular users (Event Admins) and assign them to events
        $eventAdmins = User::factory()->count(7)->create();
        
        foreach ($eventAdmins as $index => $admin) {
            $admin->assignRole($adminEventRole);
            // Assign each admin to one of the created events
            $admin->update(['event_id' => $events[$index % $events->count()]->id]);
        }

        // 5. Create 30 Guests total, distributed across events
        foreach ($events as $event) {
            $guests = Guest::factory()->count(5)->create([
                'event_id' => $event->id,
                'created_by' => $eventAdmins->random()->id,
            ]);

            // 6. Generate Check-in/Check-out data for testing
            foreach ($guests as $guest) {
                // Randomly check-in some guests
                if (rand(0, 1)) {
                    $checkinTime = now()->subHours(rand(1, 5));
                    $status = rand(0, 1) ? 'checkout' : 'checkin';
                    $checkoutTime = $status === 'checkout' ? (clone $checkinTime)->addHours(rand(1, 3)) : null;

                    Checkin::create([
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

        // Add a specific test admin for convenience
        $testAdmin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Test Admin',
                'password' => Hash::make('password'),
                'event_id' => $events->first()->id,
            ]
        );
        $testAdmin->assignRole($adminEventRole);

        $this->command->info('Seeding completed successfully!');
        $this->command->info('Super Admin: superadmin@example.com / password');
        $this->command->info('Test Admin: admin@example.com / password');
    }
}
