<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunday Cash Payout Signature Sheet - {{ $paydaySunday->format('d M Y') }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 24px;
            color: #1e293b;
            background: #ffffff;
            font-size: 13px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 20px;
            font-weight: 900;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: -0.5px;
        }
        .subtitle {
            font-size: 12px;
            color: #64748b;
            margin: 0;
        }
        .kpi-box {
            text-align: right;
        }
        .kpi-title {
            font-size: 11px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }
        .kpi-value {
            font-size: 22px;
            font-weight: 900;
            color: #0f172a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 800;
            padding: 10px 8px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        td {
            padding: 10px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: monospace; }
        .sig-cell {
            width: 140px;
            height: 38px;
            border-bottom: 1px solid #0f172a;
        }
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 12px;
            color: #475569;
        }
        .auth-sign {
            border-top: 1px solid #0f172a;
            width: 200px;
            padding-top: 6px;
            text-align: center;
            font-weight: bold;
        }
        .print-btn {
            background: #2563eb;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 20px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px;">
        <button class="print-btn" onclick="window.print()">🖨️ Print This Payout Sheet</button>
        <button class="print-btn" style="background: #64748b;" onclick="window.close()">Close Window</button>
    </div>

    <div class="header">
        <div>
            <h1 class="title">Precision IT Systems &bull; SecureVision CRM</h1>
            <p class="subtitle">Weekly Labor Payout &amp; Cash Signature Sheet &bull; Sunday Payday</p>
            <p class="subtitle" style="margin-top: 3px;">
                <strong>Period:</strong> {{ $weekStart->format('d M Y') }} (Mon) to {{ $paydaySunday->copy()->subDay()->format('d M Y') }} (Sat) &bull; 
                <strong>Disbursement Date:</strong> {{ $paydaySunday->format('d M Y') }} (Sun)
            </p>
        </div>
        <div class="kpi-box">
            <div class="kpi-title">Total Cash Required</div>
            <div class="kpi-value font-mono">₹{{ number_format($totalCash, 2) }}</div>
            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">{{ count($rows) }} workers scheduled</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>Worker Name</th>
                <th>Phone</th>
                <th class="text-center">Days</th>
                <th class="text-center">Metres</th>
                <th class="text-right">Brought Fwd</th>
                <th class="text-right">Earned</th>
                <th class="text-right">Advance</th>
                <th class="text-right">Total Cash (₹)</th>
                <th class="text-center" style="width: 140px;">Worker Signature</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $idx => $r)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $r['worker']->name }}</td>
                    <td class="font-mono" style="font-size: 11px;">{{ $r['worker']->phone ?? '—' }}</td>
                    <td class="text-center font-bold">{{ $r['days_worked'] }}</td>
                    <td class="text-center">{{ $r['metres'] > 0 ? $r['metres'] . 'm' : '—' }}</td>
                    <td class="text-right font-mono" style="font-size: 11px;">{{ $r['brought_forward'] > 0 ? '₹' . number_format($r['brought_forward'], 2) : '—' }}</td>
                    <td class="text-right font-mono" style="font-size: 11px;">₹{{ number_format($r['earned'], 2) }}</td>
                    <td class="text-right font-mono" style="font-size: 11px;">{{ $r['advances'] > 0 ? '-₹' . number_format($r['advances'], 2) : '—' }}</td>
                    <td class="text-right font-bold font-mono" style="font-size: 14px;">₹{{ number_format($r['total_due'], 2) }}</td>
                    <td class="sig-cell"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 30px; color: #94a3b8;">
                        No pending wages for this week cycle.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f8fafc; font-weight: bold;">
                <td colspan="8" class="text-right uppercase">Grand Total Cash Payable:</td>
                <td class="text-right font-mono" style="font-size: 15px;">₹{{ number_format($totalCash, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <div>
            <p style="margin: 0 0 3px 0;">Generated by SecureVision CRM on {{ now()->format('d M Y H:i A') }}</p>
            <p style="margin: 0; font-size: 11px;">Precision IT Systems &bull; NP Solutions &bull; Linepix</p>
        </div>
        <div class="auth-sign">
            Managing Partner / Site Supervisor
        </div>
    </div>

</body>
</html>
