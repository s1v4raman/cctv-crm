<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt - {{ $payment->receipt_no ?? 'REC-' . $payment->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.5;
            background: #ffffff;
            padding: 30px;
        }
        .header-table { width: 100%; margin-bottom: 25px; border-bottom: 2px solid #4f46e5; padding-bottom: 15px; }
        .company-name { font-size: 20px; font-weight: 800; color: #1e1b4b; text-transform: uppercase; letter-spacing: 0.5px; }
        .company-sub { font-size: 10px; color: #64748b; margin-top: 3px; }
        .receipt-badge {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            font-size: 12px;
            font-weight: 800;
            padding: 6px 14px;
            border-radius: 6px;
            display: inline-block;
            text-transform: uppercase;
        }
        .receipt-title { font-size: 18px; font-weight: 800; color: #0f172a; margin-top: 5px; }

        .meta-grid { width: 100%; margin-bottom: 20px; }
        .meta-col { width: 50%; vertical-align: top; }
        .box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 15px;
        }
        .box-title { font-size: 9px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .box-content strong { color: #0f172a; font-size: 12px; }

        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table th {
            background: #1e1b4b;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 8px 12px;
            text-align: left;
        }
        .details-table th.right, .details-table td.right { text-align: right; }
        .details-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11px;
            color: #334155;
        }

        .amount-highlight {
            background: #eef2ff;
            border: 1.5px solid #c7d2fe;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 25px;
        }
        .amount-highlight .label { font-size: 10px; font-weight: 700; color: #4338ca; text-transform: uppercase; }
        .amount-highlight .val { font-size: 24px; font-weight: 900; color: #312e81; margin-top: 2px; }
        .amount-words { font-size: 10px; font-style: italic; color: #475569; margin-top: 4px; }

        .footer-table { width: 100%; margin-top: 30px; }
        .stamp-box {
            border: 1.5px dashed #cbd5e1;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            width: 220px;
            float: right;
            background: #fdfefe;
        }
        .stamp-title { font-size: 9px; font-weight: 700; color: #64748b; text-transform: uppercase; }
        .stamp-verified { color: #059669; font-weight: 800; font-size: 12px; margin: 6px 0; }
        .note { font-size: 9px; color: #94a3b8; line-height: 1.4; }
    </style>
</head>
<body>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="company-name">{{ config('app.name', 'Precision IT Systems') }}</div>
                <div class="company-sub">
                    Plot No.553, Lig-1, 27th Street, Tamil Nadu Housing Board, Avadi, Chennai-600054.<br>
                    GSTIN: 33AHLPI3531N1Z8 · Support: +91 96772 57774 · Email: precisionitsystem@gmail.com
                </div>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: top;">
                <div class="receipt-badge">✓ Payment Confirmed</div>
                <div class="receipt-title">RECEIPT #{{ $payment->receipt_no ?? 'REC-' . str_pad((string)$payment->id, 4, '0', STR_PAD_LEFT) }}</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                    Date: {{ $payment->paid_on ? $payment->paid_on->format('d M Y') : now()->format('d M Y') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Meta Grid (Customer & Transaction info) --}}
    <table class="meta-grid">
        <tr>
            <td class="meta-col" style="padding-right: 10px;">
                <div class="box">
                    <div class="box-title">Received From (Customer)</div>
                    <div class="box-content">
                        <strong>{{ $customerName }}</strong><br>
                        @if(!empty($customerPhone)) Phone: {{ $customerPhone }}<br> @endif
                        @if(!empty($customerEmail)) Email: {{ $customerEmail }}<br> @endif
                        @if(!empty($customerAddress)) Site: {{ $customerAddress }} @endif
                    </div>
                </div>
            </td>
            <td class="meta-col" style="padding-left: 10px;">
                <div class="box">
                    <div class="box-title">Payment & Gateway Details</div>
                    <div class="box-content">
                        <strong>Method:</strong> {{ $payment->formatted_method }}<br>
                        @if($payment->reference_no) <strong>Ref / Txn ID:</strong> {{ $payment->reference_no }}<br> @endif
                        @if($payment->gateway_payment_id) <strong>Razorpay Pay ID:</strong> {{ $payment->gateway_payment_id }}<br> @endif
                        @if($invoice) <strong>Tax Invoice #:</strong> {{ $invoice->invoice_no }}<br> @endif
                        @if($quotation) <strong>Quotation #:</strong> {{ $quotation->quotation_no }} @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Amount Paid Highlight Card --}}
    <div class="amount-highlight">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="label">Amount Received & Acknowledged</div>
                    <div class="val">₹{{ number_format((float)$payment->amount, 2) }}</div>
                    <div class="amount-words">
                        In Words: <em>INR {{ \App\Models\Quotation::numberToWords((float)$payment->amount) }} Only</em>
                    </div>
                </td>
                @if($invoice)
                <td style="text-align: right; vertical-align: middle;">
                    <div style="font-size: 10px; color: #475569;">Invoice Total: ₹{{ number_format((float)$invoice->total, 2) }}</div>
                    <div style="font-size: 10px; color: #059669; font-weight: 700;">Total Paid: ₹{{ number_format((float)$invoice->amount_paid, 2) }}</div>
                    <div style="font-size: 11px; color: {{ $invoice->balanceDue() > 0 ? '#b91c1c' : '#059669' }}; font-weight: 800; margin-top: 3px;">
                        Remaining Balance: ₹{{ number_format((float)$invoice->balanceDue(), 2) }}
                    </div>
                </td>
                @endif
            </tr>
        </table>
    </div>

    {{-- Line Items Summary Table (If linked to invoice/quotation) --}}
    @if(!empty($items) && count($items) > 0)
    <table class="details-table">
        <thead>
            <tr>
                <th style="width: 50%;">Product / Service Description</th>
                <th class="right" style="width: 15%;">Qty</th>
                <th class="right" style="width: 15%;">Unit Price</th>
                <th class="right" style="width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>
                    <strong>{{ $item->item_name ?? ($item->product?->name ?? 'Hardware Asset') }}</strong>
                    @if(!empty($item->description))
                        <div style="font-size: 9.5px; color: #64748b;">{{ $item->description }}</div>
                    @endif
                </td>
                <td class="right">{{ $item->quantity }}</td>
                <td class="right">₹{{ number_format((float)$item->unit_price, 2) }}</td>
                <td class="right">₹{{ number_format((float)$item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Footer with Digital Authorization Stamp --}}
    <table class="footer-table">
        <tr>
            <td style="width: 60%; vertical-align: bottom;">
                <p class="note">
                    * This is a computer-generated digital tax receipt and does not require a physical signature.<br>
                    * All payments are subject to electronic verification and bank clearance.<br>
                    * For billing questions or invoice copies, contact accounts@precisionit.com.
                </p>
            </td>
            <td style="width: 40%; vertical-align: top;">
                <div class="stamp-box">
                    <div class="stamp-title">Precision IT Systems</div>
                    <div class="stamp-verified">★ DIGITALLY CONFIRMED ★</div>
                    <div style="font-size: 9px; color: #64748b;">
                        Recorded On: {{ $payment->created_at ? $payment->created_at->format('d M Y, h:i A') : now()->format('d M Y') }}<br>
                        Authorized Billing Signatory
                    </div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
