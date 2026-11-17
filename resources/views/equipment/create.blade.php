<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('equipment.index') }}" class="back-link">&larr; Back</a>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Register Installed Hardware</h2>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 max-w-[800px] mx-auto pb-32" x-data="equipmentRegisterForm(@js($products))" x-init="calculateWarranties()">

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

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        {{-- Customer / Site --}}
                        <div class="sm:col-span-2">
                            <div class="flex items-center justify-between mb-2">
                                <label class="crm-label mb-0 text-sm font-semibold text-slate-800 dark:text-slate-200">Customer Site / Lead <span class="required-marker text-rose-500">*</span></label>
                                @if(auth()->user()->isOffice())
                                <a href="{{ route('leads.create', ['return_to' => 'equipment']) }}" 
                                   class="px-3 py-1 text-xs font-semibold rounded-lg bg-[var(--crm-accent,#2563eb)] hover:opacity-90 active:scale-95 text-white transition cursor-pointer shadow-xs inline-flex items-center justify-center"
                                   style="color: #ffffff !important;">
                                    <span class="text-white font-semibold" style="color: #ffffff !important;">Add New Customer</span>
                                </a>
                                @endif
                            </div>
                            <select aria-label="Customer Site / Lead" name="lead_id" data-searchable="true" data-search-placeholder="Search customer name, phone, address..." class="crm-select crm-searchable-select w-full h-[44px] text-sm rounded-lg" required>
                                <option value="">-- Select Customer / Lead (or General Estimate) --</option>
                                @foreach($leads as $l)
                                    <option value="{{ $l->id }}" @selected(old('lead_id', $selectedLeadId) == $l->id)>
                                        {{ $l->customer_name }} ({{ $l->phone }}) {{ $l->site_address ? "— {$l->site_address}" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Installation Job (Optional) --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="crm-label mb-0 text-sm font-semibold text-slate-800 dark:text-slate-200">Associated Installation Job</label>
                                @if(auth()->user()->isOffice())
                                <a href="{{ route('jobs.create-general', ['return_to' => 'equipment']) }}" 
                                   target="_blank"
                                   class="px-3 py-1 text-xs font-semibold rounded-lg bg-[var(--crm-accent,#2563eb)] hover:opacity-90 active:scale-95 text-white transition cursor-pointer shadow-xs inline-flex items-center justify-center"
                                   style="color: #ffffff !important;">
                                    <span class="text-white font-semibold" style="color: #ffffff !important;">Add New Installation Job</span>
                                </a>
                                @endif
                            </div>
                            <select aria-label="Associated Installation Job" name="installation_job_id" data-searchable="true" data-search-placeholder="Search job number, client, or site..." class="crm-select crm-searchable-select w-full h-[44px] text-sm rounded-lg">
                                <option value="">-- None (Direct Entry / Existing Site) --</option>
                                @foreach($jobs as $j)
                                    <option value="{{ $j->id }}" @selected(old('installation_job_id', $selectedJobId) == $j->id)>
                                        {{ $j->job_no }} — {{ $j->quotation?->lead?->customer_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Product Catalog Reference --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="crm-label mb-0 text-sm font-semibold text-slate-800 dark:text-slate-200">Product Model (Catalog)</label>
                                <button type="button" 
                                        onclick="window.openQuickAddProductModal && window.openQuickAddProductModal({ targetSelectId: 'product_id_select', nameInputId: 'equipment_name_input' })" 
                                        class="px-3 py-1 text-xs font-semibold rounded-lg bg-[var(--crm-accent,#2563eb)] hover:opacity-90 active:scale-95 text-white transition cursor-pointer shadow-xs inline-flex items-center justify-center"
                                        style="color: #ffffff !important;">
                                    <span class="text-white font-semibold" style="color: #ffffff !important;">Add New Product Catalog</span>
                                </button>
                            </div>
                            <select id="product_id_select" aria-label="Product Model (Catalog)" name="product_id" data-searchable="true" data-search-placeholder="Search product model, name, or stock..." class="crm-select crm-searchable-select w-full h-[44px] text-sm rounded-lg" @change="onProductChange($event.target.value)">
                                <option value="">-- Select Product Model (Catalog) --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" @selected(old('product_id') == $p->id)>
                                        {{ $p->name }} {{ $p->model_no ? "({$p->model_no})" : '' }} [Stock: {{ $p->stock_quantity }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Equipment Name --}}
                    <div class="mb-5">
                        <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Equipment Name / Description <span class="required-marker text-rose-500">*</span></label>
                        <input aria-label="Equipment Name / Description" type="text" id="equipment_name_input" name="equipment_name" x-model="equipmentName" placeholder="e.g. 4MP ColorVu Bullet Camera" required class="crm-input w-full h-[44px] text-sm rounded-lg">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        {{-- Serial Number --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="crm-label mb-0 text-sm font-semibold text-slate-800 dark:text-slate-200">Hardware Serial Number (S/N) <span class="required-marker text-rose-500">*</span></label>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" onclick="openBarcodeScanner('equipment-barcode-modal', 'serial_number_input', window.onEquipmentScanSuccess, 'hardware')"
                                            class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-600 transition cursor-pointer shadow-xs">
                                        <span>Barcode Scanner</span>
                                    </button>
                                    <button type="button" onclick="openBarcodeScanner('equipment-barcode-modal', 'serial_number_input', window.onEquipmentScanSuccess, 'mobile')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-600 transition cursor-pointer shadow-xs">
                                        <span>Mobile Scanner</span>
                                    </button>
                                </div>
                            </div>
                            <input aria-label="Serial number" type="text" id="serial_number_input" name="serial_number" value="{{ old('serial_number', request('serial')) }}" placeholder="e.g. SN-HK2026-981247" required class="crm-input font-mono w-full h-[44px] text-sm rounded-lg">
                        </div>

                        {{-- MAC Address --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="crm-label mb-0 text-sm font-semibold text-slate-800 dark:text-slate-200">MAC / IP Address (Optional)</label>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" onclick="openBarcodeScanner('equipment-barcode-modal', 'mac_address_input', window.onEquipmentScanSuccess, 'hardware')"
                                            class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-600 transition cursor-pointer shadow-xs">
                                        <span>Barcode Scanner</span>
                                    </button>
                                    <button type="button" onclick="openBarcodeScanner('equipment-barcode-modal', 'mac_address_input', window.onEquipmentScanSuccess, 'mobile')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-600 transition cursor-pointer shadow-xs">
                                        <span>Mobile Scanner</span>
                                    </button>
                                </div>
                            </div>
                            <input aria-label="Mac address" type="text" id="mac_address_input" name="mac_address" value="{{ old('mac_address') }}" placeholder="e.g. BC:A8:A6:11:22:33" class="crm-input font-mono w-full h-[44px] text-sm rounded-lg">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        {{-- Location Tag --}}
                        <div>
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Location / Channel Placement</label>
                            <input aria-label="Location / Channel Placement" type="text" name="location_tag" value="{{ old('location_tag') }}" placeholder="e.g. Main Gate, Server Rack" class="crm-input w-full h-[44px] text-sm rounded-lg">
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Operational Status</label>
                            <select aria-label="Operational Status" name="status" class="crm-select w-full h-[44px] text-sm rounded-lg">
                                <option value="active" @selected(old('status') == 'active')>Active</option>
                                <option value="under_repair" @selected(old('status') == 'under_repair')>Under Repair</option>
                                <option value="replaced" @selected(old('status') == 'replaced')>Replaced</option>
                                <option value="decommissioned" @selected(old('status') == 'decommissioned')>Decommissioned</option>
                            </select>
                        </div>
                    </div>

                    <h3 class="mb-4 pb-2 border-b border-[#ddd] font-bold text-base text-gray-900 mt-6">Warranty Timeline</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-5">
                        <div>
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Installation Date</label>
                            <input aria-label="Installation Date" type="date" name="installation_date" x-model="installDate" @change="calculateWarranties()" class="crm-input w-full h-[44px] text-sm rounded-lg">
                        </div>
                        <div>
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Mfg Warranty Expiry</label>
                            <input aria-label="Mfg Warranty Expiry" type="date" name="manufacturer_warranty_expiry" x-model="mfgExpiry" class="crm-input w-full h-[44px] text-sm rounded-lg">
                        </div>
                        <div>
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Service Warranty Expiry</label>
                            <input aria-label="Service Warranty Expiry" type="date" name="service_warranty_expiry" x-model="serviceExpiry" class="crm-input w-full h-[44px] text-sm rounded-lg">
                        </div>
                    </div>

                    {{-- Stock Deduction Option --}}
                    <div class="mb-5 flex items-center gap-2.5 p-3.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg" x-show="selectedProduct">
                        <input type="checkbox" id="deduct_stock" name="deduct_stock" value="1" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <label for="deduct_stock" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer select-none">
                            Auto-deduct 1 unit from warehouse inventory for <span class="font-bold text-slate-900 dark:text-white" x-text="selectedProduct ? selectedProduct.name : ''"></span>
                        </label>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-6">
                        <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Technical Notes / Config Details</label>
                        <textarea aria-label="Technical Notes / Config Details" name="notes" rows="3" class="crm-input w-full p-3 text-sm rounded-lg" style="height: auto;" placeholder="e.g. IP 192.168.1.104, default admin credentials, Lens 2.8mm">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-4 border-t border-slate-200 dark:border-slate-800 max-w-full">
                        <button type="submit" class="w-full sm:w-auto btn-md min-h-[44px] h-auto px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md transition cursor-pointer inline-flex items-center justify-center gap-2 max-w-full text-center">
                            <span>Save &amp; Register Equipment</span>
                        </button>
                        <a href="{{ route('equipment.index') }}" class="w-full sm:w-auto btn-md min-h-[44px] h-auto px-5 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white hover:bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold text-sm transition inline-flex items-center justify-center text-center">
                            Cancel
                        </a>
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
                init() {
                    window.onEquipmentScanSuccess = (rawCode, parsed, lookupData, targetId) => {
                        this.handleScanResult(rawCode, parsed, lookupData, targetId);
                    };

                    // Hardware Barcode Scanner Gun direct scan handler (USB/Bluetooth)
                    const bindHardwareGun = (inputId) => {
                        const el = document.getElementById(inputId);
                        if (!el) return;
                        el.addEventListener('keydown', (e) => {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                const val = el.value.trim();
                                if (val) {
                                    const parsed = window.parseDeviceBarcode ? window.parseDeviceBarcode(val) : { serial: val, mac: '', model: '' };
                                    this.handleScanResult(val, parsed, null, inputId);
                                    el.classList.add('ring-2', 'ring-emerald-500');
                                    setTimeout(() => el.classList.remove('ring-2', 'ring-emerald-500'), 1200);
                                }
                            }
                        });
                    };
                    bindHardwareGun('serial_number_input');
                    bindHardwareGun('mac_address_input');
                },
                handleScanResult(rawCode, parsed, lookupData, targetId) {
                    const sInput = document.getElementById('serial_number_input');
                    const mInput = document.getElementById('mac_address_input');

                    if (targetId === 'mac_address_input') {
                        if (parsed && parsed.mac) {
                            if (mInput) mInput.value = parsed.mac;
                        }
                        if (parsed && parsed.serial && sInput && !sInput.value) {
                            sInput.value = parsed.serial;
                        }
                    } else {
                        if (parsed && parsed.serial) {
                            if (sInput) sInput.value = parsed.serial;
                        }
                        if (parsed && parsed.mac && mInput && !mInput.value) {
                            mInput.value = parsed.mac;
                        }
                    }

                    // Auto-fill Product Name / Equipment Name & Select Catalog Model
                    if (lookupData) {
                        if (lookupData.equipment_name || lookupData.product_name) {
                            this.equipmentName = lookupData.equipment_name || lookupData.product_name;
                        }
                        if (lookupData.product_id) {
                            const pSelect = document.querySelector('select[name="product_id"]');
                            if (pSelect) {
                                pSelect.value = lookupData.product_id;
                                this.onProductChange(lookupData.product_id);
                            }
                        }
                        if (lookupData.mac_address && mInput && !mInput.value) {
                            mInput.value = lookupData.mac_address;
                        }
                    }

                    // Auto-match model from barcode/QR code if product is not yet selected
                    if (parsed && parsed.model && (!this.selectedProduct || !this.selectedProduct.id)) {
                        const matched = this.products.find(p => 
                            (p.model_no && p.model_no.toLowerCase().includes(parsed.model.toLowerCase())) ||
                            (p.name && p.name.toLowerCase().includes(parsed.model.toLowerCase()))
                        );
                        if (matched) {
                            const pSelect = document.querySelector('select[name="product_id"]');
                            if (pSelect) {
                                pSelect.value = matched.id;
                                this.onProductChange(matched.id);
                            }
                        }
                    }
                },
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
