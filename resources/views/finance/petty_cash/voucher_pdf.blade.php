<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Petty Cash Voucher - {{ $transaction->voucher_no }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5px;
            line-height: 1.4;
            color: #1e293b;
            background: #ffffff;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header-title {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-sub {
            font-size: 9px;
            color: #475569;
            margin-top: 2px;
        }
        .voucher-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-advance { background: #dbeafe; color: #1d4ed8; }
        .badge-collection { background: #d1fae5; color: #047857; }
        .badge-expense { background: #ffe4e6; color: #be123c; }
        .badge-handover { background: #f3e8ff; color: #6b21a8; }
        .box {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 12px;
            background: #f8fafc;
        }
        table.meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.meta-table td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            color: #64748b;
            font-size: 8.5px;
            text-transform: uppercase;
        }
        .val {
            font-size: 10px;
            font-weight: 600;
            color: #0f172a;
        }
        .amount-box {
            border: 2px solid #0f172a;
            background: #ffffff;
            padding: 12px;
            text-align: center;
            margin: 15px 0;
            border-radius: 4px;
        }
        .amount-large {
            font-size: 18px;
            font-weight: 900;
            color: #0f172a;
        }
        .amount-words {
            font-size: 9px;
            color: #475569;
            font-style: italic;
            margin-top: 4px;
        }
        .sign-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .sign-box {
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding: 10px;
        }
        .sign-line {
            border-top: 1px solid #94a3b8;
            margin-top: 45px;
            padding-top: 5px;
            font-size: 8.5px;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="header-title">OFFICIAL PETTY CASH VOUCHER</div>
                <div class="header-sub">CCTV & Security Solutions CRM — Financial Operations</div>
            </td>
            <td style="width: 40%; text-align: right;">
                <div style="font-size: 12px; font-weight: bold; font-family: monospace; color: #1e293b;">
                    {{ $transaction->voucher_no }}
                </div>
                <div style="font-size: 8.5px; color: #64748b; margin-top: 2px;">
                    Date: <strong>{{ $transaction->transaction_date ? $transaction->transaction_date->format('d F Y') : now()->format('d F Y') }}</strong>
                </div>
                <div style="margin-top: 4px;">
                    <span class="voucher-badge badge-advance">
                        {{ strtoupper(str_replace('_', ' ', $transaction->transaction_type)) }} — {{ strtoupper(str_replace('_', ' ', $transaction->category)) }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Meta Details Box -->
    <div class="box">
        <table class="meta-table">
            <tr>
                <td style="width: 25%;"><span class="label">Primary Account:</span></td>
                <td style="width: 35%;" class="val">{{ $transaction->account->name ?? 'Head Safe' }}</td>
                <td style="width: 20%;"><span class="label">Custodian:</span></td>
                <td style="width: 20%;" class="val">{{ $transaction->account->custodian->name ?? 'Office Cashier' }}</td>
            </tr>
            @if($transaction->destination_account_id)
            <tr>
                <td><span class="label">Transfer Destination:</span></td>
                <td class="val">{{ $transaction->destinationAccount->name ?? '—' }}</td>
                <td><span class="label">Holder:</span></td>
                <td class="val">{{ $transaction->destinationAccount->custodian->name ?? '—' }}</td>
            </tr>
            @endif
            @if($transaction->vendor_payee_name)
            <tr>
                <td><span class="label">Payee / Customer / Party:</span></td>
                <td colspan="3" class="val">{{ $transaction->vendor_payee_name }}</td>
            </tr>
            @endif
            @if($transaction->related_job_id || $transaction->related_invoice_id || $transaction->related_ticket_id)
            <tr>
                <td><span class="label">Linked Reference:</span></td>
                <td colspan="3" class="val">
                    @if($transaction->job) Job: #{{ $transaction->job->job_no ?? $transaction->job->id }} @endif
                    @if($transaction->ticket) | Ticket: #{{ $transaction->ticket->ticket_no ?? $transaction->ticket->id }} @endif
                    @if($transaction->invoice) | Invoice: #{{ $transaction->invoice->invoice_number ?? $transaction->invoice->id }} @endif
                </td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Amount Display Box -->
    <div class="amount-box">
        <div style="font-size: 9px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 2px;">Voucher Cash Amount</div>
        <div class="amount-large">₹ {{ number_format($transaction->amount, 2) }}</div>
        <div class="amount-words">
            (Amount: Rupees {{ number_format($transaction->amount, 2) }} Only)
        </div>
    </div>

    <!-- Narration / Notes Box -->
    <div class="box">
        <div class="label" style="margin-bottom: 4px;">Particulars / Purpose / Narration:</div>
        <div style="font-size: 10px; color: #0f172a; line-height: 1.5;">
            {{ $transaction->notes ?? 'No specific notes recorded.' }}
        </div>
        @if($transaction->receipt_photo_path)
            <div style="margin-top: 6px; font-size: 8.5px; color: #059669; font-weight: 600;">
                ✓ Physical Receipt Attached & Digitally Stored (Ref: {{ basename($transaction->receipt_photo_path) }})
            </div>
        @endif
    </div>

    <!-- Signatures -->
    <table class="sign-table">
        <tr>
            <td class="sign-box">
                <div class="sign-line">Disbursed / Handed By<br><span style="font-weight: normal; font-size: 8px;">({{ $transaction->user->name ?? 'Staff' }})</span></div>
            </td>
            <td class="sign-box">
                <div class="sign-line">Received / Claimed By<br><span style="font-weight: normal; font-size: 8px;">({{ $transaction->vendor_payee_name ?? $transaction->destinationAccount->custodian->name ?? 'Recipient' }})</span></div>
            </td>
            <td class="sign-box">
                <div class="sign-line">Finance Approver<br><span style="font-weight: normal; font-size: 8px;">({{ $transaction->approvedBy->name ?? 'Authorized Officer' }})</span></div>
            </td>
        </tr>
    </table>

    <div style="text-align: center; font-size: 7.5px; color: #94a3b8; margin-top: 30px;">
        Generated via CCTV CRM on {{ $generatedAt ?? now()->format('d M Y, h:i A') }}.
    </div>

</body>
</html>
