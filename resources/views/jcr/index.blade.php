<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Job Completion Reports</h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('jobs.index') }}" class="btn-secondary">
                    Installation Jobs
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-[1200px] mx-auto space-y-6">
        @if (session('status'))
            <div class="p-3 bg-white dark:bg-slate-900 border border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded text-xs">
                {{ session('status') }}
            </div>
        @endif

        {{-- Stat Cards: Gray label and bold number only (5.5) --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="stat-card">
                <div class="stat-label">Total Reports</div>
                <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Installation Signoffs</div>
                <div class="stat-value">{{ $stats['installation'] ?? 0 }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Service Reports</div>
                <div class="stat-value">{{ $stats['service'] ?? 0 }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Average Rating</div>
                <div class="stat-value">{{ number_format($stats['avg_rating'] ?? 5, 1) }} / 5</div>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="crm-section">
            <form method="GET" action="{{ route('jcr.index') }}" class="flex items-center gap-3 flex-wrap">
                <input aria-label="Search" type="text" name="search" value="{{ request('search') }}" placeholder="e.g. JCR-2026-001" class="crm-input" style="width: 240px;">

                <select aria-label="Type" name="type" class="crm-select" style="width: 180px;">
                    <option value="">All Job Types</option>
                    <option value="installation" @selected(request('type') === 'installation')>Installation</option>
                    <option value="service" @selected(request('type') === 'service')>Service Ticket</option>
                    <option value="amc" @selected(request('type') === 'amc')>AMC Maintenance</option>
                </select>

                <select aria-label="Technician" name="technician_id" class="crm-select" style="width: 180px;">
                    <option value="">All Technicians</option>
                    @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}" @selected(request('technician_id') == $tech->id)>{{ $tech->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn-primary">
                    Search
                </button>
                @if(request()->anyFilled(['search', 'type', 'technician_id']))
                    <a href="{{ route('jcr.index') }}" class="btn-secondary">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Table Section (5.3) --}}
        <div class="crm-section p-0 overflow-x-auto">
            <table class="crm-table crm-responsive">
                <thead>
                    <tr>
                        <th class="nowrap">Report No</th>
                        <th class="col-name">Customer & Site Location</th>
                        <th class="nowrap">Job Type & Ref</th>
                        <th class="nowrap">Technician</th>
                        <th class="nowrap">QA Passed</th>
                        <th class="nowrap">Date Signed</th>
                        <th class="nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                    <tr>
                        <td class="nowrap">
                            <a href="{{ route('jcr.show', $report) }}" class="action-link font-mono">
                                {{ $report->report_no }}
                            </a>
                        </td>
                        <td class="col-name">
                            <a href="{{ route('leads.show', $report->lead) }}" class="action-link font-bold">
                                {{ $report->lead->customer_name }}
                            </a>
                            <div class="text-xs text-slate-500">{{ $report->lead->site_address ?? $report->lead->phone }}</div>
                        </td>
                        <td class="nowrap">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $report->job_type_label }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $report->reference_no }}</div>
                        </td>
                        <td class="nowrap font-medium text-slate-900 dark:text-white">
                            {{ $report->technician->name }}
                        </td>
                        <td class="nowrap">
                            <span class="status-won">{{ $report->passed_checklist_count }}/8 Passed</span>
                        </td>
                        <td class="nowrap">
                            <div class="text-slate-900 dark:text-white">{{ $report->completion_date->format('d M Y') }}</div>
                            <div class="text-xs text-slate-500">{{ $report->completion_date->format('h:i A') }}</div>
                        </td>
                        <td class="nowrap">
                            <a href="{{ route('jcr.show', $report) }}" class="action-link">View</a>
                            <a href="{{ route('jcr.download-pdf', $report) }}" class="action-link">PDF</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-6 text-slate-500 text-sm">
                            No Job Completion Reports yet
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
            <div class="mt-4">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
