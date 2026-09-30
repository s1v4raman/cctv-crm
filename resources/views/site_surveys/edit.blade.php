<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Survey Schedule</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $siteSurvey->lead->customer_name }} · Scheduled for {{ $siteSurvey->survey_date->format('d M Y') }}</p>
            </div>
            <a href="{{ route('site-surveys.show', $siteSurvey) }}" style="font-size:.82rem;font-weight:600;color:#6366f1;text-decoration:none">← Back to Survey</a>
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
        .err-msg { font-size:.75rem; color:#ef4444; margin-top:.2rem; }

        /* Customer Request Info Box */
        .req-box {
            background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%);
            border: 1.5px solid #c7d2fe;
            border-radius: .75rem;
            padding: 1rem 1.15rem;
            margin-bottom: 1.25rem;
        }
        .req-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:.5rem; }
        .req-title { font-size:.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#4338ca; display:flex; align-items:center; gap:.4rem; }
        .req-grid { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; font-size:.8rem; }
        @media(max-width:600px){ .req-grid { grid-template-columns:1fr; } }
        .req-item label { font-size:.7rem; font-weight:700; color:#6b7280; text-transform:uppercase; display:block; margin-bottom:.15rem; }
        .req-item span { font-weight:600; color:#1e293b; }
        .btn-use-addr {
            background:#4f46e5; color:#fff; border:none; border-radius:.4rem;
            padding:.25rem .6rem; font-size:.7rem; font-weight:700; cursor:pointer;
        }
        .btn-use-addr:hover { background:#4338ca; }

        /* Read-only Findings Grid */
        .findings-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:1rem; margin-bottom:1rem; }
        @media(max-width:600px){ .findings-grid { grid-template-columns:1fr; } }
        .finding-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:#6rem; padding:.75rem 1rem; }
        .finding-lbl { font-size:.7rem; font-weight:700; text-transform:uppercase; color:#94a3b8; }
        .finding-val { font-size:1.1rem; font-weight:800; color:#1e293b; margin-top:.2rem; }

        /* Existing Photos */
        .existing-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); gap:.75rem; }
        .ex-photo { position:relative; border-radius:.65rem; overflow:hidden; border:1px solid #e2e8f0; background:#fff; }
        .ex-photo img { width:100%; height:100px; object-fit:cover; display:block; }
        .ex-caption { padding:.3rem .5rem; font-size:.7rem; color:#64748b; }

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
        html.dark .form-label { color: #cbd5e1; }
        html.dark .form-input, html.dark .form-select, html.dark .form-textarea { background: #0b1120; border-color: #334155; color: #f8fafc; }
        html.dark .req-box { background: #0f172a; border-color: #3730a3; }
        html.dark .req-title { color: #a5b4fc; }
        html.dark .req-item label { color: #94a3b8; }
        html.dark .req-item span { color: #f8fafc; }
        html.dark .finding-box { background: #0b1120; border-color: #334155; }
        html.dark .finding-val { color: #f8fafc; }
        html.dark .ex-photo { background: #0b1120; border-color: #334155; }
        html.dark .ex-caption { color: #94a3b8; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">
            <form method="POST" action="{{ route('site-surveys.update', $siteSurvey) }}">
                @csrf @method('PUT')

                {{-- Schedule & Customer Details --}}
                <div class="pg-card">
                    <div class="section-head">
                        <div class="section-icon" style="background:#ede9fe">📅</div>
                        <span class="section-title">Schedule & Assignment Details</span>
                    </div>
                    <div class="card-body">

                        {{-- Customer / Lead Selection --}}
                        <div class="form-group form-full" style="margin-bottom:1rem">
                            <label class="form-label">Customer / Lead <span>*</span></label>
                            <select name="lead_id" id="lead-select" class="form-select">
                                @foreach($leads as $lead)
                                    <option value="{{ $lead->id }}"
                                        data-name="{{ $lead->customer_name }}"
                                        data-phone="{{ $lead->phone }}"
                                        data-email="{{ $lead->email }}"
                                        data-address="{{ $lead->site_address }}"
                                        data-notes="{{ $lead->notes }}"
                                        @selected(old('lead_id', $siteSurvey->lead_id) == $lead->id)>
                                        {{ $lead->customer_name }}{{ $lead->company_name ? ' (' . $lead->company_name . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('lead_id') <span class="err-msg">{{ $message }}</span> @enderror
                        </div>

                        {{-- Customer Request Information Preview --}}
                        <div class="req-box" id="customer-req-box">
                            <div class="req-header">
                                <span class="req-title">📍 Customer Request & Premises Information</span>
                                <button type="button" class="btn-use-addr" id="btn-copy-address">Use Customer Address</button>
                            </div>
                            <div class="req-grid">
                                <div class="req-item" style="grid-column:1/-1">
                                    <label>Customer Registered Address</label>
                                    <span id="req-address-display">{{ $siteSurvey->lead->site_address ?: 'Address not registered yet' }}</span>
                                </div>
                                <div class="req-item">
                                    <label>Contact Person & Phone</label>
                                    <span id="req-contact-display">{{ $siteSurvey->lead->customer_name }} ({{ $siteSurvey->lead->phone ?: '—' }})</span>
                                </div>
                                <div class="req-item">
                                    <label>Customer Requirement / Notes</label>
                                    <span id="req-notes-display">{{ $siteSurvey->lead->notes ?: 'No specific notes recorded' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group {{ $errors->has('surveyed_by') ? 'has-error' : '' }}">
                                <label class="form-label">Assigned Field Technician / Engineer <span>*</span></label>
                                <select name="surveyed_by" class="form-select" required>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}" @selected(old('surveyed_by', $siteSurvey->surveyed_by) == $tech->id)>
                                            {{ $tech->name }} ({{ ucfirst($tech->role) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('surveyed_by') <span class="err-msg">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Survey Scheduled Date <span>*</span></label>
                                <input type="date" name="survey_date" class="form-input"
                                       value="{{ old('survey_date', $siteSurvey->survey_date->format('Y-m-d')) }}" required>
                                @error('survey_date') <span class="err-msg">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Status <span>*</span></label>
                                <select name="status" class="form-select">
                                    <option value="pending"   @selected(old('status', $siteSurvey->status) === 'pending')>⏳ Pending / Scheduled for Visit</option>
                                    <option value="completed" @selected(old('status', $siteSurvey->status) === 'completed')>✓ Completed</option>
                                    <option value="cancelled" @selected(old('status', $siteSurvey->status) === 'cancelled')>✕ Cancelled</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Contact Person on Site</label>
                                <input type="text" name="contact_person" id="contact_person" class="form-input" value="{{ old('contact_person', $siteSurvey->contact_person) }}">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Contact Phone Number</label>
                                <input type="text" name="contact_phone" id="contact_phone" class="form-input" value="{{ old('contact_phone', $siteSurvey->contact_phone) }}">
                            </div>

                            <div class="form-group form-full">
                                <label class="form-label">Site Address for Inspection</label>
                                <input type="text" name="site_address" id="site_address" class="form-input" value="{{ old('site_address', $siteSurvey->site_address) }}">
                            </div>

                            <div class="form-group form-full">
                                <label class="form-label">Instructions & Notes for Field Technician</label>
                                <textarea name="visit_notes" class="form-textarea" style="min-height:90px" placeholder="Scope of survey, camera locations requested, gate access notes...">{{ old('visit_notes', $siteSurvey->visit_notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- On-Site Technician Findings (Read-only for Admin) --}}
                @if($siteSurvey->status === 'completed' || $siteSurvey->camera_count_recommended)
                    <div class="pg-card">
                        <div class="section-head">
                            <div class="section-icon" style="background:#fef3c7">🔍</div>
                            <span class="section-title">On-Site Technician Inspection Report (Field Data)</span>
                        </div>
                        <div class="card-body">
                            <div class="findings-grid">
                                <div class="finding-box">
                                    <div class="finding-lbl">Cameras Recommended</div>
                                    <div class="finding-val" style="color:#4f46e5">{{ $siteSurvey->camera_count_recommended ?: '—' }} Units</div>
                                </div>
                                <div class="finding-box">
                                    <div class="finding-lbl">Cable Length Estimate</div>
                                    <div class="finding-val" style="color:#0ea5e9">{{ $siteSurvey->cable_length_estimate ? number_format($siteSurvey->cable_length_estimate, 1) . ' m' : '—' }}</div>
                                </div>
                                <div class="finding-box">
                                    <div class="finding-lbl">DVR / NVR Location</div>
                                    <div class="finding-val" style="font-size:.95rem">{{ $siteSurvey->dvr_location ?: '—' }}</div>
                                </div>
                            </div>

                            @if($siteSurvey->power_availability)
                                <div style="margin-bottom:.75rem">
                                    <span style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase">⚡ Power Availability:</span>
                                    <p style="font-size:.85rem;color:#334155;margin:.2rem 0">{{ $siteSurvey->power_availability }}</p>
                                </div>
                            @endif

                            @if($siteSurvey->challenges)
                                <div style="margin-bottom:.75rem">
                                    <span style="font-size:.72rem;font-weight:700;color:#d97706;text-transform:uppercase">⚠️ Site Challenges:</span>
                                    <p style="font-size:.85rem;color:#78350f;margin:.2rem 0">{{ $siteSurvey->challenges }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Uploaded Photos Gallery (Read-Only) --}}
                @if($siteSurvey->photos->count())
                    <div class="pg-card">
                        <div class="section-head">
                            <div class="section-icon" style="background:#dbeafe">📷</div>
                            <span class="section-title">Inspection Photos Uploaded by Technician ({{ $siteSurvey->photos->count() }})</span>
                        </div>
                        <div class="card-body">
                            <div class="existing-grid">
                                @foreach($siteSurvey->photos as $photo)
                                    <div class="ex-photo">
                                        <img src="{{ $photo->url() }}" alt="{{ $photo->original_name }}">
                                        @if($photo->caption)
                                            <div class="ex-caption">{{ $photo->caption }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Submit --}}
                <div style="display:flex;justify-content:flex-end;gap:1rem;align-items:center">
                    <a href="{{ route('site-surveys.show', $siteSurvey) }}" style="font-size:.85rem;color:#64748b;font-weight:600;text-decoration:none">Cancel</a>
                    <button type="submit" class="btn-submit">
                        <svg style="width:.9rem;height:.9rem" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Save Schedule Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const leadSelect = document.getElementById('lead-select');
            const addrDisplay = document.getElementById('req-address-display');
            const contactDisplay = document.getElementById('req-contact-display');
            const notesDisplay = document.getElementById('req-notes-display');
            const btnCopyAddr = document.getElementById('btn-copy-address');
            const addrInput = document.getElementById('site_address');

            function updateCustomerPreview() {
                const selectedOpt = leadSelect.options[leadSelect.selectedIndex];
                if (!selectedOpt || !selectedOpt.value) return;

                const name = selectedOpt.getAttribute('data-name') || '—';
                const phone = selectedOpt.getAttribute('data-phone') || '—';
                const address = selectedOpt.getAttribute('data-address') || 'Address not registered yet';
                const notes = selectedOpt.getAttribute('data-notes') || 'No specific notes recorded';

                addrDisplay.textContent = address;
                contactDisplay.textContent = `${name} (${phone})`;
                notesDisplay.textContent = notes;
            }

            leadSelect.addEventListener('change', updateCustomerPreview);

            btnCopyAddr.addEventListener('click', function() {
                const selectedOpt = leadSelect.options[leadSelect.selectedIndex];
                if (selectedOpt && selectedOpt.value) {
                    const address = selectedOpt.getAttribute('data-address');
                    if (address) addrInput.value = address;
                }
            });
        });
    </script>
</x-app-layout>
