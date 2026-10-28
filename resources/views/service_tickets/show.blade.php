<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('service-tickets.index') }}" class="back-link">&larr; Back</a>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-1">
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Ticket #{{ $ticket->ticket_no }}</h2>
                    @php
                        $ticketStatusColor = '#2563eb';
                        if (in_array($ticket->status, ['resolved', 'closed'])) $ticketStatusColor = '#16a34a';
                        elseif ($ticket->status === 'cancelled') $ticketStatusColor = '#dc2626';
                    @endphp
                    <span class="text-sm font-bold" style="color: {{ $ticketStatusColor }};">
                        {{ $ticket->status_label }}
                    </span>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @if($ticket->lead)
                        <a href="{{ route('leads.show', $ticket->lead) }}" class="btn-secondary">
                            Customer Profile
                        </a>
                    @endif
                    @if($ticket->amcContract)
                        <a href="{{ route('amcs.show', $ticket->amcContract) }}" class="btn-secondary">
                            AMC Contract
                        </a>
                    @endif
                    <a href="{{ route('service-tickets.edit', $ticket) }}" class="btn-secondary">
                        Edit Details
                    </a>
                    @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('service-tickets.destroy', $ticket) }}" data-confirm="Are you sure you want to delete this service ticket?" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="danger-link p-0 bg-transparent border-0 cursor-pointer text-xs ml-2">
                                Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    <div class="max-w-[1200px] mx-auto space-y-6">
        @if (session('success'))
            <div class="p-3 bg-white dark:bg-slate-900 border border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded text-xs">
                {{ session('success') }}
            </div>
        @endif

        {{-- Section 1: Ticket & Complaint Overview (Two-column detail table) --}}
        <div class="crm-section">
            <h3>Ticket Details</h3>
            <table class="crm-table">
                <tbody>
                    <tr>
                        <td class="w-1/3 text-slate-500 font-bold">Ticket Number</td>
                        <td class="w-2/3 text-slate-900 dark:text-white font-mono">{{ $ticket->ticket_no }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Subject / Title</td>
                        <td class="text-slate-900 dark:text-white">{{ $ticket->title }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Issue Category</td>
                        <td class="text-slate-900 dark:text-white">{{ $ticket->issue_type_label }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Status</td>
                        <td class="text-slate-900 dark:text-white">
                            <span class="status-{{ in_array($ticket->status, ['resolved', 'closed']) ? 'completed' : ($ticket->status === 'cancelled' ? 'cancelled' : ($ticket->status === 'in_progress' ? 'progress' : 'quoted')) }}">
                                {{ $ticket->status_label }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Priority</td>
                        <td class="text-slate-900 dark:text-white">{{ $ticket->priority_label }} Priority</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Billing Category</td>
                        <td class="text-slate-900 dark:text-white">
                            @if($ticket->billing_type === 'warranty_amc') Warranty / AMC
                            @elseif($ticket->billing_type === 'billable') Billable (&#8377;{{ number_format($ticket->cost, 2) }})
                            @else Free @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Reported Symptoms & Notes</td>
                        <td class="text-slate-900 dark:text-white whitespace-pre-line">{{ $ticket->description ?: 'None specified' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Logged On</td>
                        <td class="text-slate-900 dark:text-white">{{ $ticket->created_at->format('d M Y, h:i A') }} (by {{ $ticket->createdBy?->name ?? 'System' }})</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Section 2: Customer & Premises Info (Two-column detail table) --}}
        <div class="crm-section">
            <h3>Customer & Premises</h3>
            <table class="crm-table">
                <tbody>
                    <tr>
                        <td class="w-1/3 text-slate-500 font-bold">Customer Name</td>
                        <td class="w-2/3 text-slate-900 dark:text-white">
                            <a href="{{ route('leads.show', $ticket->lead) }}" class="action-link">
                                {{ $ticket->lead->customer_name }}
                            </a>
                            @if($ticket->lead->company_name)
                                <span class="text-slate-500 text-xs">({{ $ticket->lead->company_name }})</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Contact Phone</td>
                        <td class="text-slate-900 dark:text-white">
                            <a href="tel:{{ $ticket->lead->phone }}" class="action-link">
                                {{ $ticket->lead->phone }}
                            </a>
                        </td>
                    </tr>
                    @if($ticket->lead->email)
                        <tr>
                            <td class="text-slate-500 font-bold">Email Address</td>
                            <td class="text-slate-900 dark:text-white">{{ $ticket->lead->email }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="text-slate-500 font-bold">Premises Address</td>
                        <td class="text-slate-900 dark:text-white">{{ $ticket->lead->site_address ?: 'Not specified' }}</td>
                    </tr>
                    @if($ticket->amcContract)
                        <tr>
                            <td class="text-slate-500 font-bold">AMC Agreement</td>
                            <td class="text-slate-900 dark:text-white">
                                <a href="{{ route('amcs.show', $ticket->amcContract) }}" class="action-link">
                                    {{ $ticket->amcContract->contract_no }}
                                </a>
                                (Valid until {{ $ticket->amcContract->end_date->format('d M Y') }})
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Section 3: Field Technician Dispatch (Two-column detail table & assignment form) --}}
        <div class="crm-section">
            <h3>Technician Dispatch</h3>
            <table class="crm-table mb-4">
                <tbody>
                    <tr>
                        <td class="w-1/3 text-slate-500 font-bold">Assigned Technician</td>
                        <td class="w-2/3 text-slate-900 dark:text-white">
                            {{ $ticket->assignedTechnician?->name ?? 'None Assigned' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Scheduled Visit Date</td>
                        <td class="text-slate-900 dark:text-white">
                            {{ $ticket->scheduled_date ? $ticket->scheduled_date->format('d M Y') : 'Not scheduled' }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <form method="POST" action="{{ route('service-tickets.assign', $ticket) }}">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="crm-label">Technician <span class="required-marker">*</span></label>
                        <select aria-label="Technician" name="assigned_technician_id" required class="crm-select">
                            <option value="">Select technician</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" {{ $ticket->assigned_technician_id == $tech->id ? 'selected' : '' }}>
                                    {{ $tech->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="crm-label">Scheduled Date</label>
                        <input aria-label="Scheduled Date" type="date" name="scheduled_date" value="{{ $ticket->scheduled_date?->format('Y-m-d') }}" class="crm-input">
                    </div>
                </div>
                <div class="form-actions mt-4">
                    <button type="submit" class="btn-secondary">
                        Save
                    </button>
                </div>
            </form>
        </div>

        {{-- Section 4: Diagnostics, Spares & Resolution Summary --}}
        <div class="crm-section">
            <h3>Technician Resolution Log</h3>
            <table class="crm-table mb-4">
                <tbody>
                    <tr>
                        <td class="w-1/3 text-slate-500 font-bold">Troubleshooting Findings</td>
                        <td class="w-2/3 text-slate-900 dark:text-white whitespace-pre-line">{{ $ticket->troubleshooting_notes ?: 'No troubleshooting notes recorded yet.' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Replacement Spares / Parts</td>
                        <td class="text-slate-900 dark:text-white whitespace-pre-line">{{ $ticket->parts_replaced ?: 'No replacement parts logged.' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Final Resolution Notes</td>
                        <td class="text-slate-900 dark:text-white whitespace-pre-line">{{ $ticket->resolution_notes ?: 'Pending resolution.' }}</td>
                    </tr>
                    @if($ticket->resolved_at)
                        <tr>
                            <td class="text-slate-500 font-bold">Resolved Timestamp</td>
                            <td class="text-slate-900 dark:text-white">{{ $ticket->resolved_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            {{-- Form to update status and resolution notes --}}
            <form method="POST" action="{{ route('service-tickets.updateStatus', $ticket) }}">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="crm-label">Status <span class="required-marker">*</span></label>
                        <select aria-label="Status" name="status" class="crm-select">
                            @foreach(\App\Models\ServiceTicket::statusOptions() as $k => $label)
                                <option value="{{ $k }}" {{ $ticket->status === $k ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="crm-label">Parts Replaced</label>
                        <input aria-label="Parts Replaced" type="text" name="parts_replaced" value="{{ $ticket->parts_replaced }}" placeholder="e.g. 1x 12V 2A SMPS Adapter" class="crm-input">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="crm-label">Troubleshooting Notes</label>
                    <textarea aria-label="Troubleshooting Notes" name="troubleshooting_notes" rows="2" placeholder="e.g. BNC connector loose, crimped new pin" class="crm-input h-auto py-2">{{ $ticket->troubleshooting_notes }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="crm-label">Resolution Summary</label>
                    <textarea aria-label="Resolution Summary" name="resolution_notes" rows="2" placeholder="e.g. System tested and cameras restored to live feed" class="crm-input h-auto py-2">{{ $ticket->resolution_notes }}</textarea>
                </div>

                <div class="form-actions">
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
</x-app-layout>
