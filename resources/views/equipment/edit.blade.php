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
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Customer Site / Lead <span class="required-marker text-rose-500">*</span></label>
                            <select name="lead_id" data-no-tom="true" class="crm-select w-full h-[44px] text-sm rounded-lg" required>
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
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Associated Installation Job</label>
                            <select name="installation_job_id" data-no-tom="true" class="crm-select w-full h-[44px] text-sm rounded-lg">
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
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Product Model (Catalog)</label>
                            <select name="product_id" data-no-tom="true" class="crm-select w-full h-[44px] text-sm rounded-lg">
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
                        <input type="text" name="equipment_name" value="{{ old('equipment_name', $equipment->equipment_name) }}" required class="crm-input w-full h-[44px] text-sm rounded-lg">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        {{-- Serial Number --}}
                        <div>
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Hardware Serial Number (S/N) <span class="required-marker text-rose-500">*</span></label>
                            <input type="text" id="serial_number_input" name="serial_number" value="{{ old('serial_number', $equipment->serial_number) }}" required class="crm-input font-mono w-full h-[44px] text-sm rounded-lg">
                        </div>

                        {{-- MAC Address --}}
                        <div>
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">MAC / IP Address (Optional)</label>
                            <input type="text" id="mac_address_input" name="mac_address" value="{{ old('mac_address', $equipment->mac_address) }}" class="crm-input font-mono w-full h-[44px] text-sm rounded-lg">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        {{-- Location Tag --}}
                        <div>
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Location / Channel Placement</label>
                            <input type="text" name="location_tag" value="{{ old('location_tag', $equipment->location_tag) }}" class="crm-input w-full h-[44px] text-sm rounded-lg">
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Operational Status</label>
                            <select name="status" class="crm-select w-full h-[44px] text-sm rounded-lg">
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
                            <input type="date" name="installation_date" value="{{ old('installation_date', $equipment->installation_date?->format('Y-m-d')) }}" class="crm-input w-full h-[44px] text-sm rounded-lg">
                        </div>
                        <div>
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Mfg Warranty Expiry</label>
                            <input type="date" name="manufacturer_warranty_expiry" value="{{ old('manufacturer_warranty_expiry', $equipment->manufacturer_warranty_expiry?->format('Y-m-d')) }}" class="crm-input w-full h-[44px] text-sm rounded-lg">
                        </div>
                        <div>
                            <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Service Warranty Expiry</label>
                            <input type="date" name="service_warranty_expiry" value="{{ old('service_warranty_expiry', $equipment->service_warranty_expiry?->format('Y-m-d')) }}" class="crm-input w-full h-[44px] text-sm rounded-lg">
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-6">
                        <label class="crm-label text-sm font-semibold text-slate-800 dark:text-slate-200 mb-2 block">Technical Notes / Config Details</label>
                        <textarea name="notes" rows="3" class="crm-input w-full p-3 text-sm rounded-lg" style="height: auto;">{{ old('notes', $equipment->notes) }}</textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="submit" class="h-[44px] px-6 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md transition cursor-pointer inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span>Save Changes</span>
                        </button>
                        <a href="{{ route('equipment.show', $equipment) }}" class="h-[44px] px-5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white hover:bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold text-sm transition inline-flex items-center">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
</x-app-layout>
