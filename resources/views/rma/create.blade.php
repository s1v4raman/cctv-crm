<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Raise RMA Claim</h2>
    </x-slot>

    <div class="py-5">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('rma.index') }}" class="back-link">&larr; Back</a>

            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Raise RMA &amp; Vendor Warranty Claim</h2>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-4 rounded-[4px] bg-red-50 border border-red-200 text-[#dc2626] text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('rma.store') }}">
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

                {{-- Section 1: Equipment & Customer Linkage --}}
                <div class="crm-section mb-4">
                    <h3>Equipment &amp; Site Source</h3>

                    @if($prefilledEquipment)
                        <div class="p-3 border border-[#ddd] rounded-[4px] bg-[#f9fafb] mb-4 text-sm">
                            <span class="font-bold text-gray-500 block mb-1">Linked Installed Asset</span>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <strong class="font-bold text-gray-900">{{ $prefilledEquipment->equipment_name }}</strong>
                                    <div class="text-gray-500 mt-0.5">
                                        Client: <strong class="text-gray-700">{{ $prefilledLead?->customer_name ?? 'N/A' }}</strong> &middot; Location: {{ $prefilledEquipment->location_tag ?: 'General' }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="font-mono font-bold text-[#2563eb]">
                                        S/N: {{ $prefilledEquipment->serial_number }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @elseif($prefilledTicket)
                        <div class="p-3 border border-[#ddd] rounded-[4px] bg-[#f9fafb] mb-4 text-sm">
                            <span class="font-bold text-gray-500 block mb-1">Linked Service Ticket: {{ $prefilledTicket->ticket_no }}</span>
                            <strong class="font-bold text-gray-900">{{ $prefilledTicket->title }}</strong>
                            <div class="text-gray-500 mt-0.5">Client: <strong class="text-gray-700">{{ $prefilledTicket->lead?->customer_name ?? 'N/A' }}</strong></div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="crm-label mb-0">Faulty Serial Number (S/N) <span class="required-marker">*</span></label>
                                <button type="button" onclick="openBarcodeScanner('rma-barcode-modal', 'rma_serial_input', onRmaBarcodeScanned)"
                                        class="action-link text-xs" style="background: none; border: none; cursor: pointer;">
                                    Scan Barcode
                                </button>
                            </div>
                            <input aria-label="Faulty Serial Number (S/N)" type="text" id="rma_serial_input" name="faulty_serial_number" value="{{ old('faulty_serial_number', $prefilledEquipment?->serial_number) }}" required placeholder="e.g. HK-982348123" class="crm-input font-mono">
                            <div id="rma-scan-feedback" style="display:none;font-size:12px;margin-top:4px;"></div>
                        </div>
                        <div>
                            <label class="crm-label">MAC / IP Address (Optional)</label>
                            <input aria-label="MAC / IP Address (Optional)" type="text" id="rma_mac_input" name="faulty_mac_address" value="{{ old('faulty_mac_address', $prefilledEquipment?->mac_address) }}" placeholder="e.g. 54:C8:01:A2:FE:9B" class="crm-input font-mono">
                        </div>

                        <div>
                            <label class="crm-label">Product Model (Catalog)</label>
                            <select aria-label="Product Model (Catalog)" name="product_id" class="crm-select">
                                <option value="">Select Catalog Product (Optional)</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" @selected(old('product_id', $prefilledProduct?->id) == $prod->id)>
                                        {{ $prod->name }} ({{ $prod->model_no ?: 'Model N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="crm-label">Customer / Organization (Optional)</label>
                            <select aria-label="Customer / Organization (Optional)" name="lead_id" class="crm-select">
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

                {{-- Section 2: Supplier Destination --}}
                <div class="crm-section mb-4">
                    <h3>Supplier &amp; Warranty Terms</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Target Supplier / Vendor <span class="required-marker">*</span></label>
                            <select aria-label="Target Supplier / Vendor" name="supplier_id" required class="crm-select">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                        {{ $supplier->name }} ({{ $supplier->company_name ?: $supplier->city }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="crm-label">Warranty Status at Claim <span class="required-marker">*</span></label>
                            <select aria-label="Warranty Status at Claim" name="warranty_status_at_claim" required class="crm-select">
                                <option value="under_warranty" @selected(old('warranty_status_at_claim', $warrantyStatus) === 'under_warranty')>Under Manufacturer Warranty</option>
                                <option value="out_of_warranty" @selected(old('warranty_status_at_claim', $warrantyStatus) === 'out_of_warranty')>Out of Warranty (Chargeable Service)</option>
                                <option value="extended_warranty" @selected(old('warranty_status_at_claim', $warrantyStatus) === 'extended_warranty')>Extended Warranty / AMC</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="crm-label">Vendor RMA Reference # (Optional)</label>
                            <input aria-label="Vendor RMA Reference # (Optional)" type="text" name="vendor_rma_ref" value="{{ old('vendor_rma_ref') }}" placeholder="e.g. HIK-SERVICE-9821" class="crm-input font-mono">
                        </div>
                        <div>
                            <label class="crm-label">Expected Return Date</label>
                            <input aria-label="Expected Return Date" type="date" name="expected_return_date" value="{{ old('expected_return_date', now()->addDays(7)->format('Y-m-d')) }}" class="crm-input">
                        </div>
                    </div>
                </div>

                {{-- Section 3: Hardware Defect Details --}}
                <div class="crm-section mb-4">
                    <h3>Hardware Defect &amp; Failure Diagnosis</h3>

                    <div class="mb-4">
                        <label class="crm-label">Fault Category <span class="required-marker">*</span></label>
                        <select aria-label="Fault Category" name="fault_category" required class="crm-select">
                            @foreach(\App\Models\RmaClaim::faultCategories() as $key => $label)
                                <option value="{{ $key }}" @selected(old('fault_category') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="crm-label">Detailed Defect Description <span class="required-marker">*</span></label>
                        <textarea aria-label="Detailed Defect Description" name="issue_description" rows="3" required class="crm-input" style="height: auto;" placeholder="e.g. Camera powers on with 12V DC, but sensor sends purple screen feeds.">{{ old('issue_description', $prefilledTicket?->description) }}</textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('rma.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save</button>
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
                feedback.innerHTML = `<span class="text-[#2563eb] font-semibold">Looking up serial ${code}...</span>`;
            }

            try {
                const response = await fetch(`{{ route('equipment.lookup') }}?serial=${encodeURIComponent(code)}`);
                const data = await response.json();

                if (data.found && data.type === 'installed_equipment') {
                    if (feedback) {
                        feedback.innerHTML = `<span class="text-[#16a34a] font-bold">Matched: ${data.equipment_name} (${data.customer_name})</span>`;
                    }
                    const macInput = document.getElementById('rma_mac_input');
                    if (macInput && data.mac_address && !macInput.value) {
                        macInput.value = data.mac_address;
                    }
                } else if (feedback) {
                    feedback.innerHTML = `<span class="text-gray-500 font-medium">Scanned S/N: ${code} (Unlinked hardware)</span>`;
                }
            } catch (e) {
                if (feedback) feedback.style.display = 'none';
            }
        }
    </script>
</x-app-layout>
