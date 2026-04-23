<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')
                ->constrained('discounts')
                ->onDelete('cascade');
            $table->string('tenant_id');
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');
            $table->string('action');               // approve | upgrade_superadmin | renewal
            $table->string('plan_slug');
            $table->decimal('original_price', 10, 2);
            $table->decimal('discount_amount', 10, 2);
            $table->decimal('final_price', 10, 2);
            $table->unsignedBigInteger('applied_by')->nullable();
            $table->foreign('applied_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            $table->timestamps();

            $table->index(['discount_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_usages');
    }
};
