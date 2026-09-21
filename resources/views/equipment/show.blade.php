<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <h2 class="text-2xl font-black tracking-wider text-white uppercase font-['Outfit']">{{ $equipment->equipment_name }}</h2>
                </div>
                <p class="mt-1 text-xs text-slate-400 flex items-center gap-2 font-mono">
                    <span>Serial: <strong class="text-amber-400">{{ $equipment->serial_number }}</strong></span>
                    @if($equipment->mac_address)
                        <span>&bull;</span>
                        <span>MAC: <strong class="text-sky-400">{{ $equipment->mac_address }}</strong></span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('rma.create', ['equipment_id' => $equipment->id]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 text-xs font-black shadow-lg shadow-amber-500/20 transition">
                    ⚡ Raise RMA Claim
                </a>
                <a href="{{ route('equipment.edit', $equipment) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-800 text-slate-200 hover:bg-slate-700 text-xs font-bold transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Details
                </a>
                <a href="{{ route('equipment.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-700/60 bg-slate-800/40 text-slate-400 hover:text-white text-xs font-semibold transition">
                    &larr; Back to Registry
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#060913] min-h-screen text-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-6 p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-sm font-semibold flex items-center gap-3">
                    <span class="text-base">✓</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Warranty Timelines Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                {{-- Manufacturer Warranty --}}
                @php $mfgStatus = $equipment->mfg_warranty_status; @endphp
                <div class="bg-[#0F172A] border {{ $mfgStatus === 'active' ? 'border-emerald-500/40 bg-gradient-to-br from-[#0F172A] to-emerald-950/20' : ($mfgStatus === 'expiring_soon' ? 'border-amber-500/40 bg-gradient-to-br from-[#0F172A] to-amber-950/20' : ($mfgStatus === 'expired' ? 'border-rose-500/40 bg-gradient-to-br from-[#0F172A] to-rose-950/20' : 'border-white/10')) }} rounded-2xl p-6 shadow-xl relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-mono uppercase tracking-widest text-slate-400">Manufacturer Hardware Warranty</span>
                        @if($mfgStatus === 'active')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">Active</span>
                        @elseif($mfgStatus === 'expiring_soon')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-amber-500/10 border border-amber-500/30 text-amber-400">⚠️ Expiring Soon</span>
                        @elseif($mfgStatus === 'expired')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-rose-500/10 border border-rose-500/30 text-rose-400">Expired</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700">Not Configured</span>
                        @endif
                    </div>
                    <div class="text-3xl font-black text-white font-['Outfit'] mb-2">
                        @if($equipment->mfg_days_remaining !== null)
                            @if($equipment->mfg_days_remaining > 0)
                                <span class="text-emerald-400">{{ $equipment->mfg_days_remaining }}</span> <span class="text-xs font-mono font-normal text-slate-400">days remaining</span>
                            @else
                                <span class="text-rose-400">Expired {{ abs($equipment->mfg_days_remaining) }} days ago</span>
                            @endif
                        @else
                            <span class="text-slate-500 text-lg">No Expiry Configured</span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-400 font-mono">
                        Expiry Date: <strong class="text-slate-200">{{ $equipment->manufacturer_warranty_expiry?->format('d M Y') ?? 'N/A' }}</strong>
                    </div>
                </div>

                {{-- Service / Labor Warranty --}}
                @php $svcStatus = $equipment->service_warranty_status; @endphp
                <div class="bg-[#0F172A] border {{ $svcStatus === 'active' ? 'border-emerald-500/40 bg-gradient-to-br from-[#0F172A] to-emerald-950/20' : ($svcStatus === 'expiring_soon' ? 'border-amber-500/40 bg-gradient-to-br from-[#0F172A] to-amber-950/20' : ($svcStatus === 'expired' ? 'border-rose-500/40 bg-gradient-to-br from-[#0F172A] to-rose-950/20' : 'border-white/10')) }} rounded-2xl p-6 shadow-xl relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-mono uppercase tracking-widest text-slate-400">Installation & Labor Service Warranty</span>
                        @if($svcStatus === 'active')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">Active</span>
                        @elseif($svcStatus === 'expiring_soon')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-amber-500/10 border border-amber-500/30 text-amber-400">⚠️ Expiring Soon</span>
                        @elseif($svcStatus === 'expired')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-rose-500/10 border border-rose-500/30 text-rose-400">Expired</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700">Not Configured</span>
                        @endif
                    </div>
                    <div class="text-3xl font-black text-white font-['Outfit'] mb-2">
                        @if($equipment->service_days_remaining !== null)
                            @if($equipment->service_days_remaining > 0)
                                <span class="text-sky-400">{{ $equipment->service_days_remaining }}</span> <span class="text-xs font-mono font-normal text-slate-400">days remaining</span>
                            @else
                                <span class="text-rose-400">Expired {{ abs($equipment->service_days_remaining) }} days ago</span>
                            @endif
                        @else
                            <span class="text-slate-500 text-lg">No Expiry Configured</span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-400 font-mono">
                        Expiry Date: <strong class="text-slate-200">{{ $equipment->service_warranty_expiry?->format('d M Y') ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>

            {{-- Main Details Card --}}
            <div class="bg-[#0F172A] border border-white/10 rounded-2xl p-6 shadow-xl mb-6">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 font-['Outfit'] mb-4 pb-3 border-b border-white/5">
                    Equipment & Placement Specifications
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-400">Serial Number (S/N)</div>
                        <div class="text-base font-bold text-amber-400 font-mono mt-1">{{ $equipment->serial_number }}</div>
                    </div>

                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-400">MAC / IP Address</div>
                        <div class="text-sm font-mono text-sky-400 mt-1">{{ $equipment->mac_address ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-400">Channel / Location Tag</div>
                        <div class="text-sm font-semibold text-white mt-1">{{ $equipment->location_tag ?: 'Not specified' }}</div>
                    </div>

                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-400">Operational Status</div>
                        <div class="mt-1">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider {{ $equipment->status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $equipment->status === 'active' ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                                {{ ucfirst(str_replace('_', ' ', $equipment->status)) }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-400">Commissioning Date</div>
                        <div class="text-sm font-semibold text-slate-300 mt-1">{{ $equipment->installation_date?->format('d M Y') ?? '—' }}</div>
                    </div>

                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-400">Linked Product Model</div>
                        <div class="text-sm font-bold text-white mt-1">{{ $equipment->product?->name ?? 'Custom / Uncatalogued' }}</div>
                        @if($equipment->product?->brand || $equipment->product?->model_no)
                            <div class="text-xs text-slate-400 font-mono mt-0.5">{{ $equipment->product?->brand }} &bull; {{ $equipment->product?->model_no }}</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Customer & Job Link Card --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-[#0F172A] border border-white/10 rounded-2xl p-6 shadow-xl">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 font-['Outfit'] mb-3 pb-3 border-b border-white/5">
                        Customer Premise Link
                    </h3>
                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest block">Client Name</span>
                            <a href="{{ route('leads.show', $equipment->lead) }}" class="font-bold text-amber-400 hover:text-amber-300 text-sm mt-0.5 inline-block">
                                {{ $equipment->lead?->customer_name }}
                            </a>
                        </div>
                        <div>
                            <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest block">Phone</span>
                            <span class="font-mono text-slate-200">{{ $equipment->lead?->phone }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest block">Installation Address</span>
                            <span class="text-slate-300 font-mono leading-relaxed">{{ $equipment->lead?->site_address ?: 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-[#0F172A] border border-white/10 rounded-2xl p-6 shadow-xl">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 font-['Outfit'] mb-3 pb-3 border-b border-white/5">
                        Installation Job & Origin
                    </h3>
                    @if($equipment->installationJob)
                        <div class="space-y-3 text-xs">
                            <div>
                                <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest block">Job Identifier</span>
                                <a href="{{ route('jobs.show', $equipment->installationJob) }}" class="font-bold text-sky-400 hover:text-sky-300 text-sm font-mono mt-0.5 inline-block">
                                    {{ $equipment->installationJob->job_no }}
                                </a>
                            </div>
                            <div>
                                <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest block">Assigned Technician</span>
                                <span class="text-slate-200 font-semibold">{{ $equipment->installationJob->assignedTechnician?->name ?? 'Not assigned' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest block">Job Status</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-slate-800 text-slate-300 border border-slate-700 mt-0.5">
                                    {{ ucfirst(str_replace('_', ' ', $equipment->installationJob->status)) }}
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="py-6 text-center text-xs text-slate-500 font-mono">
                            No specific job linked (manual entry or pre-existing installation).
                        </div>
                    @endif
                </div>
            </div>

            {{-- Notes --}}
            @if($equipment->notes)
            <div class="bg-[#0F172A] border border-white/10 rounded-2xl p-6 shadow-xl">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 font-['Outfit'] mb-2 pb-2 border-b border-white/5">Engineering Notes</h3>
                <p class="text-xs text-slate-300 font-mono leading-relaxed whitespace-pre-line">{{ $equipment->notes }}</p>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
