<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Revenue & AMC Retention Report</title>
    <style>
        @page {
            margin: 28px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5px;
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
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-sub {
            font-size: 8.5px;
            color: #475569;
        }
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .kpi-box {
            width: 25%;
            border: 1px solid #cbd5e1;
            padding: 8px;
            background-color: #f8fafc;
            vertical-align: top;
        }
        .kpi-label {
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            display: block;
            margin-bottom: 3px;
        }
        .kpi-val {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }
        .section-heading {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            background-color: #f1f5f9;
            padding: 4px 8px;
            border-left: 3px solid #4f46e5;
            margin-top: 10px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #334155;
            text-align: left;
        }
        .data-table td {
            border: 1px solid #e2e8f0;
            padding: 5px 6px;
            font-size: 8.5px;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 7.5px;
            font-weight: bold;
        }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .footer {
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 7.5px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td>
                <div class="header-title">CCTV CRM &bull; MRR & AMC Retention Report</div>
                <div class="header-sub">Recurring Subscription Revenue, Contract Retention & Lead-to-Quote Conversion</div>
            </td>
            <td style="text-align: right;">
                <div style="font-size: 9px; font-weight: bold; color: #0f172a;">Report Period: {{ $data['date_range']['label'] }}</div>
                <div class="header-sub">Generated on: {{ now()->format('d M Y, h:i A') }}</div>
            </td>
        </tr>
    </table>

    {{-- Top 4 KPI Metric Summary --}}
    <table class="kpi-table">
        <tr>
            <td class="kpi-box">
                <span class="kpi-label">Monthly Recurring (MRR)</span>
                <div class="kpi-val" style="color: #4338ca;">₹{{ number_format($data['snapshot']['active_mrr'], 2) }}</div>
                <div style="font-size: 7.5px; color: #64748b; margin-top: 2px;">ARR: ₹{{ number_format($data['snapshot']['active_arr'], 2) }}</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">AMC Contract Retention</span>
                <div class="kpi-val" style="color: #15803d;">{{ $data['snapshot']['retention_rate'] }}%</div>
                <div style="font-size: 7.5px; color: #64748b; margin-top: 2px;">{{ $data['snapshot']['active_contracts'] }} active (Churn: {{ $data['snapshot']['churn_rate'] }}%)</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">Lead-to-Won Conversion</span>
                <div class="kpi-val" style="color: #1d4ed8;">{{ $data['conversion']['lead_to_won_rate'] }}%</div>
                <div style="font-size: 7.5px; color: #64748b; margin-top: 2px;">Quote-to-Won: {{ $data['conversion']['quote_to_accepted_rate'] }}%</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">Avg Quote Close Time</span>
                <div class="kpi-val" style="color: #b45309;">{{ $data['conversion']['avg_turnaround_days'] }} Days</div>
                <div style="font-size: 7.5px; color: #64748b; margin-top: 2px;">Turnaround to acceptance</div>
            </td>
        </tr>
    </table>

    {{-- 12-Month Sales Funnel & MRR History --}}
    <div class="section-heading">12-Month Sales Conversion & Recurring Revenue Ledger</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 14%;">Month</th>
                <th style="width: 14%; text-align: right;">MRR (₹)</th>
                <th style="width: 10%; text-align: center;">Leads</th>
                <th style="width: 12%; text-align: center;">Quotes Issued</th>
                <th style="width: 12%; text-align: center;">Quotes Won</th>
                <th style="width: 12%; text-align: center;">Win Rate (%)</th>
                <th style="width: 13%; text-align: right;">Quoted Pipeline</th>
                <th style="width: 13%; text-align: right;">Won Value (₹)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['conversion']['monthly_trend']['labels'] as $idx => $mLabel)
                <tr>
                    <td><strong>{{ $mLabel }}</strong></td>
                    <td style="text-align: right; font-weight: bold; color: #4338ca;">₹{{ number_format($data['timeline']['mrr'][$idx], 2) }}</td>
                    <td style="text-align: center;">{{ $data['conversion']['monthly_trend']['leads'][$idx] }}</td>
                    <td style="text-align: center;">{{ $data['conversion']['monthly_trend']['quotations'][$idx] }}</td>
                    <td style="text-align: center; font-weight: bold; color: #15803d;">{{ $data['conversion']['monthly_trend']['accepted'][$idx] }}</td>
                    <td style="text-align: center;">
                        <span class="badge {{ $data['conversion']['monthly_trend']['conversion_rates'][$idx] >= 40 ? 'badge-success' : 'badge-warning' }}">
                            {{ $data['conversion']['monthly_trend']['conversion_rates'][$idx] }}%
                        </span>
                    </td>
                    <td style="text-align: right; color: #64748b;">₹{{ number_format($data['conversion']['monthly_trend']['quoted_amounts'][$idx], 2) }}</td>
                    <td style="text-align: right; font-weight: bold; color: #15803d;">₹{{ number_format($data['conversion']['monthly_trend']['accepted_amounts'][$idx], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Confidential &bull; CCTV CRM Enterprise Analytics &bull; Generated Automatically
    </div>

</body>
</html>
