<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('portal.dashboard') }}" class="text-xs font-semibold text-blue-600 hover:underline">← Back to Overview</a>
                    <span class="text-slate-400">/</span>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Billing</span>
                </div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight mt-1">Invoices &amp; Payment Receipts</h1>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-[#f8fafc] dark:bg-[#060913] min-h-screen text-slate-800 dark:text-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            {{-- If Pending Quotations exist, show Quotation Payment Card --}}
            @if(isset($pendingQuotations) && $pendingQuotations->isNotEmpty())
                <div class="bg-white dark:bg-[#0f172a] rounded-[4px] border border-slate-300 dark:border-slate-800 p-5 space-y-3">
                    <div>
                        <span class="text-xs font-bold text-blue-600 uppercase tracking-wider block">
                            Active Quotations · Advance &amp; Full Payment
                        </span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white mt-1">
                            Quotations Awaiting Advance / Full Payment
                        </h2>
                        <p class="text-xs text-slate-500">
                            You can pay the 50% advance deposit online, full project amount, or choose Cash on Site delivery.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        @foreach($pendingQuotations as $quote)
                            @php
                                $adv = round((float)$quote->total * 0.50, 2);
                            @endphp
                            <div class="bg-slate-50 dark:bg-slate-800/80 rounded-[4px] p-4 border border-slate-200 dark:border-slate-700 flex flex-col justify-between gap-3">
                                <div>
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="text-xs font-mono font-bold text-blue-600">#{{ $quote->quotation_no }}</span>
                                            <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-0.5">CCTV Installation Quotation</h3>
                                        </div>
                                        <span class="text-xs font-bold {{ $quote->status === 'accepted' ? 'text-emerald-700' : 'text-blue-600' }}">
                                            {{ ucfirst($quote->status) }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-slate-200 dark:border-slate-700 text-xs">
                                        <div>
                                            <span class="text-slate-500 text-[11px] block">Total Valuation:</span>
                                            <strong class="text-slate-900 dark:text-slate-200">₹{{ number_format((float)$quote->total, 2) }}</strong>
                                        </div>
                                        <div>
                                            <span class="text-slate-500 text-[11px] block">50% Advance Due:</span>
                                            <strong class="text-emerald-700">₹{{ number_format($adv, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 pt-2 border-t border-slate-200 dark:border-slate-700 flex-wrap">
                                    <a href="{{ route('payment.checkout.quotation', $quote) }}"
                                       class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-[4px] transition-colors cursor-pointer">
                                        Pay Advance / Cash &rarr;
                                    </a>
                                    <a href="{{ route('quotations.public-pdf', ['quotation' => $quote->id]) }}" target="_blank"
                                       class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-medium text-xs rounded-[4px] transition-colors">
                                        View PDF
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!$hasLead || $invoices->isEmpty())
                @if(!isset($pendingQuotations) || $pendingQuotations->isEmpty())
                    <div class="bg-white dark:bg-[#0f172a] rounded-[4px] p-8 border border-slate-300 dark:border-slate-800 text-center max-w-2xl mx-auto space-y-3">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">No Invoices Issued Yet</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">When an installation project, spare parts order, or AMC contract is billed, your tax invoices and payment receipts will appear here.</p>
                        <div class="pt-2">
                            <a href="{{ route('portal.quotations') }}" 
                               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-[4px] transition-colors cursor-pointer">
                                Review CCTV Quotations &rarr;
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
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="stat-card">
                        <div class="text-xs font-medium text-slate-500">Total Billed</div>
                        <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">₹{{ number_format($totalInvoiced, 2) }}</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">{{ $invoices->count() }} Issued Invoices</div>
                    </div>
                    <div class="stat-card">
                        <div class="text-xs font-medium text-slate-500">Total Paid to Date</div>
                        <div class="text-xl font-bold text-emerald-700 mt-1">₹{{ number_format($totalPaid, 2) }}</div>
                        <div class="text-[11px] text-emerald-700 mt-0.5">Verified Received</div>
                    </div>
                    <div class="stat-card">
                        <div class="text-xs font-medium text-slate-500">Outstanding Balance</div>
                        <div class="text-xl font-bold {{ $totalBalance > 0 ? 'text-amber-600' : 'text-slate-900 dark:text-slate-200' }} mt-1">₹{{ number_format($totalBalance, 2) }}</div>
                        <div class="text-[11px] {{ $totalBalance > 0 ? 'text-amber-600' : 'text-emerald-700' }} font-bold mt-0.5">
                            {{ $totalBalance > 0 ? 'Payment Pending' : 'All Invoices Settled' }}
                        </div>
                    </div>
                </div>

                {{-- Invoices Table Card --}}
                <div class="bg-white dark:bg-[#0f172a] rounded-[4px] border border-slate-300 dark:border-slate-800 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs data-table">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">Invoice No</th>
                                    <th class="px-4 py-3">Date &amp; Due Date</th>
                                    <th class="px-4 py-3">Total Amount</th>
                                    <th class="px-4 py-3">Paid</th>
                                    <th class="px-4 py-3">Balance Due</th>
                                    <th class="px-4 py-3">Payment Status</th>
                                    <th class="px-4 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                @foreach($invoices as $invoice)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="font-mono text-xs font-bold text-blue-600">{{ $invoice->invoice_no }}</div>
                                            @if($invoice->quotation)
                                                <div class="text-[11px] text-slate-500 mt-0.5 font-mono">Quote #{{ $invoice->quotation->quotation_no }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</div>
                                            @if($invoice->due_date)
                                                <div class="text-[10px] text-slate-500 mt-0.5">Due: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 font-bold text-slate-900 dark:text-white text-sm">
                                            ₹{{ number_format((float) $invoice->total, 2) }}
                                        </td>
                                        <td class="px-4 py-3 font-bold text-emerald-700">
                                            ₹{{ number_format((float) $invoice->amount_paid, 2) }}
                                        </td>
                                        <td class="px-4 py-3 font-bold {{ $invoice->balanceDue() > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                            ₹{{ number_format($invoice->balanceDue(), 2) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($invoice->status === 'paid')
                                                <span class="text-xs font-bold text-emerald-700">
                                                    Paid in Full
                                                </span>
                                            @elseif($invoice->status === 'partially_paid')
                                                <span class="text-xs font-bold text-amber-600">
                                                    Partially Paid
                                                </span>
                                            @elseif($invoice->status === 'overdue')
                                                <span class="text-xs font-bold text-rose-600">
                                                    Overdue
                                                </span>
                                            @else
                                                <span class="text-xs font-bold text-slate-600">
                                                    Unpaid
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-2 flex-wrap">
                                                @if($invoice->balanceDue() > 0)
                                                    <a href="{{ route('payment.checkout.invoice', $invoice) }}"
                                                       class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-[4px] transition-colors cursor-pointer">
                                                        <span>Pay Online &rarr;</span>
                                                    </a>
                                                @endif

                                                @if($invoice->payments->isNotEmpty())
                                                    @php $latestPayment = $invoice->payments->last(); @endphp
                                                    <a href="{{ route('payments.receipt.pdf', $latestPayment) }}" target="_blank"
                                                       class="text-blue-600 hover:underline font-bold text-xs">
                                                        Receipt
                                                    </a>
                                                @endif

                                                @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                                                    <a href="{{ route('invoices.show', $invoice) }}" 
                                                       class="text-blue-600 hover:underline font-medium text-xs">
                                                        Details
                                                    </a>
                                                @elseif($invoice->quotation)
                                                    <a href="{{ route('quotations.public-pdf', ['quotation' => $invoice->quotation->id]) }}" target="_blank"
                                                       class="text-blue-600 hover:underline font-medium text-xs">
                                                        Bill PDF
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
                        <div class="p-3 border-t border-slate-200 dark:border-slate-800">
                            {{ $invoices->links() }}
                        </div>
                    @endif
                </div>
            @endif

            {{-- Supported Payment Methods Box --}}
            <div class="bg-white dark:bg-[#0f172a] border border-slate-300 dark:border-slate-800 rounded-[4px] p-5 space-y-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Accepted Payment Methods
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Multiple payment options available for your convenience.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-1">
                    {{-- Method 1: Instant Online Gateway --}}
                    <div class="bg-slate-50 dark:bg-slate-800/60 p-3 rounded-[4px] border border-slate-200 dark:border-slate-700 space-y-1">
                        <h4 class="text-xs font-bold text-blue-600">1. Instant Online</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            Google Pay, PhonePe, Paytm, all Cards and NetBanking via Razorpay.
                        </p>
                    </div>

                    {{-- Method 2: Direct UPI QR --}}
                    <div class="bg-slate-50 dark:bg-slate-800/60 p-3 rounded-[4px] border border-slate-200 dark:border-slate-700 space-y-1">
                        <h4 class="text-xs font-bold text-emerald-700">2. Direct UPI QR</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            Scan dynamic QR code directly with any UPI app on mobile or desktop.
                        </p>
                    </div>

                    {{-- Method 3: Pay by Cash on Site --}}
                    <div class="bg-slate-50 dark:bg-slate-800/60 p-3 rounded-[4px] border border-slate-200 dark:border-slate-700 space-y-1">
                        <h4 class="text-xs font-bold text-amber-700">3. Cash on Installation</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            Pay cash directly to our technician at your site upon project arrival or handover.
                        </p>
                    </div>

                    {{-- Method 4: Electronic Bank Transfer --}}
                    <div class="bg-slate-50 dark:bg-slate-800/60 p-3 rounded-[4px] border border-slate-200 dark:border-slate-700 space-y-1">
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">4. Bank NEFT / RTGS</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 font-mono leading-relaxed">
                            Direct bank account transfer to Precision IT Systems.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
