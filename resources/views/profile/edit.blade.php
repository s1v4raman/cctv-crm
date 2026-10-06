<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight font-heading flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-sm shadow-amber-400"></span>
                    {{ __('Account & Security Settings') }}
                </h1>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Manage your Precision IT Systems identity credentials, security password, display theme, and portal permissions.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Theme & Display Preferences (Zoho CRM Color Palette & Day/Night Mode) --}}
            <div class="p-6 sm:p-8 bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800/80 shadow-sm dark:shadow-2xl rounded-2xl">
                <div class="max-w-xl">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: var(--crm-accent, #2563eb);"></span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading">
                            {{ __('Theme & Display Preferences') }}
                        </h3>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">
                        {{ __('Personalize your interface with Zoho-style Day, Night, or Auto mode, and customize your CRM accent color with 13 hand-curated palettes.') }}
                    </p>
                    <x-zoho-theme-customizer :inline="true" :userId="auth()->id()" :userAccent="auth()->user()->theme_accent" :userMode="auth()->user()->theme_mode" :userStyle="auth()->user()->theme_style" />
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800/80 shadow-sm dark:shadow-2xl rounded-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800/80 shadow-sm dark:shadow-2xl rounded-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-white dark:bg-[#0f172a] border border-rose-500/30 dark:border-rose-500/20 shadow-sm dark:shadow-2xl rounded-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
