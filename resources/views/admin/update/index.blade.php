@extends('layouts.app')

@section('title', 'System Update')

@section('content')
<style>
    :root{--sa-primary:#003087;--sa-accent:#0057B8;--sa-success:#16a34a;--sa-warning:#b38a00;--sa-danger:#CE1126;--sa-border:#c5d8f5;--sa-text:#001a4d;--sa-text-muted:#5a7aaa;--sa-bg:#ffffff;}
    .dark{--sa-bg:#0a1628;--sa-border:#1e3a6b;--sa-text:#dde8ff;--sa-text-muted:#6b8abf;}
    .badge{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:700;}
    .badge-green{background:rgba(22,163,74,.12);color:#16a34a;}
    .badge-yellow{background:rgba(179,138,0,.12);color:#b38a00;}
    .badge-blue{background:rgba(0,87,184,.12);color:#0057B8;}
    .badge-red{background:rgba(206,17,38,.12);color:#CE1126;}
    .badge-gray{background:rgba(90,122,170,.12);color:#5a7aaa;}
</style>

<div class="max-w-3xl mx-auto space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold" style="color:var(--sa-primary)">
            <i class="fas fa-cloud-download-alt mr-2" style="color:var(--sa-accent)"></i>
            System Update
        </h1>
        <p class="text-sm mt-1" style="color:var(--sa-text-muted)">
            Keep your system up-to-date with the latest features, fixes, and improvements.
        </p>
    </div>

    @if(session('success'))
    <div class="rounded-xl p-4 text-sm font-medium" style="background:rgba(22,163,74,.1);color:#16a34a;border:1px solid rgba(22,163,74,.2)">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('info'))
    <div class="rounded-xl p-4 text-sm font-medium" style="background:rgba(0,87,184,.08);color:#0057B8;border:1px solid rgba(0,87,184,.2)">
        <i class="fas fa-info-circle mr-2"></i>{{ session('info') }}
    </div>
    @endif
    @if(session('error'))
    <div class="rounded-xl p-4 text-sm font-medium" style="background:rgba(206,17,38,.08);color:#CE1126;border:1px solid rgba(206,17,38,.2)">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    {{-- Current Status Card --}}
    <div class="rounded-2xl border-2 p-6" style="background:var(--sa-bg);border-color:var(--sa-border)">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide mb-1" style="color:var(--sa-text-muted)">Current Version</p>
                <p class="text-3xl font-bold font-mono" style="color:var(--sa-primary)">
                    v{{ $status->current_version ?? '1.0.0' }}
                </p>
                @if($release)
                <p class="text-xs mt-1" style="color:var(--sa-text-muted)">
                    Latest available: <span class="font-semibold font-mono">v{{ $release->version }}</span>
                </p>
                @endif
            </div>
            <div>
                @php
                $badge = match($status->update_status) {
                    'up_to_date'       => ['badge-green',  'fa-check-circle',  'Up to Date'],
                    'update_available' => ['badge-yellow', 'fa-exclamation-circle', 'Update Available'],
                    'queued'           => ['badge-blue',   'fa-clock',         'Queued'],
                    'running'          => ['badge-blue',   'fa-spinner fa-spin','Updating…'],
                    'completed'        => ['badge-green',  'fa-check-circle',  'Completed'],
                    'failed'           => ['badge-red',    'fa-times-circle',  'Failed'],
                    default            => ['badge-gray',   'fa-circle',        'Unknown'],
                };
                @endphp
                <span class="badge {{ $badge[0] }} text-sm px-4 py-2">
                    <i class="fas {{ $badge[1] }}"></i> {{ $badge[2] }}
                </span>
            </div>
        </div>

        {{-- Progress / CTA --}}
        @if($status->update_status === 'up_to_date')
        <div class="mt-6 rounded-xl p-4 text-center" style="background:rgba(22,163,74,.06);border:1.5px solid rgba(22,163,74,.2)">
            <i class="fas fa-shield-alt text-2xl mb-2" style="color:#16a34a"></i>
            <p class="font-semibold text-sm" style="color:#16a34a">Your system is fully up to date!</p>
            <p class="text-xs mt-1" style="color:var(--sa-text-muted)">Last updated {{ $status->last_updated_at?->diffForHumans() ?? 'N/A' }}</p>
        </div>

        @elseif(in_array($status->update_status, ['queued', 'running']))
        <div class="mt-6 rounded-xl p-4" style="background:rgba(0,87,184,.06);border:1.5px solid rgba(0,87,184,.2)">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(0,87,184,.15)">
                    <i class="fas fa-sync-alt fa-spin" style="color:var(--sa-accent)"></i>
                </div>
                <div>
                    <p class="font-semibold text-sm" style="color:var(--sa-accent)">Update in progress…</p>
                    <p class="text-xs mt-0.5" style="color:var(--sa-text-muted)">Please wait. This usually takes less than a minute.</p>
                </div>
            </div>
            {{-- Auto-refresh while updating --}}
            <script>setTimeout(()=>location.reload(),8000);</script>
        </div>

        @elseif($status->update_status === 'update_available')
        <div class="mt-6 rounded-xl p-5" style="background:rgba(179,138,0,.06);border:1.5px solid rgba(179,138,0,.25)">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="font-bold text-sm" style="color:#b38a00">
                        <i class="fas fa-arrow-up mr-1"></i>
                        Update Available — v{{ $release?->version }}
                    </p>
                    @if($release?->name)
                    <p class="text-xs mt-1" style="color:var(--sa-text-muted)">{{ $release->name }}</p>
                    @endif
                </div>
                <form action="{{ route('admin.update.apply') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-white text-sm font-bold hover:opacity-90 transition"
                            style="background:var(--sa-accent)"
                            onclick="return confirm('Apply the system update now? Your system will run database migrations. This is safe and reversible.')">
                        <i class="fas fa-rocket mr-1"></i> Update Now
                    </button>
                </form>
            </div>
            @if($release?->body)
            <div class="mt-4 pt-4 border-t" style="border-color:rgba(179,138,0,.2)">
                <p class="text-xs font-semibold mb-2" style="color:var(--sa-text-muted)">What's new:</p>
                <div class="text-xs leading-relaxed whitespace-pre-wrap" style="color:var(--sa-text)">{{ Str::limit($release->body, 600) }}</div>
            </div>
            @endif
        </div>

        @elseif($status->update_status === 'failed')
        <div class="mt-6 rounded-xl p-4" style="background:rgba(206,17,38,.06);border:1.5px solid rgba(206,17,38,.2)">
            <p class="font-semibold text-sm" style="color:#CE1126">
                <i class="fas fa-exclamation-triangle mr-2"></i>Last update attempt failed.
            </p>
            @if($status->failure_reason)
            <p class="text-xs mt-1 font-mono" style="color:var(--sa-text-muted)">{{ $status->failure_reason }}</p>
            @endif
            <p class="text-xs mt-2" style="color:var(--sa-text-muted)">
                Please contact support if this problem persists.
                <a href="{{ route('admin.support.create') }}" class="font-semibold underline" style="color:var(--sa-accent)">Open a ticket →</a>
            </p>
            @if($release)
            <form action="{{ route('admin.update.apply') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-lg text-white"
                        style="background:#CE1126">Retry Update</button>
            </form>
            @endif
        </div>
        @endif
    </div>

    {{-- Recent Releases --}}
    @if($releases->isNotEmpty())
    <div class="rounded-2xl border-2 p-5" style="background:var(--sa-bg);border-color:var(--sa-border)">
        <h2 class="font-bold text-sm mb-4" style="color:var(--sa-primary)">
            <i class="fas fa-history mr-2" style="color:var(--sa-accent)"></i>Recent Releases
        </h2>
        <div class="space-y-3">
            @foreach($releases as $r)
            <div class="flex items-start gap-3 pb-3 {{ !$loop->last ? 'border-b' : '' }}" style="border-color:var(--sa-border)">
                <span class="font-mono font-bold text-sm w-20 flex-shrink-0" style="color:var(--sa-primary)">v{{ $r->version }}</span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium" style="color:var(--sa-text)">{{ $r->name }}</p>
                    @if($r->body)
                    <p class="text-xs mt-0.5 line-clamp-2" style="color:var(--sa-text-muted)">{{ $r->body }}</p>
                    @endif
                    <p class="text-xs mt-1" style="color:var(--sa-text-muted)">{{ $r->published_at?->format('M j, Y') }}</p>
                </div>
                @if($r->is_active)
                <span class="badge badge-green flex-shrink-0"><i class="fas fa-circle text-xs"></i> Current</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Update History --}}
    @if($logs->isNotEmpty())
    <div class="rounded-2xl border-2 overflow-hidden" style="background:var(--sa-bg);border-color:var(--sa-border)">
        <div class="px-5 py-4 border-b" style="border-color:var(--sa-border)">
            <h2 class="font-bold text-sm" style="color:var(--sa-primary)">
                <i class="fas fa-list-alt mr-2" style="color:var(--sa-accent)"></i>Update History
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr style="border-bottom:1px solid var(--sa-border)">
                        <th class="text-left px-5 py-3 font-semibold" style="color:var(--sa-text-muted)">Version</th>
                        <th class="text-left px-5 py-3 font-semibold" style="color:var(--sa-text-muted)">Status</th>
                        <th class="text-left px-5 py-3 font-semibold" style="color:var(--sa-text-muted)">Duration</th>
                        <th class="text-left px-5 py-3 font-semibold" style="color:var(--sa-text-muted)">Date</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                <tr class="border-b" style="border-color:var(--sa-border)">
                    <td class="px-5 py-3 font-mono" style="color:var(--sa-text)">
                        {{ $log->from_version ?? '?' }} → v{{ $log->to_version }}
                    </td>
                    <td class="px-5 py-3">
                        @php $bc = match($log->status){ 'completed'=>'badge-green','failed'=>'badge-red','running'=>'badge-blue',default=>'badge-gray' }; @endphp
                        <span class="badge {{ $bc }}">{{ ucfirst($log->status) }}</span>
                    </td>
                    <td class="px-5 py-3" style="color:var(--sa-text-muted)">{{ $log->duration ?? '—' }}</td>
                    <td class="px-5 py-3" style="color:var(--sa-text-muted)">{{ $log->created_at->format('M j, Y') }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
