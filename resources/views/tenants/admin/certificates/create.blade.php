@extends('layouts.app')

@section('title', 'Issue Certificate')

@section('content')
<style>
    :root {
        --certf-surface:      #ffffff;
        --certf-surface2:     #f0f5ff;
        --certf-border:       #c5d8f5;
        --certf-text:         #001a4d;
        --certf-text-sec:     #1a3a6b;
        --certf-muted:        #5a7aaa;
        --certf-accent:       #0057B8;
        --certf-accent-bg:    #e8f0fb;
        --certf-primary:      #003087;
        --certf-red:          #CE1126;
        --certf-red-bg:       #fff0f2;
        --certf-error:        #CE1126;
        --certf-input-bg:     #ffffff;
    }
    .dark {
        --certf-surface:      #0a1628;
        --certf-surface2:     #0d1f3c;
        --certf-border:       #1e3a6b;
        --certf-text:         #dde8ff;
        --certf-text-sec:     #adc4f0;
        --certf-muted:        #6b8abf;
        --certf-primary:      #5b9cf6;
        --certf-input-bg:     #0d1f3c;
    }
</style>
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.certificates.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition"
           style="border-color:var(--certf-border); color:var(--certf-muted);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--certf-primary);">
                <i class="fas fa-plus mr-2" style="color:var(--certf-red);"></i> Issue Certificate
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--certf-muted);">Create a new training certificate</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--certf-surface); border-color:var(--certf-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        <form method="POST" action="{{ route('admin.certificates.store') }}" class="p-8 space-y-6">
            @csrf

            {{-- Enrollment --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--certf-muted);">
                    Trainee & Course <span style="color:var(--certf-red);">*</span>
                </label>
                <select name="enrollment_id"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition"
                        style="background:var(--certf-input-bg); border-color:{{ $errors->has('enrollment_id') ? 'var(--certf-red)' : 'var(--certf-border)' }}; color:var(--certf-text);"
                        onfocus="this.style.borderColor='#0057B8'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                        onblur="this.style.borderColor='{{ $errors->has('enrollment_id') ? 'var(--certf-red)' : 'var(--certf-border)' }}'; this.style.boxShadow='none'">
                    <option value="">Select a Completed Enrollment</option>
                    @foreach ($enrollments as $enrollment)
                        <option value="{{ $enrollment->id }}" {{ old('enrollment_id') == $enrollment->id ? 'selected' : '' }}>
                            {{ $enrollment->trainee->name }} — {{ $enrollment->course->name }} ({{ $enrollment->course->code }})
                        </option>
                    @endforeach
                </select>
                @error('enrollment_id')
                    <p class="text-xs mt-1" style="color:var(--certf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Trainer --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--certf-muted);">
                    Trainer / Assessor <span style="color:var(--certf-red);">*</span>
                </label>
                <select name="trainer_id"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition"
                        style="background:var(--certf-input-bg); border-color:{{ $errors->has('trainer_id') ? 'var(--certf-red)' : 'var(--certf-border)' }}; color:var(--certf-text);"
                        onfocus="this.style.borderColor='#0057B8'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                        onblur="this.style.borderColor='{{ $errors->has('trainer_id') ? 'var(--certf-red)' : 'var(--certf-border)' }}'; this.style.boxShadow='none'">
                    <option value="">— Select Trainer —</option>
                    @foreach ($trainers as $trainer)
                        <option value="{{ $trainer->id }}" {{ old('trainer_id') == $trainer->id ? 'selected' : '' }}>
                            {{ $trainer->name }}
                        </option>
                    @endforeach
                </select>
                @error('trainer_id')
                    <p class="text-xs mt-1" style="color:var(--certf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Certificate Number --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--certf-muted);">
                    Certificate Number <span style="color:var(--certf-red);">*</span>
                </label>
                <input type="text" name="certificate_number" value="{{ old('certificate_number') }}"
                       placeholder="e.g. CERT-2026-0001"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition"
                       style="background:var(--certf-input-bg); border-color:{{ $errors->has('certificate_number') ? 'var(--certf-red)' : 'var(--certf-border)' }}; color:var(--certf-text);"
                       onfocus="this.style.borderColor='#0057B8'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('certificate_number') ? 'var(--certf-red)' : 'var(--certf-border)' }}'; this.style.boxShadow='none'">
                @error('certificate_number')
                    <p class="text-xs mt-1" style="color:var(--certf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Issued Date --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--certf-muted);">
                    Issued Date <span style="color:var(--certf-red);">*</span>
                </label>
                <input type="date" name="issued_at" value="{{ old('issued_at') }}"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition"
                       style="background:var(--certf-input-bg); border-color:{{ $errors->has('issued_at') ? 'var(--certf-red)' : 'var(--certf-border)' }}; color:var(--certf-text);"
                       onfocus="this.style.borderColor='#0057B8'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('issued_at') ? 'var(--certf-red)' : 'var(--certf-border)' }}'; this.style.boxShadow='none'">
                @error('issued_at')
                    <p class="text-xs mt-1" style="color:var(--certf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Expiry Date --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--certf-muted);">
                    Expiry Date (Optional)
                </label>
                <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition"
                       style="background:var(--certf-input-bg); border-color:{{ $errors->has('expires_at') ? 'var(--certf-red)' : 'var(--certf-border)' }}; color:var(--certf-text);"
                       onfocus="this.style.borderColor='#0057B8'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('expires_at') ? 'var(--certf-red)' : 'var(--certf-border)' }}'; this.style.boxShadow='none'">
                <p class="text-xs mt-1" style="color:var(--certf-muted);">Leave blank if certificate does not expire</p>
                @error('expires_at')
                    <p class="text-xs mt-1" style="color:var(--certf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                        style="background:linear-gradient(135deg,#0057B8,#003087); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
                    <i class="fas fa-save"></i> Issue Certificate
                </button>
                <a href="{{ route('admin.certificates.index') }}"
                   class="px-5 py-2.5 rounded-xl text-sm font-600 border transition"
                   style="border-color:var(--certf-border); color:var(--certf-muted);">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>
@endsection