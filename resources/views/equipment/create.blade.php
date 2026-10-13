<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('equipment.index') }}" class="back-link">&larr; Back</a>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Register Installed Hardware</h2>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 max-w-[800px] mx-auto" x-data="equipmentRegisterForm(@js($products))" x-init="calculateWarranties()">

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
                <form method="POST" action="{{ route('equipment.store') }}">
                    @csrf

                    @if(request('redirect_to'))
                        <input type="hidden" name="redirect_to" value="{{ request('redirect_to') }}">
                    @endif

                    <h3 class="mb-4 pb-2 border-b border-[#ddd] font-bold text-base text-gray-900">Hardware & Site Details</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        {{-- Customer / Site --}}
                        <div class="sm:col-span-2">
                            <label class="crm-label">Customer Site / Lead <span class="required-marker">*</span></label>
                            <select name="lead_id" class="crm-select" required>
                                <option value="">Select Customer / Lead...</option>
                                @foreach($leads as $l)
                                    <option value="{{ $l->id }}" @selected(old('lead_id', $selectedLeadId) == $l->id)>
                                        {{ $l->customer_name }} ({{ $l->phone }}) {{ $l->site_address ? "— {$l->site_address}" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Installation Job (Optional) --}}
                        <div>
                            <label class="crm-label">Associated Installation Job</label>
                            <select name="installation_job_id" class="crm-select">
                                <option value="">None (Direct Entry / Existing Site)</option>
                                @foreach($jobs as $j)
                                    <option value="{{ $j->id }}" @selected(old('installation_job_id', $selectedJobId) == $j->id)>
                                        {{ $j->job_no }} — {{ $j->quotation?->lead?->customer_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Product Catalog Reference --}}
                        <div>
                            <label class="crm-label">Product Model (Catalog)</label>
                            <select name="product_id" class="crm-select" @change="onProductChange($event.target.value)">
                                <option value="">Select Catalog Item...</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" @selected(old('product_id') == $p->id)>
                                        {{ $p->name }} {{ $p->model_no ? "({$p->model_no})" : '' }} [Stock: {{ $p->stock_quantity }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Equipment Name --}}
                    <div class="mb-4">
                        <label class="crm-label">Equipment Name / Description <span class="required-marker">*</span></label>
                        <input type="text" name="equipment_name" x-model="equipmentName" placeholder="e.g. 4MP ColorVu Bullet Camera" required class="crm-input">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        {{-- Serial Number --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="crm-label mb-0">Hardware Serial Number (S/N) <span class="required-marker">*</span></label>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="openBarcodeScanner('equipment-barcode-modal', 'serial_number_input', null, 'laptop')"
                                            class="action-link text-xs" style="background: none; border: none; cursor: pointer;">
                                        Laptop Cam
                                    </button>
                                    <button type="button" onclick="openBarcodeScanner('equipment-barcode-modal', 'serial_number_input', null, 'mobile')"
                                            class="action-link text-xs" style="background: none; border: none; cursor: pointer;">
                                        Mobile Cam
                                    </button>
                                </div>
                            </div>
                            <input type="text" id="serial_number_input" name="serial_number" value="{{ old('serial_number', request('serial')) }}" placeholder="e.g. SN-HK2026-981247" required class="crm-input font-mono">
                        </div>

                        {{-- MAC Address --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="crm-label mb-0">MAC / IP Address (Optional)</label>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="openBarcodeScanner('equipment-barcode-modal', 'mac_address_input', null, 'laptop')"
                                            class="action-link text-xs" style="background: none; border: none; cursor: pointer;">
                                        Laptop Cam
                                    </button>
                                    <button type="button" onclick="openBarcodeScanner('equipment-barcode-modal', 'mac_address_input', null, 'mobile')"
                                            class="action-link text-xs" style="background: none; border: none; cursor: pointer;">
                                        Mobile Cam
                                    </button>
                                </div>
                            </div>
                            <input type="text" id="mac_address_input" name="mac_address" value="{{ old('mac_address') }}" placeholder="e.g. BC:A8:A6:11:22:33" class="crm-input font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        {{-- Location Tag --}}
                        <div>
                            <label class="crm-label">Location / Channel Placement</label>
                            <input type="text" name="location_tag" value="{{ old('location_tag') }}" placeholder="e.g. Main Gate, Server Rack" class="crm-input">
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="crm-label">Operational Status</label>
                            <select name="status" class="crm-select">
                                <option value="active" @selected(old('status') == 'active')>Active</option>
                                <option value="under_repair" @selected(old('status') == 'under_repair')>Under Repair</option>
                                <option value="replaced" @selected(old('status') == 'replaced')>Replaced</option>
                                <option value="decommissioned" @selected(old('status') == 'decommissioned')>Decommissioned</option>
                            </select>
                        </div>
                    </div>

                    <h3 class="mb-4 pb-2 border-b border-[#ddd] font-bold text-base text-gray-900 mt-6">Warranty Timeline</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Installation Date</label>
                            <input type="date" name="installation_date" x-model="installDate" @change="calculateWarranties()" class="crm-input">
                        </div>
                        <div>
                            <label class="crm-label">Mfg Warranty Expiry</label>
                            <input type="date" name="manufacturer_warranty_expiry" x-model="mfgExpiry" class="crm-input">
                        </div>
                        <div>
                            <label class="crm-label">Service Warranty Expiry</label>
                            <input type="date" name="service_warranty_expiry" x-model="serviceExpiry" class="crm-input">
                        </div>
                    </div>

                    {{-- Stock Deduction Option --}}
                    <div class="mb-4 flex items-center gap-2 p-3 bg-[#f8fafc] border border-[#ddd] rounded-[4px]" x-show="selectedProduct">
                        <input type="checkbox" id="deduct_stock" name="deduct_stock" value="1" class="w-4 h-4 rounded-[4px] border-[#ccc] text-[#2563eb]">
                        <label for="deduct_stock" class="text-sm text-gray-700 cursor-pointer select-none">
                            Auto-deduct 1 unit from warehouse inventory for <span class="font-bold text-gray-900" x-text="selectedProduct ? selectedProduct.name : ''"></span>
                        </label>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-4">
                        <label class="crm-label">Technical Notes / Config Details</label>
                        <textarea name="notes" rows="2" class="crm-input" style="height: auto;" placeholder="e.g. IP 192.168.1.104, default admin credentials, Lens 2.8mm">{{ old('notes') }}</textarea>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('equipment.index') }}" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>

    <x-barcode-scanner modalId="equipment-barcode-modal" title="Scan Device Serial / Barcode" />

    <script>
        function equipmentRegisterForm(productsList) {
            return {
                products: productsList || [],
                selectedProduct: null,
                equipmentName: '{{ old('equipment_name', '') }}',
                installDate: '{{ old('installation_date', date('Y-m-d')) }}',
                mfgExpiry: '{{ old('manufacturer_warranty_expiry', '') }}',
                serviceExpiry: '{{ old('service_warranty_expiry', '') }}',
                onProductChange(productId) {
                    this.selectedProduct = this.products.find(p => p.id == productId);
                    if (this.selectedProduct) {
                        if (!this.equipmentName) {
                            this.equipmentName = this.selectedProduct.name;
                        }
                        this.calculateWarranties();
                    }
                },
                calculateWarranties() {
                    if (!this.installDate) return;
                    const d = new Date(this.installDate);
                    if (isNaN(d.getTime())) return;

                    const months = (this.selectedProduct && this.selectedProduct.default_warranty_months) ? parseInt(this.selectedProduct.default_warranty_months) : 24;
                    
                    const mfgDate = new Date(d);
                    mfgDate.setMonth(mfgDate.getMonth() + months);
                    this.mfgExpiry = mfgDate.toISOString().split('T')[0];

                    const svcDate = new Date(d);
                    svcDate.setMonth(svcDate.getMonth() + 12);
                    this.serviceExpiry = svcDate.toISOString().split('T')[0];
                }
            };
        }
    </script>
</x-app-layout>
