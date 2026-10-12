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

            {{-- 1. Personal Profile Identity Card --}}
            <div class="p-6 sm:p-8 bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800/80 shadow-xs rounded-2xl">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full flex items-center justify-center font-bold text-2xl sm:text-3xl text-white shadow-md ring-4 ring-slate-100 dark:ring-slate-800 crm-user-avatar shrink-0"
                             style="background-color: var(--crm-accent, #2563eb);">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="space-y-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white font-heading truncate">
                                    {{ auth()->user()->name }}
                                </h2>
                                @php
                                    $currentUser = auth()->user();
                                    $roleBadgeClass = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200';
                                    if ($currentUser->isAdmin()) $roleBadgeClass = 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300 border-purple-200 dark:border-purple-800';
                                    elseif ($currentUser->isStaff()) $roleBadgeClass = 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 border-blue-200 dark:border-blue-800';
                                    elseif ($currentUser->isTechnician()) $roleBadgeClass = 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300 border-amber-200 dark:border-amber-800';
                                    elseif ($currentUser->isCustomer()) $roleBadgeClass = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border {{ $roleBadgeClass }}">
                                    {{ $currentUser->role === 'staff' ? 'Employee' : ucfirst($currentUser->role) }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                    ✓ Active
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                <span>{{ $currentUser->email }}</span>
                                <span>•</span>
                                <span>User ID #{{ $currentUser->id }}</span>
                                <span>•</span>
                                <span>Member since {{ $currentUser->created_at?->format('M Y') ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    @if($currentUser->isAdmin())
                        <div class="shrink-0 flex items-center gap-2">
                            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 transition">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Manage All Accounts
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Theme & Display Preferences (Zoho CRM Color Palette & Day/Night Mode) --}}
            <div class="p-6 sm:p-8 bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800/80 shadow-xs rounded-2xl">
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
