<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SchoolConfiguration; // Import the model

class SchoolConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default configuration record if one doesn't exist with ID 1
        SchoolConfiguration::firstOrCreate(
            ['id' => 1], // Condition to find existing record
            [            // Values to use if creating a new record
                'school_name' => 'Your School Name',
                'school_address' => '123 School Street, City, Country',
                'school_phone' => '+1234567890',
                'school_email' => 'info@yourschool.com',
                'current_academic_year' => '2024/2025', // Example
                'current_term_semester' => 'First Term', // Example
                'school_logo_path' => null,
                'date_format' => 'Y-m-d',
                'app_timezone' => 'UTC',
                'currency_symbol' => '$',
            ]
        );

        $this->command->info('Default school configuration seeded/ensured.');
    }
}