<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantUsageStat;
use Illuminate\Support\Facades\DB;

class TenantStorageService
{
    /**
     * Recalculate storage (DB + files) for a single tenant.
     */
    public function calculate(Tenant $tenant): TenantUsageStat
    {
        $dbSize   = $this->getDatabaseSize($tenant);
        $fileSize = $this->getFileSize($tenant);

        return TenantUsageStat::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'db_size_bytes'       => $dbSize,
                'file_size_bytes'     => $fileSize,
                'last_calculated_at'  => now(),
            ]
        );
    }

    /**
     * Recalculate storage for all approved tenants.
     */
    public function calculateAll(): array
    {
        $results  = [];
        $tenants  = Tenant::where('status', 'approved')->get();

        foreach ($tenants as $tenant) {
            try {
                $stat      = $this->calculate($tenant);
                $results[] = [
                    'tenant' => $tenant->name,
                    'db'     => $stat->formatted_db_size,
                    'files'  => $stat->formatted_file_size,
                    'total'  => $stat->formatted_total_storage,
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'tenant' => $tenant->name,
                    'error'  => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    // ── Internals ─────────────────────────────────────────────────────────

    protected function getDatabaseSize(Tenant $tenant): int
    {
        try {
            return (int) $tenant->run(function () {
                $dbName = DB::connection('tenant')->getDatabaseName();

                $result = DB::connection('tenant')->selectOne("
                    SELECT SUM(data_length + index_length) AS size_bytes
                    FROM information_schema.tables
                    WHERE table_schema = ?
                ", [$dbName]);

                return $result?->size_bytes ?? 0;
            });
        } catch (\Throwable) {
            return 0;
        }
    }

    protected function getFileSize(Tenant $tenant): int
    {
        try {
            // Branding uploads are stored at storage/app/public/branding/{tenant_id}/
            $brandingPath = storage_path("app/public/branding/{$tenant->id}");

            if (! is_dir($brandingPath)) {
                return 0;
            }

            return (int) $this->directorySize($brandingPath);
        } catch (\Throwable) {
            return 0;
        }
    }

    protected function directorySize(string $path): int
    {
        $size = 0;

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }
}