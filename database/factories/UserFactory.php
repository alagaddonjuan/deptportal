<?php

namespace Database\Factories;

use App\Models\Role; // Import Role model
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Default password for test users
            'remember_token' => Str::random(10),
            'role_id' => Role::factory(), // Create a role for the user by default
        ];
    }
    // ... (unverified state method) ...
}