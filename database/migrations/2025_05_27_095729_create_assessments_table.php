<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')
                  ->constrained('subjects')
                  ->onDelete('cascade');
            $table->string('name');
            $table->string('type')->nullable()->comment('e.g., exam, quiz, homework, project');
            $table->decimal('max_marks', 8, 2)->unsigned();
            $table->decimal('weightage', 5, 4)->unsigned()->nullable()->comment('e.g., 0.25 for 25%');
            $table->date('assessment_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};