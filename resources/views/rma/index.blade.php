<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold leading-tight text-white tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-[0_0_10px_#f59e0b]"></span>
                    RMA & Vendor Warranty Claims
                </h2>
                <p class="mt-1 text-sm text-slate-400 font-medium">Manage faulty hardware returns, supplier repairs, tracking challans & warranty replacements</p>
            </div>
            <a href="{{ route('rma.create') }}"
               class="crm-btn-primary btn-amber inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-xs font-extrabold shadow-md transition"
               style="background-color: var(--crm-accent, #2563eb);">
                <span>+</span> Raise New RMA Claim
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-[#060913] min-h-screen font-['Plus_Jakarta_Sans']">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-950/40 border border-emerald-500/30 text-emerald-300 text-sm font-medium flex items-center gap-2">
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 mb-6">
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-white/5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)]">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Claims</span>
                    <div class="text-2xl font-extrabold text-white mt-1 font-['Outfit']">{{ $stats['total'] }}</div>
                    <span class="text-[11px] text-slate-500">All recorded RMAs</span>
                </div>
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-white/5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)]">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400">Active In-Process</span>
                    <div class="text-2xl font-extrabold text-amber-400 mt-1 font-['Outfit']">{{ $stats['active_in_process'] }}</div>
                    <span class="text-[11px] text-slate-500">Draft / In transit</span>
                </div>
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-white/5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)]">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-sky-400">Shipped to Vendors</span>
                    <div class="text-2xl font-extrabold text-sky-400 mt-1 font-['Outfit']">{{ $stats['shipped'] }}</div>
                    <span class="text-[11px] text-slate-500">In courier transit</span>
                </div>
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-white/5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)]">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-purple-400">In Vendor Repair</span>
                    <div class="text-2xl font-extrabold text-purple-400 mt-1 font-['Outfit']">{{ $stats['with_vendor'] }}</div>
                    <span class="text-[11px] text-slate-500">Under diagnosis</span>
                </div>
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-white/5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)]">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">Resolved / Closed</span>
                    <div class="text-2xl font-extrabold text-emerald-400 mt-1 font-['Outfit']">{{ $stats['resolved'] }}</div>
                    <span class="text-[11px] text-slate-500">Replaced & deployed</span>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-[#0f172a] rounded-2xl p-4 mb-6 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5">
                <form method="GET" action="{{ route('rma.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-300 mb-1.5">Search Keyword</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="RMA #, Serial #, Vendor AWB, Customer..." class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl bg-[#060913] border-slate-700 text-slate-200 focus:border-amber-400 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-1.5">Supplier / Vendor</label>
                        <select name="supplier_id" class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl bg-[#060913] border-slate-700 text-slate-200 focus:border-amber-400 focus:ring-amber-400">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-1.5">Status</label>
                        <select name="status" class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl bg-[#060913] border-slate-700 text-slate-200 focus:border-amber-400 focus:ring-amber-400">
                            <option value="">All Statuses</option>
                            @foreach(\App\Models\RmaClaim::statusLabels() as $key => $label)
                                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn-amber min-h-[44px] px-5 py-2.5 text-sm font-bold rounded-xl transition" style="background-color: var(--crm-accent, #2563eb);">
                            Filter
                        </button>
                        @if(request()->anyFilled(['search', 'status', 'supplier_id', 'warranty']))
                            <a href="{{ route('rma.index') }}" class="btn-secondary min-h-[44px] px-4 py-2.5 text-sm font-semibold rounded-xl">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-[#0f172a] rounded-2xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5 overflow-hidden">
                <table class="min-w-full divide-y divide-white/5 text-xs">
                    <thead class="bg-[#0b1120]">
                        <tr>
                            <th class="px-4 py-3.5 text-left font-bold text-slate-400 uppercase tracking-wider text-[11px]">RMA Number</th>
                            <th class="px-4 py-3.5 text-left font-bold text-slate-400 uppercase tracking-wider text-[11px]">Vendor / Supplier</th>
                            <th class="px-4 py-3.5 text-left font-bold text-slate-400 uppercase tracking-wider text-[11px]">Faulty Item & S/N</th>
                            <th class="px-4 py-3.5 text-left font-bold text-slate-400 uppercase tracking-wider text-[11px]">Customer / Site</th>
                            <th class="px-4 py-3.5 text-left font-bold text-slate-400 uppercase tracking-wider text-[11px]">Fault Category</th>
                            <th class="px-4 py-3.5 text-left font-bold text-slate-400 uppercase tracking-wider text-[11px]">Status</th>
                            <th class="px-4 py-3.5 text-right font-bold text-slate-400 uppercase tracking-wider text-[11px]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 bg-[#0f172a]">
                        @forelse($claims as $claim)
                            <tr class="hover:bg-slate-800/50 transition">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <a href="{{ route('rma.show', $claim) }}" class="font-bold text-sky-400 hover:underline">
                                        {{ $claim->rma_no }}
                                    </a>
                                    <div class="text-[10px] text-slate-500">{{ $claim->created_at->format('d M Y') }}</div>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="font-bold text-slate-200">{{ $claim->supplier->name }}</div>
                                    @if($claim->vendor_rma_ref)
                                        <div class="text-[10px] text-slate-400 font-mono">Ref: {{ $claim->vendor_rma_ref }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-slate-200">{{ $claim->product?->name ?? 'Hardware Asset' }}</div>
                                    <div class="font-mono text-[11px] text-amber-400 font-bold">S/N: {{ $claim->faulty_serial_number }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($claim->lead)
                                        <div class="font-semibold text-slate-200">{{ $claim->lead->customer_name }}</div>
                                        <div class="text-[11px] text-slate-400 truncate max-w-xs">{{ $claim->lead->site_address ?? $claim->lead->phone }}</div>
                                    @else
                                        <span class="text-slate-500 italic">Warehouse / Stock Item</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="text-slate-300 font-medium">{{ $claim->fault_category_label }}</span>
                                    <div class="text-[10px] uppercase font-bold {{ $claim->warranty_status_at_claim === 'under_warranty' ? 'text-emerald-400' : 'text-rose-400' }}">
                                        {{ str_replace('_', ' ', $claim->warranty_status_at_claim) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $claim->badge_class }}">
                                        {{ $claim->status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-right font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('rma.show', $claim) }}" class="px-2.5 py-1 rounded-lg bg-sky-500/10 border border-sky-500/30 text-sky-400 hover:bg-sky-500/20 text-xs font-semibold" title="View RMA Timeline">
                                            Timeline &rarr;
                                        </a>
                                        <a href="{{ route('rma.dispatch-pdf', $claim) }}" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-white/5" title="Download Vendor Dispatch Challan">
                                            📄
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                    No RMA claims found matching your filter criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $claims->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
