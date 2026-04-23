@extends('layouts.app')

@section('title', 'Schedule Details')

@section('content')
<style>
:root {
  --sch-surface:      #ffffff;
  --sch-border:       #c5d8f5;
  --sch-text:         #001a4d;
  --sch-text-sec:     #5a7aaa;
  --sch-accent:       #0057B8;
  --sch-accent-bg:    rgba(0,87,184,0.10);
  --sch-red:          #CE1126;
  --sch-red-bg:       rgba(206,17,38,0.15);
  --sch-green:        #16a34a;
  --sch-green-bg:     rgba(22,163,74,0.15);
  --sch-gold:         #F5C518;
  --sch-gold-bg:      rgba(245,197,24,0.15);
}
.dark {
  --sch-surface:      #0d1f3c;
  --sch-border:       #1e3a6b;
  --sch-text:         #dde8ff;
  --sch-text-sec:     #9ca3af;
  --sch-accent:       #5ba3f5;
  --sch-accent-bg:    rgba(91,163,245,0.15);
  --sch-red:          #ff6b7a;
  --sch-red-bg:       rgba(255,107,122,0.15);
  --sch-green:        #36d399;
  --sch-green-bg:     rgba(54,211,153,0.15);
  --sch-gold:         #fcd34d;
  --sch-gold-bg:      rgba(252,211,77,0.15);
}
</style>
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('trainer.schedules.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition hover:bg-[#e8f0fb]"
           style="border-color:var(--sch-border); color:var(--sch-text-sec);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--sch-accent);">
                <i class="fas fa-calendar-alt mr-2" style="color:var(--sch-red);"></i> Schedule Details
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--sch-text-sec);">View training schedule information</p>
        </div>
    </div>

    {{-- Course & Trainer Info Card --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--sch-surface); border-color:var(--sch-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>
        
        <div class="p-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Course Info --}}
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--sch-text-sec);">Course</p>
                    <div class="font-700 text-lg" style="color:var(--sch-text);">{{ $trainingSchedule->course->name }}</div>
                    <p class="text-sm mt-1" style="color:var(--sch-text-sec);">{{ $trainingSchedule->course->code ?? 'N/A' }}</p>
                </div>

                {{-- Trainer Info --}}
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--sch-text-sec);">Trainer</p>
                    <div class="font-700 text-lg" style="color:var(--sch-text);">{{ $trainingSchedule->trainer->name }}</div>
                    <p class="text-sm mt-1" style="color:var(--sch-text-sec);">{{ $trainingSchedule->trainer->email }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Schedule Details Card --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--sch-surface); border-color:var(--sch-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>
        
        <div class="p-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Status --}}
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--sch-text-sec);">Status</p>
                    @php
                        $statusStyles = [
                            'upcoming' => ['bg' => 'var(--sch-accent-bg)',  'color' => 'var(--sch-accent)',  'label' => 'Upcoming'],
                            'ongoing'  => ['bg' => 'var(--sch-green-bg)',   'color' => 'var(--sch-green)',   'label' => 'Ongoing'],
                            'completed' => ['bg' => 'var(--sch-gold-bg)',   'color' => 'var(--sch-gold)',    'label' => 'Completed'],
                            'cancelled' => ['bg' => 'var(--sch-red-bg)',    'color' => 'var(--sch-red)',     'label' => 'Cancelled'],
                        ];
                        $style = $statusStyles[$trainingSchedule->status] ?? ['bg' => '#f0f5ff', 'color' => '#5a7aaa'];
                    @endphp
                    <span class="inline-block px-3 py-1.5 rounded-lg font-700 text-sm"
                          style="background:{{ $style['bg'] }}; color:{{ $style['color'] }};">
                        {{ $style['label'] }}
                    </span>
                </div>

                {{-- Duration --}}
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--sch-text-sec);">Duration</p>
                    <div class="font-700 text-lg" style="color:var(--sch-text);">{{ $trainingSchedule->course->duration_hours ?? 'N/A' }} hours</div>
                </div>
            </div>

            {{-- Divider --}}
            <div style="height:1px; background:var(--sch-border);"></div>

            {{-- Dates --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--sch-text-sec);">Start Date</p>
                    <div class="font-700 text-lg" style="color:var(--sch-text);">{{ $trainingSchedule->start_date?->format('M d, Y') }}</div>
                    <p class="text-sm mt-1" style="color:var(--sch-text-sec);">{{ $trainingSchedule->start_date?->format('l') }}</p>
                </div>

                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--sch-text-sec);">End Date</p>
                    <div class="font-700 text-lg" style="color:var(--sch-text);">{{ $trainingSchedule->end_date?->format('M d, Y') }}</div>
                    <p class="text-sm mt-1" style="color:var(--sch-text-sec);">{{ $trainingSchedule->end_date?->format('l') }}</p>
                </div>
            </div>

            {{-- Divider --}}
            <div style="height:1px; background:var(--sch-border);"></div>

            {{-- Time --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--sch-text-sec);">Start Time</p>
                    <div class="font-700 text-lg" style="color:var(--sch-text);">{{ $trainingSchedule->time_start ?? 'N/A' }}</div>
                </div>

                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--sch-text-sec);">End Time</p>
                    <div class="font-700 text-lg" style="color:var(--sch-text);">{{ $trainingSchedule->time_end ?? 'N/A' }}</div>
                </div>
            </div>

            @if($trainingSchedule->location)
            {{-- Divider --}}
            <div style="height:1px; background:var(--sch-border);"></div>

            {{-- Location --}}
            <div>
                <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--sch-text-sec);">Location</p>
                <div class="font-700 text-lg" style="color:var(--sch-text);">{{ $trainingSchedule->location }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Enrollments Card --}}
    @if($enrollments->count() > 0)
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--sch-surface); border-color:var(--sch-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        <div class="p-8">
            <h3 class="text-lg font-700 mb-4" style="color:var(--sch-text);">
                <i class="fas fa-user-graduate mr-2" style="color:var(--sch-accent);"></i> Enrolled Trainees
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:var(--sch-accent-bg); border-bottom:1px solid var(--sch-border);">
                            <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--sch-accent);">Trainee</th>
                            <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--sch-accent);">Email</th>
                            <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--sch-accent);">Enrollment Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color:var(--sch-border);">
                        @foreach($enrollments as $enrollment)
                            <tr style="background:var(--sch-surface);">
                                <td class="px-5 py-3">
                                    <div class="font-600" style="color:var(--sch-text);">{{ $enrollment->trainee->name }}</div>
                                </td>
                                <td class="px-5 py-3 text-sm" style="color:var(--sch-text-sec);">
                                    {{ $enrollment->trainee->email }}
                                </td>
                                <td class="px-5 py-3 text-sm font-600" style="color:var(--sch-text-sec);">
                                    {{ $enrollment->created_at?->format('M d, Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="rounded-2xl border p-8 text-center"
         style="background:var(--sch-surface); border-color:var(--sch-border);">
        <i class="fas fa-inbox text-2xl opacity-50 mb-2 block"></i>
        <p class="text-sm" style="color:var(--sch-text-sec);">No trainees enrolled in this schedule yet.</p>
    </div>
    @endif

    {{-- Actions --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('trainer.schedules.index') }}"
           class="px-5 py-2.5 rounded-xl text-sm font-600 border transition hover:bg-[#e8f0fb]"
           style="border-color:var(--sch-border); color:var(--sch-text-sec);">
            <i class="fas fa-arrow-left mr-1"></i> Back to Schedules
        </a>
    </div>

</div>
@endsection
