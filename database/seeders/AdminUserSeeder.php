<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    // Find the 'admin' role
    $adminRole = Role::where('slug', 'admin')->first();

    if ($adminRole) {
        // Check if admin user already exists to avoid duplicates
        $adminUser = User::where('email', 'admin@schoolportal.com')->first();

        if (!$adminUser) {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@tasuedncsportal.com',
            'password' => Hash::make('T@suedP0rtal'), // Change 'password' to a strong default password
            'role_id' => $adminRole->id,
            'email_verified_at' => now(), // Optionally mark email as verified
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->command->info('Admin user created successfully.');
        } else {
        $this->command->info('Admin user already exists.');
        }
    } else {
        $this->command->error('Admin role not found. Please run RolesTableSeeder first.');
    }

    }
}