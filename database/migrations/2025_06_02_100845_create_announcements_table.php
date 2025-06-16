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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('user_id') // The user (admin/teacher) who created the announcement
                  ->constrained('users')
                  ->onDelete('cascade'); // If the user is deleted, their announcements are also deleted
            $table->string('title');
            $table->text('content');
            $table->string('status')->default('draft')->comment('e.g., draft, published, archived');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Simple Targeting Mechanism
            $table->string('target_audience_type')->nullable()->comment('e.g., all, role, course');
            $table->unsignedBigInteger('target_audience_id')->nullable()->comment('ID of the target (role_id or course_id)');
            
            $table->timestamps(); // created_at and updated_at

            // Indexes
            $table->index(['target_audience_type', 'target_audience_id']);
            $table->index('published_at');
            $table->index('expires_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};