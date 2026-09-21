<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SmtpVerificationTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $targetEmail;
    public string $fromName;
    public string $fromAddress;
    public string $host;
    public string $timestamp;

    public function __construct(string $targetEmail, string $fromName, string $fromAddress, string $host)
    {
        $this->targetEmail = $targetEmail;
        $this->fromName = $fromName;
        $this->fromAddress = $fromAddress;
        $this->host = $host;
        $this->timestamp = now()->format('d M Y, h:i A');
    }

    public function build(): self
    {
        return $this->from($this->fromAddress, $this->fromName)
            ->to($this->targetEmail)
            ->subject('✅ Live SMTP Verification: CCTV CRM Email Dispatch Test')
            ->html(
                "<div style='font-family:Arial,sans-serif;padding:24px;border:1px solid #e2e8f0;border-radius:12px;max-width:550px;background:#ffffff;'>"
                . "<h2 style='color:#4f46e5;margin-top:0;font-size:18px;'>🔒 CCTV CRM Mail Gateway Verification</h2>"
                . "<p style='font-size:14px;color:#334155;line-height:1.5;'>This is a live test email sent from your CCTV Operations CRM to verify real-time SMTP dispatch across the internet.</p>"
                . "<div style='background:#f8fafc;border:1px solid #e2e8f0;padding:14px;border-radius:8px;font-size:13px;color:#334155;margin:18px 0;'>"
                . "<div><strong>Status:</strong> <span style='color:#16a34a;font-weight:700;'>✅ Verified & Operational</span></div>"
                . "<div style='margin-top:4px;'><strong>SMTP Host:</strong> {$this->host}</div>"
                . "<div style='margin-top:4px;'><strong>Sender:</strong> {$this->fromName} &lt;{$this->fromAddress}&gt;</div>"
                . "<div style='margin-top:4px;'><strong>Recipient:</strong> {$this->targetEmail}</div>"
                . "<div style='margin-top:4px;'><strong>Timestamp:</strong> {$this->timestamp}</div>"
                . "</div>"
                . "<p style='font-size:12px;color:#64748b;line-height:1.5;'>Quotation proposal PDFs, payment receipts, and automated customer reminders will now be delivered to your customers in real-time.</p>"
                . "<hr style='border:none;border-top:1px solid #e2e8f0;margin:16px 0;'>"
                . "<div style='font-size:11px;color:#94a3b8;'>CCTV Operations & Service Management CRM System</div>"
                . "</div>"
            );
    }
}
