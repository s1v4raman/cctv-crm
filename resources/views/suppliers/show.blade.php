<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('suppliers.index') }}" class="btn-back">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-bold leading-tight text-gray-900">{{ $supplier->name }}</h2>
                        @if($supplier->is_active)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Active</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">Inactive</span>
                        @endif
                    </div>
                    @if($supplier->company_name)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $supplier->company_name }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('purchase-orders.create', ['supplier_id' => $supplier->id]) }}" class="btn-create-po">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    New Purchase Order
                </a>
                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-head-secondary">
                    ✏️ Edit
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

        .btn-create-po {
            display: inline-flex; align-items: center;
            background-color: #4f46e5; color: #ffffff !important;
            padding: 0.5rem 1rem; border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 700; text-decoration: none;
            box-shadow: 0 1px 2px rgba(79, 70, 229, 0.2);
            transition: all 0.15s ease-in-out; border: 1px solid #4338ca;
        }
        .btn-create-po:hover { background-color: #4338ca; transform: translateY(-1px); }

        .btn-head-secondary {
            display: inline-flex; align-items: center;
            background-color: #ffffff; color: #334155 !important;
            padding: 0.5rem 1rem; border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 600; text-decoration: none;
            border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.15s ease-in-out;
        }
        .btn-head-secondary:hover { background-color: #f8fafc; color: #0f172a !important; }

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

        .p-table { width:100%; border-collapse:collapse; }
        .p-table thead { background:#f8fafc; border-bottom: 1px solid #e2e8f0; }
        .p-table thead th {
            padding:.75rem 1.25rem; font-size:.67rem; font-weight:700;
            text-transform:uppercase; letter-spacing:.06em; color:#64748b; text-align:left;
        }
        .p-table thead th.right { text-align:right; }
        .p-table tbody tr { border-bottom:1px solid #f1f5f9; }
        .p-table tbody td { padding:.85rem 1.25rem; font-size:.85rem; color:#374151; vertical-align:middle; }
        .p-table tbody td.right { text-align:right; }

        .badge-status {
            display:inline-flex; padding:.25rem .65rem; border-radius:9999px;
            font-size:.72rem; font-weight:700; text-transform:capitalize;
        }
        .status-draft              { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
        .status-ordered            { background:#e0e7ff; color:#3730a3; border:1px solid #c7d2fe; }
        .status-partially_received { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
        .status-received           { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
        .status-cancelled          { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
    </style>

    <div class="pg-wrap">
        <div class="pg-inner">

            @if (session('status'))
                <div class="mb-4 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Vendor Details Overview --}}
            <div class="pg-card">
                <div class="section-head">
                    <h3>Vendor Profile & Contact Information</h3>
                </div>
                <div class="info-grid">
                    <div>
                        <div class="info-label">Contact Person</div>
                        <div class="info-value">{{ $supplier->contact_person ?: '—' }}</div>
                    </div>
                    <div>
                        <div class="info-label">Phone / WhatsApp</div>
                        <div class="info-value">
                            @if($supplier->phone)
                                <a href="tel:{{ $supplier->phone }}" class="text-indigo-600 hover:underline">📞 {{ $supplier->phone }}</a>
                            @else — @endif
                        </div>
                    </div>
                    <div>
                        <div class="info-label">Email</div>
                        <div class="info-value text-xs font-semibold">
                            @if($supplier->email)
                                <a href="mailto:{{ $supplier->email }}" class="text-indigo-600 hover:underline">✉️ {{ $supplier->email }}</a>
                            @else — @endif
                        </div>
                    </div>
                    <div>
                        <div class="info-label">GSTIN / Tax ID</div>
                        <div class="info-value font-mono text-xs">{{ $supplier->gst_number ?: 'Unregistered' }}</div>
                    </div>
                    <div>
                        <div class="info-label">Payment Terms</div>
                        <div class="info-value text-sm">{{ $supplier->payment_terms ?: 'Standard' }}</div>
                    </div>
                    <div>
                        <div class="info-label">Total PO Spend</div>
                        <div class="info-value text-emerald-600 font-extrabold">₹{{ number_format($supplier->totalPurchaseVolume(), 2) }}</div>
                    </div>
                    <div class="col-span-2">
                        <div class="info-label">Warehouse Address</div>
                        <div class="info-value text-xs font-normal text-gray-700">
                            📍 {{ $supplier->address ?: '—' }}{{ $supplier->city ? ", {$supplier->city}" : '' }}{{ $supplier->state ? ", {$supplier->state}" : '' }}
                        </div>
                    </div>
                    @if($supplier->notes)
                    <div class="col-span-4">
                        <div class="info-label">Vendor Notes & Bank Details</div>
                        <div class="info-value font-normal text-xs text-gray-700 whitespace-pre-line">{{ $supplier->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Purchase Orders History --}}
            <div class="pg-card">
                <div class="section-head">
                    <div>
                        <h3>Procurement History</h3>
                        <p class="text-xs text-gray-500 mt-0.5">All purchase orders issued to {{ $supplier->name }}</p>
                    </div>
                    <a href="{{ route('purchase-orders.create', ['supplier_id' => $supplier->id]) }}" class="text-xs font-bold text-indigo-600 hover:underline">
                        + New Order &rarr;
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="p-table">
                        <thead>
                            <tr>
                                <th>PO Number</th>
                                <th>Order Date</th>
                                <th>Items</th>
                                <th class="right">Total Amount</th>
                                <th class="right">Paid</th>
                                <th>PO Status</th>
                                <th class="right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supplier->purchaseOrders as $po)
                            <tr>
                                <td>
                                    <a href="{{ route('purchase-orders.show', $po) }}" class="font-mono font-bold text-indigo-600 hover:underline">
                                        {{ $po->po_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="text-xs font-semibold text-gray-800">{{ $po->order_date->format('d M Y') }}</div>
                                    @if($po->expected_delivery_date)
                                        <div class="text-[11px] text-gray-400">Exp: {{ $po->expected_delivery_date->format('d M Y') }}</div>
                                    @endif
                                </td>
                                <td class="text-xs">
                                    <span class="font-bold text-gray-800">{{ $po->items->count() }} line items</span>
                                    <div class="text-[11px] text-gray-500 truncate max-w-xs">
                                        {{ $po->items->pluck('item_name')->join(', ') }}
                                    </div>
                                </td>
                                <td class="right font-bold text-gray-900">
                                    ₹{{ number_format($po->total, 2) }}
                                </td>
                                <td class="right font-semibold text-emerald-600">
                                    ₹{{ number_format($po->amount_paid, 2) }}
                                </td>
                                <td>
                                    <span class="badge-status status-{{ $po->status }}">
                                        {{ str_replace('_', ' ', $po->status) }}
                                    </span>
                                </td>
                                <td class="right">
                                    <a href="{{ route('purchase-orders.show', $po) }}" class="text-xs font-bold text-indigo-600 hover:underline">
                                        View & Inward &rarr;
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-sm text-gray-400">
                                    No purchase orders created for this supplier yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
