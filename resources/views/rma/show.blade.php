<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xl font-bold tracking-tight text-slate-900 dark:text-white uppercase">{{ $rma->rma_no }}</span>
                    <span class="text-xs font-bold uppercase text-blue-600">
                        {{ $rma->status_label }}
                    </span>
                    <span class="text-xs font-bold uppercase {{ $rma->warranty_status_at_claim === 'under_warranty' ? 'text-emerald-700' : 'text-rose-600' }}">
                        {{ str_replace('_', ' ', $rma->warranty_status_at_claim) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Created on {{ $rma->created_at->format('d M Y, h:i A') }} by {{ $rma->creator->name }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('rma.dispatch-pdf', $rma) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[4px] bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-colors">
                    Vendor Challan PDF
                </a>
                <a href="{{ route('rma.edit', $rma) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[4px] border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 text-xs font-medium transition-colors">
                    Edit
                </a>
                <a href="{{ route('rma.index') }}"
                   class="crm-back-link text-xs font-semibold text-blue-600 hover:underline">
                    &larr; Back to RMA List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f8fafc] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-5 p-3 rounded-[4px] border border-emerald-200 bg-emerald-50 text-emerald-800 text-xs font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Stepper Banner --}}
            <div class="bg-white dark:bg-[#0f172a] border border-slate-300 dark:border-slate-800 rounded-[4px] p-4 mb-5">
                <div class="flex items-center justify-between overflow-x-auto text-xs font-bold gap-3">
                    <div class="flex items-center gap-2 {{ in_array($rma->status, ['draft', 'shipped_to_vendor', 'in_vendor_repair', 'replaced', 'repaired', 'credit_note', 'closed']) ? 'text-blue-600' : 'text-slate-400' }}">
                        <span>1. RMA Registered</span>
                    </div>
                    <span class="text-slate-400">&rarr;</span>
                    <div class="flex items-center gap-2 {{ in_array($rma->status, ['shipped_to_vendor', 'in_vendor_repair', 'replaced', 'repaired', 'credit_note', 'closed']) ? 'text-blue-600' : 'text-slate-400' }}">
                        <span>2. Shipped to Vendor</span>
                    </div>
                    <span class="text-slate-400">&rarr;</span>
                    <div class="flex items-center gap-2 {{ in_array($rma->status, ['in_vendor_repair', 'replaced', 'repaired', 'credit_note', 'closed']) ? 'text-blue-600' : 'text-slate-400' }}">
                        <span>3. Vendor Diagnosis</span>
                    </div>
                    <span class="text-slate-400">&rarr;</span>
                    <div class="flex items-center gap-2 {{ in_array($rma->status, ['replaced', 'repaired', 'credit_note', 'closed']) ? 'text-emerald-700' : 'text-slate-400' }}">
                        <span>4. Resolved / Replaced</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                {{-- LEFT COLUMN: Details & Fault Summary (2 cols) --}}
                <div class="lg:col-span-2 space-y-5">

                    {{-- Faulty Equipment & Customer Card --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-300 dark:border-slate-800 rounded-[4px] p-5">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3 pb-2 border-b border-slate-200 dark:border-slate-800">
                            Faulty Hardware Asset Details
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs mb-3">
                            <div>
                                <span class="text-slate-500 block">Product Model</span>
                                <strong class="text-slate-900 dark:text-white text-sm block">{{ $rma->product?->name ?? 'Hardware Asset' }}</strong>
                                <span class="text-slate-500 font-mono block">{{ $rma->product?->model_no ?? 'Model N/A' }}</span>
                            </div>

                            <div>
                                <span class="text-slate-500 block">Faulty Serial Number</span>
                                <span class="font-mono font-bold text-sm text-slate-900 dark:text-white block">
                                    {{ $rma->faulty_serial_number }}
                                </span>
                                @if($rma->faulty_mac_address)
                                    <div class="text-[11px] text-slate-500 font-mono mt-0.5">MAC: {{ $rma->faulty_mac_address }}</div>
                                @endif
                            </div>

                            <div>
                                <span class="text-slate-500 block">Customer / Organization</span>
                                @if($rma->lead)
                                    <strong class="text-slate-900 dark:text-white block text-sm">{{ $rma->lead->customer_name }}</strong>
                                    <span class="text-slate-500 block">{{ $rma->lead->site_address ?? $rma->lead->address ?? 'N/A' }}</span>
                                @else
                                    <span class="text-slate-500 italic">Warehouse / Unassigned Stock</span>
                                @endif
                            </div>

                            <div>
                                <span class="text-slate-500 block">Target Vendor / Supplier</span>
                                <strong class="text-slate-900 dark:text-white block text-sm">{{ $rma->supplier->name }}</strong>
                                <span class="text-slate-500 block">{{ $rma->supplier->company_name ?: $rma->supplier->city }} &bull; {{ $rma->supplier->phone }}</span>
                            </div>
                        </div>

                        @if($rma->installedEquipment)
                            <div class="p-3 bg-slate-50 dark:bg-[#060913] rounded-[4px] border border-slate-200 dark:border-slate-800 text-xs flex items-center justify-between">
                                <span class="text-slate-700 dark:text-slate-300">Linked Equipment: <strong class="text-slate-900 dark:text-white">{{ $rma->installedEquipment->equipment_name }}</strong></span>
                                <a href="{{ route('equipment.show', $rma->installedEquipment) }}" class="text-blue-600 hover:underline font-bold">
                                    View Asset &rarr;
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Failure Description Card --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-300 dark:border-slate-800 rounded-[4px] p-5">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2 pb-2 border-b border-slate-200 dark:border-slate-800">
                            Failure Category &amp; Diagnostic Notes
                        </h3>
                        <div class="mb-2">
                            <span class="text-xs font-bold text-blue-600">
                                {{ $rma->fault_category_label }}
                            </span>
                        </div>
                        <div class="p-3 rounded-[4px] bg-slate-50 dark:bg-[#060913] border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 leading-relaxed">
                            {{ $rma->issue_description }}
                        </div>
                    </div>

                    {{-- Resolution Details Card --}}
                    @if(in_array($rma->status, ['replaced', 'repaired', 'credit_note', 'rejected', 'closed']))
                        <div class="bg-white dark:bg-[#0f172a] border border-slate-300 dark:border-slate-800 rounded-[4px] p-5">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3 pb-2 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                                <span>Vendor Resolution &amp; Inward Details</span>
                                <span class="text-xs text-slate-500">Received: {{ $rma->received_from_vendor_date?->format('d M Y') ?? '—' }}</span>
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs mb-3">
                                <div>
                                    <span class="text-slate-500 block">Resolution Type</span>
                                    <strong class="text-slate-900 dark:text-white text-sm capitalize block">{{ str_replace('_', ' ', $rma->resolution_type) }}</strong>
                                </div>

                                @if($rma->replacement_serial_number)
                                    <div>
                                        <span class="text-slate-500 block">New Replacement Serial Number</span>
                                        <span class="font-mono font-bold text-sm text-emerald-700 block">
                                            {{ $rma->replacement_serial_number }}
                                        </span>
                                        @if($rma->replacement_mac_address)
                                            <div class="text-[11px] text-slate-500 font-mono mt-0.5">MAC: {{ $rma->replacement_mac_address }}</div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            @if($rma->vendor_repair_notes)
                                <div class="mt-3 text-xs">
                                    <span class="text-slate-500 block mb-1">Vendor Service Notes</span>
                                    <div class="p-3 bg-slate-50 dark:bg-[#060913] rounded-[4px] border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200">
                                        {{ $rma->vendor_repair_notes }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                </div>

                {{-- RIGHT COLUMN: Actions & Tracking (1 col) --}}
                <div class="space-y-5">

                    {{-- Action Card: Dispatch to Vendor --}}
                    @if($rma->status === 'draft')
                        <div class="bg-white dark:bg-[#0f172a] border border-slate-300 dark:border-slate-800 rounded-[4px] p-5">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-1">
                                Dispatch to Vendor
                            </h4>
                            <p class="text-xs text-slate-500 mb-3">Ship item to supplier service center with courier details.</p>

                            <form method="POST" action="{{ route('rma.dispatch', $rma) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Shipping Carrier <span class="text-rose-500">*</span></label>
                                    <input type="text" name="shipping_courier" required placeholder="e.g. DTDC, Blue Dart" class="w-full bg-white dark:bg-[#060913] border border-slate-300 dark:border-slate-700 rounded-[4px] px-3 h-[38px] text-sm text-slate-900 dark:text-white focus:border-blue-600 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Consignment / AWB No.</label>
                                    <input type="text" name="tracking_number" placeholder="e.g. D19283741" class="w-full bg-white dark:bg-[#060913] border border-slate-300 dark:border-slate-700 rounded-[4px] px-3 h-[38px] text-sm text-slate-900 dark:text-white font-mono focus:border-blue-600 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Dispatched Date <span class="text-rose-500">*</span></label>
                                    <input type="date" name="dispatched_date" value="{{ now()->format('Y-m-d') }}" required class="w-full bg-white dark:bg-[#060913] border border-slate-300 dark:border-slate-700 rounded-[4px] px-3 h-[38px] text-sm text-slate-900 dark:text-white focus:border-blue-600 focus:outline-none">
                                </div>

                                <button type="submit" class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-[4px] text-sm font-bold transition-colors cursor-pointer">
                                    Mark Dispatched &amp; Save AWB
                                </button>
                            </form>
                        </div>
                    @endif

                    {{-- Action Card: Record Resolution --}}
                    @if(in_array($rma->status, ['shipped_to_vendor', 'in_vendor_repair']))
                        <div class="bg-white dark:bg-[#0f172a] border border-slate-300 dark:border-slate-800 rounded-[4px] p-5">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-1">
                                Record Vendor Resolution
                            </h4>
                            <p class="text-xs text-slate-500 mb-3">Inward replacement unit or record repaired unit from supplier.</p>

                            <form method="POST" action="{{ route('rma.resolution', $rma) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Resolution Outcome <span class="text-rose-500">*</span></label>
                                    <select name="resolution_type" required class="w-full bg-white dark:bg-[#060913] border border-slate-300 dark:border-slate-700 rounded-[4px] px-3 h-[38px] text-sm text-slate-900 dark:text-white focus:border-blue-600 focus:outline-none" onchange="toggleReplacementFields(this.value)">
                                        <option value="replacement">Brand New Replacement (New S/N)</option>
                                        <option value="repaired_unit">Original Unit Repaired &amp; Returned</option>
                                        <option value="credit_note">Vendor Credit Note / Refund</option>
                                        <option value="rejected_damage">Claim Rejected (Void)</option>
                                    </select>
                                </div>

                                <div id="replacementSerialNumberDiv">
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">New Serial Number (S/N) <span class="text-rose-500">*</span></label>
                                    <input type="text" name="replacement_serial_number" placeholder="Enter new serial number" class="w-full bg-white dark:bg-[#060913] border border-slate-300 dark:border-slate-700 rounded-[4px] px-3 h-[38px] text-sm text-slate-900 dark:text-white font-mono focus:border-blue-600 focus:outline-none">
                                </div>

                                <div id="replacementMacDiv">
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">New MAC Address</label>
                                    <input type="text" name="replacement_mac_address" placeholder="e.g. 54:C8:01:B3:99:11" class="w-full bg-white dark:bg-[#060913] border border-slate-300 dark:border-slate-700 rounded-[4px] px-3 h-[38px] text-sm text-slate-900 dark:text-white font-mono focus:border-blue-600 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Received Date <span class="text-rose-500">*</span></label>
                                    <input type="date" name="received_from_vendor_date" value="{{ now()->format('Y-m-d') }}" required class="w-full bg-white dark:bg-[#060913] border border-slate-300 dark:border-slate-700 rounded-[4px] px-3 h-[38px] text-sm text-slate-900 dark:text-white focus:border-blue-600 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Resolution Notes</label>
                                    <textarea name="vendor_repair_notes" rows="2" placeholder="e.g. Replaced mainboard, tested OK..." class="w-full bg-white dark:bg-[#060913] border border-slate-300 dark:border-slate-700 rounded-[4px] p-2 text-sm text-slate-900 dark:text-white focus:border-blue-600 focus:outline-none"></textarea>
                                </div>

                                <button type="submit" class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-[4px] text-sm font-bold transition-colors cursor-pointer">
                                    Inward &amp; Update Equipment
                                </button>
                            </form>
                        </div>
                    @endif

                    {{-- Shipping Logistics Info Card --}}
                    @if($rma->dispatched_date)
                        <div class="bg-white dark:bg-[#0f172a] border border-slate-300 dark:border-slate-800 rounded-[4px] p-5">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-2 pb-2 border-b border-slate-200 dark:border-slate-800">
                                Shipping &amp; Vendor Tracking
                            </h4>
                            <div class="space-y-2 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Courier:</span>
                                    <strong class="text-slate-900 dark:text-white">{{ $rma->shipping_courier }}</strong>
                                </div>
                                @if($rma->tracking_number)
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">AWB No:</span>
                                        <strong class="text-slate-900 dark:text-white font-mono">{{ $rma->tracking_number }}</strong>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Dispatched:</span>
                                    <span class="text-slate-700 dark:text-slate-300">{{ $rma->dispatched_date->format('d M Y') }}</span>
                                </div>
                                @if($rma->turnaround_days !== null)
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">Turnaround:</span>
                                        <span class="font-bold text-emerald-700">{{ $rma->turnaround_days }} days</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Audit Trail Timeline --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-300 dark:border-slate-800 rounded-[4px] p-5">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-2 pb-2 border-b border-slate-200 dark:border-slate-800">
                            RMA Audit History Log
                        </h4>
                        <div class="space-y-2.5 text-xs">
                            @forelse($rma->statusLogs as $log)
                                <div class="pb-2 border-b border-slate-200 dark:border-slate-800 last:border-0 last:pb-0">
                                    <div class="font-bold text-slate-900 dark:text-white capitalize">{{ str_replace('_', ' ', $log->to_status) }}</div>
                                    @if($log->notes)
                                        <div class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5">{{ $log->notes }}</div>
                                    @endif
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $log->created_at->format('d M, h:i A') }} &bull; {{ $log->changedBy->name }}</div>
                                </div>
                            @empty
                                <span class="text-slate-500 text-xs">No status history logged yet.</span>
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
