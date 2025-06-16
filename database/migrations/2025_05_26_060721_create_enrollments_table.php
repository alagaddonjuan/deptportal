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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id(); // Primary Key

            $table->foreignId('user_id')    // The student being enrolled
                  ->constrained('users')    // Foreign key to users.id
                  ->onDelete('cascade');   // If user is deleted, their enrollments are deleted

            $table->foreignId('course_id')  // The course they are enrolling in
                  ->constrained('courses')  // Foreign key to courses.id
                  ->onDelete('cascade');   // If course is deleted, enrollments in it are deleted

            $table->date('enrollment_date')->nullable()->default(now());
            $table->string('status')->default('enrolled')->comment('e.g., enrolled, completed, withdrawn, pending');
            $table->string('academic_year')->nullable()->comment('e.g., 2024/2025');
            $table->integer('semester_term')->unsigned()->nullable()->comment('If enrollment is specific to a term');
            $table->text('notes')->nullable();
            $table->timestamps(); // created_at and updated_at

            // Optional: Prevent duplicate enrollments for the same student in the same course for the same academic year.
            // Adjust this constraint if your logic allows for re-enrollment or different scenarios.
            $table->unique(['user_id', 'course_id', 'academic_year'], 'student_course_academic_year_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};