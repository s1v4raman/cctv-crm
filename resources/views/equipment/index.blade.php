<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Installed Equipment & Serial Tracking</h2>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('equipment.create') }}" class="btn-primary">
                Register Equipment
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
            border-radius: 8px;
            padding: 12px 16px;
            border: 1px solid var(--card-border);
        }
        html.dark .stat-card {
            background: #0f172a;
            border-color: var(--card-border);
        }
        .stat-label { font-size: 14px; font-weight: normal; color: var(--text-muted); margin-bottom: 0.25rem; }
        .stat-value { font-size: 26px; font-weight: bold; color: var(--text-main); line-height: 1.2; }

        .pg-card {
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid var(--card-border);
            overflow: hidden;
        }
        html.dark .pg-card {
            background: #0f172a;
            border-color: var(--card-border);
        }

        .filter-bar {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem 1rem; border-bottom: 1px solid var(--card-border); flex-wrap: wrap;
            background: #ffffff;
        }
        html.dark .filter-bar {
            border-bottom: 1px solid var(--card-border);
            background: #0f172a;
        }
        .filter-bar input, .filter-bar select {
            border: 1px solid var(--card-border); border-radius: 6px;
            height: 38px; min-height: 38px; padding: 0 0.875rem; font-size: 14px; color: var(--text-main);
            background: #ffffff; outline: none; font-family: inherit;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        html.dark .filter-bar input, html.dark .filter-bar select {
            border: 1px solid #475569;
            background: #0b1120;
            color: var(--text-main);
        }
        .filter-bar select {
            padding-right: 2.25rem;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%231e293b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.25em 1.25em;
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
        }
        html.dark .filter-bar select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        }
        .filter-bar input:focus, .filter-bar select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        .p-table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; border: none !important; }
        .p-table thead { background: #f0f0f0; border-top: none !important; border-bottom: 1px solid var(--card-border); }
        html.dark .p-table thead { background: var(--card-border); border-bottom: 1px solid var(--card-border); }
        .p-table thead th {
            padding: 0.75rem 1rem;
            font-size: 14px; font-weight: bold; text-transform: none;
            color: var(--text-main); text-align: left; white-space: nowrap;
            border-top: none !important;
            border-bottom: 1px solid var(--card-border);
        }
        html.dark .p-table thead th {
            color: var(--text-main);
            border-bottom: 1px solid var(--card-border);
        }
        .p-table thead th.center { text-align: center; }
        .p-table thead th.right { text-align: right; }
        .p-table tbody tr { border-bottom: 1px solid var(--card-border); background: #ffffff; }
        html.dark .p-table tbody tr { border-bottom: 1px solid var(--card-border); background: #0f172a; }
        .p-table tbody tr:last-child,
        .p-table tbody tr:last-child td { border-bottom: none !important; }
        .p-table tbody tr:hover { background: #f5f5f5; }
        html.dark .p-table tbody tr:hover { background: var(--card-border); }
        .p-table tbody td { padding: 0.75rem 1rem; vertical-align: middle; font-size: 14px; color: var(--text-main); border-bottom: 1px solid var(--card-border); }
        html.dark .p-table tbody td { color: var(--text-main); border-bottom: 1px solid var(--card-border); }
        .p-table tbody td.center { text-align: center; }
        .p-table tbody td.right { text-align: right; }

        .sn-link { font-family: Arial, sans-serif; font-size: 14px; font-weight: normal; color: #2563eb; text-decoration: none; }
        .sn-link:hover { text-decoration: underline; color: #1d4ed8; }
        .mac-text { font-family: Arial, sans-serif; font-size: 12px; color: #666666; }
        html.dark .mac-text { color: var(--text-muted); }

        /* Status: Clean 1px single border badge matching Image 4 */
        .status-badge {
            display: inline-flex;
            align-items: center;
            font-size: 12px;
            font-weight: 700;
            padding: 2px 8px !important;
            border-radius: 4px;
            border: 1px solid var(--card-border) !important;
            background: #ffffff;
        }
        html.dark .status-badge {
            border-color: #475569 !important;
            background: #0f172a;
        }
        .status-active         { color: #16a34a !important; }
        .status-under_repair   { color: #d97706 !important; }
        .status-replaced       { color: #2563eb !important; }
        .status-decommissioned { color: #555555 !important; }

        .pg-links { padding: 0.75rem 1rem; border-top: 1px solid var(--card-border); }
        html.dark .pg-links { border-top: 1px solid var(--card-border); }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if (session('status'))
                <div class="mb-4 p-3 rounded bg-white border border-[#dddddd] text-[#16a34a] text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="pg-card">
                {{-- Filters --}}
                <form method="GET" action="{{ route('equipment.index') }}" class="filter-bar">
                    <input aria-label="Search Serial No, MAC, Device" type="text" name="search" value="{{ request('search') }}" placeholder="Search Serial No, MAC, Device..." style="min-width: 220px;" />

                    <select aria-label="Lead" name="lead_id" data-no-tom>
                        <option value="">All Customer Sites</option>
                        @foreach($leads as $l)
                            <option value="{{ $l->id }}" @selected(request('lead_id') == $l->id)>{{ $l->customer_name }}</option>
                        @endforeach
                    </select>

                    <select aria-label="Warranty" name="warranty">
                        <option value="">All Warranty States</option>
                        <option value="active" @selected(request('warranty') == 'active')>Active Warranty</option>
                        <option value="expiring_soon" @selected(request('warranty') == 'expiring_soon')>Expiring Soon (&le; 30 days)</option>
                        <option value="expired" @selected(request('warranty') == 'expired')>Expired Warranty</option>
                    </select>

                    <select aria-label="Status" name="status">
                        <option value="">All Device Status</option>
                        <option value="active" @selected(request('status') == 'active')>Active</option>
                        <option value="under_repair" @selected(request('status') == 'under_repair')>Under Repair</option>
                        <option value="replaced" @selected(request('status') == 'replaced')>Replaced</option>
                        <option value="decommissioned" @selected(request('status') == 'decommissioned')>Decommissioned</option>
                    </select>

                    <button type="submit" class="h-[42px] px-5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-xs transition inline-flex items-center justify-center cursor-pointer">
                        Search
                    </button>
                    @if(request()->hasAny(['search', 'lead_id', 'warranty', 'status']))
                        <a href="{{ route('equipment.index') }}" class="h-[42px] px-4 inline-flex items-center rounded-lg border border-[var(--card-border)] dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 text-sm font-semibold transition shadow-xs">Clear</a>
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
                                    @php
                                        $eqStatusColors = [
                                            'active'         => '#16a34a',
                                            'under_repair'   => '#d97706',
                                            'replaced'       => '#2563eb',
                                            'decommissioned' => '#555555',
                                        ];
                                        $sColor = $eqStatusColors[strtolower($item->status)] ?? '#555555';
                                    @endphp
                                    <span class="status-text" style="color: {{ $sColor }}; font-weight: bold;">
                                        {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                    </span>
                                </td>
                                <td class="right whitespace-nowrap text-sm table-actions" style="text-align:right;">
                                    <a href="{{ route('equipment.show', $item) }}" class="action-link">View</a>
                                    <a href="{{ route('equipment.edit', $item) }}" class="action-link">Edit</a>
                                    @if(auth()->user()->isAdmin())
                                        <form method="POST" action="{{ route('equipment.destroy', $item) }}" data-confirm="Are you sure you want to delete this equipment record?" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="danger-link">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-6 text-sm text-slate-500">
                                    No equipment yet
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Total count shown below the table --}}
                <div class="px-4 py-2.5 border-t border-[#ddd] dark:border-slate-800 bg-[#fbfbfb] dark:bg-slate-900/60 flex items-center justify-between text-xs text-slate-500">
                    <span class="font-medium">Total: {{ $equipments->total() }}</span>
                    @if($equipments->hasPages())
                        <div class="text-xs">
                            {{ $equipments->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
