@extends('layouts.app')

@section('title', 'Release v' . $release->version)

@section('content')
<style>
    :root {
        --sa-primary:#003087;--sa-accent:#0057B8;--sa-success:#16a34a;
        --sa-warning:#b38a00;--sa-danger:#CE1126;--sa-border:#c5d8f5;
        --sa-text:#001a4d;--sa-text-muted:#5a7aaa;--sa-bg:#ffffff;
    }
    .dark { --sa-bg:#0a1628;--sa-border:#1e3a6b;--sa-text:#dde8ff;--sa-text-muted:#6b8abf; }
    .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;}
    .badge-green{background:rgba(22,163,74,.12);color:#16a34a;}
    .badge-yellow{background:rgba(179,138,0,.12);color:#b38a00;}
    .badge-blue{background:rgba(0,87,184,.12);color:#0057B8;}
    .badge-red{background:rgba(206,17,38,.12);color:#CE1126;}
    .badge-gray{background:rgba(90,122,170,.12);color:#5a7aaa;}
    .dark .badge-green{color:#4ade80;}.dark .badge-yellow{color:#fbbf24;}
    .dark .badge-blue{color:#60a5fa;}.dark .badge-red{color:#f87171;}
</style>

<div class="space-y-6">

    {{-- Back + Header --}}
    <div class="flex items-start justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('superadmin.releases.index') }}"
               class="w-9 h-9 flex items-center justify-center rounded-xl border-2 transition hover:bg-gray-50 dark:hover:bg-white/5"
               style="border-color:var(--sa-border);color:var(--sa-text-muted)">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold" style="color:var(--sa-primary)">
                    Release <span class="font-mono">v{{ $release->version }}</span>
                </h1>
                <p class="text-sm mt-0.5" style="color:var(--sa-text-muted)">
                    {{ $release->name }} &bull; Published {{ $release->published_at?->format('M j, Y') ?? 'Unknown' }}
                </p>
            </div>
        </div>
        <div class="flex gap-2">
            @if(!$release->is_deployed)
            <form action="{{ route('superadmin.releases.deploy', $release) }}" method="POST">
                @csrf
                <button type="submit"
                    class="px-4 py-2 rounded-xl text-white text-sm font-semibold hover:opacity-90"
                    style="background:var(--sa-success)"
                    onclick="return confirm('Mark this release as deployed and push update badges to all tenants?')">
                    <i class="fas fa-rocket mr-1"></i> Mark Deployed &amp; Activate
                </button>
            </form>
            @else
            <span class="badge badge-green px-4 py-2 text-sm">
                <i class="fas fa-check-circle"></i> Deployed &amp; Active
            </span>
            @endif

            @if($release->is_deployed)
            <form action="{{ route('superadmin.releases.push-all', $release) }}" method="POST">
                @csrf
                <button type="submit"
                    class="px-4 py-2 rounded-xl text-white text-sm font-semibold hover:opacity-90"
                    style="background:var(--sa-accent)"
                    onclick="return confirm('Queue migration jobs for ALL tenants with pending updates?')">
                    <i class="fas fa-broadcast-tower mr-1"></i> Push to All Pending
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-xl p-4 text-sm font-medium" style="background:rgba(22,163,74,.1);color:#16a34a;border:1px solid rgba(22,163,74,.2)">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    {{-- Release Notes --}}
    @if($release->body)
    <div class="rounded-2xl border-2 p-6" style="background:var(--sa-bg);border-color:var(--sa-border)">
        <h2 class="font-bold mb-4 flex items-center gap-2" style="color:var(--sa-primary)">
            <i class="fas fa-file-alt" style="color:var(--sa-accent)"></i> Release Notes
        </h2>
        <div class="text-sm leading-relaxed whitespace-pre-wrap rounded-xl p-4"
             style="background:var(--sa-bg);color:var(--sa-text);border:1px solid var(--sa-border)">{{ $release->body }}</div>
        @if($release->github_url)
        <a href="{{ $release->github_url }}" target="_blank"
           class="inline-flex items-center gap-2 mt-4 text-sm font-medium"
           style="color:var(--sa-accent)">
            <i class="fab fa-github"></i> View on GitHub
        </a>
        @endif
    </div>
    @endif

    {{-- Tenant Update Status Table --}}
    <div class="rounded-2xl border-2 overflow-hidden" style="background:var(--sa-bg);border-color:var(--sa-border)">
        <div class="px-6 py-4 border-b flex items-center justify-between" style="border-color:var(--sa-border)">
            <h2 class="font-bold" style="color:var(--sa-primary)">
                <i class="fas fa-layer-group mr-2" style="color:var(--sa-accent)"></i>
                Tenant Update Status
            </h2>
            <form action="{{ route('superadmin.releases.sync-tenants') }}" method="POST">
                @csrf
                <button class="text-xs font-semibold px-3 py-1.5 rounded-lg"
                        style="background:rgba(0,87,184,.1);color:var(--sa-accent)">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh All
                </button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid var(--sa-border)">
                        <th class="text-left px-6 py-3 font-semibold" style="color:var(--sa-text-muted)">Tenant</th>
                        <th class="text-left px-6 py-3 font-semibold" style="color:var(--sa-text-muted)">Current</th>
                        <th class="text-left px-6 py-3 font-semibold" style="color:var(--sa-text-muted)">Latest</th>
                        <th class="text-left px-6 py-3 font-semibold" style="color:var(--sa-text-muted)">Status</th>
                        <th class="text-left px-6 py-3 font-semibold" style="color:var(--sa-text-muted)">Last Updated</th>
                        <th class="text-left px-6 py-3 font-semibold" style="color:var(--sa-text-muted)">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($tenantStatuses as $vs)
                <tr class="border-b hover:bg-gray-50 dark:hover:bg-white/5 transition"
                    style="border-color:var(--sa-border)">
                    <td class="px-6 py-4">
                        <div class="font-semibold" style="color:var(--sa-text)">{{ $vs->tenant?->name ?? '—' }}</div>
                        <div class="text-xs mt-0.5" style="color:var(--sa-text-muted)">{{ $vs->tenant?->subdomain }}</div>
                    </td>
                    <td class="px-6 py-4 font-mono text-sm" style="color:var(--sa-text)">
                        {{ $vs->current_version ?? '—' }}
                    </td>
                    <td class="px-6 py-4 font-mono text-sm" style="color:var(--sa-text)">
                        {{ $vs->latest_version ?? '—' }}
                    </td>
                    <td class="px-6 py-4">
                        @php
                        $badge = match($vs->update_status) {
                            'up_to_date'       => ['badge-green','fa-check-circle','Up to Date'],
                            'update_available' => ['badge-yellow','fa-exclamation-circle','Update Available'],
                            'queued'           => ['badge-blue','fa-clock','Queued'],
                            'running'          => ['badge-blue','fa-spinner fa-spin','Updating…'],
                            'failed'           => ['badge-red','fa-times-circle','Failed'],
                            default            => ['badge-gray','fa-circle','Unknown'],
                        };
                        @endphp
                        <span class="badge {{ $badge[0] }}">
                            <i class="fas {{ $badge[1] }} text-xs"></i> {{ $badge[2] }}
                        </span>
                        @if($vs->update_status === 'failed' && $vs->failure_reason)
                        <p class="text-xs mt-1" style="color:#CE1126">{{ Str::limit($vs->failure_reason, 60) }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-xs" style="color:var(--sa-text-muted)">
                        {{ $vs->last_updated_at?->diffForHumans() ?? 'Never' }}
                    </td>
                    <td class="px-6 py-4">
                        @if(in_array($vs->update_status, ['update_available', 'failed']) && $release->is_deployed)
                        <form action="{{ route('superadmin.releases.push-tenant', $release) }}" method="POST">
                            @csrf
                            <input type="hidden" name="tenant_id" value="{{ $vs->tenant_id }}">
                            <button class="text-xs font-semibold px-3 py-1.5 rounded-lg text-white hover:opacity-90"
                                    style="background:var(--sa-accent)">
                                <i class="fas fa-play mr-1"></i> Run Update
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center" style="color:var(--sa-text-muted)">
                        No tenant version records. Click "Refresh All" to initialize.
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Update Logs --}}
    @if($logs->isNotEmpty())
    <div class="rounded-2xl border-2 overflow-hidden" style="background:var(--sa-bg);border-color:var(--sa-border)">
        <div class="px-6 py-4 border-b" style="border-color:var(--sa-border)">
            <h2 class="font-bold" style="color:var(--sa-primary)">
                <i class="fas fa-history mr-2" style="color:var(--sa-accent)"></i> Update Logs
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid var(--sa-border)">
                        <th class="text-left px-6 py-3 font-semibold" style="color:var(--sa-text-muted)">Tenant</th>
                        <th class="text-left px-6 py-3 font-semibold" style="color:var(--sa-text-muted)">From → To</th>
                        <th class="text-left px-6 py-3 font-semibold" style="color:var(--sa-text-muted)">Status</th>
                        <th class="text-left px-6 py-3 font-semibold" style="color:var(--sa-text-muted)">Triggered By</th>
                        <th class="text-left px-6 py-3 font-semibold" style="color:var(--sa-text-muted)">Duration</th>
                        <th class="text-left px-6 py-3 font-semibold" style="color:var(--sa-text-muted)">Time</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                <tr class="border-b hover:bg-gray-50 dark:hover:bg-white/5" style="border-color:var(--sa-border)">
                    <td class="px-6 py-3 font-medium" style="color:var(--sa-text)">{{ $log->tenant?->name ?? $log->tenant_id }}</td>
                    <td class="px-6 py-3 font-mono text-xs" style="color:var(--sa-text-muted)">
                        {{ $log->from_version ?? '?' }} → {{ $log->to_version ?? '?' }}
                    </td>
                    <td class="px-6 py-3">
                        @php $sc = match($log->status){ 'completed'=>'badge-green','failed'=>'badge-red','running'=>'badge-blue',default=>'badge-gray' }; @endphp
                        <span class="badge {{ $sc }}">{{ ucfirst($log->status) }}</span>
                    </td>
                    <td class="px-6 py-3 text-xs" style="color:var(--sa-text-muted)">{{ ucfirst($log->triggered_by) }}</td>
                    <td class="px-6 py-3 text-xs" style="color:var(--sa-text-muted)">{{ $log->duration ?? '—' }}</td>
                    <td class="px-6 py-3 text-xs" style="color:var(--sa-text-muted)">{{ $log->created_at->diffForHumans() }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
