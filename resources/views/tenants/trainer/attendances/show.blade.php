@extends('layouts.app')

@section('title', 'Attendance Details')

@section('content')
<style>
:root {
  --att-surface:      #ffffff;
  --att-border:       #c5d8f5;
  --att-text:         #001a4d;
  --att-text-sec:     #5a7aaa;
  --att-accent:       #0057B8;
  --att-accent-bg:    rgba(0,87,184,0.10);
  --att-red:          #CE1126;
  --att-red-bg:       rgba(206,17,38,0.15);
  --att-green:        #16a34a;
  --att-green-bg:     rgba(22,163,74,0.15);
  --att-gold:         #F5C518;
  --att-gold-bg:      rgba(245,197,24,0.15);
}
.dark {
  --att-surface:      #0d1f3c;
  --att-border:       #1e3a6b;
  --att-text:         #dde8ff;
  --att-text-sec:     #9ca3af;
  --att-accent:       #5ba3f5;
  --att-accent-bg:    rgba(91,163,245,0.15);
  --att-red:          #ff6b7a;
  --att-red-bg:       rgba(255,107,122,0.15);
  --att-green:        #36d399;
  --att-green-bg:     rgba(54,211,153,0.15);
  --att-gold:         #fcd34d;
  --att-gold-bg:      rgba(252,211,77,0.15);
}
</style>
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('trainer.attendances.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition hover:bg-[#e8f0fb]"
           style="border-color:var(--att-border); color:var(--att-text-sec);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--att-accent);">
                <i class="fas fa-calendar-check mr-2" style="color:var(--att-red);"></i> Attendance Details
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--att-text-sec);">View attendance information</p>
        </div>
    </div>

    {{-- Trainee & Course Info Card --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--att-surface); border-color:var(--att-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>
        
        <div class="p-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Trainee Info --}}
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--att-text-sec);">Trainee</p>
                    <div class="font-700 text-lg" style="color:var(--att-text);">{{ $attendance->enrollment->trainee->name }}</div>
                    <p class="text-sm mt-1" style="color:var(--att-text-sec);">{{ $attendance->enrollment->trainee->email }}</p>
                </div>

                {{-- Course Info --}}
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--att-text-sec);">Course</p>
                    <div class="font-700 text-lg" style="color:var(--att-text);">{{ $attendance->enrollment->course->name }}</div>
                    <p class="text-sm mt-1" style="color:var(--att-text-sec);">{{ $attendance->enrollment->course->code ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Attendance Details Card --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--att-surface); border-color:var(--att-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>
        
        <div class="p-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Date --}}
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--att-text-sec);">Date</p>
                    <div class="font-700 text-lg" style="color:var(--att-text);">{{ $attendance->date?->format('M d, Y') }}</div>
                    <p class="text-sm mt-1" style="color:var(--att-text-sec);">{{ $attendance->date?->format('l') }}</p>
                </div>

                {{-- Status --}}
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--att-text-sec);">Status</p>
                    @php
                        $statusStyles = [
                            'present' => ['bg' => 'var(--att-green-bg)',  'color' => 'var(--att-green)', 'label' => 'Present', 'icon' => 'fa-check'],
                            'absent'  => ['bg' => 'var(--att-red-bg)',    'color' => 'var(--att-red)',   'label' => 'Absent',  'icon' => 'fa-times'],
                            'late'    => ['bg' => 'var(--att-gold-bg)',   'color' => 'var(--att-gold)',  'label' => 'Late',    'icon' => 'fa-clock'],
                        ];
                        $style = $statusStyles[$attendance->status] ?? ['bg' => '#f0f5ff', 'color' => '#5a7aaa'];
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-700 text-sm"
                          style="background:{{ $style['bg'] }}; color:{{ $style['color'] }};">
                        <i class="fas {{ $style['icon'] }}"></i> {{ $style['label'] }}
                    </span>
                </div>
            </div>

            {{-- Divider --}}
            <div style="height:1px; background:var(--att-border);"></div>

            {{-- Metadata --}}
            <div class="grid grid-cols-2 gap-4" style="border-top:1px solid var(--att-border);">
                <div>
                    <p class="text-xs font-600" style="color:var(--att-text-sec);">Recorded At</p>
                    <p class="font-600 mt-1" style="color:var(--att-text);">{{ $attendance->created_at?->format('M d, Y H:i A') }}</p>
                </div>
                <div>
                    <p class="text-xs font-600" style="color:var(--att-text-sec);">Last Updated</p>
                    <p class="font-600 mt-1" style="color:var(--att-text);">{{ $attendance->updated_at?->format('M d, Y H:i A') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('trainer.attendances.edit', $attendance) }}"
           class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
           style="background:linear-gradient(135deg,var(--att-accent),#003087); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
            <i class="fas fa-edit"></i> Edit Attendance
        </a>
        <form method="POST" action="{{ route('trainer.attendances.destroy', $attendance) }}"
              style="display:inline;"
              onsubmit="return confirm('Are you sure you want to delete this attendance record?');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                    style="background:linear-gradient(135deg,var(--att-red),#A50E1E); box-shadow:0 3px 12px rgba(206,17,38,0.28);">
                <i class="fas fa-trash"></i> Delete Attendance
            </button>
        </form>
        <a href="{{ route('trainer.attendances.index') }}"
           class="px-5 py-2.5 rounded-xl text-sm font-600 border transition hover:bg-[#e8f0fb]"
           style="border-color:var(--att-border); color:var(--att-text-sec);">
            Back to Attendance
        </a>
    </div>

</div>
@endsection
