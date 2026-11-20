<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Quotation {{ $quotation->quotation_no ?? 'PITS/QUOT/26-21/58' }}</title>
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
@endphp

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

</body>
</html>