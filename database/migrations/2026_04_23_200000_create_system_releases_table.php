<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_releases', function (Blueprint $table) {
            $table->id();
            $table->string('github_id')->unique()->nullable();
            $table->string('tag_name');                          // e.g. v1.2.0
            $table->string('version');                           // e.g. 1.2.0 (semver)
            $table->string('name')->nullable();                  // GitHub release title
            $table->longText('body')->nullable();                // Markdown changelog / release notes
            $table->boolean('is_prerelease')->default(false);
            $table->boolean('is_active')->default(false);        // Superadmin marks this as "live"
            $table->boolean('is_deployed')->default(false);      // Code is deployed to server
            $table->string('github_url')->nullable();
            $table->string('download_url')->nullable();
            $table->json('manifest')->nullable();                // Optional structured metadata
            $table->timestamp('published_at')->nullable();       // GitHub published_at
            $table->timestamps();

            $table->index('version');
            $table->index('is_active');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_releases');
    }
};
