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
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subject_id')
                  ->constrained('subjects')
                  ->onDelete('cascade');

            $table->foreignId('teacher_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->tinyInteger('day_of_week')->unsigned()->comment('Day of the week (ISO-8601: 1 for Monday, 7 for Sunday)');
            $table->time('start_time');
            $table->time('end_time');
            // --- MODIFIED LINES ---
            $table->string('location', 100)->nullable()->comment('e.g., Room 101, Physics Lab, Online'); // Reduced length
            $table->string('academic_year_term', 30)->nullable()->comment('e.g., 2025/2026-Sem1');     // Reduced length
            // --- END OF MODIFIED LINES ---
            $table->text('notes')->nullable();
            $table->timestamps();

            // Custom shorter index names
            $table->index(
                ['teacher_id', 'day_of_week', 'start_time', 'academic_year_term'],
                'cs_teacher_day_time_term_idx' // Shorter custom name
            );
            $table->index(
                ['location', 'day_of_week', 'start_time', 'academic_year_term'],
                'cs_location_day_time_term_idx' // Shorter custom name
            );
            $table->index(
                ['subject_id', 'academic_year_term'],
                'cs_subject_academic_term_idx' // Custom name for consistency, though original might have been fine
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_schedules', function (Blueprint $table) {
            // Optional: Explicitly drop custom-named indexes if needed for rollback testing,
            // though dropIfExists('class_schedules') handles the table.
            // Laravel usually handles dropping indexes by their column definition as well.
            // If you want to be explicit for custom names:
            // $table->dropIndex('cs_teacher_day_time_term_idx');
            // $table->dropIndex('cs_location_day_time_term_idx');
            // $table->dropIndex('cs_subject_academic_term_idx');
        });
        Schema::dropIfExists('class_schedules');
    }
};