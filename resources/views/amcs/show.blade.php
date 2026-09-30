<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $amc->contract_no }}</h2>
                <p class="mt-1 text-sm text-gray-500">Customer: <strong class="text-gray-800">{{ $amc->lead->customer_name }}</strong></p>
            </div>
            <div class="flex items-center gap-3">
                <span class="badge badge-{{ $amc->status }}">
                    Status: {{ ucfirst($amc->status) }}
                </span>
                <a href="{{ route('amcs.index') }}" class="btn-head-secondary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Contracts
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .pg-wrap  { background:#f1f5f9; min-height:100vh; padding:1.75rem 0 3rem; }
        .pg-inner { max-width:1080px; margin:0 auto; padding:0 1.25rem; }

        .btn-head-secondary {
            display: inline-flex; align-items: center;
            background-color: #ffffff; color: #334155 !important;
            padding: 0.5rem 1rem; border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 600; text-decoration: none;
            border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.15s ease-in-out;
        }
        .btn-head-secondary:hover { background-color: #f8fafc; color: #0f172a !important; border-color: #94a3b8; }

        .pg-card {
            background:#fff;
            border-radius:1rem;
            border:1px solid rgba(99,102,241,.08);
            box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(99,102,241,.06);
            overflow:hidden;
            margin-bottom:1.75rem;
        }

        .section-head {
            display:flex; align-items:center; justify-content:space-between;
            padding:1.15rem 1.5rem; border-bottom:1px solid #f1f5f9;
        }
        .section-head h3 { font-size:1rem; font-weight:700; color:#0f172a; }

        /* Info Grid */
        .info-grid {
            display:grid; grid-template-columns:repeat(2,1fr);
            gap:1.5rem; padding:1.5rem;
        }
        @media(min-width:640px){ .info-grid{grid-template-columns:repeat(3,1fr);} }
        .info-label {
            font-size:.72rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.06em; color:#64748b; margin-bottom:.35rem;
        }
        .info-value { font-size:.95rem; font-weight:700; color:#0f172a; }
        .info-link { color:#4f46e5; text-decoration:none; }
        .info-link:hover { text-decoration:underline; }

        html.dark .info-value { color: #f8fafc !important; }
        html.dark .info-label { color: #94a3b8 !important; }
        html.dark .info-link { color: #818cf8 !important; }
        html.dark .section-head { border-bottom-color: #1e293b; }
        html.dark .section-head h3 { color: #f8fafc; }

        /* Status Badges */
        .badge {
            display:inline-flex; padding:.25rem .75rem; border-radius:9999px;
            font-size:.72rem; font-weight:700;
        }
        .badge-pending   { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
        .badge-active    { background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; }
        .badge-expired   { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
        .badge-cancelled { background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }
        .badge-completed { background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; }

        /* Table */
        .visits-table { width:100%; border-collapse:collapse; }
        .visits-table thead { background:#f8fafc; border-bottom:1px solid #e2e8f0; }
        .visits-table thead th {
            padding:.75rem 1.25rem; font-size:.67rem; font-weight:700;
            text-transform:uppercase; letter-spacing:.06em; color:#64748b; text-align:left;
        }
        .visits-table thead th.right { text-align:right; }
        .visits-table tbody tr { border-bottom:1px solid #f1f5f9; transition:background .1s; }
        .visits-table tbody tr:hover { background:#f8fafc; }
        .visits-table tbody td { padding:.85rem 1.25rem; font-size:.85rem; color:#374151; vertical-align:middle; }
        .visits-table tbody td.right { text-align:right; }

        /* Buttons */
        .btn-assign {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: .4rem .85rem; border-radius: .45rem;
            background-color: #4f46e5; color: #ffffff !important;
            font-size: .78rem; font-weight: 700; border: 1px solid #4338ca;
            cursor: pointer; transition: all .15s;
        }
        .btn-assign:hover { background-color: #4338ca; }

        .btn-complete {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: .4rem .85rem; border-radius: .45rem;
            background-color: #16a34a; color: #ffffff !important;
            font-size: .78rem; font-weight: 700; border: 1px solid #15803d;
            cursor: pointer; transition: all .15s;
        }
        .btn-complete:hover { background-color: #15803d; }

        .btn-danger {
            display: inline-flex; align-items: center;
            padding: .5rem 1rem; border-radius: .5rem;
            background-color: #fef2f2; color: #dc2626 !important;
            border: 1px solid #fca5a5; font-size: .82rem; font-weight: 700;
            cursor: pointer; transition: all .15s;
        }
        .btn-danger:hover { background-color: #dc2626; color: #ffffff !important; }

        .btn-log-ticket {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.4rem 0.85rem; border-radius: 0.5rem;
            font-size: 0.78rem; font-weight: 700;
            background-color: #fef2f2; color: #dc2626 !important;
            border: 1px solid #fca5a5; text-decoration: none;
            transition: all .15s;
        }
        .btn-log-ticket:hover { background-color: #dc2626; color: #ffffff !important; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if (session('status'))
                <div class="mb-4 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Contract Card --}}
            <div class="pg-card">
                <div class="section-head">
                    <h3>AMC Contract Information</h3>
                    @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('amcs.destroy', $amc) }}"
                              onsubmit="return confirm('Are you sure you want to delete this AMC contract? This action will permanently remove all servicing visit records.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete Contract
                            </button>
                        </form>
                    @endif
                </div>
                <div class="info-grid">
                    <div>
                        <div class="info-label">Contract Number</div>
                        <div class="info-value font-mono text-indigo-600 dark:text-indigo-400">{{ $amc->contract_no }}</div>
                    </div>
                    <div>
                        <div class="info-label">Servicing Period</div>
                        <div class="info-value">{{ $amc->start_date->format('d M Y') }} &rarr; {{ $amc->end_date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="info-label">Annual Value</div>
                        <div class="info-value text-emerald-600 dark:text-emerald-400">₹{{ number_format($amc->value, 2) }}</div>
                    </div>
                    <div>
                        <div class="info-label">Servicing Frequency</div>
                        <div class="info-value capitalize">{{ str_replace('_', ' ', $amc->frequency) }}</div>
                    </div>
                    <div>
                        <div class="info-label">Customer Site Link</div>
                        <div class="info-value">
                            <a href="{{ route('leads.show', $amc->lead) }}" class="info-link">
                                {{ $amc->lead->customer_name }}
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="info-label">Quick Status Update</div>
                        <div class="info-value mt-1">
                            <form method="POST" action="{{ route('amcs.updateStatus', $amc) }}">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="border border-gray-300 dark:border-slate-700 rounded-lg px-2.5 py-1 text-xs font-bold text-gray-800 dark:text-slate-100 bg-gray-50 dark:bg-slate-800 focus:bg-white dark:focus:bg-slate-700" onchange="this.form.submit()">
                                    <option value="pending" @selected($amc->status === 'pending')>Pending</option>
                                    <option value="active" @selected($amc->status === 'active')>Active</option>
                                    <option value="expired" @selected($amc->status === 'expired')>Expired</option>
                                    <option value="cancelled" @selected($amc->status === 'cancelled')>Cancelled</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    @if($amc->notes)
                        <div class="col-span-2 sm:col-span-3">
                            <div class="info-label">Contract Scope & Equipment Coverage</div>
                            <div class="info-value text-sm font-normal text-gray-700 dark:text-slate-300 whitespace-pre-line">{{ $amc->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Scheduled Visits Timeline --}}
            <div class="pg-card">
                <div class="section-head">
                    <div>
                        <h3>Preventive Servicing Schedule ({{ $amc->visits->count() }} Visits)</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Automated maintenance routine generated for this contract period</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="visits-table">
                        <thead>
                            <tr>
                                <th>Scheduled Date</th>
                                <th>Assigned Technician</th>
                                <th>Servicing Report / Execution</th>
                                <th>Status</th>
                                <th class="right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($amc->visits as $visit)
                                <tr>
                                    <td class="font-bold text-gray-900">
                                        {{ $visit->scheduled_date->format('d M Y') }}
                                    </td>
                                    <td>
                                        @if($visit->status === 'completed')
                                            <span class="font-bold text-gray-800">👤 {{ $visit->assignedTechnician->name ?? 'Technician' }}</span>
                                        @else
                                            <form method="POST" action="{{ route('amc-visits.assign', $visit) }}" class="flex items-center gap-1.5">
                                                @csrf
                                                <select name="assigned_technician_id" class="border border-gray-300 rounded-md px-2 py-1 text-xs text-gray-800 bg-white" required>
                                                    <option value="">-- Select Tech --</option>
                                                    @foreach($technicians as $tech)
                                                        <option value="{{ $tech->id }}" @selected($visit->assigned_technician_id == $tech->id)>
                                                            {{ $tech->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn-assign">Assign</button>
                                            </form>
                                        @endif
                                    </td>
                                    <td>
                                        @if($visit->status === 'completed')
                                            <div>
                                                <div class="text-xs font-semibold text-gray-500">Done on: {{ $visit->completed_at?->format('d M Y, h:i A') }}</div>
                                                <div class="text-xs text-gray-800 mt-1 font-medium italic bg-slate-50 p-2 rounded border border-slate-100">"{{ $visit->completion_notes }}"</div>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Awaiting routine visit...</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $visit->status }}">
                                            {{ ucfirst($visit->status) }}
                                        </span>
                                    </td>
                                    <td class="right">
                                        @if($visit->status !== 'completed')
                                            <form method="POST" action="{{ route('amc-visits.complete', $visit) }}" class="inline-flex items-center gap-1.5">
                                                @csrf
                                                <input type="text" name="completion_notes" placeholder="Camera cleaning, NVR test..." required
                                                       class="border border-gray-300 rounded-md px-2.5 py-1 text-xs text-gray-800 w-44">
                                                <button type="submit" class="btn-complete">
                                                    ✓ Mark Done
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                                                ✓ Completed
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-gray-400 text-sm">
                                        No scheduled servicing visits found for this contract.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Associated Service / Breakdown Tickets --}}
            <div class="pg-card">
                <div class="section-head">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🚨</span>
                        <div>
                            <h3>Breakdown & Repair Tickets under AMC</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Reported equipment defects and emergency callouts</p>
                        </div>
                    </div>
                    <a href="{{ route('service-tickets.create', ['lead_id' => $amc->lead_id, 'amc_contract_id' => $amc->id]) }}" class="btn-log-ticket">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Log Breakdown Ticket
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="visits-table">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Issue Summary</th>
                                <th>Priority</th>
                                <th>Technician</th>
                                <th>Status</th>
                                <th class="right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($amc->serviceTickets as $ticket)
                                <tr>
                                    <td>
                                        <a href="{{ route('service-tickets.show', $ticket) }}" class="font-mono font-bold text-indigo-600 hover:underline">
                                            {{ $ticket->ticket_no }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="font-bold text-sm text-gray-900">{{ $ticket->title }}</div>
                                        <div class="text-xs text-gray-500">{{ $ticket->issue_type_label }}</div>
                                    </td>
                                    <td>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold capitalize
                                            @if($ticket->priority === 'critical') bg-rose-100 text-rose-800 border border-rose-200
                                            @elseif($ticket->priority === 'high') bg-amber-100 text-amber-800 border border-amber-200
                                            @elseif($ticket->priority === 'medium') bg-yellow-100 text-yellow-800 border border-yellow-200
                                            @else bg-slate-100 text-slate-700 border border-slate-200 @endif">
                                            {{ $ticket->priority }}
                                        </span>
                                    </td>
                                    <td class="font-semibold text-gray-800">{{ $ticket->assignedTechnician->name ?? 'Unassigned' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $ticket->status }}">
                                            {{ $ticket->status_label }}
                                        </span>
                                    </td>
                                    <td class="right">
                                        <a href="{{ route('service-tickets.show', $ticket) }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                            View Ticket &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-gray-400 text-sm">
                                        No breakdown or repair tickets filed under this AMC contract.
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
