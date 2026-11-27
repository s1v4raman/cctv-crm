<x-app-layout>
    {{-- TECHNICIAN FIELD WORKSTATION --}}
    {{-- Fully Responsive: Compact App View on Mobile + Expansive Enterprise Dashboard on Laptop --}}

    @php
        $currentUser = auth()->user();
        $myAttendance = $myAttendanceToday ?? \App\Models\EmployeeAttendance::where('user_id', $currentUser->id)->whereDate('date', now()->toDateString())->first();
        $primaryPrj = $primaryProject ?? null;
        $firstJob = isset($activeJobs) ? $activeJobs->first() : null;
        $scannerJobContext = [
            'work_order_id' => $firstJob?->id,
            'project_id'    => $primaryPrj?->id ?? ($selectedProject?->id ?? null),
            'lead_id'       => $firstJob?->quotation?->lead_id ?? ($primaryPrj?->lead_id ?? ($selectedProject?->lead_id ?? null)),
            'site_label'    => $firstJob?->quotation?->lead?->customer_name ?? ($primaryPrj?->site_name ?? ($selectedProject?->title ?? '')),
        ];
    @endphp

    <div x-data="{
        currentTab: @js(request('tab', 'overview')), // 'overview', 'projects', 'jobs', 'surveys', 'workers', 'quotations', 'allowances', 'scanner'
        scanCodeInput: '',
        showAddWorkerModal: false,
        showExistingQuoteModal: false,
        showNewLeadQuoteModal: false,
        showAddAllowanceModal: false,
        selectedLeadIdForQuote: '',
        jobSearchTerm: '',
        jobStatusFilter: 'all',
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
    }" class="tech-mobile-scope min-h-screen pb-28 lg:pb-10 text-slate-800 dark:text-slate-100 bg-[#F8FAFC] dark:bg-slate-950">

        {{-- Main Container: 16px screen side padding on mobile, Adaptive Max-Width on Desktop --}}
        <div class="tech-mobile-container max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6 sm:space-y-8">

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
                            <button type="button" onclick="document.getElementById('techSiteCheckInModal').classList.remove('hidden')"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 !text-white font-bold text-xs sm:text-sm shadow-md shadow-blue-500/20 transition cursor-pointer"
                                    style="color: #ffffff !important;">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 !text-white" fill="none" stroke="#ffffff" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="!text-white font-bold" style="color: #ffffff !important;">{{ __('My Attendance') }}</span>
                            </button>
                        @else
                            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 sm:px-4 sm:py-2 min-h-[38px] rounded-xl {{ $myAttendance->approval_status === 'pending' ? 'bg-amber-50 dark:bg-amber-950/60 border border-amber-300 dark:border-amber-800' : ($myAttendance->approval_status === 'rejected' ? 'bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800' : 'bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800') }} text-xs sm:text-sm">
                                <span class="w-2.5 h-2.5 rounded-full {{ $myAttendance->approval_status === 'pending' ? 'bg-amber-500 animate-pulse' : ($myAttendance->approval_status === 'rejected' ? 'bg-rose-500' : 'bg-emerald-500') }}"></span>
                                <div>
                                    <span class="font-bold {{ $myAttendance->approval_status === 'pending' ? 'text-amber-800 dark:text-amber-300' : ($myAttendance->approval_status === 'rejected' ? 'text-rose-800 dark:text-rose-300' : 'text-emerald-800 dark:text-emerald-300') }}">
                                        {{ __('Full Day Attendance Logged') }}
                                    </span>
                                    <span class="text-[10px] block opacity-80">
                                        @if($myAttendance->approval_status === 'pending')
                                            {{ __('(Pending Review)') }}
                                        @elseif($myAttendance->approval_status === 'rejected')
                                            {{ __('(Rejected: :reason)', ['reason' => $myAttendance->rejection_reason ?? 'Office review']) }}
                                        @else
                                            {{ __('(Approved by Office)') }}
                                        @endif
                                        @if($myAttendance->project)
                                            &bull; {{ Str::limit($myAttendance->project->title, 22) }}
                                        @endif
                                    </span>
                                </div>
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
                            <span class="!text-white font-bold" style="color: #ffffff !important;">{{ __('New Project') }}</span>
                        </a>

                        {{-- Add Site Location (Quick Modal) --}}
                        <button type="button" onclick="openQuickAddSiteModal()"
                                class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 min-h-[38px] rounded-xl bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:hover:bg-blue-900/50 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 font-bold text-xs sm:text-sm shadow-xs transition cursor-pointer">
                            <span>{{ __('Add Site Location') }}</span>
                        </button>

                        {{-- Log Worker Attendance (Method 1) --}}
                        <button type="button" onclick="openAttendanceModal()"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 min-h-[38px] rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-bold text-xs sm:text-sm shadow-xs transition cursor-pointer">
                            <span>{{ __('Log Worker Attendance') }}</span>
                        </button>

                        {{-- Log Site Work Output (Method 2) --}}
                        <button type="button" onclick="openWorkOutputModal()"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 min-h-[38px] rounded-xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/40 dark:hover:bg-amber-900/50 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 font-bold text-xs sm:text-sm shadow-xs transition cursor-pointer">
                            <span>{{ __('Log Site Work Output') }}</span>
                        </button>

                        {{-- Hardware Scanner (Opens Full Multi-Function Modal) --}}
                        <button type="button" onclick="openHardwareScannerModal()"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 min-h-[38px] rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-bold text-xs sm:text-sm shadow-xs transition cursor-pointer">
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
            {{-- 2. METRIC HIGHLIGHTS CARDS (5 Columns across on Laptop)             --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <div :class="currentTab !== 'overview' ? 'hidden lg:grid' : ''" class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4 lg:gap-5">
                
                {{-- Assigned Projects --}}
                <div @click="switchTab('projects')"
                     class="p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm text-left hover:border-blue-500 hover:shadow-md transition-all group cursor-pointer"
                     :class="currentTab === 'projects' ? 'ring-2 ring-blue-500 border-blue-500' : ''">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">{{ __('Projects') }}</span>
                        <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="7" rx="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line stroke-linecap="round" stroke-linejoin="round" x1="2" x2="22" y1="13" y2="13"/></svg>
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
                        <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
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
                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
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
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M22 21v-2a4 4 0 0 0-3-3.87"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 dark:text-white mt-1.5 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition">{{ $activeWorkers->count() }}</div>
                    <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                        <span>{{ __('Field Workers') }}</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">&rarr;</span>
                    </div>
                </div>

                {{-- Quotations & Estimates --}}
                <div @click="switchTab('quotations')"
                     class="p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm text-left hover:border-blue-500 hover:shadow-md transition-all group cursor-pointer col-span-2 lg:col-span-1"
                     :class="currentTab === 'quotations' ? 'ring-2 ring-blue-500 border-blue-500' : ''">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">{{ __('Quotations') }}</span>
                        <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 dark:text-white mt-1.5 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">{{ isset($quotations) ? $quotations->count() : 0 }}</div>
                    <div class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                        <span>{{ __('Create & Share') }}</span>
                        <span class="text-blue-600 dark:text-blue-400 font-bold">&rarr;</span>
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
                                ? 'bg-blue-600 !text-white border-blue-600 shadow-sm' 
                                : 'bg-white hover:bg-slate-50 dark:bg-slate-800/80 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:text-slate-900 dark:hover:text-white'"
                            :style="currentTab === 'overview' ? 'color: #ffffff !important;' : ''">
                        <svg class="w-4 h-4 shrink-0" :class="currentTab === 'overview' ? '!text-white' : ''" :stroke="currentTab === 'overview' ? '#ffffff' : 'currentColor'" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                        <span :class="currentTab === 'overview' ? '!text-white font-bold' : ''">{{ __('Overview') }}</span>
                    </button>

                    <button type="button" @click="switchTab('projects')"
                            class="shrink-0 px-4 sm:px-5 py-2 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer border"
                            :class="currentTab === 'projects' 
                                ? 'bg-blue-600 !text-white border-blue-600 shadow-sm' 
                                : 'bg-white hover:bg-slate-50 dark:bg-slate-800/80 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:text-slate-900 dark:hover:text-white'"
                            :style="currentTab === 'projects' ? 'color: #ffffff !important;' : ''">
                        <svg class="w-4 h-4 shrink-0" :class="currentTab === 'projects' ? '!text-white' : ''" :stroke="currentTab === 'projects' ? '#ffffff' : 'currentColor'" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21"/></svg>
                        <span :class="currentTab === 'projects' ? '!text-white font-bold' : ''">{{ __('Projects') }}</span>
                        @if($assignedProjects->count() > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="currentTab === 'projects' ? 'bg-white/25 !text-white' : 'bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300'">
                                {{ $assignedProjects->count() }}
                            </span>
                        @endif
                    </button>

                    <button type="button" @click="switchTab('jobs')"
                            class="shrink-0 px-4 sm:px-5 py-2 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer border"
                            :class="currentTab === 'jobs' 
                                ? 'bg-blue-600 !text-white border-blue-600 shadow-sm' 
                                : 'bg-white hover:bg-slate-50 dark:bg-slate-800/80 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:text-slate-900 dark:hover:text-white'"
                            :style="currentTab === 'jobs' ? 'color: #ffffff !important;' : ''">
                        <svg class="w-4 h-4 shrink-0" :class="currentTab === 'jobs' ? '!text-white' : ''" :stroke="currentTab === 'jobs' ? '#ffffff' : 'currentColor'" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.204a4.5 4.5 0 00-6.364-6.364l-.707.707a4.5 4.5 0 006.364 6.364l.707-.707z"/></svg>
                        <span :class="currentTab === 'jobs' ? '!text-white font-bold' : ''">{{ __('Jobs') }}</span>
                        @if($activeJobs->count() > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="currentTab === 'jobs' ? 'bg-white/25 !text-white' : 'bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300'">
                                {{ $activeJobs->count() }}
                            </span>
                        @endif
                    </button>

                    <button type="button" @click="switchTab('surveys')"
                            class="shrink-0 px-4 sm:px-5 py-2 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer border"
                            :class="currentTab === 'surveys' 
                                ? 'bg-blue-600 !text-white border-blue-600 shadow-sm' 
                                : 'bg-white hover:bg-slate-50 dark:bg-slate-800/80 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:text-slate-900 dark:hover:text-white'"
                            :style="currentTab === 'surveys' ? 'color: #ffffff !important;' : ''">
                        <svg class="w-4 h-4 shrink-0" :class="currentTab === 'surveys' ? '!text-white' : ''" :stroke="currentTab === 'surveys' ? '#ffffff' : 'currentColor'" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                        <span :class="currentTab === 'surveys' ? '!text-white font-bold' : ''">{{ __('Surveys') }}</span>
                        @if($activeSurveys->count() > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="currentTab === 'surveys' ? 'bg-white/25 !text-white' : 'bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300'">
                                {{ $activeSurveys->count() }}
                            </span>
                        @endif
                    </button>

                    <button type="button" @click="switchTab('workers')"
                            class="shrink-0 px-4 sm:px-5 py-2 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer border"
                            :class="currentTab === 'workers' 
                                ? 'bg-blue-600 !text-white border-blue-600 shadow-sm' 
                                : 'bg-white hover:bg-slate-50 dark:bg-slate-800/80 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:text-slate-900 dark:hover:text-white'"
                            :style="currentTab === 'workers' ? 'color: #ffffff !important;' : ''">
                        <svg class="w-4 h-4 shrink-0" :class="currentTab === 'workers' ? '!text-white' : ''" :stroke="currentTab === 'workers' ? '#ffffff' : 'currentColor'" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        <span :class="currentTab === 'workers' ? '!text-white font-bold' : ''">{{ __('Workers') }}</span>
                    </button>

                    <button type="button" @click="switchTab('quotations')"
                            class="shrink-0 px-4 sm:px-5 py-2 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer border"
                            :class="currentTab === 'quotations' 
                                ? 'bg-blue-600 !text-white border-blue-600 shadow-sm' 
                                : 'bg-white hover:bg-slate-50 dark:bg-slate-800/80 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:text-slate-900 dark:hover:text-white'"
                            :style="currentTab === 'quotations' ? 'color: #ffffff !important;' : ''">
                        <svg class="w-4 h-4 shrink-0" :class="currentTab === 'quotations' ? '!text-white' : ''" :stroke="currentTab === 'quotations' ? '#ffffff' : 'currentColor'" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        <span :class="currentTab === 'quotations' ? '!text-white font-bold' : ''">{{ __('Quotations') }}</span>
                        @if(isset($quotations) && $quotations->count() > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="currentTab === 'quotations' ? 'bg-white/25 !text-white' : 'bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300'">
                                {{ $quotations->count() }}
                            </span>
                        @endif
                    </button>

                    <button type="button" @click="switchTab('allowances')"
                            class="shrink-0 px-4 sm:px-5 py-2 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer border"
                            :class="currentTab === 'allowances' 
                                ? 'bg-blue-600 !text-white border-blue-600 shadow-sm' 
                                : 'bg-white hover:bg-slate-50 dark:bg-slate-800/80 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:text-slate-900 dark:hover:text-white'"
                            :style="currentTab === 'allowances' ? 'color: #ffffff !important;' : ''">
                        <svg class="w-4 h-4 shrink-0" :class="currentTab === 'allowances' ? '!text-white' : 'text-slate-500 dark:text-slate-400'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185zM9.75 9h.008v.008H9.75V9zm4.5 6h.008v.008h-.008V15z"/></svg>
                        <span :class="currentTab === 'allowances' ? '!text-white font-bold' : ''">{{ __('Allowances & Claims') }}</span>
                        @if(isset($pendingClaimsCount) && $pendingClaimsCount > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="currentTab === 'allowances' ? 'bg-white/25 !text-white' : 'bg-amber-500 text-white'">
                                {{ $pendingClaimsCount }}
                            </span>
                        @endif
                    </button>

                    <button type="button" @click="switchTab('scanner')"
                            class="shrink-0 px-4 sm:px-5 py-2 min-h-[38px] rounded-xl text-xs sm:text-sm font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer border"
                            :class="currentTab === 'scanner' 
                                ? 'bg-blue-600 !text-white border-blue-600 shadow-sm' 
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
                                        <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="7" rx="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line stroke-linecap="round" stroke-linejoin="round" x1="2" x2="22" y1="13" y2="13"/></svg>
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
                                                @if(!empty($primaryPrj->site_name) || !empty($primaryPrj->site_address))
                                                    <div class="inline-flex items-center gap-1.5 text-xs text-blue-700 dark:text-blue-300 bg-blue-50/90 dark:bg-blue-950/60 px-2.5 py-1 rounded-lg border border-blue-200 dark:border-blue-800/60 mt-1.5">
                                                        <svg class="w-3.5 h-3.5 shrink-0 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                                        <span>
                                                            <strong>{{ __('Assigned Site:') }}</strong> {{ $primaryPrj->site_name ?: '' }}{{ !empty($primaryPrj->site_name) && !empty($primaryPrj->site_address) ? ' — ' : '' }}{{ Str::limit($primaryPrj->site_address, 36) }}
                                                        </span>
                                                    </div>
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
                                            <button type="button" onclick="openWorkOutputModal('{{ $assignedProjects->first()?->id }}')" 
                                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 min-h-[38px] rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs sm:text-sm font-bold hover:bg-amber-100 transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                                                <span>{{ __('Work Output') }}</span>
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
                                        <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
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
                                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
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
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="openQuickAddSiteModal()" 
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:hover:bg-blue-900/50 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 font-bold text-xs sm:text-sm shadow-xs transition cursor-pointer">
                            <span>{{ __('Add Site Location') }}</span>
                        </button>
                        <a href="{{ route('projects.create') }}" 
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 !text-white font-bold text-xs sm:text-sm shadow-xs transition"
                           style="color: #ffffff !important;">
                            <span class="!text-white font-bold" style="color: #ffffff !important;">{{ __('New Project') }}</span>
                        </a>
                    </div>
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
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300 border border-blue-300 dark:border-blue-700 flex items-center gap-1">
                                                ⭐ {{ __('Assigned to You') }}
                                            </span>
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
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

                                {{-- Assigned Site Location & Contact Details --}}
                                <div class="p-3.5 rounded-xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-800/70 text-xs space-y-2">
                                    <div class="flex items-start gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 mt-0.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <span class="text-[10px] uppercase font-bold text-blue-700 dark:text-blue-300 block tracking-wide">{{ __('Assigned Site Location') }}</span>
                                            <strong class="text-slate-900 dark:text-white text-xs block mt-0.5 font-bold">
                                                {{ $prj->site?->name ?? ($prj->customer_name ?? __('Customer Site')) }}
                                            </strong>
                                            @php
                                                $sAddr = $prj->site?->address ?: $prj->site_address;
                                                $sCity = $prj->site?->city;
                                            @endphp
                                            @if($sAddr || $sCity)
                                                <span class="text-[11px] text-slate-600 dark:text-slate-300 block mt-0.5">
                                                    {{ $sAddr }}{{ $sCity && !str_contains($sAddr ?? '', $sCity) ? ', ' . $sCity : '' }}
                                                </span>
                                            @endif
                                            @if($prj->site?->google_maps_link)
                                                <a href="{{ $prj->site->google_maps_link }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 dark:text-blue-400 hover:underline mt-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                                    <span>{{ __('Open in Google Maps') }}</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    @if($prj->contact_person || $prj->contact_phone)
                                        <div class="pt-1.5 border-t border-blue-200/50 dark:border-blue-800/50 flex items-center justify-between text-[11px]">
                                            <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                                                <strong class="text-slate-700 dark:text-slate-200">{{ __('Site Contact:') }}</strong>
                                                <span>{{ $prj->contact_person ?? __('Site Manager') }}</span>
                                            </div>
                                            @if($prj->contact_phone)
                                                <a href="tel:{{ $prj->contact_phone }}" class="text-blue-600 dark:text-blue-400 font-bold hover:underline">
                                                    {{ $prj->contact_phone }}
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
                                            <svg class="w-4 h-4 shrink-0 text-slate-700 dark:text-slate-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="stroke: currentColor !important;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <span>{{ __('Call') }}</span>
                                        </a>
                                    @endif

                                    <button type="button" onclick="openAttendanceModal('{{ $prj->id }}')" 
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 min-h-[38px] rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 text-xs sm:text-sm font-bold hover:bg-blue-100 transition cursor-pointer">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        <span>{{ __('Punch Crew') }}</span>
                                    </button>

                                    <button type="button" onclick="openWorkOutputModal('{{ $prj->id }}')" 
                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 min-h-[38px] rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs sm:text-sm font-bold hover:bg-amber-100 transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                                        <span>{{ __('Work Output') }}</span>
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
                <div class="px-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white tracking-tight">{{ __('Active Installation Jobs') }}</h2>
                        <p class="text-xs sm:text-sm text-[#475569] dark:text-slate-400">{{ __('Installation work orders assigned to you for field execution') }}</p>
                    </div>
                </div>

                {{-- Mobile-first Search & Filter Bar --}}
                <div class="space-y-3">
                    <div class="tech-search-bar">
                        <svg class="w-5 h-5 text-[#64748B] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        <input type="text" x-model="jobSearchTerm" placeholder="{{ __('Search job ID, client, site address...') }}" class="tech-search-input">
                        <button type="button" x-show="jobSearchTerm" @click="jobSearchTerm = ''" class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs cursor-pointer">✕</button>
                    </div>

                    {{-- Horizontal Scrolling Filter Chips --}}
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                        <button type="button" @click="jobStatusFilter = 'all'" class="tech-chip" :class="jobStatusFilter === 'all' ? 'active' : ''">{{ __('All Jobs') }}</button>
                        <button type="button" @click="jobStatusFilter = 'pending'" class="tech-chip" :class="jobStatusFilter === 'pending' ? 'active' : ''">{{ __('Pending') }}</button>
                        <button type="button" @click="jobStatusFilter = 'assigned'" class="tech-chip" :class="jobStatusFilter === 'assigned' ? 'active' : ''">{{ __('In Progress') }}</button>
                        <button type="button" @click="jobStatusFilter = 'completed'" class="tech-chip" :class="jobStatusFilter === 'completed' ? 'active' : ''">{{ __('Completed') }}</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @forelse($activeJobs as $job)
                        @php 
                            $lead = $job->quotation?->lead;
                            $statusStyle = match($job->status) {
                                'assigned' => 'tech-badge-in-progress',
                                'completed' => 'tech-badge-completed',
                                'cancelled' => 'tech-badge-cancelled',
                                default => 'tech-badge-assigned'
                            };
                        @endphp
                        <div x-show="(jobStatusFilter === 'all' || jobStatusFilter === '{{ $job->status }}') && (!jobSearchTerm || '{{ strtolower($job->job_no . ' ' . ($lead?->customer_name ?? '') . ' ' . ($lead?->site_address ?? '')) }}'.includes(jobSearchTerm.toLowerCase()))"
                             class="tech-card crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 sm:p-6 shadow-sm space-y-4 flex flex-col justify-between">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs font-bold text-[#1E3A8A] dark:text-blue-400">{{ $job->job_no ?? ('JOB-' . $job->id) }}</span>
                                            <span class="tech-badge {{ $statusStyle }}">
                                                {{ __(ucfirst(str_replace('_', ' ', $job->status))) }}
                                            </span>
                                        </div>
                                        <h3 class="text-base sm:text-lg font-bold text-[#0F172A] dark:text-white mt-1 truncate">{{ $lead?->customer_name ?? __('Installation Site') }}</h3>
                                        <p class="text-xs text-[#64748B] mt-0.5">{{ __('Scheduled') }}: {{ $job->scheduled_at ? \Carbon\Carbon::parse($job->scheduled_at)->translatedFormat('d M Y, h:i A') : $job->created_at->translatedFormat('d M Y') }}</p>
                                    </div>

                                    @if($lead?->phone)
                                        <a href="tel:{{ $lead->phone }}" 
                                           class="tech-icon-btn bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-[#1E3A8A] dark:text-blue-400"
                                           aria-label="{{ __('Call Customer') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        </a>
                                    @endif
                                </div>

                                {{-- Key-Value data display --}}
                                <div class="rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60 px-3 py-1 text-xs">
                                    <div class="tech-kv-row">
                                        <span class="tech-kv-label">{{ __('Site Address') }}</span>
                                        <span class="tech-kv-value">{{ $lead?->site_address ?? $lead?->address ?? __('—') }}</span>
                                    </div>
                                    @if($lead?->phone)
                                        <div class="tech-kv-row">
                                            <span class="tech-kv-label">{{ __('Phone') }}</span>
                                            <span class="tech-kv-value">
                                                <a href="tel:{{ $lead->phone }}" class="text-[#1E3A8A] dark:text-blue-400 font-bold hover:underline">{{ $lead->phone }}</a>
                                            </span>
                                        </div>
                                    @endif
                                    @if($job->installation_notes)
                                        <div class="tech-kv-row" style="border-bottom: none !important;">
                                            <span class="tech-kv-label">{{ __('Work Notes') }}</span>
                                            <span class="tech-kv-value text-left" style="text-align: left !important;">{{ $job->installation_notes }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Mobile Primary Action: JCR Sign-off & Status Update --}}
                            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-3">
                                <a href="{{ route('jcr.create-job', $job) }}" 
                                   class="tech-btn-primary" 
                                   style="background-color: #1E3A8A !important; color: #ffffff !important;">
                                    <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="#ffffff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                    <span class="text-white font-semibold">{{ __('Submit and Complete JCR') }}</span>
                                </a>

                                <form method="POST" action="{{ route('technician.jobs.updateStatus', $job) }}" class="space-y-3">
                                    @csrf
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="tech-label">{{ __('Job Status *') }}</label>
                                            <select name="status" class="tech-input text-xs font-semibold">
                                                <option value="pending" @selected($job->status === 'pending')>{{ __('Pending') }}</option>
                                                <option value="scheduled" @selected($job->status === 'scheduled')>{{ __('Scheduled') }}</option>
                                                <option value="assigned" @selected($job->status === 'assigned')>{{ __('In Progress') }}</option>
                                                <option value="completed" @selected($job->status === 'completed')>{{ __('Completed') }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <div class="flex items-center justify-between mb-1">
                                                <label for="job-notes-{{ $job->id }}" class="tech-label mb-0">{{ __('Update Notes') }}</label>
                                                <x-voice-mic-button target="#job-notes-{{ $job->id }}" />
                                            </div>
                                            <input type="text" id="job-notes-{{ $job->id }}" name="notes" value="{{ $job->installation_notes }}" placeholder="{{ __('Findings or notes...') }}"
                                                   class="tech-input text-xs">
                                        </div>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="tech-btn-secondary w-full sm:w-auto">
                                            <span>{{ __('Save Status') }}</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full rounded-2xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 sm:p-12 text-center text-xs sm:text-sm text-[#64748B]">
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
                        <p class="text-xs sm:text-sm text-slate-400">{{ __('Active field technicians, cable pullers, and helpers') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showAddWorkerModal = true"
                                class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 min-h-[38px] rounded-xl bg-emerald-600 hover:bg-emerald-700 !text-white font-bold text-xs sm:text-sm shadow-xs transition cursor-pointer"
                                style="color: #ffffff !important;">
                            <svg class="w-4 h-4 shrink-0 !text-white" fill="none" stroke="#ffffff" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            <span class="!text-white" style="color: #ffffff !important;">{{ __('Add Worker') }}</span>
                        </button>
                        <button type="button" onclick="openAttendanceModal()"
                                class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 !text-white font-bold text-xs sm:text-sm shadow-xs transition cursor-pointer"
                                style="color: #ffffff !important;">
                            <svg class="w-4 h-4 shrink-0 !text-white" fill="none" stroke="#ffffff" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            <span class="!text-white" style="color: #ffffff !important;">{{ __('Punch Attendance') }}</span>
                        </button>
                        <button type="button" onclick="openWorkOutputModal()"
                                class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 min-h-[38px] rounded-xl bg-amber-500 hover:bg-amber-600 !text-white font-bold text-xs sm:text-sm shadow-xs transition cursor-pointer"
                                style="color: #ffffff !important;">
                            <svg class="w-4 h-4 shrink-0 !text-white" fill="none" stroke="#ffffff" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                            <span class="!text-white" style="color: #ffffff !important;">{{ __('Log Work Output') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Realtime Attendance Status Strip (Avoids Repeated Duplicate Punches Across Technicians) --}}
                @php
                    $markedCount = $workersToday->where('is_already_marked', true)->count();
                    $pendingCount = $workersToday->where('is_already_marked', false)->count();
                @endphp
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">{{ __('Today\'s Roster Status') }}:</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>{{ $markedCount }} {{ __('Logged Today') }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span>{{ $pendingCount }} {{ __('Available to Punch') }}</span>
                        </span>
                    </div>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">
                        {{ __('Workers already punched by any technician can be updated with 1-click.') }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($workersToday as $w)
                        <div class="rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border {{ $w->is_already_marked ? 'border-emerald-300 dark:border-emerald-900/60 bg-emerald-50/15' : 'border-slate-200 dark:border-slate-800' }} p-5 shadow-sm space-y-3.5 flex flex-col justify-between hover:border-blue-400 transition">
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-full border-2 {{ $w->is_already_marked ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' : 'border-blue-500 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-300' }} font-black text-sm flex items-center justify-center shrink-0 shadow-xs">
                                        {{ $w->initials }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-sm font-black text-slate-900 dark:text-white truncate">{{ $w->name }}</h4>
                                        <span class="text-xs text-slate-400 block truncate">{{ $w->role_label }}</span>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $w->is_already_marked ? 'border border-emerald-400 text-emerald-600 bg-emerald-50/70' : $w->status_badge_class }}">
                                        {{ $w->status }}
                                    </span>
                                </div>

                                @if($w->is_already_marked)
                                    <div class="p-2.5 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-xs space-y-1">
                                        <div class="flex items-center justify-between text-[11px] font-bold text-emerald-800 dark:text-emerald-300">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                                <span>{{ __('Already Added Today') }}</span>
                                            </span>
                                            @if($w->check_in_time)
                                                <span class="font-mono text-[10px] text-emerald-600 dark:text-emerald-400">{{ $w->check_in_time }}</span>
                                            @endif
                                        </div>
                                        <p class="text-[10px] text-slate-600 dark:text-slate-300 leading-tight">
                                            {{ __('Logged by:') }} <strong class="text-slate-800 dark:text-slate-100">{{ $w->logger_name }}</strong><br>
                                            <span class="text-slate-500">{{ __('Site:') }} {{ Str::limit($w->project_name, 32) }}</span>
                                        </p>
                                    </div>
                                @else
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
                                @endif
                            </div>

                            <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                                <span class="text-[11px] text-slate-400">{{ __('On-Duty Today') }}</span>

                                @if($w->is_already_marked)
                                    <button type="button" onclick="openAttendanceModal(null, '{{ $w->id }}')" 
                                            class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 hover:underline cursor-pointer"
                                            title="{{ __('Click to update shift or site for this worker') }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                        <span>{{ __('Update Shift') }} &rarr;</span>
                                    </button>
                                @else
                                    <button type="button" onclick="openAttendanceModal(null, '{{ $w->id }}')" 
                                            class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                        {{ __('Punch Shift') }} &rarr;
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-3 text-center">
                    <a href="{{ route('workers.attendance.index') }}" class="text-xs sm:text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:underline">
                        {{ __('View Complete Worker Attendance Register') }} &rarr;
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
                            <button type="button" onclick="openHardwareScannerModal('auto', {{ Js::from($scannerJobContext) }})"
                                    title="{{ __('Open Camera Scanner') }}"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 !text-white font-bold text-xs sm:text-sm shadow-sm transition cursor-pointer"
                                    style="color: #ffffff !important;">
                                <svg class="w-4 h-4 shrink-0 !text-white" fill="none" stroke="#ffffff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="!text-white font-bold" style="color: #ffffff !important;">{{ __('Launch Hardware Scanner') }}</span>
                                <span class="sr-only">{{ __('Open Camera Scanner') }}</span>
                            </button>

                            <div class="w-full sm:max-w-xs flex items-center gap-1.5">
                                <input type="text" id="techScanCodeInput" x-model="scanCodeInput" placeholder="{{ __('Type serial number...') }}" 
                                       @keydown.enter="if(scanCodeInput.trim()) { handleTechScannedCode(scanCodeInput.trim()); scanCodeInput = ''; }"
                                       class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 px-3.5 py-2 min-h-[38px] text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <x-voice-mic-button target="#techScanCodeInput" label="" class="!h-[38px] !px-2.5 !rounded-xl" />
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

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- TAB PANE 7: QUOTATIONS & PROPOSALS (Laptop & Mobile)               --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <div x-show="currentTab === 'quotations'" class="space-y-4 lg:space-y-6">
                {{-- Pane Header & Actions --}}
                <div class="rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm">
                    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                </div>
                                <h2 class="text-base sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Client Quotations & Estimates') }}</h2>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                {{ __('Create on-site proposals, download formatted PDFs, and share via WhatsApp or Email directly with clients.') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2.5 flex-wrap w-full lg:w-auto">
                            {{-- Button 1: Quote Existing Client --}}
                            <button type="button" @click="showExistingQuoteModal = true"
                                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2.5 min-h-[42px] rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 font-bold text-xs sm:text-sm transition shadow-2xs hover:shadow-xs active:scale-[0.98] cursor-pointer border border-slate-200 dark:border-slate-700">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                                <span>{{ __('Quote Existing Client') }}</span>
                            </button>

                            {{-- Button 2: New Client & Quote --}}
                            <button type="button" @click="showNewLeadQuoteModal = true"
                                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2.5 min-h-[42px] rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] !text-white font-bold text-xs sm:text-sm shadow-sm transition cursor-pointer"
                                    style="color: #ffffff !important; background-color: #059669 !important;">
                                <svg class="w-4 h-4 !text-white shrink-0" fill="none" stroke="#ffffff" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
                                <span class="!text-white" style="color: #ffffff !important;">{{ __('New Client & Quote') }}</span>
                            </button>

                            {{-- Button 3: All Clients browse --}}
                            <a href="{{ route('quotations.create-general') }}" 
                               class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 min-h-[42px] rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold transition shadow-2xs group"
                               title="{{ __('Browse All Leads & Clients') }}">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
                                <span>{{ __('All Clients') }}</span>
                                <svg class="w-3.5 h-3.5 text-slate-400 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Quotations Table / List --}}
                <div class="rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            {{ __('Recent Quotations') }} ({{ isset($quotations) ? $quotations->count() : 0 }})
                        </h3>
                        <span class="text-[11px] text-slate-400">{{ __('Showing latest proposals') }}</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs divide-y divide-slate-100 dark:divide-slate-800">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 uppercase tracking-wider text-[10px]">
                                    <th class="py-3 px-4">{{ __('Quotation #') }}</th>
                                    <th class="py-3 px-4">{{ __('Customer / Site') }}</th>
                                    <th class="py-3 px-4">{{ __('Items') }}</th>
                                    <th class="py-3 px-4">{{ __('Approval & Status') }}</th>
                                    <th class="py-3 px-4 text-right">{{ __('Customer Sharing') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($quotations as $q)
                                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                        <td class="py-3 px-4 font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                            <a href="{{ route('quotations.show', $q) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ $q->quotation_no ?? ('QUO-' . $q->id) }}
                                            </a>
                                            <div class="text-[10px] text-slate-400 mt-0.5">
                                                {{ $q->quotation_date ? $q->quotation_date->format('d M Y') : $q->created_at->format('d M Y') }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $q->lead?->customer_name ?? __('Customer') }}</div>
                                            @if($q->lead?->phone)
                                                <a href="tel:{{ $q->lead->phone }}" class="text-[11px] text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 block mt-0.5">
                                                    {{ $q->lead->phone }}
                                                </a>
                                            @endif
                                            @if($q->lead?->site_address)
                                                <div class="text-[10px] text-slate-400 truncate max-w-xs">{{ $q->lead->site_address }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold">
                                                {{ $q->items->count() }} {{ __('items') }}
                                            </span>
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 font-medium">
                                                {{ __('Format') }}: <span class="font-bold text-slate-700 dark:text-slate-300">{{ $q->pdf_format_name }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            @if($q->status === 'pending_approval')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                    <svg class="w-3.5 h-3.5 text-amber-500 animate-pulse shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span>{{ __('Pending Admin Approval') }}</span>
                                                </span>
                                            @elseif($q->status === 'approved')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span>{{ __('Approved - Ready to Share') }}</span>
                                                </span>
                                            @elseif($q->status === 'sent')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                    <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                                    <span>{{ __('Sent to Customer') }}</span>
                                                </span>
                                            @elseif($q->status === 'accepted')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-900 dark:bg-emerald-900/60 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700">
                                                    <svg class="w-3.5 h-3.5 text-emerald-700 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                                    <span>{{ __('Accepted by Client') }}</span>
                                                </span>
                                            @elseif($q->status === 'rejected')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                    <span>{{ __('Rejected') }}</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                    <span>{{ ucfirst($q->status) }}</span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center gap-2 justify-end">
                                                {{-- View Details --}}
                                                <a href="{{ route('quotations.show', $q) }}" 
                                                   class="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/60 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-bold text-xs inline-flex items-center gap-1.5 transition group"
                                                   title="{{ __('View Quotation Scope & Details') }}">
                                                    <span>{{ __('View') }}</span>
                                                    <svg class="w-3.5 h-3.5 shrink-0 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                                </a>

                                                @if($q->status === 'pending_approval')
                                                    {{-- Locked sharing pending admin approval --}}
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 text-xs font-semibold"
                                                          title="{{ __('Locked: Admin approval is required before sending quotation to customer') }}">
                                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                                        <span>{{ __('Awaiting Approval') }}</span>
                                                    </span>
                                                @else
                                                    {{-- Quick PDF Download in approved/active format --}}
                                                    <a href="{{ route('quotations.pdf', [$q, $q->pdf_format ?? '1']) }}" 
                                                       class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:text-blue-600 hover:border-blue-300 transition" 
                                                       title="{{ __('Download Formatted PDF (:name)', ['name' => $q->pdf_format_name]) }}">
                                                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 12L12 16.5m0 0L16.5 12M12 16.5V3"/></svg>
                                                    </a>

                                                    {{-- Quick WhatsApp Share in approved format --}}
                                                    <a href="{{ route('quotations.send-whatsapp', ['quotation' => $q, 'format' => $q->pdf_format ?? '1']) }}" 
                                                       target="_blank"
                                                       class="p-1.5 rounded-lg border border-emerald-200 dark:border-emerald-800/80 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 transition" 
                                                       title="{{ __('Send Quote via WhatsApp (:name)', ['name' => $q->pdf_format_name]) }}">
                                                        <x-icon.whatsapp :badge="false" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-400">
                                            <div class="max-w-sm mx-auto space-y-2">
                                                <svg class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                                <p class="font-bold text-slate-700 dark:text-slate-300 text-sm">{{ __('No quotations created yet') }}</p>
                                                <p class="text-xs text-slate-400">{{ __('Create a quote for an on-site customer or existing lead to share professional estimates.') }}</p>
                                                <div class="pt-2 flex items-center justify-center gap-2">
                                                    <button type="button" @click="showNewLeadQuoteModal = true" class="btn-primary text-xs py-1.5 px-3">
                                                        {{ __('Create First Quotation') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- TAB PANE 8: TRAVEL ALLOWANCES & FIELD EXPENSES (Laptop & Mobile)    --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <div x-show="currentTab === 'allowances'" class="space-y-4 lg:space-y-6">
                {{-- Pane Header & Actions --}}
                <div class="rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm">
                    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Travel Allowances & On-Site Claims') }}</span>
                            </div>
                            <h2 class="text-base sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">
                                {{ __('Site Work Allowances & Fuel KM') }}
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                {{ __('Submit site fuel travel (KM), tools, meals or operational expenses. Approved allowances are verified by Admin and added directly to your monthly salary slip.') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2.5 flex-wrap w-full sm:w-auto">
                            <button type="button" @click="showAddAllowanceModal = true"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold text-xs sm:text-sm shadow-sm transition cursor-pointer shrink-0">
                                <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                <span class="text-white font-bold">{{ __('Add Travel / Work Allowance') }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Allowance KPI Summary Strip --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                    {{-- Approved this month --}}
                    <div class="p-4 rounded-2xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">{{ __('Approved This Month') }}</span>
                        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 font-mono">
                            ₹{{ number_format($approvedAllowancesMonth ?? 0, 2) }}
                        </div>
                        <div class="text-[11px] text-slate-400 mt-1.5 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>{{ __('Integrated into Monthly Salary') }}</span>
                        </div>
                    </div>

                    {{-- Pending Review --}}
                    <div class="p-4 rounded-2xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">{{ __('Pending Admin Review') }}</span>
                        <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 font-mono">
                            {{ $pendingClaimsCount ?? 0 }} <span class="text-xs font-bold text-slate-500">({{ '₹' . number_format($pendingClaimsAmount ?? 0, 2) }})</span>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-1.5 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            <span>{{ __('Awaiting management approval') }}</span>
                        </div>
                    </div>

                    {{-- Total Travel KM Logged --}}
                    <div class="p-4 rounded-2xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">{{ __('Fuel Travel Logged') }}</span>
                        <div class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-1 font-mono">
                            {{ number_format($totalKmLoggedMonth ?? 0, 1) }} <span class="text-xs font-bold text-slate-500">KM</span>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-1.5 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                            <span>{{ __('Standard rate ₹6.00 / KM') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Allowance Claims History Table --}}
                <div class="rounded-2xl sm:rounded-3xl crm-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
                        <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            {{ __('My Allowance & Travel Ledger (:count)', ['count' => isset($myExpenseClaims) ? $myExpenseClaims->count() : 0]) }}
                        </h3>
                        <span class="text-[11px] text-slate-400">
                            {{ __('Amounts updated by Admin reflect here live') }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-400 font-bold uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="py-3 px-4 sm:px-6">{{ __('Date & Claim #') }}</th>
                                    <th class="py-3 px-4">{{ __('Category & Purpose') }}</th>
                                    <th class="py-3 px-4">{{ __('Route / KM Calculation') }}</th>
                                    <th class="py-3 px-4 text-right">{{ __('Claim Amount') }}</th>
                                    <th class="py-3 px-4">{{ __('Approval & Salary Status') }}</th>
                                    <th class="py-3 px-4 text-center">{{ __('Receipt') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($myExpenseClaims ?? [] as $claim)
                                    @php
                                        $isTargetClaim = (request('claim_no') && request('claim_no') == $claim->claim_no) || (request('claim_id') && request('claim_id') == $claim->id);
                                    @endphp
                                    <tr id="tech-claim-{{ $claim->id }}" data-claim-no="{{ $claim->claim_no }}" class="{{ $isTargetClaim ? 'bg-amber-50/90 dark:bg-amber-950/40 ring-2 ring-amber-500 rounded-lg' : 'hover:bg-slate-50/50 dark:hover:bg-slate-800/30' }} transition">
                                        <td class="py-3.5 px-4 sm:px-6 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <div class="font-bold text-slate-900 dark:text-white">
                                                    {{ $claim->expense_date ? $claim->expense_date->format('d M Y') : '—' }}
                                                </div>
                                                @if($isTargetClaim)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-500 text-white animate-pulse">
                                                        {{ __('Target Claim') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="font-mono text-[11px] text-blue-600 dark:text-blue-400 font-bold">
                                                #{{ $claim->claim_no }}
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $claim->category_badge_class }}">
                                                {{ $claim->category_label }}
                                            </span>
                                            <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 line-clamp-2">
                                                {{ $claim->description }}
                                            </div>
                                            @if($claim->site)
                                                <div class="text-[11px] font-bold text-blue-600 dark:text-blue-400 mt-1 flex items-center gap-1">
                                                    <span>📍 {{ $claim->site->name }}</span>
                                                    @if($claim->site->client_name)
                                                        <span class="text-slate-400 font-normal">({{ $claim->site->client_name }})</span>
                                                    @endif
                                                </div>
                                                @if($claim->site->address)
                                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 line-clamp-1">{{ $claim->site->address }}</div>
                                                @endif
                                            @elseif($claim->project)
                                                <div class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 mt-1 flex items-center gap-1">
                                                    <span>🏢 {{ $claim->project->title }}</span>
                                                    @if($claim->project->company_name)
                                                        <span class="text-slate-400 font-normal">({{ $claim->project->company_name }})</span>
                                                    @endif
                                                </div>
                                                @if($claim->project->site_address)
                                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 line-clamp-1">{{ $claim->project->site_address }}</div>
                                                @endif
                                            @elseif($claim->installationJob)
                                                <div class="text-[10px] text-slate-400 mt-0.5">
                                                    {{ __('Job:') }} {{ $claim->installationJob->job_no }} ({{ $claim->installationJob->quotation?->lead?->customer_name }})
                                                </div>
                                            @endif
                                            @if($claim->admin_notes)
                                                <div class="text-[10px] text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/40 px-2 py-1 rounded mt-1 border border-blue-200 dark:border-blue-900/40">
                                                    <strong>{{ __('Admin Note:') }}</strong> {{ $claim->admin_notes }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            @if($claim->travel_distance_km > 0)
                                                <div class="font-bold text-purple-700 dark:text-purple-300">
                                                    {{ $claim->travel_distance_km }} KM
                                                    <span class="text-[10px] font-normal text-slate-400">(@ ₹{{ number_format($claim->rate_per_km, 2) }}/km)</span>
                                                </div>
                                                @if($claim->travel_from || $claim->travel_to)
                                                    <div class="text-[10px] text-slate-400">
                                                        {{ $claim->travel_from ?: 'Site' }} &rarr; {{ $claim->travel_to ?: 'Site' }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-slate-400 text-[11px]">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <div class="font-black text-sm text-slate-900 dark:text-white font-mono">
                                                ₹{{ number_format((float) $claim->amount, 2) }}
                                            </div>
                                            @if($claim->actioned_by && $claim->status !== 'rejected')
                                                <span class="text-[9px] text-emerald-600 dark:text-emerald-400 block font-semibold">
                                                    {{ __('Verified by Admin') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $claim->status_badge_class }}">
                                                {{ $claim->status_label }}
                                            </span>
                                            @if($claim->status === 'approved')
                                                <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold mt-1">
                                                    {{ __('Added to upcoming Salary') }}
                                                </div>
                                            @elseif($claim->status === 'paid')
                                                <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold mt-1">
                                                    {{ $claim->payment_method === 'payroll_addition' ? __('Credited in Salary Slip') : __('Disbursed via ') . ucfirst($claim->payment_method) }}
                                                </div>
                                            @elseif($claim->status === 'rejected' && $claim->rejection_reason)
                                                <div class="text-[10px] text-rose-600 dark:text-rose-400 mt-1 max-w-xs truncate" title="{{ $claim->rejection_reason }}">
                                                    {{ __('Reason: ') . $claim->rejection_reason }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            @if($claim->receipt_path)
                                                <a href="{{ asset('storage/' . $claim->receipt_path) }}" target="_blank" class="text-blue-600 dark:text-blue-400 font-bold hover:underline inline-flex items-center gap-1 text-[11px]">
                                                    <span>{{ __('View Proof') }}</span>
                                                </a>
                                            @else
                                                <span class="text-slate-400 text-[11px]">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-400">
                                            <div class="max-w-sm mx-auto space-y-2">
                                                <svg class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                <p class="font-bold text-slate-700 dark:text-slate-300 text-sm">{{ __('No allowance claims logged yet') }}</p>
                                                <p class="text-xs text-slate-400">{{ __('Log fuel travel KM, parking, tolls or emergency materials for on-site client jobs.') }}</p>
                                                <div class="pt-2 flex items-center justify-center gap-2">
                                                    <button type="button" @click="showAddAllowanceModal = true" class="btn-primary text-xs py-1.5 px-3 inline-flex items-center gap-1.5">
                                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                        <span>{{ __('Add Your First Claim') }}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- 4. ULTRA-CRISP MOBILE BOTTOM NAVIGATION (Height: 68px + safe-area, 5 core tabs) --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <nav aria-label="{{ __('Field sections') }}" class="lg:hidden fixed bottom-0 inset-x-0 z-50 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 px-3 pt-2 pb-[max(0.6rem,env(safe-area-inset-bottom))] shadow-[0_-4px_20px_rgba(15,23,42,0.08)]">
            <div class="w-full max-w-md mx-auto flex items-center justify-between gap-1">
                
                {{-- Home / Overview Tab --}}
                <button type="button" @click="switchTab('overview')"
                        :aria-current="currentTab === 'overview' ? 'page' : 'false'"
                        class="flex-1 h-auto min-h-[48px] py-1 px-1 rounded-xl flex flex-col items-center justify-center gap-1 transition cursor-pointer select-none tech-nav-tab"
                        :class="currentTab === 'overview' ? 'text-[#1E3A8A] dark:text-blue-400 font-bold active' : 'text-[#64748B] hover:text-slate-800 dark:hover:text-slate-200 inactive'">
                    <div class="relative">
                        <svg class="w-6 h-6 pointer-events-none shrink-0" :fill="currentTab === 'overview' ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                    </div>
                    <span class="text-[11px] font-medium leading-tight pointer-events-none">{{ __('Home') }}</span>
                </button>

                {{-- Projects Tab --}}
                <button type="button" @click="switchTab('projects')"
                        :aria-current="currentTab === 'projects' ? 'page' : 'false'"
                        class="flex-1 h-auto min-h-[48px] py-1 px-1 rounded-xl flex flex-col items-center justify-center gap-1 transition cursor-pointer select-none tech-nav-tab"
                        :class="currentTab === 'projects' ? 'text-[#1E3A8A] dark:text-blue-400 font-bold active' : 'text-[#64748B] hover:text-slate-800 dark:hover:text-slate-200 inactive'">
                    <div class="relative">
                        <svg class="w-6 h-6 pointer-events-none shrink-0" :fill="currentTab === 'projects' ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21"/></svg>
                        @if($assignedProjects->count() > 0)
                            <span class="absolute -top-1 -right-2 px-1.5 py-0.2 min-w-[18px] h-[18px] rounded-full bg-[#1E3A8A] text-white text-[10px] font-bold flex items-center justify-center">
                                {{ $assignedProjects->count() }}
                            </span>
                        @endif
                    </div>
                    <span class="text-[11px] font-medium leading-tight pointer-events-none">{{ __('Projects') }}</span>
                </button>

                {{-- Jobs Tab --}}
                <button type="button" @click="switchTab('jobs')"
                        :aria-current="currentTab === 'jobs' ? 'page' : 'false'"
                        class="flex-1 h-auto min-h-[48px] py-1 px-1 rounded-xl flex flex-col items-center justify-center gap-1 transition cursor-pointer select-none tech-nav-tab"
                        :class="currentTab === 'jobs' ? 'text-[#1E3A8A] dark:text-blue-400 font-bold active' : 'text-[#64748B] hover:text-slate-800 dark:hover:text-slate-200 inactive'">
                    <div class="relative">
                        <svg class="w-6 h-6 pointer-events-none shrink-0" :fill="currentTab === 'jobs' ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.204a4.5 4.5 0 00-6.364-6.364l-.707.707a4.5 4.5 0 006.364 6.364l.707-.707z"/></svg>
                        @if($activeJobs->count() > 0)
                            <span class="absolute -top-1 -right-2 px-1.5 py-0.2 min-w-[18px] h-[18px] rounded-full bg-[#F59E0B] text-[#78350F] text-[10px] font-bold flex items-center justify-center">
                                {{ $activeJobs->count() }}
                            </span>
                        @endif
                    </div>
                    <span class="text-[11px] font-medium leading-tight pointer-events-none">{{ __('Jobs') }}</span>
                </button>

                {{-- Surveys Tab --}}
                <button type="button" @click="switchTab('surveys')"
                        :aria-current="currentTab === 'surveys' ? 'page' : 'false'"
                        class="flex-1 h-auto min-h-[48px] py-1 px-1 rounded-xl flex flex-col items-center justify-center gap-1 transition cursor-pointer select-none tech-nav-tab"
                        :class="currentTab === 'surveys' ? 'text-[#1E3A8A] dark:text-blue-400 font-bold active' : 'text-[#64748B] hover:text-slate-800 dark:hover:text-slate-200 inactive'">
                    <div class="relative">
                        <svg class="w-6 h-6 pointer-events-none shrink-0" :fill="currentTab === 'surveys' ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                        @if($activeSurveys->count() > 0)
                            <span class="absolute -top-1 -right-2 px-1.5 py-0.2 min-w-[18px] h-[18px] rounded-full bg-[#16A34A] text-white text-[10px] font-bold flex items-center justify-center">
                                {{ $activeSurveys->count() }}
                            </span>
                        @endif
                    </div>
                    <span class="text-[11px] font-medium leading-tight pointer-events-none">{{ __('Surveys') }}</span>
                </button>

                {{-- Allowances / Field Claims Tab --}}
                <button type="button" @click="switchTab('allowances')"
                        :aria-current="currentTab === 'allowances' ? 'page' : 'false'"
                        class="flex-1 h-auto min-h-[48px] py-1 px-1 rounded-xl flex flex-col items-center justify-center gap-1 transition cursor-pointer select-none relative tech-nav-tab"
                        :class="currentTab === 'allowances' ? 'text-[#1E3A8A] dark:text-blue-400 font-bold active' : 'text-[#64748B] hover:text-slate-800 dark:hover:text-slate-200 inactive'">
                    <div class="relative">
                        <svg class="w-6 h-6 pointer-events-none shrink-0" :fill="currentTab === 'allowances' ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185zM9.75 9h.008v.008H9.75V9zm4.5 6h.008v.008h-.008V15z"/></svg>
                        @if(isset($pendingClaimsCount) && $pendingClaimsCount > 0)
                            <span class="absolute -top-1 -right-2 px-1.5 py-0.2 min-w-[18px] h-[18px] rounded-full bg-[#DC2626] text-white text-[10px] font-bold flex items-center justify-center">
                                {{ $pendingClaimsCount }}
                            </span>
                        @endif
                    </div>
                    <span class="text-[11px] font-medium leading-tight pointer-events-none">{{ __('Claims') }}</span>
                </button>

            </div>
        </nav>

    {{-- HARDWARE BARCODE SCANNER COMPONENT (Modal provided globally via app layout) --}}
    <x-barcode-scanner modalId="tech-barcode-modal" :title="__('Hardware Scanner')" />

    {{-- SPLIT GUIDED WORKER ATTENDANCE MODAL COMPONENT --}}
    <x-mark-attendance-modal :workers="$activeWorkers" :projects="$activeProjects" :today-attendances="$todayAttendanceByWorkerId ?? null" :assigned-project-ids="$assignedProjectIds ?? []" :default-project-id="$assignedProjects->first()?->id" />

    {{-- TECHNICIAN SITE CHECK-IN MODAL --}}
    <div id="techSiteCheckInModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('My Attendance (Full Day)') }}</h3>
                        <p class="text-[11px] text-slate-400">{{ __('Record full day on-site attendance for today') }}</p>
                    </div>
                </div>
                <x-close-button onclick="document.getElementById('techSiteCheckInModal').classList.add('hidden')" />
            </div>

            <form method="POST" action="{{ route('attendance.clock-in') }}" class="space-y-4 mt-4">
                @csrf
                <input type="hidden" name="location_type" value="on_site">

                <div>
                    <label for="tech-checkin-project" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Select Project Site *') }}</label>
                    <select id="tech-checkin-project" name="project_id" class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                        <option value="">{{ __('General On-Site / Field Engineering') }}</option>
                        @if($assignedProjects->isNotEmpty())
                            <optgroup label="⭐ {{ __('My Assigned Project Sites') }} ({{ $assignedProjects->count() }})">
                                @foreach($assignedProjects as $p)
                                    @php
                                        $siteLoc = $p->site?->name ? ($p->site->name . ($p->site->address ? ' — ' . Str::limit($p->site->address, 28) : '')) : ($p->site_address ?: ($p->customer_name ?? __('Site')));
                                    @endphp
                                    <option value="{{ $p->id }}" @selected(($primaryPrj->id ?? null) === $p->id)>
                                        ⭐ [Assigned] {{ $p->title }} &mdash; 📍 {{ $siteLoc }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                        @php
                            $assignedIds = $assignedProjects->pluck('id')->all();
                            $otherActiveProjects = $activeProjects->filter(fn($p) => !in_array($p->id, $assignedIds));
                        @endphp
                        @if($otherActiveProjects->isNotEmpty())
                            <optgroup label="🏢 {{ __('All Project Sites & Locations') }} ({{ $otherActiveProjects->count() }})">
                                @foreach($otherActiveProjects as $p)
                                    @php
                                        $siteLoc = $p->site?->name ? ($p->site->name . ($p->site->address ? ' — ' . Str::limit($p->site->address, 28) : '')) : ($p->site_address ?: ($p->customer_name ?? __('Site')));
                                    @endphp
                                    <option value="{{ $p->id }}">
                                        {{ $p->title }} &mdash; 📍 {{ $siteLoc }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="tech-checkin-notes" class="block text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Work Planned / Site Notes (Optional)') }}</label>
                        <x-voice-mic-button target="#tech-checkin-notes" />
                    </div>
                    <textarea id="tech-checkin-notes" name="notes" rows="2" placeholder="{{ __('e.g. CCTV wiring testing, NVR configuration...') }}" class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-between">
                    <button type="button" onclick="document.getElementById('techSiteCheckInModal').classList.add('hidden')" class="btn-secondary text-xs">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn-primary text-xs !bg-emerald-600 hover:!bg-emerald-700 !text-white flex items-center gap-1.5 shadow-xs" style="color: #ffffff !important;">
                        <svg class="w-4 h-4 !text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="!text-white font-bold" style="color: #ffffff !important;">{{ __('Confirm Full Day Attendance') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- TECHNICIAN ADD FIELD CREW WORKER MODAL (NO WAGE / COST INPUTS) --}}
    <div x-show="showAddWorkerModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto">
        <div @click.away="showAddWorkerModal = false" class="bg-white dark:bg-[#0f172a] rounded-t-2xl sm:rounded-2xl max-w-lg w-full p-4 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-2xl max-h-[92vh] sm:max-h-[90vh] overflow-y-auto my-0 sm:my-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-8 h-8 shrink-0 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ __('Register New Field Worker') }}</h3>
                        <p class="text-[11px] text-slate-400 truncate">{{ __('Add a worker to field crew for site attendance and tasks') }}</p>
                    </div>
                </div>
                <x-close-button @click="showAddWorkerModal = false" class="shrink-0" />
            </div>

            <form method="POST" action="{{ route('workers.store') }}" enctype="multipart/form-data" class="space-y-4 mt-4">
                @csrf
                <input type="hidden" name="is_active" value="1">

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="worker_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Worker Full Name *') }}</label>
                        <x-voice-mic-button target="#worker_name" />
                    </div>
                    <input type="text" id="worker_name" name="name" required placeholder="e.g. Ramesh Kumar" class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="worker_phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Phone Number *') }}</label>
                        <x-voice-mic-button target="#worker_phone" />
                    </div>
                    <input type="tel" id="worker_phone" name="phone" required placeholder="e.g. 9876543210" class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Primary Skill / Trade') }}</label>
                    <select name="skills" class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                        <option value="General assistant">{{ __('General assistant') }}</option>
                        <option value="Cabling technician">{{ __('Cabling technician') }}</option>
                        <option value="CCTV installation">{{ __('CCTV installation') }}</option>
                        <option value="Senior technician">{{ __('Senior technician') }}</option>
                        <option value="Helper">{{ __('Helper') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Worker Profile Photo (Optional)') }}</label>
                    <input type="file" name="photo" accept="image/*" class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-slate-800 dark:text-slate-200 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="worker_notes" class="block text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Notes / Identity Details (Optional)') }}</label>
                        <x-voice-mic-button target="#worker_notes" />
                    </div>
                    <textarea id="worker_notes" name="notes" rows="2" placeholder="{{ __('e.g. Aadhaar / emergency contact / remarks...') }}" class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-between gap-2">
                    <button type="button" @click="showAddWorkerModal = false" class="btn-secondary text-xs flex-1 sm:flex-initial justify-center">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn-primary text-xs !bg-emerald-600 hover:!bg-emerald-700 !text-white flex-1 sm:flex-initial flex items-center justify-center gap-1.5 shadow-xs" style="color: #ffffff !important;">
                        <svg class="w-4 h-4 !text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span class="!text-white font-bold" style="color: #ffffff !important;">{{ __('Save Field Worker') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- QUOTE EXISTING CLIENT MODAL --}}
    <div x-show="showExistingQuoteModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto">
        <div @click.away="showExistingQuoteModal = false" class="bg-white dark:bg-[#0f172a] rounded-t-2xl sm:rounded-2xl max-w-md w-full p-4 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-2xl max-h-[92vh] sm:max-h-[90vh] overflow-y-auto my-0 sm:my-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-8 h-8 shrink-0 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ __('Quote Existing Client') }}</h3>
                        <p class="text-[11px] text-slate-400 truncate">{{ __('Select an existing registered client or lead') }}</p>
                    </div>
                </div>
                <x-close-button @click="showExistingQuoteModal = false" class="shrink-0" />
            </div>

            <div class="space-y-4 mt-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Select Customer / Lead *') }}</label>
                    <select x-model="selectedLeadIdForQuote" class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                        <option value="">{{ __('-- Select Customer --') }}</option>
                        @foreach($leads as $lead)
                            <option value="{{ $lead->id }}">{{ $lead->customer_name }} — {{ $lead->phone }} ({{ $lead->company_name ?: __('Individual') }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-700/60 text-[11px] text-slate-500 dark:text-slate-400">
                    {{ __('Tip: If you do not see the customer in this list, click "All Clients" or add them as a new customer.') }}
                </div>

                <div class="pt-3 flex items-center justify-between gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showExistingQuoteModal = false" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-semibold transition cursor-pointer flex-1 sm:flex-initial text-center justify-center">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" 
                            :disabled="!selectedLeadIdForQuote"
                            @click="if(selectedLeadIdForQuote) { window.location.href = '/leads/' + selectedLeadIdForQuote + '/quote'; }"
                            class="inline-flex items-center justify-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs sm:text-sm shadow-sm transition cursor-pointer flex-1 sm:flex-initial" 
                            style="color: #ffffff !important; background-color: #2563eb !important;">
                        <span class="text-white font-bold" style="color: #ffffff !important;">{{ __('Proceed to Quotation') }}</span>
                        <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="#ffffff" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- REGISTER NEW CLIENT & CREATE QUOTE MODAL --}}
    <div x-show="showNewLeadQuoteModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto">
        <div @click.away="showNewLeadQuoteModal = false" class="bg-white dark:bg-[#0f172a] rounded-t-2xl sm:rounded-2xl max-w-lg w-full p-4 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-2xl max-h-[92vh] sm:max-h-[90vh] overflow-y-auto my-0 sm:my-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-8 h-8 shrink-0 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2m12-10a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ __('New Client & Quotation Builder') }}</h3>
                        <p class="text-[11px] text-slate-400 truncate">{{ __('Enter client details to create lead and launch quotation') }}</p>
                    </div>
                </div>
                <x-close-button @click="showNewLeadQuoteModal = false" class="shrink-0" />
            </div>

            <form method="POST" action="{{ route('leads.store') }}" class="space-y-4 mt-4">
                @csrf
                <input type="hidden" name="for_quote" value="1">
                <input type="hidden" name="return_to" value="quotation">
                <input type="hidden" name="source" value="field_technician">

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="lead_customer_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Customer / Contact Name *') }}</label>
                        <x-voice-mic-button target="#lead_customer_name" />
                    </div>
                    <input type="text" id="lead_customer_name" name="customer_name" required placeholder="e.g. Rajesh Sharma" class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="lead_phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Phone Number (WhatsApp) *') }}</label>
                            <x-voice-mic-button target="#lead_phone" />
                        </div>
                        <input type="tel" id="lead_phone" name="phone" required placeholder="e.g. 9876543210" class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="lead_email" class="block text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Email Address') }}</label>
                            <x-voice-mic-button target="#lead_email" />
                        </div>
                        <input type="email" id="lead_email" name="email" placeholder="e.g. rajesh@example.com" class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="lead_company_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Company / Organization (Optional)') }}</label>
                        <x-voice-mic-button target="#lead_company_name" />
                    </div>
                    <input type="text" id="lead_company_name" name="company_name" placeholder="e.g. Apex Security Solutions Pvt Ltd" class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="lead_site_address" class="block text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Installation Facility / Site Address') }}</label>
                        <x-voice-mic-button target="#lead_site_address" />
                    </div>
                    <textarea id="lead_site_address" name="site_address" rows="2" placeholder="e.g. #14, 2nd Main Road, Industrial Estate, Bengaluru" class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200"></textarea>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="lead_notes" class="block text-xs font-bold text-slate-700 dark:text-slate-300">{{ __('Project Requirement / Client Scope Notes') }}</label>
                        <x-voice-mic-button target="#lead_notes" />
                    </div>
                    <textarea id="lead_notes" name="notes" rows="2" placeholder="e.g. 8-channel IP CCTV setup with 2TB HDD, cabling estimate 120m..." class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-between gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showNewLeadQuoteModal = false" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-semibold transition cursor-pointer flex-1 sm:flex-initial text-center justify-center">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" 
                            class="inline-flex items-center justify-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-bold text-xs sm:text-sm shadow-sm transition cursor-pointer flex-1 sm:flex-initial" 
                            style="color: #ffffff !important; background-color: #2563eb !important;">
                        <span class="text-white font-bold" style="color: #ffffff !important;">{{ __('Create & Quote') }}</span>
                        <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="#ffffff" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- TECHNICIAN ADD ALLOWANCE / TRAVEL EXPENSE MODAL --}}
    <div x-show="showAddAllowanceModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="showAddAllowanceModal = false" class="bg-white dark:bg-[#0f172a] rounded-t-2xl sm:rounded-2xl max-w-lg w-full p-4 sm:p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4 max-h-[92vh] sm:max-h-[90vh] overflow-y-auto my-0 sm:my-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-8 h-8 shrink-0 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ __('Log Travel & Site Work Allowance') }}</h3>
                        <p class="text-[11px] text-slate-400 truncate">{{ __('Submitted directly to Admin for approval and salary addition') }}</p>
                    </div>
                </div>
                <x-close-button @click="showAddAllowanceModal = false" class="shrink-0" />
            </div>

            <form action="{{ route('finance.expenses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="return_to" value="technician_dashboard">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- Category --}}
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">{{ __('Expense Category *') }}</label>
                        <select name="expense_category" id="techCategorySelect" onchange="toggleTechKm(this.value)" required class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                            <option value="fuel_travel">{{ __('Fuel & Travel (KM Calculation)') }}</option>
                            <option value="hardware_tools">{{ __('Hardware Tools & Cables') }}</option>
                            <option value="food_lodging">{{ __('Meals & On-Site Allowance') }}</option>
                            <option value="toll_parking">{{ __('Tolls & Parking Fees') }}</option>
                            <option value="emergency_materials">{{ __('Emergency Site Supplies') }}</option>
                            <option value="other">{{ __('Other Work Expense') }}</option>
                        </select>
                    </div>

                    {{-- Expense Date --}}
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">{{ __('Expense Date *') }}</label>
                        <input type="date" name="expense_date" required value="{{ now()->toDateString() }}" 
                               class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                    </div>
                </div>

                {{-- Interactive KM Calculator Strip --}}
                <div id="techKmSection" class="p-3.5 rounded-xl bg-purple-50/70 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-900/50 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-purple-900 dark:text-purple-300 flex items-center gap-1.5">
                            {{ __('Smart Route & KM Calculator') }}
                        </span>
                        <span class="text-[11px] text-purple-700 dark:text-purple-400 font-semibold">{{ __('Standard: ₹6.00 / KM') }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="techTravelFrom" class="text-[11px] font-semibold text-purple-800 dark:text-purple-300">{{ __('From Location') }}</label>
                                <x-voice-mic-button target="#techTravelFrom" />
                            </div>
                            <input type="text" id="techTravelFrom" name="travel_from" placeholder="{{ __('From (e.g. Office / Home)') }}" 
                                   class="w-full text-xs rounded-xl border border-purple-200 dark:border-purple-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="techTravelTo" class="text-[11px] font-semibold text-purple-800 dark:text-purple-300">{{ __('To Location') }}</label>
                                <x-voice-mic-button target="#techTravelTo" />
                            </div>
                            <input type="text" id="techTravelTo" name="travel_to" placeholder="{{ __('To (e.g. Client Site / Tech Park)') }}" 
                                   class="w-full text-xs rounded-xl border border-purple-200 dark:border-purple-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 items-center">
                        <div>
                            <label class="block text-xs font-semibold text-purple-800 dark:text-purple-300 mb-1">{{ __('Total Distance (KM)') }}</label>
                            <input type="number" step="0.1" name="travel_distance_km" id="techKmInput" oninput="calculateTechKmAmount()" placeholder="e.g. 24.5" 
                                   class="w-full text-xs rounded-xl border border-purple-200 dark:border-purple-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-purple-800 dark:text-purple-300 mb-1">{{ __('Rate / KM (₹)') }}</label>
                            <input type="number" step="0.5" name="rate_per_km" id="techRateInput" value="6.0" oninput="calculateTechKmAmount()" 
                                   class="w-full text-xs rounded-xl border border-purple-200 dark:border-purple-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-2">
                        </div>
                    </div>
                </div>

                {{-- Amount Field --}}
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">{{ __('Claim Amount (₹) *') }}</label>
                    <input type="number" step="0.01" name="amount" id="techClaimAmount" required placeholder="0.00" 
                           class="w-full text-sm font-bold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-slate-900 dark:text-white">
                </div>

                {{-- Installation Site & Assigned Work Linkage --}}
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700/80 space-y-2.5" x-data="{ showNewSiteForm: false }">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">
                            {{ __('Installation Site & Assigned Work (Optional)') }}
                        </label>
                        <button type="button" 
                                @click="showNewSiteForm = !showNewSiteForm"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 hover:bg-blue-100 text-[11px] font-bold border border-blue-200 dark:border-blue-800/80 transition cursor-pointer">
                            <span x-text="showNewSiteForm ? '{{ __('Hide New Site Form') }}' : '{{ __('Add New Installation Site') }}'"></span>
                        </button>
                    </div>

                    {{-- Dropdown for Existing Sites & Admin-Assigned Projects --}}
                    <select name="linked_target" id="techSiteSelect" onchange="onTechSiteSelected(this)" 
                            class="w-full text-xs font-medium rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200">
                        <option value="" data-title="" data-client="" data-address="" data-phone="" data-status="">
                            {{ __('None / General Field Expense') }}
                        </option>

                        {{-- Admin Assigned Projects --}}
                        @if(isset($assignedProjects) && $assignedProjects->isNotEmpty())
                            <optgroup label="🏢 {{ __('Admin Assigned Installation Sites & Projects') }}">
                                @foreach($assignedProjects as $p)
                                    <option value="project:{{ $p->id }}" 
                                            data-type="project"
                                            data-title="{{ $p->title }}" 
                                            data-client="{{ $p->company_name ?: ($p->site?->client_name ?? 'Client') }}" 
                                            data-address="{{ $p->site_address ?: ($p->site?->address ?? '') }}" 
                                            data-phone="{{ $p->contact_phone ?: ($p->site?->phone ?? '') }}" 
                                            data-status="{{ ucfirst(str_replace('_', ' ', $p->status)) }} (Assigned by Admin)">
                                        🏢 {{ $p->title }} — {{ $p->site_address ?: ($p->company_name ?? 'Assigned Project') }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif

                        {{-- Registered Client Installation Sites --}}
                        @if(isset($availableSites) && $availableSites->isNotEmpty())
                            <optgroup label="📍 {{ __('Client Installation Sites') }}">
                                @foreach($availableSites as $s)
                                    <option value="site:{{ $s->id }}" 
                                            data-type="site"
                                            data-title="{{ $s->name }}" 
                                            data-client="{{ $s->client_name ?: 'Client' }}" 
                                            data-address="{{ $s->address }}" 
                                            data-phone="{{ $s->phone ?: '' }}" 
                                            data-status="Active Installation Site">
                                        📍 {{ $s->name }} — {{ $s->address }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif

                        {{-- Active Installation Jobs --}}
                        @if(isset($activeJobs) && $activeJobs->isNotEmpty())
                            <optgroup label="🔧 {{ __('Field Installation Jobs') }}">
                                @foreach($activeJobs as $job)
                                    @php $jLead = $job->quotation?->lead; @endphp
                                    <option value="job:{{ $job->id }}" 
                                            data-type="job"
                                            data-title="{{ $job->job_no }}" 
                                            data-client="{{ $jLead?->customer_name ?? 'Customer' }}" 
                                            data-address="{{ $jLead?->site_address ?? ($jLead?->address ?? '') }}" 
                                            data-phone="{{ $jLead?->phone ?? '' }}" 
                                            data-status="Installation Job ({{ ucfirst($job->status) }})">
                                        🔧 {{ $job->job_no }} — {{ $jLead?->customer_name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>

                    {{-- Live Display of Selected Admin-Assigned Installation Site Data --}}
                    <div id="techSiteDetailsCard" class="hidden p-3 rounded-xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/70 text-xs space-y-1">
                        <div class="flex items-center justify-between">
                            <span id="techCardBadge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-600 text-white shadow-xs">
                                {{ __('Admin Assigned Installation Site') }}
                            </span>
                            <button type="button" onclick="setSiteAsDestination()" class="text-[11px] text-blue-700 dark:text-blue-300 font-bold hover:underline cursor-pointer">
                                {{ __('Set as Travel Destination →') }}
                            </button>
                        </div>
                        <div class="font-bold text-slate-900 dark:text-white text-xs mt-1" id="techCardTitle">--</div>
                        <div class="text-[11px] text-slate-600 dark:text-slate-300 flex items-start gap-1" id="techCardAddressRow">
                            <svg class="w-3.5 h-3.5 shrink-0 text-slate-400 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            <span id="techCardAddress">--</span>
                        </div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 flex items-center gap-3 pt-0.5" id="techCardMeta">
                            <span id="techCardClient">--</span>
                            <span id="techCardPhone">--</span>
                        </div>
                    </div>

                    {{-- Inline Add New Installation Site Panel --}}
                    <div x-show="showNewSiteForm" x-cloak class="p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-blue-300 dark:border-blue-700 space-y-2.5 shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-1.5">
                            <span class="text-xs font-bold text-blue-600 dark:text-blue-400 flex items-center gap-1">
                                <span>➕</span> {{ __('Register New Installation Site for Claim') }}
                            </span>
                            <span class="text-[10px] text-slate-400">{{ __('Stores site in CRM & links to this allowance') }}</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <div class="flex items-center justify-between mb-0.5">
                                    <label for="inlineNewSiteName" class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300">{{ __('Site / Facility Name *') }}</label>
                                    <x-voice-mic-button target="#inlineNewSiteName" />
                                </div>
                                <input type="text" name="new_site_name" id="inlineNewSiteName" placeholder="{{ __('e.g. Reliance Smart Point - Koramangala') }}" 
                                       class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-2">
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-0.5">
                                    <label for="inlineNewClientName" class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300">{{ __('Client / Organization') }}</label>
                                    <x-voice-mic-button target="#inlineNewClientName" />
                                </div>
                                <input type="text" name="new_client_name" id="inlineNewClientName" placeholder="{{ __('e.g. Reliance Retail Ltd') }}" 
                                       class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-2">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-0.5">
                                <label for="inlineNewSiteAddress" class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300">{{ __('Installation Site Address *') }}</label>
                                <x-voice-mic-button target="#inlineNewSiteAddress" />
                            </div>
                            <input type="text" name="new_site_address" id="inlineNewSiteAddress" placeholder="{{ __('e.g. #42, 80 Feet Road, 4th Block, Koramangala, Bengaluru') }}" 
                                   class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-2">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 items-center">
                            <div>
                                <div class="flex items-center justify-between mb-0.5">
                                    <label for="inlineNewSitePhone" class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300">{{ __('Site Contact Phone (Optional)') }}</label>
                                    <x-voice-mic-button target="#inlineNewSitePhone" />
                                </div>
                                <input type="tel" name="new_site_phone" id="inlineNewSitePhone" placeholder="{{ __('e.g. 9876543210') }}" 
                                       class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-2">
                            </div>
                            <div class="pt-4 text-right">
                                <button type="button" onclick="quickSaveNewSite()" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                    {{ __('✓ Create & Select Site') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Description (Optional) --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="techExpenseDescription" class="block font-bold text-slate-700 dark:text-slate-300">
                            {{ __('Description / Purpose (Optional)') }}
                        </label>
                        <x-voice-mic-button target="#techExpenseDescription" />
                    </div>
                    <textarea id="techExpenseDescription" name="description" rows="2" placeholder="{{ __('State site location, work done, or items purchased (optional)...') }}" 
                              class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 text-slate-800 dark:text-slate-200"></textarea>
                </div>

                {{-- Receipt Upload --}}
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">{{ __('Receipt / Fuel Bill Photo (Optional)') }}</label>
                    <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full text-xs text-slate-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showAddAllowanceModal = false" class="btn-secondary text-xs">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn-primary text-xs px-5 py-2.5">
                        {{ __('Submit for Admin Approval') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>

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
                                <span>${techScanText.viewHistory}</span> →
                            </a>
                        </div>
                    `;
                } else if (data.found && data.type === 'catalog_product') {
                    const targetSerial = data.serial || code;
                    const targetMac = data.mac || '';
                    const targetModel = data.model || data.product_name || '';
                    const targetPid = data.product_id || data.id || '';

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
                                    ${targetMac ? `<div class="text-xs text-blue-600 dark:text-blue-400 font-mono mt-0.5">MAC: ${targetMac}</div>` : ''}
                                </div>
                            </div>
                            <a href="{{ route('equipment.create') }}?product_id=${targetPid}&serial=${encodeURIComponent(targetSerial)}&mac=${encodeURIComponent(targetMac)}&model=${encodeURIComponent(targetModel)}" 
                               class="self-start sm:self-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 !text-white font-bold text-xs sm:text-sm shadow-sm transition"
                                style="color: #ffffff !important;">
                                <span style="color: #ffffff !important;">${techScanText.installAtSite} →</span>
                            </a>
                        </div>
                    `;
                } else {
                    const parsed = window.parseDeviceBarcode ? window.parseDeviceBarcode(code) : null;
                    const isStrictMac = (str) => {
                        if (!str) return false;
                        const s = String(str).replace(/^MAC[:\s=-]*/i, '').trim();
                        return /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/.test(s) ||
                               (/^[0-9A-Fa-f]{12}$/.test(s) && /[A-Fa-f]/.test(s));
                    };
                    const nfMac = (parsed && parsed.mac && isStrictMac(parsed.mac)) ? parsed.mac : (isStrictMac(code) ? code : (data.mac || ''));
                    const nfSerial = (parsed && parsed.serial && !isStrictMac(parsed.serial)) ? parsed.serial : (!isStrictMac(code) ? (data.serial || code) : '');
                    const nfModel = (parsed && parsed.model) ? parsed.model : (data.model || '');

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
                                    ${(nfModel || nfMac) ? `<div class="text-xs text-slate-600 dark:text-slate-300 mt-1 font-mono">${nfModel ? 'Model: ' + nfModel + ' ' : ''}${nfMac ? 'MAC: ' + nfMac : ''}</div>` : ''}
                                </div>
                            </div>
                            <a href="{{ route('equipment.create') }}?serial=${encodeURIComponent(nfSerial)}&mac=${encodeURIComponent(nfMac)}&model=${encodeURIComponent(nfModel)}" 
                               class="self-start sm:self-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 min-h-[38px] rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 !text-white font-bold text-xs sm:text-sm shadow-sm transition"
                               style="color: #ffffff !important;">
                                <span style="color: #ffffff !important;">${techScanText.registerNew} →</span>
                            </a>
                        </div>
                    `;
                }
            } catch (e) {
                container.innerHTML = `<div class="text-rose-400 text-xs">${techScanText.lookupError.replace(':error', e.message)}</div>`;
            }
        }

        function toggleTechKm(cat) {
            const sec = document.getElementById('techKmSection');
            if (sec) {
                if (cat === 'fuel_travel') {
                    sec.classList.remove('hidden');
                } else {
                    sec.classList.add('hidden');
                }
            }
        }

        function calculateTechKmAmount() {
            const km = parseFloat(document.getElementById('techKmInput').value) || 0;
            const rate = parseFloat(document.getElementById('techRateInput').value) || 6.0;
            if (km > 0) {
                const total = (km * rate).toFixed(2);
                document.getElementById('techClaimAmount').value = total;
            }
        }

        function onTechSiteSelected(sel) {
            const opt = sel.options[sel.selectedIndex];
            const card = document.getElementById('techSiteDetailsCard');
            if (!opt || !opt.value) {
                card.classList.add('hidden');
                return;
            }
            const title = opt.getAttribute('data-title') || opt.text;
            const client = opt.getAttribute('data-client') || '';
            const address = opt.getAttribute('data-address') || '';
            const phone = opt.getAttribute('data-phone') || '';
            const status = opt.getAttribute('data-status') || 'Installation Site';

            document.getElementById('techCardTitle').textContent = title;
            document.getElementById('techCardBadge').textContent = status;
            document.getElementById('techCardAddress').textContent = address || 'No address registered';
            document.getElementById('techCardClient').textContent = client ? ('Client: ' + client) : '';
            document.getElementById('techCardPhone').textContent = phone ? ('📞 ' + phone) : '';
            card.classList.remove('hidden');

            const toInput = document.getElementById('techTravelTo');
            if (toInput && (!toInput.value || toInput.value.trim() === '')) {
                toInput.value = address || title;
            }
        }

        function setSiteAsDestination() {
            const sel = document.getElementById('techLinkedSiteSelect');
            const opt = sel.options[sel.selectedIndex];
            if (opt && opt.value) {
                const address = opt.getAttribute('data-address') || opt.getAttribute('data-title');
                const toInput = document.getElementById('techTravelTo');
                if (toInput && address) {
                    toInput.value = address;
                    toInput.focus();
                }
            }
        }

        async function quickSaveNewSite() {
            const name = document.getElementById('inlineNewSiteName').value.trim();
            const address = document.getElementById('inlineNewSiteAddress').value.trim();
            const client = document.getElementById('inlineNewClientName').value.trim();
            const phone = document.getElementById('inlineNewSitePhone').value.trim();

            if (!name || !address) {
                alert('Please enter both Site Name and Address to register an installation site.');
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                    || document.querySelector('input[name="_token"]')?.value;

                const response = await fetch('/sites', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        name: name,
                        address: address,
                        client_name: client,
                        contact_person: client || 'Site Manager',
                        phone: phone || '-'
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    const newId = data.site ? data.site.id : data.id;
                    const newName = data.site ? data.site.name : name;

                    const sel = document.getElementById('techLinkedSiteSelect');
                    const newOpt = document.createElement('option');
                    newOpt.value = `site:${newId}`;
                    newOpt.textContent = `📍 ${newName} (${client || 'Site'})`;
                    newOpt.setAttribute('data-type', 'site');
                    newOpt.setAttribute('data-title', newName);
                    newOpt.setAttribute('data-client', client);
                    newOpt.setAttribute('data-address', address);
                    newOpt.setAttribute('data-phone', phone);
                    newOpt.setAttribute('data-status', 'Registered Installation Site');

                    sel.appendChild(newOpt);
                    sel.value = `site:${newId}`;
                    onTechSiteSelected(sel);

                    alert(`Installation site "${newName}" created and selected!`);
                } else {
                    alert('Site details stored for this claim.');
                }
            } catch (err) {
                alert('Site details stored for this claim.');
            }
        }

        // Auto-focus target allowance or record if navigating from Topbar Notifications
        document.addEventListener('DOMContentLoaded', function () {
            const targetClaimNo = @js(request('claim_no'));
            const targetClaimId = @js(request('claim_id'));
            if (targetClaimNo) {
                const el = document.querySelector('[data-claim-no="' + targetClaimNo + '"]');
                if (el) {
                    setTimeout(() => {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 250);
                }
            } else if (targetClaimId) {
                const el = document.getElementById('tech-claim-' + targetClaimId);
                if (el) {
                    setTimeout(() => {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 250);
                }
            }
        });
    </script>

    {{-- Universal Voice Dictation & Speech Recognition Engine provided globally via app layout --}}
</x-app-layout>
