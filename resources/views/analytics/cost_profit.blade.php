<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold leading-tight text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_10px_#10b981]"></span>
                    Pre-Tax Cost &amp; Net Profit Analysis
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 font-medium">Operating profit calculated without tax, minus employee salary disbursements &amp; hardware buy cost</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('analytics.cost-profit.export-pdf', ['range' => $selectedRange, 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"
                   class="crm-btn-primary btn-amber inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-xs font-extrabold shadow-md transition"
                   style="background: linear-gradient(135deg, var(--crm-accent, #10b981), var(--crm-accent-hover, #059669));">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Export Cost-Profit PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="pg-wrap">
        <div class="pg-inner space-y-6">

            {{-- Navigation Sub-Tabs --}}
            <div class="flex items-center gap-2 border-b border-slate-200 dark:border-white/5 pb-3 overflow-x-auto">
                <a href="{{ route('analytics.index', ['range' => $selectedRange]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request()->routeIs('analytics.index') ? 'crm-tab-active text-white font-black shadow-md' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}"
                   @if(request()->routeIs('analytics.index')) style="background: linear-gradient(135deg, var(--crm-accent, #f59e0b), var(--crm-accent-hover, #d97706)); color: #fff;" @endif>
                    📊 Executive Overview
                </a>
                <a href="{{ route('analytics.cost-profit', ['range' => $selectedRange]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request()->routeIs('analytics.cost-profit') ? 'crm-tab-active text-white font-black shadow-md' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}"
                   @if(request()->routeIs('analytics.cost-profit')) style="background: linear-gradient(135deg, var(--crm-accent, #10b981), var(--crm-accent-hover, #059669)); color: #fff;" @endif>
                    💰 Cost &amp; Profit (Without Tax &amp; Minus Salary)
                </a>
                <a href="{{ route('analytics.technicians', ['range' => $selectedRange]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request()->routeIs('analytics.technicians') ? 'crm-tab-active text-white font-black shadow-md' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}"
                   @if(request()->routeIs('analytics.technicians')) style="background: linear-gradient(135deg, var(--crm-accent, #f59e0b), var(--crm-accent-hover, #d97706)); color: #fff;" @endif>
                    🛠️ Technician Performance (FTFR &amp; MTTR)
                </a>
                <a href="{{ route('analytics.mrr-retention', ['range' => $selectedRange]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request()->routeIs('analytics.mrr-retention') ? 'crm-tab-active text-white font-black shadow-md' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}"
                   @if(request()->routeIs('analytics.mrr-retention')) style="background: linear-gradient(135deg, var(--crm-accent, #f59e0b), var(--crm-accent-hover, #d97706)); color: #fff;" @endif>
                    📈 Monthly Revenue &amp; AMC Retention
                </a>
            </div>

            {{-- Date Range Filter Bar --}}
            <div class="pg-card p-4 flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <span class="text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px] mr-2">Analysis Period:</span>
                    <a href="{{ route('analytics.cost-profit', ['range' => 'today']) }}"
                       class="px-3 py-1.5 rounded-xl font-bold transition {{ $selectedRange === 'today' ? 'crm-pill-active bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                        Today
                    </a>
                    <a href="{{ route('analytics.cost-profit', ['range' => 'this_month']) }}"
                       class="px-3 py-1.5 rounded-xl font-bold transition {{ $selectedRange === 'this_month' ? 'crm-pill-active bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                        This Month
                    </a>
                    <a href="{{ route('analytics.cost-profit', ['range' => 'last_month']) }}"
                       class="px-3 py-1.5 rounded-xl font-bold transition {{ $selectedRange === 'last_month' ? 'crm-pill-active bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                        Last Month
                    </a>
                    <a href="{{ route('analytics.cost-profit', ['range' => 'this_quarter']) }}"
                       class="px-3 py-1.5 rounded-xl font-bold transition {{ $selectedRange === 'this_quarter' ? 'crm-pill-active bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                        This Quarter
                    </a>
                    <a href="{{ route('analytics.cost-profit', ['range' => 'this_year']) }}"
                       class="px-3 py-1.5 rounded-xl font-bold transition {{ $selectedRange === 'this_year' ? 'crm-pill-active bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                        This Year (FY)
                    </a>
                    <a href="{{ route('analytics.cost-profit', ['range' => 'all_time']) }}"
                       class="px-3 py-1.5 rounded-xl font-bold transition {{ $selectedRange === 'all_time' ? 'crm-pill-active bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                        All Time
                    </a>
                </div>

                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-900 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/5">
                    Date Window: <strong class="text-slate-900 dark:text-white">{{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</strong>
                </div>
            </div>

            {{-- 5 High-Impact KPI Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

                {{-- Revenue Without Tax --}}
                <div class="bg-white dark:bg-[#0f172a] p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Revenue (Without Tax)</span>
                            <span class="text-[11px] text-blue-600 dark:text-sky-400 font-semibold">Subtotal Billings</span>
                        </div>
                        <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm font-bold shrink-0 border border-blue-200 dark:border-blue-800">₹</div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-extrabold text-slate-900 dark:text-white leading-tight font-['Outfit']">₹{{ number_format($data['financials']['revenue_without_tax'], 2) }}</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1">
                            <span>+ ₹{{ number_format($data['financials']['tax_collected_cost'], 2) }} GST</span>
                        </div>
                    </div>
                </div>

                {{-- Product Buy Cost (COGS) --}}
                <div class="bg-white dark:bg-[#0f172a] p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Product Buy Cost</span>
                            <span class="text-[11px] text-rose-500 font-semibold">Hardware COGS</span>
                        </div>
                        <div class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm font-bold shrink-0 border border-rose-200 dark:border-rose-800">📦</div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-extrabold text-slate-900 dark:text-slate-200 leading-tight font-['Outfit']">₹{{ number_format($data['financials']['product_buy_cost'], 2) }}</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                            Equipment purchase cost
                        </div>
                    </div>
                </div>

                {{-- Gross Profit (Without Tax) --}}
                <div class="bg-white dark:bg-[#0f172a] p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Gross Profit</span>
                            <span class="text-[11px] text-amber-500 font-semibold">Pre-Tax Margin</span>
                        </div>
                        <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-bold shrink-0 border border-amber-200 dark:border-amber-800">⚡</div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 leading-tight font-['Outfit']">₹{{ number_format($data['financials']['gross_profit_without_tax'], 2) }}</div>
                        <div class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 mt-1">
                            {{ $data['financials']['gross_margin_without_tax_percent'] }}% Gross Margin
                        </div>
                    </div>
                </div>

                {{-- Employee Salary Credited Cost --}}
                <div class="bg-white dark:bg-[#0f172a] p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Salary Credited Cost</span>
                            <span class="text-[11px] text-purple-600 dark:text-purple-400 font-semibold">Auto-Deducted</span>
                        </div>
                        <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-bold shrink-0 border border-purple-200 dark:border-purple-800">👥</div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-extrabold text-purple-600 dark:text-purple-400 leading-tight font-['Outfit']">₹{{ number_format($data['financials']['employee_salary_cost'], 2) }}</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                            ₹{{ number_format($data['financials']['salary_credited_cost'], 2) }} paid • {{ $data['salary']['employee_count'] }} staff
                        </div>
                    </div>
                </div>

                {{-- NET OPERATING PROFIT (Without Tax) --}}
                <div class="bg-white dark:bg-[#0f172a] p-5 rounded-2xl border-2 {{ $data['financials']['net_profit_without_tax'] >= 0 ? 'border-emerald-500/50 bg-gradient-to-br from-emerald-50/20 dark:from-emerald-950/20 to-transparent' : 'border-rose-500/50 bg-gradient-to-br from-rose-50/20 dark:from-rose-950/20 to-transparent' }} shadow-lg flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider {{ $data['financials']['net_profit_without_tax'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} block">NET PROFIT (Without Tax)</span>
                            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">After Salary &amp; COGS</span>
                        </div>
                        <div class="w-8 h-8 rounded-xl {{ $data['financials']['net_profit_without_tax'] >= 0 ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/30' : 'bg-rose-500/10 text-rose-500 border border-rose-500/30' }} flex items-center justify-center text-sm font-bold shrink-0">💎</div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black leading-tight font-['Outfit'] {{ $data['financials']['net_profit_without_tax'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            ₹{{ number_format($data['financials']['net_profit_without_tax'], 2) }}
                        </div>
                        <div class="text-[11px] font-extrabold {{ $data['financials']['net_profit_without_tax'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} mt-1">
                            {{ $data['financials']['net_margin_percent'] }}% Net Operating Margin
                        </div>
                    </div>
                </div>

            </div>

            {{-- Master P&L Statement Waterfall Table --}}
            <div class="pg-card overflow-hidden">
                <div class="p-4 bg-slate-50 dark:bg-[#0b1120] border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <span>📋</span> Full Cost &amp; Profit Statement (Income Ledger)
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Line-by-line operating statement separating sales turnover, material buy cost, and auto-computed employee payroll</p>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-bold border border-blue-200 dark:border-blue-800">
                            Pre-Tax Reconciliation Mode
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                            
                            {{-- Line 1: Invoiced Revenue (Without Tax) --}}
                            <tr class="bg-blue-50/20 dark:bg-blue-950/10">
                                <td class="px-6 py-3.5 text-slate-900 dark:text-white font-bold flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-md bg-blue-500 text-white flex items-center justify-center font-bold text-xs">+</span>
                                    <span>Total Invoiced Revenue (Without Tax / Subtotal)</span>
                                </td>
                                <td class="px-6 py-3.5 text-slate-500 dark:text-slate-400 text-right">Gross operating sales turnover before taxes</td>
                                <td class="px-6 py-3.5 text-right font-bold text-base text-slate-900 dark:text-white font-mono">
                                    ₹{{ number_format($data['financials']['revenue_without_tax'], 2) }}
                                </td>
                            </tr>

                            {{-- Line 2: Product Buy Cost (COGS) --}}
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                <td class="px-6 py-3.5 text-slate-700 dark:text-slate-300 font-semibold flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-md bg-rose-500 text-white flex items-center justify-center font-bold text-xs">−</span>
                                    <span>Product Buy Cost (Hardware Equipment &amp; Cables)</span>
                                </td>
                                <td class="px-6 py-3.5 text-slate-500 dark:text-slate-400 text-right">Direct cost of cameras, NVRs, HDDs, cables &amp; accessories</td>
                                <td class="px-6 py-3.5 text-right font-bold text-base text-rose-600 dark:text-rose-400 font-mono">
                                    − ₹{{ number_format($data['financials']['product_buy_cost'], 2) }}
                                </td>
                            </tr>

                            {{-- Line 3: Gross Profit Without Tax --}}
                            <tr class="bg-slate-50/60 dark:bg-slate-800/60 border-y border-slate-200 dark:border-slate-700">
                                <td class="px-6 py-3.5 text-slate-900 dark:text-white font-bold flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-md bg-amber-500 text-slate-950 flex items-center justify-center font-bold text-xs">=</span>
                                    <span>Gross Operating Profit (Without Tax)</span>
                                </td>
                                <td class="px-6 py-3.5 text-amber-600 dark:text-amber-400 font-semibold text-right">
                                    Gross Margin: {{ $data['financials']['gross_margin_without_tax_percent'] }}%
                                </td>
                                <td class="px-6 py-3.5 text-right font-black text-base text-amber-600 dark:text-amber-400 font-mono">
                                    ₹{{ number_format($data['financials']['gross_profit_without_tax'], 2) }}
                                </td>
                            </tr>

                            {{-- Line 4: Employee Salary Expense --}}
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                <td class="px-6 py-3.5 text-slate-700 dark:text-slate-300 font-semibold flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-md bg-purple-500 text-white flex items-center justify-center font-bold text-xs">−</span>
                                    <span>Employee Salaries &amp; Staff Compensation (Auto-Deducted)</span>
                                </td>
                                <td class="px-6 py-3.5 text-slate-500 dark:text-slate-400 text-right">
                                    Admin (₹{{ number_format($data['salary']['role_breakdown']['admin']['salary'], 2) }}) + Staff (₹{{ number_format($data['salary']['role_breakdown']['staff']['salary'], 2) }}) + Technicians (₹{{ number_format($data['salary']['role_breakdown']['technician']['salary'], 2) }})
                                </td>
                                <td class="px-6 py-3.5 text-right font-bold text-base text-purple-600 dark:text-purple-400 font-mono">
                                    − ₹{{ number_format($data['financials']['employee_salary_cost'], 2) }}
                                </td>
                            </tr>

                            {{-- Line 5: NET OPERATING PROFIT WITHOUT TAX --}}
                            <tr class="bg-emerald-500/10 border-t-2 border-emerald-500">
                                <td class="px-6 py-4 text-slate-900 dark:text-white font-extrabold text-sm flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-md bg-emerald-600 text-white flex items-center justify-center font-bold text-xs">★</span>
                                    <span>NET OPERATING PROFIT (WITHOUT TAX)</span>
                                </td>
                                <td class="px-6 py-4 text-emerald-600 dark:text-emerald-400 font-extrabold text-right text-xs uppercase tracking-wider">
                                    Bottom-Line Pre-Tax Net Margin: {{ $data['financials']['net_margin_percent'] }}%
                                </td>
                                <td class="px-6 py-4 text-right font-black text-xl font-mono {{ $data['financials']['net_profit_without_tax'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    ₹{{ number_format($data['financials']['net_profit_without_tax'], 2) }}
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                {{-- Non-Operating Tax Reference Box --}}
                <div class="p-4 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Tax &amp; Compliance Memo (Excluded from Pre-Tax Profit):</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-[11px]">
                        <div>Sales GST Billed to Clients: <strong class="text-blue-600 dark:text-sky-400">₹{{ number_format($data['financials']['tax_collected_cost'], 2) }}</strong></div>
                        <div>Procurement Tax Paid on POs: <strong class="text-slate-700 dark:text-slate-300">₹{{ number_format($data['financials']['tax_paid_cost'], 2) }}</strong></div>
                        <div>Net GST Liability to Remit: <strong class="text-amber-600 dark:text-amber-400">₹{{ number_format($data['procurement']['net_tax_liability'], 2) }}</strong></div>
                    </div>
                </div>
            </div>

            {{-- 2 Sub-Sections: Employee Salary Deduction Roster & Invoiced Jobs Profitability --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Employee Salary Breakdown --}}
                <div class="pg-card overflow-hidden">
                    <div class="p-4 bg-slate-50 dark:bg-[#0b1120] border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span>👥</span> Employee Salary Deduction Ledger ({{ $data['salary']['employee_count'] }} Staff)
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Automatically computed wages deducted from the operating profit</p>
                        </div>
                        <a href="{{ route('finance.salaries.index') }}" class="text-[11px] text-blue-600 font-bold hover:underline">Manage Salaries →</a>
                    </div>
                    
                    <div class="overflow-x-auto max-h-80 overflow-y-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase text-[10px] sticky top-0">
                                <tr>
                                    <th class="px-4 py-2.5 text-left">Staff Name</th>
                                    <th class="px-4 py-2.5 text-left">Role</th>
                                    <th class="px-4 py-2.5 text-right">Monthly Base</th>
                                    <th class="px-4 py-2.5 text-right">Period Salary</th>
                                    <th class="px-4 py-2.5 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($data['salary']['details'] as $emp)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                        <td class="px-4 py-2.5 font-bold text-slate-800 dark:text-slate-200">
                                            {{ $emp['name'] }}
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $emp['role'] === 'technician' ? 'bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800' : ($emp['role'] === 'admin' ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800' : 'bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800') }}">
                                                {{ $emp['role'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5 text-right text-slate-500 font-mono">
                                            ₹{{ number_format($emp['monthly_base'], 2) }}
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-bold text-purple-600 dark:text-purple-400 font-mono">
                                            ₹{{ number_format($emp['computed_salary'], 2) }}
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            @if($emp['is_credited'])
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                                    ✓ Credited
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                                    Accrued
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-slate-400">No active employees found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Invoiced Client Projects (Without-Tax Profitability) --}}
                <div class="pg-card overflow-hidden">
                    <div class="p-4 bg-slate-50 dark:bg-[#0b1120] border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span>📹</span> Client Invoiced Jobs (Pre-Tax Profit Breakdown)
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Shows subtotal vs hardware buy cost per job</p>
                        </div>
                        <a href="{{ route('invoices.index') }}" class="text-[11px] text-blue-600 font-bold hover:underline">All Invoices →</a>
                    </div>
                    
                    <div class="overflow-x-auto max-h-80 overflow-y-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase text-[10px] sticky top-0">
                                <tr>
                                    <th class="px-4 py-2.5 text-left">Invoice #</th>
                                    <th class="px-4 py-2.5 text-left">Customer</th>
                                    <th class="px-4 py-2.5 text-right">Subtotal (Excl Tax)</th>
                                    <th class="px-4 py-2.5 text-right">Buy Cost</th>
                                    <th class="px-4 py-2.5 text-right">Gross Profit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($data['recent_invoices'] as $inv)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                        <td class="px-4 py-2.5 font-bold font-mono text-blue-600 dark:text-sky-400">
                                            {{ $inv['invoice_no'] }}
                                        </td>
                                        <td class="px-4 py-2.5 font-medium text-slate-800 dark:text-slate-200 truncate max-w-[140px]">
                                            {{ $inv['client_name'] }}
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-bold text-slate-900 dark:text-white font-mono">
                                            ₹{{ number_format($inv['subtotal'], 2) }}
                                        </td>
                                        <td class="px-4 py-2.5 text-right text-rose-600 dark:text-rose-400 font-mono">
                                            ₹{{ number_format($inv['product_buy_cost'], 2) }}
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">
                                            ₹{{ number_format($inv['profit_without_tax'], 2) }}
                                            <span class="text-[9px] block text-slate-400">({{ $inv['margin_percent'] }}%)</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-slate-400">No invoices in this timeframe.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
