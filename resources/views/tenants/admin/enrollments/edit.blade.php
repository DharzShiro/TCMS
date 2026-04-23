@extends('layouts.app')

@section('title', 'Edit Enrollment')

@section('content')
<style>
  :root {
    --enf-surface: #ffffff;
    --enf-border: #c5d8f5;
    --enf-text: #001a4d;
    --enf-text-sec: #5a7aaa;
    --enf-accent: #0057B8;
    --enf-accent-bg: #e8f0fb;
    --enf-red: #CE1126;
    --enf-red-bg: #fff0f2;
  }
  .dark {
    --enf-surface: #0d1f3c;
    --enf-border: #1e3a6b;
    --enf-text: #dde8ff;
    --enf-text-sec: #3a5a8a;
    --enf-accent: #0057B8;
    --enf-accent-bg: #122550;
    --enf-red: #CE1126;
    --enf-red-bg: #5a0a0a;
  }
</style>
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.enrollments.show', $enrollment) }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition hover:bg-[#e8f0fb]"
           style="border-color:var(--enf-border); color:var(--enf-text-sec);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--enf-accent);">
                <i class="fas fa-pen mr-2" style="color:var(--enf-red);"></i> Edit Enrollment
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--enf-text-sec);">Updating enrollment for {{ $enrollment->trainee->name }}</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="rounded-2xl border overflow-hidden dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--enf-surface); border-color:var(--enf-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        <form method="POST" action="{{ route('admin.enrollments.update', $enrollment) }}" class="p-8 space-y-6">
            @csrf
            @method('PUT')

            {{-- Trainee --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--enf-text-sec);">
                    Trainee <span style="color:var(--enf-red);">*</span>
                </label>
                <select name="trainee_id"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                               dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                        style="border-color:{{ $errors->has('trainee_id') ? 'var(--enf-red)' : 'var(--enf-border)' }}; color:var(--enf-text);"
                        onfocus="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--enf-accent'); this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                        onblur="this.style.borderColor='{{ $errors->has('trainee_id') ? 'var(--enf-red)' : 'var(--enf-border)' }}'; this.style.boxShadow='none'">
                    <option value="">Select a trainee</option>
                    @foreach ($trainees as $trainee)
                        <option value="{{ $trainee->id }}" {{ old('trainee_id', $enrollment->trainee_id) == $trainee->id ? 'selected' : '' }}>
                            {{ $trainee->name }} ({{ $trainee->email }})
                        </option>
                    @endforeach
                </select>
                @error('trainee_id')
                    <p class="text-xs mt-1" style="color:var(--enf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Course --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--enf-text-sec);">
                    Course <span style="color:var(--enf-red);">*</span>
                </label>
                <select name="course_id"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                               dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                        style="border-color:{{ $errors->has('course_id') ? 'var(--enf-red)' : 'var(--enf-border)' }}; color:var(--enf-text);"
                        onfocus="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--enf-accent'); this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                        onblur="this.style.borderColor='{{ $errors->has('course_id') ? 'var(--enf-red)' : 'var(--enf-border)' }}'; this.style.boxShadow='none'">
                    <option value="">Select a course</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" {{ old('course_id', $enrollment->course_id) == $course->id ? 'selected' : '' }}>
                            {{ $course->name }} ({{ $course->code }})
                        </option>
                    @endforeach
                </select>
                @error('course_id')
                    <p class="text-xs mt-1" style="color:var(--enf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Enrollment Date --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--enf-text-sec);">
                    Enrollment Date
                </label>
                <input type="date" name="enrolled_at" value="{{ old('enrolled_at', $enrollment->enrolled_at?->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:{{ $errors->has('enrolled_at') ? 'var(--enf-red)' : 'var(--enf-border)' }}; color:var(--enf-text);"
                       onfocus="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--enf-accent'); this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('enrolled_at') ? 'var(--enf-red)' : 'var(--enf-border)' }}'; this.style.boxShadow='none'">
                @error('enrolled_at')
                    <p class="text-xs mt-1" style="color:var(--enf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--enf-text-sec);">
                    Status <span style="color:var(--enf-red);">*</span>
                </label>
                <select name="status"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                               dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                        style="border-color:{{ $errors->has('status') ? 'var(--enf-red)' : 'var(--enf-border)' }}; color:var(--enf-text);"
                        onfocus="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--enf-accent'); this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                        onblur="this.style.borderColor='{{ $errors->has('status') ? 'var(--enf-red)' : 'var(--enf-border)' }}'; this.style.boxShadow='none'">
                    <option value="">Select Status</option>
                    <option value="pending" {{ old('status', $enrollment->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ old('status', $enrollment->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="completed" {{ old('status', $enrollment->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="dropped" {{ old('status', $enrollment->status) === 'dropped' ? 'selected' : '' }}>Dropped</option>
                </select>
                @error('status')
                    <p class="text-xs mt-1" style="color:var(--enf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                        style="background:linear-gradient(135deg,var(--enf-accent),#003087); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
                    <i class="fas fa-save"></i> Update Enrollment
                </button>
                <a href="{{ route('admin.enrollments.show', $enrollment) }}"
                   class="px-5 py-2.5 rounded-xl text-sm font-600 border transition hover:bg-[#e8f0fb]"
                   style="border-color:var(--enf-border); color:var(--enf-text-sec);">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
