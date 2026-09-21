<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('leads.index') }}" class="p-2 rounded-xl bg-[#0F172A] border border-white/10 text-slate-400 hover:text-white hover:border-amber-400/40 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl sm:text-2xl font-black text-white font-heading tracking-tight">
                            {{ $lead->customer_name }}
                        </h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider
                            @if($lead->status === 'won') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                            @elseif($lead->status === 'quoted') bg-amber-500/20 text-amber-400 border border-amber-500/30
                            @elseif($lead->status === 'contacted') bg-sky-500/20 text-sky-400 border border-sky-500/30
                            @elseif($lead->status === 'lost') bg-rose-500/20 text-rose-400 border border-rose-500/30
                            @else bg-slate-800 text-slate-400 border border-slate-700 @endif">
                            ● {{ ucfirst($lead->status) }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5 flex items-center gap-2">
                        <span>📞 {{ $lead->phone }}</span>
                        @if($lead->email) <span>&middot; ✉️ {{ $lead->email }}</span> @endif
                        @if($lead->source) <span>&middot; 🏷️ <span class="capitalize">{{ $lead->source }}</span></span> @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('leads.status', $lead) }}" class="inline-flex items-center">
                        @csrf
                        <select name="status" onchange="this.form.submit()"
                            class="bg-[#060913] border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 font-bold focus:border-amber-400 focus:outline-none cursor-pointer">
                            @foreach (['new','contacted','quoted','won','lost'] as $s)
                                <option value="{{ $s }}" @selected($lead->status === $s)>Status: {{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </form>

                    <a href="{{ route('leads.edit', $lead) }}"
                       class="px-3.5 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                        </svg>
                        <span>Edit</span>
                    </a>
                @endif
                
                <a href="{{ route('leads.vcard', $lead) }}"
                   class="px-3.5 py-2 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold transition flex items-center gap-1.5"
                   title="Download vCard to phone contacts">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.765z"/>
                    </svg>
                    <span>Save Contact</span>
                </a>

                <a href="{{ route('site-surveys.create', ['lead_id' => $lead->id]) }}"
                   class="px-3.5 py-2 rounded-xl bg-indigo-500/15 hover:bg-indigo-500/25 text-indigo-400 border border-indigo-500/30 text-xs font-bold transition flex items-center gap-1.5">
                    <span>📐 Survey</span>
                </a>

                <a href="{{ route('estimator.index', ['lead_id' => $lead->id]) }}"
                   class="px-3.5 py-2 rounded-xl bg-purple-500/15 hover:bg-purple-500/25 text-purple-400 border border-purple-500/30 text-xs font-bold transition flex items-center gap-1.5">
                    <span>🧮 BOM</span>
                </a>

                <a href="{{ route('quotations.create', $lead) }}"
                   class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-extrabold text-xs shadow-lg shadow-amber-500/20 transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>New Quote</span>
                </a>

                <a href="{{ route('service-tickets.create', ['lead_id' => $lead->id]) }}"
                   class="px-3.5 py-2 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-400 border border-rose-500/30 text-xs font-bold transition flex items-center gap-1.5">
                    <span>🚨 Ticket</span>
                </a>

                @if(auth()->user()->isAdmin())
                <form method="POST" action="{{ route('leads.destroy', $lead) }}"
                      onsubmit="return confirm('Delete {{ addslashes($lead->customer_name) }}? All quotations and jobs will also be deleted.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-3 py-2 rounded-xl bg-rose-950/40 hover:bg-rose-900/60 text-rose-400 border border-rose-800/50 text-xs font-bold transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-[#060913] min-h-screen text-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center gap-2">
                    <span>✓ {{ session('status') }}</span>
                </div>
            @endif

            {{-- Lead Info Overview Card --}}
            <div class="bg-[#0F172A] rounded-2xl border border-white/10 shadow-xl overflow-hidden">
                <div class="px-6 py-4 bg-[#0B1120] border-b border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-base">👤</span>
                        <h3 class="text-sm font-extrabold text-white font-heading">Lead & Customer Profile</h3>
                    </div>
                    <span class="text-xs text-slate-400 font-mono">Lead ID #{{ $lead->id }}</span>
                </div>
                
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Customer Name</div>
                        <div class="text-sm font-bold text-white mt-1">{{ $lead->customer_name }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Primary Phone</div>
                        <div class="text-sm font-bold text-amber-400 mt-1 font-mono">{{ $lead->phone }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Email Address</div>
                        <div class="text-sm font-medium text-slate-300 mt-1">{{ $lead->email ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Acquisition Source</div>
                        <div class="text-sm font-bold text-sky-400 mt-1 capitalize">{{ $lead->source ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Registration Date</div>
                        <div class="text-sm font-medium text-slate-300 mt-1">{{ $lead->created_at->format('d M Y') }}</div>
                    </div>
                    @if($lead->site_address)
                    <div class="sm:col-span-2 lg:col-span-3">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Premises / Installation Site Address</div>
                        <div class="text-sm font-medium text-slate-200 mt-1 flex items-center gap-1.5">
                            <span class="text-amber-400">📍</span> {{ $lead->site_address }}
                        </div>
                    </div>
                    @endif
                </div>

                @if($lead->activeAmcContract)
                    <div class="mx-6 mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🛡️</span>
                            <div>
                                <div class="text-sm font-extrabold text-emerald-300">
                                    Active AMC Contract: {{ $lead->activeAmcContract->contract_no }}
                                </div>
                                <div class="text-xs text-emerald-400/80 mt-0.5">
                                    {{ ucfirst($lead->activeAmcContract->frequency) }} servicing &middot; Valid until {{ $lead->activeAmcContract->end_date->format('d M Y') }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 shrink-0">
                            <a href="{{ route('service-tickets.create', ['lead_id' => $lead->id, 'amc_contract_id' => $lead->activeAmcContract->id]) }}" 
                               class="px-3 py-1.5 rounded-xl bg-emerald-500 text-slate-950 font-black text-xs shadow-md hover:bg-emerald-400 transition">
                                + Log AMC Service
                            </a>
                            <a href="{{ route('amcs.show', $lead->activeAmcContract) }}" 
                               class="px-3 py-1.5 rounded-xl bg-slate-800 text-emerald-400 border border-emerald-500/30 font-bold text-xs hover:bg-slate-700 transition">
                                Agreement &rarr;
                            </a>
                        </div>
                    </div>
                @endif

                @if($lead->notes)
                    <div class="mx-6 mb-6 p-4 rounded-xl bg-[#060913] border border-slate-800 text-xs text-slate-300 leading-relaxed">
                        <span class="font-bold text-amber-400 uppercase tracking-wider text-[10px] block mb-1">Notes & Scope:</span>
                        {{ $lead->notes }}
                    </div>
                @endif
            </div>

            {{-- Site Surveys Card --}}
            <div class="bg-[#0F172A] rounded-2xl border border-white/10 shadow-xl overflow-hidden">
                <div class="px-6 py-4 bg-[#0B1120] border-b border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-base">📋</span>
                        <h3 class="text-sm font-extrabold text-white font-heading">Site Surveys & Technical Audits</h3>
                    </div>
                    <a href="{{ route('site-surveys.create', ['lead_id' => $lead->id]) }}"
                       class="px-3 py-1 rounded-xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 text-xs font-bold hover:bg-indigo-500/30 transition">
                        + Schedule Survey
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#060913]/60 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-white/5">
                            <tr>
                                <th class="px-6 py-3">Survey Date</th>
                                <th class="px-6 py-3">Surveyed By</th>
                                <th class="px-6 py-3">Cameras Rec.</th>
                                <th class="px-6 py-3">Photos</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-slate-300">
                            @forelse ($lead->siteSurveys as $s)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="px-6 py-3.5 font-bold text-white">
                                        <a href="{{ route('site-surveys.show', $s) }}" class="text-amber-400 hover:underline">
                                            {{ $s->survey_date->format('d M Y') }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-3.5">{{ $s->surveyedBy->name ?? '—' }}</td>
                                    <td class="px-6 py-3.5 font-bold text-sky-400">{{ $s->camera_count_recommended ?? '—' }}</td>
                                    <td class="px-6 py-3.5">
                                        @if($s->photos->count())
                                            <span class="px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 font-bold text-[11px] border border-indigo-500/30">
                                                📷 {{ $s->photos->count() }}
                                            </span>
                                        @else
                                            <span class="text-slate-500">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                            @if($s->status === 'completed') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                            @elseif($s->status === 'cancelled') bg-rose-500/20 text-rose-400 border border-rose-500/30
                                            @else bg-amber-500/20 text-amber-400 border border-amber-500/30 @endif">
                                            {{ ucfirst($s->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 text-right">
                                        <a href="{{ route('site-surveys.show', $s) }}" class="text-amber-400 hover:underline font-bold">
                                            View Report &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                        No site surveys conducted yet for this lead.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Quotations Card --}}
            <div class="bg-[#0F172A] rounded-2xl border border-white/10 shadow-xl overflow-hidden">
                <div class="px-6 py-4 bg-[#0B1120] border-b border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-base">📑</span>
                        <h3 class="text-sm font-extrabold text-white font-heading">Commercial Quotations</h3>
                    </div>
                    <a href="{{ route('quotations.create', $lead) }}"
                       class="px-3 py-1 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-bold hover:bg-amber-500/30 transition">
                        + New Quotation
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#060913]/60 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-white/5">
                            <tr>
                                <th class="px-6 py-3">Quotation No</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Valid Until</th>
                                <th class="px-6 py-3">Grand Total</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-slate-300">
                            @forelse ($lead->quotations as $q)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="px-6 py-3.5 font-bold font-mono text-amber-400">
                                        <a href="{{ route('quotations.show', $q) }}" class="hover:underline">
                                            {{ $q->quotation_no }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-3.5">{{ $q->quotation_date?->format('d M Y') ?? '—' }}</td>
                                    <td class="px-6 py-3.5">{{ $q->valid_until?->format('d M Y') ?? '—' }}</td>
                                    <td class="px-6 py-3.5 font-black text-white font-heading text-sm">₹{{ number_format($q->total, 2) }}</td>
                                    <td class="px-6 py-3.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                            @if($q->status === 'accepted') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                            @elseif($q->status === 'sent') bg-sky-500/20 text-sky-400 border border-sky-500/30
                                            @elseif($q->status === 'rejected') bg-rose-500/20 text-rose-400 border border-rose-500/30
                                            @else bg-slate-800 text-slate-400 border border-slate-700 @endif">
                                            {{ ucfirst($q->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 text-right">
                                        <a href="{{ route('quotations.show', $q) }}" class="text-amber-400 hover:underline font-bold">
                                            View Quote &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                        No commercial quotations generated yet for this lead.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Support & Service Tickets Card --}}
            <div class="bg-[#0F172A] rounded-2xl border border-white/10 shadow-xl overflow-hidden">
                <div class="px-6 py-4 bg-[#0B1120] border-b border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🚨</span>
                        <h3 class="text-sm font-extrabold text-white font-heading">Support & Service Tickets</h3>
                    </div>
                    <a href="{{ route('service-tickets.create', ['lead_id' => $lead->id]) }}"
                       class="px-3 py-1 rounded-xl bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs font-bold hover:bg-rose-500/30 transition">
                        + Log Ticket
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#060913]/60 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-white/5">
                            <tr>
                                <th class="px-6 py-3">Ticket #</th>
                                <th class="px-6 py-3">Issue Title</th>
                                <th class="px-6 py-3">Priority</th>
                                <th class="px-6 py-3">Assigned Engineer</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-slate-300">
                            @forelse ($lead->serviceTickets as $t)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="px-6 py-3.5 font-bold font-mono text-amber-400">
                                        <a href="{{ route('service-tickets.show', $t) }}" class="hover:underline">
                                            {{ $t->ticket_no }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <div class="font-bold text-white">{{ $t->title }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $t->issue_type_label }}</div>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                            @if($t->priority === 'critical') bg-rose-500/20 text-rose-400 border border-rose-500/30
                                            @elseif($t->priority === 'high') bg-amber-500/20 text-amber-400 border border-amber-500/30
                                            @else bg-slate-800 text-slate-400 border border-slate-700 @endif">
                                            {{ $t->priority }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5">{{ $t->assignedTechnician->name ?? 'Unassigned' }}</td>
                                    <td class="px-6 py-3.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                            @if($t->status === 'resolved' || $t->status === 'closed') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                            @elseif($t->status === 'in_progress') bg-amber-500/20 text-amber-400 border border-amber-500/30
                                            @elseif($t->status === 'assigned') bg-sky-500/20 text-sky-400 border border-sky-500/30
                                            @else bg-rose-500/20 text-rose-400 border border-rose-500/30 @endif">
                                            {{ $t->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 text-right">
                                        <a href="{{ route('service-tickets.show', $t) }}" class="text-amber-400 hover:underline font-bold">
                                            Details &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                        No support tickets recorded for this customer.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Installed CCTV Hardware & Serial Register Card --}}
            <div class="bg-[#0F172A] rounded-2xl border border-white/10 shadow-xl overflow-hidden">
                <div class="px-6 py-4 bg-[#0B1120] border-b border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-base">📹</span>
                        <h3 class="text-sm font-extrabold text-white font-heading">Installed CCTV Hardware & Serial Numbers</h3>
                    </div>
                    <a href="{{ route('equipment.create', ['lead_id' => $lead->id, 'redirect_to' => url()->current()]) }}"
                       class="px-3 py-1 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold hover:bg-emerald-500/30 transition">
                        + Add Asset
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#060913]/60 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-white/5">
                            <tr>
                                <th class="px-6 py-3">Serial Number</th>
                                <th class="px-6 py-3">Equipment / Camera Model</th>
                                <th class="px-6 py-3">Location Tag</th>
                                <th class="px-6 py-3">Mfg Warranty</th>
                                <th class="px-6 py-3">Service Warranty</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-slate-300">
                            @forelse ($lead->installedEquipment as $item)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="px-6 py-3.5 font-mono font-bold text-amber-400">
                                        <a href="{{ route('equipment.show', $item) }}" class="hover:underline">
                                            {{ $item->serial_number }}
                                        </a>
                                        @if($item->mac_address)
                                            <div class="text-[10px] text-slate-500 font-mono">{{ $item->mac_address }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <div class="font-bold text-white">{{ $item->equipment_name }}</div>
                                        @if($item->product)
                                            <div class="text-[11px] text-slate-400">{{ $item->product->brand }} {{ $item->product->model_no }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 text-[11px] font-bold border border-slate-700">
                                            📍 {{ $item->location_tag ?: 'Main Site' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        @if($item->mfg_warranty_status === 'active')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                                ● Active ({{ $item->mfg_days_remaining }}d)
                                            </span>
                                        @elseif($item->mfg_warranty_status === 'expiring_soon')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                                ▲ Expiring ({{ $item->mfg_days_remaining }}d)
                                            </span>
                                        @elseif($item->mfg_warranty_status === 'expired')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                                ✕ Expired
                                            </span>
                                        @else
                                            <span class="text-slate-500">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5">
                                        @if($item->service_warranty_status === 'active')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                                ● Active ({{ $item->service_days_remaining }}d)
                                            </span>
                                        @elseif($item->service_warranty_status === 'expiring_soon')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                                ▲ Expiring ({{ $item->service_days_remaining }}d)
                                            </span>
                                        @elseif($item->service_warranty_status === 'expired')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                                ✕ Expired
                                            </span>
                                        @else
                                            <span class="text-slate-500">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-300 border border-slate-700">
                                            {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 text-right">
                                        <a href="{{ route('equipment.show', $item) }}" class="text-amber-400 hover:underline font-bold">
                                            Asset &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                        No installed equipment or camera serial numbers registered for this client premises.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pt-2">
                <a href="{{ route('leads.index') }}" class="text-xs text-slate-400 hover:text-white transition flex items-center gap-1">
                    <span>← Back to Leads Pipeline</span>
                </a>
            </div>

        </div>
    </div>
</x-app-layout>