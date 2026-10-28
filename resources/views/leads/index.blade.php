<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Leads">
            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                <x-slot name="actions">
                    <a href="{{ route('leads.create') }}" class="btn-primary">New Lead</a>
                </x-slot>
            @endif
        </x-page-header>
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

                {{-- Filter bar: search + status (applies straight away) --}}
                <x-filter-bar :action="route('leads.index')" placeholder="Search customer, phone, address…" :filters="['search', 'status']">
                    <x-filter-select name="status" label="Status" placeholder="All Statuses"
                                     :options="['new' => 'New', 'contacted' => 'Contacted', 'quoted' => 'Quoted', 'won' => 'Won', 'lost' => 'Lost']" />
                </x-filter-bar>

                {{-- Leads table (cards on phones) --}}
                <x-data-table class="leads-table" :columns="[
                    ['label' => 'Customer', 'style' => 'width: 38%; min-width: 260px;'],
                    ['label' => 'Phone', 'class' => 'col-phone', 'style' => 'min-width: 140px;'],
                    'Email',
                    ['label' => 'Source', 'style' => 'min-width: 130px; white-space: nowrap;'],
                    'Status',
                    ['label' => 'Added', 'class' => 'whitespace-nowrap'],
                    ['label' => 'Actions', 'class' => 'text-right whitespace-nowrap'],
                ]">
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
                                        <x-status-badge :status="$lead->status" />
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
                                                      data-confirm="Delete lead {{ $lead->customer_name }}?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="danger-link text-[var(--status-red)] hover:underline">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-table-empty :colspan="7">
                                    <x-empty-message empty="No leads yet" filtered="No leads match these filters." :filters="['search', 'status']" />
                                </x-table-empty>
                            @endforelse
                </x-data-table>

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