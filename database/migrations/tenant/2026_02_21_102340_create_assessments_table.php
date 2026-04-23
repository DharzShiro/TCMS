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

            
            $table->foreignId('enrollment_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('trainer_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->decimal('score', 5, 2)->nullable(); 
            $table->text('remarks')->nullable();

            
            $table->enum('result', ['competent', 'not_yet_competent']);

            $table->timestamp('assessed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};