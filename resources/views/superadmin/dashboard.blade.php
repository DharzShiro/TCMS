@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

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
</style>

<div class="space-y-6">
    {{-- Page Header --}}
    <div>
        <h1 class="text-3xl font-bold" style="color: var(--sa-primary);">
            <i class="fas fa-crown mr-2" style="color: var(--sa-danger);"></i> Super Admin Dashboard
        </h1>
        <p class="text-sm mt-1" style="color: var(--sa-text-muted);">
            Manage all tenants and system-wide settings. Welcome back, <span class="font-semibold" style="color: var(--sa-accent);">{{ Auth::user()->name }}</span>!
        </p>
    </div>

    {{-- Stats Cards Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
        {{-- Total Tenants Card --}}
        <a href="{{ route('superadmin.tenants.index') }}"
           class="rounded-2xl border-2 p-6 transition hover:shadow-lg hover:-translate-y-1 cursor-pointer"
           style="background: var(--sa-bg); border-color: var(--sa-border);">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold" style="color: var(--sa-text-muted);">Total Tenants</h3>
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: rgba(0, 87, 184, 0.1); color: var(--sa-accent);">
                    <i class="fas fa-layer-group text-lg"></i>
                </div>
            </div>
            <div class="text-3xl font-bold" style="color: var(--sa-primary);">{{ $tenants->count() }}</div>
            <p class="text-xs mt-2" style="color: var(--sa-text-muted);">View and manage tenants</p>
        </a>

        {{-- Approved Tenants Card --}}
        <div class="rounded-2xl border-2 p-6" style="background: var(--sa-bg); border-color: var(--sa-border);">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold" style="color: var(--sa-text-muted);">Approved</h3>
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: rgba(22, 163, 74, 0.1); color: var(--sa-success);">
                    <i class="fas fa-check-circle text-lg"></i>
                </div>
            </div>
            <div class="text-3xl font-bold" style="color: var(--sa-success);">{{ $approvedTenants->count() }}</div>
            <p class="text-xs mt-2" style="color: var(--sa-text-muted);">Active tenants</p>
        </div>

        {{-- Pending Tenants Card --}}
        <div class="rounded-2xl border-2 p-6" style="background: var(--sa-bg); border-color: var(--sa-border);">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold" style="color: var(--sa-text-muted);">Pending</h3>
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: rgba(179, 138, 0, 0.1); color: var(--sa-warning);">
                    <i class="fas fa-hourglass-half text-lg"></i>
                </div>
            </div>
            <div class="text-3xl font-bold" style="color: var(--sa-warning);">{{ $pendingTenants->count() }}</div>
            <p class="text-xs mt-2" style="color: var(--sa-text-muted);">Awaiting approval</p>
        </div>

        {{-- Rejected Tenants Card --}}
        <div class="rounded-2xl border-2 p-6" style="background: var(--sa-bg); border-color: var(--sa-border);">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold" style="color: var(--sa-text-muted);">Rejected</h3>
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: rgba(206, 17, 38, 0.1); color: var(--sa-danger);">
                    <i class="fas fa-times-circle text-lg"></i>
                </div>
            </div>
            <div class="text-3xl font-bold" style="color: var(--sa-danger);">{{ $rejectedTenants->count() }}</div>
            <p class="text-xs mt-2" style="color: var(--sa-text-muted);">Rejected requests</p>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div style="background: var(--sa-bg); border: 2px solid var(--sa-border);" class="rounded-2xl p-6">
        <h2 class="text-lg font-bold mb-4" style="color: var(--sa-primary);">
            <i class="fas fa-bolt mr-2" style="color: var(--sa-accent);"></i> Quick Actions
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <a href="{{ route('superadmin.tenants.create') }}"
               class="px-4 py-3 rounded-lg font-medium text-white text-center transition hover:shadow-lg"
               style="background: var(--sa-accent);">
                <i class="fas fa-plus mr-2"></i> Register New Tenant
            </a>
            <a href="{{ route('superadmin.tenants.index') }}"
               class="px-4 py-3 rounded-lg font-medium text-center transition border-2"
               style="background: var(--sa-bg); border-color: var(--sa-accent); color: var(--sa-accent);">
                <i class="fas fa-list mr-2"></i> Manage All Tenants
            </a>
            <a href="{{ route('superadmin.analytics.index') }}"
            class="px-4 py-3 rounded-lg font-medium text-center transition border-2"
            style="background: var(--sa-bg); border-color: var(--sa-accent); color: var(--sa-accent);">
                <i class="fas fa-chart-line mr-2"></i> Platform Analytics
            </a>
            <a href="{{ route('superadmin.monitoring.index') }}"
            class="px-4 py-3 rounded-lg font-medium text-center transition border-2"
            style="background: var(--sa-bg); border-color: var(--sa-accent); color: var(--sa-accent);">
                <i class="fas fa-server mr-2"></i> Tenant Monitoring
            </a>
        </div>
    </div>

    {{-- Recent Pending Tenants --}}
    @if($pendingTenants->count() > 0)
        <div style="background: var(--sa-bg); border: 2px solid var(--sa-border);" class="rounded-2xl p-6">
            <h2 class="text-lg font-bold mb-4" style="color: var(--sa-primary);">
                <i class="fas fa-inbox mr-2" style="color: var(--sa-warning);"></i> Pending Approvals
            </h2>
            <div class="space-y-3">
                @foreach($pendingTenants->take(5) as $tenant)
                    <div class="flex items-center justify-between p-4 rounded-xl" style="background: rgba(179, 138, 0, 0.05); border-left: 4px solid var(--sa-warning);">
                        <div>
                            <p class="font-semibold" style="color: var(--sa-text);">{{ $tenant->name }}</p>
                            <p class="text-sm" style="color: var(--sa-text-muted);">{{ $tenant->admin_email }}</p>
                        </div>
                        <a href="{{ route('superadmin.tenants.show', $tenant) }}"
                           class="px-4 py-2 rounded-lg text-white text-sm transition"
                           style="background: var(--sa-accent);">
                            Review
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection