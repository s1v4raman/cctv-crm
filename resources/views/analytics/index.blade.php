<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold leading-tight text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-[0_0_10px_#f59e0b]"></span>
                    Executive Business Analytics &amp; Profitability
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 font-medium">Financial health, gross margin analysis, recurring revenue &amp; technician scorecard</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('analytics.export-pdf', ['range' => $selectedRange, 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"
                   class="crm-btn-primary btn-amber inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-xs font-extrabold shadow-md transition"
                   style="background-color: var(--crm-accent, #2563eb);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Export Executive PDF
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Include Chart.js via CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <div class="pg-wrap">
        <div class="pg-inner space-y-6">

            {{-- Navigation Sub-Tabs --}}
            <div class="flex items-center gap-2 border-b border-slate-200 dark:border-white/5 pb-3 overflow-x-auto">
                <a href="{{ route('analytics.index', ['range' => $selectedRange]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request()->routeIs('analytics.index') ? 'crm-tab-active text-white shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}"
                   @if(request()->routeIs('analytics.index')) style="background-color: var(--crm-accent, #2563eb); color: #fff;" @endif>
                    📊 Executive Overview
                </a>
                <a href="{{ route('analytics.technicians', ['range' => $selectedRange]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request()->routeIs('analytics.technicians') ? 'crm-tab-active text-white shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}"
                   @if(request()->routeIs('analytics.technicians')) style="background-color: var(--crm-accent, #2563eb); color: #fff;" @endif>
                    🛠️ Technician Performance (FTFR &amp; MTTR)
                </a>
                <a href="{{ route('analytics.mrr-retention', ['range' => $selectedRange]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request()->routeIs('analytics.mrr-retention') ? 'crm-tab-active text-white shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}"
                   @if(request()->routeIs('analytics.mrr-retention')) style="background-color: var(--crm-accent, #2563eb); color: #fff;" @endif>
                    📈 Monthly Revenue &amp; AMC Retention
                </a>
                <a href="{{ route('analytics.cost-profit', ['range' => $selectedRange]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request()->routeIs('analytics.cost-profit') ? 'crm-tab-active text-white shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}"
                   @if(request()->routeIs('analytics.cost-profit')) style="background-color: var(--crm-accent, #2563eb); color: #fff;" @endif>
                    💰 Cost &amp; Profit Analysis
                </a>
            </div>

            {{-- Date Range Filter Bar --}}
            <div class="pg-card p-4 flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <span class="text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px] mr-2">Timeframe:</span>
                    <a href="{{ route('analytics.index', ['range' => 'today']) }}"
                       class="px-3 py-1.5 rounded-xl font-bold transition {{ $selectedRange === 'today' ? 'crm-pill-active text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300' }}"
                       @if($selectedRange === 'today') style="background-color: var(--crm-accent, #2563eb);" @endif>
                        Today
                    </a>
                    <a href="{{ route('analytics.index', ['range' => 'this_month']) }}"
                       class="px-3 py-1.5 rounded-xl font-bold transition {{ $selectedRange === 'this_month' ? 'crm-pill-active text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300' }}"
                       @if($selectedRange === 'this_month') style="background-color: var(--crm-accent, #2563eb);" @endif>
                        This Month
                    </a>
                    <a href="{{ route('analytics.index', ['range' => 'last_month']) }}"
                       class="px-3 py-1.5 rounded-xl font-bold transition {{ $selectedRange === 'last_month' ? 'crm-pill-active text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300' }}"
                       @if($selectedRange === 'last_month') style="background-color: var(--crm-accent, #2563eb);" @endif>
                        Last Month
                    </a>
                    <a href="{{ route('analytics.index', ['range' => 'this_quarter']) }}"
                       class="px-3 py-1.5 rounded-xl font-bold transition {{ $selectedRange === 'this_quarter' ? 'crm-pill-active text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300' }}"
                       @if($selectedRange === 'this_quarter') style="background-color: var(--crm-accent, #2563eb);" @endif>
                        This Quarter
                    </a>
                    <a href="{{ route('analytics.index', ['range' => 'this_year']) }}"
                       class="px-3 py-1.5 rounded-xl font-bold transition {{ $selectedRange === 'this_year' ? 'crm-pill-active text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300' }}"
                       @if($selectedRange === 'this_year') style="background-color: var(--crm-accent, #2563eb);" @endif>
                        This Year (FY)
                    </a>
                    <a href="{{ route('analytics.index', ['range' => 'all_time']) }}"
                       class="px-3 py-1.5 rounded-xl font-bold transition {{ $selectedRange === 'all_time' ? 'crm-pill-active text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300' }}"
                       @if($selectedRange === 'all_time') style="background-color: var(--crm-accent, #2563eb);" @endif>
                        All Time
                    </a>
                </div>

                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-900 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/5">
                    Active Window: <strong class="text-slate-900 dark:text-white">{{ $overview['date_range']['label'] }}</strong>
                </div>
            </div>

            {{-- 9 Master Financial KPI Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-9 gap-3">

                {{-- 1. Total Revenue (with tax) --}}
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-white/5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] flex flex-col justify-between" style="min-height:110px">
                    <div class="flex items-start justify-between gap-1">
                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block leading-tight">Total Revenue</span>
                        <div class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center text-xs font-bold shrink-0 border border-blue-500/20">₹</div>
                    </div>
                    <div>
                        <div class="text-base font-extrabold text-white mt-1 leading-tight font-['Outfit']">₹{{ number_format($overview['financials']['invoiced_revenue'], 0) }}</div>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Incl. GST billed</span>
                    </div>
                </div>

                {{-- 2. Revenue Without Tax (Subtotal) --}}
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-sky-500/30 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] flex flex-col justify-between" style="min-height:110px">
                    <div class="flex items-start justify-between gap-1">
                        <span class="text-[9px] font-bold uppercase tracking-wider text-sky-400 block leading-tight">Revenue (No Tax)</span>
                        <div class="w-7 h-7 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center text-xs font-bold shrink-0 border border-sky-500/20">💹</div>
                    </div>
                    <div>
                        <div class="text-base font-extrabold text-sky-300 mt-1 leading-tight font-['Outfit']">₹{{ number_format($overview['financials']['revenue_without_tax'] ?? round($overview['financials']['invoiced_revenue']/1.18, 0), 0) }}</div>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Pre-tax subtotal</span>
                    </div>
                </div>

                {{-- 3. Product Buy Cost (COGS) --}}
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-orange-500/20 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] flex flex-col justify-between" style="min-height:110px">
                    <div class="flex items-start justify-between gap-1">
                        <span class="text-[9px] font-bold uppercase tracking-wider text-orange-400 block leading-tight">Product Buy Cost</span>
                        <div class="w-7 h-7 rounded-lg bg-orange-500/10 text-orange-400 flex items-center justify-center text-xs font-bold shrink-0 border border-orange-500/20">📦</div>
                    </div>
                    <div>
                        <div class="text-base font-extrabold text-orange-300 mt-1 leading-tight font-['Outfit']">₹{{ number_format($overview['financials']['product_buy_cost'] ?? $overview['financials']['cogs'], 0) }}</div>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Hardware COGS</span>
                    </div>
                </div>

                {{-- 4. Tax Paid Cost (Input GST on POs) --}}
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-red-500/20 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] flex flex-col justify-between" style="min-height:110px">
                    <div class="flex items-start justify-between gap-1">
                        <span class="text-[9px] font-bold uppercase tracking-wider text-red-400 block leading-tight">Tax Paid Cost</span>
                        <div class="w-7 h-7 rounded-lg bg-red-500/10 text-red-400 flex items-center justify-center text-xs font-bold shrink-0 border border-red-500/20">🧾</div>
                    </div>
                    <div>
                        <div class="text-base font-extrabold text-red-300 mt-1 leading-tight font-['Outfit']">₹{{ number_format($overview['financials']['tax_paid_cost'] ?? 0, 0) }}</div>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Input GST on POs</span>
                    </div>
                </div>

                {{-- 5. Salary Credited Cost --}}
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-violet-500/30 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] flex flex-col justify-between" style="min-height:110px">
                    <div class="flex items-start justify-between gap-1">
                        <span class="text-[9px] font-bold uppercase tracking-wider text-violet-400 block leading-tight">Salary Credited</span>
                        <div class="w-7 h-7 rounded-lg bg-violet-500/10 text-violet-400 flex items-center justify-center text-xs font-bold shrink-0 border border-violet-500/20">👥</div>
                    </div>
                    <div>
                        <div class="text-base font-extrabold text-violet-300 mt-1 leading-tight font-['Outfit']">₹{{ number_format($overview['financials']['salary_credited_cost'] ?? $overview['financials']['employee_salary_cost'] ?? 0, 0) }}</div>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Staff payroll cost</span>
                    </div>
                </div>

                {{-- 6. Gross Profit (No Tax) --}}
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-emerald-500/30 bg-gradient-to-br from-[#0f172a] to-emerald-950/20 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] flex flex-col justify-between" style="min-height:110px">
                    <div class="flex items-start justify-between gap-1">
                        <span class="text-[9px] font-bold uppercase tracking-wider text-emerald-400 block leading-tight">Gross Profit</span>
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xs font-bold shrink-0 border border-emerald-500/30">💎</div>
                    </div>
                    <div>
                        <div class="text-base font-extrabold text-emerald-400 mt-1 leading-tight font-['Outfit']">₹{{ number_format($overview['financials']['gross_profit_without_tax'] ?? $overview['financials']['gross_profit'], 0) }}</div>
                        <span class="text-[10px] font-bold text-emerald-400 block mt-0.5">{{ $overview['financials']['gross_margin_without_tax_percent'] ?? $overview['financials']['gross_margin_percent'] }}% margin</span>
                    </div>
                </div>

                {{-- 7. Net Profit WITHOUT Tax & Salary --}}
                @php
                    $netProfit = $overview['financials']['net_profit_without_tax'] ?? ($overview['financials']['gross_profit_without_tax'] ?? $overview['financials']['gross_profit']) - ($overview['financials']['employee_salary_cost'] ?? 0);
                    $netColor = $netProfit >= 0 ? 'text-teal-300' : 'text-rose-400';
                    $netBorder = $netProfit >= 0 ? 'border-teal-500/30' : 'border-rose-500/30';
                @endphp
                <div class="bg-[#0f172a] p-4 rounded-2xl border {{ $netBorder }} shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] flex flex-col justify-between" style="min-height:110px">
                    <div class="flex items-start justify-between gap-1">
                        <span class="text-[9px] font-bold uppercase tracking-wider {{ $netProfit >= 0 ? 'text-teal-400' : 'text-rose-400' }} block leading-tight">Net Op. Profit</span>
                        <div class="w-7 h-7 rounded-lg {{ $netProfit >= 0 ? 'bg-teal-500/10 text-teal-400 border-teal-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }} flex items-center justify-center text-xs font-bold shrink-0 border">{{ $netProfit >= 0 ? '📈' : '📉' }}</div>
                    </div>
                    <div>
                        <div class="text-base font-extrabold {{ $netColor }} mt-1 leading-tight font-['Outfit']">₹{{ number_format($netProfit, 0) }}</div>
                        <span class="text-[10px] text-slate-400 block mt-0.5">No tax · No salary</span>
                    </div>
                </div>

                {{-- 8. Cash Collected --}}
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-white/5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] flex flex-col justify-between" style="min-height:110px">
                    <div class="flex items-start justify-between gap-1">
                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block leading-tight">Cash Collected</span>
                        <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-xs font-bold shrink-0 border border-indigo-500/20">💳</div>
                    </div>
                    <div>
                        <div class="text-base font-extrabold text-sky-400 mt-1 leading-tight font-['Outfit']">₹{{ number_format($overview['financials']['cash_collected'], 0) }}</div>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Realized inflow</span>
                    </div>
                </div>

                {{-- 9. AMC Recurring ARR --}}
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-white/5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] flex flex-col justify-between" style="min-height:110px">
                    <div class="flex items-start justify-between gap-1">
                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block leading-tight">AMC Portfolio</span>
                        <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center text-xs font-bold shrink-0 border border-amber-500/20">🔄</div>
                    </div>
                    <div>
                        <div class="text-base font-extrabold text-amber-400 mt-1 leading-tight font-['Outfit']">₹{{ number_format($overview['amc']['arr'], 0) }}</div>
                        <span class="text-[10px] text-slate-400 block mt-0.5">{{ $overview['amc']['active_contracts'] }} contracts</span>
                    </div>
                </div>

            </div>

            {{-- Cost Breakdown Highlight Bar --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3.5 flex flex-wrap items-center gap-x-6 gap-y-2 shadow-xs">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Cost Waterfall</span>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-sky-500 inline-block"></span>
                    <span class="text-[11px] text-slate-600 dark:text-slate-300 font-semibold">Revenue (No Tax): <strong class="text-sky-700 dark:text-sky-300">₹{{ number_format($overview['financials']['revenue_without_tax'] ?? round($overview['financials']['invoiced_revenue']/1.18, 0), 0) }}</strong></span>
                </div>
                <span class="text-slate-400 dark:text-slate-600 text-sm">−</span>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-orange-500 inline-block"></span>
                    <span class="text-[11px] text-slate-600 dark:text-slate-300 font-semibold">Product Buy: <strong class="text-orange-700 dark:text-orange-300">₹{{ number_format($overview['financials']['product_buy_cost'] ?? $overview['financials']['cogs'], 0) }}</strong></span>
                </div>
                <span class="text-slate-400 dark:text-slate-600 text-sm">−</span>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-violet-500 inline-block"></span>
                    <span class="text-[11px] text-slate-600 dark:text-slate-300 font-semibold">Salary: <strong class="text-violet-700 dark:text-violet-300">₹{{ number_format($overview['financials']['salary_credited_cost'] ?? $overview['financials']['employee_salary_cost'] ?? 0, 0) }}</strong></span>
                </div>
                <span class="text-slate-400 dark:text-slate-600 text-sm">=</span>
                @php $waterNetProfit = $overview['financials']['net_profit_without_tax'] ?? (($overview['financials']['revenue_without_tax'] ?? round($overview['financials']['invoiced_revenue']/1.18, 0)) - ($overview['financials']['product_buy_cost'] ?? $overview['financials']['cogs']) - ($overview['financials']['employee_salary_cost'] ?? 0)); @endphp
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full {{ $waterNetProfit >= 0 ? 'bg-teal-500' : 'bg-rose-500' }} inline-block"></span>
                    <span class="text-[11px] font-bold {{ $waterNetProfit >= 0 ? 'text-teal-700 dark:text-teal-300' : 'text-rose-600 dark:text-rose-400' }}">NET: ₹{{ number_format($waterNetProfit, 0) }}</span>
                </div>
                <a href="{{ route('analytics.cost-profit', ['range' => $selectedRange]) }}" class="ml-auto text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-300 dark:border-emerald-500/30 px-3 py-1.5 rounded-xl transition shadow-2xs">
                    Full Analysis →
                </a>
            </div>

            {{-- 4 Chart.js Interactive Visualizations --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Chart 1: 12-Month Financial Performance & Profitability Trend --}}
                <div class="bg-[#0f172a] p-5 rounded-2xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-white">12-Month Financial Performance Trend</h3>
                            <p class="text-[11px] text-slate-400">Revenue (No Tax) · Product Buy Cost · Salary · Net Profit</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="financialsTrendChart"></canvas>
                    </div>
                </div>

                {{-- Chart 2: Revenue Streams Breakdown (Doughnut) --}}
                <div class="bg-[#0f172a] p-5 rounded-2xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-white">Revenue Streams Contribution</h3>
                            <p class="text-[11px] text-slate-400">Installation Projects vs AMC Subscriptions vs Service Repairs</p>
                        </div>
                    </div>
                    <div class="h-64 flex items-center justify-center">
                        <canvas id="revenueStreamsChart"></canvas>
                    </div>
                </div>

                {{-- Chart 3: Sales & Lead Funnel Pipeline --}}
                <div class="bg-[#0f172a] p-5 rounded-2xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-white">Sales Conversion Funnel</h3>
                            <p class="text-[11px] text-slate-400">Lead Inquiries &rarr; Site Surveys &rarr; Quotes &rarr; Won Projects</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-400 font-bold text-xs border border-amber-500/20">
                            {{ $overview['lead_funnel']['conversion_rate'] }}% Conversion Rate
                        </span>
                    </div>
                    <div class="h-60">
                        <canvas id="leadFunnelChart"></canvas>
                    </div>
                </div>

                {{-- Chart 4: Accounts Receivable Aging Analysis --}}
                <div class="bg-[#0f172a] p-5 rounded-2xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-white">Accounts Receivable Aging Buckets</h3>
                            <p class="text-[11px] text-slate-400">Outstanding invoice balances by days overdue</p>
                        </div>
                    </div>
                    <div class="h-60">
                        <canvas id="agingBucketsChart"></canvas>
                    </div>
                </div>

            </div>

            {{-- Tables: Technician Performance Scorecard & Top Products --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Technician Scorecard --}}
                <div class="bg-[#0f172a] rounded-2xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5 overflow-hidden">
                    <div class="p-4 bg-[#0b1120] border-b border-white/5 flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-white">Field Engineering & CSAT Scorecard</h3>
                            <p class="text-[11px] text-slate-400">Technician job completions & customer satisfaction ratings</p>
                        </div>
                    </div>
                    <table class="min-w-full divide-y divide-white/5 text-xs">
                        <thead class="bg-[#0b1120]/50">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-bold text-slate-400">Technician</th>
                                <th class="px-4 py-2.5 text-center font-bold text-slate-400">Jobs</th>
                                <th class="px-4 py-2.5 text-center font-bold text-slate-400">Tickets</th>
                                <th class="px-4 py-2.5 text-center font-bold text-slate-400">JCR Signed</th>
                                <th class="px-4 py-2.5 text-right font-bold text-slate-400">CSAT ⭐</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($overview['technicians'] as $tech)
                                <tr class="hover:bg-slate-800/50 transition">
                                    <td class="px-4 py-3 font-bold text-slate-200">
                                        {{ $tech['name'] }}
                                        <span class="text-[10px] text-slate-500 block font-normal capitalize">{{ $tech['role'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-semibold text-slate-300">{{ $tech['jobs_completed'] }}</td>
                                    <td class="px-4 py-3 text-center font-semibold text-slate-300">{{ $tech['tickets_resolved'] }}</td>
                                    <td class="px-4 py-3 text-center font-semibold text-sky-400">{{ $tech['jcr_signoffs'] }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="inline-block px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/30 font-bold text-[11px]">
                                            ⭐ {{ $tech['average_rating'] }} / 5.0
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-slate-500">No technician performance data recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Top Margin Products --}}
                <div class="bg-[#0f172a] rounded-2xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5 overflow-hidden">
                    <div class="p-4 bg-[#0b1120] border-b border-white/5 flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-white">High-Margin CCTV Catalog Assets</h3>
                            <p class="text-[11px] text-slate-400">Highest gross margin hardware products</p>
                        </div>
                    </div>
                    <table class="min-w-full divide-y divide-white/5 text-xs">
                        <thead class="bg-[#0b1120]/50">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-bold text-slate-400">Product Model</th>
                                <th class="px-4 py-2.5 text-right font-bold text-slate-400">Selling Price</th>
                                <th class="px-4 py-2.5 text-right font-bold text-slate-400">Cost Price</th>
                                <th class="px-4 py-2.5 text-right font-bold text-slate-400">Margin %</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($overview['top_products'] as $prod)
                                <tr class="hover:bg-slate-800/50 transition">
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-slate-200">{{ $prod['name'] }}</div>
                                        <div class="text-[10px] text-slate-500 font-mono">{{ $prod['model_no'] }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-amber-400">₹{{ number_format($prod['unit_price'], 2) }}</td>
                                    <td class="px-4 py-3 text-right text-slate-400 font-mono">₹{{ number_format($prod['cost_price'], 2) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-emerald-400">
                                        +{{ $prod['margin_percent'] }}%
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-slate-500">No active products in catalog.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>

    {{-- Initialize Chart.js Scripts with Dark Theme --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Chart.defaults.color = '#94a3b8';
            Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.05)';
            
            // 1. Monthly Financials & Profitability Trend Chart
            const trendCtx = document.getElementById('financialsTrendChart').getContext('2d');
            new Chart(trendCtx, {
                type: 'bar',
                data: {
                    labels: @json($overview['monthly_trend']['labels']),
                    datasets: [
                        {
                            label: 'Revenue (No Tax) ₹',
                            data: @json($overview['monthly_trend']['revenue_without_tax'] ?? $overview['monthly_trend']['revenue']),
                            backgroundColor: 'rgba(56,189,248,0.75)',
                            borderRadius: 5,
                            order: 1,
                        },
                        {
                            label: 'Product Buy Cost ₹',
                            data: @json($overview['monthly_trend']['cogs']),
                            backgroundColor: 'rgba(251,146,60,0.75)',
                            borderRadius: 5,
                            order: 2,
                        },
                        {
                            label: 'Salary Cost ₹',
                            data: @json($overview['monthly_trend']['salary'] ?? $overview['monthly_trend']['salary_cost'] ?? []),
                            backgroundColor: 'rgba(167,139,250,0.75)',
                            borderRadius: 5,
                            order: 3,
                        },
                        {
                            label: 'Net Profit (No Tax) ₹',
                            data: @json($overview['monthly_trend']['net_profit_without_tax'] ?? $overview['monthly_trend']['profit']),
                            type: 'line',
                            borderColor: '#2dd4bf',
                            backgroundColor: 'rgba(45,212,191,0.15)',
                            borderWidth: 2.5,
                            pointBackgroundColor: '#2dd4bf',
                            pointRadius: 3,
                            fill: true,
                            tension: 0.4,
                            order: 0,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 10, font: { size: 10 }, color: '#cbd5e1' } },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) { return ' ₹' + ctx.parsed.y.toLocaleString('en-IN', {maximumFractionDigits:0}); }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { font: { size: 10 }, callback: v => '₹' + (v/1000).toFixed(0) + 'k' } },
                        x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                    }
                }
            });

            // 2. Revenue Streams Breakdown (Doughnut)
            const streamsCtx = document.getElementById('revenueStreamsChart').getContext('2d');
            new Chart(streamsCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Project Installations', 'AMC Contracts', 'Service Ticket Repairs'],
                    datasets: [{
                        data: [
                            {{ $overview['revenue_streams']['projects'] }},
                            {{ $overview['revenue_streams']['amc'] }},
                            {{ $overview['revenue_streams']['service'] }}
                        ],
                        backgroundColor: ['#3b82f6', '#f59e0b', '#06b6d4'],
                        borderWidth: 2,
                        borderColor: '#0f172a',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 }, color: '#cbd5e1' } }
                    }
                }
            });

            // 3. Sales Conversion Funnel Chart
            const funnelCtx = document.getElementById('leadFunnelChart').getContext('2d');
            new Chart(funnelCtx, {
                type: 'bar',
                data: {
                    labels: ['Lead Inquiries', 'Site Surveys', 'Quotations Issued', 'Won Projects'],
                    datasets: [{
                        label: 'Count',
                        data: [
                            {{ $overview['lead_funnel']['total_leads'] }},
                            {{ $overview['lead_funnel']['surveys'] }},
                            {{ $overview['lead_funnel']['quotations'] }},
                            {{ $overview['lead_funnel']['won_projects'] }}
                        ],
                        backgroundColor: ['#6366f1', '#38bdf8', '#fbbf24', '#10b981'],
                        borderRadius: 6,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { font: { size: 10 } } },
                        y: { grid: { display: false }, ticks: { font: { size: 11 } } }
                    }
                }
            });

            // 4. Receivables Aging Buckets Chart
            const agingCtx = document.getElementById('agingBucketsChart').getContext('2d');
            new Chart(agingCtx, {
                type: 'bar',
                data: {
                    labels: ['Current (Not Due)', '1 - 30 Days Overdue', '31 - 60 Days Overdue', '60+ Days Overdue'],
                    datasets: [{
                        label: 'Outstanding (₹)',
                        data: [
                            {{ $overview['financials']['aging_buckets']['current'] }},
                            {{ $overview['financials']['aging_buckets']['1_30'] }},
                            {{ $overview['financials']['aging_buckets']['31_60'] }},
                            {{ $overview['financials']['aging_buckets']['60_plus'] }}
                        ],
                        backgroundColor: ['#3b82f6', '#f59e0b', '#f97316', '#ef4444'],
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { font: { size: 10 } } },
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                    }
                }
            });

        });
    </script>
</x-app-layout>
