<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendor Accounts Payable & 3-Way Matching Report</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5px;
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
            width: 16.66%;
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
        .kpi-val.emerald { color: #059669; }
        .kpi-val.amber { color: #d97706; }
        .kpi-val.orange { color: #ea580c; }
        .kpi-val.rose { color: #dc2626; }
        .kpi-val.blue { color: #2563eb; }
        .kpi-sub {
            font-size: 6.5px;
            color: #64748b;
            margin-top: 1px;
        }
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
            padding: 4px 6px;
            text-align: left;
            border: 1px solid #0f172a;
        }
        .table th.text-right { text-align: right; }
        .table th.text-center { text-align: center; }
        .table td {
            padding: 4px 6px;
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
        .badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 6.5px;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-matched { background-color: #d1fae5; color: #065f46; }
        .badge-partial { background-color: #dbeafe; color: #1e40af; }
        .badge-unbilled { background-color: #fef3c7; color: #92400e; }
        .badge-pending { background-color: #f1f5f9; color: #475569; }
        .badge-mismatch { background-color: #fee2e2; color: #991b1b; }
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
                <div class="header-title">Vendor Accounts Payable & 3-Way Match Report</div>
                <div class="header-sub">Procurement Payables Aging Analysis & 3-Way Matching Verification</div>
            </td>
            <td class="meta-box" style="vertical-align: top;">
                <div><strong>Generated:</strong> {{ $generatedAt ?? now()->format('d M Y, h:i A') }}</div>
                <div><strong>Days Payable Outstanding (DPO):</strong> {{ $summary['dpo_days'] }} Days</div>
                <div><strong>3-Way Match Pass Rate:</strong> {{ $summary['three_way_match_pass_rate'] }}%</div>
            </td>
        </tr>
    </table>

    <!-- AP Summary Cards -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-box">
                <span class="kpi-label">Total Payables</span>
                <span class="kpi-val rose">₹{{ number_format($summary['total_payable_balance'], 2) }}</span>
                <div class="kpi-sub">{{ $summary['unpaid_pos_count'] }} Open POs ({{ $summary['suppliers_count'] }} Vendors)</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">0–30 Days (Current)</span>
                <span class="kpi-val blue">₹{{ number_format($summary['current_0_30'], 2) }}</span>
                <div class="kpi-sub">{{ $summary['total_payable_balance'] > 0 ? round(($summary['current_0_30'] / $summary['total_payable_balance']) * 100, 1) : 0 }}% of AP</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">31–60 Days</span>
                <span class="kpi-val amber">₹{{ number_format($summary['overdue_31_60'], 2) }}</span>
                <div class="kpi-sub">{{ $summary['total_payable_balance'] > 0 ? round(($summary['overdue_31_60'] / $summary['total_payable_balance']) * 100, 1) : 0 }}% of AP</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">61–90 Days</span>
                <span class="kpi-val orange">₹{{ number_format($summary['critical_61_90'], 2) }}</span>
                <div class="kpi-sub">{{ $summary['total_payable_balance'] > 0 ? round(($summary['critical_61_90'] / $summary['total_payable_balance']) * 100, 1) : 0 }}% of AP</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">90+ Days (Critical)</span>
                <span class="kpi-val rose">₹{{ number_format($summary['high_risk_90_plus'], 2) }}</span>
                <div class="kpi-sub">{{ $summary['total_payable_balance'] > 0 ? round(($summary['high_risk_90_plus'] / $summary['total_payable_balance']) * 100, 1) : 0 }}% of AP</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">DPO Velocity</span>
                <span class="kpi-val">{{ $summary['dpo_days'] }} <span style="font-size: 8px;">Days</span></span>
                <div class="kpi-sub">{{ $summary['three_way_match_pass_rate'] }}% 3-Way Match</div>
            </td>
        </tr>
    </table>

    <!-- Supplier AP Aging Matrix Table -->
    <table class="table">
        <thead>
            <tr>
                <th style="width: 25%;">Supplier / Company</th>
                <th style="width: 15%;">Contact / GSTIN</th>
                <th class="text-center" style="width: 8%;">Open POs</th>
                <th class="text-right" style="width: 12%;">0–30 Days</th>
                <th class="text-right" style="width: 12%;">31–60 Days</th>
                <th class="text-right" style="width: 12%;">61–90 Days</th>
                <th class="text-right" style="width: 12%;">90+ Days</th>
                <th class="text-right" style="width: 14%;">Net Payable</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suppliers as $s)
                <tr>
                    <td><strong>{{ $s['supplier_name'] }}</strong></td>
                    <td>{{ $s['phone'] }} ({{ $s['gst_number'] }})</td>
                    <td class="text-center">{{ $s['unpaid_po_count'] }}</td>
                    <td class="text-right">{{ $s['days_0_30'] > 0 ? '₹' . number_format($s['days_0_30'], 2) : '-' }}</td>
                    <td class="text-right">{{ $s['days_31_60'] > 0 ? '₹' . number_format($s['days_31_60'], 2) : '-' }}</td>
                    <td class="text-right">{{ $s['days_61_90'] > 0 ? '₹' . number_format($s['days_61_90'], 2) : '-' }}</td>
                    <td class="text-right" style="{{ $s['days_90_plus'] > 0 ? 'color: #dc2626; font-weight: bold;' : '' }}">
                        {{ $s['days_90_plus'] > 0 ? '₹' . number_format($s['days_90_plus'], 2) : '-' }}
                    </td>
                    <td class="text-right" style="font-weight: bold;">₹{{ number_format($s['total_due'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px; color: #64748b;">
                        No outstanding supplier accounts payable found.
                    </td>
                </tr>
            @endforelse

            @if(count($suppliers) > 0)
                <tr class="table-total">
                    <td colspan="3" style="text-align: right; font-weight: bold;">TOTAL ACCOUNTS PAYABLE:</td>
                    <td class="text-right" style="font-weight: bold; color: #2563eb;">₹{{ number_format($summary['current_0_30'], 2) }}</td>
                    <td class="text-right" style="font-weight: bold; color: #d97706;">₹{{ number_format($summary['overdue_31_60'], 2) }}</td>
                    <td class="text-right" style="font-weight: bold; color: #ea580c;">₹{{ number_format($summary['critical_61_90'], 2) }}</td>
                    <td class="text-right" style="font-weight: bold; color: #dc2626;">₹{{ number_format($summary['high_risk_90_plus'], 2) }}</td>
                    <td class="text-right" style="font-weight: bold; font-size: 9px;">₹{{ number_format($summary['total_payable_balance'], 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Confidential Document — Generated automatically by CCTV CRM Vendor Accounts Payable & 3-Way Matching Engine
    </div>

</body>
</html>
