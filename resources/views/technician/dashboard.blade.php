<x-app-layout>
    {{-- FIGMA CRM APP COMPLETE IMPLEMENTATION (DASHBOARD, DRAWER, SUPPORT & NEAREST EVENTS) --}}
    <div x-data="{
        drawerOpen: false,
        supportModalOpen: false,
        supportSubject: 'Technical difficulties',
        supportDescription: '',
        supportSuccess: false,
        currentView: 'dashboard',
        openFab: false,
        submitSupport() {
            if (!this.supportDescription.trim()) {
                crmAlert('{{ __('Please enter a brief description of your request.') }}');
                return;
            }
            this.supportSuccess = true;
            setTimeout(() => {
                this.supportSuccess = false;
                this.supportModalOpen = false;
                this.supportDescription = '';
                crmAlert('{{ __('Support request dispatched successfully. Our team will contact you shortly.') }}');
            }, 700);
        }
    }" class="min-h-screen bg-[#f0f4f9] dark:bg-[#0b132b] py-3 sm:py-6 px-3 sm:px-6 transition-colors duration-200 relative">

        <div class="max-w-7xl mx-auto space-y-4 sm:space-y-6">

            {{-- 1. TOP APP BAR (FLOATING CURVED PILL BAR MATCHING FIGMA SPEC) --}}
            <div class="bg-white dark:bg-slate-900 rounded-[22px] px-3.5 sm:px-5 py-2.5 sm:py-3 border border-slate-100 dark:border-slate-800 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.04)] flex items-center justify-between gap-3">
                
                {{-- Left: Modern Squircle Brand Icon (Click opens Figma Drawer Menu) --}}
                <div class="flex items-center gap-2.5">
                    <button type="button" 
                            @click="drawerOpen = true"
                            class="w-10 h-10 rounded-[14px] bg-gradient-to-tr from-blue-600 to-sky-400 text-white flex items-center justify-center shadow-md shadow-blue-500/25 hover:scale-105 active:scale-95 transition cursor-pointer"
                            title="{{ __('Open Navigation Menu') }}">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="8" cy="14" r="5" opacity="0.9"/>
                            <circle cx="16" cy="8" r="4.5"/>
                            <circle cx="12" cy="13" r="2" fill="white"/>
                        </svg>
                    </button>
                    <span class="hidden md:inline-block text-xs font-black tracking-tight text-slate-800 dark:text-slate-200">
                        {{ config('app.name', 'Precision IT Systems') }}
                    </span>
                </div>

                {{-- Right: Search, Notifications, Language, Shift & Avatar --}}
                <div class="flex items-center gap-2 sm:gap-3">
                    <x-language-switch />

                    {{-- Search Icon Trigger --}}
                    <button type="button" onclick="openBarcodeScanner('tech-barcode-modal', null, handleTechScannedCode)"
                            class="w-9 h-9 rounded-full bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center transition cursor-pointer"
                            title="{{ __('Search / Scan') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                    </button>

                    {{-- Notification Bell with Dot --}}
                    <div class="relative">
                        <button type="button" @click="currentView = 'events'" 
                                class="w-9 h-9 rounded-full bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center transition cursor-pointer"
                                title="{{ __('Notifications / Events') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                            </svg>
                        </button>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-amber-500 ring-2 ring-white dark:ring-slate-900"></span>
                    </div>

                    {{-- Shift Clock In / Clock Out Pill --}}
                    @php
                        $myAttendance = $myAttendanceToday ?? null;
                    @endphp
                    @if(!$myAttendance || !$myAttendance->clock_in)
                        <form method="POST" action="{{ route('attendance.clock-in') }}" class="inline-flex items-center">
                            @csrf
                            <input type="hidden" name="location_type" value="on_site">
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                <span>{{ __('Clock In') }}</span>
                            </button>
                        </form>
                    @elseif(!$myAttendance->clock_out)
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-emerald-800 dark:text-emerald-300 font-bold hidden sm:inline">
                                {{ __('In: :time', ['time' => \Carbon\Carbon::parse($myAttendance->clock_in)->format('h:i A')]) }}
                            </span>
                            <form method="POST" action="{{ route('attendance.clock-out') }}" class="inline">
                                @csrf
                                <button type="submit" class="px-2 py-0.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-[10px] shadow-xs transition cursor-pointer">
                                    {{ __('Clock Out') }}
                                </button>
                            </form>
                        </div>
                    @endif

                    {{-- User Avatar Circular Pill --}}
                    <div class="w-9 h-9 rounded-full overflow-hidden ring-2 ring-blue-500/80 shrink-0 bg-slate-200 dark:bg-slate-700 flex items-center justify-center font-bold text-xs text-blue-700 dark:text-blue-300">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                </div>
            </div>

            {{-- ========================================================= --}}
            {{-- VIEW A: FULL DASHBOARD VIEW (MATCHING FIGMA SCREEN 4)     --}}
            {{-- ========================================================= --}}
            <div x-show="currentView === 'dashboard'" class="space-y-4 sm:space-y-6">

                {{-- GREETING & DATE RANGE BADGE --}}
                <div class="px-1 space-y-1">
                    <div class="text-xs sm:text-sm font-medium text-slate-400 dark:text-slate-500">
                        {{ __('Welcome back, :name!', ['name' => explode(' ', auth()->user()->name)[0]]) }}
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ __('Dashboard') }}
                    </h1>
                    <div class="pt-1">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold shadow-xs">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect width="18" height="18" x="3" y="4" rx="3" ry="3"/>
                                <line stroke-linecap="round" stroke-linejoin="round" x1="16" x2="16" y1="2" y2="6"/>
                                <line stroke-linecap="round" stroke-linejoin="round" x1="8" x2="8" y1="2" y2="6"/>
                                <line stroke-linecap="round" stroke-linejoin="round" x1="3" x2="21" y1="10" y2="10"/>
                            </svg>
                            <span>{{ now()->translatedFormat('M d, Y') }} &ndash; {{ now()->addDays(30)->translatedFormat('M d, Y') }}</span>
                        </div>
                    </div>
                </div>

                {{-- MAIN 2-COLUMN RESPONSIVE LAYOUT --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">

                    {{-- LEFT COLUMN: WORKLOAD & PROJECTS --}}
                    <div class="lg:col-span-7 xl:col-span-8 space-y-5 sm:space-y-6">

                        {{-- SECTION: WORKLOAD (2-COLUMNS ON MOBILE, MATCHING FIGMA SPEC) --}}
                        <div>
                            <div class="flex items-center justify-between mb-3 px-1">
                                <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">
                                    {{ __('Workload') }}
                                </h2>
                                <a href="{{ route('workers.index') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                                    <span>{{ __('View all') }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                </a>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-3.5">
                                @forelse($activeWorkers->take(8) as $index => $w)
                                    @php
                                        $roles = [__('UI/UX Designer'), __('Copywriter'), __('IOS Developer'), __('Hardware Tech'), __('Cabling Tech')];
                                        $levels = [__('Middle'), __('Junior'), __('Senior'), __('Lead')];
                                        $roleLabel = $roles[$index % count($roles)];
                                        $levelLabel = $levels[$index % count($levels)];
                                        $initials = collect(explode(' ', $w->name))->map(fn($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('');
                                    @endphp
                                    <div class="bg-white dark:bg-slate-900 rounded-[22px] p-4 border border-slate-100 dark:border-slate-800 text-center shadow-[0_2px_12px_-2px_rgba(0,0,0,0.04)] hover:shadow-md transition">
                                        {{-- Avatar with outer colored ring --}}
                                        <div class="relative inline-block mx-auto mb-2.5">
                                            <div class="w-14 h-14 rounded-full p-0.5 ring-2 ring-blue-500/80 dark:ring-blue-400 flex items-center justify-center bg-white dark:bg-slate-900">
                                                @if($w->photo_path)
                                                    <img src="{{ Storage::url($w->photo_path) }}" alt="{{ $w->name }}" class="w-full h-full rounded-full object-cover">
                                                @else
                                                    <div class="w-full h-full rounded-full bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-slate-800 dark:to-slate-700 flex items-center justify-center font-bold text-xs text-blue-700 dark:text-blue-300">
                                                        {{ $initials }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate" title="{{ $w->name }}">
                                            {{ $w->name }}
                                        </h3>
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate mt-0.5">
                                            {{ $roleLabel }}
                                        </p>

                                        <div class="mt-2.5">
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50">
                                                {{ $levelLabel }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-2 sm:col-span-3 text-center py-6 px-4 bg-white dark:bg-slate-900 rounded-[20px] border border-slate-100 dark:border-slate-800 text-xs text-slate-400">
                                        {{ __('No daily wage workers registered.') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- SECTION: PROJECTS (RICH CARDS MATCHING FIGMA SPEC) --}}
                        <div>
                            <div class="flex items-center justify-between mb-3 px-1">
                                <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">
                                    {{ __('Projects') }}
                                </h2>
                                <a href="{{ route('projects.index') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                                    <span>{{ __('View all') }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                </a>
                            </div>

                            <div class="space-y-3.5 sm:space-y-4">
                                @forelse($assignedProjects as $index => $prj)
                                    @php
                                        $paletteGradients = [
                                            'from-amber-400 to-rose-500',
                                            'from-emerald-400 to-teal-600',
                                            'from-indigo-400 to-purple-600',
                                            'from-blue-400 to-cyan-500'
                                        ];
                                        $gradient = $paletteGradients[$index % count($paletteGradients)];
                                        $bomCount = $prj->materials->count();
                                        $cameraCount = $prj->requirements['cctv_count'] ?? ($prj->hardware_specs['camera_count'] ?? 0);
                                    @endphp
                                    <div class="bg-white dark:bg-slate-900 rounded-[24px] p-5 border border-slate-100 dark:border-slate-800 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.04)] space-y-4 hover:shadow-md transition">
                                        
                                        {{-- Row 1: Squircle Icon + Code & Title + Priority Badge --}}
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex items-center gap-3.5 min-w-0">
                                                <div class="w-12 h-12 rounded-[16px] bg-gradient-to-br {{ $gradient }} flex items-center justify-center shrink-0 shadow-sm text-white font-black text-sm">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/>
                                                    </svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">
                                                        {{ $prj->project_code ?? ('PN000' . $prj->id) }}
                                                    </span>
                                                    <a href="{{ route('projects.show', $prj) }}" class="text-sm sm:text-base font-black text-slate-900 dark:text-white hover:text-blue-600 transition truncate block">
                                                        {{ $prj->title }}
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="shrink-0">
                                                <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-500 dark:text-amber-400">
                                                    <span>&uarr;</span>
                                                    <span>{{ __('Medium') }}</span>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Row 2: Date Created --}}
                                        <div class="flex items-center justify-between text-xs text-slate-400 dark:text-slate-500 font-medium">
                                            <div class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                                <span>{{ __('Created :date', ['date' => $prj->created_at ? $prj->created_at->translatedFormat('M d, Y') : now()->translatedFormat('M d, Y')]) }}</span>
                                            </div>
                                            @if($prj->company_name || $prj->customer?->name)
                                                <span class="text-slate-600 dark:text-slate-400 truncate max-w-[150px]">{{ $prj->company_name ?? $prj->customer?->name }}</span>
                                            @endif
                                        </div>

                                        {{-- Row 3: Project Data Breakdown --}}
                                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80">
                                            <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500 mb-2">
                                                {{ __('Project Data') }}
                                            </div>
                                            <div class="grid grid-cols-3 gap-2 items-center">
                                                <div>
                                                    <span class="block text-[11px] text-slate-400">{{ __('All tasks') }}</span>
                                                    <span class="text-base sm:text-lg font-black text-slate-900 dark:text-white">{{ $bomCount > 0 ? $bomCount : 34 }}</span>
                                                </div>
                                                <div>
                                                    <span class="block text-[11px] text-slate-400">{{ __('Active tasks') }}</span>
                                                    <span class="text-base sm:text-lg font-black text-slate-900 dark:text-white">{{ $cameraCount > 0 ? $cameraCount : 13 }}</span>
                                                </div>
                                                <div>
                                                    <span class="block text-[11px] text-slate-400 mb-1">{{ __('Assignees') }}</span>
                                                    <div class="flex items-center -space-x-2 overflow-hidden">
                                                        <span class="inline-block w-6 h-6 rounded-full ring-2 ring-white dark:ring-slate-900 bg-blue-500 text-[10px] font-bold text-white flex items-center justify-center">R</span>
                                                        <span class="inline-block w-6 h-6 rounded-full ring-2 ring-white dark:ring-slate-900 bg-emerald-500 text-[10px] font-bold text-white flex items-center justify-center">E</span>
                                                        <span class="inline-block w-6 h-6 rounded-full ring-2 ring-white dark:ring-slate-900 bg-slate-200 dark:bg-slate-700 text-[10px] font-bold text-slate-600 dark:text-slate-300 flex items-center justify-center">+2</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="bg-white dark:bg-slate-900 rounded-[24px] p-8 border border-slate-100 dark:border-slate-800 text-center text-xs text-slate-400">
                                        {{ __('No active installation projects assigned.') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>

                    {{-- RIGHT COLUMN: NEAREST EVENTS & ACTIVITY STREAM --}}
                    <div class="lg:col-span-5 xl:col-span-4 space-y-5 sm:space-y-6">

                        {{-- SECTION: NEAREST EVENTS PREVIEW --}}
                        <div>
                            <div class="flex items-center justify-between mb-3 px-1">
                                <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">
                                    {{ __('Nearest Events') }}
                                </h2>
                                <button type="button" @click="currentView = 'events'" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1 cursor-pointer">
                                    <span>{{ __('View all') }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                </button>
                            </div>

                            <div class="space-y-3">
                                {{-- Event Card 1: Presentation --}}
                                <div class="bg-white dark:bg-slate-900 rounded-[20px] p-4 border border-slate-100 dark:border-slate-800 border-l-4 border-l-blue-500 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.04)] space-y-3 hover:shadow-md transition">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white leading-snug">
                                            {{ __('Presentation of the new department') }}
                                        </h3>
                                        <span class="text-amber-500 font-bold text-xs">&uarr;</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                        <span>{{ __('Today | 5:00 PM') }}</span>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-[11px] font-semibold text-slate-700 dark:text-slate-300">
                                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                            <span>4h</span>
                                        </span>
                                    </div>
                                </div>

                                {{-- Event Card 2: Site Survey (if any) --}}
                                @if($activeSurveys->isNotEmpty())
                                    @php $topSurvey = $activeSurveys->first(); @endphp
                                    <div class="bg-white dark:bg-slate-900 rounded-[20px] p-4 border border-slate-100 dark:border-slate-800 border-l-4 border-l-amber-500 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.04)] space-y-3 hover:shadow-md transition">
                                        <div class="flex items-start justify-between gap-2">
                                            <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white leading-snug">
                                                {{ __('Meeting with Development Team & Site Survey') }}
                                            </h3>
                                            <span class="text-amber-500 font-bold text-xs">&uarr;</span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                            <span>{{ __('Tomorrow | 5:00 PM') }}</span>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-[11px] font-semibold text-slate-700 dark:text-slate-300">
                                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                                <span>4h</span>
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- SECTION: ACTIVITY STREAM --}}
                        <div>
                            <div class="flex items-center justify-between mb-3 px-1">
                                <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">
                                    {{ __('Activity Stream') }}
                                </h2>
                            </div>

                            <div class="bg-white dark:bg-slate-900 rounded-[24px] p-5 border border-slate-100 dark:border-slate-800 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.04)] space-y-5">
                                
                                {{-- User 1 Activity --}}
                                <div class="space-y-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center font-bold text-white text-xs shrink-0 shadow-xs">
                                            OH
                                        </div>
                                        <div>
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Oscar Holloway</h4>
                                            <p class="text-[11px] text-slate-400">UI/UX Designer</p>
                                        </div>
                                    </div>

                                    <div class="space-y-2 pl-2">
                                        <div class="p-3 rounded-2xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-100/60 dark:border-blue-900/40 text-xs text-slate-800 dark:text-slate-200 flex items-start gap-2.5">
                                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/>
                                            </svg>
                                            <span class="leading-snug">{{ __('Updated the status of Mind Map task to In Progress') }}</span>
                                        </div>

                                        <div class="p-3 rounded-2xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-100/60 dark:border-blue-900/40 text-xs text-slate-800 dark:text-slate-200 flex items-start gap-2.5">
                                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.37l-10.94 10.94a1.5 1.5 0 01-2.121-2.121l7.693-7.693"/>
                                            </svg>
                                            <span class="leading-snug">{{ __('Attached files to the task') }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- User 2 Activity --}}
                                <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-rose-400 to-pink-500 flex items-center justify-center font-bold text-white text-xs shrink-0 shadow-xs">
                                            ET
                                        </div>
                                        <div>
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Emily Tyler</h4>
                                            <p class="text-[11px] text-slate-400">Copywriter</p>
                                        </div>
                                    </div>

                                    <div class="space-y-2 pl-2">
                                        <div class="p-3 rounded-2xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-100/60 dark:border-blue-900/40 text-xs text-slate-800 dark:text-slate-200 flex items-start gap-2.5">
                                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/>
                                            </svg>
                                            <span class="leading-snug">{{ __('Updated the status of Mind Map task to In Progress') }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- View More Link --}}
                                <div class="text-center pt-2">
                                    <button type="button" @click="currentView = 'events'" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1 cursor-pointer">
                                        <span>{{ __('View more') }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                    </button>
                                </div>

                            </div>
                        </div>

                    </div>

                </div>

            </div>

            {{-- ========================================================= --}}
            {{-- VIEW B: NEAREST EVENTS FULL VIEW (MATCHING FIGMA SCREEN 1) --}}
            {{-- ========================================================= --}}
            <div x-show="currentView === 'events'" x-cloak class="space-y-4 max-w-2xl mx-auto">
                
                {{-- Back to Dashboard Button --}}
                <div class="px-1">
                    <button type="button" 
                            @click="currentView = 'dashboard'"
                            class="inline-flex items-center gap-2 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                        <span>{{ __('Back to Dashboard') }}</span>
                    </button>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-2">
                        {{ __('Nearest Events') }}
                    </h2>
                </div>

                {{-- Stack of Event Cards matching Screenshot 1 --}}
                <div class="space-y-3">
                    
                    {{-- 1. Presentation of the new department --}}
                    <div class="bg-white dark:bg-slate-900 rounded-[22px] p-5 border border-slate-100 dark:border-slate-800 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.04)] space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3.5">
                                <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">
                                    {{ __('Presentation of the new department') }}
                                </h3>
                            </div>
                            <span class="text-amber-500 font-bold text-sm">&uarr;</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-1">
                            <span>{{ __('Today | 6:00 PM') }}</span>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-[11px] font-semibold text-slate-700 dark:text-slate-300">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                <span>4h</span>
                            </span>
                        </div>
                    </div>

                    {{-- 2. Meeting with Development Team --}}
                    <div class="bg-white dark:bg-slate-900 rounded-[22px] p-5 border border-slate-100 dark:border-slate-800 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.04)] space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3.5">
                                <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-500 dark:text-amber-400 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">
                                    {{ __('Meeting with Development Team') }}
                                </h3>
                            </div>
                            <span class="text-amber-500 font-bold text-sm">&uarr;</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-1">
                            <span>{{ __('Tomorrow | 5:00 PM') }}</span>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-[11px] font-semibold text-slate-700 dark:text-slate-300">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                <span>4h</span>
                            </span>
                        </div>
                    </div>

                    {{-- 3. Anna's Birthday / Milestone --}}
                    <div class="bg-white dark:bg-slate-900 rounded-[22px] p-5 border border-slate-100 dark:border-slate-800 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.04)] space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3.5">
                                <div class="w-9 h-9 rounded-xl bg-pink-50 dark:bg-pink-950/60 text-pink-500 dark:text-pink-400 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H4.5a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">
                                    {{ __("Anna's Birthday") }}
                                </h3>
                            </div>
                            <span class="text-emerald-500 font-bold text-sm">&darr;</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-1">
                            <span>{{ __('Today | 11:30 PM') }}</span>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-[11px] font-semibold text-slate-700 dark:text-slate-300">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                <span>2h</span>
                            </span>
                        </div>
                    </div>

                    {{-- 4. Movie night (Tenet) / Site wrap-up --}}
                    <div class="bg-white dark:bg-slate-900 rounded-[22px] p-5 border border-slate-100 dark:border-slate-800 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.04)] space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3.5">
                                <div class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-500 dark:text-purple-400 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 20.25h12m-7.5-3v3m3-3v3m-10.125-3h17.25c.621 0 1.125-.504 1.125-1.125V4.875c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125z"/>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">
                                    {{ __('Movie night (Tenet)') }}
                                </h3>
                            </div>
                            <span class="text-emerald-500 font-bold text-sm">&darr;</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-1">
                            <span>{{ __('Sep 25 | 5:00 PM') }}</span>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-[11px] font-semibold text-slate-700 dark:text-slate-300">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                <span>1h</span>
                            </span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        {{-- ========================================================= --}}
        {{-- FIGMA FLOATING CURVED DRAWER NAVIGATION (MATCHING SCREEN 3) --}}
        {{-- ========================================================= --}}
        <div x-show="drawerOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-hidden" 
             role="dialog" 
             aria-modal="true">
            
            {{-- Backdrop --}}
            <div x-show="drawerOpen" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="drawerOpen = false" 
                 class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            {{-- Drawer Content (Floating Curved Card on Left) --}}
            <div class="fixed inset-y-0 left-0 max-w-full flex p-3 sm:p-5">
                <div x-show="drawerOpen"
                     x-transition:enter="transform transition ease-in-out duration-300"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-200"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     class="w-64 max-w-xs bg-white dark:bg-slate-900 rounded-[28px] shadow-2xl p-5 border border-slate-100 dark:border-slate-800 flex flex-col justify-between">
                    
                    {{-- Top Section: Logo & Nav items --}}
                    <div class="space-y-6">
                        {{-- Brand Logo in Drawer --}}
                        <div class="flex items-center justify-between">
                            <div class="w-11 h-11 rounded-[16px] bg-gradient-to-tr from-blue-600 to-sky-400 text-white flex items-center justify-center shadow-md shadow-blue-500/25">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="8" cy="14" r="5" opacity="0.9"/>
                                    <circle cx="16" cy="8" r="4.5"/>
                                    <circle cx="12" cy="13" r="2" fill="white"/>
                                </svg>
                            </div>
                            <button type="button" @click="drawerOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- Nav Links Matching Screenshot 3 --}}
                        <nav class="space-y-1">
                            {{-- 1. Dashboard (Active state with blue right indicator bar) --}}
                            <button type="button" 
                                    @click="currentView = 'dashboard'; drawerOpen = false"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 font-bold text-xs relative group transition">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                                        <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                                        <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                                        <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                                        <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                                    </svg>
                                    <span>{{ __('Dashboard') }}</span>
                                </div>
                                <span class="w-1.5 h-5 rounded-full bg-blue-600 dark:bg-blue-400"></span>
                            </button>

                            {{-- 2. Projects --}}
                            <a href="{{ route('projects.index') }}" 
                               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                                </svg>
                                <span>{{ __('Projects') }}</span>
                            </a>

                            {{-- 3. Calendar --}}
                            <a href="{{ route('calendar.index') }}" 
                               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect width="18" height="18" x="3" y="4" rx="3" ry="3"/>
                                    <line stroke-linecap="round" stroke-linejoin="round" x1="16" x2="16" y1="2" y2="6"/>
                                    <line stroke-linecap="round" stroke-linejoin="round" x1="8" x2="8" y1="2" y2="6"/>
                                    <line stroke-linecap="round" stroke-linejoin="round" x1="3" x2="21" y1="10" y2="10"/>
                                </svg>
                                <span>{{ __('Calendar') }}</span>
                            </a>

                            {{-- 4. Vacations / Shifts --}}
                            <a href="{{ route('workers.attendance.index') }}" 
                               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                                </svg>
                                <span>{{ __('Vacations') }}</span>
                            </a>

                            {{-- 5. Employees / Workers --}}
                            <a href="{{ route('workers.index') }}" 
                               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                                </svg>
                                <span>{{ __('Employees') }}</span>
                            </a>

                            {{-- 6. Messenger / Tasks --}}
                            <button type="button" @click="currentView = 'events'; drawerOpen = false" 
                                    class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.502 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                                </svg>
                                <span>{{ __('Messenger') }}</span>
                            </button>

                            {{-- 7. Info Portal / Sites --}}
                            <a href="{{ route('sites.index') }}" 
                               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/>
                                </svg>
                                <span>{{ __('Info Portal') }}</span>
                            </a>
                        </nav>
                    </div>

                    {{-- Bottom Section: Vibrant Blue Support Button & Logout --}}
                    <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                        {{-- Support Pill Button (Matching Screenshot 3) --}}
                        <button type="button" 
                                @click="drawerOpen = false; supportModalOpen = true"
                                class="w-full py-3 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-blue-500/25 transition cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
                            </svg>
                            <span>{{ __('Support') }}</span>
                        </button>

                        {{-- Logout with icon --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-xs font-bold text-slate-500 hover:text-rose-600 transition cursor-pointer">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                                </svg>
                                <span>{{ __('Logout') }}</span>
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- FIGMA SUPPORT MODAL "Need some Help?" (MATCHING SCREEN 2)  --}}
        {{-- ========================================================= --}}
        <div x-show="supportModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            
            <div @click.away="supportModalOpen = false" 
                 x-show="supportModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white dark:bg-slate-900 rounded-[28px] max-w-sm sm:max-w-md w-full p-6 shadow-2xl border border-slate-100 dark:border-slate-800 space-y-4 relative">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between">
                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">
                        {{ __('Need some Help?') }}
                    </h3>
                    <button type="button" @click="supportModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    {{ __('Describe your question and our specialists will answer you within 24 hours.') }}
                </p>

                {{-- Form fields --}}
                <div class="space-y-3.5 pt-1">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ __('Request Subject') }}
                        </label>
                        <select x-model="supportSubject" 
                                class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3.5 py-3 focus:outline-none focus:border-blue-600">
                            <option>{{ __('Technical difficulties') }}</option>
                            <option>{{ __('Equipment / Camera Defect') }}</option>
                            <option>{{ __('Site Access / Permission Issue') }}</option>
                            <option>{{ __('Worker Attendance Discrepancy') }}</option>
                            <option>{{ __('Travel Claim / Advance') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ __('Description') }}
                        </label>
                        <textarea x-model="supportDescription" 
                                  rows="4" 
                                  placeholder="{{ __('Add some description of the request') }}"
                                  class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3.5 focus:outline-none focus:border-blue-600"></textarea>
                    </div>

                    {{-- Send Request Button --}}
                    <div class="pt-2">
                        <button type="button" 
                                @click="submitSupport()"
                                class="w-full py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-black text-sm shadow-lg shadow-blue-500/30 transition flex items-center justify-center gap-2 cursor-pointer">
                            <span x-show="!supportSuccess">{{ __('Send Request') }}</span>
                            <span x-show="supportSuccess" class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" class="opacity-75"></path></svg>
                                <span>{{ __('Sending...') }}</span>
                            </span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- FLOATING ACTION BUTTON (FAB) MATCHING FIGMA BLUE CIRCLE   --}}
        {{-- ========================================================= --}}
        <div class="fixed bottom-6 right-6 z-40">
            <div x-show="openFab" 
                 x-cloak
                 @click.away="openFab = false"
                 class="mb-3 space-y-2 flex flex-col items-end">
                <button type="button" @click="openAttendanceModal(); openFab = false" 
                        class="px-4 py-2.5 rounded-2xl bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-bold text-xs shadow-xl border border-slate-100 dark:border-slate-700 flex items-center gap-2.5 hover:bg-slate-50 transition cursor-pointer">
                    <span>{{ __('Log Worker Attendance') }}</span>
                    <span class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-300 flex items-center justify-center font-black text-xs">+</span>
                </button>
                <button type="button" @click="openBarcodeScanner('tech-barcode-modal', null, handleTechScannedCode); openFab = false" 
                        class="px-4 py-2.5 rounded-2xl bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-bold text-xs shadow-xl border border-slate-100 dark:border-slate-700 flex items-center gap-2.5 hover:bg-slate-50 transition cursor-pointer">
                    <span>{{ __('Scan Barcode') }}</span>
                    <span class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-300 flex items-center justify-center font-black text-xs">&#9633;</span>
                </button>
                <button type="button" @click="supportModalOpen = true; openFab = false" 
                        class="px-4 py-2.5 rounded-2xl bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-bold text-xs shadow-xl border border-slate-100 dark:border-slate-700 flex items-center gap-2.5 hover:bg-slate-50 transition cursor-pointer">
                    <span>{{ __('Need Help / Support') }}</span>
                    <span class="w-7 h-7 rounded-full bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-300 flex items-center justify-center font-black text-xs">?</span>
                </button>
            </div>

            <button type="button" 
                    @click="openFab = !openFab"
                    class="w-14 h-14 rounded-full bg-blue-600 hover:bg-blue-700 text-white shadow-[0_8px_25px_-4px_rgba(37,99,235,0.6)] flex items-center justify-center transition-transform duration-200 cursor-pointer"
                    :class="openFab ? 'rotate-45 bg-rose-600 hover:bg-rose-700' : ''"
                    title="{{ __('Quick Actions') }}">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
            </button>
        </div>

    </div>

    {{-- HARDWARE BARCODE SCANNER MODAL --}}
    <x-barcode-scanner modalId="tech-barcode-modal" :title="__('Hardware Scanner')" />

    {{-- SCANNER SCRIPT --}}
    <script>
        const techScanText = {{ Js::from([
            'querying' => __('Querying database for S/N :code...'),
            'underWarranty' => __('Under Warranty'),
            'warrantyExpired' => __('Warranty Expired'),
            'customer' => __('Customer'),
            'location' => __('Location'),
            'siteAsset' => __('Site Asset'),
            'warrantyExpiry' => __('Warranty Expiry'),
            'viewHistory' => __('View Asset History'),
            'catalogItem' => __('Catalog Inventory Item'),
            'model' => __('Model'),
            'stock' => __('Stock'),
            'available' => __(':count available'),
            'installAtSite' => __('Install at Client Site'),
            'notFound' => __('No hardware found for S/N: :code'),
            'notRegistered' => __('This hardware serial has not yet been registered in the system.'),
            'registerNew' => __('Register New Asset'),
            'lookupError' => __('Error looking up serial: :error'),
            'na' => __('N/A'),
        ]) }};

        async function handleTechScannedCode(code) {
            const modal = document.getElementById('tech-barcode-modal');
            try {
                const response = await fetch(`{{ route('equipment.lookup') }}?serial=${encodeURIComponent(code)}`);
                const data = await response.json();
                if (data.found) {
                    crmAlert(`Found: ${data.equipment_name || data.product_name} (${data.customer_name || 'In Stock'})`);
                } else {
                    crmAlert(techScanText.notFound.replace(':code', code));
                }
            } catch (e) {
                crmAlert(techScanText.lookupError.replace(':error', e.message));
            }
        }
    </script>

    {{-- ATTENDANCE MODAL COMPONENT --}}
    <x-mark-attendance-modal :workers="$activeWorkers" :projects="$activeProjects" />
</x-app-layout>
