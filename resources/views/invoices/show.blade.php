<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <a href="{{ route('invoices.index') }}" class="back-link">
                    &larr; Back
                </a>
                <div class="flex items-center gap-3 mt-1">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                        Tax Invoice {{ $invoice->invoice_no }}
                    </h2>
                    @php
                        $invStatusColor = '#2563eb';
                        if ($invoice->status === 'paid') $invStatusColor = '#16a34a';
                        elseif ($invoice->status === 'cancelled') $invStatusColor = '#dc2626';
                    @endphp
                    <span class="text-sm font-bold" style="color: {{ $invStatusColor }};">
                        {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                @if ($invoice->balanceDue() > 0)
                    <a href="{{ route('payment.checkout.invoice', $invoice) }}" target="_blank" class="btn-primary text-xs">
                        Pay Online
                    </a>
                @endif
                @if($invoice->quotation->lead->phone)
                    <a href="https://api.whatsapp.com/send?phone={{ $invoice->quotation->getWhatsAppPhone() }}&text={{ urlencode("Hello " . $invoice->quotation->lead->customer_name . ",\n\nPlease find your secure online payment link for Tax Invoice *" . $invoice->invoice_no . "* (Balance Due: ₹" . number_format($invoice->balanceDue(), 2) . "):\n\nPay Online:\n" . route('payment.checkout.invoice', $invoice) . "\n\nThank you!") }}"
                       target="_blank" class="btn-secondary text-xs">
                        WhatsApp
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="pg-wrap py-6">
        <div class="pg-inner space-y-6">

            @if (session('status'))
                <div class="p-3 rounded border border-emerald-300 bg-emerald-50 text-emerald-800 text-xs">
                    {{ session('status') }}
                </div>
            @endif

            {{-- 1. Invoice Overview: Two-column table inside standard section --}}
            <div class="crm-section">
                <h3>Invoice Overview</h3>
                <table class="crm-table w-full">
                    <tbody>
                        <tr>
                            <td style="width: 25%; color: #555; font-weight: 500;">Invoice Identifier</td>
                            <td style="color: #222; font-weight: 500;">{{ $invoice->invoice_no }}</td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Invoice Date</td>
                            <td style="color: #222; font-weight: 500;">{{ $invoice->invoice_date->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Payment Due Date</td>
                            <td style="color: #222; font-weight: 500;">
                                {{ $invoice->due_date?->format('d M Y') ?? 'Immediate' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Installation Job</td>
                            <td style="color: #222; font-weight: 500;">
                                <a href="{{ route('jobs.show', $invoice->installationJob) }}" class="action-link">
                                    {{ $invoice->installationJob->job_no }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Quotation Proposal</td>
                            <td style="color: #222; font-weight: 500;">
                                <a href="{{ route('quotations.show', $invoice->quotation) }}" class="action-link">
                                    {{ $invoice->quotation->quotation_no }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #555; font-weight: 500;">Customer Premise</td>
                            <td style="color: #222; font-weight: 500;">
                                <a href="{{ route('leads.show', $invoice->quotation->lead) }}" class="action-link">
                                    {{ $invoice->quotation->lead->customer_name }}
                                </a>
                            </td>
                        </tr>
                        @if($invoice->notes)
                        <tr>
                            <td style="color: #555; font-weight: 500;">Invoice Terms &amp; Notes</td>
                            <td style="color: #222; font-weight: 500;">{{ $invoice->notes }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- 2. Billed Line Items --}}
            <div class="crm-section">
                <h3>Billed Line Items</h3>
                <div class="overflow-x-auto">
                    <table class="crm-table w-full">
                        <thead>
                            <tr>
                                <th>Item Description</th>
                                <th class="text-right">Quantity</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->quotation->items as $item)
                                <tr>
                                    <td class="font-bold text-slate-900">{{ $item->item_name }}</td>
                                    <td class="text-right font-medium" style="white-space: nowrap;">{{ $item->quantity }} {{ $item->unit }}</td>
                                    <td class="text-right" style="white-space: nowrap;">₹{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-right font-bold text-slate-900" style="white-space: nowrap;">₹{{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 pt-4 border-t border-[#ddd] flex justify-end">
                    <div class="w-full md:w-72 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Subtotal</span>
                            <span class="font-medium text-slate-900">₹{{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        @if ($invoice->discount > 0)
                            <div class="flex justify-between text-red-600">
                                <span>Discount</span>
                                <span class="font-medium">−₹{{ number_format($invoice->discount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between pb-2 border-b border-[#ddd]">
                            <span class="text-slate-500">GST ({{ $invoice->tax_percent }}%)</span>
                            <span class="font-medium text-slate-900">₹{{ number_format($invoice->tax_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-slate-900">
                            <span>Invoice Total</span>
                            <span>₹{{ number_format($invoice->total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-emerald-700">
                            <span>Amount Paid</span>
                            <span class="font-medium">₹{{ number_format($invoice->amount_paid, 2) }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-[#ddd] font-bold text-red-600">
                            <span>Remaining Balance</span>
                            <span>₹{{ number_format($invoice->balanceDue(), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Payments Ledger --}}
            <div class="crm-section">
                <h3>Payments Ledger</h3>
                
                <div class="overflow-x-auto">
                    <table class="crm-table w-full">
                        <thead>
                            <tr>
                                <th>Receipt #</th>
                                <th>Date Paid</th>
                                <th>Method</th>
                                <th>Reference / UTR</th>
                                <th>Recorded By</th>
                                <th class="text-right">Amount Paid</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invoice->payments as $payment)
                                <tr>
                                    <td class="font-mono font-semibold" style="white-space: nowrap;">
                                        {{ $payment->receipt_no ?? 'REC-' . $payment->id }}
                                    </td>
                                    <td style="white-space: nowrap;">{{ $payment->paid_on->format('d M Y') }}</td>
                                    <td class="capitalize">{{ $payment->formatted_method }}</td>
                                    <td>{{ $payment->reference_no ?? '—' }}</td>
                                    <td style="white-space: nowrap;">{{ $payment->recordedBy->name ?? 'System' }}</td>
                                    <td class="text-right font-bold text-slate-900" style="white-space: nowrap;">₹{{ number_format($payment->amount, 2) }}</td>
                                    <td class="text-right" style="white-space: nowrap;">
                                        <a href="{{ route('payments.receipt.pdf', $payment) }}" target="_blank" class="action-link mr-2">
                                            Receipt
                                        </a>
                                        <form method="POST" action="{{ route('payments.destroy', $payment) }}"
                                              onsubmit="return confirm('Remove payment of ₹{{ number_format($payment->amount, 2) }}?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="danger-link text-xs cursor-pointer">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-6 text-slate-500 text-sm">
                                        No payment transactions recorded yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Record Payment Form --}}
                @if ($invoice->balanceDue() > 0)
                    <div class="mt-6 pt-6 border-t border-[#ddd]">
                        <h4 class="text-sm font-bold text-slate-900 mb-4">
                            Record Payment
                        </h4>
                        <form method="POST" action="{{ route('payments.store', $invoice) }}" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="crm-label">Payment Amount (₹)<span class="required-marker">*</span></label>
                                    <input type="number" name="amount" min="0.01" step="0.01" max="{{ $invoice->balanceDue() }}" value="{{ old('amount', $invoice->balanceDue()) }}" required class="crm-input w-full">
                                    @error('amount') <div class="error-text">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="crm-label">Date Paid<span class="required-marker">*</span></label>
                                    <input type="date" name="paid_on" value="{{ old('paid_on', now()->format('Y-m-d')) }}" required class="crm-input w-full">
                                    @error('paid_on') <div class="error-text">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="crm-label">Payment Method<span class="required-marker">*</span></label>
                                    <select name="method" class="crm-select w-full" required>
                                        <option value="cash" @selected(old('method') === 'cash')>Cash</option>
                                        <option value="upi" @selected(old('method') === 'upi')>UPI</option>
                                        <option value="razorpay" @selected(old('method') === 'razorpay')>Razorpay</option>
                                        <option value="bank_transfer" @selected(old('method') === 'bank_transfer')>Bank Transfer</option>
                                        <option value="card" @selected(old('method') === 'card')>Card</option>
                                        <option value="cheque" @selected(old('method') === 'cheque')>Cheque</option>
                                        <option value="other" @selected(old('method') === 'other')>Other</option>
                                    </select>
                                    @error('method') <div class="error-text">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="crm-label">Reference / UTR No.</label>
                                    <input type="text" name="reference_no" value="{{ old('reference_no') }}" placeholder="e.g. UPI-987214" class="crm-input w-full">
                                    @error('reference_no') <div class="error-text">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="crm-label">Payment Notes</label>
                                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="e.g. Received via NEFT" class="crm-input w-full">
                                @error('notes') <div class="error-text">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn-primary">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="mt-4 p-3 rounded border border-emerald-300 bg-emerald-50 text-emerald-800 text-xs font-semibold">
                        Invoice is fully paid.
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
