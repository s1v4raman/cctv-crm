<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-white font-heading tracking-tight">Installed Equipment & Serial Tracking</h2>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-sky-500/20 text-sky-400 px-2 py-0.5 rounded border border-sky-500/30">Asset Registry</span>
                </div>
                <p class="mt-1 text-xs text-slate-400">Track deployed cameras, NVR channels, serial barcodes, MAC addresses & dual warranty periods</p>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('equipment.create') }}" class="btn-amber">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Register Equipment
            </a>
            @endif
        </div>
    </x-slot>

    <style>
        /* .pg-wrap uses global app.css */
        /* .pg-inner uses global app.css */

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }
        @media (min-width: 640px) {
            .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1024px) {
            .stat-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        .stat-card {
            background: #0f172a;
            border-radius: 1rem;
            padding: 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s ease;
        }
        .stat-card:hover { border-color: rgba(245, 158, 11, 0.3); transform: translateY(-2px); }
        .stat-label { font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; margin-bottom: 0.25rem; }
        .stat-value { font-size: 1.7rem; font-weight: 800; color: #ffffff; font-family: 'Outfit', sans-serif; }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 0.75rem;
            display: flex; align-items: center; justify-content: center;
            background: #1e293b; border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .pg-card {
            background:#0f172a;
            border-radius:1rem;
            border:1px solid rgba(255, 255, 255, 0.08);
            box-shadow:0 10px 25px -5px rgba(0, 0, 0, 0.5);
            overflow:hidden;
        }

        .filter-bar {
            display:flex; align-items:center; gap:.75rem;
            padding:1rem 1.25rem; border-bottom:1px solid rgba(255, 255, 255, 0.08);
            background:#0b1120; flex-wrap:wrap;
        }
        .filter-bar input, .filter-bar select {
            border:1px solid #334155; border-radius:.6rem;
            padding:.5rem .9rem; font-size:.85rem; color:#ffffff;
            outline:none; transition: all .15s;
        }
        .filter-bar input:focus, .filter-bar select:focus { border-color:#f59e0b; }
        .filter-bar input { min-width:240px; }

        .btn-filter {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: .5rem 1.1rem; border-radius: .6rem;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #020617 !important;
            font-size: .82rem; font-weight: 800;
            border: none; cursor: pointer;
            box-shadow: 0 4px 12px -2px rgba(245, 158, 11, 0.3);
        }
        .btn-filter:hover { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); }

        .btn-clear {
            display: inline-flex; align-items: center;
            font-size: .82rem; color: #94a3b8 !important; text-decoration: none; font-weight: 700;
            padding: .5rem .85rem; border-radius: .6rem; border: 1px solid #334155; background: #1e293b;
            transition: all .15s;
        }
        .btn-clear:hover { background: #334155; color: #ffffff !important; }

        .p-table { width:100%; border-collapse:collapse; }
        .p-table thead { background:#0b1120; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
        .p-table thead th {
            padding:.75rem 1.25rem;
            font-size:.68rem; font-weight:800; text-transform:uppercase;
            letter-spacing:.06em; color:#94a3b8; text-align:left; white-space:nowrap;
        }
        .p-table thead th.center { text-align:center; }
        .p-table tbody tr { border-bottom:1px solid rgba(255, 255, 255, 0.05); transition:background .12s; }
        .p-table tbody tr:hover { background:#1e293b; }
        .p-table tbody td { padding:.85rem 1.25rem; vertical-align:middle; color:#cbd5e1; }
        .p-table tbody td.center { text-align:center; }

        .sn-badge {
            display: inline-block;
            font-family: ui-monospace, monospace;
            font-size: 0.82rem;
            font-weight: 700;
            background: #1e293b;
            color: #f59e0b;
            padding: 0.25rem 0.55rem;
            border-radius: 0.35rem;
            border: 1px solid rgba(245, 158, 11, 0.3);
            text-decoration: none;
            transition: all .15s;
        }
        .sn-badge:hover { background: #334155; color: #fbbf24; border-color: #f59e0b; }

        .mac-badge {
            font-family: ui-monospace, monospace;
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 600;
        }

        .warranty-pill {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 0.3rem 0.65rem;
            border-radius: 0.5rem;
            font-size: 0.72rem;
            line-height: 1.25;
        }
        .w-active   { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .w-warning  { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .w-expired  { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .w-none     { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

        .status-badge {
            display: inline-flex;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .status-active        { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .status-under_repair  { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
        .status-replaced      { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .status-decommissioned{ background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

        /* Action Buttons */
        .btn-action-view {
            display: inline-flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; border-radius: 0.45rem;
            background-color: #eef2ff; color: #4f46e5 !important;
            border: 1px solid #c7d2fe; transition: all .15s;
        }
        .btn-action-view:hover { background-color: #4f46e5; color: #ffffff !important; transform: translateY(-1px); }

        .btn-action-edit {
            display: inline-flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; border-radius: 0.45rem;
            background-color: #f0fdf4; color: #16a34a !important;
            border: 1px solid #bbf7d0; transition: all .15s;
        }
        .btn-action-edit:hover { background-color: #16a34a; color: #ffffff !important; transform: translateY(-1px); }

        .btn-action-delete {
            display: inline-flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; border-radius: 0.45rem;
            background-color: #fef2f2; color: #dc2626 !important;
            border: 1px solid #fecaca; transition: all .15s; cursor: pointer;
        }
        .btn-action-delete:hover { background-color: #dc2626; color: #ffffff !important; transform: translateY(-1px); }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if (session('status'))
                <div class="mb-4 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Stat Cards --}}
            <div class="stat-grid">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Installed Hardware</div>
                        <div class="stat-value">{{ $totalDevices }}</div>
                        <span class="text-xs text-gray-500 font-medium">{{ $activeDevices }} active devices</span>
                    </div>
                    <div class="stat-icon bg-indigo-50 text-indigo-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Active Deployments</div>
                        <div class="stat-value text-emerald-600">{{ $activeDevices }}</div>
                        <span class="text-xs text-emerald-600 font-medium">Under operational status</span>
                    </div>
                    <div class="stat-icon bg-emerald-50 text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Warranty Expiring Soon</div>
                        <div class="stat-value text-amber-600">{{ $expiringSoonCount }}</div>
                        <span class="text-xs text-amber-600 font-medium">Within next 30 days (AMC leads)</span>
                    </div>
                    <div class="stat-icon bg-amber-50 text-amber-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-label">Expired Warranties</div>
                        <div class="stat-value text-slate-600">{{ $expiredCount }}</div>
                        <span class="text-xs text-slate-500 font-medium">Out of standard warranty</span>
                    </div>
                    <div class="stat-icon bg-slate-100 text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
            </div>

            {{-- Main Table --}}
            <div class="pg-card">
                {{-- Filters --}}
                <form method="GET" action="{{ route('equipment.index') }}" class="filter-bar">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Serial No, MAC, Device, Client..." />

                    <select name="lead_id" data-no-tom>
                        <option value="">All Customer Sites</option>
                        @foreach($leads as $l)
                            <option value="{{ $l->id }}" @selected(request('lead_id') == $l->id)>{{ $l->customer_name }}</option>
                        @endforeach
                    </select>

                    <select name="warranty">
                        <option value="">All Warranty States</option>
                        <option value="active" @selected(request('warranty') == 'active')>🟢 Active Warranty</option>
                        <option value="expiring_soon" @selected(request('warranty') == 'expiring_soon')>🟡 Expiring Soon (&le; 30 days)</option>
                        <option value="expired" @selected(request('warranty') == 'expired')>🔴 Expired Warranty</option>
                    </select>

                    <select name="status">
                        <option value="">All Device Status</option>
                        <option value="active" @selected(request('status') == 'active')>Active</option>
                        <option value="under_repair" @selected(request('status') == 'under_repair')>Under Repair</option>
                        <option value="replaced" @selected(request('status') == 'replaced')>Replaced</option>
                        <option value="decommissioned" @selected(request('status') == 'decommissioned')>Decommissioned</option>
                    </select>

                    <button type="submit" class="btn-filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'lead_id', 'warranty', 'status']))
                        <a href="{{ route('equipment.index') }}" class="btn-clear">Clear</a>
                    @endif
                </form>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="p-table">
                        <thead>
                            <tr>
                                <th>Serial Number & MAC</th>
                                <th>Equipment / Camera Model</th>
                                <th>Customer & Site Location</th>
                                <th>Mfg Warranty (Hardware)</th>
                                <th>Service Warranty (Labor)</th>
                                <th class="center">Status</th>
                                <th class="center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($equipments as $item)
                            <tr>
                                <td>
                                    <a href="{{ route('equipment.show', $item) }}" class="sn-badge">
                                        {{ $item->serial_number }}
                                    </a>
                                    @if($item->mac_address)
                                        <div class="mac-badge mt-1">MAC: {{ $item->mac_address }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-bold text-sm text-gray-900">{{ $item->equipment_name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        {{ $item->product?->brand ?? '' }} {{ $item->product?->model_no ?? '' }}
                                        @if($item->location_tag)
                                            &middot; <span class="text-indigo-600 font-semibold">📍 {{ $item->location_tag }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="font-bold text-sm text-gray-900">
                                        <a href="{{ route('leads.show', $item->lead) }}" class="text-indigo-600 hover:underline">
                                            {{ $item->lead?->customer_name }}
                                        </a>
                                    </div>
                                    @if($item->installationJob)
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            Job: <a href="{{ route('jobs.show', $item->installationJob) }}" class="font-medium underline text-gray-700">{{ $item->installationJob->job_no }}</a>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @php $mfgStatus = $item->mfg_warranty_status; @endphp
                                    @if($mfgStatus === 'active')
                                        <span class="warranty-pill w-active">
                                            <span class="font-bold">Active ({{ $item->mfg_days_remaining }}d left)</span>
                                            <span class="text-[10px] opacity-85">Exp: {{ $item->manufacturer_warranty_expiry?->format('d M Y') }}</span>
                                        </span>
                                    @elseif($mfgStatus === 'expiring_soon')
                                        <span class="warranty-pill w-warning">
                                            <span class="font-bold">⚠️ Expiring ({{ $item->mfg_days_remaining }}d)</span>
                                            <span class="text-[10px] opacity-85">Exp: {{ $item->manufacturer_warranty_expiry?->format('d M Y') }}</span>
                                        </span>
                                    @elseif($mfgStatus === 'expired')
                                        <span class="warranty-pill w-expired">
                                            <span class="font-bold">Expired</span>
                                            <span class="text-[10px] opacity-85">{{ $item->manufacturer_warranty_expiry?->format('d M Y') }}</span>
                                        </span>
                                    @else
                                        <span class="warranty-pill w-none">No Data</span>
                                    @endif
                                </td>
                                <td>
                                    @php $svcStatus = $item->service_warranty_status; @endphp
                                    @if($svcStatus === 'active')
                                        <span class="warranty-pill w-active">
                                            <span class="font-bold">Active ({{ $item->service_days_remaining }}d left)</span>
                                            <span class="text-[10px] opacity-85">Exp: {{ $item->service_warranty_expiry?->format('d M Y') }}</span>
                                        </span>
                                    @elseif($svcStatus === 'expiring_soon')
                                        <span class="warranty-pill w-warning">
                                            <span class="font-bold">⚠️ Expiring ({{ $item->service_days_remaining }}d)</span>
                                            <span class="text-[10px] opacity-85">Exp: {{ $item->service_warranty_expiry?->format('d M Y') }}</span>
                                        </span>
                                    @elseif($svcStatus === 'expired')
                                        <span class="warranty-pill w-expired">
                                            <span class="font-bold">Expired</span>
                                            <span class="text-[10px] opacity-85">{{ $item->service_warranty_expiry?->format('d M Y') }}</span>
                                        </span>
                                    @else
                                        <span class="warranty-pill w-none">No Data</span>
                                    @endif
                                </td>
                                <td class="center">
                                    <span class="status-badge status-{{ $item->status }}">
                                        {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                    </span>
                                </td>
                                <td class="center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('equipment.show', $item) }}" class="btn-action-view" title="View details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('equipment.edit', $item) }}" class="btn-action-edit" title="Edit hardware">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        @if(auth()->user()->isAdmin())
                                        <form method="POST" action="{{ route('equipment.destroy', $item) }}" onsubmit="return confirm('Are you sure you want to delete this equipment record?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action-delete" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-sm text-gray-500 font-medium">
                                    No installed equipment logged yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($equipments->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $equipments->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
