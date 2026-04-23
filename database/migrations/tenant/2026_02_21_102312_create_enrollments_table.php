<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();

            
            $table->foreignId('trainee_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            
            $table->foreignId('course_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->enum('status', ['pending', 'approved', 'completed', 'dropped'])
                  ->default('pending');

            
            $table->timestamp('enrolled_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};