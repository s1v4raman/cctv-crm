<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-rose-500 p-0.5 shadow-lg shadow-amber-500/20 flex items-center justify-center">
                    <div class="w-full h-full bg-[#060913] rounded-[10px] flex items-center justify-center text-amber-400 font-bold">
                        ⚙️
                    </div>
                </div>
                <div>
                    <h2 class="text-xl font-black leading-tight text-white font-heading tracking-tight">Edit Service Ticket: <span class="font-mono text-amber-400">{{ $ticket->ticket_no }}</span></h2>
                    <p class="mt-0.5 text-xs text-slate-400">Modify breakdown complaint details, technician dispatch, or resolution.</p>
                </div>
            </div>
            <a href="{{ route('service-tickets.show', $ticket) }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-800/80 text-xs font-bold text-slate-300 hover:text-white hover:bg-slate-700 transition shadow-sm">
                &larr; Back to Ticket
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-[#060913] min-h-screen text-slate-200" style="background-color: #060913;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @if ($errors->any())
                <div class="mb-6 bg-rose-950/40 border border-rose-500/40 text-rose-300 p-4 rounded-2xl text-xs shadow-lg">
                    <p class="font-bold mb-1 flex items-center gap-2">
                        <span>⚠️</span> Please fix the following issues:
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-slate-300 pl-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-[#0f172a] rounded-2xl border border-slate-800 shadow-2xl overflow-hidden" style="background-color: #0f172a;">
                <div class="p-6 border-b border-slate-800 bg-[#0b1120]/60 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-white font-heading">Update Ticket Details</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Edit status, assigned technician, symptoms or billing.</p>
                    </div>
                    <span class="text-[10px] font-mono uppercase tracking-widest text-amber-400 px-2 py-0.5 rounded bg-amber-500/10 border border-amber-500/20">
                        {{ $ticket->ticket_no }}
                    </span>
                </div>

                <form method="POST" action="{{ route('service-tickets.update', $ticket) }}" class="p-6 sm:p-8 space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Customer & AMC section -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-200 mb-1.5">
                                Customer / Site Lead <span class="text-amber-400">*</span>
                            </label>
                            <select name="lead_id" required 
                                    class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20">
                                @foreach($leads as $lead)
                                    <option value="{{ $lead->id }}" {{ old('lead_id', $ticket->lead_id) == $lead->id ? 'selected' : '' }}>
                                        {{ $lead->customer_name }} ({{ $lead->phone }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-200 mb-1.5">
                                Covered Under AMC?
                            </label>
                            <select name="amc_contract_id" 
                                    class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20">
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
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-200 mb-1.5">
                                Issue Category <span class="text-amber-400">*</span>
                            </label>
                            <select name="issue_type" required 
                                    class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20">
                                @foreach(\App\Models\ServiceTicket::issueTypeOptions() as $k => $label)
                                    <option value="{{ $k }}" {{ old('issue_type', $ticket->issue_type) === $k ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-200 mb-1.5">
                                Priority Level <span class="text-amber-400">*</span>
                            </label>
                            <select name="priority" required 
                                    class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20">
                                @foreach(\App\Models\ServiceTicket::priorityOptions() as $k => $label)
                                    <option value="{{ $k }}" {{ old('priority', $ticket->priority) === $k ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-200 mb-1.5">
                                Status <span class="text-amber-400">*</span>
                            </label>
                            <select name="status" required 
                                    class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20">
                                @foreach(\App\Models\ServiceTicket::statusOptions() as $k => $label)
                                    <option value="{{ $k }}" {{ old('status', $ticket->status) === $k ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Title & Details -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-200 mb-1.5">
                            Complaint Title / Summary <span class="text-amber-400">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title', $ticket->title) }}" required 
                            class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-200 mb-1.5">
                            Detailed Description <span class="text-amber-400">*</span>
                        </label>
                        <textarea name="description" rows="3" required
                            class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20">{{ old('description', $ticket->description) }}</textarea>
                    </div>

                    <!-- Dispatch & Scheduling -->
                    <div class="p-4 bg-[#060913] rounded-2xl border border-slate-800 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            <h4 class="text-xs font-extrabold text-white uppercase tracking-wider font-heading">Technician Dispatch & Visit</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-200 mb-1.5">
                                    Assigned Technician
                                </label>
                                <select name="assigned_technician_id" 
                                        class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#0b1120] text-white focus:border-amber-400 focus:ring-amber-400/20">
                                    <option value="">-- Unassigned --</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}" {{ old('assigned_technician_id', $ticket->assigned_technician_id) == $tech->id ? 'selected' : '' }}>
                                            🔧 {{ $tech->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-200 mb-1.5">
                                    Scheduled Date
                                </label>
                                <input type="date" name="scheduled_date" value="{{ old('scheduled_date', $ticket->scheduled_date?->format('Y-m-d')) }}"
                                    class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#0b1120] text-white focus:border-amber-400 focus:ring-amber-400/20">
                            </div>
                        </div>
                    </div>

                    <!-- Troubleshooting & Resolution -->
                    <div class="space-y-4 p-4 bg-[#060913] rounded-2xl border border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <h4 class="text-xs font-extrabold text-white uppercase tracking-wider font-heading">Troubleshooting & Engineering Notes</h4>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-200 mb-1.5">
                                Troubleshooting Notes
                            </label>
                            <textarea name="troubleshooting_notes" rows="2"
                                class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#0b1120] text-white focus:border-amber-400 focus:ring-amber-400/20">{{ old('troubleshooting_notes', $ticket->troubleshooting_notes) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-200 mb-1.5">
                                Parts Replaced
                            </label>
                            <input type="text" name="parts_replaced" value="{{ old('parts_replaced', $ticket->parts_replaced) }}"
                                class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#0b1120] text-white focus:border-amber-400 focus:ring-amber-400/20">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-200 mb-1.5">
                                Resolution Notes
                            </label>
                            <textarea name="resolution_notes" rows="2"
                                class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#0b1120] text-white focus:border-amber-400 focus:ring-amber-400/20">{{ old('resolution_notes', $ticket->resolution_notes) }}</textarea>
                        </div>
                    </div>

                    <!-- Billing -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-200 mb-1.5">
                                Billing Type <span class="text-amber-400">*</span>
                            </label>
                            <select name="billing_type" required 
                                    class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20">
                                <option value="warranty_amc" {{ old('billing_type', $ticket->billing_type) === 'warranty_amc' ? 'selected' : '' }}>🛡️ Warranty / AMC (Free)</option>
                                <option value="billable" {{ old('billing_type', $ticket->billing_type) === 'billable' ? 'selected' : '' }}>💰 Billable Repair</option>
                                <option value="free_courtesy" {{ old('billing_type', $ticket->billing_type) === 'free_courtesy' ? 'selected' : '' }}>🎁 Free Courtesy Visit</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-200 mb-1.5">
                                Charge / Amount (₹)
                            </label>
                            <input type="number" step="0.01" name="cost" value="{{ old('cost', $ticket->cost) }}"
                                class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
                        <a href="{{ route('service-tickets.show', $ticket) }}"
                           class="btn-secondary min-h-[44px] px-5 py-2.5 text-sm font-semibold rounded-xl border border-slate-700 text-slate-400 hover:text-white hover:bg-slate-800 transition">
                            Cancel
                        </a>
                        <button type="submit"
                                class="btn-amber min-h-[44px] px-6 py-2.5 text-sm font-bold">
                            <span>Save Changes</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
