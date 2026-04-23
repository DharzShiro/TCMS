@extends('layouts.app')

@section('title', $course->name)

@section('content')
<style>
:root {
  --crsd-surface:     #ffffff;
  --crsd-border:      #c5d8f5;
  --crsd-text:        #001a4d;
  --crsd-text-sec:    #5a7aaa;
  --crsd-accent:      #0057B8;
  --crsd-accent-bg:   rgba(0,87,184,0.10);
  --crsd-red:         #CE1126;
  --crsd-red-bg:      rgba(206,17,38,0.15);
  --crsd-gold:        #F5C518;
  --crsd-gold-bg:     rgba(245,197,24,0.15);
  --crsd-green:       #16a34a;
  --crsd-green-bg:    rgba(22,163,74,0.12);
}
.dark {
  --crsd-surface:     #0d1f3c;
  --crsd-border:      #1e3a6b;
  --crsd-text:        #dde8ff;
  --crsd-text-sec:    #9ca3af;
  --crsd-accent:      #5ba3f5;
  --crsd-accent-bg:   rgba(91,163,245,0.15);
  --crsd-red:         #ff6b7a;
  --crsd-red-bg:      rgba(255,107,122,0.15);
  --crsd-gold:        #fcd34d;
  --crsd-gold-bg:     rgba(252,211,77,0.15);
  --crsd-green:       #4ade80;
  --crsd-green-bg:    rgba(74,222,128,0.12);
}
</style>

<div class="max-w-3xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('trainee.courses.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition hover:bg-[#e8f0fb]"
           style="border-color:var(--crsd-border); color:var(--crsd-text-sec);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--crsd-accent);">
                <i class="fas fa-book mr-2" style="color:var(--crsd-red);"></i> Course Details
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--crsd-text-sec);">Viewing details for {{ $course->name }}</p>
        </div>
    </div>

    {{-- Course Card --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--crsd-surface); border-color:var(--crsd-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        {{-- TESDA color stripe --}}
        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        {{-- Course Hero --}}
        <div class="p-8 flex flex-col sm:flex-row items-start sm:items-center gap-6"
             style="background:linear-gradient(135deg, #003087 0%, #0057B8 100%); position:relative; overflow:hidden;">

            {{-- Background circles --}}
            <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,0.05);"></div>
            <div style="position:absolute;bottom:-40px;left:-20px;width:120px;height:120px;border-radius:50%;background:rgba(245,197,24,0.07);"></div>

            {{-- Icon --}}
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl text-white flex-shrink-0"
                 style="background:rgba(255,255,255,0.15); border:2px solid rgba(255,255,255,0.20); position:relative; z-index:1;">
                <i class="fas fa-book"></i>
            </div>

            {{-- Course name & badges --}}
            <div style="position:relative; z-index:1;">
                <div class="text-xl font-bold text-white">{{ $course->name }}</div>
                <div class="text-sm mt-0.5" style="color:rgba(255,255,255,0.65);">{{ $course->code }}</div>
                <div class="flex flex-wrap gap-2 mt-3">
                    {{-- Status badge --}}
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold"
                          style="background:rgba(22,163,74,0.20); border:1px solid rgba(22,163,74,0.35); color:#4ade80;">
                        <i class="fas fa-check-circle mr-1" style="font-size:9px;"></i>
                        {{ ucfirst($course->status) }}
                    </span>
                    {{-- Level badge --}}
                    @if($course->level)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold"
                              style="background:rgba(245,197,24,0.20); border:1px solid rgba(245,197,24,0.35); color:var(--crsd-gold);">
                            <i class="fas fa-layer-group mr-1" style="font-size:9px;"></i>
                            {{ $course->level }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Enroll / Already Enrolled Button --}}
            <div style="position:relative; z-index:1;" class="sm:ml-auto">
                @if(!$existingEnrollment)
                    <form method="POST" action="{{ route('trainee.courses.enroll', $course) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-semibold transition hover:-translate-y-0.5"
                                style="background:rgba(22,163,74,0.85); border:1px solid rgba(0,0,0,0.10);">
                            <i class="fas fa-check"></i> Enroll Now
                        </button>
                    </form>
                @else
                    <button type="button" disabled
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-semibold opacity-75 cursor-not-allowed"
                            style="background:rgba(255,255,255,0.20);">
                        <i class="fas fa-check-circle"></i> Already Enrolled
                    </button>
                @endif
            </div>
        </div>

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="mx-8 mt-6 p-4 rounded-xl border text-sm font-medium"
                 style="background:var(--crsd-red-bg); border-color:var(--crsd-red); color:var(--crsd-red);">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Detail rows --}}
        <div class="p-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
            @php
                $details = [
                    [
                        'icon'  => 'fa-hashtag',
                        'color' => 'var(--crsd-red)',
                        'bg'    => 'var(--crsd-red-bg)',
                        'label' => 'Course Code',
                        'value' => $course->code,
                    ],
                    [
                        'icon'  => 'fa-clock',
                        'color' => 'var(--crsd-accent)',
                        'bg'    => 'var(--crsd-accent-bg)',
                        'label' => 'Duration',
                        'value' => $course->duration_hours . ' hours',
                    ],
                    [
                        'icon'  => 'fa-layer-group',
                        'color' => '#b38a00',
                        'bg'    => 'var(--crsd-gold-bg)',
                        'label' => 'Level',
                        'value' => $course->level ?? '—',
                    ],
                    [
                        'icon'  => 'fa-users',
                        'color' => 'var(--crsd-accent)',
                        'bg'    => 'var(--crsd-accent-bg)',
                        'label' => 'Total Enrollments',
                        'value' => $course->enrollments_count,
                    ],
                ];
            @endphp

            @foreach($details as $d)
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs flex-shrink-0"
                         style="background:{{ $d['bg'] }}; color:{{ $d['color'] }};">
                        <i class="fas {{ $d['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide mb-0.5"
                             style="color:var(--crsd-text-sec);">{{ $d['label'] }}</div>
                        <div class="text-sm font-medium" style="color:var(--crsd-text);">
                            {{ $d['value'] ?? '—' }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Description --}}
        @if($course->description)
            <div class="px-8 pb-8">
                <h3 class="text-sm font-semibold mb-3" style="color:var(--crsd-text);">
                    <i class="fas fa-align-left mr-2" style="color:var(--crsd-text-sec);"></i>
                    Course Description
                </h3>
                <div class="p-4 rounded-xl text-sm leading-relaxed"
                     style="background:var(--crsd-accent-bg); color:var(--crsd-text-sec);">
                    {{ $course->description }}
                </div>
            </div>
        @endif
    </div>

    {{-- Available Schedules --}}
    @if($course->schedules->count() > 0)
        <div class="space-y-4">
            <h2 class="text-lg font-bold" style="color:var(--crsd-accent);">
                <i class="fas fa-calendar-check mr-2" style="color:var(--crsd-gold);"></i>
                Available Schedules
            </h2>

            <div class="space-y-3">
                @foreach($course->schedules as $schedule)
                    @php
                        $statusStyle = match($schedule->status) {
                            'upcoming' => ['bg' => 'var(--crsd-accent-bg)', 'color' => 'var(--crsd-accent)'],
                            'ongoing'  => ['bg' => 'var(--crsd-green-bg)',  'color' => 'var(--crsd-green)'],
                            default    => ['bg' => 'var(--crsd-gold-bg)',   'color' => 'var(--crsd-gold)'],
                        };
                    @endphp
                    <div class="rounded-2xl border p-5"
                         style="background:var(--crsd-surface); border-color:var(--crsd-border);">

                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <h3 class="font-semibold text-sm" style="color:var(--crsd-text);">
                                    {{ $schedule->start_date?->format('F d, Y') }}
                                    —
                                    {{ $schedule->end_date?->format('F d, Y') }}
                                </h3>
                                @if($schedule->trainer)
                                    <p class="text-xs mt-1" style="color:var(--crsd-text-sec);">
                                        Trainer:
                                        <span class="font-medium" style="color:var(--crsd-text);">
                                            {{ $schedule->trainer->name }}
                                        </span>
                                    </p>
                                @endif
                            </div>
                            <span class="px-3 py-1 rounded-lg text-xs font-semibold flex-shrink-0"
                                  style="background:{{ $statusStyle['bg'] }}; color:{{ $statusStyle['color'] }};">
                                {{ ucfirst($schedule->status) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs" style="color:var(--crsd-text-sec);">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-clock" style="color:var(--crsd-accent); width:14px;"></i>
                                <span>{{ $schedule->time_start }} – {{ $schedule->time_end }}</span>
                            </div>
                            @if($schedule->location)
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-map-marker-alt" style="color:var(--crsd-red); width:14px;"></i>
                                    <span>{{ $schedule->location }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="rounded-2xl border p-8 text-center"
             style="background:var(--crsd-surface); border-color:var(--crsd-border);">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3"
                 style="background:var(--crsd-accent-bg);">
                <i class="fas fa-calendar-times" style="color:var(--crsd-accent);"></i>
            </div>
            <p class="text-sm font-medium" style="color:var(--crsd-text-sec);">No schedules available for this course yet.</p>
        </div>
    @endif

    {{-- Footer Actions --}}
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('trainee.courses.index') }}"
           class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-medium border transition hover:bg-[#f0f5ff]"
           style="border-color:var(--crsd-border); color:var(--crsd-text-sec);">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </a>
        @if($existingEnrollment)
            <a href="{{ route('trainee.enrollments.show', $existingEnrollment) }}"
               class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-medium transition hover:-translate-y-0.5"
               style="background:linear-gradient(135deg, var(--crsd-accent), #003087); box-shadow:0 3px 12px rgba(0,87,184,0.20);">
                <i class="fas fa-eye"></i> View My Enrollment
            </a>
        @endif
    </div>

</div>
@endsection