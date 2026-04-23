<section>
    <header class="mb-6">
        <h2 class="text-lg font-bold dark:text-white" style="color:var(--prf-blue-dark);">
            <i class="fas fa-lock mr-2" style="color:var(--prf-red);"></i> {{ __('Update Password') }}
        </h2>
        <p class="mt-1 text-sm dark:text-gray-400" style="color:var(--prf-text-sec);">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        {{-- Current Password --}}
        <div>
            <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--prf-text-sec);">
                {{ __('Current Password') }} <span style="color:var(--prf-red);">*</span>
            </label>
            <input type="password" id="update_password_current_password" name="current_password"
                   placeholder="Enter your current password"
                   autocomplete="current-password"
                   class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                          dark:bg-[var(--prf-surface-secondary)] dark:border-[var(--prf-border)] dark:text-white"
                   style="background:var(--prf-surface); border-color:{{ $errors->updatePassword->has('current_password') ? 'var(--prf-red)' : 'var(--prf-border)' }}; color:var(--prf-text);"
                   onfocus="this.style.borderColor='var(--prf-blue-primary)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                   onblur="this.style.borderColor='{{ $errors->updatePassword->has('current_password') ? 'var(--prf-red)' : 'var(--prf-border)' }}'; this.style.boxShadow='none'" />
            @error('current_password', 'updatePassword')
                <p class="text-xs mt-1" style="color:var(--prf-red);">{{ $message }}</p>
            @enderror
        </div>

        {{-- New Password --}}
        <div>
            <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--prf-text-sec);">
                {{ __('New Password') }} <span style="color:var(--prf-red);">*</span>
            </label>
            <input type="password" id="update_password_password" name="password"
                   placeholder="Enter a new password"
                   autocomplete="new-password"
                   class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                          dark:bg-[var(--prf-surface-secondary)] dark:border-[var(--prf-border)] dark:text-white"
                   style="background:var(--prf-surface); border-color:{{ $errors->updatePassword->has('password') ? 'var(--prf-red)' : 'var(--prf-border)' }}; color:var(--prf-text);"
                   onfocus="this.style.borderColor='var(--prf-blue-primary)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                   onblur="this.style.borderColor='{{ $errors->updatePassword->has('password') ? 'var(--prf-red)' : 'var(--prf-border)' }}'; this.style.boxShadow='none'" />
            @error('password', 'updatePassword')
                <p class="text-xs mt-1" style="color:var(--prf-red);">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div>
            <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--prf-text-sec);">
                {{ __('Confirm Password') }} <span style="color:var(--prf-red);">*</span>
            </label>
            <input type="password" id="update_password_password_confirmation" name="password_confirmation"
                   placeholder="Confirm your new password"
                   autocomplete="new-password"
                   class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                          dark:bg-[var(--prf-surface-secondary)] dark:border-[var(--prf-border)] dark:text-white"
                   style="background:var(--prf-surface); border-color:{{ $errors->updatePassword->has('password_confirmation') ? 'var(--prf-red)' : 'var(--prf-border)' }}; color:var(--prf-text);"
                   onfocus="this.style.borderColor='var(--prf-blue-primary)'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                   onblur="this.style.borderColor='{{ $errors->updatePassword->has('password_confirmation') ? 'var(--prf-red)' : 'var(--prf-border)' }}'; this.style.boxShadow='none'" />
            @error('password_confirmation', 'updatePassword')
                <p class="text-xs mt-1" style="color:var(--prf-red);">{{ $message }}</p>
            @enderror
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-3 pt-4">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                    style="background:linear-gradient(135deg,var(--prf-blue-primary),var(--prf-blue-dark)); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
                <i class="fas fa-save"></i> {{ __('Update Password') }}
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-500 flex items-center gap-2"
                    style="color:var(--prf-success);">
                    <i class="fas fa-check-circle"></i> {{ __('Password updated successfully!') }}
                </p>
            @endif
        </div>
    </form>
</section>