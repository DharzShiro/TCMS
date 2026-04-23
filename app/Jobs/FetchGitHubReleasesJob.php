<?php

namespace App\Jobs;

use App\Events\NewReleasePublished;
use App\Models\SystemRelease;
use App\Services\Updates\GitHubReleaseService;
use App\Services\Updates\TenantVersionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchGitHubReleasesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function handle(
        GitHubReleaseService $github,
        TenantVersionService $versions,
    ): void {
        if (! $github->isConfigured()) {
            Log::info('[GitHub] Skipping fetch — owner/repo not configured.');
            return;
        }

        $knownLatest = SystemRelease::latest()?->version;

        $synced = $github->syncToDatabase();

        Log::info("[GitHub] Synced {$synced} releases from GitHub.");

        $newLatest = SystemRelease::latest();

        // If a new version appeared, sync all tenant statuses and fire event
        if ($newLatest && $newLatest->version !== $knownLatest) {
            Log::info("[GitHub] New release detected: {$newLatest->version}");
            $versions->syncAllStatuses();
            event(new NewReleasePublished($newLatest));
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('[GitHub] FetchReleasesJob failed: ' . $e->getMessage());
    }
}
