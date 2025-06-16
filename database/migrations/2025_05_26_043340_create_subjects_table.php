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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id(); // Primary Key, Auto-Incrementing BigInt, Unsigned

            $table->foreignId('course_id')
                  ->constrained('courses') // Assumes 'courses' table, 'id' column
                  ->onDelete('cascade');   // If a course is deleted, its subjects are also deleted

            $table->foreignId('teacher_id')
                  ->nullable()
                  ->constrained('users') // Assumes 'users' table, 'id' column for teachers
                  ->onDelete('set null'); // If a teacher is deleted, set teacher_id to null

            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('credits', 4, 2)->unsigned()->nullable(); // e.g., 15.00 credits, adjust precision/scale as needed
            $table->string('type')->nullable()->comment('e.g., Core, Elective, Practical, Theory');
            $table->integer('semester_term')->unsigned()->nullable()->comment('In which semester/term subject is offered');
            $table->string('status')->default('active')->comment('e.g., active, inactive');
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};