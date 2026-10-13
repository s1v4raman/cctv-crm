<section>
    <header>
        <h2 class="text-lg font-extrabold text-slate-900 dark:text-white font-heading">
            {{ __('Profile Information & Photo') }}
        </h2>

        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            {{ __("Update your account profile photo, display name, and notification email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        {{-- Interactive Avatar / Photo Upload Component --}}
        <div x-data="{
            photoName: null,
            photoPreview: null,
            removeAvatar: false,
            updatePhotoPreview() {
                const photo = this.$refs.photo.files[0];
                if (!photo) return;
                this.removeAvatar = false;
                this.photoName = photo.name;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.photoPreview = e.target.result;
                };
                reader.readAsDataURL(photo);
            },
            clearPhoto() {
                this.photoPreview = null;
                this.photoName = null;
                this.removeAvatar = true;
                if (this.$refs.photo) {
                    this.$refs.photo.value = null;
                }
            }
        }" class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40">
            <input type="hidden" name="remove_avatar" :value="removeAvatar ? '1' : '0'">
            <input type="file" 
                   x-ref="photo" 
                   name="avatar" 
                   id="avatar-input"
                   class="hidden" 
                   accept="image/png, image/jpeg, image/jpg, image/webp" 
                   @change="updatePhotoPreview()">

            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-2">
                {{ __('Profile Photo') }}
            </label>

            <div class="flex items-center gap-4">
                {{-- Live Image Preview or Monogram Badge --}}
                <div class="relative shrink-0">
                    {{-- 1. New selected photo preview --}}
                    <template x-if="photoPreview">
                        <img :src="photoPreview" alt="Preview" class="w-16 h-16 rounded-full object-cover ring-2 ring-blue-500 shadow-md">
                    </template>

                    {{-- 2. Existing avatar photo --}}
                    <template x-if="!photoPreview && !removeAvatar && '{{ $user->avatar_url }}'">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-full object-cover ring-2 ring-slate-200 dark:ring-slate-700 shadow-sm">
                    </template>

                    {{-- 3. Fallback Initial Monogram --}}
                    <template x-if="!photoPreview && (removeAvatar || !'{{ $user->avatar_url }}')">
                        <div class="w-16 h-16 rounded-full text-white flex items-center justify-center font-bold text-2xl shadow-sm ring-2 ring-slate-200 dark:ring-slate-700" 
                             style="background-color: var(--crm-accent, #2563eb);">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </template>
                </div>

                <div class="space-y-1.5 flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" 
                                @click="$refs.photo.click()" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-slate-800 border border-[#ddd] dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 transition shadow-2xs cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span x-text="photoPreview || '{{ $user->avatar_url }}' ? 'Change Photo' : 'Upload Photo'"></span>
                        </button>

                        <button type="button" 
                                x-show="(photoPreview || ('{{ $user->avatar_url }}' && !removeAvatar))" 
                                @click="clearPhoto()" 
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Remove
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">
                        JPG, PNG, or WEBP up to 4MB. Updates profile views on the header and sidebar instantly across all pages.
                    </p>
                    <template x-if="photoName">
                        <p class="text-[11px] font-mono text-blue-600 dark:text-blue-400 truncate" x-text="'Selected: ' + photoName"></p>
                    </template>
                </div>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
        </div>

        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-3 rounded-xl bg-amber-500/10 border border-amber-500/30">
                    <p class="text-xs text-amber-300">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-xs text-amber-400 hover:text-amber-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-bold text-xs text-emerald-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Changes') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-xs font-bold text-emerald-500 flex items-center gap-1.5"
                >
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ __('Profile & Photo Updated Successfully.') }}
                </p>
            @endif
        </div>
    </form>
</section>
