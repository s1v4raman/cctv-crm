<x-app-layout>
    @php
        $statuses = ['pending' => 'Pending', 'scheduled' => 'Scheduled', 'assigned' => 'Assigned', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
        $views = collect(['' => 'All Jobs'] + $statuses)->map(fn ($label, $value) => [
            'label' => $value === '' ? $label : $label . ' Jobs',
            'url' => route('jobs.index', array_filter(['status' => $value ?: null])),
            'active' => (string) request('status', '') === (string) $value,
        ])->values()->all();
        $filterCount = collect(['search', 'status', 'technician_id'])->filter(fn ($f) => request()->filled($f))->count();
    @endphp

    <x-list-page key="jobs" :paginator="$jobs" :views="$views"
        :sorts="['date_desc' => 'Newest first', 'date_asc' => 'Oldest first', 'scheduled_asc' => 'Scheduled, soonest', 'scheduled_desc' => 'Scheduled, latest', 'job_no' => 'Job number']"
        :create-url="auth()->user()->isOffice() ? route('jobs.create-general') : null" create-label="New Job"
        filter-title="Filter Jobs by" :filter-action="route('jobs.index')" :keep="['sort' => request('sort'), 'per_page' => request('per_page')]"
        :filter-count="$filterCount" :clear-url="route('jobs.index')"
        :columns="['scheduled' => 'Scheduled', 'technician' => 'Technician', 'status' => 'Status']"
        empty-text="No jobs yet." filtered-text="No jobs match these filters.">

        <x-slot name="filters">
            <div class="ld-sub">Job no, customer or technician</div>
            <input type="text" name="search" value="{{ request('search') }}" class="ld-input" aria-label="Job number, customer or technician">

            <div class="ld-sub">Status</div>
            <select name="status" class="ld-input" aria-label="Status">
                <option value="">Any status</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="ld-sub">Technician</div>
            <select name="technician_id" class="ld-input" aria-label="Technician">
                <option value="">Anyone</option>
                @foreach($technicians as $tech)
                    <option value="{{ $tech->id }}" @selected((string) request('technician_id') === (string) $tech->id)>{{ $tech->name }}</option>
                @endforeach
            </select>
        </x-slot>

        <x-slot name="head">
            <th style="min-width: 140px">Job No</th>
            <th style="min-width: 240px">Customer</th>
            <th x-show="cols.scheduled" style="min-width: 130px">Scheduled</th>
            <th x-show="cols.technician" style="min-width: 160px">Technician</th>
            <th x-show="cols.status" style="min-width: 120px">Status</th>
        </x-slot>

        @foreach($jobs as $job)
            <tr>
                <x-list-pick :id="$job->id" :label="$job->job_no" />
                <td class="whitespace-nowrap"><a href="{{ route('jobs.show', $job) }}" class="ld-name plain-link">{{ $job->job_no }}</a></td>
                <td>
                    <div>{{ $job->quotation?->lead?->customer_name ?? '—' }}</div>
                    @if($job->quotation?->lead?->site_address)
                        <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $job->quotation->lead->site_address }}</div>
                    @endif
                </td>
                <td x-show="cols.scheduled" class="whitespace-nowrap">{{ $job->scheduled_date?->format('d/m/Y') ?? 'Not scheduled' }}</td>
                <td x-show="cols.technician" class="whitespace-nowrap">{{ $job->assignedTechnician?->name ?? '—' }}</td>
                <td x-show="cols.status">
                    <span class="{{ $job->status === 'cancelled' ? 'text-rose-700 dark:text-rose-400' : '' }}">{{ $statuses[$job->status] ?? ucfirst(str_replace('_', ' ', $job->status)) }}</span>
                </td>
                <td>
                    <div class="ld-row-actions">
                        <a href="{{ route('jobs.show', $job) }}" class="plain-link" title="Open" aria-label="Open {{ $job->job_no }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5h5v5M10 14L19 5M19 14v5H5V5h5"/></svg>
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-list-page>
</x-app-layout>
