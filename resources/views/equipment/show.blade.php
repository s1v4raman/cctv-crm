<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('equipment.index') }}" class="back-link">&larr; Back</a>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mt-1">
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $equipment->equipment_name }}</h2>
                    @php
                        $eqStatusColor = '#16a34a';
                        if ($equipment->status === 'under_repair' || $equipment->status === 'faulty') $eqStatusColor = '#dc2626';
                    @endphp
                    <span class="text-sm font-bold" style="color: {{ $eqStatusColor }};">
                        {{ ucfirst(str_replace('_', ' ', $equipment->status ?? 'active')) }}
                    </span>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('equipment.edit', $equipment) }}" class="btn-secondary">
                        Edit Details
                    </a>
                    @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('equipment.destroy', $equipment) }}" data-confirm="Are you sure you want to delete this equipment record?" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="danger-link p-0 bg-transparent border-0 cursor-pointer text-xs ml-2">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-50 dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 rounded-xl border border-emerald-500/30 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-300 text-sm font-semibold flex items-center gap-3">
                    <span class="text-base font-bold">✓</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Warranty Timelines Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Manufacturer Warranty --}}
                @php $mfgStatus = $equipment->mfg_warranty_status; @endphp
                <div class="bg-white dark:bg-[#0F172A] border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Manufacturer Hardware Warranty</span>
                        @if($mfgStatus === 'active')
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Active</span>
                        @elseif($mfgStatus === 'expiring_soon')
                            <span class="text-xs font-bold text-amber-600 dark:text-amber-400">⚠️ Expiring Soon</span>
                        @elseif($mfgStatus === 'expired')
                            <span class="text-xs font-bold text-rose-600 dark:text-rose-400">Expired</span>
                        @else
                            <span class="text-xs text-slate-400">Not Configured</span>
                        @endif
                    </div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
                        @if($equipment->mfg_days_remaining !== null)
                            @if($equipment->mfg_days_remaining > 0)
                                <span class="text-emerald-600 dark:text-emerald-400">{{ $equipment->mfg_days_remaining }}</span> <span class="text-xs font-normal text-slate-500 dark:text-slate-400">days remaining</span>
                            @else
                                <span class="text-rose-600 dark:text-rose-400">Expired {{ abs($equipment->mfg_days_remaining) }} days ago</span>
                            @endif
                        @else
                            <span class="text-slate-500 text-sm">No Expiry Configured</span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        Expiry Date: <strong class="text-slate-900 dark:text-white">{{ $equipment->manufacturer_warranty_expiry?->format('d M Y') ?? 'N/A' }}</strong>
                    </div>
                </div>

                {{-- Service / Labor Warranty --}}
                @php $svcStatus = $equipment->service_warranty_status; @endphp
                <div class="bg-white dark:bg-[#0F172A] border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Installation &amp; Labor Service Warranty</span>
                        @if($svcStatus === 'active')
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Active</span>
                        @elseif($svcStatus === 'expiring_soon')
                            <span class="text-xs font-bold text-amber-600 dark:text-amber-400">⚠️ Expiring Soon</span>
                        @elseif($svcStatus === 'expired')
                            <span class="text-xs font-bold text-rose-600 dark:text-rose-400">Expired</span>
                        @else
                            <span class="text-xs text-slate-400">Not Configured</span>
                        @endif
                    </div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
                        @if($equipment->service_days_remaining !== null)
                            @if($equipment->service_days_remaining > 0)
                                <span class="text-blue-600 dark:text-blue-400">{{ $equipment->service_days_remaining }}</span> <span class="text-xs font-normal text-slate-500 dark:text-slate-400">days remaining</span>
                            @else
                                <span class="text-rose-600 dark:text-rose-400">Expired {{ abs($equipment->service_days_remaining) }} days ago</span>
                            @endif
                        @else
                            <span class="text-slate-500 text-sm">No Expiry Configured</span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        Expiry Date: <strong class="text-slate-900 dark:text-white">{{ $equipment->service_warranty_expiry?->format('d M Y') ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>

            {{-- Main Details Card --}}
            <div class="bg-white dark:bg-[#0F172A] border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
                    Equipment &amp; Placement Specifications
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Serial Number (S/N)</div>
                        <div class="text-sm font-bold text-slate-900 dark:text-white font-mono mt-1">{{ $equipment->serial_number }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">MAC / IP Address</div>
                        <div class="text-sm font-mono text-slate-900 dark:text-white mt-1">{{ $equipment->mac_address ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Channel / Location Tag</div>
                        <div class="text-sm font-semibold text-slate-900 dark:text-white mt-1">{{ $equipment->location_tag ?: 'Not specified' }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Operational Status</div>
                        <div class="mt-1">
                            <span class="{{ $equipment->status === 'active' ? 'status-won' : 'status-open' }}">
                                {{ ucfirst(str_replace('_', ' ', $equipment->status)) }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Commissioning Date</div>
                        <div class="text-sm font-semibold text-slate-900 dark:text-white mt-1">{{ $equipment->installation_date?->format('d M Y') ?? '—' }}</div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Linked Product Model</div>
                        <div class="text-sm font-bold text-slate-900 dark:text-white mt-1">{{ $equipment->product?->name ?? 'Custom / Uncatalogued' }}</div>
                        @if($equipment->product?->brand || $equipment->product?->model_no)
                            <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">{{ $equipment->product?->brand }} &bull; {{ $equipment->product?->model_no }}</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Customer & Job Link Card --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-[#0F172A] border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
                        Customer Premise Link
                    </h3>
                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider block">Client Name</span>
                            <a href="{{ route('leads.show', $equipment->lead) }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline text-sm mt-0.5 inline-block">
                                {{ $equipment->lead?->customer_name }}
                            </a>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider block">Phone</span>
                            <span class="font-mono text-slate-900 dark:text-white">{{ $equipment->lead?->phone }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider block">Installation Address</span>
                            <span class="text-slate-700 dark:text-slate-300 leading-relaxed">{{ $equipment->lead?->site_address ?: 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-[#0F172A] border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
                        Installation Job &amp; Origin
                    </h3>
                    @if($equipment->installationJob)
                        <div class="space-y-3 text-xs">
                            <div>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider block">Job Identifier</span>
                                <a href="{{ route('jobs.show', $equipment->installationJob) }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline text-sm font-mono mt-0.5 inline-block">
                                    {{ $equipment->installationJob->job_no }}
                                </a>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider block">Assigned Technician</span>
                                <span class="text-slate-900 dark:text-white font-semibold">{{ $equipment->installationJob->assignedTechnician?->name ?? 'Not assigned' }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider block">Job Status</span>
                                <span class="status-open mt-0.5 inline-block">
                                    {{ ucfirst(str_replace('_', ' ', $equipment->installationJob->status)) }}
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="py-6 text-center text-xs text-slate-500">
                            No specific job linked (manual entry or pre-existing installation).
                        </div>
                    @endif
                </div>
            </div>

            {{-- Notes --}}
            @if($equipment->notes)
            <div class="bg-white dark:bg-[#0F172A] border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2 pb-2 border-b border-slate-100 dark:border-slate-800">Engineering Notes</h3>
                <p class="text-xs text-slate-700 dark:text-slate-300 font-mono leading-relaxed whitespace-pre-line">{{ $equipment->notes }}</p>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
