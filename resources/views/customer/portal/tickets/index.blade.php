<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('portal.dashboard') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">← Back to Overview</a>
                    <span class="text-slate-400">/</span>
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Helpdesk</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight mt-1 font-heading">Service & Breakdown Tickets</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Raise support requests for offline cameras, DVR issues, recording errors, and track resolution in realtime.</p>
            </div>
            <a href="{{ route('portal.tickets.create') }}" 
               class="crm-customer-action-btn inline-flex items-center gap-2 px-4 py-2 text-white text-xs font-bold rounded-xl shadow-xs transition-all duration-200 cursor-pointer"
               style="background: linear-gradient(135deg, var(--crm-accent, #be123c), var(--crm-accent-hover, #9f1239)); border: 1px solid var(--crm-accent, #be123c); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(190,18,60,0.35));">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Log New Service Ticket</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f8fafc] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(!$hasLead || $tickets->isEmpty() && request('status', 'all') === 'all' && !request('search'))
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-10 border border-slate-200/90 dark:border-slate-800 shadow-xs text-center max-w-2xl mx-auto space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center mx-auto mb-3 font-bold text-2xl">
                        🎫
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white font-heading">No Support Tickets Logged</h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">All your CCTV cameras and security equipment are operating normally. If any issue arises, log a breakdown ticket for immediate engineer dispatch.</p>
                    <div class="pt-2">
                        <a href="{{ route('portal.tickets.create') }}" 
                           class="crm-customer-action-btn inline-flex items-center gap-2 px-5 py-2.5 text-white font-bold text-xs rounded-xl shadow-xs transition-all duration-200 cursor-pointer"
                           style="background: linear-gradient(135deg, var(--crm-accent, #be123c), var(--crm-accent-hover, #9f1239)); border: 1px solid var(--crm-accent, #be123c); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(190,18,60,0.35));">
                            Report Camera Issue →
                        </a>
                    </div>
                </div>
            @else
                {{-- KPI Metric Summary Cards --}}
                @php
                    $totalTicketsCount = method_exists($tickets, 'total') ? $tickets->total() : $tickets->count();
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Tickets Logged</div>
                        <div class="text-2xl font-black text-slate-900 dark:text-white font-heading mt-1">{{ $totalTicketsCount }}</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-1">Lifetime Site Requests</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Under Review</div>
                        <div class="text-2xl font-black text-amber-600 dark:text-amber-400 font-heading mt-1">
                            {{ $tickets->filter(fn($t) => $t->status === 'open')->count() }} Active
                        </div>
                        <div class="text-[11px] text-amber-600 dark:text-amber-400 font-medium mt-1">● Triage & Engineering</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Technician Dispatched</div>
                        <div class="text-2xl font-black text-blue-600 dark:text-blue-400 font-heading mt-1">
                            {{ $tickets->filter(fn($t) => in_array($t->status, ['assigned', 'in_progress']))->count() }} Dispatched
                        </div>
                        <div class="text-[11px] text-blue-600 dark:text-blue-400 font-medium mt-1">⚙ Field Tech On The Job</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Resolved & Closed</div>
                        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-heading mt-1">
                            {{ $tickets->filter(fn($t) => in_array($t->status, ['resolved', 'closed']))->count() }} Resolved
                        </div>
                        <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium mt-1">✓ Complete Restoration</div>
                    </div>
                </div>

                {{-- Filter & Search Header --}}
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
                    
                    {{-- Status Pills --}}
                    <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0">
                        <a href="{{ route('portal.tickets', ['status' => 'all', 'search' => request('search')]) }}" 
                           class="px-3 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 {{ $statusFilter === 'all' ? 'crm-customer-action-btn text-white font-bold shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
                           @if($statusFilter === 'all') style="background: linear-gradient(135deg, var(--crm-accent, #be123c), var(--crm-accent-hover, #9f1239)); border: 1px solid var(--crm-accent, #be123c);" @endif>
                            All Tickets
                        </a>
                        <a href="{{ route('portal.tickets', ['status' => 'open', 'search' => request('search')]) }}" 
                           class="px-3 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 {{ $statusFilter === 'open' ? 'bg-amber-500 text-white font-bold shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                            Under Review
                        </a>
                        <a href="{{ route('portal.tickets', ['status' => 'in_progress', 'search' => request('search')]) }}" 
                           class="px-3 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 {{ $statusFilter === 'in_progress' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                            In Progress
                        </a>
                        <a href="{{ route('portal.tickets', ['status' => 'resolved', 'search' => request('search')]) }}" 
                           class="px-3 py-1.5 rounded-xl text-xs font-semibold transition shrink-0 {{ $statusFilter === 'resolved' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                            Resolved
                        </a>
                    </div>

                    {{-- Search Bar --}}
                    <form method="GET" action="{{ route('portal.tickets') }}" class="w-full sm:w-80 flex items-center gap-2">
                        <input type="hidden" name="status" value="{{ $statusFilter }}">
                        <div class="relative w-full">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Search ticket # or description..." 
                                   class="w-full pl-9 pr-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <button type="submit" 
                                class="crm-customer-action-btn px-4 py-2 text-white text-xs font-bold rounded-xl shadow-xs transition-all duration-200 shrink-0 cursor-pointer"
                                style="background: linear-gradient(135deg, var(--crm-accent, #be123c), var(--crm-accent-hover, #9f1239)); border: 1px solid var(--crm-accent, #be123c); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(190,18,60,0.35));">
                            Find
                        </button>
                    </form>
                </div>

                {{-- Tickets Table / List --}}
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-6 py-3.5">Ticket Details</th>
                                    <th class="px-6 py-3.5">Issue Category</th>
                                    <th class="px-6 py-3.5">Priority</th>
                                    <th class="px-6 py-3.5">Live Status</th>
                                    <th class="px-6 py-3.5">Assigned Engineer</th>
                                    <th class="px-6 py-3.5">Logged Date</th>
                                    <th class="px-6 py-3.5 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium text-slate-700 dark:text-slate-300">
                                @foreach($tickets as $ticket)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono text-xs font-bold text-blue-600 dark:text-blue-400">#{{ $ticket->ticket_no }}</span>
                                            </div>
                                            <div class="font-bold text-slate-900 dark:text-white text-sm mt-0.5 font-heading">{{ $ticket->title }}</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1 mt-0.5">{{ $ticket->description }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-[11px] border border-slate-200 dark:border-slate-700">
                                                {{ $ticket->issue_type_label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($ticket->priority === 'critical')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                    ● Critical
                                                </span>
                                            @elseif($ticket->priority === 'high')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                    ● High
                                                </span>
                                            @elseif($ticket->priority === 'medium')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                    ● Medium
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                    Low
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($ticket->status === 'resolved' || $ticket->status === 'closed')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                    ✓ {{ $ticket->status_label }}
                                                </span>
                                            @elseif($ticket->status === 'cancelled')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                    ✕ Declined
                                                </span>
                                            @elseif($ticket->status === 'in_progress')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                    ⚙ In Progress
                                                </span>
                                            @elseif($ticket->status === 'assigned')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                    👤 Tech Dispatched
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                    ⏳ Under Review
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($ticket->assignedTechnician)
                                                <div class="font-bold text-slate-900 dark:text-white">{{ $ticket->assignedTechnician->name }}</div>
                                                <div class="text-[10px] text-slate-500 dark:text-slate-400">Field Engineer</div>
                                            @else
                                                <span class="text-slate-400 italic">Dispatching Engineer...</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400 font-mono">
                                            {{ $ticket->created_at->format('d M Y, h:i A') }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('portal.tickets.show', $ticket) }}" 
                                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#2563eb] hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                                <span>View Stepper</span>
                                                <span>→</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($tickets, 'hasPages') && $tickets->hasPages())
                        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                            {{ $tickets->links() }}
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
