@extends('layouts.app')

@section('title', 'Update Management')

@section('content')
<style>
    :root {
        --sa-primary: #003087; --sa-accent: #0057B8;
        --sa-success: #16a34a; --sa-warning: #b38a00;
        --sa-danger: #CE1126; --sa-border: #c5d8f5;
        --sa-text: #001a4d; --sa-text-muted: #5a7aaa; --sa-bg: #ffffff;
    }
    .dark { --sa-bg:#0a1628; --sa-border:#1e3a6b; --sa-text:#dde8ff; --sa-text-muted:#6b8abf; }
    .badge { display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600; }
    .badge-green  { background:rgba(22,163,74,.12);  color:#16a34a; }
    .badge-yellow { background:rgba(179,138,0,.12);  color:#b38a00; }
    .badge-blue   { background:rgba(0,87,184,.12);   color:#0057B8; }
    .badge-red    { background:rgba(206,17,38,.12);  color:#CE1126; }
    .badge-gray   { background:rgba(90,122,170,.12); color:#5a7aaa; }
    .dark .badge-green  { color:#4ade80; } .dark .badge-yellow { color:#fbbf24; }
    .dark .badge-blue   { color:#60a5fa; } .dark .badge-red    { color:#f87171; }
</style>

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-primary)">
                <i class="fas fa-cloud-download-alt mr-2" style="color:var(--sa-accent)"></i>
                Update Management
            </h1>
            <p class="text-sm mt-1" style="color:var(--sa-text-muted)">
                Manage system releases, track tenant update progress, and push migrations.
            </p>
        </div>
        <button id="fetchBtn"
            class="flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-semibold transition hover:opacity-90"
            style="background:var(--sa-accent)">
            <i id="fetchBtnIcon" class="fas fa-sync-alt"></i>
            <span id="fetchBtnText">Fetch from GitHub</span>
        </button>
    </div>

    {{-- Toast notification (AJAX feedback) --}}
    <div id="fetchToast" style="
        display:none; position:fixed; top:24px; right:24px; z-index:9999;
        min-width:320px; max-width:420px; border-radius:14px; padding:16px 20px;
        box-shadow:0 8px 32px rgba(0,0,0,.15); font-size:14px; font-weight:600;
        display:none; align-items:center; gap:12px;
    ">
        <i id="fetchToastIcon" class="fas text-lg"></i>
        <span id="fetchToastMsg"></span>
    </div>

    @if(session('success'))
        <div class="rounded-xl p-4 text-sm font-medium" style="background:rgba(22,163,74,.1);color:#16a34a;border:1px solid rgba(22,163,74,.2)">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-xl p-4 text-sm font-medium" style="background:rgba(206,17,38,.1);color:#CE1126;border:1px solid rgba(206,17,38,.2)">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- Summary KPI cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        @php
        $kpis = [
            ['label'=>'Total Tenants','value'=>$summary['total'],'icon'=>'fa-layer-group','color'=>'var(--sa-accent)'],
            ['label'=>'Up to Date','value'=>$summary['up_to_date'],'icon'=>'fa-check-circle','color'=>'#16a34a'],
            ['label'=>'Update Available','value'=>$summary['update_available'],'icon'=>'fa-exclamation-circle','color'=>'#b38a00'],
            ['label'=>'In Progress','value'=>($summary['queued']+$summary['running']),'icon'=>'fa-spinner','color'=>'var(--sa-accent)'],
            ['label'=>'Failed','value'=>$summary['failed'],'icon'=>'fa-times-circle','color'=>'#CE1126'],
        ];
        @endphp
        @foreach($kpis as $k)
        <div class="rounded-2xl border-2 p-4" style="background:var(--sa-bg);border-color:var(--sa-border)">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-text-muted)">{{ $k['label'] }}</p>
                <i class="fas {{ $k['icon'] }}" style="color:{{ $k['color'] }}"></i>
            </div>
            <p class="text-3xl font-bold" style="color:{{ $k['color'] }}">{{ $k['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Latest deployed release banner --}}
    @if($latest)
    <div class="rounded-2xl border-2 p-5 flex items-center justify-between gap-4"
         style="background:rgba(0,87,184,.05);border-color:rgba(0,87,184,.25)">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-lg text-white"
                 style="background:var(--sa-accent)">
                <i class="fas fa-rocket"></i>
            </div>
            <div>
                <p class="font-bold text-sm" style="color:var(--sa-primary)">
                    Latest Active Release — <span class="font-mono">v{{ $latest->version }}</span>
                    @if($latest->is_deployed)
                        <span class="badge badge-green ml-2"><i class="fas fa-circle text-xs"></i> Deployed</span>
                    @else
                        <span class="badge badge-yellow ml-2"><i class="fas fa-clock text-xs"></i> Not Deployed</span>
                    @endif
                </p>
                <p class="text-xs mt-0.5" style="color:var(--sa-text-muted)">
                    {{ $latest->name }} &bull; Published {{ $latest->published_at?->diffForHumans() }}
                    @if(! $latest->is_deployed)
                        &bull; <span style="color:#b38a00">Open the release to mark it as deployed.</span>
                    @endif
                </p>
            </div>
        </div>
        <a href="{{ route('superadmin.releases.show', $latest) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold text-white"
           style="background:var(--sa-accent)">
            View &amp; Manage
        </a>
    </div>
    @else
    <div class="rounded-2xl border-2 border-dashed p-8 text-center" style="border-color:var(--sa-border)">
        <i class="fas fa-cloud text-3xl mb-3" style="color:var(--sa-text-muted)"></i>
        <p class="font-semibold" style="color:var(--sa-text-muted)">No releases synced yet.</p>
        <p class="text-sm mt-1" style="color:var(--sa-text-muted)">Click "Fetch from GitHub" to pull your releases.</p>
    </div>
    @endif

    {{-- Releases Table --}}
    <div class="rounded-2xl border-2 overflow-hidden" style="background:var(--sa-bg);border-color:var(--sa-border)">
        <div class="px-6 py-4 border-b flex items-center justify-between" style="border-color:var(--sa-border)">
            <h2 class="font-bold" style="color:var(--sa-primary)">All Releases</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid var(--sa-border)">
                        <th class="text-left px-6 py-3 font-semibold" style="color:var(--sa-text-muted)">Version</th>
                        <th class="text-left px-6 py-3 font-semibold" style="color:var(--sa-text-muted)">Name</th>
                        <th class="text-left px-6 py-3 font-semibold" style="color:var(--sa-text-muted)">Published</th>
                        <th class="text-left px-6 py-3 font-semibold" style="color:var(--sa-text-muted)">Status</th>
                        <th class="text-left px-6 py-3 font-semibold" style="color:var(--sa-text-muted)">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($releases as $r)
                <tr class="border-b hover:bg-gray-50 dark:hover:bg-white/5 transition"
                    style="border-color:var(--sa-border)">
                    <td class="px-6 py-4">
                        <span class="font-mono font-bold" style="color:var(--sa-primary)">v{{ $r->version }}</span>
                        @if($r->is_prerelease)
                            <span class="badge badge-yellow ml-1">Pre-release</span>
                        @endif
                    </td>
                    <td class="px-6 py-4" style="color:var(--sa-text)">{{ $r->name }}</td>
                    <td class="px-6 py-4" style="color:var(--sa-text-muted)">
                        {{ $r->published_at?->format('M j, Y') ?? '—' }}
                    </td>
                    <td class="px-6 py-4">
                        @if($r->is_active && $r->is_deployed)
                            <span class="badge badge-green"><i class="fas fa-circle text-xs"></i> Active</span>
                        @elseif($r->is_deployed)
                            <span class="badge badge-blue">Deployed</span>
                        @else
                            <span class="badge badge-gray">Not Deployed</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('superadmin.releases.show', $r) }}"
                           class="text-xs font-semibold px-3 py-1.5 rounded-lg"
                           style="background:rgba(0,87,184,.1);color:var(--sa-accent)">
                            <i class="fas fa-eye mr-1"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center" style="color:var(--sa-text-muted)">
                        No releases found. Fetch from GitHub to populate.
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($releases->hasPages())
        <div class="px-6 py-4 border-t" style="border-color:var(--sa-border)">
            {{ $releases->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const btn      = document.getElementById('fetchBtn');
    const btnIcon  = document.getElementById('fetchBtnIcon');
    const btnText  = document.getElementById('fetchBtnText');
    const toast    = document.getElementById('fetchToast');
    const toastIcon= document.getElementById('fetchToastIcon');
    const toastMsg = document.getElementById('fetchToastMsg');

    let toastTimer = null;

    function showToast(type, message) {
        clearTimeout(toastTimer);

        const isSuccess = type === 'success';
        toast.style.background  = isSuccess ? '#f0fdf4' : '#fff1f2';
        toast.style.border      = isSuccess ? '1.5px solid #bbf7d0' : '1.5px solid #fecdd3';
        toast.style.color       = isSuccess ? '#15803d' : '#be123c';
        toastIcon.className     = 'fas text-lg ' + (isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle');
        toastMsg.textContent    = message;
        toast.style.display     = 'flex';

        toastTimer = setTimeout(() => { toast.style.display = 'none'; }, isSuccess ? 5000 : 7000);
    }

    function setLoading(loading) {
        btn.disabled = loading;
        if (loading) {
            btnIcon.className = 'fas fa-spinner fa-spin';
            btnText.textContent = 'Fetching…';
        } else {
            btnIcon.className = 'fas fa-sync-alt';
            btnText.textContent = 'Fetch from GitHub';
        }
    }

    btn.addEventListener('click', async function () {
        setLoading(true);
        toast.style.display = 'none';

        try {
            const res = await fetch('{{ route('superadmin.releases.fetch') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            });

            const data = await res.json();

            if (res.ok && data.status === 'success') {
                showToast('success', data.message ?? 'Fetch job dispatched!');
                setTimeout(() => window.location.reload(), 4000);
            } else {
                showToast('error', data.message ?? 'Something went wrong.');
                setLoading(false);
            }
        } catch (err) {
            showToast('error', 'Network error — could not reach the server.');
            setLoading(false);
        }
    });
})();
</script>
@endpush
