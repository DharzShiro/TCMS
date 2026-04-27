<?php

namespace App\Console\Commands;

use App\Jobs\FetchGitHubReleasesJob;
use App\Services\Updates\GitHubReleaseService;
use Illuminate\Console\Command;

class FetchGitHubReleases extends Command
{
    protected $signature   = 'releases:fetch {--sync : Sync tenant version statuses after fetch}';
    protected $description = 'Fetch latest releases from GitHub and sync to database';

    public function handle(GitHubReleaseService $github): int
    {
        if (! $github->isConfigured()) {
            $this->error('GitHub is not configured. Set GITHUB_OWNER and GITHUB_REPO in .env');
            return self::FAILURE;
        }

        $this->info('Fetching releases from GitHub…');

        $stats = $github->syncToDatabase();

        $this->info("Fetched: {$stats['fetched']} | Synced: {$stats['synced']} | Skipped prerelease: {$stats['skipped_prerelease']} | Skipped invalid tag: {$stats['skipped_invalid_tag']}");

        if ($this->option('sync')) {
            $this->call('releases:sync-tenants');
        }

        return self::SUCCESS;
    }
}
