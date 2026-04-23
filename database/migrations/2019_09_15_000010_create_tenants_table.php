<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();

            // Custom columns
            $table->string('name');
            $table->string('admin_email')->unique();
            $table->string('subdomain')->unique();
            $table->string('subscription')->default('basic');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_active')->default(true);
            $table->string('brand_name')->nullable();         // Custom app name
            $table->string('brand_logo')->nullable();         // Path to uploaded logo
            $table->string('brand_color_primary')->nullable(); // e.g. #003087
            $table->string('brand_color_accent')->nullable();  // e.g. #CE1126
            $table->string('brand_tagline')->nullable();       // Custom tagline
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
            $table->json('data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}