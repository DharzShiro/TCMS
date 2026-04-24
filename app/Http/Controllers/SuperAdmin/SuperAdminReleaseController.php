<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
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
        $latest   = SystemRelease::latestActive();

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

    public function fetch(Request $request)
    {
        if (! $this->github->isConfigured()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'GitHub is not configured. Set GITHUB_OWNER and GITHUB_REPO in .env',
                ], 422);
            }
            return back()->with('error', 'GitHub is not configured.');
        }

        try {
            $synced = $this->github->syncToDatabase();
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'GitHub fetch failed: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'GitHub fetch failed: ' . $e->getMessage());
        }

        // Refresh all tenant version badges against the newly synced active release
        $this->versions->syncAllStatuses();

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Synced {$synced} release(s) from GitHub.",
            ]);
        }

        return back()->with('success', "Synced {$synced} release(s) from GitHub.");
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
