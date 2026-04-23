@extends('layouts.app')

@section('title', 'Attendance Details')

@section('content')
<style>
:root {
  --attd-surface:     #ffffff;
  --attd-border:      #c5d8f5;
  --attd-text:        #001a4d;
  --attd-text-sec:    #5a7aaa;
  --attd-accent:      #0057B8;
  --attd-accent-bg:   rgba(0,87,184,0.10);
  --attd-red:         #CE1126;
  --attd-red-bg:      rgba(206,17,38,0.15);
  --attd-gold:        #F5C518;
  --attd-gold-bg:     rgba(245,197,24,0.15);
  --attd-green:       #22C55E;
  --attd-green-bg:    rgba(34,197,94,0.15);
}
.dark {
  --attd-surface:     #0d1f3c;
  --attd-border:      #1e3a6b;
  --attd-text:        #dde8ff;
  --attd-text-sec:    #9ca3af;
  --attd-accent:      #5ba3f5;
  --attd-accent-bg:   rgba(91,163,245,0.15);
  --attd-red:         #ff6b7a;
  --attd-red-bg:      rgba(255,107,122,0.15);
  --attd-gold:        #fcd34d;
  --attd-gold-bg:     rgba(252,211,77,0.15);
  --attd-green:       #36d399;
  --attd-green-bg:    rgba(54,211,153,0.15);
}
</style>
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.attendances.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition hover:bg-[#e8f0fb]"
           style="border-color:var(--attd-border); color:var(--attd-text-sec);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--attd-accent);">
                <i class="fas fa-clipboard-check mr-2" style="color:var(--attd-red);"></i> Attendance Details
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--attd-text-sec);">Viewing attendance for {{ $attendance->enrollment->trainee->name }}</p>
        </div>
    </div>

    {{-- Attendance Card --}}
    <div class="rounded-2xl border overflow-hidden dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--attd-surface); border-color:var(--attd-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        {{-- Header --}}
        <div class="p-8 flex flex-col sm:flex-row items-start sm:items-center gap-6"
             style="background: linear-gradient(135deg, #003087 0%, #0057B8 100%); position:relative; overflow:hidden;">
            <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,0.05);"></div>
            <div style="position:absolute;bottom:-40px;left:-20px;width:120px;height:120px;border-radius:50%;background:rgba(245,197,24,0.07);"></div>

            <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl font-900 text-white flex-shrink-0"
                 style="background:rgba(255,255,255,0.15); border:2px solid rgba(255,255,255,0.20); position:relative; z-index:1;">
                <i class="fas fa-clipboard-check"></i>
            </div>

            <div style="position:relative;z-index:1;">
                <div class="text-xl font-800 text-white">{{ $attendance->enrollment->trainee->name }}</div>
                <div class="text-sm mt-0.5" style="color:rgba(255,255,255,0.65);">{{ $attendance->enrollment->course->name }}</div>
                <div class="flex flex-wrap gap-2 mt-3">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-700"
                          style="background:{{ match($attendance->status) {
                            'present' => 'rgba(34,197,94,0.25)',
                            'absent' => 'rgba(206,17,38,0.25)',
                            'late' => 'rgba(245,197,24,0.25)',
                            default => 'rgba(107,114,128,0.25)'
                          } }}; border:1px solid {{ match($attendance->status) {
                            'present' => 'rgba(34,197,94,0.40)',
                            'absent' => 'rgba(206,17,38,0.40)',
                            'late' => 'rgba(245,197,24,0.40)',
                            default => 'rgba(107,114,128,0.40)'
                          } }}; color:#fff;">
                        <i class="fas {{ match($attendance->status) {
                            'present' => 'fa-check-circle',
                            'absent' => 'fa-times-circle',
                            'late' => 'fa-hourglass-end',
                            default => 'fa-circle-question'
                        } }} mr-1" style="font-size:9px;"></i> {{ ucfirst($attendance->status) }}
                    </span>
                </div>
            </div>

            <div class="sm:ml-auto flex gap-2" style="position:relative;z-index:1;">
                <a href="{{ route('admin.attendances.edit', $attendance) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-700 transition hover:-translate-y-0.5"
                   style="background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.22); color:#fff;">
                    <i class="fas fa-pen text-xs"></i> Edit
                </a>
            </div>
        </div>

        {{-- Details --}}
        <div class="p-8 space-y-8">
            {{-- Trainee & Course Info --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Trainee --}}
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm flex-shrink-0"
                         style="background:var(--attd-gold-bg); color:var(--attd-gold);">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-700 uppercase tracking-wide mb-0.5" style="color:var(--attd-text-sec);">Trainee</div>
                        <div class="text-sm font-600 dark:text-white" style="color:var(--attd-text);">
                            {{ $attendance->enrollment->trainee->name }}
                        </div>
                        <div class="text-xs mt-1" style="color:var(--attd-text-sec);">
                            {{ $attendance->enrollment->trainee->email }}
                        </div>
                    </div>
                </div>

                {{-- Course --}}
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm flex-shrink-0"
                         style="background:var(--attd-accent-bg); color:var(--attd-accent);">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-700 uppercase tracking-wide mb-0.5" style="color:var(--attd-text-sec);">Course</div>
                        <div class="text-sm font-600 dark:text-white" style="color:var(--attd-text);">
                            {{ $attendance->enrollment->course->name }}
                        </div>
                        <div class="text-xs mt-1" style="color:var(--attd-text-sec);">
                            {{ $attendance->enrollment->course->code }} • {{ $attendance->enrollment->course->duration_hours }} hours
                        </div>
                    </div>
                </div>
            </div>

            <hr style="border-color:var(--attd-border); margin:0;">

            {{-- Attendance Info --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Date --}}
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm flex-shrink-0"
                         style="background:#fff0f2; color:#CE1126;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-700 uppercase tracking-wide mb-0.5" style="color:#5a7aaa;">Date</div>
                        <div class="text-sm font-600 dark:text-white" style="color:#001a4d;">
                            {{ $attendance->date->format('F d, Y') }}
                        </div>
                        <div class="text-xs mt-1" style="color:#5a7aaa;">
                            {{ $attendance->date->format('l') }}
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm flex-shrink-0"
                         style="background:{{ match($attendance->status) {
                            'present' => 'rgba(34,197,94,0.15)',
                            'absent' => 'rgba(206,17,38,0.15)',
                            'late' => 'rgba(245,197,24,0.15)',
                            default => 'rgba(107,114,128,0.15)'
                          } }}; color:{{ match($attendance->status) {
                            'present' => '#22C55E',
                            'absent' => '#CE1126',
                            'late' => '#F5C518',
                            default => '#6B7280'
                          } }};">
                        <i class="fas {{ match($attendance->status) {
                            'present' => 'fa-check-circle',
                            'absent' => 'fa-times-circle',
                            'late' => 'fa-hourglass-end',
                            default => 'fa-circle-question'
                        } }}"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-700 uppercase tracking-wide mb-0.5" style="color:var(--attd-text-sec);">Attendance Status</div>
                        <div class="text-sm font-600 dark:text-white" style="color:{{ match($attendance->status) {
                            'present' => 'var(--attd-green)',
                            'absent' => 'var(--attd-red)',
                            'late' => 'var(--attd-gold)',
                            default => '#6B7280'
                        } }};">
                            {{ ucfirst($attendance->status) }}
                        </div>
                        @if ($attendance->status === 'present')
                            <div class="text-xs mt-1" style="color:var(--attd-text-sec);">Trainee was present</div>
                        @elseif ($attendance->status === 'absent')
                            <div class="text-xs mt-1" style="color:var(--attd-text-sec);">Trainee was absent</div>
                        @else
                            <div class="text-xs mt-1" style="color:var(--attd-text-sec);">Trainee arrived late</div>
                        @endif
                    </div>
                </div>
            </div>

            <hr style="border-color:var(--attd-border); margin:0;">

            {{-- Metadata --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-xs font-700 uppercase tracking-wide mb-1" style="color:var(--attd-text-sec);">Record Created</div>
                    <div class="text-sm font-600 dark:text-white" style="color:var(--attd-text);">
                        {{ $attendance->created_at->format('F d, Y') }}
                    </div>
                    <div class="text-xs mt-0.5" style="color:var(--attd-text-sec);">
                        {{ $attendance->created_at->format('h:i A') }}
                    </div>
                </div>
                <div>
                    <div class="text-xs font-700 uppercase tracking-wide mb-1" style="color:var(--attd-text-sec);">Last Updated</div>
                    <div class="text-sm font-600 dark:text-white" style="color:var(--attd-text);">
                        {{ $attendance->updated_at->format('F d, Y') }}
                    </div>
                    <div class="text-xs mt-0.5" style="color:var(--attd-text-sec);">
                        {{ $attendance->updated_at->format('h:i A') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <a href="{{ route('admin.attendances.edit', $attendance) }}"
           class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
           style="background:linear-gradient(135deg,var(--attd-accent),#003087); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
            <i class="fas fa-pen"></i> Edit Attendance
        </a>
        <form method="POST" action="{{ route('admin.attendances.destroy', $attendance) }}" class="flex-1 sm:flex-initial"
              onsubmit="return confirm('Delete this attendance record? This action cannot be undone.')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-sm font-700 transition hover:scale-105"
                    style="background:var(--attd-red-bg); border:1px solid rgba(206,17,38,0.30); color:var(--attd-red);">
                <i class="fas fa-trash"></i> Delete
            </button>
        </form>
        <a href="{{ route('admin.attendances.index') }}"
           class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-sm font-600 border transition hover:bg-[#e8f0fb]"
           style="border-color:var(--attd-border); color:var(--attd-text-sec);">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

</div>
@endsection
