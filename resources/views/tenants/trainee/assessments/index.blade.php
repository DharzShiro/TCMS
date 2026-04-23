@extends('layouts.app')

@section('title', 'My Assessments')

@section('content')
<style>
:root {
  --asf-surface:      #ffffff;
  --asf-border:       #c5d8f5;
  --asf-text:         #001a4d;
  --asf-text-sec:     #5a7aaa;
  --asf-accent:       #0057B8;
  --asf-accent-bg:    rgba(0,87,184,0.10);
  --asf-red:          #CE1126;
  --asf-red-bg:       rgba(206,17,38,0.15);
  --asf-green:        #16a34a;
  --asf-green-bg:     rgba(22,163,74,0.15);
  --asf-gold:         #F5C518;
  --asf-gold-bg:      rgba(245,197,24,0.15);
}
.dark {
  --asf-surface:      #0d1f3c;
  --asf-border:       #1e3a6b;
  --asf-text:         #dde8ff;
  --asf-text-sec:     #9ca3af;
  --asf-accent:       #5ba3f5;
  --asf-accent-bg:    rgba(91,163,245,0.15);
  --asf-red:          #ff6b7a;
  --asf-red-bg:       rgba(255,107,122,0.15);
  --asf-green:        #36d399;
  --asf-green-bg:     rgba(54,211,153,0.15);
  --asf-gold:         #fcd34d;
  --asf-gold-bg:      rgba(252,211,77,0.15);
}
</style>
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--asf-accent);">
                <i class="fas fa-clipboard-check mr-2" style="color:var(--asf-red);"></i>
                My Assessments
            </h1>
            <p class="text-sm mt-1" style="color:var(--asf-text-sec);">
                View your assessment results and feedback
            </p>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Total Assessments --}}
        <div class="rounded-2xl border p-6"
             style="background:var(--asf-surface); border-color:var(--asf-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide" style="color:var(--asf-text-sec);">Total Assessments</p>
                    <p class="text-2xl font-700 mt-2" style="color:var(--asf-text);">{{ $assessments->total() ?? 0 }}</p>
                </div>
                <div class="text-3xl rounded-xl p-3" style="background:var(--asf-accent-bg); color:var(--asf-accent);">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>

        {{-- Competent --}}
        <div class="rounded-2xl border p-6"
             style="background:var(--asf-surface); border-color:var(--asf-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide" style="color:var(--asf-text-sec);">Competent</p>
                    <p class="text-2xl font-700 mt-2" style="color:var(--asf-green);">{{ $competentCount ?? 0 }}</p>
                </div>
                <div class="text-3xl rounded-xl p-3" style="background:var(--asf-green-bg); color:var(--asf-green);">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        {{-- Not Yet Competent --}}
        <div class="rounded-2xl border p-6"
             style="background:var(--asf-surface); border-color:var(--asf-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide" style="color:var(--asf-text-sec);">Not Yet Competent</p>
                    <p class="text-2xl font-700 mt-2" style="color:var(--asf-red);">{{ $notYetCompetentCount ?? 0 }}</p>
                </div>
                <div class="text-3xl rounded-xl p-3" style="background:var(--asf-red-bg); color:var(--asf-red);">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-2xl border p-5 dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--asf-surface); border-color:var(--asf-border);">
        <form method="GET" action="{{ route('trainee.assessments.index') }}"
              class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:var(--asf-text-sec);"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by course name or code..."
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white dark:placeholder-[#3a5a8a]"
                       style="border-color:var(--asf-border); color:var(--asf-text);"
                       onfocus="this.style.borderColor='var(--asf-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='var(--asf-border)'; this.style.boxShadow='none'">
            </div>
            <select name="result"
                    class="px-4 py-2.5 rounded-xl border text-sm outline-none transition
                           dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                    style="border-color:var(--asf-border); color:var(--asf-text);"
                    onfocus="this.style.borderColor='var(--asf-accent)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                    onblur="this.style.borderColor='var(--asf-border)'; this.style.boxShadow='none'">
                <option value="">All Results</option>
                <option value="competent" {{ request('result') === 'competent' ? 'selected' : '' }}>Competent</option>
                <option value="not_yet_competent" {{ request('result') === 'not_yet_competent' ? 'selected' : '' }}>Not Yet Competent</option>
            </select>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl border text-sm font-600 transition"
                    style="border-color:var(--asf-border); color:var(--asf-accent); background:var(--asf-accent-bg);">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'result']))
                <a href="{{ route('trainee.assessments.index') }}"
                   class="px-5 py-2.5 rounded-xl border text-sm font-600 transition text-center"
                   style="border-color:var(--asf-border); color:var(--asf-text-sec);">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Assessments Table --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--asf-surface); border-color:var(--asf-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--asf-accent-bg); border-bottom:1px solid var(--asf-border);">
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--asf-accent);">Course</th>
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--asf-accent);">Score</th>
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--asf-accent);">Result</th>
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--asf-accent);">Assessed Date</th>
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--asf-accent);">Trainer</th>
                        <th class="px-5 py-3 text-left text-xs font-700 uppercase tracking-wide" style="color:var(--asf-accent);">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--asf-border);">
                    @forelse ($assessments as $assessment)
                        <tr class="transition hover:bg-opacity-50" style="background:var(--asf-surface);">
                            <td class="px-5 py-3">
                                <div class="font-600" style="color:var(--asf-text);">{{ $assessment->enrollment->course->name }}</div>
                                <div class="text-xs" style="color:var(--asf-text-sec);">{{ $assessment->enrollment->course->code ?? 'N/A' }}</div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="font-700 text-base" style="color:var(--asf-accent);">
                                    {{ $assessment->score !== null ? $assessment->score . '%' : '—' }}
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $resultStyles = [
                                        'competent' => ['bg' => 'var(--asf-green-bg)',  'color' => 'var(--asf-green)', 'label' => 'Competent'],
                                        'not_yet_competent' => ['bg' => 'var(--asf-red-bg)', 'color' => 'var(--asf-red)', 'label' => 'Not Yet Competent'],
                                    ];
                                    $style = $resultStyles[$assessment->result] ?? ['bg' => '#f0f5ff', 'color' => '#5a7aaa'];
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg text-xs font-700"
                                      style="background:{{ $style['bg'] }}; color:{{ $style['color'] }};">
                                    {{ $style['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-xs" style="color:var(--asf-text-sec);">
                                {{ $assessment->assessed_at?->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-3 text-sm font-600" style="color:var(--asf-text-sec);">
                                {{ $assessment->trainer->name }}
                            </td>
                            <td class="px-5 py-3">
                                <a href="{{ route('trainee.assessments.show', $assessment) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs transition"
                                   style="background:var(--asf-accent-bg); color:var(--asf-accent);"
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm" style="color:var(--asf-text-sec);">
                                <i class="fas fa-inbox text-2xl opacity-50 mb-2 block"></i>
                                No assessments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if ($assessments->hasPages())
        <div class="flex justify-center">
            {{ $assessments->links() }}
        </div>
    @endif

</div>
@endsection
