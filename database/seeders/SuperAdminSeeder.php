<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Super Admin Seeder
 * 
 * Creates the super admin user for platform management.
 * 
 * Default credentials (CHANGE IN PRODUCTION):
 * Email: admin@platform.com
 * Password: password
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@platform.com',
            'phone' => null,
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        // Optional: Create a regular user for testing
        User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'phone' => null,
            'password' => Hash::make('password'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
    }
}
