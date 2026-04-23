@extends('layouts.app')

@section('title', 'Bulk Attendance')

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
  --att-gold:         #b38a00;
  --att-gold-bg:      rgba(245,197,24,0.20);
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

/* Status radio pill */
.status-pill { display: none; }
.status-label {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 14px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    border: 1.5px solid transparent;
    transition: all 0.15s ease;
    user-select: none;
}
.status-pill:checked + .status-label {
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    transform: translateY(-1px);
}

/* Present */
.pill-present             { background: var(--att-green-bg); color: var(--att-green); border-color: transparent; }
.status-pill:checked ~ .pill-present,
input[value="present"]:checked + .pill-present { border-color: var(--att-green); background: var(--att-green-bg); }

/* Late */
.pill-late { background: var(--att-gold-bg); color: var(--att-gold); border-color: transparent; }
input[value="late"]:checked + .pill-late { border-color: var(--att-gold); }

/* Absent */
.pill-absent { background: var(--att-red-bg); color: var(--att-red); border-color: transparent; }
input[value="absent"]:checked + .pill-absent { border-color: var(--att-red); }

/* Row highlight when selected */
.trainee-row { transition: background 0.15s ease; }
.trainee-row:has(input[value="present"]:checked) { background: rgba(22,163,74,0.04) !important; }
.trainee-row:has(input[value="absent"]:checked)  { background: rgba(206,17,38,0.04) !important; }
.trainee-row:has(input[value="late"]:checked)    { background: rgba(245,197,24,0.04) !important; }

/* Quick-set button */
.quick-btn {
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    border: 1.5px solid transparent;
    transition: all 0.15s ease;
}
.quick-btn:hover { transform: translateY(-1px); }
</style>

<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('trainer.attendances.index') }}"
               class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition hover:bg-[#e8f0fb]"
               style="border-color:var(--att-border); color:var(--att-text-sec);">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold" style="color:var(--att-accent);">
                    <i class="fas fa-users mr-2" style="color:var(--att-red);"></i> Bulk Attendance
                </h1>
                <p class="text-sm mt-0.5" style="color:var(--att-text-sec);">
                    Mark attendance for all trainees in a course at once
                </p>
            </div>
        </div>
        <a href="{{ route('trainer.attendances.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold border transition"
           style="border-color:var(--att-border); color:var(--att-text-sec);">
            <i class="fas fa-user"></i> Single Record
        </a>
    </div>

    {{-- Step 1: Course & Date Selector --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--att-surface); border-color:var(--att-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>
        <div class="p-6">
            <p class="text-xs font-700 uppercase tracking-widest mb-4" style="color:var(--att-text-sec);">
                <i class="fas fa-filter mr-1"></i> Step 1 — Select Course & Date
            </p>
            <form method="GET" action="{{ route('trainer.attendances.bulk') }}"
                  class="flex flex-col sm:flex-row gap-3">

                <select name="course_id"
                        class="flex-1 px-4 py-2.5 rounded-xl border text-sm outline-none transition
                               dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                        style="border-color:var(--att-border); color:var(--att-text);"
                        onfocus="this.style.borderColor='var(--att-accent)'"
                        onblur="this.style.borderColor='var(--att-border)'">
                    <option value="">Select Course</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}"
                            {{ request('course_id') == $course->id ? 'selected' : '' }}>
                            {{ $course->name }} ({{ $course->code }})
                        </option>
                    @endforeach
                </select>

                <input type="date" name="date" value="{{ $selectedDate }}"
                       class="px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:var(--att-border); color:var(--att-text);"
                       onfocus="this.style.borderColor='var(--att-accent)'"
                       onblur="this.style.borderColor='var(--att-border)'">

                <button type="submit"
                        class="px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                        style="background:linear-gradient(135deg,var(--att-accent),#003087); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
                    <i class="fas fa-search mr-1"></i> Load Trainees
                </button>
            </form>
        </div>
    </div>

    {{-- Step 2: Mark Attendance --}}
    @if ($selectedCourse && $enrollments->count() > 0)

        <form method="POST" action="{{ route('trainer.attendances.bulk.store') }}">
            @csrf
            <input type="hidden" name="course_id" value="{{ $selectedCourse->id }}">
            <input type="hidden" name="date" value="{{ $selectedDate }}">

            <div class="rounded-2xl border overflow-hidden"
                 style="background:var(--att-surface); border-color:var(--att-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">
                <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

                {{-- Table Header --}}
                <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                     style="border-bottom:1px solid var(--att-border); background:var(--att-accent-bg);">
                    <div>
                        <p class="text-xs font-700 uppercase tracking-widest mb-0.5" style="color:var(--att-text-sec);">
                            Step 2 — Mark Attendance
                        </p>
                        <p class="font-700" style="color:var(--att-text);">
                            {{ $selectedCourse->name }}
                            <span class="text-xs font-500 ml-2" style="color:var(--att-text-sec);">
                                {{ \Carbon\Carbon::parse($selectedDate)->format('l, M d Y') }}
                            </span>
                        </p>
                    </div>

                    {{-- Quick set all --}}
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-600" style="color:var(--att-text-sec);">Set all:</span>
                        <button type="button" onclick="setAll('present')"
                                class="quick-btn"
                                style="background:var(--att-green-bg); color:var(--att-green); border-color:var(--att-green);">
                            <i class="fas fa-check mr-1"></i> Present
                        </button>
                        <button type="button" onclick="setAll('late')"
                                class="quick-btn"
                                style="background:var(--att-gold-bg); color:var(--att-gold); border-color:var(--att-gold);">
                            <i class="fas fa-clock mr-1"></i> Late
                        </button>
                        <button type="button" onclick="setAll('absent')"
                                class="quick-btn"
                                style="background:var(--att-red-bg); color:var(--att-red); border-color:var(--att-red);">
                            <i class="fas fa-times mr-1"></i> Absent
                        </button>
                    </div>
                </div>

                {{-- Trainee Rows --}}
                <div class="divide-y" style="border-color:var(--att-border);">
                    @foreach ($enrollments as $index => $enrollment)
                        @php
                            $existing = $existingAttendance->get($enrollment->id);
                            $currentStatus = old("attendance.{$enrollment->id}", $existing?->status ?? 'present');
                        @endphp
                        <div class="trainee-row px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                            {{-- Trainee Info --}}
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-800 text-white flex-shrink-0"
                                     style="background:linear-gradient(135deg,var(--att-accent),#003087);">
                                    {{ strtoupper(substr($enrollment->trainee->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-700 text-sm" style="color:var(--att-text);">
                                        {{ $enrollment->trainee->name }}
                                        @if ($existing)
                                            <span class="text-xs font-500 ml-1" style="color:var(--att-text-sec);">(updating)</span>
                                        @endif
                                    </p>
                                    <p class="text-xs" style="color:var(--att-text-sec);">{{ $enrollment->trainee->email }}</p>
                                </div>
                            </div>

                            {{-- Status Pills --}}
                            <div class="flex items-center gap-2 flex-wrap">

                                {{-- Present --}}
                                <input type="radio"
                                       name="attendance[{{ $enrollment->id }}]"
                                       id="present_{{ $enrollment->id }}"
                                       value="present"
                                       class="status-pill"
                                       {{ $currentStatus === 'present' ? 'checked' : '' }}>
                                <label for="present_{{ $enrollment->id }}" class="status-label pill-present">
                                    <i class="fas fa-check text-xs"></i> Present
                                </label>

                                {{-- Late --}}
                                <input type="radio"
                                       name="attendance[{{ $enrollment->id }}]"
                                       id="late_{{ $enrollment->id }}"
                                       value="late"
                                       class="status-pill"
                                       {{ $currentStatus === 'late' ? 'checked' : '' }}>
                                <label for="late_{{ $enrollment->id }}" class="status-label pill-late">
                                    <i class="fas fa-clock text-xs"></i> Late
                                </label>

                                {{-- Absent --}}
                                <input type="radio"
                                       name="attendance[{{ $enrollment->id }}]"
                                       id="absent_{{ $enrollment->id }}"
                                       value="absent"
                                       class="status-pill"
                                       {{ $currentStatus === 'absent' ? 'checked' : '' }}>
                                <label for="absent_{{ $enrollment->id }}" class="status-label pill-absent">
                                    <i class="fas fa-times text-xs"></i> Absent
                                </label>

                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                     style="border-top:1px solid var(--att-border); background:var(--att-accent-bg);">
                    <p class="text-sm" style="color:var(--att-text-sec);">
                        <i class="fas fa-users mr-1"></i>
                        <strong style="color:var(--att-text);">{{ $enrollments->count() }}</strong> trainee(s) —
                        {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('trainer.attendances.index') }}"
                           class="px-5 py-2.5 rounded-xl text-sm font-600 border transition"
                           style="border-color:var(--att-border); color:var(--att-text-sec);">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                                style="background:linear-gradient(135deg,var(--att-accent),#003087); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
                            <i class="fas fa-save"></i> Save Attendance
                        </button>
                    </div>
                </div>
            </div>
        </form>

    @elseif (request()->filled('course_id') && $enrollments->count() === 0)
        <div class="rounded-2xl border p-10 text-center"
             style="background:var(--att-surface); border-color:var(--att-border);">
            <i class="fas fa-user-slash text-3xl mb-3 block" style="color:var(--att-text-sec); opacity:0.4;"></i>
            <p class="font-600" style="color:var(--att-text-sec);">No approved trainees found for this course.</p>
        </div>

    @elseif (!request()->filled('course_id'))
        <div class="rounded-2xl border p-10 text-center"
             style="background:var(--att-surface); border-color:var(--att-border);">
            <i class="fas fa-arrow-up text-3xl mb-3 block" style="color:var(--att-text-sec); opacity:0.3;"></i>
            <p class="font-600" style="color:var(--att-text-sec);">Select a course and date above to load trainees.</p>
        </div>
    @endif

</div>

<script>
function setAll(status) {
    document.querySelectorAll(`input[value="${status}"]`).forEach(radio => {
        radio.checked = true;
        // Trigger row highlight update
        radio.dispatchEvent(new Event('change'));
    });
}
</script>
@endsection