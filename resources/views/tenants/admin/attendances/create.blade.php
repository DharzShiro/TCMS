@extends('layouts.app')

@section('title', 'Record Attendance')

@section('content')
<style>
:root {
  --attf-surface:     #ffffff;
  --attf-border:      #c5d8f5;
  --attf-text:        #001a4d;
  --attf-text-sec:    #5a7aaa;
  --attf-accent:      #0057B8;
  --attf-accent-bg:   rgba(0,87,184,0.10);
  --attf-red:         #CE1126;
  --attf-red-bg:      rgba(206,17,38,0.15);
  --attf-gold:        #F5C518;
  --attf-gold-bg:     rgba(245,197,24,0.15);
  --attf-green:       #22C55E;
  --attf-green-bg:    rgba(34,197,94,0.15);
}
.dark {
  --attf-surface:     #0d1f3c;
  --attf-border:      #1e3a6b;
  --attf-text:        #dde8ff;
  --attf-text-sec:    #9ca3af;
  --attf-accent:      #5ba3f5;
  --attf-accent-bg:   rgba(91,163,245,0.15);
  --attf-red:         #ff6b7a;
  --attf-red-bg:      rgba(255,107,122,0.15);
  --attf-gold:        #fcd34d;
  --attf-gold-bg:     rgba(252,211,77,0.15);
  --attf-green:       #36d399;
  --attf-green-bg:    rgba(54,211,153,0.15);
}
</style>
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.attendances.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition hover:bg-[#e8f0fb]"
           style="border-color:var(--attf-border); color:var(--attf-text-sec);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--attf-accent);">
                <i class="fas fa-plus mr-2" style="color:var(--attf-red);"></i> Record Attendance
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--attf-text-sec);">Add a new attendance record</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="rounded-2xl border overflow-hidden dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--attf-surface); border-color:var(--attf-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        <form method="POST" action="{{ route('admin.attendances.store') }}" class="p-8 space-y-6">
            @csrf

            {{-- Enrollment --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--attf-text-sec);">
                    Trainee & Course <span style="color:var(--attf-red);">*</span>
                </label>
                <select name="enrollment_id"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                               dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                        style="border-color:{{ $errors->has('enrollment_id') ? 'var(--attf-red)' : 'var(--attf-border)' }}; color:var(--attf-text);"
                        onfocus="this.style.borderColor='var(--attf-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                        onblur="this.style.borderColor='{{ $errors->has('enrollment_id') ? 'var(--attf-red)' : 'var(--attf-border)' }}'; this.style.boxShadow='none'">
                    <option value="">Select a Trainee & Course</option>
                    @foreach ($enrollments as $enrollment)
                        <option value="{{ $enrollment->id }}" {{ old('enrollment_id') == $enrollment->id ? 'selected' : '' }}>
                            {{ $enrollment->trainee->name }} — {{ $enrollment->course->name }} ({{ $enrollment->course->code }})
                        </option>
                    @endforeach
                </select>
                @error('enrollment_id')
                    <p class="text-xs mt-1" style="color:var(--attf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Date --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--attf-text-sec);">
                    Date <span style="color:var(--attf-red);">*</span>
                </label>
                <input type="date" name="date" value="{{ old('date') }}"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:{{ $errors->has('date') ? 'var(--attf-red)' : 'var(--attf-border)' }}; color:var(--attf-text);"
                       onfocus="this.style.borderColor='var(--attf-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('date') ? 'var(--attf-red)' : 'var(--attf-border)' }}'; this.style.boxShadow='none'">
                @error('date')
                    <p class="text-xs mt-1" style="color:var(--attf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--attf-text-sec);">
                    Attendance Status <span style="color:var(--attf-red);">*</span>
                </label>
                <div class="grid grid-cols-3 gap-3">
                    @foreach (['present' => ['label' => 'Present', 'icon' => 'fa-check-circle', 'color' => 'var(--attf-green)', 'bg' => 'rgba(34,197,94,0.10)'],
                               'absent' => ['label' => 'Absent', 'icon' => 'fa-times-circle', 'color' => 'var(--attf-red)', 'bg' => 'rgba(206,17,38,0.10)'],
                               'late' => ['label' => 'Late', 'icon' => 'fa-hourglass-end', 'color' => 'var(--attf-gold)', 'bg' => 'rgba(245,197,24,0.10)']] as $key => $option)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="status" value="{{ $key }}" 
                                   {{ old('status') === $key ? 'checked' : '' }}
                                   class="sr-only">
                            <div class="p-4 rounded-xl border-2 transition text-center"
                                 style="border-color:{{ old('status') === $key ? $option['color'] : 'var(--attf-border)' }}; background:{{ old('status') === $key ? $option['bg'] : 'var(--attf-surface)' }};">
                                <div class="text-2xl mb-2" style="color:{{ $option['color'] }};">
                                    <i class="fas {{ $option['icon'] }}"></i>
                                </div>
                                <div class="text-xs font-700 uppercase tracking-wide" style="color:{{ $option['color'] }};">
                                    {{ $option['label'] }}
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('status')
                    <p class="text-xs mt-1" style="color:var(--attf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                        style="background:linear-gradient(135deg,var(--attf-accent),#003087); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
                    <i class="fas fa-save"></i> Save Attendance
                </button>
                <a href="{{ route('admin.attendances.index') }}"
                   class="px-5 py-2.5 rounded-xl text-sm font-600 border transition hover:bg-[#e8f0fb]"
                   style="border-color:var(--attf-border); color:var(--attf-text-sec);">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
