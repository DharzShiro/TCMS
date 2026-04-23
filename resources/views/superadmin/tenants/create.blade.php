@extends('layouts.app')

@section('title', 'Register New Tenant')

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

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--sa-text);
    }

    .form-input, .form-select {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid var(--sa-border);
        border-radius: 0.5rem;
        background: var(--sa-bg);
        color: var(--sa-text);
        font-size: 1rem;
        transition: border-color 0.3s;
    }

    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: var(--sa-accent);
    }

    .form-error {
        color: var(--sa-danger);
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .form-help {
        color: var(--sa-text-muted);
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
</style>

<div class="space-y-6 max-w-2xl">
    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('superadmin.tenants.index') }}"
           class="px-4 py-2 rounded-lg transition"
           style="background: rgba(0, 48, 135, 0.1); color: var(--sa-accent);">
            <i class="fas fa-arrow-left mr-2"></i> Back to Tenants
        </a>
        <h1 class="text-3xl font-bold" style="color: var(--sa-primary);">
            <i class="fas fa-plus-circle mr-2" style="color: var(--sa-accent);"></i> Register New Tenant
        </h1>
    </div>

    {{-- Form Card --}}
    <div class="rounded-2xl border-2 p-8" style="background: var(--sa-bg); border-color: var(--sa-border);">
        <p class="mb-6" style="color: var(--sa-text-muted);">
            Fill in the details below to register a new organization as a tenant. Once submitted, the application will be pending review.
        </p>

        {{-- Errors --}}
        @if($errors->any())
            <div class="rounded-xl border-2 p-4 mb-6" style="background: rgba(206, 17, 38, 0.05); border-color: var(--sa-danger);">
                <div style="color: var(--sa-danger);" class="font-semibold flex items-start gap-3">
                    <i class="fas fa-exclamation-circle mt-0.5"></i>
                    <div>
                        <p class="font-bold mb-2">Please fix the following errors:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Registration Form --}}
        <form action="{{ route('superadmin.tenants.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Organization Name --}}
            <div class="form-group">
                <label class="form-label">Organization Name *</label>
                <input type="text" name="name" class="form-input"
                       placeholder="e.g., Acme Training Center"
                       value="{{ old('name') }}"
                       required>
                @error('name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-help">
                    <i class="fas fa-info-circle mr-1"></i> The official name of your organization
                </div>
            </div>

            {{-- Admin Email --}}
            <div class="form-group">
                <label class="form-label">Admin Email Address *</label>
                <input type="email" name="admin_email" class="form-input"
                       placeholder="admin@example.com"
                       value="{{ old('admin_email') }}"
                       required>
                @error('admin_email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-help">
                    <i class="fas fa-info-circle mr-1"></i> The email address where login credentials will be sent
                </div>
            </div>

            {{-- Subdomain --}}
            <div class="form-group">
                <label class="form-label">Subdomain *</label>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="text" name="subdomain" class="form-input"
                           placeholder="acme-training"
                           value="{{ old('subdomain') }}"
                           style="margin-bottom: 0;"
                           required>
                    <span style="color: var(--sa-text-muted);">.tcm.com</span>
                </div>
                @error('subdomain')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-help">
                    <i class="fas fa-info-circle mr-1"></i> Use only letters, numbers, and hyphens (no spaces)
                </div>
            </div>

            {{-- Subscription Plan --}}
            <div class="form-group">
                <label class="form-label">Subscription Plan *</label>
                <select name="subscription" class="form-select" required>
                    <option value="">-- Select a Plan --</option>
                    <option value="basic" {{ old('subscription') === 'basic' ? 'selected' : '' }}>
                        Basic Plan (30 days)
                    </option>
                    <option value="standard" {{ old('subscription') === 'standard' ? 'selected' : '' }}>
                        Standard Plan (6 months)
                    </option>
                    <option value="premium" {{ old('subscription') === 'premium' ? 'selected' : '' }}>
                        Premium Plan (1 year)
                    </option>
                </select>
                @error('subscription')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-help">
                    <i class="fas fa-info-circle mr-1"></i> The initial plan can be upgraded later
                </div>
            </div>

            {{-- Info Box --}}
            <div class="rounded-xl border-2 p-4" style="background: rgba(0, 87, 184, 0.05); border-color: rgba(0, 87, 184, 0.2);">
                <p class="text-sm" style="color: var(--sa-text);">
                    <i class="fas fa-lightbulb mr-2" style="color: var(--sa-accent);"></i>
                    <strong>What happens next?</strong>
                    After registration, the tenant request will be pending review. You can approve, reject, or upgrade the tenant from the dashboard.
                </p>
            </div>

            {{-- Form Actions --}}
            <div class="flex gap-3 pt-6">
                <button type="submit" class="flex-1 px-6 py-3 rounded-lg font-medium text-white transition hover:shadow-lg"
                        style="background: var(--sa-accent);">
                    <i class="fas fa-check mr-2"></i> Register Tenant
                </button>
                <a href="{{ route('superadmin.tenants.index') }}"
                   class="flex-1 px-6 py-3 rounded-lg font-medium text-center transition border-2"
                   style="background: var(--sa-bg); border-color: var(--sa-border); color: var(--sa-text);">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
            </div>
        </form>
    </div>

    {{-- Help Section --}}
    <div class="rounded-2xl border-2 p-6" style="background: var(--sa-bg); border-color: var(--sa-border);">
        <h2 class="text-lg font-bold mb-4" style="color: var(--sa-primary);">
            <i class="fas fa-question-circle mr-2" style="color: var(--sa-accent);"></i> Need Help?
        </h2>
        <div class="space-y-4 text-sm" style="color: var(--sa-text-muted);">
            <div>
                <p class="font-semibold mb-1" style="color: var(--sa-text);">What is a Subdomain?</p>
                <p>A subdomain is a unique identifier for your organization. For example, if you enter "acme-training", your organization's URL will be acme-training.tcm.com</p>
            </div>
            <div>
                <p class="font-semibold mb-1" style="color: var(--sa-text);">Subscription Plans</p>
                <ul class="list-disc list-inside space-y-1">
                    <li><strong>Free Plan:</strong> Limited features, 30-day trial period</li>
                    <li><strong>Pro Plan:</strong> Full features, premium support, unlimited storage</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
