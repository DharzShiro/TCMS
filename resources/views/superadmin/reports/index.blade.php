@extends('layouts.app')

@section('title', 'Platform Reports')

@section('content')

<style>
    :root {
        --sa-primary: #003087;
        --sa-accent:  #0057B8;
        --sa-success: #16a34a;
        --sa-warning: #b38a00;
        --sa-danger:  #CE1126;
        --sa-gold:    #F5C518;
        --sa-border:  #c5d8f5;
        --sa-text:    #001a4d;
        --sa-muted:   #5a7aaa;
        --sa-bg:      #ffffff;
        --sa-surface: #f4f8ff;
    }
    .dark {
        --sa-bg:      #0a1628;
        --sa-surface: #0d1f3c;
        --sa-border:  #1e3a6b;
        --sa-text:    #dde8ff;
        --sa-muted:   #6b8abf;
    }

    /* ── Report Cards ── */
    .report-card {
        border-radius: 18px;
        border: 2px solid var(--sa-border);
        background: var(--sa-bg);
        overflow: hidden;
        transition: box-shadow .18s, transform .18s;
    }
    .report-card:hover { box-shadow: 0 8px 30px rgba(0,48,135,.10); transform: translateY(-2px); }

    .report-card-header {
        padding: 20px 24px 16px;
        border-bottom: 2px solid var(--sa-border);
        background: var(--sa-surface);
        display: flex; align-items: flex-start; gap: 14px;
    }

    .report-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }

    .report-card-body { padding: 20px 24px; }

    /* ── Export Button Group ── */
    .export-group {
        display: flex; gap: 8px; flex-wrap: wrap; margin-top: 16px;
    }

    .export-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 9px 16px; border-radius: 10px;
        font-size: 12px; font-weight: 700; border: none; cursor: pointer;
        font-family: inherit; text-decoration: none; transition: all .15s;
        letter-spacing: .2px;
    }
    .export-btn:hover { transform: translateY(-1px); }

    .btn-excel {
        background: rgba(21,128,61,.10);
        color: #166534;
        border: 1.5px solid rgba(21,128,61,.25);
    }
    .btn-excel:hover { background: rgba(21,128,61,.18); }

    .btn-pdf {
        background: rgba(206,17,38,.10);
        color: var(--sa-danger);
        border: 1.5px solid rgba(206,17,38,.25);
    }
    .btn-pdf:hover { background: rgba(206,17,38,.18); }

    .btn-csv {
        background: rgba(90,122,170,.10);
        color: var(--sa-muted);
        border: 1.5px solid rgba(90,122,170,.25);
    }
    .btn-csv:hover { background: rgba(90,122,170,.18); }

    /* ── Stats pills ── */
    .stat-pill {
        display: flex; flex-direction: column; align-items: center;
        padding: 14px 18px; border-radius: 12px;
        background: var(--sa-surface); border: 1.5px solid var(--sa-border);
        gap: 3px; flex: 1; min-width: 80px;
    }
    .stat-pill-val { font-size: 22px; font-weight: 800; color: var(--sa-primary); line-height: 1; }
    .stat-pill-lbl { font-size: 10px; font-weight: 600; color: var(--sa-muted); text-transform: uppercase; letter-spacing: .5px; }

    /* ── Mini bar chart ── */
    .mini-bar-group { display: flex; flex-direction: column; gap: 8px; margin-top: 12px; }
    .mini-bar-row   { display: flex; align-items: center; gap: 8px; }
    .mini-bar-label { width: 52px; font-size: 10px; font-weight: 600; color: var(--sa-muted); text-align: right; flex-shrink: 0; }
    .mini-bar-track { flex: 1; height: 16px; border-radius: 4px; background: var(--sa-surface); overflow: hidden; }
    .mini-bar-fill  { height: 100%; border-radius: 4px; display: flex; align-items: center; justify-content: flex-end; padding-right: 6px; }
    .mini-bar-val   { font-size: 10px; font-weight: 700; color: #fff; }

    /* ── Sub-filter row ── */
    .filter-row {
        display: flex; align-items: center; gap: 8px;
        padding: 12px 16px; border-radius: 10px;
        background: var(--sa-surface); border: 1.5px solid var(--sa-border);
        margin-bottom: 12px; flex-wrap: wrap;
    }
    .filter-row label { font-size: 12px; font-weight: 600; color: var(--sa-muted); }
    .filter-row select {
        font-size: 12px; font-weight: 600; color: var(--sa-text);
        background: var(--sa-bg); border: 1.5px solid var(--sa-border);
        border-radius: 8px; padding: 5px 10px; outline: none; font-family: inherit;
    }
    .filter-row select:focus { border-color: var(--sa-accent); }

    /* ── Plan breakdown ── */
    .plan-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 0; border-bottom: 1px solid var(--sa-border); font-size: 13px;
    }
    .plan-row:last-child { border-bottom: none; }

    .plan-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
</style>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- Page Header                                                    --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div class="mb-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold" style="color:var(--sa-primary);">
                <i class="fas fa-file-chart-column mr-2" style="color:var(--sa-accent);"></i>
                Platform Reports
            </h1>
            <p class="text-sm mt-1" style="color:var(--sa-muted);">
                Generate and export consolidated reports across all tenants
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('superadmin.analytics.index') }}"
               class="text-sm px-4 py-2 rounded-lg font-semibold"
               style="background:rgba(0,48,135,.08);color:var(--sa-accent);">
                <i class="fas fa-chart-line mr-1"></i> Analytics
            </a>
            <a href="{{ route('superadmin.dashboard') }}"
               class="text-sm px-4 py-2 rounded-lg font-semibold"
               style="background:rgba(0,48,135,.08);color:var(--sa-accent);">
                <i class="fas fa-arrow-left mr-1"></i> Dashboard
            </a>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- Quick Stats Row                                                --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div class="flex flex-wrap gap-3 mb-6">
    <div class="stat-pill">
        <span class="stat-pill-val">{{ $totalTenants }}</span>
        <span class="stat-pill-lbl">Total</span>
    </div>
    <div class="stat-pill">
        <span class="stat-pill-val" style="color:var(--sa-success);">{{ $approvedTenants }}</span>
        <span class="stat-pill-lbl">Approved</span>
    </div>
    <div class="stat-pill">
        <span class="stat-pill-val" style="color:var(--sa-warning);">{{ $pendingTenants }}</span>
        <span class="stat-pill-lbl">Pending</span>
    </div>
    <div class="stat-pill">
        <span class="stat-pill-val" style="color:var(--sa-danger);">{{ $rejectedTenants }}</span>
        <span class="stat-pill-lbl">Rejected</span>
    </div>
    @if($expiringSoon > 0)
    <div class="stat-pill" style="border-color:rgba(179,138,0,.35);background:rgba(179,138,0,.06);">
        <span class="stat-pill-val" style="color:var(--sa-warning);">{{ $expiringSoon }}</span>
        <span class="stat-pill-lbl" style="color:var(--sa-warning);">Expiring Soon</span>
    </div>
    @endif
    @if($expiredCount > 0)
    <div class="stat-pill" style="border-color:rgba(206,17,38,.25);background:rgba(206,17,38,.05);">
        <span class="stat-pill-val" style="color:var(--sa-danger);">{{ $expiredCount }}</span>
        <span class="stat-pill-lbl" style="color:var(--sa-danger);">Expired</span>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- Notice                                                         --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div class="flex items-start gap-3 mb-6 px-4 py-3 rounded-xl"
     style="background:rgba(0,87,184,.06);border:1.5px solid rgba(0,87,184,.18);">
    <i class="fas fa-info-circle mt-0.5" style="color:var(--sa-accent);flex-shrink:0;"></i>
    <p class="text-sm" style="color:var(--sa-text);">
        All reports are generated at export time and reflect the current state of the platform.
        <strong>Excel</strong> and <strong>PDF</strong> formats include full TESDA branding.
        <strong>CSV</strong> is ideal for further processing in spreadsheet tools.
    </p>
</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- Report Cards Grid                                              --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">

    {{-- ── 1. Tenant Overview ───────────────────────────────────── --}}
    <div class="report-card">
        <div class="report-card-header">
            <div class="report-icon" style="background:rgba(0,87,184,.10);color:var(--sa-accent);">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <div class="font-bold text-base" style="color:var(--sa-primary);">Tenant Overview</div>
                <div class="text-xs mt-0.5" style="color:var(--sa-muted);">
                    All registered tenants — name, email, subdomain, plan, status, expiry
                </div>
            </div>
        </div>
        <div class="report-card-body">
            <p class="text-sm" style="color:var(--sa-muted);">
                A complete list of every tenant in the system, including their subscription plan and current approval status.
                <span class="font-semibold" style="color:var(--sa-text);">{{ $totalTenants }} records</span> total.
            </p>
            <div class="export-group">
                <a href="{{ route('superadmin.reports.tenants', ['format' => 'excel']) }}"
                   class="export-btn btn-excel">
                    <i class="fas fa-file-excel"></i> Excel (.xlsx)
                </a>
                <a href="{{ route('superadmin.reports.tenants', ['format' => 'pdf']) }}"
                   class="export-btn btn-pdf">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <a href="{{ route('superadmin.reports.tenants', ['format' => 'csv']) }}"
                   class="export-btn btn-csv">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
            </div>
        </div>
    </div>

    {{-- ── 2. Subscription Status ───────────────────────────────── --}}
    <div class="report-card">
        <div class="report-card-header">
            <div class="report-icon" style="background:rgba(245,197,24,.12);color:#a07800;">
                <i class="fas fa-credit-card"></i>
            </div>
            <div>
                <div class="font-bold text-base" style="color:var(--sa-primary);">Subscription Status</div>
                <div class="text-xs mt-0.5" style="color:var(--sa-muted);">
                    Approved tenants — plan, expiry date, days remaining, active/expired
                </div>
            </div>
        </div>
        <div class="report-card-body">

            {{-- ── Dynamic plan breakdown ── --}}
            <div class="mb-3">
                @forelse($subscriptionBreakdown as $slug => $info)
                    <div class="plan-row">
                        <div class="flex items-center gap-2">
                            <div class="plan-dot" style="background:{{ $info['color'] }};"></div>
                            <span style="color:var(--sa-text);font-weight:600;">
                                @if($info['icon'])
                                    {{ $info['icon'] }}
                                @endif
                                {{ $info['name'] }}
                            </span>
                        </div>
                        <span class="font-bold" style="color:var(--sa-primary);">{{ $info['count'] }}</span>
                    </div>
                @empty
                    <p class="text-sm" style="color:var(--sa-muted);">No approved tenants yet.</p>
                @endforelse
            </div>

            <div class="export-group">
                <a href="{{ route('superadmin.reports.subscriptions', ['format' => 'excel']) }}"
                   class="export-btn btn-excel">
                    <i class="fas fa-file-excel"></i> Excel (.xlsx)
                </a>
                <a href="{{ route('superadmin.reports.subscriptions', ['format' => 'pdf']) }}"
                   class="export-btn btn-pdf">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <a href="{{ route('superadmin.reports.subscriptions', ['format' => 'csv']) }}"
                   class="export-btn btn-csv">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
            </div>
        </div>
    </div>

    {{-- ── 3. Tenant Activity ────────────────────────────────────── --}}
    <div class="report-card">
        <div class="report-card-header">
            <div class="report-icon" style="background:rgba(22,163,74,.10);color:var(--sa-success);">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div>
                <div class="font-bold text-base" style="color:var(--sa-primary);">Tenant Activity</div>
                <div class="text-xs mt-0.5" style="color:var(--sa-muted);">
                    Per-tenant stats — trainers, trainees, courses, enrollments, assessments, certificates
                </div>
            </div>
        </div>
        <div class="report-card-body">
            <p class="text-sm" style="color:var(--sa-muted);">
                Cross-database summary of activity for every approved tenant. Includes all key metrics in a single exportable table.
            </p>
            <div class="flex items-center gap-2 mt-2 mb-1 px-3 py-2 rounded-lg text-xs"
                 style="background:rgba(179,138,0,.06);border:1px solid rgba(179,138,0,.2);color:var(--sa-warning);">
                <i class="fas fa-clock"></i>
                This report queries each tenant's database — may take a moment for large deployments.
            </div>
            <div class="export-group">
                <a href="{{ route('superadmin.reports.activity', ['format' => 'excel']) }}"
                   class="export-btn btn-excel">
                    <i class="fas fa-file-excel"></i> Excel (.xlsx)
                </a>
                <a href="{{ route('superadmin.reports.activity', ['format' => 'pdf']) }}"
                   class="export-btn btn-pdf">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <a href="{{ route('superadmin.reports.activity', ['format' => 'csv']) }}"
                   class="export-btn btn-csv">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
            </div>
        </div>
    </div>

    {{-- ── 4. Monthly Registrations ─────────────────────────────── --}}
    <div class="report-card">
        <div class="report-card-header">
            <div class="report-icon" style="background:rgba(206,17,38,.10);color:var(--sa-danger);">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <div class="font-bold text-base" style="color:var(--sa-primary);">Monthly Registrations</div>
                <div class="text-xs mt-0.5" style="color:var(--sa-muted);">
                    Tenant sign-ups over time — one row per registration with month, plan, and status
                </div>
            </div>
        </div>
        <div class="report-card-body">

            {{-- Mini bar chart (last 6 months) ── --}}
            @php
                $last6 = array_slice($monthlyRegistrations, -6);
                $barMax = collect($last6)->max('count');
                $barMax = max($barMax, 1);
            @endphp
            <div class="mini-bar-group">
                @foreach($last6 as $m)
                    @php $pct = ($m['count'] / $barMax) * 100; @endphp
                    <div class="mini-bar-row">
                        <div class="mini-bar-label">{{ substr($m['label'], 0, 6) }}</div>
                        <div class="mini-bar-track">
                            <div class="mini-bar-fill"
                                 style="width:{{ max($pct, 4) }}%; background:linear-gradient(90deg,var(--sa-accent),var(--sa-primary));">
                                @if($m['count'] > 0)
                                    <span class="mini-bar-val">{{ $m['count'] }}</span>
                                @endif
                            </div>
                        </div>
                        @if($m['count'] === 0)
                            <span style="font-size:10px;color:var(--sa-muted);">0</span>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Range picker ── --}}
            <div class="filter-row mt-3">
                <label><i class="fas fa-sliders-h mr-1"></i> Range:</label>
                <select id="months-select">
                    <option value="3">Last 3 months</option>
                    <option value="6">Last 6 months</option>
                    <option value="12" selected>Last 12 months</option>
                    <option value="24">Last 24 months</option>
                </select>
            </div>

            <div class="export-group" id="reg-export-group">
                <a href="{{ route('superadmin.reports.registrations', ['format' => 'excel', 'months' => 12]) }}"
                   class="export-btn btn-excel" id="reg-excel">
                    <i class="fas fa-file-excel"></i> Excel (.xlsx)
                </a>
                <a href="{{ route('superadmin.reports.registrations', ['format' => 'pdf', 'months' => 12]) }}"
                   class="export-btn btn-pdf" id="reg-pdf">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <a href="{{ route('superadmin.reports.registrations', ['format' => 'csv', 'months' => 12]) }}"
                   class="export-btn btn-csv" id="reg-csv">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- Plans quick-reference (dynamic)                               --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
@if($plans->isNotEmpty())
<div class="rounded-2xl border-2 p-5 mb-6" style="border-color:var(--sa-border);background:var(--sa-bg);">
    <div class="font-bold text-sm mb-3" style="color:var(--sa-primary);">
        <i class="fas fa-tags mr-2" style="color:var(--sa-accent);"></i>
        Active Subscription Plans
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach($plans as $plan)
            @php
                $count = $subscriptionBreakdown[$plan->slug]['count'] ?? 0;
                $color = $subscriptionBreakdown[$plan->slug]['color'] ?? '#5a7aaa';
            @endphp
            <div class="rounded-xl p-3 text-center" style="background:var(--sa-surface);border:1.5px solid var(--sa-border);">
                @if($plan->icon)
                    <div class="text-lg mb-1">{{ $plan->icon }}</div>
                @endif
                <div class="font-bold text-sm" style="color:var(--sa-text);">{{ $plan->name }}</div>
                <div class="text-xs mt-0.5" style="color:var(--sa-muted);">{{ $plan->duration_label }}</div>
                <div class="mt-2 text-xl font-extrabold" style="color:{{ $color }};">{{ $count }}</div>
                <div class="text-xs" style="color:var(--sa-muted);">tenants</div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- Help / Notes                                                   --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div class="rounded-2xl border-2 p-5" style="border-color:var(--sa-border);background:var(--sa-bg);">
    <div class="font-bold text-sm mb-3" style="color:var(--sa-primary);">
        <i class="fas fa-question-circle mr-2" style="color:var(--sa-accent);"></i>
        Export Format Guide
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm" style="color:var(--sa-muted);">
        <div class="flex items-start gap-3">
            <i class="fas fa-file-excel mt-0.5 text-green-700 flex-shrink-0"></i>
            <div>
                <span class="font-semibold" style="color:var(--sa-text);">Excel (.xlsx)</span><br>
                Full TESDA-branded spreadsheet with colored headers, alternating rows, and auto-sized columns. Best for review and sharing.
            </div>
        </div>
        <div class="flex items-start gap-3">
            <i class="fas fa-file-pdf mt-0.5 flex-shrink-0" style="color:var(--sa-danger);"></i>
            <div>
                <span class="font-semibold" style="color:var(--sa-text);">PDF</span><br>
                Landscape A4 document with TESDA header, tricolor bar, and table. Ideal for printing or official submission.
            </div>
        </div>
        <div class="flex items-start gap-3">
            <i class="fas fa-file-csv mt-0.5 flex-shrink-0" style="color:var(--sa-muted);"></i>
            <div>
                <span class="font-semibold" style="color:var(--sa-text);">CSV</span><br>
                Plain comma-separated text. Best for importing into other systems, scripts, or custom Excel work.
            </div>
        </div>
    </div>
</div>

<script>
    // Update the Registration export links when range changes
    const sel = document.getElementById('months-select');
    const baseExcel = "{{ route('superadmin.reports.registrations', ['format' => 'excel', 'months' => '__M__']) }}";
    const basePdf   = "{{ route('superadmin.reports.registrations', ['format' => 'pdf',   'months' => '__M__']) }}";
    const baseCsv   = "{{ route('superadmin.reports.registrations', ['format' => 'csv',   'months' => '__M__']) }}";

    sel.addEventListener('change', function () {
        const m = this.value;
        document.getElementById('reg-excel').href = baseExcel.replace('__M__', m);
        document.getElementById('reg-pdf').href   = basePdf.replace('__M__', m);
        document.getElementById('reg-csv').href   = baseCsv.replace('__M__', m);
    });
</script>

@endsection