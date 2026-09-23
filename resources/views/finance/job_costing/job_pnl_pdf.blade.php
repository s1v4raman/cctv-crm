<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project Profit & Loss Statement - {{ $costing['job_no'] }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
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
        .project-meta-box {
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
        .section-heading {
            font-size: 9.5px;
            font-weight: bold;
            color: #0f172a;
            background: #e2e8f0;
            padding: 4px 6px;
            margin-top: 10px;
            margin-bottom: 5px;
            border-left: 3px solid #10b981;
            text-transform: uppercase;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 5px;
            border: 1px solid #0f172a;
        }
        .data-table td {
            border: 1px solid #e2e8f0;
            padding: 4px 5px;
            font-size: 8px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }
        .footer {
            margin-top: 15px;
            padding-top: 6px;
            border-top: 1px solid #cbd5e1;
            font-size: 7.5px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: middle;">
                <div class="header-title">CCTV INSTALLATION PROJECT P&L STATEMENT</div>
                <div class="header-sub">
                    Financial Costing & Profitability Sheet • Project: <strong class="font-mono">{{ $costing['job_no'] }}</strong>
                </div>
            </td>
            <td style="width: 45%;" class="project-meta-box">
                <strong>Customer:</strong> {{ $costing['customer_name'] }}<br>
                <strong>Site:</strong> {{ $costing['site_address'] }}<br>
                <strong>Technician:</strong> {{ $costing['technician_name'] }} • <strong>Date:</strong> {{ $costing['scheduled_date'] }}<br>
                <strong>Invoice Ref:</strong> {{ $costing['invoice_no'] }} • <strong>Quotation Ref:</strong> {{ $costing['quotation_no'] }}
            </td>
        </tr>
    </table>

    <!-- KPI Summary Grid -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-box">
                <span class="kpi-label">Taxable Revenue (Net)</span>
                <span class="kpi-val">₹{{ number_format($costing['revenue_taxable'], 2) }}</span>
                <div style="font-size: 7px; color: #64748b; margin-top: 2px;">Gross Invoiced: ₹{{ number_format($costing['revenue_gross'], 2) }}</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">Hardware Material Cost (COGS)</span>
                <span class="kpi-val" style="color: #7c3aed;">₹{{ number_format($costing['hardware_cogs'], 2) }}</span>
                <div style="font-size: 7px; color: #64748b; margin-top: 2px;">{{ count($costing['hardware_items']) }} Bill of Material lines</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">Labor & Expenses</span>
                <span class="kpi-val" style="color: #d97706;">₹{{ number_format($costing['labor_cost'] + $costing['field_expenses'] + $costing['other_direct_costs'], 2) }}</span>
                <div style="font-size: 7px; color: #64748b; margin-top: 2px;">Labor: ₹{{ number_format($costing['labor_cost'], 2) }} ({{ $costing['labor_hours'] }}h)</div>
            </td>
            <td class="kpi-box" style="background-color: {{ $costing['gross_profit'] >= 0 ? '#ecfdf5' : '#fff1f2' }}; border-color: {{ $costing['gross_profit'] >= 0 ? '#10b981' : '#f43f5e' }};">
                <span class="kpi-label" style="color: {{ $costing['gross_profit'] >= 0 ? '#065f46' : '#9f1239' }};">Net Gross Profit</span>
                <span class="kpi-val" style="color: {{ $costing['gross_profit'] >= 0 ? '#047857' : '#e11d48' }};">₹{{ number_format($costing['gross_profit'], 2) }}</span>
                <div style="font-size: 7.5px; font-weight: bold; color: {{ $costing['gross_profit'] >= 0 ? '#047857' : '#e11d48' }}; margin-top: 2px;">
                    {{ $costing['gross_margin_percent'] }}% Margin ({{ $costing['margin_label'] }})
                </div>
            </td>
        </tr>
    </table>

    <!-- 1. Hardware Bill of Materials & Line Item Margins -->
    <div class="section-heading">1. Hardware Materials & Procurement Cost Analysis</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 32%;">Item Description</th>
                <th style="width: 10%;">HSN</th>
                <th class="text-center" style="width: 8%;">Qty</th>
                <th class="text-right" style="width: 12%;">Unit Buy (₹)</th>
                <th class="text-right" style="width: 12%;">Selling (₹)</th>
                <th class="text-right" style="width: 13%;">Total Cost (₹)</th>
                <th class="text-right" style="width: 13%;">Total Selling (₹)</th>
                <th class="text-right" style="width: 13%;">Profit (₹)</th>
                <th class="text-center" style="width: 9%;">Margin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($costing['hardware_items'] as $item)
                <tr>
                    <td class="font-bold">{{ $item['name'] }}</td>
                    <td class="font-mono">{{ $item['hsn_code'] }}</td>
                    <td class="text-center">{{ $item['quantity'] }} {{ $item['unit'] }}</td>
                    <td class="text-right font-mono">₹{{ number_format($item['unit_cost'], 2) }}</td>
                    <td class="text-right font-mono">₹{{ number_format($item['unit_price'], 2) }}</td>
                    <td class="text-right font-mono" style="color: #7c3aed;">₹{{ number_format($item['total_cost'], 2) }}</td>
                    <td class="text-right font-mono font-bold">₹{{ number_format($item['total_selling'], 2) }}</td>
                    <td class="text-right font-mono font-bold" style="color: #059669;">₹{{ number_format($item['item_margin'], 2) }}</td>
                    <td class="text-center font-mono font-bold">{{ $item['margin_percent'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="color: #94a3b8; padding: 6px;">No itemized line details found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 2. Labor & Overheads Breakdown -->
    <div class="section-heading">2. Technician Labor Time & Direct Project Overheads</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 40%;">Cost Category</th>
                <th style="width: 25%;">Reference / Details</th>
                <th class="text-right" style="width: 15%;">Units / Hours</th>
                <th class="text-right" style="width: 20%;">Total Expense (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-bold">Technician Labor Time</td>
                <td>{{ $costing['technician_name'] }} (@ ₹{{ number_format($costing['hourly_rate'], 2) }}/hr)</td>
                <td class="text-right font-mono">{{ $costing['labor_hours'] }} hrs</td>
                <td class="text-right font-mono font-bold" style="color: #d97706;">₹{{ number_format($costing['labor_cost'], 2) }}</td>
            </tr>
            <tr>
                <td class="font-bold">Field Travel & Materials Claims</td>
                <td>{{ count($costing['expense_claims_list']) }} approved claims</td>
                <td class="text-right font-mono">—</td>
                <td class="text-right font-mono font-bold">₹{{ number_format($costing['field_expenses'], 2) }}</td>
            </tr>
            <tr>
                <td class="font-bold">Other Direct Overheads</td>
                <td>{{ $costing['costing_notes'] ?: 'Scaffolding, rentals, permits' }}</td>
                <td class="text-right font-mono">—</td>
                <td class="text-right font-mono font-bold">₹{{ number_format($costing['other_direct_costs'], 2) }}</td>
            </tr>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="3" class="text-right">TOTAL DIRECT OVERHEADS:</td>
                <td class="text-right font-mono">₹{{ number_format($costing['labor_cost'] + $costing['field_expenses'] + $costing['other_direct_costs'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 3. Final Profitability Summary -->
    <div class="section-heading">3. Project Net Profitability Summary</div>
    <table style="width: 100%; border: 1px solid #cbd5e1; padding: 8px; background-color: #f8fafc; font-size: 8.5px;">
        <tr>
            <td style="width: 50%;">
                <strong>Total Taxable Revenue:</strong> ₹{{ number_format($costing['revenue_taxable'], 2) }}<br>
                <strong>Total Project Costs (COGS + Labor + Exp):</strong> ₹{{ number_format($costing['total_cost'], 2) }}<br>
                <strong>Amount Collected:</strong> ₹{{ number_format($costing['amount_collected'], 2) }}
            </td>
            <td style="width: 50%; text-align: right;">
                <span style="font-size: 11px; font-weight: bold; color: {{ $costing['gross_profit'] >= 0 ? '#047857' : '#e11d48' }};">
                    Net Project Profit: ₹{{ number_format($costing['gross_profit'], 2) }}
                </span><br>
                <strong>Gross Margin:</strong> {{ $costing['gross_margin_percent'] }}% ({{ $costing['margin_label'] }})<br>
                <strong>Receivable Balance:</strong> ₹{{ number_format($costing['receivable_balance'], 2) }}
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated automatically by CCTV CRM Enterprise Job Costing Engine on {{ now()->format('d M Y, h:i A') }} • Confidential Internal Management Statement
    </div>

</body>
</html>
