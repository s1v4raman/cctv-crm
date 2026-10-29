<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <span id="last-updated" class="hidden"></span>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Dashboard</h2>
            </div>
            @if(!$isAdmin)
                <div>
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                        My Attendance
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    {{-- Dashboard Error Banner --}}
    <div id="dashboard-error" class="hidden mb-[20px] p-[12px] bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded"></div>

    <div class="space-y-[20px]">
        @if($isAdmin)
            {{-- ========================================================================= --}}
            {{-- ADMIN EXECUTIVE DASHBOARD --}}
            {{-- ========================================================================= --}}

            {{-- ── 1. Top Stat Cards: one row, equal width, flexbox with gap: 12px. Maximum 4 cards. ── --}}
            <div class="flex flex-col sm:flex-row gap-[12px] mb-[20px] w-full">

                {{-- Total Leads Card --}}
                <div class="stat-card flex-1 p-[16px] bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl shadow-xs">
                    <div class="text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wide">Total Leads</div>
                    <div class="text-2xl font-bold text-[var(--text-main)] mt-1" id="stat-total-leads">{{ $totalLeads ?? 16 }}</div>
                    <span id="stat-leads-month" class="hidden">0</span>
                </div>

                {{-- Conversion Rate Card --}}
                <div class="stat-card flex-1 p-[16px] bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl shadow-xs">
                    <div class="text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wide">Conversion Rate</div>
                    <div class="text-2xl font-bold text-[var(--text-main)] mt-1"><span id="stat-conversion">0</span>%</div>
                    <span id="stat-won" class="hidden">0</span>
                    <span id="stat-lost" class="hidden">0</span>
                </div>

                {{-- Accepted Revenue Card --}}
                <div class="stat-card flex-1 p-[16px] bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl shadow-xs">
                    <div class="text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wide">Accepted Revenue</div>
                    <div class="text-2xl font-bold text-[var(--text-main)] mt-1" id="stat-revenue">₹0</div>
                    <span id="stat-revenue-month" class="hidden">0</span>
                </div>

                {{-- Open Jobs Card --}}
                <div class="stat-card flex-1 p-[16px] bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl shadow-xs">
                    <div class="text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wide">Open Jobs</div>
                    <div class="text-2xl font-bold text-[var(--text-main)] mt-1" id="stat-open-jobs">0</div>
                    <span id="stat-inprogress-jobs" class="hidden">0</span>
                    {{-- Hidden spans for DOM script compatibility --}}
                    <span id="stat-total-projects" class="hidden">0</span>
                    <span id="stat-projects-in-progress" class="hidden">0</span>
                    <span id="stat-project-val" class="hidden">₹0</span>
                </div>

            </div>

            {{-- ── 2. Middle Row: Sales & Deals Pipeline Funnel ── --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-[20px] mb-[20px]">

                {{-- Sales (Bar Chart): Single box with a border, no nested cards --}}
                <div class="lg:col-span-7 bg-[var(--card-bg)] text-[var(--text-main)] border border-[var(--card-border)] rounded-xl p-[20px] shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-[var(--card-border)] mb-4">
                        <h3 class="text-sm font-bold text-[var(--text-main)]">Revenue Overview</h3>
                        <div class="flex items-center gap-3">
                            <select class="h-[32px] px-2.5 text-xs rounded-md border border-[var(--input-border)] bg-[var(--card-bg)] text-[var(--text-main)] focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                                <option value="3">Past 3 Months</option>
                                <option value="6" selected>Past 6 Months</option>
                                <option value="12">Past 12 Months</option>
                            </select>
                        </div>
                    </div>

                    <div id="revenue-chart-container" class="relative" style="height: 270px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                    <div id="revenue-table-container" class="hidden overflow-x-auto my-2">
                        <table class="w-full text-xs border border-[var(--table-border)] border-collapse">
                            <thead>
                                <tr class="border-b border-[var(--table-border)] bg-[var(--table-header-bg)] text-[var(--text-main)]">
                                    <th class="text-left py-2 px-3 font-semibold border-r border-[var(--table-border)]">Period</th>
                                    <th class="text-right py-2 px-3 font-semibold">Accepted Revenue</th>
                                </tr>
                            </thead>
                            <tbody id="revenue-table-body" class="divide-y divide-[var(--table-border)] text-[var(--text-main)]">
                            </tbody>
                        </table>
                    </div>

                    {{-- Sales Mini Metrics Footer: clean columns, no card inside card --}}
                    <div class="grid grid-cols-3 gap-[12px] pt-[16px] mt-[16px] border-t border-[var(--card-border)] text-center sm:text-left">
                        <div>
                            <div class="text-[11px] font-medium uppercase tracking-wider text-[var(--text-muted)]">Invoiced</div>
                            <div class="text-base font-bold text-[var(--text-main)] mt-0.5" id="stat-footer-rev">₹0</div>
                        </div>
                        <div>
                            <div class="text-[11px] font-medium uppercase tracking-wider text-[var(--text-muted)]">Collected</div>
                            <div class="text-base font-bold text-[var(--text-main)] mt-0.5" id="stat-footer-collected">₹0</div>
                        </div>
                        <div>
                            <div class="text-[11px] font-medium uppercase tracking-wider text-[var(--text-muted)]">Open Quotes</div>
                            <div class="text-base font-bold text-[var(--text-main)] mt-0.5" id="stat-footer-active">0</div>
                        </div>
                    </div>
                </div>

                {{-- Deals Pipeline: Table with defined borders between headers, rows, and cells --}}
                <div class="lg:col-span-5 bg-[var(--card-bg)] text-[var(--text-main)] border border-[var(--card-border)] rounded-xl p-5 flex flex-col justify-between shadow-xs">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-[var(--card-border)] mb-3">
                            <h3 class="text-sm font-bold text-[var(--text-main)]">Deals Pipeline</h3>
                            <a href="{{ route('quotations.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                View all
                            </a>
                        </div>

                        <table class="w-full text-xs border border-[var(--table-border)] border-collapse rounded overflow-hidden">
                            <thead>
                                <tr class="border-b border-[var(--table-border)] bg-[var(--table-header-bg)] text-[var(--text-main)]">
                                    <th class="text-left py-2 px-3 font-semibold border-r border-[var(--table-border)]">Stage</th>
                                    <th class="text-right py-2 px-3 font-semibold">Deals</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--table-border)] text-[var(--text-main)]">
                                <tr class="border-b border-[var(--table-border)]">
                                    <td class="py-2.5 px-3 border-r border-[var(--table-border)] text-[var(--text-main)]">Draft Proposals</td>
                                    <td class="py-2.5 px-3 text-right font-medium text-[var(--text-main)]" id="stat-draft-quotes">0</td>
                                </tr>
                                <tr class="border-b border-[var(--table-border)]">
                                    <td class="py-2.5 px-3 border-r border-[var(--table-border)] text-[var(--text-main)]">Sent to Client</td>
                                    <td class="py-2.5 px-3 text-right font-medium text-[var(--text-main)]" id="stat-sent-quotes">0</td>
                                </tr>
                                <tr class="border-b border-[var(--table-border)]">
                                    <td class="py-2.5 px-3 border-r border-[var(--table-border)] text-[var(--text-main)]">Accepted / Won</td>
                                    <td class="py-2.5 px-3 text-right font-medium text-[var(--text-main)]" id="stat-accepted-quotes">0</td>
                                </tr>
                                <tr class="border-b border-[var(--table-border)]">
                                    <td class="py-2.5 px-3 border-r border-[var(--table-border)] text-[var(--text-main)]">Expired / Stalled</td>
                                    <td class="py-2.5 px-3 text-right font-medium text-[var(--text-main)]" id="stat-expired-quotes">0</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-3 border-r border-[var(--table-border)] text-[var(--text-main)]">Declined / Lost</td>
                                    <td class="py-2.5 px-3 text-right font-medium text-[var(--text-main)]" id="stat-rejected-quotes">0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Pipeline Total Summary --}}
                    <div class="pt-4 mt-4 border-t border-[var(--card-border)] flex items-center justify-between text-xs">
                        <span class="font-medium text-[var(--text-muted)]">Value of open quotes (draft + sent)</span>
                        <span class="font-bold text-[var(--text-main)] text-sm" id="stat-pipeline-total">₹0</span>
                    </div>
                </div>

                </div>

                {{-- ── 3. Bottom Row: Recent Leads & Scheduled Jobs ── --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                    {{-- Recent Leads Card --}}
                    <div class="bg-[var(--card-bg)] text-[var(--text-main)] border border-[var(--card-border)] rounded-xl p-5 shadow-xs transition-colors duration-200">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-sm font-bold text-[var(--text-main)] tracking-tight">Recent Leads</h3>
                            </div>
                            <a href="{{ route('leads.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                View all
                            </a>
                        </div>

                        <div class="divide-y divide-[var(--card-border)]" id="recent-leads-list">
                            <div class="py-8 text-center text-xs text-[var(--text-muted)]">Loading leads...</div>
                        </div>
                    </div>

                    {{-- Tasks & Scheduled Jobs Card --}}
                    <div class="bg-[var(--card-bg)] text-[var(--text-main)] border border-[var(--card-border)] rounded-xl p-5 shadow-xs transition-colors duration-200">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-sm font-bold text-[var(--text-main)] tracking-tight">Tasks &amp; Scheduled Work</h3>
                            </div>
                            <a href="{{ route('jobs.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                View all
                            </a>
                        </div>

                        <div class="divide-y divide-[var(--card-border)]" id="upcoming-jobs-list">
                            <div class="py-8 text-center text-xs text-[var(--text-muted)]">Loading jobs...</div>
                        </div>
                    </div>

                </div>

                {{-- ── 4. Secondary Operations Row (Quotations & Job Distribution) ── --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-6">

                    {{-- Quotation Distribution --}}
                    <div class="bg-[var(--card-bg)] text-[var(--text-main)] border border-[var(--card-border)] rounded-xl p-5 shadow-xs transition-colors duration-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-[var(--text-main)]">Quotation Breakdown</h4>
                            <span class="text-xs text-[var(--text-muted)]">Status</span>
                        </div>
                        <div class="relative" style="height: 180px;">
                            <canvas id="quotationChart"></canvas>
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-2 mt-4 text-[11px]" id="quotation-legend"></div>
                    </div>

                    {{-- Lead Status Breakdown --}}
                    <div class="bg-[var(--card-bg)] text-[var(--text-main)] border border-[var(--card-border)] rounded-xl p-5 shadow-xs transition-colors duration-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-[var(--text-main)]">Lead Status Share</h4>
                            <span class="text-xs text-[var(--text-muted)]">Pipeline</span>
                        </div>
                        <div class="relative" style="height: 180px;">
                            <canvas id="leadChart"></canvas>
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-2 mt-4 text-[11px]" id="lead-legend"></div>
                    </div>

                    {{-- Installation Jobs Bar Chart --}}
                    <div class="bg-[var(--card-bg)] text-[var(--text-main)] border border-[var(--card-border)] rounded-xl p-5 shadow-xs transition-colors duration-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-[var(--text-main)]">Field Job Distribution</h4>
                            <span class="text-xs text-[var(--text-muted)]">Work Orders</span>
                        </div>
                        <div class="relative" style="height: 180px;">
                            <canvas id="jobChart"></canvas>
                        </div>
                    </div>

                </div>

            @else
                {{-- ========================================================================= --}}
                {{-- STAFF WORK-BASED DASHBOARD --}}
                {{-- ========================================================================= --}}

                {{-- ── 1. Top 4 Metric Cards: one row, equal width, flexbox with gap: 12px ── --}}
                <div class="flex flex-col sm:flex-row gap-[12px] mb-[20px] w-full">

                    {{-- Open Jobs Card --}}
                    <div class="stat-card flex-1 p-[16px] bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl shadow-xs">
                        <div class="text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wide">Open Jobs</div>
                        <div class="text-2xl font-bold text-[var(--text-main)] mt-1" id="staff-open-jobs">0</div>
                        <span id="staff-inprogress-jobs" class="hidden">0</span>
                    </div>

                    {{-- Support Tickets Card --}}
                    <div class="stat-card flex-1 p-[16px] bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl shadow-xs">
                        <div class="text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wide">Support Tickets</div>
                        <div class="text-2xl font-bold text-[var(--text-main)] mt-1" id="staff-open-tickets">0</div>
                        <span id="staff-critical-tickets" class="hidden">0</span>
                    </div>

                    {{-- Completed Jobs Card --}}
                    <div class="stat-card flex-1 p-[16px] bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl shadow-xs">
                        <div class="text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wide">Completed Jobs</div>
                        <div class="text-2xl font-bold text-[var(--text-main)] mt-1" id="staff-completed-jobs">0</div>
                    </div>

                    {{-- Attendance Card --}}
                    <div class="stat-card flex-1 p-[16px] bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl shadow-xs">
                        <div class="text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wide">Attendance</div>
                        <div class="text-2xl font-bold text-[var(--text-main)] mt-1 capitalize" id="staff-att-status">
                            {{ $myAttendanceToday ? ucfirst($myAttendanceToday->status) : 'Not Clocked In' }}
                        </div>
                        <span id="staff-att-badge" class="hidden"></span>
                        <span id="staff-att-details" class="hidden"></span>
                    </div>

                </div>

                {{-- ── 2. Middle Row: Jobs & Support Tickets ── --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">

                    {{-- Jobs Card --}}
                    <div class="bg-[var(--card-bg)] text-[var(--text-main)] border border-[var(--card-border)] rounded-xl p-5 shadow-xs transition-colors duration-200">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-sm font-bold text-[var(--text-main)] tracking-tight">Tasks &amp; Scheduled Field Jobs</h3>
                            </div>
                            <a href="{{ route('jobs.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                View all
                            </a>
                        </div>

                        <div class="divide-y divide-[var(--card-border)]" id="upcoming-jobs-list">
                            <div class="py-8 text-center text-xs text-[var(--text-muted)]">Loading upcoming jobs...</div>
                        </div>
                    </div>

                    {{-- Support Tickets Queue --}}
                    <div class="bg-[var(--card-bg)] text-[var(--text-main)] border border-[var(--card-border)] rounded-xl p-5 shadow-xs transition-colors duration-200">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-sm font-bold text-[var(--text-main)] tracking-tight">Active Support &amp; SLA Tickets</h3>
                            </div>
                            <a href="{{ route('service-tickets.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                View all
                            </a>
                        </div>

                        <div class="divide-y divide-[var(--card-border)]" id="staff-tickets-list">
                            <div class="py-8 text-center text-xs text-[var(--text-muted)]">Loading service tickets...</div>
                        </div>
                    </div>

                </div>

                {{-- ── 3. Bottom Row: Work Distribution & Operations Hub ── --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                    {{-- Field Job Distribution Chart --}}
                    <div class="lg:col-span-2 bg-[var(--card-bg)] text-[var(--text-main)] border border-[var(--card-border)] rounded-xl p-6 shadow-xs transition-colors duration-200">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-base font-bold text-[var(--text-main)] tracking-tight">Field Work Distribution</h4>
                                <p class="text-xs text-[var(--text-muted)] mt-0.5">Status breakdown of current installation jobs</p>
                            </div>
                        </div>
                        <div class="relative" style="height: 220px;">
                            <canvas id="jobChart"></canvas>
                        </div>
                    </div>

                    {{-- Operational Quick Links Hub --}}
                    <div class="bg-[var(--card-bg)] text-[var(--text-main)] border border-[var(--card-border)] rounded-xl p-5 shadow-xs transition-colors duration-200 flex flex-col justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight mb-1">Field Operations Hub</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Quick access to field engineering modules</p>

                            <div class="space-y-2.5">
                                <a href="{{ route('calendar.index') }}" class="flex items-center justify-between p-3 rounded-[4px] bg-slate-50 dark:bg-slate-800/60 hover:bg-blue-50 dark:hover:bg-blue-950/40 border border-[#eee] dark:border-slate-800 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-[4px] bg-blue-100 dark:bg-blue-900/50 text-[#2563eb] dark:text-blue-400 flex items-center justify-center font-bold">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">Calendar &amp; Dispatch</div>
                                            <div class="text-[11px] text-slate-400">View daily schedule</div>
                                        </div>
                                    </div>
                                    <span class="text-xs text-[#2563eb] font-semibold">Open &rarr;</span>
                                </a>

                                <a href="{{ route('projects.index') }}" class="flex items-center justify-between p-3 rounded-[4px] bg-slate-50 dark:bg-slate-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-[#eee] dark:border-slate-800 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-[4px] bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="7" rx="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line stroke-linecap="round" stroke-linejoin="round" x1="2" x2="22" y1="13" y2="13"/></svg>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">Projects &amp; Sites</div>
                                            <div class="text-[11px] text-slate-400">Architecture &amp; execution</div>
                                        </div>
                                    </div>
                                    <span class="text-xs text-indigo-600 font-semibold">Open &rarr;</span>
                                </a>

                                <a href="{{ route('equipment.index') }}" class="flex items-center justify-between p-3 rounded-[4px] bg-slate-50 dark:bg-slate-800/60 hover:bg-purple-50 dark:hover:bg-purple-950/40 border border-[#eee] dark:border-slate-800 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-[4px] bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">Equipment &amp; Hardware</div>
                                            <div class="text-[11px] text-slate-400">Serial numbers &amp; warranty</div>
                                        </div>
                                    </div>
                                    <span class="text-xs text-purple-600 font-semibold">Open &rarr;</span>
                                </a>
                            </div>
                        </div>

                        <div class="pt-4 mt-4 border-t border-[#eee] dark:border-slate-800 text-center">
                            <a href="{{ route('attendance.index') }}" class="text-xs font-semibold text-[#2563eb] dark:text-blue-400 hover:underline">
                                Go to Daily Attendance System &rarr;
                            </a>
                        </div>
                    </div>

                </div>
            @endif

        </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js"></script>

    <script>
    "use strict";

    const IS_ADMIN = {{ $isAdmin ? 'true' : 'false' }};

    /* Clean SaaS Colour Palette */
    const C = {
        primary: '#2563eb',
        sky:     '#38bdf8',
        emerald: '#10b981',
        amber:   '#f59e0b',
        purple:  '#8b5cf6',
        rose:    '#f43f5e',
        slate:   '#64748b',
    };

    /* Global chart instances & cache */
    let revenueChart, quotationChart, leadChart, jobChart;
    let latestChartData = null;

    /* Helpers */
    function isDarkTheme() {
        return document.documentElement.classList.contains('dark');
    }

    function money(v) {
        return '₹' + Number(v || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 });
    }

    function escapeHtml(v) {
        const el = document.createElement('div');
        el.textContent = v || '';
        return el.innerHTML;
    }

    function statusBadge(status) {
        const s = (status || '').toLowerCase();
        let color = 'var(--text-main)';
        let bg = 'var(--table-header-bg)';
        let border = 'var(--card-border)';
        if (s.includes('won') || s.includes('accepted') || s.includes('completed') || s.includes('active') || s.includes('paid')) {
            color = 'var(--status-green)';
            bg = isDarkTheme() ? 'rgba(74, 222, 128, 0.15)' : 'rgba(22, 163, 74, 0.12)';
            border = 'var(--status-green)';
        } else if (s.includes('lost') || s.includes('rejected') || s.includes('cancelled') || s.includes('critical') || s.includes('expired')) {
            color = 'var(--status-red)';
            bg = isDarkTheme() ? 'rgba(248, 113, 113, 0.15)' : 'rgba(220, 38, 38, 0.12)';
            border = 'var(--status-red)';
        } else if (s.includes('pending') || s.includes('draft') || s.includes('sent') || s.includes('progress') || s.includes('scheduled')) {
            color = 'var(--status-orange)';
            bg = isDarkTheme() ? 'rgba(251, 191, 36, 0.15)' : 'rgba(217, 119, 6, 0.12)';
            border = 'var(--status-orange)';
        }
        const label = (status || '').replace(/_/g, ' ');
        return `<span style="border: 1px solid ${border}; border-radius: 4px; padding: 2px 8px; font-size: 11px; color: ${color}; text-transform: capitalize; background: ${bg}; font-weight: 600;">${escapeHtml(label)}</span>`;
    }

    function getInitials(name) {
        if (!name) return 'U';
        const parts = name.trim().split(' ');
        if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }

    const baseFont = { family: "'Poppins', 'Arial', sans-serif", size: 11 };

    function getGridColor() {
        return isDarkTheme() ? 'rgba(148, 163, 184, 0.12)' : 'rgba(226, 232, 240, 0.7)';
    }

    function getTickColor() {
        return isDarkTheme() ? '#94a3b8' : '#64748b';
    }

    function baseOptions(extra = {}) {
        const dark = isDarkTheme();
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: dark ? '#0f172a' : 'var(--card-border)',
                    titleColor: dark ? '#f8fafc' : '#f8fafc',
                    bodyColor: dark ? '#cbd5e1' : '#cbd5e1',
                    borderColor: dark ? 'var(--card-border)' : 'transparent',
                    borderWidth: dark ? 1 : 0,
                    padding: 10,
                    cornerRadius: 4,
                    titleFont: { ...baseFont, weight: '700' },
                    bodyFont: { ...baseFont },
                    displayColors: false,
                }
            },
            ...extra
        };
    }

    function buildLegend(containerId, labels, colors) {
        const el = document.getElementById(containerId);
        if (!el) return;
        el.innerHTML = labels.map((lbl, i) => `
            <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                <span class="w-2 h-2 rounded-sm" style="background:${colors[i]}"></span>
                ${escapeHtml(lbl)}
            </span>
        `).join('');
    }

    /* Stat card updates */
    function updateStats(data) {
        const s = data.stats || {};
        if (IS_ADMIN) {
            const elTotalLeads = document.getElementById('stat-total-leads');
            if (elTotalLeads) elTotalLeads.textContent = s.total_leads || 0;
            const elLeadsMo = document.getElementById('stat-leads-month');
            if (elLeadsMo) elLeadsMo.textContent = s.leads_this_month || 0;
            const elConv = document.getElementById('stat-conversion');
            if (elConv) elConv.textContent = s.conversion_rate || 0;
            const elWon = document.getElementById('stat-won');
            if (elWon) elWon.textContent = s.won_leads || 0;
            const elLost = document.getElementById('stat-lost');
            if (elLost) elLost.textContent = s.lost_leads || 0;
            const elRev = document.getElementById('stat-revenue');
            if (elRev) elRev.textContent = money(s.revenue_accepted);
            const elRevMo = document.getElementById('stat-revenue-month');
            if (elRevMo) elRevMo.textContent = Number(s.revenue_this_month || 0).toLocaleString('en-IN');
            const elOpenJobs = document.getElementById('stat-open-jobs');
            if (elOpenJobs) elOpenJobs.textContent = s.open_jobs || 0;
            const elInprogJobs = document.getElementById('stat-inprogress-jobs');
            if (elInprogJobs) elInprogJobs.textContent = s.in_progress_jobs || 0;

            const elTotPrj = document.getElementById('stat-total-projects');
            if (elTotPrj) elTotPrj.textContent = s.total_projects || 0;
            const elInprogPrj = document.getElementById('stat-projects-in-progress');
            if (elInprogPrj) elInprogPrj.textContent = s.in_progress_projects || 0;
            const elValPrj = document.getElementById('stat-project-val');
            if (elValPrj) elValPrj.textContent = money(s.total_project_valuation || 0);
            
            const elDraft = document.getElementById('stat-draft-quotes');
            if (elDraft) elDraft.textContent = s.draft_quotations || 0;
            const elSent = document.getElementById('stat-sent-quotes');
            if (elSent) elSent.textContent = s.sent_quotations || 0;
            const elAcc = document.getElementById('stat-accepted-quotes');
            if (elAcc) elAcc.textContent = s.accepted_quotations || 0;
            const elRej = document.getElementById('stat-rejected-quotes');
            if (elRej) elRej.textContent = s.rejected_quotations || 0;
            const elExp = document.getElementById('stat-expired-quotes');
            if (elExp) elExp.textContent = s.expired_quotations || 0;

            const elFootRev = document.getElementById('stat-footer-rev');
            if (elFootRev) elFootRev.textContent = money(s.invoiced_total);
            const elFootCol = document.getElementById('stat-footer-collected');
            if (elFootCol) elFootCol.textContent = money(s.collected_total);
            const elFootAct = document.getElementById('stat-footer-active');
            const openQuotes = (s.draft_quotations || 0) + (s.sent_quotations || 0);
            if (elFootAct) elFootAct.textContent = openQuotes + (openQuotes === 1 ? ' quote' : ' quotes');
            const elPipeTot = document.getElementById('stat-pipeline-total');
            if (elPipeTot) elPipeTot.textContent = money(s.pipeline_value);
        } else {
            // Staff / Technician view
            const elStaffJobs = document.getElementById('staff-open-jobs');
            if (elStaffJobs) elStaffJobs.textContent = s.open_jobs || 0;
            const elStaffInprog = document.getElementById('staff-inprogress-jobs');
            if (elStaffInprog) elStaffInprog.textContent = s.in_progress_jobs || 0;
            const elStaffTickets = document.getElementById('staff-open-tickets');
            if (elStaffTickets) elStaffTickets.textContent = s.open_tickets || 0;
            const elStaffCrit = document.getElementById('staff-critical-tickets');
            if (elStaffCrit) elStaffCrit.textContent = s.critical_tickets || 0;
            const elStaffComp = document.getElementById('staff-completed-jobs');
            if (elStaffComp) elStaffComp.textContent = s.completed_jobs || 0;

            if (data.attendance) {
                const elStatus = document.getElementById('staff-att-status');
                const elBadge = document.getElementById('staff-att-badge');
                const elDetails = document.getElementById('staff-att-details');
                if (elStatus) {
                    elStatus.textContent = data.attendance.clocked_in ? 'Clocked In' : (data.attendance.status === 'absent' ? 'Absent' : 'Not Clocked In');
                }
                if (elBadge) {
                    if (data.attendance.clocked_in) {
                        elBadge.className = 'inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded border border-[#ddd] bg-slate-50 text-slate-700';
                        elBadge.textContent = 'Active Shift';
                    } else {
                        elBadge.className = 'inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded border border-[#ddd] bg-slate-50 text-slate-600';
                        elBadge.textContent = 'Off Shift';
                    }
                }
                if (elDetails && data.attendance.clock_in) {
                    elDetails.textContent = 'Clock In: ' + data.attendance.clock_in + (data.attendance.clock_out ? ' · Clock Out: ' + data.attendance.clock_out : ' (On Duty)');
                }
            }
        }
    }

    /* Table & Lists render */
    function updateTables(data) {
        /* Admin Recent Leads */
        const leadsList = document.getElementById('recent-leads-list');
        if (leadsList && data.recentLeads) {
            leadsList.innerHTML = data.recentLeads.length
                ? data.recentLeads.map((l) => {
                    return `
                    <div class="py-2.5 flex items-center justify-between gap-3">
                        <div class="truncate">
                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">${escapeHtml(l.customer_name)}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 truncate">${escapeHtml(l.phone || 'No phone provided')}</div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            ${statusBadge(l.status)}
                        </div>
                    </div>`;
                }).join('')
                : `<div class="py-8 text-center text-xs text-slate-400">No leads found.</div>`;
        }

        /* Upcoming Jobs */
        const jobsList = document.getElementById('upcoming-jobs-list');
        if (jobsList && data.upcomingJobs) {
            jobsList.innerHTML = data.upcomingJobs.length
                ? data.upcomingJobs.map(j => {
                    const date = j.scheduled_date
                        ? new Date(j.scheduled_date).toLocaleDateString('en-IN', {
                            day: '2-digit', month: 'short', year: 'numeric'
                          })
                        : 'Unscheduled';
                    const tech = j.technician ? ` · Tech: ${j.technician.name}` : '';
                    return `
                    <div class="py-2.5 flex items-center justify-between gap-3">
                        <div class="truncate">
                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">${escapeHtml(j.job_no)}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 truncate">${escapeHtml(j.quotation?.lead?.customer_name || 'Field Job')} &middot; ${date}${escapeHtml(tech)}</div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            ${statusBadge(j.status)}
                        </div>
                    </div>`;
                }).join('')
                : `<div class="py-8 text-center text-xs text-slate-400">No open jobs scheduled.</div>`;
        }

        /* Staff / Technician Tickets List */
        const ticketsList = document.getElementById('staff-tickets-list');
        if (ticketsList && data.recentTickets) {
            ticketsList.innerHTML = data.recentTickets.length
                ? data.recentTickets.map(t => {
                    return `
                    <div class="py-2.5 flex items-center justify-between gap-3">
                        <div class="truncate">
                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">${escapeHtml(t.ticket_no)} &middot; ${escapeHtml(t.title)}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 truncate">${escapeHtml(t.lead?.customer_name || 'Client')}</div>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <span style="border: 1px solid #ddd; border-radius: 4px; padding: 2px 8px; font-size: 11px; color: #475569; text-transform: capitalize; background: #fff;">${escapeHtml(t.priority)}</span>
                            ${statusBadge(t.status)}
                        </div>
                    </div>`;
                }).join('')
                : `<div class="py-8 text-center text-xs text-slate-400">No active support tickets.</div>`;
        }
    }

    /* Chart creation */
    function createCharts(data) {
        latestChartData = data;

        @if($isAdmin)
        /* 1. Revenue Simple Bar Chart */
        const ctxRev = document.getElementById('revenueChart');
        const revChartBox = document.getElementById('revenue-chart-container');
        const revTableBox = document.getElementById('revenue-table-container');
        const revTableBody = document.getElementById('revenue-table-body');

        if (data.revenueChart && data.revenueChart.data) {
            if (data.revenueChart.data.length < 3) {
                // Charts with fewer than 3 values are shown as a table
                if (revChartBox) revChartBox.classList.add('hidden');
                if (revTableBox) {
                    revTableBox.classList.remove('hidden');
                    if (revTableBody) {
                        revTableBody.innerHTML = data.revenueChart.labels.map((lbl, idx) => `
                            <tr>
                                <td class="py-2.5 px-3 font-medium">${escapeHtml(lbl)}</td>
                                <td class="py-2.5 px-3 text-right font-bold text-slate-900 dark:text-white">${money(data.revenueChart.data[idx] || 0)}</td>
                            </tr>
                        `).join('');
                    }
                }
            } else if (ctxRev) {
                if (revTableBox) revTableBox.classList.add('hidden');
                if (revChartBox) revChartBox.classList.remove('hidden');
                revenueChart = new Chart(ctxRev, {
                    type: 'bar',
                    data: {
                        labels: data.revenueChart.labels,
                        datasets: [{
                            label: 'Accepted Revenue',
                            data: data.revenueChart.data,
                            backgroundColor: '#2563eb',
                            borderRadius: 4,
                            maxBarThickness: 44,
                        }]
                    },
                    options: baseOptions({
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: baseFont, color: getTickColor() },
                                border: { display: false }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: getGridColor() },
                                ticks: {
                                    font: baseFont,
                                    color: getTickColor(),
                                    callback: (v) => money(v)
                                },
                                border: { display: false }
                            }
                        },
                        plugins: {
                            ...baseOptions().plugins,
                            tooltip: {
                                ...baseOptions().plugins.tooltip,
                                callbacks: { label: (ctx) => ' ' + money(ctx.parsed.y) }
                            }
                        }
                    })
                });
            }
        }

        /* 2. Quotation Pipeline Doughnut */
        const qColors = [C.slate, C.primary, C.emerald, C.rose, C.amber];
        const ctxQuote = document.getElementById('quotationChart');
        if (ctxQuote && data.quotationChart) {
            quotationChart = new Chart(ctxQuote, {
                type: 'doughnut',
                data: {
                    labels: data.quotationChart.labels,
                    datasets: [{
                        data: data.quotationChart.data,
                        backgroundColor: qColors,
                        borderWidth: 2,
                        borderColor: isDarkTheme() ? '#0f172a' : '#ffffff',
                        hoverOffset: 4,
                    }]
                },
                options: baseOptions({
                    cutout: '70%',
                    plugins: {
                        ...baseOptions().plugins,
                        tooltip: {
                            ...baseOptions().plugins.tooltip,
                            callbacks: { label: (ctx) => ` ${ctx.label}: ${ctx.parsed}` }
                        }
                    }
                })
            });
            buildLegend('quotation-legend', data.quotationChart.labels, qColors);
        }

        /* 3. Lead Status Doughnut */
        const lColors = [C.slate, C.sky, C.amber, C.emerald, C.rose];
        const ctxLead = document.getElementById('leadChart');
        if (ctxLead && data.leadChart) {
            leadChart = new Chart(ctxLead, {
                type: 'doughnut',
                data: {
                    labels: data.leadChart.labels,
                    datasets: [{
                        data: data.leadChart.data,
                        backgroundColor: lColors,
                        borderWidth: 2,
                        borderColor: isDarkTheme() ? '#0f172a' : '#ffffff',
                        hoverOffset: 4,
                    }]
                },
                options: baseOptions({
                    cutout: '70%',
                    plugins: {
                        ...baseOptions().plugins,
                        tooltip: {
                            ...baseOptions().plugins.tooltip,
                            callbacks: { label: (ctx) => ` ${ctx.label}: ${ctx.parsed}` }
                        }
                    }
                })
            });
            buildLegend('lead-legend', data.leadChart.labels, lColors);
        }
        @endif

        /* 4. Installation Job Status Bar (Rendered for both Admin & Staff) */
        const jColors = [C.slate, C.primary, C.purple, C.amber, C.emerald, C.rose];
        const ctxJob = document.getElementById('jobChart');
        if (ctxJob && data.jobChart) {
            jobChart = new Chart(ctxJob, {
                type: 'bar',
                data: {
                    labels: data.jobChart.labels,
                    datasets: [{
                        label: 'Jobs',
                        data: data.jobChart.data,
                        backgroundColor: '#2563eb',
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 32,
                    }]
                },
                options: baseOptions({
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: baseFont, color: getTickColor() },
                            border: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: getGridColor() },
                            ticks: {
                                precision: 0,
                                stepSize: 1,
                                font: baseFont,
                                color: getTickColor()
                            },
                            border: { display: false }
                        }
                    },
                    plugins: {
                        ...baseOptions().plugins,
                        tooltip: {
                            ...baseOptions().plugins.tooltip,
                            callbacks: { label: (ctx) => ` ${ctx.parsed.y} job(s)` }
                        }
                    }
                })
            });
        }
    }

    /* Chart updates */
    function updateCharts(data) {
        latestChartData = data;

        @if($isAdmin)
        if (revenueChart && data.revenueChart) {
            revenueChart.data.labels = data.revenueChart.labels;
            revenueChart.data.datasets[0].data = data.revenueChart.data;
            revenueChart.update('active');
        }

        if (quotationChart && data.quotationChart) {
            quotationChart.data.labels = data.quotationChart.labels;
            quotationChart.data.datasets[0].data = data.quotationChart.data;
            quotationChart.data.datasets[0].borderColor = isDarkTheme() ? '#0f172a' : '#ffffff';
            quotationChart.update('active');
            buildLegend('quotation-legend', data.quotationChart.labels,
                [C.slate, C.primary, C.emerald, C.rose, C.amber]);
        }

        if (leadChart && data.leadChart) {
            leadChart.data.labels = data.leadChart.labels;
            leadChart.data.datasets[0].data = data.leadChart.data;
            leadChart.data.datasets[0].borderColor = isDarkTheme() ? '#0f172a' : '#ffffff';
            leadChart.update('active');
            buildLegend('lead-legend', data.leadChart.labels,
                [C.slate, C.sky, C.amber, C.emerald, C.rose]);
        }
        @endif

        if (jobChart && data.jobChart) {
            jobChart.data.labels = data.jobChart.labels;
            jobChart.data.datasets[0].data = data.jobChart.data;
            jobChart.update('active');
        }
    }

    /* Dynamic Chart Theme Switching */
    window.addEventListener('theme-changed', () => {
        if (!latestChartData) return;

        const grid = getGridColor();
        const tick = getTickColor();
        const borderColor = isDarkTheme() ? '#0f172a' : '#ffffff';

        @if($isAdmin)
        if (revenueChart) {
            revenueChart.options.scales.x.ticks.color = tick;
            revenueChart.options.scales.y.ticks.color = tick;
            revenueChart.options.scales.y.grid.color  = grid;
            revenueChart.update();
        }

        if (quotationChart) {
            quotationChart.data.datasets[0].borderColor = borderColor;
            quotationChart.update();
            buildLegend('quotation-legend', latestChartData.quotationChart.labels,
                [C.slate, C.primary, C.emerald, C.rose, C.amber]);
        }

        if (leadChart) {
            leadChart.data.datasets[0].borderColor = borderColor;
            leadChart.update();
            buildLegend('lead-legend', latestChartData.leadChart.labels,
                [C.slate, C.sky, C.amber, C.emerald, C.rose]);
        }
        @endif

        if (jobChart) {
            jobChart.options.scales.x.ticks.color = tick;
            jobChart.options.scales.y.ticks.color = tick;
            jobChart.options.scales.y.grid.color  = grid;
            jobChart.update();
        }
    });

    /* Main refresh */
    const dataUrl = "{{ route('dashboard.data') }}";

    async function refreshDashboard(firstLoad = false) {
        const errorBox = document.getElementById('dashboard-error');
        try {
            const res = await fetch(dataUrl, { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            updateStats(data);
            updateTables(data);

            if (firstLoad) {
                createCharts(data);
            } else {
                updateCharts(data);
            }

            if (errorBox) {
                errorBox.classList.remove('visible');
                errorBox.textContent = '';
            }
            const updateElem = document.getElementById('last-updated');
            if (updateElem) {
                updateElem.textContent = 'Updated ' + new Date().toLocaleTimeString('en-IN', {
                    hour: '2-digit', minute: '2-digit', second: '2-digit'
                });
            }

        } catch (err) {
            console.error('Dashboard error:', err);
            if (errorBox) {
                errorBox.textContent = 'Dashboard could not refresh: ' + err.message;
                errorBox.classList.add('visible');
            }
            const updateElem = document.getElementById('last-updated');
            if (updateElem) {
                updateElem.textContent = 'Update failed';
            }
        }
    }

    refreshDashboard(true);
    setInterval(() => refreshDashboard(false), 30_000);
    </script>
</x-app-layout>