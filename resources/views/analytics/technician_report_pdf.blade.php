<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Technician Performance & Field Operations Report</title>
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
        .badge-info { background: #e0e7ff; color: #3730a3; }
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
                <div class="header-title">CCTV CRM &bull; Field Operations Scorecard</div>
                <div class="header-sub">Technician Performance, First-Time Fix Rate & Resolution Efficiency</div>
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
                <span class="kpi-label">First-Time Fix Rate (FTFR)</span>
                <div class="kpi-val" style="color: #15803d;">{{ $data['summary']['team_avg_ftfr'] }}%</div>
                <div style="font-size: 7.5px; color: #64748b; margin-top: 2px;">No 30-day repeat call</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">Mean Resolution Time (MTTR)</span>
                <div class="kpi-val" style="color: #1d4ed8;">{{ $data['summary']['team_avg_resolution_format'] }}</div>
                <div style="font-size: 7.5px; color: #64748b; margin-top: 2px;">Average turnaround</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">Completed Installations</span>
                <div class="kpi-val" style="color: #4338ca;">{{ $data['summary']['total_jobs_completed'] }} Jobs</div>
                <div style="font-size: 7.5px; color: #64748b; margin-top: 2px;">+ {{ $data['summary']['total_tickets_resolved'] }} tickets resolved</div>
            </td>
            <td class="kpi-box">
                <span class="kpi-label">Team CSAT Score</span>
                <div class="kpi-val" style="color: #b45309;">⭐ {{ $data['summary']['team_avg_csat'] }} / 5.0</div>
                <div style="font-size: 7.5px; color: #64748b; margin-top: 2px;">Digital JCR sign-offs</div>
            </td>
        </tr>
    </table>

    {{-- Field Engineer Leaderboard Table --}}
    <div class="section-heading">Engineer Performance Leaderboard</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Engineer</th>
                <th style="width: 12%; text-align: center;">FTFR (%)</th>
                <th style="width: 14%; text-align: center;">Avg MTTR</th>
                <th style="width: 12%; text-align: center;">Jobs Done</th>
                <th style="width: 12%; text-align: center;">Tickets Resolved</th>
                <th style="width: 10%; text-align: center;">JCR Signed</th>
                <th style="width: 15%; text-align: right;">Customer CSAT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['technicians'] as $idx => $tech)
                <tr>
                    <td>
                        <strong>#{{ $idx + 1 }} {{ $tech['name'] }}</strong>
                        <div style="font-size: 7.5px; color: #64748b;">{{ ucfirst($tech['role']) }} &bull; {{ $tech['email'] }}</div>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge {{ $tech['first_time_fix_rate'] >= 80 ? 'badge-success' : 'badge-warning' }}">
                            {{ $tech['first_time_fix_rate'] }}%
                        </span>
                        <div style="font-size: 7px; color: #64748b;">{{ $tech['first_time_fix_count'] }}/{{ $tech['tickets_resolved'] }}</div>
                    </td>
                    <td style="text-align: center; font-weight: bold; color: #1d4ed8;">
                        {{ $tech['avg_resolution_formatted'] }}
                    </td>
                    <td style="text-align: center; font-weight: bold; color: #4338ca;">
                        {{ $tech['jobs_completed'] }} <span style="font-size: 7px; color: #64748b;">/ {{ $tech['jobs_total'] }}</span>
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $tech['tickets_resolved'] }} <span style="font-size: 7px; color: #64748b;">/ {{ $tech['tickets_total'] }}</span>
                    </td>
                    <td style="text-align: center;">
                        {{ $tech['jcr_signoffs'] }}
                    </td>
                    <td style="text-align: right; font-weight: bold; color: #b45309;">
                        ⭐ {{ $tech['average_rating'] }} / 5.0
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 12px;">No technician records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Confidential &bull; CCTV CRM Enterprise Analytics &bull; Generated Automatically
    </div>

</body>
</html>
