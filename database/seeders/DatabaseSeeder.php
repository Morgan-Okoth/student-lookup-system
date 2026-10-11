<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin user for the admin panel
        User::firstOrCreate(
            ['email' => 'admin@university.ac.ke'],
            [
                'name'     => 'System Administrator',
                'password' => Hash::make('Admin@1234'),
            ]
        );

        // Seed in dependency order
        $this->call([
            StudentSeeder::class,
            CourseSeeder::class,
            ResultSeeder::class,
        ]);
    }
}
