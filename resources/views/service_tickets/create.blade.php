<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold leading-tight text-slate-900 dark:text-white">Log Service Ticket</h2>
            </div>
            <a href="{{ route('service-tickets.index') }}" 
               class="crm-back-link text-xs font-semibold text-blue-600 hover:underline">
                &larr; Back to Tickets
            </a>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f8fafc] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @if ($errors->any())
                <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-800 p-3 rounded-[4px] text-xs">
                    <p class="font-bold mb-1">Please fix the following issues:</p>
                    <ul class="list-disc list-inside space-y-1 pl-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-[#0f172a] rounded-[4px] border border-slate-300 dark:border-slate-800">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">New Complaint Form</h3>
                    </div>
                </div>

                <form method="POST" action="{{ route('service-tickets.store') }}" class="p-5 space-y-4">
                    @csrf

                    <!-- Customer & AMC section -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Customer / Site Lead <span class="text-rose-500">*</span>
                            </label>
                            <select name="lead_id" id="lead_id" required onchange="checkLeadAmc(this.value)" 
                                    class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                <option value="">-- Select Customer / Site --</option>
                                @foreach($leads as $lead)
                                    <option value="{{ $lead->id }}" {{ old('lead_id', $selectedLeadId) == $lead->id ? 'selected' : '' }}>
                                        {{ $lead->customer_name }} ({{ $lead->phone }}) - {{ $lead->site_address }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Covered Under AMC? (Optional)
                            </label>
                            <select name="amc_contract_id" id="amc_contract_id" 
                                    class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                <option value="">-- No AMC / Standalone --</option>
                                @foreach($amcContracts as $amc)
                                    <option value="{{ $amc->id }}" {{ old('amc_contract_id', $selectedAmcId) == $amc->id ? 'selected' : '' }}>
                                        {{ $amc->contract_no }} - {{ $amc->lead->customer_name }} (Exp: {{ $amc->end_date->format('d M Y') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Dynamic AMC Notice -->
                    <div id="amc-notice-box" class="hidden p-3 bg-emerald-50 border border-emerald-200 rounded-[4px] text-xs text-emerald-900 flex items-center justify-between">
                        <div>
                            <strong id="amc-notice-text" class="font-bold">Active AMC Contract Detected</strong>
                            <p class="text-[11px] text-emerald-700 mt-0.5">Billing coverage has been auto-set to Warranty/AMC.</p>
                        </div>
                        <span class="text-xs font-bold text-emerald-700">
                            Free AMC Visit
                        </span>
                    </div>

                    <!-- Issue Type & Priority -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Breakdown Category / Issue <span class="text-rose-500">*</span>
                            </label>
                            <select name="issue_type" required 
                                    class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                @foreach(\App\Models\ServiceTicket::issueTypeOptions() as $k => $label)
                                    <option value="{{ $k }}" {{ old('issue_type') === $k ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Priority Level <span class="text-rose-500">*</span>
                            </label>
                            <select name="priority" required 
                                    class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low (Minor issue)</option>
                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium (Standard fault)</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High (Critical camera/monitoring down)</option>
                                <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>Critical / Emergency (Entire CCTV blackout)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Title & Details -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Complaint Title / Summary <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}" required 
                            placeholder="e.g. 2 Cameras at Main Entrance showing blank black screen"
                            class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Detailed Description of Complaint <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="description" rows="3" required
                            placeholder="Detail what the customer observed, error codes, time since issue started..."
                            class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white p-3 focus:outline-none focus:border-blue-600">{{ old('description') }}</textarea>
                    </div>

                    <!-- Dispatch & Scheduling -->
                    <div class="p-4 bg-slate-50 dark:bg-slate-900/60 rounded-[4px] border border-slate-200 dark:border-slate-800 space-y-3">
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white">Technician Dispatch</h4>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Assign Service Technician
                                </label>
                                <select name="assigned_technician_id" 
                                        class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0f172a] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                    <option value="">-- Assign Later --</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}" {{ old('assigned_technician_id') == $tech->id ? 'selected' : '' }}>
                                            {{ $tech->name }} ({{ $tech->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Scheduled Repair Date
                                </label>
                                <input type="date" name="scheduled_date" value="{{ old('scheduled_date', date('Y-m-d')) }}"
                                    class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0f172a] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                            </div>
                        </div>
                    </div>

                    <!-- Billing & Cost -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Billing Type <span class="text-rose-500">*</span>
                            </label>
                            <select name="billing_type" id="billing_type" required 
                                    class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                <option value="warranty_amc" {{ old('billing_type', 'warranty_amc') === 'warranty_amc' ? 'selected' : '' }}>Under Warranty / AMC Contract (Free)</option>
                                <option value="billable" {{ old('billing_type') === 'billable' ? 'selected' : '' }}>Billable Repair (Parts / Service Charge)</option>
                                <option value="free_courtesy" {{ old('billing_type') === 'free_courtesy' ? 'selected' : '' }}>Free Courtesy Visit</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Estimated Service Charge (₹)
                            </label>
                            <input type="number" step="0.01" name="cost" value="{{ old('cost') }}" placeholder="0.00"
                                class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                        <a href="{{ route('service-tickets.index') }}"
                           class="px-4 py-2 text-sm font-medium rounded-[4px] border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-5 py-2 text-sm font-bold rounded-[4px] bg-blue-600 hover:bg-blue-700 text-white transition-colors cursor-pointer">
                            Log and Dispatch Ticket
                        </button>
                    </div>
                </form>
            </div>
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
                        noticeText.textContent = `Active AMC: ${data.contract_no} (Valid until ${data.end_date} · ${data.frequency})`;
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
