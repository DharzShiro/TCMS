@extends('layouts.app')

@section('title', 'Available Courses')

@section('content')
<style>
:root {
  --crs-surface:      #ffffff;
  --crs-border:       #c5d8f5;
  --crs-text:         #001a4d;
  --crs-text-sec:     #5a7aaa;
  --crs-accent:       #0057B8;
  --crs-accent-bg:    rgba(0,87,184,0.10);
  --crs-red:          #CE1126;
  --crs-red-bg:       rgba(206,17,38,0.15);
  --crs-green:        #16a34a;
  --crs-green-bg:     rgba(22,163,74,0.15);
  --crs-gold:         #F5C518;
  --crs-gold-bg:      rgba(245,197,24,0.15);
}
.dark {
  --crs-surface:      #0d1f3c;
  --crs-border:       #1e3a6b;
  --crs-text:         #dde8ff;
  --crs-text-sec:     #9ca3af;
  --crs-accent:       #5ba3f5;
  --crs-accent-bg:    rgba(91,163,245,0.15);
  --crs-red:          #ff6b7a;
  --crs-red-bg:       rgba(255,107,122,0.15);
  --crs-green:        #36d399;
  --crs-green-bg:     rgba(54,211,153,0.15);
  --crs-gold:         #fcd34d;
  --crs-gold-bg:      rgba(252,211,77,0.15);
}
</style>
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--crs-accent);">
                <i class="fas fa-book mr-2" style="color:var(--crs-red);"></i>
                Available Courses
            </h1>
            <p class="text-sm mt-1" style="color:var(--crs-text-sec);">
                Browse and enroll in available training courses.
            </p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
             style="background:var(--crs-green-bg); border:1px solid rgba(22,163,74,0.30); color:var(--crs-green);">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="rounded-2xl border p-5 dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--crs-surface); border-color:var(--crs-border);">
        <form method="GET" action="{{ route('trainee.courses.index') }}"
              class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:var(--crs-text-sec);"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name or code..."
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white dark:placeholder-[#3a5a8a]"
                       style="border-color:var(--crs-border); color:var(--crs-text);"
                       onfocus="this.style.borderColor='var(--crs-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='var(--crs-border)'; this.style.boxShadow='none'">
            </div>
            <select name="level"
                    class="px-4 py-2.5 rounded-xl border text-sm outline-none transition
                           dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                    style="border-color:var(--crs-border); color:var(--crs-text);"
                    onfocus="this.style.borderColor='var(--crs-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                    onblur="this.style.borderColor='var(--crs-border)'; this.style.boxShadow='none'">
                <option value="">All Levels</option>
                <option value="NC I" {{ request('level') === 'NC I' ? 'selected' : '' }}>NC I</option>
                <option value="NC II" {{ request('level') === 'NC II' ? 'selected' : '' }}>NC II</option>
                <option value="NC III" {{ request('level') === 'NC III' ? 'selected' : '' }}>NC III</option>
                <option value="NC IV" {{ request('level') === 'NC IV' ? 'selected' : '' }}>NC IV</option>
                <option value="COC" {{ request('level') === 'COC' ? 'selected' : '' }}>COC</option>
            </select>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-white text-sm font-bold transition hover:-translate-y-0.5"
                    style="background:linear-gradient(135deg,var(--crs-accent),#003087);">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if (request()->filled('search') || request()->filled('level'))
                <a href="{{ route('trainee.courses.index') }}"
                   class="px-4 py-2.5 rounded-xl text-sm font-semibold border transition hover:bg-[#e8f0fb]"
                   style="border-color:var(--crs-border); color:var(--crs-text-sec);">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Courses Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($courses as $course)
            <div class="rounded-2xl border overflow-hidden transition hover:shadow-md hover:-translate-y-1 dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
                 style="background:var(--crs-surface); border-color:var(--crs-border);">
                
                <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

                <div class="p-5 space-y-4">
                    {{-- Course Header --}}
                    <div>
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-800 text-white flex-shrink-0"
                                 style="background:linear-gradient(135deg,var(--crs-accent),#003087);">
                                <i class="fas fa-book"></i>
                            </div>
                            @php
                                $isEnrolled = in_array($course->id, $enrolledCourseIds);
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-xs font-700 flex-shrink-0"
                                  style="background:{{ $isEnrolled ? 'var(--crs-green-bg)' : 'var(--crs-accent-bg)' }}; color:{{ $isEnrolled ? 'var(--crs-green)' : 'var(--crs-accent)' }};">
                                {{ $isEnrolled ? 'Enrolled' : 'Available' }}
                            </span>
                        </div>
                        <h3 class="font-700 dark:text-white" style="color:var(--crs-text);">{{ $course->name }}</h3>
                        <p class="text-xs mt-1" style="color:var(--crs-text-sec);">{{ $course->code }}</p>
                    </div>

                    {{-- Course Details --}}
                    <div class="space-y-2 text-xs" style="color:var(--crs-text-sec);">
                        @if($course->level)
                        <div class="flex items-center gap-2">
                            <i class="fas fa-layer-group" style="color:var(--crs-gold); width:16px;"></i>
                            <span>{{ $course->level }}</span>
                        </div>
                        @endif
                        <div class="flex items-center gap-2">
                            <i class="fas fa-clock" style="color:var(--crs-accent); width:16px;"></i>
                            <span>{{ $course->duration_hours }} hours</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-users" style="color:var(--crs-red); width:16px;"></i>
                            <span>{{ $course->enrollments_count }} {{ Str::plural('trainee', $course->enrollments_count) }}</span>
                        </div>
                    </div>

                    {{-- Description --}}
                    @if($course->description)
                    <div class="p-3 rounded-lg text-xs" style="background:var(--crs-accent-bg); color:var(--crs-text-sec);">
                        {{ Str::limit($course->description, 100) }}
                    </div>
                    @endif

                    {{-- Action --}}
                    <a href="{{ route('trainee.courses.show', $course) }}"
                       class="block w-full py-2.5 rounded-xl text-center text-sm font-700 transition hover:-translate-y-0.5 text-white"
                       style="background:linear-gradient(135deg,var(--crs-accent),#003087); box-shadow:0 3px 12px rgba(0,87,184,0.15);">
                        View Details
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center">
                <div class="flex flex-col items-center gap-3" style="color:var(--crs-text-sec);">
                    <i class="fas fa-book text-4xl opacity-25"></i>
                    <p class="font-600">No courses found</p>
                    <p class="text-xs">Try adjusting your search or filters.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($courses->hasPages())
        <div class="flex justify-center">
            {{ $courses->links() }}
        </div>
    @endif

</div>
@endsection
