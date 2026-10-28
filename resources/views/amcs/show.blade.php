<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <a href="{{ route('amcs.index') }}" class="back-link">
                    &larr; Back
                </a>
                <div class="flex items-center gap-3 mt-1">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                        AMC {{ $amc->contract_no }}
                    </h2>
                    @php
                        $amcStatusColor = '#2563eb';
                        if ($amc->status === 'active') $amcStatusColor = '#16a34a';
                        elseif ($amc->status === 'expired' || $amc->status === 'cancelled') $amcStatusColor = '#dc2626';
                    @endphp
                    <span class="text-sm font-bold" style="color: {{ $amcStatusColor }};">
                        {{ ucfirst($amc->status) }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('service-tickets.create', ['lead_id' => $amc->lead_id, 'amc_contract_id' => $amc->id]) }}" class="btn-primary text-xs">
                    Log Ticket
                </a>
                @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('amcs.destroy', $amc) }}"
                          data-confirm="Delete this AMC contract?" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="danger-link text-xs ml-2 cursor-pointer">
                            Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="pg-wrap py-6">
        <div class="pg-inner space-y-6">

            @if (session('status'))
                <div class="p-3 rounded border border-emerald-300 bg-emerald-50 text-emerald-800 text-xs">
                    {{ session('status') }}
                </div>
            @endif

            {{-- 1. Contract Information: Two-column table inside standard section --}}
            <div class="crm-section">
                <h3>AMC Contract Information</h3>
                <table class="crm-table w-full">
                    <tbody>
                        <tr>
                            <td style="width: 25%; color: var(--text-muted); font-weight: 500;">Contract Number</td>
                            <td style="color: var(--text-main); font-weight: 500;">{{ $amc->contract_no }}</td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Servicing Period</td>
                            <td style="color: var(--text-main); font-weight: 500;">{{ $amc->start_date->format('d M Y') }} to {{ $amc->end_date->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Annual Value</td>
                            <td style="color: var(--text-main); font-weight: 500;">₹{{ number_format($amc->value, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Servicing Frequency</td>
                            <td style="color: var(--text-main); font-weight: 500;">{{ ucfirst(str_replace('_', ' ', $amc->frequency)) }}</td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Customer Premise</td>
                            <td style="color: var(--text-main); font-weight: 500;">
                                <a href="{{ route('leads.show', $amc->lead) }}" class="action-link">
                                    {{ $amc->lead->customer_name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Status</td>
                            <td style="color: var(--text-main); font-weight: 500;">
                                <form method="POST" action="{{ route('amcs.updateStatus', $amc) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <select aria-label="Status" name="status" class="crm-select text-xs" style="width: auto;" onchange="this.form.submit()">
                                        <option value="pending" @selected($amc->status === 'pending')>Pending</option>
                                        <option value="active" @selected($amc->status === 'active')>Active</option>
                                        <option value="expired" @selected($amc->status === 'expired')>Expired</option>
                                        <option value="cancelled" @selected($amc->status === 'cancelled')>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        @if($amc->notes)
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Coverage &amp; Scope</td>
                            <td style="color: var(--text-main); font-weight: 500;">{{ $amc->notes }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- 2. Preventive Servicing Schedule --}}
            <div class="crm-section">
                <h3>Preventive Servicing Schedule ({{ $amc->visits->count() }} Visits)</h3>
                
                <div class="overflow-x-auto">
                    <table class="crm-table w-full">
                        <thead>
                            <tr>
                                <th>Scheduled Date</th>
                                <th>Assigned Technician</th>
                                <th>Servicing Report</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($amc->visits as $visit)
                                <tr>
                                    <td class="font-semibold text-slate-900" style="white-space: nowrap;">
                                        {{ $visit->scheduled_date->format('d M Y') }}
                                    </td>
                                    <td style="white-space: nowrap;">
                                        @if($visit->status === 'completed')
                                            <span class="font-medium text-slate-900">{{ $visit->assignedTechnician->name ?? 'Technician' }}</span>
                                        @else
                                            <form method="POST" action="{{ route('amc-visits.assign', $visit) }}" class="inline-flex items-center gap-1.5">
                                                @csrf
                                                <select aria-label="Assigned technician" name="assigned_technician_id" class="crm-select text-xs" style="width: auto; height: 32px;" required>
                                                    <option value="">Select Technician...</option>
                                                    @foreach($technicians as $tech)
                                                        <option value="{{ $tech->id }}" @selected($visit->assigned_technician_id == $tech->id)>
                                                            {{ $tech->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn-secondary text-xs" style="height: 32px; padding: 0 8px;">Assign</button>
                                            </form>
                                        @endif
                                    </td>
                                    <td>
                                        @if($visit->status === 'completed')
                                            <div class="text-xs text-slate-600">
                                                <span>{{ $visit->completed_at?->format('d M Y') }}: </span>
                                                <span class="font-medium text-slate-900">{{ $visit->completion_notes }}</span>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400">Pending visit</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-{{ strtolower($visit->status) }}">
                                            {{ ucfirst($visit->status) }}
                                        </span>
                                    </td>
                                    <td class="text-right" style="white-space: nowrap;">
                                        @if($visit->status !== 'completed')
                                            <form method="POST" action="{{ route('amc-visits.complete', $visit) }}" class="inline-flex items-center gap-1.5">
                                                @csrf
                                                <input aria-label="Completion notes" type="text" name="completion_notes" placeholder="e.g. Lens cleaned, tested" required
                                                       class="crm-input text-xs" style="width: 180px; height: 32px;">
                                                <button type="submit" class="btn-primary text-xs" style="height: 32px; padding: 0 8px;">
                                                    Mark Done
                                                </button>
                                            </form>
                                        @else
                                            <span class="status-completed">Done</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-6 text-slate-500 text-sm">
                                        No servicing visits scheduled yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 3. Associated Breakdown / Service Tickets --}}
            <div class="crm-section">
                <h3>Breakdown &amp; Repair Tickets</h3>
                
                <div class="overflow-x-auto">
                    <table class="crm-table w-full">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Issue Summary</th>
                                <th>Priority</th>
                                <th>Technician</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($amc->serviceTickets as $ticket)
                                <tr>
                                    <td class="font-mono font-semibold" style="white-space: nowrap;">
                                        <a href="{{ route('service-tickets.show', $ticket) }}" class="action-link">
                                            {{ $ticket->ticket_no }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="font-semibold text-slate-900">{{ $ticket->title }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $ticket->issue_type_label }}</div>
                                    </td>
                                    <td class="capitalize">{{ $ticket->priority }}</td>
                                    <td style="white-space: nowrap;">{{ $ticket->assignedTechnician->name ?? 'Unassigned' }}</td>
                                    <td>
                                        <span class="status-{{ strtolower($ticket->status) }}">
                                            {{ ucfirst($ticket->status_label) }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('service-tickets.show', $ticket) }}" class="action-link">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-slate-500 text-sm">
                                        No breakdown tickets under this contract yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
