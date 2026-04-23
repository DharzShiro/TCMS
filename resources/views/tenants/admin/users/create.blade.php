@extends('layouts.app')

@section('title', 'Add User')

@section('content')
<style>
  :root {
    --usf-surface: #ffffff;
    --usf-border: #c5d8f5;
    --usf-text: #001a4d;
    --usf-text-sec: #5a7aaa;
    --usf-accent: #0057B8;
    --usf-accent-bg: #e8f0fb;
    --usf-red: #CE1126;
    --usf-red-bg: #fff0f2;
  }
  .dark {
    --usf-surface: #0d1f3c;
    --usf-border: #1e3a6b;
    --usf-text: #dde8ff;
    --usf-text-sec: #3a5a8a;
    --usf-accent: #0057B8;
    --usf-accent-bg: #122550;
    --usf-red: #CE1126;
    --usf-red-bg: #5a0a0a;
  }
</style>
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.users.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition hover:bg-[#e8f0fb]"
           style="border-color:var(--usf-border); color:var(--usf-text-sec);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold dark:text-white" style="color:var(--usf-accent);">
                <i class="fas fa-plus mr-2" style="color:var(--usf-red);"></i> Add User
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--usf-text-sec);">Create a new user account</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="rounded-2xl border overflow-hidden dark:bg-[#0d1f3c] dark:border-[#1e3a6b]"
         style="background:var(--usf-surface); border-color:var(--usf-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="p-8 space-y-6">
            @csrf

            {{-- Name --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--usf-text-sec);">
                    Full Name <span style="color:var(--usf-red);">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}"
                       placeholder="e.g. Juan Dela Cruz"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:{{ $errors->has('name') ? 'var(--usf-red)' : 'var(--usf-border)' }}; color:var(--usf-text);"
                       onfocus="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--usf-accent'); this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('name') ? 'var(--usf-red)' : 'var(--usf-border)' }}'; this.style.boxShadow='none'">
                @error('name')
                    <p class="text-xs mt-1" style="color:var(--usf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--usf-text-sec);">
                    Email Address <span style="color:var(--usf-red);">*</span>
                </label>
                <input type="email" name="email" value="{{ old('email') }}"
                       placeholder="e.g. user@example.com"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:{{ $errors->has('email') ? 'var(--usf-red)' : 'var(--usf-border)' }}; color:var(--usf-text);"
                       onfocus="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--usf-accent'); this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('email') ? 'var(--usf-red)' : 'var(--usf-border)' }}'; this.style.boxShadow='none'">
                @error('email')
                    <p class="text-xs mt-1" style="color:var(--usf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Role --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--usf-text-sec);">
                    Role <span style="color:var(--usf-red);">*</span>
                </label>
                <select name="role"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                               dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                        style="border-color:{{ $errors->has('role') ? 'var(--usf-red)' : 'var(--usf-border)' }}; color:var(--usf-text);"
                        onfocus="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--usf-accent'); this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                        onblur="this.style.borderColor='{{ $errors->has('role') ? 'var(--usf-red)' : 'var(--usf-border)' }}'; this.style.boxShadow='none'">
                    <option value="">-- Select Role --</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="trainer" {{ old('role') === 'trainer' ? 'selected' : '' }}>Trainer</option>
                    <option value="trainee" {{ old('role') === 'trainee' ? 'selected' : '' }}>Trainee</option>
                </select>
                @error('role')
                    <p class="text-xs mt-1" style="color:var(--usf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--usf-text-sec);">
                    Password <span style="color:var(--usf-red);">*</span>
                </label>
                <input type="password" name="password"
                       placeholder="Minimum 8 characters"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:{{ $errors->has('password') ? 'var(--usf-red)' : 'var(--usf-border)' }}; color:var(--usf-text);"
                       onfocus="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--usf-accent'); this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='{{ $errors->has('password') ? 'var(--usf-red)' : 'var(--usf-border)' }}'; this.style.boxShadow='none'">
                @error('password')
                    <p class="text-xs mt-1" style="color:var(--usf-red);">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--usf-text-sec);">
                    Confirm Password <span style="color:var(--usf-red);">*</span>
                </label>
                <input type="password" name="password_confirmation"
                       placeholder="Repeat password"
                       class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                              dark:bg-[#0a1628] dark:border-[#1e3a6b] dark:text-white"
                       style="border-color:var(--usf-border); color:var(--usf-text);"
                       onfocus="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--usf-accent'); this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor=getComputedStyle(document.documentElement).getPropertyValue('--usf-border'); this.style.boxShadow='none'">
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                        style="background:linear-gradient(135deg,var(--usf-accent),#003087); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
                    <i class="fas fa-save"></i> Save User
                </button>
                <a href="{{ route('admin.users.index') }}"
                   class="px-5 py-2.5 rounded-xl text-sm font-600 border transition hover:bg-[#e8f0fb]"
                   style="border-color:var(--usf-border); color:var(--usf-text-sec);">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
