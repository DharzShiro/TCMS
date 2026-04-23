@extends('layouts.app')

@section('title', 'Trainer Dashboard')

@section('content')
<style>
    /* ══════════════════════════════════════════
       DASHBOARD DESIGN TOKENS — TESDA Theme
    ══════════════════════════════════════════ */
    :root {
        --db-surface:      #ffffff;
        --db-surface2:     #f0f5ff;
        --db-border:       #c5d8f5;
        --db-text:         #001a4d;
        --db-text-sec:     #1a3a6b;
        --db-muted:        #5a7aaa;
        --db-accent:       #0057B8;
        --db-accent-bg:    #e8f0fb;
        --db-primary:      #003087;
        --db-red:          #CE1126;
        --db-red-bg:       #fff0f2;
        --db-green:        #16a34a;
        --db-green-bg:     #f0fdf4;
        --db-gold:         #b38a00;
        --db-gold-bg:      rgba(245,197,24,0.12);
    }
    .dark {
        --db-surface:      #0a1628;
        --db-surface2:     #0d1f3c;
        --db-border:       #1e3a6b;
        --db-text:         #dde8ff;
        --db-text-sec:     #adc4f0;
        --db-muted:        #6b8abf;
        --db-accent-bg:    rgba(0,87,184,0.15);
        --db-primary:      #5b9cf6;
        --db-red-bg:       rgba(206,17,38,0.12);
        --db-green-bg:     rgba(22,163,74,0.12);
        --db-gold-bg:      rgba(245,197,24,0.08);
    }
</style>

<div class="space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold" style="color:var(--db-primary);">
            <i class="fas fa-chalkboard mr-2" style="color:var(--db-red);"></i> Trainer Dashboard
        </h1>
        <p class="text-sm mt-1" style="color:var(--db-muted);">
            Welcome back, <span class="font-700" style="color:var(--db-accent);">{{ Auth::user()->name }}</span>!
            Here's an overview of your training activities.
        </p>
    </div>

    {{-- Stats Cards Row 1 --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $stats = [
                [
                    'label' => 'Total Trainees',
                    'value' => $totalTrainees,
                    'icon'  => 'fa-users',
                    'color' => '#0057B8',
                    'bg'    => '#e8f0fb',
                ],
                [
                    'label' => 'Upcoming Schedules',
                    'value' => $upcomingSchedules->count(),
                    'icon'  => 'fa-calendar-clock',
                    'color' => '#b38a00',
                    'bg'    => 'rgba(245,197,24,0.12)',
                ],
                [
                    'label' => 'Ongoing Courses',
                    'value' => $ongoingSchedules->count(),
                    'icon'  => 'fa-book-open',
                    'color' => '#16a34a',
                    'bg'    => '#f0fdf4',
                ],
                [
                    'label' => 'Attendance Records',
                    'value' => $totalAttendance,
                    'icon'  => 'fa-clipboard-list',
                    'color' => '#CE1126',
                    'bg'    => '#fff0f2',
                ],
            ];
        @endphp

        @foreach ($stats as $stat)
            <div class="rounded-2xl border p-5 transition hover:-translate-y-0.5 hover:shadow-md"
                 style="background:var(--db-surface); border-color:var(--db-border);">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm"
                         style="background:{{ $stat['bg'] }}; color:{{ $stat['color'] }};">
                        <i class="fas {{ $stat['icon'] }}"></i>
                    </div>
                </div>
                <div class="text-2xl font-800" style="color:var(--db-text);">{{ $stat['value'] }}</div>
                <div class="text-xs mt-0.5 font-600" style="color:var(--db-muted);">{{ $stat['label'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Second Row - Status Cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Attendance Status --}}
        <div class="rounded-2xl border p-5"
             style="background:var(--db-surface); border-color:var(--db-border);">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-700" style="color:var(--db-primary);">Attendance Status</h3>
                <i class="fas fa-clipboard-check text-xs" style="color:var(--db-muted);"></i>
            </div>
            <div class="space-y-3">
                @php
                    $attendanceStats = [
                        ['label' => 'Present',   'value' => $presentCount,   'color' => '#16a34a', 'bg' => '#f0fdf4'],
                        ['label' => 'Late',      'value' => $lateCount,      'color' => '#b38a00', 'bg' => 'rgba(245,197,24,0.12)'],
                        ['label' => 'Absent',    'value' => $absentCount,    'color' => '#CE1126', 'bg' => '#fff0f2'],
                        ['label' => 'Total',     'value' => $totalAttendance, 'color' => '#0057B8', 'bg' => '#e8f0fb'],
                    ];
                @endphp
                @foreach ($attendanceStats as $a)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full" style="background:{{ $a['color'] }};"></div>
                            <span class="text-xs font-600" style="color:var(--db-muted);">{{ $a['label'] }}</span>
                        </div>
                        <span class="text-xs font-700 px-2 py-0.5 rounded-lg"
                              style="background:{{ $a['bg'] }}; color:{{ $a['color'] }};">
                            {{ $a['value'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Training Schedules --}}
        <div class="rounded-2xl border p-5"
             style="background:var(--db-surface); border-color:var(--db-border);">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-700" style="color:var(--db-primary);">Training Schedules</h3>
                <i class="fas fa-calendar text-xs" style="color:var(--db-muted);"></i>
            </div>
            <div class="space-y-3">
                @php
                    $scheduleStats = [
                        ['label' => 'Upcoming',  'value' => $upcomingSchedules->count(), 'color' => '#0057B8', 'bg' => '#e8f0fb'],
                        ['label' => 'Ongoing',   'value' => $ongoingSchedules->count(),  'color' => '#16a34a', 'bg' => '#f0fdf4'],
                    ];
                @endphp
                @foreach ($scheduleStats as $s)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full" style="background:{{ $s['color'] }};"></div>
                            <span class="text-xs font-600" style="color:var(--db-muted);">{{ $s['label'] }}</span>
                        </div>
                        <span class="text-xs font-700 px-2 py-0.5 rounded-lg"
                              style="background:{{ $s['bg'] }}; color:{{ $s['color'] }};">
                            {{ $s['value'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Assessment Results --}}
        <div class="rounded-2xl border p-5"
             style="background:var(--db-surface); border-color:var(--db-border);">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-700" style="color:var(--db-primary);">Assessment Results</h3>
                <i class="fas fa-tasks text-xs" style="color:var(--db-muted);"></i>
            </div>
            <div class="space-y-3">
                @php
                    $assessmentStats = [
                        ['label' => 'Competent',         'value' => $competentCount,       'color' => '#16a34a', 'bg' => '#f0fdf4'],
                        ['label' => 'Not Yet Competent', 'value' => $notYetCompetentCount, 'color' => '#CE1126', 'bg' => '#fff0f2'],
                        ['label' => 'Total Assessed',    'value' => $totalAssessments,     'color' => '#0057B8', 'bg' => '#e8f0fb'],
                    ];
                @endphp
                @foreach ($assessmentStats as $a)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full" style="background:{{ $a['color'] }};"></div>
                            <span class="text-xs font-600" style="color:var(--db-muted);">{{ $a['label'] }}</span>
                        </div>
                        <span class="text-xs font-700 px-2 py-0.5 rounded-lg"
                              style="background:{{ $a['bg'] }}; color:{{ $a['color'] }};">
                            {{ $a['value'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- Upcoming Schedule Cards --}}
    @if($upcomingSchedules->count() > 0)
    <div>
        <h3 class="text-lg font-700 mb-4" style="color:var(--db-primary);">
            <i class="fas fa-calendar-alt mr-2" style="color:var(--db-gold);"></i> Upcoming Schedules
        </h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($upcomingSchedules as $schedule)
                <div class="rounded-2xl border p-5"
                     style="background:var(--db-surface); border-color:var(--db-border);">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h4 class="font-700 text-sm" style="color:var(--db-text);">{{ $schedule->course->name }}</h4>
                            <p class="text-xs mt-1" style="color:var(--db-muted);">{{ $schedule->course->code ?? 'N/A' }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-lg text-xs font-600"
                              style="background:rgba(0,87,184,0.12); color:#0057B8;">
                            {{ ucfirst($schedule->status) }}
                        </span>
                    </div>
                    <div class="space-y-2 text-xs" style="color:var(--db-text-sec);">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar-days" style="color:var(--db-gold); width:16px;"></i>
                            <span>{{ $schedule->start_date?->format('M d, Y') }} - {{ $schedule->end_date?->format('M d, Y') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-clock" style="color:var(--db-accent); width:16px;"></i>
                            <span>{{ $schedule->start_time }} - {{ $schedule->end_time }}</span>
                        </div>
                        @if($schedule->location)
                        <div class="flex items-center gap-2">
                            <i class="fas fa-map-marker-alt" style="color:var(--db-red); width:16px;"></i>
                            <span>{{ $schedule->location }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recent Attendance Records --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--db-surface); border-color:var(--db-border);">
        <div class="px-5 py-4 border-b flex items-center justify-between"
             style="border-color:var(--db-border);">
            <h3 class="text-sm font-700" style="color:var(--db-primary);">
                <i class="fas fa-history mr-1" style="color:var(--db-red);"></i> Recent Attendance Records
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--db-accent-bg); border-bottom:1px solid var(--db-border);">
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--db-accent);">Trainee</th>
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--db-accent);">Course</th>
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--db-accent);">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--db-accent);">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--db-border);">
                    @forelse ($recentAttendance as $attendance)
                        <tr class="transition" style="background:var(--db-surface);">
                            <td class="px-5 py-3">
                                <div class="font-600" style="color:var(--db-text);">{{ $attendance->enrollment->trainee->name }}</div>
                                <div class="text-xs" style="color:var(--db-muted);">{{ $attendance->enrollment->trainee->email }}</div>
                            </td>
                            <td class="px-5 py-3 text-xs font-600" style="color:var(--db-text-sec);">
                                {{ $attendance->enrollment->course->name }}
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $statusStyles = [
                                        'present' => ['bg' => '#f0fdf4',               'color' => '#16a34a'],
                                        'absent'  => ['bg' => '#fff0f2',               'color' => '#CE1126'],
                                        'late'    => ['bg' => 'rgba(245,197,24,0.12)', 'color' => '#b38a00'],
                                    ];
                                    $style = $statusStyles[$attendance->status] ?? ['bg' => '#f0f5ff', 'color' => '#5a7aaa'];
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg text-xs font-600"
                                      style="background:{{ $style['bg'] }}; color:{{ $style['color'] }};">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-xs" style="color:var(--db-muted);">
                                {{ $attendance->date?->format('M d, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-xs" style="color:var(--db-muted);">
                                No attendance records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
