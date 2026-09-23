<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip - {{ $payroll->payroll_number }} - {{ $payroll->user?->name }}</title>
    <style>
        @page {
            margin: 28px 32px;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1e293b;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2.5px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .company-sub {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 2px;
        }
        .payslip-badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 4px;
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
        }
        .info-grid td {
            padding: 8px 10px;
            vertical-align: top;
            font-size: 9px;
        }
        .info-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            display: block;
            margin-bottom: 2px;
        }
        .info-val {
            font-size: 10.5px;
            font-weight: bold;
            color: #0f172a;
        }
        .attendance-strip {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            text-align: center;
        }
        .attendance-strip th {
            padding: 6px 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            background: #e2e8f0;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .attendance-strip td {
            padding: 6px 4px;
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            border: 1px solid #cbd5e1;
        }
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .breakdown-table th {
            padding: 8px 10px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #ffffff;
        }
        .th-earnings {
            background-color: #1e40af;
        }
        .th-deductions {
            background-color: #991b1b;
        }
        .breakdown-table td {
            padding: 6px 10px;
            font-size: 9.5px;
            border-bottom: 1px solid #e2e8f0;
        }
        .subtotal-row td {
            font-weight: bold;
            background-color: #f8fafc;
            border-top: 1.5px solid #cbd5e1;
        }
        .net-pay-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: #ecfdf5;
            border: 2px solid #059669;
        }
        .net-pay-box td {
            padding: 12px 14px;
        }
        .net-val {
            font-size: 20px;
            font-weight: bold;
            color: #065f46;
        }
        .bank-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .bank-info td {
            padding: 6px 10px;
            font-size: 8.5px;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
        }
        .sig-line {
            border-top: 1px solid #64748b;
            padding-top: 4px;
            font-size: 8.5px;
            font-weight: bold;
            color: #475569;
            text-align: center;
        }
        .footer {
            margin-top: 24px;
            border-top: 1px solid #cbd5e1;
            padding-top: 6px;
            font-size: 7.5px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- Company Letterhead --}}
    <table class="header-table">
        <tr>
            <td style="width: 65%;">
                <div class="company-name">CCTV CRM &amp; SECURITY SYSTEMS</div>
                <div class="company-sub">Electronic Surveillance &amp; Technical Services Private Limited</div>
                <div class="company-sub">GSTIN: 33AAAAA0000A1Z5 | Corporate Reg: DL-772910</div>
                <div class="company-sub">Phone: +91 87890 76658 | Email: accounts@cctvcrm.com</div>
            </td>
            <td style="width: 35%; text-align: right; vertical-align: top;">
                <span class="payslip-badge">PAYSLIP &bull; {{ strtoupper($payroll->status) }}</span>
                <div style="font-size: 13px; font-weight: bold; color: #0f172a; margin-top: 6px;">{{ $payroll->payroll_number }}</div>
                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">
                    Date of Issue: {{ now()->format('d M Y') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Employee & Pay Cycle Master Details --}}
    <table class="info-grid">
        <tr>
            <td style="width: 25%; border-right: 1px solid #cbd5e1;">
                <span class="info-label">Employee Name</span>
                <div class="info-val">{{ $payroll->user?->name }}</div>
                <div style="font-size: 8.5px; color: #64748b;">{{ $payroll->user?->email }}</div>
            </td>
            <td style="width: 25%; border-right: 1px solid #cbd5e1;">
                <span class="info-label">Designation / Role</span>
                <div class="info-val">{{ ucfirst($payroll->user?->role) }}</div>
                <div style="font-size: 8.5px; color: #64748b;">Operations &amp; Security</div>
            </td>
            <td style="width: 25%; border-right: 1px solid #cbd5e1;">
                <span class="info-label">Pay Period</span>
                <div class="info-val">{{ $payroll->formatted_period }}</div>
                <div style="font-size: 8.5px; color: #64748b;">Cycle: {{ strtoupper($payroll->period_type) }}</div>
            </td>
            <td style="width: 25%;">
                <span class="info-label">Payment Status</span>
                <div class="info-val" style="color: {{ $payroll->status === 'paid' ? '#059669' : ($payroll->status === 'approved' ? '#2563eb' : '#d97706') }};">
                    {{ strtoupper($payroll->status) }}
                </div>
                <div style="font-size: 8.5px; color: #64748b;">
                    {{ $payroll->payment_date ? \Carbon\Carbon::parse($payroll->payment_date)->format('d M Y') : 'Pending Payout' }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Attendance & Shift Record --}}
    <table class="attendance-strip">
        <thead>
            <tr>
                <th>Total Working Days</th>
                <th>Present Days</th>
                <th>Half Days</th>
                <th>Approved Leaves</th>
                <th>Loss of Pay (Absent)</th>
                <th>Overtime Hours</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $payroll->working_days }}</td>
                <td style="color: #059669;">{{ $payroll->present_days }}</td>
                <td style="color: #0284c7;">{{ $payroll->half_days }}</td>
                <td style="color: #7c3aed;">{{ $payroll->leave_days }}</td>
                <td style="color: {{ $payroll->absent_days > 0 ? '#dc2626' : '#64748b' }};">{{ $payroll->absent_days }}</td>
                <td style="color: #2563eb;">{{ $payroll->overtime_hours }} hrs</td>
            </tr>
        </tbody>
    </table>

    {{-- Earnings and Deductions Table --}}
    @php
        $salaryMaster = $payroll->user?->salaryStructure;
        $totalEarnings = (float) $payroll->basic_pay + (float) $payroll->overtime_pay + (float) $payroll->allowances;
        $totalDeductions = (float) $payroll->deductions;
    @endphp

    <table class="breakdown-table">
        <thead>
            <tr>
                <th class="th-earnings" style="width: 32%;">Earnings Component</th>
                <th class="th-earnings" style="width: 18%; text-align: right;">Amount (&#8377;)</th>
                <th class="th-deductions" style="width: 32%; border-left: 2px solid #ffffff;">Deductions &amp; Taxes</th>
                <th class="th-deductions" style="width: 18%; text-align: right;">Amount (&#8377;)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Salary &amp; HRA</td>
                <td style="text-align: right; font-weight: bold;">&#8377;{{ number_format((float) $payroll->basic_pay, 2) }}</td>
                <td style="border-left: 1px solid #cbd5e1;">Loss of Pay (LWP / Absenteeism)</td>
                <td style="text-align: right; color: #dc2626;">&#8377;{{ number_format((float) $payroll->deductions, 2) }}</td>
            </tr>
            <tr>
                <td>Overtime Compensation ({{ $payroll->overtime_hours }} hrs)</td>
                <td style="text-align: right;">&#8377;{{ number_format((float) $payroll->overtime_pay, 2) }}</td>
                <td style="border-left: 1px solid #cbd5e1;">Provident Fund / PT (Statutory)</td>
                <td style="text-align: right; color: #64748b;">&#8377;0.00</td>
            </tr>
            <tr>
                <td>Travel &amp; Special Allowances</td>
                <td style="text-align: right;">&#8377;{{ number_format((float) $payroll->allowances, 2) }}</td>
                <td style="border-left: 1px solid #cbd5e1;">Salary Advances / Other</td>
                <td style="text-align: right; color: #64748b;">&#8377;0.00</td>
            </tr>
            <tr class="subtotal-row">
                <td style="color: #1e40af;">Total Gross Earnings (A)</td>
                <td style="text-align: right; color: #1e40af;">&#8377;{{ number_format($totalEarnings, 2) }}</td>
                <td style="color: #991b1b; border-left: 1px solid #cbd5e1;">Total Deductions (B)</td>
                <td style="text-align: right; color: #991b1b;">&#8377;{{ number_format($totalDeductions, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Net Pay Highlight Box --}}
    <table class="net-pay-box">
        <tr>
            <td style="width: 60%;">
                <span class="info-label" style="color: #065f46;">Net Payable Salary (Gross - Deductions)</span>
                <div style="font-size: 8.5px; color: #047857; margin-top: 2px;">
                    Take-home pay calculated as per biometric/daily attendance records.
                </div>
            </td>
            <td style="width: 40%; text-align: right;">
                <div class="net-val">&#8377;{{ number_format((float) $payroll->net_salary, 2) }}</div>
                <div style="font-size: 8px; color: #065f46; font-weight: bold; text-transform: uppercase;">Indian Rupees Only</div>
            </td>
        </tr>
    </table>

    {{-- Bank & Disbursal Information --}}
    @if($salaryMaster)
        <table class="bank-info">
            <tr>
                <td style="width: 25%;">
                    <span class="info-label">Disbursal Mode</span>
                    <strong>{{ ucfirst($salaryMaster->payment_method ?? 'Bank Transfer') }}</strong>
                </td>
                <td style="width: 25%;">
                    <span class="info-label">Bank Name</span>
                    <strong>{{ $salaryMaster->bank_name ?: 'HDFC Bank' }}</strong>
                </td>
                <td style="width: 25%;">
                    <span class="info-label">Account Number</span>
                    <strong>{{ $salaryMaster->bank_account_number ? '••••' . substr($salaryMaster->bank_account_number, -4) : 'Recorded on File' }}</strong>
                </td>
                <td style="width: 25%;">
                    <span class="info-label">Transaction / UTR Ref</span>
                    <strong>{{ $payroll->payment_reference ?: 'Generated on Payout' }}</strong>
                </td>
            </tr>
        </table>
    @endif

    {{-- Authorization & Signatures --}}
    <table class="signature-table">
        <tr>
            <td style="width: 40%; text-align: center;">
                <div style="height: 35px;"></div>
                <div class="sig-line">Employee Signature / Acknowledgment</div>
            </td>
            <td style="width: 20%;"></td>
            <td style="width: 40%; text-align: center;">
                <div style="font-size: 9px; font-weight: bold; color: #059669; height: 35px; line-height: 35px;">
                    ✓ SECUREVISION CERTIFIED
                </div>
                <div class="sig-line">Authorized Signatory &bull; HR / Accounts</div>
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div class="footer">
        This is a computer-generated official payroll slip generated by CCTV Security CRM &middot; No physical signature required &middot; Confidential &middot; Page 1 of 1
    </div>

</body>
</html>
