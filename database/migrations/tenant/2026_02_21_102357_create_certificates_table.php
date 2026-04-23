<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();

            
            $table->foreignId('enrollment_id')
                  ->constrained()
                  ->cascadeOnDelete();

            
            $table->string('certificate_number')->unique();

            $table->date('issued_at');
            $table->date('expires_at')->nullable();
            $table->foreignId('trainer_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};