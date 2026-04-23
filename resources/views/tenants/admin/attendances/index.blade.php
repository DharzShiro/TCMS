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
  --att-gold:         #F5C518;
  --att-gold-bg:      rgba(245,197,24,0.15);
  --att-green:        #22C55E;
  --att-green-bg:     rgba(34,197,94,0.15);
  --att-muted:        #6B7280;
  --att-muted-bg:     rgba(107,114,128,0.15);
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
  --att-gold:         #fcd34d;
  --att-gold-bg:      rgba(252,211,77,0.15);
  --att-green:        #36d399;
  --att-green-bg:     rgba(54,211,153,0.15);
  --att-muted:        #9ca3af;
  --att-muted-bg:     rgba(156,163,175,0.15);
}
</style>
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--att-accent);">
                <i class="fas fa-clipboard-check mr-2" style="color:var(--att-red);"></i>
                Attendance Management
            </h1>
            <p class="text-sm mt-1" style="color:var(--att-text-sec);">
                Track trainee attendance for all courses.
            </p>
        </div>
        <a href="{{ route('admin.attendances.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold shadow transition hover:-translate-y-0.5"
           style="background: linear-gradient(135deg,var(--att-red),#A50E1E); box-shadow:0 3px 12px rgba(206,17,38,0.28);">
            <i class="fas fa-plus"></i> Record Attendance
        </a>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
             style="background:var(--att-green-bg); border:1px solid rgba(34,197,94,0.30); color:var(--att-green);">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="rounded-2xl border p-5 dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--att-surface); border-color:var(--att-border);">
        <form method="GET" action="{{ route('admin.attendances.index') }}"
              class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:var(--att-text-sec);"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by trainee name, email or course..."
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
                    class="px-5 py-2.5 rounded-xl text-white text-sm font-bold transition hover:-translate-y-0.5"
                    style="background:linear-gradient(135deg,var(--att-accent),#003087);">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if (request()->filled('search') || request()->filled('date') || request()->filled('status'))
                <a href="{{ route('admin.attendances.index') }}"
                   class="px-4 py-2.5 rounded-xl text-sm font-semibold border transition hover:bg-[#e8f0fb]"
                   style="border-color:var(--att-border); color:var(--att-text-sec);">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="rounded-2xl border overflow-hidden dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--att-surface); border-color:var(--att-border);">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--att-accent-bg); border-bottom:1px solid var(--att-border);">
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--att-accent);">#</th>
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--att-accent);">Trainee</th>
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--att-accent);">Course</th>
                        <th class="px-5 py-3 text-center font-700 text-xs uppercase tracking-wide" style="color:var(--att-accent);">Date</th>
                        <th class="px-5 py-3 text-center font-700 text-xs uppercase tracking-wide" style="color:var(--att-accent);">Status</th>
                        <th class="px-5 py-3 text-center font-700 text-xs uppercase tracking-wide" style="color:var(--att-accent);">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-[#1e3a6b]" style="divide-color:var(--att-border);">
                    @forelse ($attendances as $attendance)
                        <tr class="transition hover:bg-[#f0f5ff] dark:hover:bg-[#122550]">
                            <td class="px-5 py-4 font-mono text-xs" style="color:var(--att-text-sec);">
                                {{ $attendances->firstItem() + $loop->index }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-800 text-white flex-shrink-0"
                                         style="background:linear-gradient(135deg,var(--att-gold),#E5B505);">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <div class="font-700 dark:text-white" style="color:var(--att-text);">{{ $attendance->enrollment->trainee->name }}</div>
                                        <div class="text-xs" style="color:var(--att-text-sec);">{{ $attendance->enrollment->trainee->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-700 text-white flex-shrink-0"
                                         style="background:linear-gradient(135deg,var(--att-accent),#003087);">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-600 dark:text-white" style="color:var(--att-text);">
                                            {{ $attendance->enrollment->course->name }}
                                        </div>
                                        <div class="text-xs" style="color:var(--att-text-sec);">
                                            {{ $attendance->enrollment->course->code }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center text-xs font-600" style="color:var(--att-text-sec);">
                                {{ $attendance->date->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-700 inline-block"
                                      style="background:{{ match($attendance->status) {
                                            'present' => 'var(--att-green-bg)',
                                            'absent' => 'var(--att-red-bg)',
                                            'late' => 'var(--att-gold-bg)',
                                            default => 'var(--att-muted-bg)'
                                      } }}; color:{{ match($attendance->status) {
                                            'present' => 'var(--att-green)',
                                            'absent' => 'var(--att-red)',
                                            'late' => 'var(--att-gold)',
                                            default => 'var(--att-muted)'
                                    } }};">  
                                    <i class="fas {{ match($attendance->status) {
                                        'present' => 'fa-check-circle',
                                        'absent' => 'fa-times-circle',
                                        'late' => 'fa-hourglass-end',
                                        default => 'fa-circle-question'
                                    } }} mr-1" style="font-size:9px;"></i> {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.attendances.show', $attendance) }}"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                       style="background:var(--att-accent-bg); color:var(--att-accent);" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.attendances.edit', $attendance) }}"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                       style="background:var(--att-gold-bg); color:#b38a00;" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.attendances.destroy', $attendance) }}"
                                          onsubmit="return confirm('Delete attendance record for {{ addslashes($attendance->enrollment->trainee->name) }}? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                                style="background:var(--att-red-bg); color:var(--att-red);" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3" style="color:var(--att-text-sec);">
                                    <i class="fas fa-clipboard-check text-4xl opacity-25"></i>
                                    <p class="font-600">No attendance records found</p>
                                    <p class="text-xs">Try adjusting your search or filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($attendances->hasPages())
            <div class="px-5 py-4 border-t dark:border-[#1e3a6b]" style="border-color:var(--att-border);">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
