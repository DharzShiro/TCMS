<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Jobs\FetchGitHubReleasesJob;
use App\Models\SystemRelease;
use App\Models\Tenant;
use App\Models\TenantVersionStatus;
use App\Services\Updates\GitHubReleaseService;
use App\Services\Updates\TenantVersionService;
use Illuminate\Http\Request;

class SuperAdminReleaseController extends Controller
{
    public function __construct(
        private readonly GitHubReleaseService $github,
        private readonly TenantVersionService $versions,
    ) {}

    public function index()
    {
        $releases = SystemRelease::orderByDesc('published_at')->paginate(15);
        $summary  = $this->versions->getUpdateSummary();
        $latest   = SystemRelease::latest();

        return view('superadmin.releases.index', compact('releases', 'summary', 'latest'));
    }

    public function show(SystemRelease $release)
    {
        $tenantStatuses = TenantVersionStatus::with('tenant')
            ->whereHas('tenant', fn($q) => $q->where('status', 'approved'))
            ->orderBy('update_status')
            ->get();

        $logs = $release->tenantUpdateLogs()
            ->with('tenant')
            ->latest()
            ->take(50)
            ->get();

        return view('superadmin.releases.show', compact('release', 'tenantStatuses', 'logs'));
    }

    public function fetch()
    {
        FetchGitHubReleasesJob::dispatch()->onQueue('updates');

        return back()->with('success', 'GitHub sync job dispatched. Refresh in a moment.');
    }

    public function deploy(SystemRelease $release)
    {
        $release->update(['is_deployed' => true, 'is_active' => true]);

        // Mark all other releases inactive
        SystemRelease::where('id', '!=', $release->id)->update(['is_active' => false]);

        // Sync all tenant statuses against this new active release
        $this->versions->syncAllStatuses();

        return back()->with('success', "Release v{$release->version} marked as deployed. Tenant update badges refreshed.");
    }

    public function undeploy(SystemRelease $release)
    {
        $release->update(['is_deployed' => false, 'is_active' => false]);

        return back()->with('success', "Release v{$release->version} undeployed.");
    }

    public function syncTenants(Request $request)
    {
        $this->versions->syncAllStatuses();

        return back()->with('success', 'All tenant version statuses refreshed.');
    }

    public function pushUpdateToAll(SystemRelease $release)
    {
        $queued = $this->versions->queueAllPendingUpdates($release);

        return back()->with('success', "Queued migration jobs for {$queued} tenant(s) needing update.");
    }

    public function pushUpdateToTenant(Request $request, SystemRelease $release)
    {
        $tenant = Tenant::findOrFail($request->tenant_id);
        $this->versions->queueUpdate($tenant, $release, 'superadmin');

        return back()->with('success', "Update job queued for {$tenant->name}.");
    }
}
