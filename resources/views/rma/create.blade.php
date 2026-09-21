<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-rose-500 p-0.5 shadow-lg shadow-amber-500/20 flex items-center justify-center">
                    <div class="w-full h-full bg-[#060913] rounded-[10px] flex items-center justify-center text-amber-400 font-bold">
                        ⚡
                    </div>
                </div>
                <div>
                    <h2 class="text-xl font-black leading-tight text-white font-heading tracking-tight">
                        Raise RMA & Vendor Warranty Claim
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-400 font-medium">Log faulty hardware return for supplier repair or replacement</p>
                </div>
            </div>
            <a href="{{ route('rma.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-800/80 text-xs font-bold text-slate-300 hover:text-white hover:bg-slate-700 transition shadow-sm">
                &larr; Back to RMA List
            </a>
        </div>
    </x-slot>

    <div class="py-6 bg-[#060913] min-h-screen text-slate-200" style="background-color: #060913;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="mb-5 p-4 rounded-2xl bg-rose-950/40 border border-rose-500/40 text-rose-300 text-xs shadow-lg">
                    <div class="font-bold flex items-center gap-2 mb-1">
                        <span>⚠️</span> Please fix the following errors:
                    </div>
                    <ul class="list-disc pl-5 space-y-0.5 text-slate-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('rma.store') }}" class="space-y-6">
                @csrf

                {{-- Hidden context if prefilled --}}
                @if($prefilledEquipment)
                    <input type="hidden" name="installed_equipment_id" value="{{ $prefilledEquipment->id }}">
                    <input type="hidden" name="lead_id" value="{{ $prefilledLead?->id }}">
                @endif
                @if($prefilledTicket)
                    <input type="hidden" name="service_ticket_id" value="{{ $prefilledTicket->id }}">
                    @if(!$prefilledLead && $prefilledTicket->lead_id)
                        <input type="hidden" name="lead_id" value="{{ $prefilledTicket->lead_id }}">
                    @endif
                @endif

                {{-- Card 1: Equipment & Customer Linkage --}}
                <div class="bg-[#0f172a] rounded-2xl p-6 shadow-2xl border border-slate-800" style="background-color: #0f172a;">
                    <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-800">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30 font-bold text-xs">1</span>
                        <h4 class="text-base font-extrabold text-white font-heading">Faulty Equipment & Site Source</h4>
                    </div>

                    @if($prefilledEquipment)
                        <div class="p-4 rounded-xl bg-[#060913] border border-amber-500/30 mb-4">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-400 block mb-1">Linked Installed Asset</span>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <strong class="text-sm font-bold text-white">{{ $prefilledEquipment->equipment_name }}</strong>
                                    <div class="text-xs text-slate-400 mt-0.5">
                                        Client: <strong class="text-slate-200">{{ $prefilledLead?->customer_name ?? 'N/A' }}</strong> &middot; Location: {{ $prefilledEquipment->location_tag ?: 'General' }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-mono font-bold text-amber-300 bg-amber-500/10 px-2.5 py-1 rounded-lg border border-amber-500/30">
                                        S/N: {{ $prefilledEquipment->serial_number }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @elseif($prefilledTicket)
                        <div class="p-4 rounded-xl bg-[#060913] border border-amber-500/30 mb-4">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-400 block mb-1">Linked Service Ticket: {{ $prefilledTicket->ticket_no }}</span>
                            <strong class="text-sm font-bold text-white">{{ $prefilledTicket->title }}</strong>
                            <div class="text-xs text-slate-400 mt-0.5">Client: <strong class="text-slate-200">{{ $prefilledTicket->lead?->customer_name ?? 'N/A' }}</strong></div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Faulty Unit Serial Number (S/N) <span class="text-amber-400">*</span></label>
                                <button type="button" onclick="openBarcodeScanner('rma-barcode-modal', 'rma_serial_input', onRmaBarcodeScanned)"
                                        class="text-xs font-bold text-amber-400 hover:text-amber-300 inline-flex items-center gap-1 transition">
                                    📷 Scan Barcode
                                </button>
                            </div>
                            <div class="relative flex items-center">
                                <input type="text" id="rma_serial_input" name="faulty_serial_number" value="{{ old('faulty_serial_number', $prefilledEquipment?->serial_number) }}" required placeholder="e.g. HK-982348123" 
                                    class="w-full text-xs font-mono font-bold rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5 pr-10">
                                <button type="button" onclick="openBarcodeScanner('rma-barcode-modal', 'rma_serial_input', onRmaBarcodeScanned)"
                                        class="absolute right-2 text-slate-400 hover:text-amber-400 p-1" title="Scan Barcode">
                                    📸
                                </button>
                            </div>
                            <div id="rma-scan-feedback" style="display:none;font-size:11px;margin-top:4px;"></div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">MAC / IP Address (Optional)</label>
                            <input type="text" id="rma_mac_input" name="faulty_mac_address" value="{{ old('faulty_mac_address', $prefilledEquipment?->mac_address) }}" placeholder="e.g. 54:C8:01:A2:FE:9B" 
                                class="w-full text-xs font-mono rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Product Model (Catalog)</label>
                            <select name="product_id" class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                                <option value="">Select Catalog Product (Optional)</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" @selected(old('product_id', $prefilledProduct?->id) == $prod->id)>
                                        {{ $prod->name }} ({{ $prod->model_no ?: 'Model N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Customer / Organization (Optional)</label>
                            <select name="lead_id" class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                                <option value="">None (Warehouse / Direct Stock)</option>
                                @foreach($leads as $l)
                                    <option value="{{ $l->id }}" @selected(old('lead_id', $prefilledLead?->id) == $l->id)>
                                        {{ $l->customer_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Vendor & Supplier Destination --}}
                <div class="bg-[#0f172a] rounded-2xl p-6 shadow-2xl border border-slate-800" style="background-color: #0f172a;">
                    <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-800">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-bold text-xs">2</span>
                        <h4 class="text-base font-extrabold text-white font-heading">Supplier & Warranty Terms</h4>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Target Supplier / Vendor Service Center <span class="text-amber-400">*</span></label>
                            <select name="supplier_id" required class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                        {{ $supplier->name }} ({{ $supplier->company_name ?: $supplier->city }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Warranty Status at Claim <span class="text-amber-400">*</span></label>
                            <select name="warranty_status_at_claim" required class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5 font-bold">
                                <option value="under_warranty" @selected(old('warranty_status_at_claim', $warrantyStatus) === 'under_warranty')>🟢 Under Manufacturer Warranty</option>
                                <option value="out_of_warranty" @selected(old('warranty_status_at_claim', $warrantyStatus) === 'out_of_warranty')>🔴 Out of Warranty (Chargeable Service)</option>
                                <option value="extended_warranty" @selected(old('warranty_status_at_claim', $warrantyStatus) === 'extended_warranty')>🟡 Extended Warranty / AMC</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Vendor RMA Reference # (Optional)</label>
                            <input type="text" name="vendor_rma_ref" value="{{ old('vendor_rma_ref') }}" placeholder="e.g. HIK-SERVICE-9821" 
                                class="w-full text-xs font-mono rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Expected Return / Resolution Date</label>
                            <input type="date" name="expected_return_date" value="{{ old('expected_return_date', now()->addDays(7)->format('Y-m-d')) }}" 
                                class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                        </div>
                    </div>
                </div>

                {{-- Card 3: Fault Diagnosis & Defect Details --}}
                <div class="bg-[#0f172a] rounded-2xl p-6 shadow-2xl border border-slate-800" style="background-color: #0f172a;">
                    <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-800">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/30 font-bold text-xs">3</span>
                        <h4 class="text-base font-extrabold text-white font-heading">Hardware Defect & Failure Diagnosis</h4>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Fault Category <span class="text-amber-400">*</span></label>
                        <select name="fault_category" required class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                            @foreach(\App\Models\RmaClaim::faultCategories() as $key => $label)
                                <option value="{{ $key }}" @selected(old('fault_category') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Detailed Defect Description & Symptoms <span class="text-amber-400">*</span></label>
                        <textarea name="issue_description" rows="3" required 
                            class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5" 
                            placeholder="Describe symptom: e.g. Camera powers on with 12V DC, but sensor sends purple screen feeds. Tested with separate POE switch port. No physical damage.">{{ old('issue_description', $prefilledTicket?->description) }}</textarea>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('rma.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-700 text-xs font-bold text-slate-400 hover:text-white hover:bg-slate-800 transition">
                        Cancel
                    </a>
                    <button type="submit" class="btn-amber">
                        <span>Create RMA Claim & Generate Slip</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>

        </div>
    </div>

    <x-barcode-scanner modalId="rma-barcode-modal" title="📸 Scan Faulty Unit Serial Number" />

    <script>
        async function onRmaBarcodeScanned(code) {
            const feedback = document.getElementById('rma-scan-feedback');
            if (feedback) {
                feedback.style.display = 'block';
                feedback.innerHTML = `<span class="text-amber-400 font-semibold">🔍 Looking up serial ${code}...</span>`;
            }

            try {
                const response = await fetch(`{{ route('equipment.lookup') }}?serial=${encodeURIComponent(code)}`);
                const data = await response.json();

                if (data.found && data.type === 'installed_equipment') {
                    if (feedback) {
                        feedback.innerHTML = `<span class="text-emerald-400 font-bold">✓ Matched: ${data.equipment_name} (${data.customer_name})</span>`;
                    }
                    const macInput = document.getElementById('rma_mac_input');
                    if (macInput && data.mac_address && !macInput.value) {
                        macInput.value = data.mac_address;
                    }
                } else if (feedback) {
                    feedback.innerHTML = `<span class="text-slate-400 font-medium">Scanned S/N: ${code} (Unlinked hardware)</span>`;
                }
            } catch (e) {
                if (feedback) feedback.style.display = 'none';
            }
        }
    </script>
</x-app-layout>
