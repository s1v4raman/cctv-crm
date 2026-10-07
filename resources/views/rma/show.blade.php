<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black tracking-wider text-white uppercase font-['Outfit']">🛡️ {{ $rma->rma_no }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold uppercase border {{ $rma->badge_class }}">
                        {{ $rma->status_label }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase {{ $rma->warranty_status_at_claim === 'under_warranty' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30' }}">
                        {{ str_replace('_', ' ', $rma->warranty_status_at_claim) }}
                    </span>
                </div>
                <p class="text-xs text-slate-400 font-mono mt-1">Created on {{ $rma->created_at->format('d M Y, h:i A') }} by {{ $rma->creator->name }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('rma.dispatch-pdf', $rma) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 text-xs font-black shadow-lg shadow-amber-500/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Vendor Challan PDF
                </a>
                <a href="{{ route('rma.edit', $rma) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-800 text-slate-200 hover:bg-slate-700 text-xs font-bold transition">
                    Edit
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

            {{-- Stepper Banner --}}
            <div class="bg-[#0F172A] border border-white/10 rounded-2xl p-5 mb-6 shadow-xl">
                <div class="flex items-center justify-between overflow-x-auto text-xs font-bold gap-3 font-mono">
                    <div class="flex items-center gap-2 {{ in_array($rma->status, ['draft', 'shipped_to_vendor', 'in_vendor_repair', 'replaced', 'repaired', 'credit_note', 'closed']) ? 'text-amber-400' : 'text-slate-500' }}">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black {{ in_array($rma->status, ['draft', 'shipped_to_vendor', 'in_vendor_repair', 'replaced', 'repaired', 'credit_note', 'closed']) ? 'bg-amber-400 text-slate-950' : 'bg-slate-800 text-slate-400' }}">1</span>
                        <span>RMA Registered</span>
                    </div>
                    <span class="text-slate-600">&rarr;</span>
                    <div class="flex items-center gap-2 {{ in_array($rma->status, ['shipped_to_vendor', 'in_vendor_repair', 'replaced', 'repaired', 'credit_note', 'closed']) ? 'text-amber-400' : 'text-slate-500' }}">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black {{ in_array($rma->status, ['shipped_to_vendor', 'in_vendor_repair', 'replaced', 'repaired', 'credit_note', 'closed']) ? 'bg-amber-400 text-slate-950' : 'bg-slate-800 text-slate-400' }}">2</span>
                        <span>Shipped to Vendor</span>
                    </div>
                    <span class="text-slate-600">&rarr;</span>
                    <div class="flex items-center gap-2 {{ in_array($rma->status, ['in_vendor_repair', 'replaced', 'repaired', 'credit_note', 'closed']) ? 'text-amber-400' : 'text-slate-500' }}">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black {{ in_array($rma->status, ['in_vendor_repair', 'replaced', 'repaired', 'credit_note', 'closed']) ? 'bg-amber-400 text-slate-950' : 'bg-slate-800 text-slate-400' }}">3</span>
                        <span>Vendor Diagnosis</span>
                    </div>
                    <span class="text-slate-600">&rarr;</span>
                    <div class="flex items-center gap-2 {{ in_array($rma->status, ['replaced', 'repaired', 'credit_note', 'closed']) ? 'text-emerald-400' : 'text-slate-500' }}">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black {{ in_array($rma->status, ['replaced', 'repaired', 'credit_note', 'closed']) ? 'bg-emerald-400 text-slate-950' : 'bg-slate-800 text-slate-400' }}">4</span>
                        <span>Resolved / Replaced</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- LEFT COLUMN: Details & Fault Summary (2 cols) --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Faulty Equipment & Customer Card --}}
                    <div class="bg-[#0F172A] border border-white/10 rounded-2xl p-6 shadow-xl">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 font-['Outfit'] mb-4 pb-3 border-b border-white/5">
                            Faulty Hardware Asset Details
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs mb-4">
                            <div>
                                <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest block">Product Model</span>
                                <strong class="text-white text-sm font-['Outfit'] mt-0.5 block">{{ $rma->product?->name ?? 'Hardware Asset' }}</strong>
                                <span class="text-slate-400 font-mono text-[11px] block">{{ $rma->product?->model_no ?? 'Model N/A' }}</span>
                            </div>

                            <div>
                                <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest block">Faulty Serial Number</span>
                                <span class="font-mono font-bold text-sm text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-lg border border-amber-500/20 inline-block mt-0.5">
                                    {{ $rma->faulty_serial_number }}
                                </span>
                                @if($rma->faulty_mac_address)
                                    <div class="text-[11px] text-sky-400 font-mono mt-1">MAC: {{ $rma->faulty_mac_address }}</div>
                                @endif
                            </div>

                            <div>
                                <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest block">Customer / Organization</span>
                                @if($rma->lead)
                                    <strong class="text-white block text-sm mt-0.5">{{ $rma->lead->customer_name }}</strong>
                                    <span class="text-slate-400 font-mono text-[11px] block">{{ $rma->lead->site_address ?? $rma->lead->address ?? 'N/A' }}</span>
                                @else
                                    <span class="text-slate-500 italic">Warehouse / Unassigned Stock</span>
                                @endif
                            </div>

                            <div>
                                <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest block">Target Vendor / Supplier</span>
                                <strong class="text-white block text-sm mt-0.5">{{ $rma->supplier->name }}</strong>
                                <span class="text-slate-400 font-mono text-[11px] block">{{ $rma->supplier->company_name ?: $rma->supplier->city }} &bull; {{ $rma->supplier->phone }}</span>
                            </div>
                        </div>

                        @if($rma->installedEquipment)
                            <div class="p-3.5 bg-[#060913] rounded-xl border border-white/10 text-xs flex items-center justify-between">
                                <span class="text-slate-300 font-mono">Linked Equipment: <strong class="text-amber-400">{{ $rma->installedEquipment->equipment_name }}</strong></span>
                                <a href="{{ route('equipment.show', $rma->installedEquipment) }}" class="text-sky-400 hover:text-sky-300 font-bold font-mono">
                                    View Asset &rarr;
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Failure Description Card --}}
                    <div class="bg-[#0F172A] border border-white/10 rounded-2xl p-6 shadow-xl">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 font-['Outfit'] mb-3 pb-3 border-b border-white/5">
                            Failure Category & Diagnostic Notes
                        </h3>
                        <div class="mb-3">
                            <span class="inline-block px-2.5 py-1 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20 text-xs font-mono font-bold">
                                {{ $rma->fault_category_label }}
                            </span>
                        </div>
                        <div class="p-4 rounded-xl bg-[#060913] border border-white/10 text-xs text-slate-300 font-mono leading-relaxed">
                            {{ $rma->issue_description }}
                        </div>
                    </div>

                    {{-- Resolution Details Card --}}
                    @if(in_array($rma->status, ['replaced', 'repaired', 'credit_note', 'rejected', 'closed']))
                        <div class="bg-[#0F172A] border border-emerald-500/30 rounded-2xl p-6 shadow-xl">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-emerald-400 font-['Outfit'] mb-3 pb-3 border-b border-emerald-500/20 flex items-center justify-between">
                                <span>✓ Vendor Resolution & Inward Details</span>
                                <span class="text-xs font-mono text-slate-400">Received: {{ $rma->received_from_vendor_date?->format('d M Y') ?? '—' }}</span>
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs mb-3">
                                <div>
                                    <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest block">Resolution Type</span>
                                    <strong class="text-white text-sm capitalize font-['Outfit'] mt-0.5 block">{{ str_replace('_', ' ', $rma->resolution_type) }}</strong>
                                </div>

                                @if($rma->replacement_serial_number)
                                    <div>
                                        <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest block">New Replacement Serial Number</span>
                                        <span class="font-mono font-bold text-sm text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-lg border border-emerald-500/30 inline-block mt-0.5">
                                            {{ $rma->replacement_serial_number }}
                                        </span>
                                        @if($rma->replacement_mac_address)
                                            <div class="text-[11px] text-sky-400 font-mono mt-1">MAC: {{ $rma->replacement_mac_address }}</div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            @if($rma->vendor_repair_notes)
                                <div class="mt-3 text-xs">
                                    <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest block mb-1">Vendor Service Notes</span>
                                    <div class="p-3 bg-[#060913] rounded-xl border border-white/10 text-slate-300 font-mono">
                                        {{ $rma->vendor_repair_notes }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                </div>

                {{-- RIGHT COLUMN: Actions & Tracking (1 col) --}}
                <div class="space-y-6">

                    {{-- Action Card: Dispatch to Vendor --}}
                    @if($rma->status === 'draft')
                        <div class="bg-[#0F172A] border border-white/10 rounded-2xl p-6 shadow-xl">
                            <h4 class="text-sm font-bold uppercase tracking-wider text-white mb-2 flex items-center gap-2 font-['Outfit']">
                                <span>🚚</span> Dispatch to Vendor
                            </h4>
                            <p class="text-xs text-slate-400 mb-4">Ship item to supplier service center with courier details.</p>

                            <form method="POST" action="{{ route('rma.dispatch', $rma) }}" class="space-y-3.5">
                                @csrf
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Shipping Carrier <span class="text-amber-400">*</span></label>
                                    <input type="text" name="shipping_courier" required placeholder="e.g. DTDC, Blue Dart" class="w-full bg-[#060913] border border-slate-700 rounded-xl px-3.5 py-2.5 min-h-[44px] text-sm text-white focus:border-amber-400 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Consignment / AWB No.</label>
                                    <input type="text" name="tracking_number" placeholder="e.g. D19283741" class="w-full bg-[#060913] border border-slate-700 rounded-xl px-3.5 py-2.5 min-h-[44px] text-sm text-white font-mono focus:border-amber-400 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Dispatched Date <span class="text-amber-400">*</span></label>
                                    <input type="date" name="dispatched_date" value="{{ now()->format('Y-m-d') }}" required class="w-full bg-[#060913] border border-slate-700 rounded-xl px-3.5 py-2.5 min-h-[44px] text-sm text-white font-mono focus:border-amber-400 focus:outline-none">
                                </div>

                                <button type="submit" class="w-full min-h-[44px] py-2.5 px-5 bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-400 hover:to-sky-500 text-white rounded-xl text-sm font-bold uppercase tracking-wider shadow-lg shadow-sky-500/20 transition">
                                    Mark Dispatched & Save AWB &rarr;
                                </button>
                            </form>
                        </div>
                    @endif

                    {{-- Action Card: Record Resolution --}}
                    @if(in_array($rma->status, ['shipped_to_vendor', 'in_vendor_repair']))
                        <div class="bg-[#0F172A] border border-white/10 rounded-2xl p-6 shadow-xl">
                            <h4 class="text-sm font-bold uppercase tracking-wider text-white mb-2 flex items-center gap-2 font-['Outfit']">
                                <span>🔄</span> Record Vendor Resolution
                            </h4>
                            <p class="text-xs text-slate-400 mb-4">Inward replacement unit or record repaired unit from supplier.</p>

                            <form method="POST" action="{{ route('rma.resolution', $rma) }}" class="space-y-3.5">
                                @csrf
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Resolution Outcome <span class="text-amber-400">*</span></label>
                                    <select name="resolution_type" required class="w-full bg-[#060913] border border-slate-700 rounded-xl px-3.5 py-2.5 min-h-[44px] text-sm text-white focus:border-amber-400 focus:outline-none" onchange="toggleReplacementFields(this.value)">
                                        <option value="replacement">✨ Brand New Replacement (New S/N)</option>
                                        <option value="repaired_unit">🔧 Original Unit Repaired & Returned</option>
                                        <option value="credit_note">💰 Vendor Credit Note / Refund</option>
                                        <option value="rejected_damage">❌ Claim Rejected (Void)</option>
                                    </select>
                                </div>

                                <div id="replacementSerialNumberDiv">
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">New Serial Number (S/N) <span class="text-amber-400">*</span></label>
                                    <input type="text" name="replacement_serial_number" placeholder="Enter new serial number" class="w-full bg-[#060913] border border-slate-700 rounded-xl px-3.5 py-2.5 min-h-[44px] text-sm text-white font-mono focus:border-amber-400 focus:outline-none">
                                </div>

                                <div id="replacementMacDiv">
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">New MAC Address</label>
                                    <input type="text" name="replacement_mac_address" placeholder="e.g. 54:C8:01:B3:99:11" class="w-full bg-[#060913] border border-slate-700 rounded-xl px-3.5 py-2.5 min-h-[44px] text-sm text-white font-mono focus:border-amber-400 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Received Date <span class="text-amber-400">*</span></label>
                                    <input type="date" name="received_from_vendor_date" value="{{ now()->format('Y-m-d') }}" required class="w-full bg-[#060913] border border-slate-700 rounded-xl px-3.5 py-2.5 min-h-[44px] text-sm text-white font-mono focus:border-amber-400 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Resolution Notes</label>
                                    <textarea name="vendor_repair_notes" rows="2" placeholder="e.g. Replaced mainboard, tested OK..." class="w-full bg-[#060913] border border-slate-700 rounded-xl px-3.5 py-2.5 min-h-[44px] text-sm text-white focus:border-amber-400 focus:outline-none"></textarea>
                                </div>

                                <button type="submit" class="w-full min-h-[44px] py-2.5 px-5 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-white rounded-xl text-sm font-bold uppercase tracking-wider shadow-lg shadow-emerald-500/20 transition">
                                    Inward & Update Equipment &rarr;
                                </button>
                            </form>
                        </div>
                    @endif

                    {{-- Shipping Logistics Info Card --}}
                    @if($rma->dispatched_date)
                        <div class="bg-[#0F172A] border border-white/10 rounded-2xl p-6 shadow-xl">
                            <h4 class="text-sm font-bold uppercase tracking-wider text-slate-300 font-['Outfit'] mb-3 pb-2 border-b border-white/5">
                                Shipping & Vendor Tracking
                            </h4>
                            <div class="space-y-2.5 text-xs font-mono">
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Courier:</span>
                                    <strong class="text-white">{{ $rma->shipping_courier }}</strong>
                                </div>
                                @if($rma->tracking_number)
                                    <div class="flex justify-between">
                                        <span class="text-slate-400">AWB No:</span>
                                        <strong class="text-amber-400">{{ $rma->tracking_number }}</strong>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Dispatched:</span>
                                    <span class="text-slate-200">{{ $rma->dispatched_date->format('d M Y') }}</span>
                                </div>
                                @if($rma->turnaround_days !== null)
                                    <div class="flex justify-between">
                                        <span class="text-slate-400">TAT:</span>
                                        <span class="font-bold text-emerald-400">{{ $rma->turnaround_days }} days</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Audit Trail Timeline --}}
                    <div class="bg-[#0F172A] border border-white/10 rounded-2xl p-6 shadow-xl">
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-300 font-['Outfit'] mb-3 pb-2 border-b border-white/5">
                            RMA Audit History Log
                        </h4>
                        <div class="space-y-3 text-xs">
                            @forelse($rma->statusLogs as $log)
                                <div class="flex items-start gap-2.5 pb-2.5 border-b border-white/5 last:border-0 last:pb-0">
                                    <span class="w-2 h-2 rounded-full bg-amber-400 mt-1.5 flex-shrink-0"></span>
                                    <div>
                                        <div class="font-bold text-white capitalize font-mono">{{ str_replace('_', ' ', $log->to_status) }}</div>
                                        @if($log->notes)
                                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $log->notes }}</div>
                                        @endif
                                        <div class="text-[10px] font-mono text-slate-500 mt-0.5">{{ $log->created_at->format('d M, h:i A') }} &bull; {{ $log->changedBy->name }}</div>
                                    </div>
                                </div>
                            @empty
                                <span class="text-slate-500 text-xs font-mono">No status history logged yet.</span>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>

    <script>
        function toggleReplacementFields(val) {
            const snDiv = document.getElementById('replacementSerialNumberDiv');
            const macDiv = document.getElementById('replacementMacDiv');
            if (val === 'replacement') {
                snDiv.style.display = 'block';
                macDiv.style.display = 'block';
            } else {
                snDiv.style.display = 'none';
                macDiv.style.display = 'none';
            }
        }
    </script>
</x-app-layout>
