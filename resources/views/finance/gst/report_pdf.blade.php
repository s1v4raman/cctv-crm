<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GST Return & Tax Compliance Audit Package - {{ $selectedMonth }}</title>
    <style>
        @page {
            margin: 25px 28px;
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
        .company-box {
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
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            background: #e2e8f0;
            padding: 4px 6px;
            margin-top: 10px;
            margin-bottom: 5px;
            border-left: 3px solid #f59e0b;
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
            <td style="width: 60%; vertical-align: middle;">
                <div class="header-title">GST RETURN & TAX COMPLIANCE AUDIT PACKAGE</div>
                <div class="header-sub">
                    Statutory Summary Report • Period: <strong>{{ Carbon\Carbon::parse($selectedMonth.'-01')->format('F Y') }}</strong> ({{ $startDate->format('d/m/Y') }} to {{ $endDate->format('d/m/Y') }})
                </div>
            </td>
            <td style="width: 40%;" class="company-box">
                <strong style="font-size: 9.5px; color: #0f172a;">{{ $companyGst['trade_name'] }}</strong><br>
                GSTIN: <strong class="font-mono">{{ $companyGst['gstin'] }}</strong> • PAN: <strong class="font-mono">{{ $companyGst['pan'] }}</strong><br>
                State: {{ $companyGst['state'] }} (Code: {{ $companyGst['state_code'] }})<br>
                {{ $companyGst['address'] }}
            </td>
        </tr>
    </table>

    <!-- KPI Summary Grid -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-box">
                <span class="kpi-label">Gross Sales Turnover</span>
                <span class="kpi-val">₹{{ number_format($gstr1Data['total_gross_turnover'], 2) }}</span>
                <div style="font-size: 7px; color: #64748b; margin-top: 2px;">Taxable: ₹{{ number_format($gstr1Data['total_taxable_turnover'], 2) }}</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">Output GST (Sales)</span>
                <span class="kpi-val" style="color: #4f46e5;">₹{{ number_format($gstr1Data['total_output_tax'], 2) }}</span>
                <div style="font-size: 7px; color: #64748b; margin-top: 2px;">CGST: ₹{{ number_format($gstr1Data['total_output_cgst'], 2) }} | SGST: ₹{{ number_format($gstr1Data['total_output_sgst'], 2) }}</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">Input Tax Credit (ITC)</span>
                <span class="kpi-val" style="color: #059669;">₹{{ number_format($itcData['grand_total_itc'], 2) }}</span>
                <div style="font-size: 7px; color: #64748b; margin-top: 2px;">POs: ₹{{ number_format($itcData['total_po_itc'], 2) }} | Exp: ₹{{ number_format($itcData['total_expense_itc'], 2) }}</div>
            </td>
            <td class="kpi-box" style="background-color: #fef3c7; border-color: #f59e0b;">
                <span class="kpi-label" style="color: #92400e;">Net Tax Payable (Cash)</span>
                <span class="kpi-val" style="color: #b45309;">₹{{ number_format($gstr3bData['net_tax_payable_cash']['total'], 2) }}</span>
                <div style="font-size: 7px; color: #92400e; margin-top: 2px;">
                    @if($gstr3bData['itc_carry_forward']['total'] > 0)
                        ITC Carry Forward: ₹{{ number_format($gstr3bData['itc_carry_forward']['total'], 2) }}
                    @else
                        Discharge via E-Challan
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- 1. GSTR-3B Tax Computation & Set-off Matrix -->
    <div class="section-heading">1. GSTR-3B Tax Computation & Offset Matrix</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35%;">Tax Head / Description</th>
                <th class="text-right" style="width: 15%;">IGST (₹)</th>
                <th class="text-right" style="width: 15%;">CGST (₹)</th>
                <th class="text-right" style="width: 15%;">SGST (₹)</th>
                <th class="text-right" style="width: 20%;">Total (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-bold">(A) Total Output Tax Liability (Sales)</td>
                <td class="text-right font-mono">₹{{ number_format($gstr3bData['output_tax']['igst'], 2) }}</td>
                <td class="text-right font-mono">₹{{ number_format($gstr3bData['output_tax']['cgst'], 2) }}</td>
                <td class="text-right font-mono">₹{{ number_format($gstr3bData['output_tax']['sgst'], 2) }}</td>
                <td class="text-right font-mono font-bold">₹{{ number_format($gstr3bData['output_tax']['total'], 2) }}</td>
            </tr>
            <tr>
                <td class="font-bold">(B) Eligible Input Tax Credit (ITC)</td>
                <td class="text-right font-mono" style="color: #059669;">₹{{ number_format($gstr3bData['input_tax_credit']['igst'], 2) }}</td>
                <td class="text-right font-mono" style="color: #059669;">₹{{ number_format($gstr3bData['input_tax_credit']['cgst'], 2) }}</td>
                <td class="text-right font-mono" style="color: #059669;">₹{{ number_format($gstr3bData['input_tax_credit']['sgst'], 2) }}</td>
                <td class="text-right font-mono font-bold" style="color: #059669;">₹{{ number_format($gstr3bData['input_tax_credit']['total'], 2) }}</td>
            </tr>
            <tr style="background-color: #fef3c7; font-weight: bold;">
                <td style="color: #92400e;">(C) Net Cash Tax Payable to Govt</td>
                <td class="text-right font-mono" style="color: #b45309;">₹{{ number_format($gstr3bData['net_tax_payable_cash']['igst'], 2) }}</td>
                <td class="text-right font-mono" style="color: #b45309;">₹{{ number_format($gstr3bData['net_tax_payable_cash']['cgst'], 2) }}</td>
                <td class="text-right font-mono" style="color: #b45309;">₹{{ number_format($gstr3bData['net_tax_payable_cash']['sgst'], 2) }}</td>
                <td class="text-right font-mono font-bold" style="color: #b45309;">₹{{ number_format($gstr3bData['net_tax_payable_cash']['total'], 2) }}</td>
            </tr>
            <tr>
                <td style="color: #64748b;">(D) Closing ITC Carry Forward</td>
                <td class="text-right font-mono">₹{{ number_format($gstr3bData['itc_carry_forward']['igst'], 2) }}</td>
                <td class="text-right font-mono">₹{{ number_format($gstr3bData['itc_carry_forward']['cgst'], 2) }}</td>
                <td class="text-right font-mono">₹{{ number_format($gstr3bData['itc_carry_forward']['sgst'], 2) }}</td>
                <td class="text-right font-mono font-bold" style="color: #059669;">₹{{ number_format($gstr3bData['itc_carry_forward']['total'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 2. GSTR-1 Table 4: B2B Invoices -->
    <div class="section-heading">2. GSTR-1 Table 4: B2B Invoices (Registered Clients)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Inv No</th>
                <th>Date</th>
                <th>Customer Legal Name</th>
                <th>GSTIN</th>
                <th>POS</th>
                <th class="text-right">Taxable (₹)</th>
                <th class="text-right">CGST (₹)</th>
                <th class="text-right">SGST (₹)</th>
                <th class="text-right">IGST (₹)</th>
                <th class="text-right">Total (₹)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($gstr1Data['b2b_invoices'] as $b2b)
                <tr>
                    <td class="font-mono font-bold">{{ $b2b['invoice_no'] }}</td>
                    <td>{{ $b2b['invoice_date'] }}</td>
                    <td>{{ $b2b['customer_name'] }}</td>
                    <td class="font-mono">{{ $b2b['customer_gstin'] }}</td>
                    <td>{{ $b2b['pos_state'] }}</td>
                    <td class="text-right font-mono">₹{{ number_format($b2b['taxable_value'], 2) }}</td>
                    <td class="text-right font-mono">₹{{ number_format($b2b['cgst'], 2) }}</td>
                    <td class="text-right font-mono">₹{{ number_format($b2b['sgst'], 2) }}</td>
                    <td class="text-right font-mono">₹{{ number_format($b2b['igst'], 2) }}</td>
                    <td class="text-right font-mono font-bold">₹{{ number_format($b2b['total_value'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="color: #94a3b8; padding: 6px;">No B2B Invoices for this return period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 3. Table 12: HSN Summary -->
    <div class="section-heading">3. GSTR-1 Table 12: HSN-Wise Summary of Outward Supplies</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>HSN Code</th>
                <th>Description</th>
                <th class="text-center">UQC</th>
                <th class="text-center">Qty</th>
                <th class="text-center">Rate</th>
                <th class="text-right">Taxable (₹)</th>
                <th class="text-right">CGST (₹)</th>
                <th class="text-right">SGST (₹)</th>
                <th class="text-right">IGST (₹)</th>
                <th class="text-right">Total (₹)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($gstr1Data['hsn_summary'] as $hsn)
                <tr>
                    <td class="font-mono font-bold" style="color: #d97706;">{{ $hsn['hsn_code'] }}</td>
                    <td>{{ $hsn['description'] }}</td>
                    <td class="text-center font-mono">{{ $hsn['uqc'] }}</td>
                    <td class="text-center">{{ number_format($hsn['total_qty'], 1) }}</td>
                    <td class="text-center">{{ $hsn['tax_rate'] }}%</td>
                    <td class="text-right font-mono">₹{{ number_format($hsn['taxable_value'], 2) }}</td>
                    <td class="text-right font-mono">₹{{ number_format($hsn['cgst'], 2) }}</td>
                    <td class="text-right font-mono">₹{{ number_format($hsn['sgst'], 2) }}</td>
                    <td class="text-right font-mono">₹{{ number_format($hsn['igst'], 2) }}</td>
                    <td class="text-right font-mono font-bold">₹{{ number_format($hsn['total_value'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="color: #94a3b8; padding: 6px;">No HSN line items recorded.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 4. GSTR-2B Input Tax Credit Summary -->
    <div class="section-heading">4. GSTR-2B Inward Supplies & Input Tax Credit Ledger</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>PO Number</th>
                <th>Supplier Bill No</th>
                <th>Supplier Name</th>
                <th>Supplier GSTIN</th>
                <th class="text-right">Taxable (₹)</th>
                <th class="text-right">CGST (₹)</th>
                <th class="text-right">SGST (₹)</th>
                <th class="text-right">IGST (₹)</th>
                <th class="text-right">ITC Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($itcData['purchase_orders'] as $po)
                <tr>
                    <td class="font-mono">{{ $po['po_number'] }}</td>
                    <td class="font-mono">{{ $po['supplier_inv_no'] }}</td>
                    <td>{{ $po['supplier_name'] }}</td>
                    <td class="font-mono">{{ $po['supplier_gstin'] }}</td>
                    <td class="text-right font-mono">₹{{ number_format($po['taxable_value'], 2) }}</td>
                    <td class="text-right font-mono">₹{{ number_format($po['cgst'], 2) }}</td>
                    <td class="text-right font-mono">₹{{ number_format($po['sgst'], 2) }}</td>
                    <td class="text-right font-mono">₹{{ number_format($po['igst'], 2) }}</td>
                    <td class="text-right font-mono font-bold" style="color: #059669;">₹{{ number_format($po['itc_amount'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="color: #94a3b8; padding: 6px;">No Inward Purchase Orders recorded.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Return Filing Audit Log -->
    @if($filing)
        <div class="section-heading">5. Return Filing & Payment Audit Information</div>
        <table style="width: 100%; border: 1px solid #cbd5e1; padding: 6px; background-color: #f8fafc; font-size: 8px;">
            <tr>
                <td style="width: 25%;"><strong>GSTR-1 Status:</strong> {{ strtoupper($filing->gstr1_status) }}</td>
                <td style="width: 25%;"><strong>GSTR-3B Status:</strong> {{ strtoupper($filing->gstr3b_status) }}</td>
                <td style="width: 25%;"><strong>Tax Discharged:</strong> ₹{{ number_format($filing->tax_paid, 2) }}</td>
                <td style="width: 25%;"><strong>Challan No:</strong> {{ $filing->challan_no ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Filing Date:</strong> {{ $filing->filing_date?->format('d/m/Y') ?: 'N/A' }}</td>
                <td><strong>CIN:</strong> {{ $filing->cin_number ?: 'N/A' }}</td>
                <td><strong>Mode:</strong> {{ $filing->payment_mode ?: 'Online Portal' }}</td>
                <td><strong>Audit Notes:</strong> {{ $filing->notes ?: 'None' }}</td>
            </tr>
        </table>
    @endif

    <div class="footer">
        Generated automatically by CCTV CRM Enterprise Finance Engine on {{ now()->format('d M Y, h:i A') }} • Confidential Tax Document
    </div>

</body>
</html>
