{{-- resources/views/tenants/admin/reports/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Analytics & Reports')

@section('content')
<style>
    /* ── Design tokens (inherits sidebar palette) ── */
    :root {
        --rpt-navy:   #001a4d;
        --rpt-blue:   #0057B8;
        --rpt-gold:   #c9a84c;
        --rpt-red:    #CE1126;
        --rpt-green:  #16a34a;
        --rpt-orange: #ea580c;
        --rpt-purple: #7c3aed;
        --rpt-muted:  #5a7aaa;
    }

    /* ── Stat cards ── */
    .rpt-card {
        background: white;
        border: 1px solid #dde8f8;
        border-radius: 14px;
        padding: 20px;
        position: relative;
        overflow: hidden;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }
    .rpt-card:hover { box-shadow: 0 8px 24px rgba(0,48,135,0.10); transform: translateY(-2px); }
    .rpt-card::before {
        content: '';
        position: absolute; top: 0; left: 0; right: 0;
        height: 3px;
    }
    .rpt-card.blue::before   { background: linear-gradient(90deg, #0057B8, #5b9cf6); }
    .rpt-card.gold::before   { background: linear-gradient(90deg, #c9a84c, #f5d78e); }
    .rpt-card.green::before  { background: linear-gradient(90deg, #16a34a, #4ade80); }
    .rpt-card.red::before    { background: linear-gradient(90deg, #CE1126, #f87171); }
    .rpt-card.purple::before { background: linear-gradient(90deg, #7c3aed, #c4b5fd); }
    .rpt-card.orange::before { background: linear-gradient(90deg, #ea580c, #fb923c); }

    .rpt-icon {
        width: 44px; height: 44px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
    .rpt-icon.blue   { background: #e8f0fb; color: #0057B8; }
    .rpt-icon.gold   { background: #fef9ec; color: #c9a84c; }
    .rpt-icon.green  { background: #dcfce7; color: #16a34a; }
    .rpt-icon.red    { background: #fee2e2; color: #CE1126; }
    .rpt-icon.purple { background: #ede9fe; color: #7c3aed; }
    .rpt-icon.orange { background: #fff7ed; color: #ea580c; }

    /* ── Export section ── */
    .export-card {
        background: white;
        border: 1px solid #dde8f8;
        border-radius: 14px;
        overflow: hidden;
    }
    .export-card-header {
        background: linear-gradient(135deg, #001a4d 0%, #0057B8 100%);
        padding: 16px 20px;
        display: flex; align-items: center; gap: 12px;
    }
    .export-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 14px 20px;
        border-bottom: 1px solid #f0f5ff;
        gap: 12px;
        transition: background 0.15s;
    }
    .export-row:hover  { background: #f8fbff; }
    .export-row:last-child { border-bottom: none; }

    .export-badge {
        font-size: 9px; font-weight: 800; letter-spacing: 0.5px;
        text-transform: uppercase; padding: 3px 8px; border-radius: 6px;
        flex-shrink: 0;
    }
    .badge-basic    { background: #e8f0fb; color: #0057B8; }
    .badge-standard { background: #ede9fe; color: #7c3aed; }
    .badge-premium  { background: #fef9ec; color: #c9a84c; border: 1px solid rgba(201,168,76,0.3); }
    .badge-locked   { background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; }

    .btn-export {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600;
        text-decoration: none; border: none; cursor: pointer;
        transition: all 0.18s ease;
    }
    .btn-export-csv  { background: #e8f0fb; color: #0057B8; }
    .btn-export-csv:hover { background: #0057B8; color: white; }
    .btn-export-pdf  { background: #fef9ec; color: #c9a84c; border: 1px solid rgba(201,168,76,0.3); }
    .btn-export-pdf:hover { background: #c9a84c; color: white; }
    .btn-export-locked {
        background: #f1f5f9; color: #94a3b8;
        cursor: not-allowed; opacity: 0.6; pointer-events: none;
    }

    /* ── Bar chart ── */
    .bar-chart { display: flex; align-items: flex-end; gap: 6px; height: 120px; padding-top: 8px; }
    .bar-wrap  { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; height: 100%; justify-content: flex-end; }
    .bar {
        width: 100%; border-radius: 5px 5px 0 0;
        min-height: 4px;
        background: linear-gradient(180deg, #5b9cf6, #0057B8);
        transition: height 0.6s ease;
        position: relative;
    }
    .bar:hover::after {
        content: attr(data-count);
        position: absolute; top: -22px; left: 50%; transform: translateX(-50%);
        background: #001a4d; color: white; font-size: 10px; font-weight: 700;
        padding: 2px 6px; border-radius: 5px; white-space: nowrap;
    }
    .bar-label { font-size: 9px; color: #5a7aaa; text-align: center; line-height: 1.2; }

    /* ── Donut placeholder (CSS-only ring) ── */
    .donut-ring {
        width: 80px; height: 80px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; font-weight: 800; color: #001a4d;
        flex-shrink: 0;
    }

    /* ── Upgrade CTA ── */
    .upgrade-cta {
        background: linear-gradient(135deg, #001a4d 0%, #0057B8 60%, #CE1126 150%);
        border-radius: 14px; padding: 20px 24px;
        display: flex; align-items: center; gap: 16px;
        position: relative; overflow: hidden;
    }
    .upgrade-cta::before {
        content: '';
        position: absolute; top: 0; left: -100%; width: 60%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.07), transparent);
        animation: cta-shimmer 3s infinite;
    }
    @keyframes cta-shimmer { 0% { left: -100%; } 100% { left: 200%; } }

    /* ── Section titles ── */
    .section-title {
        font-size: 11px; font-weight: 700; letter-spacing: 0.08em;
        text-transform: uppercase; color: var(--rpt-muted);
        margin-bottom: 12px;
    }

    /* Dark mode adjustments */
    .dark .rpt-card { background: #0d1f3c; border-color: #1e3a6b; }
    .dark .export-card { background: #0d1f3c; border-color: #1e3a6b; }
    .dark .export-row { border-color: #1e3a6b; }
    .dark .export-row:hover { background: #0a1628; }
    .dark .rpt-icon.blue   { background: rgba(0,87,184,0.15); }
    .dark .rpt-icon.gold   { background: rgba(201,168,76,0.12); }
    .dark .rpt-icon.green  { background: rgba(22,163,74,0.12); }
    .dark .rpt-icon.red    { background: rgba(206,17,38,0.12); }
    .dark .rpt-icon.purple { background: rgba(124,58,237,0.12); }
    .dark .rpt-icon.orange { background: rgba(234,88,12,0.12); }
    .dark .bar-label { color: #6b8abf; }
</style>

<div class="p-6 max-w-7xl mx-auto space-y-8">

    {{-- ── Page Header ────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#001a4d] dark:text-[#dde8ff]">Analytics & Reports</h1>
            <p class="text-sm text-[#5a7aaa] mt-0.5">Overview of your training center's performance</p>
        </div>
        <div class="flex items-center gap-2">
            @php
                $planColors = [
                    'basic'    => 'bg-[#e8f0fb] text-[#0057B8]',
                    'standard' => 'bg-[#ede9fe] text-[#7c3aed]',
                    'premium'  => 'bg-[#fef9ec] text-[#c9a84c] border border-[rgba(201,168,76,0.3)]',
                ];
            @endphp
            <span class="px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide {{ $planColors[$plan] ?? 'bg-gray-100 text-gray-600' }}">
                {{ ucfirst($plan) }} Plan
            </span>
            <span class="text-xs text-[#5a7aaa]">{{ now()->format('F d, Y') }}</span>
        </div>
    </div>

    {{-- ── Flash Errors ────────────────────────────────────────────── --}}
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-12 px-4 py-3 flex gap-3 items-start" style="border-radius:10px">
            <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 flex-shrink-0"></i>
            <div>
                @foreach($errors->all() as $error)
                    <p class="text-sm text-red-700">{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Overview Stats Grid ─────────────────────────────────────── --}}
    <div>
        <p class="section-title">Overview</p>
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">

            {{-- Trainees --}}
            <div class="rpt-card blue">
                <div class="flex items-start gap-3">
                    <div class="rpt-icon blue"><i class="fas fa-user-graduate"></i></div>
                    <div class="min-w-0">
                        <div class="text-2xl font-black text-[#001a4d] dark:text-white leading-none">{{ number_format($totalTrainees) }}</div>
                        <div class="text-xs text-[#5a7aaa] mt-1">Trainees</div>
                    </div>
                </div>
            </div>

            {{-- Trainers --}}
            <div class="rpt-card purple">
                <div class="flex items-start gap-3">
                    <div class="rpt-icon purple"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div class="min-w-0">
                        <div class="text-2xl font-black text-[#001a4d] dark:text-white leading-none">{{ number_format($totalTrainers) }}</div>
                        <div class="text-xs text-[#5a7aaa] mt-1">Trainers</div>
                    </div>
                </div>
            </div>

            {{-- Courses --}}
            <div class="rpt-card gold">
                <div class="flex items-start gap-3">
                    <div class="rpt-icon gold"><i class="fas fa-book-open"></i></div>
                    <div class="min-w-0">
                        <div class="text-2xl font-black text-[#001a4d] dark:text-white leading-none">{{ number_format($activeCourses) }}</div>
                        <div class="text-xs text-[#5a7aaa] mt-1">Active Courses</div>
                    </div>
                </div>
            </div>

            {{-- Enrollments --}}
            <div class="rpt-card green">
                <div class="flex items-start gap-3">
                    <div class="rpt-icon green"><i class="fas fa-user-plus"></i></div>
                    <div class="min-w-0">
                        <div class="text-2xl font-black text-[#001a4d] dark:text-white leading-none">{{ number_format($enrollmentStats['total']) }}</div>
                        <div class="text-xs text-[#5a7aaa] mt-1">Enrollments</div>
                    </div>
                </div>
            </div>

            {{-- Assessments --}}
            <div class="rpt-card orange">
                <div class="flex items-start gap-3">
                    <div class="rpt-icon orange"><i class="fas fa-clipboard-check"></i></div>
                    <div class="min-w-0">
                        <div class="text-2xl font-black text-[#001a4d] dark:text-white leading-none">{{ number_format($assessmentStats['total']) }}</div>
                        <div class="text-xs text-[#5a7aaa] mt-1">Assessments</div>
                    </div>
                </div>
            </div>

            {{-- Certificates --}}
            <div class="rpt-card red">
                <div class="flex items-start gap-3">
                    <div class="rpt-icon red"><i class="fas fa-certificate"></i></div>
                    <div class="min-w-0">
                        <div class="text-2xl font-black text-[#001a4d] dark:text-white leading-none">{{ number_format($certificateStats['total']) }}</div>
                        <div class="text-xs text-[#5a7aaa] mt-1">Certificates</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Main Grid: Charts + Breakdowns ─────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Monthly Enrollments Bar Chart --}}
        <div class="rpt-card blue lg:col-span-2">
            <p class="section-title">Monthly Enrollments (Last 6 Months)</p>
            @php
                $maxCount = max(array_column($monthlyEnrollments, 'count') ?: [1]);
                $maxCount = max($maxCount, 1);
            @endphp
            <div class="bar-chart">
                @foreach($monthlyEnrollments as $month)
                    @php
                        $heightPct = round(($month['count'] / $maxCount) * 100);
                        $heightPx  = max(4, round($heightPct * 1.1));
                    @endphp
                    <div class="bar-wrap">
                        <div class="bar" style="height: {{ $heightPx }}px" data-count="{{ $month['count'] }}"></div>
                        <span class="bar-label">{{ $month['label'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 text-xs text-[#5a7aaa] text-right">
                Total: <strong class="text-[#001a4d] dark:text-white">{{ number_format(array_sum(array_column($monthlyEnrollments, 'count'))) }}</strong> new enrollments
            </div>
        </div>

        {{-- Enrollment Status Breakdown --}}
        <div class="rpt-card blue">
            <p class="section-title">Enrollment Status</p>
            <div class="space-y-3">
                @php
                    $statuses = [
                        'approved'  => ['label' => 'Approved',  'color' => '#16a34a', 'bg' => '#dcfce7', 'count' => $enrollmentStats['approved']],
                        'pending'   => ['label' => 'Pending',   'color' => '#c9a84c', 'bg' => '#fef9ec', 'count' => $enrollmentStats['pending']],
                        'completed' => ['label' => 'Completed', 'color' => '#0057B8', 'bg' => '#e8f0fb', 'count' => $enrollmentStats['completed']],
                        'dropped'   => ['label' => 'Dropped',   'color' => '#CE1126', 'bg' => '#fee2e2', 'count' => $enrollmentStats['dropped']],
                    ];
                    $totalEnr = max($enrollmentStats['total'], 1);
                @endphp
                @foreach($statuses as $s)
                    @php $pct = round(($s['count'] / $totalEnr) * 100) @endphp
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="font-semibold text-[#001a4d] dark:text-[#dde8ff]">{{ $s['label'] }}</span>
                            <span class="text-[#5a7aaa]">{{ $s['count'] }}  ({{ $pct }}%)</span>
                        </div>
                        <div class="h-2 rounded-full" style="background: {{ $s['bg'] }}">
                            <div class="h-2 rounded-full transition-all duration-700" style="width: {{ $pct }}%; background: {{ $s['color'] }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Second Row: Assessment + Attendance + Schedules ────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Assessment Results --}}
        <div class="rpt-card orange">
            <p class="section-title">Assessment Results</p>
            @php
                $totalAss = max($assessmentStats['total'], 1);
                $compPct  = round(($assessmentStats['competent'] / $totalAss) * 100);
                $nycPct   = 100 - $compPct;
            @endphp
            <div class="flex items-center gap-4">
                <div class="donut-ring" style="background: conic-gradient(#16a34a {{ $compPct * 3.6 }}deg, #CE1126 {{ $compPct * 3.6 }}deg);">
                    <div class="w-14 h-14 bg-white dark:bg-[#0d1f3c] rounded-full flex items-center justify-center text-sm font-bold text-[#001a4d] dark:text-white">
                        {{ $compPct }}%
                    </div>
                </div>
                <div class="space-y-2 flex-1">
                    <div class="flex justify-between text-xs">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-600 inline-block"></span>Competent</span>
                        <span class="font-bold text-[#001a4d] dark:text-white">{{ $assessmentStats['competent'] }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-600 inline-block"></span>Not Yet</span>
                        <span class="font-bold text-[#001a4d] dark:text-white">{{ $assessmentStats['not_yet_competent'] }}</span>
                    </div>
                    <div class="flex justify-between text-xs border-t border-[#dde8f8] pt-2">
                        <span class="text-[#5a7aaa]">Total</span>
                        <span class="font-bold text-[#001a4d] dark:text-white">{{ $assessmentStats['total'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attendance Summary --}}
        <div class="rpt-card green">
            <p class="section-title">Attendance Summary</p>
            @php
                $totalAtt   = max($attendanceStats['total'], 1);
                $presentPct = round(($attendanceStats['present'] / $totalAtt) * 100);
            @endphp
            <div class="flex items-center gap-4 mb-3">
                <div class="text-4xl font-black text-green-600">{{ $presentPct }}%</div>
                <div class="text-xs text-[#5a7aaa] leading-relaxed">Overall<br>Attendance Rate</div>
            </div>
            <div class="space-y-2">
                @foreach([
                    ['Present', $attendanceStats['present'], '#16a34a'],
                    ['Absent',  $attendanceStats['absent'],  '#CE1126'],
                    ['Late',    $attendanceStats['late'],    '#c9a84c'],
                ] as [$label, $count, $color])
                    <div class="flex justify-between text-xs">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full inline-block" style="background:{{ $color }}"></span>
                            {{ $label }}
                        </span>
                        <span class="font-bold text-[#001a4d] dark:text-white">{{ number_format($count) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Schedule Stats --}}
        <div class="rpt-card gold">
            <p class="section-title">Training Schedules</p>
            <div class="space-y-3">
                @foreach([
                    ['Upcoming',  $scheduleStats['upcoming'],  '#0057B8'],
                    ['Ongoing',   $scheduleStats['ongoing'],   '#16a34a'],
                    ['Completed', $scheduleStats['completed'], '#c9a84c'],
                    ['Cancelled', $scheduleStats['cancelled'], '#CE1126'],
                ] as [$label, $count, $color])
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-[#5a7aaa]">{{ $label }}</span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold" style="background: {{ $color }}18; color: {{ $color }}">
                            {{ $count }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Top Courses ──────────────────────────────────────────────── --}}
    <div class="rpt-card blue">
        <p class="section-title">Top Courses by Enrollment</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#dde8f8]">
                        <th class="text-left pb-2 text-xs font-semibold text-[#5a7aaa] w-8">#</th>
                        <th class="text-left pb-2 text-xs font-semibold text-[#5a7aaa]">Course</th>
                        <th class="text-left pb-2 text-xs font-semibold text-[#5a7aaa]">Code</th>
                        <th class="text-left pb-2 text-xs font-semibold text-[#5a7aaa]">Level</th>
                        <th class="text-right pb-2 text-xs font-semibold text-[#5a7aaa]">Enrollments</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCourses as $i => $course)
                        <tr class="border-b border-[#f0f5ff] hover:bg-[#f8fbff] dark:hover:bg-[#0a1628] transition-colors">
                            <td class="py-2.5 text-xs text-[#5a7aaa]">{{ $i + 1 }}</td>
                            <td class="py-2.5 font-semibold text-[#001a4d] dark:text-[#dde8ff]">{{ $course->name }}</td>
                            <td class="py-2.5 text-xs text-[#5a7aaa] font-mono">{{ $course->code }}</td>
                            <td class="py-2.5">
                                @if($course->level)
                                    <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-[#e8f0fb] text-[#0057B8]">{{ $course->level }}</span>
                                @else
                                    <span class="text-xs text-[#5a7aaa]">—</span>
                                @endif
                            </td>
                            <td class="py-2.5 text-right font-bold text-[#001a4d] dark:text-white">{{ number_format($course->enrollments_count) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-center text-xs text-[#5a7aaa]">No courses found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Export Section ───────────────────────────────────────────── --}}
    <div>
        <p class="section-title">Data Exports</p>

        {{-- Plan notice --}}
        @if($plan === 'basic')
            <div class="upgrade-cta mb-4">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-xl flex-shrink-0">⚡</div>
                <div class="flex-1">
                    <div class="text-white font-bold text-sm">Exports are not available on the Basic plan</div>
                    <div class="text-white/70 text-xs mt-0.5">Upgrade to Standard to export CSV reports, or Premium for unlimited CSV & PDF exports.</div>
                </div>
                <a href="{{ route('admin.subscription.index') }}"
                   class="flex-shrink-0 px-4 py-2 rounded-10 bg-[#F5C518] text-[#1a1a00] text-xs font-extrabold uppercase tracking-wide"
                   style="border-radius: 8px">
                    Upgrade Now
                </a>
            </div>
        @elseif($plan === 'standard')
            <div class="mb-4 flex items-start gap-3 px-4 py-3 rounded-xl bg-purple-50 border border-purple-200">
                <i class="fas fa-info-circle text-purple-500 mt-0.5 flex-shrink-0"></i>
                <div class="text-sm text-purple-700">
                    <strong>Standard Plan:</strong> CSV exports up to 3,000 records/month. PDF export and unlimited records require <strong>Premium</strong>.
                    <a href="{{ route('admin.subscription.index') }}" class="underline font-semibold">Upgrade →</a>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- Left column --}}
            <div class="export-card">
                <div class="export-card-header">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                        <i class="fas fa-users text-white text-sm"></i>
                    </div>
                    <span class="text-white font-bold text-sm">People</span>
                </div>

                {{-- Trainees --}}
                <div class="export-row">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="export-badge badge-basic">Basic</span>
                        <div>
                            <div class="text-sm font-semibold text-[#001a4d] dark:text-[#dde8ff]">Trainees List</div>
                            <div class="text-xs text-[#5a7aaa]">Names, emails, enrollment counts</div>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        @if($plan !== 'basic')
                            <a href="{{ route('admin.reports.export.trainees', ['format' => 'csv']) }}" class="btn-export btn-export-csv">
                                <i class="fas fa-file-csv text-xs"></i> CSV
                            </a>
                            @if($plan === 'premium')
                                <a href="{{ route('admin.reports.export.trainees', ['format' => 'pdf']) }}" class="btn-export btn-export-pdf">
                                    <i class="fas fa-file-pdf text-xs"></i> PDF
                                </a>
                            @endif
                        @else
                            <span class="btn-export btn-export-locked"><i class="fas fa-lock text-xs"></i> CSV</span>
                        @endif
                    </div>
                </div>

                {{-- Trainers (standard+) --}}
                <div class="export-row">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="export-badge {{ in_array($plan, ['standard','premium']) ? 'badge-standard' : 'badge-locked' }}">Standard</span>
                        <div>
                            <div class="text-sm font-semibold text-[#001a4d] dark:text-[#dde8ff]">Trainers List</div>
                            <div class="text-xs text-[#5a7aaa]">Names, emails, assessment counts</div>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        @if(in_array($plan, ['standard', 'premium']))
                            <a href="{{ route('admin.reports.export.trainers', ['format' => 'csv']) }}" class="btn-export btn-export-csv">
                                <i class="fas fa-file-csv text-xs"></i> CSV
                            </a>
                            @if($plan === 'premium')
                                <a href="{{ route('admin.reports.export.trainers', ['format' => 'pdf']) }}" class="btn-export btn-export-pdf">
                                    <i class="fas fa-file-pdf text-xs"></i> PDF
                                </a>
                            @endif
                        @else
                            <span class="btn-export btn-export-locked"><i class="fas fa-lock text-xs"></i> CSV</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right column --}}
            <div class="export-card">
                <div class="export-card-header">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                        <i class="fas fa-table text-white text-sm"></i>
                    </div>
                    <span class="text-white font-bold text-sm">Training Data</span>
                </div>

                {{-- Enrollments --}}
                <div class="export-row">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="export-badge badge-basic">Basic</span>
                        <div>
                            <div class="text-sm font-semibold text-[#001a4d] dark:text-[#dde8ff]">Enrollments</div>
                            <div class="text-xs text-[#5a7aaa]">Trainee, course, status, dates</div>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        @if($plan !== 'basic')
                            <a href="{{ route('admin.reports.export.enrollments', ['format' => 'csv']) }}" class="btn-export btn-export-csv">
                                <i class="fas fa-file-csv text-xs"></i> CSV
                            </a>
                            @if($plan === 'premium')
                                <a href="{{ route('admin.reports.export.enrollments', ['format' => 'pdf']) }}" class="btn-export btn-export-pdf">
                                    <i class="fas fa-file-pdf text-xs"></i> PDF
                                </a>
                            @endif
                        @else
                            <span class="btn-export btn-export-locked"><i class="fas fa-lock text-xs"></i> CSV</span>
                        @endif
                    </div>
                </div>

                {{-- Attendance --}}
                <div class="export-row">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="export-badge badge-basic">Basic</span>
                        <div>
                            <div class="text-sm font-semibold text-[#001a4d] dark:text-[#dde8ff]">Attendance Records</div>
                            <div class="text-xs text-[#5a7aaa]">Trainee, course, date, status</div>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        @if($plan !== 'basic')
                            <a href="{{ route('admin.reports.export.attendances', ['format' => 'csv']) }}" class="btn-export btn-export-csv">
                                <i class="fas fa-file-csv text-xs"></i> CSV
                            </a>
                            @if($plan === 'premium')
                                <a href="{{ route('admin.reports.export.attendances', ['format' => 'pdf']) }}" class="btn-export btn-export-pdf">
                                    <i class="fas fa-file-pdf text-xs"></i> PDF
                                </a>
                            @endif
                        @else
                            <span class="btn-export btn-export-locked"><i class="fas fa-lock text-xs"></i> CSV</span>
                        @endif
                    </div>
                </div>

                {{-- Assessments (standard+) --}}
                <div class="export-row">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="export-badge {{ in_array($plan, ['standard','premium']) ? 'badge-standard' : 'badge-locked' }}">Standard</span>
                        <div>
                            <div class="text-sm font-semibold text-[#001a4d] dark:text-[#dde8ff]">Assessments</div>
                            <div class="text-xs text-[#5a7aaa]">Results, scores, trainer, remarks</div>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        @if(in_array($plan, ['standard', 'premium']))
                            <a href="{{ route('admin.reports.export.assessments', ['format' => 'csv']) }}" class="btn-export btn-export-csv">
                                <i class="fas fa-file-csv text-xs"></i> CSV
                            </a>
                            @if($plan === 'premium')
                                <a href="{{ route('admin.reports.export.assessments', ['format' => 'pdf']) }}" class="btn-export btn-export-pdf">
                                    <i class="fas fa-file-pdf text-xs"></i> PDF
                                </a>
                            @endif
                        @else
                            <span class="btn-export btn-export-locked"><i class="fas fa-lock text-xs"></i> CSV</span>
                        @endif
                    </div>
                </div>

                {{-- Certificates (premium only) --}}
                <div class="export-row">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="export-badge {{ $plan === 'premium' ? 'badge-premium' : 'badge-locked' }}">Premium</span>
                        <div>
                            <div class="text-sm font-semibold text-[#001a4d] dark:text-[#dde8ff]">Certificates</div>
                            <div class="text-xs text-[#5a7aaa]">Cert numbers, issued/expiry dates</div>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        @if($plan === 'premium')
                            <a href="{{ route('admin.reports.export.certificates', ['format' => 'csv']) }}" class="btn-export btn-export-csv">
                                <i class="fas fa-file-csv text-xs"></i> CSV
                            </a>
                            <a href="{{ route('admin.reports.export.certificates', ['format' => 'pdf']) }}" class="btn-export btn-export-pdf">
                                <i class="fas fa-file-pdf text-xs"></i> PDF
                            </a>
                        @else
                            <span class="btn-export btn-export-locked"><i class="fas fa-lock text-xs"></i> CSV</span>
                            <span class="btn-export btn-export-locked"><i class="fas fa-lock text-xs"></i> PDF</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection