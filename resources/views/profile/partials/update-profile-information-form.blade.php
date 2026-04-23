<section>
    <header class="mb-6">
        <h2 class="text-lg font-bold dark:text-white" style="color:var(--prf-blue-dark);">
            <i class="fas fa-id-card mr-2" style="color:var(--prf-red);"></i> {{ __('Profile Information') }}
        </h2>
        <p class="mt-1 text-sm dark:text-gray-400" style="color:var(--prf-text-sec);">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        {{-- Name Field --}}
        <div>
            <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--prf-text-sec);">
                {{ __('Name') }} <span style="color:var(--prf-red);">*</span>
            </label>
            <input type="text" id="name" name="name"
                   value="{{ old('name', $user->name) }}"
                   placeholder="Enter your full name"
                   required autofocus autocomplete="name"
                   class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                          dark:bg-[var(--prf-surface-secondary)] dark:border-[var(--prf-border)] dark:text-white"
                   style="background:var(--prf-surface); border-color:{{ $errors->has('name') ? 'var(--prf-red)' : 'var(--prf-border)' }}; color:var(--prf-text);"
                   onfocus="this.style.borderColor='var(--prf-blue-primary)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                   onblur="this.style.borderColor='{{ $errors->has('name') ? 'var(--prf-red)' : 'var(--prf-border)' }}'; this.style.boxShadow='none'" />
            @error('name')
                <p class="text-xs mt-1" style="color:var(--prf-red);">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email Field --}}
        <div>
            <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--prf-text-sec);">
                {{ __('Email Address') }} <span style="color:var(--prf-red);">*</span>
            </label>
            <input type="email" id="email" name="email"
                   value="{{ old('email', $user->email) }}"
                   placeholder="Enter your email address"
                   required autocomplete="username"
                   class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                          dark:bg-[var(--prf-surface-secondary)] dark:border-[var(--prf-border)] dark:text-white"
                   style="background:var(--prf-surface); border-color:{{ $errors->has('email') ? 'var(--prf-red)' : 'var(--prf-border)' }}; color:var(--prf-text);"
                   onfocus="this.style.borderColor='var(--prf-blue-primary)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                   onblur="this.style.borderColor='{{ $errors->has('email') ? 'var(--prf-red)' : 'var(--prf-border)' }}'; this.style.boxShadow='none'" />
            @error('email')
                <p class="text-xs mt-1" style="color:var(--prf-red);">{{ $message }}</p>
            @enderror
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-3 pt-4">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                    style="background:linear-gradient(135deg,var(--prf-blue-primary),var(--prf-blue-dark)); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
                <i class="fas fa-save"></i> {{ __('Save Changes') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-500 flex items-center gap-2"
                    style="color:var(--prf-success);">
                    <i class="fas fa-check-circle"></i> {{ __('Saved successfully!') }}
                </p>
            @endif
        </div>
    </form>
</section>