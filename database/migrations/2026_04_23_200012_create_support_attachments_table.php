<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('support_messages')->onDelete('cascade');
            $table->string('original_name');
            $table->string('stored_path');        // relative to storage/app/
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // bytes
            $table->timestamps();

            $table->index('message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_attachments');
    }
};
