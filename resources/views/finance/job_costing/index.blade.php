<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                    Job Costing & Per-Project Profit & Loss
                </h2>
            </div>

            <!-- Export Portfolio CSV Button -->
            <div class="flex items-center gap-2">
                <a href="{{ route('finance.job-costing.export-csv', request()->query()) }}" class="btn-secondary">
                    Export Portfolio CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        {{-- Finance Category Sub-Navigation --}}
        <x-finance-subnav active="job_costing" />

        @if (session('status'))
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 rounded text-xs font-medium text-emerald-900 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Total Projects Revenue -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Taxable Revenue (Net)</div>
                <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">
                    &#8377;{{ number_format($summary['total_portfolio_revenue'], 2) }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ $summary['total_projects_count'] }} Projects Analyzed
                </div>
            </div>

            <!-- 2. Hardware Materials COGS -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Hardware Material Cost</div>
                <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">
                    &#8377;{{ number_format($summary['total_portfolio_cogs'], 2) }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ $summary['total_portfolio_revenue'] > 0 ? round(($summary['total_portfolio_cogs'] / $summary['total_portfolio_revenue']) * 100, 1) : 0 }}% of revenue
                </div>
            </div>

            <!-- 3. Labor & Travel Expenses -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Labor & Field Expenses</div>
                <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">
                    &#8377;{{ number_format($summary['total_portfolio_labor'] + $summary['total_portfolio_expenses'] + $summary['total_portfolio_other'], 2) }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    Labor: &#8377;{{ number_format($summary['total_portfolio_labor'], 2) }}
                </div>
            </div>

            <!-- 4. Total Project Profit & Margin -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Net Project Gross Profit</div>
                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                    &#8377;{{ number_format($summary['total_portfolio_profit'], 2) }}
                    <span class="text-xs font-normal text-slate-500 dark:text-slate-400">({{ $summary['avg_margin_percent'] }}% Margin)</span>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ $summary['high_profit_count'] }} High Margin &bull; {{ $summary['loss_making_count'] }} Loss-Making
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
            <form method="GET" action="{{ route('finance.job-costing.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search -->
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Search Job / Client</label>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Job #, Customer..."
                           class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                </div>

                <!-- Margin Status Filter -->
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Profitability Health</label>
                    <select name="margin_status" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        <option value="">All Profit Tiers</option>
                        <option value="high_profit" {{ $filters['margin_status'] === 'high_profit' ? 'selected' : '' }}>High Margin (&ge; 35%)</option>
                        <option value="healthy" {{ $filters['margin_status'] === 'healthy' ? 'selected' : '' }}>Healthy Margin (20% - 35%)</option>
                        <option value="slim_margin" {{ $filters['margin_status'] === 'slim_margin' ? 'selected' : '' }}>Slim Margin (5% - 20%)</option>
                        <option value="loss_making" {{ $filters['margin_status'] === 'loss_making' ? 'selected' : '' }}>Loss-Making (&lt; 5%)</option>
                    </select>
                </div>

                <!-- Technician Filter -->
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Technician</label>
                    <select name="technician_id" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
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
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Time Period</label>
                    <select name="range" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs px-3 py-2 text-slate-900 dark:text-white focus:outline-none" style="height: 38px;">
                        <option value="all" {{ $range === 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="this_month" {{ $range === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ $range === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    </select>
                </div>

                <!-- Submit / Reset Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-[#2563eb] hover:bg-blue-700 text-white text-xs font-medium rounded transition" style="height: 38px;">
                        Filter
                    </button>
                    <a href="{{ route('finance.job-costing.index') }}" class="px-4 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium rounded transition flex items-center justify-center" style="height: 38px;">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Projects Profitability Data Table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Project Financial Performance</h2>
                <div class="text-xs text-slate-500">
                    Showing {{ count($summary['jobs']) }} Installation Projects
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left text-slate-600 dark:text-slate-400">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 text-[11px] font-bold">
                        <tr>
                            <th class="py-3 px-3">Job / Project</th>
                            <th class="py-3 px-3">Customer & Site</th>
                            <th class="py-3 px-3">Technician</th>
                            <th class="py-3 px-3 text-right">Taxable Rev (&#8377;)</th>
                            <th class="py-3 px-3 text-right">Hardware COGS (&#8377;)</th>
                            <th class="py-3 px-3 text-right">Labor (&#8377;)</th>
                            <th class="py-3 px-3 text-right">Travel / Other (&#8377;)</th>
                            <th class="py-3 px-3 text-right">Net Profit (&#8377;)</th>
                            <th class="py-3 px-3 text-center">Margin %</th>
                            <th class="py-3 px-3 text-center">Health Status</th>
                            <th class="py-3 px-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse($summary['jobs'] as $costing)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <!-- Job No & Date -->
                                <td class="py-3 px-3">
                                    <a href="{{ route('finance.job-costing.show', $costing['job']->id) }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ $costing['job_no'] }}
                                    </a>
                                    <div class="text-[11px] text-slate-400">{{ $costing['scheduled_date'] }}</div>
                                </td>

                                <!-- Customer & Site -->
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $costing['customer_name'] }}</div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-xs">{{ $costing['site_address'] }}</div>
                                </td>

                                <!-- Technician -->
                                <td class="py-3 px-3">
                                    {{ $costing['technician_name'] }}
                                </td>

                                <!-- Revenue -->
                                <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                                    &#8377;{{ number_format($costing['revenue_taxable'], 2) }}
                                </td>

                                <!-- Hardware COGS -->
                                <td class="py-3 px-3 text-right text-slate-700 dark:text-slate-300">
                                    &#8377;{{ number_format($costing['hardware_cogs'], 2) }}
                                </td>

                                <!-- Labor Cost -->
                                <td class="py-3 px-3 text-right text-slate-700 dark:text-slate-300">
                                    <div>&#8377;{{ number_format($costing['labor_cost'], 2) }}</div>
                                    <div class="text-[10px] text-slate-400">({{ $costing['labor_hours'] }}h)</div>
                                </td>

                                <!-- Travel & Overheads -->
                                <td class="py-3 px-3 text-right text-slate-700 dark:text-slate-300">
                                    &#8377;{{ number_format($costing['field_expenses'] + $costing['other_direct_costs'], 2) }}
                                </td>

                                <!-- Net Profit -->
                                <td class="py-3 px-3 text-right font-bold {{ $costing['gross_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    &#8377;{{ number_format($costing['gross_profit'], 2) }}
                                </td>

                                <!-- Margin % -->
                                <td class="py-3 px-3 text-center font-bold {{ $costing['gross_margin_percent'] >= 20 ? 'text-emerald-600' : ($costing['gross_margin_percent'] >= 5 ? 'text-amber-600' : 'text-rose-600') }}">
                                    {{ $costing['gross_margin_percent'] }}%
                                </td>

                                <!-- Health Status -->
                                <td class="py-3 px-3 text-center">
                                    @if($costing['gross_margin_percent'] >= 35)
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">High Margin</span>
                                    @elseif($costing['gross_margin_percent'] >= 20)
                                        <span class="font-bold text-blue-600 dark:text-blue-400">Healthy</span>
                                    @elseif($costing['gross_margin_percent'] >= 5)
                                        <span class="font-bold text-amber-600 dark:text-amber-400">Slim Margin</span>
                                    @else
                                        <span class="font-bold text-rose-600 dark:text-rose-400">Loss-Making</span>
                                    @endif
                                </td>

                                <!-- Action Link -->
                                <td class="py-3 px-3 text-center">
                                    <a href="{{ route('finance.job-costing.show', $costing['job']->id) }}"
                                       class="text-blue-600 hover:underline">
                                        P&amp;L Sheet
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-8 text-center text-slate-400 text-xs">
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
