<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();           // e.g. TKT-0001
            $table->string('tenant_id');
            // Requester info stored as strings — tenant user lives in tenant DB
            $table->string('requester_name');
            $table->string('requester_email');
            $table->unsignedBigInteger('tenant_user_id')->nullable(); // ref only (tenant DB)
            $table->string('subject');
            $table->enum('category', [
                'bug_report',
                'technical_issue',
                'account_concern',
                'billing_concern',
                'feature_request',
                'general_inquiry',
            ])->default('general_inquiry');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->unsignedBigInteger('assignee_id')->nullable(); // central users table (superadmin)
            $table->timestamp('last_reply_at')->nullable();
            $table->enum('last_reply_by', ['admin', 'tenant'])->nullable();
            $table->unsignedInteger('unread_admin')->default(0);  // Unread count for admin
            $table->unsignedInteger('unread_tenant')->default(0); // Unread count for tenant
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['status', 'priority']);
            $table->index('ticket_number');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
