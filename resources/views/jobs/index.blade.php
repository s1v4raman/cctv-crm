<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Installation Jobs</h2>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('jobs.create-general') }}" class="btn-primary">
                New Job
            </a>
            @endif
        </div>
    </x-slot>

    <style>
        .pg-card {
            background:#ffffff;
            border-radius:8px;
            border:1px solid var(--card-border);
            overflow:hidden;
        }
        html.dark .pg-card {
            background:#0f172a;
            border-color:var(--card-border);
        }

        /* Filter toolbar */
        .filter-bar {
            display:flex;
            align-items:center;
            gap:.75rem;
            padding:.75rem 1rem;
            border-bottom:1px solid var(--card-border);
            background:#ffffff;
            flex-wrap:wrap;
        }
        html.dark .filter-bar {
            background:#0b1120;
            border-color:var(--card-border);
        }
        .filter-bar input, .filter-bar select {
            border:1px solid var(--card-border);
            border-radius:6px;
            height:36px;
            padding:0 .75rem;
            font-size:.85rem;
            color: var(--text-main);
            background:#ffffff;
            outline:none;
        }
        html.dark .filter-bar input, html.dark .filter-bar select {
            background:#0b1120;
            border-color:#475569;
            color:#ffffff;
        }
        .filter-bar input:focus, .filter-bar select:focus { border-color:#2563eb; }
        .filter-bar input { min-width:220px; }

        /* Table */
        .jobs-table { width:100%; border-collapse:collapse; border:none !important; }
        .jobs-table thead { background:#f8fafc; }
        html.dark .jobs-table thead { background:#0b1120; }
        .jobs-table thead th {
            padding:.75rem 1rem;
            font-size:.8rem;
            font-weight:600;
            color:#475569;
            text-align:left;
            white-space:nowrap;
            border-top:none !important;
            border-bottom:1px solid var(--card-border) !important;
            border-right:1px solid var(--card-border) !important;
        }
        html.dark .jobs-table thead th {
            color: var(--text-muted);
            border-top:none !important;
            border-bottom:1px solid var(--card-border) !important;
            border-right:1px solid var(--card-border) !important;
        }
        .jobs-table tbody tr {
            border-bottom:none !important;
            background:#ffffff;
        }
        html.dark .jobs-table tbody tr {
            border-bottom:none !important;
            background:#0f172a;
        }
        .jobs-table tbody tr:hover { background:#f8fafc; }
        html.dark .jobs-table tbody tr:hover { background:var(--card-border); }
        .jobs-table tbody td {
            padding:.75rem 1rem;
            vertical-align:middle;
            font-size:.85rem;
            border-bottom:1px solid var(--card-border) !important;
            border-right:1px solid var(--card-border) !important;
        }
        html.dark .jobs-table tbody td {
            border-bottom:1px solid var(--card-border) !important;
            border-right:1px solid var(--card-border) !important;
        }
        .jobs-table th:first-child,
        .jobs-table td:first-child {
            border-left:none !important;
        }
        .jobs-table th:last-child,
        .jobs-table td:last-child {
            border-right:none !important;
        }
        .jobs-table tbody tr:last-child td {
            border-bottom:none !important;
        }

        .cell-name { font-size:.85rem; font-weight:600; color: var(--text-main); }
        html.dark .cell-name { color:#ffffff; }
        .cell-name a { color:#2563eb; text-decoration:none; }
        .cell-name a:hover { text-decoration:underline; }
        .cell-sub  { font-size:.75rem; color:#64748b; margin-top:.15rem; }
        html.dark .cell-sub { color: var(--text-muted); }
        .cell-text { font-size:.85rem; color:#475569; }
        html.dark .cell-text { color: var(--text-muted); }

        /* Status: Clean 1px single border pill matching Image 4 */
        .badge {
            display:inline-flex;
            align-items:center;
            font-size:.75rem;
            font-weight:700;
            border-radius:4px;
            padding:2px 8px !important;
            border:1px solid var(--card-border) !important;
            background:#ffffff;
        }
        html.dark .badge {
            border-color:#475569 !important;
            background:#0f172a;
        }
        .badge-pending     { color:#64748b !important; }
        .badge-scheduled   { color:#2563eb !important; }
        .badge-assigned    { color:#7e22ce !important; }
        .badge-in_progress { color:#d97706 !important; }
        .badge-completed   { color:#16a34a !important; }
        .badge-cancelled   { color:#dc2626 !important; }

        /* Technician text tag */
        .tech-tag {
            display:inline-flex;
            align-items:center;
            font-size:.75rem;
            font-weight:600;
            border-radius:4px;
            padding:2px 8px;
            border:1px solid var(--card-border);
            background:#f8fafc;
            color: var(--text-main);
        }
        html.dark .tech-tag {
            border-color:#475569;
            background:var(--card-border);
            color: var(--text-muted);
        }

        /* Date */
        .date-chip {
            font-size:.85rem;
            color:#475569;
            white-space:nowrap;
        }
        html.dark .date-chip { color: var(--text-muted); }

        /* Pagination */
        .pg-links { padding:.75rem 1rem; border-top:1px solid #e2e8f0; }
        html.dark .pg-links { border-top-color: var(--text-main); }

        /* Empty */
        .empty-state { text-align:center; padding:3rem 1rem; color:#64748b; font-size:.85rem; }
        html.dark .empty-state { color: var(--text-muted); }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if (session('status'))
                <div class="mb-4 p-3 rounded bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            <div class="pg-card">

                <form method="GET" action="{{ route('jobs.index') }}" class="filter-bar flex items-center gap-2.5 flex-wrap p-2.5 text-xs">
                    <div class="relative w-72 min-w-[220px] flex items-center shrink-0">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" id="job-search" value="{{ request('search') }}" 
                               placeholder="Search job no, customer, tech…" 
                               oninput="filterJobs()" 
                               class="w-full text-xs" 
                               style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important; padding-left: 36px !important;">
                    </div>
                    <select name="status" id="job-status-filter" onchange="this.form.submit()" 
                            class="text-xs shrink-0" 
                            style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important; min-width: 140px;">
                        <option value="">All Statuses</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
                        <option value="assigned" @selected(request('status') === 'assigned')>Assigned</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                        <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                    <button type="submit" 
                            style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important;" 
                            class="px-4 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition shrink-0 inline-flex items-center justify-center shadow-xs cursor-pointer">
                        Filter
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('jobs.index') }}" 
                           style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important;" 
                           class="px-3.5 inline-flex items-center rounded-md border border-slate-300 dark:border-slate-700 bg-white hover:bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold transition shrink-0">
                            Clear
                        </a>
                    @endif
                    <span id="job-count" style="margin-left:auto;font-size:.75rem;color:#64748b;"></span>
                </form>

                <div style="overflow-x:auto">
                    <table class="jobs-table" id="jobs-table">
                        <thead>
                            <tr>
                                <th>Job No</th>
                                <th>Customer</th>
                                <th>Scheduled</th>
                                <th>Technician</th>
                                <th>Status</th>
                                <th class="text-right" style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($jobs as $job)
                                <tr class="job-row"
                                    data-search="{{ strtolower($job->job_no . ' ' . $job->quotation->lead->customer_name) }}"
                                    data-status="{{ $job->status }}">

                                    <td>
                                        <div class="cell-name">
                                            <a href="{{ route('jobs.show', $job) }}">{{ $job->job_no }}</a>
                                        </div>
                                    </td>

                                    <td class="col-name" style="min-width: 200px;">
                                        <div class="cell-name">
                                            {{ $job->quotation->lead->customer_name }}
                                        </div>
                                        @if($job->quotation->lead->site_address)
                                            <div class="cell-sub">{{ $job->quotation->lead->site_address }}</div>
                                        @endif
                                    </td>

                                    <td class="col-date whitespace-nowrap">
                                        @if($job->scheduled_date)
                                            <span class="date-chip">
                                                {{ $job->scheduled_date->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="cell-text">Unscheduled</span>
                                        @endif
                                    </td>

                                    <td class="col-tech whitespace-nowrap">
                                        @if($job->assignedTechnician)
                                            <span class="tech-tag">{{ $job->assignedTechnician->name }}</span>
                                        @else
                                            <span class="cell-text">—</span>
                                        @endif
                                    </td>

                                    <td>
                                        @php
                                            $jobStatusColors = [
                                                'completed'   => '#16a34a',
                                                'in_progress' => '#d97706',
                                                'scheduled'   => '#d97706',
                                                'assigned'    => '#d97706',
                                                'pending'     => '#d97706',
                                                'cancelled'   => '#dc2626',
                                            ];
                                            $sColor = $jobStatusColors[strtolower($job->status)] ?? '#d97706';
                                        @endphp
                                        <span class="status-text" style="color: {{ $sColor }}; font-weight: bold;">
                                            {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap text-right text-sm table-actions" style="text-align:right;">
                                        <a href="{{ route('jobs.show', $job) }}" class="action-link">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            No jobs yet
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Total count shown below the table --}}
                <div class="px-4 py-2.5 border-t border-[#ddd] dark:border-slate-800 bg-[#fbfbfb] dark:bg-slate-900/60 flex items-center justify-between text-xs text-slate-500">
                    <span class="font-medium">Total: {{ $jobs->total() }}</span>
                    @if($jobs->hasPages())
                        <div class="text-xs">
                            {{ $jobs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
    function filterJobs() {
        const q      = document.getElementById('job-search').value.toLowerCase();
        const status = document.getElementById('job-status-filter').value;
        const rows   = document.querySelectorAll('.job-row');
        let visible  = 0;

        rows.forEach(row => {
            const searchMatch = row.dataset.search.includes(q);
            const statusMatch = !status || row.dataset.status === status;
            const show        = searchMatch && statusMatch;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        const total = rows.length;
        document.getElementById('job-count').textContent =
            visible === total ? `${total} job${total !== 1 ? 's' : ''}`
                              : `${visible} of ${total} jobs`;
    }
    filterJobs();
    </script>
</x-app-layout>