<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('jobs.index') }}" class="p-2 rounded-[4px] bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Installation Job #{{ $job->job_no }}
                        </h2>
                        <span class="text-xs font-bold uppercase
                            @if($job->status === 'completed') text-emerald-600 dark:text-emerald-400
                            @elseif($job->status === 'in_progress') text-amber-600 dark:text-amber-400
                            @elseif($job->status === 'assigned') text-blue-600 dark:text-blue-400
                            @elseif($job->status === 'cancelled') text-rose-600 dark:text-rose-400
                            @else text-slate-600 dark:text-slate-400 @endif">
                            ● {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1.5">
                        <span>Client:</span>
                        <a href="{{ route('leads.show', $job->quotation->lead) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-bold">
                            {{ $job->quotation->lead->customer_name }}
                        </a>
                        @if($job->quotation->lead->site_address)
                            <span>&middot; {{ $job->quotation->lead->site_address }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                @if ($job->status === 'completed')
                    @if ($job->invoice)
                        <a href="{{ route('invoices.show', $job->invoice) }}"
                           class="px-3.5 py-2 rounded-[4px] bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700 text-xs font-semibold transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>View Tax Invoice</span>
                        </a>
                    @else
                        <a href="{{ route('invoices.create', $job) }}"
                           class="px-4 py-2 rounded-[4px] bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            <span>Generate Invoice</span>
                        </a>
                    @endif
                @endif
                <a href="{{ route('jobs.index') }}"
                   class="px-3 py-2 rounded-[4px] bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium border border-slate-300 dark:border-slate-700 transition">
                    &larr; All Jobs
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-50 dark:bg-slate-950 min-h-screen text-slate-800 dark:text-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-3 rounded-[4px] bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2">
                    <span>✓ {{ session('status') }}</span>
                </div>
            @endif

            {{-- Job Summary & Telemetry Overview --}}
            <div class="bg-white dark:bg-slate-900 rounded-[4px] border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Job Execution Details</h3>
                    </div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">Job #{{ $job->job_no }}</span>
                </div>
                
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Work Order ID</div>
                        <div class="text-sm font-bold text-blue-600 dark:text-blue-400 mt-1 font-mono">{{ $job->job_no }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Customer Premise</div>
                        <div class="text-sm font-semibold text-slate-900 dark:text-white mt-1">
                            <a href="{{ route('leads.show', $job->quotation->lead) }}" class="hover:text-blue-600 transition">
                                {{ $job->quotation->lead->customer_name }}
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Linked Commercial Quote</div>
                        <div class="text-sm font-semibold text-blue-600 dark:text-blue-400 mt-1 font-mono">
                            <a href="{{ route('quotations.show', $job->quotation) }}" class="hover:underline">
                                {{ $job->quotation->quotation_no }}
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Assigned Field Engineer</div>
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mt-1">
                            {{ $job->technician?->name ?? 'Unassigned' }}
                            @if($job->technician?->phone)
                                <span class="text-xs text-slate-500 font-normal">({{ $job->technician->phone }})</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Scheduled Date</div>
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mt-1">
                            {{ $job->scheduled_date ? $job->scheduled_date->format('d M Y') : 'Pending Schedule' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Current Execution Phase</div>
                        <div class="mt-1">
                            <span class="text-xs font-bold uppercase
                                @if($job->status === 'completed') text-emerald-600 dark:text-emerald-400
                                @elseif($job->status === 'in_progress') text-amber-600 dark:text-amber-400
                                @elseif($job->status === 'assigned') text-blue-600 dark:text-blue-400
                                @else text-slate-600 dark:text-slate-400 @endif">
                                {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                            </span>
                        </div>
                    </div>
                    @if($job->quotation->lead->site_address)
                    <div class="sm:col-span-2 lg:col-span-3">
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Site Installation Address</div>
                        <div class="text-sm font-medium text-slate-700 dark:text-slate-300 mt-1">
                            {{ $job->quotation->lead->site_address }}
                        </div>
                    </div>
                    @endif
                </div>

                @if($job->installation_notes)
                    <div class="mx-6 mb-6 p-4 rounded-[4px] bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
                        <span class="font-bold text-slate-800 dark:text-slate-200 text-xs block mb-1">Installation Notes:</span>
                        {{ $job->installation_notes }}
                    </div>
                @endif
            </div>

            {{-- Deployed Hardware / Serial Numbers Card --}}
            <div class="bg-white dark:bg-slate-900 rounded-[4px] border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Deployed CCTV Hardware & Serial Register</h3>
                    </div>
                    <a href="{{ route('equipment.create', ['lead_id' => $job->quotation->lead_id, 'job_id' => $job->id, 'redirect_to' => url()->current()]) }}"
                       class="px-3 py-1.5 rounded-[4px] bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>Log Serial / Deploy</span>
                    </a>
                </div>

                @if($job->installedEquipment->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-6 py-3">Serial Number</th>
                                    <th class="px-6 py-3">Device / Model</th>
                                    <th class="px-6 py-3">Location Tag</th>
                                    <th class="px-6 py-3">Mfg Warranty</th>
                                    <th class="px-6 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                @foreach($job->installedEquipment as $eq)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                        <td class="px-6 py-3.5 font-mono font-bold text-blue-600 dark:text-blue-400">
                                            <a href="{{ route('equipment.show', $eq) }}" class="hover:underline">
                                                {{ $eq->serial_number }}
                                            </a>
                                            @if($eq->mac_address)
                                                <div class="text-[10px] text-slate-500 font-mono">MAC: {{ $eq->mac_address }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3.5">
                                            <div class="font-semibold text-slate-900 dark:text-white">{{ $eq->equipment_name }}</div>
                                            @if($eq->product)
                                                <div class="text-[11px] text-slate-500">{{ $eq->product->brand }} {{ $eq->product->model_no }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3.5">
                                            <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">
                                                {{ $eq->location_tag ?: 'Main Site' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3.5">
                                            @if($eq->mfg_warranty_status === 'active')
                                                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                                    ● Active ({{ $eq->mfg_days_remaining }}d)
                                                </span>
                                            @else
                                                <span class="text-xs font-bold text-rose-600 dark:text-rose-400">
                                                    {{ ucfirst($eq->mfg_warranty_status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3.5 text-right">
                                            <a href="{{ route('equipment.show', $eq) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-semibold">
                                                View Asset &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-slate-500 text-xs">
                        No equipment serial numbers logged for this installation job yet.
                    </div>
                @endif
            </div>

            {{-- Update Work Order Card (Admin Control) --}}
            @if(auth()->user()->isAdmin())
            <div class="bg-white dark:bg-slate-900 rounded-[4px] border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Update Work Order Status & Dispatch</h3>
                </div>
                
                <div class="p-6">
                    <form method="POST" action="{{ route('jobs.update', $job) }}" class="space-y-4 text-xs">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-medium text-slate-700 dark:text-slate-300 text-sm mb-1.5">Scheduled Date</label>
                                <input type="date" name="scheduled_date"
                                       value="{{ $job->scheduled_date?->format('Y-m-d') }}"
                                       class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-[4px] px-3 py-2 min-h-[38px] text-sm focus:border-blue-600 focus:outline-none">
                            </div>

                            <div>
                                <label class="block font-medium text-slate-700 dark:text-slate-300 text-sm mb-1.5">Assigned Technician</label>
                                <select name="assigned_technician_id" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-[4px] px-3 py-2 min-h-[38px] text-sm focus:border-blue-600 focus:outline-none">
                                    <option value="">Select Technician...</option>
                                    @foreach ($technicians as $tech)
                                        <option value="{{ $tech->id }}" @selected($job->assigned_technician_id == $tech->id)>
                                            {{ $tech->name }} ({{ $tech->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block font-medium text-slate-700 dark:text-slate-300 text-sm mb-1.5">Job Status</label>
                                <select name="status" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-[4px] px-3 py-2 min-h-[38px] text-sm focus:border-blue-600 focus:outline-none">
                                    @foreach (['pending','scheduled','assigned','in_progress','completed','cancelled'] as $s)
                                        <option value="{{ $s }}" @selected($job->status === $s)>
                                            {{ ucfirst(str_replace('_', ' ', $s)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 dark:text-slate-300 text-sm mb-1.5">Installation Notes</label>
                            <textarea name="installation_notes" rows="3"
                                      placeholder="Any notes about this installation…"
                                      class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-[4px] px-3 py-2 text-sm focus:border-blue-600 focus:outline-none">{{ $job->installation_notes }}</textarea>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="px-5 py-2.5 rounded-[4px] bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                                Save Work Order Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>