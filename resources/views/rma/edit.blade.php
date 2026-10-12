<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold leading-tight text-slate-900 dark:text-white">
                    Edit RMA Claim: <span class="font-mono text-blue-600">{{ $rma->rma_no }}</span>
                </h2>
            </div>
            <a href="{{ route('rma.show', $rma) }}"
               class="crm-back-link text-xs font-semibold text-blue-600 hover:underline">
                &larr; Back to Details
            </a>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f8fafc] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            @if ($errors->any())
                <div class="mb-5 p-3 rounded-[4px] bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('rma.update', $rma) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="bg-white dark:bg-[#0f172a] rounded-[4px] p-5 border border-slate-300 dark:border-slate-800 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Target Supplier / Vendor <span class="text-rose-500">*</span></label>
                            <select name="supplier_id" required class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $rma->supplier_id) == $supplier->id)>
                                        {{ $supplier->name }} ({{ $supplier->company_name ?: $supplier->city }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Product Model</label>
                            <select name="product_id" class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                <option value="">Select Product (Optional)</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" @selected(old('product_id', $rma->product_id) == $prod->id)>
                                        {{ $prod->name }} ({{ $prod->model_no ?: 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Faulty Serial Number (S/N) <span class="text-rose-500">*</span></label>
                            <input type="text" name="faulty_serial_number" value="{{ old('faulty_serial_number', $rma->faulty_serial_number) }}" required 
                                class="w-full text-xs font-mono font-bold rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">MAC Address (Optional)</label>
                            <input type="text" name="faulty_mac_address" value="{{ old('faulty_mac_address', $rma->faulty_mac_address) }}" 
                                class="w-full text-xs font-mono rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Fault Category <span class="text-rose-500">*</span></label>
                            <select name="fault_category" required class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                @foreach(\App\Models\RmaClaim::faultCategories() as $key => $label)
                                    <option value="{{ $key }}" @selected(old('fault_category', $rma->fault_category) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Warranty Status at Claim <span class="text-rose-500">*</span></label>
                            <select name="warranty_status_at_claim" required class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600 font-medium">
                                <option value="under_warranty" @selected(old('warranty_status_at_claim', $rma->warranty_status_at_claim) === 'under_warranty')>Under Manufacturer Warranty</option>
                                <option value="out_of_warranty" @selected(old('warranty_status_at_claim', $rma->warranty_status_at_claim) === 'out_of_warranty')>Out of Warranty</option>
                                <option value="extended_warranty" @selected(old('warranty_status_at_claim', $rma->warranty_status_at_claim) === 'extended_warranty')>Extended Warranty</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Vendor's RMA Ref #</label>
                            <input type="text" name="vendor_rma_ref" value="{{ old('vendor_rma_ref', $rma->vendor_rma_ref) }}" 
                                class="w-full text-xs font-mono rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Expected Return Date</label>
                            <input type="date" name="expected_return_date" value="{{ old('expected_return_date', $rma->expected_return_date?->format('Y-m-d')) }}" 
                                class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Defect Description <span class="text-rose-500">*</span></label>
                        <textarea name="issue_description" rows="3" required 
                            class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white p-3 focus:outline-none focus:border-blue-600">{{ old('issue_description', $rma->issue_description) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('rma.show', $rma) }}" class="px-4 py-2 text-sm font-medium rounded-[4px] border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 text-sm font-bold rounded-[4px] bg-blue-600 hover:bg-blue-700 text-white transition-colors cursor-pointer">
                        Save Changes
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
