<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('purchase-orders.index') }}" class="btn-back">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-bold leading-tight text-gray-900 font-mono">{{ $purchaseOrder->po_number }}</h2>
                        <span class="badge-status status-{{ $purchaseOrder->status }}">
                            {{ str_replace('_', ' ', $purchaseOrder->status) }}
                        </span>
                        <span class="badge-payment pay-{{ $purchaseOrder->payment_status }}">
                            Payment: {{ str_replace('_', ' ', $purchaseOrder->payment_status) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Supplier: <strong class="text-gray-800">{{ $purchaseOrder->supplier->name }}</strong> &middot; 
                        Issued on {{ $purchaseOrder->order_date->format('d M Y') }} by {{ $purchaseOrder->createdBy->name ?? 'System' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if(auth()->user()->isAdmin() && in_array($purchaseOrder->status, ['draft', 'cancelled']))
                    <form method="POST" action="{{ route('purchase-orders.destroy', $purchaseOrder) }}" onsubmit="return confirm('Are you sure you want to delete this purchase order?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete">
                            🗑️ Delete PO
                        </button>
                    </form>
                @endif
                <a href="{{ route('purchase-orders.index') }}" class="btn-head-secondary">
                    Back to All POs
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .pg-wrap  { background:#f1f5f9; min-height:100vh; padding:1.75rem 0 3rem; }
        .pg-inner { max-width:1180px; margin:0 auto; padding:0 1.25rem; }

        .btn-back {
            display: inline-flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 0.5rem;
            background-color: #ffffff; color: #475569 !important;
            border: 1px solid #cbd5e1; text-decoration: none;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .btn-back:hover { background-color: #f8fafc; color: #0f172a !important; }

        .btn-head-secondary {
            display: inline-flex; align-items: center;
            background-color: #ffffff; color: #334155 !important;
            padding: 0.5rem 1rem; border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 600; text-decoration: none;
            border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.15s ease-in-out;
        }
        .btn-head-secondary:hover { background-color: #f8fafc; color: #0f172a !important; }

        .btn-delete {
            display: inline-flex; align-items: center;
            background-color: #fef2f2; color: #dc2626 !important;
            padding: 0.5rem 0.9rem; border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 600; border: 1px solid #fecaca;
            cursor: pointer; transition: all 0.15s ease-in-out;
        }
        .btn-delete:hover { background-color: #fee2e2; }

        .pg-card {
            background:#fff;
            border-radius:1rem;
            border:1px solid rgba(99,102,241,.08);
            box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(99,102,241,.06);
            overflow:hidden; margin-bottom:1.5rem;
        }

        .section-head {
            display:flex; align-items:center; justify-content:space-between;
            padding:1.1rem 1.5rem; border-bottom:1px solid #f1f5f9;
        }
        .section-head h3 { font-size:1rem; font-weight:700; color:#0f172a; }

        .info-grid {
            display:grid; grid-template-columns:repeat(2,1fr);
            gap:1.5rem; padding:1.5rem;
        }
        @media(min-width:640px){ .info-grid{grid-template-columns:repeat(4,1fr);} }
        .info-label {
            font-size:.72rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.06em; color:#64748b; margin-bottom:.35rem;
        }
        .info-value { font-size:.95rem; font-weight:700; color:#0f172a; }

        .badge-status {
            display:inline-flex; padding:.25rem .65rem; border-radius:9999px;
            font-size:.72rem; font-weight:700; text-transform:capitalize;
        }
        .status-draft              { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
        .status-ordered            { background:#e0e7ff; color:#3730a3; border:1px solid #c7d2fe; }
        .status-partially_received { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
        .status-received           { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
        .status-cancelled          { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }

        .badge-payment {
            display:inline-flex; padding:.2rem .55rem; border-radius:9999px;
            font-size:.7rem; font-weight:700; text-transform:capitalize;
        }
        .pay-unpaid         { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
        .pay-partially_paid { background:#fef3c7; color:#b45309; border:1px solid #fde68a; }
        .pay-paid           { background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; }

        .p-table { width:100%; border-collapse:collapse; }
        .p-table thead { background:#f8fafc; border-bottom: 1px solid #e2e8f0; }
        .p-table thead th {
            padding:.75rem 1.25rem; font-size:.67rem; font-weight:700;
            text-transform:uppercase; letter-spacing:.06em; color:#64748b; text-align:left;
        }
        .p-table thead th.right { text-align:right; }
        .p-table thead th.center { text-align:center; }
        .p-table tbody tr { border-bottom:1px solid #f1f5f9; }
        .p-table tbody td { padding:.85rem 1.25rem; font-size:.85rem; color:#374151; vertical-align:middle; }
        .p-table tbody td.right { text-align:right; }
        .p-table tbody td.center { text-align:center; }

        .receipt-summary-container { display:flex; justify-content:flex-end; padding:1.5rem; }
        .receipt-box { width:100%; max-width:380px; background:#f8fafc; border-radius:0.75rem; padding:1.25rem; border:1px solid #e2e8f0; }
        .receipt-row { display:flex; justify-content:space-between; font-size:.875rem; padding:.35rem 0; color:#475569; }
        .receipt-row.bold { font-size:1.15rem; font-weight:800; border-top:1px solid #cbd5e1; margin-top:.5rem; padding-top:.75rem; color:#0f172a; }
        .receipt-row.paid { color:#059669; font-weight:700; }
        .receipt-row.due { color:#b91c1c; font-weight:800; font-size:1rem; border-top:1px dashed #cbd5e1; padding-top:.5rem; margin-top:.35rem; }

        .form-label {
            display:block; font-size:.75rem; font-weight:700;
            text-transform:uppercase; letter-spacing:.05em;
            color:#475569; margin-bottom:.35rem;
        }
        .form-input, .form-select {
            width:100%; border:1px solid #cbd5e1; border-radius:.5rem;
            padding:.55rem .8rem; font-size:.85rem; color:#1e293b;
            background:#ffffff; outline:none; transition:all .15s;
        }
        .form-input:focus, .form-select:focus { border-color:#4f46e5; box-shadow:0 0 0 3px rgba(79,70,229,.15); }

        .btn-inward {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.65rem 1.4rem; border-radius: 0.5rem;
            background-color: #16a34a; color: #ffffff !important;
            font-size: 0.875rem; font-weight: 700;
            border: 1px solid #15803d; cursor: pointer;
            box-shadow: 0 1px 2px rgba(22, 163, 74, 0.2);
            transition: all 0.15s ease-in-out;
        }
        .btn-inward:hover { background-color: #15803d; transform: translateY(-1px); }

        .btn-update-status {
            display: inline-flex; align-items: center;
            padding: 0.55rem 1.1rem; border-radius: 0.5rem;
            background-color: #4f46e5; color: #ffffff !important;
            font-size: 0.85rem; font-weight: 700;
            border: 1px solid #4338ca; cursor: pointer;
            transition: all 0.15s ease-in-out;
        }
        .btn-update-status:hover { background-color: #4338ca; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if (session('status'))
                <div class="mb-4 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- PO Overview Card --}}
            <div class="pg-card">
                <div class="section-head">
                    <h3>Purchase Order Overview</h3>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('purchase-orders.updateStatus', $purchaseOrder) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-select text-xs py-1.5 px-3 font-semibold">
                                <option value="draft" @selected($purchaseOrder->status === 'draft')>Draft</option>
                                <option value="ordered" @selected($purchaseOrder->status === 'ordered')>Ordered</option>
                                <option value="cancelled" @selected($purchaseOrder->status === 'cancelled')>Cancelled</option>
                            </select>
                            <button type="submit" class="btn-update-status">Update Status</button>
                        </form>
                    </div>
                </div>

                <div class="info-grid">
                    <div>
                        <div class="info-label">PO Number</div>
                        <div class="info-value font-mono text-indigo-600">{{ $purchaseOrder->po_number }}</div>
                    </div>
                    <div>
                        <div class="info-label">Supplier / Distributor</div>
                        <div class="info-value">
                            <a href="{{ route('suppliers.show', $purchaseOrder->supplier) }}" class="text-indigo-600 hover:underline">
                                {{ $purchaseOrder->supplier->name }}
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="info-label">Order Date</div>
                        <div class="info-value">{{ $purchaseOrder->order_date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="info-label">Expected Delivery</div>
                        <div class="info-value">
                            {{ $purchaseOrder->expected_delivery_date?->format('d M Y') ?? 'Immediate' }}
                        </div>
                    </div>
                    <div>
                        <div class="info-label">Supplier Contact</div>
                        <div class="info-value text-xs font-semibold">
                            {{ $purchaseOrder->supplier->contact_person ?: '—' }} ({{ $purchaseOrder->supplier->phone ?: 'No phone' }})
                        </div>
                    </div>
                    <div>
                        <div class="info-label">Supplier GSTIN</div>
                        <div class="info-value font-mono text-xs">{{ $purchaseOrder->supplier->gst_number ?: 'Unregistered' }}</div>
                    </div>
                    <div>
                        <div class="info-label">Payment Terms</div>
                        <div class="info-value text-xs">{{ $purchaseOrder->supplier->payment_terms ?: 'Standard' }}</div>
                    </div>
                    <div>
                        <div class="info-label">Created By</div>
                        <div class="info-value text-xs">{{ $purchaseOrder->createdBy->name ?? 'System' }}</div>
                    </div>
                    @if($purchaseOrder->notes)
                    <div class="col-span-2 sm:col-span-4">
                        <div class="info-label">Procurement & Delivery Notes</div>
                        <div class="info-value font-normal text-xs text-gray-700 whitespace-pre-line">{{ $purchaseOrder->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Ordered Line Items & Receiving --}}
            <div class="pg-card">
                <div class="section-head">
                    <div>
                        <h3>Ordered Line Items & Receiving Progress</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Tracking quantities ordered vs inwarded into warehouse inventory</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="p-table">
                        <thead>
                            <tr>
                                <th>Item Description</th>
                                <th>SKU / Model</th>
                                <th>Catalog Link</th>
                                <th class="center">Ordered Qty</th>
                                <th class="center">Received Qty</th>
                                <th class="right">Unit Cost</th>
                                <th class="right">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $item)
                            <tr>
                                <td class="font-bold text-gray-900">{{ $item->item_name }}</td>
                                <td class="font-mono text-xs text-gray-600">{{ $item->sku ?: '—' }}</td>
                                <td>
                                    @if($item->product)
                                        <a href="{{ route('products.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                                            ✓ {{ $item->product->name }}
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">Non-Catalog</span>
                                    @endif
                                </td>
                                <td class="center font-bold text-gray-800">{{ $item->quantity_ordered }}</td>
                                <td class="center font-extrabold" style="color:{{ $item->quantity_received >= $item->quantity_ordered ? '#059669' : ($item->quantity_received > 0 ? '#d97706' : '#64748b') }}">
                                    {{ $item->quantity_received }} / {{ $item->quantity_ordered }}
                                </td>
                                <td class="right text-gray-700">₹{{ number_format($item->unit_cost, 2) }}</td>
                                <td class="right font-bold text-gray-900">₹{{ number_format($item->total_cost, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="receipt-summary-container">
                    <div class="receipt-box">
                        <div class="receipt-row">
                            <span>Subtotal:</span>
                            <span class="font-bold text-gray-800">₹{{ number_format($purchaseOrder->subtotal, 2) }}</span>
                        </div>
                        <div class="receipt-row">
                            <span>GST Tax ({{ $purchaseOrder->tax_percent }}%):</span>
                            <span class="font-bold text-gray-800">₹{{ number_format($purchaseOrder->tax_amount, 2) }}</span>
                        </div>
                        <div class="receipt-row">
                            <span>Shipping / Freight:</span>
                            <span class="font-bold text-gray-800">₹{{ number_format($purchaseOrder->shipping_cost, 2) }}</span>
                        </div>
                        <div class="receipt-row bold">
                            <span>Grand Total:</span>
                            <span class="text-indigo-600">₹{{ number_format($purchaseOrder->total, 2) }}</span>
                        </div>
                        <div class="receipt-row paid">
                            <span>Amount Paid to Vendor:</span>
                            <span>₹{{ number_format($purchaseOrder->amount_paid, 2) }}</span>
                        </div>
                        <div class="receipt-row due">
                            <span>Vendor Balance Due:</span>
                            <span>₹{{ number_format($purchaseOrder->balanceDue(), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Goods Receipt & Inwarding Section --}}
            @if(in_array($purchaseOrder->status, ['ordered', 'partially_received']))
            <div class="pg-card">
                <div class="section-head bg-emerald-50/50">
                    <div>
                        <h3 class="text-emerald-950 flex items-center gap-2">
                            <span>📥</span> Inward Received Stock into Central Warehouse
                        </h3>
                        <p class="text-xs text-emerald-800 mt-0.5">
                            When vendor delivery arrives, enter quantities received to automatically increase inventory & create audit movements.
                        </p>
                    </div>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}">
                        @csrf
                        <div class="overflow-x-auto mb-4">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 uppercase font-bold">
                                        <th class="py-2.5 px-3">Item Description</th>
                                        <th class="py-2.5 px-3 center">Ordered</th>
                                        <th class="py-2.5 px-3 center">Already Received</th>
                                        <th class="py-2.5 px-3 center">Pending Units</th>
                                        <th class="py-2.5 px-3 w-40 text-right">Inward Now Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseOrder->items as $item)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-3 px-3 font-bold text-gray-900">{{ $item->item_name }}</td>
                                        <td class="py-3 px-3 center font-semibold">{{ $item->quantity_ordered }}</td>
                                        <td class="py-3 px-3 center font-semibold text-emerald-600">{{ $item->quantity_received }}</td>
                                        <td class="py-3 px-3 center font-bold text-amber-600">{{ $item->pendingQuantity() }}</td>
                                        <td class="py-3 px-3 text-right">
                                            @if($item->pendingQuantity() > 0)
                                                <input type="number" name="received[{{ $item->id }}]" min="0" max="{{ $item->pendingQuantity() }}" value="{{ $item->pendingQuantity() }}" class="form-input text-right font-bold text-xs w-28 ml-auto">
                                            @else
                                                <span class="text-xs font-bold text-emerald-600">✓ Completed</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center justify-end pt-2">
                            <button type="submit" class="btn-inward">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Inward Stock into Central Warehouse
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @elseif($purchaseOrder->status === 'received')
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 px-6 flex items-center gap-3 text-emerald-900 font-bold text-sm mb-6">
                <svg class="w-6 h-6 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <div>All ordered items have been 100% received and inwarded into central inventory.</div>
                    <div class="text-xs font-normal text-emerald-700 mt-0.5">Stock records and movement ledgers updated.</div>
                </div>
            </div>
            @endif

            {{-- Vendor Accounts Payable & Payment Section --}}
            <div class="pg-card">
                <div class="section-head">
                    <div>
                        <h3>Vendor Payment Tracking</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Manage payments made to {{ $purchaseOrder->supplier->name }}</p>
                    </div>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('purchase-orders.updatePayment', $purchaseOrder) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Total Amount Paid to Vendor (₹)</label>
                                <input type="number" step="0.01" min="0" max="{{ $purchaseOrder->total }}" name="amount_paid" value="{{ old('amount_paid', $purchaseOrder->amount_paid) }}" required class="form-input font-bold text-gray-900">
                            </div>

                            <div>
                                <label class="form-label">Payment Status</label>
                                <select name="payment_status" class="form-select font-bold">
                                    <option value="unpaid" @selected($purchaseOrder->payment_status === 'unpaid')>Unpaid (Pending Settlement)</option>
                                    <option value="partially_paid" @selected($purchaseOrder->payment_status === 'partially_paid')>Partially Paid</option>
                                    <option value="paid" @selected($purchaseOrder->payment_status === 'paid')>Paid in Full</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="btn-update-status">
                                💾 Save Payment Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
