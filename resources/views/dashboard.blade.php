<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Welcome back, <strong class="text-slate-800 dark:text-slate-200">{{ Auth::user()->name }}</strong> 👋</span>
                    <span class="text-slate-300 dark:text-slate-700">•</span>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/80">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Grid
                    </span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight font-heading">
                    @if($isAdmin)
                        Operations &amp; Executive Command Center
                    @else
                        Workforce Operations Command Center
                    @endif
                </h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    @if($isAdmin)
                        CCTV Operations, Automated Quotations, Financials &amp; Field Telemetry
                    @else
                        Field Engineering, Dispatch, Support Tickets &amp; Daily Work Telemetry
                    @endif
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2 sm:gap-2.5 shrink-0">
                <div class="flex items-center gap-2 text-xs font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2 rounded-xl shadow-xs">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="last-updated">Connecting live grid...</span>
                </div>

                @if($isAdmin)
                    <a href="{{ route('leads.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-95 rounded-xl shadow-xs transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>New Lead</span>
                    </a>
                    
                    <a href="{{ route('quotations.create-general') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 active:scale-95 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xs transition-all">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                        <span>New Quote</span>
                    </a>

                    <a href="{{ route('jobs.create-general') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 active:scale-95 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xs transition-all">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
                        </svg>
                        <span>New Job</span>
                    </a>

                    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 active:scale-95 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xs transition-all">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span>Products</span>
                    </a>
                @else
                    <a href="{{ route('attendance.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:scale-95 rounded-xl shadow-xs transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>My Attendance</span>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    {{-- Modern SaaS Light & Dark Styles --}}
    <style>
        .db-wrap {
            padding: 1.5rem 0 3rem;
            min-height: calc(100vh - 4.5rem);
            transition: background-color 0.2s ease;
        }

        .db-inner {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        #dashboard-error {
            display: none;
            margin-bottom: 1.25rem;
            border-radius: 1rem;
            border: 1px solid #fecdd3;
            background: #fff1f2;
            padding: 0.9rem 1.25rem;
            font-size: 0.85rem;
            color: #e11d48;
        }
        html.dark #dashboard-error {
            background: rgba(244, 63, 94, 0.1);
            border-color: rgba(244, 63, 94, 0.3);
            color: #fb7185;
        }
        #dashboard-error.visible { display: block; }

        /* Badge Utilities */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.2rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.68rem;
            font-weight: 700;
            line-height: 1.4;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .badge-new         { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .badge-contacted   { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; }
        .badge-quoted      { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
        .badge-won         { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-lost        { background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; }
        .badge-draft       { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .badge-sent        { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; }
        .badge-accepted    { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-rejected    { background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; }
        .badge-expired     { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
        .badge-pending     { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .badge-scheduled   { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; }
        .badge-assigned    { background: #f5f3ff; color: #7c3aed; border: 1px solid #ede9fe; }
        .badge-in_progress { background: #fffbeb; color: #b45309; border: 1px solid #fef3c7; }
        .badge-completed   { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-cancelled   { background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; }
        .badge-open        { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; }
        .badge-resolved    { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-critical    { background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; }
        .badge-high        { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
        .badge-medium      { background: #fefce8; color: #ca8a04; border: 1px solid #fef08a; }
        .badge-low         { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

        html.dark .badge-new         { background: rgba(148, 163, 184, 0.15); color: #cbd5e1; border-color: rgba(148, 163, 184, 0.3); }
        html.dark .badge-contacted   { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border-color: rgba(59, 130, 246, 0.3); }
        html.dark .badge-quoted      { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border-color: rgba(245, 158, 11, 0.3); }
        html.dark .badge-won         { background: rgba(16, 185, 129, 0.15); color: #34d399; border-color: rgba(16, 185, 129, 0.3); }
        html.dark .badge-lost        { background: rgba(244, 63, 94, 0.15); color: #fb7185; border-color: rgba(244, 63, 94, 0.3); }
        html.dark .badge-draft       { background: rgba(148, 163, 184, 0.15); color: #cbd5e1; border-color: rgba(148, 163, 184, 0.3); }
        html.dark .badge-sent        { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border-color: rgba(59, 130, 246, 0.3); }
        html.dark .badge-accepted    { background: rgba(16, 185, 129, 0.15); color: #34d399; border-color: rgba(16, 185, 129, 0.3); }
        html.dark .badge-rejected    { background: rgba(244, 63, 94, 0.15); color: #fb7185; border-color: rgba(244, 63, 94, 0.3); }
        html.dark .badge-expired     { background: rgba(249, 115, 22, 0.15); color: #fb923c; border-color: rgba(249, 115, 22, 0.3); }
        html.dark .badge-pending     { background: rgba(148, 163, 184, 0.15); color: #cbd5e1; border-color: rgba(148, 163, 184, 0.3); }
        html.dark .badge-scheduled   { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border-color: rgba(59, 130, 246, 0.3); }
        html.dark .badge-assigned    { background: rgba(168, 85, 247, 0.15); color: #c084fc; border-color: rgba(168, 85, 247, 0.3); }
        html.dark .badge-in_progress { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border-color: rgba(245, 158, 11, 0.3); }
        html.dark .badge-completed   { background: rgba(16, 185, 129, 0.15); color: #34d399; border-color: rgba(16, 185, 129, 0.3); }
        html.dark .badge-cancelled   { background: rgba(244, 63, 94, 0.15); color: #fb7185; border-color: rgba(244, 63, 94, 0.3); }
        html.dark .badge-open        { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border-color: rgba(59, 130, 246, 0.3); }
        html.dark .badge-resolved    { background: rgba(16, 185, 129, 0.15); color: #34d399; border-color: rgba(16, 185, 129, 0.3); }
        html.dark .badge-critical    { background: rgba(244, 63, 94, 0.15); color: #fb7185; border-color: rgba(244, 63, 94, 0.3); }
        html.dark .badge-high        { background: rgba(249, 115, 22, 0.15); color: #fb923c; border-color: rgba(249, 115, 22, 0.3); }
        html.dark .badge-medium      { background: rgba(234, 179, 8, 0.15); color: #facc15; border-color: rgba(234, 179, 8, 0.3); }
        html.dark .badge-low         { background: rgba(148, 163, 184, 0.15); color: #cbd5e1; border-color: rgba(148, 163, 184, 0.3); }

        /* Pipeline Funnel Tiers */
        .funnel-stage {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 1.25rem;
            border-radius: 0.875rem;
            transition: all 0.2s ease;
            margin-bottom: 0.65rem;
        }
        .funnel-stage:hover {
            transform: translateX(4px);
        }
    </style>

    <div class="db-wrap bg-[#f8fafc] dark:bg-[#060913]">
        <div class="db-inner">

            {{-- Error Banner --}}
            <div id="dashboard-error"></div>

            @if($isAdmin)
                {{-- ========================================================================= --}}
                {{-- 👑 ADMIN FULL EXECUTIVE DASHBOARD --}}
                {{-- ========================================================================= --}}

                {{-- ── 1. Top 4 Metric Cards (Executive) ── --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">

                    {{-- Total Leads Card --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-[#2563eb] dark:text-blue-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2h5M12 12a4 4 0 100-8 4 4 0 000 8z"/>
                                </svg>
                            </div>
                            <span class="inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800">
                                Total Leads
                            </span>
                        </div>
                        <div class="mt-4">
                            <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight" id="stat-total-leads">—</div>
                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">
                                Pipeline Leads &middot; <strong class="text-slate-700 dark:text-slate-200 font-semibold" id="stat-leads-month">0</strong> added this month
                            </div>
                        </div>
                    </div>

                    {{-- Conversion Rate Card --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                            <span class="inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800">
                                Win Rate
                            </span>
                        </div>
                        <div class="mt-4">
                            <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                                <span id="stat-conversion">0</span>%
                            </div>
                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">
                                Conversion &middot; <strong class="text-slate-700 dark:text-slate-200 font-semibold" id="stat-won">0</strong> won / <strong class="text-slate-700 dark:text-slate-200 font-semibold" id="stat-lost">0</strong> lost
                            </div>
                        </div>
                    </div>

                    {{-- Accepted Revenue Card --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8v1m0 8v1m-9-5a9 9 0 1118 0A9 9 0 013 12z"/>
                                </svg>
                            </div>
                            <span class="inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-800">
                                ₹<span id="stat-revenue-month">0</span> this mo
                            </span>
                        </div>
                        <div class="mt-4">
                            <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight" id="stat-revenue">₹0</div>
                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">
                                Accepted Revenue &middot; Closed Proposals
                            </div>
                        </div>
                    </div>

                    {{-- Open Jobs Card --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <span class="inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-800">
                                <span id="stat-inprogress-jobs">0</span> active
                            </span>
                        </div>
                        <div class="mt-4">
                            <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight" id="stat-open-jobs">0</div>
                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">
                                Open Work Orders &middot; In Field / Scheduled
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ── 2. Middle Row: Sales Overview & Deals Pipeline ── --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-6">

                    {{-- Sales Overview (Line Chart) --}}
                    <div class="lg:col-span-7 bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs transition-colors duration-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Sales Overview</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Accepted quotation revenue over the last 6 months</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#2563eb]"></span>
                                    <span>Revenue</span>
                                </div>
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg">
                                    <span>Past 6 Months</span>
                                </div>
                            </div>
                        </div>

                        <div class="relative" style="height: 270px;">
                            <canvas id="revenueChart"></canvas>
                        </div>

                        {{-- Sales Mini Metrics Footer --}}
                        <div class="grid grid-cols-3 gap-3 pt-5 mt-5 border-t border-slate-100 dark:border-slate-800 text-center sm:text-left">
                            <div>
                                <div class="text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Invoiced</div>
                                <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5" id="stat-footer-rev">₹0</div>
                            </div>
                            <div>
                                <div class="text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Won Ratio</div>
                                <div class="text-base font-bold text-emerald-600 dark:text-emerald-400 mt-0.5"><span id="stat-footer-ratio">0</span>%</div>
                            </div>
                            <div>
                                <div class="text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Active Pipeline</div>
                                <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5"><span id="stat-footer-active">0</span> Quotes</div>
                            </div>
                        </div>
                    </div>

                    {{-- Deals Pipeline (Tapered Funnel Visual) --}}
                    <div class="lg:col-span-5 bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs flex flex-col justify-between transition-colors duration-200">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Deals Pipeline</h3>
                                <a href="{{ route('quotations.index') }}" class="text-xs font-semibold text-[#2563eb] dark:text-blue-400 hover:text-blue-700 transition-colors">
                                    View All →
                                </a>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">Active quotations categorized across deal stages</p>

                            {{-- Funnel Stack --}}
                            <div class="space-y-2.5">

                                {{-- Stage 1: Draft Proposals --}}
                                <div class="w-full funnel-stage bg-blue-50/80 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/50 hover:border-blue-300">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#2563eb]"></span>
                                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">Draft Proposals</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-[#2563eb] dark:text-blue-400" id="stat-draft-quotes">0</span>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">deals</span>
                                    </div>
                                </div>

                                {{-- Stage 2: Sent Quotations --}}
                                <div class="w-[92%] funnel-stage bg-sky-50/80 dark:bg-sky-950/40 border border-sky-100 dark:border-sky-900/50 hover:border-sky-300">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">Sent to Client</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-sky-600 dark:text-sky-400" id="stat-sent-quotes">0</span>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">deals</span>
                                    </div>
                                </div>

                                {{-- Stage 3: Accepted Deals --}}
                                <div class="w-[84%] funnel-stage bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/50 hover:border-emerald-300">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">Accepted / Won</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400" id="stat-accepted-quotes">0</span>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">deals</span>
                                    </div>
                                </div>

                                {{-- Stage 4: Expired / Stalled --}}
                                <div class="w-[76%] funnel-stage bg-amber-50/80 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-900/50 hover:border-amber-300">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">Expired / Pending Review</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400" id="stat-expired-quotes">0</span>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">deals</span>
                                    </div>
                                </div>

                                {{-- Stage 5: Rejected Proposals --}}
                                <div class="w-[68%] funnel-stage bg-rose-50/80 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-900/50 hover:border-rose-300">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">Declined / Lost</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-rose-600 dark:text-rose-400" id="stat-rejected-quotes">0</span>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">deals</span>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Pipeline Total Summary --}}
                        <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                            <span class="font-medium text-slate-500 dark:text-slate-400">Total Live Pipeline Value</span>
                            <span class="font-extrabold text-slate-900 dark:text-white text-sm" id="stat-pipeline-total">₹0</span>
                        </div>
                    </div>

                </div>

                {{-- ── 3. Bottom Row: Recent Leads & Tasks / Scheduled Jobs ── --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                    {{-- Recent Leads Card --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs transition-colors duration-200">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Recent Leads</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Latest customer inquiries and prospects</p>
                            </div>
                            <a href="{{ route('leads.index') }}" class="text-xs font-semibold text-[#2563eb] dark:text-blue-400 hover:text-blue-700 transition-colors">
                                View All →
                            </a>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800" id="recent-leads-list">
                            <div class="py-8 text-center text-xs text-slate-400">Loading recent leads...</div>
                        </div>
                    </div>

                    {{-- Tasks & Scheduled Jobs Card --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs transition-colors duration-200">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Tasks &amp; Scheduled Work</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Field installation and engineering jobs</p>
                            </div>
                            <a href="{{ route('jobs.index') }}" class="text-xs font-semibold text-[#2563eb] dark:text-blue-400 hover:text-blue-700 transition-colors">
                                View All →
                            </a>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800" id="upcoming-jobs-list">
                            <div class="py-8 text-center text-xs text-slate-400">Loading upcoming jobs...</div>
                        </div>
                    </div>

                </div>

                {{-- ── 4. Secondary Operations Row (Quotations & Job Distribution) ── --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-6">

                    {{-- Quotation Distribution Doughnut --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs transition-colors duration-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Quotation Breakdown</h4>
                            <span class="text-xs text-slate-400">Status</span>
                        </div>
                        <div class="relative" style="height: 180px;">
                            <canvas id="quotationChart"></canvas>
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-2 mt-4 text-[11px]" id="quotation-legend"></div>
                    </div>

                    {{-- Lead Status Breakdown Doughnut --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs transition-colors duration-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Lead Status Share</h4>
                            <span class="text-xs text-slate-400">Pipeline</span>
                        </div>
                        <div class="relative" style="height: 180px;">
                            <canvas id="leadChart"></canvas>
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-2 mt-4 text-[11px]" id="lead-legend"></div>
                    </div>

                    {{-- Installation Jobs Bar Chart --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs transition-colors duration-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Field Job Distribution</h4>
                            <span class="text-xs text-slate-400">Work Orders</span>
                        </div>
                        <div class="relative" style="height: 180px;">
                            <canvas id="jobChart"></canvas>
                        </div>
                    </div>

                </div>

            @else
                {{-- ========================================================================= --}}
                {{-- 🛠️ EMPLOYEE & TECHNICIAN WORK-BASED DASHBOARD (No Analytics / No Revenue) --}}
                {{-- ========================================================================= --}}

                {{-- ── 1. Top 4 Work Metric Cards ── --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">

                    {{-- Open Work Orders Card --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-[#2563eb] dark:text-blue-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                            </div>
                            <span class="inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800">
                                Field Queue
                            </span>
                        </div>
                        <div class="mt-4">
                            <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight" id="staff-open-jobs">0</div>
                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">
                                Open Installation Jobs &middot; <span id="staff-inprogress-jobs">0</span> active in progress
                            </div>
                        </div>
                    </div>

                    {{-- Support & Repair Tickets Card --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <span class="inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded-full bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-800">
                                <span id="staff-critical-tickets">0</span> Critical
                            </span>
                        </div>
                        <div class="mt-4">
                            <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight" id="staff-open-tickets">0</div>
                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">
                                Active Support Tickets &amp; Breakdown Calls
                            </div>
                        </div>
                    </div>

                    {{-- Completed Work Orders Card --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800">
                                Delivered
                            </span>
                        </div>
                        <div class="mt-4">
                            <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight" id="staff-completed-jobs">0</div>
                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">
                                Completed Field Installations &amp; Jobs
                            </div>
                        </div>
                    </div>

                    {{-- My Attendance Card --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded-full" id="staff-att-badge">
                                Today
                            </span>
                        </div>
                        <div class="mt-4">
                            <div class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight capitalize" id="staff-att-status">
                                {{ $myAttendanceToday ? ucfirst($myAttendanceToday->status) : 'Not Clocked In' }}
                            </div>
                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1" id="staff-att-details">
                                @if($myAttendanceToday && $myAttendanceToday->clock_in)
                                    In: {{ \Carbon\Carbon::parse($myAttendanceToday->clock_in)->format('h:i A') }}
                                    @if($myAttendanceToday->clock_out)
                                        &middot; Out: {{ \Carbon\Carbon::parse($myAttendanceToday->clock_out)->format('h:i A') }}
                                    @endif
                                @else
                                    Visit Attendance module to punch in
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ── 2. Middle Row: Scheduled Jobs & Active Service Tickets ── --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">

                    {{-- Tasks & Scheduled Jobs Card --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs transition-colors duration-200">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Tasks &amp; Scheduled Field Jobs</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Upcoming engineering assignments and installations</p>
                            </div>
                            <a href="{{ route('jobs.index') }}" class="text-xs font-semibold text-[#2563eb] dark:text-blue-400 hover:text-blue-700 transition-colors">
                                View All →
                            </a>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800" id="upcoming-jobs-list">
                            <div class="py-8 text-center text-xs text-slate-400">Loading upcoming jobs...</div>
                        </div>
                    </div>

                    {{-- Active Support Tickets Queue --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs transition-colors duration-200">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Active Support &amp; SLA Tickets</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Reported client issues and repair requests</p>
                            </div>
                            <a href="{{ route('service-tickets.index') }}" class="text-xs font-semibold text-[#2563eb] dark:text-blue-400 hover:text-blue-700 transition-colors">
                                View All →
                            </a>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800" id="staff-tickets-list">
                            <div class="py-8 text-center text-xs text-slate-400">Loading service tickets...</div>
                        </div>
                    </div>

                </div>

                {{-- ── 3. Bottom Row: Work Distribution & Operations Hub ── --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                    {{-- Field Job Distribution Chart --}}
                    <div class="lg:col-span-2 bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs transition-colors duration-200">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Field Work Distribution</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Status breakdown of current installation jobs</p>
                            </div>
                        </div>
                        <div class="relative" style="height: 220px;">
                            <canvas id="jobChart"></canvas>
                        </div>
                    </div>

                    {{-- Operational Quick Links Hub --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs transition-colors duration-200 flex flex-col justify-between">
                        <div>
                            <h4 class="text-base font-bold text-slate-900 dark:text-white tracking-tight mb-1">Field Operations Hub</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Quick access to field engineering modules</p>

                            <div class="space-y-2.5">
                                <a href="{{ route('calendar.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 hover:bg-blue-50 dark:hover:bg-blue-950/40 border border-slate-100 dark:border-slate-800 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                                            📅
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">Calendar &amp; Dispatch</div>
                                            <div class="text-[11px] text-slate-400">View daily schedule</div>
                                        </div>
                                    </div>
                                    <span class="text-xs text-blue-600 dark:text-blue-400 font-bold">Open →</span>
                                </a>

                                <a href="{{ route('jcr.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 border border-slate-100 dark:border-slate-800 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                                            📋
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">Job Completion Reports</div>
                                            <div class="text-[11px] text-slate-400">Sign-off reports &amp; PDF</div>
                                        </div>
                                    </div>
                                    <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">Open →</span>
                                </a>

                                <a href="{{ route('equipment.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 hover:bg-purple-50 dark:hover:bg-purple-950/40 border border-slate-100 dark:border-slate-800 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                                            🔍
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">Equipment &amp; Hardware</div>
                                            <div class="text-[11px] text-slate-400">Serial numbers &amp; warranty</div>
                                        </div>
                                    </div>
                                    <span class="text-xs text-purple-600 dark:text-purple-400 font-bold">Open →</span>
                                </a>
                            </div>
                        </div>

                        <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 text-center">
                            <a href="{{ route('attendance.index') }}" class="text-xs font-bold text-[#2563eb] dark:text-blue-400 hover:underline">
                                Go to Daily Attendance System →
                            </a>
                        </div>
                    </div>

                </div>
            @endif

        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js"></script>

    <script>
    "use strict";

    const IS_ADMIN = {{ $isAdmin ? 'true' : 'false' }};

    /* Clean SaaS Colour Palette */
    const C = {
        primary: '#2563eb',
        sky:     '#38bdf8',
        emerald: '#10b981',
        amber:   '#f59e0b',
        purple:  '#8b5cf6',
        rose:    '#f43f5e',
        slate:   '#64748b',
    };

    /* Global chart instances & cache */
    let revenueChart, quotationChart, leadChart, jobChart;
    let latestChartData = null;

    /* Helpers */
    function isDarkTheme() {
        return document.documentElement.classList.contains('dark');
    }

    function money(v) {
        return '₹' + Number(v || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 });
    }

    function escapeHtml(v) {
        const el = document.createElement('div');
        el.textContent = v || '';
        return el.innerHTML;
    }

    function statusBadge(status) {
        const label = (status || '').replace(/_/g, ' ');
        return `<span class="badge badge-${escapeHtml(status)}">${escapeHtml(label)}</span>`;
    }

    function getInitials(name) {
        if (!name) return 'U';
        const parts = name.trim().split(' ');
        if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }

    const baseFont = { family: "'Outfit', 'Plus Jakarta Sans', sans-serif", size: 11 };

    function getGridColor() {
        return isDarkTheme() ? 'rgba(148, 163, 184, 0.12)' : 'rgba(226, 232, 240, 0.7)';
    }

    function getTickColor() {
        return isDarkTheme() ? '#94a3b8' : '#64748b';
    }

    function baseOptions(extra = {}) {
        const dark = isDarkTheme();
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: dark ? '#0f172a' : '#1e293b',
                    titleColor: dark ? '#f8fafc' : '#f8fafc',
                    bodyColor: dark ? '#cbd5e1' : '#cbd5e1',
                    borderColor: dark ? '#334155' : 'transparent',
                    borderWidth: dark ? 1 : 0,
                    padding: 10,
                    cornerRadius: 8,
                    titleFont: { ...baseFont, weight: '700' },
                    bodyFont: { ...baseFont },
                    displayColors: false,
                }
            },
            ...extra
        };
    }

    function buildLegend(containerId, labels, colors) {
        const el = document.getElementById(containerId);
        if (!el) return;
        el.innerHTML = labels.map((lbl, i) => `
            <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                <span class="w-2 h-2 rounded-full" style="background:${colors[i]}"></span>
                ${escapeHtml(lbl)}
            </span>
        `).join('');
    }

    /* Stat card updates */
    function updateStats(data) {
        const s = data.stats || {};
        if (IS_ADMIN) {
            const elTotalLeads = document.getElementById('stat-total-leads');
            if (elTotalLeads) elTotalLeads.textContent = s.total_leads || 0;
            const elLeadsMo = document.getElementById('stat-leads-month');
            if (elLeadsMo) elLeadsMo.textContent = s.leads_this_month || 0;
            const elConv = document.getElementById('stat-conversion');
            if (elConv) elConv.textContent = s.conversion_rate || 0;
            const elWon = document.getElementById('stat-won');
            if (elWon) elWon.textContent = s.won_leads || 0;
            const elLost = document.getElementById('stat-lost');
            if (elLost) elLost.textContent = s.lost_leads || 0;
            const elRev = document.getElementById('stat-revenue');
            if (elRev) elRev.textContent = money(s.revenue_accepted);
            const elRevMo = document.getElementById('stat-revenue-month');
            if (elRevMo) elRevMo.textContent = Number(s.revenue_this_month || 0).toLocaleString('en-IN');
            const elOpenJobs = document.getElementById('stat-open-jobs');
            if (elOpenJobs) elOpenJobs.textContent = s.open_jobs || 0;
            const elInprogJobs = document.getElementById('stat-inprogress-jobs');
            if (elInprogJobs) elInprogJobs.textContent = s.in_progress_jobs || 0;
            
            const elDraft = document.getElementById('stat-draft-quotes');
            if (elDraft) elDraft.textContent = s.draft_quotations || 0;
            const elSent = document.getElementById('stat-sent-quotes');
            if (elSent) elSent.textContent = s.sent_quotations || 0;
            const elAcc = document.getElementById('stat-accepted-quotes');
            if (elAcc) elAcc.textContent = s.accepted_quotations || 0;
            const elRej = document.getElementById('stat-rejected-quotes');
            if (elRej) elRej.textContent = s.rejected_quotations || 0;
            const elExp = document.getElementById('stat-expired-quotes');
            if (elExp) elExp.textContent = s.expired_quotations || 0;

            const elFootRev = document.getElementById('stat-footer-rev');
            if (elFootRev) elFootRev.textContent = money(s.revenue_accepted);
            const elFootRat = document.getElementById('stat-footer-ratio');
            if (elFootRat) elFootRat.textContent = s.conversion_rate || 0;
            const elFootAct = document.getElementById('stat-footer-active');
            if (elFootAct) elFootAct.textContent = ((s.draft_quotations || 0) + (s.sent_quotations || 0));
            const elPipeTot = document.getElementById('stat-pipeline-total');
            if (elPipeTot) elPipeTot.textContent = money(s.revenue_accepted);
        } else {
            // Staff / Technician view
            const elStaffJobs = document.getElementById('staff-open-jobs');
            if (elStaffJobs) elStaffJobs.textContent = s.open_jobs || 0;
            const elStaffInprog = document.getElementById('staff-inprogress-jobs');
            if (elStaffInprog) elStaffInprog.textContent = s.in_progress_jobs || 0;
            const elStaffTickets = document.getElementById('staff-open-tickets');
            if (elStaffTickets) elStaffTickets.textContent = s.open_tickets || 0;
            const elStaffCrit = document.getElementById('staff-critical-tickets');
            if (elStaffCrit) elStaffCrit.textContent = s.critical_tickets || 0;
            const elStaffComp = document.getElementById('staff-completed-jobs');
            if (elStaffComp) elStaffComp.textContent = s.completed_jobs || 0;

            if (data.attendance) {
                const elStatus = document.getElementById('staff-att-status');
                const elBadge = document.getElementById('staff-att-badge');
                const elDetails = document.getElementById('staff-att-details');
                if (elStatus) {
                    elStatus.textContent = data.attendance.clocked_in ? 'Clocked In' : (data.attendance.status === 'absent' ? 'Absent' : 'Not Clocked In');
                }
                if (elBadge) {
                    if (data.attendance.clocked_in) {
                        elBadge.className = 'inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800';
                        elBadge.textContent = 'Active Shift';
                    } else {
                        elBadge.className = 'inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400';
                        elBadge.textContent = 'Off Shift';
                    }
                }
                if (elDetails && data.attendance.clock_in) {
                    elDetails.textContent = 'Clock In: ' + data.attendance.clock_in + (data.attendance.clock_out ? ' · Clock Out: ' + data.attendance.clock_out : ' (On Duty)');
                }
            }
        }
    }

    /* Table & Lists render */
    function updateTables(data) {
        const avatarColors = [
            'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400',
            'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400',
            'bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400',
            'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400',
            'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400',
            'bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400'
        ];

        /* Admin Recent Leads */
        const leadsList = document.getElementById('recent-leads-list');
        if (leadsList && data.recentLeads) {
            leadsList.innerHTML = data.recentLeads.length
                ? data.recentLeads.map((l, idx) => {
                    const colorClass = avatarColors[idx % avatarColors.length];
                    const initials = getInitials(l.customer_name);
                    return `
                    <div class="py-3.5 flex items-center justify-between gap-3 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 -mx-3 px-3 rounded-xl transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full ${colorClass} font-bold text-xs flex items-center justify-center flex-shrink-0">
                                ${escapeHtml(initials)}
                            </div>
                            <div class="truncate">
                                <div class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">${escapeHtml(l.customer_name)}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 truncate">${escapeHtml(l.phone || 'No phone provided')}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            ${statusBadge(l.status)}
                        </div>
                    </div>`;
                }).join('')
                : `<div class="py-8 text-center text-xs text-slate-400">No recent leads found.</div>`;
        }

        /* Upcoming Jobs / Work Orders */
        const jobsList = document.getElementById('upcoming-jobs-list');
        if (jobsList && data.upcomingJobs) {
            jobsList.innerHTML = data.upcomingJobs.length
                ? data.upcomingJobs.map(j => {
                    const date = j.scheduled_date
                        ? new Date(j.scheduled_date).toLocaleDateString('en-IN', {
                            day: '2-digit', month: 'short', year: 'numeric'
                          })
                        : 'Unscheduled';
                    const tech = j.technician ? ` · Tech: ${j.technician.name}` : '';
                    return `
                    <div class="py-3 flex items-center justify-between gap-3 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 -mx-3 px-3 rounded-xl transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="truncate">
                                <div class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">${escapeHtml(j.job_no)}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 truncate">${escapeHtml(j.quotation?.lead?.customer_name || 'Field Job')} &middot; ${date}${escapeHtml(tech)}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            ${statusBadge(j.status)}
                        </div>
                    </div>`;
                }).join('')
                : `<div class="py-8 text-center text-xs text-slate-400">No open field jobs scheduled.</div>`;
        }

        /* Staff / Technician Tickets List */
        const ticketsList = document.getElementById('staff-tickets-list');
        if (ticketsList && data.recentTickets) {
            ticketsList.innerHTML = data.recentTickets.length
                ? data.recentTickets.map(t => {
                    const priorityClass = t.priority === 'critical' ? 'badge-critical' : (t.priority === 'high' ? 'badge-high' : 'badge-medium');
                    return `
                    <div class="py-3 flex items-center justify-between gap-3 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 -mx-3 px-3 rounded-xl transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div class="truncate">
                                <div class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">${escapeHtml(t.ticket_no)} &middot; ${escapeHtml(t.title)}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 truncate">${escapeHtml(t.lead?.customer_name || 'Client')}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <span class="badge ${priorityClass}">${escapeHtml(t.priority)}</span>
                            ${statusBadge(t.status)}
                        </div>
                    </div>`;
                }).join('')
                : `<div class="py-8 text-center text-xs text-slate-400">No active support tickets.</div>`;
        }
    }

    /* Chart creation */
    function createCharts(data) {
        latestChartData = data;

        @if($isAdmin)
        /* 1. Revenue Trend Line */
        const ctxRev = document.getElementById('revenueChart');
        if (ctxRev && data.revenueChart) {
            revenueChart = new Chart(ctxRev, {
                type: 'line',
                data: {
                    labels: data.revenueChart.labels,
                    datasets: [{
                        label: 'Accepted Revenue',
                        data: data.revenueChart.data,
                        borderColor: '#2563eb',
                        backgroundColor: (ctx) => {
                            const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 270);
                            gradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)');
                            gradient.addColorStop(1, 'rgba(37, 99, 235, 0.0)');
                            return gradient;
                        },
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                    }]
                },
                options: baseOptions({
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: baseFont, color: getTickColor() },
                            border: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: getGridColor() },
                            ticks: {
                                font: baseFont,
                                color: getTickColor(),
                                callback: (v) => money(v)
                            },
                            border: { display: false }
                        }
                    },
                    plugins: {
                        ...baseOptions().plugins,
                        tooltip: {
                            ...baseOptions().plugins.tooltip,
                            callbacks: { label: (ctx) => ' ' + money(ctx.parsed.y) }
                        }
                    }
                })
            });
        }

        /* 2. Quotation Pipeline Doughnut */
        const qColors = [C.slate, C.primary, C.emerald, C.rose, C.amber];
        const ctxQuote = document.getElementById('quotationChart');
        if (ctxQuote && data.quotationChart) {
            quotationChart = new Chart(ctxQuote, {
                type: 'doughnut',
                data: {
                    labels: data.quotationChart.labels,
                    datasets: [{
                        data: data.quotationChart.data,
                        backgroundColor: qColors,
                        borderWidth: 2,
                        borderColor: isDarkTheme() ? '#0f172a' : '#ffffff',
                        hoverOffset: 4,
                    }]
                },
                options: baseOptions({
                    cutout: '70%',
                    plugins: {
                        ...baseOptions().plugins,
                        tooltip: {
                            ...baseOptions().plugins.tooltip,
                            callbacks: { label: (ctx) => ` ${ctx.label}: ${ctx.parsed}` }
                        }
                    }
                })
            });
            buildLegend('quotation-legend', data.quotationChart.labels, qColors);
        }

        /* 3. Lead Status Doughnut */
        const lColors = [C.slate, C.sky, C.amber, C.emerald, C.rose];
        const ctxLead = document.getElementById('leadChart');
        if (ctxLead && data.leadChart) {
            leadChart = new Chart(ctxLead, {
                type: 'doughnut',
                data: {
                    labels: data.leadChart.labels,
                    datasets: [{
                        data: data.leadChart.data,
                        backgroundColor: lColors,
                        borderWidth: 2,
                        borderColor: isDarkTheme() ? '#0f172a' : '#ffffff',
                        hoverOffset: 4,
                    }]
                },
                options: baseOptions({
                    cutout: '70%',
                    plugins: {
                        ...baseOptions().plugins,
                        tooltip: {
                            ...baseOptions().plugins.tooltip,
                            callbacks: { label: (ctx) => ` ${ctx.label}: ${ctx.parsed}` }
                        }
                    }
                })
            });
            buildLegend('lead-legend', data.leadChart.labels, lColors);
        }
        @endif

        /* 4. Installation Job Status Bar (Rendered for both Admin & Staff) */
        const jColors = [C.slate, C.primary, C.purple, C.amber, C.emerald, C.rose];
        const ctxJob = document.getElementById('jobChart');
        if (ctxJob && data.jobChart) {
            jobChart = new Chart(ctxJob, {
                type: 'bar',
                data: {
                    labels: data.jobChart.labels,
                    datasets: [{
                        label: 'Jobs',
                        data: data.jobChart.data,
                        backgroundColor: jColors,
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 32,
                    }]
                },
                options: baseOptions({
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: baseFont, color: getTickColor() },
                            border: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: getGridColor() },
                            ticks: {
                                precision: 0,
                                stepSize: 1,
                                font: baseFont,
                                color: getTickColor()
                            },
                            border: { display: false }
                        }
                    },
                    plugins: {
                        ...baseOptions().plugins,
                        tooltip: {
                            ...baseOptions().plugins.tooltip,
                            callbacks: { label: (ctx) => ` ${ctx.parsed.y} job(s)` }
                        }
                    }
                })
            });
        }
    }

    /* Chart updates */
    function updateCharts(data) {
        latestChartData = data;

        @if($isAdmin)
        if (revenueChart && data.revenueChart) {
            revenueChart.data.labels = data.revenueChart.labels;
            revenueChart.data.datasets[0].data = data.revenueChart.data;
            revenueChart.update('active');
        }

        if (quotationChart && data.quotationChart) {
            quotationChart.data.labels = data.quotationChart.labels;
            quotationChart.data.datasets[0].data = data.quotationChart.data;
            quotationChart.data.datasets[0].borderColor = isDarkTheme() ? '#0f172a' : '#ffffff';
            quotationChart.update('active');
            buildLegend('quotation-legend', data.quotationChart.labels,
                [C.slate, C.primary, C.emerald, C.rose, C.amber]);
        }

        if (leadChart && data.leadChart) {
            leadChart.data.labels = data.leadChart.labels;
            leadChart.data.datasets[0].data = data.leadChart.data;
            leadChart.data.datasets[0].borderColor = isDarkTheme() ? '#0f172a' : '#ffffff';
            leadChart.update('active');
            buildLegend('lead-legend', data.leadChart.labels,
                [C.slate, C.sky, C.amber, C.emerald, C.rose]);
        }
        @endif

        if (jobChart && data.jobChart) {
            jobChart.data.labels = data.jobChart.labels;
            jobChart.data.datasets[0].data = data.jobChart.data;
            jobChart.update('active');
        }
    }

    /* Dynamic Chart Theme Switching */
    window.addEventListener('theme-changed', () => {
        if (!latestChartData) return;

        const grid = getGridColor();
        const tick = getTickColor();
        const borderColor = isDarkTheme() ? '#0f172a' : '#ffffff';

        @if($isAdmin)
        if (revenueChart) {
            revenueChart.options.scales.x.ticks.color = tick;
            revenueChart.options.scales.y.ticks.color = tick;
            revenueChart.options.scales.y.grid.color  = grid;
            revenueChart.update();
        }

        if (quotationChart) {
            quotationChart.data.datasets[0].borderColor = borderColor;
            quotationChart.update();
            buildLegend('quotation-legend', latestChartData.quotationChart.labels,
                [C.slate, C.primary, C.emerald, C.rose, C.amber]);
        }

        if (leadChart) {
            leadChart.data.datasets[0].borderColor = borderColor;
            leadChart.update();
            buildLegend('lead-legend', latestChartData.leadChart.labels,
                [C.slate, C.sky, C.amber, C.emerald, C.rose]);
        }
        @endif

        if (jobChart) {
            jobChart.options.scales.x.ticks.color = tick;
            jobChart.options.scales.y.ticks.color = tick;
            jobChart.options.scales.y.grid.color  = grid;
            jobChart.update();
        }
    });

    /* Main refresh */
    const dataUrl = "{{ route('dashboard.data') }}";

    async function refreshDashboard(firstLoad = false) {
        const errorBox = document.getElementById('dashboard-error');
        try {
            const res = await fetch(dataUrl, { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            updateStats(data);
            updateTables(data);

            if (firstLoad) {
                createCharts(data);
            } else {
                updateCharts(data);
            }

            if (errorBox) {
                errorBox.classList.remove('visible');
                errorBox.textContent = '';
            }
            const updateElem = document.getElementById('last-updated');
            if (updateElem) {
                updateElem.textContent = 'Updated ' + new Date().toLocaleTimeString('en-IN', {
                    hour: '2-digit', minute: '2-digit', second: '2-digit'
                });
            }

        } catch (err) {
            console.error('Dashboard error:', err);
            if (errorBox) {
                errorBox.textContent = 'Dashboard could not refresh: ' + err.message;
                errorBox.classList.add('visible');
            }
            const updateElem = document.getElementById('last-updated');
            if (updateElem) {
                updateElem.textContent = 'Update failed';
            }
        }
    }

    refreshDashboard(true);
    setInterval(() => refreshDashboard(false), 30_000);
    </script>
</x-app-layout>