<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">Service Tickets</h2>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" @click="$dispatch('open-email-modal')" class="btn-secondary">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Email Gateway</span>
                </button>
                <a href="{{ route('service-tickets.export-pdf', request()->query()) }}" class="btn-secondary">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>PDF Export</span>
                </a>
                <a href="{{ route('service-tickets.create') }}" class="btn-primary">
                    Log Ticket
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .status-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.75rem;
            border-radius: 4px;
            font-size: 0.82rem;
            font-weight: 500;
            color: #475569;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            text-decoration: none;
            transition: border-color 0.15s;
        }
        html.dark .status-tab {
            background: #0f172a;
            border-color: var(--text-main);
            color: var(--text-muted);
        }
        .status-tab.active {
            background: #2563eb !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
            font-weight: 600;
        }
        .status-tab .count-badge {
            font-size: 0.75rem;
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
            background: #f1f5f9;
            color: #475569;
        }
        .status-tab.active .count-badge {
            background: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
        }

        .cell-no { font-weight: 600; color: #2563eb; text-decoration: none; }
        .cell-no:hover { text-decoration: underline; }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px 14px;
        }
        html.dark .stat-card {
            background: #0f172a;
            border-color: var(--text-main);
        }

        /* Priority: plain bold colored text */
        .badge-priority { font-size: 0.85rem; font-weight: 700; background: transparent !important; border: none !important; padding: 0 !important; }
        .priority-critical { color: #dc2626 !important; }
        .priority-high     { color: #ea580c !important; }
        .priority-medium   { color: #d97706 !important; }
        .priority-low      { color: #64748b !important; }

        /* Status: plain bold colored text */
        .badge-status { font-size: 0.85rem; font-weight: 700; background: transparent !important; border: none !important; padding: 0 !important; }
        .status-open        { color: #dc2626 !important; }
        .status-assigned    { color: #7e22ce !important; }
        .status-in_progress { color: #d97706 !important; }
        .status-resolved    { color: #16a34a !important; }
        .status-closed      { color: #64748b !important; }
        .status-cancelled   { color: #64748b !important; }
    </style>

    <div class="pg-wrap" x-data="{ emailModalOpen: false }" @open-email-modal.window="emailModalOpen = true">
        <div class="pg-inner">
            
            @if (session('success'))
                <div class="mb-4 p-3 rounded bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filters Bar (Medium 38px Height Level) -->
            <div class="mb-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-2.5 shadow-xs">
                <form method="GET" action="{{ route('service-tickets.index') }}" class="flex flex-wrap items-center gap-2.5 text-xs">
                    <div class="relative w-72 min-w-[220px] flex items-center shrink-0">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 pointer-events-none shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Search ticket #, issue, customer, phone..."
                            style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important; padding-left: 36px !important;"
                            class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-600">
                    </div>

                    <select name="status" style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important; min-width: 140px;" class="text-xs px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 shrink-0">
                        <option value="">All Statuses</option>
                        <option value="open" @selected(request('status') === 'open')>Open</option>
                        <option value="assigned" @selected(request('status') === 'assigned')>Assigned</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                        <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
                        <option value="closed" @selected(request('status') === 'closed')>Closed</option>
                    </select>
                    
                    <button type="submit"
                            style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important;"
                            class="px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition inline-flex items-center justify-center shrink-0 shadow-xs cursor-pointer">
                        Filter
                    </button>

                    @if(request()->hasAny(['search', 'status', 'priority', 'technician_id', 'issue_type']))
                        <a href="{{ route('service-tickets.index', ['tab' => $activeTab]) }}"
                           style="height: 38px !important; min-height: 38px !important; max-height: 38px !important; border-radius: 8px !important;"
                           class="px-3.5 inline-flex items-center rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium transition shrink-0">
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
                                                    <span class="text-[10px] text-slate-500 font-medium">
                                                        [Email]
                                                    </span>
                                                @endif
                                            </a>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                @if($isDelayed)
                                                    <span class="text-xs text-red-600 font-semibold">
                                                        {{ $ticket->created_at->diffForHumans(null, true) }} open
                                                    </span>
                                                @else
                                                    <span class="text-xs text-slate-400">
                                                        {{ $ticket->created_at->diffForHumans() }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($ticket->amc_contract_id)
                                                <div class="text-xs text-emerald-600 font-medium mt-0.5">
                                                    AMC Covered
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ticket->lead)
                                                <a href="{{ route('leads.show', $ticket->lead) }}" class="font-semibold text-slate-900 dark:text-white hover:text-blue-600 text-xs">
                                                    {{ $ticket->lead->customer_name }}
                                                </a>
                                                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-2">
                                                    @if($ticket->lead->phone)
                                                        <span>{{ $ticket->lead->phone }}</span>
                                                    @endif
                                                    @if($ticket->lead->email)
                                                        <span class="text-slate-500">{{ $ticket->lead->email }}</span>
                                                    @endif
                                                </div>
                                                @if($ticket->lead->site_address)
                                                    <div class="text-[11px] text-slate-400 truncate max-w-[200px]" title="{{ $ticket->lead->site_address }}">
                                                        {{ $ticket->lead->site_address }}
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
                                                {{ ucfirst($ticket->priority) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($ticket->assignedTechnician)
                                                <div class="text-xs text-slate-700 dark:text-slate-300">
                                                    {{ $ticket->assignedTechnician->name }}
                                                </div>
                                            @else
                                                <span class="text-xs text-slate-400">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ticket->scheduled_date)
                                                <span class="text-xs text-slate-600 dark:text-slate-400">
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
                                        <td class="whitespace-nowrap text-right text-sm table-actions" style="text-align:right;">
                                            <a href="{{ route('service-tickets.show', $ticket) }}" class="action-link">View</a>
                                            <a href="{{ route('service-tickets.edit', $ticket) }}" class="action-link">Edit</a>
                                            @if(auth()->user()->isAdmin())
                                                <form method="POST" action="{{ route('service-tickets.destroy', $ticket) }}" class="inline"
                                                      onsubmit="return confirm('Delete service ticket {{ addslashes($ticket->ticket_no) }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="danger-link">Delete</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Total count shown below the table --}}
                    <div class="px-4 py-2.5 border-t border-[#ddd] dark:border-slate-800 bg-[#fbfbfb] dark:bg-slate-900/60 flex items-center justify-between text-xs text-slate-500">
                        <span class="font-medium">Total: {{ $tickets->total() }}</span>
                        @if($tickets->hasPages())
                            <div class="text-xs">
                                {{ $tickets->links() }}
                            </div>
                        @endif
                    </div>
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
                <div class="relative bg-[#0f172a] rounded-[4px] max-w-xl w-full p-6 border border-slate-700 overflow-hidden text-slate-200" @click.stop style="background-color: var(--text-main);">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/20 border border-amber-500/30 text-amber-400 flex items-center justify-center font-bold">
                                
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
                            <span>Live Inbound Webhook Endpoint:</span>
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
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Customer Full Name *</label>
                                    <input type="text" name="customer_name" required placeholder="e.g. Ramesh Kumar" value="Ramesh Kumar"
                                        class="w-full text-xs rounded-[4px] border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Customer Email Address *</label>
                                    <input type="email" name="email" required placeholder="e.g. ramesh@techcorp.com" value="customer@example.com"
                                        class="w-full text-xs rounded-[4px] border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Phone Number (Optional)</label>
                                    <input type="tel" name="phone" placeholder="e.g. +91 98765 43210" value="+91 98765 43210"
                                        class="w-full text-xs rounded-[4px] border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Priority Override</label>
                                    <select name="priority" class="w-full text-xs rounded-[4px] border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                                        <option value="">Auto-Detect from Email Keywords</option>
                                        <option value="critical">Critical / Emergency</option>
                                        <option value="high">High</option>
                                        <option value="medium">Medium</option>
                                        <option value="low">Low</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-300 mb-1.5">Email Subject *</label>
                                <input type="text" name="subject" required placeholder="e.g. Camera 3 offline" value="Camera 3 offline and NVR beeping sound at Sector 18 site"
                                    class="w-full text-xs rounded-[4px] border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-300 mb-1.5">Email Body Description *</label>
                                <textarea name="body" required rows="3" placeholder="Describe the fault..."
                                    class="w-full text-xs rounded-[4px] border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">Hi Support Team, Our main entrance camera #3 went black this morning and the NVR recorder has started making a continuous beeping alert sound. Please dispatch a technician urgently.</textarea>
                            </div>

                            <div class="pt-2 flex items-center justify-end gap-2">
                                <button type="button" @click="emailModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-700 text-slate-400 font-bold text-xs hover:text-white hover:bg-slate-800 transition">
                                    Cancel
                                </button>
                                <button type="submit" class="px-4 py-2 rounded-[4px] bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition inline-flex items-center gap-1.5">
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
