<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role; // <-- Import the Role model
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // <-- Import the Hash facade

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run the seeder that creates the roles ('admin', 'teacher', etc.)
        $this->call(RoleSeeder::class);

        // Find the 'admin' role
        $adminRole = Role::where('slug', 'admin')->first();

        // Create a default admin user
        // The User::firstOrCreate method will find the user by email or create them if they don't exist.
        User::firstOrCreate(
            ['email' => 'admin@tasuedncsportal.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('T@suedP0rtal'), // The default password will be 'T@suedP0rtal'
                'role_id' => $adminRole->id,
                'email_verified_at' => now(), // Mark email as verified
            ]
        );
    }
}
