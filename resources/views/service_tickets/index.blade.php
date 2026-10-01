<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Support &amp; Service Desk</h2>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-rose-500/10 text-rose-600 dark:text-rose-400 px-2 py-0.5 rounded border border-rose-200 dark:border-rose-500/30">SLA 24/7</span>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Manage client camera issues, warranty repairs, technician dispatches, and SLA timers</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" @click="$dispatch('open-email-modal')" class="btn-dark">
                    <svg class="w-4 h-4 mr-1.5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Email Gateway
                </button>
                <a href="{{ route('service-tickets.export-pdf', request()->query()) }}" class="btn-dark">
                    <svg class="w-4 h-4 mr-1.5 text-rose-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    PDF Export
                </a>
                <a href="{{ route('service-tickets.create') }}" class="btn-amber">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Log Ticket
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        /* Status Tabs (override global for amber active style) */
        .status-tab.active { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important; color: #020617 !important; border-color: #f59e0b !important; font-weight: 800; }
        .status-tab.active .count-badge { background: rgba(0,0,0,0.25) !important; color: #020617 !important; font-weight: 800; }

        /* Ticket-specific cell colors */
        .cell-no { font-weight:700; color:#2563eb; text-decoration:none; }
        .cell-no:hover { color:#1d4ed8; text-decoration:underline; }
        html.dark .cell-no { color:#f59e0b; }
        html.dark .cell-no:hover { color:#fbbf24; }
        .cell-name { font-weight:700; color:#0f172a; }
        html.dark .cell-name { color:#ffffff; }

        /* Alert messages */
        .alert-success {
            background-color: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #059669;
            padding: 0.875rem 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
            font-weight: 600;
        }
        html.dark .alert-success {
            background-color: rgba(16,185,129,.1);
            border-color: rgba(16,185,129,.3);
            color: #34d399;
        }

        /* Priority badges */
        .badge-priority { display:inline-flex; align-items:center; gap:0.35rem; padding:.2rem .6rem; border-radius:999px; font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; }
        .priority-critical { background:#fef2f2; color:#dc2626; border:1px solid #fee2e2; }
        .priority-high     { background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }
        .priority-medium   { background:#fffbeb; color:#d97706; border:1px solid #fde68a; }
        .priority-low      { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
        html.dark .priority-critical { background:rgba(244,63,94,.2); color:#fb7185; border-color:rgba(244,63,94,.4); }
        html.dark .priority-high     { background:rgba(249,115,22,.2); color:#fb923c; border-color:rgba(249,115,22,.4); }
        html.dark .priority-medium   { background:rgba(245,158,11,.2); color:#fbbf24; border-color:rgba(245,158,11,.4); }
        html.dark .priority-low      { background:rgba(148,163,184,.2); color:#cbd5e1; border-color:rgba(148,163,184,.4); }

        /* Status badges */
        .badge-status { display:inline-flex; padding:.2rem .65rem; border-radius:999px; font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; }
        .status-open       { background:#fef2f2; color:#dc2626; border:1px solid #fee2e2; }
        .status-assigned   { background:#faf5ff; color:#7e22ce; border:1px solid #f3e8ff; }
        .status-in_progress{ background:#fffbeb; color:#d97706; border:1px solid #fde68a; }
        .status-resolved   { background:#ecfdf5; color:#059669; border:1px solid #d1fae5; }
        .status-closed     { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
        .status-cancelled  { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
        html.dark .status-open        { background:rgba(244,63,94,.15); color:#fb7185; border-color:rgba(244,63,94,.3); }
        html.dark .status-assigned    { background:rgba(168,85,247,.15); color:#c084fc; border-color:rgba(168,85,247,.3); }
        html.dark .status-in_progress { background:rgba(245,158,11,.15); color:#fbbf24; border-color:rgba(245,158,11,.3); }
        html.dark .status-resolved    { background:rgba(16,185,129,.15); color:#34d399; border-color:rgba(16,185,129,.3); }
        html.dark .status-closed      { background:rgba(148,163,184,.15); color:#cbd5e1; border-color:rgba(148,163,184,.3); }
        html.dark .status-cancelled   { background:rgba(148,163,184,.15); color:#cbd5e1; border-color:rgba(148,163,184,.3); }
    </style>

    <div class="pg-wrap" x-data="{ emailModalOpen: false }" @open-email-modal.window="emailModalOpen = true">
        <div class="pg-inner">
            
            @if (session('success'))
                <div class="alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Metric Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="stat-card">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Tickets</p>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $metrics['total'] }}</h3>
                    </div>
                    <div class="p-3 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Active / Open</p>
                        <h3 class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $metrics['open'] }}</h3>
                    </div>
                    <div class="p-3 bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Critical / Urgent</p>
                        <h3 class="text-2xl font-bold text-red-600 dark:text-rose-400 mt-1">{{ $metrics['critical'] }}</h3>
                    </div>
                    <div class="p-3 bg-red-50 dark:bg-rose-950/50 text-red-600 dark:text-rose-400 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">SLA Overdue (>48h)</p>
                        <h3 class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">{{ $metrics['overdue'] }}</h3>
                    </div>
                    <div class="p-3 bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Status Tabs -->
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <a href="{{ route('service-tickets.index', array_merge(request()->except('tab', 'page'), ['tab' => 'all'])) }}" 
                    class="status-tab {{ $activeTab === 'all' ? 'active' : '' }}">
                    All Tickets <span class="count-badge">{{ $tabCounts['all'] }}</span>
                </a>
                <a href="{{ route('service-tickets.index', array_merge(request()->except('tab', 'page'), ['tab' => 'critical'])) }}" 
                    class="status-tab {{ $activeTab === 'critical' ? 'active' : '' }}">
                    🚨 Critical <span class="count-badge">{{ $tabCounts['critical'] }}</span>
                </a>
                <a href="{{ route('service-tickets.index', array_merge(request()->except('tab', 'page'), ['tab' => 'open'])) }}" 
                    class="status-tab {{ $activeTab === 'open' ? 'active' : '' }}">
                    ⚠️ Open & Assigned <span class="count-badge">{{ $tabCounts['open'] }}</span>
                </a>
                <a href="{{ route('service-tickets.index', array_merge(request()->except('tab', 'page'), ['tab' => 'in_progress'])) }}" 
                    class="status-tab {{ $activeTab === 'in_progress' ? 'active' : '' }}">
                    🟡 In Progress <span class="count-badge">{{ $tabCounts['in_progress'] }}</span>
                </a>
                <a href="{{ route('service-tickets.index', array_merge(request()->except('tab', 'page'), ['tab' => 'resolved'])) }}" 
                    class="status-tab {{ $activeTab === 'resolved' ? 'active' : '' }}">
                    🟢 Resolved <span class="count-badge">{{ $tabCounts['resolved'] }}</span>
                </a>
                <a href="{{ route('service-tickets.index', array_merge(request()->except('tab', 'page'), ['tab' => 'closed'])) }}" 
                    class="status-tab {{ $activeTab === 'closed' ? 'active' : '' }}">
                    📁 Closed <span class="count-badge">{{ $tabCounts['closed'] }}</span>
                </a>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm mb-6">
                <form method="GET" action="{{ route('service-tickets.index') }}" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                    
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Search ticket #, issue, customer, phone..."
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-500 py-2">
                    </div>

                    <div>
                        <select name="issue_type" class="filter-select text-xs">
                            <option value="">All Categories</option>
                            @foreach(\App\Models\ServiceTicket::issueTypeOptions() as $k => $label)
                                <option value="{{ $k }}" {{ request('issue_type') === $k ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <select name="priority" class="filter-select text-xs">
                            <option value="">All Priorities</option>
                            @foreach(\App\Models\ServiceTicket::priorityOptions() as $k => $label)
                                <option value="{{ $k }}" {{ request('priority') === $k ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <select name="technician_id" class="filter-select text-xs">
                            <option value="">All Technicians</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" {{ request('technician_id') == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                            class="btn-filter"
                            style="display: inline-flex; align-items: center; justify-content: center; background-color: var(--crm-accent, #be123c); color: #ffffff !important; border-radius: 8px; padding: 7px 14px; font-size: 12px; font-weight: 700; cursor: pointer; border: none; box-shadow: 0 1px 2px rgba(0,0,0,0.1); transition: all 0.15s;">
                        Filter
                    </button>

                    @if(request()->hasAny(['search', 'status', 'priority', 'technician_id', 'issue_type']))
                        <a href="{{ route('service-tickets.index', ['tab' => $activeTab]) }}"
                           style="display: inline-flex; align-items: center; font-size: 12px; color: #64748b; text-decoration: none; font-weight: 600; padding: 7px 10px;"
                           onmouseover="this.style.color='#1e293b'" onmouseout="this.style.color='#64748b'">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Tickets Table -->
            <div class="pg-card">
                @if($tickets->isEmpty())
                    <div class="text-center py-12 px-4">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-800">No Tickets in this Queue</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">No tickets match the selected filters or tab.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="p-table">
                            <thead>
                                <tr>
                                    <th>Ticket # & SLA</th>
                                    <th>Customer & Site</th>
                                    <th>Complaint / Fault</th>
                                    <th>Priority</th>
                                    <th>Assigned Tech</th>
                                    <th>Schedule</th>
                                    <th>Status</th>
                                    <th style="text-align:right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tickets as $ticket)
                                    @php
                                        $isDelayed = in_array($ticket->status, ['open', 'assigned', 'in_progress']) && $ticket->created_at->diffInHours(now()) >= 48;
                                        $isEmailInbound = str_starts_with($ticket->description ?? '', '--- Inbound') || ($ticket->lead && $ticket->lead->source === 'email');
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('service-tickets.show', $ticket) }}" class="cell-no font-mono flex items-center gap-1.5">
                                                {{ $ticket->ticket_no }}
                                                @if($isEmailInbound)
                                                    <span class="text-[9px] bg-indigo-100 text-indigo-700 px-1 py-0.2 rounded font-bold border border-indigo-200">
                                                        ✉️ Email
                                                    </span>
                                                @endif
                                            </a>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                @if($isDelayed)
                                                    <span class="text-[10px] bg-rose-100 text-rose-700 px-1.5 py-0.5 rounded font-bold">
                                                        ⚠️ {{ $ticket->created_at->diffForHumans(null, true) }} open
                                                    </span>
                                                @else
                                                    <span class="text-[10px] text-slate-400">
                                                        {{ $ticket->created_at->diffForHumans() }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($ticket->amc_contract_id)
                                                <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">
                                                    🛡️ AMC Covered
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ticket->lead)
                                                <a href="{{ route('leads.show', $ticket->lead) }}" class="font-bold text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 text-xs">
                                                    {{ $ticket->lead->customer_name }}
                                                </a>
                                                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-2">
                                                    @if($ticket->lead->phone)
                                                        <span>📞 {{ $ticket->lead->phone }}</span>
                                                    @endif
                                                    @if($ticket->lead->email)
                                                        <span class="text-indigo-600">✉️ {{ $ticket->lead->email }}</span>
                                                    @endif
                                                </div>
                                                @if($ticket->lead->site_address)
                                                    <div class="text-[10px] text-slate-400 truncate max-w-[200px]" title="{{ $ticket->lead->site_address }}">
                                                        📍 {{ $ticket->lead->site_address }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-xs text-slate-400">Unlinked Customer</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="font-medium text-slate-800 text-xs">{{ $ticket->title }}</div>
                                            <div class="text-[11px] text-slate-500 mt-0.5">
                                                {{ $ticket->issue_type_label }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge-priority priority-{{ $ticket->priority }}">
                                                @if($ticket->priority === 'critical') 🔴 @elseif($ticket->priority === 'high') 🟠 @elseif($ticket->priority === 'medium') 🟡 @else ⚪ @endif
                                                {{ $ticket->priority }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($ticket->assignedTechnician)
                                                <div class="flex items-center gap-1.5 text-xs text-slate-700">
                                                    <div class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-[10px]">
                                                        {{ substr($ticket->assignedTechnician->name, 0, 1) }}
                                                    </div>
                                                    <span>{{ $ticket->assignedTechnician->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-xs text-amber-600 font-medium">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ticket->scheduled_date)
                                                <span class="text-xs text-slate-600">
                                                    {{ $ticket->scheduled_date->format('d M Y') }}
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-400">Not set</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge-status status-{{ $ticket->status }}">
                                                {{ $ticket->status_label }}
                                            </span>
                                        </td>
                                        <td style="text-align:right">
                                            <a href="{{ route('service-tickets.show', $ticket) }}" class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                                View &rsaquo;
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($tickets->hasPages())
                        <div class="p-4 border-t border-slate-100">
                            {{ $tickets->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Inbound Email Gateway Modal -->
        <div x-show="emailModalOpen" 
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="emailModalOpen = false"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-[#0f172a] rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-700 overflow-hidden text-slate-200" @click.stop style="background-color: #0f172a;">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/20 border border-amber-500/30 text-amber-400 flex items-center justify-center font-bold">
                                ✉️
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-white font-heading">Inbound Email Support Gateway</h3>
                                <p class="text-xs text-slate-400">How customer emails automatically convert into CRM tickets</p>
                            </div>
                        </div>
                        <button type="button" @click="emailModalOpen = false" class="p-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition">
                            ✕
                        </button>
                    </div>

                    <!-- Webhook Info Box -->
                    <div class="mt-4 p-3.5 rounded-xl bg-[#060913] border border-slate-800 text-xs space-y-1">
                        <div class="font-bold flex items-center gap-1.5 text-amber-400">
                            <span>🔗 Live Inbound Webhook Endpoint:</span>
                        </div>
                        <code class="block font-mono bg-[#0b1120] px-3 py-2 rounded-lg border border-slate-700 text-amber-300 text-[11px] select-all">
                            POST {{ url('/api/inbound-email') }}
                        </code>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Point your Mailgun, SendGrid, Postmark, AWS SES, or email parser webhook to this URL to automatically ingest all incoming customer support emails.
                        </p>
                    </div>

                    <!-- Simulation Form -->
                    <div class="mt-5">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-3 flex items-center justify-between">
                            <span>Simulate Customer Support Email</span>
                            <span class="text-[10px] text-slate-500 font-mono">Test instant conversion</span>
                        </div>

                        <form method="POST" action="{{ route('service-tickets.simulate-email') }}" class="space-y-3.5">
                            @csrf

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-300 mb-1">Customer Full Name *</label>
                                    <input type="text" name="customer_name" required placeholder="e.g. Ramesh Kumar" value="Ramesh Kumar"
                                        class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-300 mb-1">Customer Email Address *</label>
                                    <input type="email" name="email" required placeholder="e.g. ramesh@techcorp.com" value="customer@example.com"
                                        class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-300 mb-1">Phone Number (Optional)</label>
                                    <input type="tel" name="phone" placeholder="e.g. +91 98765 43210" value="+91 98765 43210"
                                        class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-300 mb-1">Priority Override</label>
                                    <select name="priority" class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                                        <option value="">Auto-Detect from Email Keywords</option>
                                        <option value="critical">🔴 Critical / Emergency</option>
                                        <option value="high">🟠 High</option>
                                        <option value="medium">🟡 Medium</option>
                                        <option value="low">🟢 Low</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Email Subject *</label>
                                <input type="text" name="subject" required placeholder="e.g. Camera 3 offline" value="Camera 3 offline and NVR beeping sound at Sector 18 site"
                                    class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Email Body Description *</label>
                                <textarea name="body" required rows="3" placeholder="Describe the fault..."
                                    class="w-full text-xs rounded-xl border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">Hi Support Team, Our main entrance camera #3 went black this morning and the NVR recorder has started making a continuous beeping alert sound. Please dispatch a technician urgently.</textarea>
                            </div>

                            <div class="pt-2 flex items-center justify-end gap-2">
                                <button type="button" @click="emailModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-700 text-slate-400 font-bold text-xs hover:text-white hover:bg-slate-800 transition">
                                    Cancel
                                </button>
                                <button type="submit" class="btn-amber">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    <span>Process Inbound Email</span>
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>
</x-app-layout>
