<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_version_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('current_version')->nullable();       // Version tenant is on now
            $table->string('latest_version')->nullable();        // Latest detected version
            $table->enum('update_status', [
                'up_to_date',
                'update_available',
                'queued',
                'running',
                'completed',
                'failed',
                'skipped',
            ])->default('up_to_date');
            $table->string('failure_reason')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->json('applied_releases')->nullable();        // Array of release IDs applied
            $table->timestamps();

            $table->unique('tenant_id');
            $table->index('update_status');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_version_statuses');
    }
};
