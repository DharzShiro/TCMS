<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Jobs\DeployApplicationJob;
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
        $releases = SystemRelease::orderByRaw('
            CAST(SUBSTRING_INDEX(version, ".", 1) AS UNSIGNED) DESC,
            CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(version, ".", 2), ".", -1) AS UNSIGNED) DESC,
            CAST(SUBSTRING_INDEX(version, ".", -1) AS UNSIGNED) DESC
        ')->paginate(15);
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
            $stats = $this->github->syncToDatabase();
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => 'GitHub fetch failed: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'GitHub fetch failed: ' . $e->getMessage());
        }

        $this->versions->syncAllStatuses();

        $message = $this->buildSyncMessage($stats);
        $status  = $stats['synced'] > 0 ? 'success' : ($stats['fetched'] === 0 ? 'warning' : 'warning');

        if ($request->expectsJson()) {
            return response()->json(['status' => $status, 'message' => $message]);
        }

        return $stats['synced'] > 0
            ? back()->with('success', $message)
            : back()->with('warning', $message);
    }

    public function deploy(SystemRelease $release)
    {
        DeployApplicationJob::dispatch($release)->onQueue('updates');

        return back()->with('success', "Deployment of v{$release->version} started. Code, UI, and tenant migrations will be applied automatically in the background.");
    }

    private function buildSyncMessage(array $stats): string
    {
        if ($stats['fetched'] === 0) {
            return 'GitHub returned no releases. Make sure you have published releases (not just tags) at github.com → Releases.';
        }

        if ($stats['synced'] > 0) {
            $msg = "Synced {$stats['synced']} of {$stats['fetched']} release(s) from GitHub.";
            if ($stats['skipped_prerelease'] > 0) {
                $msg .= " ({$stats['skipped_prerelease']} pre-release(s) skipped — enable GITHUB_INCLUDE_PRERELEASES=true to include them)";
            }
            return $msg;
        }

        // synced === 0 but fetched > 0
        $parts = [];
        if ($stats['skipped_prerelease'] > 0) {
            $parts[] = "{$stats['skipped_prerelease']} skipped because they are marked as Pre-release on GitHub";
        }
        if ($stats['skipped_invalid_tag'] > 0) {
            $parts[] = "{$stats['skipped_invalid_tag']} skipped due to invalid tag format (expected v1.2.3)";
        }
        $detail = $parts ? implode('; ', $parts) . '.' : 'Unknown filter reason.';
        return "Fetched {$stats['fetched']} release(s) from GitHub but none were saved — {$detail}";
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
