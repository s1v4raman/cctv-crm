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
        .outer-frame {
            border: 3px double #000;
            padding: 15px;
            min-height: 98%;
            box-sizing: border-box;
        }
        .header-section {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        .header-title {
            font-size: 16px;
            font-weight: bold;
            color: #000;
            margin: 2px 0;
        }
        .header-subtitle {
            font-size: 9px;
            font-style: italic;
            margin-bottom: 4px;
        }
        .header-address {
            font-size: 9px;
            color: #111;
        }
        .doc-title-container {
            border: 1px solid #000;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            padding: 4px;
            margin: 8px 0;
            background-color: #f1f5f9;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .meta-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .items-table th {
            border: 1px solid #000;
            background-color: #f8fafc;
            padding: 6px 5px;
            font-weight: bold;
            text-align: left;
            font-size: 9px;
        }
        .items-table td {
            border: 1px solid #000;
            padding: 6px 5px;
            font-size: 9px;
            vertical-align: top;
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
            margin-bottom: 10px;
        }
        .totals-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 10px;
        }
        .terms-box {
            font-size: 8px;
            color: #111;
            margin-top: 15px;
            line-height: 1.4;
        }
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }
        .sign-table td {
            text-align: center;
            font-size: 9px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="outer-frame">

    <div class="header-section">
        <div class="header-title">A.C.S. MEDICAL COLLEGE AND HOSPITAL</div>
        <div class="header-subtitle">(A Constituent Unit of Dr. M.G.R. Educational and Research Institute)</div>
        <div class="header-address">
            Periyar EVR High Road, NH-4, Chennai - Bangalore Highways, Velappanchavadi, Chennai - 600 077.<br>
            Phone : 91 44 26802133 / 155 Website : www.acsmch.ac.in
        </div>
    </div>

    <table style="width: 100%;">
        <tr>
            <td style="font-size: 11px; font-weight: bold;">NO - {{ $quotation->id }}</td>
        </tr>
    </table>

    <div class="doc-title-container">
        QUOTATION DETAILS
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <strong>To:</strong><br>
                <strong>{{ $quotation->lead->customer_name }}</strong><br>
                @if($quotation->lead->company_name)
                    {{ $quotation->lead->company_name }}<br>
                @endif
                @if($quotation->lead->site_address)
                    {!! nl2br(e($quotation->lead->site_address)) !!}
                @else
                    Chennai, Tamil Nadu
                @endif
            </td>
            <td style="width: 50%;">
                <strong>Quotation No:</strong> {{ $quotation->quotation_no }}<br>
                <strong>Date:</strong> {{ ($quotation->quotation_date ?? $quotation->created_at ?? now())->format('d.m.Y') }}<br>
                <strong>Valid Until:</strong> {{ $quotation->valid_until ? $quotation->valid_until->format('d.m.Y') : 'N/A' }}<br>
                <strong>Supplier:</strong> NP Solutions
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th class="center" style="width: 8%">S.No</th>
                <th style="width: 50%">Description of Material / Items</th>
                <th class="center" style="width: 10%">Qty</th>
                <th class="right" style="width: 15%">Unit Price</th>
                <th class="right" style="width: 17%">Amount Rs.P.</th>
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
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">₹{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">₹{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td rowspan="3" style="width: 60%; vertical-align: top; font-size: 9px;">
                <strong>Rupees in words:</strong><br>
                <strong>{{ ucwords(str_replace('-', ' ', \App\Models\Quotation::numberToWords($quotation->total))) }} Only</strong>
                
                @if($quotation->notes)
                    <div style="margin-top: 15px; font-weight: normal; color: #555;">
                        <strong>Note:</strong><br>
                        {{ $quotation->notes }}
                    </div>
                @endif
            </td>
            <td style="width: 20%; font-weight: bold; text-align: right;">Total Amount:</td>
            <td style="width: 20%; text-align: right; font-weight: bold;">₹{{ number_format($quotation->subtotal - $quotation->discount, 2) }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; text-align: right;">GST @ {{ $quotation->tax_percent }}%:</td>
            <td style="text-align: right; font-weight: bold;">₹{{ number_format($quotation->tax_amount, 2) }}</td>
        </tr>
        <tr style="background-color: #f8fafc; font-size: 11px;">
            <td style="font-weight: bold; text-align: right; border-top: 2px solid #000;">Net Amount:</td>
            <td style="text-align: right; font-weight: bold; border-top: 2px solid #000; color: #1e293b;">₹{{ number_format($quotation->total, 2) }}</td>
        </tr>
    </table>

    <div class="terms-box">
        <strong>Terms & Conditions:</strong><br>
        1. All Supplies/Delivery to our Premises & Subject to the approval & Quality Confirmation.<br>
        2. Invoice should quote our Quotation No. & Date.<br>
        3. Delivery should be immediate or within scheduled days.<br>
        4. Guarantee/Warranty should be as per the agreed terms.<br>
        5. All Disputes are subject to Chennai Jurisdiction.
    </div>

    <table class="sign-table">
        <tr>
            <td style="width: 50%;">
                <br><br>
                <div style="border-top: 1px solid #000; display: inline-block; width: 180px; padding-top: 4px;">
                    SECRETARY / PRESIDENT
                </div>
            </td>
            <td style="width: 50%;">
                <br><br>
                <div style="border-top: 1px solid #000; display: inline-block; width: 180px; padding-top: 4px;">
                    Signature of the Authorised Person
                </div>
            </td>
        </tr>
    </table>

</div>

</body>
</html>
