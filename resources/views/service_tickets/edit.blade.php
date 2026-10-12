<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold leading-tight text-slate-900 dark:text-white">Edit Service Ticket: <span class="font-mono text-blue-600">{{ $ticket->ticket_no }}</span></h2>
            </div>
            <a href="{{ route('service-tickets.show', $ticket) }}" 
               class="crm-back-link text-xs font-semibold text-blue-600 hover:underline">
                &larr; Back to Ticket
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
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Update Ticket Details</h3>
                    </div>
                    <span class="text-xs font-mono font-bold text-blue-600">
                        {{ $ticket->ticket_no }}
                    </span>
                </div>

                <form method="POST" action="{{ route('service-tickets.update', $ticket) }}" class="p-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Customer & AMC section -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Customer / Site Lead <span class="text-rose-500">*</span>
                            </label>
                            <select name="lead_id" required 
                                    class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                @foreach($leads as $lead)
                                    <option value="{{ $lead->id }}" {{ old('lead_id', $ticket->lead_id) == $lead->id ? 'selected' : '' }}>
                                        {{ $lead->customer_name }} ({{ $lead->phone }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Covered Under AMC?
                            </label>
                            <select name="amc_contract_id" 
                                    class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                <option value="">-- No AMC / Standalone --</option>
                                @foreach($amcContracts as $amc)
                                    <option value="{{ $amc->id }}" {{ old('amc_contract_id', $ticket->amc_contract_id) == $amc->id ? 'selected' : '' }}>
                                        {{ $amc->contract_no }} - {{ $amc->lead->customer_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Issue Type, Priority, Status -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Issue Category <span class="text-rose-500">*</span>
                            </label>
                            <select name="issue_type" required 
                                    class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                @foreach(\App\Models\ServiceTicket::issueTypeOptions() as $k => $label)
                                    <option value="{{ $k }}" {{ old('issue_type', $ticket->issue_type) === $k ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Priority Level <span class="text-rose-500">*</span>
                            </label>
                            <select name="priority" required 
                                    class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                @foreach(\App\Models\ServiceTicket::priorityOptions() as $k => $label)
                                    <option value="{{ $k }}" {{ old('priority', $ticket->priority) === $k ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Status <span class="text-rose-500">*</span>
                            </label>
                            <select name="status" required 
                                    class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                @foreach(\App\Models\ServiceTicket::statusOptions() as $k => $label)
                                    <option value="{{ $k }}" {{ old('status', $ticket->status) === $k ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Title & Details -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Complaint Title / Summary <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title', $ticket->title) }}" required 
                            class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Detailed Description <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="description" rows="3" required
                            class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white p-3 focus:outline-none focus:border-blue-600">{{ old('description', $ticket->description) }}</textarea>
                    </div>

                    <!-- Dispatch & Scheduling -->
                    <div class="p-4 bg-slate-50 dark:bg-slate-900/60 rounded-[4px] border border-slate-200 dark:border-slate-800 space-y-3">
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white">Technician Dispatch &amp; Visit</h4>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Assigned Technician
                                </label>
                                <select name="assigned_technician_id" 
                                        class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b1120] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                    <option value="">-- Unassigned --</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}" {{ old('assigned_technician_id', $ticket->assigned_technician_id) == $tech->id ? 'selected' : '' }}>
                                            {{ $tech->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Scheduled Date
                                </label>
                                <input type="date" name="scheduled_date" value="{{ old('scheduled_date', $ticket->scheduled_date?->format('Y-m-d')) }}"
                                    class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b1120] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                            </div>
                        </div>
                    </div>

                    <!-- Troubleshooting & Resolution -->
                    <div class="space-y-3 p-4 bg-slate-50 dark:bg-slate-900/60 rounded-[4px] border border-slate-200 dark:border-slate-800">
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white">Troubleshooting and Resolution Notes</h4>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Troubleshooting Notes
                            </label>
                            <textarea name="troubleshooting_notes" rows="2"
                                class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b1120] text-slate-900 dark:text-white p-3 focus:outline-none focus:border-blue-600">{{ old('troubleshooting_notes', $ticket->troubleshooting_notes) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Parts Replaced
                            </label>
                            <input type="text" name="parts_replaced" value="{{ old('parts_replaced', $ticket->parts_replaced) }}"
                                class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b1120] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Resolution Notes
                            </label>
                            <textarea name="resolution_notes" rows="2"
                                class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b1120] text-slate-900 dark:text-white p-3 focus:outline-none focus:border-blue-600">{{ old('resolution_notes', $ticket->resolution_notes) }}</textarea>
                        </div>
                    </div>

                    <!-- Billing -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Billing Type <span class="text-rose-500">*</span>
                            </label>
                            <select name="billing_type" required 
                                    class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                                <option value="warranty_amc" {{ old('billing_type', $ticket->billing_type) === 'warranty_amc' ? 'selected' : '' }}>Warranty / AMC (Free)</option>
                                <option value="billable" {{ old('billing_type', $ticket->billing_type) === 'billable' ? 'selected' : '' }}>Billable Repair</option>
                                <option value="free_courtesy" {{ old('billing_type', $ticket->billing_type) === 'free_courtesy' ? 'selected' : '' }}>Free Courtesy Visit</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Charge / Amount (₹)
                            </label>
                            <input type="number" step="0.01" name="cost" value="{{ old('cost', $ticket->cost) }}"
                                class="w-full text-sm rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#060913] text-slate-900 dark:text-white h-[38px] px-3 focus:outline-none focus:border-blue-600">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                        <a href="{{ route('service-tickets.show', $ticket) }}"
                           class="px-4 py-2 text-sm font-medium rounded-[4px] border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-5 py-2 text-sm font-bold rounded-[4px] bg-blue-600 hover:bg-blue-700 text-white transition-colors cursor-pointer">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
