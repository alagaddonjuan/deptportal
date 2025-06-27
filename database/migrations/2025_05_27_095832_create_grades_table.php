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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');
            
            // This is the correct, descriptive column name.
            $table->foreignId('student_user_id')->constrained('users')->onDelete('cascade');

            $table->decimal('marks_obtained', 5, 2);
            $table->string('grade_letter')->nullable();
            $table->timestamps();

            // Prevent a student from having two grades for the same assessment
            $table->unique(['assessment_id', 'student_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};