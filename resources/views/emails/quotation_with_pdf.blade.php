<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation #{{ $quotation->quotation_no }}</title>
    <style>
        body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; color: #1e293b; }
        .wrapper { width: 100%; background-color: #f1f5f9; padding: 30px 10px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 28px 24px; color: #ffffff; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: 0.5px; }
        .header p { margin: 6px 0 0; font-size: 13px; color: #94a3b8; }
        .content { padding: 28px 24px; }
        .greeting { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 12px; }
        .intro-text { font-size: 13px; line-height: 1.6; color: #475569; margin-bottom: 20px; }
        .custom-msg { background-color: #f8fafc; border-left: 4px solid #6366f1; padding: 14px 16px; border-radius: 0 8px 8px 0; margin-bottom: 22px; font-size: 13px; color: #334155; line-height: 1.5; white-space: pre-line; }
        .summary-card { background-color: #faf5ff; border: 1px solid #e9d5ff; border-radius: 10px; padding: 18px 20px; margin-bottom: 24px; }
        .summary-card h3 { margin: 0 0 12px; font-size: 14px; font-weight: 800; color: #6b21a8; text-transform: uppercase; letter-spacing: 0.5px; }
        .summary-row { display: flex; justify-content: space-between; font-size: 13px; padding: 5px 0; color: #475569; }
        .summary-row.total { border-top: 1px dashed #c084fc; margin-top: 8px; padding-top: 10px; font-size: 16px; font-weight: 800; color: #0f172a; }
        .attachment-notice { background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 12px 16px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; }
        .attachment-icon { font-size: 20px; }
        .attachment-text { font-size: 12px; font-weight: 600; color: #065f46; }
        .cta-center { text-align: center; margin: 26px 0; }
        .btn-portal { display: inline-block; background-color: #4f46e5; color: #ffffff !important; padding: 13px 28px; border-radius: 8px; font-size: 13px; font-weight: 700; text-decoration: none; box-shadow: 0 2px 4px rgba(79, 70, 229, 0.3); }
        .footer { background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 24px; text-align: center; font-size: 11px; color: #94a3b8; line-height: 1.5; }
        .footer strong { color: #475569; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            
            {{-- Header --}}
            <div class="header">
                <h1>{{ config('app.name', 'CCTV Operations CRM') }}</h1>
                <p>Commercial & Industrial Security Solutions</p>
            </div>

            {{-- Content --}}
            <div class="content">
                <div class="greeting">
                    Dear {{ $quotation->lead->customer_name ?? 'Valued Customer' }},
                </div>

                <div class="intro-text">
                    Thank you for contacting us regarding your CCTV surveillance and security requirements. We have prepared a customized proposal and price quotation for your installation site.
                </div>

                @if(!empty($customMessage))
                    <div class="custom-msg">
                        <strong>Message from Engineering Team:</strong><br>
                        {{ $customMessage }}
                    </div>
                @endif

                {{-- Summary Box --}}
                <div class="summary-card">
                    <h3>Quotation Overview</h3>
                    <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 13px;">
                        <tr>
                            <td style="padding: 4px 0; color: #64748b;">Quotation Number:</td>
                            <td style="padding: 4px 0; text-align: right; font-weight: 700; color: #0f172a;">#{{ $quotation->quotation_no }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 0; color: #64748b;">Quotation Date:</td>
                            <td style="padding: 4px 0; text-align: right; font-weight: 600; color: #334155;">{{ $quotation->quotation_date ? $quotation->quotation_date->format('d M Y') : now()->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 0; color: #64748b;">Valid Until:</td>
                            <td style="padding: 4px 0; text-align: right; font-weight: 600; color: #334155;">{{ $quotation->valid_until ? $quotation->valid_until->format('d M Y') : '15 days from issue' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 0; color: #64748b;">Total Line Items:</td>
                            <td style="padding: 4px 0; text-align: right; font-weight: 600; color: #334155;">{{ $quotation->items->count() }} items</td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 0; color: #64748b;">Subtotal:</td>
                            <td style="padding: 4px 0; text-align: right; font-weight: 600; color: #334155;">₹{{ number_format($quotation->subtotal, 2) }}</td>
                        </tr>
                        @if($quotation->discount > 0)
                            <tr>
                                <td style="padding: 4px 0; color: #059669;">Special Discount:</td>
                                <td style="padding: 4px 0; text-align: right; font-weight: 600; color: #059669;">- ₹{{ number_format($quotation->discount, 2) }}</td>
                            </tr>
                        @endif
                        @if($quotation->tax_amount > 0)
                            <tr>
                                <td style="padding: 4px 0; color: #64748b;">GST ({{ $quotation->tax_percent ?? 18 }}%):</td>
                                <td style="padding: 4px 0; text-align: right; font-weight: 600; color: #334155;">₹{{ number_format($quotation->tax_amount, 2) }}</td>
                            </tr>
                        @endif
                        <tr style="border-top: 1px dashed #c084fc;">
                            <td style="padding: 10px 0 4px; font-size: 15px; font-weight: 800; color: #0f172a;">Grand Total:</td>
                            <td style="padding: 10px 0 4px; text-align: right; font-size: 17px; font-weight: 800; color: #4338ca;">₹{{ number_format($quotation->total, 2) }}</td>
                        </tr>
                    </table>
                </div>

                {{-- Attachment Notice --}}
                <div class="attachment-notice">
                    <div class="attachment-icon">📎</div>
                    <div class="attachment-text">
                        The complete itemized PDF proposal document (<strong>Quotation-{{ $quotation->quotation_no }}.pdf</strong>) is attached to this email for your records.
                    </div>
                </div>

                {{-- Action Button --}}
                <div class="cta-center">
                    <a href="{{ route('portal.quotations') }}" class="btn-portal" target="_blank">
                        ✓ Review & Approve Online in Portal
                    </a>
                </div>

                <div style="font-size: 12px; color: #64748b; text-align: center; margin-top: 15px;">
                    Or download proposal directly: <a href="{{ route('quotations.public-pdf', ['quotation' => $quotation, 'format' => '1']) }}" style="color: #4f46e5; font-weight: 700;">Download PDF Proposal</a>
                </div>
            </div>

            {{-- Footer --}}
            <div class="footer">
                <p style="margin: 0 0 6px;">
                    <strong>{{ config('app.name', 'CCTV Operations CRM') }}</strong> &bull; Professional CCTV & Security Engineering
                </p>
                <p style="margin: 0;">
                    If you have any questions, please reply directly to this email or contact support.
                </p>
            </div>

        </div>
    </div>
</body>
</html>
