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
        Schema::create('courses', function (Blueprint $table) {
            $table->id(); // Primary Key, Auto-Incrementing BigInt, Unsigned
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('level')->nullable()->comment('e.g., Undergraduate, Postgraduate, PhD, Year 1');
            $table->integer('duration_years')->unsigned()->nullable();
            $table->string('status')->default('active')->comment('e.g., active, inactive, archived');
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};