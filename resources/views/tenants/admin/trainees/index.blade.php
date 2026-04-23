
@extends('layouts.app')

@section('title', 'Trainees Management')

@section('content')
<style>
    /* ══════════════════════════════════════════
       TRAINEE LIST DESIGN TOKENS — TESDA Theme
    ══════════════════════════════════════════ */
    :root {
        --tne-surface:      #ffffff;
        --tne-surface2:     #f0f5ff;
        --tne-border:       #c5d8f5;
        --tne-text:         #001a4d;
        --tne-text-sec:     #1a3a6b;
        --tne-muted:        #5a7aaa;
        --tne-accent:       #0057B8;
        --tne-accent-bg:    #e8f0fb;
        --tne-primary:      #003087;
        --tne-red:          #CE1126;
        --tne-red-bg:       #fff0f2;
    }
    .dark {
        --tne-surface:      #0a1628;
        --tne-surface2:     #0d1f3c;
        --tne-border:       #1e3a6b;
        --tne-text:         #dde8ff;
        --tne-text-sec:     #adc4f0;
        --tne-muted:        #6b8abf;
        --tne-accent-bg:    rgba(0,87,184,0.15);
        --tne-primary:      #5b9cf6;
        --tne-red-bg:       rgba(206,17,38,0.12);
    }
</style>
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--tne-primary);">
                <i class="fas fa-users mr-2" style="color:var(--tne-red);"></i> Trainees Management
            </h1>
            <p class="text-sm mt-1" style="color:var(--tne-muted);">Manage all registered trainees.</p>
        </div>
        <a href="{{ route('admin.trainees.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold shadow transition hover:-translate-y-0.5"
           style="background:linear-gradient(135deg,#CE1126,#A50E1E); box-shadow:0 3px 12px rgba(206,17,38,0.28);">
            <i class="fas fa-plus"></i> Add Trainee
        </a>
    </div>

    @if (session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
             style="background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @php
        $tenant = tenancy()->tenant;
        $plan   = $tenant->subscription;
        $limit  = \App\Helpers\SubscriptionHelper::getLimit($plan, 'trainees');
        $count  = \App\Models\User::where('role', 'trainee')->count();
        $atLimit = $limit !== null && $count >= $limit;
    @endphp

    @if($atLimit)
        <div style="background:#fff7ed; border:1.5px solid #fed7aa; border-radius:12px; padding:14px 18px; margin-bottom:16px; display:flex; align-items:center; gap:12px;">
            <i class="fas fa-exclamation-triangle" style="color:#f97316; font-size:18px;"></i>
            <div>
                <strong style="color:#9a3412;">Trainee limit reached</strong>
                <span style="color:#c2410c; font-size:13px;"> — You have {{ $count }}/{{ $limit }} trainees on your {{ ucfirst($plan) }} plan.</span>
                <a href="{{ route('admin.subscription.index') }}" style="color:#0057B8; font-weight:600; font-size:13px; margin-left:8px;">
                    Upgrade to add more →
                </a>
            </div>
        </div>
    @else
        @if($limit !== null)
            <div style="font-size:12px; color:#5a7aaa; margin-bottom:12px;">
                <i class="fas fa-users"></i> {{ $count }} / {{ $limit }} trainees used on your {{ ucfirst($plan) }} plan.
            </div>
        @endif
    @endif

    <div class="rounded-2xl border p-5"
         style="background:var(--tne-surface); border-color:var(--tne-border);">
        <form method="GET" action="{{ route('admin.trainees.index') }}"
              class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:var(--tne-muted);"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name or email..."
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border text-sm outline-none transition"
                       style="border-color:var(--tne-border); color:var(--tne-text);"
                       onfocus="this.style.borderColor='#0057B8'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='var(--tne-border)'; this.style.boxShadow='none'">
            </div>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-white text-sm font-bold transition hover:-translate-y-0.5"
                    style="background:linear-gradient(135deg,#0057B8,#003087);">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if (request()->filled('search'))
                <a href="{{ route('admin.trainees.index') }}"
                   class="px-4 py-2.5 rounded-xl text-sm font-semibold border transition"
                   style="border-color:var(--tne-border); color:var(--tne-muted);">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--tne-surface); border-color:var(--tne-border);">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--tne-accent-bg); border-bottom:1px solid var(--tne-border);">
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--tne-accent);">#</th>
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--tne-accent);">Trainee</th>
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--tne-accent);">Enrollments</th>
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--tne-accent);">Date Joined</th>
                        <th class="px-5 py-3 text-center text-xs font-700 uppercase tracking-wide" style="color:var(--tne-accent);">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--tne-border);">
                    @forelse ($trainees as $trainee)
                        <tr style="background:var(--tne-surface);">
                            <td class="px-5 py-4 font-mono text-xs" style="color:var(--tne-muted);">
                                {{ $trainees->firstItem() + $loop->index }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-800 text-white flex-shrink-0"
                                         style="background:linear-gradient(135deg,#CE1126,#A50E1E);">
                                        {{ strtoupper(substr($trainee->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-700" style="color:var(--tne-text);">{{ $trainee->name }}</div>
                                        <div class="text-xs" style="color:var(--tne-muted);">{{ $trainee->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-600"
                                      style="background:var(--tne-accent-bg); color:var(--tne-accent); border:1px solid var(--tne-border);">
                                    {{ $trainee->enrollments_count ?? $trainee->enrollments()->count() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs" style="color:var(--tne-muted);">
                                {{ $trainee->created_at?->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.trainees.show', $trainee) }}"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                       style="background:var(--tne-accent-bg); color:var(--tne-accent);" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.trainees.edit', $trainee) }}"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                       style="background:rgba(245,197,24,0.15); color:#b38a00;" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.trainees.destroy', $trainee) }}"
                                          onsubmit="return confirm('Delete trainee {{ addslashes($trainee->name) }}? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                                style="background:var(--tne-red-bg); color:var(--tne-red);" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3" style="color:var(--tne-muted);">
                                    <i class="fas fa-users text-4xl opacity-25"></i>
                                    <p class="font-600">No trainees found</p>
                                    <p class="text-xs">Try adjusting your search or add a new trainee.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($trainees->hasPages())
            <div class="px-5 py-4 border-t" style="border-color:var(--tne-border);">
                {{ $trainees->links() }}
            </div>
        @endif
    </div>

</div>
@endsection