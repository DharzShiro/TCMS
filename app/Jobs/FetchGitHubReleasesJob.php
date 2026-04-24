<?php

namespace App\Jobs;

use App\Events\NewReleasePublished;
use App\Models\SystemRelease;
use App\Services\Updates\GitHubReleaseService;
use App\Services\Updates\TenantVersionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchGitHubReleasesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // 1 try — retrying won't fix a config or network problem
    public int $tries   = 1;
    public int $timeout = 60;

    public function handle(
        GitHubReleaseService $github,
        TenantVersionService $versions,
    ): void {
        if (! $github->isConfigured()) {
            Log::info('[GitHub] Skipping fetch — GITHUB_OWNER or GITHUB_REPO not set in .env.');
            return;
        }

        $knownLatest = SystemRelease::latestActive()?->version;

        try {
            $synced = $github->syncToDatabase();
        } catch (ConnectionException $e) {
            // GitHub unreachable (no internet, firewall, DNS) — not a fatal error
            Log::warning('[GitHub] Could not reach GitHub API: ' . $e->getMessage());
            return;
        } catch (\Throwable $e) {
            Log::error('[GitHub] Unexpected error during sync: ' . $e->getMessage());
            return;
        }

        Log::info("[GitHub] Synced {$synced} releases from GitHub.");

        $newLatest = SystemRelease::latestActive();

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
