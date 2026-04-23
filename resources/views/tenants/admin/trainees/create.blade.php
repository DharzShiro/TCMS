@extends('layouts.app')

@section('title', 'Add Trainee')

@section('content')
<style>
    :root {
        --tnf-surface:      #ffffff;
        --tnf-surface2:     #f0f5ff;
        --tnf-border:       #c5d8f5;
        --tnf-text:         #001a4d;
        --tnf-text-sec:     #1a3a6b;
        --tnf-muted:        #5a7aaa;
        --tnf-accent:       #0057B8;
        --tnf-accent-bg:    #e8f0fb;
        --tnf-primary:      #003087;
        --tnf-red:          #CE1126;
        --tnf-red-bg:       #fff0f2;
        --tnf-error:        #CE1126;
        --tnf-input-bg:     #ffffff;
    }
    .dark {
        --tnf-surface:      #0a1628;
        --tnf-surface2:     #0d1f3c;
        --tnf-border:       #1e3a6b;
        --tnf-text:         #dde8ff;
        --tnf-text-sec:     #adc4f0;
        --tnf-muted:        #6b8abf;
        --tnf-primary:      #5b9cf6;
        --tnf-input-bg:     #0d1f3c;
    }
</style>
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-4">
        <a href="{{ route('admin.trainees.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition"
           style="border-color:var(--tnf-border); color:var(--tnf-muted);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--tnf-primary);">
                <i class="fas fa-plus mr-2" style="color:var(--tnf-red);"></i> Add Trainee
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--tnf-muted);">Create a new trainee account</p>
        </div>
    </div>

    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--tnf-surface); border-color:var(--tnf-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        <form method="POST" action="{{ route('admin.trainees.store') }}" class="p-8 space-y-6">
            @csrf

            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--tnf-muted);">
                    Full Name <span style="color:var(--tnf-red);">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}"
                       placeholder="e.g. Maria Santos"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition"
                       style="background:var(--tnf-input-bg); border-color:{{ $errors->has('name') ? 'var(--tnf-red)' : 'var(--tnf-border)' }}; color:var(--tnf-text);"
                       onfocus="this.style.borderColor='#0057B8'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('name') ? 'var(--tnf-red)' : 'var(--tnf-border)' }}'; this.style.boxShadow='none'">
                @error('name')
                    <p class="text-xs mt-1" style="color:var(--tnf-red);">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--tnf-muted);">
                    Email Address <span style="color:var(--tnf-red);">*</span>
                </label>
                <input type="email" name="email" value="{{ old('email') }}"
                       placeholder="e.g. maria@example.com"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition"
                       style="background:var(--tnf-input-bg); border-color:{{ $errors->has('email') ? 'var(--tnf-red)' : 'var(--tnf-border)' }}; color:var(--tnf-text);"
                       onfocus="this.style.borderColor='#0057B8'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('email') ? 'var(--tnf-red)' : 'var(--tnf-border)' }}'; this.style.boxShadow='none'">
                @error('email')
                    <p class="text-xs mt-1" style="color:var(--tnf-red);">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--tnf-muted);">
                    Password <span style="color:var(--tnf-red);">*</span>
                </label>
                <input type="password" name="password"
                       placeholder="Minimum 8 characters"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition"
                       style="background:var(--tnf-input-bg); border-color:{{ $errors->has('password') ? 'var(--tnf-red)' : 'var(--tnf-border)' }}; color:var(--tnf-text);"
                       onfocus="this.style.borderColor='#0057B8'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('password') ? 'var(--tnf-red)' : 'var(--tnf-border)' }}'; this.style.boxShadow='none'">
                @error('password')
                    <p class="text-xs mt-1" style="color:var(--tnf-red);">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--tnf-muted);">
                    Confirm Password <span style="color:var(--tnf-red);">*</span>
                </label>
                <input type="password" name="password_confirmation"
                       placeholder="Repeat password"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition"
                       style="background:var(--tnf-input-bg); border-color:var(--tnf-border); color:var(--tnf-text);"
                       onfocus="this.style.borderColor='#0057B8'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='var(--tnf-border)'; this.style.boxShadow='none'">
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                        style="background:linear-gradient(135deg,#CE1126,#A50E1E); box-shadow:0 3px 12px rgba(206,17,38,0.25);">
                    <i class="fas fa-save"></i> Save Trainee
                </button>
                <a href="{{ route('admin.trainees.index') }}"
                   class="px-5 py-2.5 rounded-xl text-sm font-600 border transition"
                   style="border-color:var(--tnf-border); color:var(--tnf-muted);">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>
@endsection