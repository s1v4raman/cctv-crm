<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('site-surveys.index') }}" class="back-link">&larr; Back</a>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Schedule Site Survey</h2>
        </div>
    </x-slot>

    <div class="max-w-[800px] mx-auto space-y-6 py-6 px-4 sm:px-6" x-data="siteSurveyCreateApp()">
        <form method="POST" action="{{ route('site-surveys.store') }}" id="survey-schedule-form">
            @csrf

            <div class="crm-section mb-6">
                <h3>Survey Details</h3>

                <div class="space-y-4">
                    {{-- Customer / Lead --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="lead-select" class="crm-label mb-0">
                                Customer <span class="required-marker">*</span>
                            </label>
                            <button type="button" 
                                    @click="newCustomerModalOpen = true"
                                    id="btn-add-new-customer"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200/80 px-2.5 py-1 rounded-md transition cursor-pointer shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span>Add New Customer</span>
                            </button>
                        </div>
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

        {{-- Quick Add Customer Modal Dialog --}}
        <div x-show="newCustomerModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;"
             @keydown.escape.window="newCustomerModalOpen = false">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" 
                 @click="newCustomerModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-2xl transition-all"
                     @click.stop>
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Add New Customer</h3>
                                <p class="text-[11px] text-slate-500">Quickly create and link customer profile</p>
                            </div>
                        </div>
                        <button type="button" @click="newCustomerModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg leading-none p-1">
                            &times;
                        </button>
                    </div>

                    <div x-show="errorMessage" class="mb-3 p-2.5 rounded-lg bg-rose-50 text-rose-600 border border-rose-200 text-xs" x-text="errorMessage"></div>

                    <form @submit.prevent="submitQuickCustomer()" class="space-y-3.5">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Customer / Company Name <span class="text-rose-500">*</span></label>
                            <input type="text" x-model="newCustomer.customer_name" required placeholder="e.g. Ramesh Kumar or Tech Park Corp"
                                   class="w-full text-xs h-9 px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone Number <span class="text-rose-500">*</span></label>
                            <input type="tel" x-model="newCustomer.phone" required placeholder="e.g. +91 98765 43210"
                                   class="w-full text-xs h-9 px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Address (Optional)</label>
                            <input type="email" x-model="newCustomer.email" placeholder="e.g. ramesh@example.com"
                                   class="w-full text-xs h-9 px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Site / Installation Address (Optional)</label>
                            <textarea x-model="newCustomer.site_address" rows="2" placeholder="e.g. Plot 14, Electronic City, Bangalore"
                                      class="w-full text-xs p-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-blue-500 focus:outline-none"></textarea>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <a href="{{ route('leads.create', ['return_to' => 'survey']) }}" class="text-[11px] text-blue-600 hover:underline">
                                Full Registration Form &rarr;
                            </a>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="newCustomerModalOpen = false" class="px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 rounded-lg">
                                    Cancel
                                </button>
                                <button type="submit" :disabled="isSubmitting" class="px-4 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm disabled:opacity-50 inline-flex items-center gap-1.5">
                                    <span x-show="!isSubmitting">Save &amp; Select</span>
                                    <span x-show="isSubmitting">Saving...</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function siteSurveyCreateApp() {
            return {
                newCustomerModalOpen: false,
                isSubmitting: false,
                errorMessage: '',
                newCustomer: {
                    customer_name: '',
                    phone: '',
                    email: '',
                    site_address: ''
                },
                async submitQuickCustomer() {
                    if (!this.newCustomer.customer_name || !this.newCustomer.phone) {
                        this.errorMessage = 'Please provide customer name and phone.';
                        return;
                    }
                    this.isSubmitting = true;
                    this.errorMessage = '';
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const res = await fetch('{{ route("leads.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                customer_name: this.newCustomer.customer_name,
                                phone: this.newCustomer.phone,
                                email: this.newCustomer.email,
                                site_address: this.newCustomer.site_address,
                                source: 'direct_entry',
                                status: 'new'
                            })
                        });

                        const data = await res.json();
                        if (res.ok && data.success && data.lead) {
                            const lead = data.lead;
                            const select = document.getElementById('lead-select');
                            if (select) {
                                const newOpt = document.createElement('option');
                                newOpt.value = lead.id;
                                newOpt.textContent = lead.customer_name + (lead.company_name ? ` (${lead.company_name})` : '');
                                newOpt.setAttribute('data-name', lead.customer_name || '');
                                newOpt.setAttribute('data-phone', lead.phone || '');
                                newOpt.setAttribute('data-email', lead.email || '');
                                newOpt.setAttribute('data-address', lead.site_address || '');
                                newOpt.setAttribute('data-notes', lead.notes || '');
                                newOpt.selected = true;
                                select.appendChild(newOpt);
                                select.dispatchEvent(new Event('change'));
                            }
                            // Reset form & close
                            this.newCustomer = { customer_name: '', phone: '', email: '', site_address: '' };
                            this.newCustomerModalOpen = false;
                        } else {
                            this.errorMessage = data.message || 'Error saving customer. Please check fields.';
                        }
                    } catch (err) {
                        this.errorMessage = 'Network error while creating customer.';
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            };
        }

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
