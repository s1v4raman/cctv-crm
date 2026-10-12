<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('service-tickets.index') }}" class="px-2.5 py-1.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded text-xs font-medium transition">
                    &larr; Back
                </a>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                            Ticket #{{ $ticket->ticket_no }}
                        </h1>
                        <span class="font-bold text-xs capitalize
                            @if($ticket->status === 'resolved' || $ticket->status === 'closed') text-emerald-600 dark:text-emerald-400
                            @elseif($ticket->status === 'in_progress') text-amber-600 dark:text-amber-400
                            @elseif($ticket->status === 'assigned') text-blue-600 dark:text-blue-400
                            @elseif($ticket->status === 'cancelled') text-rose-600 dark:text-rose-400
                            @else text-slate-600 dark:text-slate-400 @endif">
                            {{ $ticket->status_label }}
                        </span>
                        <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                        <span class="font-bold text-xs uppercase
                            @if($ticket->priority === 'critical') text-rose-600 dark:text-rose-400
                            @elseif($ticket->priority === 'high') text-amber-600 dark:text-amber-400
                            @elseif($ticket->priority === 'medium') text-blue-600 dark:text-blue-400
                            @else text-slate-600 dark:text-slate-400 @endif">
                            {{ $ticket->priority_label }} Priority
                        </span>
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Logged on {{ $ticket->created_at->format('d M Y, h:i A') }} by {{ $ticket->createdBy?->name ?? 'System' }}
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('service-tickets.edit', $ticket) }}"
                   class="px-3 py-1.5 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-medium transition">
                    Edit
                </a>
                @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('service-tickets.destroy', $ticket) }}" onsubmit="return confirm('Are you sure you want to delete this service ticket?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-3 py-1.5 rounded border border-rose-300 dark:border-rose-800 bg-white dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950 text-rose-600 dark:text-rose-400 text-xs font-medium transition">
                            Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        @if (session('success'))
            <div class="p-3 rounded border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-900 dark:text-emerald-200 text-xs font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ showAcceptModal: false, showRejectModal: false }">
            
            <!-- Left 2 Cols: Complaint & Troubleshooting / Resolution Details -->
            <div class="lg:col-span-2 space-y-6">

                {{-- Admin Accept / Reject Decision Banner --}}
                @if($ticket->status === 'open')
                    <div class="bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-700 rounded p-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Service Request Awaiting Decision</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Review premises issue and assign certified technician or decline request.</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button" @click="showAcceptModal = true" class="px-3.5 py-1.5 bg-[#2563eb] hover:bg-blue-700 text-white font-medium text-xs rounded transition">
                                    Accept &amp; Dispatch
                                </button>
                                <button type="button" @click="showRejectModal = true" class="px-3.5 py-1.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-medium text-xs rounded transition">
                                    Decline
                                </button>
                            </div>
                        </div>
                    </div>
                @elseif($ticket->status === 'cancelled')
                    <div class="bg-white dark:bg-slate-900 border border-rose-300 dark:border-rose-700 rounded p-4">
                        <h2 class="text-sm font-bold text-rose-700 dark:text-rose-400">Request Declined / Cancelled</h2>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 whitespace-pre-line">{{ $ticket->resolution_notes ?: 'This request was cancelled or declined.' }}</p>
                    </div>
                @elseif($ticket->status === 'assigned' || $ticket->status === 'in_progress')
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Work Order Dispatched</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Assigned to <strong class="text-slate-900 dark:text-white">{{ $ticket->assignedTechnician?->name ?? 'Technician' }}</strong> 
                                @if($ticket->scheduled_date) &bull; Visit: {{ $ticket->scheduled_date->format('d M Y') }} @endif
                            </p>
                        </div>
                        <button type="button" @click="showRejectModal = true" class="px-3 py-1.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium rounded transition">
                            Cancel / Decline
                        </button>
                    </div>
                @endif
                
                <!-- Breakdown Complaint Overview Card -->
                <div class="bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 p-4 space-y-3">
                    <div class="flex items-start justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div>
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">
                                {{ $ticket->issue_type_label }}
                            </span>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white mt-0.5">{{ $ticket->title }}</h2>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block">Billing Category</span>
                            <span class="text-xs font-bold text-slate-900 dark:text-white">
                                @if($ticket->billing_type === 'warranty_amc') Warranty / AMC
                                @elseif($ticket->billing_type === 'billable') Billable (&#8377;{{ number_format($ticket->cost, 2) }})
                                @else Free @endif
                            </span>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Reported Symptoms &amp; Notes</h3>
                        <div class="text-xs text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 p-3 rounded border border-slate-200 dark:border-slate-700 whitespace-pre-line">
                            {{ $ticket->description }}
                        </div>
                    </div>
                </div>

                <!-- Repair & Troubleshooting Logs Card -->
                <div class="bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 p-4 space-y-3">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Technician Troubleshooting &amp; Fix Summary</h2>

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Troubleshooting Notes / Root Cause
                            </label>
                            <div class="text-xs text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 p-3 rounded border border-slate-200 dark:border-slate-700 min-h-[40px] whitespace-pre-line">
                                {{ $ticket->troubleshooting_notes ?: 'No troubleshooting notes recorded yet.' }}
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Replacement Parts Used
                            </label>
                            <div class="text-xs text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 p-3 rounded border border-slate-200 dark:border-slate-700 min-h-[35px] whitespace-pre-line">
                                {{ $ticket->parts_replaced ?: 'No replacement parts logged.' }}
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Final Resolution
                            </label>
                            <div class="text-xs text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 p-3 rounded border border-slate-200 dark:border-slate-700 min-h-[40px] whitespace-pre-line">
                                {{ $ticket->resolution_notes ?: 'Pending technician resolution.' }}
                            </div>
                        </div>

                        @if($ticket->resolved_at)
                            <div class="text-xs text-emerald-600 dark:text-emerald-400 font-bold pt-1">
                                Resolved on {{ $ticket->resolved_at->format('d M Y, h:i A') }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Status / Resolution Update Form -->
                <div class="bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 p-4 space-y-3">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                        Update Resolution / Status
                    </h2>
                    <form method="POST" action="{{ route('service-tickets.updateStatus', $ticket) }}" class="space-y-3 text-xs">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                                <select name="status" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded px-3 py-2 text-xs focus:outline-none" style="height: 38px;">
                                    @foreach(\App\Models\ServiceTicket::statusOptions() as $k => $label)
                                        <option value="{{ $k }}" {{ $ticket->status === $k ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Parts Replaced (Optional)</label>
                                <input type="text" name="parts_replaced" value="{{ $ticket->parts_replaced }}" placeholder="e.g. 1x SMPS Adapter"
                                    class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded px-3 py-2 text-xs focus:outline-none" style="height: 38px;">
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Troubleshooting Notes</label>
                            <textarea name="troubleshooting_notes" rows="2" placeholder="Diagnosis notes..."
                                class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded px-3 py-2 text-xs focus:outline-none">{{ $ticket->troubleshooting_notes }}</textarea>
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Resolution Summary</label>
                            <textarea name="resolution_notes" rows="2" placeholder="Action taken..."
                                class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded px-3 py-2 text-xs focus:outline-none">{{ $ticket->resolution_notes }}</textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                    class="px-4 py-2 bg-[#2563eb] hover:bg-blue-700 text-white font-medium text-xs rounded transition">
                                Save Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column: Customer Info, Technician & Schedule, AMC -->
            <div class="space-y-4">
                
                <!-- Customer Information Card -->
                <div class="bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 p-4 space-y-2">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                        <h2 class="text-xs font-bold text-slate-700 dark:text-slate-300">Customer &amp; Site</h2>
                        <a href="{{ route('leads.show', $ticket->lead) }}" class="text-xs text-blue-600 hover:underline">
                            Profile &rarr;
                        </a>
                    </div>
                    <div class="space-y-2 text-xs text-slate-600 dark:text-slate-400">
                        <div>
                            <span class="text-slate-500 block text-[11px]">Customer Name</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $ticket->lead->customer_name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Phone</span>
                            <a href="tel:{{ $ticket->lead->phone }}" class="text-blue-600 hover:underline">
                                {{ $ticket->lead->phone }}
                            </a>
                        </div>
                        @if($ticket->lead->email)
                            <div>
                                <span class="text-slate-500 block text-[11px]">Email</span>
                                <span class="text-slate-900 dark:text-white">{{ $ticket->lead->email }}</span>
                            </div>
                        @endif
                        <div>
                            <span class="text-slate-500 block text-[11px]">Site Address</span>
                            <span class="text-slate-900 dark:text-white">{{ $ticket->lead->site_address ?: 'Not specified' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Assigned Technician & Dispatch Card -->
                <div class="bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 p-4 space-y-3">
                    <h2 class="text-xs font-bold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800 pb-2">
                        Technician Dispatch
                    </h2>

                    @if($ticket->assignedTechnician)
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded border border-slate-200 dark:border-slate-700 text-xs">
                            <p class="font-bold text-slate-900 dark:text-white">{{ $ticket->assignedTechnician->name }}</p>
                            <p class="text-[11px] text-slate-400">{{ $ticket->assignedTechnician->email }}</p>
                        </div>
                    @else
                        <div class="p-3 bg-amber-50 dark:bg-amber-950/40 rounded border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-xs font-medium">
                            No technician currently assigned.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('service-tickets.assign', $ticket) }}" class="space-y-3 text-xs">
                        @csrf
                        <div>
                            <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Technician</label>
                            <select name="assigned_technician_id" required class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded px-3 py-2 text-xs focus:outline-none" style="height: 38px;">
                                <option value="">-- Choose Technician --</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}" {{ $ticket->assigned_technician_id == $tech->id ? 'selected' : '' }}>
                                        {{ $tech->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Scheduled Date</label>
                            <input type="date" name="scheduled_date" value="{{ $ticket->scheduled_date?->format('Y-m-d') }}"
                                class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded px-3 py-2 text-xs focus:outline-none" style="height: 38px;">
                        </div>
                        <button type="submit"
                                class="w-full px-4 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-medium text-xs rounded transition">
                            Update Dispatch
                        </button>
                    </form>
                </div>

                <!-- AMC Contract Details (if covered) -->
                @if($ticket->amcContract)
                    <div class="bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 p-4 space-y-2">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                            <h2 class="text-xs font-bold text-slate-700 dark:text-slate-300">AMC Contract</h2>
                            <a href="{{ route('amcs.show', $ticket->amcContract) }}" class="text-xs text-blue-600 hover:underline">
                                View &rarr;
                            </a>
                        </div>
                        <div class="text-xs text-slate-600 dark:text-slate-400 space-y-1">
                            <p class="font-bold text-slate-900 dark:text-white">{{ $ticket->amcContract->contract_no }}</p>
                            <p class="text-[11px] text-slate-500">
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
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
             @keydown.escape.window="showAcceptModal = false">
            
            <div class="bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 max-w-lg w-full p-6 space-y-4" @click.stop>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Approve Work Request</h3>
                        <p class="text-xs text-slate-500">Ticket #{{ $ticket->ticket_no }} &bull; {{ $ticket->lead->customer_name }}</p>
                    </div>
                    <button type="button" @click="showAcceptModal = false" class="text-slate-400 hover:text-slate-600">
                        ✕
                    </button>
                </div>

                <form method="POST" action="{{ route('service-tickets.accept', $ticket) }}" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Assign Technician (Optional)</label>
                        <select name="assigned_technician_id" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded px-3 py-2 text-xs focus:outline-none" style="height: 38px;">
                            <option value="">-- Assign Later --</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" {{ $ticket->assigned_technician_id == $tech->id ? 'selected' : '' }}>
                                    {{ $tech->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Scheduled Visit Date</label>
                        <input type="date" name="scheduled_date" value="{{ $ticket->scheduled_date?->format('Y-m-d') ?: now()->addDay()->format('Y-m-d') }}"
                            class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded px-3 py-2 text-xs focus:outline-none" style="height: 38px;">
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Approval Note (Optional)</label>
                        <textarea name="admin_notes" rows="2" placeholder="Approval message..."
                            class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded px-3 py-2 text-xs focus:outline-none"></textarea>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showAcceptModal = false" class="px-4 py-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium text-xs transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 rounded bg-[#2563eb] hover:bg-blue-700 text-white font-medium text-xs transition">
                            Confirm Approval
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Decline & Reject Modal -->
        <div x-show="showRejectModal" 
             style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
             @keydown.escape.window="showRejectModal = false">
            
            <div class="bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 max-w-lg w-full p-6 space-y-4" @click.stop>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Decline Work Request</h3>
                        <p class="text-xs text-slate-500">Ticket #{{ $ticket->ticket_no }} &bull; {{ $ticket->lead->customer_name }}</p>
                    </div>
                    <button type="button" @click="showRejectModal = false" class="text-slate-400 hover:text-slate-600">
                        ✕
                    </button>
                </div>

                <form method="POST" action="{{ route('service-tickets.reject', $ticket) }}" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Reason for Declining *</label>
                        <textarea name="rejection_reason" required rows="3" placeholder="Specify reason..."
                            class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded px-3 py-2 text-xs focus:outline-none"></textarea>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showRejectModal = false" class="px-4 py-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium text-xs transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 rounded bg-rose-600 hover:bg-rose-700 text-white font-medium text-xs transition">
                            Decline Request
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
