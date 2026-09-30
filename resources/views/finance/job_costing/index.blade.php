<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-lg shadow-emerald-500/20 text-white">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-black tracking-tight text-slate-900 dark:text-white sm:text-2xl">
                            Job Costing & Per-Project Profit & Loss
                        </h2>
                        <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 rounded-full">
                            Project P&L Engine
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Real-time gross margin, hardware buy costs, technician labor time, and on-site expenses per installation
                    </p>
                </div>
            </div>

            <!-- Export Portfolio CSV Button -->
            <div class="flex items-center gap-2.5">
                <a href="{{ route('finance.job-costing.export-csv', request()->query()) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export Portfolio CSV</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        {{-- Finance Category Sub-Navigation --}}
        <x-finance-subnav active="job_costing" />

        @if (session('status'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl flex items-center justify-between shadow-sm animate-in fade-in duration-200">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-emerald-100 dark:bg-emerald-900/80 text-emerald-700 dark:text-emerald-300 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span class="text-xs md:text-sm font-bold text-emerald-900 dark:text-emerald-200">{{ session('status') }}</span>
                </div>
            </div>
        @endif

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Total Projects Revenue -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:border-blue-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Taxable Revenue (Net)</span>
                    <span class="p-2 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 rounded-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                    ₹{{ number_format($summary['total_portfolio_revenue'], 2) }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center justify-between">
                    <span>{{ $summary['total_projects_count'] }} Projects Analyzed</span>
                    <span class="font-bold text-blue-600 dark:text-blue-400">Total Billed</span>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
            </div>

            <!-- 2. Hardware Materials COGS -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:border-purple-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Hardware Material Cost</span>
                    <span class="p-2 bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 rounded-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-2">
                    ₹{{ number_format($summary['total_portfolio_cogs'], 2) }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center justify-between">
                    <span>Cameras, DVRs, Cables</span>
                    <span class="font-semibold text-purple-700 dark:text-purple-300">
                        {{ $summary['total_portfolio_revenue'] > 0 ? round(($summary['total_portfolio_cogs'] / $summary['total_portfolio_revenue']) * 100, 1) : 0 }}% of Rev
                    </span>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-pink-600"></div>
            </div>

            <!-- 3. Labor & Travel Expenses -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:border-amber-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Labor & Field Expenses</span>
                    <span class="p-2 bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-2">
                    ₹{{ number_format($summary['total_portfolio_labor'] + $summary['total_portfolio_expenses'] + $summary['total_portfolio_other'], 2) }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center justify-between">
                    <span>Labor: ₹{{ number_format($summary['total_portfolio_labor'], 2) }}</span>
                    <span>Travel/Fuel: ₹{{ number_format($summary['total_portfolio_expenses'], 2) }}</span>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-600"></div>
            </div>

            <!-- 4. Total Project Profit & Margin -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:border-emerald-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Net Project Gross Profit</span>
                    <span class="p-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2 flex items-baseline gap-2">
                    <span>₹{{ number_format($summary['total_portfolio_profit'], 2) }}</span>
                    <span class="text-xs font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                        {{ $summary['avg_margin_percent'] }}% Margin
                    </span>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center justify-between">
                    <span class="text-emerald-600 font-bold">{{ $summary['high_profit_count'] }} High Margin</span>
                    <span class="text-rose-600 font-bold">{{ $summary['loss_making_count'] }} Loss-Making</span>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-600"></div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm">
            <form method="GET" action="{{ route('finance.job-costing.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Search Job / Client</label>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Job #, Customer, Phone..."
                           class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 text-xs font-semibold focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Margin Status Filter -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Profitability Health</label>
                    <select name="margin_status" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 text-xs font-semibold focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Profit Tiers</option>
                        <option value="high_profit" {{ $filters['margin_status'] === 'high_profit' ? 'selected' : '' }}>🟢 High Margin (≥ 35%)</option>
                        <option value="healthy" {{ $filters['margin_status'] === 'healthy' ? 'selected' : '' }}>🔵 Healthy Margin (20% - 35%)</option>
                        <option value="slim_margin" {{ $filters['margin_status'] === 'slim_margin' ? 'selected' : '' }}>🟡 Slim Margin (5% - 20%)</option>
                        <option value="loss_making" {{ $filters['margin_status'] === 'loss_making' ? 'selected' : '' }}>🔴 Loss-Making (&lt; 5%)</option>
                    </select>
                </div>

                <!-- Technician Filter -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Technician</label>
                    <select name="technician_id" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 text-xs font-semibold focus:ring-2 focus:ring-emerald-500">
                        <option value="">All Technicians</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ (string)$filters['technician_id'] === (string)$tech->id ? 'selected' : '' }}>
                                {{ $tech->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Time Period</label>
                    <select name="range" class="w-full rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 text-xs font-semibold focus:ring-2 focus:ring-emerald-500">
                        <option value="all" {{ $range === 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="this_month" {{ $range === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ $range === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    </select>
                </div>

                <!-- Submit / Reset Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                        Filter Jobs
                    </button>
                    <a href="{{ route('finance.job-costing.index') }}" class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Projects Profitability Data Table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Project Financial Performance & Margins</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Click on any project to inspect the itemized Bill of Materials and labor breakdown</p>
                </div>
                <div class="text-xs font-bold text-slate-500">
                    Showing {{ count($summary['jobs']) }} Installation Projects
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Job / Project</th>
                            <th class="py-3 px-4">Customer & Site</th>
                            <th class="py-3 px-4">Technician</th>
                            <th class="py-3 px-4 text-right">Taxable Rev (₹)</th>
                            <th class="py-3 px-4 text-right">Hardware COGS (₹)</th>
                            <th class="py-3 px-4 text-right">Labor (₹)</th>
                            <th class="py-3 px-4 text-right">Travel / Other (₹)</th>
                            <th class="py-3 px-4 text-right font-bold text-emerald-600 dark:text-emerald-400">Net Profit (₹)</th>
                            <th class="py-3 px-4 text-center">Margin %</th>
                            <th class="py-3 px-4 text-center">Health Status</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse($summary['jobs'] as $costing)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <!-- Job No & Date -->
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('finance.job-costing.show', $costing['job']->id) }}" class="font-mono font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ $costing['job_no'] }}
                                    </a>
                                    <div class="text-[10px] text-slate-400">{{ $costing['scheduled_date'] }}</div>
                                </td>

                                <!-- Customer & Site -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $costing['customer_name'] }}</div>
                                    <div class="text-[10px] text-slate-400 truncate max-w-xs">{{ $costing['site_address'] }}</div>
                                </td>

                                <!-- Technician -->
                                <td class="py-3.5 px-4 font-semibold text-slate-600 dark:text-slate-400">
                                    {{ $costing['technician_name'] }}
                                </td>

                                <!-- Revenue -->
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                    ₹{{ number_format($costing['revenue_taxable'], 2) }}
                                </td>

                                <!-- Hardware COGS -->
                                <td class="py-3.5 px-4 text-right font-mono text-purple-700 dark:text-purple-400">
                                    ₹{{ number_format($costing['hardware_cogs'], 2) }}
                                </td>

                                <!-- Labor Cost -->
                                <td class="py-3.5 px-4 text-right font-mono text-amber-700 dark:text-amber-400">
                                    <div>₹{{ number_format($costing['labor_cost'], 2) }}</div>
                                    <div class="text-[9px] text-slate-400">({{ $costing['labor_hours'] }}h)</div>
                                </td>

                                <!-- Travel & Overheads -->
                                <td class="py-3.5 px-4 text-right font-mono text-slate-600 dark:text-slate-400">
                                    ₹{{ number_format($costing['field_expenses'] + $costing['other_direct_costs'], 2) }}
                                </td>

                                <!-- Net Profit -->
                                <td class="py-3.5 px-4 text-right font-mono font-black {{ $costing['gross_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    ₹{{ number_format($costing['gross_profit'], 2) }}
                                </td>

                                <!-- Margin % -->
                                <td class="py-3.5 px-4 text-center font-mono font-bold {{ $costing['gross_margin_percent'] >= 20 ? 'text-emerald-600' : ($costing['gross_margin_percent'] >= 5 ? 'text-amber-600' : 'text-rose-600') }}">
                                    {{ $costing['gross_margin_percent'] }}%
                                </td>

                                <!-- Health Status Badge -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase border {{ $costing['margin_badge_class'] }}">
                                        {{ $costing['margin_label'] }}
                                    </span>
                                </td>

                                <!-- Action Link -->
                                <td class="py-3.5 px-4 text-center">
                                    <a href="{{ route('finance.job-costing.show', $costing['job']->id) }}"
                                       class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 hover:underline">
                                        <span>P&L Sheet</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-10 text-center text-slate-400 text-xs">
                                    No installation projects matched your search filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
