<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Installed Equipment</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $equipment->equipment_name }} &middot; S/N: <span class="font-mono font-bold text-gray-700">{{ $equipment->serial_number }}</span></p>
            </div>
            <a href="{{ route('equipment.show', $equipment) }}" class="btn-head-secondary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                View Equipment
            </a>
        </div>
    </x-slot>

    <style>
        .pg-wrap  { background:#f1f5f9; min-height:100vh; padding:1.75rem 0 3rem; }
        .pg-inner { max-width:780px; margin:0 auto; padding:0 1.25rem; }

        .btn-head-secondary {
            display: inline-flex; align-items: center;
            background-color: #ffffff; color: #334155 !important;
            padding: 0.5rem 1rem; border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 600; text-decoration: none;
            border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.15s ease-in-out;
        }
        .btn-head-secondary:hover { background-color: #f8fafc; color: #0f172a !important; border-color: #94a3b8; }

        .pg-card {
            background:#fff;
            border-radius:1rem;
            border:1px solid rgba(99,102,241,.08);
            box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(99,102,241,.06);
            padding: 2rem;
        }

        .form-label {
            display: block; font-size: 0.875rem; font-weight: 600;
            color: #475569; margin-bottom: 0.4rem;
        }
        .form-input, .form-select, .form-textarea {
            width: 100%; border: 1px solid #cbd5e1; border-radius: 0.6rem;
            min-height: 44px; padding: 0.625rem 0.95rem; font-size: 0.9375rem; border-radius: 0.75rem; color: #1e293b;
            background: #fff; outline: none; transition: all 0.15s;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .btn-submit-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            min-height: 44px; padding: 0.625rem 1.5rem; border-radius: 0.75rem; font-size: 0.9375rem; font-weight: 700;
            background-color: #4f46e5; color: #ffffff !important;
            border: 1px solid #4338ca; cursor: pointer;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.25);
            transition: all 0.15s ease-in-out;
        }
        .btn-submit-primary:hover {
            background-color: #4338ca;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(79, 70, 229, 0.35);
        }

        .btn-cancel {
            display: inline-flex; align-items: center; justify-content: center;
            min-height: 44px; padding: 0.625rem 1.25rem; border-radius: 0.75rem; font-size: 0.9375rem; font-weight: 600;
            background-color: #ffffff; color: #475569 !important;
            border: 1px solid #cbd5e1; text-decoration: none;
            transition: all 0.15s ease-in-out;
        }
        .btn-cancel:hover { background-color: #f8fafc; color: #0f172a !important; border-color: #94a3b8; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if ($errors->any())
                <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="pg-card">
                <form method="POST" action="{{ route('equipment.update', $equipment) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        {{-- Customer / Site --}}
                        <div class="sm:col-span-2">
                            <label class="form-label">Customer Site / Lead <span class="text-red-500">*</span></label>
                            <select name="lead_id" class="form-select searchable-select" required>
                                @foreach($leads as $l)
                                    <option value="{{ $l->id }}" @selected(old('lead_id', $equipment->lead_id) == $l->id)>
                                        {{ $l->customer_name }} ({{ $l->phone }}) {{ $l->site_address ? "— {$l->site_address}" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Installation Job --}}
                        <div>
                            <label class="form-label">Associated Installation Job</label>
                            <select name="installation_job_id" class="form-select searchable-select">
                                <option value="">None (Direct Entry / Existing Site)</option>
                                @foreach($jobs as $j)
                                    <option value="{{ $j->id }}" @selected(old('installation_job_id', $equipment->installation_job_id) == $j->id)>
                                        {{ $j->job_no }} — {{ $j->quotation?->lead?->customer_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Product Catalog Reference --}}
                        <div>
                            <label class="form-label">Product Model (Catalog)</label>
                            <select name="product_id" class="form-select searchable-select">
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
                        <label class="form-label">Equipment Name / Description <span class="text-red-500">*</span></label>
                        <input type="text" name="equipment_name" value="{{ old('equipment_name', $equipment->equipment_name) }}" required class="form-input font-medium">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        {{-- Serial Number --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="form-label mb-0">Hardware Serial Number (S/N) <span class="text-red-500">*</span></label>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" onclick="openBarcodeScanner('edit-equipment-barcode-modal', 'edit_serial_input', null, 'laptop')"
                                            class="text-[11px] font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-0.5 bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 rounded-md border border-blue-200 dark:border-blue-800 transition-colors"
                                            title="Scan using laptop webcam">
                                        📷 Laptop Cam
                                    </button>
                                    <button type="button" onclick="openBarcodeScanner('edit-equipment-barcode-modal', 'edit_serial_input', null, 'mobile')"
                                            class="text-[11px] font-bold text-emerald-600 hover:text-emerald-800 inline-flex items-center gap-0.5 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800 transition-colors"
                                            title="Scan wirelessly using mobile phone camera">
                                        📱 Scan with Mobile
                                    </button>
                                </div>
                            </div>
                            <div class="relative flex items-center">
                                <input type="text" id="edit_serial_input" name="serial_number" value="{{ old('serial_number', $equipment->serial_number) }}" required class="form-input font-mono font-bold pr-16">
                                <div class="absolute right-2 flex items-center gap-1">
                                    <button type="button" onclick="openBarcodeScanner('edit-equipment-barcode-modal', 'edit_serial_input', null, 'laptop')"
                                            class="text-slate-400 hover:text-blue-600 p-1 text-sm" title="Scan with Laptop Webcam">
                                        📸
                                    </button>
                                    <button type="button" onclick="openBarcodeScanner('edit-equipment-barcode-modal', 'edit_serial_input', null, 'mobile')"
                                            class="text-slate-400 hover:text-emerald-600 p-1 text-sm" title="Scan with Mobile Phone">
                                        📱
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- MAC Address --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="form-label mb-0">MAC / IP Address</label>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" onclick="openBarcodeScanner('edit-equipment-barcode-modal', 'edit_mac_input', null, 'laptop')"
                                            class="text-[11px] font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-0.5 bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 rounded-md border border-blue-200 dark:border-blue-800 transition-colors"
                                            title="Scan using laptop webcam">
                                        📷 Laptop Cam
                                    </button>
                                    <button type="button" onclick="openBarcodeScanner('edit-equipment-barcode-modal', 'edit_mac_input', null, 'mobile')"
                                            class="text-[11px] font-bold text-emerald-600 hover:text-emerald-800 inline-flex items-center gap-0.5 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800 transition-colors"
                                            title="Scan wirelessly using mobile phone camera">
                                        📱 Scan with Mobile
                                    </button>
                                </div>
                            </div>
                            <div class="relative flex items-center">
                                <input type="text" id="edit_mac_input" name="mac_address" value="{{ old('mac_address', $equipment->mac_address) }}" class="form-input font-mono pr-16">
                                <div class="absolute right-2 flex items-center gap-1">
                                    <button type="button" onclick="openBarcodeScanner('edit-equipment-barcode-modal', 'edit_mac_input', null, 'laptop')"
                                            class="text-slate-400 hover:text-blue-600 p-1 text-sm" title="Scan with Laptop Webcam">
                                        📸
                                    </button>
                                    <button type="button" onclick="openBarcodeScanner('edit-equipment-barcode-modal', 'edit_mac_input', null, 'mobile')"
                                            class="text-slate-400 hover:text-emerald-600 p-1 text-sm" title="Scan with Mobile Phone">
                                        📱
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        {{-- Location Tag --}}
                        <div>
                            <label class="form-label">Location / Channel Placement</label>
                            <input type="text" name="location_tag" value="{{ old('location_tag', $equipment->location_tag) }}" class="form-input">
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="form-label">Operational Status</label>
                            <select name="status" class="form-select font-semibold">
                                <option value="active" @selected(old('status', $equipment->status) == 'active')>Active (Operational)</option>
                                <option value="under_repair" @selected(old('status', $equipment->status) == 'under_repair')>Under Repair</option>
                                <option value="replaced" @selected(old('status', $equipment->status) == 'replaced')>Replaced</option>
                                <option value="decommissioned" @selected(old('status', $equipment->status) == 'decommissioned')>Decommissioned</option>
                            </select>
                        </div>
                    </div>

                    {{-- Warranty Dates Section --}}
                    <div class="p-4 bg-[#060913] border border-slate-800 rounded-xl mb-4">
                        <div class="text-xs font-extrabold text-amber-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Warranty Setup & Timeline</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-semibold text-slate-200 mb-1.5">Installation Date</label>
                                <input type="date" name="installation_date" value="{{ old('installation_date', $equipment->installation_date?->format('Y-m-d')) }}" class="form-input text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-200 mb-1.5">Mfg Warranty Expiry</label>
                                <input type="date" name="manufacturer_warranty_expiry" value="{{ old('manufacturer_warranty_expiry', $equipment->manufacturer_warranty_expiry?->format('Y-m-d')) }}" class="form-input text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-200 mb-1.5">Service Warranty Expiry</label>
                                <input type="date" name="service_warranty_expiry" value="{{ old('service_warranty_expiry', $equipment->service_warranty_expiry?->format('Y-m-d')) }}" class="form-input text-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-6">
                        <label class="form-label">Technical Notes / Config Details</label>
                        <textarea name="notes" rows="3" class="form-textarea">{{ old('notes', $equipment->notes) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <a href="{{ route('equipment.index') }}" class="btn-cancel">
                            Cancel
                        </a>
                        <button type="submit" class="btn-submit-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Update Equipment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-barcode-scanner modalId="edit-equipment-barcode-modal" title="📸 Scan Device Serial / Barcode" />
</x-app-layout>
