{{-- resources/views/superadmin/monitoring/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Tenant Monitoring')

@section('content')
<style>
    :root {
        --sa-primary: #003087;
        --sa-accent:  #0057B8;
        --sa-success: #16a34a;
        --sa-warning: #b38a00;
        --sa-danger:  #CE1126;
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

    .stat-card {
        border-radius: 16px; border: 2px solid var(--sa-border);
        background: var(--sa-bg); padding: 22px 24px;
        transition: transform .18s, box-shadow .18s;
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(0,48,135,.10); }

    .mon-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .mon-table th {
        padding: 10px 14px; text-align: left; font-weight: 700;
        font-size: 11px; letter-spacing: .4px; text-transform: uppercase;
        color: var(--sa-muted); border-bottom: 2px solid var(--sa-border);
        background: var(--sa-surface);
    }
    .mon-table td { padding: 11px 14px; color: var(--sa-text); border-bottom: 1px solid var(--sa-border); }
    .mon-table tr:last-child td { border-bottom: none; }
    .mon-table tr:hover td { background: var(--sa-surface); }

    /* Storage bar */
    .usage-bar { height: 8px; border-radius: 4px; background: var(--sa-surface); overflow: hidden; min-width: 80px; }
    .usage-fill { height: 100%; border-radius: 4px; transition: width .4s; }
</style>

<div class="space-y-6">

    {{-- ── Header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold" style="color:var(--sa-primary);">
                <i class="fas fa-server mr-2" style="color:var(--sa-accent);"></i> Tenant Monitoring
            </h1>
            <p class="text-sm mt-1" style="color:var(--sa-muted);">
                Storage and bandwidth consumption across all approved tenants
            </p>
        </div>
        <div class="flex gap-2">
            <form action="{{ route('superadmin.monitoring.recalculate.all') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm px-4 py-2 rounded-lg font-semibold text-white transition"
                        style="background:var(--sa-accent);">
                    <i class="fas fa-sync-alt mr-1"></i> Recalculate All
                </button>
            </form>
            <a href="{{ route('superadmin.dashboard') }}"
               class="text-sm px-4 py-2 rounded-lg font-semibold"
               style="background:rgba(0,48,135,.08);color:var(--sa-accent);">
                <i class="fas fa-arrow-left mr-1"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- ── Flash ── --}}
    @if(session('success'))
        <div class="rounded-xl border-2 p-4" style="background:rgba(22,163,74,.05);border-color:var(--sa-success);">
            <div style="color:var(--sa-success);" class="font-semibold flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-xl border-2 p-4" style="background:rgba(206,17,38,.05);border-color:var(--sa-danger);">
            <div style="color:var(--sa-danger);" class="font-semibold flex items-center gap-3">
                <i class="fas fa-times-circle"></i> {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- ── Platform Totals ── --}}
    @php
        use App\Models\TenantUsageStat;
        $fmt = fn(int $b) => TenantUsageStat::formatBytes($b);
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="text-xs font-bold uppercase tracking-widest mb-1" style="color:var(--sa-muted);">Total DB Storage</div>
            <div class="text-2xl font-black" style="color:var(--sa-primary);">{{ $fmt($totals['db_bytes']) }}</div>
            <div class="text-xs mt-1" style="color:var(--sa-muted);">across all tenant databases</div>
        </div>
        <div class="stat-card">
            <div class="text-xs font-bold uppercase tracking-widest mb-1" style="color:var(--sa-muted);">Total File Storage</div>
            <div class="text-2xl font-black" style="color:var(--sa-accent);">{{ $fmt($totals['file_bytes']) }}</div>
            <div class="text-xs mt-1" style="color:var(--sa-muted);">uploads, logos, etc.</div>
        </div>
        <div class="stat-card">
            <div class="text-xs font-bold uppercase tracking-widest mb-1" style="color:var(--sa-muted);">Bandwidth Today</div>
            <div class="text-2xl font-black" style="color:var(--sa-warning);">{{ $fmt($totals['bandwidth_today']) }}</div>
            <div class="text-xs mt-1" style="color:var(--sa-muted);">request + response bytes</div>
        </div>
        <div class="stat-card">
            <div class="text-xs font-bold uppercase tracking-widest mb-1" style="color:var(--sa-muted);">Bandwidth All-Time</div>
            <div class="text-2xl font-black" style="color:var(--sa-text);">{{ $fmt($totals['bandwidth_total']) }}</div>
            <div class="text-xs mt-1" style="color:var(--sa-muted);">since monitoring began</div>
        </div>
    </div>

    {{-- ── Top Consumers ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Storage --}}
        <div class="rounded-2xl border-2 p-5" style="background:var(--sa-bg);border-color:var(--sa-border);">
            <div class="font-bold text-sm mb-4" style="color:var(--sa-primary);">
                <i class="fas fa-database mr-2" style="color:var(--sa-accent);"></i> Top Storage Consumers
            </div>
            @php $maxDb = $topStorage->max(fn($s) => $s->db_size_bytes) ?: 1; @endphp
            <div class="space-y-3">
                @forelse($topStorage as $s)
                    <div class="flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-sm truncate" style="color:var(--sa-text);">
                                {{ $s->tenant?->name ?? 'Unknown' }}
                            </div>
                            <div class="usage-bar mt-1">
                                <div class="usage-fill"
                                     style="width:{{ ($s->db_size_bytes / $maxDb) * 100 }}%;
                                            background:linear-gradient(90deg,var(--sa-accent),var(--sa-primary));"></div>
                            </div>
                        </div>
                        <div class="text-xs font-bold whitespace-nowrap" style="color:var(--sa-text);">
                            {{ $s->formatted_db_size }}
                        </div>
                    </div>
                @empty
                    <p class="text-sm" style="color:var(--sa-muted);">No data yet. Run a recalculation.</p>
                @endforelse
            </div>
        </div>

        {{-- Bandwidth --}}
        <div class="rounded-2xl border-2 p-5" style="background:var(--sa-bg);border-color:var(--sa-border);">
            <div class="font-bold text-sm mb-4" style="color:var(--sa-primary);">
                <i class="fas fa-wifi mr-2" style="color:var(--sa-warning);"></i> Top Bandwidth Today
            </div>
            @php $maxBw = $topBandwidth->max(fn($s) => $s->bandwidth_bytes_today) ?: 1; @endphp
            <div class="space-y-3">
                @forelse($topBandwidth as $s)
                    <div class="flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-sm truncate" style="color:var(--sa-text);">
                                {{ $s->tenant?->name ?? 'Unknown' }}
                            </div>
                            <div class="usage-bar mt-1">
                                <div class="usage-fill"
                                     style="width:{{ ($s->bandwidth_bytes_today / $maxBw) * 100 }}%;
                                            background:linear-gradient(90deg,#b38a00,var(--sa-warning));"></div>
                            </div>
                        </div>
                        <div class="text-xs font-bold whitespace-nowrap" style="color:var(--sa-text);">
                            {{ $s->formatted_bandwidth_today }}
                        </div>
                    </div>
                @empty
                    <p class="text-sm" style="color:var(--sa-muted);">No bandwidth data yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Full Table ── --}}
    <div class="rounded-2xl border-2 overflow-hidden" style="background:var(--sa-bg);border-color:var(--sa-border);">
        <div class="px-6 py-4 border-b" style="border-color:var(--sa-border);">
            <div class="font-bold" style="color:var(--sa-primary);">
                <i class="fas fa-table mr-2" style="color:var(--sa-accent);"></i>
                All Tenant Usage — {{ $tenants->count() }} tenant(s)
            </div>
        </div>

        @if($tenants->count() > 0)
            <div class="overflow-x-auto">
                <table class="mon-table">
                    <thead>
                        <tr>
                            <th>Organization</th>
                            <th>Plan</th>
                            <th>DB Size</th>
                            <th>File Size</th>
                            <th>Total Storage</th>
                            <th>Bandwidth Today</th>
                            <th>Bandwidth Total</th>
                            <th>Last Checked</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tenants as $tenant)
                            @php $s = $tenant->usageStat; @endphp
                            <tr>
                                <td>
                                    <div class="font-semibold">{{ $tenant->name }}</div>
                                    <div class="text-xs" style="color:var(--sa-muted);">{{ $tenant->subdomain }}.tcm.com</div>
                                </td>
                                <td>
                                    <span class="px-2 py-1 rounded-full text-xs font-bold"
                                          style="background:rgba(0,87,184,.1);color:var(--sa-accent);">
                                        {{ ucfirst($tenant->subscription) }}
                                    </span>
                                </td>
                                <td class="font-semibold">{{ $s?->formatted_db_size   ?? '—' }}</td>
                                <td class="font-semibold">{{ $s?->formatted_file_size ?? '—' }}</td>
                                <td>
                                    <span class="font-bold" style="color:var(--sa-primary);">
                                        {{ $s ? $s->formatted_total_storage : '—' }}
                                    </span>
                                </td>
                                <td style="color:var(--sa-warning);">
                                    {{ $s?->formatted_bandwidth_today ?? '—' }}
                                </td>
                                <td style="color:var(--sa-muted);">
                                    {{ $s?->formatted_bandwidth_total ?? '—' }}
                                </td>
                                <td class="text-xs" style="color:var(--sa-muted);">
                                    {{ $s?->last_calculated_at?->diffForHumans() ?? 'Never' }}
                                </td>
                                <td>
                                    <form action="{{ route('superadmin.monitoring.recalculate', $tenant) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="text-xs px-3 py-1 rounded-lg font-semibold transition"
                                                style="background:rgba(0,87,184,.10);color:var(--sa-accent);">
                                            <i class="fas fa-sync-alt mr-1"></i> Recalc
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <i class="fas fa-server text-5xl mb-4" style="color:var(--sa-muted);opacity:.3;"></i>
                <p style="color:var(--sa-muted);">No approved tenants to monitor.</p>
            </div>
        @endif
    </div>

</div>
@endsection