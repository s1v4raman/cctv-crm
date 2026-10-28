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

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        {{-- Customer / Site --}}
                        <div class="sm:col-span-2">
                            <div class="flex items-center justify-between mb-2">
                                <label class="crm-label mb-0 text-sm font-semibold text-slate-800 dark:text-slate-200">Customer Site / Lead <span class="required-marker text-rose-500">*</span></label>
                                <a href="{{ route('leads.create', ['return_to' => 'equipment']) }}" 
                                   class="btn-subtle text-white font-semibold"
                                   style="color: #ffffff !important;">
                                    <span class="text-white font-semibold" style="color: #ffffff !important;">Add New Customer</span>
                                </a>
                            </div>
                            <select aria-label="Customer Site / Lead" name="lead_id" data-no-tom="true" class="crm-select w-full h-[44px] text-sm rounded-lg" required>
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
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Associated Installation Job</label>
                            <select aria-label="Associated Installation Job" name="installation_job_id" data-no-tom="true" class="crm-select w-full h-[44px] text-sm rounded-lg">
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
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Product Model (Catalog)</label>
                            <select aria-label="Product Model (Catalog)" name="product_id" data-no-tom="true" class="crm-select w-full h-[44px] text-sm rounded-lg" @change="onProductChange($event.target.value)">
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
                        <input aria-label="Equipment Name / Description" type="text" name="equipment_name" x-model="equipmentName" placeholder="e.g. 4MP ColorVu Bullet Camera" required class="crm-input w-full h-[44px] text-sm rounded-lg">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        {{-- Serial Number --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="crm-label mb-0 text-sm font-semibold text-slate-800 dark:text-slate-200">Hardware Serial Number (S/N) <span class="required-marker text-rose-500">*</span></label>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" onclick="openBarcodeScanner('equipment-barcode-modal', 'serial_number_input', null, 'laptop')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700 transition cursor-pointer shadow-xs">
                                        <svg class="w-3.5 h-3.5 text-slate-700 dark:text-slate-300" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3"/></svg>
                                        <span>Laptop Cam</span>
                                    </button>
                                    <button type="button" onclick="openBarcodeScanner('equipment-barcode-modal', 'serial_number_input', null, 'mobile')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700 transition cursor-pointer shadow-xs">
                                        <svg class="w-3.5 h-3.5 text-slate-700 dark:text-slate-300" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
                                        <span>Mobile Cam</span>
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
                                    <button type="button" onclick="openBarcodeScanner('equipment-barcode-modal', 'mac_address_input', null, 'laptop')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700 transition cursor-pointer shadow-xs">
                                        <svg class="w-3.5 h-3.5 text-slate-700 dark:text-slate-300" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3"/></svg>
                                        <span>Laptop Cam</span>
                                    </button>
                                    <button type="button" onclick="openBarcodeScanner('equipment-barcode-modal', 'mac_address_input', null, 'mobile')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700 transition cursor-pointer shadow-xs">
                                        <svg class="w-3.5 h-3.5 text-slate-700 dark:text-slate-300" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
                                        <span>Mobile Cam</span>
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

                    <div class="flex items-center gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="submit" class="h-[44px] px-6 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md transition cursor-pointer inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span>Save &amp; Register Equipment</span>
                        </button>
                        <a href="{{ route('equipment.index') }}" class="h-[44px] px-5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white hover:bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold text-sm transition inline-flex items-center">
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
