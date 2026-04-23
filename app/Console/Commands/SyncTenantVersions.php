<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\Updates\TenantVersionService;
use Illuminate\Console\Command;

class SyncTenantVersions extends Command
{
    protected $signature   = 'releases:sync-tenants {--tenant= : Sync a specific tenant by ID}';
    protected $description = 'Sync all tenant version statuses against the latest active release';

    public function handle(TenantVersionService $versions): int
    {
        if ($id = $this->option('tenant')) {
            $tenant = Tenant::find($id);

            if (! $tenant) {
                $this->error("Tenant {$id} not found.");
                return self::FAILURE;
            }

            $status = $versions->syncTenantStatus($tenant);
            $this->info("Tenant {$tenant->name}: status = {$status->update_status}");
            return self::SUCCESS;
        }

        $this->info('Syncing all tenant version statuses…');
        $versions->syncAllStatuses();

        $summary = $versions->getUpdateSummary();
        $this->table(
            ['Metric', 'Count'],
            collect($summary)->map(fn($v, $k) => [ucfirst(str_replace('_', ' ', $k)), $v])->toArray()
        );

        return self::SUCCESS;
    }
}
