<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Petty Cash & Daily Field Cashbook Report</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8px;
            line-height: 1.3;
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
            font-size: 14px;
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
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .kpi-box {
            width: 25%;
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
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
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
        }
        .section-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin: 10px 0 4px 0;
            padding-bottom: 2px;
            border-bottom: 1px solid #e2e8f0;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        table.data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7px;
            padding: 4px 5px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        table.data-table td {
            padding: 3.5px 5px;
            border: 1px solid #e2e8f0;
            font-size: 7.5px;
            vertical-align: middle;
        }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 6.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-vault { background: #e0e7ff; color: #3730a3; }
        .badge-tech { background: #dbeafe; color: #1e40af; }
        .badge-advance { background: #dbeafe; color: #1e40af; }
        .badge-collection { background: #d1fae5; color: #065f46; }
        .badge-expense { background: #ffe4e6; color: #9f1239; }
        .badge-handover { background: #f3e8ff; color: #6b21a8; }
        .badge-matched { background: #d1fae5; color: #065f46; }
        .badge-shortage { background: #fee2e2; color: #991b1b; }
        .badge-excess { background: #fef3c7; color: #92400e; }
        .footer {
            margin-top: 15px;
            border-top: 1px solid #cbd5e1;
            padding-top: 5px;
            font-size: 7px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td>
                <div class="header-title">Petty Cash & Daily Field Cashbook Report</div>
                <div class="header-sub">Comprehensive Cash Float Management & Physical Reconciliation Statement</div>
            </td>
            <td class="meta-box">
                <div><strong>As of Date:</strong> {{ $summary['as_of_date'] ?? now()->format('d M Y') }}</div>
                <div><strong>Generated:</strong> {{ $generatedAt ?? now()->format('d M Y, h:i A') }}</div>
            </td>
        </tr>
    </table>

    <!-- KPI Summary Row -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-box">
                <span class="kpi-label">Total Liquid Cash</span>
                <span class="kpi-val">₹{{ number_format($summary['total_liquid_cash'], 2) }}</span>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">Main Safe Vault</span>
                <span class="kpi-val" style="color: #4338ca;">₹{{ number_format($summary['main_vault_balance'], 2) }}</span>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">Total Field Floats</span>
                <span class="kpi-val" style="color: #0284c7;">₹{{ number_format($summary['total_field_float'], 2) }}</span>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">High Float Risk Accounts</span>
                <span class="kpi-val" style="color: {{ $summary['high_risk_wallets_count'] > 0 ? '#b91c1c' : '#15803d' }};">
                    {{ $summary['high_risk_wallets_count'] }} Custodians
                </span>
            </td>
        </tr>
    </table>

    <!-- Custodian Accounts Position -->
    <div class="section-title">1. Custodian Cash Positions & Float Limits</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Account / Wallet</th>
                <th>Type</th>
                <th>Custodian / Holder</th>
                <th style="text-align: right;">Current Float Balance</th>
                <th style="text-align: right;">Warning Threshold</th>
                <th style="text-align: center;">Risk Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accounts as $acc)
                <tr>
                    <td><strong>{{ $acc['name'] }}</strong></td>
                    <td>
                        <span class="badge {{ $acc['account_type'] === 'main_vault' ? 'badge-vault' : 'badge-tech' }}">
                            {{ $acc['account_type'] === 'main_vault' ? 'Head Vault' : 'Field Wallet' }}
                        </span>
                    </td>
                    <td>{{ $acc['custodian_name'] }}</td>
                    <td style="text-align: right; font-weight: bold;">₹{{ number_format($acc['current_balance'], 2) }}</td>
                    <td style="text-align: right; color: #64748b;">₹{{ number_format($acc['warning_limit'], 2) }}</td>
                    <td style="text-align: center;">
                        @if($acc['is_high_risk'])
                            <span class="badge badge-shortage">Risk: High Float</span>
                        @else
                            <span class="badge badge-matched">Within Limit</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Transactions Log -->
    <div class="section-title">2. Cash Transactions Log ({{ count($transactions) }} Entries)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Voucher #</th>
                <th>Account</th>
                <th>Type / Category</th>
                <th>Particulars / Counterparty</th>
                <th style="text-align: right;">Amount (₹)</th>
                <th>Recorded By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $txn)
                <tr>
                    <td>{{ $txn->transaction_date ? $txn->transaction_date->format('d M Y') : '—' }}</td>
                    <td style="font-family: monospace; font-weight: bold;">{{ $txn->voucher_no }}</td>
                    <td>{{ $txn->account->name ?? 'Vault' }}</td>
                    <td>
                        <span class="badge badge-advance">{{ str_replace('_', ' ', $txn->transaction_type) }}</span>
                        <span style="font-size: 7px; color: #475569;">{{ str_replace('_', ' ', $txn->category) }}</span>
                    </td>
                    <td>
                        {{ $txn->notes ?? '—' }}
                        @if($txn->vendor_payee_name) <br><span style="color: #64748b;">Party: {{ $txn->vendor_payee_name }}</span> @endif
                    </td>
                    <td style="text-align: right; font-weight: bold;">₹{{ number_format($txn->amount, 2) }}</td>
                    <td>{{ $txn->user->name ?? 'Staff' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 10px;">No cash transactions in selected scope.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        CCTV CRM Petty Cash & Daily Float Reconciliation Module | Page Generated Automatically
    </div>

</body>
</html>
