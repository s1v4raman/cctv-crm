<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Goods Receipt Note - {{ $goodsReceipt->grn_number }}</title>
    <style>
        @page {
            margin: 12mm 15mm 15mm 15mm;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5px;
            color: #111827;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        .company-header {
            margin-bottom: 8px;
        }
        .company-name {
            font-size: 13px;
            font-weight: 800;
            color: #000000;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }
        .company-details {
            font-size: 8.5px;
            color: #1f2937;
            line-height: 1.35;
        }

        .doc-title {
            text-align: center;
            font-size: 14px;
            font-weight: 800;
            color: #111827;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 6px 0 10px 0;
            border-bottom: 1.5px solid #111827;
            padding-bottom: 4px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            table-layout: fixed;
        }
        .meta-table td {
            vertical-align: top;
            font-size: 8px;
            line-height: 1.35;
            color: #1f2937;
            padding: 0 4px;
        }
        .meta-heading {
            font-size: 8.5px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 3px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 2px;
        }
        .party-name {
            font-weight: 800;
            color: #000000;
            margin-bottom: 2px;
        }
        .detail-row {
            margin-bottom: 2px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .items-table th {
            border: 1px solid #374151;
            padding: 5px 4px;
            font-size: 7.5px;
            font-weight: 800;
            text-align: center;
            background-color: #f9fafb;
            color: #111827;
            text-transform: uppercase;
        }
        .items-table td {
            border: 1px solid #4b5563;
            padding: 5px 4px;
            font-size: 7.5px;
            vertical-align: top;
        }
        .items-table td.center { text-align: center; }
        .items-table td.right { text-align: right; }
        .items-table .total-row td {
            font-weight: 800;
            background-color: #f9fafb;
            border-top: 1.5px solid #111827;
            border-bottom: 1.5px solid #111827;
        }

        .serial-tag {
            display: inline-block;
            font-family: monospace;
            font-size: 6.5px;
            background-color: #f3f4f6;
            border: 0.5px solid #d1d5db;
            padding: 1px 3px;
            margin: 1px;
            border-radius: 2px;
        }

        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .sign-table td {
            width: 50%;
            vertical-align: top;
            font-size: 8px;
        }
        .sign-box {
            border-top: 1px solid #374151;
            padding-top: 4px;
            margin-top: 35px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    @php
        $companyName = $settings?->company_trade_name ?: ($settings?->company_legal_name ?: 'NP SOLUTIONS');
        $companyAddress = $settings?->company_address ?: 'NO 12 1ST MAIN ROAD SUBRAMANIYAPURAM, CHENNAI - 600 077';
        $companyGstin = $settings?->company_gstin ?: '33DNAPA0957C1ZQ';
        $companyPhone = '9884664108';
        $companyEmail = 'npsolutions.in@gmail.com';
        $companyStateCode = $settings?->company_state_code ?: '33';
        $companyStateName = $settings?->company_state ?: 'Tamil Nadu';

        $supplier = $goodsReceipt->supplier;
        $supplierName = $supplier?->company_name ?: ($supplier?->name ?? 'Vendor');
        $totalAccepted = $goodsReceipt->totalReceivedQty();
        $totalRejected = $goodsReceipt->totalRejectedQty();
        $totalCost = 0.0;
    @endphp

    {{-- Company Header (Top Left) --}}
    <div class="company-header">
        <div class="company-name">{{ $companyName }}</div>
        <div class="company-details">
            {{ $companyAddress }}<br>
            Phone no. : {{ $companyPhone }} | Email : {{ $companyEmail }}<br>
            GSTIN : {{ $companyGstin }} | State: {{ $companyStateCode }}-{{ $companyStateName }}
        </div>
    </div>

    {{-- Document Title --}}
    <div class="doc-title">Goods Receipt Note (GRN)</div>

    {{-- 3-Column Metadata --}}
    <table class="meta-table">
        <tr>
            <td style="width: 36%;">
                <div class="meta-heading">Receipt Details</div>
                <div class="detail-row">GRN No. : <strong>{{ $goodsReceipt->grn_number }}</strong></div>
                <div class="detail-row">Received Date : <strong>{{ $goodsReceipt->received_date?->format('d-m-Y') }}</strong></div>
                <div class="detail-row">PO Reference : <strong>{{ $goodsReceipt->purchaseOrder?->po_number ?? '-' }}</strong></div>
                <div class="detail-row">Delivery Note / DC : <strong>{{ $goodsReceipt->delivery_note_no ?: 'None' }}</strong></div>
                <div class="detail-row">Inward Depot : <strong>Central Warehouse, Chennai</strong></div>
            </td>

            <td style="width: 36%;">
                <div class="meta-heading">Vendor / Supplier</div>
                <div class="party-name">{{ strtoupper($supplierName) }}</div>
                <div>
                    @if($supplier?->address)
                        {!! nl2br(e(strtoupper($supplier->address))) !!}<br>
                    @endif
                    @if($supplier?->phone)
                        <strong>Phone:</strong> {{ $supplier->phone }}<br>
                    @endif
                    @if($supplier?->gst_number)
                        <strong>GSTIN:</strong> {{ $supplier->gst_number }}<br>
                    @endif
                    <strong>State:</strong> {{ $supplier?->state ?: 'Tamil Nadu' }}
                </div>
            </td>

            <td style="width: 28%;">
                <div class="meta-heading">Verification Summary</div>
                <div class="detail-row">Received By : <strong>{{ $goodsReceipt->receivedBy?->name ?? 'Storekeeper' }}</strong></div>
                <div class="detail-row">Accepted Units : <strong>{{ $totalAccepted }}</strong></div>
                <div class="detail-row">Rejected Units : <strong>{{ $totalRejected }}</strong></div>
                @if($goodsReceipt->notes)
                    <div class="detail-row" style="margin-top: 4px;">
                        <strong>Remarks:</strong> {{ $goodsReceipt->notes }}
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Items Table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 45%; text-align: left;">Item Description</th>
                <th style="width: 14%;">SKU</th>
                <th style="width: 10%;">Accepted</th>
                <th style="width: 10%;">Rejected</th>
                <th style="width: 16%; text-align: right;">Rate (₹)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($goodsReceipt->items as $index => $item)
                @php
                    $rate = (float) $item->unit_cost;
                    $lineTotal = (float) ($item->qty_received * $rate);
                    $totalCost += $lineTotal;
                @endphp
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product?->name ?: ($item->purchaseOrderItem?->item_name ?? 'Item') }}</strong>
                        @if($item->qty_rejected > 0 && $item->reject_reason)
                            <div style="font-size: 6.5px; color: #dc2626;">Rejected Note: {{ $item->reject_reason }}</div>
                        @endif
                        @if($item->serials->isNotEmpty())
                            <div style="margin-top: 3px;">
                                <span style="font-size: 6.5px; font-weight: bold; color: #374151;">Serials:</span>
                                @foreach($item->serials as $s)
                                    <span class="serial-tag">{{ $s->serial_no }}</span>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="center">{{ $item->product?->sku ?: ($item->purchaseOrderItem?->sku ?? '-') }}</td>
                    <td class="center" style="font-weight: bold; color: #047857;">{{ $item->qty_received }}</td>
                    <td class="center" style="font-weight: bold; color: {{ $item->qty_rejected > 0 ? '#b91c1c' : '#6b7280' }};">{{ $item->qty_rejected }}</td>
                    <td class="right">{{ number_format($rate, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" class="right">Total Inward Quantity</td>
                <td class="center">{{ $totalAccepted }}</td>
                <td class="center">{{ $totalRejected }}</td>
                <td class="right">₹ {{ number_format($totalCost, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Signatures --}}
    <table class="sign-table">
        <tr>
            <td style="padding-right: 20px;">
                <div class="sign-box">Received & Inspected By (Storekeeper)</div>
            </td>
            <td style="padding-left: 20px; text-align: right;">
                <div class="sign-box">Verified & Authorized By (Store In-Charge)</div>
            </td>
        </tr>
    </table>

</body>
</html>
