<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ ($docType ?? ($quotation->doc_type ?? 'quotation')) === 'invoice' ? ('Tax Invoice ' . (method_exists($quotation, 'getFormattedInvoiceNo') ? $quotation->getFormattedInvoiceNo('1') : '2026-27/122')) : ('Quotation ' . ($quotation->quotation_no ?? 'PITS/QUOT/26-21/58')) }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 12mm 6mm 12mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #000000;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .header-logo-cell {
            width: 48%;
            vertical-align: top;
            padding: 0;
        }
        .header-logo-img {
            width: 215px;
            height: auto;
            display: block;
        }
        .header-address-cell {
            width: 52%;
            text-align: right;
            vertical-align: top;
            font-size: 7.8pt;
            line-height: 1.28;
            padding: 0;
        }
        .company-name {
            font-size: 10.5pt;
            font-weight: bold;
            margin-bottom: 1px;
        }
        .quotation-title {
            text-align: center;
            font-size: 10.5pt;
            font-weight: bold;
            margin-top: 4px;
            margin-bottom: 6px;
            color: #000000;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .meta-ref {
            text-align: left;
            width: 55%;
        }
        .meta-date {
            text-align: right;
            width: 45%;
        }
        .to-section {
            font-size: 8pt;
            line-height: 1.3;
            margin-bottom: 8px;
        }
        .to-label {
            font-weight: bold;
        }
        .to-customer {
            font-weight: bold;
            text-transform: uppercase;
        }
        .salutation {
            font-size: 8pt;
            line-height: 1.35;
            margin-bottom: 6px;
        }
        .section-heading {
            font-size: 8.5pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            font-size: 7.6pt;
        }
        .items-table th {
            background-color: #0b2861;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            border: 1px solid #000000;
            padding: 3px 2px;
            vertical-align: middle;
            line-height: 1.15;
            font-size: 7.5pt;
        }
        .items-table td {
            border: 1px solid #000000;
            padding: 2.2px 4px;
            vertical-align: top;
            font-size: 7.6pt;
            line-height: 1.2;
        }
        .col-no {
            width: 4.5%;
            text-align: center;
        }
        .col-desc {
            width: 40.5%;
            text-align: left;
        }
        .col-qty {
            width: 6%;
            text-align: center;
        }
        .col-quoted {
            width: 16%;
            text-align: right;
        }
        .col-discount {
            width: 16%;
            text-align: right;
        }
        .col-total {
            width: 17%;
            text-align: right;
        }
        .currency-symbol {
            float: left;
            padding-left: 1px;
        }
        .empty-row td {
            height: 10px;
            border: 1px solid #000000;
            padding: 0;
        }
        .summary-label {
            text-align: right;
            padding-right: 6px;
            border: 1px solid #000000;
            font-weight: normal;
        }
        .summary-value {
            text-align: right;
            padding-right: 6px;
            border: 1px solid #000000;
        }
        .total-row {
            background-color: #0b2861;
            color: #ffffff;
            font-weight: bold;
        }
        .total-row td.summary-label {
            background-color: #0b2861;
            color: #ffffff;
            font-weight: bold;
        }
        .total-row td.summary-value {
            background-color: #0b2861;
            color: #ffffff;
            font-weight: bold;
        }
        .terms-section {
            margin-top: 8px;
            font-size: 7.6pt;
            line-height: 1.4;
            page-break-inside: avoid;
        }
        .terms-title {
            font-weight: bold;
            margin-bottom: 2px;
        }
        .footer-section {
            margin-top: 10px;
            font-size: 7.6pt;
            line-height: 1.3;
            page-break-inside: avoid;
        }
        .footer-company {
            font-weight: bold;
            margin-top: 2px;
        }
    </style>
</head>
<body>

@php
    $logoPath = public_path('images/precision-quotation-logo.png');
    if (!file_exists($logoPath)) {
        $logoPath = public_path('logo.png');
    }
    $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';

    $refNo = method_exists($quotation, 'getFormattedReferenceNo')
        ? $quotation->getFormattedReferenceNo('1')
        : ($quotation->quotation_no ?? 'PITS/QUOT/26-27/58');

    $quoteDate = $quotation->quotation_date ?? $quotation->created_at ?? now();
    $formattedDate = $quoteDate instanceof \Carbon\Carbon ? $quoteDate->format('j-M-y') : date('j-M-y', strtotime($quoteDate));

    $lead = $quotation->lead ?? null;
    $customerName = $lead?->customer_name ?? 'TRUE WAVES ASSOCIATES';
    $companyName = $lead?->company_name ?? '';
    $address = $lead?->site_address ?? '';
    
    // Address lines
    $addressLines = [];
    if (!empty($address)) {
        $rawLines = preg_split('/\r\n|\r|\n/', $address);
        foreach ($rawLines as $l) {
            $l = trim($l);
            if ($l !== '') $addressLines[] = $l;
        }
    }
    if (empty($addressLines)) {
        if (!empty($companyName) && strcasecmp($companyName, $customerName) !== 0) {
            $addressLines[] = $companyName;
        }
        $addressLines[] = '27/5, Easwaran Koil Street, Vellai Thottam, West';
        $addressLines[] = 'Mambalam, Chennai, Greater Chennai, Tamil Nadu';
        $addressLines[] = '600033';
    }

    $subtotal = (float)($quotation->subtotal ?? 0);
    $taxPercent = (float)($quotation->tax_percent ?? 18);
    $taxAmount = (float)($quotation->tax_amount ?? round($subtotal * ($taxPercent / 100), 2));
    $grandTotal = (float)($quotation->total ?? ($subtotal + $taxAmount));
    $roundedTotal = round($grandTotal);
    $roundOff = round($roundedTotal - $grandTotal, 2);
    
    $items = $quotation->items ?? collect();
    $isAboveTen = count($items) > 10;
    $isTurnkey = (($quotation->pricing_mode ?? 'itemized') === 'total_only') || $isAboveTen;

    $docType = $docType ?? $quotation->doc_type ?? 'quotation';
    $isInvoice = ($docType === 'invoice');
    $invoiceNo = method_exists($quotation, 'getFormattedInvoiceNo')
        ? $quotation->getFormattedInvoiceNo('1')
        : '2026-27/122';
    $dueDate = ($quoteDate instanceof \Carbon\Carbon ? $quoteDate->copy()->addDays(45) : now()->addDays(45));
    $dueDateFormatted = $dueDate->format('d-m-Y');
    $invoiceDateFormatted = $quoteDate instanceof \Carbon\Carbon ? $quoteDate->format('d-m-Y') : date('d-m-Y', strtotime($quoteDate));
    $amountInWords = ucwords(str_replace('-', ' ', \App\Models\Quotation::numberToWords($roundedTotal))) . ' Rupees only';
    $halfTaxPercent = round($taxPercent / 2, 1);
    $halfTaxAmount = round($taxAmount / 2, 2);
@endphp

@if($isInvoice)
    {{-- Tax Invoice View Matching Exact Design Layout --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <tr>
            <td style="width: 25%;"></td>
            <td style="width: 50%; text-align: center; vertical-align: middle;">
                <div style="font-size: 13pt; font-weight: bold; color: #000000; letter-spacing: 0.3px;">Tax Invoice</div>
            </td>
            <td style="width: 25%; text-align: right; vertical-align: middle;">
                <div style="font-size: 8.5pt; font-weight: normal; color: #4b5563; letter-spacing: 0.5px;">ORIGINAL FOR RECIPIENT</div>
            </td>
        </tr>
    </table>

    {{-- Master Box: Company Profile, Details Grid, and Bill To --}}
    <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #555555; margin-bottom: 0;">
        <tr>
            {{-- Left: Logo and Precision IT Systems Company Details --}}
            <td style="width: 53%; vertical-align: top; border-right: 1.5px solid #555555; padding: 6px 8px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 135px; vertical-align: middle; padding-right: 8px;">
                            @if($logoBase64)
                                <img src="data:image/png;base64,{{ $logoBase64 }}" style="width: 130px; height: auto; display: block;" alt="Precision IT Systems">
                            @else
                                <div style="font-size: 14pt; font-weight: bold; color: #0b2861;">
                                    PRECISION <span style="font-size: 9pt; color: #333; display: block;">IT SYSTEMS</span>
                                </div>
                            @endif
                        </td>
                        <td style="vertical-align: top; font-size: 7.2pt; line-height: 1.25; color: #111827;">
                            <div style="font-size: 9.5pt; font-weight: bold; color: #000000; margin-bottom: 1px;">Precision IT Systems</div>
                            <div>Plot No 553 LIG I, 27th Street,</div>
                            <div>Tamil Nadu Housing Board,</div>
                            <div>Avadi, Chennai 600 054</div>
                            <div>Phone no.: 893883299</div>
                            <div>Email: support@precisionitsystems.com</div>
                            <div>GSTIN: 33AHLPI3531N1Z8</div>
                            <div>State: 33-Tamil Nadu</div>
                        </td>
                    </tr>
                </table>
            </td>

            {{-- Right: 2x2 Details Grid --}}
            <td style="width: 47%; vertical-align: top; padding: 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; vertical-align: top; padding: 4px 6px; border-right: 1.5px solid #555555; border-bottom: 1.5px solid #555555;">
                            <div style="font-size: 7pt; color: #374151;">Invoice No.</div>
                            <div style="font-size: 8.5pt; font-weight: bold; color: #000000; margin-top: 1px;">{{ $invoiceNo }}</div>
                        </td>
                        <td style="width: 50%; vertical-align: top; padding: 4px 6px; border-bottom: 1.5px solid #555555;">
                            <div style="font-size: 7pt; color: #374151;">Date</div>
                            <div style="font-size: 8.5pt; font-weight: bold; color: #000000; margin-top: 1px;">{{ $invoiceDateFormatted }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%; vertical-align: top; padding: 4px 6px; border-right: 1.5px solid #555555; border-bottom: 1.5px solid #555555;">
                            <div style="font-size: 7pt; color: #374151;">Due Date:</div>
                            <div style="font-size: 8.5pt; font-weight: bold; color: #000000; margin-top: 1px;">{{ $dueDateFormatted }}</div>
                        </td>
                        <td style="width: 50%; vertical-align: top; padding: 4px 6px; border-bottom: 1.5px solid #555555;">
                            <div style="font-size: 7pt; color: #374151;">Place of supply</div>
                            <div style="font-size: 8.5pt; font-weight: bold; color: #000000; margin-top: 1px;">33-Tamil Nadu</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border: none; padding: 0; font-size: 1pt; line-height: 1;">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- Bill To Row (Spanning 100%) --}}
        <tr>
            <td colspan="2" style="border-top: 1.5px solid #555555; padding: 5px 8px; vertical-align: top; font-size: 7.4pt; line-height: 1.3;">
                <div style="font-size: 7pt; color: #374151;">Bill To</div>
                <div style="font-size: 9.5pt; font-weight: bold; color: #000000; margin-top: 1px; margin-bottom: 2px;">{{ $customerName }}</div>
                @foreach($addressLines as $line)
                    <div style="color: #111827;">{{ $line }}</div>
                @endforeach
                <div style="color: #111827; margin-top: 1px;">State: 33-Tamil Nadu</div>
            </td>
        </tr>
    </table>

    {{-- Tax Invoice Table --}}
    <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #555555; border-top: none; font-size: 7.2pt; margin-bottom: 0;">
        <thead>
            <tr style="border-bottom: 1.5px solid #555555; background-color: #f8fafc;">
                <th style="width: 4%; text-align: center; padding: 4px 2px; border-right: 1px solid #555555; font-weight: bold;">#</th>
                <th style="width: 34%; text-align: left; padding: 4px 6px; border-right: 1px solid #555555; font-weight: bold;">Item name</th>
                <th style="width: 9%; text-align: center; padding: 4px 2px; border-right: 1px solid #555555; font-weight: bold;">HSN/ SAC</th>
                <th style="width: 7%; text-align: center; padding: 4px 2px; border-right: 1px solid #555555; font-weight: bold;">Quantity</th>
                <th style="width: 6%; text-align: center; padding: 4px 2px; border-right: 1px solid #555555; font-weight: bold;">Unit</th>
                <th style="width: 12%; text-align: right; padding: 4px 4px; border-right: 1px solid #555555; font-weight: bold;">Price/ Unit</th>
                <th style="width: 13%; text-align: right; padding: 4px 4px; border-right: 1px solid #555555; font-weight: bold;">Taxable amount</th>
                <th style="width: 5%; text-align: center; padding: 4px 2px; border-right: 1px solid #555555; font-weight: bold;">GST</th>
                <th style="width: 10%; text-align: right; padding: 4px 6px; font-weight: bold;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @if($isTurnkey)
                <tr>
                    <td style="text-align: center; border-right: 1px solid #555555; padding: 3px 2px;">1</td>
                    <td style="text-align: left; border-right: 1px solid #555555; padding: 3px 6px;">
                        <strong>Complete Turnkey CCTV Surveillance System & Security Infrastructure Package</strong>
                        <div style="font-size: 6.8pt; color: #444;">Supply, Installation, System Integration, Cabling &amp; Setup</div>
                    </td>
                    <td style="text-align: center; border-right: 1px solid #555555; padding: 3px 2px;">8525</td>
                    <td style="text-align: center; border-right: 1px solid #555555; padding: 3px 2px;">1</td>
                    <td style="text-align: center; border-right: 1px solid #555555; padding: 3px 2px;">Set</td>
                    <td style="text-align: right; border-right: 1px solid #555555; padding: 3px 4px;">&#8377; {{ number_format($subtotal, 2) }}</td>
                    <td style="text-align: right; border-right: 1px solid #555555; padding: 3px 4px;">&#8377; {{ number_format($subtotal, 2) }}</td>
                    <td style="text-align: center; border-right: 1px solid #555555; padding: 3px 2px;">{{ $taxPercent }}%</td>
                    <td style="text-align: right; padding: 3px 6px;">&#8377; {{ number_format($grandTotal, 2) }}</td>
                </tr>
            @else
                @php $tIdx = 1; $tQty = 0; @endphp
                @foreach($items as $item)
                    @php
                        $qty = $item->quantity ?? 1;
                        $tQty += $qty;
                        $rate = (float)($item->unit_price ?? 0);
                        $lineTaxable = (float)($item->total ?? ($rate * $qty));
                        $lineGst = round($lineTaxable * ($taxPercent / 100), 2);
                        $lineAmount = round($lineTaxable + $lineGst, 2);
                        $hsn = $item->hsn_code;
                    @endphp
                    <tr>
                        <td style="text-align: center; border-right: 1px solid #555555; padding: 3px 2px;">{{ $tIdx++ }}</td>
                        <td style="text-align: left; border-right: 1px solid #555555; padding: 3px 6px;">
                            <strong>{{ $item->item_name }}</strong>
                            @if(!empty($item->description))
                                <div style="font-size: 6.8pt; color: #444;">{!! nl2br(e($item->description)) !!}</div>
                            @endif
                        </td>
                        <td style="text-align: center; border-right: 1px solid #555555; padding: 3px 2px;">{{ $hsn }}</td>
                        <td style="text-align: center; border-right: 1px solid #555555; padding: 3px 2px;">{{ $qty }}</td>
                        <td style="text-align: center; border-right: 1px solid #555555; padding: 3px 2px;">Nos</td>
                        <td style="text-align: right; border-right: 1px solid #555555; padding: 3px 4px;">&#8377; {{ number_format($rate, 2) }}</td>
                        <td style="text-align: right; border-right: 1px solid #555555; padding: 3px 4px;">&#8377; {{ number_format($lineTaxable, 2) }}</td>
                        <td style="text-align: center; border-right: 1px solid #555555; padding: 3px 2px;">{{ $taxPercent }}%</td>
                        <td style="text-align: right; padding: 3px 6px;">&#8377; {{ number_format($lineAmount, 2) }}</td>
                    </tr>
                @endforeach
            @endif
            <tr style="background-color: #f8fafc; font-weight: bold; border-top: 1.5px solid #555555;">
                <td colspan="2" style="text-align: left; padding: 4px 6px; border-right: 1px solid #555555;">Total</td>
                <td style="border-right: 1px solid #555555;"></td>
                <td style="text-align: center; border-right: 1px solid #555555;">{{ $isTurnkey ? 1 : ($tQty ?? count($items)) }}</td>
                <td colspan="2" style="border-right: 1px solid #555555;"></td>
                <td style="text-align: right; padding: 4px 4px; border-right: 1px solid #555555;">&#8377; {{ number_format($subtotal, 2) }}</td>
                <td style="border-right: 1px solid #555555;"></td>
                <td style="text-align: right; padding: 4px 6px;">&#8377; {{ number_format($roundedTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Bottom Section: Two Columns (HSN/Bank/Terms on Left, Totals/Signature on Right) --}}
    <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #555555; border-top: none;">
        <tr>
            {{-- Left Column --}}
            <td style="width: 58%; vertical-align: top; border-right: 1.5px solid #555555; padding: 6px 8px;">
                <div style="font-size: 7pt; color: #374151;">Total Amount (in words)</div>
                <div style="font-size: 8pt; font-weight: bold; color: #000000; margin-bottom: 6px;">{{ $amountInWords }}</div>

                {{-- HSN/SAC Breakdown Table --}}
                @php
                    $hsnBreakdown = [];
                    if ($isTurnkey) {
                        $hsnBreakdown['8525'] = [
                            'hsn' => '8525',
                            'taxable' => $subtotal,
                            'cgst_amount' => $halfTaxAmount,
                            'sgst_amount' => $halfTaxAmount,
                            'total_tax' => $taxAmount,
                        ];
                    } else {
                        foreach ($items as $item) {
                            $hsnCode = $item->hsn_code;
                            $qty = (float)($item->quantity ?? 1);
                            $rate = (float)($item->unit_price ?? 0);
                            $lineTaxable = (float)($item->total ?? ($rate * $qty));
                            $lineGst = round($lineTaxable * ($taxPercent / 100), 2);
                            $lineHalfGst = round($lineGst / 2, 2);

                            if (!isset($hsnBreakdown[$hsnCode])) {
                                $hsnBreakdown[$hsnCode] = [
                                    'hsn' => $hsnCode,
                                    'taxable' => 0,
                                    'cgst_amount' => 0,
                                    'sgst_amount' => 0,
                                    'total_tax' => 0,
                                ];
                            }
                            $hsnBreakdown[$hsnCode]['taxable'] += $lineTaxable;
                            $hsnBreakdown[$hsnCode]['cgst_amount'] += $lineHalfGst;
                            $hsnBreakdown[$hsnCode]['sgst_amount'] += $lineHalfGst;
                            $hsnBreakdown[$hsnCode]['total_tax'] += $lineGst;
                        }
                    }
                @endphp
                <table style="width: 100%; border-collapse: collapse; font-size: 6.8pt; border: 1px solid #555555; margin-bottom: 6px;">
                    <thead>
                        <tr style="background-color: #f1f5f9; border-bottom: 1px solid #555555; font-weight: bold;">
                            <th style="border-right: 1px solid #555555; padding: 2px;">HSN/ SAC</th>
                            <th style="border-right: 1px solid #555555; padding: 2px; text-align: right;">Taxable amount</th>
                            <th style="border-right: 1px solid #555555; padding: 2px; text-align: center;">CGST Rate</th>
                            <th style="border-right: 1px solid #555555; padding: 2px; text-align: right;">CGST Amount</th>
                            <th style="border-right: 1px solid #555555; padding: 2px; text-align: center;">SGST Rate</th>
                            <th style="border-right: 1px solid #555555; padding: 2px; text-align: right;">SGST Amount</th>
                            <th style="padding: 2px; text-align: right;">Total Tax</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hsnBreakdown as $bRow)
                            <tr>
                                <td style="border-right: 1px solid #555555; text-align: center; padding: 2px;">{{ $bRow['hsn'] }}</td>
                                <td style="border-right: 1px solid #555555; text-align: right; padding: 2px;">{{ number_format($bRow['taxable'], 2) }}</td>
                                <td style="border-right: 1px solid #555555; text-align: center; padding: 2px;">{{ $halfTaxPercent }}%</td>
                                <td style="border-right: 1px solid #555555; text-align: right; padding: 2px;">{{ number_format($bRow['cgst_amount'], 2) }}</td>
                                <td style="border-right: 1px solid #555555; text-align: center; padding: 2px;">{{ $halfTaxPercent }}%</td>
                                <td style="border-right: 1px solid #555555; text-align: right; padding: 2px;">{{ number_format($bRow['sgst_amount'], 2) }}</td>
                                <td style="text-align: right; padding: 2px;">{{ number_format($bRow['total_tax'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td style="border-right: 1px solid #555555; text-align: center; padding: 2px;">8525</td>
                                <td style="border-right: 1px solid #555555; text-align: right; padding: 2px;">{{ number_format($subtotal, 2) }}</td>
                                <td style="border-right: 1px solid #555555; text-align: center; padding: 2px;">{{ $halfTaxPercent }}%</td>
                                <td style="border-right: 1px solid #555555; text-align: right; padding: 2px;">{{ number_format($halfTaxAmount, 2) }}</td>
                                <td style="border-right: 1px solid #555555; text-align: center; padding: 2px;">{{ $halfTaxPercent }}%</td>
                                <td style="border-right: 1px solid #555555; text-align: right; padding: 2px;">{{ number_format($halfTaxAmount, 2) }}</td>
                                <td style="text-align: right; padding: 2px;">{{ number_format($taxAmount, 2) }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="font-size: 7.2pt; line-height: 1.35; margin-bottom: 6px;">
                    <div style="font-weight: bold; color: #111827;">Bank Details:</div>
                    <div>Bank Name: <strong>HDFC Bank</strong></div>
                    <div>Account No.: <strong>50200088909890</strong></div>
                    <div>Branch: <strong>Avadi, Chennai - 54</strong> &bull; IFSC: <strong>HDFC0001859</strong></div>
                </div>

                <div style="font-size: 6.8pt; color: #4b5563; line-height: 1.25;">
                    <div style="font-weight: bold; color: #111827;">Terms and Conditions:</div>
                    <div>1. Goods once sold will not be taken back.</div>
                    <div>2. Interest @ 18% p.a. will be charged if payment is not made within the due date.</div>
                    <div>3. Subject to Chennai jurisdiction.</div>
                </div>
            </td>

            {{-- Right Column --}}
            <td style="width: 42%; vertical-align: top; padding: 0;">
                <table style="width: 100%; border-collapse: collapse; font-size: 7.6pt;">
                    <tr>
                        <td style="padding: 4px 8px; border-bottom: 1px solid #e5e7eb; color: #374151;">Sub Total</td>
                        <td style="padding: 4px 8px; border-bottom: 1px solid #e5e7eb; text-align: right; font-weight: bold;">&#8377; {{ number_format($subtotal, 2) }}</td>
                    </tr>
                    @if($roundOff != 0)
                    <tr>
                        <td style="padding: 4px 8px; border-bottom: 1px solid #e5e7eb; color: #374151;">Round off</td>
                        <td style="padding: 4px 8px; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $roundOff > 0 ? '+' : '' }}{{ number_format($roundOff, 2) }}</td>
                    </tr>
                    @endif
                    <tr style="border-bottom: 1px solid #555555; background-color: #f1f5f9; font-weight: bold;">
                        <td style="padding: 5px 8px; font-size: 8.5pt;">Total</td>
                        <td style="padding: 5px 8px; font-size: 8.5pt; text-align: right;">&#8377; {{ number_format($roundedTotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 8px; border-bottom: 1px solid #e5e7eb; color: #374151;">Received</td>
                        <td style="padding: 4px 8px; border-bottom: 1px solid #e5e7eb; text-align: right;">&#8377; 0.00</td>
                    </tr>
                    <tr style="border-bottom: 1.5px solid #555555; font-weight: bold;">
                        <td style="padding: 4px 8px; color: #111827;">Balance</td>
                        <td style="padding: 4px 8px; text-align: right; color: #111827;">&#8377; {{ number_format($roundedTotal, 2) }}</td>
                    </tr>
                </table>

                <div style="padding: 8px 10px; text-align: right; margin-top: 15px; font-size: 7.4pt;">
                    <div style="font-weight: bold; color: #111827;">For Precision IT Systems</div>
                    <div style="height: 52px;"></div>
                    <div style="font-weight: bold; color: #111827;">Authorized Signatory</div>
                </div>
            </td>
        </tr>
    </table>
@else
    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td class="header-logo-cell">
                @if($logoBase64)
                    <img src="data:image/png;base64,{{ $logoBase64 }}" class="header-logo-img" alt="PRECISION IT SYSTEMS">
                @else
                    <div style="font-size: 15pt; font-weight: bold; color: #0b2861;">
                        PRECISION <span style="font-size: 10pt; color: #333; display: block;">IT SYSTEMS</span>
                    </div>
                @endif
            </td>
            <td class="header-address-cell">
                <div class="company-name">PRECISION IT SYSTEMS</div>
                <div>Plot No.553, Lig-1, 27th Street, Tamil Nadu Housing Board,</div>
                <div>Avadi, Chennai-600054.</div>
                <div>Contact No: 8939883299,</div>
                <div>Email: precisionitsystem@gmail.com</div>
                <div style="font-weight: bold;">GSTIN: 33AHLPI3531N1Z8</div>
            </td>
        </tr>
    </table>

    {{-- Quotation Title --}}
    <div class="quotation-title">Quotation</div>

    {{-- Meta: Reference No & Date --}}
    <table class="meta-table">
        <tr>
            <td class="meta-ref">Reference No: &nbsp; {{ $refNo }}</td>
            <td class="meta-date">Date: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; {{ $formattedDate }}</td>
        </tr>
    </table>

    {{-- To Customer --}}
    <div class="to-section">
        <div class="to-label">To</div>
        <div class="to-customer">{{ $customerName }}</div>
        @foreach($addressLines as $line)
            <div>{{ $line }}</div>
        @endforeach
    </div>

    {{-- Salutation --}}
    <div class="salutation">
        <div>Dear sir,</div>
        <div>with reference to your RFQ, please find our competitive offer below. We look forward to your order</div>
    </div>

    {{-- Section Heading --}}
    <div class="section-heading">Network</div>

    {{-- Items Table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-desc">ITEM DESCRIPTION</th>
                <th class="col-qty">QTY</th>
                <th class="col-quoted">Quoted Rate Excl<br>of Tax (INR)</th>
                <th class="col-discount">Disocunt Rate<br>Excl of Tax (INR)</th>
                <th class="col-total">Total Price Excl<br>of Tax (INR)</th>
            </tr>
        </thead>
        <tbody>
            @if($isTurnkey)
                <tr>
                    <td class="col-no">1</td>
                    <td class="col-desc">
                        <strong>Complete Turnkey CCTV Surveillance System & Security Infrastructure Package</strong>
                        <div style="font-size: 7pt; color: #444; margin-top: 1px;">
                            Supply, Installation, System Integration, Cabling, Setup, Testing &amp; Commissioning as per Site Specifications.
                            @if(!empty($quotation->notes))
                                <br>{{ $quotation->notes }}
                            @endif
                        </div>
                    </td>
                    <td class="col-qty">1</td>
                    <td class="col-quoted"><span class="currency-symbol">&#8377;</span> {{ number_format($subtotal, 2) }}</td>
                    <td class="col-discount"><span class="currency-symbol">&#8377;</span> {{ number_format($subtotal, 2) }}</td>
                    <td class="col-total">{{ number_format($subtotal, 2) }}</td>
                </tr>
            @else
                @php $index = 1; @endphp
                @forelse($items as $item)
                    @php
                        $qty = $item->quantity ?? 1;
                        $rate = (float)($item->unit_price ?? 0);
                        $discountRate = isset($item->discount_rate) && $item->discount_rate > 0 ? (float)$item->discount_rate : $rate;
                        $lineTotal = (float)($item->total ?? ($rate * $qty));
                    @endphp
                    <tr>
                        <td class="col-no">{{ $index++ }}</td>
                        <td class="col-desc">
                            <div>{{ $item->item_name }}</div>
                            @if(!empty($item->description))
                                <div style="font-size: 7.2pt; color: #222; margin-top: 1px;">{!! nl2br(e($item->description)) !!}</div>
                            @endif
                        </td>
                        <td class="col-qty">{{ $qty }}</td>
                        <td class="col-quoted"><span class="currency-symbol">&#8377;</span> {{ number_format($rate, 2) }}</td>
                        <td class="col-discount"><span class="currency-symbol">&#8377;</span> {{ number_format($discountRate, 2) }}</td>
                        <td class="col-total">{{ number_format($lineTotal, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="col-no">1</td>
                        <td class="col-desc">8 Port Network Switch</td>
                        <td class="col-qty">1</td>
                        <td class="col-quoted"><span class="currency-symbol">&#8377;</span> 1,300.00</td>
                        <td class="col-discount"><span class="currency-symbol">&#8377;</span> 1,300.00</td>
                        <td class="col-total">1,300.00</td>
                    </tr>
                @endforelse
            @endif

            {{-- Empty spacing row maintaining vertical grid column lines --}}
            <tr class="empty-row">
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

            {{-- Sub Total Row --}}
            <tr>
                <td colspan="4" style="border: 1px solid #000; border-right: none; background-color: #ffffff;"></td>
                <td class="summary-label">Sub Total</td>
                <td class="summary-value">{{ number_format($subtotal, 2) }}</td>
            </tr>

            {{-- GST Tax Row --}}
            <tr>
                <td colspan="4" style="border: 1px solid #000; border-right: none; background-color: #ffffff;"></td>
                <td class="summary-label">GST Tax {{ $taxPercent }} %</td>
                <td class="summary-value">{{ number_format($taxAmount, 2) }}</td>
            </tr>

            @if($roundOff != 0)
            <tr>
                <td colspan="4" style="border: 1px solid #000; border-right: none; background-color: #ffffff;"></td>
                <td class="summary-label">Round off</td>
                <td class="summary-value">{{ $roundOff > 0 ? '+ ' : '- ' }}{{ number_format(abs($roundOff), 2) }}</td>
            </tr>
            @endif

            {{-- Grand Total Row --}}
            <tr class="total-row">
                <td colspan="4" style="border: 1px solid #000; border-right: none; background-color: #ffffff;"></td>
                <td class="summary-label">Total</td>
                <td class="summary-value">&#8377; {{ number_format($roundedTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Terms & Condition --}}
    <div class="terms-section">
        <div class="terms-title">Terms &amp; Condition</div>
        <div><strong>Taxes:</strong> As mentioned above Our GSTIN: 33AHLPI3531N1Z8</div>
        <div><strong>Delivery:</strong> 20 Days after Receving the Advance Payment &amp; PO</div>
        <div><strong>Payment :</strong> 50% in Advance along with PO.</div>
        <div><strong>validity:</strong> Price validity is 2 Days</div>
        <div><strong>warranty:</strong> One Year</div>
    </div>

    {{-- Sign-off & Seal Block --}}
    <div class="footer-section">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="vertical-align: top; width: 55%;">
                    <div>Thanking you.</div>
                    <div class="footer-company">For Precision IT Systems</div>
                    <div style="margin-top: 2px;">ph.: +91-9677257774</div>
                    <div>email: precisionitsystem@gmail.com</div>
                </td>
                <td style="vertical-align: top; width: 45%; text-align: right;">
                    <div style="font-weight: bold; font-size: 7.6pt; color: #1e293b;">For :Precision IT Systems</div>
                    <div style="height: 55px;"></div>
                    <div style="font-weight: bold; font-size: 7.8pt; color: #0f172a;">Authorized Signatory</div>
                </td>
            </tr>
        </table>
    </div>

    @if($isAboveTen)
        <div style="page-break-before: always; height: 10px;"></div>

        <table style="width: 100%; border-collapse: collapse; font-size: 8pt; font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; border: 1px solid #000;">
            <thead>
                <tr style="background-color: #f1f5f9;">
                    <th style="border: 1px solid #000; width: 6%; text-align: center; padding: 4px 2px;">#</th>
                    <th style="border: 1px solid #000; width: 48%; text-align: left; padding: 4px 6px;">ITEM DESCRIPTION</th>
                    <th style="border: 1px solid #000; width: 8%; text-align: center; padding: 4px 2px;">QTY</th>
                    <th style="border: 1px solid #000; width: 18%; text-align: right; padding: 4px 6px;">Rate (&#8377;)</th>
                    <th style="border: 1px solid #000; width: 20%; text-align: right; padding: 4px 6px;">Total Amount (&#8377;)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $idx => $it)
                    @php
                        $qty = $it->quantity ?? 1;
                        $rate = (float)($it->unit_price ?? 0);
                        $lTotal = (float)($it->total ?? ($rate * $qty));
                    @endphp
                    <tr>
                        <td style="border: 1px solid #000; text-align: center; padding: 3px 2px;">{{ $idx + 1 }}</td>
                        <td style="border: 1px solid #000; text-align: left; padding: 3px 6px;">
                            <strong>{{ $it->item_name }}</strong>
                            @if(!empty($it->description))
                                <div style="font-size: 7pt; color: #444; margin-top: 1px;">{!! nl2br(e($it->description)) !!}</div>
                            @endif
                        </td>
                        <td style="border: 1px solid #000; text-align: center; padding: 3px 2px;">{{ $qty }}</td>
                        <td style="border: 1px solid #000; text-align: right; padding: 3px 6px;">{{ number_format($rate, 2) }}</td>
                        <td style="border: 1px solid #000; text-align: right; padding: 3px 6px;">{{ number_format($lTotal, 2) }}</td>
                    </tr>
                @endforeach

                {{-- Summary rows matching Image 4 --}}
                <tr>
                    <td colspan="3" style="border: 1px solid #000; border-right: none;"></td>
                    <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px 6px;">Total Amount:</td>
                    <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px 6px;">{{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid #000; border-right: none;"></td>
                    <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px 6px;">GST &#64; {{ $taxPercent }}%:</td>
                    <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px 6px;">{{ number_format($taxAmount, 2) }}</td>
                </tr>
                @if($roundOff != 0)
                <tr>
                    <td colspan="3" style="border: 1px solid #000; border-right: none;"></td>
                    <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px 6px;">Round off:</td>
                    <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px 6px;">{{ $roundOff > 0 ? '+ ' : '- ' }}{{ number_format(abs($roundOff), 2) }}</td>
                </tr>
                @endif
                <tr style="background-color: #fafafa;">
                    <td colspan="3" style="border: 1px solid #000; border-right: none;"></td>
                    <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px 6px;">Net Amount:</td>
                    <td style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px 6px;">{{ number_format($roundedTotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" style="border: 1px solid #000; padding: 6px 8px; font-weight: bold; font-size: 8.5pt;">
                        Rupees: {{ ucwords(str_replace('-', ' ', \App\Models\Quotation::numberToWords($roundedTotal))) }} Rupees only.
                    </td>
                </tr>
            </tbody>
        </table>
    @endif
@endif

</body>
</html>