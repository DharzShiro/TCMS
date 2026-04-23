<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->string('plan_slug');
            $table->foreignId('discount_usage_id')->nullable()
                ->constrained('discount_usages')->onDelete('set null'); // link if a discount was used
            $table->decimal('amount_paid', 10, 2);
            $table->string('action');           // approve | upgrade_superadmin | renewal
            $table->timestamp('starts_at');
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('applied_by')->nullable();
            $table->foreign('applied_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['tenant_id', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_subscriptions');
    }
};
