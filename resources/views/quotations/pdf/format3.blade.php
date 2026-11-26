<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ ($docType ?? ($quotation->doc_type ?? 'quotation')) === 'invoice' ? ('Tax Invoice ' . (method_exists($quotation, 'getFormattedInvoiceNo') ? $quotation->getFormattedInvoiceNo('3') : 'LP/26-27/02')) : ('Quotation ' . (method_exists($quotation, 'getFormattedReferenceNo') ? $quotation->getFormattedReferenceNo('3') : ($quotation->quotation_no ?? 'LP/26-27/02'))) }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 10mm 6mm 10mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 7.6pt;
            color: #1e293b;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .header-table {
            margin-top: 4px;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .header-left {
            width: 58%;
            vertical-align: top;
            font-size: 7.4pt;
            line-height: 1.25;
        }
        .company-name {
            font-size: 12pt;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 1px;
            letter-spacing: 0.5px;
        }
        .header-right {
            width: 42%;
            text-align: right;
            vertical-align: top;
            padding-top: 1px;
        }
        .logo-img {
            width: 125px;
            height: auto;
            display: inline-block;
        }
        .header-divider {
            border: none;
            border-top: 1px solid #cbd5e1;
            margin: 4px 0 6px 0;
        }
        .doc-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            color: #3875c5;
            margin: 2px 0 6px 0;
            letter-spacing: 0.5px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 7.4pt;
            line-height: 1.25;
        }
        .meta-left {
            width: 55%;
            vertical-align: top;
        }
        .meta-right {
            width: 45%;
            vertical-align: top;
            text-align: right;
        }
        .meta-heading {
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 1px;
        }
        .client-name {
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.2pt;
        }
        .items-table th {
            background-color: #3875c5;
            color: #ffffff;
            font-weight: bold;
            padding: 3.5px 2px;
            font-size: 7.2pt;
            vertical-align: middle;
            border: none;
            text-align: center;
        }
        .items-table th.th-left {
            text-align: left;
            padding-left: 4px;
        }
        .items-table th.th-right {
            text-align: right;
            padding-right: 4px;
        }
        .items-table td {
            padding: 2.2px 2px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
            font-size: 7.2pt;
            line-height: 1.15;
            color: #1e293b;
        }
        .col-num {
            width: 3.5%;
            text-align: center;
        }
        .col-name {
            width: 33.5%;
            text-align: left;
            padding-left: 4px !important;
        }
        .col-hsn {
            width: 7%;
            text-align: center;
        }
        .col-qty {
            width: 7%;
            text-align: center;
        }
        .col-unit {
            width: 5%;
            text-align: center;
        }
        .col-price {
            width: 14%;
            text-align: right;
            padding-right: 4px !important;
        }
        .col-gst {
            width: 15%;
            text-align: right;
            padding-right: 4px !important;
        }
        .col-amount {
            width: 15%;
            text-align: right;
            padding-right: 4px !important;
        }
        .total-row td {
            border-top: 1.2px solid #0f172a;
            border-bottom: 1.2px solid #0f172a;
            font-weight: bold;
            padding: 3.5px 2px;
            background-color: #ffffff;
        }
        .bottom-section {
            width: 100%;
            page-break-inside: avoid;
            margin-top: 6px;
        }
        .middle-layout {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        .middle-left {
            width: 53%;
            vertical-align: top;
            padding-right: 12px;
        }
        .middle-right {
            width: 47%;
            vertical-align: top;
        }
        .section-title {
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 1px;
            font-size: 7.5pt;
        }
        .words-val {
            font-size: 7.2pt;
            color: #334155;
            margin-bottom: 6px;
            line-height: 1.25;
        }
        .terms-val {
            font-size: 7.2pt;
            color: #334155;
            line-height: 1.2;
        }
        .calc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.4pt;
        }
        .calc-table td {
            padding: 1.5px 0;
        }
        .calc-label {
            text-align: left;
            padding-left: 6px;
            color: #334155;
        }
        .calc-val {
            text-align: right;
            padding-right: 4px;
            color: #0f172a;
        }
        .calc-total-row {
            background-color: #3875c5;
            color: #ffffff;
            font-weight: bold;
        }
        .calc-total-row td {
            padding: 3.5px 4px;
            color: #ffffff;
            font-size: 8pt;
        }
        .calc-balance-row td {
            border-bottom: 1.2px solid #52525b;
            padding-bottom: 3px;
        }

        /* Bottom Pay To & Signatory aligned table */
        .pay-sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 7.4pt;
            page-break-inside: avoid;
        }
        .pay-col {
            width: 53%;
            vertical-align: top;
            padding-right: 12px;
        }
        .sign-col {
            width: 47%;
            vertical-align: top;
            text-align: right;
        }
        .bank-info {
            font-size: 7.2pt;
            line-height: 1.3;
            color: #334155;
        }
        .sign-box {
            float: right;
            width: 155px;
            text-align: center;
            margin-right: 2px;
        }
        .sign-for-title {
            font-size: 7.6pt;
            color: #1e293b;
            text-align: center;
        }
        .sign-auth-title {
            font-size: 7.6pt;
            font-weight: bold;
            color: #0f172a;
            text-align: center;
        }
    </style>
</head>
<body>

@php
    $logoPath = public_path('images/linepix-logo-clean.png');
    $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';

    $docType = $docType ?? $quotation->doc_type ?? 'quotation';
    $isInvoice = ($docType === 'invoice');
    $invoiceNo = method_exists($quotation, 'getFormattedInvoiceNo')
        ? $quotation->getFormattedInvoiceNo('3')
        : 'LP/26-27/02';

    $quoteNo = method_exists($quotation, 'getFormattedReferenceNo')
        ? $quotation->getFormattedReferenceNo('3')
        : ($quotation->quotation_no ?? 'LP/26-27/02');
    $quoteDate = $quotation->quotation_date ?? $quotation->created_at ?? now();
    $formattedDate = $quoteDate instanceof \Carbon\Carbon ? $quoteDate->format('d-m-Y') : date('d-m-Y', strtotime($quoteDate));

    $lead = $quotation->lead ?? null;
    $customerName = $lead?->customer_name ?? 'ACS MEDICAL COLLEGE AND HOSPITAL';
    $address = $lead?->site_address ?? '';

    $addressLines = [];
    if (!empty($address)) {
        $rawLines = preg_split('/\r\n|\r|\n/', $address);
        foreach ($rawLines as $l) {
            $l = trim($l);
            if ($l !== '') $addressLines[] = $l;
        }
    }
    if (empty($addressLines)) {
        $addressLines[] = 'Periyar EVR High Road, NH 4, Chennai – Bangalore';
        $addressLines[] = 'Highway, Velappanchavadi, Chennai – 600 077';
    }

    $subtotal = (float)($quotation->subtotal ?? 171000.00);
    $taxPercent = (float)($quotation->tax_percent ?? 18);
    $halfTaxPercent = round($taxPercent / 2);
    $taxAmount = (float)($quotation->tax_amount ?? 30780.00);
    $halfTaxAmount = round($taxAmount / 2, 2);
    $grandTotal = (float)($quotation->total ?? 201780.00);

    $items = $quotation->items ?? collect();
    $isAboveTen = count($items) > 10;
    $isTurnkey = (($quotation->pricing_mode ?? 'itemized') === 'total_only') || $isAboveTen;

    $totalQty = 0;
    foreach($items as $it) {
        $totalQty += ($it->quantity ?? 1);
    }
    if ($totalQty === 0) $totalQty = 2;

    $roundedTotal = round($grandTotal);
    $roundOff = round($roundedTotal - $grandTotal, 2);
    $amountInWords = ucwords(str_replace('-', ' ', \App\Models\Quotation::numberToWords($roundedTotal))) . ' Rupees only';
@endphp

    {{-- Top Header --}}
    <table class="header-table">
        <tr>
            <td class="header-left">
                <div class="company-name">LINEPIX</div>
                <div>Plot No 553 LIG I, 27th Street,</div>
                <div>Tamil Nadu Housing Board, Avadi, Chennai - 54</div>
                <div>Phone no. : 8939884299</div>
                <div>Email : linepix.desk@gmail.com</div>
                <div>GSTIN : 33AHLPI3531N2Z7</div>
                <div>State: 33-Tamil Nadu</div>
            </td>
            <td class="header-right">
                @if($logoBase64)
                    <img src="data:image/png;base64,{{ $logoBase64 }}" class="logo-img" alt="Linepix">
                @else
                    <div style="font-size: 22pt; font-weight: bold; color: #0e3b64;">
                        Line<span style="color: #00a884;">pix</span>
                    </div>
                    <div style="font-size: 8pt; color: #00a884; font-style: italic;">a pixel by pixel solutions</div>
                @endif
            </td>
        </tr>
    </table>

    <hr class="header-divider">

    {{-- Document Title --}}
    <div class="doc-title">{{ $isInvoice ? 'Tax Invoice' : 'Quotation' }}</div>

    {{-- Two Column Meta Table --}}
    <table class="meta-table">
        <tr>
            <td class="meta-left">
                <div class="meta-heading">Bill To</div>
                <div class="client-name">{{ $customerName }}</div>
                @foreach($addressLines as $line)
                    <div>{{ $line }}</div>
                @endforeach
                <div>GSTIN : 33AADTM8372L1Z5</div>
                <div>State: 33-Tamil Nadu</div>
            </td>
            <td class="meta-right">
                <div class="meta-heading">{{ $isInvoice ? 'Invoice Details' : 'Quotation Details' }}</div>
                <div>{{ $isInvoice ? 'Invoice No. :' : 'Quote No. :' }} {{ $isInvoice ? $invoiceNo : $quoteNo }}</div>
                <div>Date : {{ $formattedDate }}</div>
                <div>Place of supply: 33-Tamil Nadu</div>
            </td>
        </tr>
    </table>

    {{-- Items Table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-name th-left">Item name</th>
                <th class="col-hsn">HSN/<br>SAC</th>
                <th class="col-qty">Quantity</th>
                <th class="col-unit">Unit</th>
                <th class="col-price th-right">Price/ Unit</th>
                <th class="col-gst th-right">GST</th>
                <th class="col-amount th-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @if($isTurnkey)
                <tr>
                    <td class="col-num">1</td>
                    <td class="col-name">
                        <div style="font-weight: bold;">Complete Turnkey CCTV Surveillance System &amp; Infrastructure Package</div>
                        <div style="font-size: 7pt; color: #64748b; margin-top: 1px;">
                            Supply, Installation, System Integration, Cabling, Setup, Testing &amp; Commissioning as per Site Specifications.
                            @if(!empty($quotation->notes))
                                <br>{{ $quotation->notes }}
                            @endif
                        </div>
                    </td>
                    <td class="col-hsn">8525</td>
                    <td class="col-qty">1</td>
                    <td class="col-unit">Set</td>
                    <td class="col-price">&#8377; {{ number_format($subtotal, 2) }}</td>
                    <td class="col-gst">&#8377; {{ number_format($taxAmount, 2) }}<br><span style="font-size: 6.8pt; color: #64748b;">({{ $taxPercent }}%)</span></td>
                    <td class="col-amount">&#8377; {{ number_format($grandTotal, 2) }}</td>
                </tr>
            @else
                @php $idx = 1; @endphp
                @forelse($items as $item)
                    @php
                        $qty = $item->quantity ?? 1;
                        $unitPrice = (float)($item->unit_price ?? 0);
                        $taxable = (float)($item->total ?? ($unitPrice * $qty));
                        $itemGst = round($taxable * ($taxPercent / 100), 2);
                        $lineTotal = round($taxable + $itemGst, 2);
                        $hsn = $item->hsn_code;
                        $unit = $item->unit ?? 'Nos';
                    @endphp
                    <tr>
                        <td class="col-num">{{ $idx++ }}</td>
                        <td class="col-name">
                            <div style="font-weight: bold;">{{ $item->item_name }}</div>
                            @if(!empty($item->description))
                                <div style="font-size: 7pt; color: #475569; margin-top: 1px;">{!! nl2br(e($item->description)) !!}</div>
                            @endif
                        </td>
                        <td class="col-hsn">{{ $hsn }}</td>
                        <td class="col-qty">{{ $qty }}</td>
                        <td class="col-unit">{{ $unit }}</td>
                        <td class="col-price">&#8377; {{ number_format($unitPrice, 2) }}</td>
                        <td class="col-gst">&#8377; {{ number_format($itemGst, 2) }}<br><span style="font-size: 6.8pt; color: #64748b;">({{ $taxPercent }}%)</span></td>
                        <td class="col-amount">&#8377; {{ number_format($lineTotal, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="col-num">1</td>
                        <td class="col-name"><strong>Pro - Pupil - Student Attendance Management System V2</strong></td>
                        <td class="col-hsn"></td>
                        <td class="col-qty">1</td>
                        <td class="col-unit">Nos</td>
                        <td class="col-price">&#8377; 1,56,000.00</td>
                        <td class="col-gst">&#8377; 28,080.00<br><span style="font-size: 6.8pt; color: #64748b;">(18%)</span></td>
                        <td class="col-amount">&#8377; 1,84,080.00</td>
                    </tr>
                    <tr>
                        <td class="col-num">2</td>
                        <td class="col-name"><strong>Student Data Management and Configuration</strong></td>
                        <td class="col-hsn"></td>
                        <td class="col-qty">1</td>
                        <td class="col-unit">Nos</td>
                        <td class="col-price">&#8377; 15,000.00</td>
                        <td class="col-gst">&#8377; 2,700.00<br><span style="font-size: 6.8pt; color: #64748b;">(18%)</span></td>
                        <td class="col-amount">&#8377; 17,700.00</td>
                    </tr>
                @endforelse
            @endif

            {{-- Total Row --}}
            <tr class="total-row">
                <td colspan="2" style="text-align: left; padding-left: 5px;">Total</td>
                <td></td>
                <td style="text-align: center;">{{ $isTurnkey ? 1 : $totalQty }}</td>
                <td colspan="2"></td>
                <td style="text-align: right; padding-right: 5px;">&#8377; {{ number_format($taxAmount, 2) }}</td>
                <td style="text-align: right; padding-right: 5px;">&#8377; {{ number_format($roundedTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Bottom Layout --}}
    {{-- Bottom Section (page-break-inside: avoid ensures it stays intact) --}}
    <div class="bottom-section">
        {{-- Middle Layout: Words, Terms & Calculations --}}
        <table class="middle-layout">
            <tr>
                {{-- Left: Amount in words & Terms --}}
                <td class="middle-left">
                    <div class="section-title">Invoice Amount In Words</div>
                    <div class="words-val">{{ $amountInWords }}</div>

                    <div class="section-title">Terms and Conditions</div>
                    <div class="terms-val">Thanks for doing business with us!</div>
                </td>

                {{-- Right: Calculation Breakdown --}}
                <td class="middle-right">
                    <table class="calc-table">
                        <tr>
                            <td class="calc-label">Sub Total</td>
                            <td class="calc-val">&#8377; {{ number_format($subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="calc-label">SGST&#64;{{ $halfTaxPercent }}%</td>
                            <td class="calc-val">&#8377; {{ number_format($halfTaxAmount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="calc-label">CGST&#64;{{ $halfTaxPercent }}%</td>
                            <td class="calc-val">&#8377; {{ number_format($halfTaxAmount, 2) }}</td>
                        </tr>
                        @if($roundOff != 0)
                        <tr>
                            <td class="calc-label">Round off</td>
                            <td class="calc-val">{{ $roundOff > 0 ? '+ ' : '- ' }}&#8377; {{ number_format(abs($roundOff), 2) }}</td>
                        </tr>
                        @endif
                        <tr class="calc-total-row">
                            <td class="calc-label">Total</td>
                            <td class="calc-val">&#8377; {{ number_format($roundedTotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="calc-label">Received</td>
                            <td class="calc-val">&#8377; 0.00</td>
                        </tr>
                        <tr class="calc-balance-row">
                            <td class="calc-label">Balance</td>
                            <td class="calc-val">&#8377; {{ number_format($roundedTotal, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Bottom Layout: Pay To & Signatory Aligned Table --}}
        <table class="pay-sign-table">
            {{-- Row 1: "Pay To:" horizontally aligned in straight line with "For :LINEPIX" --}}
            <tr>
                <td class="pay-col">
                    <div class="section-title">Pay To:</div>
                </td>
                <td class="sign-col">
                    <div class="sign-box">
                        <div class="sign-for-title">For :LINEPIX</div>
                    </div>
                </td>
            </tr>

            {{-- Row 2: Bank details lines on left, Signature vertical gap on right --}}
            <tr>
                <td class="pay-col" style="padding-top: 1px;">
                    <div class="bank-info">
                        Bank Name : HDFC BANK, ASHOK NAGAR<br>
                        Bank Account No. : 50200080043394<br>
                        Bank IFSC code : HDFC0000136
                    </div>
                </td>
                <td class="sign-col" style="vertical-align: middle;">
                    <div class="sign-box" style="height: 32px;"></div>
                </td>
            </tr>

            {{-- Row 3: "Account holder's name : LINEPIX" horizontally aligned in straight line with "Authorized Signatory" --}}
            <tr>
                <td class="pay-col" style="padding-top: 1px;">
                    <div class="bank-info">
                        Account holder's name : LINEPIX
                    </div>
                </td>
                <td class="sign-col" style="padding-top: 1px;">
                    <div class="sign-box">
                        <div class="sign-auth-title">Authorized Signatory</div>
                    </div>
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

</body>
</html>