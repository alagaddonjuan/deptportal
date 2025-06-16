<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role; // <-- Import the Role model

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use firstOrCreate to prevent duplicates if the seeder is run multiple times
        Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Administrator']);
        Role::firstOrCreate(['slug' => 'teacher'], ['name' => 'Teacher']);
        Role::firstOrCreate(['slug' => 'student'], ['name' => 'Student']);
        Role::firstOrCreate(['slug' => 'parent'], ['name' => 'Parent/Guardian']);
    }
}