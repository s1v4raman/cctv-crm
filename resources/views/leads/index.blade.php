<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Leads</h2>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('leads.create') }}" 
               class="inline-flex items-center px-3.5 py-1.5 rounded text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition shadow-2xs">
                + New Lead
            </a>
            @endif
        </div>
    </x-slot>

    <style>
        .leads-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
        }
        .dark .leads-table {
            background: #0f172a;
        }
        .leads-table thead tr {
            background-color: #f0f0f0 !important;
            border-bottom: 1px solid #ddd !important;
        }
        .dark .leads-table thead tr {
            background-color: #1e293b !important;
            border-bottom-color: #334155 !important;
        }
        .leads-table thead th {
            padding: 0.65rem 1rem;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #475569;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .dark .leads-table thead th {
            color: #94a3b8;
            border-bottom-color: #334155;
        }
        .leads-table tbody tr {
            background-color: #ffffff !important;
            border-bottom: 1px solid #ddd !important;
            transition: background-color 0.1s;
        }
        .dark .leads-table tbody tr {
            background-color: #0f172a !important;
            border-bottom-color: #334155 !important;
        }
        .leads-table tbody tr:hover {
            background-color: #f9fafb !important;
        }
        .dark .leads-table tbody tr:hover {
            background-color: #1e293b !important;
        }
        .leads-table tbody td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            font-size: 0.8rem;
        }
        .col-phone {
            white-space: nowrap !important;
        }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner space-y-4">

            @if (session('status'))
                <div class="p-3 rounded border border-emerald-300 bg-emerald-50 text-emerald-800 text-xs">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-3 rounded border border-rose-300 bg-rose-50 text-rose-800 text-xs">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="bg-white dark:bg-[#0f172a] border border-[#ddd] dark:border-slate-800 rounded-[4px] overflow-hidden">

                {{-- Filter bar in one simple row: Search box, Status dropdown, Search button --}}
                <form method="GET" action="{{ route('leads.index') }}" class="p-3 border-b border-[#ddd] dark:border-slate-800 bg-white dark:bg-[#0f172a] flex items-center gap-2 flex-wrap">
                    <div class="relative flex-1 min-w-[220px] sm:max-w-xs">
                        <input type="text" name="search" id="lead-search" value="{{ request('search') }}" 
                               placeholder="Search customer, phone, address…" 
                               style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                               class="crm-input w-full px-3 text-xs border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-slate-400">
                    </div>

                    <select name="status" id="lead-status-filter" 
                            style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                            class="crm-select px-2.5 text-xs border border-[#ddd] dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:border-slate-400">
                        <option value="">All Statuses</option>
                        <option value="new" @selected(request('status') === 'new')>New</option>
                        <option value="contacted" @selected(request('status') === 'contacted')>Contacted</option>
                        <option value="quoted" @selected(request('status') === 'quoted')>Quoted</option>
                        <option value="won" @selected(request('status') === 'won')>Won</option>
                        <option value="lost" @selected(request('status') === 'lost')>Lost</option>
                    </select>

                    <button type="submit" 
                            style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                            class="crm-btn-primary px-3.5 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition">
                        Search
                    </button>

                    @if(request('search') || request('status'))
                        <a href="{{ route('leads.index') }}" 
                           style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                           class="crm-btn-secondary px-2.5 text-xs font-medium border border-[#ddd] dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition inline-flex items-center">
                            Reset
                        </a>
                    @endif
                </form>

                {{-- Table with white background, #f0f0f0 header row, 1px solid #ddd borders --}}
                <div class="overflow-x-auto">
                    <table class="leads-table">
                        <thead>
                            <tr>
                                <th style="width: 38%; min-width: 260px;">Customer</th>
                                <th class="col-phone" style="min-width: 140px;">Phone</th>
                                <th>Email</th>
                                <th style="min-width: 130px; white-space: nowrap;">Source</th>
                                <th>Status</th>
                                <th class="whitespace-nowrap">Added</th>
                                <th class="text-right whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($leads as $lead)
                                @php
                                    $statusMap = [
                                        'won' => 'text-emerald-600 dark:text-emerald-400',
                                        'quoted' => 'text-amber-600 dark:text-amber-400',
                                        'new' => 'text-blue-600 dark:text-blue-400',
                                        'contacted' => 'text-purple-600 dark:text-purple-400',
                                        'lost' => 'text-rose-600 dark:text-rose-400',
                                    ];
                                    $statusColor = $statusMap[strtolower($lead->status)] ?? 'text-slate-700 dark:text-slate-300';
                                @endphp
                                <tr>
                                    {{-- Customer name (dark text) with address in smaller gray text below --}}
                                    <td>
                                        <div>
                                            <a href="{{ route('leads.show', $lead) }}" 
                                               class="font-bold text-slate-900 dark:text-white hover:underline text-xs">
                                                {{ $lead->customer_name }}
                                            </a>
                                        </div>
                                        @if($lead->site_address)
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                {{ $lead->site_address }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Phone with whitespace-nowrap to prevent line breaks in numbers --}}
                                    <td class="col-phone text-slate-700 dark:text-slate-300">
                                        {{ $lead->phone }}
                                    </td>

                                    <td class="text-slate-600 dark:text-slate-400">
                                        {{ $lead->email ?? '—' }}
                                    </td>

                                    {{-- Source: Plain text with min-width 130px and no wrapping --}}
                                    <td style="min-width: 130px; white-space: nowrap;" class="text-slate-700 dark:text-slate-300 capitalize text-xs">
                                        {{ $lead->source ?: '—' }}
                                    </td>

                                    {{-- Status: Plain colored normal-case text, no pills --}}
                                    <td>
                                        <span class="font-bold text-xs {{ $statusColor }}">
                                            {{ ucfirst(strtolower($lead->status)) }}
                                        </span>
                                    </td>

                                    <td class="text-slate-500 dark:text-slate-400 whitespace-nowrap text-xs">
                                        {{ $lead->created_at->format('d M Y') }}
                                    </td>

                                    {{-- Actions: View | Edit | Delete --}}
                                    <td class="text-right whitespace-nowrap text-sm">
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            <a href="{{ route('leads.show', $lead) }}" class="text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:underline">View</a>
                                            @if(auth()->user()->isAdmin())
                                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                                <a href="{{ route('leads.edit', $lead) }}" class="text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:underline">Edit</a>
                                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                                <form method="POST" action="{{ route('leads.destroy', $lead) }}"
                                                      class="inline"
                                                      onsubmit="return confirm('Delete lead {{ addslashes($lead->customer_name) }}? This will also delete all quotations and jobs linked to this lead.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-700 dark:text-red-500 hover:underline cursor-pointer bg-transparent border-0 p-0 font-medium">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-xs text-slate-400">
                                        No leads found.
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('leads.create') }}" class="text-blue-600 font-semibold hover:underline ml-1">Create the first lead &rarr;</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Total count shown below the table --}}
                <div class="px-4 py-2.5 border-t border-[#ddd] dark:border-slate-800 bg-[#fbfbfb] dark:bg-slate-900/60 flex items-center justify-between text-xs text-slate-500">
                    <span class="font-medium">Total: {{ $leads->total() }}</span>
                    @if($leads->hasPages())
                        <div class="text-xs">
                            {{ $leads->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>