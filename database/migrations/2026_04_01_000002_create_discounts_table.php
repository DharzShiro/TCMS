<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();           // e.g. SAVE20
            $table->string('label');                    // Human-readable name
            $table->enum('type', ['percentage', 'fixed']); // % or flat ₱
            $table->decimal('value', 10, 2);            // 20.00 or 500.00
            $table->json('plan_slugs')->nullable();    // null = applies to all plans
            $table->json('tenant_ids')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_automatic')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};