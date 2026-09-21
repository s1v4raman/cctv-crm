<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cost &amp; Profit Analysis Report</title>
    <style>
        @page { margin: 24px 28px; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px; line-height: 1.35;
            color: #1e293b; background: #ffffff;
        }
        .header-bar { width: 100%; border-bottom: 2.5px solid #059669; padding-bottom: 8px; margin-bottom: 14px; }
        .header-title { font-size: 15px; font-weight: bold; color: #064e3b; text-transform: uppercase; letter-spacing: 0.5px; }
        .header-sub { font-size: 8px; color: #475569; }
        .section-heading {
            font-size: 9.5px; font-weight: bold; color: #0f172a;
            background-color: #f1f5f9; padding: 4px 8px;
            border-left: 3px solid #059669;
            margin-top: 12px; margin-bottom: 6px; text-transform: uppercase;
        }
        .kpi-grid { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .kpi-box { border: 1px solid #cbd5e1; padding: 7px 8px; background-color: #f8fafc; vertical-align: top; }
        .kpi-label { font-size: 7px; font-weight: bold; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 2px; }
        .kpi-val { font-size: 13px; font-weight: bold; color: #0f172a; }
        .kpi-sub { font-size: 7.5px; color: #64748b; margin-top: 2px; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .data-table th { background-color: #064e3b; color: #fff; padding: 4px 6px; text-align: left; font-size: 7.5px; text-transform: uppercase; }
        .data-table td { border: 1px solid #e2e8f0; padding: 3.5px 6px; font-size: 8px; vertical-align: top; }
        .data-table tr:nth-child(even) td { background-color: #f8fafc; }
        .row-revenue td { background-color: #eff6ff; font-weight: bold; color: #1e40af; }
        .row-cogs td { color: #ea580c; }
        .row-gross td { background-color: #ecfdf5; font-weight: bold; color: #059669; }
        .row-salary td { color: #7c3aed; }
        .row-net-pos td { background-color: #f0fdfa; font-weight: bold; color: #0f766e; font-size: 9.5px; border: 1.5px solid #99f6e4; }
        .row-net-neg td { background-color: #fef2f2; font-weight: bold; color: #dc2626; font-size: 9.5px; border: 1.5px solid #fecaca; }
        .row-memo td { color: #94a3b8; font-style: italic; font-size: 7.5px; }
        .badge-paid { background: #dcfce7; color: #166534; padding: 1px 5px; border-radius: 3px; font-weight: bold; font-size: 7px; }
        .badge-pending { background: #fef9c3; color: #854d0e; padding: 1px 5px; border-radius: 3px; font-weight: bold; font-size: 7px; }
        .footer { margin-top: 18px; border-top: 1px solid #cbd5e1; padding-top: 5px; font-size: 7px; color: #94a3b8; text-align: right; }
    </style>
</head>
<body>

    {{-- Header --}}
    <table class="header-bar">
        <tr>
            <td style="width:60%">
                <div class="header-title">Cost &amp; Profit Analysis Report</div>
                <div class="header-sub">Revenue Without Tax &middot; Product Buy Cost &middot; Salary Credited &middot; Tax Paid &middot; Net Operating Profit</div>
            </td>
            <td style="width:40%; text-align:right">
                <div style="font-size:11px; font-weight:bold; color:#059669">COST &amp; PROFIT INTELLIGENCE</div>
                <div style="font-size:7.5px; color:#64748b; margin-top:2px">
                    <strong>Period:</strong> {{ $data['date_range']['label'] }}<br>
                    <strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- KPI Variables --}}
    @php
        $fin           = $data['financials'];
        $revNoTax      = $fin['revenue_without_tax']      ?? round(($fin['invoiced_revenue'] ?? 0) / 1.18, 2);
        $productBuy    = $fin['product_buy_cost']         ?? ($fin['cogs'] ?? 0);
        $taxPaid       = $fin['tax_paid_cost']            ?? 0;
        $taxCollected  = $fin['tax_collected_cost']       ?? (($fin['invoiced_revenue'] ?? 0) - $revNoTax);
        $salaryCredited= $fin['salary_credited_cost']     ?? ($fin['employee_salary_cost'] ?? 0);
        $grossNoTax    = $fin['gross_profit_without_tax'] ?? ($fin['gross_profit'] ?? 0);
        $grossPct      = $fin['gross_margin_without_tax_percent'] ?? ($fin['gross_margin_percent'] ?? 0);
        $netProfit     = $fin['net_profit_without_tax']   ?? ($grossNoTax - $salaryCredited);
        $netColor      = $netProfit >= 0 ? '#0f766e' : '#dc2626';
        $netBg         = $netProfit >= 0 ? '#f0fdfa' : '#fef2f2';
        $netBorder     = $netProfit >= 0 ? '#99f6e4' : '#fecaca';
    @endphp

    {{-- 1. Master KPI Boxes --}}
    <div class="section-heading">1. Master Cost &amp; Profit KPIs</div>
    <table class="kpi-grid">
        <tr>
            <td class="kpi-box" style="width:19%">
                <span class="kpi-label">Revenue (No Tax)</span>
                <div class="kpi-val" style="color:#1e40af">&#8377;{{ number_format($revNoTax, 2) }}</div>
                <div class="kpi-sub">Pre-tax subtotal</div>
            </td>
            <td style="width:1%"></td>
            <td class="kpi-box" style="width:19%; background:#fff7ed; border-color:#fed7aa">
                <span class="kpi-label" style="color:#c2410c">Product Buy Cost</span>
                <div class="kpi-val" style="color:#ea580c">&#8377;{{ number_format($productBuy, 2) }}</div>
                <div class="kpi-sub">Hardware / COGS</div>
            </td>
            <td style="width:1%"></td>
            <td class="kpi-box" style="width:19%; background:#ecfdf5; border-color:#a7f3d0">
                <span class="kpi-label" style="color:#065f46">Gross Profit (No Tax)</span>
                <div class="kpi-val" style="color:#059669">&#8377;{{ number_format($grossNoTax, 2) }}</div>
                <div class="kpi-sub">{{ $grossPct }}% margin</div>
            </td>
            <td style="width:1%"></td>
            <td class="kpi-box" style="width:19%; background:#f5f3ff; border-color:#ddd6fe">
                <span class="kpi-label" style="color:#7c3aed">Salary Credited</span>
                <div class="kpi-val" style="color:#7c3aed">&#8377;{{ number_format($salaryCredited, 2) }}</div>
                <div class="kpi-sub">Staff payroll cost</div>
            </td>
            <td style="width:1%"></td>
            <td class="kpi-box" style="width:20%; background:{{ $netBg }}; border:2px solid {{ $netBorder }}">
                <span class="kpi-label" style="color:{{ $netColor }}">NET Op. Profit</span>
                <div class="kpi-val" style="color:{{ $netColor }}; font-size:14px">&#8377;{{ number_format($netProfit, 2) }}</div>
                <div class="kpi-sub" style="color:{{ $netColor }}">No tax &middot; No salary</div>
            </td>
        </tr>
    </table>

    {{-- 2. Cost Waterfall --}}
    <div class="section-heading">2. Cost &amp; Profit Waterfall Statement</div>
    <table class="data-table">
        <tbody>
            <tr class="row-revenue">
                <td style="width:65%">( + ) Revenue WITHOUT Tax &mdash; Pre-tax Subtotal</td>
                <td style="text-align:right">&#8377;{{ number_format($revNoTax, 2) }}</td>
            </tr>
            <tr class="row-cogs">
                <td>( &minus; ) Product Buy Cost / COGS (Hardware &amp; Equipment)</td>
                <td style="text-align:right">&#8377;{{ number_format($productBuy, 2) }}</td>
            </tr>
            <tr class="row-gross">
                <td>( = ) Gross Operating Profit (Without Tax)</td>
                <td style="text-align:right">&#8377;{{ number_format($grossNoTax, 2) }} &nbsp; <span style="font-size:7.5px">({{ $grossPct }}%)</span></td>
            </tr>
            <tr class="row-salary">
                <td>( &minus; ) Employee Salary Credited Cost</td>
                <td style="text-align:right">&#8377;{{ number_format($salaryCredited, 2) }}</td>
            </tr>
            <tr class="{{ $netProfit >= 0 ? 'row-net-pos' : 'row-net-neg' }}">
                <td>( = ) NET OPERATING PROFIT &mdash; Without Tax &amp; Without Salary</td>
                <td style="text-align:right">&#8377;{{ number_format($netProfit, 2) }}</td>
            </tr>
            <tr class="row-memo">
                <td>(Memo) Tax Paid on Purchases &mdash; Input GST</td>
                <td style="text-align:right">&#8377;{{ number_format($taxPaid, 2) }}</td>
            </tr>
            <tr class="row-memo">
                <td>(Memo) Tax Collected from Clients &mdash; Output GST (Pass-Through)</td>
                <td style="text-align:right">&#8377;{{ number_format($taxCollected, 2) }}</td>
            </tr>
            <tr class="row-memo">
                <td>(Memo) Net GST Position (Collected &minus; Paid)</td>
                <td style="text-align:right">&#8377;{{ number_format($taxCollected - $taxPaid, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- 3. Salary Ledger --}}
    @if(!empty($data['salary_breakdown']))
    <div class="section-heading">3. Employee Salary Credited Ledger</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:30%">Employee Name</th>
                <th style="width:18%">Role</th>
                <th style="text-align:right; width:20%">Monthly Rate (&#8377;)</th>
                <th style="text-align:right; width:18%">Period Amount (&#8377;)</th>
                <th style="text-align:center; width:14%">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $salaryTotal = 0; @endphp
            @forelse($data['salary_breakdown']['employees'] ?? [] as $emp)
                @php $salaryTotal += $emp['period_amount'] ?? $emp['monthly_salary'] ?? 0; @endphp
                <tr>
                    <td><strong>{{ $emp['name'] }}</strong></td>
                    <td style="text-transform:capitalize; color:#64748b">{{ $emp['role'] }}</td>
                    <td style="text-align:right">&#8377;{{ number_format($emp['monthly_salary'] ?? 0, 2) }}</td>
                    <td style="text-align:right; font-weight:bold">&#8377;{{ number_format($emp['period_amount'] ?? $emp['monthly_salary'] ?? 0, 2) }}</td>
                    <td style="text-align:center">
                        @if(($emp['status'] ?? '') === 'paid')
                            <span class="badge-paid">PAID</span>
                        @else
                            <span class="badge-pending">CREDITED</span>
                        @endif
                    </td>
                </tr>
            @empty
                {{-- Role-level summary --}}
                @foreach(['admin' => 'Admin / Management', 'staff' => 'Office Staff', 'technician' => 'Field Technicians'] as $rk => $rl)
                    @if(isset($data['salary_breakdown'][$rk . '_salary']) && $data['salary_breakdown'][$rk . '_salary'] > 0)
                        @php $salaryTotal += $data['salary_breakdown'][$rk . '_salary']; @endphp
                        <tr>
                            <td><em>{{ $rl }}</em></td>
                            <td style="text-transform:capitalize; color:#64748b">{{ $rk }}</td>
                            <td style="text-align:right; color:#94a3b8">&mdash;</td>
                            <td style="text-align:right; font-weight:bold">&#8377;{{ number_format($data['salary_breakdown'][$rk . '_salary'], 2) }}</td>
                            <td style="text-align:center"><span class="badge-pending">CREDITED</span></td>
                        </tr>
                    @endif
                @endforeach
            @endforelse
            <tr style="background:#f1f5f9; font-weight:bold">
                <td colspan="3" style="text-align:right">TOTAL SALARY CREDITED</td>
                <td style="text-align:right; color:#7c3aed">&#8377;{{ number_format($data['salary_breakdown']['total_salary'] ?? $salaryTotal, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    @endif

    {{-- 4. Tax Summary --}}
    <div class="section-heading">4. Tax Cost Summary</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:50%">Tax Category</th>
                <th style="text-align:right; width:25%">Amount (&#8377;)</th>
                <th style="width:25%">Note</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Tax Paid Cost (Input GST on Purchase Orders)</strong></td>
                <td style="text-align:right; color:#dc2626; font-weight:bold">&#8377;{{ number_format($taxPaid, 2) }}</td>
                <td style="color:#64748b">GST paid to suppliers</td>
            </tr>
            <tr>
                <td><strong>Tax Collected (Output GST Billed to Clients)</strong></td>
                <td style="text-align:right; color:#d97706; font-weight:bold">&#8377;{{ number_format($taxCollected, 2) }}</td>
                <td style="color:#64748b">Pass-through; owed to govt.</td>
            </tr>
            <tr style="background:#f8fafc; font-weight:bold">
                <td>Net GST Position (Output &minus; Input)</td>
                <td style="text-align:right; font-weight:bold; color:{{ ($taxCollected - $taxPaid) >= 0 ? '#059669' : '#dc2626' }}">
                    &#8377;{{ number_format($taxCollected - $taxPaid, 2) }}
                </td>
                <td style="color:#64748b">{{ ($taxCollected - $taxPaid) >= 0 ? 'Payable to govt.' : 'Refund due' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- 5. Per-Invoice Profitability --}}
    @if(!empty($data['invoice_profitability']))
    <div class="section-heading">5. Per-Invoice Unit Profitability (Top 20 Jobs)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:12%">Invoice #</th>
                <th style="width:28%">Client</th>
                <th style="text-align:right; width:16%">Subtotal (&#8377;)</th>
                <th style="text-align:right; width:14%">COGS (&#8377;)</th>
                <th style="text-align:right; width:16%">Gross Profit (&#8377;)</th>
                <th style="text-align:right; width:14%">Margin %</th>
            </tr>
        </thead>
        <tbody>
            @foreach(array_slice($data['invoice_profitability'], 0, 20) as $inv)
            <tr>
                <td style="font-family:monospace">#{{ $inv['invoice_number'] }}</td>
                <td>{{ $inv['client_name'] }}</td>
                <td style="text-align:right">&#8377;{{ number_format($inv['subtotal'] ?? 0, 2) }}</td>
                <td style="text-align:right; color:#ea580c">&#8377;{{ number_format($inv['cogs'] ?? 0, 2) }}</td>
                <td style="text-align:right; font-weight:bold; color:{{ ($inv['gross_profit'] ?? 0) >= 0 ? '#059669' : '#dc2626' }}">
                    &#8377;{{ number_format($inv['gross_profit'] ?? 0, 2) }}
                </td>
                <td style="text-align:right; font-weight:bold; color:{{ ($inv['margin_percent'] ?? 0) >= 30 ? '#059669' : (($inv['margin_percent'] ?? 0) >= 10 ? '#d97706' : '#dc2626') }}">
                    {{ number_format($inv['margin_percent'] ?? 0, 1) }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Footer --}}
    <div class="footer">
        CCTV Security CRM &middot; Cost &amp; Profit Analysis Report &middot; Period: {{ $data['date_range']['label'] }} &middot; Confidential
    </div>

</body>
</html>
