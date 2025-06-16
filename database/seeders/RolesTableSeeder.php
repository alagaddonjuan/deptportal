<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Import DB facade
use App\Models\Role; // Import the Role model

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Manages the entire school portal system, users, and settings.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Teacher',
                'slug' => 'teacher',
                'description' => 'Manages courses, subjects, assignments, grades, and student attendance.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Student',
                'slug' => 'student',
                'description' => 'Accesses courses, submits assignments, views grades and attendance.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Parent',
                'slug' => 'parent',
                'description' => 'Views their child\'s progress, grades, attendance, and school announcements.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert the roles into the database
        // Using DB::table for mass insert is often efficient
        // Or loop and create with Eloquent model:
        foreach ($roles as $roleData) {
            Role::create($roleData);
        }

        // Alternatively, for simple cases without much logic per role:
        // DB::table('roles')->insert($roles);

        $this->command->info('Roles table seeded successfully!');
    }  //
    
}
