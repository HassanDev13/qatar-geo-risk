<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin user
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@munasiq.org',
            'password' => 'password', // Default password, should be changed after first login
            'is_admin' => true,
        ]);

        // Create Hacene user
        User::factory()->create([
            'name' => 'Hacene',
            'email' => 'hacene@munasiq.org',
            'password' => 'password', // Default password, should be changed after first login
        ]);

        // Create Zahra user
        User::factory()->create([
            'name' => 'Zahra',
            'email' => 'zahra@munasiq.org',
            'password' => 'password', // Default password, should be changed after first login
        ]);
    }
}
