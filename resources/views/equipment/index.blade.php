<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#222222]">Installed Equipment & Serial Tracking</h1>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('equipment.create') }}" class="px-4 py-2 rounded bg-[#2563eb] hover:bg-[#1d4ed8] text-white text-sm font-normal transition">
                + Register Equipment
            </a>
            @endif
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
            padding: 12px 16px;
            border: 1px solid #dddddd;
        }
        .stat-label { font-size: 14px; font-weight: normal; color: #555555; margin-bottom: 0.25rem; }
        .stat-value { font-size: 26px; font-weight: bold; color: #222222; line-height: 1.2; }

        .pg-card {
            background: #ffffff;
            border-radius: 4px;
            border: 1px solid #dddddd;
            overflow: hidden;
        }

        .filter-bar {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem 1rem; border-bottom: 1px solid #dddddd; flex-wrap: wrap;
            background: #ffffff;
        }
        .filter-bar input, .filter-bar select {
            border: 1px solid #cccccc; border-radius: 4px;
            height: 38px; padding: 0 0.75rem; font-size: 14px; color: #222222;
            background: #ffffff; outline: none; font-family: Arial, sans-serif;
        }
        .filter-bar input:focus, .filter-bar select:focus { border-color: #2563eb; }

        .p-table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; }
        .p-table thead { background: #f0f0f0; border-bottom: 1px solid #dddddd; }
        .p-table thead th {
            padding: 0.75rem 1rem;
            font-size: 14px; font-weight: bold; text-transform: none;
            color: #222222; text-align: left; white-space: nowrap;
        }
        .p-table thead th.center { text-align: center; }
        .p-table thead th.right { text-align: right; }
        .p-table tbody tr { border-bottom: 1px solid #dddddd; background: #ffffff; }
        .p-table tbody tr:last-child { border-bottom: none; }
        .p-table tbody tr:hover { background: #f5f5f5; }
        .p-table tbody td { padding: 0.75rem 1rem; vertical-align: middle; font-size: 14px; color: #222222; }
        .p-table tbody td.center { text-align: center; }
        .p-table tbody td.right { text-align: right; }

        .sn-link { font-family: Arial, sans-serif; font-size: 14px; font-weight: normal; color: #2563eb; text-decoration: none; }
        .sn-link:hover { text-decoration: underline; color: #1d4ed8; }
        .mac-text { font-family: Arial, sans-serif; font-size: 12px; color: #666666; }

        /* Status: Plain bold colored text */
        .status-badge { font-size: 14px; font-weight: bold; background: transparent !important; border: none !important; padding: 0 !important; }
        .status-active         { color: #16a34a !important; }
        .status-under_repair   { color: #d97706 !important; }
        .status-replaced       { color: #2563eb !important; }
        .status-decommissioned { color: #555555 !important; }

        .pg-links { padding: 0.75rem 1rem; border-top: 1px solid #dddddd; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if (session('status'))
                <div class="mb-4 p-3 rounded bg-white border border-[#dddddd] text-[#16a34a] text-sm">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Stat Cards --}}
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Installed Units</div>
                    <div class="stat-value">{{ $totalCount ?? $totalDevices ?? 0 }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Under Active Warranty</div>
                    <div class="stat-value text-[#16a34a]">{{ $activeWarrantyCount ?? $activeDevices ?? 0 }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Expiring Soon (≤ 30d)</div>
                    <div class="stat-value text-[#d97706]">{{ $expiringSoonCount ?? 0 }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Out of Warranty</div>
                    <div class="stat-value text-[#dc2626]">{{ $expiredWarrantyCount ?? $expiredCount ?? 0 }}</div>
                </div>
            </div>

            <div class="pg-card">
                {{-- Filters --}}
                <form method="GET" action="{{ route('equipment.index') }}" class="filter-bar">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Serial No, MAC, Device..." style="min-width: 220px;" />

                    <select name="lead_id" data-no-tom>
                        <option value="">All Customer Sites</option>
                        @foreach($leads as $l)
                            <option value="{{ $l->id }}" @selected(request('lead_id') == $l->id)>{{ $l->customer_name }}</option>
                        @endforeach
                    </select>

                    <select name="warranty">
                        <option value="">All Warranty States</option>
                        <option value="active" @selected(request('warranty') == 'active')>Active Warranty</option>
                        <option value="expiring_soon" @selected(request('warranty') == 'expiring_soon')>Expiring Soon (&le; 30 days)</option>
                        <option value="expired" @selected(request('warranty') == 'expired')>Expired Warranty</option>
                    </select>

                    <select name="status">
                        <option value="">All Device Status</option>
                        <option value="active" @selected(request('status') == 'active')>Active</option>
                        <option value="under_repair" @selected(request('status') == 'under_repair')>Under Repair</option>
                        <option value="replaced" @selected(request('status') == 'replaced')>Replaced</option>
                        <option value="decommissioned" @selected(request('status') == 'decommissioned')>Decommissioned</option>
                    </select>

                    <button type="submit" class="h-[38px] px-4 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'lead_id', 'warranty', 'status']))
                        <a href="{{ route('equipment.index') }}" class="h-[38px] px-3.5 inline-flex items-center rounded border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium transition">Clear</a>
                    @endif
                </form>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="p-table">
                        <thead>
                            <tr>
                                <th>Serial Number &amp; MAC</th>
                                <th>Equipment / Model</th>
                                <th>Customer &amp; Site</th>
                                <th>Mfg Warranty</th>
                                <th>Service Warranty</th>
                                <th class="center">Status</th>
                                <th class="right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($equipments as $item)
                            <tr>
                                <td>
                                    <a href="{{ route('equipment.show', $item) }}" class="sn-link">
                                        {{ $item->serial_number }}
                                    </a>
                                    @if($item->mac_address)
                                        <div class="mac-text mt-0.5">MAC: {{ $item->mac_address }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-900 dark:text-white text-sm">{{ $item->equipment_name }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        {{ $item->product?->brand ?? '' }} {{ $item->product?->model_no ?? '' }}
                                        @if($item->location_tag)
                                            &middot; <span>{{ $item->location_tag }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="font-semibold text-sm text-slate-900 dark:text-white">
                                        <a href="{{ route('leads.show', $item->lead) }}" class="hover:text-blue-600">
                                            {{ $item->lead?->customer_name }}
                                        </a>
                                    </div>
                                    @if($item->installationJob)
                                        <div class="text-xs text-slate-500 mt-0.5">
                                            Job: <a href="{{ route('jobs.show', $item->installationJob) }}" class="hover:underline">{{ $item->installationJob->job_no }}</a>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @php $mfgStatus = $item->mfg_warranty_status; @endphp
                                    @if($mfgStatus === 'active')
                                        <div class="text-xs text-emerald-600 font-medium">Active ({{ $item->mfg_days_remaining }}d left)</div>
                                        <div class="text-[11px] text-slate-400">Exp: {{ $item->manufacturer_warranty_expiry?->format('d M Y') }}</div>
                                    @elseif($mfgStatus === 'expiring_soon')
                                        <div class="text-xs text-amber-600 font-medium">Expiring ({{ $item->mfg_days_remaining }}d)</div>
                                        <div class="text-[11px] text-slate-400">Exp: {{ $item->manufacturer_warranty_expiry?->format('d M Y') }}</div>
                                    @elseif($mfgStatus === 'expired')
                                        <div class="text-xs text-red-600 font-medium">Expired</div>
                                        <div class="text-[11px] text-slate-400">{{ $item->manufacturer_warranty_expiry?->format('d M Y') }}</div>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php $svcStatus = $item->service_warranty_status; @endphp
                                    @if($svcStatus === 'active')
                                        <div class="text-xs text-emerald-600 font-medium">Active ({{ $item->service_days_remaining }}d left)</div>
                                        <div class="text-[11px] text-slate-400">Exp: {{ $item->service_warranty_expiry?->format('d M Y') }}</div>
                                    @elseif($svcStatus === 'expiring_soon')
                                        <div class="text-xs text-amber-600 font-medium">Expiring ({{ $item->service_days_remaining }}d)</div>
                                        <div class="text-[11px] text-slate-400">Exp: {{ $item->service_warranty_expiry?->format('d M Y') }}</div>
                                    @elseif($svcStatus === 'expired')
                                        <div class="text-xs text-red-600 font-medium">Expired</div>
                                        <div class="text-[11px] text-slate-400">{{ $item->service_warranty_expiry?->format('d M Y') }}</div>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="center">
                                    <span class="status-badge status-{{ $item->status }}">
                                        {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                    </span>
                                </td>
                                <td class="right whitespace-nowrap text-sm" style="text-align:right;">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <a href="{{ route('equipment.show', $item) }}" class="text-blue-600 hover:underline">View</a>
                                        <span class="text-slate-300">|</span>
                                        <a href="{{ route('equipment.edit', $item) }}" class="text-blue-600 hover:underline">Edit</a>
                                        @if(auth()->user()->isAdmin())
                                            <span class="text-slate-300">|</span>
                                            <form method="POST" action="{{ route('equipment.destroy', $item) }}" onsubmit="return confirm('Are you sure you want to delete this equipment record?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline cursor-pointer bg-transparent border-0 p-0 font-medium">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-sm text-slate-500 font-medium">
                                    No installed equipment logged yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($equipments->hasPages())
                <div class="p-3 border-t border-slate-200 dark:border-slate-800">
                    {{ $equipments->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
