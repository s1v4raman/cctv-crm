<x-app-layout>
    @php
        $types = ['installation' => 'Installation', 'service' => 'Service Ticket', 'amc' => 'AMC Maintenance'];
        $views = collect(['' => 'All Job Reports'] + $types)->map(fn ($label, $value) => [
            'label' => $value === '' ? $label : $label . ' Reports',
            'url' => route('jcr.index', array_filter(['type' => $value ?: null])),
            'active' => (string) request('type', '') === (string) $value,
        ])->values()->all();
        $filterCount = collect(['search', 'type', 'technician_id'])->filter(fn ($f) => request()->filled($f))->count();
    @endphp

    <x-list-page key="jcr" :paginator="$reports" :views="$views"
        :sorts="['date_desc' => 'Completed, newest', 'date_asc' => 'Completed, oldest', 'report_no' => 'Report number']"
        filter-title="Filter Reports by" :filter-action="route('jcr.index')" :keep="['sort' => request('sort'), 'per_page' => request('per_page')]"
        :filter-count="$filterCount" :clear-url="route('jcr.index')"
        :columns="['job' => 'Job', 'technician' => 'Technician', 'checklist' => 'Checklist', 'completed' => 'Completed']"
        empty-text="No job completion reports yet." filtered-text="No job completion reports match these filters.">

        <x-slot name="actions">
            <a href="{{ route('jobs.index') }}" class="ld-outline plain-link">Installation Jobs</a>
        </x-slot>

        <x-slot name="filters">
            <div class="ld-sub">Report no, signer or customer</div>
            <input type="text" name="search" value="{{ request('search') }}" class="ld-input" aria-label="Report number, signer or customer">

            <div class="ld-sub">Job type</div>
            <select name="type" class="ld-input" aria-label="Job type">
                <option value="">Any type</option>
                @foreach($types as $value => $label)
                    <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
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
            <th style="min-width: 150px">Report No</th>
            <th style="min-width: 230px">Customer</th>
            <th x-show="cols.job" style="min-width: 170px">Job</th>
            <th x-show="cols.technician" style="min-width: 150px">Technician</th>
            <th x-show="cols.checklist" style="min-width: 110px">Checklist</th>
            <th x-show="cols.completed" style="min-width: 130px">Completed</th>
        </x-slot>

        @foreach($reports as $report)
            <tr>
                <x-list-pick :id="$report->id" :label="$report->report_no" />
                <td class="whitespace-nowrap"><a href="{{ route('jcr.show', $report) }}" class="ld-name plain-link">{{ $report->report_no }}</a></td>
                <td>
                    @if($report->lead)
                        <a href="{{ route('leads.show', $report->lead) }}" class="ld-name plain-link">{{ $report->lead->customer_name }}</a>
                        <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $report->lead->site_address ?? $report->lead->phone }}</div>
                    @else
                        —
                    @endif
                </td>
                <td x-show="cols.job">
                    <div>{{ $report->job_type_label }}</div>
                    <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $report->reference_no }}</div>
                </td>
                <td x-show="cols.technician" class="whitespace-nowrap">{{ $report->technician->name ?? '—' }}</td>
                <td x-show="cols.checklist" class="whitespace-nowrap"><span class="{{ $report->passed_checklist_count < 8 ? 'text-amber-700 dark:text-amber-400' : '' }}">{{ $report->passed_checklist_count }} of 8 passed</span></td>
                <td x-show="cols.completed" class="whitespace-nowrap">
                    <div>{{ $report->completion_date?->format('d/m/Y') }}</div>
                    <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $report->completion_date?->format('h:i A') }}</div>
                </td>
                <td>
                    <div class="ld-row-actions">
                        <a href="{{ route('jcr.download-pdf', $report) }}" class="plain-link" title="Download PDF" aria-label="Download PDF of {{ $report->report_no }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v11m0 0l-4-4m4 4l4-4M5 19h14"/></svg>
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-list-page>
</x-app-layout>
