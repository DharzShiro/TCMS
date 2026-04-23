@extends('layouts.app')

@section('title', 'Training Schedules')

@section('content')
<style>
:root {
  --sch-surface:      #ffffff;
  --sch-border:       #c5d8f5;
  --sch-text:         #001a4d;
  --sch-text-sec:     #5a7aaa;
  --sch-accent:       #0057B8;
  --sch-accent-bg:    rgba(0,87,184,0.10);
  --sch-red:          #CE1126;
  --sch-red-bg:       rgba(206,17,38,0.15);
  --sch-green:        #16a34a;
  --sch-green-bg:     rgba(22,163,74,0.15);
  --sch-gold:         #F5C518;
  --sch-gold-bg:      rgba(245,197,24,0.15);
}
.dark {
  --sch-surface:      #0d1f3c;
  --sch-border:       #1e3a6b;
  --sch-text:         #dde8ff;
  --sch-text-sec:     #9ca3af;
  --sch-accent:       #5ba3f5;
  --sch-accent-bg:    rgba(91,163,245,0.15);
  --sch-red:          #ff6b7a;
  --sch-red-bg:       rgba(255,107,122,0.15);
  --sch-green:        #36d399;
  --sch-green-bg:     rgba(54,211,153,0.15);
  --sch-gold:         #fcd34d;
  --sch-gold-bg:      rgba(252,211,77,0.15);
}
</style>

<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--sch-accent);">
                <i class="fas fa-calendar-alt mr-2" style="color:var(--sch-red);"></i>
                Training Schedules
            </h1>
            <p class="text-sm mt-1" style="color:var(--sch-text-sec);">
                View training schedules for your enrolled courses.
            </p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
             style="background:var(--sch-green-bg); border:1px solid rgba(22,163,74,0.30); color:var(--sch-green);">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="rounded-2xl border p-5 dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--sch-surface); border-color:var(--sch-border);">
        <form method="GET" action="{{ route('trainee.schedules.index') }}"
              class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:var(--sch-text-sec);"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by course name, code, or location..."
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white dark:placeholder-[#3a5a8a]"
                       style="border-color:var(--sch-border); color:var(--sch-text);"
                       onfocus="this.style.borderColor='var(--sch-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='var(--sch-border)'; this.style.boxShadow='none'">
            </div>
            <select name="status"
                    class="px-4 py-2.5 rounded-xl border text-sm outline-none transition
                           dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                    style="border-color:var(--sch-border); color:var(--sch-text);"
                    onfocus="this.style.borderColor='var(--sch-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                    onblur="this.style.borderColor='var(--sch-border)'; this.style.boxShadow='none'">
                <option value="">All Status</option>
                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
            <button type="submit" class="px-5 py-2.5 rounded-xl font-600 text-sm transition text-white hover:opacity-90"
                    style="background:var(--sch-accent);">
                <i class="fas fa-filter mr-1.5"></i> Filter
            </button>
        </form>
    </div>

    {{-- Schedules Table --}}
    @if ($schedules->count())
        <div class="rounded-2xl border overflow-hidden dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
             style="background:var(--sch-surface); border-color:var(--sch-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead style="background:linear-gradient(135deg,rgba(0,87,184,0.08),rgba(206,17,38,0.08)); border-bottom:1px solid var(--sch-border);">
                        <tr>
                            <th class="px-6 py-4 text-left font-700" style="color:var(--sch-text);">Course</th>
                            <th class="px-6 py-4 text-left font-700" style="color:var(--sch-text);">Trainer</th>
                            <th class="px-6 py-4 text-left font-700" style="color:var(--sch-text);">Location</th>
                            <th class="px-6 py-4 text-left font-700" style="color:var(--sch-text);">Start Date</th>
                            <th class="px-6 py-4 text-left font-700" style="color:var(--sch-text);">Status</th>
                            <th class="px-6 py-4 text-center font-700" style="color:var(--sch-text);">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color:var(--sch-border);">
                        @forelse ($schedules as $schedule)
                            <tr class="hover:bg-opacity-50 transition" style="background:rgba(0,87,184,0.02);">
                                <td class="px-6 py-4">
                                    <div class="font-600" style="color:var(--sch-text);">{{ $schedule->course->name }}</div>
                                    <div class="text-xs mt-0.5" style="color:var(--sch-text-sec);">{{ $schedule->course->code }}</div>
                                </td>
                                <td class="px-6 py-4" style="color:var(--sch-text-sec);">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-700" style="background:var(--sch-accent-bg); color:var(--sch-accent);">
                                            {{ strtoupper(substr($schedule->trainer->name, 0, 1)) }}
                                        </div>
                                        {{ $schedule->trainer->name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4" style="color:var(--sch-text-sec);">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-map-marker-alt text-xs" style="color:var(--sch-red);"></i>
                                        {{ $schedule->location }}
                                    </div>
                                </td>
                                <td class="px-6 py-4" style="color:var(--sch-text);">
                                    {{ \Carbon\Carbon::parse($schedule->start_date)->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusConfig = [
                                            'scheduled' => ['bg' => 'var(--sch-gold-bg)', 'color' => 'var(--sch-gold)', 'icon' => 'fa-calendar'],
                                            'ongoing' => ['bg' => 'var(--sch-accent-bg)', 'color' => 'var(--sch-accent)', 'icon' => 'fa-play-circle'],
                                            'completed' => ['bg' => 'var(--sch-green-bg)', 'color' => 'var(--sch-green)', 'icon' => 'fa-check-circle'],
                                        ];
                                        $config = $statusConfig[$schedule->status] ?? $statusConfig['scheduled'];
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-700"
                                          style="background:{{ $config['bg'] }}; color:{{ $config['color'] }};">
                                        <i class="fas {{ $config['icon'] }}"></i>
                                        {{ ucfirst($schedule->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('trainee.schedules.show', $schedule) }}"
                                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg font-600 text-xs transition text-white hover:opacity-90"
                                       style="background:var(--sch-accent);">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <i class="fas fa-calendar text-3xl" style="color:var(--sch-text-sec); opacity:0.5;"></i>
                                        <div class="font-600" style="color:var(--sch-text);">No schedules found</div>
                                        <p class="text-sm" style="color:var(--sch-text-sec);">Try adjusting your search or enroll in courses to see their schedules.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="flex justify-center">
            {{ $schedules->links() }}
        </div>
    @else
        <div class="rounded-2xl border p-12 text-center dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
             style="background:var(--sch-surface); border-color:var(--sch-border);">
            <i class="fas fa-calendar text-5xl mb-4" style="color:var(--sch-text-sec); opacity:0.5;"></i>
            <h3 class="text-lg font-700 mb-2" style="color:var(--sch-text);">No schedules available</h3>
            <p class="text-sm mb-6" style="color:var(--sch-text-sec);">You are not enrolled in any courses yet. Browse available courses to get started.</p>
            <a href="{{ route('trainee.courses.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-600 text-sm transition text-white hover:opacity-90"
               style="background:var(--sch-accent);">
                <i class="fas fa-book"></i> Browse Courses
            </a>
        </div>
    @endif

</div>
@endsection
