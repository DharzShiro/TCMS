@extends('layouts.app')

@section('title', 'Enrollments Management')

@section('content')
<style>
  :root {
    --en-surface: #ffffff;
    --en-border: #c5d8f5;
    --en-text: #001a4d;
    --en-text-sec: #5a7aaa;
    --en-accent: #0057B8;
    --en-accent-bg: #e8f0fb;
    --en-red: #CE1126;
    --en-red-bg: #fff0f2;
    --en-gold: #F5C518;
    --en-gold-bg: rgba(245, 197, 24, 0.15);
    --en-green: #22c55e;
    --en-green-bg: rgba(34, 197, 94, 0.15);
  }
  .dark {
    --en-surface: #0d1f3c;
    --en-border: #1e3a6b;
    --en-text: #dde8ff;
    --en-text-sec: #3a5a8a;
    --en-accent: #0057B8;
    --en-accent-bg: #122550;
    --en-red: #CE1126;
    --en-red-bg: #5a0a0a;
    --en-gold: #F5C518;
    --en-gold-bg: rgba(245, 197, 24, 0.1);
    --en-green: #86efac;
    --en-green-bg: rgba(52, 168, 83, 0.1);
  }
</style>
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--en-accent);">
                <i class="fas fa-clipboard-list mr-2" style="color:var(--en-red);"></i>
                Enrollments Management
            </h1>
            <p class="text-sm mt-1" style="color:var(--en-text-sec);">
                Manage trainee course enrollments and enrollment status.
            </p>
        </div>
        <a href="{{ route('admin.enrollments.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold shadow transition hover:-translate-y-0.5"
           style="background: linear-gradient(135deg,var(--en-red),#A50E1E); box-shadow:0 3px 12px rgba(206,17,38,0.28);">
            <i class="fas fa-plus"></i> Add Enrollment
        </a>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
             style="background:var(--en-green-bg); border:1px solid var(--en-green); color:var(--en-green);">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="rounded-2xl border p-5 dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--en-surface); border-color:var(--en-border);">
        <form method="GET" action="{{ route('admin.enrollments.index') }}"
              class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:var(--en-text-sec);"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by trainee or course..."
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white dark:placeholder-[#3a5a8a]"
                       style="border-color:var(--en-border); color:var(--en-text);"
                       onfocus="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--en-accent'); this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--en-border'); this.style.boxShadow='none'">
            </div>
            <select name="status"
                    class="px-4 py-2.5 rounded-xl border text-sm outline-none transition
                           dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                    style="border-color:var(--en-border); color:var(--en-text);"
                    onfocus="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--en-accent'); this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                    onblur="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--en-border'); this.style.boxShadow='none';">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="dropped" {{ request('status') === 'dropped' ? 'selected' : '' }}>Dropped</option>
            </select>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-white text-sm font-bold transition hover:-translate-y-0.5"
                    style="background:linear-gradient(135deg,var(--en-accent),#003087);">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if (request()->filled('search') || request()->filled('status'))
                <a href="{{ route('admin.enrollments.index') }}"
                   class="px-4 py-2.5 rounded-xl text-sm font-semibold border transition hover:bg-[#e8f0fb]"
                   style="border-color:var(--en-border); color:var(--en-text-sec);">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="rounded-2xl border overflow-hidden dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--en-surface); border-color:var(--en-border);">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--en-accent-bg); border-bottom:1px solid var(--en-border);">
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--en-accent);">#</th>
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--en-accent);">Trainee</th>
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--en-accent);">Course</th>
                        <th class="px-5 py-3 text-center font-700 text-xs uppercase tracking-wide" style="color:var(--en-accent);">Enrolled Date</th>
                        <th class="px-5 py-3 text-center font-700 text-xs uppercase tracking-wide" style="color:var(--en-accent);">Status</th>
                        <th class="px-5 py-3 text-center font-700 text-xs uppercase tracking-wide" style="color:var(--en-accent);">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-[#1e3a6b]" style="divide-color:var(--en-accent-bg);">
                    @forelse ($enrollments as $enrollment)
                        <tr class="transition hover:bg-[#f0f5ff] dark:hover:bg-[#122550]">
                            <td class="px-5 py-4 font-mono text-xs" style="color:var(--en-text-sec);">
                                {{ $enrollments->firstItem() + $loop->index }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-800 text-white flex-shrink-0"
                                         style="background:linear-gradient(135deg,var(--en-accent),#003087);">
                                        {{ strtoupper(substr($enrollment->trainee->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-700 dark:text-white" style="color:var(--en-text);">{{ $enrollment->trainee->name }}</div>
                                        <div class="text-xs" style="color:var(--en-text-sec);">{{ $enrollment->trainee->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div>
                                    <div class="font-700 dark:text-white" style="color:var(--en-text);">{{ $enrollment->course->name }}</div>
                                    <div class="text-xs" style="color:var(--en-text-sec);">{{ $enrollment->course->code }}</div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center text-xs font-600" style="color:var(--en-text-sec);">
                                {{ $enrollment->enrolled_at?->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @php
                                    $statusColors = [
                                        'pending' => ['bg' => 'rgba(234,179,8,0.15)', 'color' => '#ea b308', 'icon' => 'fa-hourglass-half'],
                                        'approved' => ['bg' => 'rgba(59,130,246,0.15)', 'color' => '#3b82f6', 'icon' => 'fa-check-circle'],
                                        'completed' => ['bg' => 'rgba(34,197,94,0.15)', 'color' => '#22c55e', 'icon' => 'fa-check-double'],
                                        'dropped' => ['bg' => 'rgba(206,17,38,0.15)', 'color' => '#CE1126', 'icon' => 'fa-times-circle'],
                                    ];
                                    $statusColor = $statusColors[$enrollment->status] ?? $statusColors['pending'];
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg text-xs font-700 inline-block"
                                      style="background:{{ $statusColor['bg'] }}; color:{{ $statusColor['color'] }};">
                                    <i class="fas {{ $statusColor['icon'] }} mr-1" style="font-size:9px;"></i> {{ ucfirst($enrollment->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.enrollments.show', $enrollment) }}"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                       style="background:#e8f0fb; color:#0057B8;" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.enrollments.edit', $enrollment) }}"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                       style="background:rgba(245,197,24,0.15); color:#b38a00;" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.enrollments.destroy', $enrollment) }}"
                                          onsubmit="return confirm('Delete enrollment for {{ addslashes($enrollment->trainee->name) }}? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                                style="background:#fff0f2; color:#CE1126;" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3" style="color:var(--en-text-sec);">
                                    <i class="fas fa-clipboard-list text-4xl opacity-25"></i>
                                    <p class="font-600">No enrollments found</p>
                                    <p class="text-xs">Try adjusting your search or filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($enrollments->hasPages())
            <div class="px-5 py-4 border-t dark:border-[#1e3a6b]" style="border-color:var(--en-border);">
                {{ $enrollments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
