<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Job Completion Reports</h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('jobs.index') }}"
                   class="px-3 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 text-sm font-medium rounded hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                   style="border-radius: 4px;">
                    Installation Jobs
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 1024px) {
            .stat-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        .stat-card {
            background: #ffffff;
            border-radius: 4px;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
        }
        html.dark .stat-card {
            background: #0f172a;
            border-color: #1e293b;
        }
        .stat-label { font-size: 14px; font-weight: 500; color: #64748b; margin-bottom: 0.25rem; }
        html.dark .stat-label { color: #94a3b8; }
        .stat-value { font-size: 1.6rem; font-weight: 700; color: #0f172a; line-height: 1; }
        html.dark .stat-value { color: #ffffff; }

        .pg-card {
            background: #ffffff;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        html.dark .pg-card {
            background: #0f172a;
            border-color: #1e293b;
        }

        .filter-bar {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; flex-wrap: wrap;
            background: #ffffff;
        }
        html.dark .filter-bar {
            background: #0b1120;
            border-color: #1e293b;
        }
        .filter-input {
            height: 38px;
            padding: 0 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 14px;
            background: #ffffff;
            color: #1e293b;
        }
        html.dark .filter-input {
            background: #060913;
            border-color: #334155;
            color: #f1f5f9;
        }
    </style>

    <div class="py-6 bg-slate-50 dark:bg-[#060913] min-h-screen">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-5 p-4 rounded bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-sm font-medium" style="border-radius: 4px;">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Stats Cards --}}
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Signed JCRs</div>
                    <div class="stat-value">{{ $reports->total() }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Avg Customer Rating</div>
                    <div class="stat-value text-amber-600 dark:text-amber-400">4.9 ★</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">QA Pass Rate</div>
                    <div class="stat-value text-emerald-600 dark:text-emerald-400">100%</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Active Technicians</div>
                    <div class="stat-value text-purple-600 dark:text-purple-400">{{ $technicians->count() }}</div>
                </div>
            </div>

            {{-- Table & Filters Card --}}
            <div class="pg-card">
                <form method="GET" action="{{ route('jcr.index') }}" class="filter-bar">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search JCR No, Client, Phone..." class="filter-input flex-1 min-w-[200px]">
                    
                    <select name="type" class="filter-input">
                        <option value="">All Types</option>
                        <option value="installation" @selected(request('type') === 'installation')>New Installation</option>
                        <option value="service" @selected(request('type') === 'service')>Service Ticket</option>
                        <option value="amc" @selected(request('type') === 'amc')>AMC Servicing</option>
                    </select>

                    <select name="technician_id" class="filter-input">
                        <option value="">All Technicians</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" @selected(request('technician_id') == $tech->id)>{{ $tech->name }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-4 text-white text-sm font-semibold rounded bg-blue-600 hover:bg-blue-700 transition" style="height: 38px; border-radius: 4px;">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'type', 'technician_id']))
                        <a href="{{ route('jcr.index') }}" class="px-3 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center" style="height: 38px; border-radius: 4px;">
                            Reset
                        </a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Report No</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Customer & Site Location</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Job Type & Ref</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Technician</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Rating & QA</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Date Signed</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                            @forelse($reports as $report)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <a href="{{ route('jcr.show', $report) }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ $report->report_no }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-900 dark:text-slate-200">{{ $report->lead->customer_name }}</div>
                                        <div class="text-xs text-slate-500 truncate max-w-xs">{{ $report->lead->site_address ?? $report->lead->phone }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @php
                                            $typeColor = match($report->job_type) {
                                                'service' => 'text-rose-600 dark:text-rose-400',
                                                'amc' => 'text-teal-600 dark:text-teal-400',
                                                default => 'text-blue-600 dark:text-blue-400',
                                            };
                                        @endphp
                                        <div class="font-bold {{ $typeColor }}">
                                            {{ $report->job_type_label }}
                                        </div>
                                        <div class="text-xs text-slate-500 font-mono">{{ $report->reference_no }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-900 dark:text-slate-200">
                                        {{ $report->technician->name }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-amber-500 font-bold text-xs">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span>{{ $i <= $report->customer_rating ? '★' : '☆' }}</span>
                                            @endfor
                                        </div>
                                        <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 mt-0.5">
                                            {{ $report->passed_checklist_count }}/8 QA Passed
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                        <div class="font-medium text-slate-900 dark:text-slate-200">{{ $report->completion_date->format('d M Y') }}</div>
                                        <div class="text-xs text-slate-500">{{ $report->completion_date->format('h:i A') }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right font-medium text-sm">
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            <a href="{{ route('jcr.show', $report) }}" class="text-blue-600 dark:text-blue-400 hover:underline">View</a>
                                            <span class="text-slate-300 dark:text-slate-600">|</span>
                                            <a href="{{ route('jcr.download-pdf', $report) }}" class="text-blue-600 dark:text-blue-400 hover:underline">PDF</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                        No Job Completion Reports found matching your filter criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $reports->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
