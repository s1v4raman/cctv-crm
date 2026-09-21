<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                    <h2 class="text-2xl font-black tracking-wider text-white uppercase font-['Outfit']">{{ $invoice->invoice_no }}</h2>
                </div>
                <p class="mt-1 text-xs text-slate-400 font-mono">
                    Customer: <strong class="text-amber-400">{{ $invoice->quotation->lead->customer_name }}</strong>
                </p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                @php
                    $statusStyles = [
                        'unpaid' => 'border-slate-600 bg-slate-800 text-slate-300',
                        'partially_paid' => 'border-amber-500/30 bg-amber-500/10 text-amber-400',
                        'paid' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400',
                        'overdue' => 'border-rose-500/30 bg-rose-500/10 text-rose-400',
                    ];
                @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold uppercase tracking-wider border {{ $statusStyles[$invoice->status] ?? 'border-slate-700 bg-slate-800 text-slate-300' }}">
                    Status: {{ ucfirst(str_replace('_',' ',$invoice->status)) }}
                </span>
                <a href="{{ route('invoices.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-700/60 bg-slate-800/40 text-slate-400 hover:text-white text-xs font-semibold transition">
                    &larr; Back to Invoices
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#060913] min-h-screen text-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-6 p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-sm font-semibold flex items-center gap-3">
                    <span class="text-base">✓</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Invoice Overview --}}
            <div class="bg-[#0F172A] border border-white/10 rounded-2xl p-6 shadow-xl mb-6">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 font-['Outfit'] mb-4 pb-3 border-b border-white/5">
                    Invoice Overview
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-400">Invoice Identifier</div>
                        <div class="text-base font-bold text-amber-400 font-mono mt-1">{{ $invoice->invoice_no }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-400">Invoice Date</div>
                        <div class="text-sm font-semibold text-slate-200 mt-1">{{ $invoice->invoice_date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-400">Payment Due Date</div>
                        <div class="text-sm font-semibold mt-1 {{ $invoice->status === 'overdue' ? 'text-rose-400 font-bold' : 'text-slate-200' }}">
                            {{ $invoice->due_date?->format('d M Y') ?? 'Immediate' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-400">Installation Job</div>
                        <div class="text-sm font-bold text-sky-400 font-mono mt-1">
                            <a href="{{ route('jobs.show', $invoice->installationJob) }}" class="hover:underline">
                                {{ $invoice->installationJob->job_no }}
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-400">Quotation Proposal</div>
                        <div class="text-sm font-bold text-amber-400 font-mono mt-1">
                            <a href="{{ route('quotations.show', $invoice->quotation) }}" class="hover:underline">
                                {{ $invoice->quotation->quotation_no }}
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-400">Customer Premise</div>
                        <div class="text-sm font-bold text-white mt-1">
                            <a href="{{ route('leads.show', $invoice->quotation->lead) }}" class="hover:underline">
                                {{ $invoice->quotation->lead->customer_name }}
                            </a>
                        </div>
                    </div>
                    @if($invoice->notes)
                    <div class="col-span-2 md:col-span-3 pt-3 border-t border-white/5">
                        <div class="text-[10px] font-mono uppercase tracking-widest text-slate-400">Invoice Terms & Bank Coordinates</div>
                        <div class="text-xs text-slate-300 font-mono leading-relaxed mt-1 whitespace-pre-line">{{ $invoice->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Items Receipt Grid --}}
            <div class="bg-[#0F172A] border border-white/10 rounded-2xl overflow-hidden shadow-xl mb-6">
                <div class="px-6 py-4 border-b border-white/5">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 font-['Outfit']">
                        Billed Line Items
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/5 bg-[#0B1120] text-[10px] font-mono uppercase tracking-widest text-slate-400">
                                <th class="py-3 px-6">Item Description</th>
                                <th class="py-3 px-6 text-right">Quantity</th>
                                <th class="py-3 px-6 text-right">Unit Price</th>
                                <th class="py-3 px-6 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-xs text-slate-300">
                            @foreach ($invoice->quotation->items as $item)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="py-3.5 px-6 font-bold text-white">{{ $item->item_name }}</td>
                                    <td class="py-3.5 px-6 text-right font-mono font-bold text-sky-400">{{ $item->quantity }} {{ $item->unit }}</td>
                                    <td class="py-3.5 px-6 text-right font-mono text-slate-400">₹{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="py-3.5 px-6 text-right font-mono font-bold text-white">₹{{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-6 bg-[#0B1120] border-t border-white/5 flex justify-end">
                    <div class="w-full max-w-sm bg-[#060913] border border-white/10 rounded-xl p-5">
                        <div class="flex justify-between items-center text-xs text-slate-400 mb-2">
                            <span>Subtotal</span>
                            <span class="font-mono text-slate-200">₹{{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        @if ($invoice->discount > 0)
                            <div class="flex justify-between items-center text-xs text-rose-400 mb-2">
                                <span>Discount</span>
                                <span class="font-mono font-bold">−₹{{ number_format($invoice->discount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center text-xs text-slate-400 mb-3 pb-3 border-b border-white/10">
                            <span>GST Tax ({{ $invoice->tax_percent }}%)</span>
                            <span class="font-mono text-slate-200">₹{{ number_format($invoice->tax_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-bold text-white uppercase font-mono tracking-wider">Invoice Total</span>
                            <span class="text-lg font-black text-amber-400 font-mono">₹{{ number_format($invoice->total, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs text-emerald-400 mb-2">
                            <span>Total Amount Paid</span>
                            <span class="font-mono font-bold">₹{{ number_format($invoice->amount_paid, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-dashed border-white/10">
                            <span class="text-xs font-bold text-rose-400 uppercase font-mono">Remaining Balance</span>
                            <span class="text-base font-black text-rose-400 font-mono">₹{{ number_format($invoice->balanceDue(), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Online Payment Link & UPI QR Card (Instant Checkout) --}}
            @if ($invoice->balanceDue() > 0)
                <div class="bg-gradient-to-r from-indigo-950/80 via-[#0F172A] to-sky-950/80 border border-indigo-500/30 rounded-2xl p-6 shadow-xl mb-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-2xl shrink-0 text-amber-400">
                                ⚡
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-extrabold text-white font-['Outfit']">Live Online Payment & UPI Gateway</h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        Razorpay + Dynamic UPI
                                    </span>
                                </div>
                                <p class="text-xs text-slate-300 mt-1 max-w-xl">
                                    Customers can pay securely online via Cards, NetBanking, or scan dynamic UPI QR code on Google Pay, PhonePe, or Paytm with instant auto-receipt.
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <a href="{{ route('payment.checkout.invoice', $invoice) }}" target="_blank"
                               class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs inline-flex items-center gap-2 shadow-lg shadow-amber-500/20 transition">
                                <span>💳 Customer Checkout</span>
                                <span>↗</span>
                            </a>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ route('payment.checkout.invoice', $invoice) }}'); alert('Payment link copied: {{ route('payment.checkout.invoice', $invoice) }}');"
                                    class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs border border-slate-700 inline-flex items-center gap-1.5 transition">
                                📋 Copy Link
                            </button>
                            @if($invoice->quotation->lead->phone)
                                <a href="https://api.whatsapp.com/send?phone={{ $invoice->quotation->getWhatsAppPhone() }}&text={{ urlencode("Hello " . $invoice->quotation->lead->customer_name . ",\n\nPlease find your secure online payment link for Tax Invoice *" . $invoice->invoice_no . "* (Balance Due: ₹" . number_format($invoice->balanceDue(), 2) . "):\n\n💳 Pay Online via Razorpay / UPI:\n" . route('payment.checkout.invoice', $invoice) . "\n\nThank you!") }}"
                                   target="_blank"
                                   class="px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-md transition">
                                    💬 WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Payments History --}}
            <div class="bg-[#0F172A] border border-white/10 rounded-2xl overflow-hidden shadow-xl mb-6">
                <div class="px-6 py-4 border-b border-white/5">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 font-['Outfit']">Payments Ledger</h3>
                    <p class="text-xs text-slate-400 font-mono mt-0.5">Recorded transaction history and official GST payment receipts</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/5 bg-[#0B1120] text-[10px] font-mono uppercase tracking-widest text-slate-400">
                                <th class="py-3 px-6">Receipt #</th>
                                <th class="py-3 px-6">Date Paid</th>
                                <th class="py-3 px-6">Method</th>
                                <th class="py-3 px-6">Reference / Txn No.</th>
                                <th class="py-3 px-6">Recorded By</th>
                                <th class="py-3 px-6 text-right">Amount Paid</th>
                                <th class="py-3 px-6 text-right">Receipt & Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-xs text-slate-300">
                            @forelse ($invoice->payments as $payment)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="py-3.5 px-6">
                                        <span class="font-mono text-xs font-bold text-sky-400 bg-sky-500/10 px-2 py-0.5 rounded border border-sky-500/20">
                                            {{ $payment->receipt_no ?? 'REC-' . $payment->id }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-6 font-bold text-white">{{ $payment->paid_on->format('d M Y') }}</td>
                                    <td class="py-3.5 px-6">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold bg-slate-800 text-slate-300 capitalize border border-slate-700">
                                            {{ $payment->formatted_method }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-6"><span class="font-mono text-xs text-slate-400">{{ $payment->reference_no ?? '—' }}</span></td>
                                    <td class="py-3.5 px-6 text-slate-400">{{ $payment->recordedBy->name ?? 'System / Online' }}</td>
                                    <td class="py-3.5 px-6 text-right font-bold text-emerald-400 font-mono">₹{{ number_format($payment->amount, 2) }}</td>
                                    <td class="py-3.5 px-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('payments.receipt.pdf', $payment) }}" target="_blank"
                                               class="px-2.5 py-1 rounded-lg bg-sky-500/10 hover:bg-sky-500/20 text-sky-400 text-xs font-bold border border-sky-500/30 inline-flex items-center gap-1 transition">
                                                📄 Receipt PDF
                                            </a>
                                            <form method="POST" action="{{ route('payments.destroy', $payment) }}"
                                                  onsubmit="return confirm('Are you sure you want to remove this payment entry of ₹{{ number_format($payment->amount, 2) }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-400 hover:text-rose-300 font-semibold text-[11px] underline">Remove</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-slate-500 text-xs font-mono">
                                        No payment transactions recorded for this invoice yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Record Payment Form --}}
                @if ($invoice->balanceDue() > 0)
                    <div class="bg-[#0B1120] border-t border-white/5 p-6">
                        <h4 class="text-sm font-bold text-white mb-4 flex items-center gap-2 font-['Outfit'] uppercase">
                            <span>💳</span> Record New Payment Transaction
                        </h4>
                        <form method="POST" action="{{ route('payments.store', $invoice) }}">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">Payment Amount (₹) <span class="text-amber-400">*</span></label>
                                    <input type="number" name="amount" min="0.01" step="0.01" max="{{ $invoice->balanceDue() }}" value="{{ old('amount', $invoice->balanceDue()) }}" required class="w-full bg-[#060913] border border-slate-700 rounded-xl p-2.5 text-xs text-white font-mono font-bold focus:border-amber-400 focus:outline-none">
                                    @error('amount') <div class="text-xs text-rose-400 mt-1 font-semibold">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">Date Paid <span class="text-amber-400">*</span></label>
                                    <input type="date" name="paid_on" value="{{ old('paid_on', now()->format('Y-m-d')) }}" required class="w-full bg-[#060913] border border-slate-700 rounded-xl p-2.5 text-xs text-white font-mono focus:border-amber-400 focus:outline-none">
                                    @error('paid_on') <div class="text-xs text-rose-400 mt-1 font-semibold">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">Payment Method <span class="text-amber-400">*</span></label>
                                    <select name="method" class="w-full bg-[#060913] border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:border-amber-400 focus:outline-none" required>
                                        <option value="cash" @selected(old('method') === 'cash')>💵 Cash</option>
                                        <option value="upi" @selected(old('method') === 'upi')>📱 UPI (GPay / PhonePe / Paytm)</option>
                                        <option value="razorpay" @selected(old('method') === 'razorpay')>⚡ Razorpay Online</option>
                                        <option value="bank_transfer" @selected(old('method') === 'bank_transfer')>🏦 Bank Transfer / NEFT / IMPS</option>
                                        <option value="card" @selected(old('method') === 'card')>💳 Credit / Debit Card</option>
                                        <option value="cheque" @selected(old('method') === 'cheque')>📝 Cheque</option>
                                        <option value="other" @selected(old('method') === 'other')>Other</option>
                                    </select>
                                    @error('method') <div class="text-xs text-rose-400 mt-1 font-semibold">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">Reference / Transaction / UTR No.</label>
                                    <input type="text" name="reference_no" value="{{ old('reference_no') }}" placeholder="e.g. UPI-202689128, Cheque #004812" class="w-full bg-[#060913] border border-slate-700 rounded-xl p-2.5 text-xs text-white font-mono focus:border-amber-400 focus:outline-none">
                                    @error('reference_no') <div class="text-xs text-rose-400 mt-1 font-semibold">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">Payment Notes (Optional)</label>
                                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="e.g. 50% advance received on site" class="w-full bg-[#060913] border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:border-amber-400 focus:outline-none">
                                <p class="text-[10px] text-slate-500 font-mono mt-1">Receipt PDF will automatically be generated and sent via WhatsApp and Email upon recording.</p>
                                @error('notes') <div class="text-xs text-rose-400 mt-1 font-semibold">{{ $message }}</div> @enderror
                            </div>

                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-white font-black text-xs uppercase tracking-wider flex items-center gap-2 shadow-lg shadow-emerald-500/20 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Record Payment & Generate Receipt
                            </button>
                        </form>
                    </div>
                @else
                    <div class="bg-emerald-500/10 border-t border-emerald-500/20 p-4 px-6 flex items-center gap-2 text-emerald-400 font-bold text-xs font-mono">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Invoice has been fully settled and paid in full.</span>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
