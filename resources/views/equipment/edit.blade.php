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

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        {{-- Customer / Site --}}
                        <div class="sm:col-span-2">
                            <label class="crm-label">Customer Site / Lead <span class="required-marker">*</span></label>
                            <select name="lead_id" class="crm-select" required>
                                @foreach($leads as $l)
                                    <option value="{{ $l->id }}" @selected(old('lead_id', $equipment->lead_id) == $l->id)>
                                        {{ $l->customer_name }} ({{ $l->phone }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Installation Job --}}
                        <div>
                            <label class="crm-label">Associated Job</label>
                            <select name="installation_job_id" class="crm-select">
                                <option value="">None (Standalone)</option>
                                @foreach($jobs as $j)
                                    <option value="{{ $j->id }}" @selected(old('installation_job_id', $equipment->installation_job_id) == $j->id)>
                                        {{ $j->job_no }} &mdash; {{ $j->quotation?->lead?->customer_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Product Catalog Reference --}}
                        <div>
                            <label class="crm-label">Product Model (Catalog)</label>
                            <select name="product_id" class="crm-select">
                                <option value="">Select Catalog Item...</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" @selected(old('product_id', $equipment->product_id) == $p->id)>
                                        {{ $p->name }} {{ $p->model_no ? "({$p->model_no})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Equipment Name --}}
                    <div class="mb-4">
                        <label class="crm-label">Equipment Name / Description <span class="required-marker">*</span></label>
                        <input type="text" name="equipment_name" value="{{ old('equipment_name', $equipment->equipment_name) }}" required class="crm-input">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        {{-- Serial Number --}}
                        <div>
                            <label class="crm-label">Hardware Serial Number (S/N) <span class="required-marker">*</span></label>
                            <input type="text" id="serial_number_input" name="serial_number" value="{{ old('serial_number', $equipment->serial_number) }}" required class="crm-input font-mono">
                        </div>

                        {{-- MAC Address --}}
                        <div>
                            <label class="crm-label">MAC / IP Address (Optional)</label>
                            <input type="text" id="mac_address_input" name="mac_address" value="{{ old('mac_address', $equipment->mac_address) }}" class="crm-input font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        {{-- Location Tag --}}
                        <div>
                            <label class="crm-label">Location / Channel Placement</label>
                            <input type="text" name="location_tag" value="{{ old('location_tag', $equipment->location_tag) }}" class="crm-input">
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="crm-label">Operational Status</label>
                            <select name="status" class="crm-select">
                                <option value="active" @selected(old('status', $equipment->status) == 'active')>Active</option>
                                <option value="under_repair" @selected(old('status', $equipment->status) == 'under_repair')>Under Repair</option>
                                <option value="replaced" @selected(old('status', $equipment->status) == 'replaced')>Replaced</option>
                                <option value="decommissioned" @selected(old('status', $equipment->status) == 'decommissioned')>Decommissioned</option>
                            </select>
                        </div>
                    </div>

                    <h3 class="mb-4 pb-2 border-b border-[#ddd] font-bold text-base text-gray-900 mt-6">Warranty Timeline</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Installation Date</label>
                            <input type="date" name="installation_date" value="{{ old('installation_date', $equipment->installation_date?->format('Y-m-d')) }}" class="crm-input">
                        </div>
                        <div>
                            <label class="crm-label">Mfg Warranty Expiry</label>
                            <input type="date" name="manufacturer_warranty_expiry" value="{{ old('manufacturer_warranty_expiry', $equipment->manufacturer_warranty_expiry?->format('Y-m-d')) }}" class="crm-input">
                        </div>
                        <div>
                            <label class="crm-label">Service Warranty Expiry</label>
                            <input type="date" name="service_warranty_expiry" value="{{ old('service_warranty_expiry', $equipment->service_warranty_expiry?->format('Y-m-d')) }}" class="crm-input">
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-4">
                        <label class="crm-label">Technical Notes / Config Details</label>
                        <textarea name="notes" rows="2" class="crm-input" style="height: auto;">{{ old('notes', $equipment->notes) }}</textarea>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('equipment.show', $equipment) }}" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
</x-app-layout>
