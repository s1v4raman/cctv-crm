<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black tracking-wider text-white uppercase font-['Outfit'] flex items-center gap-2">
                    <span class="text-amber-400">🛠️</span> Technician Performance & Field Operations
                </h2>
                <p class="mt-1 text-xs text-slate-400 font-mono">First-Time Fix Rate (FTFR), Mean Time to Resolution (MTTR), completed installations & engineer leaderboard</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('analytics.technicians.export-pdf', ['range' => $selectedRange, 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 text-xs font-black shadow-lg shadow-amber-500/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Export Technician Scorecard PDF
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
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request()->routeIs('analytics.index') ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'text-slate-400 hover:text-white bg-[#0F172A] border border-white/10' }}">
                    📊 Executive Overview
                </a>
                <a href="{{ route('analytics.technicians', ['range' => $selectedRange]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request()->routeIs('analytics.technicians') ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'text-slate-400 hover:text-white bg-[#0F172A] border border-white/10' }}">
                    🛠️ Technician Performance (FTFR & MTTR)
                </a>
                <a href="{{ route('analytics.mrr-retention', ['range' => $selectedRange]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request()->routeIs('analytics.mrr-retention') ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'text-slate-400 hover:text-white bg-[#0F172A] border border-white/10' }}">
                    📈 Monthly Revenue & AMC Retention
                </a>
                <a href="{{ route('analytics.cost-profit', ['range' => $selectedRange]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ request()->routeIs('analytics.cost-profit') ? 'bg-emerald-500 text-white font-black shadow-lg shadow-emerald-500/20' : 'text-slate-400 hover:text-white bg-[#0F172A] border border-white/10' }}">
                    💰 Cost & Profit Analysis
                </a>
            </div>

            {{-- Date Range Filter Bar --}}
            <div class="bg-[#0F172A] rounded-2xl p-4 shadow-xl border border-white/10 flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-slate-400 font-mono font-bold uppercase tracking-wider text-[11px] mr-2">Timeframe:</span>
                    @foreach(['today' => 'Today', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_quarter' => 'This Quarter', 'this_year' => 'This Year (FY)', 'all_time' => 'All Time'] as $rKey => $rLabel)
                        <a href="{{ route('analytics.technicians', ['range' => $rKey]) }}"
                           class="px-3.5 py-1.5 rounded-xl font-bold transition text-xs {{ $selectedRange === $rKey ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'bg-[#060913] border border-slate-700 text-slate-300 hover:border-slate-500 hover:text-white' }}">
                            {{ $rLabel }}
                        </a>
                    @endforeach
                </div>

                <div class="text-xs font-mono text-slate-400 bg-[#060913] px-3.5 py-1.5 rounded-xl border border-white/10">
                    Active Window: <strong class="text-amber-400">{{ $data['date_range']['label'] }}</strong>
                </div>
            </div>

            {{-- 4 Master Engineer KPI Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                {{-- Team First-Time Fix Rate (FTFR) --}}
                <div class="bg-[#0F172A] p-5 rounded-2xl border border-white/10 shadow-xl flex flex-col justify-between" style="min-height:130px">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400 block">First-Time Fix Rate (FTFR)</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-sm font-bold shrink-0 border border-emerald-500/20">🎯</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-emerald-400 mt-1 leading-tight font-['Outfit']">{{ $data['summary']['team_avg_ftfr'] }}%</div>
                        <span class="text-[11px] text-slate-400 font-mono block mt-1">Resolved on 1st visit (no 30d repeat)</span>
                    </div>
                </div>

                {{-- Team Mean Time to Resolution (MTTR) --}}
                <div class="bg-[#0F172A] p-5 rounded-2xl border border-white/10 shadow-xl flex flex-col justify-between" style="min-height:130px">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400 block">Avg Resolution Time (MTTR)</span>
                        <div class="w-9 h-9 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center text-sm font-bold shrink-0 border border-sky-500/20">⚡</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-sky-400 mt-1 leading-tight font-['Outfit']">{{ $data['summary']['team_avg_resolution_format'] }}</div>
                        <span class="text-[11px] text-slate-400 font-mono block mt-1">Average ticket resolution</span>
                    </div>
                </div>

                {{-- Completed Installation Jobs --}}
                <div class="bg-[#0F172A] p-5 rounded-2xl border border-white/10 shadow-xl flex flex-col justify-between" style="min-height:130px">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400 block">Completed Installations</span>
                        <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-sm font-bold shrink-0 border border-indigo-500/20">📦</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-white mt-1 leading-tight font-['Outfit']">{{ $data['summary']['total_jobs_completed'] }} Jobs</div>
                        <span class="text-[11px] text-slate-400 font-mono block mt-1">+ {{ $data['summary']['total_tickets_resolved'] }} service tickets</span>
                    </div>
                </div>

                {{-- Customer CSAT Rating --}}
                <div class="bg-[#0F172A] p-5 rounded-2xl border border-white/10 shadow-xl flex flex-col justify-between" style="min-height:130px">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400 block">Customer CSAT Score</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-sm font-bold shrink-0 border border-amber-500/20">⭐</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-amber-400 mt-1 leading-tight font-['Outfit']">{{ $data['summary']['team_avg_csat'] }} / 5.0</div>
                        <span class="text-[11px] text-slate-400 font-mono block mt-1">Across digital JCR sign-offs</span>
                    </div>
                </div>

            </div>

            {{-- 2 Interactive Visual Performance Charts --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Chart 1: First-Time Fix Rate & Resolution Time Comparison --}}
                <div class="bg-[#0F172A] p-6 rounded-2xl shadow-xl border border-white/10">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit']">First-Time Fix Rate (%) & MTTR (Hours) by Engineer</h3>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">Benchmark engineer repair accuracy and resolution efficiency</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="ftfrMttrChart"></canvas>
                    </div>
                </div>

                {{-- Chart 2: Completed Workload Distribution --}}
                <div class="bg-[#0F172A] p-6 rounded-2xl shadow-xl border border-white/10">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit']">Completed Installations & Resolved Tickets Workload</h3>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">Total volume of completed field projects & service dispatches</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="workloadChart"></canvas>
                    </div>
                </div>

            </div>

            {{-- Comprehensive Engineer Performance Leaderboard Table --}}
            <div class="bg-[#0F172A] rounded-2xl shadow-xl border border-white/10 overflow-hidden">
                <div class="p-5 bg-[#0B1120] border-b border-white/5 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200 font-['Outfit']">Field Engineer Performance Scorecard & Leaderboard</h3>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">Detailed breakdown of first-time fix rate, resolution times, completed installations, and customer ratings</p>
                    </div>
                    <span class="text-xs font-mono font-bold text-amber-400 bg-amber-500/10 px-3 py-1 rounded-lg border border-amber-500/20">
                        {{ $data['summary']['total_engineers'] }} Field Engineers
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/5 text-xs text-slate-300">
                        <thead>
                            <tr class="bg-[#060913] text-[10px] font-mono uppercase tracking-widest text-slate-400 border-b border-white/5">
                                <th class="px-4 py-3 text-left">Engineer</th>
                                <th class="px-4 py-3 text-center text-emerald-400">FTFR (%)</th>
                                <th class="px-4 py-3 text-center text-sky-400">Avg MTTR</th>
                                <th class="px-4 py-3 text-center text-amber-400">Completed Jobs</th>
                                <th class="px-4 py-3 text-center">Resolved Tickets</th>
                                <th class="px-4 py-3 text-center">Open Backlog</th>
                                <th class="px-4 py-3 text-center">JCR Signed</th>
                                <th class="px-4 py-3 text-right text-amber-400">CSAT ⭐</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 font-mono">
                            @forelse($data['technicians'] as $idx => $tech)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center font-black text-xs shrink-0 border border-amber-500/20">
                                                #{{ $idx + 1 }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-white font-['Outfit']">{{ $tech['name'] }}</div>
                                                <div class="text-[10px] text-slate-400 capitalize">{{ $tech['role'] }} &bull; {{ $tech['email'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="inline-block px-2.5 py-1 rounded-full font-bold text-[10px] {{ $tech['first_time_fix_rate'] >= 80 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : ($tech['first_time_fix_rate'] >= 60 ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30') }}">
                                            {{ $tech['first_time_fix_rate'] }}%
                                        </span>
                                        <div class="text-[9px] text-slate-500 mt-0.5">{{ $tech['first_time_fix_count'] }} of {{ $tech['tickets_resolved'] }}</div>
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-bold text-sky-400">
                                        <span class="inline-block px-2 py-0.5 rounded-lg bg-sky-500/10 text-sky-400 border border-sky-500/20 text-xs">
                                            {{ $tech['avg_resolution_formatted'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="font-black text-amber-400 text-sm">{{ $tech['jobs_completed'] }}</span>
                                        <span class="text-[10px] text-slate-500 block">of {{ $tech['jobs_total'] }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="font-black text-slate-200 text-sm">{{ $tech['tickets_resolved'] }}</span>
                                        <span class="text-[10px] text-slate-500 block">of {{ $tech['tickets_total'] }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-semibold">
                                        @if($tech['open_backlog'] > 0)
                                            <span class="inline-block px-2 py-0.5 rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/30 font-bold text-[10px]">
                                                {{ $tech['open_backlog'] }} active
                                            </span>
                                        @else
                                            <span class="text-slate-500 text-xs">0 active</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-semibold text-emerald-400">
                                        {{ $tech['jcr_signoffs'] }} JCRs
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <span class="inline-block px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/30 font-bold text-xs">
                                            ⭐ {{ $tech['average_rating'] }} / 5.0
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-slate-500 font-mono text-xs">
                                        No technician activity or work orders found in this timeframe.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Initialize Chart.js Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            Chart.defaults.color = '#94a3b8';
            Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.06)';

            // 1. FTFR & MTTR Comparison Chart
            const ftfrCtx = document.getElementById('ftfrMttrChart').getContext('2d');
            new Chart(ftfrCtx, {
                type: 'bar',
                data: {
                    labels: @json($data['chart_data']['labels']),
                    datasets: [
                        {
                            label: 'First-Time Fix Rate (%)',
                            data: @json($data['chart_data']['ftfr']),
                            backgroundColor: '#34d399',
                            borderRadius: 6,
                            yAxisID: 'y',
                        },
                        {
                            label: 'Avg Resolution Time (Hours)',
                            data: @json($data['chart_data']['mttr']),
                            backgroundColor: '#38bdf8',
                            borderRadius: 6,
                            yAxisID: 'y1',
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
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            max: 100,
                            ticks: { callback: v => v + '%' }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            grid: { drawOnChartArea: false },
                            ticks: { callback: v => v + ' hrs' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 2. Completed Workload (Installations vs Tickets)
            const workloadCtx = document.getElementById('workloadChart').getContext('2d');
            new Chart(workloadCtx, {
                type: 'bar',
                data: {
                    labels: @json($data['chart_data']['labels']),
                    datasets: [
                        {
                            label: 'Completed Installations',
                            data: @json($data['chart_data']['jobs']),
                            backgroundColor: '#f59e0b',
                            borderRadius: 6,
                        },
                        {
                            label: 'Resolved Service Tickets',
                            data: @json($data['chart_data']['tickets']),
                            backgroundColor: '#38bdf8',
                            borderRadius: 6,
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

        });
    </script>
</x-app-layout>
