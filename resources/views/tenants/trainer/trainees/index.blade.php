@extends('layouts.app')

@section('title', 'My Trainees')

@section('content')
<style>
:root {
  --tn-surface:      #ffffff;
  --tn-border:       #c5d8f5;
  --tn-text:         #001a4d;
  --tn-text-sec:     #5a7aaa;
  --tn-accent:       #0057B8;
  --tn-accent-bg:    rgba(0,87,184,0.10);
  --tn-red:          #CE1126;
  --tn-red-bg:       rgba(206,17,38,0.15);
  --tn-green:        #16a34a;
  --tn-green-bg:     rgba(22,163,74,0.15);
  --tn-gold:         #F5C518;
  --tn-gold-bg:      rgba(245,197,24,0.15);
}
.dark {
  --tn-surface:      #0d1f3c;
  --tn-border:       #1e3a6b;
  --tn-text:         #dde8ff;
  --tn-text-sec:     #9ca3af;
  --tn-accent:       #5ba3f5;
  --tn-accent-bg:    rgba(91,163,245,0.15);
  --tn-red:          #ff6b7a;
  --tn-red-bg:       rgba(255,107,122,0.15);
  --tn-green:        #36d399;
  --tn-green-bg:     rgba(54,211,153,0.15);
  --tn-gold:         #fcd34d;
  --tn-gold-bg:      rgba(252,211,77,0.15);
}
</style>
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--tn-accent);">
                <i class="fas fa-user-graduate mr-2" style="color:var(--tn-red);"></i>
                My Trainees
            </h1>
            <p class="text-sm mt-1" style="color:var(--tn-text-sec);">
                View all trainees enrolled in your courses
            </p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
             style="background:var(--tn-green-bg); border:1px solid rgba(22,163,74,0.30); color:var(--tn-green);">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
             style="background:var(--tn-red-bg); border:1px solid rgba(206,17,38,0.30); color:var(--tn-red);">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="rounded-2xl border p-5 dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--tn-surface); border-color:var(--tn-border);">
        <form method="GET" action="{{ route('trainer.trainees.index') }}"
              class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:var(--tn-text-sec);"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by trainee name or email..."
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white dark:placeholder-[#3a5a8a]"
                       style="border-color:var(--tn-border); color:var(--tn-text);"
                       onfocus="this.style.borderColor='var(--tn-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='var(--tn-border)'; this.style.boxShadow='none'">
            </div>
            <select name="course_id"
                    class="px-4 py-2.5 rounded-xl border text-sm outline-none transition
                           dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                    style="border-color:var(--tn-border); color:var(--tn-text);"
                    onfocus="this.style.borderColor='var(--tn-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                    onblur="this.style.borderColor='var(--tn-border)'; this.style.boxShadow='none'">
                <option value="">All Courses</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                        {{ $course->name }}
                    </option>
                @endforeach
            </select>
            <select name="status"
                    class="px-4 py-2.5 rounded-xl border text-sm outline-none transition
                           dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                    style="border-color:var(--tn-border); color:var(--tn-text);"
                    onfocus="this.style.borderColor='var(--tn-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                    onblur="this.style.borderColor='var(--tn-border)'; this.style.boxShadow='none'">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="dropped" {{ request('status') === 'dropped' ? 'selected' : '' }}>Dropped</option>
            </select>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl border text-sm font-600 transition"
                    style="border-color:var(--tn-border); color:var(--tn-accent); background:var(--tn-accent-bg);">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'course_id', 'status']))
                <a href="{{ route('trainer.trainees.index') }}"
                   class="px-5 py-2.5 rounded-xl border text-sm font-600 transition text-center"
                   style="border-color:var(--tn-border); color:var(--tn-text-sec);">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Trainees Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse ($enrollments as $enrollment)
            <div class="rounded-2xl border overflow-hidden transition hover:shadow-lg"
                 style="background:var(--tn-surface); border-color:var(--tn-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
                
                <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

                <div class="p-6 space-y-4">
                    {{-- Header --}}
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-700 text-lg" style="color:var(--tn-text);">
                                {{ $enrollment->trainee->name }}
                            </h3>
                            <p class="text-xs mt-1" style="color:var(--tn-text-sec);">
                                {{ $enrollment->trainee->email }}
                            </p>
                        </div>
                        @php
                            $statusStyles = [
                                'pending' => ['bg' => 'var(--tn-gold-bg)',   'color' => 'var(--tn-gold)',    'label' => 'Pending'],
                                'approved' => ['bg' => 'var(--tn-green-bg)',  'color' => 'var(--tn-green)',   'label' => 'Approved'],
                                'completed' => ['bg' => 'var(--tn-accent-bg)', 'color' => 'var(--tn-accent)',  'label' => 'Completed'],
                                'dropped' => ['bg' => 'var(--tn-red-bg)',    'color' => 'var(--tn-red)',     'label' => 'Dropped'],
                            ];
                            $style = $statusStyles[$enrollment->status] ?? ['bg' => '#f0f5ff', 'color' => '#5a7aaa'];
                        @endphp
                        <span class="px-3 py-1 rounded-lg text-xs font-700"
                              style="background:{{ $style['bg'] }}; color:{{ $style['color'] }};">
                            {{ $style['label'] }}
                        </span>
                    </div>

                    {{-- Divider --}}
                    <div style="height:1px; background:var(--tn-border);"></div>

                    {{-- Details --}}
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-book" style="color:var(--tn-accent); width:20px; text-align:center;"></i>
                            <div class="text-sm" style="color:var(--tn-text-sec);">
                                <div class="font-600" style="color:var(--tn-text);">{{ $enrollment->course->name }}</div>
                                <div class="text-xs">{{ $enrollment->course->code ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <i class="fas fa-calendar-alt" style="color:var(--tn-gold); width:20px; text-align:center;"></i>
                            <div class="text-sm" style="color:var(--tn-text-sec);">
                                <div class="font-600" style="color:var(--tn-text);">{{ $enrollment->created_at?->format('M d, Y') }}</div>
                                <div class="text-xs">Enrollment Date</div>
                            </div>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div style="height:1px; background:var(--tn-border);"></div>

                    {{-- Course Info --}}
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div>
                            <p class="text-xs font-600" style="color:var(--tn-text-sec);">NC Level</p>
                            <p class="font-700 mt-1" style="color:var(--tn-text);">{{ $enrollment->course->level ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-600" style="color:var(--tn-text-sec);">Duration</p>
                            <p class="font-700 mt-1" style="color:var(--tn-text);">{{ $enrollment->course->duration_hours ?? 'N/A' }}h</p>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4">
                        <a href="{{ route('trainer.trainees.show', $enrollment->trainee) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-600 transition w-full justify-center hover:-translate-y-0.5"
                           style="background:linear-gradient(135deg,var(--tn-accent),#003087); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
                            <i class="fas fa-eye"></i> View Profile
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border p-12 text-center"
                 style="background:var(--tn-surface); border-color:var(--tn-border);">
                <i class="fas fa-inbox text-4xl opacity-50 mb-4 block"></i>
                <p class="text-sm" style="color:var(--tn-text-sec);">No trainees found.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($enrollments->hasPages())
        <div class="flex justify-center">
            {{ $enrollments->links() }}
        </div>
    @endif

</div>
@endsection
