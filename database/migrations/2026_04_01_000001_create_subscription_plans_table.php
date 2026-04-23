<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();

            // Slug matches the existing 'basic' | 'standard' | 'premium' values in tenants table
            $table->string('slug')->unique();           // basic, standard, premium
            $table->string('name');                     // Basic Plan, Standard Plan, Premium Plan
            $table->string('icon', 20)->default('📦');
            $table->text('description')->nullable();

            // Pricing
            $table->decimal('price', 10, 2)->default(0);   // base price (₱ / month equivalent)
            $table->string('currency', 10)->default('PHP');

            // Duration in days that each plan grants
            $table->unsignedInteger('duration_days');       // 30, 180, 365

            // Feature limits — mirrors CheckSubscription / SubscriptionHelper
            $table->unsignedInteger('max_trainees')->nullable();   // null = unlimited
            $table->unsignedInteger('max_trainers')->nullable();
            $table->unsignedInteger('max_users')->nullable();
            $table->unsignedInteger('max_courses')->nullable();
            $table->unsignedInteger('max_exports_monthly')->nullable(); // null = unlimited, 0 = none

            // Allowed export formats stored as JSON array: ["csv"], ["csv","excel","pdf"]
            $table->json('allowed_export_formats')->nullable();

            // Feature flags
            $table->boolean('has_assessments')->default(false);
            $table->boolean('has_certificates')->default(false);
            $table->boolean('has_custom_reports')->default(false);
            $table->boolean('has_branding')->default(false);
            $table->boolean('has_trainers')->default(false);

            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->date('available_from')->nullable();
            $table->date('available_until')->nullable();

            $table->timestamps();
        });

        // Seed the three canonical plans so the system has data immediately
        DB::table('subscription_plans')->insert([
            [
                'slug'                   => 'basic',
                'name'                   => 'Basic Plan',
                'description'            => 'Essentials for small training centers. Manage trainees, courses, enrollments, and attendance.',
                'price'                  => 0.00,
                'currency'               => 'PHP',
                'duration_days'          => 30,
                'max_trainees'           => 100,
                'max_trainers'           => 0,
                'max_users'              => 1,
                'max_courses'            => 20,
                'max_exports_monthly'    => 0,
                'allowed_export_formats' => json_encode([]),
                'has_assessments'        => false,
                'has_certificates'       => false,
                'has_custom_reports'     => false,
                'has_branding'           => false,
                'has_trainers'           => false,
                'is_active'              => true,
                'sort_order'             => 1,
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
            [
                'slug'                   => 'standard',
                'name'                   => 'Standard Plan',
                'description'            => 'Full operations suite with trainers, assessments, schedules, and CSV exports.',
                'price'                  => 1499.00,
                'currency'               => 'PHP',
                'duration_days'          => 180,
                'max_trainees'           => 500,
                'max_trainers'           => null,
                'max_users'              => 5,
                'max_courses'            => null,
                'max_exports_monthly'    => 3000,
                'allowed_export_formats' => json_encode(['csv']),
                'has_assessments'        => true,
                'has_certificates'       => false,
                'has_custom_reports'     => false,
                'has_branding'           => false,
                'has_trainers'           => true,
                'is_active'              => true,
                'sort_order'             => 2,
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
            [
                'slug'                   => 'premium',
                'name'                   => 'Premium Plan',
                'description'            => 'Unlimited everything — certificates, custom reports, branding, and all export formats.',
                'price'                  => 3999.00,
                'currency'               => 'PHP',
                'duration_days'          => 365,
                'max_trainees'           => null,
                'max_trainers'           => null,
                'max_users'              => null,
                'max_courses'            => null,
                'max_exports_monthly'    => null,
                'allowed_export_formats' => json_encode(['csv', 'excel', 'pdf']),
                'has_assessments'        => true,
                'has_certificates'       => true,
                'has_custom_reports'     => true,
                'has_branding'           => true,
                'has_trainers'           => true,
                'is_active'              => true,
                'sort_order'             => 3,
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
        ]);

        // Set default icons based on slug
        DB::table('subscription_plans')->where('slug', 'basic')->update(['icon' => '🌱']);
        DB::table('subscription_plans')->where('slug', 'standard')->update(['icon' => '🚀']);
        DB::table('subscription_plans')->where('slug', 'premium')->update(['icon' => '💎']);
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
