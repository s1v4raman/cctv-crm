<x-app-layout>
    @php
        $views = collect(['all' => 'All Sites'] + $cities->mapWithKeys(fn ($c) => [$c => $c . ' Sites'])->all())->map(fn ($label, $city) => [
            'label' => $label,
            'url' => route('sites.index', array_filter(['city' => $city === 'all' ? null : $city])),
            'active' => $cityFilter === $city,
        ])->values()->all();
        $filterCount = collect([$search !== '', $cityFilter !== 'all'])->filter()->count();
    @endphp

    <x-list-page key="sites" :paginator="$sites" :views="$views"
        :sorts="['date_desc' => 'Newest first', 'name' => 'Name, A to Z', 'city' => 'City', 'projects_desc' => 'Most projects']"
        :create-url="route('sites.create')" create-label="New Site"
        filter-title="Filter Sites by" :filter-action="route('sites.index')" :keep="['sort' => request('sort'), 'per_page' => request('per_page')]"
        :filter-count="$filterCount" :clear-url="route('sites.index')"
        :columns="['client' => 'Client & Contact', 'address' => 'Address', 'map' => 'Map', 'projects' => 'Projects']"
        empty-text="No installation sites yet." filtered-text="No sites match these filters.">

        <x-slot name="filters">
            <div class="ld-sub">Site, code, client, phone or address</div>
            <input type="text" name="search" value="{{ $search }}" class="ld-input" aria-label="Site, code, client, phone or address">

            <div class="ld-sub">City</div>
            <select name="city" class="ld-input" aria-label="City">
                <option value="all">Any city</option>
                @foreach($cities as $city)
                    <option value="{{ $city }}" @selected($cityFilter === $city)>{{ $city }}</option>
                @endforeach
            </select>
        </x-slot>

        <x-slot name="head">
            <th style="min-width: 230px">Site</th>
            <th x-show="cols.client" style="min-width: 190px">Client & Contact</th>
            <th x-show="cols.address" style="min-width: 260px">Address</th>
            <th x-show="cols.map" style="min-width: 90px">Map</th>
            <th x-show="cols.projects" style="min-width: 80px; text-align: right">Projects</th>
        </x-slot>

        @foreach($sites as $site)
            <tr>
                <td>
                    <a href="{{ route('sites.show', $site) }}" class="ld-name plain-link">{{ $site->name }}</a>
                    <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $site->site_code }}</div>
                </td>
                <td x-show="cols.client">
                    <div>{{ $site->client_name ?? '—' }}</div>
                    @if($site->client_phone)<div class="text-xs mt-0.5 whitespace-nowrap" style="color: var(--ld-muted)">{{ $site->client_phone }}</div>@endif
                </td>
                <td x-show="cols.address">
                    <div>{{ $site->address }}</div>
                    <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $site->city }}{{ $site->pincode ? ' - ' . $site->pincode : '' }}</div>
                </td>
                <td x-show="cols.map" class="whitespace-nowrap">
                    @if($site->has_coordinates || $site->google_maps_url)
                        <a href="{{ $site->navigate_url }}" target="_blank" rel="noopener noreferrer">Open map</a>
                    @else
                        <span style="color: var(--ld-muted)">—</span>
                    @endif
                </td>
                <td x-show="cols.projects" style="text-align: right">{{ $site->projects_count }}</td>
                <td>
                    <div class="ld-row-actions">
                        <a href="{{ route('sites.edit', $site) }}" class="plain-link" title="Edit" aria-label="Edit {{ $site->name }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.2 5.2l3.6 3.6M4 20l4.5-1 10.3-10.3a2.5 2.5 0 00-3.5-3.5L5 15.5 4 20z"/></svg>
                        </a>
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('sites.destroy', $site) }}" data-confirm="Delete site {{ $site->name }}? This cannot be undone.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-cancel" title="Delete" aria-label="Delete {{ $site->name }}">
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
