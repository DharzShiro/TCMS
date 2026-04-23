<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_update_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('release_id')->nullable()->constrained('system_releases')->nullOnDelete();
            $table->string('from_version')->nullable();
            $table->string('to_version')->nullable();
            $table->enum('status', ['queued', 'running', 'completed', 'failed', 'rolled_back'])->default('queued');
            $table->enum('triggered_by', ['superadmin', 'tenant', 'auto'])->default('auto');
            $table->text('output')->nullable();                  // Artisan migration output
            $table->text('failure_reason')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index('created_at');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_update_logs');
    }
};
