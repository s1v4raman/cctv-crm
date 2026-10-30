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
                        $leadStatusColor = 'var(--crm-accent, #2563eb)';
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
                @if(auth()->user()->isOffice())
                    <form method="POST" action="{{ route('leads.status', $lead) }}" class="inline">
                        @csrf
                        <select aria-label="Status" name="status" onchange="this.form.submit()"
                                class="px-2.5 py-1.5 rounded text-xs font-semibold border border-[#ccc] bg-white text-slate-800 focus:outline-none cursor-pointer">
                            @foreach (['new','contacted','quoted','won','lost'] as $s)
                                <option value="{{ $s }}" @selected($lead->status === $s)>Status: {{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif

                {{-- Secondary navigation buttons to other modules --}}
                @unless(auth()->user()->isTechnician())
                <a href="{{ route('site-surveys.create', ['lead_id' => $lead->id]) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Survey</span>
                </a>
                @endunless

                <a href="{{ route('leads.vcard', $lead) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Save Contact</span>
                </a>

                @if(auth()->user()->isOffice())
                    <a href="{{ route('leads.edit', $lead) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Edit</span>
                    </a>
                @endif

                {{-- Primary action: New Quote --}}
                <a href="{{ route('quotations.create', $lead) }}" class="btn-primary text-xs inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>New Quote</span>
                </a>

                {{-- Danger action link --}}
                @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('leads.destroy', $lead) }}"
                          data-confirm="Delete {{ $lead->customer_name }}? All quotations and jobs will also be deleted."
                          class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1 text-xs text-rose-600 hover:text-rose-700 ml-1 py-1 px-2 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded cursor-pointer transition">
                            <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Delete</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <style>
        .crm-table,
        .detail-table {
            width: 100% !important;
            border-collapse: collapse !important;
            border-spacing: 0 !important;
            border: none !important;
        }
        .crm-table thead tr,
        .detail-table thead tr {
            background-color: #f1f5f9 !important;
            border-top: none !important;
            border-bottom: 1px solid var(--card-border) !important;
        }
        html.dark .crm-table thead tr,
        .dark .detail-table thead tr {
            background-color: #0b1120 !important;
            border-bottom: 1px solid var(--card-border) !important;
        }
        .crm-table thead th,
        .detail-table thead th {
            padding: 0.65rem 1rem !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
            color: var(--card-border) !important;
            text-align: left !important;
            border-top: none !important;
            border-bottom: 1px solid var(--card-border) !important;
            border-right: 1px solid var(--card-border) !important;
        }
        html.dark .crm-table thead th,
        .dark .detail-table thead th {
            color: #94a3b8 !important;
            border-bottom: 1px solid var(--card-border) !important;
            border-right: 1px solid var(--card-border) !important;
        }
        .crm-table tbody tr,
        .detail-table tbody tr {
            background-color: #ffffff !important;
            border-bottom: none !important;
        }
        html.dark .crm-table tbody tr,
        .dark .detail-table tbody tr {
            background-color: #0f172a !important;
            border-bottom: none !important;
        }
        .crm-table tbody td,
        .detail-table tbody td {
            padding: 0.75rem 1rem !important;
            vertical-align: middle !important;
            font-size: 0.85rem !important;
            border-bottom: 1px solid var(--card-border) !important;
            border-right: 1px solid var(--card-border) !important;
            color: var(--card-border) !important;
        }
        html.dark .crm-table tbody td,
        .dark .detail-table tbody td {
            border-bottom: 1px solid var(--card-border) !important;
            border-right: 1px solid var(--card-border) !important;
            color: #cbd5e1 !important;
        }
        .crm-table th:first-child,
        .crm-table td:first-child,
        .detail-table th:first-child,
        .detail-table td:first-child {
            border-left: none !important;
        }
        .crm-table th:last-child,
        .crm-table td:last-child,
        .detail-table th:last-child,
        .detail-table td:last-child {
            border-right: none !important;
        }
        .crm-table tbody tr:last-child td,
        .detail-table tbody tr:last-child td {
            border-bottom: none !important;
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
                            <td style="width: 25%; color: var(--text-muted); font-weight: 500;">Phone</td>
                            <td style="color: var(--text-main); font-weight: 500;">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <span>{{ $lead->phone }}</span>
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="tel:{{ $lead->phone }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition" title="Call Contact">
                                            <svg class="w-3 h-3 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <span>Call</span>
                                        </a>
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition" title="WhatsApp">
                                            <x-icon.whatsapp class="w-3.5 h-3.5 text-black dark:text-white shrink-0" />
                                            <span>WhatsApp</span>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Email</td>
                            <td style="color: var(--text-main); font-weight: 500;">
                                @if($lead->email)
                                    <div class="inline-flex items-center gap-2">
                                        <a href="mailto:{{ $lead->email }}" class="action-link inline-flex items-center gap-1">
                                            <x-icon.email class="w-3.5 h-3.5 text-black dark:text-white" />
                                            <span>{{ $lead->email }}</span>
                                        </a>
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @if($lead->company_name)
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Company</td>
                            <td style="color: var(--text-main); font-weight: 500;">{{ $lead->company_name }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Source</td>
                            <td style="color: var(--text-main); font-weight: 500;">{{ ucfirst($lead->source ?: '—') }}</td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Created</td>
                            <td style="color: var(--text-main); font-weight: 500;">{{ $lead->created_at->format('d M Y') }}</td>
                        </tr>
                        @if($lead->site_address)
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Site Address</td>
                            <td style="color: var(--text-main); font-weight: 500;">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <span>{{ $lead->site_address }}</span>
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($lead->site_address) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition" title="Open in Maps">
                                        <svg class="w-3 h-3 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>Map</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @if($lead->notes)
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Notes</td>
                            <td style="color: var(--text-main); font-weight: 500;">{{ $lead->notes }}</td>
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
                        <a href="{{ route('service-tickets.create', ['lead_id' => $lead->id, 'amc_contract_id' => $lead->activeAmcContract->id]) }}" class="btn-primary text-xs inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                            <span>Log Ticket</span>
                        </a>
                        <a href="{{ route('amcs.show', $lead->activeAmcContract) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>View Agreement</span>
                        </a>
                    </div>
                </div>
            @endif

            {{-- 2. Lower Section: Site Surveys --}}
            <div class="crm-section">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-[var(--card-border)] dark:border-slate-700">
                    <h3 class="border-none pb-0 mb-0" style="border:none !important; border-bottom:none !important; margin-bottom:0 !important; padding-bottom:0 !important;">Site Surveys</h3>
                    @unless(auth()->user()->isTechnician())
                    <a href="{{ route('site-surveys.create', ['lead_id' => $lead->id]) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Schedule Survey</span>
                    </a>
                    @endunless
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
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-[var(--card-border)] dark:border-slate-700">
                    <h3 class="border-none pb-0 mb-0" style="border:none !important; border-bottom:none !important; margin-bottom:0 !important; padding-bottom:0 !important;">Quotations</h3>
                    <a href="{{ route('quotations.create', $lead) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>Create Quotation</span>
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

            {{-- 5. Lower Section: Installed CCTV Hardware --}}
            <div class="crm-section">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-[var(--card-border)] dark:border-slate-700">
                    <h3 class="border-none pb-0 mb-0" style="border:none !important; border-bottom:none !important; margin-bottom:0 !important; padding-bottom:0 !important;">Installed CCTV Hardware & Serial Numbers</h3>
                    <a href="{{ route('equipment.create', ['lead_id' => $lead->id, 'redirect_to' => url()->current()]) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Asset</span>
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