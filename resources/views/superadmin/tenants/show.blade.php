@extends('layouts.app')

@section('title', $tenant->name)

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
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-success {
        background: rgba(22, 163, 74, 0.1);
        color: var(--sa-success);
    }

    .badge-warning {
        background: rgba(179, 138, 0, 0.1);
        color: var(--sa-warning);
    }

    .badge-danger {
        background: rgba(206, 17, 38, 0.1);
        color: var(--sa-danger);
    }

    .info-item {
        padding: 1rem;
        border-radius: 0.75rem;
        background: rgba(0, 48, 135, 0.05);
    }
</style>

<div class="space-y-6">
    {{-- Page Header with Back Button --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('superadmin.tenants.index') }}"
           class="px-4 py-2 rounded-lg transition"
           style="background: rgba(0, 48, 135, 0.1); color: var(--sa-accent);">
            <i class="fas fa-arrow-left mr-2"></i> Back to Tenants
        </a>
        <h1 class="text-3xl font-bold" style="color: var(--sa-primary);">{{ $tenant->name }}</h1>
        @if($tenant->status === 'approved')
            <span class="badge badge-success">
                <i class="fas fa-check-circle mr-1"></i> Approved
            </span>
        @elseif($tenant->status === 'pending')
            <span class="badge badge-warning">
                <i class="fas fa-hourglass-half mr-1"></i> Pending
            </span>
        @else
            <span class="badge badge-danger">
                <i class="fas fa-times-circle mr-1"></i> Rejected
            </span>
        @endif
    </div>

    {{-- Enable / Disable access --}}
    @if($tenant->is_active)
        <form action="{{ route('superadmin.tenants.disable', $tenant) }}" method="POST"
            onsubmit="return confirm('Disable access for {{ addslashes($tenant->name) }}? They will be immediately blocked from the system.')">
            @csrf @method('PATCH')
            <button type="submit"
                    class="w-full px-4 py-3 rounded-lg font-medium text-white transition"
                    style="background: var(--sa-warning);">
                <i class="fas fa-toggle-off mr-2"></i> Disable Tenant Access
            </button>
        </form>
    @else
        <form action="{{ route('superadmin.tenants.enable', $tenant) }}" method="POST">
            @csrf @method('PATCH')
            <button type="submit"
                    class="w-full px-4 py-3 rounded-lg font-medium text-white transition"
                    style="background: var(--sa-success);">
                <i class="fas fa-toggle-on mr-2"></i> Enable Tenant Access
            </button>
        </form>
    @endif

    {{-- Access state --}}
    @if($tenant->is_active)
        <div class="px-4 py-3 rounded-lg" style="background: rgba(22,163,74,0.08); border-left: 4px solid var(--sa-success);">
            <p style="color: var(--sa-success);" class="font-semibold text-sm"><i class="fas fa-toggle-on mr-1"></i> Access Enabled</p>
            <p style="color: var(--sa-text-muted);" class="text-xs mt-1">Tenant users can log in and use the system.</p>
        </div>
    @else
        <div class="px-4 py-3 rounded-lg" style="background: rgba(90,122,170,0.08); border-left: 4px solid var(--sa-text-muted);">
            <p style="color: var(--sa-text-muted);" class="font-semibold text-sm"><i class="fas fa-toggle-off mr-1"></i> Access Disabled</p>
            <p style="color: var(--sa-text-muted);" class="text-xs mt-1">All users in this tenant are blocked from logging in.</p>
        </div>
    @endif

    {{-- ─── Alerts ──────────────────────────────────────────────────────── --}}
    @if($errors->any())
        <div class="rounded-xl border-2 p-4" style="background: rgba(206, 17, 38, 0.05); border-color: var(--sa-danger);">
            <div style="color: var(--sa-danger);" class="font-semibold flex items-start gap-3">
                <i class="fas fa-exclamation-circle mt-0.5"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="rounded-xl border-2 p-4" style="background: rgba(22, 163, 74, 0.05); border-color: var(--sa-success);">
            <div style="color: var(--sa-success);" class="font-semibold flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border-2 p-4" style="background: rgba(206, 17, 38, 0.05); border-color: var(--sa-danger);">
            <div style="color: var(--sa-danger);" class="font-semibold flex items-center gap-3">
                <i class="fas fa-times-circle"></i> {{ session('error') }}
            </div>
        </div>
    @endif
    {{-- ─────────────────────────────────────────────────────────────────── --}}

    {{-- Tenant Details Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column - Main Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Organization Details --}}
            <div class="rounded-2xl border-2 p-6" style="background: var(--sa-bg); border-color: var(--sa-border);">
                <h2 class="text-lg font-bold mb-4" style="color: var(--sa-primary);">
                    <i class="fas fa-building mr-2" style="color: var(--sa-accent);"></i> Organization Details
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="info-item">
                        <p class="text-xs font-semibold mb-2" style="color: var(--sa-text-muted);">Organization Name</p>
                        <p style="color: var(--sa-text);" class="font-semibold">{{ $tenant->name }}</p>
                    </div>
                    <div class="info-item">
                        <p class="text-xs font-semibold mb-2" style="color: var(--sa-text-muted);">Admin Email</p>
                        <p style="color: var(--sa-text);" class="font-semibold">{{ $tenant->admin_email }}</p>
                    </div>
                    <div class="info-item">
                        <p class="text-xs font-semibold mb-2" style="color: var(--sa-text-muted);">Subdomain</p>
                        <p style="color: var(--sa-text);" class="font-semibold">{{ $tenant->subdomain }}.tcm.com</p>
                    </div>
                    <div class="info-item">
                        <p class="text-xs font-semibold mb-2" style="color: var(--sa-text-muted);">Current Status</p>
                        <p style="color: var(--sa-text);" class="font-semibold">{{ ucfirst($tenant->status) }}</p>
                    </div>
                </div>
            </div>

            {{-- Subscription Details --}}
            <div class="rounded-2xl border-2 p-6" style="background: var(--sa-bg); border-color: var(--sa-border);">
                <h2 class="text-lg font-bold mb-4" style="color: var(--sa-primary);">
                    <i class="fas fa-credit-card mr-2" style="color: var(--sa-accent);"></i> Subscription Details
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="info-item">
                        <p class="text-xs font-semibold mb-2" style="color: var(--sa-text-muted);">Plan Type</p>
                        <p style="color: var(--sa-text);" class="font-semibold text-lg">{{ ucfirst($tenant->subscription) }}</p>
                    </div>
                    <div class="info-item">
                        <p class="text-xs font-semibold mb-2" style="color: var(--sa-text-muted);">Expires At</p>
                        <p style="color: var(--sa-text);" class="font-semibold">
                            @if($tenant->expires_at)
                                {{ $tenant->expires_at->format('M d, Y') }}
                            @else
                                Not set
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Upgrade Form — only show for approved tenants --}}
                @if($tenant->status === 'approved')
                    <form action="{{ route('superadmin.tenants.upgrade', $tenant) }}" method="POST" class="mt-6 pt-6 border-t" style="border-color: var(--sa-border);">
                        @csrf
                        @method('PATCH')
                        <label class="text-sm font-semibold mb-3 block" style="color: var(--sa-text);">Upgrade Plan</label>
                        <div class="flex gap-3">
                            <select name="subscription" class="flex-1 px-4 py-2 rounded-lg border" style="background: var(--sa-bg); border-color: var(--sa-border); color: var(--sa-text);">
                                <option value="basic"    {{ $tenant->subscription === 'basic'    ? 'selected' : '' }}>Basic Plan (30 days)</option>
                                <option value="standard" {{ $tenant->subscription === 'standard' ? 'selected' : '' }}>Standard Plan (6 months)</option>
                                <option value="premium"  {{ $tenant->subscription === 'premium'  ? 'selected' : '' }}>Premium Plan (1 year)</option>
                            </select>
                            <button type="submit" class="px-6 py-2 rounded-lg font-medium text-white transition"
                                    style="background: var(--sa-accent);">
                                Upgrade
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            {{-- Timestamps --}}
            <div class="rounded-2xl border-2 p-6" style="background: var(--sa-bg); border-color: var(--sa-border);">
                <h2 class="text-lg font-bold mb-4" style="color: var(--sa-primary);">
                    <i class="fas fa-clock mr-2" style="color: var(--sa-accent);"></i> Timeline
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="info-item">
                        <p class="text-xs font-semibold mb-2" style="color: var(--sa-text-muted);">Created</p>
                        <p style="color: var(--sa-text);">{{ $tenant->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="info-item">
                        <p class="text-xs font-semibold mb-2" style="color: var(--sa-text-muted);">Last Updated</p>
                        <p style="color: var(--sa-text);">{{ $tenant->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column - Actions --}}
        <div class="space-y-6">
            <div class="rounded-2xl border-2 p-6" style="background: var(--sa-bg); border-color: var(--sa-border);">
                <h2 class="text-lg font-bold mb-4" style="color: var(--sa-primary);">
                    <i class="fas fa-cog mr-2" style="color: var(--sa-accent);"></i> Actions
                </h2>

                <div class="space-y-3">

                    {{-- PENDING: show Approve + Reject --}}
                    @if($tenant->status === 'pending')
                        <form action="{{ route('superadmin.tenants.approve', $tenant) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-3 rounded-lg font-medium text-white transition"
                                    style="background: var(--sa-success);">
                                <i class="fas fa-check-circle mr-2"></i> Approve Tenant
                            </button>
                        </form>

                        <form action="{{ route('superadmin.tenants.reject', $tenant) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-3 rounded-lg font-medium text-white transition"
                                    style="background: var(--sa-danger);"
                                    onclick="return confirm('Are you sure you want to reject this tenant?');">
                                <i class="fas fa-times-circle mr-2"></i> Reject Tenant
                            </button>
                        </form>
                    @endif

                    {{-- REJECTED: show Re-approve only --}}
                    @if($tenant->status === 'rejected')
                        <form action="{{ route('superadmin.tenants.approve', $tenant) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-3 rounded-lg font-medium text-white transition"
                                    style="background: var(--sa-success);">
                                <i class="fas fa-check-circle mr-2"></i> Re-approve Tenant
                            </button>
                        </form>
                    @endif

                    {{-- Delete always visible --}}
                    <form action="{{ route('superadmin.tenants.destroy', $tenant) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full px-4 py-3 rounded-lg font-medium transition border-2"
                                style="background: rgba(206, 17, 38, 0.1); border-color: var(--sa-danger); color: var(--sa-danger);"
                                onclick="return confirm('Are you sure you want to delete this tenant? This action cannot be undone.');">
                            <i class="fas fa-trash mr-2"></i> Delete Tenant
                        </button>
                    </form>
                </div>
            </div>

            {{-- Status Info Card --}}
            <div class="rounded-2xl border-2 p-6" style="background: var(--sa-bg); border-color: var(--sa-border);">
                <h2 class="text-lg font-bold mb-4" style="color: var(--sa-primary);">
                    <i class="fas fa-info-circle mr-2" style="color: var(--sa-accent);"></i> Status Info
                </h2>
                <div class="space-y-3">
                    @if($tenant->status === 'approved')
                        <div class="px-4 py-3 rounded-lg" style="background: rgba(22, 163, 74, 0.1); border-left: 4px solid var(--sa-success);">
                            <p style="color: var(--sa-success);" class="font-semibold text-sm">✓ Active</p>
                            <p style="color: var(--sa-text-muted);" class="text-xs mt-1">This tenant is active and operational.</p>
                        </div>
                    @elseif($tenant->status === 'pending')
                        <div class="px-4 py-3 rounded-lg" style="background: rgba(179, 138, 0, 0.1); border-left: 4px solid var(--sa-warning);">
                            <p style="color: var(--sa-warning);" class="font-semibold text-sm">⏳ Under Review</p>
                            <p style="color: var(--sa-text-muted);" class="text-xs mt-1">Review and approve or reject this application.</p>
                        </div>
                    @else
                        <div class="px-4 py-3 rounded-lg" style="background: rgba(206, 17, 38, 0.1); border-left: 4px solid var(--sa-danger);">
                            <p style="color: var(--sa-danger);" class="font-semibold text-sm">✗ Rejected</p>
                            <p style="color: var(--sa-text-muted);" class="text-xs mt-1">This tenant registration was rejected.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection