<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            {{-- Left: Back link, Lead Details label, and Customer Name once as main heading --}}
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <a href="{{ route('leads.index') }}" class="hover:text-slate-800 dark:hover:text-white font-medium flex items-center gap-1 transition">
                        &larr; Back
                    </a>
                    <span>&bull;</span>
                    <span class="font-semibold uppercase tracking-wider text-[11px]">Lead Details</span>
                </div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight mt-1">
                    {{ $lead->customer_name }}
                </h1>
            </div>

            {{-- Right: Main buttons, outlined secondary buttons, Status dropdown, and Delete --}}
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Status Dropdown (Single source of status, no duplicate pill) --}}
                @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('leads.status', $lead) }}" class="inline">
                        @csrf
                        <select name="status" onchange="this.form.submit()"
                                class="px-2.5 py-1.5 rounded text-xs font-semibold border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:border-slate-400 cursor-pointer">
                            @foreach (['new','contacted','quoted','won','lost'] as $s)
                                <option value="{{ $s }}" @selected($lead->status === $s)>Status: {{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif

                {{-- Secondary actions row: outlined buttons in one color --}}
                <a href="{{ route('site-surveys.create', ['lead_id' => $lead->id]) }}"
                   class="px-3 py-1.5 rounded text-xs font-semibold border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                    Survey
                </a>

                <a href="{{ route('estimator.index', ['lead_id' => $lead->id]) }}"
                   class="px-3 py-1.5 rounded text-xs font-semibold border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                    BOM
                </a>

                <a href="{{ route('service-tickets.create', ['lead_id' => $lead->id]) }}"
                   class="px-3 py-1.5 rounded text-xs font-semibold border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                    Ticket
                </a>

                <a href="{{ route('leads.vcard', $lead) }}"
                   class="px-3 py-1.5 rounded text-xs font-semibold border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition"
                   title="Download vCard contact">
                    Save Contact
                </a>

                {{-- Main buttons: Edit and New Quote --}}
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('leads.edit', $lead) }}"
                       class="px-3.5 py-1.5 rounded text-xs font-semibold border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                        Edit
                    </a>
                @endif

                <a href="{{ route('quotations.create', $lead) }}"
                   class="px-3.5 py-1.5 rounded text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition shadow-2xs">
                    New Quote
                </a>

                {{-- Labeled Delete Button --}}
                @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('leads.destroy', $lead) }}"
                          onsubmit="return confirm('Delete {{ addslashes($lead->customer_name) }}? All quotations and jobs will also be deleted.')"
                          class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-3 py-1.5 rounded text-xs font-semibold border border-rose-200 dark:border-rose-900/60 bg-white dark:bg-slate-800 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
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

            {{-- 1. Profile Section: Simple 2-column definition list, gray labels on left, normal dark values on right --}}
            <div class="bg-white dark:bg-[#0f172a] border border-[#ddd] dark:border-slate-800 rounded-[6px] p-5">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white pb-3 border-b border-[#ddd] dark:border-slate-800 mb-4">
                    Customer Profile
                </h3>
                
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-xs">
                    {{-- Phone: Normal dark text, no yellow --}}
                    <div class="flex items-center justify-between py-1.5 border-b border-[#eee] dark:border-slate-800">
                        <dt class="text-slate-500 dark:text-slate-400">Phone</dt>
                        <dd class="text-slate-900 dark:text-white font-medium">{{ $lead->phone }}</dd>
                    </div>

                    {{-- Email --}}
                    <div class="flex items-center justify-between py-1.5 border-b border-[#eee] dark:border-slate-800">
                        <dt class="text-slate-500 dark:text-slate-400">Email</dt>
                        <dd class="text-slate-900 dark:text-white font-medium">{{ $lead->email ?: '—' }}</dd>
                    </div>

                    {{-- Company (if any) --}}
                    @if($lead->company_name)
                    <div class="flex items-center justify-between py-1.5 border-b border-[#eee] dark:border-slate-800">
                        <dt class="text-slate-500 dark:text-slate-400">Company</dt>
                        <dd class="text-slate-900 dark:text-white font-medium">{{ $lead->company_name }}</dd>
                    </div>
                    @endif

                    {{-- Source: Plain text, no colors --}}
                    <div class="flex items-center justify-between py-1.5 border-b border-[#eee] dark:border-slate-800">
                        <dt class="text-slate-500 dark:text-slate-400">Source</dt>
                        <dd class="text-slate-900 dark:text-white font-medium capitalize">{{ $lead->source ?: '—' }}</dd>
                    </div>

                    {{-- Created Date --}}
                    <div class="flex items-center justify-between py-1.5 border-b border-[#eee] dark:border-slate-800">
                        <dt class="text-slate-500 dark:text-slate-400">Created</dt>
                        <dd class="text-slate-900 dark:text-white font-medium">{{ $lead->created_at->format('d M Y') }}</dd>
                    </div>

                    {{-- Site Address --}}
                    @if($lead->site_address)
                    <div class="flex items-center justify-between py-1.5 border-b border-[#eee] dark:border-slate-800 md:col-span-2">
                        <dt class="text-slate-500 dark:text-slate-400">Site Address</dt>
                        <dd class="text-slate-900 dark:text-white font-medium text-right">{{ $lead->site_address }}</dd>
                    </div>
                    @endif

                    {{-- Notes --}}
                    @if($lead->notes)
                    <div class="py-2 md:col-span-2">
                        <dt class="text-slate-500 dark:text-slate-400 mb-1">Notes</dt>
                        <dd class="text-slate-800 dark:text-slate-200 leading-relaxed">{{ $lead->notes }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Active AMC Notice (if present) --}}
            @if($lead->activeAmcContract)
                <div class="p-4 rounded-[6px] bg-white dark:bg-[#0f172a] border border-[#ddd] dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div>
                        <div class="font-bold text-slate-900 dark:text-white">Active AMC: {{ $lead->activeAmcContract->contract_no }}</div>
                        <div class="text-slate-500 mt-0.5">{{ ucfirst($lead->activeAmcContract->frequency) }} servicing &bull; Valid until {{ $lead->activeAmcContract->end_date->format('d M Y') }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('service-tickets.create', ['lead_id' => $lead->id, 'amc_contract_id' => $lead->activeAmcContract->id]) }}" 
                           class="px-3 py-1.5 rounded text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition">
                            + Log AMC Ticket
                        </a>
                        <a href="{{ route('amcs.show', $lead->activeAmcContract) }}" 
                           class="px-3 py-1.5 rounded text-xs font-medium border border-[#ddd] dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 transition">
                            View Agreement &rarr;
                        </a>
                    </div>
                </div>
            @endif

            {{-- 2. Lower Section: Site Surveys --}}
            <div class="bg-white dark:bg-[#0f172a] border border-[#ddd] dark:border-slate-800 rounded-[6px] overflow-hidden">
                <div class="p-4 border-b border-[#ddd] dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Site Surveys</h3>
                    <a href="{{ route('site-surveys.create', ['lead_id' => $lead->id]) }}"
                       class="px-3 py-1.5 rounded text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition">
                        + Schedule Survey
                    </a>
                </div>

                @if($lead->siteSurveys->isEmpty())
                    {{-- Clean single-line empty state --}}
                    <div class="p-4 text-xs text-slate-500">
                        No site surveys conducted yet for this lead.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="detail-table">
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
                                @foreach ($lead->siteSurveys as $s)
                                    <tr>
                                        <td class="font-semibold text-slate-900 dark:text-white">
                                            <a href="{{ route('site-surveys.show', $s) }}" class="hover:underline">
                                                {{ $s->survey_date->format('d M Y') }}
                                            </a>
                                        </td>
                                        <td class="text-slate-600 dark:text-slate-400">{{ $s->surveyedBy->name ?? '—' }}</td>
                                        <td class="font-bold text-slate-800 dark:text-slate-200">{{ $s->camera_count_recommended ?? '—' }}</td>
                                        <td class="text-slate-600 dark:text-slate-400">
                                            {{ $s->photos->count() ? $s->photos->count() . ' photo(s)' : '—' }}
                                        </td>
                                        <td class="text-xs uppercase font-semibold text-slate-700 dark:text-slate-300">
                                            {{ $s->status }}
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('site-surveys.show', $s) }}" class="text-xs text-blue-600 hover:underline font-semibold">
                                                View Report &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- 3. Lower Section: Quotations --}}
            <div class="bg-white dark:bg-[#0f172a] border border-[#ddd] dark:border-slate-800 rounded-[6px] overflow-hidden">
                <div class="p-4 border-b border-[#ddd] dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Quotations</h3>
                    <a href="{{ route('quotations.create', $lead) }}"
                       class="px-3 py-1.5 rounded text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition">
                        + Add
                    </a>
                </div>

                @if($lead->quotations->isEmpty())
                    {{-- Clean single-line empty state --}}
                    <div class="p-4 text-xs text-slate-500">
                        No quotations generated yet for this lead.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="detail-table">
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
                                @foreach ($lead->quotations as $q)
                                    <tr>
                                        <td class="font-semibold text-slate-900 dark:text-white">
                                            <a href="{{ route('quotations.show', $q) }}" class="hover:underline">
                                                {{ $q->quotation_no }}
                                            </a>
                                        </td>
                                        <td class="text-slate-600 dark:text-slate-400">{{ $q->quotation_date?->format('d M Y') ?? '—' }}</td>
                                        <td class="text-slate-600 dark:text-slate-400">{{ $q->valid_until?->format('d M Y') ?? '—' }}</td>
                                        <td class="font-bold text-slate-900 dark:text-white">₹{{ number_format($q->total, 2) }}</td>
                                        <td class="text-xs uppercase font-semibold text-slate-700 dark:text-slate-300">
                                            {{ $q->status }}
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('quotations.show', $q) }}" class="text-xs text-blue-600 hover:underline font-semibold">
                                                View Quote &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- 4. Lower Section: Support & Service Tickets --}}
            <div class="bg-white dark:bg-[#0f172a] border border-[#ddd] dark:border-slate-800 rounded-[6px] overflow-hidden">
                <div class="p-4 border-b border-[#ddd] dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tickets</h3>
                    <a href="{{ route('service-tickets.create', ['lead_id' => $lead->id]) }}"
                       class="px-3 py-1.5 rounded text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition">
                        + Log Ticket
                    </a>
                </div>

                @if($lead->serviceTickets->isEmpty())
                    {{-- Clean single-line empty state --}}
                    <div class="p-4 text-xs text-slate-500">
                        No support tickets recorded for this customer.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="detail-table">
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
                                @foreach ($lead->serviceTickets as $t)
                                    <tr>
                                        <td class="font-semibold text-slate-900 dark:text-white">
                                            <a href="{{ route('service-tickets.show', $t) }}" class="hover:underline">
                                                {{ $t->ticket_no }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="font-semibold text-slate-900 dark:text-white">{{ $t->title }}</div>
                                            <div class="text-[11px] text-slate-500">{{ $t->issue_type_label }}</div>
                                        </td>
                                        <td class="text-xs uppercase font-medium text-slate-700 dark:text-slate-300">
                                            {{ $t->priority }}
                                        </td>
                                        <td class="text-slate-600 dark:text-slate-400">{{ $t->assignedTechnician->name ?? 'Unassigned' }}</td>
                                        <td class="text-xs uppercase font-semibold text-slate-700 dark:text-slate-300">
                                            {{ $t->status_label }}
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('service-tickets.show', $t) }}" class="text-xs text-blue-600 hover:underline font-semibold">
                                                Details &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- 5. Lower Section: Installed CCTV Hardware --}}
            <div class="bg-white dark:bg-[#0f172a] border border-[#ddd] dark:border-slate-800 rounded-[6px] overflow-hidden">
                <div class="p-4 border-b border-[#ddd] dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Installed CCTV Hardware & Serial Numbers</h3>
                    <a href="{{ route('equipment.create', ['lead_id' => $lead->id, 'redirect_to' => url()->current()]) }}"
                       class="px-3 py-1.5 rounded text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition">
                        + Add Asset
                    </a>
                </div>

                @if($lead->installedEquipment->isEmpty())
                    {{-- Clean single-line empty state --}}
                    <div class="p-4 text-xs text-slate-500">
                        No equipment installed yet for this customer.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="detail-table">
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
                                @foreach ($lead->installedEquipment as $item)
                                    <tr>
                                        <td class="font-semibold text-slate-900 dark:text-white font-mono">
                                            <a href="{{ route('equipment.show', $item) }}" class="hover:underline">
                                                {{ $item->serial_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="font-semibold text-slate-900 dark:text-white">{{ $item->equipment_name }}</div>
                                            @if($item->product)
                                                <div class="text-[11px] text-slate-500">{{ $item->product->brand }} {{ $item->product->model_no }}</div>
                                            @endif
                                        </td>
                                        <td class="text-slate-600 dark:text-slate-400">{{ $item->location_tag ?: 'Main Site' }}</td>
                                        <td class="text-xs text-slate-600 dark:text-slate-400">
                                            {{ ucfirst($item->mfg_warranty_status) }}
                                        </td>
                                        <td class="text-xs uppercase font-semibold text-slate-700 dark:text-slate-300">
                                            {{ str_replace('_', ' ', $item->status) }}
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('equipment.show', $item) }}" class="text-xs text-blue-600 hover:underline font-semibold">
                                                Asset &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>