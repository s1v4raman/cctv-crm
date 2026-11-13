<x-app-layout>
    {{-- MODERN HIGH-END CRM EXECUTIVE & EMPLOYEE WORKFORCE DASHBOARD --}}
    {{-- Design Reference: Pixel-perfect implementation of "Relationship first. Growth follows." --}}

    @php
        $d = $dashboardData;
        $periodLabel = $d['period_label'] ?? ($isAdmin ? 'This quarter' : 'This month');
    @endphp

    <div x-data="{
        periodOpen: false,
        selectedPeriod: '{{ $periodLabel }}',
        activeFilterTab: 'attention',
        dealSearch: '',
        selectPeriod(p) {
            this.selectedPeriod = p;
            this.periodOpen = false;
        }
    }" class="crm-content max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-7 space-y-5 sm:space-y-6">

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- 1. TOP HEADER BANNER (TITLE, SUBTITLE & TOP CONTROLS)           --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                    {{ $d['headline_title'] }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ $d['headline_subtitle'] }}
                </p>
            </div>

            <div class="flex items-center gap-2.5 self-start sm:self-auto">
                {{-- Period Selector Pill Dropdown --}}
                <div class="relative">
                    <button @click="periodOpen = !periodOpen" 
                            type="button" 
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-xs hover:border-slate-300 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect width="18" height="18" x="3" y="4" rx="2"/>
                            <line x1="16" x2="16" y1="2" y2="6"/>
                            <line x1="8" x2="8" y1="2" y2="6"/>
                            <line x1="3" x2="21" y1="10" y2="10"/>
                        </svg>
                        <span x-text="selectedPeriod"></span>
                        <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="periodOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </button>

                    <div x-show="periodOpen" 
                         x-cloak 
                         @click.away="periodOpen = false"
                         class="absolute right-0 mt-1.5 w-44 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl py-1 z-30 text-xs">
                        <button type="button" @click="selectPeriod('This quarter')" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 font-medium text-slate-700 dark:text-slate-200">{{ __('This quarter') }}</button>
                        <button type="button" @click="selectPeriod('This month')" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 font-medium text-slate-700 dark:text-slate-200">{{ __('This month') }}</button>
                        <button type="button" @click="selectPeriod('Past 6 months')" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 font-medium text-slate-700 dark:text-slate-200">{{ __('Past 6 months') }}</button>
                        <button type="button" @click="selectPeriod('This year')" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 font-medium text-slate-700 dark:text-slate-200">{{ __('This year') }}</button>
                    </div>
                </div>

                {{-- Primary Action Button --}}
                @if($isAdmin)
                    <a href="{{ route('leads.create') }}" 
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-semibold text-xs shadow-xs transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>{{ __('New deal') }}</span>
                    </a>
                @else
                    <a href="{{ route('attendance.index') }}" 
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-semibold text-xs shadow-xs transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        <span>{{ __('My Attendance') }}</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- 2. TOP ROW: 4 EQUAL-WIDTH KPI METRIC CARDS WITH SPARKLINES      --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
            @foreach($d['metrics'] as $key => $metric)
                <div class="bg-white dark:bg-slate-900 rounded-[20px] p-4 sm:p-5 border border-slate-100 dark:border-slate-800/80 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.03)] flex flex-col justify-between hover:shadow-md transition">
                    {{-- Header with (i) info --}}
                    <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $metric['label'] }}</span>
                        <button type="button" class="text-slate-300 dark:text-slate-600 hover:text-slate-500 transition cursor-help" title="{{ $metric['subtext'] }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-4m0-4h.01"/></svg>
                        </button>
                    </div>

                    {{-- Big Value & Inline Smooth Sparkline --}}
                    <div class="flex items-baseline justify-between mt-2.5">
                        <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            {{ $metric['value'] }}
                        </span>

                        {{-- Sparkline Curve --}}
                        <div class="w-20 h-8 shrink-0">
                            <svg class="w-full h-full text-indigo-500" viewBox="0 0 100 35" fill="none">
                                <path d="M 0,26 Q 18,24 35,22 T 65,14 T 85,16 T 100,6" 
                                      stroke="currentColor" 
                                      stroke-width="2.5" 
                                      stroke-linecap="round" 
                                      stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Change Badge & Subtitle --}}
                    <div class="mt-2.5 pt-2 border-t border-slate-50 dark:border-slate-800/60">
                        <div class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                            <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
                            <span>{{ $metric['change'] }}</span>
                        </div>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 truncate">
                            {{ $metric['subtext'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- 3. MAIN ASYMMETRIC GRID: 8 COLS (LEFT) + 4 COLS (RIGHT)         --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">

            {{-- ──────────────────────────────────────────────────────────── --}}
            {{-- LEFT COLUMN: PIPELINE OVERVIEW & DEALS TABLE (8 COLS)        --}}
            {{-- ──────────────────────────────────────────────────────────── --}}
            <div class="lg:col-span-8 space-y-5 sm:space-y-6">

                {{-- CARD A: PIPELINE OVERVIEW WITH SEGMENTED PROGRESS BAR --}}
                <div class="bg-white dark:bg-slate-900 rounded-[24px] p-5 sm:p-6 border border-slate-100 dark:border-slate-800/80 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.03)] space-y-4">
                    {{-- Header Row --}}
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight">
                                {{ __('Pipeline overview') }}
                            </h2>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                {{ __('A clear view of your next opportunities.') }}
                            </p>
                        </div>

                        <a href="{{ $isAdmin ? route('quotations.index') : route('projects.index') }}" 
                           class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 inline-flex items-center gap-1.5 transition">
                            <span>{{ __('View pipeline') }}</span>
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"/></svg>
                        </a>
                    </div>

                    {{-- Big Metric Callout --}}
                    <div class="flex items-baseline gap-2 pt-1">
                        <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            {{ $d['pipeline']['total_formatted'] }}
                        </span>
                        <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">
                            across {{ $d['pipeline']['deals_count'] }} open deals
                        </span>
                    </div>

                    {{-- Proportional Segmented Progress Bar --}}
                    <div class="h-3 rounded-full overflow-hidden flex bg-slate-100 dark:bg-slate-800 gap-0.5 p-0.5">
                        @foreach($d['pipeline']['stages'] as $index => $stage)
                            <div class="{{ $stage['color'] }} h-full {{ $index === 0 ? 'rounded-l-full' : '' }} {{ $index === count($d['pipeline']['stages']) - 1 ? 'rounded-r-full' : '' }}" 
                                 style="width: {{ $stage['pct'] }}%" 
                                 title="{{ $stage['name'] }}: {{ $stage['val'] }}"></div>
                        @endforeach
                    </div>

                    {{-- 4-Stage Columns Breakdown --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 text-xs">
                        @foreach($d['pipeline']['stages'] as $stage)
                            <div>
                                <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300 font-medium">
                                    <span class="w-2 h-2 rounded-full {{ $stage['dot'] }}"></span>
                                    <span>{{ $stage['name'] }}</span>
                                </div>
                                <div class="text-sm font-bold text-slate-900 dark:text-white mt-1">
                                    {{ $stage['val'] }}
                                </div>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                    {{ $stage['count'] }} deals
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Bottom Insight Highlight Banner --}}
                    <div class="p-3 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 flex items-center gap-2.5 text-xs text-indigo-950 dark:text-indigo-200 font-medium">
                        <span class="text-indigo-600 dark:text-indigo-400 text-sm">⚡</span>
                        <span>{{ $d['pipeline']['insight'] }}</span>
                    </div>
                </div>

                {{-- CARD B: DEALS TO FOCUS ON (TABLE CARD) --}}
                <div class="bg-white dark:bg-slate-900 rounded-[24px] p-5 sm:p-6 border border-slate-100 dark:border-slate-800/80 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.03)] space-y-4">
                    {{-- Header with Tabs & Filter Controls --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                                <span>{{ __('Projects & Deals to focus on') }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    {{ count($d['deals']) }}
                                </span>
                            </h2>

                            {{-- Filter Tabs --}}
                            <div class="inline-flex p-0.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs">
                                <button type="button" 
                                        @click="activeFilterTab = 'all'" 
                                        :class="activeFilterTab === 'all' ? 'bg-white dark:bg-slate-900 text-indigo-700 dark:text-indigo-300 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                                        class="px-3 py-1 rounded-lg transition cursor-pointer">
                                    {{ __('All') }} ({{ count($d['deals']) }})
                                </button>
                                <button type="button" 
                                        @click="activeFilterTab = 'attention'" 
                                        :class="activeFilterTab === 'attention' ? 'bg-white dark:bg-slate-900 text-indigo-700 dark:text-indigo-300 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                                        class="px-3 py-1 rounded-lg transition cursor-pointer">
                                    {{ __('Needs attention') }}
                                </button>
                                <button type="button" 
                                        @click="activeFilterTab = 'my'" 
                                        :class="activeFilterTab === 'my' ? 'bg-white dark:bg-slate-900 text-indigo-700 dark:text-indigo-300 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800'"
                                        class="px-3 py-1 rounded-lg transition cursor-pointer">
                                    {{ __('My projects') }}
                                </button>
                            </div>
                        </div>

                        {{-- Right Action Buttons --}}
                        <div class="flex items-center gap-2">
                            <a href="{{ route('projects.index') }}" 
                               class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-0.5 ml-1">
                                <span>{{ __('View all projects') }}</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"/></svg>
                            </a>
                        </div>
                    </div>

                    {{-- Data Table --}}
                    <div class="overflow-x-auto -mx-5 sm:mx-0">
                        <table class="crm-auto-cards w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                                    <th class="py-2.5 px-3 w-8 hidden sm:table-cell">
                                        <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-700 cursor-pointer">
                                    </th>
                                    <th class="py-2.5 px-3 font-semibold">{{ __('Project / Customer') }}</th>
                                    <th class="py-2.5 px-3 font-semibold text-right sm:text-left">{{ __('Value') }}</th>
                                    <th class="py-2.5 px-3 font-semibold">{{ __('Stage') }}</th>
                                    <th class="py-2.5 px-3 font-semibold text-center">{{ __('Lead Tech') }}</th>
                                    <th class="py-2.5 px-3 font-semibold">{{ __('Next step') }}</th>
                                    <th class="py-2.5 px-3 w-8"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                @forelse($d['deals'] as $deal)
                                    <tr x-show="activeFilterTab === 'all' || (activeFilterTab === 'attention' && {{ !empty($deal['is_overdue']) ? 'true' : 'false' }}) || (activeFilterTab === 'my' && '{{ $deal['owner'] }}' !== 'ADMIN')"
                                        class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition group">
                                        {{-- Checkbox --}}
                                        <td class="py-3 px-3 ld-box hidden sm:table-cell" data-label="">
                                            <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-700 cursor-pointer">
                                        </td>

                                        {{-- Company Logo Badge & Title --}}
                                        <td class="py-3 px-3 min-w-0 sm:min-w-[200px] w-full" data-card-header>
                                            <div class="flex items-center gap-3 min-w-0 w-full max-w-full">
                                                <div class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs flex items-center justify-center shrink-0">
                                                    {{ $deal['initial'] }}
                                                </div>
                                                <div class="min-w-0 flex-1 max-w-full overflow-hidden">
                                                    <a href="{{ $deal['url'] }}" class="font-bold text-slate-900 dark:text-white hover:text-indigo-600 transition truncate block text-sm">
                                                        {{ $deal['company'] }}
                                                    </a>
                                                    <span class="block text-[11px] text-slate-400 dark:text-slate-500 truncate">
                                                        {{ $deal['title'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Value --}}
                                        <td class="py-3 px-3 font-bold text-slate-900 dark:text-white sm:whitespace-nowrap text-right sm:text-left">
                                            &#8377;{{ number_format($deal['value'], 0) }}
                                        </td>

                                        {{-- Stage Pill Badge --}}
                                        <td class="py-3 px-3 sm:whitespace-nowrap">
                                            @php
                                                $pillStyle = match($deal['stage_color'] ?? 'indigo') {
                                                    'indigo' => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200/70',
                                                    'blue' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200/70',
                                                    'amber' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200/70',
                                                    'emerald' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/70',
                                                    default => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200'
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $pillStyle }}">
                                                {{ $deal['stage'] }}
                                            </span>
                                        </td>

                                        {{-- Owner Avatar Initials --}}
                                        <td class="py-3 px-3 text-center sm:whitespace-nowrap">
                                            <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-[10px] inline-flex items-center justify-center">
                                                {{ $deal['owner'] }}
                                            </div>
                                        </td>

                                        {{-- Next Step & Due Status --}}
                                        <td class="py-3 px-3 sm:whitespace-nowrap text-xs">
                                            <div class="text-slate-800 dark:text-slate-200 font-medium">
                                                {{ explode(' • ', $deal['next_step'])[0] }}
                                            </div>
                                            @if(!empty($deal['is_overdue']) || str_contains($deal['next_step'], 'action'))
                                                <div class="text-[11px] text-amber-600 dark:text-amber-400 flex items-center gap-1 mt-0.5">
                                                    <span>⚠️</span>
                                                    <span>{{ explode(' • ', $deal['next_step'])[1] ?? 'Action required' }}</span>
                                                </div>
                                            @else
                                                <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                                    {{ explode(' • ', $deal['next_step'])[1] ?? 'Scheduled' }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Action Dots Menu --}}
                                        <td class="py-3 px-3 text-right">
                                            <a href="{{ $deal['url'] }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg inline-block">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                            {{ __('No active projects or deals found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Table Pagination Footer --}}
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-400 dark:text-slate-500">
                        <span>Showing {{ count($d['deals']) }} active project records</span>
                        <div class="inline-flex items-center gap-2">
                            <span class="font-medium text-slate-600 dark:text-slate-300">Page 1 of 1</span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ──────────────────────────────────────────────────────────── --}}
            {{-- RIGHT COLUMN: UPCOMING ACTIVITY & QUARTERLY TARGET (4 COLS) --}}
            {{-- ──────────────────────────────────────────────────────────── --}}
            <div class="lg:col-span-4 space-y-5 sm:space-y-6">

                {{-- CARD C: UPCOMING ACTIVITY TIMELINE --}}
                <div class="bg-white dark:bg-slate-900 rounded-[24px] p-5 sm:p-6 border border-slate-100 dark:border-slate-800/80 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.03)] space-y-4">
                    {{-- Header Row with (+) --}}
                    <div class="flex items-center justify-between">
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight">
                            {{ __('Upcoming activity') }}
                        </h2>
                        <a href="{{ route('calendar.index') }}" 
                           class="w-7 h-7 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold text-sm transition"
                           title="{{ __('Add new activity') }}">+</a>
                    </div>

                    {{-- Day Header Pill --}}
                    <div class="flex items-center justify-between text-xs pt-1">
                        <span class="font-bold text-slate-800 dark:text-slate-200">
                            Today, {{ now()->translatedFormat('l, M d') }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                            {{ count($d['upcoming']) }} scheduled
                        </span>
                    </div>

                    {{-- Timeline Activity Items --}}
                    <div class="space-y-3 pt-1">
                        @forelse($d['upcoming'] as $act)
                            <div class="p-3.5 rounded-2xl bg-slate-50/70 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 space-y-1.5 hover:bg-slate-100/60 transition">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 font-mono">
                                            {{ $act['time'] }}
                                        </span>
                                        @if($act['type'] === 'call')
                                            <span class="text-xs">📞</span>
                                        @elseif($act['type'] === 'survey')
                                            <span class="text-xs">🎥</span>
                                        @else
                                            <span class="text-xs">📄</span>
                                        @endif
                                        <h3 class="text-xs font-bold text-slate-900 dark:text-white leading-tight">
                                            {{ $act['title'] }}
                                        </h3>
                                    </div>
                                    @if(!empty($act['is_soon']))
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 shrink-0">
                                            {{ $act['status_note'] }}
                                        </span>
                                    @endif
                                </div>

                                <div class="text-[11px] text-slate-600 dark:text-slate-300 font-medium pl-6">
                                    {{ $act['customer'] }}
                                </div>

                                <div class="flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500 pl-6 pt-1">
                                    <span>{{ $act['duration'] }}</span>
                                    <a href="{{ $act['link'] }}" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline inline-flex items-center gap-0.5">
                                        <span>View details</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"/></svg>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 rounded-2xl bg-slate-50/50 dark:bg-slate-850 border border-slate-100 dark:border-slate-800 text-center text-xs text-slate-400">
                                <span class="text-xl block mb-1">📅</span>
                                <p class="font-bold text-slate-700 dark:text-slate-300">{{ __('No site visits or tasks scheduled for today.') }}</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ __('Field operations team is on active standby.') }}</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Footer Link to Calendar --}}
                    <div class="pt-2 text-center">
                        <a href="{{ route('calendar.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1">
                            <span>{{ __('View full calendar') }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </a>
                    </div>
                </div>

                {{-- CARD D: GOAL & QUARTERLY TARGET TRACKER --}}
                <div class="bg-white dark:bg-slate-900 rounded-[24px] p-5 sm:p-6 border border-slate-100 dark:border-slate-800/80 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.03)] space-y-3.5">
                    {{-- Header with Flag Icon --}}
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight">
                            {{ $d['goal']['title'] }}
                        </h2>
                        <span class="text-slate-400 text-sm">🏳</span>
                    </div>

                    {{-- Big Target Metric --}}
                    <div>
                        <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            {{ explode(' of ', $d['goal']['metric'])[0] }}
                            <span class="text-xs font-normal text-slate-400">of {{ explode(' of ', $d['goal']['metric'])[1] ?? '₹1,60,000' }}</span>
                        </div>
                    </div>

                    {{-- Horizontal Progress Bar --}}
                    <div class="h-2.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full bg-indigo-600 rounded-full transition-all duration-700" 
                             style="width: {{ $d['goal']['pct'] }}%"></div>
                    </div>

                    {{-- Subtext & Encouragement --}}
                    <div class="text-xs text-slate-500 dark:text-slate-400 space-y-1">
                        <div class="font-semibold text-slate-700 dark:text-slate-300">
                            {{ $d['goal']['subtext'] }}
                        </div>
                        <p class="text-[11px] leading-relaxed text-slate-400 dark:text-slate-500">
                            {{ $d['goal']['caption'] }}
                        </p>
                    </div>

                    {{-- Footer Note --}}
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center gap-1.5 text-[11px] text-slate-400 dark:text-slate-500">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
                        <span>{{ __('All changes saved • Updated just now') }}</span>
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-app-layout>
