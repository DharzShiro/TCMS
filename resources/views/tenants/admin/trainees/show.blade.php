@extends('layouts.app')

@section('title', $trainee->name)

@section('content')
<style>
    /* ══════════════════════════════════════════
       TRAINEE DETAIL DESIGN TOKENS — TESDA Theme
    ══════════════════════════════════════════ */
    :root {
        --tnd-surface:      #ffffff;
        --tnd-surface2:     #f0f5ff;
        --tnd-border:       #c5d8f5;
        --tnd-text:         #001a4d;
        --tnd-text-sec:     #1a3a6b;
        --tnd-muted:        #5a7aaa;
        --tnd-accent:       #0057B8;
        --tnd-accent-bg:    #e8f0fb;
        --tnd-primary:      #003087;
        --tnd-red:          #CE1126;
        --tnd-red-bg:       #fff0f2;
    }
    .dark {
        --tnd-surface:      #0a1628;
        --tnd-surface2:     #0d1f3c;
        --tnd-border:       #1e3a6b;
        --tnd-text:         #dde8ff;
        --tnd-text-sec:     #adc4f0;
        --tnd-muted:        #6b8abf;
        --tnd-accent-bg:    rgba(0,87,184,0.15);
        --tnd-primary:      #5b9cf6;
        --tnd-red-bg:       rgba(206,17,38,0.12);
    }
</style>
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center gap-4">
        <a href="{{ route('admin.trainees.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition"
           style="border-color:var(--tnd-border); color:var(--tnd-muted);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--tnd-primary);">
                <i class="fas fa-user mr-2" style="color:var(--tnd-red);"></i> Trainee Profile
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--tnd-muted);">Viewing details for {{ $trainee->name }}</p>
        </div>
    </div>

    {{-- Profile Card --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--tnd-surface); border-color:var(--tnd-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        <div class="p-8 flex flex-col sm:flex-row items-start sm:items-center gap-6"
             style="background:linear-gradient(135deg,#CE1126 0%,#A50E1E 100%); position:relative; overflow:hidden;">
            <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,0.05);"></div>
            <div style="position:absolute;bottom:-40px;left:-20px;width:120px;height:120px;border-radius:50%;background:rgba(0,48,135,0.10);"></div>

            <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl font-900 text-white flex-shrink-0"
                 style="background:rgba(255,255,255,0.15); border:2px solid rgba(255,255,255,0.20); position:relative; z-index:1;">
                {{ strtoupper(substr($trainee->name, 0, 1)) }}
            </div>

            <div style="position:relative;z-index:1;">
                <div class="text-xl font-800 text-white">{{ $trainee->name }}</div>
                <div class="text-sm mt-0.5" style="color:rgba(255,255,255,0.65);">{{ $trainee->email }}</div>
                <div class="flex flex-wrap gap-2 mt-3">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-700"
                          style="background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.25); color:#fff;">
                        <i class="fas fa-user-graduate mr-1" style="font-size:9px;"></i> Trainee
                    </span>
                </div>
            </div>

            <div class="sm:ml-auto flex gap-2" style="position:relative;z-index:1;">
                <a href="{{ route('admin.trainees.edit', $trainee) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-700 transition hover:-translate-y-0.5"
                   style="background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.22); color:#fff;">
                    <i class="fas fa-pen text-xs"></i> Edit
                </a>
            </div>
        </div>

        <div class="p-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
            @php
                $details = [
                    ['icon' => 'fa-envelope', 'color' => '#CE1126', 'bg' => '#fff0f2', 'label' => 'Email Address', 'value' => $trainee->email],
                    ['icon' => 'fa-calendar',  'color' => '#5a7aaa', 'bg' => '#f0f5ff', 'label' => 'Joined',        'value' => $trainee->created_at?->format('F d, Y')],
                ];
            @endphp
            @foreach ($details as $d)
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs flex-shrink-0"
                         style="background:{{ $d['bg'] }}; color:{{ $d['color'] }};">
                        <i class="fas {{ $d['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="text-xs font-700 uppercase tracking-wide mb-0.5" style="color:var(--tnd-muted);">{{ $d['label'] }}</div>
                        <div class="text-sm font-600" style="color:var(--tnd-text);">{{ $d['value'] ?? '—' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4">
        @php
            $stats = [
                ['label' => 'Total Enrollments', 'value' => $trainee->enrollments->count(),                                             'icon' => 'fa-file-signature',  'color' => '#0057B8', 'bg' => '#e8f0fb'],
                ['label' => 'Completed Courses',  'value' => $trainee->enrollments->where('status', 'completed')->count(),               'icon' => 'fa-circle-check',    'color' => '#16a34a', 'bg' => '#f0fdf4'],
            ];
        @endphp
        @foreach ($stats as $stat)
            <div class="rounded-2xl border p-5 text-center"
                 style="background:var(--tnd-surface); border-color:var(--tnd-border);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center mx-auto mb-3 text-sm"
                     style="background:{{ $stat['bg'] }}; color:{{ $stat['color'] }};">
                    <i class="fas {{ $stat['icon'] }}"></i>
                </div>
                <div class="text-2xl font-800" style="color:var(--tnd-text);">{{ $stat['value'] }}</div>
                <div class="text-xs mt-0.5" style="color:var(--tnd-muted);">{{ $stat['label'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Enrollments Table --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--tnd-surface); border-color:var(--tnd-border);">
        <div class="px-5 py-4 border-b" style="border-color:var(--tnd-border);">
            <h3 class="text-sm font-700" style="color:var(--tnd-primary);">
                <i class="fas fa-list mr-1" style="color:var(--tnd-red);"></i> Enrollment History
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--tnd-accent-bg); border-bottom:1px solid var(--tnd-border);">
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--tnd-accent);">Course</th>
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--tnd-accent);">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--tnd-accent);">Enrolled At</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--tnd-border);">
                    @forelse ($trainee->enrollments as $enrollment)
                        <tr style="background:var(--tnd-surface);">
                            <td class="px-5 py-3 font-600" style="color:var(--tnd-text);">
                                {{ $enrollment->course->name }}
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $statusStyles = [
                                        'pending'   => ['bg' => 'rgba(245,197,24,0.12)', 'color' => '#b38a00'],
                                        'approved'  => ['bg' => '#f0fdf4',               'color' => '#16a34a'],
                                        'completed' => ['bg' => '#e8f0fb',               'color' => '#0057B8'],
                                        'dropped'   => ['bg' => '#fff0f2',               'color' => '#CE1126'],
                                    ];
                                    $style = $statusStyles[$enrollment->status] ?? ['bg' => '#f0f5ff', 'color' => '#5a7aaa'];
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg text-xs font-600"
                                      style="background:{{ $style['bg'] }}; color:{{ $style['color'] }};">
                                    {{ ucfirst($enrollment->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-xs" style="color:var(--tnd-muted);">
                                {{ $enrollment->enrolled_at?->format('M d, Y') ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-10 text-center text-xs" style="color:var(--tnd-muted);">
                                No enrollments yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Danger Zone --}}
    <div class="rounded-2xl border p-6"
         style="background:var(--tnd-surface); border-color:#f5c5cb;">
        <h3 class="text-sm font-800 mb-1 flex items-center gap-2" style="color:#CE1126;">
            <i class="fas fa-triangle-exclamation"></i> Danger Zone
        </h3>
        <p class="text-xs mb-4" style="color:var(--tnd-muted);">Deleting this trainee is permanent and cannot be undone.</p>
        <form method="POST" action="{{ route('admin.trainees.destroy', $trainee) }}"
              onsubmit="return confirm('Permanently delete {{ addslashes($trainee->name) }}?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                    style="background:linear-gradient(135deg,#CE1126,#A50E1E); box-shadow:0 3px 12px rgba(206,17,38,0.25);">
                <i class="fas fa-trash"></i> Delete Trainee
            </button>
        </form>
    </div>

</div>
@endsection