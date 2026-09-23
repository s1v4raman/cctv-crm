<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statement of Account - {{ $lead->company_name ?: $lead->name }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5px;
            line-height: 1.35;
            color: #1e293b;
            background: #ffffff;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-title {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-sub {
            font-size: 8px;
            color: #475569;
            margin-top: 2px;
        }
        .meta-box {
            font-size: 8px;
            text-align: right;
            color: #334155;
        }
        .info-table {
            width: 100%;
            margin-bottom: 14px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
        }
        .info-td {
            padding: 8px 10px;
            vertical-align: top;
            width: 50%;
            font-size: 8px;
        }
        .info-title {
            font-weight: bold;
            font-size: 7.5px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 3px;
        }
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .kpi-box {
            width: 33.33%;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            background-color: #f8fafc;
            vertical-align: top;
        }
        .kpi-label {
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            display: block;
            margin-bottom: 2px;
        }
        .kpi-val {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
        }
        .kpi-val.emerald { color: #059669; }
        .kpi-val.rose { color: #dc2626; }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            font-size: 7.5px;
            text-transform: uppercase;
            padding: 5px 6px;
            text-align: left;
            border: 1px solid #0f172a;
        }
        .table th.text-right { text-align: right; }
        .table th.text-center { text-align: center; }
        .table td {
            padding: 5px 6px;
            border: 1px solid #e2e8f0;
            font-size: 8px;
        }
        .table td.text-right { text-align: right; }
        .table td.text-center { text-align: center; }
        .table tr:nth-child(even) { background-color: #f8fafc; }
        .table-total {
            background-color: #f1f5f9;
            font-weight: bold;
        }
        .payment-box {
            margin-top: 10px;
            padding: 8px 10px;
            background-color: #f1f5f9;
            border: 1px dashed #94a3b8;
            font-size: 7.5px;
            color: #334155;
        }
        .footer {
            margin-top: 15px;
            border-top: 1px solid #cbd5e1;
            padding-top: 6px;
            font-size: 7px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <div class="header-title">Statement of Account</div>
                <div class="header-sub">Chronological Invoicing & Payment History</div>
            </td>
            <td class="meta-box" style="vertical-align: top;">
                <div><strong>Statement Date:</strong> {{ $generatedAt ?? now()->format('d M Y, h:i A') }}</div>
                <div><strong>Account ID:</strong> #CUST-{{ str_pad($lead->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div><strong>GSTIN:</strong> {{ $lead->gstin ?: 'N/A' }}</div>
            </td>
        </tr>
    </table>

    <!-- Customer & Company Meta Info -->
    <table class="info-table">
        <tr>
            <td class="info-td">
                <div class="info-title">Statement Issued To</div>
                <div style="font-size: 10px; font-weight: bold; color: #0f172a;">{{ $lead->company_legal_name ?: $lead->customer_name }}</div>
                @if($lead->company_legal_name && $lead->customer_name)
                    <div><strong>Attention:</strong> {{ $lead->customer_name }}</div>
                @endif
                <div><strong>Phone:</strong> {{ $lead->phone ?: 'N/A' }}</div>
                <div><strong>Email:</strong> {{ $lead->email ?: 'N/A' }}</div>
                <div><strong>Billing Address:</strong> {{ $lead->site_address ?: 'N/A' }}</div>
            </td>
            <td class="info-td" style="border-left: 1px solid #cbd5e1;">
                <div class="info-title">Account Summary</div>
                <div><strong>Total Invoiced:</strong> ₹{{ number_format($ledger['total_billed'], 2) }}</div>
                <div><strong>Total Receipts Received:</strong> ₹{{ number_format($ledger['total_paid'], 2) }}</div>
                <div style="margin-top: 4px; font-size: 9px;">
                    <strong>Net Balance Due:</strong> 
                    <span style="font-weight: bold; color: {{ $ledger['outstanding_balance'] > 0 ? '#dc2626' : '#059669' }};">
                        ₹{{ number_format($ledger['outstanding_balance'], 2) }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Summary KPI Cards -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-box">
                <span class="kpi-label">Lifetime Invoiced</span>
                <span class="kpi-val">₹{{ number_format($ledger['total_billed'], 2) }}</span>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">Total Paid (Credits)</span>
                <span class="kpi-val emerald">₹{{ number_format($ledger['total_paid'], 2) }}</span>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">Current Outstanding Balance</span>
                <span class="kpi-val {{ $ledger['outstanding_balance'] > 0 ? 'rose' : 'emerald' }}">
                    ₹{{ number_format($ledger['outstanding_balance'], 2) }}
                </span>
            </td>
        </tr>
    </table>

    <!-- Transaction Ledger -->
    <table class="table">
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 10%;">Type</th>
                <th style="width: 18%;">Reference #</th>
                <th style="width: 24%;">Description</th>
                <th class="text-right" style="width: 12%;">Debit (+)</th>
                <th class="text-right" style="width: 12%;">Credit (-)</th>
                <th class="text-right" style="width: 12%;">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ledger['transactions'] as $tx)
                <tr>
                    <td>{{ $tx['date']->format('d M Y') }}</td>
                    <td class="text-center">
                        <strong>{{ strtoupper($tx['type']) }}</strong>
                    </td>
                    <td><strong>{{ $tx['reference'] }}</strong></td>
                    <td>{{ $tx['description'] }}</td>
                    <td class="text-right">{{ $tx['debit'] > 0 ? '₹' . number_format($tx['debit'], 2) : '-' }}</td>
                    <td class="text-right" style="color: #059669;">{{ $tx['credit'] > 0 ? '₹' . number_format($tx['credit'], 2) : '-' }}</td>
                    <td class="text-right" style="font-weight: bold; {{ $tx['balance'] > 0 ? 'color: #dc2626;' : '' }}">
                        ₹{{ number_format($tx['balance'], 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #64748b;">
                        No transactions recorded for this account.
                    </td>
                </tr>
            @endforelse

            <tr class="table-total">
                <td colspan="4" style="text-align: right; font-weight: bold;">CLOSING BALANCE DUE:</td>
                <td class="text-right" style="font-weight: bold;">₹{{ number_format($ledger['total_billed'], 2) }}</td>
                <td class="text-right" style="font-weight: bold; color: #059669;">₹{{ number_format($ledger['total_paid'], 2) }}</td>
                <td class="text-right" style="font-weight: bold; font-size: 9px; {{ $ledger['outstanding_balance'] > 0 ? 'color: #dc2626;' : 'color: #059669;' }}">
                    ₹{{ number_format($ledger['outstanding_balance'], 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Payment Terms & Bank Remittance Instructions -->
    <div class="payment-box">
        <strong>Remittance / Settlement Notice:</strong><br>
        Please remit any outstanding balance via NEFT / RTGS / IMPS or UPI. When making a bank transfer, kindly cite your reference / invoice number in the payment remarks. Direct online checkout with Instant Receipt is also available via the client payment portal.
    </div>

    <div class="footer">
        This is a computer-generated Statement of Account and does not require a physical signature.
    </div>

</body>
</html>
