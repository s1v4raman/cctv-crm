<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ ($docType ?? ($quotation->doc_type ?? 'quotation')) === 'invoice' ? ('Tax Invoice ' . (method_exists($quotation, 'getFormattedInvoiceNo') ? $quotation->getFormattedInvoiceNo('2') : 'NP/26-27/0017')) : ('Quotation ' . ($quotation->quotation_no ?? 'NP/26-27/003')) }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 7mm 10mm 6mm 10mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 7.6pt;
            color: #111111;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        .company-header {
            font-size: 7.4pt;
            line-height: 1.25;
            margin-bottom: 6px;
            padding-bottom: 5px;
            border-bottom: 1.5px solid #555555;
        }
        .company-title {
            font-size: 10pt;
            font-weight: bold;
            color: #000000;
            margin-bottom: 1px;
        }
        .doc-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            color: #4b5563;
            margin-top: 1px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }
        .address-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.4pt;
            line-height: 1.25;
            margin-bottom: 6px;
        }
        .address-grid td {
            vertical-align: top;
            padding: 0;
        }
        .col-bill {
            width: 38%;
            padding-right: 10px !important;
        }
        .col-ship {
            width: 34%;
            padding-right: 10px !important;
        }
        .col-meta {
            width: 28%;
            text-align: right;
        }
        .grid-header {
            font-weight: bold;
            color: #000000;
            margin-bottom: 2px;
        }
        .customer-name {
            font-weight: bold;
            text-transform: uppercase;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.2pt;
        }
        .items-table th {
            background-color: #595959;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 4px 2px;
            vertical-align: middle;
            line-height: 1.15;
            font-size: 7.2pt;
            border: none;
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
            border-bottom: 1px solid #e5e7eb;
            font-size: 7pt;
            line-height: 1.15;
        }
        .t-col-num {
            width: 3%;
            text-align: center;
        }
        .t-col-name {
            width: 25.5%;
            text-align: left;
            padding-left: 4px !important;
        }
        .t-col-hsn {
            width: 6.5%;
            text-align: center;
        }
        .t-col-qty {
            width: 6.5%;
            text-align: center;
        }
        .t-col-unit {
            width: 5%;
            text-align: center;
        }
        .t-col-price {
            width: 12.5%;
            text-align: right;
            padding-right: 4px !important;
        }
        .t-col-taxable {
            width: 13.5%;
            text-align: right;
            padding-right: 4px !important;
        }
        .t-col-gst {
            width: 13.5%;
            text-align: right;
            padding-right: 4px !important;
        }
        .t-col-amount {
            width: 14%;
            text-align: right;
            padding-right: 4px !important;
        }
        .total-row td {
            border-top: 1.5px solid #000000;
            border-bottom: 1.5px solid #000000;
            font-weight: bold;
            padding: 4px 2px;
        }
        .bottom-layout {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 7.5pt;
            page-break-inside: avoid;
        }
        .bottom-left {
            width: 56%;
            vertical-align: top;
            padding-right: 15px;
        }
        .bottom-right {
            width: 44%;
            vertical-align: top;
        }
        .words-title {
            font-weight: bold;
            margin-bottom: 2px;
        }
        .words-val {
            font-size: 7.2pt;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        .terms-title {
            font-weight: bold;
            margin-bottom: 4px;
        }
        .payto-title {
            font-weight: bold;
            margin-top: 8px;
            margin-bottom: 2px;
        }
        .bank-info {
            font-size: 7.2pt;
            line-height: 1.35;
        }
        .calc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        .calc-table td {
            padding: 2.5px 0;
        }
        .calc-label {
            text-align: left;
            padding-left: 6px;
        }
        .calc-val {
            text-align: right;
            padding-right: 6px;
        }
        .calc-total-row {
            background-color: #595959;
            color: #ffffff;
            font-weight: bold;
        }
        .calc-total-row td {
            padding: 4px 6px;
            color: #ffffff;
            font-size: 8.5pt;
        }
        .sign-block {
            margin-top: 10px;
            text-align: right;
        }
        .sign-for {
            font-size: 7.5pt;
        }
        .sign-auth {
            margin-top: 55px;
            font-weight: bold;
            font-size: 7.8pt;
        }
    </style>
</head>
<body>

@php
    $quoteNo = method_exists($quotation, 'getFormattedReferenceNo')
        ? $quotation->getFormattedReferenceNo('2')
        : ($quotation->quotation_no ?? 'NP/26-27/003');
    $quoteDate = $quotation->quotation_date ?? $quotation->created_at ?? now();
    $formattedDate = $quoteDate instanceof \Carbon\Carbon ? $quoteDate->format('d-m-Y') : date('d-m-Y', strtotime($quoteDate));

    $lead = $quotation->lead ?? null;
    $customerName = $lead?->customer_name ?? 'ACS MEDICAL COLLEGE & HOSPITAL';
    $companyName = $lead?->company_name ?? '';
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
        $addressLines[] = 'PERIYAR EVR HIGH ROAD, NH 4,';
        $addressLines[] = 'CHENNAI BANGALORE HIGHWAYS';
        $addressLines[] = 'VELAPPANCHAVADI, CHENNAI - 600 077';
    }

    $subtotal = (float)($quotation->subtotal ?? 150335.00);
    $taxPercent = (float)($quotation->tax_percent ?? 18);
    $halfTaxPercent = round($taxPercent / 2);
    $taxAmount = (float)($quotation->tax_amount ?? 27060.30);
    $halfTaxAmount = round($taxAmount / 2, 2);
    $grandTotal = (float)($quotation->total ?? 177395.30);
    $roundedTotal = round($grandTotal);
    $roundOff = round($roundedTotal - $grandTotal, 2);

    $items = $quotation->items ?? collect();
    $isAboveTen = count($items) > 10;
    $isTurnkey = (($quotation->pricing_mode ?? 'itemized') === 'total_only') || $isAboveTen;

    $totalQty = 0;
    foreach($items as $it) {
        $totalQty += ($it->quantity ?? 1);
    }
    if ($totalQty === 0) $totalQty = 523;

    $docType = $docType ?? $quotation->doc_type ?? 'quotation';
    $isInvoice = ($docType === 'invoice');
    $invoiceNo = method_exists($quotation, 'getFormattedInvoiceNo')
        ? $quotation->getFormattedInvoiceNo('2')
        : 'NP/26-27/0017';

    $amountInWords = ucwords(str_replace('-', ' ', \App\Models\Quotation::numberToWords($roundedTotal))) . ' Rupees only';
@endphp

    {{-- Company Top Header --}}
    <div class="company-header">
        <div class="company-title">NP SOLUTIONS</div>
        <div>NO 12 1ST MAIN ROAD SUBRAMANIYAPURAM, CHENNAI - 77</div>
        <div>Phone no. : 9884664108</div>
        <div>Email : npsolutions.in@gmail.com</div>
        <div>GSTIN : 33DNAPA0957C1ZQ</div>
        <div>State: 33-Tamil Nadu</div>
    </div>

    {{-- Document Title --}}
    <div class="doc-title">{{ $isInvoice ? 'Tax Invoice' : 'Quotation' }}</div>

    {{-- Address & Details 3-Column Grid --}}
    <table class="address-grid">
        <tr>
            <td class="col-bill">
                <div class="grid-header">Bill To</div>
                <div class="customer-name">{{ $customerName }}</div>
                @foreach($addressLines as $line)
                    <div>{{ $line }}</div>
                @endforeach
                <div>GSTIN : 33AADTM8372L1Z5</div>
                <div>State: 33-Tamil Nadu</div>
            </td>
            <td class="col-ship">
                <div class="grid-header">Ship To</div>
                @foreach($addressLines as $line)
                    <div>{{ $line }}</div>
                @endforeach
            </td>
            <td class="col-meta">
                <div class="grid-header">{{ $isInvoice ? 'Invoice Details' : 'Quotation Details' }}</div>
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
                <th class="t-col-num">#</th>
                <th class="t-col-name th-left">Item name</th>
                <th class="t-col-hsn">HSN/<br>SAC</th>
                <th class="t-col-qty">Quantity</th>
                <th class="t-col-unit">Unit</th>
                <th class="t-col-price th-right">Price/ Unit</th>
                <th class="t-col-taxable th-right">Taxable<br>amount</th>
                <th class="t-col-gst th-right">GST</th>
                <th class="t-col-amount th-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @if($isTurnkey)
                <tr>
                    <td class="t-col-num">1</td>
                    <td class="t-col-name">
                        <div style="font-weight: bold;">Complete Turnkey CCTV Surveillance System &amp; Security Infrastructure Package</div>
                        <div style="font-size: 6.8pt; color: #444; margin-top: 1px;">
                            Supply, Installation, System Integration, Cabling, Setup, Testing &amp; Commissioning as per Site Specifications.
                            @if(!empty($quotation->notes))
                                <br>{{ $quotation->notes }}
                            @endif
                        </div>
                    </td>
                    <td class="t-col-hsn">8525</td>
                    <td class="t-col-qty">1</td>
                    <td class="t-col-unit">Set</td>
                    <td class="t-col-price">&#8377; {{ number_format($subtotal, 2) }}</td>
                    <td class="t-col-taxable">&#8377; {{ number_format($subtotal, 2) }}</td>
                    <td class="t-col-gst">&#8377; {{ number_format($taxAmount, 2) }}<br><span style="font-size: 6.5pt; color: #333;">({{ $taxPercent }}%)</span></td>
                    <td class="t-col-amount">&#8377; {{ number_format($grandTotal, 2) }}</td>
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
                        <td class="t-col-num">{{ $idx++ }}</td>
                        <td class="t-col-name">
                            <div style="font-weight: bold;">{{ $item->item_name }}</div>
                            @if(!empty($item->description))
                                <div style="font-size: 6.8pt; color: #444;">{!! nl2br(e($item->description)) !!}</div>
                            @endif
                        </td>
                        <td class="t-col-hsn">{{ $hsn }}</td>
                        <td class="t-col-qty">{{ $qty }}</td>
                        <td class="t-col-unit">{{ $unit }}</td>
                        <td class="t-col-price">&#8377; {{ number_format($unitPrice, 2) }}</td>
                        <td class="t-col-taxable">&#8377; {{ number_format($taxable, 2) }}</td>
                        <td class="t-col-gst">&#8377; {{ number_format($itemGst, 2) }}<br><span style="font-size: 6.5pt; color: #333;">({{ $taxPercent }}%)</span></td>
                        <td class="t-col-amount">&#8377; {{ number_format($lineTotal, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="t-col-num">1</td>
                        <td class="t-col-name"><strong>CP Plus 6MP IP Bullet Camera</strong></td>
                        <td class="t-col-hsn">8525</td>
                        <td class="t-col-qty">8</td>
                        <td class="t-col-unit">Nos</td>
                        <td class="t-col-price">&#8377; 6,500.00</td>
                        <td class="t-col-taxable">&#8377; 52,000.00</td>
                        <td class="t-col-gst">&#8377; 9,360.00<br><span style="font-size: 6.5pt; color: #333;">(18%)</span></td>
                        <td class="t-col-amount">&#8377; 61,360.00</td>
                    </tr>
                @endforelse
            @endif

            {{-- Table Total Row --}}
            <tr class="total-row">
                <td colspan="2" style="text-align: left; padding-left: 4px;">Total</td>
                <td></td>
                <td style="text-align: center;">{{ $isTurnkey ? 1 : $totalQty }}</td>
                <td colspan="2"></td>
                <td style="text-align: right; padding-right: 4px;">&#8377; {{ number_format($subtotal, 2) }}</td>
                <td style="text-align: right; padding-right: 4px;">&#8377; {{ number_format($taxAmount, 2) }}</td>
                <td style="text-align: right; padding-right: 4px;">&#8377; {{ number_format($roundedTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Bottom Section --}}
    <table class="bottom-layout">
        <tr>
            {{-- Left Column: Amount in words, Terms, Bank --}}
            <td class="bottom-left">
                <div class="words-title">Invoice Amount In Words</div>
                <div class="words-val">{{ $amountInWords }}</div>

                <div class="terms-title">Terms and Conditions</div>

                <div class="payto-title">Pay To:</div>
                <div class="bank-info">
                    Bank Name : HDFC BANK, CHENNAI - ASHOK NAGAR<br>
                    Bank Account No. : 50200080470710<br>
                    Bank IFSC code : HDFC0000136<br>
                    Account holder's name : NP Solutions
                </div>
            </td>

            {{-- Right Column: Calculations & Signature --}}
            <td class="bottom-right">
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
                </table>

                <div class="sign-block">
                    <div class="sign-for">For :NP SOLUTIONS</div>
                    <div class="sign-auth">Authorized Signatory</div>
                </div>
            </td>
        </tr>
    </table>

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
