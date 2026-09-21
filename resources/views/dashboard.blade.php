<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight font-heading">
                        Operations Command Center
                    </h2>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Grid
                    </span>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    CCTV Operations, Automated Quotations & Field Engineering Telemetry
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('leads.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-white bg-[#2563eb] hover:bg-blue-700 rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Lead
                </a>
                
                <a href="{{ route('quotations.create-general') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-[#0f172a] hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    New Quote
                </a>

                <a href="{{ route('jobs.create-general') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-[#0f172a] hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
                    </svg>
                    New Job
                </a>

                <div class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400 bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 px-3 py-2 rounded-xl shadow-xs">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="last-updated">Connecting live grid...</span>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- ── Modern SaaS Light & Dark Styles ── --}}
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

            {{-- ── 1. Top 4 Metric Cards ── --}}
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
                            ↑ 12.5% this mo
                        </span>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight" id="stat-total-leads">—</div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">
                            Total Leads &middot; <strong class="text-slate-700 dark:text-slate-200 font-semibold" id="stat-leads-month">0</strong> added this month
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
                            ↑ 2.4% avg
                        </span>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            <span id="stat-conversion">0</span>%
                        </div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">
                            Conversion Rate &middot; <strong class="text-slate-700 dark:text-slate-200 font-semibold" id="stat-won">0</strong> won / <strong class="text-slate-700 dark:text-slate-200 font-semibold" id="stat-lost">0</strong> lost
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
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
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
                        {{-- Populated dynamically via updateTables --}}
                        <div class="py-8 text-center text-xs text-slate-400">Loading recent leads...</div>
                    </div>
                </div>

                {{-- Tasks & Scheduled Jobs Card --}}
                <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs transition-colors duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Tasks & Scheduled Work</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Field installation and engineering jobs</p>
                        </div>
                        <a href="{{ route('jobs.index') }}" class="text-xs font-semibold text-[#2563eb] dark:text-blue-400 hover:text-blue-700 transition-colors">
                            View All →
                        </a>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800" id="upcoming-jobs-list">
                        {{-- Populated dynamically via updateTables --}}
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

        </div>
    </div>

    {{-- ── Chart.js ── --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js"></script>

    <script>
    "use strict";

    /* ─── Clean SaaS Colour Palette ─── */
    const C = {
        primary: '#2563eb', // Royal Blue
        sky:     '#38bdf8', // Sky Blue
        emerald: '#10b981', // Mint / Emerald
        amber:   '#f59e0b', // Soft Amber
        purple:  '#8b5cf6', // Lilac Purple
        rose:    '#f43f5e', // Rose
        slate:   '#64748b', // Slate Gray
    };

    /* ─── Global chart instances & cache ─── */
    let revenueChart, quotationChart, leadChart, jobChart;
    let latestChartData = null;

    /* ─── Helpers ─── */
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

    /* ─── Chart defaults with theme awareness ─── */
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

    /* ─── Build custom HTML legend ─── */
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

    /* ─── Stat card updates ─── */
    function updateStats(s) {
        document.getElementById('stat-total-leads').textContent   = s.total_leads;
        document.getElementById('stat-leads-month').textContent   = s.leads_this_month;
        document.getElementById('stat-conversion').textContent    = s.conversion_rate;
        document.getElementById('stat-won').textContent           = s.won_leads;
        document.getElementById('stat-lost').textContent          = s.lost_leads;
        document.getElementById('stat-revenue').textContent       = money(s.revenue_accepted);
        document.getElementById('stat-revenue-month').textContent =
            Number(s.revenue_this_month || 0).toLocaleString('en-IN');
        document.getElementById('stat-open-jobs').textContent      = s.open_jobs;
        document.getElementById('stat-inprogress-jobs').textContent = s.in_progress_jobs;
        
        // Pipeline counts
        document.getElementById('stat-draft-quotes').textContent    = s.draft_quotations;
        document.getElementById('stat-sent-quotes').textContent     = s.sent_quotations;
        document.getElementById('stat-accepted-quotes').textContent = s.accepted_quotations;
        document.getElementById('stat-rejected-quotes').textContent = s.rejected_quotations;
        document.getElementById('stat-expired-quotes').textContent  = s.expired_quotations;

        // Sales mini footer
        document.getElementById('stat-footer-rev').textContent    = money(s.revenue_accepted);
        document.getElementById('stat-footer-ratio').textContent  = s.conversion_rate;
        document.getElementById('stat-footer-active').textContent = (s.draft_quotations + s.sent_quotations);
        document.getElementById('stat-pipeline-total').textContent = money(s.revenue_accepted);
    }

    /* ─── Table and Lists render ─── */
    function updateTables(data) {
        const avatarColors = [
            'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400',
            'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400',
            'bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400',
            'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400',
            'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400',
            'bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400'
        ];

        /* Recent Leads List */
        const leadsList = document.getElementById('recent-leads-list');
        if (leadsList) {
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
        if (jobsList) {
            jobsList.innerHTML = data.upcomingJobs.length
                ? data.upcomingJobs.map(j => {
                    const date = j.scheduled_date
                        ? new Date(j.scheduled_date).toLocaleDateString('en-IN', {
                            day: '2-digit', month: 'short', year: 'numeric'
                          })
                        : 'Unscheduled';
                    return `
                    <div class="py-3.5 flex items-center justify-between gap-3 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 -mx-3 px-3 rounded-xl transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="truncate">
                                <div class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">${escapeHtml(j.job_no)}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 truncate">${escapeHtml(j.quotation?.lead?.customer_name || 'Client assignment')} &middot; ${date}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            ${statusBadge(j.status)}
                        </div>
                    </div>`;
                }).join('')
                : `<div class="py-8 text-center text-xs text-slate-400">No open jobs scheduled.</div>`;
        }
    }

    /* ─── Chart creation ─── */
    function createCharts(data) {
        latestChartData = data;

        /* 1. Revenue Trend Line */
        const ctxRev = document.getElementById('revenueChart');
        if (ctxRev) {
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
        if (ctxQuote) {
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
        if (ctxLead) {
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

        /* 4. Installation Job Status Bar */
        const jColors = [C.slate, C.primary, C.purple, C.amber, C.emerald, C.rose];
        const ctxJob = document.getElementById('jobChart');
        if (ctxJob) {
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
                        maxBarThickness: 28,
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

    /* ─── Chart updates (live refresh) ─── */
    function updateCharts(data) {
        latestChartData = data;

        if (revenueChart) {
            revenueChart.data.labels              = data.revenueChart.labels;
            revenueChart.data.datasets[0].data    = data.revenueChart.data;
            revenueChart.update('active');
        }

        if (quotationChart) {
            quotationChart.data.labels            = data.quotationChart.labels;
            quotationChart.data.datasets[0].data  = data.quotationChart.data;
            quotationChart.data.datasets[0].borderColor = isDarkTheme() ? '#0f172a' : '#ffffff';
            quotationChart.update('active');
            buildLegend('quotation-legend', data.quotationChart.labels,
                [C.slate, C.primary, C.emerald, C.rose, C.amber]);
        }

        if (leadChart) {
            leadChart.data.labels                 = data.leadChart.labels;
            leadChart.data.datasets[0].data       = data.leadChart.data;
            leadChart.data.datasets[0].borderColor = isDarkTheme() ? '#0f172a' : '#ffffff';
            leadChart.update('active');
            buildLegend('lead-legend', data.leadChart.labels,
                [C.slate, C.sky, C.amber, C.emerald, C.rose]);
        }

        if (jobChart) {
            jobChart.data.labels                  = data.jobChart.labels;
            jobChart.data.datasets[0].data        = data.jobChart.data;
            jobChart.update('active');
        }
    }

    /* ─── Dynamic Chart Theme Switching ─── */
    window.addEventListener('theme-changed', () => {
        if (!latestChartData) return;

        const grid = getGridColor();
        const tick = getTickColor();
        const borderColor = isDarkTheme() ? '#0f172a' : '#ffffff';

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

        if (jobChart) {
            jobChart.options.scales.x.ticks.color = tick;
            jobChart.options.scales.y.ticks.color = tick;
            jobChart.options.scales.y.grid.color  = grid;
            jobChart.update();
        }
    });

    /* ─── Main refresh ─── */
    const dataUrl = "{{ route('dashboard.data') }}";

    async function refreshDashboard(firstLoad = false) {
        const errorBox = document.getElementById('dashboard-error');
        try {
            const res = await fetch(dataUrl, { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            updateStats(data.stats);
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