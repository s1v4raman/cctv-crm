<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold leading-tight text-white tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-[0_0_10px_#f59e0b]"></span>
                    Digital Job Completion Reports (JCR)
                </h2>
                <p class="mt-1 text-sm text-slate-400 font-medium">Customer e-signatures, quality inspection audits & official handover certificates</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('jobs.index') }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-white/10 bg-[#0f172a] hover:bg-slate-800 text-slate-300 text-xs font-bold shadow-sm transition">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Installation Jobs
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#060913] min-h-screen font-['Plus_Jakarta_Sans']">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-950/40 border border-emerald-500/30 text-emerald-300 text-sm font-medium flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-white/5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Total Signed JCRs</span>
                        <div class="text-2xl font-extrabold text-white mt-1 font-['Outfit']">{{ $reports->total() }}</div>
                        <span class="text-[11px] text-slate-500 font-medium">Official handovers</span>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20 flex items-center justify-center text-lg font-bold">
                        📜
                    </div>
                </div>
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-white/5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400 block">Avg Customer Rating</span>
                        <div class="text-2xl font-extrabold text-amber-400 mt-1 font-['Outfit']">4.9 <span class="text-lg">★</span></div>
                        <span class="text-[11px] text-slate-500 font-medium">Customer satisfaction</span>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center text-lg font-bold">
                        ⭐
                    </div>
                </div>
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-white/5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400 block">QA Pass Rate</span>
                        <div class="text-2xl font-extrabold text-emerald-400 mt-1 font-['Outfit']">100%</div>
                        <span class="text-[11px] text-slate-500 font-medium">Checklist compliance</span>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-lg font-bold">
                        ✅
                    </div>
                </div>
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-white/5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-purple-400 block">Active Technicians</span>
                        <div class="text-2xl font-extrabold text-purple-400 mt-1 font-['Outfit']">{{ $technicians->count() }}</div>
                        <span class="text-[11px] text-slate-500 font-medium">Certified engineers</span>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20 flex items-center justify-center text-lg font-bold">
                        👷
                    </div>
                </div>
            </div>

            {{-- Search & Filters --}}
            <div class="bg-[#0f172a] rounded-2xl p-4 mb-6 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5">
                <form method="GET" action="{{ route('jcr.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">Search Keyword</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="JCR No, Client Name, Phone..." class="w-full text-xs rounded-xl bg-[#060913] border-slate-700 text-slate-200 focus:border-amber-400 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">Work Type</label>
                        <select name="type" class="w-full text-xs rounded-xl bg-[#060913] border-slate-700 text-slate-200 focus:border-amber-400 focus:ring-amber-400">
                            <option value="">All Types</option>
                            <option value="installation" @selected(request('type') === 'installation')>New Installation</option>
                            <option value="service" @selected(request('type') === 'service')>Service Ticket</option>
                            <option value="amc" @selected(request('type') === 'amc')>AMC Servicing</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">Technician</label>
                        <select name="technician_id" class="w-full text-xs rounded-xl bg-[#060913] border-slate-700 text-slate-200 focus:border-amber-400 focus:ring-amber-400">
                            <option value="">All Technicians</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" @selected(request('technician_id') == $tech->id)>{{ $tech->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 py-2 px-4 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition shadow-sm">
                            Filter
                        </button>
                        @if(request()->anyFilled(['search', 'type', 'technician_id']))
                            <a href="{{ route('jcr.index') }}" class="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-semibold">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Responsive Clean Table --}}
            <div class="bg-[#0f172a] rounded-2xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5 overflow-hidden">
                <div class="overflow-x-auto w-full">
                    <table class="w-full divide-y divide-white/5 text-xs text-left">
                        <thead class="bg-[#0b1120] border-b border-white/5">
                            <tr>
                                <th class="px-5 py-3.5 font-extrabold text-slate-400 uppercase tracking-wider text-[11px]">Report No</th>
                                <th class="px-5 py-3.5 font-extrabold text-slate-400 uppercase tracking-wider text-[11px]">Customer & Site Location</th>
                                <th class="px-5 py-3.5 font-extrabold text-slate-400 uppercase tracking-wider text-[11px]">Job Type & Ref</th>
                                <th class="px-5 py-3.5 font-extrabold text-slate-400 uppercase tracking-wider text-[11px]">Lead Technician</th>
                                <th class="px-5 py-3.5 font-extrabold text-slate-400 uppercase tracking-wider text-[11px]">Rating & QA Audit</th>
                                <th class="px-5 py-3.5 font-extrabold text-slate-400 uppercase tracking-wider text-[11px]">Date Signed</th>
                                <th class="px-5 py-3.5 font-extrabold text-slate-400 uppercase tracking-wider text-[11px] text-right min-w-[220px]">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 bg-[#0f172a]">
                            @forelse($reports as $report)
                                <tr class="hover:bg-slate-800/50 transition duration-150">
                                    {{-- Report No --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <a href="{{ route('jcr.show', $report) }}" class="inline-flex items-center gap-1.5 font-extrabold text-sky-400 hover:text-sky-300 text-xs">
                                            <span class="px-2 py-0.5 rounded-md bg-sky-500/10 border border-sky-500/20 font-mono text-[11px]">
                                                {{ $report->report_no }}
                                            </span>
                                        </a>
                                    </td>

                                    {{-- Customer / Site --}}
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-slate-200 text-[13px]">{{ $report->lead->customer_name }}</div>
                                        <div class="text-[11px] text-slate-400 flex items-center gap-1 mt-0.5 max-w-xs truncate">
                                            <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                            <span class="truncate">{{ $report->lead->site_address ?? $report->lead->phone }}</span>
                                        </div>
                                    </td>

                                    {{-- Job Type & Ref --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        @php
                                            $typeBadgeClass = match($report->job_type) {
                                                'service' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                                                'amc' => 'bg-teal-500/10 text-teal-400 border-teal-500/30',
                                                default => 'bg-sky-500/10 text-sky-400 border-sky-500/30',
                                            };
                                        @endphp
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $typeBadgeClass }} mb-1">
                                            {{ $report->job_type_label }}
                                        </span>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $report->reference_no }}</div>
                                    </td>

                                    {{-- Technician --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-slate-800 border border-slate-700 text-sky-400 font-bold flex items-center justify-center text-[11px]">
                                                {{ substr($report->technician->name, 0, 1) }}
                                            </div>
                                            <span class="font-bold text-slate-200">{{ $report->technician->name }}</span>
                                        </div>
                                    </td>

                                    {{-- Rating & QA --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="text-amber-400 font-bold text-xs tracking-wider">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span>{{ $i <= $report->customer_rating ? '★' : '☆' }}</span>
                                            @endfor
                                        </div>
                                        <div class="mt-1">
                                            <span class="text-[10px] text-emerald-400 font-extrabold bg-emerald-500/10 border border-emerald-500/30 px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                {{ $report->passed_checklist_count }}/8 QA Passed
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Date Signed --}}
                                    <td class="px-5 py-4 whitespace-nowrap text-slate-400">
                                        <div class="font-semibold text-slate-200">{{ $report->completion_date->format('d M Y') }}</div>
                                        <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>{{ $report->completion_date->format('h:i A') }}</span>
                                        </div>
                                    </td>

                                    {{-- Clear Visible Action Buttons --}}
                                    <td class="px-5 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            {{-- View Details --}}
                                            <a href="{{ route('jcr.show', $report) }}" 
                                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-bold transition shadow-xs border border-white/5" 
                                               title="View Full Report">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                View
                                            </a>

                                            {{-- PDF Download --}}
                                            <a href="{{ route('jcr.download-pdf', $report) }}" 
                                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-sky-500/10 hover:bg-sky-500/20 text-sky-400 border border-sky-500/30 text-[11px] font-bold transition shadow-xs" 
                                               title="Download Signed PDF Certificate">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                                PDF
                                            </a>

                                            {{-- WhatsApp Share --}}
                                            <a href="https://api.whatsapp.com/send?phone={{ preg_replace('/[^0-9]/', '', $report->lead->phone ?? '') }}&text={{ urlencode('Hello ' . ($report->lead->customer_name ?? 'Customer') . ', here is your signed CCTV Job Completion Certificate: ' . route('jcr.public-pdf', $report)) }}" 
                                               target="_blank" 
                                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[11px] font-bold transition shadow-xs" 
                                               title="Send Certificate on WhatsApp">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.458 5.704 1.459h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413"/>
                                                </svg>
                                                WhatsApp
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                                        <div class="text-3xl mb-2">📜</div>
                                        <p class="font-semibold text-slate-400">No Job Completion Reports found.</p>
                                        <p class="text-xs text-slate-500 mt-1">When technicians complete and sign off jobs, digital handover reports will appear here.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-5">
                {{ $reports->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
