@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<style>
 
</style>
@vite('resources/css/app.layout.css')
@vite('resources/css/tenants/admin/dashboard.css')
<div class="space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold" style="color:var(--db-primary);">
            <i class="fas fa-gauge-high mr-2" style="color:var(--db-red);"></i> Dashboard
        </h1>
        <p class="text-sm mt-1" style="color:var(--db-muted);">
            Welcome back, <span class="font-700" style="color:var(--db-accent);">{{ Auth::user()->name }}</span>!
            Here's what's happening in your training center.
        </p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $stats = [
                [
                    'label' => 'Total Trainers',
                    'value' => $totalTrainers,
                    'icon'  => 'fa-chalkboard-teacher',
                    'color' => '#0057B8',
                    'bg'    => '#e8f0fb',
                    'route' => route('admin.trainers.index'),
                ],
                [
                    'label' => 'Total Trainees',
                    'value' => $totalTrainees,
                    'icon'  => 'fa-users',
                    'color' => '#CE1126',
                    'bg'    => '#fff0f2',
                    'route' => route('admin.trainees.index'),
                ],
                [
                    'label' => 'Active Courses',
                    'value' => $totalCourses,
                    'icon'  => 'fa-book-open',
                    'color' => '#b38a00',
                    'bg'    => 'rgba(245,197,24,0.12)',
                    'route' => route('admin.courses.index'),
                ],
                [
                    'label' => 'Total Enrollments',
                    'value' => $totalEnrollments,
                    'icon'  => 'fa-file-signature',
                    'color' => '#16a34a',
                    'bg'    => '#f0fdf4',
                    'route' => route('admin.enrollments.index'),
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
                <div class="text-2xl font-800" style="color:var(--db-text);">{{ $stat['value'] }}</div>
                <div class="text-xs mt-0.5 font-600" style="color:var(--db-muted);">{{ $stat['label'] }}</div>
            </a>
        @endforeach
    </div>

    {{-- Plan Usage --}}
    @php
        $tenant = tenancy()->tenant;
        $plan   = $tenant->subscription;
        $usage  = [
            ['label' => 'Trainees', 'icon' => 'fa-user-graduate', 'resource' => 'trainees',
             'count' => \App\Models\User::where('role','trainee')->count()],
            ['label' => 'Trainers', 'icon' => 'fa-chalkboard-teacher', 'resource' => 'trainers',
             'count' => \App\Models\User::where('role','trainer')->count()],
            ['label' => 'Users',    'icon' => 'fa-users', 'resource' => 'users',
             'count' => \App\Models\User::whereIn('role',['admin','trainer','trainee'])->count()],
            ['label' => 'Courses',  'icon' => 'fa-book-open', 'resource' => 'courses',
             'count' => \App\Models\Course::count()],
        ];
    @endphp

    @php $tenant = tenancy()->tenant ?? null; @endphp
    @if($tenant && $tenant->expires_at)
        @php $daysLeft = (int) now()->startOfDay()->diffInDays($tenant->expires_at->startOfDay(), false); @endphp
        @if($daysLeft >= 0 && $daysLeft <= 10)
            <div style="background:{{ $daysLeft <= 3 ? 'rgba(206,17,38,.08)' : 'rgba(179,138,0,.08)' }};
                        border-bottom:2px solid {{ $daysLeft <= 3 ? 'rgba(206,17,38,.25)' : 'rgba(179,138,0,.25)' }};
                        padding:10px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;">
                <span style="font-size:13px;font-weight:600;color:{{ $daysLeft <= 3 ? '#CE1126' : '#a07800' }};">
                    {{ $daysLeft <= 3 ? '🔴' : '🟡' }}
                    Your subscription expires in <strong>{{ $daysLeft }} day(s)</strong>
                    ({{ $tenant->expires_at->format('M d, Y') }}).
                </span>
                <a href="{{ route('admin.subscription.index') }}"
                style="padding:6px 16px;border-radius:8px;font-size:12px;font-weight:700;
                        background:{{ $daysLeft <= 3 ? '#CE1126' : '#b38a00' }};color:#fff;
                        text-decoration:none;flex-shrink:0;">
                    Renew Now
                </a>
            </div>
        @endif
    @endif

    <div style="background:var(--db-surface); border:1.5px solid var(--db-border); border-radius:16px; padding:24px; margin-bottom:24px;">
        <div style="font-size:13px; font-weight:700; color:var(--db-muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px;">
            Plan Usage — {{ ucfirst($plan) }}
            <a href="{{ route('admin.subscription.index') }}" style="float:right; font-size:12px; color:var(--db-accent); font-weight:600; text-transform:none; letter-spacing:0;">
                Manage Plan →
            </a>
        </div>

        @foreach($usage as $item)
            @php
                $limit    = \App\Helpers\SubscriptionHelper::getLimit($plan, $item['resource']);
                $pct      = $limit ? min(100, round(($item['count'] / $limit) * 100)) : 0;
                $barColor = $pct >= 90 ? 'var(--db-red)' : ($pct >= 70 ? '#f97316' : 'var(--db-accent)');
            @endphp

            <div style="margin-bottom:14px;">
                <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:4px;">
                    <span style="color:var(--db-text-sec); font-weight:500;">
                        <i class="fas {{ $item['icon'] }}" style="width:16px; color:var(--db-muted);"></i>
                        {{ $item['label'] }}
                    </span>
                    <span style="color:var(--db-muted);">
                        {{ $item['count'] }} / {{ $limit ?? '∞' }}
                    </span>
                </div>
                @if($limit !== null)
                    <div style="height:6px; background:var(--db-accent-bg); border-radius:999px; overflow:hidden;">
                        <div style="height:100%; width:{{ $pct }}%; background:{{ $barColor }}; border-radius:999px; transition:width .4s ease;"></div>
                    </div>
                @else
                    <div style="height:6px; background: linear-gradient(90deg,var(--db-accent),#5b9cf6); border-radius:999px; opacity:0.3;"></div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Second Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Pending Enrollments --}}
        <div class="rounded-2xl border p-5"
             style="background:var(--db-surface); border-color:var(--db-border);">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-700" style="color:var(--db-primary);">Enrollment Status</h3>
                <i class="fas fa-file-signature text-xs" style="color:var(--db-muted);"></i>
            </div>
            <div class="space-y-3">
                @php
                    $enrollmentStats = [
                        ['label' => 'Pending',   'value' => $pendingEnrollments,   'color' => '#b38a00', 'bg' => 'rgba(245,197,24,0.12)'],
                        ['label' => 'Approved',  'value' => $approvedEnrollments,  'color' => '#16a34a', 'bg' => '#f0fdf4'],
                        ['label' => 'Completed', 'value' => $completedEnrollments, 'color' => '#0057B8', 'bg' => '#e8f0fb'],
                        ['label' => 'Dropped',   'value' => $droppedEnrollments,   'color' => '#CE1126', 'bg' => '#fff0f2'],
                    ];
                @endphp
                @foreach ($enrollmentStats as $e)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full" style="background:{{ $e['color'] }};"></div>
                            <span class="text-xs font-600" style="color:var(--db-muted);">{{ $e['label'] }}</span>
                        </div>
                        <span class="text-xs font-700 px-2 py-0.5 rounded-lg"
                              style="background:{{ $e['bg'] }}; color:{{ $e['color'] }};">
                            {{ $e['value'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Active Schedules --}}
        <div class="rounded-2xl border p-5"
             style="background:var(--db-surface); border-color:var(--db-border);">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-700" style="color:var(--db-primary);">Schedules</h3>
                <i class="fas fa-calendar text-xs" style="color:var(--db-muted);"></i>
            </div>
            <div class="space-y-3">
                @php
                    $scheduleStats = [
                        ['label' => 'Upcoming',  'value' => $upcomingSchedules,  'color' => '#0057B8', 'bg' => '#e8f0fb'],
                        ['label' => 'Ongoing',   'value' => $ongoingSchedules,   'color' => '#16a34a', 'bg' => '#f0fdf4'],
                        ['label' => 'Completed', 'value' => $completedSchedules, 'color' => '#5a7aaa', 'bg' => '#f0f5ff'],
                        ['label' => 'Cancelled', 'value' => $cancelledSchedules, 'color' => '#CE1126', 'bg' => '#fff0f2'],
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
                <i class="fas fa-clipboard-check text-xs" style="color:var(--db-muted);"></i>
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

    {{-- Recent Enrollments --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--db-surface); border-color:var(--db-border);">
        <div class="px-5 py-4 border-b flex items-center justify-between"
             style="border-color:var(--db-border);">
            <h3 class="text-sm font-700" style="color:var(--db-primary);">
                <i class="fas fa-clock-rotate-left mr-1" style="color:var(--db-red);"></i> Recent Enrollments
            </h3>
            <a href="{{ route('admin.enrollments.index') }}"
               class="text-xs font-600 transition hover:underline" style="color:var(--db-accent);">
                View all
            </a>
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
                    @forelse ($recentEnrollments as $enrollment)
                        <tr class="transition" style="background:var(--db-surface);">
                            <td class="px-5 py-3">
                                <div class="font-600" style="color:var(--db-text);">{{ $enrollment->trainee->name }}</div>
                                <div class="text-xs" style="color:var(--db-muted);">{{ $enrollment->trainee->email }}</div>
                            </td>
                            <td class="px-5 py-3 text-xs font-600" style="color:var(--db-text-sec);">
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
                            <td class="px-5 py-3 text-xs" style="color:var(--db-muted);">
                                {{ $enrollment->enrolled_at?->format('M d, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-xs" style="color:var(--db-muted);">
                                No recent enrollments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection