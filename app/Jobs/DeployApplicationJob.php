<?php

namespace App\Jobs;

use App\Models\SystemRelease;
use App\Services\Updates\TenantVersionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DeployApplicationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 900;

    public function __construct(private readonly SystemRelease $release) {}

    public function handle(TenantVersionService $versions): void
    {
        $version = $this->release->version;
        Log::info("[Deploy] Starting full deployment for v{$version}");

        // Pull latest code from GitHub (new modules, Blade templates, PHP classes)
        $this->exec(['git', 'pull', 'origin', 'main']);

        // Install any new PHP packages added in this release
        $this->exec(['composer', 'install', '--no-dev', '--optimize-autoloader', '--no-interaction']);

        // Install JS dependencies and rebuild all frontend assets (CSS, JS, new UI)
        $this->exec(['npm', 'ci']);
        $this->exec(['npm', 'run', 'build']);

        // Run central database migrations
        Artisan::call('migrate', ['--force' => true]);
        Log::info('[Deploy] Central migrations: ' . trim(Artisan::output()));

        // Rebuild all caches so new routes, config, and views are picked up
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        // Mark this release as the active deployed version
        $this->release->update(['is_deployed' => true, 'is_active' => true]);
        SystemRelease::where('id', '!=', $this->release->id)->update(['is_active' => false]);

        // Detect which tenants are behind and queue their database migrations
        $versions->syncAllStatuses();
        $queued = $versions->queueAllPendingUpdates($this->release);

        Log::info("[Deploy] v{$version} deployed. Queued {$queued} tenant migration job(s).");

        // Signal all queue workers to reload — they pick up the new code after current jobs finish
        Artisan::call('queue:restart');
    }

    public function failed(\Throwable $e): void
    {
        Log::error("[Deploy] Deployment of v{$this->release->version} failed: {$e->getMessage()}");
    }

    private function exec(array $command): void
    {
        $process = new Process($command, base_path());
        $process->setTimeout(300);

        try {
            $process->mustRun();
            Log::info('[Deploy] ' . implode(' ', $command), ['output' => trim($process->getOutput())]);
        } catch (ProcessFailedException $e) {
            Log::error('[Deploy] Command failed: ' . implode(' ', $command), [
                'stdout' => $process->getOutput(),
                'stderr' => $process->getErrorOutput(),
            ]);
            throw $e;
        }
    }
}
