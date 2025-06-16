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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')
                  ->constrained('assignments')
                  ->onDelete('cascade');
            $table->foreignId('student_id') // User (student) who made the submission
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->foreignId('enrollment_id')->nullable() // Context of enrollment (optional)
                  ->constrained('enrollments')
                  ->onDelete('set null'); // If enrollment is deleted, submission might still be relevant via student_id
            $table->timestamp('submission_date')->useCurrent();
            $table->string('file_path')->nullable()->comment('Path to the submitted file');
            $table->text('text_content')->nullable()->comment('For text-based online submissions');
            $table->string('status')->default('submitted')->comment('e.g., submitted, late, graded, resubmitted');
            $table->decimal('marks_awarded', 8, 2)->unsigned()->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->foreignId('graded_by_user_id')->nullable() // User (teacher) who graded
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamps();

            // Optional: Prevent multiple submissions by the same student for the same assignment
            // You might handle versions differently in application logic if resubmissions are allowed.
            $table->unique(['assignment_id', 'student_id'], 'student_assignment_submission_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};