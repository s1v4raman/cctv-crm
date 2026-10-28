<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <a href="{{ route('jobs.index') }}" class="back-link">
                    &larr; Back
                </a>
                <div class="flex items-center gap-3 mt-1">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                        Installation Job #{{ $job->job_no }}
                    </h2>
                    @php
                        $jStatusColor = '#2563eb';
                        if ($job->status === 'completed') $jStatusColor = '#16a34a';
                        elseif ($job->status === 'cancelled') $jStatusColor = '#dc2626';
                    @endphp
                    <span class="text-sm font-bold" style="color: {{ $jStatusColor }};">
                        {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                @if ($job->status === 'completed')
                    @if ($job->invoice)
                        <a href="{{ route('invoices.show', $job->invoice) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>View Tax Invoice</span>
                        </a>
                    @else
                        <a href="{{ route('invoices.create', $job) }}" class="btn-primary text-xs inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Generate Invoice</span>
                        </a>
                    @endif
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

            {{-- 1. Job Summary: Two-column table inside standard section --}}
            <div class="crm-section">
                <h3>Job Execution Details</h3>
                <table class="crm-table w-full">
                    <tbody>
                        <tr>
                            <td style="width: 25%; color: var(--text-muted); font-weight: 500;">Work Order ID</td>
                            <td style="color: var(--text-main); font-weight: 500;">{{ $job->job_no }}</td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Customer Premise</td>
                            <td style="color: var(--text-main); font-weight: 500;">
                                <a href="{{ route('leads.show', $job->quotation->lead) }}" class="action-link">
                                    {{ $job->quotation->lead->customer_name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Linked Commercial Quote</td>
                            <td style="color: var(--text-main); font-weight: 500;">
                                <a href="{{ route('quotations.show', $job->quotation) }}" class="action-link">
                                    {{ $job->quotation->quotation_no }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Assigned Field Engineer</td>
                            <td style="color: var(--text-main); font-weight: 500;">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span>{{ $job->technician?->name ?? 'Unassigned' }}</span>
                                    @if($job->technician?->phone)
                                        <a href="tel:{{ $job->technician->phone }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition" title="Call Technician">
                                            <svg class="w-3 h-3 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <span>{{ $job->technician->phone }}</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Scheduled Date</td>
                            <td style="color: var(--text-main); font-weight: 500;">
                                {{ $job->scheduled_date ? $job->scheduled_date->format('d M Y') : 'Pending Schedule' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Execution Phase</td>
                            <td style="color: var(--text-main); font-weight: 500;">
                                <span class="status-{{ strtolower($job->status) }}">
                                    {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                                </span>
                            </td>
                        </tr>
                        @if($job->quotation->lead->site_address)
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Site Installation Address</td>
                            <td style="color: var(--text-main); font-weight: 500;">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <span>{{ $job->quotation->lead->site_address }}</span>
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($job->quotation->lead->site_address) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition" title="Open in Maps">
                                        <svg class="w-3 h-3 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>Map</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @if($job->installation_notes)
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 500;">Installation Notes</td>
                            <td style="color: var(--text-main); font-weight: 500;">{{ $job->installation_notes }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- 2. Deployed CCTV Hardware --}}
            <div class="crm-section">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-[var(--card-border)] dark:border-slate-700">
                    <h3 class="border-none pb-0 mb-0" style="border:none !important; border-bottom:none !important; margin-bottom:0 !important; padding-bottom:0 !important;">Deployed CCTV Hardware</h3>
                    <a href="{{ route('equipment.create', ['lead_id' => $job->quotation->lead_id, 'job_id' => $job->id, 'redirect_to' => url()->current()]) }}" class="btn-secondary text-xs inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>Log Serial</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="crm-table w-full">
                        <thead>
                            <tr>
                                <th>Serial Number</th>
                                <th>Device / Model</th>
                                <th>Location Tag</th>
                                <th>Mfg Warranty</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($job->installedEquipment as $eq)
                                <tr>
                                    <td class="font-semibold text-slate-900 font-mono" style="white-space: nowrap;">
                                        <a href="{{ route('equipment.show', $eq) }}" class="action-link">
                                            {{ $eq->serial_number }}
                                        </a>
                                        @if($eq->mac_address)
                                            <div class="text-[11px] text-slate-500 font-mono">MAC: {{ $eq->mac_address }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-semibold text-slate-900">{{ $eq->equipment_name }}</div>
                                        @if($eq->product)
                                            <div class="text-[11px] text-slate-500">{{ $eq->product->brand }} {{ $eq->product->model_no }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $eq->location_tag ?: 'Main Site' }}</td>
                                    <td style="white-space: nowrap;">
                                        <span class="status-{{ $eq->mfg_warranty_status === 'active' ? 'won' : 'lost' }}">
                                            {{ ucfirst($eq->mfg_warranty_status) }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('equipment.show', $eq) }}" class="action-link">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-6 text-slate-500 text-sm">
                                        No equipment logged yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 3. Update Work Order Form (Admin Control) --}}
            @if(auth()->user()->isAdmin())
            <div class="crm-section">
                <h3>Update Work Order</h3>
                
                <form method="POST" action="{{ route('jobs.update', $job) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="crm-label">Scheduled Date</label>
                            <input type="date" name="scheduled_date"
                                   value="{{ $job->scheduled_date?->format('Y-m-d') }}"
                                   class="crm-input w-full">
                        </div>

                        <div>
                            <label class="crm-label">Assigned Technician</label>
                            <select name="assigned_technician_id" class="crm-select w-full">
                                <option value="">Select Technician...</option>
                                @foreach ($technicians as $tech)
                                    <option value="{{ $tech->id }}" @selected($job->assigned_technician_id == $tech->id)>
                                        {{ $tech->name }} ({{ $tech->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="crm-label">Job Status</label>
                            <select name="status" class="crm-select w-full">
                                @foreach (['pending','scheduled','assigned','in_progress','completed','cancelled'] as $s)
                                    <option value="{{ $s }}" @selected($job->status === $s)>
                                        {{ ucfirst(str_replace('_', ' ', $s)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="crm-label">Installation Notes</label>
                        <textarea name="installation_notes" rows="3"
                                  placeholder="e.g. Completed camera mounting in warehouse bay 2"
                                  class="w-full bg-white border border-[#ccc] rounded px-3 py-2 text-sm focus:border-blue-600 focus:outline-none">{{ $job->installation_notes }}</textarea>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('jobs.index') }}" class="btn-secondary inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Cancel</span>
                        </a>
                        <button type="submit" class="btn-primary inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" stroke="#000000" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Save</span>
                        </button>
                    </div>
                </form>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>