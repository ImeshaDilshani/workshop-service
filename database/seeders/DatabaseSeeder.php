<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ── Seeded Admin Account ─────────────────────────────────
        // As required by the spec: first Admin is seeded.
        // Admins create every other account via the user management UI.
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@workshop.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // ── Sample Workshops ─────────────────────────────────────
        Workshop::create([
            'code' => 'POT-101',
            'title' => 'Introduction to Pottery',
            'instructor' => 'Maria Chen',
            'start_date' => now()->addDays(3)->setHour(10)->setMinute(0),
            'location' => 'Main Centre - Room A',
            'description' => 'Learn the basics of wheel throwing and hand building. All materials provided. Perfect for beginners.',
            'capacity' => 12,
            'status' => 'scheduled',
        ]);

        Workshop::create([
            'code' => 'CODE-201',
            'title' => 'Python for Beginners',
            'instructor' => 'Alex Kumar',
            'start_date' => now()->addDays(5)->setHour(14)->setMinute(0),
            'location' => 'East Branch - Lab 1',
            'description' => 'A hands-on introduction to Python programming. Bring your own laptop.',
            'capacity' => 20,
            'status' => 'scheduled',
        ]);

        Workshop::create([
            'code' => 'FIT-301',
            'title' => 'Saturday Morning HIIT',
            'instructor' => 'James Wilson',
            'start_date' => now()->addDays(6)->setHour(9)->setMinute(0),
            'location' => 'West Branch - Gym',
            'description' => 'High intensity interval training for all fitness levels. Bring water and a towel.',
            'capacity' => 25,
            'status' => 'scheduled',
        ]);

        Workshop::create([
            'code' => 'ART-102',
            'title' => 'Watercolour Landscapes',
            'instructor' => 'Emma Thompson',
            'start_date' => now()->addDays(7)->setHour(11)->setMinute(0),
            'location' => 'Main Centre - Studio B',
            'description' => 'Explore watercolour techniques for painting beautiful landscapes. All skill levels welcome.',
            'capacity' => 15,
            'status' => 'scheduled',
        ]);

        Workshop::create([
            'code' => 'COOK-201',
            'title' => 'Thai Cooking Masterclass',
            'instructor' => 'Suki Patel',
            'start_date' => now()->subDays(2)->setHour(18)->setMinute(0),
            'location' => 'Main Centre - Kitchen',
            'description' => 'Learn to cook authentic Thai dishes from scratch. All ingredients provided.',
            'capacity' => 10,
            'status' => 'completed',
        ]);

        Workshop::create([
            'code' => 'YOGA-101',
            'title' => 'Gentle Yoga Flow',
            'instructor' => 'Lisa Brown',
            'start_date' => now()->addDays(1)->setHour(8)->setMinute(0),
            'location' => 'East Branch - Hall',
            'description' => 'A gentle, restorative yoga session suitable for all levels. Mats provided.',
            'capacity' => 3,
            'status' => 'scheduled',
        ]);
    }
}
