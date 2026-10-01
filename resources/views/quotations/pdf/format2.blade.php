<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->quotation_no }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #000;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .header-section {
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #111;
        }
        .company-details {
            font-size: 9px;
            color: #444;
            margin-top: 2px;
        }
        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            background-color: #f1f5f9;
            padding: 6px;
            margin: 10px 0 15px;
            letter-spacing: 1px;
            color: #334155;
            border-radius: 4px;
        }
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .grid-table td {
            width: 33.33%;
            border: 1px solid #e2e8f0;
            padding: 10px;
            vertical-align: top;
            box-sizing: border-box;
        }
        .box-title {
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 2px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th {
            background-color: #64748b;
            color: #fff;
            font-weight: bold;
            text-align: left;
            padding: 6px 5px;
            font-size: 9px;
            border: 1px solid #e2e8f0;
        }
        .items-table td {
            padding: 7px 5px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
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
            margin-top: 10px;
        }
        .totals-table td {
            padding: 4px 0;
            font-size: 10px;
        }
        .totals-label {
            text-align: right;
            padding-right: 15px;
            color: #475569;
        }
        .totals-value {
            text-align: right;
            font-weight: bold;
            width: 120px;
        }
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .bottom-table td {
            vertical-align: top;
        }
        .bank-details {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            background-color: #f8fafc;
            width: 320px;
        }
        .sign-area {
            text-align: right;
            padding-top: 15px;
        }
    </style>
</head>
<body>

    <div class="header-section">
        <div class="company-name">PRECISION IT SYSTEMS</div>
        <div class="company-details">
            Plot No.553, Lig-1, 27th Street, Tamil Nadu Housing Board, Avadi, Chennai-600054.<br>
            Phone no. : +91-9677257774<br>
            Email : precisionitsystem@gmail.com<br>
            GSTIN : 33AHLPI3531N1Z8<br>
            State: 33-Tamil Nadu
        </div>
    </div>

    <div class="title">Quotation</div>

    <table class="grid-table">
        <tr>
            <td>
                <div class="box-title">Bill To</div>
                <strong>{{ $quotation->lead->customer_name }}</strong><br>
                @if($quotation->lead->company_name)
                    {{ $quotation->lead->company_name }}<br>
                @endif
                @if($quotation->lead->site_address)
                    {!! nl2br(e($quotation->lead->site_address)) !!}
                @else
                    Chennai, Tamil Nadu
                @endif
                <br>
                GSTIN: 33AADTM8372L1Z5<br>
                State: 33-Tamil Nadu
            </td>
            <td>
                <div class="box-title">Ship To</div>
                <strong>{{ $quotation->lead->customer_name }}</strong><br>
                @if($quotation->lead->company_name)
                    {{ $quotation->lead->company_name }}<br>
                @endif
                @if($quotation->lead->site_address)
                    {!! nl2br(e($quotation->lead->site_address)) !!}
                @else
                    Chennai, Tamil Nadu
                @endif
                <br>
                State: 33-Tamil Nadu
            </td>
            <td>
                <div class="box-title">Quotation Details</div>
                <strong>Quote No:</strong> {{ $quotation->quotation_no }}<br>
                <strong>Date:</strong> {{ ($quotation->quotation_date ?? $quotation->created_at ?? now())->format('d-m-Y') }}<br>
                <strong>Place of supply:</strong> 33-Tamil Nadu
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th class="center" style="width: 5%">#</th>
                <th style="width: 40%">Item name</th>
                <th class="center" style="width: 10%">HSN/SAC</th>
                <th class="center" style="width: 8%">Quantity</th>
                <th class="center" style="width: 8%">Unit</th>
                <th class="right" style="width: 12%">Price/ Unit</th>
                <th class="right" style="width: 17%">Taxable amount</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach($quotation->items as $item)
                <tr>
                    <td class="center">{{ $i++ }}</td>
                    <td>
                        <strong>{{ $item->item_name }}</strong>
                        @if($item->description)
                            <div style="font-size: 8px; color: #555; margin-top: 2px;">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td class="center">8525</td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="center">{{ $item->unit ?? 'Nos' }}</td>
                    <td class="right">₹{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">₹{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $halfTaxPercent = $quotation->tax_percent / 2;
        $halfTaxAmount = $quotation->tax_amount / 2;
        $grandTotal = $quotation->total;
        $roundedTotal = round($grandTotal);
        $roundOff = $roundedTotal - $grandTotal;
    @endphp

    <table class="totals-table">
        <tr>
            <td style="width: 60%; vertical-align: top; font-size: 9px; color: #555;">
                <strong>Invoice Amount In Words:</strong><br>
                Rupees {{ ucwords(str_replace('-', ' ', \App\Models\Quotation::numberToWords($roundedTotal))) }} Only
            </td>
            <td style="width: 40%;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="totals-label">Sub Total</td>
                        <td class="totals-value">₹{{ number_format($quotation->subtotal, 2) }}</td>
                    </tr>
                    @if($quotation->discount > 0)
                        <tr>
                            <td class="totals-label" style="color: #b91c1c">Discount</td>
                            <td class="totals-value" style="color: #b91c1c">−₹{{ number_format($quotation->discount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="totals-label">SGST@{{ $halfTaxPercent }}%</td>
                        <td class="totals-value">₹{{ number_format($halfTaxAmount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="totals-label">CGST@{{ $halfTaxPercent }}%</td>
                        <td class="totals-value">₹{{ number_format($halfTaxAmount, 2) }}</td>
                    </tr>
                    @if($roundOff != 0)
                        <tr>
                            <td class="totals-label">Round off</td>
                            <td class="totals-value">{{ $roundOff > 0 ? '+' : '' }}₹{{ number_format($roundOff, 2) }}</td>
                        </tr>
                    @endif
                    <tr style="border-top: 1px solid #94a3b8; font-weight: bold; font-size: 11px;">
                        <td class="totals-label" style="padding-top: 6px;">Total</td>
                        <td class="totals-value" style="padding-top: 6px; font-size: 12px; color: #0f172a;">₹{{ number_format($roundedTotal, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="bottom-table">
        <tr>
            <td>
                <div class="bank-details">
                    <div style="font-weight: bold; font-size: 9px; text-transform: uppercase; color: #475569; margin-bottom: 5px;">Pay To:</div>
                    <strong>Bank Name :</strong> HDFC BANK, CHENNAI - ASHOK NAGAR<br>
                    <strong>Bank Account No. :</strong> 50200080470710<br>
                    <strong>Bank IFSC code :</strong> HDFC0000136<br>
                    <strong>Account holder's name :</strong> NP Solutions
                </div>
                
                <div style="margin-top: 15px; font-size: 8px; color: #64748b;">
                    <strong>Terms and Conditions:</strong><br>
                    1. Goods once sold will not be taken back.<br>
                    2. Warranty as per manufacturer terms.
                </div>
            </td>
            <td class="sign-area">
                <div style="font-weight: bold; margin-bottom: 45px;">For :NP SOLUTIONS</div>
                <div style="border-top: 1px dashed #cbd5e1; display: inline-block; width: 150px; padding-top: 4px; font-size: 9px; font-weight: bold; color: #475569; text-align: center;">
                    Authorized Signatory
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
