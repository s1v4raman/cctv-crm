<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit RMA Claim</h2>
    </x-slot>

    <div class="py-5">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('rma.show', $rma) }}" class="back-link">&larr; Back</a>

            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">Edit RMA Claim &mdash; {{ $rma->rma_no }}</h2>
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

            <div class="crm-section">
                <form method="POST" action="{{ route('rma.update', $rma) }}">
                    @csrf
                    @method('PUT')

                    <h3 class="mb-4 pb-2 border-b border-[#ddd] font-bold text-base text-gray-900">Claim Details</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Target Supplier / Vendor <span class="required-marker">*</span></label>
                            <select name="supplier_id" required class="crm-select">
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $rma->supplier_id) == $supplier->id)>
                                        {{ $supplier->name }} ({{ $supplier->company_name ?: $supplier->city }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="crm-label">Product Model</label>
                            <select name="product_id" class="crm-select">
                                <option value="">Select Product (Optional)</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" @selected(old('product_id', $rma->product_id) == $prod->id)>
                                        {{ $prod->name }} ({{ $prod->model_no ?: 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Faulty Serial Number (S/N) <span class="required-marker">*</span></label>
                            <input type="text" name="faulty_serial_number" value="{{ old('faulty_serial_number', $rma->faulty_serial_number) }}" required class="crm-input font-mono">
                        </div>
                        <div>
                            <label class="crm-label">MAC Address (Optional)</label>
                            <input type="text" name="faulty_mac_address" value="{{ old('faulty_mac_address', $rma->faulty_mac_address) }}" class="crm-input font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Fault Category <span class="required-marker">*</span></label>
                            <select name="fault_category" required class="crm-select">
                                @foreach(\App\Models\RmaClaim::faultCategories() as $key => $label)
                                    <option value="{{ $key }}" @selected(old('fault_category', $rma->fault_category) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="crm-label">Warranty Status at Claim <span class="required-marker">*</span></label>
                            <select name="warranty_status_at_claim" required class="crm-select">
                                <option value="under_warranty" @selected(old('warranty_status_at_claim', $rma->warranty_status_at_claim) === 'under_warranty')>Under Manufacturer Warranty</option>
                                <option value="out_of_warranty" @selected(old('warranty_status_at_claim', $rma->warranty_status_at_claim) === 'out_of_warranty')>Out of Warranty</option>
                                <option value="extended_warranty" @selected(old('warranty_status_at_claim', $rma->warranty_status_at_claim) === 'extended_warranty')>Extended Warranty</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Vendor RMA Ref #</label>
                            <input type="text" name="vendor_rma_ref" value="{{ old('vendor_rma_ref', $rma->vendor_rma_ref) }}" class="crm-input font-mono">
                        </div>
                        <div>
                            <label class="crm-label">Expected Return Date</label>
                            <input type="date" name="expected_return_date" value="{{ old('expected_return_date', $rma->expected_return_date?->format('Y-m-d')) }}" class="crm-input">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="crm-label">Defect Description <span class="required-marker">*</span></label>
                        <textarea name="issue_description" rows="3" required class="crm-input" style="height: auto;">{{ old('issue_description', $rma->issue_description) }}</textarea>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('rma.show', $rma) }}" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
