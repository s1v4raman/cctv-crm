<x-app-layout>
    @php
        $statuses = ['active' => 'Active', 'under_repair' => 'Under Repair', 'replaced' => 'Replaced', 'decommissioned' => 'Decommissioned'];
        $warranties = ['active' => 'Under warranty', 'expiring_soon' => 'Expiring in 30 days', 'expired' => 'Expired'];
        $views = collect(['' => 'All Equipment'] + $statuses)->map(fn ($label, $value) => [
            'label' => $value === '' ? $label : $label . ' Equipment',
            'url' => route('equipment.index', array_filter(['status' => $value ?: null])),
            'active' => (string) request('status', '') === (string) $value,
        ])->values()->all();
        $filterCount = collect(['search', 'status', 'warranty', 'lead_id'])->filter(fn ($f) => request()->filled($f) && request($f) !== 'all')->count();
        $warrantyCell = function (?string $state, $days, $expiry) {
            return match ($state) {
                'active'        => ['Active, ' . $days . ' days left', '', $expiry],
                'expiring_soon' => ['Expiring Soon (' . $days . ' days)', 'text-amber-700 dark:text-amber-400', $expiry],
                'expired'       => ['Expired', 'text-rose-700 dark:text-rose-400', $expiry],
                default         => ['—', '', null],
            };
        };
    @endphp

    <x-list-page key="equipment" :paginator="$equipments" :views="$views"
        :sorts="['date_desc' => 'Newest first', 'mfg_warranty_asc' => 'Maker warranty ends soonest', 'service_warranty_asc' => 'Service warranty ends soonest', 'serial' => 'Serial number']"
        :create-url="auth()->user()->isOffice() ? route('equipment.create') : null" create-label="Add Equipment"
        filter-title="Filter Equipment by" :filter-action="route('equipment.index')" :keep="['sort' => request('sort'), 'per_page' => request('per_page')]"
        :filter-count="$filterCount" :clear-url="route('equipment.index')"
        :columns="['equipment' => 'Equipment', 'customer' => 'Customer', 'mfg' => 'Maker warranty', 'service' => 'Service warranty', 'status' => 'Status']"
        empty-text="No equipment recorded yet." filtered-text="No equipment matches these filters.">

        <x-slot name="filters">
            <div class="ld-sub">Serial, MAC, name, location or customer</div>
            <input type="text" name="search" value="{{ request('search') }}" class="ld-input" aria-label="Serial, MAC, name, location or customer">

            <div class="ld-sub">Status</div>
            <select name="status" class="ld-input" aria-label="Status">
                <option value="">Any status</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="ld-sub">Warranty</div>
            <select name="warranty" class="ld-input" aria-label="Warranty">
                <option value="">Any warranty</option>
                @foreach($warranties as $value => $label)
                    <option value="{{ $value }}" @selected(request('warranty') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="ld-sub">Customer</div>
            <select name="lead_id" class="ld-input" aria-label="Customer">
                <option value="">Any customer</option>
                @foreach($leads as $lead)
                    <option value="{{ $lead->id }}" @selected((string) request('lead_id') === (string) $lead->id)>{{ $lead->customer_name }}</option>
                @endforeach
            </select>
        </x-slot>

        <x-slot name="head">
            <th style="min-width: 170px">Serial No</th>
            <th x-show="cols.equipment" style="min-width: 230px">Equipment</th>
            <th x-show="cols.customer" style="min-width: 220px">Customer</th>
            <th x-show="cols.mfg" style="min-width: 170px">Maker warranty</th>
            <th x-show="cols.service" style="min-width: 170px">Service warranty</th>
            <th x-show="cols.status" style="min-width: 130px">Status</th>
        </x-slot>

        <x-slot:mobileCards>
            @foreach($equipments as $item)
                @php
                    [$mfgText, $mfgClass, $mfgDate] = $warrantyCell($item->mfg_warranty_status, $item->mfg_days_remaining, $item->manufacturer_warranty_expiry);
                    [$svcText, $svcClass, $svcDate] = $warrantyCell($item->service_warranty_status, $item->service_days_remaining, $item->service_warranty_expiry);
                @endphp
                <div class="p-3.5 space-y-2.5" :class="selected.includes({{ $item->id }}) && 'bg-blue-50/40 dark:bg-blue-950/20'">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-start gap-2.5 min-w-0">
                            <input type="checkbox" value="{{ $item->id }}" x-model.number="selected" aria-label="Select {{ $item->serial_number ?: $item->equipment_name }}" class="mt-1 rounded">
                            <div class="min-w-0">
                                <a href="{{ route('equipment.show', $item) }}" class="ld-name plain-link text-sm font-bold block truncate">{{ $item->serial_number }}</a>
                                <div class="text-xs text-slate-600 dark:text-slate-300 font-medium truncate">{{ $item->equipment_name }}</div>
                            </div>
                        </div>
                        <span class="shrink-0 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                            {{ $statuses[$item->status] ?? ucfirst(str_replace('_', ' ', $item->status)) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-slate-600 dark:text-slate-300 pl-6">
                        <div class="col-span-2 truncate"><span class="text-slate-400">Customer:</span> {{ $item->lead?->customer_name ?? '—' }}</div>
                        <div class="truncate"><span class="text-slate-400">Maker:</span> <span class="{{ $mfgClass }}">{{ $mfgText }}</span></div>
                        <div class="truncate"><span class="text-slate-400">Service:</span> <span class="{{ $svcClass }}">{{ $svcText }}</span></div>
                    </div>

                    <div class="flex items-center justify-between gap-2 pl-6 pt-0.5">
                        <a href="{{ route('equipment.show', $item) }}" class="text-xs font-semibold hover:underline plain-link" style="color: var(--link-color)">View Device &rarr;</a>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('equipment.edit', $item) }}" class="text-xs font-medium text-slate-600 dark:text-slate-300 hover:underline plain-link">Edit</a>
                            @if(auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('equipment.destroy', $item) }}" data-confirm="Delete equipment {{ $item->serial_number }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-rose-600 dark:text-rose-400 hover:underline cursor-pointer">Delete</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </x-slot:mobileCards>

        @foreach($equipments as $item)
            @php
                [$mfgText, $mfgClass, $mfgDate] = $warrantyCell($item->mfg_warranty_status, $item->mfg_days_remaining, $item->manufacturer_warranty_expiry);
                [$svcText, $svcClass, $svcDate] = $warrantyCell($item->service_warranty_status, $item->service_days_remaining, $item->service_warranty_expiry);
            @endphp
            <tr>
                <x-list-pick :id="$item->id" :label="$item->serial_number ?: $item->equipment_name" />
                <td class="whitespace-nowrap">
                    <a href="{{ route('equipment.show', $item) }}" class="ld-name plain-link">{{ $item->serial_number }}</a>
                    @if($item->mac_address)<div class="text-xs mt-0.5" style="color: var(--ld-muted)">MAC {{ $item->mac_address }}</div>@endif
                </td>
                <td x-show="cols.equipment">
                    <div>{{ $item->equipment_name }}</div>
                    <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ trim(($item->product?->brand ?? '') . ' ' . ($item->product?->model_no ?? '')) }}@if($item->location_tag) · {{ $item->location_tag }}@endif</div>
                </td>
                <td x-show="cols.customer">
                    @if($item->lead)<a href="{{ route('leads.show', $item->lead) }}" class="ld-name plain-link">{{ $item->lead->customer_name }}</a>@endif
                    @if($item->installationJob)<div class="text-xs mt-0.5" style="color: var(--ld-muted)">Job {{ $item->installationJob->job_no }}</div>@endif
                </td>
                <td x-show="cols.mfg">
                    <div class="{{ $mfgClass }}">{{ $mfgText }}</div>
                    @if($mfgDate)<div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $mfgDate->format('d/m/Y') }}</div>@endif
                </td>
                <td x-show="cols.service">
                    <div class="{{ $svcClass }}">{{ $svcText }}</div>
                    @if($svcDate)<div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $svcDate->format('d/m/Y') }}</div>@endif
                </td>
                <td x-show="cols.status">{{ $statuses[$item->status] ?? ucfirst(str_replace('_', ' ', $item->status)) }}</td>
                <td>
                    <div class="ld-row-actions">
                        <a href="{{ route('equipment.edit', $item) }}" class="plain-link" title="Edit" aria-label="Edit {{ $item->serial_number }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.2 5.2l3.6 3.6M4 20l4.5-1 10.3-10.3a2.5 2.5 0 00-3.5-3.5L5 15.5 4 20z"/></svg>
                        </a>
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('equipment.destroy', $item) }}" data-confirm="Delete equipment {{ $item->serial_number }}?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-cancel" title="Delete" aria-label="Delete {{ $item->serial_number }}">
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
