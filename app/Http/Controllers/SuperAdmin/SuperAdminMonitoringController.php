<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantUsageStat;
use App\Services\TenantStorageService;
use Illuminate\Http\Request;

class SuperAdminMonitoringController extends Controller
{
    public function __construct(protected TenantStorageService $storage) {}

    public function index()
    {
        $tenants = Tenant::where('status', 'approved')
            ->with('usageStat')
            ->orderBy('name')
            ->get();

        // Platform aggregates
        $totals = [
            'db_bytes'         => TenantUsageStat::sum('db_size_bytes'),
            'file_bytes'       => TenantUsageStat::sum('file_size_bytes'),
            'bandwidth_today'  => TenantUsageStat::sum('bandwidth_bytes_today'),
            'bandwidth_total'  => TenantUsageStat::sum('bandwidth_bytes_total'),
        ];

        $totals['storage_bytes'] = $totals['db_bytes'] + $totals['file_bytes'];

        // Top consumers
        $topStorage   = TenantUsageStat::orderByDesc('db_size_bytes')->with('tenant')->take(5)->get();
        $topBandwidth = TenantUsageStat::orderByDesc('bandwidth_bytes_today')->with('tenant')->take(5)->get();

        return view('superadmin.monitoring.index', compact(
            'tenants', 'totals', 'topStorage', 'topBandwidth'
        ));
    }

    /**
     * Trigger a live recalculation for one tenant.
     */
    public function recalculate(Tenant $tenant)
    {
        try {
            $stat = $this->storage->calculate($tenant);

            return back()->with('success',
                "Storage recalculated for {$tenant->name}: " .
                "DB={$stat->formatted_db_size}, Files={$stat->formatted_file_size}"
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Recalculation failed: ' . $e->getMessage());
        }
    }

    /**
     * Recalculate all tenants (triggered manually from the UI).
     */
    public function recalculateAll()
    {
        $this->storage->calculateAll();
        return back()->with('success', 'Storage recalculated for all tenants.');
    }
}