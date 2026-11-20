<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
    <style>
        @page {
            margin: 14mm 12mm 12mm 12mm;
            size: a4 portrait;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8px;
            color: #1f2937;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        /* Running Header for Page 2 and later */
        header {
            position: fixed;
            top: -10mm;
            left: 0;
            right: 0;
            height: 10mm;
            text-align: center;
            z-index: 1;
        }
        .p2-inst-name {
            font-size: 10.5px;
            font-weight: bold;
            letter-spacing: 0.5px;
            color: #000000;
            text-transform: uppercase;
        }
        .p2-vend-name {
            font-size: 9.5px;
            font-weight: bold;
            letter-spacing: 0.5px;
            color: #000000;
            text-transform: uppercase;
            margin-top: 1px;
        }

        /* Page 1 Cover to conceal fixed running header on first page */
        .p1-header-cover {
            position: absolute;
            top: -14mm;
            left: -12mm;
            right: -12mm;
            height: 14mm;
            background-color: #ffffff;
            z-index: 100;
        }

        /* Top Left Company Details */
        .company-header {
            margin-bottom: 6px;
        }
        .company-name {
            font-size: 13.5px;
            font-weight: bold;
            color: #000000;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
        }
        .company-details {
            font-size: 7.5px;
            color: #1f2937;
            line-height: 1.35;
        }

        /* Center Document Title */
        .doc-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            color: #4b5563;
            letter-spacing: 0.5px;
            margin: 6px 0 10px 0;
            text-transform: capitalize;
        }

        /* Dedicated Vendor Banner */
        .vendor-banner {
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
            padding: 4px 6px;
            margin-bottom: 8px;
            font-size: 7.5px;
            line-height: 1.35;
        }
        .vendor-banner strong {
            color: #111827;
        }

        /* 3-Column Meta Table (Matching Format Reference) */
        .meta-3col {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .meta-3col td {
            vertical-align: top;
            font-size: 7.5px;
            line-height: 1.35;
            color: #1f2937;
        }
        .meta-3col .col-bill {
            width: 38%;
            padding-right: 8px;
        }
        .meta-3col .col-ship {
            width: 34%;
            padding-right: 8px;
        }
        .meta-3col .col-details {
            width: 28%;
            text-align: right;
        }
        .col-heading {
            font-size: 8.5px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 2px;
        }
        .party-name {
            font-size: 8.5px;
            font-weight: bold;
            color: #000000;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .detail-row {
            margin-bottom: 2px;
        }
        .detail-row strong {
            color: #111827;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .items-table th {
            background-color: #525252;
            color: #ffffff;
            font-size: 7.5px;
            font-weight: bold;
            padding: 5px 4px;
            border: 1px solid #525252;
            text-align: left;
        }
        .items-table td {
            padding: 4px 4px;
            font-size: 7.2px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
            color: #1f2937;
        }
        .items-table th.center, .items-table td.center {
            text-align: center;
        }
        .items-table th.right, .items-table td.right {
            text-align: right;
        }
        .item-name {
            font-weight: bold;
            color: #111827;
            font-size: 7.5px;
        }
        .item-spec {
            font-size: 6.8px;
            color: #6b7280;
            margin-top: 1px;
        }
        .gst-percent {
            font-size: 6.8px;
            color: #4b5563;
            margin-top: 1px;
        }

        /* Table Total Row */
        .items-table tr.total-row td {
            border-top: 1.2px solid #111827;
            border-bottom: 1.2px solid #111827;
            font-weight: bold;
            font-size: 7.8px;
            padding: 5px 4px;
            background-color: #ffffff;
        }

        /* Bottom Two-Column Section */
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .bottom-table td {
            vertical-align: top;
        }
        .bottom-left {
            width: 58%;
            padding-right: 14px;
        }
        .bottom-right {
            width: 42%;
        }

        .section-label {
            font-size: 8px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 2px;
        }
        .words-text {
            font-size: 7.5px;
            color: #374151;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        .terms-text {
            font-size: 6.8px;
            color: #4b5563;
            line-height: 1.35;
            margin-bottom: 8px;
        }
        .terms-text ol {
            margin: 0;
            padding-left: 14px;
        }
        .terms-text li {
            margin-bottom: 2px;
        }

        /* Calculation Summary Table */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .summary-table td {
            padding: 2.5px 4px;
            font-size: 7.8px;
        }
        .summary-label {
            text-align: left;
            color: #374151;
        }
        .summary-value {
            text-align: right;
            font-weight: bold;
            color: #111827;
        }
        .summary-total-row td {
            background-color: #525252;
            color: #ffffff !important;
            font-weight: bold;
            font-size: 8.5px;
            padding: 4px 6px;
        }
        .summary-total-row .summary-label {
            color: #ffffff !important;
        }
        .summary-total-row .summary-value {
            color: #ffffff !important;
        }

        /* Signatory Area */
        .sign-area {
            text-align: right;
            margin-top: 8px;
            padding-right: 4px;
        }
        .for-label {
            font-size: 7.8px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 30px;
        }
        .sign-title {
            font-size: 7.8px;
            font-weight: bold;
            color: #111827;
        }
    </style>
</head>
<body>

    @php
        $settings = \App\Models\GatewaySetting::first();
        $companyName = $settings?->company_trade_name ?: ($settings?->company_legal_name ?: 'NP SOLUTIONS');
        $companyAddress = $settings?->company_address ?: 'NO 12 1ST MAIN ROAD SUBRAMANIYAPURAM, CHENNAI - 77';
        $companyPhone = '9884664108';
        $companyEmail = 'npsolutions.in@gmail.com';
        $companyGstin = $settings?->company_gstin ?: '33DNAPA0957C1ZQ';
        $companyStateCode = $settings?->company_state_code ?: '33';
        $companyStateName = $settings?->company_state ?: 'Tamil Nadu';

        $supplier = $purchaseOrder->supplier ?? null;
        $supplierName = $supplier?->company_name ?: ($supplier?->name ?? 'Vendor');
        $supplierAddress = $supplier?->address ?: '';
        $supplierCity = $supplier?->city ?? '';
        $supplierState = $supplier?->state ?: 'Tamil Nadu';
        $supplierGstin = $supplier?->gst_number ?? '';
        $supplierPhone = $supplier?->phone ?? '';

        $vendorStateCode = $supplierGstin ? \App\Helpers\GstHelper::extractStateCode($supplierGstin) : null;
        $isInterstate = !empty($vendorStateCode) && ($vendorStateCode !== $companyStateCode);

        // Project / Destination resolution:
        // If notes mention a project/site (e.g. A.C.S Medical College), use it for header and billing/shipping destination.
        $projectName = 'A.C.S MEDICAL COLLEGE AND HOSPITAL';
        if (!empty($purchaseOrder->notes) && preg_match('/(?:project|site|client)\s*:\s*([^,\n\r]+)/i', $purchaseOrder->notes, $m)) {
            $projectName = trim($m[1]);
        }

        // Bill To & Ship To are the same location as required
        $billToName = $companyName;
        $billToAddress = $companyAddress;
        $billToGstin = $companyGstin;
        $billToState = $companyStateCode . '-' . $companyStateName;
        $shipToAddress = $companyAddress;

        $totalOrderedQty = (int) $purchaseOrder->items->sum('quantity_ordered');
        $displaySubtotal = (float) $purchaseOrder->subtotal;
        $displayTaxAmount = (float) $purchaseOrder->tax_amount;
        $displayShipping = (float) ($purchaseOrder->shipping_cost ?? 0.0);
        $displayGrandTotal = (float) $purchaseOrder->total;

        $cgstAmount = (float) $purchaseOrder->cgst_amount;
        $sgstAmount = (float) $purchaseOrder->sgst_amount;
        $igstAmount = (float) $purchaseOrder->igst_amount;

        // Dynamic tax split fallback if not recorded
        $poTaxRate = (float) ($purchaseOrder->tax_percent > 0 ? $purchaseOrder->tax_percent : 18.0);
        if ($displayTaxAmount > 0 && $cgstAmount == 0 && $sgstAmount == 0 && $igstAmount == 0) {
            $split = \App\Helpers\GstHelper::calculateTaxSplit($displaySubtotal, $poTaxRate, $vendorStateCode, $companyStateCode);
            $cgstAmount = $split['cgst_amount'];
            $sgstAmount = $split['sgst_amount'];
            $igstAmount = $split['igst_amount'];
        }

        // Amount in words
        try {
            $f = new \NumberFormatter("en_IN", \NumberFormatter::SPELLOUT);
            $wordsStr = ucwords($f->format((int) round($displayGrandTotal)));
            $amountInWords = $wordsStr . ' Rupees only';
        } catch (\Throwable $e) {
            $amountInWords = 'Rupees ' . number_format($displayGrandTotal, 2) . ' only';
        }

        $formattedDate = ($purchaseOrder->order_date ?? $purchaseOrder->created_at ?? now())->format('d-m-Y');
    @endphp

    {{-- Running Header on Page 2 and Later (Matches Denoted Design in Image 1) --}}
    <header>
        <div class="p2-inst-name">{{ $projectName }}</div>
        <div class="p2-vend-name">{{ $companyName }}</div>
    </header>

    {{-- First Page Cover (Hides the fixed running header on page 1) --}}
    <div class="p1-header-cover"></div>

    {{-- Company Header (Top Left - Matches Image 2) --}}
    <div class="company-header">
        <div class="company-name">{{ $companyName }}</div>
        <div class="company-details">
            {{ $companyAddress }}<br>
            Phone no. : {{ $companyPhone }}<br>
            Email : {{ $companyEmail }}<br>
            GSTIN : {{ $companyGstin }}<br>
            State: {{ $companyStateCode }}-{{ $companyStateName }}
        </div>
    </div>

    {{-- Center Document Title --}}
    <div class="doc-title">Purchase Order</div>

    {{-- Separate Vendor / Supplier Banner --}}
    <div class="vendor-banner">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 17%; font-weight: bold; color: #111827;">Vendor / Supplier :</td>
                <td style="width: 53%;">
                    <strong>{{ strtoupper($supplierName) }}</strong>
                    @if($supplierAddress) &bull; {{ strtoupper($supplierAddress) }}@endif
                    @if($supplierPhone) &bull; Ph: {{ $supplierPhone }}@endif
                </td>
                <td style="width: 30%; text-align: right;">
                    @if($supplierGstin) <strong>GSTIN :</strong> {{ $supplierGstin }} &bull; @endif
                    <strong>State :</strong> {{ $supplierState }}
                </td>
            </tr>
        </table>
    </div>

    {{-- 3-Column Meta Table: Bill To | Ship To (Same Location) | Order Details (Matches Image 3) --}}
    <table class="meta-3col">
        <tr>
            {{-- Bill To --}}
            <td class="col-bill">
                <div class="col-heading">Bill To</div>
                <div class="party-name">{{ strtoupper($billToName) }}</div>
                <div>
                    {!! nl2br(e(strtoupper($billToAddress))) !!}<br>
                    @if($billToGstin)
                        <strong>GSTIN :</strong> {{ $billToGstin }}<br>
                    @endif
                    <strong>State:</strong> {{ $billToState }}
                </div>
            </td>

            {{-- Ship To: Same Location as Bill To --}}
            <td class="col-ship">
                <div class="col-heading">Ship To</div>
                <div>
                    {!! nl2br(e(strtoupper($shipToAddress))) !!}
                </div>
            </td>

            {{-- Order Details --}}
            <td class="col-details">
                <div class="col-heading">Order Details</div>
                <div class="detail-row">PO No. : <strong>{{ $purchaseOrder->po_number }}</strong></div>
                <div class="detail-row">Date : <strong>{{ $formattedDate }}</strong></div>
                <div class="detail-row">Place of supply: <strong>{{ $companyStateCode }}-{{ $companyStateName }}</strong></div>
                @if($purchaseOrder->expected_delivery_date)
                    <div class="detail-row">Expected By: <strong>{{ $purchaseOrder->expected_delivery_date->format('d-m-Y') }}</strong></div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Items Table (Matching Exact Reference Layout) --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="center" style="width: 4%;">#</th>
                <th style="width: 29%;">Item name</th>
                <th class="center" style="width: 9%;">HSN/SAC</th>
                <th class="center" style="width: 8%;">Quantity</th>
                <th class="center" style="width: 6%;">Unit</th>
                <th class="right" style="width: 12%;">Price/ Unit</th>
                <th class="right" style="width: 12%;">Taxable amount</th>
                <th class="right" style="width: 10%;">GST</th>
                <th class="right" style="width: 10%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $index => $item)
                @php
                    $qty = (int) $item->quantity_ordered;
                    $rate = (float) $item->unit_cost;
                    $taxable = $qty * $rate;
                    $lineGst = (float) ($item->total_cost ? ($item->total_cost - $taxable) : ($taxable * ($poTaxRate / 100)));
                    $lineTotal = (float) ($item->total_cost ?: ($taxable + $lineGst));
                    $hsn = $item->hsn_code ?: ($item->product?->hsn_code ?? '8525');
                    $unit = $item->product?->unit ?? 'Nos';
                @endphp
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>
                        <div class="item-name">{{ $item->item_name }}</div>
                        @if($item->sku)
                            <div class="item-spec">SKU: {{ $item->sku }}</div>
                        @endif
                    </td>
                    <td class="center">{{ $hsn }}</td>
                    <td class="center">{{ $qty }}</td>
                    <td class="center">{{ $unit }}</td>
                    <td class="right">₹ {{ number_format($rate, 2) }}</td>
                    <td class="right">₹ {{ number_format($taxable, 2) }}</td>
                    <td class="right">
                        ₹ {{ number_format($lineGst, 2) }}
                        <div class="gst-percent">({{ (float) $poTaxRate }}%)</div>
                    </td>
                    <td class="right">₹ {{ number_format($lineTotal, 2) }}</td>
                </tr>
            @endforeach

            {{-- Table Total Row --}}
            <tr class="total-row">
                <td colspan="3" style="text-align: left; padding-left: 6px;">Total</td>
                <td class="center">{{ $totalOrderedQty }}</td>
                <td></td>
                <td></td>
                <td class="right">₹ {{ number_format($displaySubtotal, 2) }}</td>
                <td class="right">₹ {{ number_format($displayTaxAmount, 2) }}</td>
                <td class="right">₹ {{ number_format($displayGrandTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Bottom Two-Column Section --}}
    <table class="bottom-table">
        <tr>
            <td class="bottom-left">
                <div class="section-label">Invoice Amount In Words</div>
                <div class="words-text">{{ $amountInWords }}</div>

                <div class="section-label">Terms and Conditions</div>
                <div class="terms-text">
                    <ol>
                        <li>All supplied items must be brand new, factory sealed with standard manufacturer warranty.</li>
                        <li>Original Delivery Challan and Tax Invoice mentioning PO #<strong>{{ $purchaseOrder->po_number }}</strong> must accompany the consignment.</li>
                        <li>Goods received are subject to verification and quality inspection at the warehouse.</li>
                        <li>Payment will be processed via NEFT / RTGS as per agreed payment terms.</li>
                    </ol>
                </div>
            </td>

            <td class="bottom-right">
                <table class="summary-table">
                    <tr>
                        <td class="summary-label">Sub Total</td>
                        <td class="summary-value">₹ {{ number_format($displaySubtotal, 2) }}</td>
                    </tr>
                    @if($isInterstate)
                        <tr>
                            <td class="summary-label">IGST&#64;{{ (float) $poTaxRate }}%</td>
                            <td class="summary-value">₹ {{ number_format($igstAmount ?: $displayTaxAmount, 2) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td class="summary-label">SGST&#64;{{ (float) ($poTaxRate / 2) }}%</td>
                            <td class="summary-value">₹ {{ number_format($sgstAmount ?: ($displayTaxAmount / 2), 2) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">CGST&#64;{{ (float) ($poTaxRate / 2) }}%</td>
                            <td class="summary-value">₹ {{ number_format($cgstAmount ?: ($displayTaxAmount / 2), 2) }}</td>
                        </tr>
                    @endif
                    @if($displayShipping > 0)
                        <tr>
                            <td class="summary-label">Freight / Shipping</td>
                            <td class="summary-value">₹ {{ number_format($displayShipping, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="summary-total-row">
                        <td class="summary-label">Total</td>
                        <td class="summary-value">₹ {{ number_format($displayGrandTotal, 2) }}</td>
                    </tr>
                </table>

                {{-- Signatory Area --}}
                <div class="sign-area">
                    <div class="for-label">For :{{ $companyName }}</div>
                    <div class="sign-title">Authorized Signatory</div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
