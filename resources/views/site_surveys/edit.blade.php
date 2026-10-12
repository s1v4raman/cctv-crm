<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Edit Survey Schedule</h2>
            <a href="{{ route('site-surveys.show', $siteSurvey) }}" class="crm-back-link text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition">← Back</a>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto">
        <form method="POST" action="{{ route('site-surveys.update', $siteSurvey) }}">
            @csrf @method('PUT')

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
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}" @selected(old('surveyed_by', $siteSurvey->surveyed_by) == $tech->id)>
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
                            <input type="date" name="survey_date" id="survey_date"
                                   value="{{ old('survey_date', $siteSurvey->survey_date->format('Y-m-d')) }}"
                                   style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; background-color: #ffffff; box-sizing: border-box;"
                                   class="crm-input w-full px-3 text-sm border border-slate-300 dark:border-slate-700 dark:bg-[#0b1120] text-slate-900 dark:text-white focus:outline-none focus:border-blue-500" required>
                            @error('survey_date')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label for="status" class="crm-label text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" id="status"
                                style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; background-color: #ffffff; box-sizing: border-box;"
                                class="crm-select w-full px-3 text-sm border border-slate-300 dark:border-slate-700 dark:bg-[#0b1120] text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                            <option value="pending" @selected(old('status', $siteSurvey->status) === 'pending')>Pending</option>
                            <option value="completed" @selected(old('status', $siteSurvey->status) === 'completed')>Completed</option>
                            <option value="cancelled" @selected(old('status', $siteSurvey->status) === 'cancelled')>Cancelled</option>
                        </select>
                        @error('status')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Address (single column) --}}
                    <div>
                        <label for="site_address" class="crm-label text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">
                            Address
                        </label>
                        <input type="text" name="site_address" id="site_address"
                               value="{{ old('site_address', $siteSurvey->site_address) }}"
                               placeholder="Site inspection address"
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
                            <input type="text" name="contact_person" id="contact_person"
                                   value="{{ old('contact_person', $siteSurvey->contact_person) }}"
                                   placeholder="Person on site"
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
                            <input type="text" name="contact_phone" id="contact_phone"
                                   value="{{ old('contact_phone', $siteSurvey->contact_phone) }}"
                                   placeholder="Phone number"
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
                        <textarea name="visit_notes" id="visit_notes" rows="3"
                                  placeholder="Instructions or customer requirements"
                                  style="border-radius: 4px; background-color: #ffffff; box-sizing: border-box;"
                                  class="crm-textarea w-full p-3 text-sm border border-slate-300 dark:border-slate-700 dark:bg-[#0b1120] text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 resize-vertical">{{ old('visit_notes', $siteSurvey->visit_notes) }}</textarea>
                        @error('visit_notes')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Buttons: Cancel outlined, Save Changes as blue button with no icon --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('site-surveys.show', $siteSurvey) }}" 
                   style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                   class="crm-btn-secondary px-4 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition inline-flex items-center">
                    Cancel
                </a>
                <button type="submit" 
                        style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; background-color: #2563eb; box-sizing: border-box;"
                        class="crm-btn-primary px-5 text-white text-sm font-semibold hover:bg-blue-700 transition shadow-xs">
                    Save Schedule Changes
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
