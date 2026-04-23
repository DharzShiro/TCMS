@extends('layouts.app')

@section('title', 'Courses Management')

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
                Courses Management
            </h1>
            <p class="text-sm mt-1" style="color:var(--crs-text-sec);">
                Manage all training courses and NC programs.
            </p>
        </div>
        <a href="{{ route('admin.courses.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold shadow transition hover:-translate-y-0.5"
           style="background: linear-gradient(135deg,var(--crs-red),#A50E1E); box-shadow:0 3px 12px rgba(206,17,38,0.28);">
            <i class="fas fa-plus"></i> Add Course
        </a>
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
        <form method="GET" action="{{ route('admin.courses.index') }}"
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
            <select name="status"
                    class="px-4 py-2.5 rounded-xl border text-sm outline-none transition
                           dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                    style="border-color:var(--crs-border); color:var(--crs-text);"
                    onfocus="this.style.borderColor='var(--crs-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                    onblur="this.style.borderColor='var(--crs-border)'; this.style.boxShadow='none'">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-white text-sm font-bold transition hover:-translate-y-0.5"
                    style="background:linear-gradient(135deg,var(--crs-accent),#003087);">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if (request()->filled('search') || request()->filled('level') || request()->filled('status'))
                <a href="{{ route('admin.courses.index') }}"
                   class="px-4 py-2.5 rounded-xl text-sm font-semibold border transition hover:bg-[#e8f0fb]"
                   style="border-color:var(--crs-border); color:var(--crs-text-sec);">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="rounded-2xl border overflow-hidden dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--crs-surface); border-color:var(--crs-border);">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--crs-accent-bg); border-bottom:1px solid var(--crs-border);">
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--crs-accent);">#</th>
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--crs-accent);">Course</th>
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--crs-accent);">Level</th>
                        <th class="px-5 py-3 text-center font-700 text-xs uppercase tracking-wide" style="color:var(--crs-accent);">Duration</th>
                        <th class="px-5 py-3 text-center font-700 text-xs uppercase tracking-wide" style="color:var(--crs-accent);">Enrollments</th>
                        <th class="px-5 py-3 text-center font-700 text-xs uppercase tracking-wide" style="color:var(--crs-accent);">Status</th>
                        <th class="px-5 py-3 text-center font-700 text-xs uppercase tracking-wide" style="color:var(--crs-accent);">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-[#1e3a6b]" style="divide-color:var(--crs-border);">
                    @forelse ($courses as $course)
                        <tr class="transition hover:bg-[#f0f5ff] dark:hover:bg-[#122550]">
                            <td class="px-5 py-4 font-mono text-xs" style="color:var(--crs-text-sec);">
                                {{ $courses->firstItem() + $loop->index }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-800 text-white flex-shrink-0"
                                         style="background:linear-gradient(135deg,var(--crs-accent),#003087);">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div>
                                        <div class="font-700 dark:text-white" style="color:var(--crs-text);">{{ $course->name }}</div>
                                        <div class="text-xs" style="color:var(--crs-text-sec);">{{ $course->code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-xs font-600" style="color:var(--crs-text-sec);">
                                {{ $course->level ?? '—' }}
                            </td>
                            <td class="px-5 py-4 text-center text-xs font-600" style="color:var(--crs-text-sec);">
                                {{ $course->duration_hours }} hrs
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-700 text-white"
                                      style="background:linear-gradient(135deg,var(--crs-accent),#003087);">
                                    {{ $course->enrollments_count }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-700 inline-block"
                                      style="background:{{ $course->status === 'active' ? 'var(--crs-green-bg)' : 'var(--crs-red-bg)' }}; color:{{ $course->status === 'active' ? 'var(--crs-green)' : 'var(--crs-red)' }};">
                                    <i class="fas {{ $course->status === 'active' ? 'fa-check-circle' : 'fa-times-circle' }} mr-1" style="font-size:9px;"></i> {{ ucfirst($course->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.courses.show', $course) }}"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                       style="background:var(--crs-accent-bg); color:var(--crs-accent);" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.courses.edit', $course) }}"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                       style="background:var(--crs-gold-bg); color:#b38a00;" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.courses.destroy', $course) }}"
                                          onsubmit="return confirm('Delete course {{ addslashes($course->name) }}? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                                style="background:var(--crs-red-bg); color:var(--crs-red);" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3" style="color:var(--crs-text-sec);">
                                    <i class="fas fa-book text-4xl opacity-25"></i>
                                    <p class="font-600">No courses found</p>
                                    <p class="text-xs">Try adjusting your search or filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($courses->hasPages())
            <div class="px-5 py-4 border-t dark:border-[#1e3a6b]" style="border-color:var(--crs-border);">
                {{ $courses->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
