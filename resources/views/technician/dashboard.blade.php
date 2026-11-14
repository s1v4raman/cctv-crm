<x-app-layout>
    {{-- TECHNICIAN FIELD WORKSTATION --}}
    {{-- Fully Responsive: Compact App View on Mobile + Expansive Enterprise Dashboard on Laptop --}}

    @php
        $currentUser = auth()->user();
        $myAttendance = $myAttendanceToday ?? \App\Models\EmployeeAttendance::where('user_id', $currentUser->id)->whereDate('date', now()->toDateString())->first();
        $primaryPrj = $primaryProject ?? null;
    @endphp

    <div x-data="{
        currentTab: 'overview', // 'overview', 'projects', 'jobs', 'surveys', 'workers', 'scanner'
        scanCodeInput: '',
        switchTab(t) {
            this.currentTab = t;
            this.$nextTick(() => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
                const main = document.getElementById('main-content');
                if (main && typeof main.scrollTo === 'function') {
                    main.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        }
    }" class="min-h-screen pb-24 lg:pb-10 text-slate-800 dark:text-slate-100">

        {{-- Main Container: Adaptive Max-Width (Expansive on Laptop, Contained on Mobile) --}}
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6 sm:space-y-8">

            {{-- Flash Messages --}}
            @if (session('status'))
                <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2.5 shadow-sm">
                    <svg class="w-4 h-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error') || (isset($errors) && $errors->any()))
                <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-semibold space-y-1 shadow-sm">
                    @if (session('error'))
                        <div>{{ session('error') }}</div>
                    @endif
                    @if (isset($errors))
                        @foreach ($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                    @endif
                </div>
            @endif

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- 1. WORKSTATION TOP HERO & ACTION STRIP (Laptop Wide + Mobile Stack)  --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <div :class="currentTab !== 'overview' ? 'hidden lg:block' : ''" class="rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 sm:p-6 lg:p-7 shadow-sm space-y-3.5 sm:space-y-4">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 sm:gap-4">
                    
                    {{-- User Identity & Subtitle --}}
                    <div>
                        <div class="inline-flex items-center gap-2 px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-[10px] sm:text-[11px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 mb-1.5 sm:mb-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                            <span>{{ __('Field Engineer Workstation') }}</span>
                        </div>
                        <h1 class="text-lg sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ __('Welcome, :name', ['name' => $currentUser->name]) }}
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1">
                            {{ now()->translatedFormat('l, d F Y') }} <span class="hidden sm:inline">&bull; {{ __('Precision Field Engineering & On-Site Operations') }}</span>
                        </p>
                    </div>

                    {{-- Shift Attendance & Language Switch --}}
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <x-language-switch />

                        {{-- Real Shift Clock In / Clock Out Form --}}
                        @if(!$myAttendance || !$myAttendance->clock_in)
                            <form method="POST" action="{{ route('attendance.clock-in') }}" class="inline-flex">
                                @csrf
                                <input type="hidden" name="location_type" value="on_site">
                                <button type="submit" 
                                        class="inline-flex items-center justify-center gap-2 px-4 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 !text-white font-bold text-xs sm:text-sm shadow-md shadow-blue-500/20 transition cursor-pointer"
                                        style="color: #ffffff !important;">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 !text-white" fill="none" stroke="#ffffff" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="!text-white font-bold" style="color: #ffffff !important;">{{ __('Clock In Today') }}</span>
                                </button>
                            </form>
                        @elseif(!$myAttendance->clock_out)
                            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 sm:px-4 sm:py-2 min-h-[38px] rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 text-xs sm:text-sm">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-emerald-800 dark:text-emerald-300 font-bold">
                                    {{ __('Shift: :time', ['time' => \Carbon\Carbon::parse($myAttendance->clock_in)->format('h:i A')]) }}
                                </span>
                                <form method="POST" action="{{ route('attendance.clock-out') }}" class="inline ml-1">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center px-3.5 py-1.5 min-h-[32px] rounded-lg bg-rose-600 hover:bg-rose-700 active:scale-95 !text-white font-bold text-[11px] sm:text-xs shadow-xs transition cursor-pointer"
                                            style="color: #ffffff !important;">
                                        <span class="!text-white font-bold" style="color: #ffffff !important;">{{ __('Clock Out') }}</span>
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="inline-flex items-center gap-2 px-4 py-2 min-h-[38px] rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm text-slate-700 dark:text-slate-300 font-bold">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ __('Shift Done (:hours hrs)', ['hours' => $myAttendance->total_hours]) }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Action Strip --}}
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2.5">
                    <div class="flex items-center gap-2 flex-wrap">
                        {{-- New Project (Standard Flow) --}}
                        <a href="{{ route('projects.create') }}" 
                           class="inline-flex items-center justify-center gap-2 px-4 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 !text-white font-bold text-xs sm:text-sm shadow-sm transition cursor-pointer"
                           style="color: #ffffff !important;">
                            <svg class="w-4 h-4 shrink-0 !text-white" fill="none" stroke="#ffffff" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            <span class="!text-white font-bold" style="color: #ffffff !important;">{{ __('New Project') }}</span>
                        </a>

                        {{-- Log Worker Attendance (Split Guided Modal) --}}
                        <button type="button" onclick="openAttendanceModal()"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 min-h-[38px] rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-bold text-xs sm:text-sm shadow-xs transition cursor-pointer">
                            <svg class="w-4 h-4 shrink-0 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                            <span>{{ __('Log Worker Attendance') }}</span>
                        </button>

                        {{-- Hardware Scanner (Opens Full Multi-Function Modal) --}}
                        <button type="button" onclick="openHardwareScannerModal()"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 min-h-[38px] rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-bold text-xs sm:text-sm shadow-xs transition cursor-pointer">
                            <svg class="w-4 h-4 shrink-0 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                            <span>{{ __('Hardware Scanner') }}</span>
                        </button>
                    </div>

                    <a href="{{ route('finance.expenses.index') }}" 
                       class="inline-flex items-center justify-center gap-1.5 px-4 py-2 min-h-[38px] rounded-xl text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        <span>{{ __('Travel & Fuel Claims') }}</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- 2. METRIC HIGHLIGHTS CARDS (4 Columns across on Laptop)             --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <div :class="currentTab !== 'overview' ? 'hidden lg:grid' : ''" class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
                
                {{-- Assigned Projects --}}
                <div @click="switchTab('projects')"
                     class="p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm text-left hover:border-blue-500 hover:shadow-md transition-all group cursor-pointer"
                     :class="currentTab === 'projects' ? 'ring-2 ring-blue-500 border-blue-500' : ''">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">{{ __('Projects') }}</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-xs"></span>
                    </div>
                    <div class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 dark:text-white mt-1.5 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">{{ $assignedProjects->count() }}</div>
                    <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                        <span>{{ __('Active sites') }}</span>
                        <span class="text-blue-600 dark:text-blue-400 font-bold">&rarr;</span>
                    </div>
                </div>

                {{-- Active Installation Jobs --}}
                <div @click="switchTab('jobs')"
                     class="p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm text-left hover:border-indigo-500 hover:shadow-md transition-all group cursor-pointer"
                     :class="currentTab === 'jobs' ? 'ring-2 ring-indigo-500 border-indigo-500' : ''">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">{{ __('Installation Jobs') }}</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 shadow-xs"></span>
                    </div>
                    <div class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 dark:text-white mt-1.5 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">{{ $activeJobs->count() }}</div>
                    <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                        <span>{{ __('Field orders') }}</span>
                        <span class="text-indigo-600 dark:text-indigo-400 font-bold">&rarr;</span>
                    </div>
                </div>

                {{-- Site Surveys --}}
                <div @click="switchTab('surveys')"
                     class="p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm text-left hover:border-amber-500 hover:shadow-md transition-all group cursor-pointer"
                     :class="currentTab === 'surveys' ? 'ring-2 ring-amber-500 border-amber-500' : ''">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">{{ __('Site Surveys') }}</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-xs"></span>
                    </div>
                    <div class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 dark:text-white mt-1.5 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition">{{ $activeSurveys->count() }}</div>
                    <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                        <span>{{ __('Inspection visits') }}</span>
                        <span class="text-amber-600 dark:text-amber-400 font-bold">&rarr;</span>
                    </div>
                </div>

                {{-- Field Workforce --}}
                <div @click="switchTab('workers')"
                     class="p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm text-left hover:border-emerald-500 hover:shadow-md transition-all group cursor-pointer"
                     :class="currentTab === 'workers' ? 'ring-2 ring-emerald-500 border-emerald-500' : ''">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">{{ __('Field Crew') }}</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-xs"></span>
                    </div>
                    <div class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 dark:text-white mt-1.5 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition">{{ $activeWorkers->count() }}</div>
                    <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                        <span>{{ __('Daily wage workers') }}</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">&rarr;</span>
                    </div>
                </div>

            </div>

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- 3. UNIFIED TAB NAVIGATION (Desktop / Tablet Segmented Bar)         --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <div class="hidden lg:block p-2 sm:p-2.5 rounded-2xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm my-4">
                <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto no-scrollbar py-0.5">
                    <button type="button" @click="switchTab('overview')"
                            class="shrink-0 px-4 sm:px-5 py-2 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer border"
                            :class="currentTab === 'overview' 
                                ? 'bg-[#65a30d] !text-white border-[#65a30d] shadow-sm' 
                                : 'bg-white hover:bg-slate-50 dark:bg-slate-800/80 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:text-slate-900 dark:hover:text-white'"
                            :style="currentTab === 'overview' ? 'color: #ffffff !important;' : ''">
                        <svg class="w-4 h-4 shrink-0" :class="currentTab === 'overview' ? '!text-white' : ''" :stroke="currentTab === 'overview' ? '#ffffff' : 'currentColor'" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                        <span :class="currentTab === 'overview' ? '!text-white font-bold' : ''">{{ __('Overview') }}</span>
                    </button>

                    <button type="button" @click="switchTab('projects')"
                            class="shrink-0 px-4 sm:px-5 py-2 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer border"
                            :class="currentTab === 'projects' 
                                ? 'bg-[#65a30d] !text-white border-[#65a30d] shadow-sm' 
                                : 'bg-white hover:bg-slate-50 dark:bg-slate-800/80 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:text-slate-900 dark:hover:text-white'"
                            :style="currentTab === 'projects' ? 'color: #ffffff !important;' : ''">
                        <svg class="w-4 h-4 shrink-0" :class="currentTab === 'projects' ? '!text-white' : ''" :stroke="currentTab === 'projects' ? '#ffffff' : 'currentColor'" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21"/></svg>
                        <span :class="currentTab === 'projects' ? '!text-white font-bold' : ''">{{ __('Projects') }}</span>
                        @if($assignedProjects->count() > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="currentTab === 'projects' ? 'bg-white/25 !text-white' : 'bg-lime-100 dark:bg-lime-900/60 text-lime-800 dark:text-lime-300'">
                                {{ $assignedProjects->count() }}
                            </span>
                        @endif
                    </button>

                    <button type="button" @click="switchTab('jobs')"
                            class="shrink-0 px-4 sm:px-5 py-2 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer border"
                            :class="currentTab === 'jobs' 
                                ? 'bg-[#65a30d] !text-white border-[#65a30d] shadow-sm' 
                                : 'bg-white hover:bg-slate-50 dark:bg-slate-800/80 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:text-slate-900 dark:hover:text-white'"
                            :style="currentTab === 'jobs' ? 'color: #ffffff !important;' : ''">
                        <svg class="w-4 h-4 shrink-0" :class="currentTab === 'jobs' ? '!text-white' : ''" :stroke="currentTab === 'jobs' ? '#ffffff' : 'currentColor'" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.204a4.5 4.5 0 00-6.364-6.364l-.707.707a4.5 4.5 0 006.364 6.364l.707-.707z"/></svg>
                        <span :class="currentTab === 'jobs' ? '!text-white font-bold' : ''">{{ __('Jobs') }}</span>
                        @if($activeJobs->count() > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="currentTab === 'jobs' ? 'bg-white/25 !text-white' : 'bg-lime-100 dark:bg-lime-900/60 text-lime-800 dark:text-lime-300'">
                                {{ $activeJobs->count() }}
                            </span>
                        @endif
                    </button>

                    <button type="button" @click="switchTab('surveys')"
                            class="shrink-0 px-4 sm:px-5 py-2 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer border"
                            :class="currentTab === 'surveys' 
                                ? 'bg-[#65a30d] !text-white border-[#65a30d] shadow-sm' 
                                : 'bg-white hover:bg-slate-50 dark:bg-slate-800/80 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:text-slate-900 dark:hover:text-white'"
                            :style="currentTab === 'surveys' ? 'color: #ffffff !important;' : ''">
                        <svg class="w-4 h-4 shrink-0" :class="currentTab === 'surveys' ? '!text-white' : ''" :stroke="currentTab === 'surveys' ? '#ffffff' : 'currentColor'" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                        <span :class="currentTab === 'surveys' ? '!text-white font-bold' : ''">{{ __('Surveys') }}</span>
                        @if($activeSurveys->count() > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="currentTab === 'surveys' ? 'bg-white/25 !text-white' : 'bg-lime-100 dark:bg-lime-900/60 text-lime-800 dark:text-lime-300'">
                                {{ $activeSurveys->count() }}
                            </span>
                        @endif
                    </button>

                    <button type="button" @click="switchTab('workers')"
                            class="shrink-0 px-4 sm:px-5 py-2 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer border"
                            :class="currentTab === 'workers' 
                                ? 'bg-[#65a30d] !text-white border-[#65a30d] shadow-sm' 
                                : 'bg-white hover:bg-slate-50 dark:bg-slate-800/80 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:text-slate-900 dark:hover:text-white'"
                            :style="currentTab === 'workers' ? 'color: #ffffff !important;' : ''">
                        <svg class="w-4 h-4 shrink-0" :class="currentTab === 'workers' ? '!text-white' : ''" :stroke="currentTab === 'workers' ? '#ffffff' : 'currentColor'" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        <span :class="currentTab === 'workers' ? '!text-white font-bold' : ''">{{ __('Workers') }}</span>
                    </button>

                    <button type="button" @click="switchTab('scanner')"
                            class="shrink-0 px-4 sm:px-5 py-2 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer border"
                            :class="currentTab === 'scanner' 
                                ? 'bg-[#65a30d] !text-white border-[#65a30d] shadow-sm' 
                                : 'bg-white hover:bg-slate-50 dark:bg-slate-800/80 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:text-slate-900 dark:hover:text-white'"
                            :style="currentTab === 'scanner' ? 'color: #ffffff !important;' : ''">
                        <svg class="w-4 h-4 shrink-0" :class="currentTab === 'scanner' ? '!text-white' : ''" :stroke="currentTab === 'scanner' ? '#ffffff' : 'currentColor'" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                        <span :class="currentTab === 'scanner' ? '!text-white font-bold' : ''">{{ __('Scanner') }}</span>
                    </button>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- TAB PANE 1: OVERVIEW DASHBOARD (Adaptive 12-Column Grid on Laptop)  --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <div x-show="currentTab === 'overview'" class="space-y-6 sm:space-y-8">
                
                {{-- 12-Column Bento Layout on Laptop, Vertical Stack on Mobile --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
                    
                    {{-- Left Main Area (8 Cols on Laptop) --}}
                    <div class="lg:col-span-8 space-y-6 lg:space-y-8">
                        
                        {{-- Featured Primary Project Card --}}
                        @if($primaryPrj)
                            <div class="rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-7 shadow-sm space-y-5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-xs"></span>
                                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Primary Active Project') }}</span>
                                    </div>
                                    <button type="button" @click="switchTab('projects')" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1 cursor-pointer">
                                        <span>{{ __('View all projects') }}</span>
                                        <span>&rarr;</span>
                                    </button>
                                </div>

                                <div class="rounded-2xl p-4 sm:p-6 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 space-y-4">
                                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                        <div class="flex items-start gap-3.5">
                                            <div class="w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 shadow-xs">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                                            </div>
                                            <div class="min-w-0">
                                                <span class="font-mono text-xs font-bold text-slate-400 block">{{ $primaryPrj->code }}</span>
                                                <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white leading-snug mt-0.5">{{ $primaryPrj->title }}</h2>
                                                @if(!empty($primaryPrj->created_at_label))
                                                    <span class="text-xs text-slate-400 block mt-0.5">{{ __('Created') }}: {{ $primaryPrj->created_at_label }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <span class="self-start px-3 py-1 rounded-full text-xs font-bold capitalize border border-amber-300 dark:border-amber-800 {{ $primaryPrj->priority_class ?? 'text-amber-600 bg-amber-50 dark:bg-amber-950/60' }}">
                                            {{ $primaryPrj->priority }} {{ __('Priority') }}
                                        </span>
                                    </div>

                                    {{-- Metrics Row --}}
                                    <div class="grid grid-cols-3 gap-3 py-3.5 border-y border-slate-200 dark:border-slate-700/80 text-center">
                                        <div class="p-2.5 sm:p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/70 shadow-xs">
                                            <span class="text-[10px] sm:text-[11px] text-slate-400 uppercase font-bold block">{{ __('All Tasks') }}</span>
                                            <span class="text-lg sm:text-xl font-black text-slate-900 dark:text-white mt-0.5 block">{{ $primaryPrj->all_tasks_count }}</span>
                                        </div>
                                        <div class="p-2.5 sm:p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/70 shadow-xs">
                                            <span class="text-[10px] sm:text-[11px] text-slate-400 uppercase font-bold block">{{ __('Active Tasks') }}</span>
                                            <span class="text-lg sm:text-xl font-black text-blue-600 dark:text-blue-400 mt-0.5 block">{{ $primaryPrj->active_tasks_count }}</span>
                                        </div>
                                        <div class="p-2.5 sm:p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/70 shadow-xs">
                                            <span class="text-[10px] sm:text-[11px] text-slate-400 uppercase font-bold block">{{ __('Completion') }}</span>
                                            <span class="text-lg sm:text-xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5 block">
                                                {{ $primaryPrj->all_tasks_count > 0 ? round((($primaryPrj->all_tasks_count - $primaryPrj->active_tasks_count) / $primaryPrj->all_tasks_count) * 100) : 0 }}%
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Assignees & Actions --}}
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-semibold text-slate-500">{{ __('Assignees:') }}</span>
                                            <div class="flex items-center -space-x-1.5">
                                                @forelse($primaryPrj->assignees as $asg)
                                                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center ring-2 ring-white dark:ring-slate-900 shadow-xs" title="{{ $asg['name'] }}">
                                                        {{ $asg['initials'] }}
                                                    </div>
                                                @empty
                                                    <span class="text-xs text-slate-400">{{ __('No team assigned') }}</span>
                                                @endforelse
                                                @if(!empty($primaryPrj->remaining_assignees_count) && $primaryPrj->remaining_assignees_count > 0)
                                                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-600 font-bold text-xs flex items-center justify-center ring-2 ring-white dark:ring-slate-900 shadow-xs">
                                                        +{{ $primaryPrj->remaining_assignees_count }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 flex-wrap">
                                            <button type="button" onclick="openAttendanceModal('{{ $assignedProjects->first()?->id }}')" 
                                                    class="inline-flex items-center justify-center px-4 py-2 min-h-[38px] rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 text-xs sm:text-sm font-bold hover:bg-blue-100 transition cursor-pointer">
                                                {{ __('Punch Crew') }}
                                            </button>
                                            <a href="{{ route('projects.show', $assignedProjects->first()?->id ?? 1) }}"
                                               class="inline-flex items-center justify-center gap-1.5 px-4 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 !text-white text-xs sm:text-sm font-bold shadow-xs transition"
                                               style="color: #ffffff !important;">
                                                <span class="!text-white" style="color: #ffffff !important;">{{ __('Open Workspace') }}</span>
                                                <span class="!text-white" style="color: #ffffff !important;">&rarr;</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Active Jobs & Surveys Quick Overview on Laptop (2-Column Grid) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-6">
                            
                            {{-- Quick Installation Jobs Box --}}
                            <div class="rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-3.5">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 shadow-xs"></span>
                                        <span>{{ __('Active Installation Jobs') }}</span>
                                    </h3>
                                    <button type="button" @click="switchTab('jobs')" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {{ __('View all') }} &rarr;
                                    </button>
                                </div>

                                <div class="space-y-2.5">
                                    @forelse($activeJobs->take(3) as $aj)
                                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/70 text-xs space-y-1 hover:border-indigo-400 transition">
                                            <div class="flex items-center justify-between">
                                                <strong class="font-mono text-indigo-600 dark:text-indigo-400">{{ $aj->job_no ?? ('JOB-' . $aj->id) }}</strong>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                                    {{ __(ucfirst(str_replace('_', ' ', $aj->status))) }}
                                                </span>
                                            </div>
                                            <div class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ $aj->quotation?->lead?->customer_name ?? __('Installation Site') }}</div>
                                            <div class="text-[11px] text-slate-400 truncate">{{ $aj->quotation?->lead?->site_address ?? __('Address on file') }}</div>
                                        </div>
                                    @empty
                                        <div class="p-4 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-800/30 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                                            {{ __('No active installation jobs.') }}
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Quick Site Surveys Box --}}
                            <div class="rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-3.5">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-xs"></span>
                                        <span>{{ __('Pending Site Surveys') }}</span>
                                    </h3>
                                    <button type="button" @click="switchTab('surveys')" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline">
                                        {{ __('View all') }} &rarr;
                                    </button>
                                </div>

                                <div class="space-y-2.5">
                                    @forelse($activeSurveys->take(3) as $as)
                                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/70 text-xs space-y-1 hover:border-amber-400 transition">
                                            <div class="flex items-center justify-between">
                                                <strong class="text-slate-900 dark:text-white truncate">{{ $as->lead?->customer_name ?? __('Customer Site') }}</strong>
                                                <span class="text-[10px] text-slate-400">{{ $as->survey_date ? $as->survey_date->translatedFormat('d M') : __('Immediate') }}</span>
                                            </div>
                                            <div class="text-[11px] text-slate-400 truncate">{{ $as->site_address ?: ($as->lead?->site_address ?: __('Address on file')) }}</div>
                                            <div class="pt-1 flex justify-end">
                                                <a href="{{ route('technician.surveys.show', $as) }}" class="text-[11px] font-bold text-amber-600 dark:text-amber-400 hover:underline">
                                                    {{ __('Conduct Survey') }} &rarr;
                                                </a>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-4 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-800/30 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                                            {{ __('No pending site inspection surveys.') }}
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                        </div>

                    </div>

                    {{-- Right Sidebar Area (4 Cols on Laptop) --}}
                    <div class="lg:col-span-4 space-y-6 lg:space-y-8">
                        
                        {{-- Workers Today Bento Roster --}}
                        <div class="rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight">{{ __('Field Crew Roster') }}</h3>
                                    <p class="text-xs text-slate-400">{{ __('Today\'s on-site attendance status') }}</p>
                                </div>
                                <button type="button" @click="switchTab('workers')" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ __('View all') }} &rarr;
                                </button>
                            </div>

                            @if($workersToday->isNotEmpty())
                                <div class="grid grid-cols-2 gap-2.5">
                                    @foreach($workersToday->take(6) as $w)
                                        <div class="rounded-2xl p-3 text-center flex flex-col items-center justify-between space-y-1.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/70 hover:border-blue-400 hover:shadow-xs transition">
                                            <div class="w-10 h-10 rounded-full border-2 border-blue-500 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-300 font-black text-xs flex items-center justify-center shadow-xs">
                                                {{ $w->initials }}
                                            </div>
                                            <div class="w-full">
                                                <h4 class="text-xs font-extrabold text-slate-900 dark:text-white truncate">{{ $w->name }}</h4>
                                                <span class="text-[10px] text-slate-400 block truncate">{{ $w->role_label }}</span>
                                            </div>
                                            <span class="inline-block text-[9.5px] font-bold px-2 py-0.2 rounded-full {{ $w->status_badge_class }}">
                                                {{ $w->status }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="pt-2">
                                    <button type="button" onclick="openAttendanceModal()"
                                            class="w-full py-2 px-4 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 !text-white font-bold text-xs sm:text-sm shadow-xs transition flex items-center justify-center gap-2 cursor-pointer"
                                            style="color: #ffffff !important;">
                                        <svg class="w-4 h-4 shrink-0 !text-white" fill="none" stroke="#ffffff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        <span class="!text-white" style="color: #ffffff !important;">{{ __('Log Worker Attendance') }}</span>
                                    </button>
                                </div>
                            @else
                                <div class="p-6 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                                    {{ __('No workers configured.') }}
                                </div>
                            @endif
                        </div>

                        {{-- Quick Barcode / Serial Lookup Tool --}}
                        <div class="rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 border border-blue-200/60 dark:border-blue-800/60 shadow-xs">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('On-Site Barcode Scanner') }}</h4>
                                    <p class="text-xs text-slate-400">{{ __('Instant serial lookup & warranty') }}</p>
                                </div>
                            </div>

                            <button type="button" onclick="openHardwareScannerModal()"
                                    class="w-full py-2 px-4 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 !text-white font-bold text-xs sm:text-sm shadow-sm transition flex items-center justify-center gap-2 cursor-pointer"
                                    style="color: #ffffff !important;">
                                <svg class="w-4 h-4 !text-white" fill="none" stroke="#ffffff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="!text-white font-bold" style="color: #ffffff !important;">{{ __('Open Hardware Scanner') }}</span>
                            </button>
                        </div>

                        {{-- Recent Site Activity Stream --}}
                        <div class="rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-3.5">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Recent Punches & Logs') }}</h4>
                            <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                @forelse($recentActivity->take(4) as $act)
                                    <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-300 font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ mb_substr($act->worker?->name ?? 'W', 0, 1) }}
                                            </div>
                                            <div class="min-w-0 truncate">
                                                <strong class="text-slate-800 dark:text-slate-200">{{ $act->worker?->name ?? __('Field Worker') }}</strong>
                                                <span class="text-slate-400 block text-[11px] truncate">
                                                    {{ $act->attendance_type === 'full' ? __('Full Day Attendance') : __('Site Shift') }}
                                                    @if($act->workDay?->project) &bull; {{ $act->workDay->project->title }} @endif
                                                </span>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-semibold text-slate-400 shrink-0">
                                            {{ $act->workDay?->work_date ? $act->workDay->work_date->translatedFormat('d M') : '' }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="py-4 text-center text-xs text-slate-400">
                                        {{ __('No recent activity recorded.') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- TAB PANE 2: ASSIGNED PROJECTS & SITES (2-Cols on Laptop)            --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <div x-show="currentTab === 'projects'" class="space-y-4 lg:space-y-5">
                <div class="flex items-center justify-between flex-wrap gap-2 px-1">
                    <div>
                        <h2 class="text-base sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Assigned Projects') }}</h2>
                        <p class="text-xs sm:text-sm text-slate-400">{{ __('Live site deployments and ongoing installations') }}</p>
                    </div>
                    <a href="{{ route('projects.create') }}" 
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 !text-white font-bold text-xs sm:text-sm shadow-xs transition"
                       style="color: #ffffff !important;">
                        <svg class="w-4 h-4 shrink-0 !text-white" fill="none" stroke="#ffffff" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span class="!text-white font-bold" style="color: #ffffff !important;">{{ __('New Project') }}</span>
                    </a>
                </div>

                {{-- 2 Columns on Laptop / Desktop, Single Column on Mobile --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    @forelse($assignedProjects as $prj)
                        <div class="rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-4 flex flex-col justify-between">
                            
                            <div class="space-y-3.5">
                                {{-- Top Header Row --}}
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="px-2 py-0.5 rounded-md font-mono text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                {{ $prj->project_code }}
                                            </span>
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                {{ __(\App\Models\Project::statusOptions()[$prj->status] ?? str_replace('_', ' ', $prj->status)) }}
                                            </span>
                                        </div>
                                        <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-1.5">{{ $prj->title }}</h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                            {{ __('Client') }}: <strong class="text-slate-700 dark:text-slate-300">{{ $prj->company_name ?? ($prj->site?->client_name ?? __('Client')) }}</strong>
                                            &bull; {{ __('Type') }}: {{ __($prj->project_type_label) }}
                                        </p>
                                    </div>

                                    <a href="{{ route('projects.show', $prj) }}" 
                                       class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline shrink-0">
                                        <span>{{ __('Workspace') }}</span>
                                        <span>&rarr;</span>
                                    </a>
                                </div>

                                {{-- Site Address & Phone --}}
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60 text-xs space-y-1.5">
                                    @if($prj->site)
                                        <div class="text-slate-600 dark:text-slate-300 flex items-start gap-1.5">
                                            <strong class="text-slate-800 dark:text-slate-200 shrink-0">{{ __('Site:') }}</strong>
                                            <span>{{ $prj->site->name }} &mdash; {{ $prj->site->address }}</span>
                                        </div>
                                    @elseif($prj->site_address)
                                        <div class="text-slate-600 dark:text-slate-300 flex items-start gap-1.5">
                                            <strong class="text-slate-800 dark:text-slate-200 shrink-0">{{ __('Site:') }}</strong>
                                            <span>{{ $prj->site_address }}</span>
                                        </div>
                                    @endif

                                    @if($prj->contact_person || $prj->contact_phone)
                                        <div class="flex items-center gap-2">
                                            <strong class="text-slate-800 dark:text-slate-200">{{ __('Contact:') }}</strong>
                                            <span>{{ $prj->contact_person ?? __('Manager') }}</span>
                                            @if($prj->contact_phone)
                                                <a href="tel:{{ $prj->contact_phone }}" class="text-blue-600 dark:text-blue-400 font-bold hover:underline">
                                                    ({{ $prj->contact_phone }})
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Task Count & Status Pills --}}
                                <div class="grid grid-cols-3 gap-2 text-center text-xs p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60">
                                    <div>
                                        <span class="text-[10px] text-slate-400 block font-semibold uppercase">{{ __('Total Tasks') }}</span>
                                        <span class="text-sm sm:text-base font-black text-slate-900 dark:text-white">{{ $prj->tasks->count() }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 block font-semibold uppercase">{{ __('Pending/Active') }}</span>
                                        <span class="text-sm sm:text-base font-black text-blue-600 dark:text-blue-400">{{ $prj->tasks->whereIn('status', ['pending', 'in_progress'])->count() }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 block font-semibold uppercase">{{ __('Completed') }}</span>
                                        <span class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400">{{ $prj->tasks->where('status', 'completed')->count() }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Action Buttons (100% Working CRM Routes) --}}
                            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($prj->contact_phone)
                                        <a href="tel:{{ $prj->contact_phone }}" 
                                           class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 min-h-[38px] rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <span>{{ __('Call') }}</span>
                                        </a>
                                    @endif

                                    <button type="button" onclick="openAttendanceModal('{{ $prj->id }}')" 
                                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 min-h-[38px] rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 text-xs sm:text-sm font-bold hover:bg-blue-100 transition cursor-pointer">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        <span>{{ __('Punch Crew') }}</span>
                                    </button>

                                    <a href="{{ route('projects.tasks.index', $prj) }}" 
                                       class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 min-h-[38px] rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                        <span>{{ __('Tasks') }}</span>
                                    </a>

                                    <a href="{{ route('projects.show', [$prj, 'tab' => 'work_days']) }}" 
                                       class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 min-h-[38px] rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                        <span>{{ __('Work Days') }}</span>
                                    </a>
                                </div>

                                <a href="{{ route('projects.show', $prj) }}" 
                                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2 min-h-[38px] rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-xs sm:text-sm font-bold text-blue-600 dark:text-blue-400 transition">
                                    <span>{{ __('Workspace') }}</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>

                        </div>
                    @empty
                        <div class="col-span-full rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 sm:p-12 text-center space-y-3">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21"/></svg>
                            </div>
                            <h4 class="text-sm sm:text-base font-bold text-slate-800 dark:text-slate-200">{{ __('No Projects Assigned') }}</h4>
                            <p class="text-xs sm:text-sm text-slate-400 max-w-sm mx-auto">{{ __('Projects assigned to you by admin or created by you appear here.') }}</p>
                            <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-blue-600 !text-white font-bold text-xs sm:text-sm shadow-xs hover:bg-blue-700 transition"
                               style="color: #ffffff !important;">
                                <svg class="w-4 h-4 shrink-0 !text-white" fill="none" stroke="#ffffff" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                <span class="!text-white font-bold" style="color: #ffffff !important;">{{ __('New Project') }}</span>
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- TAB PANE 3: INSTALLATION JOBS & JCR SIGN-OFF (2-Cols on Laptop)     --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <div x-show="currentTab === 'jobs'" class="space-y-4 lg:space-y-5">
                <div class="px-1">
                    <h2 class="text-base sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Active Installation Jobs') }}</h2>
                    <p class="text-xs sm:text-sm text-slate-400">{{ __('Installation work orders assigned to you for field execution') }}</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    @forelse($activeJobs as $job)
                        @php $lead = $job->quotation?->lead; @endphp
                        <div class="rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-4 flex flex-col justify-between">
                            <div class="space-y-3.5">
                                <div class="flex items-start justify-between gap-3 flex-wrap">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $job->job_no ?? ('JOB-' . $job->id) }}</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold capitalize bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                                {{ __(ucfirst(str_replace('_', ' ', $job->status))) }}
                                            </span>
                                        </div>
                                        <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-1">{{ $lead?->customer_name ?? __('Installation Site') }}</h3>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ __('Scheduled') }}: {{ $job->scheduled_at ? \Carbon\Carbon::parse($job->scheduled_at)->translatedFormat('d M Y, h:i A') : $job->created_at->translatedFormat('d M Y') }}</p>
                                    </div>

                                    <a href="{{ route('jcr.create-job', $job) }}" 
                                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                        <span>{{ __('Sign JCR') }}</span>
                                    </a>
                                </div>

                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60 text-xs space-y-1.5">
                                    <div class="text-slate-600 dark:text-slate-300 flex items-start gap-1.5">
                                        <strong class="text-slate-800 dark:text-slate-200 shrink-0">{{ __('Site Address:') }}</strong>
                                        <span>{{ $lead?->site_address ?? $lead?->address ?? __('Address not specified') }}</span>
                                    </div>
                                    @if($lead?->phone)
                                        <div class="flex items-center gap-2">
                                            <strong class="text-slate-800 dark:text-slate-200">{{ __('Customer Phone:') }}</strong>
                                            <a href="tel:{{ $lead->phone }}" class="text-blue-600 dark:text-blue-400 font-bold hover:underline">{{ $lead->phone }}</a>
                                        </div>
                                    @endif
                                    @if($job->installation_notes)
                                        <div class="text-slate-600 dark:text-slate-300 pt-1 border-t border-slate-200/40 dark:border-slate-700/40">
                                            <strong class="text-slate-800 dark:text-slate-200">{{ __('Notes:') }}</strong>
                                            <span>{{ $job->installation_notes }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Update Status Form --}}
                            <form method="POST" action="{{ route('technician.jobs.updateStatus', $job) }}" class="space-y-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                                @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">{{ __('Update Status') }}</label>
                                        <select name="status" class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3 py-2">
                                            <option value="pending" @selected($job->status === 'pending')>{{ __('Pending') }}</option>
                                            <option value="scheduled" @selected($job->status === 'scheduled')>{{ __('Scheduled') }}</option>
                                            <option value="assigned" @selected($job->status === 'assigned')>{{ __('Assigned (In Progress)') }}</option>
                                            <option value="completed" @selected($job->status === 'completed')>{{ __('Completed') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">{{ __('Work Notes') }}</label>
                                        <input type="text" name="notes" value="{{ $job->installation_notes }}" placeholder="{{ __('Findings or notes...') }}"
                                               class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-3 py-2">
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center px-4 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 !text-white font-bold text-xs sm:text-sm shadow-xs transition cursor-pointer"
                                            style="color: #ffffff !important;">
                                        <span class="!text-white" style="color: #ffffff !important;">{{ __('Save Status') }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @empty
                        <div class="col-span-full rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 sm:p-12 text-center text-xs sm:text-sm text-slate-400">
                            {{ __('No active installation jobs assigned right now.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- TAB PANE 4: SITE SURVEYS & INSPECTIONS (2-Cols on Laptop)           --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <div x-show="currentTab === 'surveys'" class="space-y-4 lg:space-y-5">
                <div class="px-1">
                    <h2 class="text-base sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Site Surveys') }}</h2>
                    <p class="text-xs sm:text-sm text-slate-400">{{ __('Customer site inspections for CCTV, cabling, and access control feasibility') }}</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    @forelse($activeSurveys as $survey)
                        @php $sLead = $survey->lead; @endphp
                        <div class="rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-4 flex flex-col justify-between">
                            <div class="space-y-3.5">
                                <div class="flex items-start justify-between gap-3 flex-wrap">
                                    <div>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                            {{ __('Pending Site Inspection') }}
                                        </span>
                                        <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-1">
                                            {{ __('Survey: :customer', ['customer' => $sLead?->customer_name ?? __('Customer Site')]) }}
                                        </h3>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ __('Date') }}: {{ $survey->survey_date ? $survey->survey_date->translatedFormat('d M Y') : __('Immediate') }}</p>
                                    </div>

                                    <a href="{{ route('technician.surveys.show', $survey) }}" 
                                       class="inline-flex items-center justify-center gap-2 px-4 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 !text-white font-bold text-xs sm:text-sm shadow-xs transition"
                                       style="color: #ffffff !important;">
                                        <svg class="w-4 h-4 shrink-0 !text-white" fill="none" stroke="#ffffff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                        <span class="!text-white" style="color: #ffffff !important;">{{ __('Conduct Survey') }}</span>
                                    </a>
                                </div>

                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60 text-xs space-y-1.5">
                                    <div class="text-slate-600 dark:text-slate-300 flex items-start gap-1.5">
                                        <strong class="text-slate-800 dark:text-slate-200 shrink-0">{{ __('Site Address:') }}</strong>
                                        <span>{{ $survey->site_address ?: ($sLead?->site_address ?: __('Not specified')) }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <strong class="text-slate-800 dark:text-slate-200">{{ __('Contact:') }}</strong>
                                        <span>{{ $survey->contact_person ?: $sLead?->customer_name }}</span>
                                        @if($survey->contact_phone || $sLead?->phone)
                                            <a href="tel:{{ $survey->contact_phone ?: $sLead?->phone }}" class="text-blue-600 dark:text-blue-400 font-bold hover:underline">
                                                ({{ $survey->contact_phone ?: $sLead?->phone }})
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center gap-2">
                                @if($survey->contact_phone || $sLead?->phone)
                                    <a href="tel:{{ $survey->contact_phone ?: $sLead?->phone }}" 
                                       class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 min-h-[38px] rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                        <span>{{ __('Call') }}</span>
                                    </a>
                                @endif
                                @if($survey->site_address || $sLead?->site_address)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($survey->site_address ?: $sLead?->site_address) }}" 
                                       target="_blank" 
                                       class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 min-h-[38px] rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                        <span>{{ __('Google Maps') }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 sm:p-12 text-center text-xs sm:text-sm text-slate-400">
                            {{ __('No pending site inspection surveys assigned.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- TAB PANE 5: FIELD WORKFORCE (3-4 Cols on Laptop/Desktop)            --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <div x-show="currentTab === 'workers'" class="space-y-4 lg:space-y-5">
                <div class="flex items-center justify-between flex-wrap gap-2 px-1">
                    <div>
                        <h2 class="text-base sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Field Crew Workers') }}</h2>
                        <p class="text-xs sm:text-sm text-slate-400">{{ __('Active daily-wage technicians, cable pullers, and helpers') }}</p>
                    </div>
                    <button type="button" onclick="openAttendanceModal()"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 !text-white font-bold text-xs sm:text-sm shadow-xs transition cursor-pointer"
                            style="color: #ffffff !important;">
                        <svg class="w-4 h-4 shrink-0 !text-white" fill="none" stroke="#ffffff" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span class="!text-white" style="color: #ffffff !important;">{{ __('Punch Attendance') }}</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($workersToday as $w)
                        <div class="rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 shadow-sm space-y-3.5 flex flex-col justify-between hover:border-blue-400 transition">
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-full border-2 border-blue-500 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-300 font-black text-sm flex items-center justify-center shrink-0 shadow-xs">
                                        {{ $w->initials }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-sm font-black text-slate-900 dark:text-white truncate">{{ $w->name }}</h4>
                                        <span class="text-xs text-slate-400 block truncate">{{ $w->role_label }}</span>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $w->status_badge_class }}">
                                        {{ $w->status }}
                                    </span>
                                </div>

                                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60 text-xs">
                                    <span class="text-slate-400 block text-[10px] uppercase font-bold">{{ __('Phone / Direct Contact') }}</span>
                                    @if($w->phone)
                                        <a href="tel:{{ $w->phone }}" class="text-blue-600 dark:text-blue-400 font-bold hover:underline">
                                            {{ $w->phone }}
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                                <span class="text-[11px] text-slate-400">{{ __('On-Duty Today') }}</span>

                                <button type="button" onclick="openAttendanceModal(null, '{{ $w->id }}')" 
                                        class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                    {{ __('Punch Shift') }} &rarr;
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-3 text-center">
                    <a href="{{ route('workers.attendance.index') }}" class="text-xs sm:text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:underline">
                        {{ __('View Complete Worker Attendance Register & Wages') }} &rarr;
                    </a>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- TAB PANE 6: HARDWARE SCANNER & COMPLETED HISTORY (Laptop 2-Cols)    --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <div x-show="currentTab === 'scanner'" class="space-y-4 lg:space-y-5">
                <div class="px-1">
                    <h2 class="text-base sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('On-Site Hardware Barcode Scanner') }}</h2>
                    <p class="text-xs sm:text-sm text-slate-400">{{ __('Scan camera, NVR or terminal serial barcode to verify warranty and client allocation') }}</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6 items-start">
                    
                    {{-- Left 7 Cols: Scanner Station --}}
                    <div class="lg:col-span-7 rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 lg:p-7 shadow-sm space-y-4">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <button type="button" onclick="openHardwareScannerModal('camera')"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 !text-white font-bold text-xs sm:text-sm shadow-sm transition cursor-pointer"
                                    style="color: #ffffff !important;">
                                <svg class="w-4 h-4 shrink-0 !text-white" fill="none" stroke="#ffffff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="!text-white font-bold" style="color: #ffffff !important;">{{ __('Launch Camera Scanner') }}</span>
                            </button>

                            <div class="w-full sm:max-w-xs flex items-center gap-2">
                                <input type="text" x-model="scanCodeInput" placeholder="{{ __('Type serial number...') }}" 
                                       @keydown.enter="if(scanCodeInput.trim()) { handleTechScannedCode(scanCodeInput.trim()); scanCodeInput = ''; }"
                                       class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 px-3.5 py-2 min-h-[38px] text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <button type="button" @click="if(scanCodeInput.trim()) { handleTechScannedCode(scanCodeInput.trim()); scanCodeInput = ''; }"
                                        class="inline-flex items-center justify-center px-4 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 !text-white text-xs sm:text-sm font-bold shadow-sm transition cursor-pointer shrink-0"
                                        style="color: #ffffff !important;">
                                    <span style="color: #ffffff !important;">{{ __('Lookup') }}</span>
                                </button>
                            </div>
                        </div>

                        {{-- Lookup Result Card --}}
                        <div id="tech-scan-result" class="hidden mt-4"></div>
                    </div>

                    {{-- Right 5 Cols: Completed Work History --}}
                    <div class="lg:col-span-5 rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 lg:p-7 shadow-sm space-y-4">
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">{{ __('Completed Work History') }}</h3>
                        <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                            @forelse($completedJobs as $cJob)
                                <div class="py-3 flex items-center justify-between">
                                    <div>
                                        <strong class="text-slate-800 dark:text-slate-200 text-xs sm:text-sm">{{ $cJob->job_no ?? ('JOB-' . $cJob->id) }}</strong>
                                        <span class="text-slate-400 block text-[11px] mt-0.5">{{ $cJob->quotation?->lead?->customer_name }} &bull; {{ $cJob->updated_at->translatedFormat('d M Y') }}</span>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                                        {{ __('Completed') }}
                                    </span>
                                </div>
                            @empty
                                <div class="py-4 text-center text-slate-400">{{ __('No completed jobs yet.') }}</div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- 4. ULTRA-CRISP MOBILE BOTTOM DOCK (Hidden on Laptop, Visible on Mobile) --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <nav aria-label="{{ __('Field sections') }}" class="lg:hidden fixed bottom-0 inset-x-0 z-50 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 px-2 pt-1.5 pb-[max(0.5rem,env(safe-area-inset-bottom))] shadow-[0_-4px_20px_rgba(0,0,0,0.08)]">
            <div class="w-full max-w-md mx-auto flex items-center justify-between gap-1">
                
                {{-- Overview Tab --}}
                <button type="button" @click="switchTab('overview')"
                        :aria-current="currentTab === 'overview' ? 'page' : 'false'"
                        class="flex-1 h-auto min-h-[48px] py-1.5 px-1 rounded-xl flex flex-col items-center justify-center gap-0.5 transition cursor-pointer select-none"
                        :class="currentTab === 'overview' ? 'text-blue-600 dark:text-blue-400 font-bold bg-slate-100/70 dark:bg-slate-800/70' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                        :style="currentTab === 'overview' ? 'color: var(--crm-accent, #2563eb);' : ''">
                    <svg class="w-5 h-5 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                    <span class="text-[11px] leading-tight pointer-events-none">{{ __('Home') }}</span>
                </button>

                {{-- Projects Tab --}}
                <button type="button" @click="switchTab('projects')"
                        :aria-current="currentTab === 'projects' ? 'page' : 'false'"
                        class="flex-1 h-auto min-h-[48px] py-1.5 px-1 rounded-xl flex flex-col items-center justify-center gap-0.5 transition cursor-pointer select-none"
                        :class="currentTab === 'projects' ? 'text-blue-600 dark:text-blue-400 font-bold bg-slate-100/70 dark:bg-slate-800/70' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                        :style="currentTab === 'projects' ? 'color: var(--crm-accent, #2563eb);' : ''">
                    <svg class="w-5 h-5 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21"/></svg>
                    <span class="text-[11px] leading-tight pointer-events-none">{{ __('Projects') }}</span>
                </button>

                {{-- Jobs Tab --}}
                <button type="button" @click="switchTab('jobs')"
                        :aria-current="currentTab === 'jobs' ? 'page' : 'false'"
                        class="flex-1 h-auto min-h-[48px] py-1.5 px-1 rounded-xl flex flex-col items-center justify-center gap-0.5 transition cursor-pointer select-none"
                        :class="currentTab === 'jobs' ? 'text-blue-600 dark:text-blue-400 font-bold bg-slate-100/70 dark:bg-slate-800/70' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                        :style="currentTab === 'jobs' ? 'color: var(--crm-accent, #2563eb);' : ''">
                    <svg class="w-5 h-5 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.204a4.5 4.5 0 00-6.364-6.364l-.707.707a4.5 4.5 0 006.364 6.364l.707-.707z"/></svg>
                    <span class="text-[11px] leading-tight pointer-events-none">{{ __('Jobs') }}</span>
                </button>

                {{-- Surveys Tab --}}
                <button type="button" @click="switchTab('surveys')"
                        :aria-current="currentTab === 'surveys' ? 'page' : 'false'"
                        class="flex-1 h-auto min-h-[48px] py-1.5 px-1 rounded-xl flex flex-col items-center justify-center gap-0.5 transition cursor-pointer select-none"
                        :class="currentTab === 'surveys' ? 'text-blue-600 dark:text-blue-400 font-bold bg-slate-100/70 dark:bg-slate-800/70' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                        :style="currentTab === 'surveys' ? 'color: var(--crm-accent, #2563eb);' : ''">
                    <svg class="w-5 h-5 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                    <span class="text-[11px] leading-tight pointer-events-none">{{ __('Surveys') }}</span>
                </button>

                {{-- Workers Tab --}}
                <button type="button" @click="switchTab('workers')"
                        :aria-current="currentTab === 'workers' ? 'page' : 'false'"
                        class="flex-1 h-auto min-h-[48px] py-1.5 px-1 rounded-xl flex flex-col items-center justify-center gap-0.5 transition cursor-pointer select-none"
                        :class="currentTab === 'workers' ? 'text-blue-600 dark:text-blue-400 font-bold bg-slate-100/70 dark:bg-slate-800/70' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                        :style="currentTab === 'workers' ? 'color: var(--crm-accent, #2563eb);' : ''">
                    <svg class="w-5 h-5 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    <span class="text-[11px] leading-tight pointer-events-none">{{ __('Crew') }}</span>
                </button>

            </div>
        </nav>

    </div>

    {{-- HARDWARE BARCODE SCANNER MODAL & COMPONENT --}}
    <x-hardware-scanner-modal modalId="hardwareScannerModal" :projects="$activeProjects" :leads="$leads ?? collect()" />
    <x-barcode-scanner modalId="tech-barcode-modal" :title="__('Hardware Scanner')" />

    {{-- SPLIT GUIDED WORKER ATTENDANCE MODAL COMPONENT --}}
    <x-mark-attendance-modal :workers="$activeWorkers" :projects="$activeProjects" />

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
            const container = document.getElementById('tech-scan-result');
            if (!container) return;

            container.classList.remove('hidden');
            container.innerHTML = `<div class="text-slate-300 text-xs flex items-center gap-2">
                <span class="inline-block animate-spin">&#9696;</span>
                <span>${techScanText.querying.replace(':code', '<strong>' + code + '</strong>')}</span>
            </div>`;

            try {
                const response = await fetch(`{{ route('equipment.lookup') }}?serial=${encodeURIComponent(code)}`);
                const data = await response.json();

                if (data.found && data.type === 'installed_equipment') {
                    const warrantyBadge = data.is_under_warranty 
                        ? `<span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">${techScanText.underWarranty}</span>`
                        : `<span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 dark:bg-rose-900/60 text-rose-800 dark:text-rose-300 border border-rose-300 dark:border-rose-700">${techScanText.warrantyExpired}</span>`;

                    container.innerHTML = `
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 p-4 sm:p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-300 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <h4 class="text-sm font-black text-slate-900 dark:text-white">${data.equipment_name}</h4>
                                        ${warrantyBadge}
                                    </div>
                                    <div class="text-xs text-slate-600 dark:text-slate-300 space-y-1">
                                        <div>${techScanText.customer}: <strong class="text-slate-900 dark:text-white">${data.customer_name}</strong> (${data.customer_phone})</div>
                                        <div>${techScanText.location}: <strong class="text-slate-900 dark:text-white">${data.location_tag || techScanText.siteAsset}</strong> &middot; S/N: <code class="font-mono bg-white dark:bg-slate-900 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 text-blue-600 dark:text-blue-400 font-bold">${data.serial_number}</code></div>
                                        <div>${techScanText.warrantyExpiry}: <strong>${data.mfg_warranty_expiry}</strong></div>
                                    </div>
                                </div>
                            </div>
                            <a href="${data.show_url}" 
                               class="self-start sm:self-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 min-h-[38px] rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-bold transition shadow-xs">
                                <span>${techScanText.viewHistory}</span> &rarr;
                            </a>
                        </div>
                    `;
                } else if (data.found && data.type === 'catalog_product') {
                    container.innerHTML = `
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 p-4 sm:p-5 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/80">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-300 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wide block">${techScanText.catalogItem}</span>
                                    <h4 class="text-sm font-black text-slate-900 dark:text-white mt-0.5">${data.product_name}</h4>
                                    <div class="text-xs text-slate-600 dark:text-slate-300 mt-1">SKU: <strong class="font-mono text-slate-800 dark:text-slate-200">${data.sku}</strong> &middot; ${techScanText.model}: ${data.model_no || techScanText.na} &middot; ${techScanText.stock}: <strong class="text-emerald-600 dark:text-emerald-400 font-bold">${techScanText.available.replace(':count', data.stock_quantity)}</strong></div>
                                </div>
                            </div>
                            <a href="{{ route('equipment.create') }}?product_id=${data.id}&serial=${encodeURIComponent(code)}" 
                               class="self-start sm:self-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 !text-white font-bold text-xs sm:text-sm shadow-sm transition"
                               style="color: #ffffff !important;">
                                <span style="color: #ffffff !important;">${techScanText.installAtSite} &rarr;</span>
                            </a>
                        </div>
                    `;
                } else {
                    container.innerHTML = `
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 sm:p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200">
                                        ${techScanText.notFound.replace(':code', '<span class="font-mono bg-amber-200/60 dark:bg-amber-900/80 px-2 py-0.5 rounded text-amber-950 dark:text-amber-100 font-black">' + code + '</span>')}
                                    </h4>
                                    <p class="text-xs text-amber-800/80 dark:text-amber-300/80 mt-0.5">${techScanText.notRegistered}</p>
                                </div>
                            </div>
                            <a href="{{ route('equipment.create') }}?serial=${encodeURIComponent(code)}" 
                               class="self-start sm:self-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 !text-white font-bold text-xs sm:text-sm shadow-sm transition"
                               style="color: #ffffff !important;">
                                <span style="color: #ffffff !important;">${techScanText.registerNew} &rarr;</span>
                            </a>
                        </div>
                    `;
                }
            } catch (e) {
                container.innerHTML = `<div class="text-rose-400 text-xs">${techScanText.lookupError.replace(':error', e.message)}</div>`;
            }
        }
    </script>
</x-app-layout>
