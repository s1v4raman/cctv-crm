<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Schedule Site Survey</h2>
            <a href="{{ route('site-surveys.index') }}" class="crm-back-link text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition">← Back</a>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto">
        <form method="POST" action="{{ route('site-surveys.store') }}" id="survey-schedule-form">
            @csrf

            {{-- Short info line --}}
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Site photos and findings are added by the technician during the visit.</p>

            {{-- Scheduling Card: Plain box with 1px border and 4px radius --}}
            <div class="crm-card bg-white dark:bg-[#0f172a] rounded-[4px] border border-slate-200 dark:border-slate-800 p-6 shadow-xs mb-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-5">Survey Details</h3>

                <div class="space-y-4">
                    {{-- Customer / Lead (single column) --}}
                    <div>
                        <label for="lead-select" class="crm-label text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">
                            Customer <span class="text-red-500">*</span>
                        </label>
                        <select name="lead_id" id="lead-select" data-no-tom 
                                style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; background-color: #ffffff; box-sizing: border-box;"
                                class="crm-select w-full px-3 text-sm border border-slate-300 dark:border-slate-700 dark:bg-[#0b1120] text-slate-900 dark:text-white focus:outline-none focus:border-blue-500" required>
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
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Technician and Date in a row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="surveyed_by" class="crm-label text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">
                                Technician <span class="text-red-500">*</span>
                            </label>
                            <select name="surveyed_by" id="surveyed_by" data-no-tom
                                    style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; background-color: #ffffff; box-sizing: border-box;"
                                    class="crm-select w-full px-3 text-sm border border-slate-300 dark:border-slate-700 dark:bg-[#0b1120] text-slate-900 dark:text-white focus:outline-none focus:border-blue-500" required>
                                <option value="">Select technician</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}" @selected(old('surveyed_by') == $tech->id)>
                                        {{ $tech->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('surveyed_by')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="survey_date" class="crm-label text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">
                                Survey Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="survey_date" id="survey_date" value="{{ old('survey_date', date('Y-m-d')) }}" 
                                   style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; background-color: #ffffff; box-sizing: border-box;"
                                   class="crm-input w-full px-3 text-sm border border-slate-300 dark:border-slate-700 dark:bg-[#0b1120] text-slate-900 dark:text-white focus:outline-none focus:border-blue-500" required>
                            @error('survey_date')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Address (single column) --}}
                    <div>
                        <label for="site_address" class="crm-label text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">
                            Address
                        </label>
                        <input type="text" name="site_address" id="site_address" value="{{ old('site_address') }}" placeholder="Site inspection address" 
                               style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; background-color: #ffffff; box-sizing: border-box;"
                               class="crm-input w-full px-3 text-sm border border-slate-300 dark:border-slate-700 dark:bg-[#0b1120] text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                        <span class="text-xs text-slate-500 dark:text-slate-400 mt-1 block">Defaults to customer’s address if left blank</span>
                        @error('site_address')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Contact Person and Phone in a row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="contact_person" class="crm-label text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">
                                Contact Person
                            </label>
                            <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person') }}" placeholder="Person on site" 
                                   style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; background-color: #ffffff; box-sizing: border-box;"
                                   class="crm-input w-full px-3 text-sm border border-slate-300 dark:border-slate-700 dark:bg-[#0b1120] text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                            @error('contact_person')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact_phone" class="crm-label text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">
                                Phone
                            </label>
                            <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone') }}" placeholder="Phone number" 
                                   style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; background-color: #ffffff; box-sizing: border-box;"
                                   class="crm-input w-full px-3 text-sm border border-slate-300 dark:border-slate-700 dark:bg-[#0b1120] text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                            @error('contact_phone')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Notes / Instructions (single column) --}}
                    <div>
                        <label for="visit_notes" class="crm-label text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">
                            Notes
                        </label>
                        <textarea name="visit_notes" id="visit_notes" rows="3" placeholder="Instructions or customer requirements" 
                                  style="border-radius: 4px; background-color: #ffffff; box-sizing: border-box;"
                                  class="crm-textarea w-full p-3 text-sm border border-slate-300 dark:border-slate-700 dark:bg-[#0b1120] text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 resize-vertical">{{ old('visit_notes') }}</textarea>
                        @error('visit_notes')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Buttons: Cancel outlined, Schedule Survey blue button with no icon --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('site-surveys.index') }}" 
                   style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                   class="crm-btn-secondary px-4 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition inline-flex items-center">
                    Cancel
                </a>
                <button type="submit" 
                        style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; background-color: #2563eb; box-sizing: border-box;"
                        class="crm-btn-primary px-5 text-white text-sm font-semibold hover:bg-blue-700 transition shadow-xs">
                    Schedule Survey
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
