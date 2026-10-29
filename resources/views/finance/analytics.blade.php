<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                    Salary Analytics
                </h1>
            </div>
            <div class="flex items-center gap-2">
                @if(auth()->user()->isAdmin())
                <a href="{{ route('finance.salaries.index') }}"
                   class="inline-flex items-center px-3 py-1.5 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                    Salary Master
                </a>
                @endif
                @if(auth()->user()->isAdmin())
                <a href="{{ route('finance.payroll.index') }}"
                   class="inline-flex items-center px-3 py-1.5 rounded bg-[#2563eb] hover:bg-blue-700 text-white text-xs font-medium transition">
                    Payrolls
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <div class="py-6 space-y-6">

        {{-- Employee Category Sub-Navigation --}}
        <x-employee-subnav active="burn" />

        {{-- Granularity Filter Selector (Monthly / Weekly / Daily) --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Analytical View:</span>
                <div class="inline-flex border border-slate-300 dark:border-slate-700 rounded overflow-hidden">
                    <a href="{{ route('finance.analytics', ['mode' => 'monthly']) }}"
                       class="px-3 py-1 text-xs font-medium transition {{ $viewMode === 'monthly' ? 'bg-[#2563eb] text-white' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                        Monthly
                    </a>
                    <a href="{{ route('finance.analytics', ['mode' => 'weekly']) }}"
                       class="px-3 py-1 text-xs font-medium transition border-l border-slate-300 dark:border-slate-700 {{ $viewMode === 'weekly' ? 'bg-[#2563eb] text-white' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                        Weekly
                    </a>
                    <a href="{{ route('finance.analytics', ['mode' => 'daily']) }}"
                       class="px-3 py-1 text-xs font-medium transition border-l border-slate-300 dark:border-slate-700 {{ $viewMode === 'daily' ? 'bg-[#2563eb] text-white' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                        Daily
                    </a>
                </div>
            </div>

            <div class="text-xs text-slate-500 dark:text-slate-400">
                Normalized across {{ $internalStaff->count() }} internal staff members
            </div>
        </div>

        {{-- Dynamic Financial KPI Cards based on View Mode --}}
        @php
            $activeTotal = match($viewMode) {
                'daily' => $totalDailyCost,
                'weekly' => $totalWeeklyCost,
                default => $totalMonthlyCost
            };
            $activeUnitLabel = match($viewMode) {
                'daily' => 'Daily Operational Burn',
                'weekly' => 'Weekly Payroll Commitment',
                default => 'Monthly Salary Commitment'
            };
            $headcount = max(1, $internalStaff->count());
            $avgActiveCost = $activeTotal / $headcount;
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Total {{ $activeUnitLabel }}</span>
                <span class="text-xl font-bold text-slate-900 dark:text-white mt-1 block">
                    &#8377;{{ number_format($activeTotal, 2) }}
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">
                    {{ ucfirst($viewMode) }} cycle
                </span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Average {{ ucfirst($viewMode) }} Wage</span>
                <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 block">
                    &#8377;{{ number_format($avgActiveCost, 2) }}
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">
                    Per employee average
                </span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Technician Daily Labor Cost</span>
                <span class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1 block">
                    &#8377;{{ number_format($roleBreakdown['technician']['daily_cost'], 2) }}
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">
                    Field service daily capacity
                </span>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Month-to-Date Attendance</span>
                <span class="text-xl font-bold text-slate-900 dark:text-white mt-1 block">
                    {{ $attendanceDaysThisMonth }} <span class="text-xs font-normal text-slate-500">days</span>
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">
                    Logged this month
                </span>
            </div>
        </div>

        {{-- Graphical Visualizations (Trend + Distribution) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- 6-Month Expenditure Trend --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 lg:col-span-2 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h2 class="font-bold text-sm text-slate-900 dark:text-white">6-Month Payroll Trend</h2>
                    <span class="text-xs text-slate-500">Monthly Cycle</span>
                </div>
                <div class="h-64 w-full">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            {{-- Role Distribution Donut --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h2 class="font-bold text-sm text-slate-900 dark:text-white">Salary Share by Role</h2>
                    <span class="text-xs text-slate-500">{{ ucfirst($viewMode) }}</span>
                </div>
                <div class="h-64 w-full flex items-center justify-center">
                    <canvas id="roleDonutChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Role-Wise Comparison Matrix Table --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 space-y-3">
            <h2 class="font-bold text-slate-900 dark:text-white text-sm">Role &amp; Department Compensation Breakdown</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 text-[11px] font-bold">
                        <tr>
                            <th class="py-3 px-3">Role / Department</th>
                            <th class="py-3 px-3 text-center">Headcount</th>
                            <th class="py-3 px-3 text-right">Per-Day Cost</th>
                            <th class="py-3 px-3 text-right">Weekly Cost</th>
                            <th class="py-3 px-3 text-right">Monthly Cost</th>
                            <th class="py-3 px-3 text-center">% Share of Payroll</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($roleBreakdown as $roleKey => $roleData)
                            @php
                                $sharePct = $totalMonthlyCost > 0 ? round(($roleData['monthly_cost'] / $totalMonthlyCost) * 100, 1) : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-3 font-bold capitalize text-slate-900 dark:text-white">
                                    {{ $roleKey }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    {{ $roleData['count'] }} member(s)
                                </td>
                                <td class="py-3 px-3 text-right font-medium text-slate-700 dark:text-slate-300">
                                    &#8377;{{ number_format($roleData['daily_cost'], 2) }}
                                </td>
                                <td class="py-3 px-3 text-right font-medium text-slate-700 dark:text-slate-300">
                                    &#8377;{{ number_format($roleData['weekly_cost'], 2) }}
                                </td>
                                <td class="py-3 px-3 text-right font-medium text-slate-900 dark:text-white">
                                    &#8377;{{ number_format($roleData['monthly_cost'], 2) }}
                                </td>
                                <td class="py-3 px-3 text-center font-bold">
                                    {{ $sharePct }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50/80 dark:bg-slate-800/80 font-bold border-t border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                        <tr>
                            <td class="py-3 px-3">Total Organization</td>
                            <td class="py-3 px-3 text-center">{{ $internalStaff->count() }} members</td>
                            <td class="py-3 px-3 text-right">&#8377;{{ number_format($totalDailyCost, 2) }}</td>
                            <td class="py-3 px-3 text-right">&#8377;{{ number_format($totalWeeklyCost, 2) }}</td>
                            <td class="py-3 px-3 text-right">&#8377;{{ number_format($totalMonthlyCost, 2) }}</td>
                            <td class="py-3 px-3 text-center">100.0%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Comprehensive Individual Employee Rate Comparison Table --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-slate-900 dark:text-white text-sm">Employee Wage Rates Comparison</h2>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('finance.salaries.index') }}" class="text-xs text-blue-600 hover:underline">
                    Configure Rates &rarr;
                </a>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 text-[11px] font-bold">
                        <tr>
                            <th class="py-3 px-3">Employee</th>
                            <th class="py-3 px-3 text-right">Monthly Base</th>
                            <th class="py-3 px-3 text-right">Weekly Rate</th>
                            <th class="py-3 px-3 text-right">Per-Day Rate</th>
                            <th class="py-3 px-3 text-right">Hourly Rate</th>
                            <th class="py-3 px-3 text-right">OT / Hour</th>
                            <th class="py-3 px-3 text-right">Allowances</th>
                            <th class="py-3 px-3 text-right font-bold">Net Monthly</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($internalStaff as $emp)
                            @php $s = $emp->salaryStructure; @endphp
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $emp->name }}</div>
                                    <div class="text-[11px] text-slate-400 capitalize">{{ $emp->role }}</div>
                                </td>
                                <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                                    &#8377;{{ number_format($s?->base_salary_monthly ?? 0, 2) }}
                                </td>
                                <td class="py-3 px-3 text-right text-emerald-600 dark:text-emerald-400">
                                    &#8377;{{ number_format($s?->weekly_rate ?? ($s ? $s->base_salary_monthly / 4.33 : 0), 2) }}
                                </td>
                                <td class="py-3 px-3 text-right text-slate-700 dark:text-slate-300">
                                    &#8377;{{ number_format($s?->daily_rate ?? ($s ? $s->base_salary_monthly / 26 : 0), 2) }}
                                </td>
                                <td class="py-3 px-3 text-right text-slate-600 dark:text-slate-400">
                                    &#8377;{{ number_format($s?->hourly_rate ?? ($s ? $s->daily_rate / 8 : 0), 2) }}
                                </td>
                                <td class="py-3 px-3 text-right text-slate-600 dark:text-slate-400">
                                    &#8377;{{ number_format($s?->overtime_hourly_rate ?? 0, 2) }}
                                </td>
                                <td class="py-3 px-3 text-right text-emerald-600 dark:text-emerald-400">
                                    +&#8377;{{ number_format($s?->total_allowances ?? 0, 2) }}
                                </td>
                                <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                                    &#8377;{{ number_format($s?->net_monthly ?? 0, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">No internal staff found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Chart.js Initialization --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? '#1e293b' : '#f1f5f9';
            const textColor = isDark ? '#94a3b8' : '#64748b';

            // 1. Trend Line Chart
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: @json($monthlyTrendLabels),
                    datasets: [{
                        label: 'Payroll Disbursement (₹)',
                        data: @json($monthlyTrendValues),
                        borderColor: '#2563eb',
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0,
                        pointRadius: 3,
                        pointBackgroundColor: '#2563eb',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ' ₹' + Number(ctx.parsed.y).toLocaleString()
                            }
                        }
                    },
                    scales: {
                        y: {
                            grid: { color: gridColor },
                            ticks: {
                                color: textColor,
                                callback: val => '₹' + Number(val).toLocaleString()
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: textColor }
                        }
                    }
                }
            });

            // 2. Role Cost Distribution Donut
            @php
                $roleMetricKey = match($viewMode) {
                    'daily' => 'daily_cost',
                    'weekly' => 'weekly_cost',
                    default => 'monthly_cost'
                };
                $roleLabels = ['Admin', 'Staff', 'Technicians'];
                $roleValues = [
                    round($roleBreakdown['admin'][$roleMetricKey], 2),
                    round($roleBreakdown['staff'][$roleMetricKey], 2),
                    round($roleBreakdown['technician'][$roleMetricKey], 2)
                ];
            @endphp

            const donutCtx = document.getElementById('roleDonutChart').getContext('2d');
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($roleLabels),
                    datasets: [{
                        data: @json($roleValues),
                        backgroundColor: ['#2563eb', '#64748b', '#059669'],
                        borderWidth: 1,
                        borderColor: isDark ? '#0f172a' : '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                boxWidth: 12,
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ' ' + ctx.label + ': ₹' + Number(ctx.parsed).toLocaleString()
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        });
    </script>
</x-app-layout>
