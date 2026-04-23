@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<style>
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
            <i class="fas fa-chart-line mr-2" style="color:var(--db-red);"></i> My Dashboard
        </h1>
        <p class="text-sm mt-1" style="color:var(--db-muted);">
            Welcome back, <span class="font-semibold" style="color:var(--db-accent);">{{ Auth::user()->name }}</span>!
            Here's an overview of your training progress.
        </p>
    </div>

    {{-- Stats Cards Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        @php
            $stats = [
                [
                    'label' => 'Total Enrollments',
                    'value' => $pendingCount + $approvedCount + $completedCount + $droppedCount,
                    'icon'  => 'fa-file-signature',
                    'color' => '#0057B8',
                    'bg'    => '#e8f0fb',
                    'route' => route('trainee.enrollments.index'),
                ],
                [
                    'label' => 'Assessments',
                    'value' => $competentCount + $notYetCompetentCount,
                    'icon'  => 'fa-clipboard-check',
                    'color' => '#b38a00',
                    'bg'    => 'rgba(245,197,24,0.12)',
                    'route' => route('trainee.assessments.index'),
                ],
                [
                    'label' => 'Certificates',
                    'value' => $certificatesCount,
                    'icon'  => 'fa-certificate',
                    'color' => '#CE1126',
                    'bg'    => '#fff0f2',
                    'route' => route('trainee.certificates.index'),
                ],
            ];
        @endphp

        @foreach ($stats as $stat)
            <a href="{{ $stat['route'] }}"
               class="rounded-2xl border p-5 transition hover:-translate-y-0.5 hover:shadow-md"
               style="background:var(--db-surface); border-color:var(--db-border);">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm"
                         style="background:{{ $stat['bg'] }}; color:{{ $stat['color'] }};">
                        <i class="fas {{ $stat['icon'] }}"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold" style="color:var(--db-text);">{{ $stat['value'] }}</div>
                <div class="text-xs mt-0.5 font-semibold" style="color:var(--db-muted);">{{ $stat['label'] }}</div>
            </a>
        @endforeach
    </div>

    {{-- Status Breakdown Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Enrollment Status --}}
        <div class="rounded-2xl border p-5"
             style="background:var(--db-surface); border-color:var(--db-border);">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold" style="color:var(--db-primary);">
                    <i class="fas fa-file-signature mr-1" style="color:var(--db-muted);"></i>
                    Enrollment Status
                </h3>
                <a href="{{ route('trainee.enrollments.index') }}"
                   class="text-xs font-medium transition"
                   style="color:var(--db-accent);">
                    View all <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="space-y-3">
                @php
                    $enrollmentStats = [
                        ['label' => 'Approved',  'value' => $approvedCount,  'color' => '#16a34a', 'bg' => '#f0fdf4'],
                        ['label' => 'Pending',   'value' => $pendingCount,   'color' => '#b38a00', 'bg' => 'rgba(245,197,24,0.12)'],
                        ['label' => 'Completed', 'value' => $completedCount, 'color' => '#0057B8', 'bg' => '#e8f0fb'],
                        ['label' => 'Dropped',   'value' => $droppedCount,   'color' => '#CE1126', 'bg' => '#fff0f2'],
                    ];
                @endphp
                @foreach ($enrollmentStats as $e)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full" style="background:{{ $e['color'] }};"></div>
                            <span class="text-xs font-medium" style="color:var(--db-muted);">{{ $e['label'] }}</span>
                        </div>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-lg"
                              style="background:{{ $e['bg'] }}; color:{{ $e['color'] }};">
                            {{ $e['value'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Assessment Results --}}
        <div class="rounded-2xl border p-5"
             style="background:var(--db-surface); border-color:var(--db-border);">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold" style="color:var(--db-primary);">
                    <i class="fas fa-tasks mr-1" style="color:var(--db-muted);"></i>
                    Assessment Results
                </h3>
                <a href="{{ route('trainee.assessments.index') }}"
                   class="text-xs font-medium transition"
                   style="color:var(--db-accent);">
                    View all <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            @if(($competentCount + $notYetCompetentCount) > 0)
                <div class="space-y-3">
                    @php
                        $assessmentStats = [
                            ['label' => 'Competent',         'value' => $competentCount,                          'color' => '#16a34a', 'bg' => '#f0fdf4'],
                            ['label' => 'Not Yet Competent', 'value' => $notYetCompetentCount,                    'color' => '#CE1126', 'bg' => '#fff0f2'],
                            ['label' => 'Total Assessed',    'value' => $competentCount + $notYetCompetentCount,  'color' => '#0057B8', 'bg' => '#e8f0fb'],
                        ];
                    @endphp
                    @foreach ($assessmentStats as $a)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full" style="background:{{ $a['color'] }};"></div>
                                <span class="text-xs font-medium" style="color:var(--db-muted);">{{ $a['label'] }}</span>
                            </div>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-lg"
                                  style="background:{{ $a['bg'] }}; color:{{ $a['color'] }};">
                                {{ $a['value'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-6 text-center">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3"
                         style="background:var(--db-surface2);">
                        <i class="fas fa-clipboard text-sm" style="color:var(--db-muted);"></i>
                    </div>
                    <p class="text-xs font-medium" style="color:var(--db-muted);">No assessments recorded yet.</p>
                </div>
            @endif
        </div>

    </div>

    {{-- Recent Enrollments --}}
    @if($recentEnrollments->count() > 0)
        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold" style="color:var(--db-primary);">
                    <i class="fas fa-history mr-2" style="color:var(--db-gold);"></i> Recent Enrollments
                </h3>
                <a href="{{ route('trainee.enrollments.index') }}"
                   class="text-xs font-medium transition"
                   style="color:var(--db-accent);">
                    View all <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($recentEnrollments as $enrollment)
                    @php
                        $statusColors = [
                            'pending'   => ['bg' => 'rgba(245,197,24,0.12)', 'color' => '#b38a00'],
                            'approved'  => ['bg' => '#f0fdf4',               'color' => '#16a34a'],
                            'completed' => ['bg' => '#e8f0fb',               'color' => '#0057B8'],
                            'dropped'   => ['bg' => '#fff0f2',               'color' => '#CE1126'],
                        ];
                        $sc = $statusColors[$enrollment->status] ?? ['bg' => '#f0f5ff', 'color' => '#5a7aaa'];
                    @endphp
                    <div class="rounded-2xl border p-5"
                         style="background:var(--db-surface); border-color:var(--db-border);">

                        {{-- Course name + status badge --}}
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h4 class="font-semibold text-sm" style="color:var(--db-text);">
                                    {{ $enrollment->course->name }}
                                </h4>
                                <p class="text-xs mt-0.5" style="color:var(--db-muted);">
                                    {{ $enrollment->course->code ?? 'N/A' }}
                                    @if($enrollment->course->level)
                                        &middot; {{ $enrollment->course->level }}
                                    @endif
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-lg text-xs font-semibold flex-shrink-0"
                                  style="background:{{ $sc['bg'] }}; color:{{ $sc['color'] }};">
                                {{ ucfirst($enrollment->status) }}
                            </span>
                        </div>

                        {{-- Meta info --}}
                        <div class="space-y-1.5 text-xs" style="color:var(--db-text-sec);">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-calendar-days" style="color:var(--db-gold); width:14px;"></i>
                                <span>Enrolled: {{ $enrollment->enrolled_at?->format('M d, Y') ?? 'N/A' }}</span>
                            </div>
                            @if($enrollment->course->duration_hours)
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-clock" style="color:var(--db-accent); width:14px;"></i>
                                    <span>{{ $enrollment->course->duration_hours }} hours</span>
                                </div>
                            @endif
                        </div>

                        {{-- Action --}}
                        <a href="{{ route('trainee.enrollments.show', $enrollment) }}"
                           class="inline-flex items-center gap-1 mt-4 text-xs font-semibold px-3 py-1.5 rounded-lg transition hover:opacity-80"
                           style="background:var(--db-accent-bg); color:var(--db-accent);">
                            View Details <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        {{-- Empty state --}}
        <div class="rounded-2xl border p-10 text-center"
             style="background:var(--db-surface); border-color:var(--db-border);">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4"
                 style="background:var(--db-surface2);">
                <i class="fas fa-book-open text-xl" style="color:var(--db-muted);"></i>
            </div>
            <h4 class="font-semibold text-sm mb-1" style="color:var(--db-text);">No enrollments yet</h4>
            <p class="text-xs mb-4" style="color:var(--db-muted);">Browse available courses and enroll to get started.</p>
            <a href="{{ route('trainee.courses.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold transition hover:opacity-80"
               style="background:var(--db-accent); color:#fff;">
                <i class="fas fa-search"></i> Browse Courses
            </a>
        </div>
    @endif

</div>
@endsection