<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold leading-tight text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-[0_0_10px_#3b82f6]"></span>
                    Technician Performance &amp; Field Operations
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 font-medium">First-Time Fix Rate (FTFR), Mean Time to Resolution (MTTR), completed installations &amp; engineer leaderboard</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('analytics.technicians.export-pdf', ['range' => $selectedRange, 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"
                   class="crm-btn-primary btn-amber inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-white text-xs font-black shadow-lg transition"
                   style="background-color: var(--crm-accent, #2563eb);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Export Technician Scorecard PDF
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
                        <a href="{{ route('analytics.technicians', ['range' => $rKey]) }}"
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

            {{-- 4 Master Engineer KPI Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                {{-- Team First-Time Fix Rate (FTFR) --}}
                <div class="pg-card p-5 rounded-2xl flex flex-col justify-between" style="min-height:130px">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">First-Time Fix Rate (FTFR)</span>
                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 leading-tight">{{ $data['summary']['team_avg_ftfr'] }}%</div>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-1">Resolved on 1st visit (no 30d repeat)</span>
                    </div>
                </div>

                {{-- Team Mean Time to Resolution (MTTR) --}}
                <div class="pg-card p-5 rounded-2xl flex flex-col justify-between" style="min-height:130px">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Avg Resolution Time (MTTR)</span>
                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-sky-600 dark:text-sky-400 mt-1 leading-tight">{{ $data['summary']['team_avg_resolution_format'] }}</div>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-1">Average ticket resolution</span>
                    </div>
                </div>

                {{-- Completed Installation Jobs --}}
                <div class="pg-card p-5 rounded-2xl flex flex-col justify-between" style="min-height:130px">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Completed Installations</span>
                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-slate-900 dark:text-white mt-1 leading-tight">{{ $data['summary']['total_jobs_completed'] }} Jobs</div>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-1">{{ $data['summary']['total_surveys_completed'] ?? 0 }} site surveys completed</span>
                    </div>
                </div>

                {{-- Customer CSAT Rating --}}
                <div class="pg-card p-5 rounded-2xl flex flex-col justify-between" style="min-height:130px">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Customer CSAT Score</span>
                        <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 leading-tight">{{ $data['summary']['team_avg_csat'] }} / 5.0</div>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-1">Average engineer service rating</span>
                    </div>
                </div>

            </div>

            {{-- 2 Interactive Visual Performance Charts --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Chart 1: First-Time Fix Rate & Resolution Time Comparison --}}
                <div class="pg-card p-6 rounded-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">First-Time Fix Rate (%) &amp; MTTR (Hours) by Engineer</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Benchmark engineer repair accuracy and resolution efficiency</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="ftfrMttrChart"></canvas>
                    </div>
                </div>

                {{-- Chart 2: Completed Workload Distribution --}}
                <div class="pg-card p-6 rounded-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Completed Installations &amp; Engineering Surveys</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Total volume of completed field installations &amp; site surveys</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="workloadChart"></canvas>
                    </div>
                </div>

            </div>

            {{-- Comprehensive Engineer Performance Leaderboard Table --}}
            <div class="pg-card rounded-2xl overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Field Engineer Performance Scorecard &amp; Leaderboard</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Detailed breakdown of first-time fix rate, resolution times, completed installations, and customer ratings</p>
                    </div>
                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-3 py-1 rounded-lg border border-amber-500/20">
                        {{ $data['summary']['total_engineers'] }} Field Engineers
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                                <th class="px-4 py-3 text-left">Engineer</th>
                                <th class="px-4 py-3 text-center text-emerald-600 dark:text-emerald-400">FTFR (%)</th>
                                <th class="px-4 py-3 text-center text-sky-600 dark:text-sky-400">Avg MTTR</th>
                                <th class="px-4 py-3 text-center text-amber-600 dark:text-amber-400">Completed Jobs</th>
                                <th class="px-4 py-3 text-center text-sky-600 dark:text-sky-400">Site Surveys</th>
                                <th class="px-4 py-3 text-center text-indigo-600 dark:text-indigo-400">Projects</th>
                                <th class="px-4 py-3 text-center">Open Backlog</th>
                                <th class="px-4 py-3 text-right text-amber-600 dark:text-amber-400">CSAT ⭐</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($data['technicians'] as $idx => $tech)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black text-xs shrink-0 border border-amber-500/20">
                                                #{{ $idx + 1 }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 dark:text-white">{{ $tech['name'] }}</div>
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400 capitalize">{{ $tech['role'] }} &bull; {{ $tech['email'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="inline-block px-2.5 py-1 rounded-full font-bold text-[11px] {{ $tech['first_time_fix_rate'] >= 80 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30' : ($tech['first_time_fix_rate'] >= 60 ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/30') }}">
                                            {{ $tech['first_time_fix_rate'] }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-bold text-sky-600 dark:text-sky-400">
                                        <span class="inline-block px-2 py-0.5 rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 text-xs">
                                            {{ $tech['avg_resolution_formatted'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="font-black text-amber-600 dark:text-amber-400 text-sm">{{ $tech['jobs_completed'] }}</span>
                                        <span class="text-[11px] text-slate-500 block">of {{ $tech['jobs_total'] }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="font-black text-sky-600 dark:text-sky-400 text-sm">{{ $tech['surveys_completed'] }}</span>
                                        <span class="text-[11px] text-slate-500 block">surveys</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="font-black text-indigo-600 dark:text-indigo-400 text-sm">{{ $tech['projects_count'] }}</span>
                                        <span class="text-[11px] text-slate-500 block">projects</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-semibold">
                                        @if($tech['open_backlog'] > 0)
                                            <span class="inline-block px-2 py-0.5 rounded-full bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/30 font-bold text-[11px]">
                                                {{ $tech['open_backlog'] }} active
                                            </span>
                                        @else
                                            <span class="text-slate-500 text-xs">0 active</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <span class="inline-block px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30 font-bold text-xs">
                                            ⭐ {{ $tech['average_rating'] }} / 5.0
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-slate-500 text-xs">
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

            // 2. Completed Workload (Installations vs Surveys)
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
                            label: 'Completed Site Surveys',
                            data: @json($data['chart_data']['surveys']),
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
