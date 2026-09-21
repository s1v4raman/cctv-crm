<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('portal.dashboard') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">← Back to Overview</a>
                    <span class="text-slate-400">/</span>
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Billing</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight mt-1 font-heading">Invoices & Payment Receipts</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Download GST tax invoices, review payment history, pay outstanding balances online.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f8fafc] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- If Pending Quotations exist, show Quotation Payment Card --}}
            @if(isset($pendingQuotations) && $pendingQuotations->isNotEmpty())
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-blue-100 dark:border-blue-900/40 p-6 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 px-2.5 py-1 rounded-full">
                                🛡️ Active Quotations · Advance & Full Payment
                            </span>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white font-heading mt-2">
                                Quotations Awaiting Advance / Full Cash Booking
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                You can pay the 50% advance deposit online, full project amount, or choose Cash on Site delivery.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        @foreach($pendingQuotations as $quote)
                            @php
                                $adv = round((float)$quote->total * 0.50, 2);
                            @endphp
                            <div class="bg-slate-50 dark:bg-slate-800/80 rounded-xl p-4 border border-slate-200 dark:border-slate-700 flex flex-col justify-between gap-3">
                                <div>
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400">#{{ $quote->quotation_no }}</span>
                                            <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-0.5">CCTV Installation Quotation</h3>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $quote->status === 'accepted' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800' }}">
                                            {{ ucfirst($quote->status) }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-slate-200 dark:border-slate-700 text-xs">
                                        <div>
                                            <span class="text-slate-500 dark:text-slate-400 text-[11px] block">Total Valuation:</span>
                                            <strong class="text-slate-800 dark:text-slate-200">₹{{ number_format((float)$quote->total, 2) }}</strong>
                                        </div>
                                        <div>
                                            <span class="text-slate-500 dark:text-slate-400 text-[11px] block">50% Advance Due:</span>
                                            <strong class="text-emerald-600 dark:text-emerald-400">₹{{ number_format($adv, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 pt-2 border-t border-slate-200 dark:border-slate-700 flex-wrap">
                                    <a href="{{ route('payment.checkout.quotation', $quote) }}"
                                       class="flex-1 min-w-[140px] px-3.5 py-2 bg-[#2563eb] hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs text-center transition">
                                        💳 Pay Advance / Cash →
                                    </a>
                                    <a href="{{ route('quotations.public-pdf', ['quotation' => $quote->id]) }}" target="_blank"
                                       class="px-3 py-2 bg-white dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 font-semibold text-xs rounded-xl transition">
                                        📄 View PDF
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!$hasLead || $invoices->isEmpty())
                @if(!isset($pendingQuotations) || $pendingQuotations->isEmpty())
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-10 border border-slate-200/90 dark:border-slate-800 shadow-xs text-center max-w-2xl mx-auto space-y-4">
                        <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 flex items-center justify-center mx-auto mb-3 font-bold text-2xl">
                            🧾
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white font-heading">No Invoices Issued Yet</h3>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">When an installation project, spare parts order, or AMC contract is billed, your official GST tax invoices and payment receipts will appear here.</p>
                        <div class="pt-2">
                            <a href="{{ route('portal.quotations') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#2563eb] hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                Review CCTV Quotations →
                            </a>
                        </div>
                    </div>
                @endif
            @else
                {{-- KPI Metric Summary Cards --}}
                @php
                    $totalInvoiced = $invoices->sum(fn($i) => (float)$i->total);
                    $totalPaid = $invoices->sum(fn($i) => (float)$i->amount_paid);
                    $totalBalance = $invoices->sum(fn($i) => $i->balanceDue());
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Billed</div>
                        <div class="text-2xl font-black text-slate-900 dark:text-white font-heading mt-1">₹{{ number_format($totalInvoiced, 2) }}</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-1">{{ $invoices->count() }} Issued Invoices</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Paid to Date</div>
                        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-heading mt-1">₹{{ number_format($totalPaid, 2) }}</div>
                        <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium mt-1">✓ Verified Received</div>
                    </div>
                    <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-xs">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Outstanding Balance</div>
                        <div class="text-2xl font-black {{ $totalBalance > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-800 dark:text-slate-200' }} font-heading mt-1">₹{{ number_format($totalBalance, 2) }}</div>
                        <div class="text-[11px] {{ $totalBalance > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }} font-bold mt-1">
                            {{ $totalBalance > 0 ? '● Payment Pending' : '✓ All Invoices Settled' }}
                        </div>
                    </div>
                </div>

                {{-- Invoices Table Card --}}
                <div class="bg-white dark:bg-[#0f172a] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-6 py-3.5">Invoice No</th>
                                    <th class="px-6 py-3.5">Date & Due Date</th>
                                    <th class="px-6 py-3.5">Total Amount</th>
                                    <th class="px-6 py-3.5">Paid</th>
                                    <th class="px-6 py-3.5">Balance Due</th>
                                    <th class="px-6 py-3.5">Payment Status</th>
                                    <th class="px-6 py-3.5 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium text-slate-700 dark:text-slate-300">
                                @foreach($invoices as $invoice)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                        <td class="px-6 py-4">
                                            <div class="font-mono text-xs font-bold text-blue-600 dark:text-blue-400">{{ $invoice->invoice_no }}</div>
                                            @if($invoice->quotation)
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 font-mono">Quote #{{ $invoice->quotation->quotation_no }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</div>
                                            @if($invoice->due_date)
                                                <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Due: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 font-bold text-slate-900 dark:text-white text-sm">
                                            ₹{{ number_format((float) $invoice->total, 2) }}
                                        </td>
                                        <td class="px-6 py-4 font-bold text-emerald-600 dark:text-emerald-400">
                                            ₹{{ number_format((float) $invoice->amount_paid, 2) }}
                                        </td>
                                        <td class="px-6 py-4 font-bold {{ $invoice->balanceDue() > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400 dark:text-slate-500' }}">
                                            ₹{{ number_format($invoice->balanceDue(), 2) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($invoice->status === 'paid')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                    ✓ Paid in Full
                                                </span>
                                            @elseif($invoice->status === 'partially_paid')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                    Partially Paid
                                                </span>
                                            @elseif($invoice->status === 'overdue')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                    Overdue
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                    Unpaid
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2 flex-wrap">
                                                @if($invoice->balanceDue() > 0)
                                                    <a href="{{ route('payment.checkout.invoice', $invoice) }}"
                                                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#2563eb] hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                                        <span>💳 Pay Online</span>
                                                        <span>→</span>
                                                    </a>
                                                @endif

                                                @if($invoice->payments->isNotEmpty())
                                                    @php $latestPayment = $invoice->payments->last(); @endphp
                                                    <a href="{{ route('payments.receipt.pdf', $latestPayment) }}" target="_blank"
                                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 font-bold text-xs rounded-xl transition">
                                                        <span>📄 Receipt</span>
                                                    </a>
                                                @endif

                                                @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                                                    <a href="{{ route('invoices.show', $invoice) }}" 
                                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-xl border border-slate-200 dark:border-slate-700 transition">
                                                        <span>View Details</span>
                                                    </a>
                                                @elseif($invoice->quotation)
                                                    <a href="{{ route('quotations.public-pdf', ['quotation' => $invoice->quotation->id]) }}" target="_blank"
                                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-xl border border-slate-200 dark:border-slate-700 transition">
                                                        <span>Bill PDF</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($invoices, 'hasPages') && $invoices->hasPages())
                        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                            {{ $invoices->links() }}
                        </div>
                    @endif
                </div>
            @endif

            {{-- 4-in-1 Supported Payment Methods Box --}}
            <div class="bg-white dark:bg-[#0f172a] border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 block">
                        💳 Accepted Payment Methods
                    </span>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white font-heading mt-1">
                        How You Can Pay Your Quotations & Invoices
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Choose whichever method is most convenient for your home or business.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-2">
                    {{-- Method 1: Instant Online Gateway --}}
                    <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-xl border border-slate-200 dark:border-slate-700 space-y-1.5">
                        <div class="text-xl">⚡</div>
                        <h4 class="text-xs font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wide">1. Instant Online</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                            Google Pay, PhonePe, Paytm, all Credit/Debit Cards & NetBanking (50+ banks) via Razorpay.
                        </p>
                    </div>

                    {{-- Method 2: Direct UPI QR --}}
                    <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-xl border border-slate-200 dark:border-slate-700 space-y-1.5">
                        <div class="text-xl">📱</div>
                        <h4 class="text-xs font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wide">2. Direct UPI QR</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                            Scan dynamic QR code directly with any UPI app on mobile or desktop for instant clearance.
                        </p>
                    </div>

                    {{-- Method 3: Pay by Cash on Site --}}
                    <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-xl border border-slate-200 dark:border-slate-700 space-y-1.5">
                        <div class="text-xl">💵</div>
                        <h4 class="text-xs font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wide">3. Cash on Installation</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                            Pay cash directly to our certified installation technician at your site upon project arrival/handover.
                        </p>
                    </div>

                    {{-- Method 4: Electronic Bank Transfer --}}
                    <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-xl border border-slate-200 dark:border-slate-700 space-y-1.5">
                        <div class="text-xl">🏛️</div>
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wide">4. Bank NEFT / RTGS</h4>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono leading-tight">
                            A/C: <span class="text-blue-600 dark:text-blue-400 font-bold">50200012345678</span><br>
                            IFSC: <span class="text-blue-600 dark:text-blue-400 font-bold">HDFC0001234</span><br>
                            HDFC Bank Limited
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
