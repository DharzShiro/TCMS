<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Tenant;
use Illuminate\Http\Request;

class SuperAdminActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::latest();

        // Filter by tenant
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by success/failure
        if ($request->filled('success')) {
            $query->where('success', $request->success === '1');
        }

        // Search by email or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_email', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs    = $query->paginate(20)->withQueryString();
        $tenants = Tenant::where('status', 'approved')->orderBy('name')->get();

        // Summary stats
        $stats = [
            'total'          => ActivityLog::count(),
            'today'          => ActivityLog::whereDate('created_at', today())->count(),
            'failed_today'   => ActivityLog::whereDate('created_at', today())->where('success', false)->count(),
            'unique_ips'     => ActivityLog::whereDate('created_at', today())->distinct('ip_address')->count(),
        ];

        return view('superadmin.activity-logs.index', compact('logs', 'tenants', 'stats'));
    }
}