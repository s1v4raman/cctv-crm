<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $docTitle ?? 'Purchase Order' }} - Demo Template</title>
    <style>
        @page {
            margin: 7mm 11mm 7mm 11mm;
            size: a4 portrait;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8.5px;
            color: #1f2937;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        /* Top Company Header */
        .company-header {
            margin-bottom: 6px;
        }
        .company-name {
            font-size: 13.5px;
            font-weight: bold;
            color: #111827;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .company-details {
            font-size: 7.5px;
            color: #374151;
            line-height: 1.35;
        }

        /* Centered Document Title */
        .doc-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            color: #4b5563;
            margin: 10px 0 14px 0;
            letter-spacing: 0.5px;
        }

        /* 3-Column Meta Section */
        .meta-3col {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .meta-3col td {
            vertical-align: top;
            font-size: 7.5px;
            line-height: 1.3;
            color: #374151;
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
        .client-name {
            font-size: 8.5px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            margin-bottom: 2px;
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
            font-size: 8px;
            font-weight: bold;
            padding: 5px 4px;
            border: 1px solid #525252;
            text-align: left;
        }
        .items-table td {
            padding: 4px 4px;
            font-size: 7.5px;
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
            font-size: 7.8px;
        }
        .item-spec {
            font-size: 7px;
            color: #6b7280;
            margin-top: 1px;
        }
        .gst-percent {
            font-size: 7px;
            color: #4b5563;
            margin-top: 1px;
        }

        /* Table Total Row */
        .items-table tr.total-row td {
            border-top: 1.2px solid #111827;
            border-bottom: 1.2px solid #111827;
            font-weight: bold;
            font-size: 8px;
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
            font-size: 7px;
            color: #4b5563;
            margin-bottom: 8px;
            line-height: 1.35;
        }
        .terms-text ol {
            margin: 0;
            padding-left: 14px;
        }
        .terms-text li {
            margin-bottom: 2px;
        }
        .bank-info {
            font-size: 7.5px;
            color: #374151;
            line-height: 1.4;
        }
        .bank-info strong {
            color: #111827;
        }

        /* Summary Box */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .summary-table td {
            padding: 2.5px 4px;
            font-size: 8px;
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
            font-size: 9px;
            padding: 5px 6px;
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
            font-size: 8px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 35px;
        }
        .sign-title {
            font-size: 8px;
            font-weight: bold;
            color: #111827;
        }
    </style>
</head>
<body>

    {{-- Company Header (Top Left) --}}
    <div class="company-header">
        <div class="company-name">{{ $companyName ?? 'NP SOLUTIONS' }}</div>
        <div class="company-details">
            {{ $companyAddress ?? 'NO 12 1ST MAIN ROAD SUBRAMANIYAPURAM, CHENNAI - 77' }}<br>
            Phone no. : {{ $companyPhone ?? '9884664108' }}<br>
            Email : {{ $companyEmail ?? 'npsolutions.in@gmail.com' }}<br>
            GSTIN : {{ $companyGstin ?? '33DNAPA0957C1ZQ' }}<br>
            State: {{ $companyState ?? '33-Tamil Nadu' }}
        </div>
    </div>

    {{-- Document Title --}}
    <div class="doc-title">{{ $docTitle ?? 'Purchase Order' }}</div>

    {{-- 3-Column Header: Bill To | Ship To | Order Details --}}
    <table class="meta-3col">
        <tr>
            {{-- Bill To --}}
            <td class="col-bill">
                <div class="col-heading">Bill To</div>
                <div class="client-name">{{ strtoupper($clientName ?? 'ACS MEDICAL COLLEGE & HOSPITAL') }}</div>
                <div>
                    {!! nl2br(e(strtoupper($clientBillAddress ?? "PERIYAR EVR HIGH ROAD, NH 4,\nCHENNAI BANGALORE HIGHWAYS\nVELAPPANCHAVADI, CHENNAI - 600 077"))) !!}<br>
                    @if(!empty($clientGstin ?? '33AADTM8372L1Z5'))
                        GSTIN : {{ $clientGstin ?? '33AADTM8372L1Z5' }}<br>
                    @endif
                    State: {{ $clientState ?? '33-Tamil Nadu' }}
                </div>
            </td>

            {{-- Ship To: Same location as Bill To --}}
            <td class="col-ship">
                <div class="col-heading">Ship To</div>
                <div>
                    {!! nl2br(e(strtoupper($clientShipAddress ?? "PERIYAR EVR HIGH ROAD, NH 4,\nCHENNAI BANGALORE HIGHWAYS\nVELAPPANCHAVADI, CHENNAI - 600 077"))) !!}
                </div>
            </td>

            {{-- Order Details --}}
            <td class="col-details">
                <div class="col-heading">Order Details</div>
                <div class="detail-row">PO No. : <strong>{{ $docNumber ?? 'PO-20261118-0005' }}</strong></div>
                <div class="detail-row">Date : <strong>{{ $docDate ?? date('d-m-Y') }}</strong></div>
                <div class="detail-row">Place of supply: <strong>{{ $placeOfSupply ?? '33-Tamil Nadu' }}</strong></div>
            </td>
        </tr>
    </table>

    {{-- Items Table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="center" style="width: 4%;">#</th>
                <th style="width: 29%;">Item name</th>
                <th class="center" style="width: 8%;">HSN/SAC</th>
                <th class="center" style="width: 8%;">Quantity</th>
                <th class="center" style="width: 6%;">Unit</th>
                <th class="right" style="width: 12%;">Price/ Unit</th>
                <th class="right" style="width: 12%;">Taxable amount</th>
                <th class="right" style="width: 10%;">GST</th>
                <th class="right" style="width: 11%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $idx => $item)
                <tr>
                    <td class="center">{{ $idx + 1 }}</td>
                    <td>
                        <div class="item-name">{{ $item['name'] }}</div>
                        @if(!empty($item['spec']))
                            <div class="item-spec">{{ $item['spec'] }}</div>
                        @endif
                    </td>
                    <td class="center">{{ $item['hsn'] ?? '-' }}</td>
                    <td class="center">{{ $item['qty'] }}</td>
                    <td class="center">{{ $item['unit'] ?? 'Nos' }}</td>
                    <td class="right">₹ {{ number_format($item['price'], 2) }}</td>
                    <td class="right">₹ {{ number_format($item['taxable'], 2) }}</td>
                    <td class="right">
                        ₹ {{ number_format($item['gst_amount'], 2) }}
                        <div class="gst-percent">({{ (float) ($item['tax_rate'] ?? 18) }}%)</div>
                    </td>
                    <td class="right">₹ {{ number_format($item['total'], 2) }}</td>
                </tr>
            @endforeach

            {{-- Table Total Row --}}
            <tr class="total-row">
                <td colspan="3" style="text-align: left; padding-left: 6px;">Total</td>
                <td class="center">{{ $totalQty ?? array_sum(array_column($items, 'qty')) }}</td>
                <td></td>
                <td></td>
                <td class="right">₹ {{ number_format($subtotal, 2) }}</td>
                <td class="right">₹ {{ number_format($taxAmount, 2) }}</td>
                <td class="right">₹ {{ number_format($grandTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Bottom Two-Column Section --}}
    <table class="bottom-table">
        <tr>
            <td class="bottom-left">
                <div class="section-label">Invoice Amount In Words</div>
                <div class="words-text">
                    {{ $amountInWords }}
                </div>

                <div class="section-label">Terms and Conditions</div>
                <div class="terms-text">
                    <ol>
                        <li>All delivered hardware must be brand new, factory sealed with original warranty.</li>
                        <li>Original Delivery Challan and GST Tax Invoice mentioning PO #<strong>{{ $docNumber ?? 'PO-20261118-0005' }}</strong> must accompany the consignment.</li>
                        <li>Payment will be processed as per agreed terms via NEFT / RTGS / Bank Transfer.</li>
                    </ol>
                </div>

                <div class="section-label">Pay To:</div>
                <div class="bank-info">
                    <strong>Bank Name :</strong> HDFC BANK, CHENNAI - ASHOK NAGAR<br>
                    <strong>Bank Account No. :</strong> 50200080470710<br>
                    <strong>Bank IFSC code :</strong> HDFC0000136<br>
                    <strong>Account holder's name :</strong> {{ $companyName ?? 'NP SOLUTIONS' }}
                </div>
            </td>

            <td class="bottom-right">
                <table class="summary-table">
                    <tr>
                        <td class="summary-label">Sub Total</td>
                        <td class="summary-value">₹ {{ number_format($subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">SGST&#64;9%</td>
                        <td class="summary-value">₹ {{ number_format($taxAmount / 2, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">CGST&#64;9%</td>
                        <td class="summary-value">₹ {{ number_format($taxAmount / 2, 2) }}</td>
                    </tr>
                    @if(!empty($roundOff) && abs($roundOff) > 0.001)
                        <tr>
                            <td class="summary-label">Round off</td>
                            <td class="summary-value">{{ $roundOff < 0 ? '- ' : '+ ' }}₹ {{ number_format(abs($roundOff), 2) }}</td>
                        </tr>
                    @endif
                    <tr class="summary-total-row">
                        <td class="summary-label">Total</td>
                        <td class="summary-value">₹ {{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </table>

                {{-- Signatory Area --}}
                <div class="sign-area">
                    <div class="for-label">For :{{ $companyName ?? 'NP SOLUTIONS' }}</div>
                    <div class="sign-title">Authorized Signatory</div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
