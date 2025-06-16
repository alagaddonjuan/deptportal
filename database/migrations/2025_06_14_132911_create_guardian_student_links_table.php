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
        Schema::create('guardian_student_links', function (Blueprint $table) {
            $table->id();
            
            // Foreign key for the guardian/parent
            $table->foreignId('guardian_user_id')->constrained('users')->onDelete('cascade');
            
            // Foreign key for the student
            $table->foreignId('student_user_id')->constrained('users')->onDelete('cascade');

            $table->string('relationship_type');
            $table->timestamps();

            // Add a unique constraint to prevent linking the same parent to the same student twice
            $table->unique(['guardian_user_id', 'student_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardian_student_links');
    }
};