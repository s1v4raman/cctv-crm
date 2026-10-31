<x-app-layout>
    @php
        $tabs = ['all' => 'All Tickets', 'critical' => 'Critical Tickets', 'open' => 'Open Tickets', 'in_progress' => 'In Progress', 'resolved' => 'Resolved Tickets', 'closed' => 'Closed Tickets'];
        $views = collect($tabs)->map(fn ($label, $tab) => [
            'label' => $label . ' (' . ($tabCounts[$tab] ?? 0) . ')',
            'url' => route('service-tickets.index', array_filter(['tab' => $tab === 'all' ? null : $tab])),
            'active' => $activeTab === $tab,
        ])->values()->all();
        $statusOptions = \App\Models\ServiceTicket::statusOptions();
        $priorityOptions = \App\Models\ServiceTicket::priorityOptions();
        $issueOptions = \App\Models\ServiceTicket::issueTypeOptions();
        $filterCount = collect(['search', 'status', 'priority', 'technician_id', 'issue_type'])->filter(fn ($f) => request()->filled($f))->count();
    @endphp

    <x-list-page key="tickets" :paginator="$tickets" :views="$views"
        :sorts="['date_desc' => 'Newest first', 'date_asc' => 'Oldest first', 'scheduled_asc' => 'Scheduled, soonest', 'ticket_no' => 'Ticket number']"
        :create-url="route('service-tickets.create')" create-label="Log Ticket"
        :export-url="null"
        filter-title="Filter Tickets by" :filter-action="route('service-tickets.index')"
        :keep="['tab' => $activeTab === 'all' ? null : $activeTab, 'sort' => request('sort'), 'per_page' => request('per_page')]"
        :filter-count="$filterCount" :clear-url="route('service-tickets.index', array_filter(['tab' => $activeTab === 'all' ? null : $activeTab]))"
        :columns="['complaint' => 'Complaint', 'priority' => 'Priority', 'technician' => 'Technician', 'schedule' => 'Schedule', 'status' => 'Status']"
        empty-text="No tickets yet." filtered-text="No tickets match these filters.">

        <x-slot name="more">
            <a href="{{ route('service-tickets.export-pdf', request()->query()) }}" class="plain-link">Export PDF</a>
            <button type="button" @click="menu = null; $dispatch('open-email-modal')">Email gateway</button>
        </x-slot>

        <x-slot name="filters">
            <div class="ld-sub">Ticket no, issue, customer or phone</div>
            <input type="text" name="search" value="{{ request('search') }}" class="ld-input" aria-label="Ticket number, issue, customer or phone">

            <div class="ld-sub">Status</div>
            <select name="status" class="ld-input" aria-label="Status">
                <option value="">Any status</option>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="ld-sub">Priority</div>
            <select name="priority" class="ld-input" aria-label="Priority">
                <option value="">Any priority</option>
                @foreach($priorityOptions as $value => $label)
                    <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="ld-sub">Issue type</div>
            <select name="issue_type" class="ld-input" aria-label="Issue type">
                <option value="">Any issue</option>
                @foreach($issueOptions as $value => $label)
                    <option value="{{ $value }}" @selected(request('issue_type') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="ld-sub">Technician</div>
            <select name="technician_id" class="ld-input" aria-label="Technician">
                <option value="">Anyone</option>
                @foreach($technicians as $tech)
                    <option value="{{ $tech->id }}" @selected((string) request('technician_id') === (string) $tech->id)>{{ $tech->name }}</option>
                @endforeach
            </select>
        </x-slot>

        <x-slot name="head">
            <th style="min-width: 150px">Ticket No</th>
            <th style="min-width: 230px">Customer</th>
            <th x-show="cols.complaint" style="min-width: 220px">Complaint</th>
            <th x-show="cols.priority" style="min-width: 100px">Priority</th>
            <th x-show="cols.technician" style="min-width: 150px">Technician</th>
            <th x-show="cols.schedule" style="min-width: 120px">Schedule</th>
            <th x-show="cols.status" style="min-width: 120px">Status</th>
        </x-slot>

        @foreach ($tickets as $ticket)
            @php $isDelayed = in_array($ticket->status, ['open', 'assigned', 'in_progress']) && $ticket->created_at->diffInHours(now()) >= 48; @endphp
            <tr>
                <x-list-pick :id="$ticket->id" :label="$ticket->ticket_no" />
                <td class="whitespace-nowrap">
                    <a href="{{ route('service-tickets.show', $ticket) }}" class="ld-name plain-link">{{ $ticket->ticket_no }}</a>
                    <div class="text-xs mt-0.5 {{ $isDelayed ? 'text-amber-700 dark:text-amber-400' : '' }}" style="{{ $isDelayed ? '' : 'color: var(--ld-muted)' }}">
                        {{ $isDelayed ? 'open ' . $ticket->created_at->diffForHumans(null, true) : $ticket->created_at->diffForHumans() }}@if($ticket->amc_contract_id) · AMC @endif
                    </div>
                </td>
                <td>
                    @if($ticket->lead)
                        <a href="{{ route('leads.show', $ticket->lead) }}" class="ld-name plain-link">{{ $ticket->lead->customer_name }}</a>
                        <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $ticket->lead->phone }}</div>
                    @else
                        <span style="color: var(--ld-muted)">No customer linked</span>
                    @endif
                </td>
                <td x-show="cols.complaint">
                    <div>{{ $ticket->title }}</div>
                    <div class="text-xs mt-0.5" style="color: var(--ld-muted)">{{ $ticket->issue_type_label }}</div>
                </td>
                <td x-show="cols.priority">
                    <span class="{{ $ticket->priority === 'critical' ? 'text-rose-700 dark:text-rose-400 font-medium' : ($ticket->priority === 'high' ? 'text-amber-700 dark:text-amber-400' : '') }}">{{ $ticket->priority_label }}</span>
                </td>
                <td x-show="cols.technician" class="whitespace-nowrap">{{ $ticket->assignedTechnician?->name ?? 'Unassigned' }}</td>
                <td x-show="cols.schedule" class="whitespace-nowrap">{{ $ticket->scheduled_date?->format('d/m/Y') ?? 'Not set' }}</td>
                <td x-show="cols.status">{{ $ticket->status_label }}</td>
                <td>
                    <div class="ld-row-actions">
                        <a href="{{ route('service-tickets.edit', $ticket) }}" class="plain-link" title="Edit" aria-label="Edit {{ $ticket->ticket_no }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.2 5.2l3.6 3.6M4 20l4.5-1 10.3-10.3a2.5 2.5 0 00-3.5-3.5L5 15.5 4 20z"/></svg>
                        </a>
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('service-tickets.destroy', $ticket) }}" data-confirm="Delete service ticket {{ $ticket->ticket_no }}?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-cancel" title="Delete" aria-label="Delete {{ $ticket->ticket_no }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-list-page>

    <div x-data="{ emailModalOpen: false }" @open-email-modal.window="emailModalOpen = true">
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
                        <x-close-button tone="dark" @click="emailModalOpen = false" />
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
                            <span class="text-[11px] text-slate-500 font-mono">Test instant conversion</span>
                        </div>

                        <form method="POST" action="{{ route('service-tickets.simulate-email') }}" class="space-y-3.5">
                            @csrf

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Customer Full Name *</label>
                                    <input aria-label="Customer Full Name" type="text" name="customer_name" required placeholder="e.g. Ramesh Kumar" value="Ramesh Kumar"
                                        class="w-full text-xs rounded-[4px] border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Customer Email Address *</label>
                                    <input aria-label="Customer Email Address" type="email" name="email" required placeholder="e.g. ramesh@techcorp.com" value="customer@example.com"
                                        class="w-full text-xs rounded-[4px] border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Phone Number (Optional)</label>
                                    <input aria-label="Phone Number (Optional)" type="tel" name="phone" placeholder="e.g. +91 98765 43210" value="+91 98765 43210"
                                        class="w-full text-xs rounded-[4px] border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-1.5">Priority Override</label>
                                    <select aria-label="Priority Override" name="priority" class="w-full text-xs rounded-[4px] border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
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
                                <input aria-label="Email Subject" type="text" name="subject" required placeholder="e.g. Camera 3 offline" value="Camera 3 offline and NVR beeping sound at Sector 18 site"
                                    class="w-full text-xs rounded-[4px] border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:ring-amber-400/20 p-2.5">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-300 mb-1.5">Email Body Description *</label>
                                <textarea aria-label="Email Body Description" name="body" required rows="3" placeholder="Describe the fault..."
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
