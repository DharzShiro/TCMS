@extends('layouts.app')

@section('title', 'Activity Logs')

@section('content')
<style>
    :root {
        --sa-primary: #003087;
        --sa-accent: #0057B8;
        --sa-success: #16a34a;
        --sa-warning: #b38a00;
        --sa-danger: #CE1126;
        --sa-border: #c5d8f5;
        --sa-text: #001a4d;
        --sa-text-muted: #5a7aaa;
        --sa-bg: #ffffff;
    }
    .dark {
        --sa-bg: #0a1628;
        --sa-border: #1e3a6b;
        --sa-text: #dde8ff;
        --sa-text-muted: #6b8abf;
    }

    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .badge-success { background: rgba(22, 163, 74, 0.1);  color: var(--sa-success); }
    .badge-warning { background: rgba(179, 138, 0, 0.1);  color: var(--sa-warning); }
    .badge-danger  { background: rgba(206, 17, 38, 0.1);  color: var(--sa-danger);  }
    .badge-blue    { background: rgba(0, 87, 184, 0.1);   color: var(--sa-accent);  }
    .badge-gray    { background: rgba(90, 122, 170, 0.1); color: var(--sa-text-muted); }
</style>

<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold" style="color: var(--sa-primary);">
                <i class="fas fa-shield-alt mr-2" style="color: var(--sa-accent);"></i> Activity Logs
            </h1>
            <p class="text-sm mt-1" style="color: var(--sa-text-muted);">
                Monitor login activity across all tenants
            </p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rounded-2xl border-2 p-5" style="background: var(--sa-bg); border-color: var(--sa-border);">
            <div class="text-xs font-bold uppercase tracking-widest" style="color: var(--sa-text-muted);">Total Logs</div>
            <div class="text-3xl font-black mt-1" style="color: var(--sa-text);">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="rounded-2xl border-2 p-5" style="background: var(--sa-bg); border-color: var(--sa-border);">
            <div class="text-xs font-bold uppercase tracking-widest" style="color: var(--sa-text-muted);">Today's Logins</div>
            <div class="text-3xl font-black mt-1" style="color: var(--sa-accent);">{{ $stats['today'] }}</div>
        </div>
        <div class="rounded-2xl border-2 p-5" style="background: var(--sa-bg); border-color: var(--sa-border);">
            <div class="text-xs font-bold uppercase tracking-widest" style="color: var(--sa-text-muted);">Failed Today</div>
            <div class="text-3xl font-black mt-1" style="color: var(--sa-danger);">{{ $stats['failed_today'] }}</div>
        </div>
        <div class="rounded-2xl border-2 p-5" style="background: var(--sa-bg); border-color: var(--sa-border);">
            <div class="text-xs font-bold uppercase tracking-widest" style="color: var(--sa-text-muted);">Unique IPs Today</div>
            <div class="text-3xl font-black mt-1" style="color: var(--sa-text);">{{ $stats['unique_ips'] }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-2xl border-2 p-5" style="background: var(--sa-bg); border-color: var(--sa-border);">
        <form method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">

                {{-- Search --}}
                <div class="lg:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search email, name, IP address..."
                        class="w-full rounded-lg px-3 py-2 text-sm border-2 outline-none transition"
                        style="border-color: var(--sa-border); color: var(--sa-text); background: var(--sa-bg);">
                </div>

                {{-- Tenant --}}
                <select name="tenant_id"
                    class="rounded-lg px-3 py-2 text-sm border-2 outline-none transition"
                    style="border-color: var(--sa-border); color: var(--sa-text); background: var(--sa-bg);">
                    <option value="">All Tenants</option>
                    @foreach ($tenants as $tenant)
                        <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>
                            {{ $tenant->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Action --}}
                <select name="action"
                    class="rounded-lg px-3 py-2 text-sm border-2 outline-none transition"
                    style="border-color: var(--sa-border); color: var(--sa-text); background: var(--sa-bg);">
                    <option value="">All Actions</option>
                    <option value="login_success" {{ request('action') === 'login_success' ? 'selected' : '' }}>Login Success</option>
                    <option value="login_failed"  {{ request('action') === 'login_failed'  ? 'selected' : '' }}>Login Failed</option>
                    <option value="logout"        {{ request('action') === 'logout'        ? 'selected' : '' }}>Logout</option>
                </select>

                {{-- Role --}}
                <select name="role"
                    class="rounded-lg px-3 py-2 text-sm border-2 outline-none transition"
                    style="border-color: var(--sa-border); color: var(--sa-text); background: var(--sa-bg);">
                    <option value="">All Roles</option>
                    <option value="admin"   {{ request('role') === 'admin'   ? 'selected' : '' }}>Admin</option>
                    <option value="trainer" {{ request('role') === 'trainer' ? 'selected' : '' }}>Trainer</option>
                    <option value="trainee" {{ request('role') === 'trainee' ? 'selected' : '' }}>Trainee</option>
                </select>

                {{-- Success/Failed --}}
                <select name="success"
                    class="rounded-lg px-3 py-2 text-sm border-2 outline-none transition"
                    style="border-color: var(--sa-border); color: var(--sa-text); background: var(--sa-bg);">
                    <option value="">Success & Failed</option>
                    <option value="1" {{ request('success') === '1' ? 'selected' : '' }}>Successful Only</option>
                    <option value="0" {{ request('success') === '0' ? 'selected' : '' }}>Failed Only</option>
                </select>

                {{-- Date From --}}
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="rounded-lg px-3 py-2 text-sm border-2 outline-none transition"
                    style="border-color: var(--sa-border); color: var(--sa-text); background: var(--sa-bg);">

                {{-- Date To --}}
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="rounded-lg px-3 py-2 text-sm border-2 outline-none transition"
                    style="border-color: var(--sa-border); color: var(--sa-text); background: var(--sa-bg);">

                {{-- Buttons --}}
                <div class="flex gap-2 lg:col-span-4">
                    <button type="submit"
                        class="px-6 py-2 rounded-lg font-medium text-white text-sm transition hover:shadow-lg"
                        style="background: var(--sa-accent);">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="{{ route('superadmin.activity-logs.index') }}"
                        class="px-6 py-2 rounded-lg font-medium text-sm transition"
                        style="background: rgba(0, 87, 184, 0.08); color: var(--sa-accent);">
                        <i class="fas fa-times mr-2"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="rounded-2xl border-2 overflow-hidden" style="background: var(--sa-bg); border-color: var(--sa-border);">
        @if ($logs->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead style="background: rgba(0, 48, 135, 0.05); border-bottom: 2px solid var(--sa-border);">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: var(--sa-text);">User</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: var(--sa-text);">Tenant</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: var(--sa-text);">Role</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: var(--sa-text);">Action</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: var(--sa-text);">IP Address</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: var(--sa-text);">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr style="border-bottom: 1px solid var(--sa-border);">

                                {{-- User --}}
                                <td class="px-6 py-4">
                                    <p class="font-semibold" style="color: var(--sa-text);">{{ $log->user_name ?? '—' }}</p>
                                    <p class="text-xs" style="color: var(--sa-text-muted);">{{ $log->user_email ?? 'Unknown' }}</p>
                                </td>

                                {{-- Tenant --}}
                                <td class="px-6 py-4">
                                    @if ($log->tenant_name)
                                        <code class="px-2 py-1 rounded text-xs" style="background: rgba(0, 48, 135, 0.1); color: var(--sa-accent);">
                                            {{ $log->tenant_name }}
                                        </code>
                                    @else
                                        <span style="color: var(--sa-text-muted);">—</span>
                                    @endif
                                </td>

                                {{-- Role --}}
                                <td class="px-6 py-4">
                                    @if ($log->role === 'admin')
                                        <span class="badge" style="background: rgba(109,40,217,0.1); color: #6d28d9;">
                                            <i class="fas fa-user-shield fa-xs"></i> Admin
                                        </span>
                                    @elseif ($log->role === 'trainer')
                                        <span class="badge badge-blue">
                                            <i class="fas fa-chalkboard-teacher fa-xs"></i> Trainer
                                        </span>
                                    @elseif ($log->role === 'trainee')
                                        <span class="badge badge-success">
                                            <i class="fas fa-user-graduate fa-xs"></i> Trainee
                                        </span>
                                    @else
                                        <span class="badge badge-gray">
                                            <i class="fas fa-user fa-xs"></i> Unknown
                                        </span>
                                    @endif
                                </td>

                                {{-- Action --}}
                                <td class="px-6 py-4">
                                    @if ($log->action === 'login_success')
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle fa-xs"></i> Logged In
                                        </span>
                                    @elseif ($log->action === 'login_failed')
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times-circle fa-xs"></i> Failed Login
                                        </span>
                                    @elseif ($log->action === 'logout')
                                        <span class="badge badge-blue">
                                            <i class="fas fa-sign-out-alt fa-xs"></i> Logged Out
                                        </span>
                                    @else
                                        <span class="badge badge-gray">{{ $log->action_label }}</span>
                                    @endif

                                    @if ($log->failure_reason)
                                        <p class="text-xs mt-1" style="color: var(--sa-danger);">{{ $log->failure_reason }}</p>
                                    @endif
                                </td>

                                {{-- IP Address --}}
                                <td class="px-6 py-4">
                                    <code class="px-2 py-1 rounded text-xs" style="background: rgba(0, 48, 135, 0.06); color: var(--sa-text-muted);">
                                        {{ $log->ip_address ?? '—' }}
                                    </code>
                                </td>

                                {{-- Time --}}
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium" style="color: var(--sa-text);">{{ $log->created_at->format('M d, Y') }}</p>
                                    <p class="text-xs" style="color: var(--sa-text-muted);">{{ $log->created_at->format('h:i A') }}</p>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($logs->hasPages())
                <div class="px-6 py-4" style="border-top: 1px solid var(--sa-border);">
                    {{ $logs->links() }}
                </div>
            @endif

        @else
            <div class="flex flex-col items-center justify-center py-12 px-6 text-center">
                <i class="fas fa-shield-alt text-5xl mb-4" style="color: var(--sa-text-muted); opacity: 0.3;"></i>
                <p style="color: var(--sa-text-muted);">No activity logs found.</p>
            </div>
        @endif
    </div>

</div>
@endsection