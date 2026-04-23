// database/migrations/2024_01_01_000001_create_tenant_usage_stats_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->create('tenant_usage_stats', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->bigInteger('db_size_bytes')->default(0);
            $table->bigInteger('file_size_bytes')->default(0);
            $table->bigInteger('bandwidth_bytes_today')->default(0);
            $table->bigInteger('bandwidth_bytes_total')->default(0);
            $table->date('bandwidth_date')->nullable();
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique('tenant_id');
            $table->index('bandwidth_date');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('tenant_usage_stats');
    }
};