<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-rose-500 p-0.5 shadow-lg shadow-amber-500/20 flex items-center justify-center">
                    <div class="w-full h-full bg-[#060913] rounded-[10px] flex items-center justify-center text-amber-400 font-bold">
                        ✏️
                    </div>
                </div>
                <div>
                    <h2 class="text-xl font-black leading-tight text-white font-heading tracking-tight">
                        Edit RMA Claim: <span class="font-mono text-amber-400">{{ $rma->rma_no }}</span>
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-400 font-medium">Update fault details and warranty reference</p>
                </div>
            </div>
            <a href="{{ route('rma.show', $rma) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-800/80 text-xs font-bold text-slate-300 hover:text-white hover:bg-slate-700 transition shadow-sm">
                &larr; Back to Details
            </a>
        </div>
    </x-slot>

    <div class="py-6 bg-[#060913] min-h-screen text-slate-200" style="background-color: #060913;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="mb-5 p-4 rounded-2xl bg-rose-950/40 border border-rose-500/40 text-rose-300 text-xs shadow-lg">
                    <ul class="list-disc pl-5 space-y-0.5 text-slate-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('rma.update', $rma) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="bg-[#0f172a] rounded-2xl p-6 shadow-2xl border border-slate-800 space-y-4" style="background-color: #0f172a;">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Target Supplier / Vendor <span class="text-amber-400">*</span></label>
                            <select name="supplier_id" required class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $rma->supplier_id) == $supplier->id)>
                                        {{ $supplier->name }} ({{ $supplier->company_name ?: $supplier->city }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Product Model</label>
                            <select name="product_id" class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
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
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Faulty Serial Number (S/N) <span class="text-amber-400">*</span></label>
                            <input type="text" name="faulty_serial_number" value="{{ old('faulty_serial_number', $rma->faulty_serial_number) }}" required 
                                class="w-full text-xs font-mono font-bold rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">MAC Address (Optional)</label>
                            <input type="text" name="faulty_mac_address" value="{{ old('faulty_mac_address', $rma->faulty_mac_address) }}" 
                                class="w-full text-xs font-mono rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Fault Category <span class="text-amber-400">*</span></label>
                            <select name="fault_category" required class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                                @foreach(\App\Models\RmaClaim::faultCategories() as $key => $label)
                                    <option value="{{ $key }}" @selected(old('fault_category', $rma->fault_category) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Warranty Status at Claim <span class="text-amber-400">*</span></label>
                            <select name="warranty_status_at_claim" required class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5 font-bold">
                                <option value="under_warranty" @selected(old('warranty_status_at_claim', $rma->warranty_status_at_claim) === 'under_warranty')>🟢 Under Manufacturer Warranty</option>
                                <option value="out_of_warranty" @selected(old('warranty_status_at_claim', $rma->warranty_status_at_claim) === 'out_of_warranty')>🔴 Out of Warranty</option>
                                <option value="extended_warranty" @selected(old('warranty_status_at_claim', $rma->warranty_status_at_claim) === 'extended_warranty')>🟡 Extended Warranty</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Vendor's RMA Ref #</label>
                            <input type="text" name="vendor_rma_ref" value="{{ old('vendor_rma_ref', $rma->vendor_rma_ref) }}" 
                                class="w-full text-xs font-mono rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Expected Return Date</label>
                            <input type="date" name="expected_return_date" value="{{ old('expected_return_date', $rma->expected_return_date?->format('Y-m-d')) }}" 
                                class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Defect Description <span class="text-amber-400">*</span></label>
                        <textarea name="issue_description" rows="3" required 
                            class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">{{ old('issue_description', $rma->issue_description) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('rma.show', $rma) }}" class="px-5 py-2.5 rounded-xl border border-slate-700 text-xs font-bold text-slate-400 hover:text-white hover:bg-slate-800 transition">
                        Cancel
                    </a>
                    <button type="submit" class="btn-amber">
                        <span>Save Changes</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
