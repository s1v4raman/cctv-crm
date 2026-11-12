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

        <x-slot name="mobileCards">
            @foreach($surveys as $survey)
                <div class="p-3.5 space-y-2.5" :class="selected.includes({{ $survey->id }}) && 'bg-blue-50/40 dark:bg-blue-950/20'">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-start gap-2.5 min-w-0">
                            <input type="checkbox" value="{{ $survey->id }}" x-model.number="selected" aria-label="Select survey for {{ $survey->lead?->customer_name ?? $survey->id }}" class="mt-1 rounded">
                            <div class="min-w-0">
                                <a href="{{ route('site-surveys.show', $survey) }}" class="ld-name plain-link text-sm font-bold block truncate">{{ $survey->lead->customer_name ?? '—' }}</a>
                                @if($survey->lead?->company_name)
                                    <div class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $survey->lead->company_name }}</div>
                                @endif
                            </div>
                        </div>
                        <span class="shrink-0 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $survey->status === 'cancelled' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700' }}">
                            {{ $statuses[$survey->status] ?? ucfirst($survey->status) }}
                        </span>
                    </div>

                    <div class="text-xs text-slate-600 dark:text-slate-300 space-y-1 pl-6">
                        @if($survey->site_address ?: $survey->lead?->site_address)
                            <div class="line-clamp-2">{{ $survey->site_address ?: $survey->lead->site_address }}</div>
                        @endif
                        <div class="flex items-center flex-wrap gap-x-3 gap-y-1 text-slate-500 dark:text-slate-400">
                            @if($survey->survey_date)<span>Date: {{ $survey->survey_date->format('d/m/Y') }}</span>@endif
                            @if($survey->surveyedBy?->name)<span>Tech: {{ $survey->surveyedBy->name }}</span>@endif
                            @if($survey->camera_count_recommended !== null)<span>Cameras: {{ $survey->camera_count_recommended }}</span>@endif
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-1 pl-6">
                        <a href="{{ route('site-surveys.show', $survey) }}" class="ld-outline plain-link text-xs" style="min-height: 32px !important; padding: 4px 10px !important;">View</a>
                        <a href="{{ route('site-surveys.edit', $survey) }}" class="ld-outline plain-link text-xs" style="min-height: 32px !important; padding: 4px 10px !important;">Edit</a>
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('site-surveys.destroy', $survey) }}" data-confirm="Delete this survey and all photos?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-cancel text-xs" title="Delete" aria-label="Delete survey">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
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
                <x-list-pick :id="$survey->id" :label="'survey for ' . ($survey->lead?->customer_name ?? $survey->id)" />
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
