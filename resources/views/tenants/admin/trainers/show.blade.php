@extends('layouts.app')

@section('title', $trainer->name)

@section('content')
<style>
    /* ══════════════════════════════════════════
       TRAINER DETAIL DESIGN TOKENS — TESDA Theme
    ══════════════════════════════════════════ */
    :root {
        --tr-surface:      #ffffff;
        --tr-surface2:     #f0f5ff;
        --tr-border:       #c5d8f5;
        --tr-text:         #001a4d;
        --tr-text-sec:     #1a3a6b;
        --tr-muted:        #5a7aaa;
        --tr-accent:       #0057B8;
        --tr-accent-bg:    #e8f0fb;
        --tr-primary:      #003087;
        --tr-red:          #CE1126;
        --tr-red-bg:       #fff0f2;
    }
    .dark {
        --tr-surface:      #0a1628;
        --tr-surface2:     #0d1f3c;
        --tr-border:       #1e3a6b;
        --tr-text:         #dde8ff;
        --tr-text-sec:     #adc4f0;
        --tr-muted:        #6b8abf;
        --tr-accent-bg:    rgba(0,87,184,0.15);
        --tr-primary:      #5b9cf6;
        --tr-red-bg:       rgba(206,17,38,0.12);
    }
</style>
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.trainers.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition"
           style="border-color:var(--tr-border); color:var(--tr-muted);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--tr-primary);">
                <i class="fas fa-chalkboard-teacher mr-2" style="color:var(--tr-red);"></i> Trainer Profile
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--tr-muted);">Viewing details for {{ $trainer->name }}</p>
        </div>
    </div>

    {{-- Profile Card --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--tr-surface); border-color:var(--tr-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        {{-- Profile hero --}}
        <div class="p-8 flex flex-col sm:flex-row items-start sm:items-center gap-6"
             style="background: linear-gradient(135deg, #003087 0%, #0057B8 100%); position:relative; overflow:hidden;">
            <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,0.05);"></div>
            <div style="position:absolute;bottom:-40px;left:-20px;width:120px;height:120px;border-radius:50%;background:rgba(245,197,24,0.07);"></div>

            <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl font-900 text-white flex-shrink-0"
                 style="background:rgba(255,255,255,0.15); border:2px solid rgba(255,255,255,0.20); position:relative; z-index:1;">
                {{ strtoupper(substr($trainer->name, 0, 1)) }}
            </div>

            <div style="position:relative;z-index:1;">
                <div class="text-xl font-800 text-white">{{ $trainer->name }}</div>
                <div class="text-sm mt-0.5" style="color:rgba(255,255,255,0.65);">{{ $trainer->email }}</div>
                <div class="flex flex-wrap gap-2 mt-3">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-700"
                          style="background:rgba(245,197,24,0.15); border:1px solid rgba(245,197,24,0.30); color:#F5C518;">
                        <i class="fas fa-shield-halved mr-1" style="font-size:9px;"></i> Trainer
                    </span>
                </div>
            </div>

            <div class="sm:ml-auto flex gap-2" style="position:relative;z-index:1;">
                <a href="{{ route('admin.trainers.edit', $trainer) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-700 transition hover:-translate-y-0.5"
                   style="background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.22); color:#fff;">
                    <i class="fas fa-pen text-xs"></i> Edit
                </a>
            </div>
        </div>

        {{-- Detail rows --}}
        <div class="p-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
            @php
                $details = [
                    ['icon' => 'fa-envelope', 'color' => '#CE1126', 'bg' => '#fff0f2', 'label' => 'Email Address', 'value' => $trainer->email],
                    ['icon' => 'fa-calendar', 'color' => '#5a7aaa', 'bg' => '#f0f5ff', 'label' => 'Joined',        'value' => $trainer->created_at?->format('F d, Y')],
                ];
            @endphp

            @foreach ($details as $d)
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs flex-shrink-0"
                         style="background:{{ $d['bg'] }}; color:{{ $d['color'] }};">
                        <i class="fas {{ $d['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="text-xs font-700 uppercase tracking-wide mb-0.5" style="color:var(--tr-muted);">{{ $d['label'] }}</div>
                        <div class="text-sm font-600" style="color:var(--tr-text);">{{ $d['value'] ?? '—' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-2 gap-4">
        @php
            $stats = [
                ['label' => 'Assessments Given', 'value' => $trainer->assessments->count(), 'icon' => 'fa-clipboard-check', 'color' => '#0057B8', 'bg' => '#e8f0fb'],
                ['label' => 'Schedules',          'value' => $trainer->schedules->count(),   'icon' => 'fa-chalkboard',      'color' => '#CE1126', 'bg' => '#fff0f2'],
            ];
        @endphp
        @foreach ($stats as $stat)
            <div class="rounded-2xl border p-5 text-center"
                 style="background:var(--tr-surface); border-color:var(--tr-border);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center mx-auto mb-3 text-sm"
                     style="background:{{ $stat['bg'] }}; color:{{ $stat['color'] }};">
                    <i class="fas {{ $stat['icon'] }}"></i>
                </div>
                <div class="text-2xl font-800" style="color:var(--tr-text);">{{ $stat['value'] }}</div>
                <div class="text-xs mt-0.5" style="color:var(--tr-muted);">{{ $stat['label'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Danger zone --}}
    <div class="rounded-2xl border p-6"
         style="background:var(--tr-surface); border-color:#f5c5cb;">
        <h3 class="text-sm font-800 mb-1 flex items-center gap-2" style="color:#CE1126;">
            <i class="fas fa-triangle-exclamation"></i> Danger Zone
        </h3>
        <p class="text-xs mb-4" style="color:var(--tr-muted);">Deleting this trainer is permanent and cannot be undone.</p>
        <form method="POST" action="{{ route('admin.trainers.destroy', $trainer) }}"
              onsubmit="return confirm('Permanently delete {{ addslashes($trainer->name) }}?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                    style="background:linear-gradient(135deg,#CE1126,#A50E1E); box-shadow:0 3px 12px rgba(206,17,38,0.25);">
                <i class="fas fa-trash"></i> Delete Trainer
            </button>
        </form>
    </div>

</div>
@endsection