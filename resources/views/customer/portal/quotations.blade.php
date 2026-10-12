<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('portal.dashboard') }}" class="text-xs font-semibold text-blue-600 hover:underline">← Back to Overview</a>
                    <span class="text-slate-400">/</span>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Commercial</span>
                </div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight mt-1">Quotations &amp; Estimates Review</h1>
            </div>
            <a href="{{ route('home') }}" 
               class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-[#0f172a] hover:bg-slate-50 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-800 text-xs font-medium rounded-[4px] transition-colors">
                <span>Browse Equipment on Store</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f8fafc] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100" x-data="{ rejectModalOpen: false, selectedQuoteId: null, quoteNo: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            {{-- Alerts --}}
            @if(session('status'))
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-[4px] text-xs font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-3 bg-rose-50 border border-rose-200 rounded-[4px] text-xs font-semibold text-rose-800">
                    {{ session('error') }}
                </div>
            @endif

            @if(!$hasLead || $quotations->isEmpty())
                <div class="bg-white dark:bg-[#0f172a] rounded-[4px] p-8 border border-slate-300 dark:border-slate-800 text-center max-w-2xl mx-auto space-y-3">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">No Quotations Recorded</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">When our technical team prepares a CCTV estimate for your site, it will appear here for review and online approval.</p>
                    <div class="pt-2">
                        <a href="{{ route('home') }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-[4px] transition-colors cursor-pointer">
                            Explore Storefront Products &rarr;
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
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="stat-card">
                        <div class="text-xs font-medium text-slate-500">Total Quotations</div>
                        <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $totalQuotes }}</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">CCTV &amp; AMC Estimates</div>
                    </div>
                    <div class="stat-card">
                        <div class="text-xs font-medium text-slate-500">Awaiting Your Approval</div>
                        <div class="text-xl font-bold text-amber-600 mt-1">{{ $pendingQuotes }}</div>
                        <div class="text-[11px] text-amber-600 mt-0.5">Direct Online Acceptance</div>
                    </div>
                    <div class="stat-card">
                        <div class="text-xs font-medium text-slate-500">Approved &amp; Initiated</div>
                        <div class="text-xl font-bold text-emerald-700 mt-1">{{ $acceptedQuotes }}</div>
                        <div class="text-[11px] text-emerald-700 mt-0.5">Scheduled for Installation</div>
                    </div>
                </div>

                {{-- Quotations Table --}}
                <div class="bg-white dark:bg-[#0f172a] rounded-[4px] border border-slate-300 dark:border-slate-800 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs data-table">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">Quotation No</th>
                                    <th class="px-4 py-3">Created Date</th>
                                    <th class="px-4 py-3">Valid Until</th>
                                    <th class="px-4 py-3">Total Amount</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-center">PDF Proposal</th>
                                    <th class="px-4 py-3 text-right">Approval Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                @foreach($quotations as $quotation)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="font-mono text-xs font-bold text-blue-600">#{{ $quotation->quotation_no }}</div>
                                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $quotation->items->count() }} line item(s)</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                            {{ $quotation->created_at->format('d M Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                            {{ $quotation->valid_until ? $quotation->valid_until->format('d M Y') : '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-bold text-slate-900 dark:text-white">₹{{ number_format((float)$quotation->total, 2) }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-bold text-xs {{ $quotation->status === 'accepted' ? 'text-emerald-700' : ($quotation->status === 'rejected' ? 'text-rose-600' : 'text-blue-600') }}">
                                                {{ ucfirst($quotation->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="{{ route('quotations.public-pdf', ['quotation' => $quotation->id]) }}" target="_blank" 
                                               class="text-blue-600 hover:underline font-medium text-xs">
                                                View PDF
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if(in_array($quotation->status, ['draft', 'sent']))
                                                <div class="flex items-center justify-end gap-2 flex-wrap">
                                                    <a href="{{ route('payment.checkout.quotation', $quotation) }}"
                                                       class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-[4px] transition-colors inline-flex items-center gap-1 cursor-pointer">
                                                        <span>Pay Advance / Cash</span>
                                                    </a>

                                                    <form method="POST" action="{{ route('portal.quotations.accept', $quotation) }}" 
                                                          onsubmit="return confirm('Are you sure you want to approve this CCTV Quotation (#{{ $quotation->quotation_no }})? Our engineering team will proceed with scheduling your project.');">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-[4px] transition-colors">
                                                            Accept
                                                        </button>
                                                    </form>

                                                    <button type="button" 
                                                            @click="rejectModalOpen = true; selectedQuoteId = '{{ $quotation->id }}'; quoteNo = '{{ $quotation->quotation_no }}'"
                                                            class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-[4px] transition-colors">
                                                        Decline
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-slate-400 font-medium text-xs">Completed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($quotations, 'hasPages') && $quotations->hasPages())
                        <div class="p-3 border-t border-slate-200 dark:border-slate-800">
                            {{ $quotations->links() }}
                        </div>
                    @endif
                </div>
            @endif

        </div>

        {{-- Decline Quotation Modal --}}
        <div x-show="rejectModalOpen" 
             style="display:none;" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
            <div @click.away="rejectModalOpen = false" 
                 class="bg-white dark:bg-[#0f172a] rounded-[4px] max-w-md w-full p-5 space-y-4 border border-slate-300 dark:border-slate-800 text-slate-800 dark:text-slate-100">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Decline Quotation <span x-text="'#' + quoteNo" class="font-mono text-rose-600"></span></h3>
                    <button @click="rejectModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">×</button>
                </div>

                <form :action="'/portal/quotations/' + selectedQuoteId + '/reject'" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Reason for Declining (Optional)
                        </label>
                        <textarea name="rejection_reason" rows="3" 
                                  placeholder="e.g. Budget constraints, need changes in camera count..."
                                  class="w-full p-2.5 rounded-[4px] border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-xs focus:border-rose-600 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="rejectModalOpen = false" 
                                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-[4px] border border-slate-300 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-[4px] transition-colors">
                            Confirm Decline
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
