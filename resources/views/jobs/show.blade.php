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
                        <a href="{{ route('invoices.show', $job->invoice) }}" class="btn-secondary text-xs">
                            View Tax Invoice
                        </a>
                    @else
                        <a href="{{ route('invoices.create', $job) }}" class="btn-primary text-xs">
                            Generate Invoice
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
                            <td style="width: 25%; color: #555; font-weight: 500;">Work Order ID</td>
                            <td style="color: #222; font-weight: 500;">{{ $job->job_no }}</td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Customer Premise</td>
                            <td style="color: #222; font-weight: 500;">
                                <a href="{{ route('leads.show', $job->quotation->lead) }}" class="action-link">
                                    {{ $job->quotation->lead->customer_name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Linked Commercial Quote</td>
                            <td style="color: #222; font-weight: 500;">
                                <a href="{{ route('quotations.show', $job->quotation) }}" class="action-link">
                                    {{ $job->quotation->quotation_no }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Assigned Field Engineer</td>
                            <td style="color: #222; font-weight: 500;">
                                {{ $job->technician?->name ?? 'Unassigned' }}
                                @if($job->technician?->phone)
                                    <span class="text-slate-500 font-normal">({{ $job->technician->phone }})</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Scheduled Date</td>
                            <td style="color: #222; font-weight: 500;">
                                {{ $job->scheduled_date ? $job->scheduled_date->format('d M Y') : 'Pending Schedule' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Execution Phase</td>
                            <td style="color: #222; font-weight: 500;">
                                <span class="status-{{ strtolower($job->status) }}">
                                    {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                                </span>
                            </td>
                        </tr>
                        @if($job->quotation->lead->site_address)
                        <tr>
                            <td style="color: #555; font-weight: 500;">Site Installation Address</td>
                            <td style="color: #222; font-weight: 500;">{{ $job->quotation->lead->site_address }}</td>
                        </tr>
                        @endif
                        @if($job->installation_notes)
                        <tr>
                            <td style="color: #555; font-weight: 500;">Installation Notes</td>
                            <td style="color: #222; font-weight: 500;">{{ $job->installation_notes }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- 2. Deployed CCTV Hardware --}}
            <div class="crm-section">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-[#ddd]">
                    <h3 class="border-b-0 pb-0 mb-0">Deployed CCTV Hardware</h3>
                    <a href="{{ route('equipment.create', ['lead_id' => $job->quotation->lead_id, 'job_id' => $job->id, 'redirect_to' => url()->current()]) }}" class="btn-secondary text-xs">
                        Log Serial
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
                                            <div class="text-[10px] text-slate-500 font-mono">MAC: {{ $eq->mac_address }}</div>
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
                        <a href="{{ route('jobs.index') }}" class="btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn-primary">
                            Save
                        </button>
                    </div>
                </form>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>