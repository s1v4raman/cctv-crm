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
                    @if($purchaseOrder->supplier?->phone)
                        <button type="button" onclick="openPoWhatsAppModal()" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs cursor-pointer">
                            <span>WhatsApp PO</span>
                        </button>
                    @endif
                    @if($purchaseOrder->supplier?->email)
                        <button type="button" onclick="openPoEmailModal()" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-xs cursor-pointer">
                            <span>Email PO</span>
                        </button>
                    @endif
                    <a href="{{ route('suppliers.show', $purchaseOrder->supplier) }}" class="btn-secondary">
                        Supplier Profile
                    </a>
                    @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
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

        {{-- Section 1: PO Overview Two-Column Table (5.5) --}}
        <div class="crm-section">
            <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-200 dark:border-slate-800">
                <h3 class="m-0 border-0 pb-0">Purchase Order Details</h3>
                <form method="POST" action="{{ route('purchase-orders.updateStatus', $purchaseOrder) }}" class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <select aria-label="Status" name="status" class="crm-select text-xs py-1" style="height: 38px; width: 140px;">
                        <option value="draft" @selected($purchaseOrder->status === 'draft')>Draft</option>
                        <option value="ordered" @selected($purchaseOrder->status === 'ordered')>Ordered</option>
                        <option value="cancelled" @selected($purchaseOrder->status === 'cancelled')>Cancelled</option>
                    </select>
                    <button type="submit" class="btn-secondary">Save</button>
                </form>
            </div>

            <table class="crm-table">
                <tbody>
                    <tr>
                        <td class="w-1/3 text-slate-500 font-bold">PO Number</td>
                        <td class="w-2/3 text-slate-900 dark:text-white font-mono">{{ $purchaseOrder->po_number }}</td>
                    </tr>
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

        {{-- Section 3: Inwarding Stock (if applicable) --}}
        @if(in_array($purchaseOrder->status, ['ordered', 'partially_received']))
            <div class="crm-section">
                <h3>Inward Received Stock</h3>
                <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}">
                    @csrf
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
                                    <td class="font-bold text-slate-900 dark:text-white">{{ $item->item_name }}</td>
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
                            Save
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Section 4: Payment Tracking --}}
        <div class="crm-section">
            <h3>Vendor Payment Tracking</h3>
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
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-emerald-50/60 dark:bg-emerald-950/30">
                <div class="text-emerald-800 dark:text-emerald-300 font-bold text-sm">
                    <span>Share Purchase Order via WhatsApp</span>
                </div>
                <button type="button" onclick="closePoWhatsAppModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg leading-none cursor-pointer">&times;</button>
            </div>
            <form method="POST" action="{{ route('purchase-orders.shareWhatsApp', $purchaseOrder) }}" target="_blank" onsubmit="closePoWhatsAppModal()">
                @csrf
                <div class="p-5 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Supplier Mobile Number *</label>
                        <input type="text" name="phone" value="{{ $purchaseOrder->supplier?->phone }}" required class="w-full px-3 py-2 border rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono">
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 space-y-1 text-[11px]">
                        <div class="flex justify-between font-bold text-slate-800 dark:text-slate-200">
                            <span>PO #{{ $purchaseOrder->po_number }}</span>
                            <span>₹{{ number_format((float) $purchaseOrder->total, 2) }}</span>
                        </div>
                        <div class="text-slate-500">{{ $purchaseOrder->items->count() }} item(s) • {{ $purchaseOrder->supplier?->name }}</div>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Additional Note / Dispatch Instructions</label>
                        <textarea name="custom_note" rows="2" placeholder="e.g. Please deliver by Thursday evening or dispatch through safe transport..." class="w-full px-3 py-2 border rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
                    </div>
                </div>
                <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex items-center justify-end gap-2">
                    <button type="button" onclick="closePoWhatsAppModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs cursor-pointer">
                        <span>Open WhatsApp & Send</span>
                    </button>
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
                    <span>Dispatch Purchase Order via Email</span>
                </div>
                <button type="button" onclick="closePoEmailModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg leading-none cursor-pointer">&times;</button>
            </div>
            <form method="POST" action="{{ route('purchase-orders.sendEmail', $purchaseOrder) }}" onsubmit="closePoEmailModal()">
                @csrf
                <div class="p-5 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Supplier Email *</label>
                        <input type="email" name="email" value="{{ $purchaseOrder->supplier?->email }}" required class="w-full px-3 py-2 border rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Subject</label>
                        <input type="text" name="subject" value="Purchase Order #{{ $purchaseOrder->po_number }} — Precision IT Systems" required class="w-full px-3 py-2 border rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Custom Message / Order Instructions</label>
                        <textarea name="custom_message" rows="3" placeholder="Add specific order notes, delivery timeframe or terms..." class="w-full px-3 py-2 border rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">Please find attached our official Purchase Order #{{ $purchaseOrder->po_number }} for immediate processing. Kindly confirm delivery timeline and dispatch status.</textarea>
                    </div>
                </div>
                <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex items-center justify-end gap-2">
                    <button type="button" onclick="closePoEmailModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-xs cursor-pointer">
                        <span>Send Official PO Email</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
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
</x-app-layout>
