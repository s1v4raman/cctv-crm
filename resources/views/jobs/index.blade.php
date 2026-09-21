<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Installation Jobs</h2>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-purple-500/10 text-purple-600 dark:text-purple-400 px-2 py-0.5 rounded border border-purple-200 dark:border-purple-500/30">Field Deployment</span>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">All CCTV camera deployment, cabling, and technician field work orders</p>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('jobs.create-general') }}" class="btn-amber">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                New Job
            </a>
            @endif
        </div>
    </x-slot>

    <style>
        /* .pg-wrap uses global app.css */
        /* .pg-inner uses global app.css */

        .pg-card {
            background:#0f172a;
            border-radius:1rem;
            border:1px solid rgba(255, 255, 255, 0.08);
            box-shadow:0 10px 25px -5px rgba(0, 0, 0, 0.5);
            overflow:hidden;
        }

        /* Filter toolbar */
        .filter-bar {
            display:flex;
            align-items:center;
            gap:.75rem;
            padding:1rem 1.25rem;
            border-bottom:1px solid rgba(255, 255, 255, 0.08);
            background:#0b1120;
            flex-wrap:wrap;
        }
        .filter-bar input, .filter-bar select {
            border:1px solid #334155;
            border-radius:.6rem;
            padding:.5rem .9rem;
            font-size:.82rem;
            color:#ffffff;
            outline:none;
            transition:border-color .15s;
        }
        .filter-bar input:focus, .filter-bar select:focus { border-color:#f59e0b; }
        .filter-bar input { min-width:220px; }

        /* Table */
        .jobs-table { width:100%; border-collapse:collapse; }
        .jobs-table thead { background:#0b1120; }
        .jobs-table thead th {
            padding:.75rem 1.25rem;
            font-size:.68rem;
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:.06em;
            color:#94a3b8;
            text-align:left;
            white-space:nowrap;
            border-bottom:1px solid rgba(255, 255, 255, 0.05);
        }
        .jobs-table tbody tr {
            border-bottom:1px solid rgba(255, 255, 255, 0.05);
            transition:background .12s;
        }
        .jobs-table tbody tr:last-child { border-bottom:none; }
        .jobs-table tbody tr:hover { background:#1e293b; }
        .jobs-table tbody tr:hover { background:#f8fafc; }
        html.dark .jobs-table tbody tr:hover { background:#1e293b; }
        .jobs-table tbody td { padding:.9rem 1.25rem; vertical-align:middle; }

        .cell-name { font-size:.85rem; font-weight:700; color:#0f172a; }
        html.dark .cell-name { color:#ffffff; }
        .cell-name a { color:#f59e0b; text-decoration:none; transition:color .15s; }
        .cell-name a:hover { color:#fbbf24; }
        .cell-sub  { font-size:.72rem; color:#64748b; margin-top:.1rem; }
        html.dark .cell-sub { color:#94a3b8; }
        .cell-text { font-size:.82rem; color:#475569; }
        html.dark .cell-text { color:#cbd5e1; }

        /* Status badges */
        .badge {
            display:inline-flex; align-items:center;
            padding:.2rem .65rem; border-radius:999px;
            font-size:.68rem; font-weight:800;
            text-transform:uppercase; letter-spacing:.04em;
        }
        .badge-pending     { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
        .badge-scheduled   { background:#eff6ff; color:#2563eb; border:1px solid #dbeafe; }
        .badge-assigned    { background:#faf5ff; color:#7e22ce; border:1px solid #f3e8ff; }
        .badge-in_progress { background:#fffbeb; color:#d97706; border:1px solid #fde68a; }
        .badge-completed   { background:#ecfdf5; color:#059669; border:1px solid #d1fae5; }
        .badge-cancelled   { background:#fef2f2; color:#dc2626; border:1px solid #fee2e2; }

        html.dark .badge-pending     { background:rgba(148, 163, 184, 0.15); color:#cbd5e1; border:1px solid rgba(148, 163, 184, 0.3); }
        html.dark .badge-scheduled   { background:rgba(59, 130, 246, 0.15); color:#60a5fa; border:1px solid rgba(59, 130, 246, 0.3); }
        html.dark .badge-assigned    { background:rgba(168, 85, 247, 0.15); color:#c084fc; border:1px solid rgba(168, 85, 247, 0.3); }
        html.dark .badge-in_progress { background:rgba(245, 158, 11, 0.15); color:#fbbf24; border:1px solid rgba(245, 158, 11, 0.3); }
        html.dark .badge-completed   { background:rgba(16, 185, 129, 0.15); color:#34d399; border:1px solid rgba(16, 185, 129, 0.3); }
        html.dark .badge-cancelled   { background:rgba(244, 63, 94, 0.15); color:#fb7185; border:1px solid rgba(244, 63, 94, 0.3); }

        /* Technician tag */
        .tech-tag {
            display:inline-block;
            padding:.2rem .55rem; border-radius:.4rem;
            font-size:.7rem; font-weight:700;
            background:#f1f5f9; color:#475569;
            border:1px solid #e2e8f0;
        }
        html.dark .tech-tag {
            background:#1e293b; color:#94a3b8;
            border:1px solid rgba(255, 255, 255, 0.05);
        }

        /* Date */
        .date-chip {
            display:inline-flex; align-items:center; gap:.3rem;
            font-size:.75rem; color:#64748b; font-weight:600;
        }
        html.dark .date-chip { color:#94a3b8; }

        /* Pagination */
        .pg-links { padding:1rem 1.25rem; border-top:1px solid #e2e8f0; }
        html.dark .pg-links { border-top:1px solid rgba(255, 255, 255, 0.08); }

        /* Empty */
        .empty-state { text-align:center; padding:3rem 1rem; color:#64748b; font-size:.85rem; }
        html.dark .empty-state { color:#94a3b8; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if (session('status'))
                <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            <div class="pg-card">

                <form method="GET" action="{{ route('jobs.index') }}" class="filter-bar flex items-center gap-2 flex-wrap">
                    <div class="relative flex-1 min-w-[220px]">
                        <input type="text" name="search" id="job-search" value="{{ request('search') }}" placeholder="Search job no, customer, tech…" oninput="filterJobs()" class="w-full">
                    </div>
                    <select name="status" id="job-status-filter" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
                        <option value="assigned" @selected(request('status') === 'assigned')>Assigned</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                        <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                    <button type="submit" class="px-3 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-lg transition shadow">
                        Search
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('jobs.index') }}" class="px-2.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-lg transition">
                            Clear
                        </a>
                    @endif
                    <span id="job-count" style="margin-left:auto;font-size:.75rem;color:#94a3b8;"></span>
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

                                    <td>
                                        <div class="cell-name">
                                            {{ $job->quotation->lead->customer_name }}
                                        </div>
                                        @if($job->quotation->lead->site_address)
                                            <div class="cell-sub">{{ Str::limit($job->quotation->lead->site_address, 40) }}</div>
                                        @endif
                                    </td>

                                    <td>
                                        @if($job->scheduled_date)
                                            <span class="date-chip">
                                                <svg style="width:.85rem;height:.85rem;color:#94a3b8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ $job->scheduled_date->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="cell-text">Unscheduled</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($job->assignedTechnician)
                                            <span class="tech-tag">{{ $job->assignedTechnician->name }}</span>
                                        @else
                                            <span class="cell-text">—</span>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge badge-{{ $job->status }}">
                                            {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <svg style="width:2.5rem;height:2.5rem;color:#cbd5e1;margin:0 auto .75rem" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
                                            </svg>
                                            No installation jobs yet.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($jobs->hasPages())
                    <div class="pg-links">{{ $jobs->links() }}</div>
                @endif
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