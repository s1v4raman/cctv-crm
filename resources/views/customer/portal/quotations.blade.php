<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('portal.dashboard') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">← Back to Overview</a>
                    <span class="text-slate-400">/</span>
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Commercial</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight mt-1 font-heading">Quotations & Estimates Review</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Review project proposals, inspect camera item breakdowns, and accept or decline quotes online.</p>
            </div>
            <a href="{{ route('home') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-white dark:bg-[#0f172a] hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/90 dark:border-slate-800 text-xs font-semibold rounded-xl shadow-xs transition-colors">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span>Browse Equipment on Store</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f8fafc] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100 transition-colors duration-200" x-data="{ rejectModalOpen: false, selectedQuoteId: null, quoteNo: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Alerts --}}
            @if(session('status'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl flex items-center gap-3 shadow-xs">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-bold text-sm">✓</div>
                    <div class="text-xs font-semibold text-emerald-900 dark:text-emerald-200">{{ session('status') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/80 rounded-2xl flex items-center gap-3 shadow-xs">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 flex items-center justify-center font-bold text-sm">!</div>
                    <div class="text-xs font-semibold text-rose-900 dark:text-rose-200">{{ session('error') }}</div>
                </div>
            @endif

            @if(!$hasLead || $quotations->isEmpty())
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-10 border border-slate-200/90 dark:border-slate-800 shadow-xs text-center max-w-2xl mx-auto space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 flex items-center justify-center mx-auto mb-3 font-bold text-2xl">
                        📋
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white font-heading">No Quotations Recorded</h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">When our technical team prepares a CCTV estimate for your site, it will appear here for your interactive review and online approval.</p>
                    <div class="pt-2">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#2563eb] hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                            Explore Storefront Products →
                        </a>
                    </div>
                </div>
            @else
                {{-- KPI Metric Summary Cards --}}
                @php
                    $totalQuotes = $quotations->count();
                    $pendingQuotes = $quotations->filter(fn($q) => in_array($q->status, ['draft', 'sent']))->count();
                    $acceptedQuotes = $quotations->filter(fn($q) => $q->status === 'accepted')->count();
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Quotations</div>
                        <div class="text-2xl font-black text-slate-900 dark:text-white font-heading mt-1">{{ $totalQuotes }} <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">Proposals</span></div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-1">CCTV & AMC Estimates</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Awaiting Your Approval</div>
                        <div class="text-2xl font-black text-amber-600 dark:text-amber-400 font-heading mt-1">{{ $pendingQuotes }} <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">Pending</span></div>
                        <div class="text-[11px] text-amber-600 dark:text-amber-400 font-medium mt-1">● Direct Online Acceptance</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Approved & Initiated</div>
                        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-heading mt-1">{{ $acceptedQuotes }} <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">Approved</span></div>
                        <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium mt-1">✓ Scheduled for Installation</div>
                    </div>
                </div>

                {{-- Quotations Table --}}
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-6 py-3.5">Quotation No</th>
                                    <th class="px-6 py-3.5">Created Date</th>
                                    <th class="px-6 py-3.5">Valid Until</th>
                                    <th class="px-6 py-3.5">Total Amount</th>
                                    <th class="px-6 py-3.5">Status</th>
                                    <th class="px-6 py-3.5 text-center">PDF Proposal</th>
                                    <th class="px-6 py-3.5 text-right">Approval Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium text-slate-700 dark:text-slate-300">
                                @foreach($quotations as $quotation)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                        <td class="px-6 py-4">
                                            <div class="font-mono text-xs font-bold text-blue-600 dark:text-blue-400">#{{ $quotation->quotation_no }}</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $quotation->items->count() }} line item(s)</div>
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                            {{ $quotation->created_at->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                            {{ $quotation->valid_until ? \Carbon\Carbon::parse($quotation->valid_until)->format('d M Y') : '30 Days' }}
                                        </td>
                                        <td class="px-6 py-4 font-bold text-slate-900 dark:text-white text-sm">
                                            ₹{{ number_format((float) $quotation->total, 2) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($quotation->status === 'accepted')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                    ✓ Approved
                                                </span>
                                            @elseif($quotation->status === 'rejected')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                    ✕ Declined
                                                </span>
                                            @elseif($quotation->status === 'expired')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                    Expired
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                    ● Awaiting Your Review
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="{{ route('quotations.public-pdf', ['quotation' => $quotation->id]) }}" target="_blank" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-semibold rounded-xl transition">
                                                <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                <span>View PDF</span>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            @if(in_array($quotation->status, ['draft', 'sent']))
                                                <div class="flex items-center justify-end gap-2 flex-wrap">
                                                    <a href="{{ route('payment.checkout.quotation', $quotation) }}"
                                                       class="px-3.5 py-1.5 bg-[#2563eb] hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition inline-flex items-center gap-1"
                                                       title="Pay 50% Advance deposit, full total, or book with Cash on Installation">
                                                        <span>💳 Pay Advance / Cash</span>
                                                    </a>

                                                    <form method="POST" action="{{ route('portal.quotations.accept', $quotation) }}" 
                                                          onsubmit="return confirm('Are you sure you want to approve this CCTV Quotation (#{{ $quotation->quotation_no }})? Our engineering team will proceed with scheduling your project.');">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                                            ✓ Accept
                                                        </button>
                                                    </form>

                                                    <button type="button" 
                                                            @click="rejectModalOpen = true; selectedQuoteId = '{{ $quotation->id }}'; quoteNo = '{{ $quotation->quotation_no }}'"
                                                            class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                                        ✕
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-slate-400 dark:text-slate-500 font-semibold text-xs">Completed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($quotations, 'hasPages') && $quotations->hasPages())
                        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                            {{ $quotations->links() }}
                        </div>
                    @endif
                </div>
            @endif

        </div>

        {{-- Decline Quotation Modal --}}
        <div x-show="rejectModalOpen" 
             style="display:none;" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="rejectModalOpen = false" 
                 class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-100">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading">Decline Quotation <span x-text="'#' + quoteNo" class="font-mono text-rose-600 dark:text-rose-400"></span></h3>
                    <button @click="rejectModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xl font-bold">×</button>
                </div>

                <form :action="'/portal/quotations/' + selectedQuoteId + '/reject'" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Feedback / Reason for Declining (Optional)
                        </label>
                        <textarea name="rejection_reason" rows="3" 
                                  placeholder="e.g. Budget constraints, need changes in camera count, pricing adjustment..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-xs font-medium focus:border-rose-600 focus:ring-2 focus:ring-rose-500/20 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="rejectModalOpen = false" 
                                class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 transition">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                            Confirm Decline
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
