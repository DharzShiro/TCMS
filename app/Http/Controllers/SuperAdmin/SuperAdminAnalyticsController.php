<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use App\Models\RenewalRequest;
use App\Models\SubscriptionPlan;
use App\Models\TenantUsageStat;

class SuperAdminAnalyticsController extends Controller
{
    public function index()
    {
        // ── Tenant Overview ────────────────────────────────────────────────
        $totalTenants    = Tenant::count();
        $approvedTenants = Tenant::where('status', 'approved')->count();
        $pendingTenants  = Tenant::where('status', 'pending')->count();
        $rejectedTenants = Tenant::where('status', 'rejected')->count();

        // ── Subscription Breakdown ─────────────────────────────────────────
        $subscriptionBreakdown = Tenant::where('status', 'approved')
            ->selectRaw('subscription, COUNT(*) as count')
            ->groupBy('subscription')
            ->pluck('count', 'subscription')
            ->toArray();

        $subscriptionBreakdown = array_merge(
            ['basic' => 0, 'standard' => 0, 'premium' => 0],
            $subscriptionBreakdown
        );

        // ── Expiring Soon (next 7 days) ────────────────────────────────────
        $expiringSoon = Tenant::where('status', 'approved')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(7)])
            ->get();

        // ── Already Expired ────────────────────────────────────────────────
        $expiredTenants = Tenant::where('status', 'approved')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->count();

        // ── Monthly Registrations (last 6 months) ──────────────────────────
        $monthlyRegistrations = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyRegistrations[] = [
                'label' => $month->format('M Y'),
                'count' => Tenant::whereYear('created_at', $month->year)
                               ->whereMonth('created_at', $month->month)
                               ->count(),
            ];
        }

        // ── Per-Tenant Stats (cross-DB) ────────────────────────────────────
        // Only approved tenants with a usable DB connection
        $tenantStats = [];
        $approvedList = Tenant::where('status', 'approved')->get();

        foreach ($approvedList as $tenant) {
            try {
                $stats = $tenant->run(function () {
                    return [
                        'trainers'    => DB::table('users')->where('role', 'trainer')->count(),
                        'trainees'    => DB::table('users')->where('role', 'trainee')->count(),
                        'courses'     => DB::table('courses')->count(),
                        'enrollments' => DB::table('enrollments')->count(),
                        'assessments' => DB::table('assessments')->count(),
                        'certificates'=> DB::table('certificates')->count(),
                    ];
                });

                $tenantStats[] = array_merge(['tenant' => $tenant], $stats);
            } catch (\Throwable) {
                // Tenant DB not yet provisioned – skip gracefully
                $tenantStats[] = [
                    'tenant'       => $tenant,
                    'trainers'     => 0,
                    'trainees'     => 0,
                    'courses'      => 0,
                    'enrollments'  => 0,
                    'assessments'  => 0,
                    'certificates' => 0,
                ];
            }
        }

        // ── Platform Aggregates (sum across all tenants) ───────────────────
        $platformTotals = [
            'trainers'     => array_sum(array_column($tenantStats, 'trainers')),
            'trainees'     => array_sum(array_column($tenantStats, 'trainees')),
            'courses'      => array_sum(array_column($tenantStats, 'courses')),
            'enrollments'  => array_sum(array_column($tenantStats, 'enrollments')),
            'assessments'  => array_sum(array_column($tenantStats, 'assessments')),
            'certificates' => array_sum(array_column($tenantStats, 'certificates')),
        ];

        // ── Renewal Request Stats ──────────────────────────────────────────────
        $renewalStats = RenewalRequest::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $renewalStats = array_merge(
            ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'cancelled_by_upgrade' => 0],
            $renewalStats
        );

        $pendingRenewals = RenewalRequest::with('tenant')
            ->where('status', 'pending')
            ->latest()
            ->get();

        // ── Subscription Plans (all, including custom) ─────────────────────────
        $allPlans = SubscriptionPlan::orderBy('sort_order')->get();

        // Count how many approved tenants are on each plan slug
        $tenantCountByPlan = Tenant::where('status', 'approved')
            ->selectRaw('subscription, COUNT(*) as count')
            ->groupBy('subscription')
            ->pluck('count', 'subscription')
            ->toArray();

        // ── Storage Aggregates ─────────────────────────────────────────────────
        $storageAggregates = [
            'db_bytes'    => TenantUsageStat::sum('db_size_bytes'),
            'file_bytes'  => TenantUsageStat::sum('file_size_bytes'),
        ];
        $storageAggregates['total_bytes'] = $storageAggregates['db_bytes'] + $storageAggregates['file_bytes'];

        $topDbStorage = TenantUsageStat::with('tenant')
            ->orderByDesc('db_size_bytes')
            ->take(8)
            ->get();
        $maxDbBytes = $topDbStorage->max('db_size_bytes') ?: 1;

        return view('superadmin.analytics.index', compact(
            'totalTenants',
            'approvedTenants',
            'pendingTenants',
            'rejectedTenants',
            'subscriptionBreakdown',
            'expiringSoon',
            'expiredTenants',
            'monthlyRegistrations',
            'tenantStats',
            'platformTotals',
            'renewalStats', 'pendingRenewals',
            'allPlans', 'tenantCountByPlan',
            'storageAggregates', 'topDbStorage', 'maxDbBytes',
        ));
    }
}