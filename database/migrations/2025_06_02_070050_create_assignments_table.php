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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')
                  ->constrained('subjects')
                  ->onDelete('cascade');
            $table->foreignId('teacher_id') // User who created the assignment
                  ->constrained('users')
                  ->onDelete('cascade'); // Or 'set null' if assignments should remain if teacher is deleted
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->decimal('max_marks', 8, 2)->unsigned()->nullable();
            $table->string('file_path')->nullable()->comment('Path to an attached assignment file');
            $table->string('status')->default('published')->comment('e.g., draft, published, archived');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};