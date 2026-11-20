<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('equipment.show', $equipment) }}" class="back-link">&larr; Back</a>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Edit Equipment &mdash; {{ $equipment->equipment_name }}</h2>
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
                <form method="POST" action="{{ route('equipment.update', $equipment) }}">
                    @csrf
                    @method('PUT')

                    <h3 class="mb-4 pb-2 border-b border-[#ddd] font-bold text-base text-gray-900">Hardware & Site Details</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        {{-- Customer / Site --}}
                        <div class="sm:col-span-2">
                            <div class="flex items-center justify-between mb-2">
                                <label class="crm-label mb-0 text-sm font-semibold text-slate-800 dark:text-slate-200">Customer Site / Lead <span class="required-marker text-rose-500">*</span></label>
                                @if(auth()->user()->isOffice())
                                <a href="{{ route('leads.create', ['return_to' => route('equipment.edit', $equipment)]) }}" 
                                   class="px-3 py-1 text-xs font-semibold rounded-lg bg-[var(--crm-accent,#2563eb)] hover:opacity-90 active:scale-95 text-white transition cursor-pointer shadow-xs inline-flex items-center justify-center"
                                   style="color: #ffffff !important;">
                                    <span class="text-white font-semibold" style="color: #ffffff !important;">Add New Customer</span>
                                </a>
                                @endif
                            </div>
                            <select aria-label="Customer Site / Lead" name="lead_id" data-searchable="true" data-search-placeholder="Search customer name, phone, address..." class="crm-select crm-searchable-select w-full h-[44px] text-sm rounded-lg" required>
                                <option value="">-- Select Customer / Lead (or General Estimate) --</option>
                                @foreach($leads as $l)
                                    <option value="{{ $l->id }}" @selected(old('lead_id', $equipment->lead_id) == $l->id)>
                                        {{ $l->customer_name }} ({{ $l->phone }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Installation Job --}}
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
                                    <option value="{{ $j->id }}" @selected(old('installation_job_id', $equipment->installation_job_id) == $j->id)>
                                        {{ $j->job_no }} &mdash; {{ $j->quotation?->lead?->customer_name }}
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
                            <select id="product_id_select" aria-label="Product Model (Catalog)" name="product_id" data-searchable="true" data-search-placeholder="Search product model, name, or stock..." class="crm-select crm-searchable-select w-full h-[44px] text-sm rounded-lg">
                                <option value="">-- Select Product Model (Catalog) --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" @selected(old('product_id', $equipment->product_id) == $p->id)>
                                        {{ $p->name }} {{ $p->model_no ? "({$p->model_no})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Equipment Name --}}
                    <div class="mb-5">
                        <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Equipment Name / Description <span class="required-marker text-rose-500">*</span></label>
                        <input aria-label="Equipment Name / Description" type="text" id="equipment_name_input" name="equipment_name" value="{{ old('equipment_name', $equipment->equipment_name) }}" required class="crm-input w-full h-[44px] text-sm rounded-lg">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        {{-- Serial Number --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="crm-label mb-0 text-sm font-semibold text-slate-800 dark:text-slate-200">Hardware Serial Number (S/N) <span class="required-marker text-rose-500">*</span></label>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" onclick="openBarcodeScanner('equipment-barcode-modal', 'serial_number_input', window.onEquipmentEditScanSuccess, 'machine')"
                                            class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-600 transition cursor-pointer shadow-xs">
                                        <span>⚡ {{ __('Barcode Machine') }}</span>
                                    </button>
                                    <button type="button" onclick="openBarcodeScanner('equipment-barcode-modal', 'serial_number_input', window.onEquipmentEditScanSuccess, 'mobile')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-600 transition cursor-pointer shadow-xs">
                                        <span>📱 {{ __('Mobile Scan') }}</span>
                                    </button>
                                </div>
                            </div>
                            <input aria-label="Hardware Serial Number (S/N)" type="text" id="serial_number_input" name="serial_number" value="{{ old('serial_number', $equipment->serial_number) }}" required class="crm-input font-mono w-full h-[44px] text-sm rounded-lg">
                        </div>

                        {{-- MAC Address --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="crm-label mb-0 text-sm font-semibold text-slate-800 dark:text-slate-200">MAC / IP Address (Optional)</label>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" onclick="openBarcodeScanner('equipment-barcode-modal', 'mac_address_input', window.onEquipmentEditScanSuccess, 'machine')"
                                            class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-600 transition cursor-pointer shadow-xs">
                                        <span>⚡ {{ __('Barcode Machine') }}</span>
                                    </button>
                                    <button type="button" onclick="openBarcodeScanner('equipment-barcode-modal', 'mac_address_input', window.onEquipmentEditScanSuccess, 'mobile')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-600 transition cursor-pointer shadow-xs">
                                        <span>📱 {{ __('Mobile Scan') }}</span>
                                    </button>
                                </div>
                            </div>
                            <input aria-label="MAC / IP Address (Optional)" type="text" id="mac_address_input" name="mac_address" value="{{ old('mac_address', $equipment->mac_address) }}" class="crm-input font-mono w-full h-[44px] text-sm rounded-lg">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        {{-- Location Tag --}}
                        <div>
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Location / Channel Placement</label>
                            <input aria-label="Location / Channel Placement" type="text" name="location_tag" value="{{ old('location_tag', $equipment->location_tag) }}" class="crm-input w-full h-[44px] text-sm rounded-lg">
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Operational Status</label>
                            <select aria-label="Operational Status" name="status" class="crm-select w-full h-[44px] text-sm rounded-lg">
                                <option value="active" @selected(old('status', $equipment->status) == 'active')>Active</option>
                                <option value="under_repair" @selected(old('status', $equipment->status) == 'under_repair')>Under Repair</option>
                                <option value="replaced" @selected(old('status', $equipment->status) == 'replaced')>Replaced</option>
                                <option value="decommissioned" @selected(old('status', $equipment->status) == 'decommissioned')>Decommissioned</option>
                            </select>
                        </div>
                    </div>

                    <h3 class="mb-4 pb-2 border-b border-[#ddd] font-bold text-base text-gray-900 mt-6">Warranty Timeline</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-5">
                        <div>
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Installation Date</label>
                            <input aria-label="Installation Date" type="date" name="installation_date" value="{{ old('installation_date', $equipment->installation_date?->format('Y-m-d')) }}" class="crm-input w-full h-[44px] text-sm rounded-lg">
                        </div>
                        <div>
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Mfg Warranty Expiry</label>
                            <input aria-label="Mfg Warranty Expiry" type="date" name="manufacturer_warranty_expiry" value="{{ old('manufacturer_warranty_expiry', $equipment->manufacturer_warranty_expiry?->format('Y-m-d')) }}" class="crm-input w-full h-[44px] text-sm rounded-lg">
                        </div>
                        <div>
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Service Warranty Expiry</label>
                            <input aria-label="Service Warranty Expiry" type="date" name="service_warranty_expiry" value="{{ old('service_warranty_expiry', $equipment->service_warranty_expiry?->format('Y-m-d')) }}" class="crm-input w-full h-[44px] text-sm rounded-lg">
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-6">
                        <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Technical Notes / Config Details</label>
                        <textarea aria-label="Technical Notes / Config Details" name="notes" rows="3" class="crm-input w-full p-3 text-sm rounded-lg" style="height: auto;">{{ old('notes', $equipment->notes) }}</textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="submit" class="btn-md h-[44px] px-6 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md transition cursor-pointer inline-flex items-center gap-2">
                            <span>Save Changes</span>
                        </button>
                        <a href="{{ route('equipment.show', $equipment) }}" class="btn-md h-[44px] px-5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white hover:bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold text-sm transition inline-flex items-center">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

    <x-barcode-scanner modalId="equipment-barcode-modal" title="Scan Device Serial / Barcode" />

    <script>
        window.onEquipmentEditScanSuccess = function(rawCode, parsed, lookupData, targetId) {
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

            if (lookupData) {
                if (lookupData.equipment_name || lookupData.product_name) {
                    const nameInput = document.getElementById('equipment_name_input');
                    if (nameInput) nameInput.value = lookupData.equipment_name || lookupData.product_name;
                }
                if (lookupData.product_id) {
                    const pSelect = document.querySelector('select[name="product_id"]');
                    if (pSelect) pSelect.value = lookupData.product_id;
                }
                if (lookupData.mac_address && mInput && !mInput.value) {
                    mInput.value = lookupData.mac_address;
                }
            }
        };

        // Direct Hardware Barcode Scanner Gun handler (USB/Bluetooth)
        document.addEventListener('DOMContentLoaded', () => {
            const bindHardwareGun = (inputId) => {
                const el = document.getElementById(inputId);
                if (!el) return;
                el.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const val = el.value.trim();
                        if (val) {
                            const parsed = window.parseDeviceBarcode ? window.parseDeviceBarcode(val) : { serial: val, mac: '', model: '' };
                            window.onEquipmentEditScanSuccess(val, parsed, null, inputId);
                            el.classList.add('ring-2', 'ring-emerald-500');
                            setTimeout(() => el.classList.remove('ring-2', 'ring-emerald-500'), 1200);
                        }
                    }
                });
            };
            bindHardwareGun('serial_number_input');
            bindHardwareGun('mac_address_input');
        });
    </script>
</x-app-layout>
