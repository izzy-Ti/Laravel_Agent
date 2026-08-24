<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'agent@example.com'],
            ['name' => 'Agent Master', 'password' => bcrypt('secret123')]
        );

        Booking::firstOrCreate(
            ['customer_email' => 'alice@example.com', 'date' => '2026-09-01'],
            [
                'customer_name' => 'Alice Johnson',
                'room_type' => 'deluxe',
                'price' => 150.00,
                'status' => 'confirmed',
                'special_requests' => 'High floor, extra pillows'
            ]
        );

        Booking::firstOrCreate(
            ['customer_email' => 'bob@example.com', 'date' => '2026-09-05'],
            [
                'customer_name' => 'Bob Smith',
                'room_type' => 'suite',
                'price' => 250.00,
                'status' => 'confirmed',
                'special_requests' => 'Late check-in at 8 PM'
            ]
        );
    }
}
