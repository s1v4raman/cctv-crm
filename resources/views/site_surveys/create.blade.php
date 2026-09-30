<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Schedule Site Survey</h2>
                <p class="mt-1 text-sm text-gray-500">Schedule a pre-installation site inspection and assign a field technician</p>
            </div>
            <a href="{{ route('site-surveys.index') }}" style="font-size:.82rem;font-weight:600;color:#6366f1;text-decoration:none">← Back to Surveys</a>
        </div>
    </x-slot>

    <style>
        .pg-wrap  { background:#f1f5f9; min-height:100vh; padding:1.75rem 0 3rem; }
        .pg-inner { max-width:860px; margin:0 auto; padding:0 1.25rem; }

        .pg-card {
            background:#fff; border-radius:1rem;
            border:1px solid rgba(99,102,241,.08);
            box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(99,102,241,.06);
            overflow:hidden; margin-bottom:1.5rem;
        }
        .section-head {
            display:flex; align-items:center; gap:.6rem;
            padding:.9rem 1.25rem; border-bottom:1px solid #f1f5f9; background:#fafbff;
        }
        .section-icon { width:2rem; height:2rem; border-radius:.5rem; display:flex; align-items:center; justify-content:center; font-size:1rem; }
        .section-title { font-size:.9rem; font-weight:700; color:#1e293b; }
        .card-body { padding:1.25rem; }

        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        @media(max-width:600px){ .form-grid { grid-template-columns:1fr; } }
        .form-full  { grid-column:1/-1; }

        .form-group { display:flex; flex-direction:column; gap:.3rem; }
        .form-label { font-size:.78rem; font-weight:600; color:#374151; }
        .form-label span { color:#ef4444; }
        .form-input, .form-select, .form-textarea {
            border:1.5px solid #e2e8f0; border-radius:.6rem; padding:.55rem .85rem;
            font-size:.85rem; color:#1e293b; outline:none; background:#fff;
            transition:border-color .2s; width:100%; box-sizing:border-box;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color:#6366f1; }
        .form-textarea { min-height:90px; resize:vertical; font-family:inherit; }
        .form-hint { font-size:.72rem; color:#94a3b8; }
        .has-error .form-input, .has-error .form-select, .has-error .form-textarea { border-color:#ef4444; }
        .err-msg { font-size:.75rem; color:#ef4444; margin-top:.2rem; }

        /* Customer Request Info Box */
        .req-box {
            background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%);
            border: 1.5px solid #c7d2fe;
            border-radius: .75rem;
            padding: 1rem 1.15rem;
            margin-bottom: 1.25rem;
            display: none;
        }
        .req-box.active { display: block; }
        .req-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:.5rem; }
        .req-title { font-size:.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#4338ca; display:flex; align-items:center; gap:.4rem; }
        .req-grid { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; font-size:.8rem; }
        @media(max-width:600px){ .req-grid { grid-template-columns:1fr; } }
        .req-item label { font-size:.7rem; font-weight:700; color:#6b7280; text-transform:uppercase; display:block; margin-bottom:.15rem; }
        .req-item span { font-weight:600; color:#1e293b; }
        .btn-use-addr {
            background:#4f46e5; color:#fff; border:none; border-radius:.4rem;
            padding:.25rem .6rem; font-size:.7rem; font-weight:700; cursor:pointer;
            transition:background .15s;
        }
        .btn-use-addr:hover { background:#4338ca; }

        /* Role Notice */
        .notice-card {
            background:#f8fafc; border:1.5px dashed #cbd5e1; border-radius:.85rem;
            padding:1rem 1.25rem; display:flex; gap:.75rem; align-items:flex-start; margin-bottom:1.5rem;
        }
        .notice-icon { font-size:1.3rem; }
        .notice-text h4 { font-size:.82rem; font-weight:700; color:#334155; margin:0 0 .2rem; }
        .notice-text p { font-size:.75rem; color:#64748b; margin:0; line-height:1.45; }

        /* Submit button */
        .btn-submit {
            padding:.65rem 1.75rem; font-size:.88rem; font-weight:700; border:none;
            border-radius:.65rem; cursor:pointer; background:#4f46e5; color:#fff;
            display:inline-flex; align-items:center; gap:.45rem; transition:background .2s;
        }
        .btn-submit:hover { background:#4338ca; }

        /* Dark mode overrides */
        html.dark .pg-wrap { background: #060913; }
        html.dark .pg-card { background: #0f172a; border-color: #1e293b; color: #f8fafc; }
        html.dark .section-head { background: #0f172a; border-bottom-color: #1e293b; }
        html.dark .section-title { color: #f8fafc; }
        html.dark .notice-card { background: #0f172a; border-color: #334155; }
        html.dark .notice-text h4 { color: #f8fafc; }
        html.dark .notice-text p { color: #cbd5e1; }
        html.dark .form-label { color: #cbd5e1; }
        html.dark .form-input, html.dark .form-select, html.dark .form-textarea { background: #0b1120; border-color: #334155; color: #f8fafc; }
        html.dark .form-hint { color: #94a3b8; }
        html.dark .req-box { background: #0f172a; border-color: #3730a3; }
        html.dark .req-title { color: #a5b4fc; }
        html.dark .req-item label { color: #94a3b8; }
        html.dark .req-item span { color: #f8fafc; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">
            <form method="POST" action="{{ route('site-surveys.store') }}" id="survey-schedule-form">
                @csrf

                {{-- Notice explaining workflow --}}
                <div class="notice-card">
                    <span class="notice-icon">ℹ️</span>
                    <div class="notice-text">
                        <h4>Admin Scheduling & Assignment</h4>
                        <p>Use this form to schedule the site survey and assign a field technician. Technical inspection findings (camera counts, cable measurements, DVR location, obstacles) and <strong>site photos will be uploaded directly by the technician</strong> on-site during their inspection.</p>
                    </div>
                </div>

                {{-- Scheduling Details Card --}}
                <div class="pg-card">
                    <div class="section-head">
                        <div class="section-icon" style="background:#ede9fe">📅</div>
                        <span class="section-title">Schedule & Customer Details</span>
                    </div>
                    <div class="card-body">

                        {{-- Customer / Lead Selection --}}
                        <div class="form-group form-full {{ $errors->has('lead_id') ? 'has-error' : '' }}" style="margin-bottom:1rem">
                            <label class="form-label">Customer / Lead <span>*</span></label>
                            <select name="lead_id" id="lead-select" class="form-select" required>
                                <option value="">— Select Customer / Lead —</option>
                                @foreach($leads as $lead)
                                    <option value="{{ $lead->id }}"
                                        data-name="{{ $lead->customer_name }}"
                                        data-phone="{{ $lead->phone }}"
                                        data-email="{{ $lead->email }}"
                                        data-address="{{ $lead->site_address }}"
                                        data-notes="{{ $lead->notes }}"
                                        @selected(old('lead_id', request('lead_id')) == $lead->id)>
                                         {{ $lead->customer_name }}{{ $lead->company_name ? ' (' . $lead->company_name . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('lead_id') <span class="err-msg">{{ $message }}</span> @enderror
                        </div>

                        {{-- Customer Request Address Preview Box --}}
                        <div class="req-box" id="customer-req-box">
                            <div class="req-header">
                                <span class="req-title">📍 Customer Request & Premises Information</span>
                                <button type="button" class="btn-use-addr" id="btn-copy-address">Use Customer Address</button>
                            </div>
                            <div class="req-grid">
                                <div class="req-item" style="grid-column:1/-1">
                                    <label>Customer Registered Address</label>
                                    <span id="req-address-display">—</span>
                                </div>
                                <div class="req-item">
                                    <label>Contact Person & Phone</label>
                                    <span id="req-contact-display">—</span>
                                </div>
                                <div class="req-item">
                                    <label>Customer Requirement / Notes</label>
                                    <span id="req-notes-display">—</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-grid">
                            {{-- Field Technician Assignment --}}
                            <div class="form-group {{ $errors->has('surveyed_by') ? 'has-error' : '' }}">
                                <label class="form-label">Assign Field Technician / Engineer <span>*</span></label>
                                <select name="surveyed_by" class="form-select" required>
                                    <option value="">— Select Field Technician —</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}" @selected(old('surveyed_by') == $tech->id)>
                                            {{ $tech->name }} ({{ ucfirst($tech->role) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('surveyed_by') <span class="err-msg">{{ $message }}</span> @enderror
                            </div>

                            {{-- Survey Date --}}
                            <div class="form-group {{ $errors->has('survey_date') ? 'has-error' : '' }}">
                                <label class="form-label">Scheduled Survey Date <span>*</span></label>
                                <input type="date" name="survey_date" class="form-input" value="{{ old('survey_date', date('Y-m-d')) }}" required>
                                @error('survey_date') <span class="err-msg">{{ $message }}</span> @enderror
                            </div>

                            {{-- Site Inspection Address --}}
                            <div class="form-group form-full {{ $errors->has('site_address') ? 'has-error' : '' }}">
                                <label class="form-label">Site Address for Inspection</label>
                                <input type="text" name="site_address" id="site_address" class="form-input" placeholder="Customer premises address where survey will be conducted" value="{{ old('site_address') }}">
                                <span class="form-hint">Defaults to customer's registered request address if left blank</span>
                                @error('site_address') <span class="err-msg">{{ $message }}</span> @enderror
                            </div>

                            {{-- Contact Person on Site --}}
                            <div class="form-group {{ $errors->has('contact_person') ? 'has-error' : '' }}">
                                <label class="form-label">On-Site Contact Person</label>
                                <input type="text" name="contact_person" id="contact_person" class="form-input" placeholder="Name of person present on site" value="{{ old('contact_person') }}">
                                @error('contact_person') <span class="err-msg">{{ $message }}</span> @enderror
                            </div>

                            {{-- Contact Phone --}}
                            <div class="form-group {{ $errors->has('contact_phone') ? 'has-error' : '' }}">
                                <label class="form-label">Contact Phone Number</label>
                                <input type="text" name="contact_phone" id="contact_phone" class="form-input" placeholder="e.g. 9876543210" value="{{ old('contact_phone') }}">
                                @error('contact_phone') <span class="err-msg">{{ $message }}</span> @enderror
                            </div>

                            {{-- Notes / Instructions for Technician --}}
                            <div class="form-group form-full {{ $errors->has('visit_notes') ? 'has-error' : '' }}">
                                <label class="form-label">Instructions & Customer Scope for Technician</label>
                                <textarea name="visit_notes" id="visit_notes" class="form-textarea" placeholder="e.g. Customer wants 4-8 IP cameras coverage for main entrance and warehouse; check power supply and rack space">{{ old('visit_notes') }}</textarea>
                                <span class="form-hint">Provide instructions or customer requirements for the field technician prior to visiting the site</span>
                                @error('visit_notes') <span class="err-msg">{{ $message }}</span> @enderror
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Submit & Actions --}}
                <div style="display:flex;justify-content:flex-end;gap:1rem;align-items:center">
                    <a href="{{ route('site-surveys.index') }}" style="font-size:.85rem;color:#64748b;font-weight:600;text-decoration:none">Cancel</a>
                    <button type="submit" class="btn-submit">
                        <svg style="width:.95rem;height:.95rem" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Schedule Survey
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const leadSelect = document.getElementById('lead-select');
            const reqBox = document.getElementById('customer-req-box');
            const addrDisplay = document.getElementById('req-address-display');
            const contactDisplay = document.getElementById('req-contact-display');
            const notesDisplay = document.getElementById('req-notes-display');
            const btnCopyAddr = document.getElementById('btn-copy-address');

            const addrInput = document.getElementById('site_address');
            const contactInput = document.getElementById('contact_person');
            const phoneInput = document.getElementById('contact_phone');
            const notesInput = document.getElementById('visit_notes');

            function updateCustomerPreview(autoFillFields = false) {
                const selectedOpt = leadSelect.options[leadSelect.selectedIndex];
                if (!selectedOpt || !selectedOpt.value) {
                    reqBox.classList.remove('active');
                    return;
                }

                const name = selectedOpt.getAttribute('data-name') || '—';
                const phone = selectedOpt.getAttribute('data-phone') || '—';
                const address = selectedOpt.getAttribute('data-address') || 'Address not registered yet';
                const notes = selectedOpt.getAttribute('data-notes') || 'No specific notes recorded';

                addrDisplay.textContent = address;
                contactDisplay.textContent = `${name} (${phone})`;
                notesDisplay.textContent = notes;
                reqBox.classList.add('active');

                if (autoFillFields) {
                    if (address && address !== 'Address not registered yet') {
                        addrInput.value = address;
                    }
                    if (name && name !== '—') {
                        contactInput.value = name;
                    }
                    if (phone && phone !== '—') {
                        phoneInput.value = phone;
                    }
                    if (notes && notes !== 'No specific notes recorded' && !notesInput.value) {
                        notesInput.value = `Customer Requirement: ${notes}`;
                    }
                }
            }

            leadSelect.addEventListener('change', function() {
                updateCustomerPreview(true);
            });

            btnCopyAddr.addEventListener('click', function() {
                const selectedOpt = leadSelect.options[leadSelect.selectedIndex];
                if (selectedOpt && selectedOpt.value) {
                    const address = selectedOpt.getAttribute('data-address');
                    if (address) addrInput.value = address;
                }
            });

            // Initial check on page load (e.g. if lead_id was in query param or old input)
            if (leadSelect.value) {
                updateCustomerPreview(!addrInput.value);
            }
        });
    </script>
</x-app-layout>
