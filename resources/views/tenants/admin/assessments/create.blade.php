@extends('layouts.app')

@section('title', 'Add Assessment')

@section('content')
<style>
:root {
  --asf-surface:     #ffffff;
  --asf-border:      #c5d8f5;
  --asf-text:        #001a4d;
  --asf-text-sec:    #5a7aaa;
  --asf-accent:      #0057B8;
  --asf-accent-bg:   rgba(0,87,184,0.10);
  --asf-red:         #CE1126;
  --asf-red-bg:      rgba(206,17,38,0.15);
}
.dark {
  --asf-surface:     #0d1f3c;
  --asf-border:      #1e3a6b;
  --asf-text:        #dde8ff;
  --asf-text-sec:    #9ca3af;
  --asf-accent:      #5ba3f5;
  --asf-accent-bg:   rgba(91,163,245,0.15);
  --asf-red:         #ff6b7a;
  --asf-red-bg:      rgba(255,107,122,0.15);
}
</style>

<div class="max-w-2xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.assessments.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition hover:bg-[#e8f0fb]"
           style="border-color:var(--asf-border); color:var(--asf-text-sec);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--asf-accent);">
                <i class="fas fa-plus mr-2" style="color:var(--asf-red);"></i> Add Assessment
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--asf-text-sec);">Record a new trainee assessment</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="rounded-2xl border overflow-hidden dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--asf-surface); border-color:var(--asf-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        <form method="POST" action="{{ route('admin.assessments.store') }}" class="p-8 space-y-6">
            @csrf

            {{-- Enrollment --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--asf-text-sec);">
                    Trainee / Enrollment <span style="color:var(--asf-red);">*</span>
                </label>
                <select name="enrollment_id"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                               dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                        style="border-color:{{ $errors->has('enrollment_id') ? 'var(--asf-red)' : 'var(--asf-border)' }}; color:var(--asf-text);"
                        onfocus="this.style.borderColor='var(--asf-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                        onblur="this.style.borderColor='{{ $errors->has('enrollment_id') ? 'var(--asf-red)' : 'var(--asf-border)' }}'; this.style.boxShadow='none'">
                    <option value="">Select a Trainee / Enrollment</option>
                    @foreach($enrollments as $enrollment)
                        <option value="{{ $enrollment->id }}" {{ old('enrollment_id') == $enrollment->id ? 'selected' : '' }}>
                            {{ $enrollment->trainee->name }} — {{ $enrollment->course->name }}
                        </option>
                    @endforeach
                </select>
                @error('enrollment_id')
                    <p class="text-xs mt-1" style="color:var(--asf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Trainer --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--asf-text-sec);">
                    Trainer / Assessor <span style="color:var(--asf-red);">*</span>
                </label>
                <select name="trainer_id"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                               dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                        style="border-color:{{ $errors->has('trainer_id') ? 'var(--asf-red)' : 'var(--asf-border)' }}; color:var(--asf-text);"
                        onfocus="this.style.borderColor='var(--asf-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                        onblur="this.style.borderColor='{{ $errors->has('trainer_id') ? 'var(--asf-red)' : 'var(--asf-border)' }}'; this.style.boxShadow='none'">
                    <option value="">Select a Trainer</option>
                    @foreach($trainers as $trainer)
                        <option value="{{ $trainer->id }}" {{ old('trainer_id') == $trainer->id ? 'selected' : '' }}>
                            {{ $trainer->name }}
                        </option>
                    @endforeach
                </select>
                @error('trainer_id')
                    <p class="text-xs mt-1" style="color:var(--asf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Score --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--asf-text-sec);">
                    Score (%) <span class="normal-case font-400" style="color:var(--asf-text-sec);">(Optional)</span>
                </label>
                <input type="number" name="score" value="{{ old('score') }}"
                       placeholder="e.g. 85"
                       min="0" max="100" step="0.5"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:{{ $errors->has('score') ? 'var(--asf-red)' : 'var(--asf-border)' }}; color:var(--asf-text);"
                       onfocus="this.style.borderColor='var(--asf-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('score') ? 'var(--asf-red)' : 'var(--asf-border)' }}'; this.style.boxShadow='none'">
                @error('score')
                    <p class="text-xs mt-1" style="color:var(--asf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Result --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--asf-text-sec);">
                    Result <span style="color:var(--asf-red);">*</span>
                </label>
                <select name="result"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                               dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                        style="border-color:{{ $errors->has('result') ? 'var(--asf-red)' : 'var(--asf-border)' }}; color:var(--asf-text);"
                        onfocus="this.style.borderColor='var(--asf-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                        onblur="this.style.borderColor='{{ $errors->has('result') ? 'var(--asf-red)' : 'var(--asf-border)' }}'; this.style.boxShadow='none'">
                    <option value="">Select Result</option>
                    <option value="competent" {{ old('result') === 'competent' ? 'selected' : '' }}>Competent</option>
                    <option value="not_yet_competent" {{ old('result') === 'not_yet_competent' ? 'selected' : '' }}>Not Yet Competent</option>
                </select>
                @error('result')
                    <p class="text-xs mt-1" style="color:var(--asf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Assessed Date --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--asf-text-sec);">
                    Assessed Date <span style="color:var(--asf-red);">*</span>
                </label>
                <input type="date" name="assessed_at" value="{{ old('assessed_at') }}"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:{{ $errors->has('assessed_at') ? 'var(--asf-red)' : 'var(--asf-border)' }}; color:var(--asf-text);"
                       onfocus="this.style.borderColor='var(--asf-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('assessed_at') ? 'var(--asf-red)' : 'var(--asf-border)' }}'; this.style.boxShadow='none'">
                @error('assessed_at')
                    <p class="text-xs mt-1" style="color:var(--asf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remarks --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--asf-text-sec);">
                    Remarks <span class="normal-case font-400" style="color:var(--asf-text-sec);">(Optional)</span>
                </label>
                <textarea name="remarks" rows="4"
                          placeholder="Enter assessment remarks or observations..."
                          class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                                 dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                          style="border-color:{{ $errors->has('remarks') ? 'var(--asf-red)' : 'var(--asf-border)' }}; color:var(--asf-text);"
                          onfocus="this.style.borderColor='var(--asf-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                          onblur="this.style.borderColor='{{ $errors->has('remarks') ? 'var(--asf-red)' : 'var(--asf-border)' }}'; this.style.boxShadow='none'">{{ old('remarks') }}</textarea>
                @error('remarks')
                    <p class="text-xs mt-1" style="color:var(--asf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                        style="background:linear-gradient(135deg,var(--asf-accent),#003087); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
                    <i class="fas fa-save"></i> Save Assessment
                </button>
                <a href="{{ route('admin.assessments.index') }}"
                   class="px-5 py-2.5 rounded-xl text-sm font-600 border transition hover:bg-[#e8f0fb]"
                   style="border-color:var(--asf-border); color:var(--asf-text-sec);">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>
@endsection