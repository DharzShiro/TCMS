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
        $release = SystemRelease::latest();
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
        $release = SystemRelease::latest();

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

        $this->versions->queueUpdate($tenant, $release, 'tenant');

        return back()->with('success', 'Update queued. This may take a minute. The page will reflect progress shortly.');
    }
}
