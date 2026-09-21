<section class="space-y-6">
    <header>
        <h2 class="text-lg font-extrabold text-rose-400 font-heading flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-xs text-slate-400">
            {{ __('Once your account is deleted, all of its associated credentials and authorization keys will be permanently removed.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8 bg-[#0f172a] text-slate-200">
            @csrf
            @method('delete')

            <h2 class="text-lg font-extrabold text-white font-heading">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-2 text-xs text-slate-400">
                {{ __('Once your account is deleted, all of its resources and authorization tokens will be permanently deleted. Please enter your account password to confirm permanent deletion.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full sm:w-3/4"
                    placeholder="{{ __('Enter current password to confirm') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button>
                    {{ __('Permanently Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
