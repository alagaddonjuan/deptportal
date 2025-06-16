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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            
            // This is the correct column name for the student's ID
            $table->foreignId('student_user_id')->constrained('users')->onDelete('cascade');
            
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->date('session_date');
            $table->enum('status', ['present', 'absent', 'late', 'excused']);
            $table->text('remarks')->nullable();
            $table->timestamps();

            // Prevent duplicate records for the same student, subject, and date
            $table->unique(['student_user_id', 'subject_id', 'session_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};