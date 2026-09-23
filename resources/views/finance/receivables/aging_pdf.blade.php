<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Debtors Aging & Accounts Receivable Analysis</title>
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
        .kpi-val.indigo { color: #4338ca; }
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
                <div class="header-title">Debtors Aging & Receivables Analysis</div>
                <div class="header-sub">Accounts Receivable Executive Summary & Aging Intervals</div>
            </td>
            <td class="meta-box" style="vertical-align: top;">
                <div><strong>Generated:</strong> {{ $generatedAt ?? now()->format('d M Y, h:i A') }}</div>
                <div><strong>Report Scope:</strong> Active Outstanding Accounts</div>
                <div><strong>Collection Velocity (DSO):</strong> {{ $summary['dso_days'] }} Days</div>
            </td>
        </tr>
    </table>

    <!-- Aging Summary Cards -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-box">
                <span class="kpi-label">Total Receivables</span>
                <span class="kpi-val indigo">₹{{ number_format($summary['total_outstanding'], 2) }}</span>
                <div class="kpi-sub">{{ $summary['total_invoices_count'] }} Open Bills ({{ $summary['total_debtors_count'] }} Debtors)</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">0–30 Days (Current)</span>
                <span class="kpi-val emerald">₹{{ number_format($summary['current_0_30'], 2) }}</span>
                <div class="kpi-sub">{{ $summary['total_outstanding'] > 0 ? round(($summary['current_0_30'] / $summary['total_outstanding']) * 100, 1) : 0 }}% of Total</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">31–60 Days</span>
                <span class="kpi-val amber">₹{{ number_format($summary['overdue_31_60'], 2) }}</span>
                <div class="kpi-sub">{{ $summary['total_outstanding'] > 0 ? round(($summary['overdue_31_60'] / $summary['total_outstanding']) * 100, 1) : 0 }}% of Total</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">61–90 Days</span>
                <span class="kpi-val orange">₹{{ number_format($summary['critical_61_90'], 2) }}</span>
                <div class="kpi-sub">{{ $summary['total_outstanding'] > 0 ? round(($summary['critical_61_90'] / $summary['total_outstanding']) * 100, 1) : 0 }}% of Total</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">90+ Days (Risk)</span>
                <span class="kpi-val rose">₹{{ number_format($summary['high_risk_90_plus'], 2) }}</span>
                <div class="kpi-sub">{{ $summary['total_outstanding'] > 0 ? round(($summary['high_risk_90_plus'] / $summary['total_outstanding']) * 100, 1) : 0 }}% of Total</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">DSO (Collection Speed)</span>
                <span class="kpi-val">{{ $summary['dso_days'] }} <span style="font-size: 8px;">Days</span></span>
                <div class="kpi-sub">{{ $summary['dso_days'] <= 45 ? 'Optimal' : ($summary['dso_days'] <= 75 ? 'Moderate' : 'High Drag') }}</div>
            </td>
        </tr>
    </table>

    <!-- Debtors Aging Matrix Table -->
    <table class="table">
        <thead>
            <tr>
                <th style="width: 25%;">Customer / Organization</th>
                <th style="width: 15%;">Contact</th>
                <th class="text-center" style="width: 8%;">Open Bills</th>
                <th class="text-right" style="width: 12%;">0–30 Days</th>
                <th class="text-right" style="width: 12%;">31–60 Days</th>
                <th class="text-right" style="width: 12%;">61–90 Days</th>
                <th class="text-right" style="width: 12%;">90+ Days</th>
                <th class="text-right" style="width: 14%;">Total Due</th>
            </tr>
        </thead>
        <tbody>
            @forelse($debtors as $d)
                <tr>
                    <td><strong>{{ $d['customer_name'] }}</strong></td>
                    <td>{{ $d['phone'] ?: ($d['email'] ?: '-') }}</td>
                    <td class="text-center">{{ $d['unpaid_invoices_count'] }}</td>
                    <td class="text-right">{{ $d['days_0_30'] > 0 ? '₹' . number_format($d['days_0_30'], 2) : '-' }}</td>
                    <td class="text-right">{{ $d['days_31_60'] > 0 ? '₹' . number_format($d['days_31_60'], 2) : '-' }}</td>
                    <td class="text-right">{{ $d['days_61_90'] > 0 ? '₹' . number_format($d['days_61_90'], 2) : '-' }}</td>
                    <td class="text-right" style="{{ $d['days_90_plus'] > 0 ? 'color: #dc2626; font-weight: bold;' : '' }}">
                        {{ $d['days_90_plus'] > 0 ? '₹' . number_format($d['days_90_plus'], 2) : '-' }}
                    </td>
                    <td class="text-right" style="font-weight: bold;">₹{{ number_format($d['total_due'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px; color: #64748b;">
                        No outstanding receivables found.
                    </td>
                </tr>
            @endforelse

            @if(count($debtors) > 0)
                <tr class="table-total">
                    <td colspan="3" style="text-align: right; font-weight: bold;">TOTAL RECEIVABLES:</td>
                    <td class="text-right" style="font-weight: bold; color: #059669;">₹{{ number_format($summary['current_0_30'], 2) }}</td>
                    <td class="text-right" style="font-weight: bold; color: #d97706;">₹{{ number_format($summary['overdue_31_60'], 2) }}</td>
                    <td class="text-right" style="font-weight: bold; color: #ea580c;">₹{{ number_format($summary['critical_61_90'], 2) }}</td>
                    <td class="text-right" style="font-weight: bold; color: #dc2626;">₹{{ number_format($summary['high_risk_90_plus'], 2) }}</td>
                    <td class="text-right" style="font-weight: bold; font-size: 9px;">₹{{ number_format($summary['total_outstanding'], 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Confidential Document — Generated automatically by CCTV CRM Accounts Receivable & Credit Control Module
    </div>

</body>
</html>
