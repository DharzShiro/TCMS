<section class="space-y-6">
    <header class="mb-6">
        <h2 class="text-lg font-bold dark:text-white flex items-center gap-2" style="color:var(--prf-red);">
            <i class="fas fa-trash-alt"></i> {{ __('Delete Account') }}
        </h2>
        <p class="mt-1 text-sm dark:text-gray-400" style="color:var(--prf-text-sec);">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <div class="inline-block">
        <button type="button"
                x-data=""
                x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                style="background:linear-gradient(135deg,var(--prf-red),var(--prf-red-dark)); box-shadow:0 3px 12px rgba(206,17,38,0.25);">
            <i class="fas fa-exclamation-triangle"></i> {{ __('Delete Account') }}
        </button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-8 space-y-6">
            @csrf
            @method('delete')

            <div>
                <h2 class="text-lg font-bold" style="color:var(--prf-red);">
                    <i class="fas fa-exclamation-circle mr-2"></i> {{ __('Are you sure you want to delete your account?') }}
                </h2>
                <p class="mt-2 text-sm" style="color:var(--prf-text-sec);">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </p>
            </div>

            <div class="border-l-4 p-4 rounded" style="border-left-color:var(--prf-red); background:var(--prf-alert-bg);">
                <p class="text-sm font-500" style="color:var(--prf-red);">
                    <i class="fas fa-info-circle mr-2"></i> {{ __('This action cannot be undone.') }}
                </p>
            </div>

            <div>
                <label class="block text-xs font-700 uppercase tracking-wide mb-1.5" style="color:var(--prf-text-sec);">
                    {{ __('Confirm Password') }} <span style="color:var(--prf-red);">*</span>
                </label>
                <input type="password"
                        id="password"
                        name="password"
                        placeholder="{{ __('Enter your password') }}"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                               dark:bg-[var(--prf-surface-secondary)] dark:border-[var(--prf-border)] dark:text-white"
                        style="background:var(--prf-surface); border-color:{{ $errors->userDeletion->has('password') ? 'var(--prf-red)' : 'var(--prf-border)' }}; color:var(--prf-text);"
                        onfocus="this.style.borderColor='var(--prf-red)'; this.style.boxShadow='0 0 0 3px rgba(206,17,38,0.10)'"
                        onblur="this.style.borderColor='{{ $errors->userDeletion->has('password') ? 'var(--prf-red)' : 'var(--prf-border)' }}'; this.style.boxShadow='none'" />
                @error('password', 'userDeletion')
                    <p class="text-xs mt-1" style="color:var(--prf-red);">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button"
                        x-on:click="$dispatch('close')"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl border text-sm font-700 transition"
                        style="border-color:var(--prf-border); color:var(--prf-text-sec); background:var(--prf-surface-secondary);">
                    <i class="fas fa-times"></i> {{ __('Cancel') }}
                </button>

                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
                        style="background:linear-gradient(135deg,var(--prf-red),var(--prf-red-dark)); box-shadow:0 3px 12px rgba(206,17,38,0.25);">
                    <i class="fas fa-trash-alt"></i> {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
