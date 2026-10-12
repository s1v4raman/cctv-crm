<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold leading-tight text-slate-900 dark:text-white">
                    Raise RMA &amp; Vendor Warranty Claim
                </h2>
            </div>
            <a href="{{ route('rma.index') }}"
               class="crm-back-link text-xs font-semibold text-blue-600 hover:underline">
                &larr; Back to RMA List
            </a>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f8fafc] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            @if ($errors->any())
                <div class="mb-5 p-3 rounded-[4px] bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                    <div class="font-bold mb-1">
                        Please fix the following errors:
                    </div>
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('rma.store') }}" class="space-y-5">
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
                <div class="bg-white dark:bg-[#0f172a] rounded-[4px] p-5 border border-slate-300 dark:border-slate-800">
                    <div class="mb-4 pb-2 border-b border-slate-200 dark:border-slate-800">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">1. Faulty Equipment &amp; Site Source</h4>
                    </div>

                    @if($prefilledEquipment)
                        <div class="p-3 rounded-[4px] bg-slate-50 dark:bg-[#060913] border border-slate-300 dark:border-slate-700 mb-4 text-xs">
                            <span class="font-bold text-slate-500 block mb-1">Linked Installed Asset</span>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <strong class="font-bold text-slate-900 dark:text-white">{{ $prefilledEquipment->equipment_name }}</strong>
                                    <div class="text-slate-500 mt-0.5">
                                        Client: <strong class="text-slate-700 dark:text-slate-300">{{ $prefilledLead?->customer_name ?? 'N/A' }}</strong> &middot; Location: {{ $prefilledEquipment->location_tag ?: 'General' }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="font-mono font-bold text-blue-600">
                                        S/N: {{ $prefilledEquipment->serial_number }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @elseif($prefilledTicket)
                        <div class="p-3 rounded-[4px] bg-slate-50 dark:bg-[#060913] border border-slate-300 dark:border-slate-700 mb-4 text-xs">
                            <span class="font-bold text-slate-500 block mb-1">Linked Service Ticket: {{ $prefilledTicket->ticket_no }}</span>
                            <strong class="font-bold text-slate-900 dark:text-white">{{ $prefilledTicket->title }}</strong>
                            <div class="text-slate-500 mt-0.5">Client: <strong class="text-slate-700 dark:text-slate-300">{{ $prefilledTicket->lead?->customer_name ?? 'N/A' }}</strong></div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Faulty Unit Serial Number (S/N) <span class="text-rose-500">*</span></label>
                                <button type="button" onclick="openBarcodeScanner('rma-barcode-modal', 'rma_serial_input', onRmaBarcodeScanned)"
                                        class="text-xs font-semibold text-blue-600 hover:underline">
                                    Scan Barcode
                                </button>
                            </div>
                            <div class="relative flex items-center">
                                <input type="text" id="rma_serial_input" name="faulty_serial_number" value="{{ old('faulty_serial_number', $prefilledEquipment?->serial_number) }}" required placeholder="e.g. HK-982348123" 
                                    class="w-full text-xs font-mono font-bold rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                            </div>
                            <div id="rma-scan-feedback" style="display:none;font-size:11px;margin-top:4px;"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">MAC / IP Address (Optional)</label>
                            <input type="text" id="rma_mac_input" name="faulty_mac_address" value="{{ old('faulty_mac_address', $prefilledEquipment?->mac_address) }}" placeholder="e.g. 54:C8:01:A2:FE:9B" 
                                class="w-full text-xs font-mono rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Product Model (Catalog)</label>
                            <select name="product_id" class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                <option value="">Select Catalog Product (Optional)</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" @selected(old('product_id', $prefilledProduct?->id) == $prod->id)>
                                        {{ $prod->name }} ({{ $prod->model_no ?: 'Model N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Customer / Organization (Optional)</label>
                            <select name="lead_id" class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
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
                <div class="bg-white dark:bg-[#0f172a] rounded-[4px] p-5 border border-slate-300 dark:border-slate-800">
                    <div class="mb-4 pb-2 border-b border-slate-200 dark:border-slate-800">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">2. Supplier &amp; Warranty Terms</h4>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Target Supplier / Vendor Service Center <span class="text-rose-500">*</span></label>
                            <select name="supplier_id" required class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                        {{ $supplier->name }} ({{ $supplier->company_name ?: $supplier->city }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Warranty Status at Claim <span class="text-rose-500">*</span></label>
                            <select name="warranty_status_at_claim" required class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600 font-medium">
                                <option value="under_warranty" @selected(old('warranty_status_at_claim', $warrantyStatus) === 'under_warranty')>Under Manufacturer Warranty</option>
                                <option value="out_of_warranty" @selected(old('warranty_status_at_claim', $warrantyStatus) === 'out_of_warranty')>Out of Warranty (Chargeable Service)</option>
                                <option value="extended_warranty" @selected(old('warranty_status_at_claim', $warrantyStatus) === 'extended_warranty')>Extended Warranty / AMC</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Vendor RMA Reference # (Optional)</label>
                            <input type="text" name="vendor_rma_ref" value="{{ old('vendor_rma_ref') }}" placeholder="e.g. HIK-SERVICE-9821" 
                                class="w-full text-xs font-mono rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Expected Return Date</label>
                            <input type="date" name="expected_return_date" value="{{ old('expected_return_date', now()->addDays(7)->format('Y-m-d')) }}" 
                                class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                        </div>
                    </div>
                </div>

                {{-- Card 3: Fault Diagnosis & Defect Details --}}
                <div class="bg-white dark:bg-[#0f172a] rounded-[4px] p-5 border border-slate-300 dark:border-slate-800">
                    <div class="mb-4 pb-2 border-b border-slate-200 dark:border-slate-800">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">3. Hardware Defect &amp; Failure Diagnosis</h4>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Fault Category <span class="text-rose-500">*</span></label>
                        <select name="fault_category" required class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                            @foreach(\App\Models\RmaClaim::faultCategories() as $key => $label)
                                <option value="{{ $key }}" @selected(old('fault_category') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Detailed Defect Description <span class="text-rose-500">*</span></label>
                        <textarea name="issue_description" rows="3" required 
                            class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white p-3 focus:outline-none focus:border-blue-600" 
                            placeholder="Describe symptom: e.g. Camera powers on with 12V DC, but sensor sends purple screen feeds. Tested with separate POE switch port.">{{ old('issue_description', $prefilledTicket?->description) }}</textarea>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('rma.index') }}" class="px-4 py-2 text-sm font-medium rounded-[4px] border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 text-sm font-bold rounded-[4px] bg-blue-600 hover:bg-blue-700 text-white transition-colors cursor-pointer">
                        Create RMA Claim
                    </button>
                </div>
            </form>

        </div>
    </div>

    <x-barcode-scanner modalId="rma-barcode-modal" title="Scan Faulty Unit Serial Number" />

    <script>
        async function onRmaBarcodeScanned(code) {
            const feedback = document.getElementById('rma-scan-feedback');
            if (feedback) {
                feedback.style.display = 'block';
                feedback.innerHTML = `<span class="text-blue-600 font-semibold">Looking up serial ${code}...</span>`;
            }

            try {
                const response = await fetch(`{{ route('equipment.lookup') }}?serial=${encodeURIComponent(code)}`);
                const data = await response.json();

                if (data.found && data.type === 'installed_equipment') {
                    if (feedback) {
                        feedback.innerHTML = `<span class="text-emerald-700 font-bold">Matched: ${data.equipment_name} (${data.customer_name})</span>`;
                    }
                    const macInput = document.getElementById('rma_mac_input');
                    if (macInput && data.mac_address && !macInput.value) {
                        macInput.value = data.mac_address;
                    }
                } else if (feedback) {
                    feedback.innerHTML = `<span class="text-slate-500 font-medium">Scanned S/N: ${code} (Unlinked hardware)</span>`;
                }
            } catch (e) {
                if (feedback) feedback.style.display = 'none';
            }
        }
    </script>
</x-app-layout>
