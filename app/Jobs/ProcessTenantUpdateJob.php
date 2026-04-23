<?php

namespace App\Jobs;

use App\Events\TenantUpdateCompleted;
use App\Events\TenantUpdateFailed;
use App\Models\Notification;
use App\Models\SystemRelease;
use App\Models\Tenant;
use App\Models\TenantUpdateLog;
use App\Models\TenantVersionStatus;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ProcessTenantUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;  // Do not auto-retry a partially-applied migration
    public int $timeout = 600;

    public function __construct(
        private readonly Tenant $tenant,
        private readonly SystemRelease $release,
        private readonly int $logId,
    ) {}

    public function handle(): void
    {
        $log = TenantUpdateLog::find($this->logId);

        if (! $log) {
            Log::error("[Update] Log #{$this->logId} not found — aborting.");
            return;
        }

        $log->update(['status' => 'running', 'started_at' => now()]);
        TenantVersionStatus::where('tenant_id', $this->tenant->id)
            ->update(['update_status' => 'running']);

        try {
            // Run pending tenant migrations for this specific tenant
            Artisan::call('tenants:migrate', [
                '--tenants' => [$this->tenant->id],
                '--force'   => true,
            ]);

            $output = Artisan::output();

            $log->update([
                'status'       => 'completed',
                'output'       => $output,
                'completed_at' => now(),
            ]);

            TenantVersionStatus::updateOrCreate(
                ['tenant_id' => $this->tenant->id],
                [
                    'current_version' => $this->release->version,
                    'latest_version'  => $this->release->version,
                    'update_status'   => 'up_to_date',
                    'last_updated_at' => now(),
                    'failure_reason'  => null,
                ]
            );

            // Add release to applied list
            $status = TenantVersionStatus::where('tenant_id', $this->tenant->id)->first();
            $applied = $status->applied_releases ?? [];
            $applied[] = $this->release->id;
            $status->update(['applied_releases' => array_unique($applied)]);

            Log::info("[Update] Tenant {$this->tenant->id} updated to {$this->release->version}.");

            // Notify tenant admin
            $this->notifyTenantAdmin(
                "System Updated to v{$this->release->version}",
                "Your system has been successfully updated to version {$this->release->version}. Enjoy the new features!",
                '/admin/update'
            );

            event(new TenantUpdateCompleted($this->tenant, $this->release));

        } catch (\Throwable $e) {
            Log::error("[Update] Failed for tenant {$this->tenant->id}: {$e->getMessage()}");

            $log->update([
                'status'         => 'failed',
                'failure_reason' => $e->getMessage(),
                'completed_at'   => now(),
            ]);

            TenantVersionStatus::where('tenant_id', $this->tenant->id)
                ->update([
                    'update_status'  => 'failed',
                    'failure_reason' => substr($e->getMessage(), 0, 500),
                ]);

            // Notify tenant admin of failure
            $this->notifyTenantAdmin(
                "Update Failed — Action Required",
                "The update to v{$this->release->version} failed. Please contact support.",
                '/admin/support/create'
            );

            event(new TenantUpdateFailed($this->tenant, $this->release, $e->getMessage()));
        }
    }

    private function notifyTenantAdmin(string $title, string $message, string $link): void
    {
        try {
            $this->tenant->run(function () use ($title, $message, $link) {
                $admin = User::where('role', 'admin')->first();
                if ($admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'title'   => $title,
                        'message' => $message,
                        'link'    => $link,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::warning("[Update] Could not notify tenant admin: {$e->getMessage()}");
        }
    }

    public function failed(\Throwable $e): void
    {
        TenantVersionStatus::where('tenant_id', $this->tenant->id)
            ->update(['update_status' => 'failed', 'failure_reason' => $e->getMessage()]);

        Log::error("[Update] Job exhausted for tenant {$this->tenant->id}: {$e->getMessage()}");
    }
}
