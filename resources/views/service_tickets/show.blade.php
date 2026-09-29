<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('service-tickets.index') }}" class="p-2 rounded-xl bg-[#0F172A] border border-white/10 text-slate-400 hover:text-white hover:border-amber-400/40 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-xl sm:text-2xl font-black text-white font-heading tracking-tight font-mono">
                            #{{ $ticket->ticket_no }}
                        </h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider
                            @if($ticket->status === 'resolved' || $ticket->status === 'closed') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                            @elseif($ticket->status === 'in_progress') bg-amber-500/20 text-amber-400 border border-amber-500/30
                            @elseif($ticket->status === 'assigned') bg-sky-500/20 text-sky-400 border border-sky-500/30
                            @elseif($ticket->status === 'cancelled') bg-rose-500/20 text-rose-400 border border-rose-500/30
                            @else bg-rose-500/20 text-rose-400 border border-rose-500/30 @endif">
                            {{ $ticket->status_label }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider
                            @if($ticket->priority === 'critical') bg-rose-500/20 text-rose-400 border border-rose-500/30
                            @elseif($ticket->priority === 'high') bg-amber-500/20 text-amber-400 border border-amber-500/30
                            @elseif($ticket->priority === 'medium') bg-purple-500/20 text-purple-400 border border-purple-500/30
                            @else bg-slate-800 text-slate-400 border border-slate-700 @endif">
                            {{ $ticket->priority_label }} Priority
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Logged on {{ $ticket->created_at->format('d M Y, h:i A') }} by {{ $ticket->createdBy?->name ?? 'System' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ route('service-tickets.edit', $ticket) }}"
                   class="px-3.5 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                    <span>✏️ Edit Ticket</span>
                </a>
                @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('service-tickets.destroy', $ticket) }}" onsubmit="return confirm('Are you sure you want to delete this service ticket?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-3.5 py-2 rounded-xl bg-rose-950/40 hover:bg-rose-900/60 text-rose-400 border border-rose-800/50 text-xs font-bold transition flex items-center gap-1.5">
                            <span>🗑️ Delete</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-[#060913] min-h-screen text-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            @if (session('success'))
                <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center gap-2">
                    <span>✓ {{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ showAcceptModal: false, showRejectModal: false }">
                
                <!-- Left 2 Cols: Complaint & Troubleshooting / Resolution Details -->
                <div class="lg:col-span-2 space-y-6">

                    {{-- Admin Accept / Reject Decision Banner --}}
                    @if($ticket->status === 'open')
                        <div class="bg-gradient-to-r from-amber-500/15 via-[#0F172A] to-[#0F172A] border border-amber-500/40 border-l-4 border-l-amber-400 rounded-2xl p-5 shadow-xl shadow-amber-500/5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-start gap-3.5">
                                    <div class="p-3 bg-gradient-to-br from-amber-400 to-amber-500 text-slate-950 rounded-xl shadow-lg shadow-amber-500/30 text-lg font-black shrink-0 flex items-center justify-center">
                                        ⚡
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-sm font-black text-amber-300 font-heading tracking-wide">Client Service Request Awaiting Decision</h3>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-400/20 text-amber-300 border border-amber-400/30">
                                                Action Required
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-100 mt-1">Review premises issue and assign certified technician or decline request.</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2.5 shrink-0">
                                    <button type="button" @click="showAcceptModal = true" class="px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-slate-950 font-black text-xs rounded-xl shadow-lg shadow-emerald-500/20 transition flex items-center gap-1.5">
                                        <span>✓ Accept & Dispatch</span>
                                    </button>
                                    <button type="button" @click="showRejectModal = true" class="px-4 py-2.5 bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                                        <span>✕ Decline</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @elseif($ticket->status === 'cancelled')
                        <div class="bg-rose-950/40 border border-rose-500/40 rounded-2xl p-5 shadow-xl flex items-start gap-3.5">
                            <div class="p-3 bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded-xl text-lg font-bold">✕</div>
                            <div>
                                <h3 class="text-sm font-extrabold text-rose-300 font-heading">Work Request Declined / Rejected</h3>
                                <p class="text-xs text-slate-300 mt-1 whitespace-pre-line">{{ $ticket->resolution_notes ?: 'This request was cancelled or declined by the admin.' }}</p>
                            </div>
                        </div>
                    @elseif($ticket->status === 'assigned' || $ticket->status === 'in_progress')
                        <div class="bg-emerald-950/30 border border-emerald-500/40 rounded-2xl p-5 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-3.5">
                                <div class="p-3 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-xl text-lg font-bold">✓</div>
                                <div>
                                    <h3 class="text-sm font-extrabold text-emerald-300 font-heading">Work Order Dispatched & Scheduled</h3>
                                    <p class="text-xs text-slate-300 mt-0.5">
                                        Assigned to <strong class="text-white">{{ $ticket->assignedTechnician?->name ?? 'Technician' }}</strong> 
                                        @if($ticket->scheduled_date) • Visit: <strong class="text-amber-400">{{ $ticket->scheduled_date->format('d M Y') }}</strong> @endif
                                    </p>
                                </div>
                            </div>
                            <button type="button" @click="showRejectModal = true" class="px-3.5 py-1.5 bg-rose-500/15 hover:bg-rose-500/25 text-rose-400 border border-rose-500/30 font-bold text-xs rounded-xl transition">
                                Cancel / Decline
                            </button>
                        </div>
                    @endif
                    
                    <!-- Breakdown Complaint Overview Card -->
                    <div class="bg-[#0F172A] rounded-2xl border border-white/10 shadow-xl p-6">
                        <div class="flex items-start justify-between mb-4 border-b border-white/5 pb-4">
                            <div>
                                <span class="inline-block text-[10px] font-extrabold uppercase tracking-wider text-amber-400 bg-amber-500/10 border border-amber-500/20 px-2.5 py-1 rounded-md mb-2">
                                    {{ $ticket->issue_type_label }}
                                </span>
                                <h3 class="text-base sm:text-lg font-extrabold text-white font-heading">{{ $ticket->title }}</h3>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Billing Category</span>
                                <span class="text-xs font-bold text-sky-400">
                                    @if($ticket->billing_type === 'warranty_amc') 🛡️ Warranty / AMC
                                    @elseif($ticket->billing_type === 'billable') 💰 Billable (₹{{ number_format($ticket->cost, 2) }})
                                    @else 🎁 Courtesy Free @endif
                                </span>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Reported Symptoms & Notes</h4>
                            <div class="text-xs text-slate-300 bg-[#060913] p-4 rounded-xl border border-slate-800 whitespace-pre-line leading-relaxed">
                                {{ $ticket->description }}
                            </div>
                        </div>
                    </div>

                    <!-- Repair & Troubleshooting Logs Card -->
                    <div class="bg-[#0F172A] rounded-2xl border border-white/10 shadow-xl p-6">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span>🔧</span> Technician Field Troubleshooting & Fix Summary
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                                    Troubleshooting Notes / Root Cause
                                </label>
                                <div class="text-xs text-slate-300 bg-[#060913] p-3.5 rounded-xl border border-slate-800 min-h-[45px] whitespace-pre-line">
                                    {{ $ticket->troubleshooting_notes ?: 'No troubleshooting notes recorded yet.' }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                                    Replacement Parts Used
                                </label>
                                <div class="text-xs text-slate-300 bg-[#060913] p-3.5 rounded-xl border border-slate-800 min-h-[35px] whitespace-pre-line">
                                    {{ $ticket->parts_replaced ?: 'No replacement parts logged.' }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                                    Final Resolution / Commissioning Note
                                </label>
                                <div class="text-xs text-emerald-300 bg-emerald-950/20 p-3.5 rounded-xl border border-emerald-500/30 min-h-[45px] whitespace-pre-line">
                                    {{ $ticket->resolution_notes ?: 'Pending technician resolution.' }}
                                </div>
                            </div>

                            @if($ticket->resolved_at)
                                <div class="text-xs text-emerald-400 font-bold pt-2 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Resolved on {{ $ticket->resolved_at->format('d M Y, h:i A') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Status / Resolution Update Form -->
                    <div class="bg-[#0F172A] rounded-2xl border border-white/10 shadow-xl p-6">
                        <h3 class="text-sm font-extrabold text-white font-heading uppercase tracking-wider mb-4">
                            Update Resolution / Status
                        </h3>
                        <form method="POST" action="{{ route('service-tickets.updateStatus', $ticket) }}" class="space-y-4 text-xs">
                            @csrf
                            @method('PATCH')

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-bold text-slate-300 uppercase tracking-wider text-[10px] mb-1">Change Status</label>
                                    <select name="status" class="w-full bg-[#060913] border border-slate-700 text-white rounded-xl px-3.5 py-2.5 focus:border-amber-400 focus:outline-none">
                                        @foreach(\App\Models\ServiceTicket::statusOptions() as $k => $label)
                                            <option value="{{ $k }}" {{ $ticket->status === $k ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-300 uppercase tracking-wider text-[10px] mb-1">Parts Replaced (Optional)</label>
                                    <input type="text" name="parts_replaced" value="{{ $ticket->parts_replaced }}" placeholder="e.g. 1x BNC Connector, 1x 12V SMPS Adapter"
                                        class="w-full bg-[#060913] border border-slate-700 text-white rounded-xl px-3.5 py-2.5 focus:border-amber-400 focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-300 uppercase tracking-wider text-[10px] mb-1">Troubleshooting Notes</label>
                                <textarea name="troubleshooting_notes" rows="2" placeholder="Describe diagnosis and root cause..."
                                    class="w-full bg-[#060913] border border-slate-700 text-white rounded-xl px-3.5 py-2.5 focus:border-amber-400 focus:outline-none">{{ $ticket->troubleshooting_notes }}</textarea>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-300 uppercase tracking-wider text-[10px] mb-1">Resolution Summary</label>
                                <textarea name="resolution_notes" rows="2" placeholder="Describe actions taken to fix the issue..."
                                    class="w-full bg-[#060913] border border-slate-700 text-white rounded-xl px-3.5 py-2.5 focus:border-amber-400 focus:outline-none">{{ $ticket->resolution_notes }}</textarea>
                            </div>

                            <button type="submit"
                                    class="px-6 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg shadow-amber-500/20 transition">
                                💾 Save Progress & Resolution
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Customer Info, Technician & Schedule, AMC -->
                <div class="space-y-6">
                    
                    <!-- Customer Information Card -->
                    <div class="bg-[#0F172A] rounded-2xl border border-white/10 shadow-xl p-6">
                        <div class="flex items-center justify-between mb-4 border-b border-white/5 pb-3">
                            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Customer & Site</h3>
                            <a href="{{ route('leads.show', $ticket->lead) }}" class="text-xs font-bold text-amber-400 hover:underline">
                                Profile &rarr;
                            </a>
                        </div>
                        <div class="space-y-3 text-xs text-slate-300">
                            <div>
                                <span class="text-slate-500 block text-[10px] uppercase font-bold">Customer Name</span>
                                <span class="font-bold text-white text-sm mt-0.5 block">{{ $ticket->lead->customer_name }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500 block text-[10px] uppercase font-bold">Phone Number</span>
                                <a href="tel:{{ $ticket->lead->phone }}" class="text-amber-400 font-bold font-mono hover:underline mt-0.5 block">
                                    📞 {{ $ticket->lead->phone }}
                                </a>
                            </div>
                            @if($ticket->lead->email)
                                <div>
                                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Email</span>
                                    <span class="mt-0.5 block text-slate-200">✉️ {{ $ticket->lead->email }}</span>
                                </div>
                            @endif
                            <div>
                                <span class="text-slate-500 block text-[10px] uppercase font-bold">Site Address</span>
                                <span class="text-slate-200 font-medium mt-0.5 block">📍 {{ $ticket->lead->site_address ?: 'Not specified' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Technician & Dispatch Card -->
                    <div class="bg-[#0F172A] rounded-2xl border border-white/10 shadow-xl p-6">
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-white/5 pb-3">
                            Assigned Technician Dispatch
                        </h3>

                        @if($ticket->assignedTechnician)
                            <div class="flex items-center gap-3 p-3.5 bg-[#060913] rounded-xl border border-slate-800 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-black text-sm">
                                    {{ substr($ticket->assignedTechnician->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-white">{{ $ticket->assignedTechnician->name }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $ticket->assignedTechnician->email }}</p>
                                </div>
                            </div>
                        @else
                            <div class="p-3.5 bg-amber-500/10 rounded-xl border border-amber-500/30 text-amber-400 text-xs mb-4 font-semibold">
                                ⚠️ No technician currently assigned to this ticket.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('service-tickets.assign', $ticket) }}" class="space-y-3 text-xs">
                            @csrf
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Re-assign Technician</label>
                                <select name="assigned_technician_id" required class="w-full bg-[#060913] border border-slate-700 text-white rounded-xl px-3.5 py-2.5 focus:border-amber-400 focus:outline-none">
                                    <option value="">-- Choose Technician --</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}" {{ $ticket->assigned_technician_id == $tech->id ? 'selected' : '' }}>
                                            {{ $tech->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Scheduled Visit Date</label>
                                <input type="date" name="scheduled_date" value="{{ $ticket->scheduled_date?->format('Y-m-d') }}"
                                    class="w-full bg-[#060913] border border-slate-700 text-white rounded-xl px-3.5 py-2.5 focus:border-amber-400 focus:outline-none">
                            </div>
                            <button type="submit"
                                    class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs transition">
                                🚀 Update Dispatch
                            </button>
                        </form>
                    </div>

                    <!-- AMC Contract Details (if covered) -->
                    @if($ticket->amcContract)
                        <div class="bg-[#0F172A] rounded-2xl border border-white/10 shadow-xl p-6">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">AMC Contract</h3>
                                <a href="{{ route('amcs.show', $ticket->amcContract) }}" class="text-xs font-bold text-amber-400 hover:underline">
                                    View Contract &rarr;
                                </a>
                            </div>
                            <div class="p-3.5 bg-emerald-500/10 rounded-xl border border-emerald-500/30 text-xs text-emerald-300 space-y-1">
                                <p class="font-bold font-mono">{{ $ticket->amcContract->contract_no }}</p>
                                <p class="text-[11px] text-emerald-400/80">
                                    Valid until: {{ $ticket->amcContract->end_date->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Accept & Approve Modal -->
            <div x-show="showAcceptModal" 
                 style="display: none;"
                 class="fixed inset-0 z-50 overflow-y-auto"
                 x-transition>
                
                <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md" @click="showAcceptModal = false"></div>

                <div class="relative min-h-screen flex items-center justify-center p-4">
                    <div class="relative bg-[#0F172A] rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-white/10 overflow-hidden" @click.stop>
                        
                        <div class="flex items-center justify-between pb-4 border-b border-white/10">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-black text-lg">
                                    ✓
                                </div>
                                <div>
                                    <h3 class="text-base font-extrabold text-white font-heading">Approve & Accept Work Request</h3>
                                    <p class="text-xs text-slate-400">Ticket #{{ $ticket->ticket_no }} • {{ $ticket->lead->customer_name }}</p>
                                </div>
                            </div>
                            <button type="button" @click="showAcceptModal = false" class="p-1 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">
                                ✕
                            </button>
                        </div>

                        <form method="POST" action="{{ route('service-tickets.accept', $ticket) }}" class="space-y-4 mt-5 text-xs">
                            @csrf

                            <div>
                                <label class="block font-bold text-slate-300 uppercase tracking-wider text-[10px] mb-1">Assign Field Technician (Optional)</label>
                                <select name="assigned_technician_id" class="w-full bg-[#060913] border border-slate-700 text-white rounded-xl px-3.5 py-2.5 focus:border-amber-400 focus:outline-none">
                                    <option value="">-- Assign Later --</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}" {{ $ticket->assigned_technician_id == $tech->id ? 'selected' : '' }}>
                                            {{ $tech->name }} (Technician)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-300 uppercase tracking-wider text-[10px] mb-1">Scheduled On-Site Visit Date</label>
                                <input type="date" name="scheduled_date" value="{{ $ticket->scheduled_date?->format('Y-m-d') ?: now()->addDay()->format('Y-m-d') }}"
                                    class="w-full bg-[#060913] border border-slate-700 text-white rounded-xl px-3.5 py-2.5 focus:border-amber-400 focus:outline-none">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-300 uppercase tracking-wider text-[10px] mb-1">Approval Note for Customer (Optional)</label>
                                <textarea name="admin_notes" rows="2" placeholder="e.g. Your CCTV site survey & work request has been approved. Our certified technician will arrive on the scheduled date."
                                    class="w-full bg-[#060913] border border-slate-700 text-white rounded-xl px-3.5 py-2.5 focus:border-amber-400 focus:outline-none"></textarea>
                            </div>

                            <div class="pt-3 flex items-center justify-end gap-2.5 border-t border-white/10">
                                <button type="button" @click="showAcceptModal = false" class="px-4 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold text-xs hover:bg-slate-800">
                                    Cancel
                                </button>
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-1.5">
                                    <span>✓ Confirm Approval & Accept</span>
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <!-- Decline & Reject Modal -->
            <div x-show="showRejectModal" 
                 style="display: none;"
                 class="fixed inset-0 z-50 overflow-y-auto"
                 x-transition>
                
                <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md" @click="showRejectModal = false"></div>

                <div class="relative min-h-screen flex items-center justify-center p-4">
                    <div class="relative bg-[#0F172A] rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-white/10 overflow-hidden" @click.stop>
                        
                        <div class="flex items-center justify-between pb-4 border-b border-white/10">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-rose-500/20 text-rose-400 border border-rose-500/30 flex items-center justify-center font-black text-lg">
                                    ✕
                                </div>
                                <div>
                                    <h3 class="text-base font-extrabold text-white font-heading">Decline / Reject Work Request</h3>
                                    <p class="text-xs text-slate-400">Ticket #{{ $ticket->ticket_no }} • {{ $ticket->lead->customer_name }}</p>
                                </div>
                            </div>
                            <button type="button" @click="showRejectModal = false" class="p-1 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">
                                ✕
                            </button>
                        </div>

                        <form method="POST" action="{{ route('service-tickets.reject', $ticket) }}" class="space-y-4 mt-5 text-xs">
                            @csrf

                            <div>
                                <label class="block font-bold text-slate-300 uppercase tracking-wider text-[10px] mb-1">Reason for Declining * (Visible to Customer)</label>
                                <textarea name="rejection_reason" required rows="3" placeholder="Please specify why this work request is being declined..."
                                    class="w-full bg-[#060913] border border-slate-700 text-white rounded-xl px-3.5 py-2.5 focus:border-rose-400 focus:outline-none"></textarea>
                            </div>

                            <div class="pt-3 flex items-center justify-end gap-2.5 border-t border-white/10">
                                <button type="button" @click="showRejectModal = false" class="px-4 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold text-xs hover:bg-slate-800">
                                    Cancel
                                </button>
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-400 hover:to-rose-500 text-white font-extrabold text-xs shadow-lg shadow-rose-500/20 transition flex items-center gap-1.5">
                                    <span>✕ Decline Request</span>
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
