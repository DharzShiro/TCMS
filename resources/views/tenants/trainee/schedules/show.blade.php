@extends('layouts.app')

@section('title', 'Schedule Details')

@section('content')
<style>
  :root {
    --sch-surface: #ffffff;
    --sch-border: #c5d8f5;
    --sch-text: #001a4d;
    --sch-text-sec: #5a7aaa;
    --sch-accent: #0057B8;
    --sch-accent-bg: #e8f0fb;
    --sch-red: #CE1126;
    --sch-red-bg: #fff0f2;
    --sch-gold: #F5C518;
    --sch-gold-bg: rgba(245, 197, 24, 0.15);
    --sch-green: #16a34a;
    --sch-green-bg: rgba(22, 163, 74, 0.15);
  }
  .dark {
    --sch-surface: #0d1f3c;
    --sch-border: #1e3a6b;
    --sch-text: #dde8ff;
    --sch-text-sec: #3a5a8a;
    --sch-accent: #0057B8;
    --sch-accent-bg: #122550;
    --sch-red: #CE1126;
    --sch-red-bg: #5a0a0a;
    --sch-gold: #F5C518;
    --sch-gold-bg: rgba(245, 197, 24, 0.1);
    --sch-green: #16a34a;
    --sch-green-bg: rgba(22, 163, 74, 0.1);
  }
</style>

<div class="max-w-3xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('trainee.schedules.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition hover:bg-[#e8f0fb]"
           style="border-color:var(--sch-border); color:var(--sch-text-sec);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--sch-accent);">
                <i class="fas fa-calendar-alt mr-2" style="color:var(--sch-red);"></i> Schedule Details
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--sch-text-sec);">{{ $trainingSchedule->course->name }}</p>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="rounded-2xl border overflow-hidden dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--sch-surface); border-color:var(--sch-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        {{-- Header --}}
        <div class="p-8 flex flex-col sm:flex-row items-start sm:items-center gap-6"
             style="background: linear-gradient(135deg, #003087 0%, #0057B8 100%); position:relative; overflow:hidden;">
            <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,0.05);"></div>
            <div style="position:absolute;bottom:-40px;left:-20px;width:120px;height:120px;border-radius:50%;background:rgba(245,197,24,0.07);"></div>

            <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl font-900 text-white flex-shrink-0"
                 style="background:rgba(255,255,255,0.15); border:2px solid rgba(255,255,255,0.20); position:relative; z-index:1;">
                <i class="fas fa-calendar-alt"></i>
            </div>

            <div style="position:relative;z-index:1;">
                <div class="text-xl font-800 text-white">{{ $trainingSchedule->course->name }}</div>
                <div class="text-sm mt-0.5" style="color:rgba(255,255,255,0.65);">{{ $trainingSchedule->course->code }}</div>
                <div class="flex flex-wrap gap-2 mt-3">
                    @php
                        $statusColors = [
                            'scheduled' => ['bg' => 'rgba(245,197,24,0.25)', 'color' => '#fcd34d', 'icon' => 'fa-calendar'],
                            'ongoing' => ['bg' => 'rgba(91,163,245,0.25)', 'color' => '#93c5fd', 'icon' => 'fa-play-circle'],
                            'completed' => ['bg' => 'rgba(54,211,153,0.25)', 'color' => '#86efac', 'icon' => 'fa-check-double'],
                        ];
                        $statusColor = $statusColors[$trainingSchedule->status] ?? $statusColors['scheduled'];
                    @endphp
                    <span class="px-2.5 py-1 rounded-lg text-xs font-700"
                          style="background:{{ $statusColor['bg'] }}; border:1px solid {{ $statusColor['color'] }}; color:{{ $statusColor['color'] }};">
                        <i class="fas {{ $statusColor['icon'] }} mr-1"></i> {{ ucfirst($trainingSchedule->status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-8 space-y-8">

            {{-- Schedule Timeline --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-xl border p-5" style="background:var(--sch-accent-bg); border-color:var(--sch-border);">
                    <div class="flex items-start gap-3.5">
                        <i class="fas fa-calendar-check text-lg mt-1" style="color:var(--sch-accent);"></i>
                        <div class="flex-1">
                            <div class="text-xs font-700 uppercase tracking-wider" style="color:var(--sch-text-sec);">Start Date</div>
                            <div class="text-lg font-800 mt-1" style="color:var(--sch-text);">
                                {{ \Carbon\Carbon::parse($trainingSchedule->start_date)->format('M d, Y') }}
                            </div>
                            <div class="text-xs mt-0.5" style="color:var(--sch-text-sec);">
                                {{ \Carbon\Carbon::parse($trainingSchedule->start_date)->format('l') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border p-5" style="background:var(--sch-gold-bg); border-color:var(--sch-border);">
                    <div class="flex items-start gap-3.5">
                        <i class="fas fa-calendar-times text-lg mt-1" style="color:var(--sch-gold);"></i>
                        <div class="flex-1">
                            <div class="text-xs font-700 uppercase tracking-wider" style="color:var(--sch-text-sec);">End Date</div>
                            <div class="text-lg font-800 mt-1" style="color:var(--sch-text);">
                                {{ \Carbon\Carbon::parse($trainingSchedule->end_date)->format('M d, Y') }}
                            </div>
                            <div class="text-xs mt-0.5" style="color:var(--sch-text-sec);">
                                {{ \Carbon\Carbon::parse($trainingSchedule->end_date)->format('l') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Location & Capacity --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-xl border p-5" style="background:var(--sch-accent-bg); border-color:var(--sch-border);">
                    <div class="flex items-start gap-3.5">
                        <i class="fas fa-map-marker-alt text-lg mt-1" style="color:var(--sch-red);"></i>
                        <div class="flex-1">
                            <div class="text-xs font-700 uppercase tracking-wider" style="color:var(--sch-text-sec);">Location</div>
                            <div class="text-base font-700 mt-1" style="color:var(--sch-text);">{{ $trainingSchedule->location }}</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border p-5" style="background:var(--sch-green-bg); border-color:var(--sch-border);">
                    <div class="flex items-start gap-3.5">
                        <i class="fas fa-users text-lg mt-1" style="color:var(--sch-green);"></i>
                        <div class="flex-1">
                            <div class="text-xs font-700 uppercase tracking-wider" style="color:var(--sch-text-sec);">Capacity</div>
                            <div class="text-base font-700 mt-1" style="color:var(--sch-text);">{{ $trainingSchedule->capacity }} participants</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Divider --}}
            <div style="height:1px; background:var(--sch-border);"></div>

            {{-- Trainer Information --}}
            <div>
                <h3 class="text-lg font-800 mb-4" style="color:var(--sch-text);">
                    <i class="fas fa-user-tie mr-2.5" style="color:var(--sch-red);"></i>
                    Trainer Information
                </h3>
                <div class="rounded-xl border p-6 flex flex-col sm:flex-row items-start sm:items-center gap-6"
                     style="background:var(--sch-accent-bg); border-color:var(--sch-border);">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-900 text-white flex-shrink-0"
                         style="background:var(--sch-accent);">
                        {{ strtoupper(substr($trainingSchedule->trainer->name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <div class="text-base font-800" style="color:var(--sch-text);">{{ $trainingSchedule->trainer->name }}</div>
                        <div class="text-sm mt-1" style="color:var(--sch-text-sec);">
                            <i class="fas fa-envelope mr-2" style="color:var(--sch-red);"></i>
                            {{ $trainingSchedule->trainer->email }}
                        </div>
                        @if ($trainingSchedule->trainer->phone)
                            <div class="text-sm mt-1.5" style="color:var(--sch-text-sec);">
                                <i class="fas fa-phone mr-2" style="color:var(--sch-red);"></i>
                                {{ $trainingSchedule->trainer->phone }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Course Description --}}
            <div>
                <h3 class="text-lg font-800 mb-4" style="color:var(--sch-text);">
                    <i class="fas fa-book mr-2.5" style="color:var(--sch-red);"></i>
                    Course Information
                </h3>
                <div class="rounded-xl border p-6" style="background:rgba(0,87,184,0.02); border-color:var(--sch-border);">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        <div>
                            <div class="text-xs font-700 uppercase tracking-wider" style="color:var(--sch-text-sec);">Course Code</div>
                            <div class="text-base font-700 mt-1.5" style="color:var(--sch-text);">{{ $trainingSchedule->course->code }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-700 uppercase tracking-wider" style="color:var(--sch-text-sec);">Duration</div>
                            <div class="text-base font-700 mt-1.5" style="color:var(--sch-text);">{{ $trainingSchedule->course->duration_hours }} hours</div>
                        </div>
                        <div>
                            <div class="text-xs font-700 uppercase tracking-wider" style="color:var(--sch-text-sec);">Level</div>
                            <div class="text-base font-700 mt-1.5" style="color:var(--sch-text);">{{ ucfirst($trainingSchedule->course->level) }}</div>
                        </div>
                    </div>
                    <div style="height:1px; background:var(--sch-border); margin-bottom:1.5rem;"></div>
                    <div>
                        <div class="text-xs font-700 uppercase tracking-wider mb-2.5" style="color:var(--sch-text-sec);">Description</div>
                        <p style="color:var(--sch-text); line-height:1.6;">
                            {{ $trainingSchedule->course->description ?? 'No description available.' }}
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-col sm:flex-row gap-3 justify-end">
        <a href="{{ route('trainee.schedules.index') }}"
           class="px-5 py-2.5 rounded-xl font-600 text-sm transition border"
           style="border-color:var(--sch-border); background:var(--sch-surface); color:var(--sch-text);">
            <i class="fas fa-arrow-left mr-1.5"></i> Back to Schedules
        </a>
        <a href="{{ route('trainee.courses.show', $trainingSchedule->course) }}"
           class="px-5 py-2.5 rounded-xl font-600 text-sm transition text-white hover:opacity-90"
           style="background:var(--sch-accent);">
            <i class="fas fa-book mr-1.5"></i> View Course
        </a>
    </div>

</div>
@endsection
