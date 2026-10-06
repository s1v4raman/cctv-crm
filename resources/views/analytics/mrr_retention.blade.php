<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black tracking-wider text-white uppercase font-['Outfit'] flex items-center gap-2">
                    <span class="text-amber-400">📈</span> Monthly Revenue, AMC Retention & Lead Conversion
                </h2>
                <p class="mt-1 text-xs text-slate-400 font-mono">Monthly recurring revenue (MRR), annual recurring revenue (ARR), AMC contract renewal retention, and sales funnel conversion</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('analytics.mrr-retention.export-pdf', ['range' => $selectedRange, 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"
                   class="crm-btn-primary btn-amber inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-white text-xs font-black shadow-lg transition"
                   style="background-color: var(--crm-accent, #2563eb);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Export MRR & Retention PDF
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Include Chart.js via CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <div class="py-8 bg-[#060913] min-h-screen text-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Navigation Sub-Tabs --}}
            <div class="flex items-center gap-2 border-b border-white/10 pb-4 overflow-x-auto">
                <a href="{{ route('analytics.index', ['range' => $selectedRange]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request()->routeIs('analytics.index') ? 'crm-tab-active text-white font-black shadow-xs' : 'text-slate-400 hover:text-white bg-[#0F172A] border border-white/10' }}"
                   @if(request()->routeIs('analytics.index')) style="background-color: var(--crm-accent, #2563eb); color: #fff;" @endif>
                    📊 Executive Overview
                </a>
                <a href="{{ route('analytics.technicians', ['range' => $selectedRange]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request()->routeIs('analytics.technicians') ? 'crm-tab-active text-white font-black shadow-xs' : 'text-slate-400 hover:text-white bg-[#0F172A] border border-white/10' }}"
                   @if(request()->routeIs('analytics.technicians')) style="background-color: var(--crm-accent, #2563eb); color: #fff;" @endif>
                    🛠️ Technician Performance (FTFR & MTTR)
                </a>
                <a href="{{ route('analytics.mrr-retention', ['range' => $selectedRange]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request()->routeIs('analytics.mrr-retention') ? 'crm-tab-active text-white font-black shadow-xs' : 'text-slate-400 hover:text-white bg-[#0F172A] border border-white/10' }}"
                   @if(request()->routeIs('analytics.mrr-retention')) style="background-color: var(--crm-accent, #2563eb); color: #fff;" @endif>
                    📈 Monthly Revenue & AMC Retention
                </a>
                <a href="{{ route('analytics.cost-profit', ['range' => $selectedRange]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request()->routeIs('analytics.cost-profit') ? 'crm-tab-active text-white font-black shadow-xs' : 'text-slate-400 hover:text-white bg-[#0F172A] border border-white/10' }}"
                   @if(request()->routeIs('analytics.cost-profit')) style="background-color: var(--crm-accent, #2563eb); color: #fff;" @endif>
                    💰 Cost & Profit Analysis
                </a>
            </div>

            {{-- Date Range Filter Bar --}}
            <div class="bg-[#0F172A] rounded-2xl p-4 shadow-xl border border-white/10 flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-slate-400 font-mono font-bold uppercase tracking-wider text-[11px] mr-2">Timeframe:</span>
                    @foreach(['today' => 'Today', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_quarter' => 'This Quarter', 'this_year' => 'This Year (FY)', 'all_time' => 'All Time'] as $rKey => $rLabel)
                        <a href="{{ route('analytics.mrr-retention', ['range' => $rKey]) }}"
                           class="px-3.5 py-1.5 rounded-xl font-bold transition text-xs {{ $selectedRange === $rKey ? 'crm-pill-active text-white font-black shadow-xs' : 'bg-[#060913] border border-slate-700 text-slate-300 hover:border-slate-500 hover:text-white' }}"
                           @if($selectedRange === $rKey) style="background-color: var(--crm-accent, #2563eb); color: #fff;" @endif>
                            {{ $rLabel }}
                        </a>
                    @endforeach
                </div>

                <div class="text-xs font-mono text-slate-400 bg-[#060913] px-3.5 py-1.5 rounded-xl border border-white/10">
                    Active Window: <strong class="text-amber-400">{{ $data['date_range']['label'] }}</strong>
                </div>
            </div>

            {{-- 4 Master Financial & Funnel KPI Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                {{-- Monthly Recurring Revenue (MRR) --}}
                <div class="bg-[#0F172A] p-5 rounded-2xl border border-white/10 shadow-xl flex flex-col justify-between" style="min-height:130px">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400 block">Monthly Recurring (MRR)</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-sm font-bold shrink-0 border border-amber-500/20">🔄</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-amber-400 mt-1 leading-tight font-['Outfit']">₹{{ number_format($data['snapshot']['active_mrr'], 2) }}</div>
                        <span class="text-[11px] text-slate-400 font-mono block mt-1">ARR: ₹{{ number_format($data['snapshot']['active_arr'], 2) }} / year</span>
                    </div>
                </div>

                {{-- AMC Retention Rate --}}
                <div class="bg-[#0F172A] p-5 rounded-2xl border border-white/10 shadow-xl flex flex-col justify-between" style="min-height:130px">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400 block">AMC Contract Retention</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-sm font-bold shrink-0 border border-emerald-500/20">🛡️</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-emerald-400 mt-1 leading-tight font-['Outfit']">{{ $data['snapshot']['retention_rate'] }}%</div>
                        <span class="text-[11px] text-slate-400 font-mono block mt-1">{{ $data['snapshot']['active_contracts'] }} active (Churn: {{ $data['snapshot']['churn_rate'] }}%)</span>
                    </div>
                </div>

                {{-- Lead to Accepted Quote Conversion --}}
                <div class="bg-[#0F172A] p-5 rounded-2xl border border-white/10 shadow-xl flex flex-col justify-between" style="min-height:130px">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400 block">Lead-to-Won Conversion</span>
                        <div class="w-9 h-9 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center text-sm font-bold shrink-0 border border-sky-500/20">🎯</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-sky-400 mt-1 leading-tight font-['Outfit']">{{ $data['conversion']['lead_to_won_rate'] }}%</div>
                        <span class="text-[11px] text-slate-400 font-mono block mt-1">Quote-to-Won: {{ $data['conversion']['quote_to_accepted_rate'] }}%</span>
                    </div>
                </div>

                {{-- Average Quote Turnaround Time --}}
                <div class="bg-[#0F172A] p-5 rounded-2xl border border-white/10 shadow-xl flex flex-col justify-between" style="min-height:130px">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400 block">Avg Quote Close Time</span>
                        <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-sm font-bold shrink-0 border border-indigo-500/20">⏳</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-white mt-1 leading-tight font-['Outfit']">{{ $data['conversion']['avg_turnaround_days'] }} Days</div>
                        <span class="text-[11px] text-slate-400 font-mono block mt-1">Avg turnaround to acceptance</span>
                    </div>
                </div>

            </div>

            {{-- 4 Chart.js Interactive Graphs --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Chart 1: 12-Month MRR Historical Growth Trend --}}
                <div class="bg-[#0F172A] p-6 rounded-2xl shadow-xl border border-white/10">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit']">12-Month Monthly Recurring Revenue (MRR) Trend</h3>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">Track active subscription growth and recurring cash flow predictability</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="mrrGrowthChart"></canvas>
                    </div>
                </div>

                {{-- Chart 2: AMC Billing Frequency Distribution --}}
                <div class="bg-[#0F172A] p-6 rounded-2xl shadow-xl border border-white/10">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit']">AMC Portfolio Frequency Split</h3>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">Contract composition across Annual, Semi-Annual, Quarterly & Monthly plans</p>
                        </div>
                    </div>
                    <div class="h-64 flex items-center justify-center">
                        <canvas id="amcFrequencyChart"></canvas>
                    </div>
                </div>

                {{-- Chart 3: 12-Month Lead -> Quote -> Accepted Conversion Funnel Trend --}}
                <div class="bg-[#0F172A] p-6 rounded-2xl shadow-xl border border-white/10">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit']">12-Month Sales Funnel Volume</h3>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">Leads generated &rarr; Quotations issued &rarr; Won accepted projects</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="funnelVolumeChart"></canvas>
                    </div>
                </div>

                {{-- Chart 4: Quoted Value vs Accepted Won Value --}}
                <div class="bg-[#0F172A] p-6 rounded-2xl shadow-xl border border-white/10">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit']">Monthly Quoted Pipeline vs Won Revenue (₹)</h3>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">Total proposal pipeline value vs successfully converted deal value</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="pipelineValueChart"></canvas>
                    </div>
                </div>

            </div>

            {{-- 12-Month Detailed Funnel & Conversion Historical Table --}}
            <div class="bg-[#0F172A] rounded-2xl shadow-xl border border-white/10 overflow-hidden">
                <div class="p-5 bg-[#0B1120] border-b border-white/5 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit']">12-Month Sales Conversion & MRR Ledger</h3>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">Month-by-month breakdown of lead volume, quotations, won conversions, and recurring revenue</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/5 text-xs text-slate-300">
                        <thead>
                            <tr class="bg-[#060913] text-[10px] font-mono uppercase tracking-widest text-slate-400 border-b border-white/5">
                                <th class="px-4 py-3 text-left">Month</th>
                                <th class="px-4 py-3 text-center text-amber-400">MRR (₹)</th>
                                <th class="px-4 py-3 text-center">Leads</th>
                                <th class="px-4 py-3 text-center">Quotes Issued</th>
                                <th class="px-4 py-3 text-center text-emerald-400">Quotes Won</th>
                                <th class="px-4 py-3 text-center text-sky-400">Win Rate (%)</th>
                                <th class="px-4 py-3 text-right">Quoted Pipeline</th>
                                <th class="px-4 py-3 text-right text-amber-400">Won Value (₹)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 font-mono">
                            @foreach($data['conversion']['monthly_trend']['labels'] as $idx => $mLabel)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="px-4 py-3.5 font-bold text-white">{{ $mLabel }}</td>
                                    <td class="px-4 py-3.5 text-center font-bold text-amber-400">₹{{ number_format($data['timeline']['mrr'][$idx], 2) }}</td>
                                    <td class="px-4 py-3.5 text-center text-slate-300">{{ $data['conversion']['monthly_trend']['leads'][$idx] }}</td>
                                    <td class="px-4 py-3.5 text-center text-slate-300">{{ $data['conversion']['monthly_trend']['quotations'][$idx] }}</td>
                                    <td class="px-4 py-3.5 text-center font-bold text-emerald-400">{{ $data['conversion']['monthly_trend']['accepted'][$idx] }}</td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full font-bold text-[10px] {{ $data['conversion']['monthly_trend']['conversion_rates'][$idx] >= 40 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400' }}">
                                            {{ $data['conversion']['monthly_trend']['conversion_rates'][$idx] }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-right text-slate-400">₹{{ number_format($data['conversion']['monthly_trend']['quoted_amounts'][$idx], 2) }}</td>
                                    <td class="px-4 py-3.5 text-right font-bold text-amber-400">₹{{ number_format($data['conversion']['monthly_trend']['accepted_amounts'][$idx], 2) }}</td>
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
