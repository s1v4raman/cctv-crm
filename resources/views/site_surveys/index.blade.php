<x-app-layout>
    @php
        $statuses = ['pending' => 'Pending', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
        $views = collect(['' => 'All Surveys'] + $statuses)->map(fn ($label, $value) => [
            'label' => $value === '' ? $label : $label . ' Surveys',
            'url' => route('site-surveys.index', array_filter(['status' => $value ?: null])),
            'active' => (string) request('status', '') === (string) $value,
        ])->values()->all();
        $filterCount = collect(['search', 'status'])->filter(fn ($f) => request()->filled($f))->count();
    @endphp

    <x-list-page key="surveys" :paginator="$surveys" :views="$views"
        :sorts="['date_desc' => 'Survey date, newest', 'date_asc' => 'Survey date, oldest', 'added' => 'Recently added']"
        :create-url="route('site-surveys.create')" create-label="New Survey"
        filter-title="Filter Surveys by" :filter-action="route('site-surveys.index')" :keep="['sort' => request('sort'), 'per_page' => request('per_page')]"
        :filter-count="$filterCount" :clear-url="route('site-surveys.index')"
        :columns="['date' => 'Date', 'address' => 'Address', 'technician' => 'Technician', 'cameras' => 'Cameras', 'status' => 'Status']"
        empty-text="No site surveys yet." filtered-text="No site surveys match these filters.">

        <x-slot name="filters">
            <div class="ld-sub">Customer, address, contact or technician</div>
            <input type="text" name="search" value="{{ request('search') }}" class="ld-input" aria-label="Customer, address, contact or technician">

            <div class="ld-sub">Status</div>
            <select name="status" class="ld-input" aria-label="Status">
                <option value="">Any status</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </x-slot>

        <x-slot name="head">
            <th style="min-width: 220px">Customer</th>
            <th x-show="cols.date" style="min-width: 120px">Date</th>
            <th x-show="cols.address" style="min-width: 260px">Address</th>
            <th x-show="cols.technician" style="min-width: 150px">Technician</th>
            <th x-show="cols.cameras" style="min-width: 90px; text-align: right">Cameras</th>
            <th x-show="cols.status" style="min-width: 110px">Status</th>
        </x-slot>

        @foreach($surveys as $survey)
            <tr>
                <td>
                    <a href="{{ route('site-surveys.show', $survey) }}" class="ld-name plain-link">{{ $survey->lead->customer_name ?? '—' }}</a>
                    @if($survey->lead?->company_name)<div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $survey->lead->company_name }}</div>@endif
                </td>
                <td x-show="cols.date" class="whitespace-nowrap">{{ $survey->survey_date?->format('d/m/Y') }}</td>
                <td x-show="cols.address">{{ $survey->site_address ?: ($survey->lead->site_address ?? '—') }}</td>
                <td x-show="cols.technician" class="whitespace-nowrap">{{ $survey->surveyedBy->name ?? '—' }}</td>
                <td x-show="cols.cameras" style="text-align: right">{{ $survey->camera_count_recommended ?? '—' }}</td>
                <td x-show="cols.status"><span class="{{ $survey->status === 'cancelled' ? 'text-rose-700 dark:text-rose-400' : '' }}">{{ $statuses[$survey->status] ?? ucfirst($survey->status) }}</span></td>
                <td>
                    <div class="ld-row-actions">
                        <a href="{{ route('site-surveys.edit', $survey) }}" class="plain-link" title="Edit" aria-label="Edit survey for {{ $survey->lead->customer_name ?? 'customer' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.2 5.2l3.6 3.6M4 20l4.5-1 10.3-10.3a2.5 2.5 0 00-3.5-3.5L5 15.5 4 20z"/></svg>
                        </a>
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('site-surveys.destroy', $survey) }}" data-confirm="Delete this survey and all photos?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-cancel" title="Delete" aria-label="Delete survey">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-list-page>
</x-app-layout>
