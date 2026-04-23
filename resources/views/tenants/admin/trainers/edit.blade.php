@extends('layouts.app')

@section('title', 'Edit Trainer')

@section('content')
<style>
    :root {
        --tf-surface:      #ffffff;
        --tf-surface2:     #f0f5ff;
        --tf-border:       #c5d8f5;
        --tf-text:         #001a4d;
        --tf-text-sec:     #1a3a6b;
        --tf-muted:        #5a7aaa;
        --tf-accent:       #0057B8;
        --tf-accent-bg:    #e8f0fb;
        --tf-primary:      #003087;
        --tf-red:          #CE1126;
        --tf-red-bg:       #fff0f2;
        --tf-error:        #CE1126;
        --tf-input-bg:     #ffffff;
    }
    .dark {
        --tf-surface:      #0a1628;
        --tf-surface2:     #0d1f3c;
        --tf-border:       #1e3a6b;
        --tf-text:         #dde8ff;
        --tf-text-sec:     #adc4f0;
        --tf-muted:        #6b8abf;
        --tf-primary:      #5b9cf6;
        --tf-input-bg:     #0d1f3c;
    }
</style>
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.trainers.show', $trainer) }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition"
           style="border-color:var(--tf-border); color:var(--tf-muted);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--tf-primary);">
                <i class="fas fa-pen mr-2" style="color:var(--tf-red);"></i> Edit Trainer
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--tf-muted);">Updating details for {{ $trainer->name }}</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--tf-surface); border-color:var(--tf-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        <form method="POST" action="{{ route('admin.trainers.update', $trainer) }}" class="p-8 space-y-6">
            @csrf
            @method('PUT')

            {{-- Name --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--tf-muted);">
                    Full Name <span style="color:var(--tf-red);">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $trainer->name) }}"
                       placeholder="e.g. Juan Dela Cruz"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition"
                       style="background:var(--tf-input-bg); border-color:{{ $errors->has('name') ? 'var(--tf-red)' : 'var(--tf-border)' }}; color:var(--tf-text);"
                       onfocus="this.style.borderColor='#0057B8'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('name') ? 'var(--tf-red)' : 'var(--tf-border)' }}'; this.style.boxShadow='none'">
                @error('name')
                    <p class="text-xs mt-1" style="color:var(--tf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--tf-muted);">
                    Email Address <span style="color:var(--tf-red);">*</span>
                </label>
                <input type="email" name="email" value="{{ old('email', $trainer->email) }}"
                       placeholder="e.g. trainer@example.com"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition"
                       style="background:var(--tf-input-bg); border-color:{{ $errors->has('email') ? 'var(--tf-red)' : 'var(--tf-border)' }}; color:var(--tf-text);"
                       onfocus="this.style.borderColor='#0057B8'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('email') ? 'var(--tf-red)' : 'var(--tf-border)' }}'; this.style.boxShadow='none'">
                @error('email')
                    <p class="text-xs mt-1" style="color:var(--tf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password (optional on edit) --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--tf-muted);">
                    New Password <span class="font-400 normal-case" style="color:var(--tf-muted);">(leave blank to keep current)</span>
                </label>
                <input type="password" name="password"
                       placeholder="Minimum 8 characters"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition"
                       style="background:var(--tf-input-bg); border-color:{{ $errors->has('password') ? 'var(--tf-red)' : 'var(--tf-border)' }}; color:var(--tf-text);"
                       onfocus="this.style.borderColor='#0057B8'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('password') ? 'var(--tf-red)' : 'var(--tf-border)' }}'; this.style.boxShadow='none'">
                @error('password')
                    <p class="text-xs mt-1" style="color:var(--tf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--tf-muted);">
                    Confirm New Password
                </label>
                <input type="password" name="password_confirmation"
                       placeholder="Repeat new password"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition"
                       style="background:var(--tf-input-bg); border-color:var(--tf-border); color:var(--tf-text);"
                       onfocus="this.style.borderColor='#0057B8'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='var(--tf-border)'; this.style.boxShadow='none'">
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                        style="background:linear-gradient(135deg,#0057B8,#003087); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
                    <i class="fas fa-save"></i> Update Trainer
                </button>
                <a href="{{ route('admin.trainers.show', $trainer) }}"
                   class="px-5 py-2.5 rounded-xl text-sm font-600 border transition"
                   style="border-color:var(--tf-border); color:var(--tf-muted);">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>
@endsection