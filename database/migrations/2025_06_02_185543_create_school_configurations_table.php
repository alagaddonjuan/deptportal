<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('school_configurations', function (Blueprint $table) {
            $table->id(); // Primary Key

            $table->string('school_name', 255)->nullable();
            $table->text('school_address')->nullable();
            $table->string('school_phone', 50)->nullable();
            $table->string('school_email', 255)->nullable();
            $table->string('current_academic_year', 20)->nullable()->comment('e.g., 2024/2025');
            $table->string('current_term_semester', 50)->nullable()->comment('e.g., First Term, Spring Semester 2025');
            $table->string('school_logo_path', 255)->nullable()->comment('Path to uploaded school logo');
            $table->string('date_format', 20)->default('Y-m-d')->nullable();
            $table->string('app_timezone', 100)->default('UTC')->nullable();
            $table->string('currency_symbol', 5)->default('$')->nullable();

            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_configurations');
    }
};