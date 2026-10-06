<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('portal.dashboard') }}" class="text-xs font-semibold hover:underline" style="color: var(--crm-accent, #2563eb);">← Back to Overview</a>
                    <span class="text-slate-400">/</span>
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Annual Maintenance</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight mt-1 font-heading">AMC Contracts & Preventive Maintenance</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Track routine CCTV camera servicing visits, technician audit reports, and contract validity.</p>
            </div>
            
            {{-- Action Button (Opens Renewal Modal - Dynamic Accent) --}}
            <button type="button" 
                    onclick="document.getElementById('amcRenewalModal').classList.remove('hidden')"
                    class="crm-customer-action-btn inline-flex items-center gap-2 px-4 py-2 text-white text-xs font-bold rounded-xl shadow-xs transition-all duration-200 cursor-pointer"
                    style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(37,99,235,0.35));">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Request AMC Renewal / Plan</span>
            </button>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f8fafc] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Alerts --}}
            @if(session('status'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl flex items-center gap-3 shadow-xs">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-bold text-sm">✓</div>
                    <div class="text-xs font-semibold text-emerald-900 dark:text-emerald-200">{{ session('status') }}</div>
                </div>
            @endif

            @if(!$hasLead || $contracts->isEmpty())
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-10 border border-slate-200/90 dark:border-slate-800 shadow-xs text-center max-w-2xl mx-auto space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50 flex items-center justify-center mx-auto font-bold text-2xl">
                        🛡️
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading">No Active AMC Contract on File</h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                        An Annual Maintenance Contract (AMC) keeps your security surveillance operational 24/7 with scheduled quarterly cleaning, DVR recording audits, angle adjustment, and zero-labor breakdown visits.
                    </p>
                    <div class="pt-2">
                        <button type="button" 
                                onclick="document.getElementById('amcRenewalModal').classList.remove('hidden')"
                                class="crm-customer-action-btn px-6 py-2.5 text-white font-bold text-xs rounded-xl shadow-xs transition-all duration-200 cursor-pointer"
                                style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(37,99,235,0.35));">
                            Request AMC Plan Quote Now →
                        </button>
                    </div>
                </div>
            @else

                {{-- Active Contract Highlight --}}
                @if($activeAmc)
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 p-6 shadow-xs">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2.5">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 uppercase tracking-wider">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Active Protection
                                    </span>
                                    <span class="font-mono text-sm font-bold" style="color: var(--crm-accent, #2563eb);">#{{ $activeAmc->contract_no }}</span>
                                </div>
                                <h2 class="text-2xl font-bold text-slate-900 dark:text-white font-heading">
                                    {{ ucfirst(str_replace('_', ' ', $activeAmc->frequency)) }} Maintenance Coverage
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                    Contract Term: <strong class="text-slate-800 dark:text-slate-200">{{ \Carbon\Carbon::parse($activeAmc->start_date)->format('d M Y') }}</strong> to <strong class="text-slate-800 dark:text-slate-200">{{ \Carbon\Carbon::parse($activeAmc->end_date)->format('d M Y') }}</strong>
                                </p>
                            </div>

                            <div class="flex items-center gap-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                                <div>
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Scheduled Servicing Cycles</div>
                                    <div class="text-2xl font-black text-slate-900 dark:text-white font-heading mt-0.5">{{ $activeAmc->visits->count() }} Visits / Year</div>
                                    <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 mt-1">
                                        {{ $activeAmc->visits->where('status', 'completed')->count() }} Completed • {{ $activeAmc->visits->where('status', 'pending')->count() }} Pending
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Quarterly Visual Progress Tracker --}}
                        @if($activeAmc->visits->isNotEmpty())
                            <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
                                <div class="text-xs font-bold uppercase tracking-wider mb-4" style="color: var(--crm-accent, #2563eb);">Quarterly Servicing Timeline</div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                    @foreach($activeAmc->visits->sortBy('scheduled_date') as $visit)
                                        <div class="p-4 rounded-xl border {{ $visit->status === 'completed' ? 'bg-emerald-50/50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800/80' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700' }}">
                                            <div class="flex items-center justify-between text-xs">
                                                <span class="font-bold text-slate-900 dark:text-white">Q{{ $loop->iteration }} Routine Check</span>
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $visit->status === 'completed' ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800' }}">
                                                    {{ $visit->status === 'completed' ? '✓ Completed' : 'Pending' }}
                                                </span>
                                            </div>
                                            <div class="text-xs font-bold mt-2 text-slate-800 dark:text-white">
                                                📅 {{ \Carbon\Carbon::parse($visit->scheduled_date)->format('d M Y') }}
                                            </div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                                {{ $visit->assignedTechnician ? 'Engineer: ' . $visit->assignedTechnician->name : 'Engineer: Auto-assigned on schedule' }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Contracts & Visits History --}}
                @foreach($contracts as $contract)
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden space-y-4">
                        <div class="px-6 py-4 border-b border-slate-200/90 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading">Contract #{{ $contract->contract_no }}</h3>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $contract->status === 'active' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700' }}">
                                        {{ $contract->status }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($contract->start_date)->format('d M Y') }} — {{ \Carbon\Carbon::parse($contract->end_date)->format('d M Y') }} • Frequency: {{ ucfirst(str_replace('_', ' ', $contract->frequency)) }}
                                </p>
                            </div>
                        </div>

                        <div class="px-6 pb-6">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Servicing & Routine Visit Schedule</h4>
                            
                            @if($contract->visits->isEmpty())
                                <p class="text-xs text-slate-400 italic">No visit logs generated for this contract.</p>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    @foreach($contract->visits->sortBy('scheduled_date') as $visit)
                                        <div class="p-4 rounded-xl border {{ $visit->status === 'completed' ? 'bg-emerald-50/40 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800/80' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700' }} space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-bold text-slate-900 dark:text-white">
                                                    Visit {{ $loop->iteration }}
                                                </span>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $visit->status === 'completed' ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800' }}">
                                                    {{ $visit->status === 'completed' ? '✓ Completed' : 'Pending' }}
                                                </span>
                                            </div>
                                            
                                            <div class="text-xs font-bold text-slate-800 dark:text-white">
                                                📅 {{ \Carbon\Carbon::parse($visit->scheduled_date)->format('d M Y') }}
                                            </div>

                                            @if($visit->assignedTechnician)
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                                    Engineer: <strong class="text-slate-700 dark:text-slate-200">{{ $visit->assignedTechnician->name }}</strong>
                                                </div>
                                            @endif

                                            @if($visit->status === 'completed' && $visit->completion_notes)
                                                <div class="text-[11px] text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                                    <span class="font-bold text-emerald-600 dark:text-emerald-400">Notes:</span> {{ $visit->completion_notes }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

            @endif

        </div>
    </div>

    {{-- AMC Renewal / Custom Plan Request Modal --}}
    <div id="amcRenewalModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-4 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-100">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xl border border-emerald-200 dark:border-emerald-800">
                        🛡️
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white font-heading">Request AMC Maintenance Plan</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Comprehensive preventive CCTV servicing contract.</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('amcRenewalModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-2xl font-bold">×</button>
            </div>

            <form method="POST" action="{{ route('portal.amc.renew') }}" class="space-y-4">
                @csrf
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    Our technical desk will prepare a tailored Annual Maintenance quotation covering all camera cleaning, connector waterproofing, and quarterly DVR health audits.
                </p>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Additional Requirements / Notes (Optional)
                    </label>
                    <textarea name="notes" rows="3" 
                              placeholder="e.g. Include 4 new outdoor cameras in renewal, need weekend visit slots..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-xs font-medium focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" onclick="document.getElementById('amcRenewalModal').classList.add('hidden')" 
                            class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="crm-customer-action-btn px-5 py-2.5 text-white text-xs font-bold rounded-xl shadow-xs transition-all duration-200 cursor-pointer"
                            style="background-color: var(--crm-accent, #2563eb); border: 1px solid var(--crm-accent, #2563eb); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(37,99,235,0.35));">
                        Submit Renewal Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
