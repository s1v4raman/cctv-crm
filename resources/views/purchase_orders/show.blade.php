<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('purchase-orders.index') }}" class="back-link">&larr; Back</a>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-1">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white font-mono">{{ $purchaseOrder->po_number }}</h2>
                    <div class="flex items-center gap-2 text-xs mt-1">
                        <span class="status-{{ $purchaseOrder->status === 'received' ? 'completed' : ($purchaseOrder->status === 'cancelled' ? 'cancelled' : 'progress') }}">
                            {{ ucfirst(str_replace('_', ' ', $purchaseOrder->status)) }}
                        </span>
                        <span class="text-slate-400">&bull;</span>
                        <span class="status-{{ $purchaseOrder->payment_status === 'paid' ? 'completed' : ($purchaseOrder->payment_status === 'unpaid' ? 'cancelled' : 'progress') }}">
                            Payment: {{ ucfirst(str_replace('_', ' ', $purchaseOrder->payment_status)) }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('purchase-orders.pdf', $purchaseOrder) }}" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-bold transition shadow-xs cursor-pointer" style="background-color: #2563eb !important; color: #ffffff !important;" title="Download Official Purchase Order PDF">
                        <span>Download PDF</span>
                    </a>
                    <a href="{{ route('purchase-orders.streamPdf', $purchaseOrder) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition shadow-xs cursor-pointer" title="View Purchase Order PDF in browser">
                        <span>View PDF</span>
                    </a>
                    @if($purchaseOrder->supplier?->phone)
                        <button type="button" onclick="openPoWhatsAppModal()" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-bold transition shadow-xs cursor-pointer" style="background-color: #059669 !important; color: #ffffff !important;">
                            <span>WhatsApp PO</span>
                        </button>
                    @endif
                    @if($purchaseOrder->supplier?->email)
                        <button type="button" onclick="openPoEmailModal()" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-bold transition shadow-xs cursor-pointer" style="background-color: #0284c7 !important; color: #ffffff !important;">
                            <span>Email PO</span>
                        </button>
                    @endif
                    @if($purchaseOrder->status === 'draft')
                        <form method="POST" action="{{ route('purchase-orders.submit', $purchaseOrder) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-xs cursor-pointer" style="background-color: #2563eb !important; color: #ffffff !important;">
                                <span>Submit for Approval</span>
                            </button>
                        </form>
                    @endif

                    @if($purchaseOrder->status === 'pending_approval' && auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('purchase-orders.approve', $purchaseOrder) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-xs cursor-pointer" style="background-color: #16a34a !important; color: #ffffff !important;">
                                <span>Approve PO</span>
                            </button>
                        </form>
                        <button type="button" onclick="document.getElementById('rejectModal').classList.remove('hidden')" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-xs cursor-pointer" style="background-color: #dc2626 !important; color: #ffffff !important;">
                            <span>Reject PO</span>
                        </button>
                    @endif

                    @if($purchaseOrder->status === 'approved')
                        <form method="POST" action="{{ route('purchase-orders.mark-sent', $purchaseOrder) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn-secondary">
                                Mark as Sent
                            </button>
                        </form>
                    @endif

                    @if($purchaseOrder->status === 'received')
                        <form method="POST" action="{{ route('purchase-orders.close', $purchaseOrder) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn-secondary">
                                Close Order
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('suppliers.show', $purchaseOrder->supplier) }}" class="btn-secondary">
                        Supplier Profile
                    </a>
                    @if((auth()->user()->isAdmin() || auth()->user()->isStaff()) && $purchaseOrder->canBeEdited())
                        <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="btn-secondary">
                            Edit
                        </a>
                    @endif
                    @if(auth()->user()->isAdmin() && in_array($purchaseOrder->status, ['draft', 'cancelled']))
                        <form method="POST" action="{{ route('purchase-orders.destroy', $purchaseOrder) }}" data-confirm="Are you sure you want to delete this purchase order?" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="danger-link p-0 bg-transparent border-0 cursor-pointer text-xs ml-2">
                                Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    <div class="max-w-[1200px] mx-auto space-y-6">
        @if (session('status'))
            <div class="p-3 bg-white dark:bg-slate-900 border border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded text-xs">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3 bg-white dark:bg-slate-900 border border-red-500 text-red-600 rounded text-xs">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Section 1: PO Overview Two-Column Table --}}
        <div class="crm-section">
            <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-200 dark:border-slate-800">
                <h3 class="m-0 border-0 pb-0">Purchase Order Details</h3>
                <form method="POST" action="{{ route('purchase-orders.updateStatus', $purchaseOrder) }}" class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    @php
                        $allowedTransitions = array_filter(
                            \App\Models\PurchaseOrder::TRANSITIONS[$purchaseOrder->status] ?? [],
                            fn($s) => $s !== \App\Models\PurchaseOrder::STATUS_RECEIVED
                        );
                    @endphp
                    @if(count($allowedTransitions) > 0)
                        <select aria-label="Status" name="status" class="crm-select text-xs py-1" style="height: 38px; width: 170px;">
                            <option value="{{ $purchaseOrder->status }}" selected disabled>{{ ucfirst(str_replace('_', ' ', $purchaseOrder->status)) }} (Current)</option>
                            @foreach($allowedTransitions as $target)
                                <option value="{{ $target }}">&rarr; Move to {{ ucfirst(str_replace('_', ' ', $target)) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-secondary">Update</button>
                    @else
                        <span class="text-xs text-slate-400 font-medium">Status Locked ({{ ucfirst(str_replace('_', ' ', $purchaseOrder->status)) }})</span>
                    @endif
                </form>
            </div>

            <table class="crm-table">
                <tbody>
                    <tr>
                        <td class="w-1/3 text-slate-500 font-bold">PO Number</td>
                        <td class="w-2/3 text-slate-900 dark:text-white font-mono">{{ $purchaseOrder->po_number }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Status</td>
                        <td class="text-slate-900 dark:text-white">
                            <span class="font-bold text-xs uppercase px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                {{ ucfirst(str_replace('_', ' ', $purchaseOrder->status)) }}
                            </span>
                        </td>
                    </tr>
                    @if($purchaseOrder->approvedBy)
                        <tr>
                            <td class="text-slate-500 font-bold">Approved By</td>
                            <td class="text-emerald-700 dark:text-emerald-400 font-semibold">
                                ✓ {{ $purchaseOrder->approvedBy->name }} on {{ $purchaseOrder->approved_at?->format('d M Y, h:i A') }}
                            </td>
                        </tr>
                    @endif
                    @if($purchaseOrder->rejectedBy)
                        <tr>
                            <td class="text-slate-500 font-bold">Rejected By</td>
                            <td class="text-rose-600 dark:text-rose-400 font-semibold">
                                ✕ {{ $purchaseOrder->rejectedBy->name }} on {{ $purchaseOrder->rejected_at?->format('d M Y, h:i A') }}
                                @if($purchaseOrder->rejection_reason)
                                    <div class="text-xs font-normal text-slate-600 dark:text-slate-400 mt-0.5">Reason: {{ $purchaseOrder->rejection_reason }}</div>
                                @endif
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td class="text-slate-500 font-bold">Supplier</td>
                        <td class="text-slate-900 dark:text-white">
                            <a href="{{ route('suppliers.show', $purchaseOrder->supplier) }}" class="action-link">
                                {{ $purchaseOrder->supplier->name }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Order Date</td>
                        <td class="text-slate-900 dark:text-white">{{ $purchaseOrder->order_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Expected Delivery</td>
                        <td class="text-slate-900 dark:text-white">{{ $purchaseOrder->expected_delivery_date?->format('d M Y') ?? 'Immediate' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Supplier Contact</td>
                        <td class="text-slate-900 dark:text-white">
                            {{ $purchaseOrder->supplier->contact_person ?: 'Not specified' }}
                            @if($purchaseOrder->supplier->phone)
                                (<a href="tel:{{ $purchaseOrder->supplier->phone }}" class="action-link">{{ $purchaseOrder->supplier->phone }}</a>)
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Supplier GSTIN</td>
                        <td class="text-slate-900 dark:text-white font-mono">{{ $purchaseOrder->supplier->gst_number ?: 'Unregistered' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Payment Terms</td>
                        <td class="text-slate-900 dark:text-white">{{ $purchaseOrder->supplier->payment_terms ?: 'Standard' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 font-bold">Created By</td>
                        <td class="text-slate-900 dark:text-white">{{ $purchaseOrder->createdBy->name ?? 'System' }}</td>
                    </tr>
                    @if($purchaseOrder->notes)
                        <tr>
                            <td class="text-slate-500 font-bold">Procurement Notes</td>
                            <td class="text-slate-900 dark:text-white whitespace-pre-line">{{ $purchaseOrder->notes }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Section 2: Ordered Items Table (5.3) --}}
        <div class="crm-section">
            <h3>Ordered Items</h3>
            <div class="overflow-x-auto">
                <table class="crm-table">
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th class="nowrap">SKU / Model</th>
                            <th class="nowrap">Catalog Product</th>
                            <th class="nowrap">Ordered Qty</th>
                            <th class="nowrap">Received Qty</th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrder->items as $item)
                        <tr>
                            <td class="font-bold text-slate-900 dark:text-white">{{ $item->item_name }}</td>
                            <td class="font-mono text-xs">{{ $item->sku ?: '—' }}</td>
                            <td class="nowrap">
                                @if($item->product)
                                    <a href="{{ route('products.index') }}" class="action-link">
                                        {{ $item->product->name }}
                                    </a>
                                @else
                                    <span class="text-slate-400 text-xs">Non-Catalog</span>
                                @endif
                            </td>
                            <td class="nowrap font-bold text-slate-900 dark:text-white">{{ $item->quantity_ordered }}</td>
                            <td class="nowrap font-bold">
                                {{ $item->quantity_received }} / {{ $item->quantity_ordered }}
                            </td>
                            <td class="nowrap text-slate-900 dark:text-white">&#8377;{{ number_format($item->unit_cost, 2) }}</td>
                            <td class="nowrap font-bold text-slate-900 dark:text-white">&#8377;{{ number_format($item->total_cost, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div class="mt-4 flex justify-end">
                <div class="w-full max-w-sm space-y-2 text-sm text-slate-700 dark:text-slate-300">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span class="font-bold text-slate-900 dark:text-white">&#8377;{{ number_format($purchaseOrder->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>GST Tax ({{ $purchaseOrder->tax_percent }}%):</span>
                        <span class="font-bold text-slate-900 dark:text-white">&#8377;{{ number_format($purchaseOrder->tax_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Shipping / Freight:</span>
                        <span class="font-bold text-slate-900 dark:text-white">&#8377;{{ number_format($purchaseOrder->shipping_cost, 2) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-300 dark:border-slate-700 pt-2 font-bold text-base text-slate-900 dark:text-white">
                        <span>Grand Total:</span>
                        <span>&#8377;{{ number_format($purchaseOrder->total, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-500">
                        <span>Amount Paid:</span>
                        <span>&#8377;{{ number_format($purchaseOrder->amount_paid, 2) }}</span>
                    </div>
                    @if($purchaseOrder->balanceDue() > 0)
                        <div class="flex justify-between font-bold text-red-600 text-xs">
                            <span>Balance Due:</span>
                            <span>&#8377;{{ number_format($purchaseOrder->balanceDue(), 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Section 3: Inwarding Stock (Goods Receipt) --}}
        @if(in_array($purchaseOrder->status, ['sent', 'ordered', 'partially_received']))
            <div class="crm-section">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                    <div>
                        <h3 class="mb-0">Inward Received Stock (GRN)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Quickly receive items into central warehouse stock ledger and generate a Goods Receipt Note.</p>
                    </div>
                    <div>
                        <a href="{{ route('goods-receipts.create', ['purchase_order_id' => $purchaseOrder->id]) }}" class="btn-secondary text-xs">
                            Full GRN Form with Serial Tracking &rarr;
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Delivery Note / DC #</label>
                            <input type="text" name="delivery_note_no" placeholder="e.g. DC-2026-001" class="crm-input w-full text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Received Date</label>
                            <input type="date" name="received_date" value="{{ date('Y-m-d') }}" class="crm-input w-full text-xs">
                        </div>
                    </div>

                    <div class="overflow-x-auto mb-4">
                        <table class="crm-table">
                            <thead>
                                <tr>
                                    <th>Item Description</th>
                                    <th class="nowrap">Ordered</th>
                                    <th class="nowrap">Received</th>
                                    <th class="nowrap">Pending</th>
                                    <th class="nowrap">Inward Now Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->items as $item)
                                <tr>
                                    <td class="font-bold text-slate-900 dark:text-white">
                                        {{ $item->item_name }}
                                        @if($item->product?->track_serial)
                                            <span class="text-[10px] text-indigo-600 font-normal ml-1">(Serial Tracked)</span>
                                        @endif
                                    </td>
                                    <td class="nowrap">{{ $item->quantity_ordered }}</td>
                                    <td class="nowrap">{{ $item->quantity_received }}</td>
                                    <td class="nowrap font-bold">{{ $item->pendingQuantity() }}</td>
                                    <td class="nowrap">
                                        @if($item->pendingQuantity() > 0)
                                            <input aria-label="Received" type="number" name="received[{{ $item->id }}]" min="0" max="{{ $item->pendingQuantity() }}" value="{{ $item->pendingQuantity() }}" class="crm-input" style="width: 100px;">
                                        @else
                                            <span class="status-won">Completed</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            Inward Stock & Generate GRN
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Goods Receipts History Section --}}
        @if($purchaseOrder->goodsReceipts->isNotEmpty())
            <div class="crm-section">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                    <div>
                        <h3 class="mb-0">Goods Receipt Notes (GRN History)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Verification receipts and delivery documents recorded against this Purchase Order.</p>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>GRN Number</th>
                                <th>Received Date</th>
                                <th>Delivery Note #</th>
                                <th>Received By</th>
                                <th class="text-center">Accepted Units</th>
                                <th class="text-center">Rejected Units</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->goodsReceipts as $grn)
                                <tr>
                                    <td class="font-mono font-bold">
                                        <a href="{{ route('goods-receipts.show', $grn) }}" class="text-blue-600 hover:underline">
                                            {{ $grn->grn_number }}
                                        </a>
                                    </td>
                                    <td>{{ $grn->received_date?->format('d M Y') }}</td>
                                    <td class="font-mono text-xs">{{ $grn->delivery_note_no ?: '-' }}</td>
                                    <td class="text-xs">{{ $grn->receivedBy?->name ?? 'Storekeeper' }}</td>
                                    <td class="text-center font-bold text-emerald-600">{{ $grn->totalReceivedQty() }}</td>
                                    <td class="text-center font-bold {{ $grn->totalRejectedQty() > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                        {{ $grn->totalRejectedQty() }}
                                    </td>
                                    <td class="text-right space-x-2">
                                        <a href="{{ route('goods-receipts.show', $grn) }}" class="text-xs font-semibold text-slate-600 dark:text-slate-300 hover:underline">
                                            View
                                        </a>
                                        <a href="{{ route('goods-receipts.pdf', $grn) }}" target="_blank" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                            PDF
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Section 4: Payment Tracking --}}
        <div class="crm-section">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                <div>
                    <h3 class="mb-0">Vendor Payment Tracking</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Track remittance, UTR transaction references, and share 1-click payment advice with vendor.</p>
                </div>
                <div class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300">
                    Paid: <span class="text-emerald-600 dark:text-emerald-400">₹{{ number_format((float) $purchaseOrder->amount_paid, 2) }}</span> / ₹{{ number_format((float) $purchaseOrder->total, 2) }}
                </div>
            </div>

            @if($purchaseOrder->vendorPayments->isNotEmpty())
                <div class="mb-6 overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Payment Ref</th>
                                <th>Date</th>
                                <th>Amount Paid</th>
                                <th>Payment Mode</th>
                                <th>UTR / Reference</th>
                                <th>Recorded By</th>
                                <th class="text-right">Share Advice</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->vendorPayments as $pay)
                                <tr>
                                    <td class="font-mono font-bold text-slate-800 dark:text-slate-100">{{ $pay->payment_reference }}</td>
                                    <td>{{ $pay->payment_date ? $pay->payment_date->format('d M Y') : '—' }}</td>
                                    <td class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">₹{{ number_format((float) $pay->amount, 2) }}</td>
                                    <td>
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                            {{ ucfirst(str_replace('_', ' ', $pay->payment_mode)) }}
                                        </span>
                                    </td>
                                    <td class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $pay->transaction_reference ?: '—' }}</td>
                                    <td class="text-slate-600 dark:text-slate-400">{{ $pay->recordedBy?->name ?? 'System' }}</td>
                                    <td class="text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            <a href="{{ route('vendor-payments.shareWhatsApp', $pay) }}" target="_blank" class="inline-flex items-center px-2 py-1 rounded text-[11px] font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs cursor-pointer" title="Send WhatsApp Remittance Advice with UTR">
                                                <span>WhatsApp Advice</span>
                                            </a>
                                            <form method="POST" action="{{ route('vendor-payments.sendEmail', $pay) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-2 py-1 rounded text-[11px] font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-xs cursor-pointer" title="Email Remittance Advice Voucher to Supplier">
                                                    <span>Email Advice</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <form method="POST" action="{{ route('purchase-orders.updatePayment', $purchaseOrder) }}">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="crm-label">Total Amount Paid (₹)</label>
                        <input aria-label="Total Amount Paid (₹)" type="number" step="0.01" min="0" max="{{ $purchaseOrder->total }}" name="amount_paid" value="{{ old('amount_paid', $purchaseOrder->amount_paid) }}" required class="crm-input">
                    </div>

                    <div>
                        <label class="crm-label">Payment Status</label>
                        <select aria-label="Payment Status" name="payment_status" class="crm-select">
                            <option value="unpaid" @selected($purchaseOrder->payment_status === 'unpaid')>Unpaid</option>
                            <option value="partially_paid" @selected($purchaseOrder->payment_status === 'partially_paid')>Partially Paid</option>
                            <option value="paid" @selected($purchaseOrder->payment_status === 'paid')>Paid</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Purchase Order WhatsApp Sharing Modal --}}
    <div id="po-whatsapp-modal" style="display: none;" 
         onclick="if(event.target === this) closePoWhatsAppModal()"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-emerald-50/60 dark:bg-emerald-950/30">
                <div class="text-emerald-800 dark:text-emerald-300 font-bold text-sm">
                    <span>Share with Supplier via WhatsApp</span>
                </div>
                <button type="button" onclick="closePoWhatsAppModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg leading-none cursor-pointer">&times;</button>
            </div>
            <form id="po-whatsapp-form" method="POST" action="{{ route('purchase-orders.shareWhatsApp', $purchaseOrder) }}" target="_blank">
                @csrf
                <div class="p-5 space-y-4 text-xs">
                    {{-- 1-Click Inquiry Template Selector --}}
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Pre-built Inquiry Template</label>
                        <select id="po-wa-template-type" name="template_type" onchange="onPoWhatsAppTemplateChange(this.value)" class="w-full px-3 py-2 border rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold">
                            <option value="po_dispatch" selected>PO Dispatch (Official PDF & Order Summary)</option>
                            <option value="stock_inquiry">Stock & Price Inquiry (SKU List & Availability)</option>
                            <option value="payment_confirmation">Payment Confirmation & Remittance (UTR)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Supplier Mobile Number *</label>
                        <input type="text" id="po-wa-phone" name="phone" value="{{ $purchaseOrder->supplier?->phone }}" required class="w-full px-3 py-2 border rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono">
                    </div>

                    <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 space-y-1 text-[11px]">
                        <div class="flex justify-between font-bold text-slate-800 dark:text-slate-200">
                            <span>PO #{{ $purchaseOrder->po_number }}</span>
                            <span>₹{{ number_format((float) $purchaseOrder->total, 2) }}</span>
                        </div>
                        <div class="text-slate-500">{{ $purchaseOrder->items->count() }} item(s) • {{ $purchaseOrder->supplier?->name }}</div>
                    </div>

                    {{-- Attached PDF Document Feature Card (for PO Dispatch) --}}
                    <div id="po-wa-pdf-card" class="p-3.5 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-[11px] space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-emerald-800 dark:text-emerald-300">Official PO PDF Document Included</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-200/80 dark:bg-emerald-800 text-emerald-900 dark:text-emerald-100">PDF Linked</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                            A secure, official PDF link is generated and included inside the WhatsApp message so your vendor can view and download the signed document with one tap.
                        </p>
                        <div class="pt-1 border-t border-emerald-200/70 dark:border-emerald-800/60">
                            <label class="inline-flex items-center gap-2 cursor-pointer text-slate-700 dark:text-slate-200 font-semibold select-none">
                                <input type="checkbox" id="po-wa-auto-download" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span>Also download PDF file to this device to attach directly in chat</span>
                            </label>
                        </div>
                    </div>

                    {{-- SKU Quick-Add Chips for Stock & Price Inquiry --}}
                    <div id="po-wa-sku-chips-wrap" style="display: none;" class="p-3 rounded-xl bg-sky-50/60 dark:bg-sky-950/30 border border-sky-200 dark:border-sky-800 space-y-1.5">
                        <span class="font-bold text-sky-800 dark:text-sky-300 text-[11px] block">Click SKU to append to inquiry note:</span>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($purchaseOrder->items as $poItem)
                                <button type="button" onclick="insertSkuIntoPoWaNote('{{ addslashes($poItem->item_name . ($poItem->sku ? ' [SKU: ' . $poItem->sku . ']' : '')) }}')" class="px-2 py-1 rounded text-[10px] font-semibold bg-white dark:bg-slate-800 border border-sky-300 dark:border-sky-700 text-sky-900 dark:text-sky-200 hover:bg-sky-100 dark:hover:bg-sky-900/60 transition cursor-pointer">
                                    + {{ Str::limit($poItem->item_name, 22) }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Payment Selector (for Payment Confirmation) --}}
                    @if($purchaseOrder->vendorPayments->isNotEmpty())
                        <div id="po-wa-payment-selector-wrap" style="display: none;">
                            <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Select Recorded Payment (UTR)</label>
                            <select id="po-wa-payment-id" name="vendor_payment_id" class="w-full px-3 py-2 border rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                                @foreach($purchaseOrder->vendorPayments as $pay)
                                    <option value="{{ $pay->id }}">
                                        {{ $pay->payment_reference }} — ₹{{ number_format((float) $pay->amount, 2) }} (UTR: {{ $pay->transaction_reference ?: 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Additional Note / Dispatch Instructions</label>
                        <textarea id="po-wa-note" name="custom_note" rows="2" placeholder="e.g. Please deliver by Thursday evening or dispatch through safe transport..." class="w-full px-3 py-2 border rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
                    </div>
                </div>
                <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex items-center justify-between gap-2 flex-wrap">
                    <a href="{{ route('purchase-orders.pdf', $purchaseOrder) }}" class="btn-secondary text-xs">
                        <span>Download PDF</span>
                    </a>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closePoWhatsAppModal()" class="btn-secondary">Cancel</button>
                        <button type="button" onclick="sendPoWhatsAppWithPdf()" class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs cursor-pointer">
                            <span>Share on WhatsApp</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Purchase Order Email Dispatch Modal --}}
    <div id="po-email-modal" style="display: none;" 
         onclick="if(event.target === this) closePoEmailModal()"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-blue-50/60 dark:bg-blue-950/30">
                <div class="text-blue-800 dark:text-blue-300 font-bold text-sm">
                    <span>Send Communication to Supplier via Email</span>
                </div>
                <button type="button" onclick="closePoEmailModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg leading-none cursor-pointer">&times;</button>
            </div>
            <form method="POST" action="{{ route('purchase-orders.sendEmail', $purchaseOrder) }}" onsubmit="closePoEmailModal()">
                @csrf
                <div class="p-5 space-y-4 text-xs">
                    {{-- 1-Click Template Selector --}}
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Pre-built Email Template</label>
                        <select id="po-email-template-type" name="template_type" onchange="onPoEmailTemplateChange(this.value)" class="w-full px-3 py-2 border rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold">
                            <option value="po_dispatch" selected>PO Dispatch (Official PDF & Order Summary)</option>
                            <option value="stock_inquiry">Stock & Price Inquiry (SKU List & Availability)</option>
                            <option value="payment_confirmation">Payment Confirmation & Remittance (UTR)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Supplier Email *</label>
                        <input type="email" name="email" value="{{ $purchaseOrder->supplier?->email }}" required class="w-full px-3 py-2 border rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>

                    {{-- Payment Selector (for Payment Confirmation) --}}
                    @if($purchaseOrder->vendorPayments->isNotEmpty())
                        <div id="po-email-payment-selector-wrap" style="display: none;">
                            <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Select Recorded Payment (UTR)</label>
                            <select name="vendor_payment_id" class="w-full px-3 py-2 border rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                                @foreach($purchaseOrder->vendorPayments as $pay)
                                    <option value="{{ $pay->id }}">
                                        {{ $pay->payment_reference }} — ₹{{ number_format((float) $pay->amount, 2) }} (UTR: {{ $pay->transaction_reference ?: 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Subject</label>
                        <input type="text" id="po-email-subject" name="subject" value="Purchase Order #{{ $purchaseOrder->po_number }} — Precision IT Systems" required class="w-full px-3 py-2 border rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Custom Message / Order Instructions</label>
                        <textarea id="po-email-message" name="custom_message" rows="4" placeholder="Add specific order notes, delivery timeframe or terms..." class="w-full px-3 py-2 border rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">Please find attached our official Purchase Order #{{ $purchaseOrder->po_number }} for immediate processing. Kindly confirm delivery timeline and dispatch status.</textarea>
                    </div>
                </div>
                <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex items-center justify-end gap-2">
                    <button type="button" onclick="closePoEmailModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-xs cursor-pointer">
                        <span>Send Email</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @php
        $vendorContactName = $purchaseOrder->supplier?->contact_person ?: ($purchaseOrder->supplier?->name ?: 'Vendor Partner');
        $poEmailPresets = [
            'po_dispatch' => [
                'subject' => "Purchase Order #{$purchaseOrder->po_number} — Precision IT Systems",
                'message' => "Please find attached our official Purchase Order #{$purchaseOrder->po_number} for immediate processing. Kindly confirm delivery timeline and dispatch status."
            ],
            'stock_inquiry' => [
                'subject' => "Stock & Price Inquiry for PO #{$purchaseOrder->po_number} — Precision IT Systems",
                'message' => "Dear {$vendorContactName},\n\nKindly check inventory availability and furnish your best dealer quotation for the hardware requirements on PO #{$purchaseOrder->po_number}.\n\nLooking forward to your quick confirmation."
            ],
            'payment_confirmation' => [
                'subject' => "Payment Remittance Advice — PO #{$purchaseOrder->po_number} — Precision IT Systems",
                'message' => "Dear {$vendorContactName},\n\nWe have processed payment against Purchase Order #{$purchaseOrder->po_number}.\nAmount Paid: ₹" . number_format((float) $purchaseOrder->amount_paid, 2) . "\nStatus: " . ucfirst(str_replace('_', ' ', $purchaseOrder->payment_status)) . "\n\nPlease find the transaction remittance advice and UTR reference attached."
            ]
        ];
    @endphp

    <script>
        const poPdfDownloadUrl = @json(route('purchase-orders.pdf', $purchaseOrder));
        const poShareWhatsAppUrl = @json(route('purchase-orders.shareWhatsApp', $purchaseOrder));
        const poEmailPresets = {!! json_encode($poEmailPresets) !!};

        function onPoWhatsAppTemplateChange(val) {
            const pdfCard = document.getElementById('po-wa-pdf-card');
            const paymentSelector = document.getElementById('po-wa-payment-selector-wrap');
            const skuChips = document.getElementById('po-wa-sku-chips-wrap');
            const noteTextarea = document.getElementById('po-wa-note');

            if (val === 'po_dispatch') {
                if (pdfCard) pdfCard.style.display = 'block';
                if (paymentSelector) paymentSelector.style.display = 'none';
                if (skuChips) skuChips.style.display = 'none';
                if (noteTextarea) noteTextarea.placeholder = 'e.g. Please deliver by Thursday evening or dispatch through safe transport...';
            } else if (val === 'stock_inquiry') {
                if (pdfCard) pdfCard.style.display = 'none';
                if (paymentSelector) paymentSelector.style.display = 'none';
                if (skuChips) skuChips.style.display = 'block';
                if (noteTextarea) noteTextarea.placeholder = 'e.g. Urgent requirement for project site. Please quote dealer prices for items listed above...';
            } else if (val === 'payment_confirmation') {
                if (pdfCard) pdfCard.style.display = 'none';
                if (paymentSelector) paymentSelector.style.display = 'block';
                if (skuChips) skuChips.style.display = 'none';
                if (noteTextarea) noteTextarea.placeholder = 'e.g. Payment processed via NEFT. Kindly acknowledge and send stamped invoice...';
            }
        }

        function onPoEmailTemplateChange(val) {
            const subjectInput = document.getElementById('po-email-subject');
            const messageInput = document.getElementById('po-email-message');
            const paymentSelector = document.getElementById('po-email-payment-selector-wrap');

            if (poEmailPresets[val]) {
                if (subjectInput) subjectInput.value = poEmailPresets[val].subject;
                if (messageInput) messageInput.value = poEmailPresets[val].message;
            }
            if (paymentSelector) {
                paymentSelector.style.display = (val === 'payment_confirmation') ? 'block' : 'none';
            }
        }

        function insertSkuIntoPoWaNote(skuText) {
            const noteTextarea = document.getElementById('po-wa-note');
            if (!noteTextarea) return;
            if (noteTextarea.value.trim().length > 0) {
                noteTextarea.value += ', ' + skuText;
            } else {
                noteTextarea.value = 'Inquiry for: ' + skuText;
            }
        }

        async function sendPoWhatsAppWithPdf() {
            const phoneInput = document.getElementById('po-wa-phone');
            const noteInput = document.getElementById('po-wa-note');
            const templateType = document.getElementById('po-wa-template-type')?.value || 'po_dispatch';
            const paymentId = document.getElementById('po-wa-payment-id')?.value || '';
            const autoDownload = document.getElementById('po-wa-auto-download')?.checked;
            const phone = phoneInput ? phoneInput.value.trim() : '';

            if (!phone) {
                alert('Please enter supplier mobile number.');
                phoneInput?.focus();
                return;
            }

            if (autoDownload && templateType === 'po_dispatch') {
                const downloadLink = document.createElement('a');
                downloadLink.href = poPdfDownloadUrl;
                downloadLink.download = 'PO_' + @json($purchaseOrder->po_number) + '.pdf';
                document.body.appendChild(downloadLink);
                downloadLink.click();
                setTimeout(() => downloadLink.remove(), 1000);
            }

            try {
                const formData = new FormData();
                formData.append('phone', phone);
                formData.append('template_type', templateType);
                if (paymentId) formData.append('vendor_payment_id', paymentId);
                formData.append('custom_note', noteInput ? noteInput.value : '');
                formData.append('_token', @json(csrf_token()));

                const res = await fetch(poShareWhatsAppUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                const data = await res.json();
                if (data && data.whatsapp_url) {
                    window.open(data.whatsapp_url, '_blank');
                } else {
                    document.getElementById('po-whatsapp-form').submit();
                }
            } catch (e) {
                document.getElementById('po-whatsapp-form').submit();
            }

            closePoWhatsAppModal();
        }

        function openPoWhatsAppModal() {
            const el = document.getElementById('po-whatsapp-modal');
            if (el) el.style.display = 'flex';
        }
        function closePoWhatsAppModal() {
            const el = document.getElementById('po-whatsapp-modal');
            if (el) el.style.display = 'none';
        }
        function openPoEmailModal() {
            const el = document.getElementById('po-email-modal');
            if (el) el.style.display = 'flex';
        }
        function closePoEmailModal() {
            const el = document.getElementById('po-email-modal');
            if (el) el.style.display = 'none';
        }

        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePoWhatsAppModal();
                closePoEmailModal();
            }
        });
    </script>
    {{-- Reject PO Modal (Admin Only) --}}
    <div id="rejectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Reject Purchase Order #{{ $purchaseOrder->po_number }}</h3>
            <p class="text-xs text-slate-500 mb-4">Please provide a reason for rejecting this procurement request so staff can revise it.</p>
            <form method="POST" action="{{ route('purchase-orders.reject', $purchaseOrder) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Rejection Reason</label>
                    <textarea name="rejection_reason" rows="3" class="crm-input text-xs w-full" placeholder="e.g. Budget limit exceeded, wrong pricing, requires renegotiation..."></textarea>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" class="btn-secondary">Cancel</button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white transition shadow-xs cursor-pointer">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
