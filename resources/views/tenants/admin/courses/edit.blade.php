@extends('layouts.app')

@section('title', 'Edit Course')

@section('content')
<style>
:root {
  --crsf-surface:     #ffffff;
  --crsf-border:      #c5d8f5;
  --crsf-text:        #001a4d;
  --crsf-text-sec:    #5a7aaa;
  --crsf-accent:      #0057B8;
  --crsf-accent-bg:   rgba(0,87,184,0.10);
  --crsf-red:         #CE1126;
  --crsf-red-bg:      rgba(206,17,38,0.15);
}
.dark {
  --crsf-surface:     #0d1f3c;
  --crsf-border:      #1e3a6b;
  --crsf-text:        #dde8ff;
  --crsf-text-sec:    #9ca3af;
  --crsf-accent:      #5ba3f5;
  --crsf-accent-bg:   rgba(91,163,245,0.15);
  --crsf-red:         #ff6b7a;
  --crsf-red-bg:      rgba(255,107,122,0.15);
}
</style>
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.courses.show', $course) }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition hover:bg-[#e8f0fb]"
           style="border-color:var(--crsf-border); color:var(--crsf-text-sec);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--crsf-accent);">
                <i class="fas fa-pen mr-2" style="color:var(--crsf-red);"></i> Edit Course
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--crsf-text-sec);">Updating details for {{ $course->name }}</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="rounded-2xl border overflow-hidden dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--crsf-surface); border-color:var(--crsf-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        <form method="POST" action="{{ route('admin.courses.update', $course) }}" class="p-8 space-y-6">
            @csrf
            @method('PUT')

            {{-- Course Code --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--crsf-text-sec);">
                    Course Code <span style="color:var(--crsf-red);">*</span>
                </label>
                <input type="text" name="code" value="{{ old('code', $course->code) }}"
                       placeholder="e.g. NC-I-ITE-001"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:{{ $errors->has('code') ? 'var(--crsf-red)' : 'var(--crsf-border)' }}; color:var(--crsf-text);"
                       onfocus="this.style.borderColor='var(--crsf-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('code') ? 'var(--crsf-red)' : 'var(--crsf-border)' }}'; this.style.boxShadow='none'">
                @error('code')
                    <p class="text-xs mt-1" style="color:var(--crsf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Course Name --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--crsf-text-sec);">
                    Course Name <span style="color:var(--crsf-red);">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $course->name) }}"
                       placeholder="e.g. Information Technology Fundamentals"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:{{ $errors->has('name') ? 'var(--crsf-red)' : 'var(--crsf-border)' }}; color:var(--crsf-text);"
                       onfocus="this.style.borderColor='var(--crsf-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('name') ? 'var(--crsf-red)' : 'var(--crsf-border)' }}'; this.style.boxShadow='none'">
                @error('name')
                    <p class="text-xs mt-1" style="color:var(--crsf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--crsf-text-sec);">
                    Description
                </label>
                <textarea name="description" rows="4"
                          placeholder="Enter course description..."
                          class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                                 dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                          style="border-color:{{ $errors->has('description') ? 'var(--crsf-red)' : 'var(--crsf-border)' }}; color:var(--crsf-text);"
                          onfocus="this.style.borderColor='var(--crsf-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                          onblur="this.style.borderColor='{{ $errors->has('description') ? 'var(--crsf-red)' : 'var(--crsf-border)' }}'; this.style.boxShadow='none'">{{ old('description', $course->description) }}</textarea>
                @error('description')
                    <p class="text-xs mt-1" style="color:var(--crsf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Duration Hours --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--crsf-text-sec);">
                    Duration (Hours) <span style="color:var(--crsf-red);">*</span>
                </label>
                <input type="number" name="duration_hours" value="{{ old('duration_hours', $course->duration_hours) }}"
                       placeholder="e.g. 40"
                       min="1"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:{{ $errors->has('duration_hours') ? 'var(--crsf-red)' : 'var(--crsf-border)' }}; color:var(--crsf-text);"
                       onfocus="this.style.borderColor='var(--crsf-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('duration_hours') ? 'var(--crsf-red)' : 'var(--crsf-border)' }}'; this.style.boxShadow='none'">
                @error('duration_hours')
                    <p class="text-xs mt-1" style="color:var(--crsf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Level --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--crsf-text-sec);">
                    NC Level
                </label>
                <select name="level"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                               dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                        style="border-color:{{ $errors->has('level') ? 'var(--crsf-red)' : 'var(--crsf-border)' }}; color:var(--crsf-text);"
                        onfocus="this.style.borderColor='var(--crsf-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                        onblur="this.style.borderColor='{{ $errors->has('level') ? 'var(--crsf-red)' : 'var(--crsf-border)' }}'; this.style.boxShadow='none'">
                    <option value="">Select Level</option>
                    <option value="NC I" {{ old('level', $course->level) === 'NC I' ? 'selected' : '' }}>NC I</option>
                    <option value="NC II" {{ old('level', $course->level) === 'NC II' ? 'selected' : '' }}>NC II</option>
                    <option value="NC III" {{ old('level', $course->level) === 'NC III' ? 'selected' : '' }}>NC III</option>
                    <option value="NC IV" {{ old('level', $course->level) === 'NC IV' ? 'selected' : '' }}>NC IV</option>
                    <option value="COC" {{ old('level', $course->level) === 'COC' ? 'selected' : '' }}>COC</option>
                </select>
                @error('level')
                    <p class="text-xs mt-1" style="color:var(--crsf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--crsf-text-sec);">
                    Status <span style="color:var(--crsf-red);">*</span>
                </label>
                <select name="status"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                               dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                        style="border-color:{{ $errors->has('status') ? 'var(--crsf-red)' : 'var(--crsf-border)' }}; color:var(--crsf-text);"
                        onfocus="this.style.borderColor='var(--crsf-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                        onblur="this.style.borderColor='{{ $errors->has('status') ? 'var(--crsf-red)' : 'var(--crsf-border)' }}'; this.style.boxShadow='none'">
                    <option value="">Select Status</option>
                    <option value="active" {{ old('status', $course->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $course->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                    <p class="text-xs mt-1" style="color:var(--crsf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                        style="background:linear-gradient(135deg,var(--crsf-accent),#003087); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
                    <i class="fas fa-save"></i> Update Course
                </button>
                <a href="{{ route('admin.courses.show', $course) }}"
                   class="px-5 py-2.5 rounded-xl text-sm font-600 border transition hover:bg-[#e8f0fb]"
                   style="border-color:var(--crsf-border); color:var(--crsf-text-sec);">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
