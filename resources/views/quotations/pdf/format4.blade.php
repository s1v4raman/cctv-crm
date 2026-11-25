<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax Invoice / Quotation {{ $quotation->quotation_no }}</title>
    <style>
        @page {
            margin: 14mm 15mm 14mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        
        /* Top Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .company-info {
            font-size: 9px;
            color: #334155;
            line-height: 1.35;
        }
        .logo-cell {
            text-align: right;
            vertical-align: top;
            width: 40%;
        }
        .logo-wordmark {
            font-size: 26px;
            font-weight: bold;
            line-height: 1;
            letter-spacing: -0.5px;
        }
        .logo-wordmark .line-part {
            color: #0e3b64;
        }
        .logo-wordmark .pix-part {
            color: #00a884;
        }
        .logo-tagline {
            font-size: 9px;
            font-style: italic;
            color: #00a884;
            letter-spacing: 0.5px;
            margin-top: 3px;
        }
        
        .header-divider {
            border: none;
            border-top: 1px solid #cbd5e1;
            margin: 6px 0 10px 0;
        }
        
        /* Document Title */
        .doc-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            color: #3875c5;
            margin: 6px 0 14px 0;
            letter-spacing: 0.5px;
        }
        
        /* Two Column Meta: Bill To & Quotation/Invoice Details */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .meta-table td {
            vertical-align: top;
            width: 50%;
        }
        .meta-label {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            margin-bottom: 2px;
        }
        .client-name {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .meta-text {
            font-size: 9px;
            color: #334155;
            line-height: 1.35;
        }
        .meta-right {
            text-align: right;
        }
        .meta-right-row {
            margin-bottom: 3px;
            font-size: 9px;
            color: #334155;
        }
        .meta-right-row strong {
            color: #0f172a;
        }
        
        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .items-table th {
            background-color: #3875c5;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            padding: 7px 6px;
            border: 1px solid #3875c5;
            text-align: left;
        }
        .items-table td {
            padding: 7px 6px;
            font-size: 9px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            color: #1e293b;
        }
        .items-table th.center, .items-table td.center {
            text-align: center;
        }
        .items-table th.right, .items-table td.right {
            text-align: right;
        }
        .item-title {
            font-weight: bold;
            color: #0f172a;
            font-size: 9px;
        }
        .item-desc {
            font-size: 8px;
            color: #64748b;
            margin-top: 2px;
            line-height: 1.25;
        }
        .tax-subtext {
            font-size: 8px;
            color: #64748b;
            margin-top: 1px;
        }
        
        /* Table Total Row */
        .items-table tr.total-row td {
            border-top: 1.5px solid #0f172a;
            border-bottom: 1.5px solid #0f172a;
            font-weight: bold;
            font-size: 9px;
            padding: 6px;
            background-color: #f8fafc;
        }
        
        /* Bottom Two Column Layout */
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .bottom-table td {
            vertical-align: top;
        }
        .bottom-left {
            width: 55%;
            padding-right: 15px;
        }
        .bottom-right {
            width: 45%;
        }
        
        .section-heading {
            font-size: 9px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 3px;
        }
        .amount-in-words {
            font-size: 9px;
            color: #334155;
            margin-bottom: 12px;
        }
        .terms-block {
            font-size: 9px;
            color: #334155;
            margin-bottom: 14px;
            line-height: 1.35;
        }
        .bank-block {
            font-size: 9px;
            color: #334155;
            line-height: 1.45;
        }
        .bank-block strong {
            color: #0f172a;
        }
        
        /* Right Side Summary Table */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .summary-table td {
            padding: 3.5px 6px;
            font-size: 9px;
        }
        .summary-label {
            text-align: left;
            color: #334155;
        }
        .summary-value {
            text-align: right;
            font-weight: bold;
            color: #0f172a;
        }
        .summary-total-row td {
            background-color: #3875c5;
            color: #ffffff !important;
            font-weight: bold;
            font-size: 10.5px;
            padding: 5.5px 8px;
        }
        .summary-total-row .summary-label {
            color: #ffffff !important;
        }
        .summary-total-row .summary-value {
            color: #ffffff !important;
        }
        
        /* Sign Area: NO seal, NO handwritten ink signatures */
        .sign-area {
            text-align: right;
            margin-top: 15px;
            padding-right: 8px;
        }
        .for-company {
            font-size: 9px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 45px;
        }
        .sign-title {
            font-size: 9px;
            font-weight: bold;
            color: #0f172a;
        }
    </style>
</head>
<body>

    @php
        $taxRate = (float)($quotation->tax_percent ?? 18);
        $halfTaxRate = $taxRate / 2;
        $isTurnkey = ($quotation->pricing_mode ?? 'itemized') === 'total_only';
        
        $totalQty = 0;
        $totalTax = 0;
        $totalAmount = 0;
    @endphp

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="company-name">LINEPIX</div>
                <div class="company-info">
                    Plot No 553 LIG I, 27th Street,<br>
                    Tamil Nadu Housing Board, Avadi, Chennai - 54<br>
                    Phone no. : 8939884299<br>
                    Email : linepix.desk@gmail.com<br>
                    GSTIN : 33AHLPI3531N2Z7<br>
                    State: 33-Tamil Nadu
                </div>
            </td>
            <td class="logo-cell">
                <div class="logo-wordmark">
                    <span class="line-part">Line</span><span class="pix-part">pix</span>
                </div>
                <div class="logo-tagline">a pixel by pixel solutions</div>
            </td>
        </tr>
    </table>

    <hr class="header-divider">

    {{-- Title: Tax Invoice --}}
    <div class="doc-title">Tax Invoice</div>

    {{-- Two Column Meta Table --}}
    <table class="meta-table">
        <tr>
            <td>
                <div class="meta-label">Bill To</div>
                <div class="client-name">{{ $quotation->lead?->company_name ?: ($quotation->lead?->customer_name ?? 'Customer') }}</div>
                <div class="meta-text">
                    @if($quotation->lead?->site_address)
                        {!! nl2br(e($quotation->lead->site_address)) !!}<br>
                    @else
                        Chennai, Tamil Nadu<br>
                    @endif
                    @if($quotation->lead?->gstin)
                        GSTIN : {{ $quotation->lead->gstin }}<br>
                    @endif
                    State: 33-Tamil Nadu
                </div>
            </td>
            <td class="meta-right">
                <div class="meta-label">Invoice Details</div>
                <div class="meta-right-row">Invoice No. : <strong>{{ $quotation->quotation_no }}</strong></div>
                <div class="meta-right-row">Date : <strong>{{ ($quotation->quotation_date ?? $quotation->created_at ?? now())->format('d-m-Y') }}</strong></div>
                <div class="meta-right-row">Place of supply: <strong>33-Tamil Nadu</strong></div>
            </td>
        </tr>
    </table>

    {{-- Items Table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="center" style="width: 4%;">#</th>
                <th style="width: 38%;">Item name</th>
                <th class="center" style="width: 9%;">HSN/SAC</th>
                <th class="center" style="width: 8%;">Quantity</th>
                <th class="center" style="width: 6%;">Unit</th>
                <th class="right" style="width: 15%;">Price/ Unit</th>
                <th class="right" style="width: 12%;">GST</th>
                <th class="right" style="width: 15%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @if($isTurnkey)
                @php
                    $totalQty = 1;
                    $totalTax = (float)$quotation->tax_amount;
                    $totalAmount = (float)$quotation->total;
                @endphp
                <tr>
                    <td class="center">1</td>
                    <td>
                        <div class="item-title">Complete Turnkey CCTV Surveillance System &amp; Security Infrastructure Package</div>
                        <div class="item-desc">
                            Supply, Installation, System Integration, Cabling, Setup, Testing &amp; Commissioning as per Site Specifications.
                            @if($quotation->notes)
                                <br>{{ $quotation->notes }}
                            @endif
                        </div>
                    </td>
                    <td class="center">8525</td>
                    <td class="center">1</td>
                    <td class="center">Set</td>
                    <td class="right">₹ {{ number_format($quotation->subtotal, 2) }}</td>
                    <td class="right">
                        ₹ {{ number_format($quotation->tax_amount, 2) }}
                        <div class="tax-subtext">({{ $taxRate }}%)</div>
                    </td>
                    <td class="right">₹ {{ number_format($quotation->total, 2) }}</td>
                </tr>
            @else
                @php $sno = 1; @endphp
                @foreach($quotation->items as $item)
                    @php
                        $qty = (float)$item->quantity;
                        $totalQty += $qty;
                        $lineSubtotal = (float)$item->total;
                        $lineTax = $lineSubtotal * ($taxRate / 100);
                        $totalTax += $lineTax;
                        $lineTotalWithTax = $lineSubtotal + $lineTax;
                        $totalAmount += $lineTotalWithTax;
                    @endphp
                    <tr>
                        <td class="center">{{ $sno++ }}</td>
                        <td>
                            <div class="item-title">{{ $item->item_name }}</div>
                            @if($item->description)
                                <div class="item-desc">{!! nl2br(e($item->description)) !!}</div>
                            @endif
                        </td>
                        <td class="center">{{ $item->hsn_code ?? '-' }}</td>
                        <td class="center">{{ $qty == (int)$qty ? (int)$qty : $qty }}</td>
                        <td class="center">{{ $item->unit ?: 'Nos' }}</td>
                        <td class="right">₹ {{ number_format($item->unit_price, 2) }}</td>
                        <td class="right">
                            ₹ {{ number_format($lineTax, 2) }}
                            <div class="tax-subtext">({{ $taxRate }}%)</div>
                        </td>
                        <td class="right">₹ {{ number_format($lineTotalWithTax, 2) }}</td>
                    </tr>
                @endforeach
            @endif

            {{-- Table Total Row --}}
            <tr class="total-row">
                <td colspan="3" style="text-align: left; padding-left: 8px;">Total</td>
                <td class="center">{{ $totalQty == (int)$totalQty ? (int)$totalQty : $totalQty }}</td>
                <td></td>
                <td></td>
                <td class="right">₹ {{ number_format($quotation->tax_amount, 2) }}</td>
                <td class="right">₹ {{ number_format($quotation->total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Bottom Two Column Layout --}}
    <table class="bottom-table">
        <tr>
            <td class="bottom-left">
                <div class="section-heading">Invoice Amount In Words</div>
                <div class="amount-in-words">
                    {{ \App\Models\Quotation::indianRupeesWords($quotation->total) }}
                </div>

                <div class="section-heading">Terms and Conditions</div>
                <div class="terms-block">
                    Thanks for doing business with us!
                    @if($quotation->notes)
                        <div style="margin-top: 3px; font-size: 8px; color: #64748b;">
                            {{ $quotation->notes }}
                        </div>
                    @endif
                </div>

                <div class="bank-block">
                    <div class="section-heading">Pay To:</div>
                    <strong>Bank Name :</strong> HDFC BANK, ASHOK NAGAR<br>
                    <strong>Bank Account No. :</strong> 50200080043394<br>
                    <strong>Bank IFSC code :</strong> HDFC0000136<br>
                    <strong>Account holder's name :</strong> LINEPIX
                </div>
            </td>

            <td class="bottom-right">
                <table class="summary-table">
                    <tr>
                        <td class="summary-label">Sub Total</td>
                        <td class="summary-value">₹ {{ number_format($quotation->subtotal - $quotation->discount, 2) }}</td>
                    </tr>
                    @if($quotation->discount > 0)
                        <tr>
                            <td class="summary-label" style="color: #dc2626;">Discount</td>
                            <td class="summary-value" style="color: #dc2626;">- ₹ {{ number_format($quotation->discount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="summary-label">SGST&#64;{{ $halfTaxRate }}%</td>
                        <td class="summary-value">₹ {{ number_format($quotation->tax_amount / 2, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">CGST&#64;{{ $halfTaxRate }}%</td>
                        <td class="summary-value">₹ {{ number_format($quotation->tax_amount / 2, 2) }}</td>
                    </tr>
                    <tr class="summary-total-row">
                        <td class="summary-label">Total</td>
                        <td class="summary-value">₹ {{ number_format($quotation->total, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Received</td>
                        <td class="summary-value">₹ 0.00</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Balance</td>
                        <td class="summary-value">₹ {{ number_format($quotation->total, 2) }}</td>
                    </tr>
                </table>

                {{-- Sign Area: Clean, No seal, No handwritten ink signatures --}}
                <div class="sign-area">
                    <div class="for-company">For :LINEPIX</div>
                    <div class="sign-title">Authorized Signatory</div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
