<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">
                        Vendor Bill: {{ $vendorBill->bill_number }}
                    </h2>
                    @if($vendorBill->status === 'approved')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">
                            Approved
                        </span>
                    @elseif($vendorBill->status === 'on_hold')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">
                            On Hold
                        </span>
                    @elseif($vendorBill->status === 'paid')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">
                            Fully Paid
                        </span>
                    @elseif($vendorBill->status === 'partially_paid')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200">
                            Partially Paid
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            {{ ucfirst($vendorBill->status) }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Supplier Invoice #{{ $vendorBill->vendor_invoice_no }} dated {{ $vendorBill->invoice_date?->format('d M Y') }}
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('vendor-bills.index') }}" class="btn-secondary">
                    &larr; Back to Bills
                </a>
                @if($vendorBill->invoice_file_path)
                    <a href="{{ route('vendor-bills.invoice-file', $vendorBill) }}" target="_blank" class="btn-secondary">
                        <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        View Original Invoice
                    </a>
                @endif
                @if($vendorBill->purchaseOrder)
                    <a href="{{ route('purchase-orders.show', $vendorBill->purchaseOrder) }}" class="btn-secondary">
                        View PO #{{ $vendorBill->purchaseOrder->po_number }}
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-sm">
                {{ session('warning') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm">
                <strong class="font-bold">Error:</strong>
                <ul class="list-disc pl-5 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 3-Way Match Verification Banner --}}
        <div class="p-5 rounded-xl border shadow-sm {{ $vendorBill->match_status === 'matched' ? 'bg-emerald-50/70 border-emerald-200 dark:bg-emerald-950/30 dark:border-emerald-800' : 'bg-amber-50/70 border-amber-200 dark:bg-amber-950/30 dark:border-amber-800' }}">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        @if($vendorBill->match_status === 'matched')
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-600 text-white font-bold text-xs">✓</span>
                            <h3 class="text-base font-bold text-emerald-900 dark:text-emerald-200">3-Way Match Verified (Passed)</h3>
                        @elseif($vendorBill->match_status === 'mismatch')
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-600 text-white font-bold text-xs">⚠</span>
                            <h3 class="text-base font-bold text-amber-900 dark:text-amber-200">3-Way Match Discrepancy (Bill Placed On Hold)</h3>
                        @else
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">3-Way Matching: Not Checked</h3>
                        @endif
                    </div>
                    <div class="mt-2 text-xs text-slate-700 dark:text-slate-300 font-mono whitespace-pre-line leading-relaxed">
                        {{ $vendorBill->match_notes ?: 'No audit notes recorded.' }}
                    </div>

                    @if($vendorBill->approved_by && $vendorBill->approval_reason)
                        <div class="mt-3 p-3 bg-white/70 dark:bg-slate-900/60 rounded-lg border border-emerald-300 text-xs text-emerald-900 dark:text-emerald-200">
                            <strong>Admin Override Approval:</strong> Approved by <em>{{ $vendorBill->approvedBy?->name }}</em> on {{ $vendorBill->approved_at?->format('d M Y, h:i A') }}<br>
                            <strong>Written Justification:</strong> {{ $vendorBill->approval_reason }}
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @if($vendorBill->purchaseOrder)
                        <form method="POST" action="{{ route('vendor-bills.reverify-match', $vendorBill) }}">
                            @csrf
                            <button type="submit" class="btn-secondary text-xs">
                                ↺ Re-verify Match
                            </button>
                        </form>
                    @endif

                    @if(auth()->user()->isAdmin() && in_array($vendorBill->status, ['on_hold', 'draft']))
                        <button type="button" onclick="document.getElementById('override-modal').classList.remove('hidden')" class="btn-primary text-xs bg-amber-600 hover:bg-amber-700">
                            Admin Override & Approve
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Meta Information Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Invoice Information</div>
                <div class="space-y-1.5 text-sm">
                    <div><span class="text-slate-500">Bill Number:</span> <strong class="font-mono text-slate-900 dark:text-white">{{ $vendorBill->bill_number }}</strong></div>
                    <div><span class="text-slate-500">Vendor Invoice #:</span> <strong class="font-mono text-slate-900 dark:text-white">{{ $vendorBill->vendor_invoice_no }}</strong></div>
                    <div><span class="text-slate-500">Invoice Date:</span> <span>{{ $vendorBill->invoice_date?->format('d M Y') }}</span></div>
                    <div><span class="text-slate-500">Due Date:</span> <strong class="{{ $vendorBill->isOverdue() ? 'text-rose-600' : '' }}">{{ $vendorBill->due_date?->format('d M Y') }}</strong></div>
                    @if($vendorBill->isOverdue())
                        <div class="text-xs text-rose-500 font-semibold">{{ $vendorBill->daysOverdue() }} days overdue</div>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Supplier Details</div>
                <div class="space-y-1.5 text-sm">
                    <div class="font-bold text-slate-900 dark:text-white">{{ $vendorBill->supplier?->company_name ?: ($vendorBill->supplier?->name ?? 'Vendor') }}</div>
                    @if($vendorBill->supplier?->gst_number)
                        <div class="text-xs"><span class="text-slate-500">GSTIN:</span> <strong class="font-mono">{{ $vendorBill->supplier->gst_number }}</strong></div>
                    @endif
                    @if($vendorBill->supplier?->phone)
                        <div class="text-xs"><span class="text-slate-500">Phone:</span> {{ $vendorBill->supplier->phone }}</div>
                    @endif
                    <div class="text-xs text-slate-500">{{ $vendorBill->supplier?->address }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Payment Summary</div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Total Billed:</span>
                        <span class="text-base font-mono font-bold text-slate-900 dark:text-white">₹{{ number_format((float) $vendorBill->total, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500">Amount Paid:</span>
                        <span class="text-base font-mono font-bold text-emerald-600 dark:text-emerald-400">₹{{ number_format((float) $vendorBill->amount_paid, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-slate-700 dark:text-slate-300 font-bold">Balance Due:</span>
                        <span class="text-lg font-mono font-bold {{ $vendorBill->balanceDue() > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            ₹{{ number_format((float) $vendorBill->balanceDue(), 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Billed Items Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-slate-900 dark:text-white">Billed Line Items & GST Split</h3>
                <span class="text-xs text-slate-500">{{ $vendorBill->items->count() }} line(s)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="crm-table w-full">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 text-xs text-slate-500 font-semibold">
                            <th class="py-3 px-4 text-left">#</th>
                            <th class="py-3 px-4 text-left">Item Description</th>
                            <th class="py-3 px-4 text-center">HSN/SAC</th>
                            <th class="py-3 px-4 text-center">Billed Qty</th>
                            <th class="py-3 px-4 text-right">Unit Rate</th>
                            <th class="py-3 px-4 text-right">Taxable</th>
                            <th class="py-3 px-4 text-right">CGST</th>
                            <th class="py-3 px-4 text-right">SGST</th>
                            <th class="py-3 px-4 text-right">IGST</th>
                            <th class="py-3 px-4 text-right">Line Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                        @foreach($vendorBill->items as $index => $item)
                            <tr>
                                <td class="py-3 px-4 text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                    {{ $item->item_name }}
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-xs text-slate-500">
                                    {{ $item->hsn_code ?: '-' }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold">
                                    {{ (float) $item->quantity }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-xs">
                                    ₹{{ number_format((float) $item->unit_cost, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-xs">
                                    ₹{{ number_format((float) $item->total_cost, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-xs text-slate-500">
                                    ₹{{ number_format((float) $item->cgst_amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-xs text-slate-500">
                                    ₹{{ number_format((float) $item->sgst_amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-xs text-slate-500">
                                    ₹{{ number_format((float) $item->igst_amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                    ₹{{ number_format((float) ($item->total_cost + $item->cgst_amount + $item->sgst_amount + $item->igst_amount), 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-slate-800/40 font-bold border-t border-slate-200 dark:border-slate-800 text-xs">
                        <tr>
                            <td colspan="5" class="py-3 px-4 text-right">Totals:</td>
                            <td class="py-3 px-4 text-right font-mono">₹{{ number_format((float) $vendorBill->subtotal, 2) }}</td>
                            <td class="py-3 px-4 text-right font-mono">₹{{ number_format((float) $vendorBill->cgst, 2) }}</td>
                            <td class="py-3 px-4 text-right font-mono">₹{{ number_format((float) $vendorBill->sgst, 2) }}</td>
                            <td class="py-3 px-4 text-right font-mono">₹{{ number_format((float) $vendorBill->igst, 2) }}</td>
                            <td class="py-3 px-4 text-right font-mono text-base font-bold text-slate-900 dark:text-white">
                                ₹{{ number_format((float) $vendorBill->total, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Disbursed Payments & Allocations --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Payment Disbursements</h3>
                    <p class="text-xs text-slate-500">Remittance history and payment allocations recorded against this bill.</p>
                </div>
                @if(auth()->user()->isAdmin() && $vendorBill->balanceDue() > 0)
                    <button type="button" onclick="document.getElementById('payment-modal').classList.remove('hidden')" class="btn-primary text-xs">
                        + Record Payment
                    </button>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="crm-table w-full">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 text-xs text-slate-500 font-semibold">
                            <th class="py-3 px-4 text-left">Payment Ref</th>
                            <th class="py-3 px-4 text-left">Date</th>
                            <th class="py-3 px-4 text-left">Mode</th>
                            <th class="py-3 px-4 text-left">UTR / Reference</th>
                            <th class="py-3 px-4 text-right">Allocated Amount</th>
                            <th class="py-3 px-4 text-left">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                        @forelse($vendorBill->paymentAllocations as $alloc)
                            <tr>
                                <td class="py-3 px-4 font-mono font-bold text-blue-600">
                                    {{ $alloc->payment?->payment_reference ?? 'VPAY' }}
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    {{ $alloc->payment?->payment_date?->format('d M Y') }}
                                </td>
                                <td class="py-3 px-4 text-xs uppercase">
                                    {{ str_replace('_', ' ', $alloc->payment?->payment_method ?? '-') }}
                                </td>
                                <td class="py-3 px-4 font-mono text-xs">
                                    {{ $alloc->payment?->transaction_reference ?: '-' }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">
                                    ₹{{ number_format((float) $alloc->amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-500">
                                    {{ $alloc->payment?->recordedBy?->name ?? 'Admin' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">
                                    No payments disbursed against this bill yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Admin Override Modal --}}
    @if(auth()->user()->isAdmin())
        <div id="override-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full mx-4 border border-slate-200 dark:border-slate-800 shadow-2xl p-6">
                <div class="flex justify-between items-center mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="font-bold text-lg text-slate-900 dark:text-white">Admin Override: Approve Mismatched Bill</h3>
                    <button type="button" onclick="document.getElementById('override-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                <form method="POST" action="{{ route('vendor-bills.approve', $vendorBill) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Written Reason / Business Justification <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="approval_reason" rows="3" required placeholder="Explain why the price or quantity variance is accepted (e.g. freight cost agreed verbally, manufacturer revised price)..." class="crm-input w-full text-xs"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('override-modal').classList.add('hidden')" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs bg-amber-600 hover:bg-amber-700">Confirm Override & Approve</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Record Payment Modal --}}
        <div id="payment-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full mx-4 border border-slate-200 dark:border-slate-800 shadow-2xl p-6">
                <div class="flex justify-between items-center mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="font-bold text-lg text-slate-900 dark:text-white">Disburse Vendor Payment</h3>
                    <button type="button" onclick="document.getElementById('payment-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                <form method="POST" action="{{ route('vendor-bills.payments.store', $vendorBill) }}">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Payment Amount (₹) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" step="0.01" name="amount" min="0.01" max="{{ $vendorBill->balanceDue() }}" value="{{ $vendorBill->balanceDue() }}" required class="crm-input w-full font-mono font-bold text-base text-emerald-600">
                            <span class="text-[10px] text-slate-400">Maximum payable: ₹{{ number_format((float) $vendorBill->balanceDue(), 2) }} (Over-payment blocked)</span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Payment Date <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="crm-input w-full text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Payment Method <span class="text-rose-500">*</span>
                            </label>
                            <select name="payment_method" required class="crm-input w-full text-xs">
                                <option value="bank_transfer">Bank Transfer (NEFT / RTGS / IMPS)</option>
                                <option value="cheque">Cheque</option>
                                <option value="upi">UPI</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Bank UTR / Cheque / Transaction Ref #
                            </label>
                            <input type="text" name="transaction_reference" placeholder="e.g. UTR123456789" class="crm-input w-full text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Remarks / Notes
                            </label>
                            <input type="text" name="notes" placeholder="e.g. Cleared via HDFC Bank" class="crm-input w-full text-xs">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-5">
                        <button type="button" onclick="document.getElementById('payment-modal').classList.add('hidden')" class="btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn-primary text-xs">Disburse Payment</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</x-app-layout>
