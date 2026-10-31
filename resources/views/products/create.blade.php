<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('products.index') }}" class="back-link">&larr; Back</a>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Add Product</h2>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 max-w-[800px] mx-auto">

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
                <form method="POST" action="{{ route('products.store') }}">
                    @csrf

                    <h3 class="mb-4 pb-2 border-b border-[#ddd] font-bold text-base text-gray-900">Product Information</h3>

                    <div class="mb-4">
                        <label class="crm-label">Product / Service Name <span class="required-marker">*</span></label>
                        <input aria-label="Product / Service Name" type="text" name="name" value="{{ old('name') }}" class="crm-input" placeholder="e.g. Hikvision 4MP Bullet Camera" required>
                        @error('name') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Category</label>
                            <input aria-label="Category" type="text" name="category" list="category-list" value="{{ old('category') }}" class="crm-input" placeholder="e.g. Camera">
                            <datalist id="category-list">
                                <option value="Camera">
                                <option value="NVR/DVR">
                                <option value="Storage / HDD">
                                <option value="Power Supply">
                                <option value="Cabling & Wire">
                                <option value="Networking & PoE">
                                <option value="Connectors & Accessories">
                                <option value="Installation Service">
                            </datalist>
                            @error('category') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="crm-label">Brand / Manufacturer</label>
                            <input aria-label="Brand / Manufacturer" type="text" name="brand" list="brand-list" value="{{ old('brand') }}" class="crm-input" placeholder="e.g. Hikvision">
                            <datalist id="brand-list">
                                <option value="Hikvision">
                                <option value="Dahua">
                                <option value="CP Plus">
                                <option value="Western Digital">
                                <option value="Seagate">
                                <option value="Uniview">
                                <option value="TP-Link">
                                <option value="D-Link">
                            </datalist>
                            @error('brand') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Model Number</label>
                            <input aria-label="Model Number" type="text" name="model_no" value="{{ old('model_no') }}" class="crm-input" placeholder="e.g. DS-2CD2043G2-I">
                            @error('model_no') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="crm-label">SKU / Product Code</label>
                            <input aria-label="SKU / Product Code" type="text" name="sku" value="{{ old('sku') }}" class="crm-input" placeholder="e.g. HK-4MP-BULLET">
                            @error('sku') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Unit <span class="required-marker">*</span></label>
                            <select aria-label="Unit" name="unit" class="crm-select" required>
                                @foreach (['Nos', 'Mtr', 'Box', 'Set', 'Job', 'Pkt', 'Roll'] as $unit)
                                    <option value="{{ $unit }}" @selected(old('unit', 'Nos') === $unit)>{{ $unit }}</option>
                                @endforeach
                            </select>
                            @error('unit') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="crm-label">Default Warranty (Months)</label>
                            <input aria-label="Default Warranty (Months)" type="number" name="default_warranty_months" value="{{ old('default_warranty_months', 24) }}" min="0" class="crm-input" placeholder="e.g. 24">
                        </div>
                    </div>

                    <h3 class="mb-4 pb-2 border-b border-[#ddd] font-bold text-base text-gray-900 mt-6">Inventory & Pricing</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Opening Stock Quantity</label>
                            <input aria-label="Opening Stock Quantity" type="number" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0" class="crm-input" placeholder="e.g. 10">
                            @error('stock_quantity') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="crm-label">Low Stock Alert Level</label>
                            <input aria-label="Low Stock Alert Level" type="number" name="min_stock_alert" value="{{ old('min_stock_alert', 5) }}" min="0" class="crm-input" placeholder="e.g. 5">
                            @error('min_stock_alert') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Cost Price (₹) <span class="required-marker">*</span></label>
                            <input aria-label="Cost Price (₹)" type="number" name="cost_price" id="cost_price" value="{{ old('cost_price', 0) }}" min="0" step="0.01" class="crm-input" placeholder="e.g. 2900" required oninput="calcMargin()">
                            @error('cost_price') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="crm-label">Sale Price (₹) <span class="required-marker">*</span></label>
                            <input aria-label="Sale Price (₹)" type="number" name="unit_price" id="unit_price" value="{{ old('unit_price', 0) }}" min="0" step="0.01" class="crm-input" placeholder="e.g. 3500" required oninput="calcMargin()">
                            @error('unit_price') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div id="margin-hint" class="text-sm font-bold text-[#16a34a] mb-4" style="display: none;"></div>

                    <div class="mb-4">
                        <label class="crm-label">Description / Specification</label>
                        <textarea aria-label="Description / Specification" name="description" rows="3" class="crm-input" style="height: auto;" placeholder="e.g. 4MP outdoor IR bullet camera, 30m night vision">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <input type="hidden" name="is_active" value="0">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" @checked((int)old('is_active', 1) === 1) class="w-4 h-4 rounded-[4px] border-[#ccc] text-blue-600">
                            <span class="text-sm text-gray-700">Available for new quotations</span>
                        </label>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('products.index') }}" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>

    <script>
    function calcMargin() {
        const cost  = parseFloat(document.getElementById('cost_price').value) || 0;
        const sale  = parseFloat(document.getElementById('unit_price').value) || 0;
        const hint  = document.getElementById('margin-hint');
        if (sale > 0 && cost >= 0) {
            const profit = sale - cost;
            const pct    = ((profit / sale) * 100).toFixed(1);
            hint.style.display = 'block';
            hint.textContent   = `Margin: ₹${profit.toLocaleString('en-IN')} (${pct}%)`;
            hint.className = profit >= 0 ? 'text-sm font-bold text-[#16a34a] mb-4' : 'text-sm font-bold text-[#dc2626] mb-4';
        } else {
            hint.style.display = 'none';
        }
    }
    calcMargin();
    </script>
</x-app-layout>