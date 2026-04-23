@extends('layouts.app')

@section('title', 'Assessment Details')

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
}
</style>
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('trainee.assessments.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition hover:bg-[#e8f0fb]"
           style="border-color:var(--asf-border); color:var(--asf-text-sec);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--asf-accent);">
                <i class="fas fa-clipboard-check mr-2" style="color:var(--asf-red);"></i> Assessment Details
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--asf-text-sec);">View your assessment result and feedback</p>
        </div>
    </div>

    {{-- Course Info Card --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--asf-surface); border-color:var(--asf-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>
        
        <div class="p-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Course Info --}}
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--asf-text-sec);">Course</p>
                    <div class="font-700 text-lg" style="color:var(--asf-text);">{{ $assessment->enrollment->course->name }}</div>
                    <p class="text-sm mt-1" style="color:var(--asf-text-sec);">{{ $assessment->enrollment->course->code ?? 'N/A' }}</p>
                </div>

                {{-- Enrollment Info --}}
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--asf-text-sec);">Enrollment Status</p>
                    <div class="font-700 text-lg" style="color:var(--asf-text);">{{ ucfirst($assessment->enrollment->status) }}</div>
                    <p class="text-sm mt-1" style="color:var(--asf-text-sec);">Since {{ $assessment->enrollment->enrolled_at?->format('M d, Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Assessment Results Card --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--asf-surface); border-color:var(--asf-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>
        
        <div class="p-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                {{-- Score --}}
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--asf-text-sec);">Score</p>
                    @if($assessment->score !== null)
                        <div class="font-800 text-3xl" style="color:var(--asf-accent);">{{ $assessment->score }}%</div>
                    @else
                        <p class="text-sm font-600" style="color:var(--asf-text-sec);">Not provided</p>
                    @endif
                </div>

                {{-- Result --}}
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--asf-text-sec);">Result</p>
                    @php
                        $resultStyles = [
                            'competent' => ['bg' => 'var(--asf-green-bg)',  'color' => 'var(--asf-green)', 'label' => 'Competent'],
                            'not_yet_competent' => ['bg' => 'var(--asf-red-bg)', 'color' => 'var(--asf-red)', 'label' => 'Not Yet Competent'],
                        ];
                        $style = $resultStyles[$assessment->result] ?? ['bg' => '#f0f5ff', 'color' => '#5a7aaa'];
                    @endphp
                    <span class="inline-block px-3 py-1.5 rounded-lg font-700 text-sm"
                          style="background:{{ $style['bg'] }}; color:{{ $style['color'] }};">
                        {{ $style['label'] }}
                    </span>
                </div>

                {{-- Assessed Date --}}
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--asf-text-sec);">Assessed Date</p>
                    <div class="font-700 text-lg" style="color:var(--asf-text);">{{ $assessment->assessed_at?->format('M d, Y') }}</div>
                    <p class="text-sm mt-1" style="color:var(--asf-text-sec);">{{ $assessment->assessed_at?->format('l') }}</p>
                </div>
            </div>

            {{-- Divider --}}
            <div style="height:1px; background:var(--asf-border);"></div>

            {{-- Remarks --}}
            @if($assessment->remarks)
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--asf-text-sec);">Feedback & Remarks</p>
                    <div class="p-4 rounded-xl" style="background:var(--asf-accent-bg); color:var(--asf-text);">
                        {{ $assessment->remarks }}
                    </div>
                </div>
            @else
                <div>
                    <p class="text-xs font-700 uppercase tracking-wide mb-2" style="color:var(--asf-text-sec);">Feedback & Remarks</p>
                    <div class="p-4 rounded-xl text-sm" style="background:var(--asf-accent-bg); color:var(--asf-text-sec);">
                        <i class="fas fa-info-circle mr-2"></i> No remarks provided by the trainer.
                    </div>
                </div>
            @endif

            {{-- Assessment Metadata --}}
            <div class="grid grid-cols-2 gap-4 pt-4" style="border-top:1px solid var(--asf-border);">
                <div>
                    <p class="text-xs font-600" style="color:var(--asf-text-sec);">Assessed By</p>
                    <p class="font-600 mt-1" style="color:var(--asf-text);">{{ $assessment->trainer->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-600" style="color:var(--asf-text-sec);">Assessment Date</p>
                    <p class="font-600 mt-1" style="color:var(--asf-text);">{{ $assessment->created_at?->format('M d, Y H:i A') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Button --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('trainee.assessments.index') }}"
           class="px-6 py-2.5 rounded-xl text-sm font-600 border transition hover:bg-[#e8f0fb]"
           style="border-color:var(--asf-border); color:var(--asf-text-sec);">
            <i class="fas fa-arrow-left mr-2"></i> Back to Assessments
        </a>
    </div>

</div>
@endsection
