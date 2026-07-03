<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = env('SEED_ADMIN_PASSWORD', \Illuminate\Support\Str::random(16));

        User::factory()->create([
            'name' => env('SEED_ADMIN_NAME', 'Admin'),
            'email' => env('SEED_ADMIN_EMAIL', 'admin@example.com'),
            'password' => $password,
            'isAdmin' => true,
            'selected' => true,
            'finished' => false,
        ]);

        if (! env('SEED_ADMIN_PASSWORD')) {
            $this->command->warn("Generated admin password: {$password}");
        }
    }
}
