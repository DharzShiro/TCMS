@extends('layouts.app')

@section('title', 'Edit Attendance')

@section('content')
<style>
:root {
  --att-surface:     #ffffff;
  --att-border:      #c5d8f5;
  --att-text:        #001a4d;
  --att-text-sec:    #5a7aaa;
  --att-accent:      #0057B8;
  --att-accent-bg:   rgba(0,87,184,0.10);
  --att-red:         #CE1126;
  --att-red-bg:      rgba(206,17,38,0.15);
}
.dark {
  --att-surface:     #0d1f3c;
  --att-border:      #1e3a6b;
  --att-text:        #dde8ff;
  --att-text-sec:    #9ca3af;
  --att-accent:      #5ba3f5;
  --att-accent-bg:   rgba(91,163,245,0.15);
  --att-red:         #ff6b7a;
  --att-red-bg:      rgba(255,107,122,0.15);
}
</style>
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('trainer.attendances.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition hover:bg-[#e8f0fb]"
           style="border-color:var(--att-border); color:var(--att-text-sec);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--att-accent);">
                <i class="fas fa-edit mr-2" style="color:var(--att-red);"></i> Edit Attendance
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--att-text-sec);">Update attendance record</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="rounded-2xl border overflow-hidden dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--att-surface); border-color:var(--att-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        <form method="POST" action="{{ route('trainer.attendances.update', $attendance) }}" class="p-8 space-y-6">
            @csrf
            @method('PUT')

            {{-- Enrollment Selection --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--att-text-sec);">
                    Trainee <span style="color:var(--att-red);">*</span>
                </label>
                <select name="enrollment_id"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                               dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                        style="border-color:{{ $errors->has('enrollment_id') ? 'var(--att-red)' : 'var(--att-border)' }}; color:var(--att-text);"
                        onfocus="this.style.borderColor='var(--att-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                        onblur="this.style.borderColor='{{ $errors->has('enrollment_id') ? 'var(--att-red)' : 'var(--att-border)' }}'; this.style.boxShadow='none'">
                    <option value="">Select a Trainee</option>
                    @foreach($enrollments as $enrollment)
                        <option value="{{ $enrollment->id }}" {{ old('enrollment_id', $attendance->enrollment_id) == $enrollment->id ? 'selected' : '' }}>
                            {{ $enrollment->trainee->name }} - {{ $enrollment->course->name }}
                        </option>
                    @endforeach
                </select>
                @error('enrollment_id')
                    <p class="text-xs mt-1" style="color:var(--att-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Attendance Date --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--att-text-sec);">
                    Date <span style="color:var(--att-red);">*</span>
                </label>
                <input type="date" name="date" value="{{ old('date', $attendance->date?->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:{{ $errors->has('date') ? 'var(--att-red)' : 'var(--att-border)' }}; color:var(--att-text);"
                       onfocus="this.style.borderColor='var(--att-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('date') ? 'var(--att-red)' : 'var(--att-border)' }}'; this.style.boxShadow='none'">
                @error('date')
                    <p class="text-xs mt-1" style="color:var(--att-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Attendance Status --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--att-text-sec);">
                    Status <span style="color:var(--att-red);">*</span>
                </label>
                <div class="grid grid-cols-3 gap-4">
                    @foreach(['present' => ['label' => 'Present', 'icon' => 'fa-check', 'color' => '#16a34a'], 'late' => ['label' => 'Late', 'icon' => 'fa-clock', 'color' => '#b38a00'], 'absent' => ['label' => 'Absent', 'icon' => 'fa-times', 'color' => '#CE1126']] as $value => $option)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="status" value="{{ $value }}" {{ old('status', $attendance->status) === $value ? 'checked' : '' }}
                                   class="sr-only peer" style="color:var(--att-accent);">
                            <div class="p-4 rounded-xl border-2 text-center transition peer-checked:border-[color:var(--att-accent)] peer-checked:bg-[rgba(0,87,184,0.05)]"
                                 style="border-color:var(--att-border); color:var(--att-text-sec);">
                                <i class="fas {{ $option['icon'] }} text-2xl mb-1" style="color:{{ $option['color'] }};"></i>
                                <div class="font-600 text-sm">{{ $option['label'] }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('status')
                    <p class="text-xs mt-2" style="color:var(--att-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                        style="background:linear-gradient(135deg,var(--att-accent),#003087); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
                    <i class="fas fa-save"></i> Update Attendance
                </button>
                <a href="{{ route('trainer.attendances.index') }}"
                   class="px-5 py-2.5 rounded-xl text-sm font-600 border transition hover:bg-[#e8f0fb]"
                   style="border-color:var(--att-border); color:var(--att-text-sec);">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
