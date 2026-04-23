@extends('layouts.app')

@section('title', 'My Training Schedules')

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
                My Training Schedules
            </h1>
            <p class="text-sm mt-1" style="color:var(--sch-text-sec);">
                View all assigned training schedules
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
    @if (session('error'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
             style="background:var(--sch-red-bg); border:1px solid rgba(206,17,38,0.30); color:var(--sch-red);">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="rounded-2xl border p-5 dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--sch-surface); border-color:var(--sch-border);">
        <form method="GET" action="{{ route('trainer.schedules.index') }}"
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
                <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl border text-sm font-600 transition"
                    style="border-color:var(--sch-border); color:var(--sch-accent); background:var(--sch-accent-bg);">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('trainer.schedules.index') }}"
                   class="px-5 py-2.5 rounded-xl border text-sm font-600 transition text-center"
                   style="border-color:var(--sch-border); color:var(--sch-text-sec);">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Schedules Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse ($schedules as $schedule)
            <div class="rounded-2xl border overflow-hidden transition hover:shadow-lg"
                 style="background:var(--sch-surface); border-color:var(--sch-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
                
                <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

                <div class="p-6 space-y-4">
                    {{-- Header --}}
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-700 text-lg" style="color:var(--sch-text);">
                                {{ $schedule->course->name }}
                            </h3>
                            <p class="text-xs mt-1" style="color:var(--sch-text-sec);">
                                {{ $schedule->course->code ?? 'N/A' }}
                            </p>
                        </div>
                        @php
                            $statusStyles = [
                                'upcoming' => ['bg' => 'var(--sch-accent-bg)',  'color' => 'var(--sch-accent)',  'label' => 'Upcoming'],
                                'ongoing'  => ['bg' => 'var(--sch-green-bg)',   'color' => 'var(--sch-green)',   'label' => 'Ongoing'],
                                'completed' => ['bg' => 'var(--sch-gold-bg)',   'color' => 'var(--sch-gold)',    'label' => 'Completed'],
                                'cancelled' => ['bg' => 'var(--sch-red-bg)',    'color' => 'var(--sch-red)',     'label' => 'Cancelled'],
                            ];
                            $style = $statusStyles[$schedule->status] ?? ['bg' => '#f0f5ff', 'color' => '#5a7aaa'];
                        @endphp
                        <span class="px-3 py-1 rounded-lg text-xs font-700"
                              style="background:{{ $style['bg'] }}; color:{{ $style['color'] }};">
                            {{ $style['label'] }}
                        </span>
                    </div>

                    {{-- Divider --}}
                    <div style="height:1px; background:var(--sch-border);"></div>

                    {{-- Details --}}
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-calendar-days" style="color:var(--sch-gold); width:20px; text-align:center;"></i>
                            <div class="text-sm" style="color:var(--sch-text-sec);">
                                <div class="font-600" style="color:var(--sch-text);">{{ $schedule->start_date?->format('M d, Y') }} - {{ $schedule->end_date?->format('M d, Y') }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <i class="fas fa-clock" style="color:var(--sch-accent); width:20px; text-align:center;"></i>
                            <div class="text-sm" style="color:var(--sch-text-sec);">
                                <div class="font-600" style="color:var(--sch-text);">{{ $schedule->time_start ?? 'N/A' }} - {{ $schedule->time_end ?? 'N/A' }}</div>
                            </div>
                        </div>

                        @if($schedule->location)
                        <div class="flex items-center gap-3">
                            <i class="fas fa-map-marker-alt" style="color:var(--sch-red); width:20px; text-align:center;"></i>
                            <div class="text-sm" style="color:var(--sch-text-sec);">
                                <div class="font-600" style="color:var(--sch-text);">{{ $schedule->location }}</div>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Divider --}}
                    <div style="height:1px; background:var(--sch-border);"></div>

                    {{-- Course Info --}}
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div>
                            <p class="text-xs font-600" style="color:var(--sch-text-sec);">NC Level</p>
                            <p class="font-700 mt-1" style="color:var(--sch-text);">{{ $schedule->course->level ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-600" style="color:var(--sch-text-sec);">Duration</p>
                            <p class="font-700 mt-1" style="color:var(--sch-text);">{{ $schedule->course->duration_hours ?? 'N/A' }} hours</p>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4">
                        <a href="{{ route('trainer.schedules.show', $schedule) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-600 transition w-full justify-center hover:-translate-y-0.5"
                           style="background:linear-gradient(135deg,var(--sch-accent),#003087); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border p-12 text-center"
                 style="background:var(--sch-surface); border-color:var(--sch-border);">
                <i class="fas fa-inbox text-4xl opacity-50 mb-4 block"></i>
                <p class="text-sm" style="color:var(--sch-text-sec);">No training schedules found.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($schedules->hasPages())
        <div class="flex justify-center">
            {{ $schedules->links() }}
        </div>
    @endif

</div>
@endsection
