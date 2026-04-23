@extends('layouts.app')

@section('title', 'Edit Training Schedule')

@section('content')
<style>
:root {
  --sch-surface:     #ffffff;
  --sch-border:      #c5d8f5;
  --sch-text:        #001a4d;
  --sch-text-sec:    #5a7aaa;
  --sch-accent:      #0057B8;
  --sch-accent-bg:   rgba(0,87,184,0.10);
  --sch-red:         #CE1126;
  --sch-red-bg:      rgba(206,17,38,0.15);
}
.dark {
  --sch-surface:     #0d1f3c;
  --sch-border:      #1e3a6b;
  --sch-text:        #dde8ff;
  --sch-text-sec:    #9ca3af;
  --sch-accent:      #5ba3f5;
  --sch-accent-bg:   rgba(91,163,245,0.15);
  --sch-red:         #ff6b7a;
  --sch-red-bg:      rgba(255,107,122,0.15);
}
</style>
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('trainer.schedules.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition hover:bg-[#e8f0fb]"
           style="border-color:var(--sch-border); color:var(--sch-text-sec);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--sch-accent);">
                <i class="fas fa-edit mr-2" style="color:var(--sch-red);"></i> Edit Training Schedule
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--sch-text-sec);">Update schedule details</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="rounded-2xl border overflow-hidden dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--sch-surface); border-color:var(--sch-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        <form method="POST" action="{{ route('trainer.schedules.update', $schedule) }}" class="p-8 space-y-6">
            @csrf
            @method('PUT')

            {{-- Course Selection --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--sch-text-sec);">
                    Course <span style="color:var(--sch-red);">*</span>
                </label>
                <select name="course_id"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                               dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                        style="border-color:{{ $errors->has('course_id') ? 'var(--sch-red)' : 'var(--sch-border)' }}; color:var(--sch-text);"
                        onfocus="this.style.borderColor='var(--sch-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                        onblur="this.style.borderColor='{{ $errors->has('course_id') ? 'var(--sch-red)' : 'var(--sch-border)' }}'; this.style.boxShadow='none'">
                    <option value="">Select a Course</option>
                </select>
                @error('course_id')
                    <p class="text-xs mt-1" style="color:var(--sch-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Start Date --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--sch-text-sec);">
                    Start Date <span style="color:var(--sch-red);">*</span>
                </label>
                <input type="date" name="start_date" value="{{ old('start_date', $schedule->start_date?->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:{{ $errors->has('start_date') ? 'var(--sch-red)' : 'var(--sch-border)' }}; color:var(--sch-text);"
                       onfocus="this.style.borderColor='var(--sch-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('start_date') ? 'var(--sch-red)' : 'var(--sch-border)' }}'; this.style.boxShadow='none'">
                @error('start_date')
                    <p class="text-xs mt-1" style="color:var(--sch-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- End Date --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--sch-text-sec);">
                    End Date <span style="color:var(--sch-red);">*</span>
                </label>
                <input type="date" name="end_date" value="{{ old('end_date', $schedule->end_date?->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:{{ $errors->has('end_date') ? 'var(--sch-red)' : 'var(--sch-border)' }}; color:var(--sch-text);"
                       onfocus="this.style.borderColor='var(--sch-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('end_date') ? 'var(--sch-red)' : 'var(--sch-border)' }}'; this.style.boxShadow='none'">
                @error('end_date')
                    <p class="text-xs mt-1" style="color:var(--sch-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Start Time --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--sch-text-sec);">
                    Start Time <span class="text-xs" style="color:var(--sch-text-sec);">(Optional)</span>
                </label>
                <input type="time" name="time_start" value="{{ old('time_start', $schedule->time_start) }}"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:{{ $errors->has('time_start') ? 'var(--sch-red)' : 'var(--sch-border)' }}; color:var(--sch-text);"
                       onfocus="this.style.borderColor='var(--sch-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('time_start') ? 'var(--sch-red)' : 'var(--sch-border)' }}'; this.style.boxShadow='none'">
                @error('time_start')
                    <p class="text-xs mt-1" style="color:var(--sch-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- End Time --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--sch-text-sec);">
                    End Time <span class="text-xs" style="color:var(--sch-text-sec);">(Optional)</span>
                </label>
                <input type="time" name="time_end" value="{{ old('time_end', $schedule->time_end) }}"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:{{ $errors->has('time_end') ? 'var(--sch-red)' : 'var(--sch-border)' }}; color:var(--sch-text);"
                       onfocus="this.style.borderColor='var(--sch-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('time_end') ? 'var(--sch-red)' : 'var(--sch-border)' }}'; this.style.boxShadow='none'">
                @error('time_end')
                    <p class="text-xs mt-1" style="color:var(--sch-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Location --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--sch-text-sec);">
                    Location <span class="text-xs" style="color:var(--sch-text-sec);">(Optional)</span>
                </label>
                <input type="text" name="location" value="{{ old('location', $schedule->location) }}"
                       placeholder="e.g. Training Room A, Building 1"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:{{ $errors->has('location') ? 'var(--sch-red)' : 'var(--sch-border)' }}; color:var(--sch-text);"
                       onfocus="this.style.borderColor='var(--sch-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('location') ? 'var(--sch-red)' : 'var(--sch-border)' }}'; this.style.boxShadow='none'">
                @error('location')
                    <p class="text-xs mt-1" style="color:var(--sch-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--sch-text-sec);">
                    Status <span style="color:var(--sch-red);">*</span>
                </label>
                <select name="status"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                               dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                        style="border-color:{{ $errors->has('status') ? 'var(--sch-red)' : 'var(--sch-border)' }}; color:var(--sch-text);"
                        onfocus="this.style.borderColor='var(--sch-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                        onblur="this.style.borderColor='{{ $errors->has('status') ? 'var(--sch-red)' : 'var(--sch-border)' }}'; this.style.boxShadow='none'">
                    <option value="">Select Status</option>
                    <option value="upcoming" {{ old('status', $schedule->status) === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="ongoing" {{ old('status', $schedule->status) === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="completed" {{ old('status', $schedule->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ old('status', $schedule->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                @error('status')
                    <p class="text-xs mt-1" style="color:var(--sch-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                        style="background:linear-gradient(135deg,var(--sch-accent),#003087); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
                    <i class="fas fa-save"></i> Update Schedule
                </button>
                <a href="{{ route('trainer.schedules.index') }}"
                   class="px-5 py-2.5 rounded-xl text-sm font-600 border transition hover:bg-[#e8f0fb]"
                   style="border-color:var(--sch-border); color:var(--sch-text-sec);">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
