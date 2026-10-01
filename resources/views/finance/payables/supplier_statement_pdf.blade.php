<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Statement of Account - {{ $supplier->company_name ?? $supplier->name }}</title>
    <style>
        @page {
            margin: 20px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 10px;
            font-size: 10px;
            line-height: 1.4;
        }
        .header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .company-title {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .statement-badge {
            float: right;
            text-align: right;
        }
        .statement-title {
            font-size: 14px;
            font-weight: bold;
            color: #4338ca;
            letter-spacing: 0.5px;
        }
        .meta-text {
            font-size: 9px;
            color: #64748b;
        }
        .two-column {
            width: 100%;
            margin-bottom: 16px;
        }
        .two-column td {
            vertical-align: top;
        }
        .info-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px;
        }
        .info-box h3 {
            margin: 0 0 6px 0;
            font-size: 11px;
            color: #334155;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
        }
        .summary-cards {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: separate;
            border-spacing: 6px 0;
        }
        .card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 10px;
            text-align: center;
        }
        .card-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .card-value {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }
        .card-value.highlight {
            color: #b91c1c;
        }
        .card-value.success {
            color: #15803d;
        }
        table.ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        table.ledger-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 8.5px;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #0f172a;
        }
        table.ledger-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
        }
        table.ledger-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-po { background: #e0e7ff; color: #3730a3; }
        .badge-payment { background: #dcfce7; color: #166534; }
        .footer {
            margin-top: 24px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 12px;
            font-size: 8px;
            color: #64748b;
            text-align: center;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
        }
        .signature-table td {
            width: 50%;
            vertical-align: bottom;
            padding: 0 20px;
        }
        .sig-line {
            border-top: 1px solid #475569;
            padding-top: 4px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            color: #334155;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="statement-badge">
            <div class="statement-title">SUPPLIER STATEMENT OF ACCOUNT</div>
            <div class="meta-text">Generated On: {{ is_string($generatedAt) ? $generatedAt : $generatedAt->format('d M Y, h:i A') }}</div>
            <div class="meta-text">Account Ref: SUP-{{ str_pad($supplier->id, 5, '0', STR_PAD_LEFT) }}</div>
        </div>
        <div class="company-title">Precision IT Systems</div>
        <div class="meta-text">GSTIN: 33AHLPI3531N1Z8 | Avadi, Chennai-600054</div>
        <div class="meta-text">Accounts Payable Department | precisionitsystem@gmail.com | +91-9677257774</div>
    </div>

    <table class="two-column">
        <tr>
            <td style="width: 50%; padding-right: 8px;">
                <div class="info-box">
                    <h3>Vendor Details</h3>
                    <div style="font-size: 11px; font-weight: bold; color: #0f172a;">{{ $supplier->company_name ?? $supplier->name }}</div>
                    @if($supplier->contact_person && $supplier->contact_person !== $supplier->company_name)
                        <div><strong style="color: #64748b;">Contact:</strong> {{ $supplier->contact_person }}</div>
                    @endif
                    @if($supplier->phone)
                        <div><strong style="color: #64748b;">Phone:</strong> {{ $supplier->phone }}</div>
                    @endif
                    @if($supplier->email)
                        <div><strong style="color: #64748b;">Email:</strong> {{ $supplier->email }}</div>
                    @endif
                    @if($supplier->gstin)
                        <div><strong style="color: #64748b;">GSTIN:</strong> {{ $supplier->gstin }}</div>
                    @endif
                    @if($supplier->address)
                        <div><strong style="color: #64748b;">Address:</strong> {{ $supplier->address }}</div>
                    @endif
                </div>
            </td>
            <td style="width: 50%; padding-left: 8px;">
                <div class="info-box">
                    <h3>Account & Remittance Terms</h3>
                    <div><strong style="color: #64748b;">Credit Terms:</strong> Net {{ $supplier->credit_period_days ?? 30 }} Days</div>
                    <div><strong style="color: #64748b;">Payment Mode:</strong> Bank Transfer / NEFT / RTGS</div>
                    @if($supplier->bank_name)
                        <div><strong style="color: #64748b;">Bank:</strong> {{ $supplier->bank_name }}</div>
                    @endif
                    @if($supplier->account_number)
                        <div><strong style="color: #64748b;">A/C No:</strong> {{ $supplier->account_number }}</div>
                    @endif
                    @if($supplier->ifsc_code)
                        <div><strong style="color: #64748b;">IFSC:</strong> {{ $supplier->ifsc_code }}</div>
                    @endif
                    <div style="margin-top: 4px; color: #4338ca; font-weight: 500;">
                        Please verify all listed purchase bills and disbursements.
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table class="summary-cards">
        <tr>
            <td class="card">
                <div class="card-label">Total Invoiced (Bills/POs)</div>
                <div class="card-value font-mono">&#8377;{{ number_format($ledger['total_billed'] ?? 0, 2) }}</div>
            </td>
            <td class="card">
                <div class="card-label">Total Disbursed (Paid)</div>
                <div class="card-value success font-mono">&#8377;{{ number_format($ledger['total_paid'] ?? 0, 2) }}</div>
            </td>
            <td class="card" style="border-color: #fca5a5; background: #fff5f5;">
                <div class="card-label" style="color: #991b1b;">Net Balance Payable</div>
                <div class="card-value highlight font-mono">&#8377;{{ number_format($ledger['net_balance'] ?? 0, 2) }}</div>
            </td>
        </tr>
    </table>

    <div style="font-weight: bold; font-size: 10px; color: #0f172a; margin-bottom: 6px; text-transform: uppercase;">
        Statement Ledger Transactions
    </div>

    <table class="ledger-table">
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 10%;">Type</th>
                <th style="width: 18%;">Reference #</th>
                <th style="width: 24%;">Description / Bill No</th>
                <th style="width: 12%;" class="text-right">Billed</th>
                <th style="width: 12%;" class="text-right">Paid</th>
                <th style="width: 12%;" class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ledger['transactions'] ?? [] as $tx)
                <tr>
                    <td class="font-mono">{{ \Carbon\Carbon::parse($tx['date'])->format('d M Y') }}</td>
                    <td>
                        @if($tx['type'] === 'purchase_order')
                            <span class="badge badge-po">Bill / PO</span>
                        @else
                            <span class="badge badge-payment">Payment</span>
                        @endif
                    </td>
                    <td class="font-mono font-bold">{{ $tx['reference'] }}</td>
                    <td>
                        {{ $tx['description'] ?? $tx['details'] ?? '' }}
                        @if(!empty($tx['sub_text']))
                            <div style="font-size: 8px; color: #64748b;">{{ $tx['sub_text'] }}</div>
                        @endif
                    </td>
                    <td class="text-right font-mono font-bold" style="color: #0f172a;">
                        {{ $tx['type'] === 'purchase_order' ? '₹' . number_format($tx['credit'], 2) : '-' }}
                    </td>
                    <td class="text-right font-mono font-bold" style="color: #15803d;">
                        {{ $tx['type'] === 'payment' ? '₹' . number_format($tx['debit'], 2) : '-' }}
                    </td>
                    <td class="text-right font-mono font-bold" style="color: {{ ($tx['running_balance'] ?? $tx['balance']) > 0 ? '#b91c1c' : '#1e293b' }};">
                        &#8377;{{ number_format($tx['running_balance'] ?? $tx['balance'], 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #64748b;">
                        No transactions found for this supplier account.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="4" class="text-right" style="padding: 8px; border-top: 2px solid #0f172a;">Closing Balance:</td>
                <td class="text-right font-mono" style="padding: 8px; border-top: 2px solid #0f172a;">&#8377;{{ number_format($ledger['total_billed'] ?? 0, 2) }}</td>
                <td class="text-right font-mono" style="padding: 8px; border-top: 2px solid #0f172a; color: #15803d;">&#8377;{{ number_format($ledger['total_paid'] ?? 0, 2) }}</td>
                <td class="text-right font-mono" style="padding: 8px; border-top: 2px solid #0f172a; color: #b91c1c;">&#8377;{{ number_format($ledger['net_balance'] ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                <div class="sig-line">Prepared & Verified By (Accounts)</div>
            </td>
            <td>
                <div class="sig-line">Supplier Acknowledgment & Stamp</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        This is a computer-generated Statement of Account. If you notice any discrepancy in PO invoices or received disbursements, please reach out to accounts@apexsecurity.in within 7 working days.
    </div>

</body>
</html>
