<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('portal.dashboard') }}" class="text-xs font-semibold hover:underline" style="color: var(--crm-accent, #2563eb);">← Back to Overview</a>
                    <span class="text-slate-400">/</span>
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Asset Register</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight mt-1 font-heading">My CCTV Equipment & Cameras</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Real-time status, warranty health, locations, and 1-click issue reporting for your cameras.</p>
            </div>
            <a href="{{ route('portal.tickets.create') }}" 
               class="crm-customer-action-btn inline-flex items-center gap-2 px-4 py-2 text-white text-xs font-bold rounded-xl shadow-xs transition-all duration-200 cursor-pointer"
               style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(37,99,235,0.35));">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Report Breakdown</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f8fafc] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(!$hasLead || $equipment->isEmpty() && !request('search'))
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-10 border border-slate-200/90 dark:border-slate-800 shadow-xs text-center max-w-2xl mx-auto space-y-4">
                    <div class="w-16 h-16 rounded-2xl crm-customer-icon-box flex items-center justify-center mx-auto font-bold text-2xl" style="background-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.1); color: var(--crm-accent, #2563eb); border: 1px solid rgba(var(--crm-accent-rgb, 37, 99, 235), 0.25);">
                        📹
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading">No CCTV Cameras Registered Yet</h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                        Once our technician installs your security hardware or links your existing site assets, each camera's live warranty, model serial, and location tag will appear here.
                    </p>
                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                        <a href="{{ route('portal.dashboard') }}" 
                           class="crm-customer-action-btn px-5 py-2.5 text-white font-bold text-xs rounded-xl shadow-xs transition-all duration-200 cursor-pointer"
                           style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(37,99,235,0.35));">
                            Configure Site Address on Overview →
                        </a>
                        <a href="{{ route('home') }}" class="px-5 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-xl border border-slate-200 dark:border-slate-700 transition">
                            Explore Storefront Models
                        </a>
                    </div>
                </div>
            @else
                {{-- KPI Metric Summary Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Installed Cameras</div>
                        <div class="text-2xl font-black text-slate-900 dark:text-white font-heading mt-1">{{ method_exists($equipment, 'total') ? $equipment->total() : $equipment->count() }} <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">Units</span></div>
                        <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold mt-1">● Monitored Hardware Assets</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Manufacturer Warranty</div>
                        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-heading mt-1">
                            {{ $equipment->filter(fn($e) => $e->mfg_warranty_status === 'active')->count() }} <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">Active</span>
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-1">Direct Brand Support</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Warranty Expiring Soon</div>
                        <div class="text-2xl font-black text-amber-600 dark:text-amber-400 font-heading mt-1">
                            {{ $equipment->filter(fn($e) => $e->mfg_warranty_status === 'expiring_soon')->count() }} <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">Units</span>
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-1">Eligible for AMC Renewal</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Service Coverage</div>
                        <div class="text-2xl font-black font-heading mt-1" style="color: var(--crm-accent, #2563eb);">
                            {{ $equipment->filter(fn($e) => $e->service_warranty_status === 'active')->count() }} <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">Covered</span>
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-1">Zero Labor Charges</div>
                    </div>
                </div>

                {{-- Search Bar --}}
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
                    <form method="GET" action="{{ route('portal.equipment') }}" class="w-full sm:w-96 flex items-center gap-2">
                        <div class="relative w-full">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Search camera name, serial no, location..." 
                                   class="w-full pl-9 pr-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <button type="submit" 
                                class="crm-customer-action-btn px-4 py-2 text-white text-xs font-bold rounded-xl shadow-xs transition-all duration-200 shrink-0 cursor-pointer"
                                style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(37,99,235,0.35));">
                            Search
                        </button>
                        @if(request('search'))
                            <a href="{{ route('portal.equipment') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 transition">
                                Clear
                            </a>
                        @endif
                    </form>

                    <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold self-end sm:self-center">
                        Showing {{ method_exists($equipment, 'total') ? $equipment->total() : $equipment->count() }} installed unit(s)
                    </div>
                </div>

                {{-- Equipment Cards / Table --}}
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-6 py-3.5">Device & Model</th>
                                    <th class="px-6 py-3.5">Location Tag</th>
                                    <th class="px-6 py-3.5">Serial / MAC Address</th>
                                    <th class="px-6 py-3.5">Installation Date</th>
                                    <th class="px-6 py-3.5">Manufacturer Warranty</th>
                                    <th class="px-6 py-3.5">Service Warranty</th>
                                    <th class="px-6 py-3.5 text-right">Quick Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium text-slate-700 dark:text-slate-300">
                                @foreach($equipment as $item)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-1.5 font-heading">
                                                <span>{{ $item->equipment_name }}</span>
                                            </div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 font-mono">
                                                {{ $item->product ? $item->product->brand . ' ' . $item->product->model_no : 'Hardware Asset' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs border border-slate-200 dark:border-slate-700">
                                                📍 {{ $item->location_tag ?: 'Main Site' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-mono text-xs font-bold" style="color: var(--crm-accent, #2563eb);">{{ $item->serial_number ?: 'N/A' }}</div>
                                            @if($item->mac_address)
                                                <div class="font-mono text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">MAC: {{ $item->mac_address }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400">
                                            {{ $item->installation_date ? \Carbon\Carbon::parse($item->installation_date)->format('d M Y') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($item->mfg_warranty_status === 'active')
                                                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                    <span>● Active</span>
                                                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">({{ $item->mfg_days_remaining }}d left)</span>
                                                </div>
                                                <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Until {{ \Carbon\Carbon::parse($item->manufacturer_warranty_expiry)->format('d M Y') }}</div>
                                            @elseif($item->mfg_warranty_status === 'expiring_soon')
                                                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                    <span>▲ Expiring Soon</span>
                                                    <span>({{ $item->mfg_days_remaining }}d)</span>
                                                </div>
                                                <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Until {{ \Carbon\Carbon::parse($item->manufacturer_warranty_expiry)->format('d M Y') }}</div>
                                            @elseif($item->mfg_warranty_status === 'expired')
                                                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                    <span>✕ Expired</span>
                                                </div>
                                                <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">{{ \Carbon\Carbon::parse($item->manufacturer_warranty_expiry)->format('d M Y') }}</div>
                                            @else
                                                <span class="text-slate-400 dark:text-slate-500">Not Applicable</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($item->service_warranty_status === 'active')
                                                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold crm-customer-pill border" style="background-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.1); color: var(--crm-accent, #2563eb); border-color: rgba(var(--crm-accent-rgb, 37, 99, 235), 0.25);">
                                                    <span>● Covered</span>
                                                    <span class="text-blue-600 dark:text-blue-400 font-medium">({{ $item->service_days_remaining }}d)</span>
                                                </div>
                                            @elseif($item->service_warranty_status === 'expired')
                                                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                    <span>Expired</span>
                                                </div>
                                            @else
                                                <span class="text-slate-400 dark:text-slate-500">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('portal.tickets.create', ['equipment_id' => $item->id]) }}" 
                                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>Report Issue</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($equipment, 'hasPages') && $equipment->hasPages())
                        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                            {{ $equipment->links() }}
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
