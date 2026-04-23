<?php

namespace App\Services\Updates;

use App\Events\TenantUpdateCompleted;
use App\Events\TenantUpdateFailed;
use App\Jobs\ProcessTenantUpdateJob;
use App\Models\SystemRelease;
use App\Models\Tenant;
use App\Models\TenantUpdateLog;
use App\Models\TenantVersionStatus;
use Illuminate\Support\Collection;

class TenantVersionService
{
    public function getOrCreateStatus(Tenant $tenant): TenantVersionStatus
    {
        return TenantVersionStatus::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'current_version' => config('github.current_version', '1.0.0'),
                'update_status'   => 'up_to_date',
            ]
        );
    }

    /**
     * Refresh all tenants' latest_version and update_status against the
     * current active release. Called by the daily sync command.
     */
    public function syncAllStatuses(): void
    {
        $latest = SystemRelease::latest();

        if (! $latest) {
            return;
        }

        Tenant::where('status', 'approved')->each(function (Tenant $tenant) use ($latest) {
            $this->syncTenantStatus($tenant, $latest);
        });
    }

    public function syncTenantStatus(Tenant $tenant, ?SystemRelease $release = null): TenantVersionStatus
    {
        $release ??= SystemRelease::latest();
        $status   = $this->getOrCreateStatus($tenant);

        $data = ['last_checked_at' => now()];

        if ($release) {
            $data['latest_version'] = $release->version;

            // Only flip to "update_available" if not currently in a running/queued state
            if (
                ! in_array($status->update_status, ['queued', 'running']) &&
                version_compare($release->version, $status->current_version ?? '0.0.0', '>')
            ) {
                $data['update_status'] = 'update_available';
            }
        }

        $status->update($data);
        $status->refresh();

        return $status;
    }

    /**
     * Queue a migration job for a specific tenant.
     */
    public function queueUpdate(Tenant $tenant, SystemRelease $release, string $triggeredBy = 'auto'): TenantUpdateLog
    {
        $status = $this->getOrCreateStatus($tenant);

        $log = TenantUpdateLog::create([
            'tenant_id'    => $tenant->id,
            'release_id'   => $release->id,
            'from_version' => $status->current_version,
            'to_version'   => $release->version,
            'status'       => 'queued',
            'triggered_by' => $triggeredBy,
        ]);

        $status->update(['update_status' => 'queued']);

        ProcessTenantUpdateJob::dispatch($tenant, $release, $log->id)
            ->onQueue('updates');

        return $log;
    }

    /**
     * Queue updates for ALL tenants that need it (batch rollout).
     */
    public function queueAllPendingUpdates(SystemRelease $release): int
    {
        $queued = 0;

        TenantVersionStatus::where('update_status', 'update_available')
            ->with('tenant')
            ->each(function (TenantVersionStatus $vs) use ($release, &$queued) {
                if ($vs->tenant) {
                    $this->queueUpdate($vs->tenant, $release, 'auto');
                    $queued++;
                }
            });

        return $queued;
    }

    public function getTenantsNeedingUpdate(): Collection
    {
        return TenantVersionStatus::where('update_status', 'update_available')
            ->with('tenant')
            ->get();
    }

    public function getUpdateSummary(): array
    {
        $counts = TenantVersionStatus::selectRaw('update_status, count(*) as total')
            ->groupBy('update_status')
            ->pluck('total', 'update_status')
            ->toArray();

        $total = Tenant::where('status', 'approved')->count();

        return [
            'total'            => $total,
            'up_to_date'       => $counts['up_to_date'] ?? 0,
            'update_available' => $counts['update_available'] ?? 0,
            'queued'           => $counts['queued'] ?? 0,
            'running'          => $counts['running'] ?? 0,
            'failed'           => $counts['failed'] ?? 0,
        ];
    }
}
