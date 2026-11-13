@php
    $cookieMode = request()->cookie('crm_mode');
    $isDarkServer = ($cookieMode === 'night' || $cookieMode === 'dark' || $cookieMode === null);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 dark:bg-[#0b1120] {{ $isDarkServer ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $worker->name }} - {{ __('Daily Wages Worker Portal') }} | Precision IT Systems</title>

    <!-- PWA Web App Manifest & Mobile Meta Tags -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/field-192.png') }}">
    <link rel="manifest" href="{{ url('/field/manifest.webmanifest') }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PrecisionField">

    <style>
        :root {
            --crm-accent: #2563eb;
            --crm-accent-hover: #1d4ed8;
        }
        * { box-shadow: none !important; }
    </style>

    <!-- Compiled Tailwind (standalone.css) + Alpine via app.js -->
    @vite(['resources/css/standalone.css', 'resources/js/app.js'])

    <x-crm-theme-init />

    <x-tamil-font stack='ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Noto Sans Tamil", "Helvetica Neue", Arial, sans-serif' />
    <x-tamil-input-script />
</head>

<body class="min-h-full antialiased font-sans text-slate-800 dark:text-slate-100 bg-slate-100/70 dark:bg-[#0b1120] transition-colors duration-150"
      x-data="workerPortalData()"
      @theme-mode-changed.window="isDarkMode = $event.detail.isDark">

    {{-- TOP NAVBAR (56px compact native app bar on mobile, full brand bar on desktop) --}}
    <header class="sticky top-0 z-30 bg-white dark:bg-[#0f172a] border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-4xl mx-auto px-3 sm:px-6 h-14 sm:h-16 flex items-center justify-between gap-2">
            {{-- Mobile (< 768px): Worker Avatar / Initials + Name --}}
            <div class="flex md:hidden items-center gap-2.5 min-w-0">
                @if ($todayAttendance && $todayAttendance->photo_path)
                    <img src="{{ $todayAttendance->photo_url }}" alt="{{ $worker->name }}" class="w-8 h-8 rounded-full object-cover border border-emerald-500 shrink-0">
                @else
                    <div class="w-8 h-8 rounded-full bg-[#65a30d] text-white font-bold text-xs flex items-center justify-center shrink-0">
                        {{ strtoupper(mb_substr($worker->name, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <div class="text-sm font-bold text-slate-900 dark:text-white truncate leading-tight">{{ $worker->name }}</div>
                    <div class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 truncate leading-tight">{{ __('Daily Wage Worker') }}</div>
                </div>
            </div>

            {{-- Desktop & Tablet (>= 768px): Brand & Portal Title --}}
            <div class="hidden md:flex items-center gap-3">
                <x-brand-logo :mark="true" class="h-9 w-9 shrink-0" />
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-base font-bold text-slate-900 dark:text-white tracking-tight leading-tight">Precision IT <span class="text-blue-600">Operations</span></span>
                        <span class="px-1.5 py-0.5 rounded text-[11px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900 uppercase tracking-wide">{{ __('Daily Wage Worker') }}</span>
                    </div>
                    <span class="block text-xs text-slate-500 dark:text-slate-400 font-medium">{{ __('Field Attendance & Cabling Tracker') }}</span>
                </div>
            </div>

            <div class="flex items-center gap-1.5 sm:gap-3 ml-auto shrink-0">
                <div class="hidden md:flex flex-col text-right">
                    <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $worker->name }}</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">{{ $worker->phone }}</span>
                </div>

                <x-language-switch />

                {{-- Universal 1-Click Dark/Light Mode Button --}}
                <button @click="toggleTheme()" 
                        type="button" 
                        id="worker-theme-toggle-btn"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-[6px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer shrink-0" 
                        :title="isDarkMode ? {{ Js::from(__('Switch to Light Mode')) }} : {{ Js::from(__('Switch to Dark Mode')) }}" 
                        aria-label="{{ __('Toggle dark/light mode') }}">
                    <template x-if="isDarkMode">
                        <svg class="w-4 h-4 text-amber-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="5" fill="currentColor"/>
                            <line x1="12" y1="1" x2="12" y2="3"/>
                            <line x1="12" y1="21" x2="12" y2="23"/>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                            <line x1="1" y1="12" x2="3" y2="12"/>
                            <line x1="21" y1="12" x2="23" y2="12"/>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                    </template>
                    <template x-if="!isDarkMode">
                        <svg class="w-4 h-4 text-slate-800 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </template>
                </button>

                <!-- Logout Form -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" 
                            title="{{ __('Sign Out') }}"
                            aria-label="{{ __('Sign Out') }}"
                            class="inline-flex items-center justify-center w-8 h-8 sm:w-auto sm:px-3 sm:py-1.5 rounded-[6px] sm:rounded-[4px] border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 gap-1.5 transition-colors cursor-pointer shrink-0">
                        <svg class="w-4 h-4 sm:w-3.5 sm:h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span class="hidden sm:inline">{{ __('Logout') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- MAIN CONTENT WRAPPER --}}
    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-5 space-y-5 pb-24 md:pb-12">

        {{-- STATUS ALERTS --}}
        @if (session('status'))
            <div class="p-3.5 rounded-[4px] bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-medium flex items-center gap-2.5">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error') || (isset($errors) && $errors->any()))
            <div class="p-3.5 rounded-[4px] bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-medium space-y-1">
                @if (session('error'))
                    <div>• {{ session('error') }}</div>
                @endif
                @if (isset($errors))
                    @foreach ($errors->all() as $error)
                        <div>• {{ $error }}</div>
                    @endforeach
                @endif
            </div>
        @endif

        {{-- WORKER HERO SUMMARY CARD (Home tab on mobile, always visible on desktop/tablet) --}}
        <div :class="activeTab === 'home' ? 'block' : 'hidden md:block'"
             class="bg-white dark:bg-[#0f172a] rounded-xl md:rounded-[4px] border border-slate-200 dark:border-slate-800 p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    @if ($todayAttendance && $todayAttendance->photo_path)
                        <div class="relative shrink-0">
                            <img src="{{ $todayAttendance->photo_url }}" 
                                 alt="{{ $worker->name }}" 
                                 @click="openPhotoPreview('{{ $todayAttendance->photo_url }}')"
                                 class="w-14 h-14 rounded-full object-cover border-2 border-emerald-500 cursor-pointer shadow-sm">
                            <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full" title="{{ __('Attendance Verified') }}"></span>
                        </div>
                    @else
                        <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 shrink-0">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                    @endif

                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white leading-tight">{{ $worker->name }}</h2>
                            @if ($todayAttendance)
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    &#10003; {{ __('Punched') }} ({{ in_array($todayAttendance->attendance_type, ['half', 'half_day']) ? __('Half Day') : __('Full Day') }})
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                    {{ __('Pending Punch') }}
                                </span>
                            @endif
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1">
                            <span>{{ $worker->phone }}</span>
                            <span>&bull;</span>
                            <span>{{ __('Rate') }}: <strong class="text-slate-700 dark:text-slate-200">&#8377;{{ number_format($worker->daily_rate, 2) }}</strong>/{{ __('day') }}</span>
                            <span>&bull;</span>
                            <span>{{ __('Today') }}: <strong class="text-slate-700 dark:text-slate-200">{{ now()->translatedFormat('D, d M Y') }}</strong></span>
                        </div>
                    </div>
                </div>

                {{-- Today Wage Quick Pill --}}
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 px-3 py-2 rounded-[4px] self-start sm:self-auto">
                    <div>
                        <span class="block text-[11px] uppercase font-bold text-slate-500 dark:text-slate-400 tracking-wider">{{ __("Today's Total Pay") }}</span>
                        <div class="flex items-baseline gap-1.5 flex-wrap">
                            <span class="text-lg font-extrabold text-blue-600 dark:text-blue-400">&#8377;{{ number_format($todayEarnings, 2) }}</span>
                            @if ($todayAttendance)
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                    (&#8377;{{ number_format($todayBaseWage, 0) }} {{ __('base') }} + &#8377;{{ number_format($todayCablingAmount, 0) }} {{ __('cabling') }} [{{ number_format($todayCablingMetres, 1) }}m @ &#8377;{{ number_format($todayCablingRate, 2) }}/m]@if($todayExtraAmount > 0) + &#8377;{{ number_format($todayExtraAmount, 0) }} {{ __('extra') }} @endif)
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2x2 KPI GRID ON HOME (Today's Pay, Days Worked, Pending Balance, Total Advances) --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <div class="p-3 bg-blue-50/70 dark:bg-blue-950/30 rounded-xl md:rounded-[4px] border border-blue-200/70 dark:border-blue-900/60">
                    <span class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __("Today's Pay") }}</span>
                    <span class="text-lg font-extrabold text-blue-700 dark:text-blue-400">&#8377;{{ number_format($todayEarnings, 0) }}</span>
                </div>
                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl md:rounded-[4px] border border-slate-200 dark:border-slate-700">
                    <span class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Days Worked') }}</span>
                    <span class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $daysWorked }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">{{ __('days') }}</span></span>
                </div>
                <div class="p-3 bg-emerald-50/60 dark:bg-emerald-950/30 rounded-xl md:rounded-[4px] border border-emerald-200/70 dark:border-emerald-900/60">
                    <span class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Pending Balance') }}</span>
                    <span class="text-lg font-extrabold text-emerald-700 dark:text-emerald-400">&#8377;{{ number_format(max($runningBalance, 0), 0) }}</span>
                </div>
                <div class="p-3 bg-amber-50/60 dark:bg-amber-950/30 rounded-xl md:rounded-[4px] border border-amber-200/70 dark:border-amber-900/60">
                    <span class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Total Advances') }}</span>
                    <span class="text-lg font-extrabold text-amber-700 dark:text-amber-400">&#8377;{{ number_format($totalAdvances, 0) }}</span>
                </div>
            </div>

            {{-- 1-Tap Mobile Quick Actions (Home Tab < 768px) --}}
            <div class="md:hidden grid grid-cols-1 gap-2.5 mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="activeTab = 'attendance'"
                        class="w-full min-h-[48px] px-4 py-3 rounded-xl bg-[#65a30d] hover:bg-[#4d7c0f] text-white font-bold text-sm flex items-center justify-between transition cursor-pointer">
                    <span class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ $todayAttendance ? __('Update Punch & Selfie') : __('Punch Attendance') }}</span>
                    </span>
                    <span class="text-xs opacity-90">&rarr;</span>
                </button>
                <div class="grid grid-cols-2 gap-2.5">
                    <button type="button" @click="activeTab = 'work'"
                            class="min-h-[48px] px-3 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>{{ __('Log Work') }}</span>
                    </button>
                    <button type="button" @click="activeTab = 'history'"
                            class="min-h-[48px] px-3 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ __('Pay & History') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- WORKER NAVIGATION TABS (Desktop & Tablet >= 768px) --}}
        <div class="hidden md:flex border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0f172a] rounded-t-[4px] overflow-hidden">
            <button type="button" 
                    @click="activeTab = 'attendance'" 
                    class="flex-1 py-3 px-2 text-xs font-bold text-center border-b-2 flex items-center justify-center gap-2 transition-colors cursor-pointer"
                    :class="activeTab === 'attendance' ? 'border-blue-600 text-blue-600 dark:text-blue-400 bg-blue-50/40 dark:bg-blue-950/40' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                <svg class="w-4 h-4 hidden sm:block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>{{ __('1. Daily Attendance (Photo Punch)') }}</span>
            </button>
            <button type="button" 
                    @click="activeTab = 'work'" 
                    class="flex-1 py-3 px-2 text-xs font-bold text-center border-b-2 flex items-center justify-center gap-2 transition-colors cursor-pointer"
                    :class="activeTab === 'work' ? 'border-blue-600 text-blue-600 dark:text-blue-400 bg-blue-50/40 dark:bg-blue-950/40' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                <svg class="w-4 h-4 hidden sm:block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>{{ __('2. Log Work (Cabling, Cameras, BOM)') }}</span>
            </button>
            <button type="button" 
                    @click="activeTab = 'history'" 
                    class="flex-1 py-3 px-2 text-xs font-bold text-center border-b-2 flex items-center justify-center gap-2 transition-colors cursor-pointer"
                    :class="activeTab === 'history' ? 'border-blue-600 text-blue-600 dark:text-blue-400 bg-blue-50/40 dark:bg-blue-950/40' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                <svg class="w-4 h-4 hidden sm:block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ __('3. My Work & Pay') }}</span>
            </button>
        </div>

        {{-- ========================================================= --}}
        {{-- SECTION 1: ATTENDANCE PUNCH WITH PHOTO VERIFICATION        --}}
        {{-- ========================================================= --}}
        <div x-show="activeTab === 'attendance' || (activeTab === 'home' && isDesktop)" class="space-y-4">
            <div class="bg-white dark:bg-[#0f172a] rounded-xl md:rounded-b-[4px] border md:border-t-0 border-slate-200 dark:border-slate-800 p-4 sm:p-6 space-y-5">
                
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Punch Daily Attendance & Selfie Photo') }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Capture your face on-site or upload a photo to verify your daily shift.') }}</p>
                    </div>
                    @if ($todayAttendance)
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                {{ $todayAttendance->check_in_at ? __('Punched at :time', ['time' => $todayAttendance->check_in_at->format('h:i A')]) : __('Punched today') }}
                            </span>
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('worker.attendance.punch') }}" enctype="multipart/form-data" class="space-y-4" @submit="submittingPunch = true">
                    @csrf

                    <!-- Project Selection -->
                    <div>
                        <label for="att_project_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            {{ __('Customer Site / Installation Project') }} <span class="text-rose-500">*</span>
                        </label>
                        <select id="att_project_id" name="project_id" required 
                                class="w-full px-3 py-2.5 text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 focus:outline-none focus:border-blue-600 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white font-medium">
                            <option value="">-- {{ __('Choose Assigned Project / Site') }} --</option>
                            @foreach ($activeProjects as $proj)
                                <option value="{{ $proj->id }}" {{ ($todayAttendance?->workDay?->project_id == $proj->id) ? 'selected' : '' }}>
                                    {{ $proj->code ? '[' . $proj->code . '] ' : '' }}{{ $proj->title }}@if($proj->customer && !str_contains(strtolower($proj->title), strtolower($proj->customer->name))) ({{ $proj->customer->name }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Attendance Shift Type -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            {{ __('Shift Type') }} <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-3 rounded-[4px] border cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors"
                                   :class="attendanceType === 'full_day' ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-950/40 dark:border-blue-500' : 'border-slate-300 dark:border-slate-700'">
                                <input type="radio" name="attendance_type" value="full_day" x-model="attendanceType" class="text-blue-600 focus:ring-0">
                                <div>
                                    <span class="block text-xs font-bold text-slate-900 dark:text-white">{{ __('Full Day (100% Rate)') }}</span>
                                    <span class="block text-[11px] text-slate-500 dark:text-slate-400">&#8377;{{ number_format($worker->daily_rate, 2) }} {{ __('wage payout') }}</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3 rounded-[4px] border cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors"
                                   :class="attendanceType === 'half_day' ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-950/40 dark:border-blue-500' : 'border-slate-300 dark:border-slate-700'">
                                <input type="radio" name="attendance_type" value="half_day" x-model="attendanceType" class="text-blue-600 focus:ring-0">
                                <div>
                                    <span class="block text-xs font-bold text-slate-900 dark:text-white">{{ __('Half Day (50% Rate)') }}</span>
                                    <span class="block text-[11px] text-slate-500 dark:text-slate-400">&#8377;{{ number_format($worker->daily_rate * 0.5, 2) }} {{ __('wage payout') }}</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Photo Attendance Capture & Upload Block -->
                    <div class="border border-slate-300 dark:border-slate-700 rounded-[4px] p-4 bg-slate-50/50 dark:bg-slate-900/40 space-y-3">
                        <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                            {{ __('Attendance Photo / Selfie Verification') }} <span class="text-rose-500">*</span>
                        </label>

                        <!-- Hidden Input for Base64 Camera Data -->
                        <input type="hidden" name="photo_data" :value="capturedPhotoData">

                        <!-- Preview Container -->
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            
                            {{-- Live Video / Canvas / Image Preview Area --}}
                            <div class="w-full sm:w-64 h-56 sm:h-48 bg-slate-200 dark:bg-slate-800 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl md:rounded-[4px] flex items-center justify-center overflow-hidden relative">
                                
                                {{-- 1. Live Camera Stream --}}
                                <video x-ref="videoElement" 
                                       x-show="cameraActive" 
                                       autoplay playsinline 
                                       class="w-full h-full object-cover"></video>

                                {{-- 2. Captured or Uploaded Preview --}}
                                <template x-if="!cameraActive && previewUrl">
                                    <img :src="previewUrl" alt="{{ __('Selfie Preview') }}" class="w-full h-full object-cover">
                                </template>

                                {{-- 3. Existing Punched Photo (if already submitted today) --}}
                                @if ($todayAttendance && $todayAttendance->photo_path)
                                    <template x-if="!cameraActive && !previewUrl">
                                        <img src="{{ $todayAttendance->photo_url }}" alt="{{ __('Today Attendance Photo') }}" class="w-full h-full object-cover">
                                    </template>
                                @endif

                                {{-- 4. Placeholder if Empty --}}
                                <template x-if="!cameraActive && !previewUrl && !hasExistingPhoto">
                                    <div class="text-center p-3 text-slate-600 dark:text-slate-400">
                                        <svg class="w-10 h-10 mx-auto mb-1 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span class="text-xs font-medium">{{ __('No photo captured yet') }}</span>
                                    </div>
                                </template>

                                <canvas x-ref="canvasElement" class="hidden"></canvas>
                            </div>

                            {{-- Camera & Upload Action Buttons (Full-width 48px thumb targets) --}}
                            <div class="flex-1 w-full space-y-2.5">
                                
                                {{-- Button to Start Live Camera --}}
                                <button type="button" 
                                        x-show="!cameraActive" 
                                        @click="startCamera()" 
                                        class="w-full min-h-[48px] py-3 px-4 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 text-xs font-bold rounded-xl md:rounded-[4px] flex items-center justify-center gap-2 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    <span x-text="previewUrl ? {{ Js::from(__('Retake Live Selfie')) }} : {{ Js::from(__('Open Front/Site Camera')) }}"></span>
                                </button>

                                {{-- Button to Snap Photo when camera is running --}}
                                <button type="button" 
                                        x-show="cameraActive" 
                                        @click="snapPhoto()" 
                                        class="w-full min-h-[48px] py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl md:rounded-[4px] flex items-center justify-center gap-2 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>{{ __('Capture Selfie Photo Now') }}</span>
                                </button>

                                {{-- Full-width 48px Selfie Upload Button --}}
                                <div class="relative">
                                    <input type="file" 
                                           id="photo_file" 
                                           name="photo" 
                                           accept="image/*" 
                                           capture="user"
                                           @change="handleFileUpload($event)"
                                           class="sr-only">
                                    <label for="photo_file"
                                           class="w-full min-h-[48px] py-3 px-4 rounded-xl md:rounded-[4px] border font-bold text-xs flex items-center justify-center gap-2 cursor-pointer transition-colors"
                                           :class="(previewUrl || hasExistingPhoto) ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 hover:bg-slate-200/70'">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M16 12l-4-4m0 0l-4 4m4-4v9"/></svg>
                                        <span x-text="previewUrl ? {{ Js::from(__('Selfie Selected ✓ (Change Photo)')) }} : (hasExistingPhoto ? {{ Js::from(__('Selfie Verified ✓ (Upload New)')) }} : {{ Js::from(__('Upload Selfie / Choose Gallery')) }})"></span>
                                    </label>
                                </div>

                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug">
                                    {{ __('Works seamlessly with front selfie camera or phone file chooser. Photo is stored on the project attendance log for payroll signoff.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Check-in notes -->
                    <div>
                        <label for="att_notes" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                            {{ __('Shift Notes / Site Location (Optional)') }}
                        </label>
                        <x-tamil-input>
                            <input type="text" id="att_notes" name="notes" 
                                   value="{{ $todayAttendance?->notes }}"
                                   placeholder="{{ __('e.g. Ground Floor CCTV cabling, Main Gate tower') }}" 
                                   class="w-full px-3 py-2 text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 focus:outline-none focus:border-blue-600 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white font-medium">
                        </x-tamil-input>
                    </div>

                    <!-- Submit Punch Button -->
                    <div class="pt-2">
                        <button type="submit" 
                                :disabled="submittingPunch"
                                class="w-full min-h-[48px] py-3 px-4 bg-[#65a30d] hover:bg-[#4d7c0f] disabled:opacity-70 text-white font-bold text-sm rounded-xl md:rounded-[4px] transition-colors flex items-center justify-center gap-2 cursor-pointer">
                            <svg x-show="!submittingPunch" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <svg x-show="submittingPunch" x-cloak class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                            <span x-text="submittingPunch ? {{ Js::from(__('Saving Attendance...')) }} : {{ Js::from($todayAttendance ? __("Update Today's Punch & Selfie Photo") : __('Clock In & Confirm Attendance')) }}"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- SECTION 2: LOG FIELD WORK (CABLING, CAMERAS & MATERIALS)  --}}
        {{-- ========================================================= --}}
        <div x-show="activeTab === 'work'" class="space-y-4">
            <div class="bg-white dark:bg-[#0f172a] rounded-xl md:rounded-b-[4px] border md:border-t-0 border-slate-200 dark:border-slate-800 p-4 sm:p-6 space-y-5">
                
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Log Daily Field Work & Material Consumption') }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Record cabling pulled and cameras mounted today. Automatically connects to Project BOM and daily wage calculations.') }}</p>
                </div>

                <form method="POST" action="{{ route('worker.work.log') }}" class="space-y-4" @submit="submittingWork = true">
                    @csrf

                    <!-- Project Selection -->
                    <div>
                        <label for="work_project_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            {{ __('Customer Site / Installation Project') }} <span class="text-rose-500">*</span>
                        </label>
                        <select id="work_project_id" name="project_id" required 
                                x-model="selectedProjectId"
                                class="w-full px-3 py-2.5 text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 focus:outline-none focus:border-blue-600 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white font-medium">
                            <option value="">-- {{ __('Choose Installation Project') }} --</option>
                            @foreach ($activeProjects as $proj)
                                <option value="{{ $proj->id }}">
                                    {{ $proj->code ? '[' . $proj->code . '] ' : '' }}{{ $proj->title }}@if($proj->customer && !str_contains(strtolower($proj->title), strtolower($proj->customer->name))) ({{ $proj->customer->name }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Cabling Meters Pulled -->
                    <div class="p-3.5 bg-blue-50/50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900/60 rounded-[4px] space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="cabling_metres" class="block text-xs font-bold text-blue-900 dark:text-blue-300 uppercase tracking-wider">
                                {{ __('Cabling Meters Pulled (Cat6 / 3+1 / Fiber)') }}
                            </label>
                            <span class="text-xs font-semibold text-blue-700 dark:text-blue-400">
                                {{ __('Per Metre Payout') }}: <strong class="font-bold" x-text="'&#8377;' + getProjectRate().toFixed(2) + '/m'"></strong>
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="stepCabling(-5)" aria-label="Decrease cabling metres"
                                    class="min-h-[44px] min-w-[44px] rounded-xl md:rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-bold text-lg flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer shrink-0">&minus;</button>
                            <div class="relative flex-1">
                                <input type="number" id="cabling_metres" name="cabling_metres" 
                                       step="0.5" min="0" 
                                       x-model="cablingMetres"
                                       placeholder="{{ __('e.g. 120') }}" 
                                       class="w-full min-h-[44px] px-3 py-2 text-base font-bold text-slate-900 dark:text-white rounded-xl md:rounded-[4px] border border-slate-300 dark:border-slate-700 focus:outline-none focus:border-blue-600 bg-white dark:bg-[#0b132b]">
                                <span class="absolute right-3 top-3 text-xs font-bold text-slate-400">{{ __('METERS') }}</span>
                            </div>
                            <button type="button" @click="stepCabling(5)" aria-label="Increase cabling metres"
                                    class="min-h-[44px] min-w-[44px] rounded-xl md:rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-bold text-lg flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer shrink-0">+</button>
                        </div>
                        
                        <!-- Quick Add Meters Chips -->
                        <div class="flex flex-wrap items-center gap-1.5 pt-1">
                            <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Quick Add:') }}</span>
                            <button type="button" @click="addCabling(10)" class="px-2 py-0.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">+10m</button>
                            <button type="button" @click="addCabling(25)" class="px-2 py-0.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">+25m</button>
                            <button type="button" @click="addCabling(50)" class="px-2 py-0.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">+50m</button>
                            <button type="button" @click="addCabling(100)" class="px-2 py-0.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">+100m</button>
                            <button type="button" @click="cablingMetres = 0" class="px-2 py-0.5 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 rounded text-xs font-medium hover:bg-rose-100">{{ __('Clear') }}</button>
                        </div>

                        <!-- Live Incentive Calculation Pill -->
                        <div class="mt-2 text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 p-2.5 rounded border border-emerald-200 dark:border-emerald-800" x-show="cablingMetres > 0">
                            <div class="flex items-center justify-between">
                                <span>{{ __('Cabling Incentive Preview') }}:</span>
                                <span class="font-mono font-bold" x-html="cablingMetres + 'm &times; &#8377;' + getProjectRate().toFixed(2) + '/m = +&#8377;' + (parseFloat(cablingMetres) * getProjectRate()).toFixed(2)"></span>
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 font-normal mt-0.5">
                                {{ __('Base shift wage (&#8377;:base) + Cabling incentive = Total Day Pay', ['base' => number_format($worker->daily_rate, 0)]) }}
                            </div>
                        </div>
                    </div>

                    <!-- Cameras Mounted / Installed -->
                    <div class="p-3.5 bg-emerald-50/50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/60 rounded-[4px] space-y-2">
                        <label for="cameras_installed" class="block text-xs font-bold text-emerald-900 dark:text-emerald-300 uppercase tracking-wider">
                            {{ __('Cameras Fixed & Terminated (IP / Dome / Bullet)') }}
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="stepCameras(-1)" aria-label="Decrease cameras"
                                    class="min-h-[44px] min-w-[44px] rounded-xl md:rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-bold text-lg flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer shrink-0">&minus;</button>
                            <div class="relative flex-1">
                                <input type="number" id="cameras_installed" name="cameras_installed" 
                                       step="1" min="0" 
                                       x-model="camerasInstalled"
                                       placeholder="{{ __('e.g. 4') }}" 
                                       class="w-full min-h-[44px] px-3 py-2 text-base font-bold text-slate-900 dark:text-white rounded-xl md:rounded-[4px] border border-slate-300 dark:border-slate-700 focus:outline-none focus:border-blue-600 bg-white dark:bg-[#0b132b]">
                                <span class="absolute right-3 top-3 text-xs font-bold text-slate-400">{{ __('UNITS') }}</span>
                            </div>
                            <button type="button" @click="stepCameras(1)" aria-label="Increase cameras"
                                    class="min-h-[44px] min-w-[44px] rounded-xl md:rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-bold text-lg flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer shrink-0">+</button>
                        </div>

                        <!-- Quick Add Cameras Chips -->
                        <div class="flex flex-wrap items-center gap-1.5 pt-1">
                            <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Quick Add:') }}</span>
                            <button type="button" @click="addCameras(1)" class="px-2 py-0.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">+1 {{ __('Camera') }}</button>
                            <button type="button" @click="addCameras(2)" class="px-2 py-0.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">+2 {{ __('Cameras') }}</button>
                            <button type="button" @click="addCameras(4)" class="px-2 py-0.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">+4 {{ __('Cameras') }}</button>
                            <button type="button" @click="addCameras(8)" class="px-2 py-0.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">+8 {{ __('Cameras') }}</button>
                        </div>
                    </div>

                    <!-- Extra Material / Hardware Consumed (BOM Tracking) -->
                    <div class="border border-slate-200 dark:border-slate-800 rounded-[4px] p-3.5 bg-slate-50 dark:bg-slate-900/40 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                                {{ __('Additional Hardware / Material Pulled (BOM Log)') }}
                            </span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Optional') }}</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <div class="sm:col-span-2">
                                <label for="material_name" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('Item / Hardware Name') }}</label>
                                <x-tamil-input>
                                    <input type="text" id="material_name" name="material_name" 
                                           placeholder="{{ __('e.g. RJ45 Connectors / PVC Conduit 25mm / Backbox') }}" 
                                           class="w-full px-3 py-1.5 text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 focus:outline-none focus:border-blue-600 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white">
                                </x-tamil-input>
                            </div>

                            <div>
                                <label for="material_qty" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('Quantity & Unit') }}</label>
                                <div class="flex gap-1">
                                    <input type="number" id="material_qty" name="material_qty" step="0.1" min="0" placeholder="{{ __('Qty') }}" 
                                           class="w-16 px-2 py-1.5 text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 focus:outline-none focus:border-blue-600 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white">
                                    <select aria-label="{{ __('Material unit') }}" name="material_unit" class="flex-1 px-2 py-1.5 text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white">
                                        <option value="nos">{{ __('nos') }}</option>
                                        <option value="meters">{{ __('meters') }}</option>
                                        <option value="boxes">{{ __('boxes') }}</option>
                                        <option value="pcs">{{ __('pcs') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="drum_or_batch_no" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('Drum / Roll / Batch Identifier') }}</label>
                            <input type="text" id="drum_or_batch_no" name="drum_or_batch_no" 
                                   placeholder="{{ __('e.g. Drum #D-04 or D-Link 305m Box #1') }}" 
                                   class="w-full px-3 py-1.5 text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 focus:outline-none focus:border-blue-600 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Work notes / execution details -->
                    <div>
                        <label for="work_notes" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                            {{ __('Field Execution Notes') }}
                        </label>
                        <x-tamil-input>
                            <textarea id="work_notes" name="work_notes" rows="2" 
                                      placeholder="{{ __('e.g. Completed cabling for Reception & Corridor. Mounted 2 dome cameras on gypsum ceiling.') }}"
                                      class="w-full px-3 py-2 text-xs rounded-[4px] border border-slate-300 dark:border-slate-700 focus:outline-none focus:border-blue-600 bg-white dark:bg-[#0b132b] text-slate-900 dark:text-white"></textarea>
                        </x-tamil-input>
                    </div>

                    <!-- Submit Work Log Button -->
                    <div class="pt-2">
                        <button type="submit" 
                                :disabled="submittingWork"
                                class="w-full min-h-[48px] py-3 px-4 bg-blue-600 hover:bg-blue-700 disabled:opacity-70 text-white font-bold text-sm rounded-xl md:rounded-[4px] transition-colors flex items-center justify-center gap-2 cursor-pointer">
                            <svg x-show="!submittingWork" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <svg x-show="submittingWork" x-cloak class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                            <span x-text="submittingWork ? {{ Js::from(__('Saving Work Log...')) }} : {{ Js::from(__('Submit Work Entry & Update Project BOM')) }}"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- SECTION 3: MY WORK & PAY (attendance, work logged, money)  --}}
        {{-- ========================================================= --}}
        <div x-show="activeTab === 'history'" class="space-y-4">
            <div class="bg-white dark:bg-[#0f172a] rounded-xl md:rounded-b-[4px] border md:border-t-0 border-slate-200 dark:border-slate-800 p-4 sm:p-6 space-y-4">

                {{-- Pay summary --}}
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Your pay') }}</h3>
                    <div class="grid grid-cols-3 gap-2.5 mt-2 text-center">
                        <div class="p-2.5 bg-slate-50 dark:bg-slate-800/60 rounded-[4px] border border-slate-200 dark:border-slate-700">
                            <span class="block text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ __('Earned') }}</span>
                            <span class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">&#8377;{{ number_format($totalEarned, 0) }}</span>
                        </div>
                        <div class="p-2.5 bg-slate-50 dark:bg-slate-800/60 rounded-[4px] border border-slate-200 dark:border-slate-700">
                            <span class="block text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ __('Paid to you') }}</span>
                            <span class="text-sm sm:text-base font-bold text-emerald-700 dark:text-emerald-400">&#8377;{{ number_format($totalPaid, 0) }}</span>
                        </div>
                        <div class="p-2.5 bg-slate-50 dark:bg-slate-800/60 rounded-[4px] border border-slate-200 dark:border-slate-700">
                            <span class="block text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ __('Balance due') }}</span>
                            <span class="text-sm sm:text-base font-bold text-blue-700 dark:text-blue-400">&#8377;{{ number_format(max($runningBalance, 0), 0) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Payments received from the office --}}
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Payments received') }}</h3>
                    @if ($recentPayments->isNotEmpty())
                        {{-- Mobile Payment Cards (< 768px) --}}
                        <div class="md:hidden mt-2 space-y-2">
                            @foreach ($recentPayments as $wagePayment)
                                <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/50 space-y-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $wagePayment->payment_date?->translatedFormat('d M Y') }}</span>
                                        <span class="shrink-0 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">&#8377;{{ number_format($wagePayment->amount, 0) }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                        {{ $wagePayment->payment_type === 'advance' ? __('Advance') : __('Wages') }} &bull; {{ ['cash' => __('Cash'), 'upi' => __('UPI / GPay'), 'bank' => __('Bank')][$wagePayment->payment_mode] ?? ucfirst((string) $wagePayment->payment_mode) }}@if(!empty($wagePayment->reference_no)) &bull; {{ $wagePayment->reference_no }}@endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Desktop/Tablet Payments Table (>= 768px) --}}
                        <div class="hidden md:block mt-2 overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-[4px]">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-xs">
                                <thead class="bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-bold text-[11px]">
                                    <tr>
                                        <th class="py-2 px-3 text-left">{{ __('Date') }}</th>
                                        <th class="py-2 px-3 text-left">{{ __('For') }}</th>
                                        <th class="py-2 px-3 text-left">{{ __('Paid by') }}</th>
                                        <th class="py-2 px-3 text-right">{{ __('Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                    @foreach ($recentPayments as $wagePayment)
                                        <tr>
                                            <td class="py-2 px-3 whitespace-nowrap text-slate-900 dark:text-white">{{ $wagePayment->payment_date?->translatedFormat('d M Y') }}</td>
                                            <td class="py-2 px-3 text-slate-700 dark:text-slate-300">{{ $wagePayment->payment_type === 'advance' ? __('Advance') : __('Wages') }}</td>
                                            <td class="py-2 px-3 text-slate-700 dark:text-slate-300">{{ ['cash' => __('Cash'), 'upi' => __('UPI / GPay'), 'bank' => __('Bank')][$wagePayment->payment_mode] ?? ucfirst((string) $wagePayment->payment_mode) }}</td>
                                            <td class="py-2 px-3 text-right font-bold text-emerald-700 dark:text-emerald-400 whitespace-nowrap">&#8377;{{ number_format($wagePayment->amount, 0) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('No payments recorded yet.') }}</p>
                    @endif
                </div>

                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 pt-2">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Your attendance & work (last 30 days)') }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Each day you punched in, the site, your shift, the work you logged and the exact pay breakdown.') }}</p>
                </div>

                @if ($recentAttendances->count() > 0)
                    {{-- Mobile Attendance & Work Cards (< 768px) --}}
                    <div class="md:hidden space-y-2.5">
                        @foreach ($recentAttendances as $att)
                            @php
                                $attMultiplier = in_array($att->attendance_type, ['half', 'half_day']) ? 0.5 : 1.0;
                                $attBaseWage = (float) $att->daily_rate_snapshot * $attMultiplier;
                                $attCablingRate = (float) ($att->cabling_rate ?: 20.00);
                                $attCablingAmount = (float) ($att->cabling_amount ?: ($att->cabling_metres * $attCablingRate));
                            @endphp
                            <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40 space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $att->workDay?->work_date ? $att->workDay->work_date->translatedFormat('d M Y') : __('N/A') }}</span>
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ !in_array($att->attendance_type, ['half', 'half_day']) ? 'bg-blue-100 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300' : 'bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300' }}">
                                        {{ in_array($att->attendance_type, ['half', 'half_day']) ? __('Half Day') : __('Full Day') }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between gap-2.5">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        @if ($att->photo_path)
                                            <img src="{{ $att->photo_url }}" alt="{{ __('Attendance Photo') }}"
                                                 @click="openPhotoPreview('{{ $att->photo_url }}')"
                                                 class="w-10 h-10 rounded-full object-cover border border-slate-300 dark:border-slate-700 cursor-pointer shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-slate-500 text-xs font-bold shrink-0">--</div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="text-xs font-medium text-slate-700 dark:text-slate-200 truncate">{{ $att->workDay?->project?->title ?? __('Site Work') }}</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                                {{ number_format($att->cabling_metres, 1) }}m {{ __('cabling') }} &bull; {{ $att->cameras_installed }} {{ __('cams') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="text-sm font-extrabold text-blue-700 dark:text-blue-400">&#8377;{{ number_format($att->total_amount, 0) }}</div>
                                        <div class="text-[10px] text-slate-400">&#8377;{{ number_format($attBaseWage, 0) }} {{ __('base') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Desktop/Tablet Attendance Table (>= 768px) --}}
                    <div class="hidden md:block overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-[4px]">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-bold uppercase text-[11px] tracking-wider">
                                <tr>
                                    <th class="py-2.5 px-3 text-left">{{ __('Date') }}</th>
                                    <th class="py-2.5 px-3 text-left">{{ __('Photo') }}</th>
                                    <th class="py-2.5 px-3 text-left">{{ __('Project / Site') }}</th>
                                    <th class="py-2.5 px-3 text-center">{{ __('Shift') }}</th>
                                    <th class="py-2.5 px-3 text-right">{{ __('Cabling') }}</th>
                                    <th class="py-2.5 px-3 text-right">{{ __('Cameras') }}</th>
                                    <th class="py-2.5 px-3 text-right">{{ __('Total Pay') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-[#0f172a]">
                                @foreach ($recentAttendances as $att)
                                    @php
                                        $attMultiplier = in_array($att->attendance_type, ['half', 'half_day']) ? 0.5 : 1.0;
                                        $attBaseWage = (float) $att->daily_rate_snapshot * $attMultiplier;
                                        $attCablingRate = (float) ($att->cabling_rate ?: 20.00);
                                        $attCablingAmount = (float) ($att->cabling_amount ?: ($att->cabling_metres * $attCablingRate));
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                                        <td class="py-2 px-3 font-semibold text-slate-900 dark:text-white whitespace-nowrap">
                                            {{ $att->workDay?->work_date ? $att->workDay->work_date->translatedFormat('d M Y') : __('N/A') }}
                                        </td>
                                        <td class="py-2 px-3 whitespace-nowrap">
                                            @if ($att->photo_path)
                                                <img src="{{ $att->photo_url }}" 
                                                     alt="{{ __('Attendance Photo') }}" 
                                                     @click="openPhotoPreview('{{ $att->photo_url }}')"
                                                     class="w-8 h-8 rounded-full object-cover border border-slate-300 dark:border-slate-700 cursor-pointer hover:border-blue-600 transition-colors">
                                            @else
                                                <span class="text-[11px] text-slate-500 dark:text-slate-400 italic">{{ __('No photo') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-3 text-slate-700 dark:text-slate-300 font-medium truncate max-w-[160px]">
                                            {{ $att->workDay?->project?->title ?? __('Site Work') }}
                                        </td>
                                        <td class="py-2 px-3 text-center whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ !in_array($att->attendance_type, ['half', 'half_day']) ? 'bg-blue-100 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300' : 'bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300' }}">
                                                {{ in_array($att->attendance_type, ['half', 'half_day']) ? __('Half Day') : __('Full Day') }}
                                            </span>
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 font-mono">&#8377;{{ number_format($attBaseWage, 0) }}</div>
                                        </td>
                                        <td class="py-2 px-3 text-right font-mono font-semibold text-slate-800 dark:text-slate-200">
                                            <div>{{ number_format($att->cabling_metres, 1) }}m</div>
                                            @if($attCablingAmount > 0)
                                                <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-normal">+&#8377;{{ number_format($attCablingAmount, 0) }} (@ &#8377;{{ number_format($attCablingRate, 2) }}/m)</div>
                                            @endif
                                        </td>
                                        <td class="py-2 px-3 text-right font-mono font-semibold text-slate-800 dark:text-slate-200">
                                            {{ $att->cameras_installed }}
                                        </td>
                                        <td class="py-2 px-3 text-right font-mono font-bold text-blue-700 dark:text-blue-400 whitespace-nowrap">
                                            <div class="text-sm">&#8377;{{ number_format($att->total_amount, 2) }}</div>
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-normal">
                                                &#8377;{{ number_format($attBaseWage, 0) }} {{ __('base') }} + &#8377;{{ number_format($attCablingAmount, 0) }} {{ __('cabling') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-6 text-center text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/40 rounded-[4px] border border-dashed border-slate-300 dark:border-slate-700">
                        <svg class="w-8 h-8 mx-auto mb-2 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ __('No attendance records found yet') }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Use the attendance tab to punch your first selfie check-in.') }}</p>
                    </div>
                @endif
            </div>
        </div>

    </main>

    {{-- MOBILE BOTTOM NAVIGATION BAR (< 768px) --}}
    <nav aria-label="{{ __('Worker Navigation') }}"
         class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white dark:bg-[#0f172a] border-t border-slate-200 dark:border-slate-800">
        <div class="grid grid-cols-4 h-16">
            <button type="button" @click="activeTab = 'home'; window.scrollTo({ top: 0, behavior: 'smooth' })"
                    class="flex flex-col items-center justify-center gap-0.5 text-[11px] font-bold transition-colors cursor-pointer"
                    :class="activeTab === 'home' ? 'text-[#65a30d]' : 'text-slate-500 dark:text-slate-400'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>{{ __('Home') }}</span>
            </button>
            <button type="button" @click="activeTab = 'attendance'; window.scrollTo({ top: 0, behavior: 'smooth' })"
                    class="flex flex-col items-center justify-center gap-0.5 text-[11px] font-bold transition-colors cursor-pointer"
                    :class="activeTab === 'attendance' ? 'text-[#65a30d]' : 'text-slate-500 dark:text-slate-400'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>{{ __('Attendance') }}</span>
            </button>
            <button type="button" @click="activeTab = 'work'; window.scrollTo({ top: 0, behavior: 'smooth' })"
                    class="flex flex-col items-center justify-center gap-0.5 text-[11px] font-bold transition-colors cursor-pointer"
                    :class="activeTab === 'work' ? 'text-[#65a30d]' : 'text-slate-500 dark:text-slate-400'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>{{ __('Work Log') }}</span>
            </button>
            <button type="button" @click="activeTab = 'history'; window.scrollTo({ top: 0, behavior: 'smooth' })"
                    class="flex flex-col items-center justify-center gap-0.5 text-[11px] font-bold transition-colors cursor-pointer"
                    :class="activeTab === 'history' ? 'text-[#65a30d]' : 'text-slate-500 dark:text-slate-400'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ __('Pay & History') }}</span>
            </button>
        </div>
    </nav>

    {{-- PHOTO PREVIEW MODAL --}}
    <div x-show="modalPhotoUrl" 
         x-cloak 
         class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4"
         @click.self="modalPhotoUrl = null">
        <div class="relative bg-white dark:bg-slate-900 rounded-[4px] overflow-hidden max-w-md w-full border border-slate-300 dark:border-slate-700 shadow-2xl">
            <div class="p-3 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between">
                <span class="text-xs font-bold">{{ __('Verified Attendance Selfie Photo') }}</span>
                <x-close-button size="sm" tone="dark" label="{{ __('Close photo') }}" @click="modalPhotoUrl = null" />
            </div>
            <div class="p-2 bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                <img :src="modalPhotoUrl" alt="{{ __('Attendance Photo') }}" class="max-h-[70vh] w-auto object-contain rounded-[4px]">
            </div>
            <div class="p-3 text-right bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
                <button type="button" @click="modalPhotoUrl = null" class="btn-md px-4 py-1.5 bg-slate-800 dark:bg-slate-700 text-white text-xs font-bold rounded-[4px] hover:bg-slate-700 dark:hover:bg-slate-600">{{ __('Close') }}</button>
            </div>
        </div>
    </div>

    {{-- ALPINE SCRIPT FOR CAMERA & PORTAL --}}
    <script>
        function workerPortalData() {
            return {
                isDarkMode: document.documentElement.classList.contains('dark'),
                isDesktop: window.innerWidth >= 768,
                activeTab: window.innerWidth < 768 ? 'home' : 'attendance',
                submittingPunch: false,
                submittingWork: false,
                attendanceType: '{{ $todayAttendance?->attendance_type ?? "full_day" }}',
                selectedProjectId: '{{ $todayAttendance?->workDay?->project_id ?? ($activeProjects->first()?->id ?? "") }}',
                projectRates: {{ \Illuminate\Support\Js::from($projectRates ?? []) }},
                cablingMetres: 0,
                camerasInstalled: 0,
                cameraActive: false,
                capturedPhotoData: '',
                previewUrl: null,
                hasExistingPhoto: {{ ($todayAttendance && $todayAttendance->photo_path) ? 'true' : 'false' }},
                modalPhotoUrl: null,
                mediaStream: null,

                getProjectRate() {
                    if (this.selectedProjectId && this.projectRates && this.projectRates[this.selectedProjectId]) {
                        return parseFloat(this.projectRates[this.selectedProjectId]);
                    }
                    return 20.00;
                },

                init() {
                    // Sync Alpine state if dark class toggled
                    const observer = new MutationObserver(() => {
                        this.isDarkMode = document.documentElement.classList.contains('dark');
                    });
                    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

                    window.addEventListener('resize', () => {
                        this.isDesktop = window.innerWidth >= 768;
                        if (this.isDesktop && this.activeTab === 'home') {
                            this.activeTab = 'attendance';
                        }
                    });
                },

                toggleTheme() {
                    if (typeof window.crmToggleTheme === 'function') {
                        this.isDarkMode = window.crmToggleTheme();
                    } else if (typeof window.applyCrmMode === 'function') {
                        this.isDarkMode = window.applyCrmMode(this.isDarkMode ? 'day' : 'night', true);
                    } else {
                        const isDark = document.documentElement.classList.toggle('dark');
                        this.isDarkMode = isDark;
                    }
                },

                addCabling(m) {
                    this.cablingMetres = (parseFloat(this.cablingMetres) || 0) + m;
                },

                stepCabling(delta) {
                    this.cablingMetres = Math.max(0, (parseFloat(this.cablingMetres) || 0) + delta);
                },

                addCameras(c) {
                    this.camerasInstalled = (parseInt(this.camerasInstalled) || 0) + c;
                },

                stepCameras(delta) {
                    this.camerasInstalled = Math.max(0, (parseInt(this.camerasInstalled) || 0) + delta);
                },

                openPhotoPreview(url) {
                    this.modalPhotoUrl = url;
                },

                async startCamera() {
                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        try {
                            this.cameraActive = true;
                            this.mediaStream = await navigator.mediaDevices.getUserMedia({ 
                                video: { 
                                    facingMode: 'user', // selfie camera preferred
                                    width: { ideal: 640 },
                                    height: { ideal: 480 }
                                },
                                audio: false 
                            });
                            this.$refs.videoElement.srcObject = this.mediaStream;
                        } catch (err) {
                            console.error('Camera error: ', err);
                            crmAlert({{ Js::from(__('Unable to access camera: :error. Please use the Choose File / Phone Gallery button below.')) }}.replace(':error', err.message));
                            this.cameraActive = false;
                        }
                    } else {
                        crmAlert({{ Js::from(__('Your browser does not support live camera capture. Please choose a photo using the file upload button.')) }});
                    }
                },

                snapPhoto() {
                    if (!this.cameraActive || !this.$refs.videoElement) return;

                    const video = this.$refs.videoElement;
                    const canvas = this.$refs.canvasElement;
                    canvas.width = video.videoWidth || 640;
                    canvas.height = video.videoHeight || 480;

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                    this.capturedPhotoData = dataUrl;
                    this.previewUrl = dataUrl;

                    this.stopCamera();
                },

                stopCamera() {
                    if (this.mediaStream) {
                        this.mediaStream.getTracks().forEach(track => track.stop());
                        this.mediaStream = null;
                    }
                    this.cameraActive = false;
                },

                handleFileUpload(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.stopCamera();
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.previewUrl = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                }
            };
        }
    </script>

    {{-- Progressive Web App (PWA) Offline & Install Handler --}}
    <x-pwa-installer />
</body>
</html>
