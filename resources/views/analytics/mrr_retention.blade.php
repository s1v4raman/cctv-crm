<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold leading-tight text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 shadow-[0_0_10px_#6366f1]"></span>
                    Monthly Revenue, AMC Retention &amp; Lead Conversion
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 font-medium">Monthly recurring revenue (MRR), annual recurring revenue (ARR), AMC contract renewal retention, and sales funnel conversion</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('analytics.mrr-retention.export-pdf', ['range' => $selectedRange, 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"
                   class="crm-btn-primary btn-amber inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-white text-xs font-bold shadow-md transition"
                   style="background-color: var(--crm-accent, #2563eb);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Export MRR &amp; Retention PDF
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Include Chart.js via CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <div class="pg-wrap">
        <div class="pg-inner space-y-6">

            {{-- Navigation Sub-Tabs Centered --}}
            <div class="flex items-center justify-center gap-2.5 py-1 overflow-x-auto">
                <a href="{{ route('analytics.index', ['range' => $selectedRange]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap {{ request()->routeIs('analytics.index') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60 shadow-xs' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('analytics.index') ? 'text-white' : 'text-slate-700 dark:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    <span>Executive Overview</span>
                </a>
                <a href="{{ route('analytics.technicians', ['range' => $selectedRange]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap {{ request()->routeIs('analytics.technicians') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60 shadow-xs' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('analytics.technicians') ? 'text-white' : 'text-slate-700 dark:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 004.486-6.32l-3.276 3.277a3.004 3.004 0 01-2.25 2.25l-3.276-3.276a4.5 4.5 0 00-6.32 4.486c.118.58.094 1.193-.07 1.743"/></svg>
                    <span>Technician Performance (FTFR &amp; MTTR)</span>
                </a>
                <a href="{{ route('analytics.cost-profit', ['range' => $selectedRange]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap {{ request()->routeIs('analytics.cost-profit') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60 shadow-xs' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('analytics.cost-profit') ? 'text-white' : 'text-slate-700 dark:text-slate-300' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Cost &amp; Profit Analysis</span>
                </a>
            </div>

            {{-- Date Range Filter Bar --}}
            <div class="pg-card p-4 flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <span class="text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px] mr-2">Timeframe:</span>
                    @foreach(['today' => 'Today', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_quarter' => 'This Quarter', 'this_year' => 'This Year (FY)', 'all_time' => 'All Time'] as $rKey => $rLabel)
                        <a href="{{ route('analytics.mrr-retention', ['range' => $rKey]) }}"
                           class="px-3 py-1.5 rounded-xl font-bold transition {{ $selectedRange === $rKey ? 'crm-pill-active text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300' }}"
                           @if($selectedRange === $rKey) style="background-color: var(--crm-accent, #2563eb);" @endif>
                            {{ $rLabel }}
                        </a>
                    @endforeach
                </div>

                <div class="text-xs font-medium text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                    Active Window: <strong class="text-amber-600 dark:text-amber-400 font-bold">{{ $data['date_range']['label'] }}</strong>
                </div>
            </div>

            {{-- 4 Master Financial & Funnel KPI Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                {{-- Monthly Recurring Revenue (MRR) --}}
                <div class="pg-card p-5 rounded-2xl flex flex-col justify-between" style="min-height:130px">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Monthly Recurring (MRR)</span>
                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 leading-tight">₹{{ number_format($data['snapshot']['active_mrr'], 2) }}</div>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-1">ARR: ₹{{ number_format($data['snapshot']['active_arr'], 2) }} / year</span>
                    </div>
                </div>

                {{-- AMC Retention Rate --}}
                <div class="pg-card p-5 rounded-2xl flex flex-col justify-between" style="min-height:130px">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">AMC Contract Retention</span>
                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 leading-tight">{{ $data['snapshot']['retention_rate'] }}%</div>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-1">{{ $data['snapshot']['active_contracts'] }} active (Churn: {{ $data['snapshot']['churn_rate'] }}%)</span>
                    </div>
                </div>

                {{-- Lead to Accepted Quote Conversion --}}
                <div class="pg-card p-5 rounded-2xl flex flex-col justify-between" style="min-height:130px">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Lead-to-Won Conversion</span>
                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.003 0H9.497m5.003 0a3.375 3.375 0 003.375-3.375V6.375A2.25 2.25 0 0015.625 4.125H8.375A2.25 2.25 0 006.125 6.375v4.5A3.375 3.375 0 009.5 14.25"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-sky-600 dark:text-sky-400 mt-1 leading-tight">{{ $data['conversion']['lead_to_won_rate'] }}%</div>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-1">Quote-to-Won: {{ $data['conversion']['quote_to_accepted_rate'] }}%</span>
                    </div>
                </div>

                {{-- Average Quote Turnaround Time --}}
                <div class="pg-card p-5 rounded-2xl flex flex-col justify-between" style="min-height:130px">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Avg Quote Close Time</span>
                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-slate-900 dark:text-white mt-1 leading-tight">{{ $data['conversion']['avg_turnaround_days'] }} Days</div>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-1">Avg turnaround to acceptance</span>
                    </div>
                </div>

            </div>

            {{-- 4 Chart.js Interactive Graphs --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Chart 1: 12-Month MRR Historical Growth Trend --}}
                <div class="pg-card p-6 rounded-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">12-Month Monthly Recurring Revenue (MRR) Trend</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Track active subscription growth and recurring cash flow predictability</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="mrrGrowthChart"></canvas>
                    </div>
                </div>

                {{-- Chart 2: AMC Billing Frequency Distribution --}}
                <div class="pg-card p-6 rounded-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">AMC Portfolio Frequency Split</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Contract composition across Annual, Semi-Annual, Quarterly &amp; Monthly plans</p>
                        </div>
                    </div>
                    <div class="h-64 flex items-center justify-center">
                        <canvas id="amcFrequencyChart"></canvas>
                    </div>
                </div>

                {{-- Chart 3: 12-Month Lead -> Quote -> Accepted Conversion Funnel Trend --}}
                <div class="pg-card p-6 rounded-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">12-Month Sales Funnel Volume</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Leads generated &rarr; Quotations issued &rarr; Won accepted projects</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="funnelVolumeChart"></canvas>
                    </div>
                </div>

                {{-- Chart 4: Quoted Value vs Accepted Won Value --}}
                <div class="pg-card p-6 rounded-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Monthly Quoted Pipeline vs Won Revenue (₹)</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Total proposal pipeline value vs successfully converted deal value</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="pipelineValueChart"></canvas>
                    </div>
                </div>

            </div>

            {{-- 12-Month Detailed Funnel & Conversion Historical Table --}}
            <div class="pg-card rounded-2xl overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">12-Month Sales Conversion &amp; MRR Ledger</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Month-by-month breakdown of lead volume, quotations, won conversions, and recurring revenue</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                                <th class="px-4 py-3 text-left">Month</th>
                                <th class="px-4 py-3 text-center text-amber-600 dark:text-amber-400">MRR (₹)</th>
                                <th class="px-4 py-3 text-center">Leads</th>
                                <th class="px-4 py-3 text-center">Quotes Issued</th>
                                <th class="px-4 py-3 text-center text-emerald-600 dark:text-emerald-400">Quotes Won</th>
                                <th class="px-4 py-3 text-center text-sky-600 dark:text-sky-400">Win Rate (%)</th>
                                <th class="px-4 py-3 text-right">Quoted Pipeline</th>
                                <th class="px-4 py-3 text-right text-amber-600 dark:text-amber-400">Won Value (₹)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($data['conversion']['monthly_trend']['labels'] as $idx => $mLabel)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                    <td class="px-4 py-3.5 font-bold text-slate-900 dark:text-white">{{ $mLabel }}</td>
                                    <td class="px-4 py-3.5 text-center font-bold text-amber-600 dark:text-amber-400">₹{{ number_format($data['timeline']['mrr'][$idx], 2) }}</td>
                                    <td class="px-4 py-3.5 text-center text-slate-600 dark:text-slate-300">{{ $data['conversion']['monthly_trend']['leads'][$idx] }}</td>
                                    <td class="px-4 py-3.5 text-center text-slate-600 dark:text-slate-300">{{ $data['conversion']['monthly_trend']['quotations'][$idx] }}</td>
                                    <td class="px-4 py-3.5 text-center font-bold text-emerald-600 dark:text-emerald-400">{{ $data['conversion']['monthly_trend']['accepted'][$idx] }}</td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full font-bold text-[11px] {{ $data['conversion']['monthly_trend']['conversion_rates'][$idx] >= 40 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                            {{ $data['conversion']['monthly_trend']['conversion_rates'][$idx] }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-right text-slate-500 dark:text-slate-400">₹{{ number_format($data['conversion']['monthly_trend']['quoted_amounts'][$idx], 2) }}</td>
                                    <td class="px-4 py-3.5 text-right font-bold text-amber-600 dark:text-amber-400">₹{{ number_format($data['conversion']['monthly_trend']['accepted_amounts'][$idx], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Initialize Chart.js Scripts with Dark Theme Palette --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            Chart.defaults.color = '#94a3b8';
            Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.06)';

            // 1. 12-Month MRR Historical Growth Chart
            const mrrCtx = document.getElementById('mrrGrowthChart').getContext('2d');
            new Chart(mrrCtx, {
                type: 'line',
                data: {
                    labels: @json($data['timeline']['labels']),
                    datasets: [
                        {
                            label: 'Monthly Recurring Revenue (₹)',
                            data: @json($data['timeline']['mrr']),
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            fill: true,
                            tension: 0.3,
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointBackgroundColor: '#f59e0b',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { callback: v => '₹' + v.toLocaleString('en-IN') }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 2. AMC Frequency Split Doughnut
            const freqCtx = document.getElementById('amcFrequencyChart').getContext('2d');
            new Chart(freqCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Annually', 'Semi-Annually', 'Quarterly', 'Monthly'],
                    datasets: [{
                        data: [
                            {{ $data['snapshot']['frequency_counts']['annually'] }},
                            {{ $data['snapshot']['frequency_counts']['semi_annually'] }},
                            {{ $data['snapshot']['frequency_counts']['quarterly'] }},
                            {{ $data['snapshot']['frequency_counts']['monthly'] }}
                        ],
                        backgroundColor: ['#f59e0b', '#38bdf8', '#a855f7', '#34d399'],
                        borderWidth: 2,
                        borderColor: '#0F172A',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } }
                    }
                }
            });

            // 3. Sales Funnel Volume Chart
            const funnelCtx = document.getElementById('funnelVolumeChart').getContext('2d');
            new Chart(funnelCtx, {
                type: 'bar',
                data: {
                    labels: @json($data['conversion']['monthly_trend']['labels']),
                    datasets: [
                        {
                            label: 'Leads Created',
                            data: @json($data['conversion']['monthly_trend']['leads']),
                            backgroundColor: '#6366f1',
                            borderRadius: 4,
                        },
                        {
                            label: 'Quotations Sent',
                            data: @json($data['conversion']['monthly_trend']['quotations']),
                            backgroundColor: '#38bdf8',
                            borderRadius: 4,
                        },
                        {
                            label: 'Won Accepted',
                            data: @json($data['conversion']['monthly_trend']['accepted']),
                            backgroundColor: '#34d399',
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
                    },
                    scales: {
                        y: { beginAtZero: true },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 4. Quoted Value vs Accepted Value Chart
            const pipelineCtx = document.getElementById('pipelineValueChart').getContext('2d');
            new Chart(pipelineCtx, {
                type: 'bar',
                data: {
                    labels: @json($data['conversion']['monthly_trend']['labels']),
                    datasets: [
                        {
                            label: 'Quoted Pipeline (₹)',
                            data: @json($data['conversion']['monthly_trend']['quoted_amounts']),
                            backgroundColor: '#475569',
                            borderRadius: 4,
                        },
                        {
                            label: 'Accepted Won Value (₹)',
                            data: @json($data['conversion']['monthly_trend']['accepted_amounts']),
                            backgroundColor: '#f59e0b',
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { callback: v => '₹' + v.toLocaleString('en-IN') }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });

        });
    </script>
</x-app-layout>
