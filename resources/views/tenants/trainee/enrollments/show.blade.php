@extends('layouts.app')

@section('title', 'Enrollment Details')

@section('content')
<style>
  :root {
    --end-surface: #ffffff;
    --end-border: #c5d8f5;
    --end-text: #001a4d;
    --end-text-sec: #5a7aaa;
    --end-accent: #0057B8;
    --end-accent-bg: #e8f0fb;
    --end-red: #CE1126;
    --end-red-bg: #fff0f2;
    --end-gold: #F5C518;
    --end-gold-bg: rgba(245, 197, 24, 0.15);
  }
  .dark {
    --end-surface: #0d1f3c;
    --end-border: #1e3a6b;
    --end-text: #dde8ff;
    --end-text-sec: #3a5a8a;
    --end-accent: #0057B8;
    --end-accent-bg: #122550;
    --end-red: #CE1126;
    --end-red-bg: #5a0a0a;
    --end-gold: #F5C518;
    --end-gold-bg: rgba(245, 197, 24, 0.1);
  }
</style>
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('trainee.enrollments.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition hover:bg-[#e8f0fb]"
           style="border-color:var(--end-border); color:var(--end-text-sec);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--end-accent);">
                <i class="fas fa-clipboard-list mr-2" style="color:var(--end-red);"></i> Enrollment Details
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--end-text-sec);">{{ $enrollment->course->name }}</p>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="rounded-2xl border overflow-hidden dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--end-surface); border-color:var(--end-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        {{-- Header --}}
        <div class="p-8 flex flex-col sm:flex-row items-start sm:items-center gap-6"
             style="background: linear-gradient(135deg, #003087 0%, #0057B8 100%); position:relative; overflow:hidden;">
            <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,0.05);"></div>
            <div style="position:absolute;bottom:-40px;left:-20px;width:120px;height:120px;border-radius:50%;background:rgba(245,197,24,0.07);"></div>

            <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl font-900 text-white flex-shrink-0"
                 style="background:rgba(255,255,255,0.15); border:2px solid rgba(255,255,255,0.20); position:relative; z-index:1;">
                <i class="fas fa-clipboard-list"></i>
            </div>

            <div style="position:relative;z-index:1;">
                <div class="text-xl font-800 text-white">{{ $enrollment->course->name }}</div>
                <div class="text-sm mt-0.5" style="color:rgba(255,255,255,0.65);">{{ $enrollment->course->code }}</div>
                <div class="flex flex-wrap gap-2 mt-3">
                    @php
                        $statusColors = [
                            'pending' => ['bg' => 'rgba(234,179,8,0.25)', 'color' => '#FFDB05', 'icon' => 'fa-hourglass-half'],
                            'approved' => ['bg' => 'rgba(59,130,246,0.25)', 'color' => '#93c5fd', 'icon' => 'fa-check-circle'],
                            'completed' => ['bg' => 'rgba(34,197,94,0.25)', 'color' => '#86efac', 'icon' => 'fa-check-double'],
                            'dropped' => ['bg' => 'rgba(206,17,38,0.25)', 'color' => '#fca5a5', 'icon' => 'fa-times-circle'],
                        ];
                        $statusColor = $statusColors[$enrollment->status] ?? $statusColors['pending'];
                    @endphp
                    <span class="px-2.5 py-1 rounded-lg text-xs font-700"
                          style="background:{{ $statusColor['bg'] }}; border:1px solid {{ $statusColor['color'] }}; color:{{ $statusColor['color'] }};">
                        <i class="fas {{ $statusColor['icon'] }} mr-1" style="font-size:9px;"></i> {{ ucfirst($enrollment->status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Enrollment Info --}}
        <div class="p-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
            @php
                $details = [
                    ['icon' => 'fa-book', 'color' => 'var(--end-red)', 'bg' => 'var(--end-red-bg)', 'label' => 'Course', 'value' => $enrollment->course->name],
                    ['icon' => 'fa-code', 'color' => 'var(--end-text-sec)', 'bg' => 'var(--end-accent-bg)', 'label' => 'Course Code', 'value' => $enrollment->course->code],
                    ['icon' => 'fa-clock', 'color' => 'var(--end-accent)', 'bg' => 'var(--end-accent-bg)', 'label' => 'Duration', 'value' => $enrollment->course->duration_hours . ' hours'],
                    ['icon' => 'fa-calendar', 'color' => 'var(--end-text-sec)', 'bg' => 'var(--end-accent-bg)', 'label' => 'Enrolled Date', 'value' => $enrollment->enrolled_at?->format('F d, Y')],
                ];
            @endphp

            @foreach ($details as $d)
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs flex-shrink-0"
                         style="background:{{ $d['bg'] }}; color:{{ $d['color'] }};">
                        <i class="fas {{ $d['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="text-xs font-700 uppercase tracking-wide mb-0.5" style="color:var(--end-text-sec);">{{ $d['label'] }}</div>
                        <div class="text-sm font-600 dark:text-white" style="color:var(--end-text);">{{ $d['value'] ?? '—' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Attendance Summary --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $attendanceStats = [
                ['label' => 'Total', 'value' => $attendanceSummary['total'], 'icon' => 'fa-calendar-check', 'color' => 'var(--end-accent)', 'bg' => 'var(--end-accent-bg)'],
                ['label' => 'Present', 'value' => $attendanceSummary['present'], 'icon' => 'fa-check-circle', 'color' => '#22c55e', 'bg' => 'rgba(34,197,94,0.15)'],
                ['label' => 'Late', 'value' => $attendanceSummary['late'], 'icon' => 'fa-clock', 'color' => '#eab308', 'bg' => 'rgba(234,179,8,0.15)'],
                ['label' => 'Absent', 'value' => $attendanceSummary['absent'], 'icon' => 'fa-times-circle', 'color' => '#CE1126', 'bg' => 'var(--end-red-bg)'],
            ];
        @endphp
        @foreach ($attendanceStats as $stat)
            <div class="rounded-2xl border p-4 text-center dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
                 style="background:var(--end-surface); border-color:var(--end-border);">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mx-auto mb-2 text-sm"
                     style="background:{{ $stat['bg'] }}; color:{{ $stat['color'] }};">
                    <i class="fas {{ $stat['icon'] }}"></i>
                </div>
                <div class="text-2xl font-800 dark:text-white" style="color:var(--end-text);">{{ $stat['value'] }}</div>
                <div class="text-xs mt-1" style="color:var(--end-text-sec);">{{ $stat['label'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Attendance Rate --}}
    <div class="rounded-2xl border p-6 dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--end-surface); border-color:var(--end-border);">
        <h3 class="text-sm font-700 mb-4 dark:text-white" style="color:var(--end-accent);">
            <i class="fas fa-chart-pie mr-2" style="color:var(--end-red);"></i> Attendance Rate
        </h3>
        <div class="flex items-center gap-4">
            <div class="flex-1">
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                    <div class="bg-gradient-to-r from-green-400 to-green-500 h-full transition-all duration-500"
                         style="width: {{ $attendanceSummary['rate'] }}%;"></div>
                </div>
            </div>
            <div class="text-3xl font-800 flex-shrink-0" style="color:var(--end-accent);">
                {{ $attendanceSummary['rate'] }}%
            </div>
        </div>
        <p class="text-xs mt-3" style="color:var(--end-text-sec);">
            {{ $attendanceSummary['present'] }} out of {{ $attendanceSummary['total'] }} sessions attended
        </p>
    </div>

    {{-- Attendance Records --}}
    <div class="rounded-2xl border overflow-hidden dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--end-surface); border-color:var(--end-border);">

        <div class="p-6 border-b dark:border-[#1e3a6b]" style="border-color:var(--end-border);">
            <h3 class="text-lg font-800 dark:text-white" style="color:var(--end-accent);">
                <i class="fas fa-clipboard-check mr-2" style="color:var(--end-red);"></i> Attendance Records
            </h3>
        </div>

        <div class="overflow-x-auto">
            @if ($enrollment->attendanceRecords->count() > 0)
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:var(--end-accent-bg); border-bottom:1px solid var(--end-border);">
                            <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--end-accent);">Date</th>
                            <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--end-accent);">Status</th>
                            <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--end-accent);">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-[#1e3a6b]" style="divide-color:var(--end-accent-bg);">
                        @foreach ($enrollment->attendanceRecords as $record)
                            <tr class="transition hover:bg-[#f0f5ff] dark:hover:bg-[#122550]">
                                <td class="px-5 py-4 text-sm font-600" style="color:var(--end-text);">
                                    {{ $record->date?->format('M d, Y') }}
                                </td>
                                <td class="px-5 py-4">
                                    @php
                                        $attendanceStatus = [
                                            'present' => ['bg' => 'rgba(34,197,94,0.15)', 'color' => '#22c55e', 'icon' => 'fa-check-circle'],
                                            'absent' => ['bg' => 'rgba(206,17,38,0.15)', 'color' => '#CE1126', 'icon' => 'fa-times-circle'],
                                            'late' => ['bg' => 'rgba(234,179,8,0.15)', 'color' => '#eab308', 'icon' => 'fa-clock'],
                                        ];
                                        $attStatus = $attendanceStatus[$record->status] ?? $attendanceStatus['absent'];
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-700 inline-block"
                                          style="background:{{ $attStatus['bg'] }}; color:{{ $attStatus['color'] }};">
                                        <i class="fas {{ $attStatus['icon'] }} mr-1" style="font-size:9px;"></i> {{ ucfirst($record->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm" style="color:var(--end-text-sec);">
                                    {{ $record->remarks ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-8 text-center" style="color:var(--end-text-sec);">
                    <i class="fas fa-clipboard text-4xl opacity-25 mb-3 block"></i>
                    <p class="font-600">No attendance records yet</p>
                    <p class="text-xs">Attendance will be recorded as training progresses.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex gap-3">
        <a href="{{ route('trainee.enrollments.index') }}"
           class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-600 border transition"
           style="border-color:var(--end-border); color:var(--end-text-sec);">
            <i class="fas fa-arrow-left mr-1"></i> Back to Enrollments
        </a>
        <a href="{{ route('trainee.courses.show', $enrollment->course) }}"
           class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-600 transition hover:-translate-y-0.5"
           style="background:linear-gradient(135deg,var(--end-accent),#003087); box-shadow:0 3px 12px rgba(0,87,184,0.15);">
            <i class="fas fa-book mr-1"></i> View Course
        </a>
    </div>

</div>
@endsection
