@extends('layouts.app')

@section('title', $course->name)

@section('content')
<style>
:root {
  --crsd-surface:     #ffffff;
  --crsd-border:      #c5d8f5;
  --crsd-text:        #001a4d;
  --crsd-text-sec:    #5a7aaa;
  --crsd-accent:      #0057B8;
  --crsd-accent-bg:   rgba(0,87,184,0.10);
  --crsd-red:         #CE1126;
  --crsd-red-bg:      rgba(206,17,38,0.15);
  --crsd-gold:        #F5C518;
  --crsd-gold-bg:     rgba(245,197,24,0.15);
}
.dark {
  --crsd-surface:     #0d1f3c;
  --crsd-border:      #1e3a6b;
  --crsd-text:        #dde8ff;
  --crsd-text-sec:    #9ca3af;
  --crsd-accent:      #5ba3f5;
  --crsd-accent-bg:   rgba(91,163,245,0.15);
  --crsd-red:         #ff6b7a;
  --crsd-red-bg:      rgba(255,107,122,0.15);
  --crsd-gold:        #fcd34d;
  --crsd-gold-bg:     rgba(252,211,77,0.15);
}
</style>
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.courses.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition hover:bg-[#e8f0fb]"
           style="border-color:var(--crsd-border); color:var(--crsd-text-sec);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--crsd-accent);">
                <i class="fas fa-book mr-2" style="color:var(--crsd-red);"></i> Course Details
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--crsd-text-sec);">Viewing details for {{ $course->name }}</p>
        </div>
    </div>

    {{-- Course Card --}}
    <div class="rounded-2xl border overflow-hidden dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--crsd-surface); border-color:var(--crsd-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        {{-- Course hero --}}
        <div class="p-8 flex flex-col sm:flex-row items-start sm:items-center gap-6"
             style="background: linear-gradient(135deg, #003087 0%, #0057B8 100%); position:relative; overflow:hidden;">
            <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,0.05);"></div>
            <div style="position:absolute;bottom:-40px;left:-20px;width:120px;height:120px;border-radius:50%;background:rgba(245,197,24,0.07);"></div>

            <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl font-900 text-white flex-shrink-0"
                 style="background:rgba(255,255,255,0.15); border:2px solid rgba(255,255,255,0.20); position:relative; z-index:1;">
                <i class="fas fa-book"></i>
            </div>

            <div style="position:relative;z-index:1;">
                <div class="text-xl font-800 text-white">{{ $course->name }}</div>
                <div class="text-sm mt-0.5" style="color:rgba(255,255,255,0.65);">{{ $course->code }}</div>
                <div class="flex flex-wrap gap-2 mt-3">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-700"
                          style="background:{{ $course->status === 'active' ? 'rgba(22,163,74,0.15)' : 'rgba(206,17,38,0.15)' }}; border:1px solid {{ $course->status === 'active' ? 'rgba(22,163,74,0.30)' : 'rgba(206,17,38,0.30)' }}; color:{{ $course->status === 'active' ? '#16a34a' : 'var(--crsd-red)' }};">
                        <i class="fas {{ $course->status === 'active' ? 'fa-check-circle' : 'fa-times-circle' }} mr-1" style="font-size:9px;"></i> {{ ucfirst($course->status) }}
                    </span>
                    @if ($course->level)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-700"
                              style="background:var(--crsd-gold-bg); border:1px solid rgba(245,197,24,0.30); color:var(--crsd-gold);">
                            <i class="fas fa-layer-group mr-1" style="font-size:9px;"></i> {{ $course->level }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="sm:ml-auto flex gap-2" style="position:relative;z-index:1;">
                <a href="{{ route('admin.courses.edit', $course) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-700 transition hover:-translate-y-0.5"
                   style="background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.22); color:#fff;">
                    <i class="fas fa-pen text-xs"></i> Edit
                </a>
            </div>
        </div>

        {{-- Detail rows --}}
        <div class="p-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
            @php
                $details = [
                    ['icon' => 'fa-hashtag', 'color' => 'var(--crsd-red)', 'bg' => 'var(--crsd-red-bg)', 'label' => 'Course Code', 'value' => $course->code],
                    ['icon' => 'fa-clock', 'color' => 'var(--crsd-accent)', 'bg' => 'var(--crsd-accent-bg)', 'label' => 'Duration', 'value' => $course->duration_hours . ' hours'],
                    ['icon' => 'fa-file-alt', 'color' => 'var(--crsd-text-sec)', 'bg' => '#f0f5ff', 'label' => 'Description', 'value' => $course->description ?? '—'],
                    ['icon' => 'fa-calendar', 'color' => 'var(--crsd-text-sec)', 'bg' => '#f0f5ff', 'label' => 'Created', 'value' => $course->created_at?->format('F d, Y')],
                ];
            @endphp

            @foreach ($details as $d)
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs flex-shrink-0"
                         style="background:{{ $d['bg'] }}; color:{{ $d['color'] }};">
                        <i class="fas {{ $d['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="text-xs font-700 uppercase tracking-wide mb-0.5" style="color:var(--crsd-text-sec);">{{ $d['label'] }}</div>
                        <div class="text-sm font-600 dark:text-white" style="color:var(--crsd-text);">{{ $d['value'] ?? '—' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-2 gap-4">
        @php
            $stats = [
                ['label' => 'Enrollments', 'value' => $course->enrollments->count(), 'icon' => 'fa-users', 'color' => 'var(--crsd-accent)', 'bg' => 'var(--crsd-accent-bg)'],
                ['label' => 'Schedules', 'value' => $course->schedules->count(), 'icon' => 'fa-calendar-check', 'color' => 'var(--crsd-red)', 'bg' => 'var(--crsd-red-bg)'],
            ];
        @endphp
        @foreach ($stats as $stat)
            <div class="rounded-2xl border p-5 text-center dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
                 style="background:var(--crsd-surface); border-color:var(--crsd-border);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center mx-auto mb-3 text-sm"
                     style="background:{{ $stat['bg'] }}; color:{{ $stat['color'] }};">
                    <i class="fas {{ $stat['icon'] }}"></i>
                </div>
                <div class="text-2xl font-800 dark:text-white" style="color:var(--crsd-text);">{{ $stat['value'] }}</div>
                <div class="text-xs mt-0.5" style="color:var(--crsd-text-sec);">{{ $stat['label'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Danger zone --}}
    <div class="rounded-2xl border p-6 dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--crsd-surface); border-color:rgba(206,17,38,0.25);">
        <h3 class="text-sm font-800 mb-1 flex items-center gap-2" style="color:var(--crsd-red);">
            <i class="fas fa-triangle-exclamation"></i> Danger Zone
        </h3>
        <p class="text-xs mb-4" style="color:var(--crsd-text-sec);">Deleting this course is permanent and cannot be undone.</p>
        <form method="POST" action="{{ route('admin.courses.destroy', $course) }}"
              onsubmit="return confirm('Permanently delete {{ addslashes($course->name) }}?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                    style="background:linear-gradient(135deg,var(--crsd-red),#A50E1E); box-shadow:0 3px 12px rgba(206,17,38,0.25);">
                <i class="fas fa-trash"></i> Delete Course
            </button>
        </form>
    </div>

</div>
@endsection
