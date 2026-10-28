<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('service-tickets.index') }}" class="back-link">&larr; Back</a>
            <h2 class="text-xl font-bold leading-tight text-slate-900 dark:text-white mt-1">New Service Ticket</h2>
        </div>
    </x-slot>

    <div class="max-w-[800px] mx-auto space-y-6 py-6 px-4 sm:px-6">
        @if ($errors->any())
            <div class="p-3 bg-white dark:bg-slate-900 border border-red-500 text-red-600 rounded text-xs">
                <p class="font-bold mb-1">Please correct the following fields:</p>
                <ul class="list-disc list-inside space-y-1 pl-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="crm-section">
            <h3>Service Ticket Details</h3>

            <form method="POST" action="{{ route('service-tickets.store') }}">
                @csrf

                <!-- Customer & AMC Section -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="crm-label">
                            Customer / Site <span class="required-marker">*</span>
                        </label>
                        <select aria-label="Customer / Site" name="lead_id" id="lead_id" required onchange="checkLeadAmc(this.value)" class="crm-select">
                            <option value="">Select customer</option>
                            @foreach($leads as $lead)
                                <option value="{{ $lead->id }}" {{ old('lead_id', $selectedLeadId) == $lead->id ? 'selected' : '' }}>
                                    {{ $lead->customer_name }} ({{ $lead->phone }})
                                </option>
                            @endforeach
                        </select>
                        @error('lead_id')
                            <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="crm-label">
                            Covered Under AMC
                        </label>
                        <select aria-label="Covered Under AMC" name="amc_contract_id" id="amc_contract_id" class="crm-select">
                            <option value="">Standalone / No AMC</option>
                            @foreach($amcContracts as $amc)
                                <option value="{{ $amc->id }}" {{ old('amc_contract_id', $selectedAmcId) == $amc->id ? 'selected' : '' }}>
                                    {{ $amc->contract_no }} - {{ $amc->lead->customer_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Dynamic AMC Notice -->
                <div id="amc-notice-box" class="hidden mb-4 p-3 bg-white dark:bg-slate-800 border border-emerald-500 rounded text-xs text-emerald-700 dark:text-emerald-300">
                    <span id="amc-notice-text" class="font-bold">Active AMC contract detected.</span>
                </div>

                <!-- Issue Type & Priority -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="crm-label">
                            Issue Category <span class="required-marker">*</span>
                        </label>
                        <select aria-label="Issue Category" name="issue_type" required class="crm-select">
                            @foreach(\App\Models\ServiceTicket::issueTypeOptions() as $k => $label)
                                <option value="{{ $k }}" {{ old('issue_type') === $k ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="crm-label">
                            Priority <span class="required-marker">*</span>
                        </label>
                        <select aria-label="Priority" name="priority" required class="crm-select">
                            <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                        </select>
                    </div>
                </div>

                <!-- Title & Details -->
                <div class="mb-4">
                    <label class="crm-label">
                        Subject / Title <span class="required-marker">*</span>
                    </label>
                    <input aria-label="Subject / Title" type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Main entrance camera feed offline" class="crm-input">
                    @error('title')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="crm-label">
                        Description <span class="required-marker">*</span>
                    </label>
                    <textarea aria-label="Description" name="description" rows="3" required placeholder="e.g. Blank screen noticed since morning after power surge" class="crm-input h-auto py-2">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Dispatch & Scheduling -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="crm-label">
                            Assign Technician
                        </label>
                        <select aria-label="Assign Technician" name="assigned_technician_id" class="crm-select">
                            <option value="">Select technician</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" {{ old('assigned_technician_id') == $tech->id ? 'selected' : '' }}>
                                    {{ $tech->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="crm-label">
                            Scheduled Date
                        </label>
                        <input aria-label="Scheduled Date" type="date" name="scheduled_date" value="{{ old('scheduled_date', date('Y-m-d')) }}" class="crm-input">
                    </div>
                </div>

                <!-- Billing & Cost -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="crm-label">
                            Billing Type <span class="required-marker">*</span>
                        </label>
                        <select aria-label="Billing Type" name="billing_type" id="billing_type" required class="crm-select">
                            <option value="warranty_amc" {{ old('billing_type', 'warranty_amc') === 'warranty_amc' ? 'selected' : '' }}>Under Warranty / AMC</option>
                            <option value="billable" {{ old('billing_type') === 'billable' ? 'selected' : '' }}>Billable</option>
                            <option value="free_courtesy" {{ old('billing_type') === 'free_courtesy' ? 'selected' : '' }}>Free Courtesy Visit</option>
                        </select>
                    </div>

                    <div>
                        <label class="crm-label">
                            Estimated Service Charge (₹)
                        </label>
                        <input aria-label="Estimated Service Charge (₹)" type="number" step="0.01" name="cost" value="{{ old('cost') }}" placeholder="e.g. 500.00" class="crm-input">
                    </div>
                </div>

                <!-- Form actions: Cancel left, Primary right -->
                <div class="form-actions mt-6">
                    <a href="{{ route('service-tickets.index') }}" class="btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn-primary">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function checkLeadAmc(leadId) {
            if (!leadId) {
                document.getElementById('amc-notice-box').classList.add('hidden');
                return;
            }

            fetch(`/service-tickets/lead-amc/${leadId}`)
                .then(res => res.json())
                .then(data => {
                    const noticeBox = document.getElementById('amc-notice-box');
                    const noticeText = document.getElementById('amc-notice-text');
                    const amcSelect = document.getElementById('amc_contract_id');
                    const billingSelect = document.getElementById('billing_type');

                    if (data.has_active_amc) {
                        noticeText.textContent = `Active AMC: ${data.contract_no} (Valid until ${data.end_date})`;
                        noticeBox.classList.remove('hidden');

                        if (amcSelect) {
                            amcSelect.value = data.amc_contract_id;
                        }
                        if (billingSelect) {
                            billingSelect.value = 'warranty_amc';
                        }
                    } else {
                        noticeBox.classList.add('hidden');
                    }
                })
                .catch(() => {
                    document.getElementById('amc-notice-box').classList.add('hidden');
                });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const initialLeadId = document.getElementById('lead_id').value;
            if (initialLeadId) {
                checkLeadAmc(initialLeadId);
            }
        });
    </script>
</x-app-layout>
