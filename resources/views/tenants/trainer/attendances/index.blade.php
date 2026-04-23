@extends('layouts.app')

@section('title', 'Attendance Management')

@section('content')
<style>
:root {
  --att-surface:      #ffffff;
  --att-border:       #c5d8f5;
  --att-text:         #001a4d;
  --att-text-sec:     #5a7aaa;
  --att-accent:       #0057B8;
  --att-accent-bg:    rgba(0,87,184,0.10);
  --att-red:          #CE1126;
  --att-red-bg:       rgba(206,17,38,0.15);
  --att-green:        #16a34a;
  --att-green-bg:     rgba(22,163,74,0.15);
  --att-gold:         #F5C518;
  --att-gold-bg:      rgba(245,197,24,0.15);
}
.dark {
  --att-surface:      #0d1f3c;
  --att-border:       #1e3a6b;
  --att-text:         #dde8ff;
  --att-text-sec:     #9ca3af;
  --att-accent:       #5ba3f5;
  --att-accent-bg:    rgba(91,163,245,0.15);
  --att-red:          #ff6b7a;
  --att-red-bg:       rgba(255,107,122,0.15);
  --att-green:        #36d399;
  --att-green-bg:     rgba(54,211,153,0.15);
  --att-gold:         #fcd34d;
  --att-gold-bg:      rgba(252,211,77,0.15);
}
</style>
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--att-accent);">
                <i class="fas fa-calendar-check mr-2" style="color:var(--att-red);"></i>
                Attendance Management
            </h1>
            <p class="text-sm mt-1" style="color:var(--att-text-sec);">
                Record and manage trainee attendance
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('trainer.attendances.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold shadow transition hover:-translate-y-0.5"
            style="background: linear-gradient(135deg,var(--att-red),#A50E1E); box-shadow:0 3px 12px rgba(206,17,38,0.28);">
                <i class="fas fa-plus"></i> Record
            </a>
            <a href="{{ route('trainer.attendances.bulk') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold shadow transition hover:-translate-y-0.5"
            style="background: linear-gradient(135deg,var(--att-accent),#003087); box-shadow:0 3px 12px rgba(0,87,184,0.28);">
                <i class="fas fa-users"></i> Bulk
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
             style="background:var(--att-green-bg); border:1px solid rgba(22,163,74,0.30); color:var(--att-green);">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
             style="background:var(--att-red-bg); border:1px solid rgba(206,17,38,0.30); color:var(--att-red);">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="rounded-2xl border p-5 dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--att-surface); border-color:var(--att-border);">
        <form method="GET" action="{{ route('trainer.attendances.index') }}"
              class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:var(--att-text-sec);"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by trainee name, email, or course..."
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white dark:placeholder-[#3a5a8a]"
                       style="border-color:var(--att-border); color:var(--att-text);"
                       onfocus="this.style.borderColor='var(--att-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='var(--att-border)'; this.style.boxShadow='none'">
            </div>
            <input type="date" name="date" value="{{ request('date') }}"
                   class="px-4 py-2.5 rounded-xl border text-sm outline-none transition
                          dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                   style="border-color:var(--att-border); color:var(--att-text);"
                   onfocus="this.style.borderColor='var(--att-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                   onblur="this.style.borderColor='var(--att-border)'; this.style.boxShadow='none'">
            <select name="status"
                    class="px-4 py-2.5 rounded-xl border text-sm outline-none transition
                           dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                    style="border-color:var(--att-border); color:var(--att-text);"
                    onfocus="this.style.borderColor='var(--att-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                    onblur="this.style.borderColor='var(--att-border)'; this.style.boxShadow='none'">
                <option value="">All Status</option>
                <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Present</option>
                <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Absent</option>
                <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Late</option>
            </select>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl border text-sm font-600 transition"
                    style="border-color:var(--att-border); color:var(--att-accent); background:var(--att-accent-bg);">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'date', 'status']))
                <a href="{{ route('trainer.attendances.index') }}"
                   class="px-5 py-2.5 rounded-xl border text-sm font-600 transition text-center"
                   style="border-color:var(--att-border); color:var(--att-text-sec);">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Attendance Table --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--att-surface); border-color:var(--att-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--att-accent-bg); border-bottom:1px solid var(--att-border);">
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--att-accent);">Trainee</th>
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--att-accent);">Course</th>
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--att-accent);">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--att-accent);">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--att-accent);">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--att-border);">
                    @forelse ($attendances as $attendance)
                        <tr class="transition hover:bg-opacity-50" style="background:var(--att-surface);">
                            <td class="px-5 py-3">
                                <div class="font-600" style="color:var(--att-text);">{{ $attendance->enrollment->trainee->name }}</div>
                                <div class="text-xs" style="color:var(--att-text-sec);">{{ $attendance->enrollment->trainee->email }}</div>
                            </td>
                            <td class="px-5 py-3 text-sm font-600" style="color:var(--att-text-sec);">
                                {{ $attendance->enrollment->course->name }}
                            </td>
                            <td class="px-5 py-3 text-sm font-600" style="color:var(--att-text);">
                                {{ $attendance->date?->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $statusStyles = [
                                        'present' => ['bg' => 'var(--att-green-bg)',  'color' => 'var(--att-green)', 'label' => 'Present', 'icon' => 'fa-check'],
                                        'absent'  => ['bg' => 'var(--att-red-bg)',    'color' => 'var(--att-red)',   'label' => 'Absent',  'icon' => 'fa-times'],
                                        'late'    => ['bg' => 'var(--att-gold-bg)',   'color' => 'var(--att-gold)',  'label' => 'Late',    'icon' => 'fa-clock'],
                                    ];
                                    $style = $statusStyles[$attendance->status] ?? ['bg' => '#f0f5ff', 'color' => '#5a7aaa'];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-700"
                                      style="background:{{ $style['bg'] }}; color:{{ $style['color'] }};">
                                    <i class="fas {{ $style['icon'] }}"></i> {{ $style['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('trainer.attendances.show', $attendance) }}"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs transition"
                                       style="background:var(--att-accent-bg); color:var(--att-accent);"
                                       title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('trainer.attendances.edit', $attendance) }}"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs transition"
                                       style="background:var(--att-gold-bg); color:var(--att-gold);"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('trainer.attendances.destroy', $attendance) }}"
                                          style="display:inline;"
                                          onsubmit="return confirm('Are you sure you want to delete this attendance record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs transition"
                                                style="background:var(--att-red-bg); color:var(--att-red);"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm" style="color:var(--att-text-sec);">
                                <i class="fas fa-inbox text-2xl opacity-50 mb-2 block"></i>
                                No attendance records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if ($attendances->hasPages())
        <div class="flex justify-center">
            {{ $attendances->links() }}
        </div>
    @endif

</div>
@endsection
