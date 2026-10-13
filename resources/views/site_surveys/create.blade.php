<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('site-surveys.index') }}" class="back-link">&larr; Back</a>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Schedule Site Survey</h2>
        </div>
    </x-slot>

    <div class="max-w-[800px] mx-auto space-y-6 py-6 px-4 sm:px-6">
        <form method="POST" action="{{ route('site-surveys.store') }}" id="survey-schedule-form">
            @csrf

            <div class="crm-section mb-6">
                <h3>Survey Details</h3>

                <div class="space-y-4">
                    {{-- Customer / Lead --}}
                    <div>
                        <label for="lead-select" class="crm-label">
                            Customer <span class="required-marker">*</span>
                        </label>
                        <select name="lead_id" id="lead-select" class="crm-select" required>
                            <option value="">Select customer</option>
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
                        @error('lead_id')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Technician and Date in a row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="surveyed_by" class="crm-label">
                                Technician <span class="required-marker">*</span>
                            </label>
                            <select name="surveyed_by" id="surveyed_by" class="crm-select" required>
                                <option value="">Select technician</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}" @selected(old('surveyed_by') == $tech->id)>
                                        {{ $tech->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('surveyed_by')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="survey_date" class="crm-label">
                                Survey Date <span class="required-marker">*</span>
                            </label>
                            <input type="date" name="survey_date" id="survey_date"
                                   value="{{ old('survey_date', date('Y-m-d')) }}"
                                   class="crm-input" required>
                            @error('survey_date')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Address --}}
                    <div>
                        <label for="site_address" class="crm-label">
                            Site Address <span class="required-marker">*</span>
                        </label>
                        <textarea name="site_address" id="site_address" rows="2"
                                  placeholder="e.g. Plot 42, Electronics City Phase 1, Bangalore"
                                  class="crm-input h-auto py-2" required>{{ old('site_address') }}</textarea>
                        @error('site_address')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- On-site Contact Person & Phone --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="contact_person" class="crm-label">
                                Contact Person
                            </label>
                            <input type="text" name="contact_person" id="contact_person"
                                   value="{{ old('contact_person') }}"
                                   placeholder="e.g. Raman Kumar"
                                   class="crm-input">
                            @error('contact_person')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact_phone" class="crm-label">
                                Contact Phone
                            </label>
                            <input type="text" name="contact_phone" id="contact_phone"
                                   value="{{ old('contact_phone') }}"
                                   placeholder="e.g. +91 98765 43210"
                                   class="crm-input">
                            @error('contact_phone')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Visit Instructions --}}
                    <div>
                        <label for="visit_notes" class="crm-label">
                            Visit Notes
                        </label>
                        <textarea name="visit_notes" id="visit_notes" rows="2"
                                  placeholder="e.g. Check perimeter wall, gate entry and server room location"
                                  class="crm-input h-auto py-2">{{ old('visit_notes') }}</textarea>
                        @error('visit_notes')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Form Actions: Cancel on left, Primary on right --}}
            <div class="form-actions">
                <a href="{{ route('site-surveys.index') }}" class="btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    Save
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const leadSelect = document.getElementById('lead-select');
            const addrInput = document.getElementById('site_address');
            const contactInput = document.getElementById('contact_person');
            const phoneInput = document.getElementById('contact_phone');
            const notesInput = document.getElementById('visit_notes');

            function updateCustomerFields(autoFill = false) {
                if (!leadSelect) return;
                const opt = leadSelect.options[leadSelect.selectedIndex];
                if (!opt || !opt.value) return;

                const name = opt.getAttribute('data-name') || '';
                const phone = opt.getAttribute('data-phone') || '';
                const address = opt.getAttribute('data-address') || '';
                const notes = opt.getAttribute('data-notes') || '';

                if (autoFill) {
                    if (address && !addrInput.value) addrInput.value = address;
                    if (name && !contactInput.value) contactInput.value = name;
                    if (phone && !phoneInput.value) phoneInput.value = phone;
                    if (notes && !notesInput.value) notesInput.value = notes;
                }
            }

            leadSelect?.addEventListener('change', function() {
                updateCustomerFields(true);
            });
        });
    </script>
</x-app-layout>
