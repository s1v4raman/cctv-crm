<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('portal.tickets') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">← Back to All Tickets</a>
                    <span class="text-slate-400">/</span>
                    <span class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400 uppercase">#{{ $ticket->ticket_no }}</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight mt-1 font-heading">{{ $ticket->title }}</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Logged on {{ $ticket->created_at->format('l, d M Y at h:i A') }}</p>
            </div>
            
            <div class="flex items-center gap-2">
                @if($ticket->status === 'resolved' || $ticket->status === 'closed')
                    <span class="px-3 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-xs flex items-center gap-1.5">
                        <span>✓</span>
                        <span>Resolved</span>
                    </span>
                @elseif($ticket->status === 'cancelled')
                    <span class="px-3 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 shadow-xs flex items-center gap-1.5">
                        <span>✕</span>
                        <span>Request Declined</span>
                    </span>
                @elseif($ticket->status === 'in_progress')
                    <span class="px-3 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shadow-xs flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>In Progress</span>
                    </span>
                @elseif($ticket->status === 'assigned')
                    <span class="px-3 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shadow-xs flex items-center gap-1.5">
                        <span>👤</span>
                        <span>Engineer Dispatched</span>
                    </span>
                @else
                    <span class="px-3 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shadow-xs flex items-center gap-1.5">
                        <span>⏳</span>
                        <span>Under Review</span>
                    </span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f8fafc] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100 transition-colors duration-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Status Alert banner --}}
            @if(session('status'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl flex items-center gap-3 shadow-xs">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-bold text-sm">✓</div>
                    <div class="text-xs font-semibold text-emerald-900 dark:text-emerald-200">{{ session('status') }}</div>
                </div>
            @endif

            {{-- Real-time 4-Stage Stepper Progress Tracker --}}
            @php
                $step = 1;
                if ($ticket->status === 'open') {
                    $step = 2;
                } elseif ($ticket->status === 'assigned') {
                    $step = 3;
                } elseif ($ticket->status === 'in_progress') {
                    $step = 3;
                } elseif ($ticket->status === 'resolved' || $ticket->status === 'closed') {
                    $step = 4;
                }
            @endphp
            <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 p-6 shadow-xs">
                <div class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 mb-5">Live Service Progress</div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {{-- Step 1 --}}
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 {{ $step >= 1 ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700' }}">
                            {{ $step > 1 ? '✓' : '1' }}
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">1. Logged</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">Ticket registered</div>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 {{ $step >= 2 ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700' }}">
                            {{ $step > 2 ? '✓' : '2' }}
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">2. Review</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">Triage & planning</div>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 {{ $step >= 3 ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700' }}">
                            {{ $step > 3 ? '✓' : '3' }}
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">3. Dispatched</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">Engineer on-site</div>
                        </div>
                    </div>

                    {{-- Step 4 --}}
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 {{ $step >= 4 ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700' }}">
                            4
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">4. Resolved</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">Feed restored</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Decision Banners for Customer --}}
            @if($ticket->status === 'cancelled')
                <div class="p-5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 rounded-2xl flex items-start gap-4 shadow-xs">
                    <div class="p-2.5 bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 rounded-xl text-base font-bold">✕</div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-rose-900 dark:text-rose-300 font-heading">Work Request Declined</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                            {{ $ticket->resolution_notes ?: 'Your work request was reviewed and could not be accepted at this time. Please contact support if you have questions.' }}
                        </p>
                    </div>
                </div>
            @elseif($ticket->status === 'assigned' || $ticket->status === 'in_progress')
                <div class="p-5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/50 rounded-2xl flex items-start gap-4 shadow-xs">
                    <div class="p-2.5 bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 rounded-xl text-base font-bold">✓</div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-emerald-900 dark:text-emerald-300 font-heading">Work Request Accepted & Confirmed!</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                            Our engineering team has scheduled your request. 
                            @if($ticket->assignedTechnician)
                                Assigned field engineer: <strong class="text-slate-800 dark:text-white">{{ $ticket->assignedTechnician->name }}</strong>.
                            @endif
                            @if($ticket->scheduled_date)
                                Scheduled visit date: <strong class="text-blue-600 dark:text-blue-400">{{ $ticket->scheduled_date->format('l, d M Y') }}</strong>.
                            @endif
                        </p>
                        @if($ticket->resolution_notes && !str_starts_with($ticket->resolution_notes, 'DECLINED'))
                            <div class="mt-2 p-3 bg-white dark:bg-slate-900 rounded-xl text-xs text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800">
                                <strong class="text-blue-600 dark:text-blue-400">Admin Note:</strong> {{ $ticket->resolution_notes }}
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($ticket->status === 'open')
                <div class="p-5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/50 rounded-2xl flex items-start gap-4 shadow-xs">
                    <div class="p-2.5 bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 rounded-xl text-base font-bold">⏳</div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-amber-900 dark:text-amber-300 font-heading">Awaiting Admin Review & Scheduling</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                            We have received your site request. Our operations manager is reviewing the technical details and assigning an engineer.
                        </p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- Left 2 Columns: Ticket Content & Resolution Notes --}}
                <div class="md:col-span-2 space-y-6">
                    
                    {{-- Problem Description Card --}}
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Incident Details</span>
                            <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold border border-slate-200 dark:border-slate-700">
                                {{ $ticket->issue_type_label }}
                            </span>
                        </div>

                        <div class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line bg-slate-50 dark:bg-slate-800/60 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                            {{ $ticket->description }}
                        </div>
                    </div>

                    {{-- Engineer Resolution Card --}}
                    @if($ticket->status === 'resolved' || $ticket->status === 'closed' || $ticket->troubleshooting_notes || $ticket->parts_replaced)
                        <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-emerald-200 dark:border-emerald-800/80 shadow-xs p-6 space-y-4">
                            <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 flex items-center justify-center text-xs font-bold">✓</div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading">Engineer Service Report & Resolution</h3>
                            </div>

                            @if($ticket->troubleshooting_notes)
                                <div class="space-y-1">
                                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Diagnostics & Findings</div>
                                    <div class="text-sm text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                        {{ $ticket->troubleshooting_notes }}
                                    </div>
                                </div>
                            @endif

                            @if($ticket->parts_replaced)
                                <div class="space-y-1">
                                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Parts / Spares Replaced</div>
                                    <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                        {{ $ticket->parts_replaced }}
                                    </div>
                                </div>
                            @endif

                            @if($ticket->resolution_notes)
                                <div class="space-y-1">
                                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Final Outcome</div>
                                    <div class="text-sm text-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-800 font-medium">
                                        {{ $ticket->resolution_notes }}
                                    </div>
                                </div>
                            @endif

                            @if($ticket->resolved_at)
                                <div class="text-xs text-slate-500 dark:text-slate-400 pt-2 flex items-center gap-1.5 font-mono">
                                    <span>Resolved on:</span>
                                    <strong class="text-blue-600 dark:text-blue-400">{{ \Carbon\Carbon::parse($ticket->resolved_at)->format('d M Y, h:i A') }}</strong>
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- In-progress timeline badge --}}
                        <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 text-slate-700 dark:text-slate-300 flex items-start gap-3 shadow-xs">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 flex items-center justify-center shrink-0 font-bold">
                                ℹ
                            </div>
                            <div class="text-xs leading-relaxed">
                                <h4 class="font-bold text-sm text-slate-900 dark:text-white font-heading">Ticket is Under Active Review</h4>
                                Our CCTV support desk is actively managing this ticket. Once the technician completes on-site diagnostics or remote camera calibration, full details will update automatically here.
                            </div>
                        </div>
                    @endif

                </div>

                {{-- Right Column: Metadata Details --}}
                <div class="space-y-6">

                    {{-- Status Card --}}
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-xs space-y-4 text-xs">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Ticket Information</h3>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                                <span class="text-slate-500 dark:text-slate-400">Ticket No:</span>
                                <span class="font-mono font-bold text-blue-600 dark:text-blue-400">#{{ $ticket->ticket_no }}</span>
                            </div>

                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                                <span class="text-slate-500 dark:text-slate-400">Priority:</span>
                                <span class="font-bold capitalize text-slate-900 dark:text-white">{{ $ticket->priority }}</span>
                            </div>

                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                                <span class="text-slate-500 dark:text-slate-400">Assigned Engineer:</span>
                                <strong class="text-slate-800 dark:text-white">{{ $ticket->assignedTechnician?->name ?: 'Pending Assignment' }}</strong>
                            </div>

                            @if($ticket->scheduled_date)
                                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                                    <span class="text-slate-500 dark:text-slate-400">Scheduled Site Visit:</span>
                                    <strong class="text-blue-600 dark:text-blue-400">{{ \Carbon\Carbon::parse($ticket->scheduled_date)->format('d M Y') }}</strong>
                                </div>
                            @endif

                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                                <span class="text-slate-500 dark:text-slate-400">Coverage Type:</span>
                                <span class="font-bold text-slate-800 dark:text-white">
                                    {{ $ticket->amc_contract_id ? 'AMC Protected' : 'Standard Service' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <span class="text-slate-500 dark:text-slate-400">Site Location:</span>
                                <span class="font-bold text-slate-800 dark:text-white">{{ $lead->customer_name }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Need Assistance CTA --}}
                    <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-3 text-xs">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white font-heading">Need to expedite this ticket?</h4>
                        <p class="text-slate-500 dark:text-slate-400 text-xs">Call our CCTV central desk quoting ticket <strong class="text-blue-600 dark:text-blue-400">#{{ $ticket->ticket_no }}</strong>.</p>
                        <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-3 font-mono font-bold text-sm text-center text-blue-600 dark:text-blue-400 border border-slate-200 dark:border-slate-700">
                            +91 98765 43210
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
