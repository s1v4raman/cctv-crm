<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->quotation_no }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-logo {
            font-size: 24px;
            font-weight: bold;
            color: #0b1a5c;
            vertical-align: top;
        }
        .header-logo span {
            color: #c02428;
        }
        .header-details {
            text-align: right;
            font-size: 10px;
            color: #333;
            vertical-align: top;
            line-height: 1.3;
        }
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .meta-table td {
            vertical-align: top;
        }
        .recipient-box {
            margin-bottom: 15px;
        }
        .recipient-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 2px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #000;
        }
        .items-table th {
            background-color: #0b1a5c;
            color: #fff;
            font-weight: bold;
            text-align: left;
            padding: 5px;
            border: 1px solid #000;
            font-size: 9px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 6px 5px;
            border: 1px solid #000;
            font-size: 10px;
            vertical-align: top;
        }
        .items-table th.right, .items-table td.right {
            text-align: right;
        }
        .items-table th.center, .items-table td.center {
            text-align: center;
        }
        .totals-row td {
            font-weight: bold;
            border: 1px solid #000;
        }
        .totals-label {
            text-align: right;
            padding-right: 10px;
        }
        .terms-section {
            margin-top: 25px;
            font-size: 10px;
        }
        .terms-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }
        .terms-list {
            margin: 0;
            padding: 0 0 0 15px;
        }
        .terms-list li {
            margin-bottom: 3px;
        }
        .footer-section {
            margin-top: 35px;
            width: 100%;
        }
        .footer-left {
            width: 50%;
            float: left;
        }
        .footer-right {
            width: 50%;
            float: right;
            text-align: right;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="header-logo">
                PRECISI<span>O</span>N<br>
                <span style="font-size: 12px; color: #333; letter-spacing: 2px; font-weight: normal; display: block; margin-top: -5px;">IT SYSTEMS</span>
            </td>
            <td class="header-details">
                <strong>Plot No.553, Lig-1, 27th Street, Tamil Nadu Housing Board,</strong><br>
                Avadi, Chennai-600054.<br>
                Contact No: 8939883299,<br>
                Email: precisionitsystem@gmail.com<br>
                GSTIN: 33AHLPI3531N1Z8
            </td>
        </tr>
    </table>

    <div class="title">Quotation</div>

    <table class="meta-table">
        <tr>
            <td style="font-weight: bold;">Reference No: {{ $quotation->quotation_no }}</td>
            <td style="text-align: right; font-weight: bold;">Date: {{ ($quotation->quotation_date ?? $quotation->created_at ?? now())->format('d-M-y') }}</td>
        </tr>
    </table>

    <div class="recipient-box">
        <div class="recipient-title">To</div>
        <strong>{{ $quotation->lead->customer_name }}</strong><br>
        @if($quotation->lead->company_name)
            {{ $quotation->lead->company_name }}<br>
        @endif
        @if($quotation->lead->site_address)
            {!! nl2br(e($quotation->lead->site_address)) !!}
        @else
            Chennai, Tamil Nadu
        @endif
    </div>

    <p>Dear sir,</p>
    <p>with reference to your RFQ, please find our competitive offer below. We look forward to your order.</p>
    
    <p style="font-weight: bold; margin-bottom: 5px;">Network / CCTV Surveillance Solutions</p>

    <table class="items-table">
        <thead>
            <tr>
                <th class="center" style="width: 5%">No</th>
                <th style="width: 50%">Item Description</th>
                <th class="center" style="width: 8%">QTY</th>
                <th class="right" style="width: 12%">Quoted Rate Excl of Tax (INR)</th>
                <th class="right" style="width: 12%">Discount Rate Excl of Tax (INR)</th>
                <th class="right" style="width: 13%">Total Price Excl of Tax (INR)</th>
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
                            <div style="font-size: 8px; color: #555; margin-top: 2px;">{!! nl2br(e($item->description)) !!}</div>
                        @endif
                    </td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">₹{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">₹{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">₹{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
            
            <tr class="totals-row">
                <td colspan="4" style="border: none;"></td>
                <td class="totals-label">Sub Total</td>
                <td class="right">₹{{ number_format($quotation->subtotal, 2) }}</td>
            </tr>
            @if($quotation->discount > 0)
                <tr class="totals-row">
                    <td colspan="4" style="border: none;"></td>
                    <td class="totals-label" style="color: #c02428">Discount</td>
                    <td class="right" style="color: #c02428">−₹{{ number_format($quotation->discount, 2) }}</td>
                </tr>
            @endif
            <tr class="totals-row">
                <td colspan="4" style="border: none;"></td>
                <td class="totals-label">GST Tax {{ $quotation->tax_percent }}%</td>
                <td class="right">₹{{ number_format($quotation->tax_amount, 2) }}</td>
            </tr>
            <tr class="totals-row" style="background-color: #0b1a5c; color: #fff;">
                <td colspan="4" style="border: none; background-color: #fff;"></td>
                <td class="totals-label" style="border: 1px solid #000;">Total</td>
                <td class="right" style="border: 1px solid #000;">₹{{ number_format($quotation->total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="terms-section">
        <div class="terms-title">Terms & Condition</div>
        <ul class="terms-list">
            <li><strong>Taxes:</strong> As mentioned above Our GSTIN: 33AHLPI3531N1Z8</li>
            <li><strong>Delivery:</strong> 20 Days after Receiving the Advance Payment & PO</li>
            <li><strong>Payment:</strong> 50% in Advance along with PO.</li>
            <li><strong>Validity:</strong> Price validity is 2 Days</li>
            <li><strong>Warranty:</strong> One Year</li>
        </ul>
    </div>

    <div class="footer-section">
        <div class="footer-left">
            Thanking you.<br><br>
            <strong>For Precision IT Systems</strong><br>
            ph.: +91-9677257774<br>
            email: precisionitsystem@gmail.com
        </div>
    </div>

</body>
</html>
