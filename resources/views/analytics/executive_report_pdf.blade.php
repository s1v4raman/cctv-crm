<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Executive Business Performance & Profitability Report</title>
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
            width: 32%;
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
            margin-bottom: 10px;
        }
        .data-table th {
            background-color: #0f172a;
            color: #ffffff;
            padding: 5px 6px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
        }
        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-size: 8.5px;
            vertical-align: top;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="header-title">CCTV Security Systems CRM</div>
                <div class="header-sub">Executive Business Analytics, Gross Margin & Profitability Summary</div>
            </td>
            <td style="width: 40%; text-align: right;">
                <div style="font-size: 11px; font-weight: bold; color: #4f46e5;">C-LEVEL EXECUTIVE BRIEF</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">
                    <strong>Report Window:</strong> {{ $overview['date_range']['label'] }}<br>
                    <strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Financial Health Summary --}}
    <div class="section-heading">1. Financial Profitability & Margin Health (Cost Breakdown)</div>
    <table class="kpi-table">
        <tr>
            <td class="kpi-box">
                <span class="kpi-label">Total Invoiced Revenue (Incl. GST)</span>
                <div class="kpi-val">₹{{ number_format($overview['financials']['invoiced_revenue'], 2) }}</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">Total billing volume</div>
            </td>
            <td style="width: 2%;"></td>
            <td class="kpi-box" style="background-color: #eff6ff; border-color: #bfdbfe;">
                <span class="kpi-label" style="color: #1d4ed8;">Revenue WITHOUT Tax (Subtotal)</span>
                <div class="kpi-val" style="color: #1e40af;">₹{{ number_format($overview['financials']['revenue_without_tax'] ?? round($overview['financials']['invoiced_revenue']/1.18, 2), 2) }}</div>
                <div style="font-size: 8px; color: #3b82f6; margin-top: 2px; font-weight: bold;">Pre-tax operating revenue</div>
            </td>
            <td style="width: 2%;"></td>
            <td class="kpi-box" style="background-color: #fff7ed; border-color: #fed7aa;">
                <span class="kpi-label" style="color: #c2410c;">Product Buy Cost (COGS)</span>
                <div class="kpi-val" style="color: #ea580c;">₹{{ number_format($overview['financials']['product_buy_cost'] ?? $overview['financials']['cogs'], 2) }}</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">Hardware & equipment purchase cost</div>
            </td>
        </tr>
        <tr><td style="height: 6px;" colspan="5"></td></tr>
        <tr>
            <td class="kpi-box" style="background-color: #fef2f2; border-color: #fecaca;">
                <span class="kpi-label" style="color: #b91c1c;">Tax Paid Cost (Input GST)</span>
                <div class="kpi-val" style="color: #dc2626;">₹{{ number_format($overview['financials']['tax_paid_cost'] ?? 0, 2) }}</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">GST paid on purchase orders</div>
            </td>
            <td style="width: 2%;"></td>
            <td class="kpi-box" style="background-color: #f5f3ff; border-color: #ddd6fe;">
                <span class="kpi-label" style="color: #7c3aed;">Salary Credited Cost (Auto-Calc)</span>
                <div class="kpi-val" style="color: #7c3aed;">₹{{ number_format($overview['financials']['salary_credited_cost'] ?? $overview['financials']['employee_salary_cost'] ?? 0, 2) }}</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">Total employee salary payroll</div>
            </td>
            <td style="width: 2%;"></td>
            <td class="kpi-box" style="background-color: #ecfdf5; border-color: #a7f3d0;">
                <span class="kpi-label" style="color: #065f46;">Gross Profit WITHOUT Tax</span>
                <div class="kpi-val" style="color: #059669;">₹{{ number_format($overview['financials']['gross_profit_without_tax'] ?? $overview['financials']['gross_profit'], 2) }}</div>
                <div style="font-size: 8px; font-weight: bold; color: #047857; margin-top: 2px;">{{ $overview['financials']['gross_margin_without_tax_percent'] ?? $overview['financials']['gross_margin_percent'] }}% Gross Margin (No Tax)</div>
            </td>
        </tr>
        <tr><td style="height: 6px;" colspan="5"></td></tr>
        @php
            $pdfNetProfit = $overview['financials']['net_profit_without_tax'] ?? (($overview['financials']['gross_profit_without_tax'] ?? $overview['financials']['gross_profit']) - ($overview['financials']['employee_salary_cost'] ?? 0));
            $pdfNetColor = $pdfNetProfit >= 0 ? '#0f766e' : '#dc2626';
            $pdfNetBg    = $pdfNetProfit >= 0 ? '#f0fdfa' : '#fef2f2';
            $pdfNetBorder= $pdfNetProfit >= 0 ? '#99f6e4' : '#fecaca';
        @endphp
        <tr>
            <td class="kpi-box" style="background-color: {{ $pdfNetBg }}; border-color: {{ $pdfNetBorder }}; border-width: 2px;">
                <span class="kpi-label" style="color: {{ $pdfNetColor }};">NET OPERATING PROFIT (No Tax, No Salary)</span>
                <div class="kpi-val" style="color: {{ $pdfNetColor }}; font-size: 16px;">₹{{ number_format($pdfNetProfit, 2) }}</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">Revenue(No Tax) − Product Buy − Salary</div>
            </td>
            <td style="width: 2%;"></td>
            <td class="kpi-box">
                <span class="kpi-label">Realized Cash Collections</span>
                <div class="kpi-val" style="color: #4f46e5;">₹{{ number_format($overview['financials']['cash_collected'], 2) }}</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">Payments realized</div>
            </td>
            <td style="width: 2%;"></td>
            <td class="kpi-box">
                <span class="kpi-label">Outstanding Receivables</span>
                <div class="kpi-val" style="color: #e11d48;">₹{{ number_format($overview['financials']['total_receivables'], 2) }}</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">Pending customer dues</div>
            </td>
        </tr>
        <tr><td style="height: 6px;" colspan="5"></td></tr>
        <tr>
            <td class="kpi-box">
                <span class="kpi-label">AMC Portfolio (ARR)</span>
                <div class="kpi-val" style="color: #d97706;">₹{{ number_format($overview['amc']['arr'], 2) }}</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">{{ $overview['amc']['active_contracts'] }} active recurring contracts</div>
            </td>
            <td style="width: 2%;"></td>
            <td class="kpi-box" style="background-color: #fef9c3; border-color: #fde047;">
                <span class="kpi-label" style="color: #854d0e;">Tax Collected (Output GST)</span>
                <div class="kpi-val" style="color: #a16207;">₹{{ number_format($overview['financials']['tax_collected_cost'] ?? ($overview['financials']['invoiced_revenue'] - ($overview['financials']['revenue_without_tax'] ?? round($overview['financials']['invoiced_revenue']/1.18, 2))), 2) }}</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">GST billed to clients (pass-through)</div>
            </td>
            <td style="width: 2%;"></td>
            <td class="kpi-box">
                <span class="kpi-label">COGS (Legacy Gross Profit)</span>
                <div class="kpi-val" style="color: #475569;">₹{{ number_format($overview['financials']['gross_profit'], 2) }}</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">{{ $overview['financials']['gross_margin_percent'] }}% margin (gross)</div>
            </td>
        </tr>
    </table>

    {{-- Cost Waterfall Summary --}}
    <div class="section-heading" style="border-left-color: #059669;">Cost & Profit Waterfall Statement</div>
    <table class="data-table">
        <tbody>
            <tr style="background-color: #f0f9ff;">
                <td style="font-weight: bold; width: 60%;">( + ) Revenue WITHOUT Tax (Subtotal)</td>
                <td style="text-align: right; font-weight: bold; color: #1e40af;">₹{{ number_format($overview['financials']['revenue_without_tax'] ?? round($overview['financials']['invoiced_revenue']/1.18, 2), 2) }}</td>
            </tr>
            <tr>
                <td style="color: #ea580c;">( − ) Product Buy Cost / COGS (Hardware)</td>
                <td style="text-align: right; color: #ea580c;">₹{{ number_format($overview['financials']['product_buy_cost'] ?? $overview['financials']['cogs'], 2) }}</td>
            </tr>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td>( = ) Gross Operating Profit (No Tax)</td>
                <td style="text-align: right; color: #059669;">₹{{ number_format($overview['financials']['gross_profit_without_tax'] ?? $overview['financials']['gross_profit'], 2) }}</td>
            </tr>
            <tr>
                <td style="color: #7c3aed;">( − ) Employee Salary Credited Cost</td>
                <td style="text-align: right; color: #7c3aed;">₹{{ number_format($overview['financials']['salary_credited_cost'] ?? $overview['financials']['employee_salary_cost'] ?? 0, 2) }}</td>
            </tr>
            <tr style="background-color: {{ $pdfNetBg }}; font-weight: bold; border: 2px solid {{ $pdfNetBorder }};">
                <td style="font-size: 10px; color: {{ $pdfNetColor }};">( = ) NET OPERATING PROFIT (Without Tax &amp; Without Salary)</td>
                <td style="text-align: right; font-size: 12px; font-weight: bold; color: {{ $pdfNetColor }};">₹{{ number_format($pdfNetProfit, 2) }}</td>
            </tr>
            <tr>
                <td style="color: #b91c1c; font-size: 8px;">(Memo) Tax Paid on Purchases (Input GST)</td>
                <td style="text-align: right; color: #b91c1c; font-size: 8px;">₹{{ number_format($overview['financials']['tax_paid_cost'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td style="color: #a16207; font-size: 8px;">(Memo) Tax Collected from Clients (Output GST)</td>
                <td style="text-align: right; color: #a16207; font-size: 8px;">₹{{ number_format($overview['financials']['tax_collected_cost'] ?? ($overview['financials']['invoiced_revenue'] - ($overview['financials']['revenue_without_tax'] ?? round($overview['financials']['invoiced_revenue']/1.18, 2))), 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Revenue Streams & Pipeline Funnel --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
        <tr>
            <td style="width: 49%; vertical-align: top;">
                <div class="section-heading">2. Revenue Streams Contribution</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Stream</th>
                            <th style="text-align: right;">Amount (₹)</th>
                            <th style="text-align: right;">Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Installation Projects</strong></td>
                            <td style="text-align: right;">₹{{ number_format($overview['revenue_streams']['projects'], 2) }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ $overview['revenue_streams']['percentages']['projects'] }}%</td>
                        </tr>
                        <tr>
                            <td><strong>AMC Contracts</strong></td>
                            <td style="text-align: right;">₹{{ number_format($overview['revenue_streams']['amc'], 2) }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ $overview['revenue_streams']['percentages']['amc'] }}%</td>
                        </tr>
                        <tr>
                            <td><strong>Service Ticket Repairs</strong></td>
                            <td style="text-align: right;">₹{{ number_format($overview['revenue_streams']['service'], 2) }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ $overview['revenue_streams']['percentages']['service'] }}%</td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td style="width: 2%;"></td>
            <td style="width: 49%; vertical-align: top;">
                <div class="section-heading">3. Sales Pipeline Funnel</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Stage</th>
                            <th style="text-align: center;">Count</th>
                            <th style="text-align: right;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total Inquiries / Leads</td>
                            <td style="text-align: center; font-weight: bold;">{{ $overview['lead_funnel']['total_leads'] }}</td>
                            <td style="text-align: right; color: #64748b;">Pipeline Entry</td>
                        </tr>
                        <tr>
                            <td>Site Surveys Conducted</td>
                            <td style="text-align: center; font-weight: bold;">{{ $overview['lead_funnel']['surveys'] }}</td>
                            <td style="text-align: right; color: #64748b;">Surveyed</td>
                        </tr>
                        <tr>
                            <td>Quotations Issued</td>
                            <td style="text-align: center; font-weight: bold;">{{ $overview['lead_funnel']['quotations'] }}</td>
                            <td style="text-align: right; color: #64748b;">Quoted</td>
                        </tr>
                        <tr>
                            <td><strong>Won / Closed Projects</strong></td>
                            <td style="text-align: center; font-weight: bold; color: #059669;">{{ $overview['lead_funnel']['won_projects'] }}</td>
                            <td style="text-align: right; font-weight: bold; color: #059669;">{{ $overview['lead_funnel']['conversion_rate'] }}% Win Rate</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    {{-- Technician Scorecard --}}
    <div class="section-heading">4. Field Engineering & Technician CSAT Scorecard</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%;">Technician Name</th>
                <th style="width: 20%;">Role</th>
                <th style="width: 15%; text-align: center;">Jobs Completed</th>
                <th style="width: 15%; text-align: center;">Tickets Resolved</th>
                <th style="width: 10%; text-align: center;">JCR Signed</th>
                <th style="width: 10%; text-align: right;">CSAT ⭐</th>
            </tr>
        </thead>
        <tbody>
            @forelse($overview['technicians'] as $tech)
                <tr>
                    <td><strong>{{ $tech['name'] }}</strong></td>
                    <td style="text-transform: capitalize; color: #64748b;">{{ $tech['role'] }}</td>
                    <td style="text-align: center;">{{ $tech['jobs_completed'] }}</td>
                    <td style="text-align: center;">{{ $tech['tickets_resolved'] }}</td>
                    <td style="text-align: center; font-weight: bold; color: #4f46e5;">{{ $tech['jcr_signoffs'] }}</td>
                    <td style="text-align: right; font-weight: bold; color: #d97706;">⭐ {{ $tech['average_rating'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8;">No technician records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div style="margin-top: 25px; border-top: 1px solid #cbd5e1; padding-top: 6px; font-size: 8px; color: #94a3b8; text-align: right;">
        CCTV Security CRM &middot; Executive Financial & Operations Intelligence Report &middot; Confidential
    </div>

</body>
</html>
