<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            {{-- Left: Back link and Customer Name as main heading --}}
            <div>
                <a href="{{ route('leads.index') }}" class="back-link">
                    &larr; Back
                </a>
                <div class="flex items-center gap-3 mt-1">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                        {{ $lead->customer_name }}
                    </h2>
                    @php
                        $leadStatusColor = '#2563eb';
                        if ($lead->status === 'won') $leadStatusColor = '#16a34a';
                        elseif ($lead->status === 'lost') $leadStatusColor = '#dc2626';
                        elseif ($lead->status === 'quoted') $leadStatusColor = '#7c3aed';
                    @endphp
                    <span class="text-sm font-bold" style="color: {{ $leadStatusColor }};">
                        {{ ucfirst($lead->status) }}
                    </span>
                </div>
            </div>

            {{-- Right: Status dropdown, navigation buttons, and Delete link --}}
            <div class="flex items-center gap-2 flex-wrap">
                @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('leads.status', $lead) }}" class="inline">
                        @csrf
                        <select name="status" onchange="this.form.submit()"
                                class="px-2.5 py-1.5 rounded text-xs font-semibold border border-[#ccc] bg-white text-slate-800 focus:outline-none cursor-pointer">
                            @foreach (['new','contacted','quoted','won','lost'] as $s)
                                <option value="{{ $s }}" @selected($lead->status === $s)>Status: {{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif

                {{-- Secondary navigation buttons to other modules --}}
                <a href="{{ route('site-surveys.create', ['lead_id' => $lead->id]) }}" class="btn-secondary text-xs">
                    Survey
                </a>

                <a href="{{ route('estimator.index', ['lead_id' => $lead->id]) }}" class="btn-secondary text-xs">
                    BOM
                </a>

                <a href="{{ route('service-tickets.create', ['lead_id' => $lead->id]) }}" class="btn-secondary text-xs">
                    Ticket
                </a>

                <a href="{{ route('leads.vcard', $lead) }}" class="btn-secondary text-xs">
                    Save Contact
                </a>

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('leads.edit', $lead) }}" class="btn-secondary text-xs">
                        Edit
                    </a>
                @endif

                {{-- Primary action: New Quote --}}
                <a href="{{ route('quotations.create', $lead) }}" class="btn-primary text-xs">
                    New Quote
                </a>

                {{-- Danger action link --}}
                @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('leads.destroy', $lead) }}"
                          onsubmit="return confirm('Delete {{ addslashes($lead->customer_name) }}? All quotations and jobs will also be deleted.')"
                          class="inline">
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

    <style>
        .detail-table {
            width: 100%;
            border-collapse: collapse;
        }
        .detail-table thead tr {
            background-color: #f0f0f0 !important;
            border-bottom: 1px solid #ddd !important;
        }
        .dark .detail-table thead tr {
            background-color: #1e293b !important;
            border-bottom-color: #334155 !important;
        }
        .detail-table thead th {
            padding: 0.65rem 1rem;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #475569;
            text-align: left;
        }
        .dark .detail-table thead th {
            color: #94a3b8;
        }
        .detail-table tbody tr {
            border-bottom: 1px solid #ddd !important;
        }
        .dark .detail-table tbody tr {
            border-bottom-color: #334155 !important;
        }
        .detail-table tbody td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            font-size: 0.8rem;
        }
    </style>

    <div class="pg-wrap py-6">
        <div class="pg-inner space-y-6">

            @if (session('status'))
                <div class="p-3 rounded border border-emerald-300 bg-emerald-50 text-emerald-800 text-xs">
                    {{ session('status') }}
                </div>
            @endif

            {{-- 1. Customer Profile: Two-column table inside standard section --}}
            <div class="crm-section">
                <h3>Customer Profile</h3>
                <table class="crm-table w-full">
                    <tbody>
                        <tr>
                            <td style="width: 25%; color: #555; font-weight: 500;">Phone</td>
                            <td style="color: #222; font-weight: 500;">{{ $lead->phone }}</td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Email</td>
                            <td style="color: #222; font-weight: 500;">{{ $lead->email ?: '—' }}</td>
                        </tr>
                        @if($lead->company_name)
                        <tr>
                            <td style="color: #555; font-weight: 500;">Company</td>
                            <td style="color: #222; font-weight: 500;">{{ $lead->company_name }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td style="color: #555; font-weight: 500;">Source</td>
                            <td style="color: #222; font-weight: 500;">{{ ucfirst($lead->source ?: '—') }}</td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Created</td>
                            <td style="color: #222; font-weight: 500;">{{ $lead->created_at->format('d M Y') }}</td>
                        </tr>
                        @if($lead->site_address)
                        <tr>
                            <td style="color: #555; font-weight: 500;">Site Address</td>
                            <td style="color: #222; font-weight: 500;">{{ $lead->site_address }}</td>
                        </tr>
                        @endif
                        @if($lead->notes)
                        <tr>
                            <td style="color: #555; font-weight: 500;">Notes</td>
                            <td style="color: #222; font-weight: 500;">{{ $lead->notes }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Active AMC Notice (if present) --}}
            @if($lead->activeAmcContract)
                <div class="crm-section flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div>
                        <div class="font-bold text-slate-900">Active AMC: {{ $lead->activeAmcContract->contract_no }}</div>
                        <div class="text-slate-500 mt-0.5">{{ ucfirst($lead->activeAmcContract->frequency) }} servicing &bull; Valid until {{ $lead->activeAmcContract->end_date->format('d M Y') }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('service-tickets.create', ['lead_id' => $lead->id, 'amc_contract_id' => $lead->activeAmcContract->id]) }}" class="btn-primary text-xs">
                            Log Ticket
                        </a>
                        <a href="{{ route('amcs.show', $lead->activeAmcContract) }}" class="btn-secondary text-xs">
                            View Agreement
                        </a>
                    </div>
                </div>
            @endif

            {{-- 2. Lower Section: Site Surveys --}}
            <div class="crm-section">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-[#ddd]">
                    <h3 class="border-b-0 pb-0 mb-0">Site Surveys</h3>
                    <a href="{{ route('site-surveys.create', ['lead_id' => $lead->id]) }}" class="btn-secondary text-xs">
                        Schedule Survey
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="crm-table w-full">
                        <thead>
                            <tr>
                                <th>Survey Date</th>
                                <th>Surveyed By</th>
                                <th>Cameras Rec.</th>
                                <th>Photos</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lead->siteSurveys as $s)
                                <tr>
                                    <td class="font-semibold text-slate-900" style="white-space: nowrap;">
                                        <a href="{{ route('site-surveys.show', $s) }}" class="action-link">
                                            {{ $s->survey_date->format('d M Y') }}
                                        </a>
                                    </td>
                                    <td style="white-space: nowrap;">{{ $s->surveyedBy->name ?? '—' }}</td>
                                    <td>{{ $s->camera_count_recommended ?? '—' }}</td>
                                    <td>{{ $s->photos->count() ? $s->photos->count() . ' photo(s)' : '—' }}</td>
                                    <td>
                                        <span class="status-{{ strtolower($s->status) }}">
                                            {{ ucfirst($s->status) }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('site-surveys.show', $s) }}" class="action-link">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-slate-500 text-sm">
                                        No site surveys yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 3. Lower Section: Quotations --}}
            <div class="crm-section">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-[#ddd]">
                    <h3 class="border-b-0 pb-0 mb-0">Quotations</h3>
                    <a href="{{ route('quotations.create', $lead) }}" class="btn-secondary text-xs">
                        Create Quotation
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="crm-table w-full">
                        <thead>
                            <tr>
                                <th>Quotation No</th>
                                <th>Date</th>
                                <th>Valid Until</th>
                                <th>Grand Total</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lead->quotations as $q)
                                <tr>
                                    <td class="font-semibold text-slate-900" style="white-space: nowrap;">
                                        <a href="{{ route('quotations.show', $q) }}" class="action-link">
                                            {{ $q->quotation_no }}
                                        </a>
                                    </td>
                                    <td style="white-space: nowrap;">{{ $q->quotation_date?->format('d M Y') ?? '—' }}</td>
                                    <td style="white-space: nowrap;">{{ $q->valid_until?->format('d M Y') ?? '—' }}</td>
                                    <td class="font-bold text-slate-900">₹{{ number_format($q->total, 2) }}</td>
                                    <td>
                                        <span class="status-{{ strtolower($q->status) }}">
                                            {{ ucfirst($q->status) }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('quotations.show', $q) }}" class="action-link">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-slate-500 text-sm">
                                        No quotations yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 4. Lower Section: Support & Service Tickets --}}
            <div class="crm-section">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-[#ddd]">
                    <h3 class="border-b-0 pb-0 mb-0">Tickets</h3>
                    <a href="{{ route('service-tickets.create', ['lead_id' => $lead->id]) }}" class="btn-secondary text-xs">
                        Log Ticket
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="crm-table w-full">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Issue Title</th>
                                <th>Priority</th>
                                <th>Assigned Engineer</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lead->serviceTickets as $t)
                                <tr>
                                    <td class="font-semibold text-slate-900" style="white-space: nowrap;">
                                        <a href="{{ route('service-tickets.show', $t) }}" class="action-link">
                                            {{ $t->ticket_no }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="font-semibold text-slate-900">{{ $t->title }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $t->issue_type_label }}</div>
                                    </td>
                                    <td>
                                        <span class="font-medium text-slate-700 capitalize">{{ $t->priority }}</span>
                                    </td>
                                    <td style="white-space: nowrap;">{{ $t->assignedTechnician->name ?? 'Unassigned' }}</td>
                                    <td>
                                        <span class="status-{{ strtolower($t->status) }}">
                                            {{ ucfirst($t->status_label) }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('service-tickets.show', $t) }}" class="action-link">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-slate-500 text-sm">
                                        No support tickets yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 5. Lower Section: Installed CCTV Hardware --}}
            <div class="crm-section">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-[#ddd]">
                    <h3 class="border-b-0 pb-0 mb-0">Installed CCTV Hardware & Serial Numbers</h3>
                    <a href="{{ route('equipment.create', ['lead_id' => $lead->id, 'redirect_to' => url()->current()]) }}" class="btn-secondary text-xs">
                        Add Asset
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="crm-table w-full">
                        <thead>
                            <tr>
                                <th>Serial Number</th>
                                <th>Equipment / Camera Model</th>
                                <th>Location</th>
                                <th>Warranty</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lead->installedEquipment as $item)
                                <tr>
                                    <td class="font-semibold text-slate-900 font-mono" style="white-space: nowrap;">
                                        <a href="{{ route('equipment.show', $item) }}" class="action-link">
                                            {{ $item->serial_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="font-semibold text-slate-900">{{ $item->equipment_name }}</div>
                                        @if($item->product)
                                            <div class="text-[11px] text-slate-500">{{ $item->product->brand }} {{ $item->product->model_no }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $item->location_tag ?: 'Main Site' }}</td>
                                    <td style="white-space: nowrap;">{{ ucfirst($item->mfg_warranty_status) }}</td>
                                    <td>
                                        <span class="status-{{ strtolower($item->status) }}">
                                            {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('equipment.show', $item) }}" class="action-link">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-slate-500 text-sm">
                                        No equipment registered yet
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