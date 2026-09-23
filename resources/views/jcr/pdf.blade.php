<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $jobCompletionReport->report_no }} - Job Completion Certificate</title>
    <style>
        @page {
            margin: 28px 32px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1e293b;
            background: #ffffff;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header-logo-text {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-sub {
            font-size: 9px;
            color: #475569;
            font-weight: 500;
        }
        .cert-badge {
            background-color: #10b981;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 4px;
            text-align: right;
            display: inline-block;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .info-table td {
            padding: 6px 8px;
            vertical-align: top;
            border: 1px solid #e2e8f0;
            font-size: 9.5px;
        }
        .info-label {
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            font-size: 8px;
            display: block;
            margin-bottom: 2px;
        }
        .section-heading {
            font-size: 10.5px;
            font-weight: bold;
            color: #0f172a;
            background-color: #f1f5f9;
            padding: 5px 8px;
            border-left: 3px solid #6366f1;
            margin-top: 12px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .checklist-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .checklist-table th {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            text-align: left;
            font-size: 8.5px;
            color: #475569;
            text-transform: uppercase;
        }
        .checklist-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            font-size: 9px;
        }
        .badge-pass {
            background-color: #dcfce7;
            color: #166534;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8.5px;
        }
        .sig-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        .sig-box {
            border: 1px solid #cbd5e1;
            background-color: #fafafa;
            padding: 10px;
            height: 90px;
            vertical-align: top;
            width: 48%;
        }
        .sig-image {
            max-height: 55px;
            max-width: 200px;
            display: block;
            margin: 0 auto;
        }
        .feedback-box {
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            padding: 8px 10px;
            border-radius: 4px;
            margin-bottom: 12px;
            font-size: 9px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: middle;">
                <div class="header-logo-text">CCTV Security Systems</div>
                <div class="header-sub">Certified Digital Job Handover & Service Completion Report</div>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: middle;">
                <span class="cert-badge">✓ CERTIFIED COMPLETION</span>
                <div style="font-size: 9px; color: #64748b; margin-top: 4px;">
                    <strong>Report #:</strong> {{ $jobCompletionReport->report_no }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Project & Customer Metadata --}}
    <table class="info-table">
        <tr>
            <td style="width: 33.3%;">
                <span class="info-label">Customer / Site Name</span>
                <strong>{{ $jobCompletionReport->lead->customer_name }}</strong>
            </td>
            <td style="width: 33.3%;">
                <span class="info-label">Service Type & Ref</span>
                <strong>{{ $jobCompletionReport->job_type_label }}</strong>
                <div style="font-size: 8.5px; color: #64748b;">Ref: {{ $jobCompletionReport->reference_no }}</div>
            </td>
            <td style="width: 33.3%;">
                <span class="info-label">Completion Date & Time</span>
                <strong>{{ $jobCompletionReport->completion_date->format('d M Y, h:i A') }}</strong>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="info-label">Site Address</span>
                {{ $jobCompletionReport->lead->site_address ?? $jobCompletionReport->lead->address ?? 'N/A' }}
            </td>
            <td>
                <span class="info-label">Lead Attending Technician</span>
                <strong>{{ $jobCompletionReport->technician->name }}</strong>
            </td>
        </tr>
    </table>

    {{-- Work Summary --}}
    @if($jobCompletionReport->work_summary)
        <div class="section-heading">Scope of Work & Observations</div>
        <div style="padding: 6px 8px; border: 1px solid #e2e8f0; background: #fafafa; font-size: 9px; line-height: 1.5; margin-bottom: 10px;">
            {{ $jobCompletionReport->work_summary }}
        </div>
    @endif

    {{-- Quality Handover Checklist --}}
    <div class="section-heading">
        Quality Assurance & Operational Verification Checklist 
        <span style="font-size: 8.5px; float: right; color: #10b981; font-weight: bold;">
            {{ $jobCompletionReport->passed_checklist_count }} of 8 Passed ({{ $jobCompletionReport->checklist_percentage }}%)
        </span>
    </div>

    <table class="checklist-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 45%;">Inspection Point</th>
                <th style="width: 35%;">Standard Operational Verification</th>
                <th style="width: 15%; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $auditItems = [
                    [1, 'Camera Physical Mounting & Alignment', 'All cameras firmly anchored, angles optimized & weather-sealed', $jobCompletionReport->all_cameras_positioned],
                    [2, 'Video Recording & Storage Config', 'Continuous / motion recording configured & verified on HDD', $jobCompletionReport->recording_configured],
                    [3, 'Mobile App Remote Viewing', 'App installed on client phone with live feeds streaming', $jobCompletionReport->remote_mobile_app_setup],
                    [4, 'Power Supply & Backup Integrity', 'Power distribution box, 12V DC regulators & UPS tested', $jobCompletionReport->power_backup_tested],
                    [5, 'Cable Dressing & Casing Trunking', 'Cables neatly harnessed, trunked with no hanging wires', $jobCompletionReport->cables_dressed_and_trunked],
                    [6, 'Client Control & Playback Training', 'Client trained on live view, video search, export & password', $jobCompletionReport->client_training_completed],
                    [7, 'Job Site Cleanliness', 'Installation area swept, drill dust cleaned, waste disposed', $jobCompletionReport->work_area_cleaned],
                    [8, 'Handover Documentation & Warranty', 'System credentials recorded and warranty terms handed over', $jobCompletionReport->warranty_card_handed],
                ];
            @endphp

            @foreach($auditItems as $item)
                <tr>
                    <td>{{ $item[0] }}</td>
                    <td><strong>{{ $item[1] }}</strong></td>
                    <td style="color: #64748b;">{{ $item[2] }}</td>
                    <td style="text-align: center;">
                        @if($item[3])
                            <span class="badge-pass">✓ PASSED</span>
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Customer Rating & Feedback --}}
    <div class="feedback-box">
        <strong>Customer Satisfaction Rating: </strong>
        @for($i = 1; $i <= 5; $i++)
            <span style="color: #f59e0b; font-size: 11px;">{{ $i <= $jobCompletionReport->customer_rating ? '★' : '☆' }}</span>
        @endfor
        <span style="font-weight: bold; color: #78350f;"> ({{ $jobCompletionReport->customer_rating }} / 5 Stars)</span>
        @if($jobCompletionReport->customer_feedback)
            <div style="margin-top: 4px; font-style: italic; color: #451a03;">
                "{{ $jobCompletionReport->customer_feedback }}"
            </div>
        @endif
    </div>

    {{-- Digital Signatures --}}
    <div class="section-heading">Signatures & Handover Authorization</div>
    <table class="sig-table">
        <tr>
            <td class="sig-box">
                <span class="info-label">Client / Customer Authorized Sign-Off</span>
                <div style="text-align: center; margin-top: 6px;">
                    @if(str_starts_with($jobCompletionReport->customer_signature ?? '', 'data:image/png') || str_starts_with($jobCompletionReport->customer_signature ?? '', 'data:image/jpeg') || str_starts_with($jobCompletionReport->customer_signature ?? '', 'http'))
                        <img src="{{ $jobCompletionReport->customer_signature }}" class="sig-image" alt="Customer Signature">
                    @elseif($jobCompletionReport->customer_signature)
                        <div style="font-family: 'Times New Roman', serif; font-style: italic; font-size: 16px; font-weight: bold; color: #1e3a8a; padding: 8px 0;">{{ $jobCompletionReport->signer_name }}</div>
                    @else
                        <span style="color: #94a3b8; font-style: italic;">Electronically Authorized</span>
                    @endif
                </div>
                <div style="font-size: 9px; margin-top: 6px; border-top: 1px solid #e2e8f0; padding-top: 4px;">
                    <strong>{{ $jobCompletionReport->signer_name }}</strong>
                    <span style="color: #64748b; font-size: 8px; display: block;">{{ $jobCompletionReport->signer_designation ?? 'Authorized Signer' }} ({{ $jobCompletionReport->signer_phone ?? '' }})</span>
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td class="sig-box">
                <span class="info-label">Attending Lead Technician</span>
                <div style="text-align: center; margin-top: 10px;">
                    <div style="font-size: 12px; font-weight: bold; color: #0f172a;">{{ $jobCompletionReport->technician->name }}</div>
                    <div style="font-size: 8.5px; color: #64748b;">Certified Field Service Engineer</div>
                    <div style="font-size: 8px; color: #10b981; font-weight: bold; margin-top: 4px;">✓ IDENTITY & WORK VERIFIED</div>
                </div>
                <div style="font-size: 9px; margin-top: 14px; border-top: 1px solid #e2e8f0; padding-top: 4px;">
                    <span style="color: #64748b; font-size: 8px; display: block;">Signed on: {{ $jobCompletionReport->completion_date->format('d M Y, h:i A') }}</span>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
