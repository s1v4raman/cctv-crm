<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $rma->rma_no }} - Vendor RMA Delivery Challan</title>
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
        .rma-badge {
            background-color: #4f46e5;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 4px;
            text-align: right;
            display: inline-block;
        }
        .address-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .address-box {
            width: 48%;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            background: #f8fafc;
            vertical-align: top;
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
            border-left: 3px solid #4f46e5;
            margin-top: 12px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .items-table th {
            background-color: #0f172a;
            color: #ffffff;
            padding: 6px 8px;
            text-align: left;
            font-size: 8.5px;
            text-transform: uppercase;
        }
        .items-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 9px;
            vertical-align: top;
        }
        .sig-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }
        .sig-box {
            border: 1px solid #cbd5e1;
            background-color: #fafafa;
            padding: 10px;
            height: 75px;
            vertical-align: top;
            width: 48%;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: middle;">
                <div class="header-logo-text">CCTV Security Systems CRM</div>
                <div class="header-sub">Hardware Return Merchandise Authorization (RMA) & Warranty Claim Slip</div>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: middle;">
                <span class="rma-badge">RMA DISPATCH CHALLAN</span>
                <div style="font-size: 9px; color: #64748b; margin-top: 4px;">
                    <strong>RMA #:</strong> {{ $rma->rma_no }}<br>
                    <strong>Date:</strong> {{ $rma->created_at->format('d M Y') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Address Blocks --}}
    <table class="address-table">
        <tr>
            <td class="address-box">
                <span class="info-label">Consignor / Dispatched By:</span>
                <strong>CCTV Security Engineering Service Dept.</strong><br>
                <span>Tech Support & Hardware RMA Lab</span><br>
                <span>Phone: +91 98765 43210</span><br>
                <span>Email: service@cctvcrm.com</span>
            </td>
            <td style="width: 4%;"></td>
            <td class="address-box">
                <span class="info-label">Vendor Service Center / Ship To:</span>
                <strong>{{ $rma->supplier->name }}</strong><br>
                @if($rma->supplier->company_name)
                    <span>{{ $rma->supplier->company_name }}</span><br>
                @endif
                <span>{{ $rma->supplier->address ?: 'Vendor Service Center' }}</span><br>
                <span>{{ $rma->supplier->city }}, {{ $rma->supplier->state }}</span><br>
                <span>Contact: {{ $rma->supplier->contact_person ?? $rma->supplier->phone }}</span>
            </td>
        </tr>
    </table>

    {{-- Hardware Item Table --}}
    <div class="section-heading">Hardware Asset & Defect Specification</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 30%;">Product Model / Brand</th>
                <th style="width: 25%;">Hardware Identifiers (S/N)</th>
                <th style="width: 25%;">Failure Category</th>
                <th style="width: 20%; text-align: center;">Warranty Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $rma->product?->name ?? 'Hardware Asset' }}</strong><br>
                    <span style="color: #64748b; font-size: 8px;">Model: {{ $rma->product?->model_no ?? 'N/A' }}</span>
                </td>
                <td>
                    <strong>S/N:</strong> <span style="font-family: monospace;">{{ $rma->faulty_serial_number }}</span><br>
                    @if($rma->faulty_mac_address)
                        <span style="color: #64748b; font-size: 8px;">MAC: {{ $rma->faulty_mac_address }}</span>
                    @endif
                </td>
                <td>
                    <strong>{{ $rma->fault_category_label }}</strong>
                </td>
                <td style="text-align: center;">
                    <span style="font-weight: bold; color: {{ $rma->warranty_status_at_claim === 'under_warranty' ? '#16a34a' : '#dc2626' }}; text-transform: uppercase;">
                        {{ str_replace('_', ' ', $rma->warranty_status_at_claim) }}
                    </span>
                </td>
            </tr>
            <tr>
                <td colspan="4" style="background-color: #fafafa;">
                    <span class="info-label">Customer Site / Client Link:</span>
                    <span>{{ $rma->lead ? $rma->lead->customer_name . ' (' . ($rma->lead->site_address ?? $rma->lead->phone) . ')' : 'Direct Warehouse Inventory Stock' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="4" style="background-color: #ffffff;">
                    <span class="info-label">Detailed Symptoms & Engineering Diagnosis:</span>
                    <div style="font-size: 9px; line-height: 1.5;">
                        {{ $rma->issue_description }}
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Logistics & Courier Information --}}
    <div class="section-heading">Logistics & Courier Dispatch Details</div>
    <table class="address-table">
        <tr>
            <td style="padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 9px; width: 33.3%;">
                <span class="info-label">Courier Carrier</span>
                <strong>{{ $rma->shipping_courier ?: 'To be assigned' }}</strong>
            </td>
            <td style="padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 9px; width: 33.3%;">
                <span class="info-label">AWB / Consignment Tracking #</span>
                <strong style="font-family: monospace;">{{ $rma->tracking_number ?: 'N/A' }}</strong>
            </td>
            <td style="padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 9px; width: 33.3%;">
                <span class="info-label">Dispatched Date</span>
                <strong>{{ $rma->dispatched_date ? $rma->dispatched_date->format('d M Y') : now()->format('d M Y') }}</strong>
            </td>
        </tr>
    </table>

    {{-- Signatures --}}
    <table class="sig-table">
        <tr>
            <td class="sig-box">
                <span class="info-label">Authorized Sender Signature</span>
                <div style="margin-top: 35px; border-top: 1px dashed #94a3b8; padding-top: 4px; font-size: 8.5px;">
                    <strong>{{ $rma->creator->name }}</strong> (Operations & RMA Dispatch)
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td class="sig-box">
                <span class="info-label">Vendor Service Center Receiver Seal & Sign</span>
                <div style="margin-top: 35px; border-top: 1px dashed #94a3b8; padding-top: 4px; font-size: 8.5px; color: #64748b;">
                    Received By (Name & Signature) / Date: ____________
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
