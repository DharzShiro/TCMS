<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemRelease;
use App\Models\TenantUpdateLog;
use App\Models\TenantVersionStatus;
use App\Services\Updates\TenantVersionService;

class AdminUpdateController extends Controller
{
    public function __construct(private readonly TenantVersionService $versions) {}

    public function index()
    {
        $tenant = tenancy()->tenant;

        // Refresh status against latest release before showing
        $release = SystemRelease::latestActive();
        $status  = $this->versions->syncTenantStatus($tenant, $release);

        $logs = TenantUpdateLog::where('tenant_id', $tenant->id)
            ->with('release')
            ->latest()
            ->take(20)
            ->get();

        $releases = SystemRelease::active()->deployed()
            ->orderByDesc('published_at')
            ->take(5)
            ->get();

        return view('admin.update.index', compact('status', 'logs', 'releases', 'release'));
    }

    public function applyUpdate()
    {
        $tenant  = tenancy()->tenant;
        $release = SystemRelease::latestActive();

        if (! $release) {
            return back()->with('error', 'No active release available.');
        }

        $status = TenantVersionStatus::where('tenant_id', $tenant->id)->first();

        if ($status && $status->isUpdating()) {
            return back()->with('info', 'An update is already in progress. Please wait.');
        }

        if ($status && $status->isUpToDate()) {
            return back()->with('info', 'Your system is already up to date.');
        }

        try {
            $log = $this->versions->runUpdateNow($tenant, $release);
        } catch (\Throwable $e) {
            return back()->with('error', 'Update could not start: ' . $e->getMessage());
        }

        if ($log->status === 'completed') {
            return back()->with('success', "Successfully updated to v{$release->version}. All changes are now live.");
        }

        return back()->with('error', 'Update failed: ' . ($log->failure_reason ?? 'Unknown error. Check your update logs or contact support.'));
    }
}
