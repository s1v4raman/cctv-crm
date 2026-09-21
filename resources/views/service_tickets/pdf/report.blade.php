<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Service Tickets Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
        }
        .brand-title {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
        }
        .brand-subtitle {
            font-size: 11px;
            color: #64748b;
        }
        .report-meta {
            text-align: right;
            font-size: 9px;
            color: #475569;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 6px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        .table td {
            padding: 6px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
            vertical-align: top;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-critical { background: #fee2e2; color: #991b1b; }
        .badge-high { background: #ffedd5; color: #9a3412; }
        .badge-medium { background: #fef9c3; color: #854d0e; }
        .badge-low { background: #f1f5f9; color: #475569; }
        .badge-open { background: #e0f2fe; color: #0369a1; }
        .badge-in_progress { background: #fef3c7; color: #b45309; }
        .badge-resolved { background: #dcfce7; color: #15803d; }
        .badge-closed { background: #f1f5f9; color: #475569; }
        .badge-cancelled { background: #f3f4f6; color: #6b7280; }
        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="brand-title">CCTV CRM</div>
                <div class="brand-subtitle">Service & Breakdown Tickets Report</div>
            </td>
            <td class="report-meta">
                <div><strong>Generated On:</strong> {{ now()->format('d M Y, h:i A') }}</div>
                <div><strong>Total Records:</strong> {{ $tickets->count() }}</div>
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 12%;">Ticket No</th>
                <th style="width: 20%;">Customer & Phone</th>
                <th style="width: 23%;">Issue & Title</th>
                <th style="width: 12%;">Priority</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 13%;">Technician</th>
                <th style="width: 8%;">Cost</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
                <tr>
                    <td>
                        <strong>{{ $ticket->ticket_no }}</strong><br>
                        <span style="color: #64748b; font-size: 8px;">{{ $ticket->created_at?->format('d M Y') }}</span>
                    </td>
                    <td>
                        <strong>{{ $ticket->lead?->customer_name ?? 'N/A' }}</strong><br>
                        <span style="color: #475569;">{{ $ticket->lead?->phone ?? '-' }}</span><br>
                        <span style="color: #64748b; font-size: 8px;">{{ Str::limit($ticket->lead?->site_address ?? '', 35) }}</span>
                    </td>
                    <td>
                        <strong>{{ $ticket->title }}</strong><br>
                        <span style="color: #64748b;">{{ $ticket->issue_type_label }}</span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $ticket->priority }}">{{ $ticket->priority_label }}</span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $ticket->status }}">{{ $ticket->status_label }}</span>
                    </td>
                    <td>
                        {{ $ticket->assignedTechnician?->name ?? 'Unassigned' }}
                    </td>
                    <td>
                        ₹{{ number_format((float) ($ticket->cost ?? 0), 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: #64748b;">No service tickets found matching criteria.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        CCTV CRM Support System • Confidential Document • Page generated on {{ now()->format('Y-m-d H:i:s') }}
    </div>
</body>
</html>
