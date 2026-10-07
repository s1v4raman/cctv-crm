<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 p-0.5 shadow-sm flex items-center justify-center">
                    <div class="w-full h-full bg-white dark:bg-[#060913] rounded-[10px] flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold">
                        🛠️
                    </div>
                </div>
                <div>
                    <h2 class="text-xl font-black leading-tight text-slate-900 dark:text-white font-heading tracking-tight">Log CCTV Breakdown / Service Ticket</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Record customer breakdown complaints & auto-dispatch field technicians.</p>
                </div>
            </div>
            <a href="{{ route('service-tickets.index') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-2xs">
                &larr; Back to Tickets
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-[#f8fafc] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100 transition-colors duration-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @if ($errors->any())
                <div class="mb-6 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-500/40 text-rose-800 dark:text-rose-300 p-4 rounded-2xl text-xs shadow-xs">
                    <p class="font-bold mb-1 flex items-center gap-2">
                        <span>⚠️</span> Please fix the following issues:
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-rose-700 dark:text-slate-300 pl-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="p-6 border-b border-slate-200/90 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white font-heading">New Breakdown Complaint Form</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Fill in the failure symptoms and dispatch details.</p>
                    </div>
                    <span class="text-[10px] font-mono uppercase tracking-widest text-blue-700 dark:text-blue-400 px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 font-bold">
                        24/7 SLA Telemetry
                    </span>
                </div>

                <form method="POST" action="{{ route('service-tickets.store') }}" class="p-6 sm:p-8 space-y-6">
                    @csrf

                    <!-- Customer & AMC section -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Customer / Site Lead <span class="text-rose-500">*</span>
                            </label>
                            <select name="lead_id" id="lead_id" required onchange="checkLeadAmc(this.value)" 
                                    class="w-full text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 min-h-[44px] px-3.5 py-2.5 shadow-xs">
                                <option value="">-- Select Customer / Site --</option>
                                @foreach($leads as $lead)
                                    <option value="{{ $lead->id }}" {{ old('lead_id', $selectedLeadId) == $lead->id ? 'selected' : '' }}>
                                        {{ $lead->customer_name }} ({{ $lead->phone }}) - {{ $lead->site_address }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Covered Under AMC? (Optional)
                            </label>
                            <select name="amc_contract_id" id="amc_contract_id" 
                                    class="w-full text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 min-h-[44px] px-3.5 py-2.5 shadow-xs">
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
                    <div id="amc-notice-box" class="hidden p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-500/40 rounded-2xl text-xs text-emerald-900 dark:text-emerald-300 flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">🛡️</span>
                            <div>
                                <strong id="amc-notice-text" class="text-emerald-950 dark:text-emerald-200 font-bold">Active AMC Contract Detected!</strong>
                                <p class="text-[11px] text-emerald-700 dark:text-emerald-400 mt-0.5">Billing coverage has been auto-set to Warranty/AMC.</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-300 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 font-bold text-[10px] uppercase tracking-wider">
                            Free AMC Visit
                        </span>
                    </div>

                    <!-- Issue Type & Priority -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Breakdown Category / Issue <span class="text-rose-500">*</span>
                            </label>
                            <select name="issue_type" required 
                                    class="w-full text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 min-h-[44px] px-3.5 py-2.5 shadow-xs">
                                @foreach(\App\Models\ServiceTicket::issueTypeOptions() as $k => $label)
                                    <option value="{{ $k }}" {{ old('issue_type') === $k ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Priority Level <span class="text-rose-500">*</span>
                            </label>
                            <select name="priority" required 
                                    class="w-full text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 min-h-[44px] px-3.5 py-2.5 shadow-xs">
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>🟢 Low (Minor issue)</option>
                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>🟡 Medium (Standard fault)</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>🟠 High (Critical camera/monitoring down)</option>
                                <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>🔴 Critical / Emergency (Entire CCTV blackout)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Title & Details -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Complaint Title / Summary <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}" required 
                            placeholder="e.g. 2 Cameras at Main Entrance showing blank black screen"
                            class="w-full text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 min-h-[44px] px-3.5 py-2.5 shadow-xs">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Detailed Description of Complaint / Symptoms <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="description" rows="4" required
                            placeholder="Detail what the customer observed, error codes on DVR, time since issue started, specific camera channels..."
                            class="w-full text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 p-2.5 shadow-2xs">{{ old('description') }}</textarea>
                    </div>

                    <!-- Dispatch & Scheduling -->
                    <div class="p-5 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200/90 dark:border-slate-800 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-600 dark:bg-blue-400"></span>
                            <h4 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider font-heading">Technician Dispatch & Visit</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Assign Service Technician
                                </label>
                                <select name="assigned_technician_id" 
                                        class="w-full text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0f172a] text-slate-900 dark:text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 min-h-[44px] px-3.5 py-2.5 shadow-xs">
                                    <option value="">-- Assign Later --</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}" {{ old('assigned_technician_id') == $tech->id ? 'selected' : '' }}>
                                            🔧 {{ $tech->name }} ({{ $tech->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Scheduled Repair Date
                                </label>
                                <input type="date" name="scheduled_date" value="{{ old('scheduled_date', date('Y-m-d')) }}"
                                    class="w-full text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0f172a] text-slate-900 dark:text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 min-h-[44px] px-3.5 py-2.5 shadow-xs">
                            </div>
                        </div>
                    </div>

                    <!-- Billing & Cost -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Billing Type <span class="text-rose-500">*</span>
                            </label>
                            <select name="billing_type" id="billing_type" required 
                                    class="w-full text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 min-h-[44px] px-3.5 py-2.5 shadow-xs">
                                <option value="warranty_amc" {{ old('billing_type', 'warranty_amc') === 'warranty_amc' ? 'selected' : '' }}>🛡️ Under Warranty / AMC Contract (Free)</option>
                                <option value="billable" {{ old('billing_type') === 'billable' ? 'selected' : '' }}>💰 Billable Repair (Parts / Service Charge)</option>
                                <option value="free_courtesy" {{ old('billing_type') === 'free_courtesy' ? 'selected' : '' }}>🎁 Free Courtesy Visit</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Estimated / Fixed Service Charge (₹)
                            </label>
                            <input type="number" step="0.01" name="cost" value="{{ old('cost') }}" placeholder="0.00"
                                class="w-full text-sm font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 min-h-[44px] px-3.5 py-2.5 shadow-xs">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                        <a href="{{ route('service-tickets.index') }}"
                           class="btn-secondary min-h-[44px] px-5 py-2.5 text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            Cancel
                        </a>
                        <button type="submit"
                                class="btn-amber min-h-[44px] px-6 py-2.5 text-sm font-bold rounded-xl shadow-xs transition-colors">
                            <span>Log & Dispatch Ticket</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
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

        // Run on initial load if lead pre-selected
        document.addEventListener('DOMContentLoaded', () => {
            const initialLeadId = document.getElementById('lead_id').value;
            if (initialLeadId) {
                checkLeadAmc(initialLeadId);
            }
        });
    </script>
</x-app-layout>
