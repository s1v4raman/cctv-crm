<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold leading-tight text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-[0_0_10px_#f59e0b]"></span>
                    Executive Salary &amp; Compensation Analytics
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 font-medium">Deep-dive financial analysis across Monthly, Weekly, and Per-Day wage commitments</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('finance.salaries.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                    👥 Salary Master
                </a>
                <a href="{{ route('finance.payroll.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition">
                    💳 Payrolls
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .pg-wrap { background:#f8fafc; min-height:100vh; padding:1.5rem 0 3rem; }
        .dark .pg-wrap { background:#060913; }
        .pg-inner { max-width:1380px; margin:0 auto; padding:0 1.25rem; }
        .pg-card {
            background:#ffffff; border-radius:1rem; border:1px solid #e2e8f0;
            box-shadow:0 1px 3px rgba(0,0,0,.04),0 4px 12px rgba(0,0,0,.02);
        }
        .dark .pg-card { background:#0f172a; border-color:#1e293b; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <div class="pg-wrap">
        <div class="pg-inner space-y-6">

            {{-- Sub-Navigation Tabs --}}
            <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
                <a href="{{ route('finance.salaries.index') }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                    👥 Employee Salary Master
                </a>
                <a href="{{ route('finance.payroll.index') }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                    💳 Monthly/Weekly Payrolls
                </a>
                <a href="{{ route('finance.analytics') }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition bg-blue-600 text-white shadow-xs">
                    📊 Salary Analytics (Monthly / Weekly / Daily)
                </a>
            </div>

            {{-- Granularity Filter Selector (Monthly / Weekly / Daily) --}}
            <div class="pg-card p-4 flex flex-wrap items-center justify-between gap-4 bg-gradient-to-r from-blue-50/50 via-white to-amber-50/50 dark:from-slate-900 dark:via-slate-900 dark:to-slate-900">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-black uppercase text-slate-400 tracking-wider">Analytical Dimension:</span>
                    <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                        <a href="{{ route('finance.analytics', ['mode' => 'monthly']) }}"
                           class="px-4 py-1.5 rounded-lg text-xs font-extrabold transition {{ $viewMode === 'monthly' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                            📅 Monthly View
                        </a>
                        <a href="{{ route('finance.analytics', ['mode' => 'weekly']) }}"
                           class="px-4 py-1.5 rounded-lg text-xs font-extrabold transition {{ $viewMode === 'weekly' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                            📆 Weekly View
                        </a>
                        <a href="{{ route('finance.analytics', ['mode' => 'daily']) }}"
                           class="px-4 py-1.5 rounded-lg text-xs font-extrabold transition {{ $viewMode === 'daily' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                            ⚡ Per-Day View
                        </a>
                    </div>
                </div>

                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                    Showing cost metrics normalized across <span class="font-bold text-slate-900 dark:text-white">{{ $internalStaff->count() }} internal staff members</span>
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
                <div class="pg-card p-5 border-l-4 border-l-blue-600 relative overflow-hidden">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total {{ $activeUnitLabel }}</span>
                    <span class="text-3xl font-black text-slate-900 dark:text-white mt-1.5 block font-mono">
                        ₹{{ number_format($activeTotal, 2) }}
                    </span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">
                        Across {{ ucfirst($viewMode) }} payroll cycle
                    </span>
                </div>

                <div class="pg-card p-5 border-l-4 border-l-emerald-600 relative overflow-hidden">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Average {{ ucfirst($viewMode) }} Wage</span>
                    <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-1.5 block font-mono">
                        ₹{{ number_format($avgActiveCost, 2) }}
                    </span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">
                        Per employee compensation
                    </span>
                </div>

                <div class="pg-card p-5 border-l-4 border-l-amber-600 relative overflow-hidden">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Technician Daily Labor Cost</span>
                    <span class="text-3xl font-black text-amber-600 dark:text-amber-400 mt-1.5 block font-mono">
                        ₹{{ number_format($roleBreakdown['technician']['daily_cost'], 2) }}
                    </span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">
                        Field service daily capacity cost
                    </span>
                </div>

                <div class="pg-card p-5 border-l-4 border-l-purple-600 relative overflow-hidden">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Month-to-Date Attendance</span>
                    <span class="text-3xl font-black text-purple-600 dark:text-purple-400 mt-1.5 block">
                        {{ $attendanceDaysThisMonth }} <span class="text-sm font-semibold text-slate-400">days</span>
                    </span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">
                        Logged by internal employees
                    </span>
                </div>
            </div>

            {{-- Graphical Visualizations (Trend + Distribution) --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- 6-Month Expenditure Trend --}}
                <div class="pg-card p-5 lg:col-span-2 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">6-Month Payroll Trend</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Historical monthly salary disbursements vs baseline</p>
                        </div>
                        <span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-300">
                            Monthly Cycle
                        </span>
                    </div>
                    <div class="h-64 w-full">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                {{-- Role Distribution Donut --}}
                <div class="pg-card p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Salary Share by Role</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ ucfirst($viewMode) }} cost allocation</p>
                        </div>
                        <span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-300">
                            {{ ucfirst($viewMode) }}
                        </span>
                    </div>
                    <div class="h-64 w-full flex items-center justify-center">
                        <canvas id="roleDonutChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Role-Wise Comparison Matrix Table --}}
            <div class="pg-card overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm">Role &amp; Department Compensation Breakdown</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Aggregated wage allocations categorized across Admin, Staff, and Field Technicians</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-400 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="py-3 px-4">Role / Department</th>
                                <th class="py-3 px-4 text-center">Headcount</th>
                                <th class="py-3 px-4 text-right">Per-Day Cost</th>
                                <th class="py-3 px-4 text-right">Weekly Cost</th>
                                <th class="py-3 px-4 text-right">Monthly Cost</th>
                                <th class="py-3 px-4 text-center">% Share of Payroll</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @foreach($roleBreakdown as $roleKey => $roleData)
                                @php
                                    $sharePct = $totalMonthlyCost > 0 ? round(($roleData['monthly_cost'] / $totalMonthlyCost) * 100, 1) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                    <td class="py-3.5 px-4 font-bold capitalize text-slate-900 dark:text-white flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full 
                                            @if($roleKey === 'admin') bg-blue-500
                                            @elseif($roleKey === 'technician') bg-emerald-500
                                            @else bg-purple-500 @endif"></span>
                                        {{ $roleKey }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-semibold">
                                        {{ $roleData['count'] }} member(s)
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-amber-600 dark:text-amber-400">
                                        ₹{{ number_format($roleData['daily_cost'], 2) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                        ₹{{ number_format($roleData['weekly_cost'], 2) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-blue-600 dark:text-blue-400">
                                        ₹{{ number_format($roleData['monthly_cost'], 2) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-20 bg-slate-100 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                                                <div class="bg-blue-600 h-full rounded-full" style="width: {{ $sharePct }}%"></div>
                                            </div>
                                            <span class="font-bold text-[11px]">{{ $sharePct }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50/80 dark:bg-slate-800/80 font-bold border-t border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                            <tr>
                                <td class="py-3 px-4 uppercase text-[10px] tracking-wider">Total Organization</td>
                                <td class="py-3 px-4 text-center">{{ $internalStaff->count() }} members</td>
                                <td class="py-3 px-4 text-right font-mono text-amber-600 dark:text-amber-400">₹{{ number_format($totalDailyCost, 2) }}</td>
                                <td class="py-3 px-4 text-right font-mono text-emerald-600 dark:text-emerald-400">₹{{ number_format($totalWeeklyCost, 2) }}</td>
                                <td class="py-3 px-4 text-right font-mono text-blue-600 dark:text-blue-400">₹{{ number_format($totalMonthlyCost, 2) }}</td>
                                <td class="py-3 px-4 text-center">100.0%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Comprehensive Individual Employee Rate Comparison Table --}}
            <div class="pg-card overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm">Employee Wage Rates Comparison (Monthly vs. Weekly vs. Daily)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Live rates configured in employee compensation profiles</p>
                    </div>
                    <a href="{{ route('finance.salaries.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                        Configure Rates →
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-400 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="py-3 px-4">Employee</th>
                                <th class="py-3 px-4 text-right">Monthly Base</th>
                                <th class="py-3 px-4 text-right">Weekly Rate</th>
                                <th class="py-3 px-4 text-right">Per-Day Rate</th>
                                <th class="py-3 px-4 text-right">Hourly Rate</th>
                                <th class="py-3 px-4 text-right">OT / Hour</th>
                                <th class="py-3 px-4 text-right">Allowances</th>
                                <th class="py-3 px-4 text-right font-black">Net Monthly</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse($internalStaff as $emp)
                                @php $s = $emp->salaryStructure; @endphp
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $emp->name }}</div>
                                        <div class="text-[10px] text-slate-400 capitalize">{{ $emp->role }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-blue-600 dark:text-blue-400">
                                        ₹{{ number_format($s?->base_salary_monthly ?? 0, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-semibold text-emerald-600 dark:text-emerald-400">
                                        ₹{{ number_format($s?->weekly_rate ?? ($s ? $s->base_salary_monthly / 4.33 : 0), 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-semibold text-amber-600 dark:text-amber-400">
                                        ₹{{ number_format($s?->daily_rate ?? ($s ? $s->base_salary_monthly / 26 : 0), 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-400">
                                        ₹{{ number_format($s?->hourly_rate ?? ($s ? $s->daily_rate / 8 : 0), 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono text-purple-600 dark:text-purple-400">
                                        ₹{{ number_format($s?->overtime_hourly_rate ?? 0, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-400">
                                        +₹{{ number_format($s?->total_allowances ?? 0, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-black text-slate-900 dark:text-white">
                                        ₹{{ number_format($s?->net_monthly ?? 0, 2) }}
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
                        borderColor: '#3b82f6',
                        backgroundColor: isDark ? 'rgba(59, 130, 246, 0.15)' : 'rgba(59, 130, 246, 0.08)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointBackgroundColor: '#3b82f6',
                        borderWidth: 2.5
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
                        backgroundColor: ['#3b82f6', '#8b5cf6', '#10b981'],
                        borderWidth: 2,
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
                                font: { size: 11, weight: 'bold' }
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
