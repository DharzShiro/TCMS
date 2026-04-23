<?php

namespace App\Console\Commands;

use App\Services\TenantStorageService;
use Illuminate\Console\Command;

class RecalculateTenantStorage extends Command
{
    protected $signature   = 'tenants:recalculate-storage {--tenant= : Specific tenant ID}';
    protected $description = 'Recalculate storage usage for all (or one) tenant(s)';

    public function handle(TenantStorageService $service): void
    {
        if ($tenantId = $this->option('tenant')) {
            $tenant = \App\Models\Tenant::findOrFail($tenantId);
            $stat   = $service->calculate($tenant);
            $this->info("✓ {$tenant->name}: DB={$stat->formatted_db_size}, Files={$stat->formatted_file_size}");
            return;
        }

        $this->info('Recalculating storage for all approved tenants...');
        $results = $service->calculateAll();

        $this->table(['Tenant', 'DB', 'Files', 'Total'], array_map(fn($r) => [
            $r['tenant'],
            $r['db']    ?? '—',
            $r['files'] ?? '—',
            $r['total'] ?? ('Error: ' . ($r['error'] ?? '?')),
        ], $results));

        $this->info('Done.');
    }
}