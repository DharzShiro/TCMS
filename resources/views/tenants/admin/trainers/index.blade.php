@extends('layouts.app')

@section('title', 'Trainers Management')

@section('content')
<style>
    /* ══════════════════════════════════════════
       TRAINER LIST DESIGN TOKENS — TESDA Theme
    ══════════════════════════════════════════ */
    :root {
        --trl-surface:      #ffffff;
        --trl-surface2:     #f0f5ff;
        --trl-border:       #c5d8f5;
        --trl-text:         #001a4d;
        --trl-text-sec:     #1a3a6b;
        --trl-muted:        #5a7aaa;
        --trl-accent:       #0057B8;
        --trl-accent-bg:    #e8f0fb;
        --trl-primary:      #003087;
        --trl-red:          #CE1126;
        --trl-red-bg:       #fff0f2;
    }
    .dark {
        --trl-surface:      #0a1628;
        --trl-surface2:     #0d1f3c;
        --trl-border:       #1e3a6b;
        --trl-text:         #dde8ff;
        --trl-text-sec:     #adc4f0;
        --trl-muted:        #6b8abf;
        --trl-accent-bg:    rgba(0,87,184,0.15);
        --trl-primary:      #5b9cf6;
        --trl-red-bg:       rgba(206,17,38,0.12);
    }
</style>
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--trl-primary);">
                <i class="fas fa-chalkboard-teacher mr-2" style="color:var(--trl-red);"></i>
                Trainers Management
            </h1>
            <p class="text-sm mt-1" style="color:var(--trl-muted);">
                Manage all registered trainers and assessors across training centers.
            </p>
        </div>
        <a href="{{ route('admin.trainers.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold shadow transition hover:-translate-y-0.5"
           style="background: linear-gradient(135deg,#CE1126,#A50E1E); box-shadow:0 3px 12px rgba(206,17,38,0.28);">
            <i class="fas fa-plus"></i> Add Trainer
        </a>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
             style="background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @php
        $tenant = tenancy()->tenant;
        $plan   = $tenant->subscription;
        $limit  = \App\Helpers\SubscriptionHelper::getLimit($plan, 'trainers');
        $count  = \App\Models\User::where('role', 'trainer')->count();
        $atLimit = $limit !== null && $count >= $limit;
    @endphp

    @if($atLimit)
        <div style="background:#fff7ed; border:1.5px solid #fed7aa; border-radius:12px; padding:14px 18px; margin-bottom:16px; display:flex; align-items:center; gap:12px;">
            <i class="fas fa-exclamation-triangle" style="color:#f97316; font-size:18px;"></i>
            <div>
                <strong style="color:#9a3412;">Trainee limit reached</strong>
                <span style="color:#c2410c; font-size:13px;"> — You have {{ $count }}/{{ $limit }} trainers on your {{ ucfirst($plan) }} plan.</span>
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

    {{-- Filters --}}
    <div class="rounded-2xl border p-5"
         style="background:var(--trl-surface); border-color:var(--trl-border);">
        <form method="GET" action="{{ route('admin.trainers.index') }}"
              class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:var(--trl-muted);"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name or email..."
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border text-sm outline-none transition"
                       style="border-color:var(--trl-border); color:var(--trl-text);"
                       onfocus="this.style.borderColor='#0057B8'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='var(--trl-border)'; this.style.boxShadow='none'">
            </div>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-white text-sm font-bold transition hover:-translate-y-0.5"
                    style="background:linear-gradient(135deg,#0057B8,#003087);">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if (request()->filled('search'))
                <a href="{{ route('admin.trainers.index') }}"
                   class="px-4 py-2.5 rounded-xl text-sm font-semibold border transition"
                   style="border-color:var(--trl-border); color:var(--trl-muted);">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--trl-surface); border-color:var(--trl-border);">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--trl-accent-bg); border-bottom:1px solid var(--trl-border);">
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--trl-accent);">#</th>
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--trl-accent);">Trainer</th>
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--trl-accent);">Date Joined</th>
                        <th class="px-5 py-3 text-center font-700 text-xs uppercase tracking-wide" style="color:var(--trl-accent);">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--trl-border);">
                    @forelse ($trainers as $trainer)
                        <tr style="background:var(--trl-surface);">
                            <td class="px-5 py-4 font-mono text-xs" style="color:var(--trl-muted);">
                                {{ $trainers->firstItem() + $loop->index }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-800 text-white flex-shrink-0"
                                         style="background:linear-gradient(135deg,#0057B8,#003087);">
                                        {{ strtoupper(substr($trainer->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-700" style="color:var(--trl-text);">{{ $trainer->name }}</div>
                                        <div class="text-xs" style="color:var(--trl-muted);">{{ $trainer->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-xs" style="color:var(--trl-muted);">
                                {{ $trainer->created_at?->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.trainers.show', $trainer) }}"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                       style="background:var(--trl-accent-bg); color:var(--trl-accent);" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.trainers.edit', $trainer) }}"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                       style="background:rgba(245,197,24,0.15); color:#b38a00;" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.trainers.destroy', $trainer) }}"
                                          onsubmit="return confirm('Delete trainer {{ addslashes($trainer->name) }}? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                                style="background:var(--trl-red-bg); color:var(--trl-red);" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3" style="color:var(--trl-muted);">
                                    <i class="fas fa-chalkboard-teacher text-4xl opacity-25"></i>
                                    <p class="font-600">No trainers found</p>
                                    <p class="text-xs">Try adjusting your search or filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($trainers->hasPages())
            <div class="px-5 py-4 border-t" style="border-color:var(--trl-border);">
                {{ $trainers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection