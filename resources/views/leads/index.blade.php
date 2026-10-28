<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-[var(--text-main)]">Leads</h2>
            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('leads.create') }}" class="btn-primary">
                New Lead
            </a>
            @endif
        </div>
    </x-slot>

    <style>
        .leads-table {
            width: 100%;
            border-collapse: collapse !important;
            background: var(--card-bg) !important;
            border: none !important;
        }
        .leads-table thead tr {
            background-color: var(--table-header-bg) !important;
            border-top: none !important;
            border-bottom: 1px solid var(--table-border) !important;
        }
        .leads-table thead th {
            padding: 0.65rem 1rem;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-main) !important;
            text-align: left;
            border-top: none !important;
            border-bottom: 1px solid var(--table-border) !important;
        }
        .leads-table tbody tr {
            background-color: var(--card-bg) !important;
            border-bottom: 1px solid var(--table-border) !important;
            transition: background-color 0.1s;
        }
        .leads-table tbody tr:last-child,
        .leads-table tbody tr:last-child td {
            border-bottom: none !important;
        }
        .leads-table th:not(:last-child),
        .leads-table td:not(:last-child) {
            border-right: 1px solid var(--table-border) !important;
        }
        .leads-table th:first-child,
        .leads-table td:first-child {
            border-left: none !important;
        }
        .leads-table th:last-child,
        .leads-table td:last-child {
            border-right: none !important;
        }
        .leads-table tbody tr:hover {
            background-color: var(--page-bg) !important;
        }
        .leads-table tbody td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            font-size: 0.8rem;
            color: var(--text-main);
            border-bottom: 1px solid var(--table-border) !important;
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

            <div class="bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl overflow-hidden shadow-xs">

                {{-- Horizontal Filter bar - Medium 38px Height Level --}}
                <form method="GET" action="{{ route('leads.index') }}" class="p-2.5 border-b border-[var(--card-border)] bg-[var(--card-bg)] flex items-center gap-2.5 flex-wrap text-xs">
                    <div class="relative w-72 min-w-[220px] flex items-center shrink-0">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" id="lead-search" value="{{ request('search') }}" 
                                placeholder="Search customer, phone, address…" 
                                style="height: var(--control-height) !important; min-height: var(--control-height) !important; max-height: var(--control-height) !important; border-radius: var(--radius-control) !important; box-sizing: border-box; padding-left: 36px !important;"
                                class="crm-input w-full text-xs border border-[var(--input-border)] bg-[var(--card-bg)] text-[var(--text-main)] placeholder-[var(--text-muted)] focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-500/30">
                    </div>

                    <select name="status" id="lead-status-filter" 
                            style="height: var(--control-height) !important; min-height: var(--control-height) !important; max-height: var(--control-height) !important; border-radius: var(--radius-control) !important; box-sizing: border-box; min-width: 140px;"
                            class="crm-select text-xs border border-[var(--input-border)] bg-[var(--card-bg)] text-[var(--text-main)] focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-500/30 shrink-0">
                        <option value="">All Statuses</option>
                        <option value="new" @selected(request('status') === 'new')>New</option>
                        <option value="contacted" @selected(request('status') === 'contacted')>Contacted</option>
                        <option value="quoted" @selected(request('status') === 'quoted')>Quoted</option>
                        <option value="won" @selected(request('status') === 'won')>Won</option>
                        <option value="lost" @selected(request('status') === 'lost')>Lost</option>
                    </select>

                    <button type="submit" 
                            style="height: var(--control-height) !important; min-height: var(--control-height) !important; max-height: var(--control-height) !important; border-radius: var(--radius-control) !important; box-sizing: border-box;"
                            class="crm-btn-primary px-4 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition shrink-0 inline-flex items-center justify-center shadow-xs cursor-pointer">
                        Filter
                    </button>

                    @if(request('search') || request('status'))
                        <a href="{{ route('leads.index') }}" 
                           style="height: var(--control-height) !important; min-height: var(--control-height) !important; max-height: var(--control-height) !important; border-radius: var(--radius-control) !important; box-sizing: border-box;"
                           class="crm-btn-secondary px-3.5 text-xs font-medium border border-[var(--button-border)] bg-[var(--card-bg)] text-[var(--text-main)] hover:bg-[var(--page-bg)] transition inline-flex items-center shrink-0">
                            Reset
                        </a>
                    @endif
                </form>

                {{-- Table with proper theme variables and 1px borders --}}
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
                                <tr>
                                    {{-- Customer name --}}
                                    <td>
                                        <div>
                                            <a href="{{ route('leads.show', $lead) }}" class="font-bold text-[var(--text-main)] hover:underline">
                                                {{ $lead->customer_name }}
                                            </a>
                                        </div>
                                        @if($lead->site_address)
                                            <div class="text-xs text-[var(--text-muted)] mt-0.5">
                                                {{ $lead->site_address }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Phone with whitespace-nowrap --}}
                                    <td class="col-phone text-[var(--text-main)]">
                                        {{ $lead->phone }}
                                    </td>

                                    <td class="text-[var(--text-muted)]">
                                        {{ $lead->email ?? '—' }}
                                    </td>

                                    <td class="text-[var(--text-main)] capitalize">
                                        {{ $lead->source ?: '—' }}
                                    </td>

                                    {{-- Status: shared status pill (tone comes from status-{name}) --}}
                                    <td>
                                        <span class="status-badge status-{{ strtolower($lead->status) }}">
                                            {{ ucfirst(strtolower($lead->status)) }}
                                        </span>
                                    </td>

                                    <td class="text-[var(--text-muted)] whitespace-nowrap">
                                        {{ $lead->created_at->format('d M Y') }}
                                    </td>

                                    {{-- Actions: View, Edit, Delete separated by a space --}}
                                    <td class="text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-2 justify-end">
                                            <a href="{{ route('leads.show', $lead) }}" class="action-link text-[var(--crm-accent)] hover:underline">View</a>
                                            @if(auth()->user()->isAdmin())
                                                <a href="{{ route('leads.edit', $lead) }}" class="action-link text-[var(--crm-accent)] hover:underline">Edit</a>
                                                <form method="POST" action="{{ route('leads.destroy', $lead) }}"
                                                      class="inline"
                                                      onsubmit="return confirm('Delete lead {{ addslashes($lead->customer_name) }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="danger-link text-[var(--status-red)] hover:underline">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-6 text-[var(--text-muted)]">
                                        No leads yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Total count shown below the table --}}
                <div class="px-4 py-2.5 border-t border-[var(--table-border)] bg-[var(--table-header-bg)] flex items-center justify-between text-xs text-[var(--text-muted)]">
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