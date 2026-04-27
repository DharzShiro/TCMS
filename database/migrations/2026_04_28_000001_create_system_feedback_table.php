<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->string('submitted_by');
            $table->tinyInteger('rating')->unsigned()->comment('1–5 stars');
            $table->string('category')->default('general');
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_feedback');
    }
};
