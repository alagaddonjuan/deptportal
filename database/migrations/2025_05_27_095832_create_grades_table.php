<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')
                  ->constrained('enrollments')
                  ->onDelete('cascade');
            $table->foreignId('assessment_id')
                  ->constrained('assessments')
                  ->onDelete('cascade');
            $table->decimal('marks_obtained', 8, 2)->unsigned()->nullable();
            $table->string('grade_letter', 5)->nullable()->comment('e.g., A+, B, Pass');
            $table->text('comments')->nullable();
            $table->foreignId('graded_by_user_id')->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->date('grading_date')->nullable();
            $table->timestamps();

            // Unique constraint: a student should have one grade entry per assessment in an enrollment
            $table->unique(['enrollment_id', 'assessment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};