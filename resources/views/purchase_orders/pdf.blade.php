<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order {{ $purchaseOrder->po_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
        }
        .header-logo {
            font-size: 24px;
            font-weight: bold;
            color: #0b1a5c;
            vertical-align: top;
        }
        .header-logo span {
            color: #c02428;
        }
        .header-details {
            text-align: right;
            font-size: 9.5px;
            color: #475569;
            vertical-align: top;
            line-height: 1.35;
        }
        .title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            color: #0b1a5c;
            margin-bottom: 14px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .meta-table td {
            padding: 6px 10px;
            font-size: 10px;
            vertical-align: top;
        }
        .addresses-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .addresses-table td {
            width: 50%;
            vertical-align: top;
            padding: 10px;
            border: 1px solid #cbd5e1;
            font-size: 10px;
            line-height: 1.4;
        }
        .address-heading {
            font-size: 11px;
            font-weight: bold;
            color: #0b1a5c;
            text-transform: uppercase;
            margin-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            border: 1px solid #0f172a;
        }
        .items-table th {
            background-color: #0b1a5c;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 7px 6px;
            border: 1px solid #0f172a;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-table td {
            padding: 6px 6px;
            border: 1px solid #cbd5e1;
            font-size: 10px;
            vertical-align: middle;
        }
        .items-table th.right, .items-table td.right {
            text-align: right;
        }
        .items-table th.center, .items-table td.center {
            text-align: center;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .totals-table td {
            padding: 5px 8px;
            font-size: 10.5px;
        }
        .totals-table .label {
            text-align: right;
            font-weight: bold;
            color: #334155;
            width: 78%;
        }
        .totals-table .val {
            text-align: right;
            font-weight: bold;
            color: #0f172a;
            width: 22%;
            border-bottom: 1px solid #e2e8f0;
        }
        .totals-table .grand-row td {
            background-color: #0b1a5c;
            color: #ffffff !important;
            font-size: 12px;
            font-weight: bold;
            padding: 8px 10px;
            border: none;
        }
        .amount-words-box {
            background-color: #f1f5f9;
            border-left: 3px solid #0b1a5c;
            padding: 7px 10px;
            font-size: 10px;
            margin-bottom: 18px;
            color: #1e293b;
        }
        .terms-section {
            margin-top: 15px;
            font-size: 9.5px;
            color: #475569;
            line-height: 1.45;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .terms-title {
            font-weight: bold;
            color: #0f172a;
            font-size: 10px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .terms-list {
            margin: 0;
            padding: 0 0 0 14px;
        }
        .terms-list li {
            margin-bottom: 2.5px;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }
        .signature-table td {
            width: 50%;
            vertical-align: bottom;
            font-size: 10px;
        }
    </style>
</head>
<body>

    {{-- Official Header --}}
    <table class="header-table">
        <tr>
            <td class="header-logo">
                PRECISI<span>O</span>N<br>
                <span style="font-size: 11px; color: #334155; letter-spacing: 2px; font-weight: normal; display: block; margin-top: -3px;">IT SYSTEMS</span>
            </td>
            <td class="header-details">
                <strong>Precision IT Systems (CCTV & Security Solutions)</strong><br>
                Plot No.553, Lig-1, 27th Street, Tamil Nadu Housing Board,<br>
                Avadi, Chennai - 600054, Tamil Nadu.<br>
                Phone: +91-9677257774 &bull; Email: precisionitsystem@gmail.com<br>
                <strong>GSTIN: 33AHLPI3531N1Z8</strong>
            </td>
        </tr>
    </table>

    <div class="title">Official Purchase Order</div>

    {{-- Meta Details --}}
    <table class="meta-table">
        <tr>
            <td><strong>PO Number:</strong> <span style="font-family: monospace; font-size: 11px;">{{ $purchaseOrder->po_number }}</span></td>
            <td><strong>Order Date:</strong> {{ ($purchaseOrder->order_date ?? $purchaseOrder->created_at ?? now())->format('d M Y') }}</td>
            <td style="text-align: right;"><strong>Status:</strong> {{ ucfirst($purchaseOrder->status) }}</td>
        </tr>
        <tr>
            <td><strong>Payment Terms:</strong> {{ $purchaseOrder->payment_terms ?: 'Net 30 Days' }}</td>
            <td><strong>Expected Delivery:</strong> {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('d M Y') : 'Immediate' }}</td>
            <td style="text-align: right;"><strong>Payment Status:</strong> {{ ucfirst($purchaseOrder->payment_status) }}</td>
        </tr>
    </table>

    {{-- Addresses: Vendor Details & Ship-To --}}
    <table class="addresses-table">
        <tr>
            <td>
                <div class="address-heading">Vendor / Supplier Details:</div>
                <strong style="font-size: 11px; color: #0f172a;">{{ $purchaseOrder->supplier?->company_name ?: $purchaseOrder->supplier?->name }}</strong><br>
                @if($purchaseOrder->supplier?->contact_person && $purchaseOrder->supplier->contact_person !== $purchaseOrder->supplier->name)
                    Attn: {{ $purchaseOrder->supplier->contact_person }}<br>
                @endif
                @if($purchaseOrder->supplier?->address)
                    {{ $purchaseOrder->supplier->address }}<br>
                @endif
                @if($purchaseOrder->supplier?->city || $purchaseOrder->supplier?->state)
                    {{ $purchaseOrder->supplier?->city }}{{ $purchaseOrder->supplier?->state ? ', ' . $purchaseOrder->supplier->state : '' }}
                    {{ $purchaseOrder->supplier?->pincode ? ' - ' . $purchaseOrder->supplier->pincode : '' }}<br>
                @endif
                <strong>Mobile:</strong> {{ $purchaseOrder->supplier?->phone ?: 'N/A' }}<br>
                <strong>Email:</strong> {{ $purchaseOrder->supplier?->email ?: 'N/A' }}<br>
                @if($purchaseOrder->supplier?->gstin)
                    <strong>GSTIN:</strong> {{ $purchaseOrder->supplier->gstin }}
                @endif
            </td>
            <td>
                <div class="address-heading">Delivery / Ship-To Location:</div>
                <strong style="font-size: 11px; color: #0f172a;">Precision IT Systems — Central Logistics</strong><br>
                Plot No.553, Lig-1, 27th Street, Tamil Nadu Housing Board,<br>
                Avadi, Chennai - 600054, Tamil Nadu, India.<br>
                <strong>Contact Person:</strong> Sivaraman (+91-9677257774)<br>
                <strong>Receiving Hours:</strong> Monday – Saturday (10:00 AM – 7:00 PM)<br>
                @if($purchaseOrder->notes)
                    <div style="margin-top: 4px; color: #b45309; font-size: 9px;">
                        <strong>Dispatch Note:</strong> {{ $purchaseOrder->notes }}
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Line Items Table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="center" style="width: 5%;">#</th>
                <th style="width: 55%;">Item Name & Specifications</th>
                <th class="center" style="width: 12%;">Qty Ordered</th>
                <th class="right" style="width: 14%;">Unit Cost (₹)</th>
                <th class="right" style="width: 14%;">Total Cost (₹)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchaseOrder->items as $index => $item)
                @php
                    $lineTotal = (float) ($item->total_cost ?: ($item->quantity_ordered * $item->unit_cost));
                @endphp
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>
                        <strong style="color: #0f172a;">{{ $item->item_name }}</strong>
                        @if($item->product && $item->product->model_number)
                            <div style="font-size: 8.5px; color: #64748b;">Model: {{ $item->product->model_number }}</div>
                        @endif
                    </td>
                    <td class="center font-bold">{{ $item->quantity_ordered }}</td>
                    <td class="right">{{ number_format((float) $item->unit_cost, 2) }}</td>
                    <td class="right" style="font-weight: bold;">{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="center" style="padding: 20px; color: #94a3b8;">No items listed on this purchase order.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Financial Totals Table --}}
    <table class="totals-table">
        <tr>
            <td class="label">Subtotal:</td>
            <td class="val">₹{{ number_format((float) $purchaseOrder->subtotal, 2) }}</td>
        </tr>
        @if((float) $purchaseOrder->tax_amount > 0 || (float) $purchaseOrder->tax_percent > 0)
            <tr>
                <td class="label">
                    GST Tax {{ (float) $purchaseOrder->tax_percent > 0 ? '(' . (float) $purchaseOrder->tax_percent . '%)' : '' }}:
                </td>
                <td class="val">₹{{ number_format((float) $purchaseOrder->tax_amount, 2) }}</td>
            </tr>
        @endif
        @if((float) $purchaseOrder->shipping_cost > 0)
            <tr>
                <td class="label">Shipping / Freight:</td>
                <td class="val">₹{{ number_format((float) $purchaseOrder->shipping_cost, 2) }}</td>
            </tr>
        @endif
        <tr class="grand-row">
            <td class="label" style="color: #ffffff;">GRAND TOTAL (INR):</td>
            <td class="val" style="color: #ffffff; text-align: right;">₹{{ number_format((float) $purchaseOrder->total, 2) }}</td>
        </tr>
    </table>

    {{-- Amount in words --}}
    @php
        $amountInWords = ucwords(\App\Models\Quotation::numberToWords($purchaseOrder->total));
    @endphp
    <div class="amount-words-box">
        <strong>Amount Chargeable (in words):</strong> Indian Rupees {{ $amountInWords }} Only.
    </div>

    {{-- Procurement Terms & Conditions --}}
    <div class="terms-section">
        <div class="terms-title">Procurement Terms & Conditions:</div>
        <ol class="terms-list">
            <li>All delivered hardware must be brand new, factory sealed, with original manufacturer warranty and valid serial numbers.</li>
            <li>Original Delivery Challan and GST Tax Invoice mentioning PO #<strong>{{ $purchaseOrder->po_number }}</strong> must accompany the consignment.</li>
            <li>Any damaged, tampered, or non-functional items identified during inward inspection must be replaced immediately.</li>
            <li>Payment will be disbursed as per agreed credit terms ({{ $purchaseOrder->payment_terms ?: 'Net 30 Days' }}) via NEFT / RTGS / Bank Transfer upon delivery verification.</li>
        </ol>
    </div>

    {{-- Signature Section --}}
    <table class="signature-table">
        <tr>
            <td>
                <div style="font-size: 9.5px; color: #64748b; margin-bottom: 40px;">Vendor Acceptance & Seal:</div>
                <div style="border-top: 1px dashed #94a3b8; width: 190px; padding-top: 4px; font-weight: bold;">
                    Authorized Vendor Signatory
                </div>
            </td>
            <td style="text-align: right;">
                <div style="font-size: 9.5px; color: #64748b; margin-bottom: 40px;">For <strong>PRECISION IT SYSTEMS</strong>:</div>
                <div style="border-top: 1px dashed #94a3b8; width: 190px; padding-top: 4px; font-weight: bold; margin-left: auto;">
                    Authorized Procurement Officer
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
