@extends('layouts.app')

@section('title', 'Trainee Profile')

@section('content')
<style>
:root {
  --tn-surface:      #ffffff;
  --tn-border:       #c5d8f5;
  --tn-text:         #001a4d;
  --tn-text-sec:     #5a7aaa;
  --tn-accent:       #0057B8;
  --tn-accent-bg:    rgba(0,87,184,0.10);
  --tn-red:          #CE1126;
  --tn-red-bg:       rgba(206,17,38,0.15);
  --tn-green:        #16a34a;
  --tn-green-bg:     rgba(22,163,74,0.15);
  --tn-gold:         #F5C518;
  --tn-gold-bg:      rgba(245,197,24,0.15);
}
.dark {
  --tn-surface:      #0d1f3c;
  --tn-border:       #1e3a6b;
  --tn-text:         #dde8ff;
  --tn-text-sec:     #9ca3af;
  --tn-accent:       #5ba3f5;
  --tn-accent-bg:    rgba(91,163,245,0.15);
  --tn-red:          #ff6b7a;
  --tn-red-bg:       rgba(255,107,122,0.15);
  --tn-green:        #36d399;
  --tn-green-bg:     rgba(54,211,153,0.15);
  --tn-gold:         #fcd34d;
  --tn-gold-bg:      rgba(252,211,77,0.15);
}
</style>
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('trainer.trainees.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition hover:bg-[#e8f0fb]"
           style="border-color:var(--tn-border); color:var(--tn-text-sec);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--tn-accent);">
                <i class="fas fa-user-graduate mr-2" style="color:var(--tn-red);"></i> Trainee Profile
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--tn-text-sec);">View trainee information and performance</p>
        </div>
    </div>

    {{-- Trainee Info Card --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--tn-surface); border-color:var(--tn-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>
        
        <div class="p-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Name --}}
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--tn-text-sec);">Full Name</p>
                    <div class="font-700 text-lg" style="color:var(--tn-text);">{{ $trainee->name }}</div>
                </div>

                {{-- Email --}}
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--tn-text-sec);">Email Address</p>
                    <div class="font-700 text-lg" style="color:var(--tn-text);">{{ $trainee->email }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Enrollments Card --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--tn-surface); border-color:var(--tn-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        <div class="p-8">
            <h3 class="text-lg font-700 mb-4" style="color:var(--tn-text);">
                <i class="fas fa-book mr-2" style="color:var(--tn-accent);"></i> Course Enrollments
            </h3>

            <div class="space-y-4">
                @forelse($enrollments as $enrollment)
                    <div class="rounded-xl p-4 border" style="background:var(--tn-surface); border-color:var(--tn-border);">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h4 class="font-700" style="color:var(--tn-text);">{{ $enrollment->course->name }}</h4>
                                <p class="text-sm mt-1" style="color:var(--tn-text-sec);">{{ $enrollment->course->code ?? 'N/A' }}</p>
                            </div>
                            @php
                                $statusStyles = [
                                    'pending' => ['bg' => 'var(--tn-gold-bg)',   'color' => 'var(--tn-gold)',    'label' => 'Pending'],
                                    'approved' => ['bg' => 'var(--tn-green-bg)',  'color' => 'var(--tn-green)',   'label' => 'Approved'],
                                    'completed' => ['bg' => 'var(--tn-accent-bg)', 'color' => 'var(--tn-accent)',  'label' => 'Completed'],
                                    'dropped' => ['bg' => 'var(--tn-red-bg)',    'color' => 'var(--tn-red)',     'label' => 'Dropped'],
                                ];
                                $style = $statusStyles[$enrollment->status] ?? ['bg' => '#f0f5ff', 'color' => '#5a7aaa'];
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-xs font-700"
                                  style="background:{{ $style['bg'] }}; color:{{ $style['color'] }};">
                                {{ $style['label'] }}
                            </span>
                        </div>
                        <div class="grid grid-cols-3 gap-4 pt-3" style="border-top:1px solid var(--tn-border);">
                            <div>
                                <p class="text-xs font-600" style="color:var(--tn-text-sec);">Level</p>
                                <p class="font-700 mt-1" style="color:var(--tn-text);">{{ $enrollment->course->level ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-600" style="color:var(--tn-text-sec);">Duration</p>
                                <p class="font-700 mt-1" style="color:var(--tn-text);">{{ $enrollment->course->duration_hours ?? 'N/A' }}h</p>
                            </div>
                            <div>
                                <p class="text-xs font-600" style="color:var(--tn-text-sec);">Enrolled</p>
                                <p class="font-700 mt-1" style="color:var(--tn-text);">{{ $enrollment->created_at?->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center rounded-xl" style="background:var(--tn-accent-bg);">
                        <p style="color:var(--tn-accent);">No enrollments found</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Attendance Summary Card --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--tn-surface); border-color:var(--tn-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        <div class="p-8">
            <h3 class="text-lg font-700 mb-4" style="color:var(--tn-text);">
                <i class="fas fa-calendar-check mr-2" style="color:var(--tn-green);"></i> Attendance Summary
            </h3>

            @if(count($attendanceSummary) > 0)
                <div class="space-y-4">
                    @foreach($enrollments as $enrollment)
                        @if(isset($attendanceSummary[$enrollment->id]))
                            @php $summary = $attendanceSummary[$enrollment->id]; @endphp
                            <div class="rounded-xl p-4 border" style="background:var(--tn-surface); border-color:var(--tn-border);">
                                <p class="text-sm font-700 mb-3" style="color:var(--tn-text);">{{ $enrollment->course->name }}</p>
                                <div class="grid grid-cols-4 gap-3">
                                    <div class="text-center">
                                        <p class="font-800 text-lg" style="color:var(--tn-accent);">{{ $summary['total'] }}</p>
                                        <p class="text-xs font-600" style="color:var(--tn-text-sec);">Total</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="font-800 text-lg" style="color:var(--tn-green);">{{ $summary['present'] }}</p>
                                        <p class="text-xs font-600" style="color:var(--tn-text-sec);">Present</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="font-800 text-lg" style="color:var(--tn-gold);">{{ $summary['late'] }}</p>
                                        <p class="text-xs font-600" style="color:var(--tn-text-sec);">Late</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="font-800 text-lg" style="color:var(--tn-red);">{{ $summary['absent'] }}</p>
                                        <p class="text-xs font-600" style="color:var(--tn-text-sec);">Absent</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="p-6 text-center rounded-xl" style="background:var(--tn-accent-bg);">
                    <p style="color:var(--tn-accent);">No attendance records found</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Assessments Card --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--tn-surface); border-color:var(--tn-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        <div class="p-8">
            <h3 class="text-lg font-700 mb-4" style="color:var(--tn-text);">
                <i class="fas fa-clipboard-check mr-2" style="color:var(--tn-accent);"></i> Assessments
            </h3>

            @if($assessments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="background:var(--tn-accent-bg); border-bottom:1px solid var(--tn-border);">
                                <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--tn-accent);">Course</th>
                                <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--tn-accent);">Score</th>
                                <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--tn-accent);">Result</th>
                                <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--tn-accent);">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color:var(--tn-border);">
                            @foreach($assessments as $assessment)
                                <tr style="background:var(--tn-surface);">
                                    <td class="px-5 py-3 text-sm font-600" style="color:var(--tn-text);">
                                        {{ $assessment->enrollment->course->name }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="font-700" style="color:var(--tn-accent);">
                                            {{ $assessment->score !== null ? $assessment->score . '%' : '—' }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-3">
                                        @php
                                            $resultStyles = [
                                                'competent' => ['bg' => 'var(--tn-green-bg)',  'color' => 'var(--tn-green)', 'label' => 'Competent'],
                                                'not_yet_competent' => ['bg' => 'var(--tn-red-bg)', 'color' => 'var(--tn-red)', 'label' => 'Not Yet Competent'],
                                            ];
                                            $style = $resultStyles[$assessment->result] ?? ['bg' => '#f0f5ff', 'color' => '#5a7aaa'];
                                        @endphp
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-700"
                                              style="background:{{ $style['bg'] }}; color:{{ $style['color'] }};">
                                            {{ $style['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-sm font-600" style="color:var(--tn-text-sec);">
                                        {{ $assessment->assessed_at?->format('M d, Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6 text-center rounded-xl" style="background:var(--tn-accent-bg);">
                    <p style="color:var(--tn-accent);">No assessments found</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('trainer.trainees.index') }}"
           class="px-5 py-2.5 rounded-xl text-sm font-600 border transition hover:bg-[#e8f0fb]"
           style="border-color:var(--tn-border); color:var(--tn-text-sec);">
            <i class="fas fa-arrow-left mr-1"></i> Back to Trainees
        </a>
    </div>

</div>
@endsection
