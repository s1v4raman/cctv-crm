<?php

namespace App\Mail;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuotationEmailWithPdf extends Mailable
{
    use Queueable, SerializesModels;

    public Quotation $quotation;
    public ?string $customMessage;
    public string $emailSubject;
    public string $pdfFormat;
    public string $pricingMode;

    public function __construct(Quotation $quotation, ?string $customMessage = null, ?string $emailSubject = null, ?string $pdfFormat = null, ?string $pricingMode = null)
    {
        $this->quotation = $quotation;
        $this->customMessage = $customMessage;
        $this->emailSubject = $emailSubject ?: "Official Quotation #{$quotation->quotation_no} - CCTV Installation & Security Proposal";
        $format = $pdfFormat ?: ($quotation->pdf_format ?? '1');
        $this->pdfFormat = in_array((string) $format, ['1', '2', '3', '4']) ? (string) $format : '1';
        $mode = $pricingMode ?: ($quotation->pricing_mode ?? 'itemized');
        $this->pricingMode = in_array((string) $mode, ['itemized', 'total_only']) ? (string) $mode : 'itemized';
    }

    public function build()
    {
        $this->quotation->loadMissing(['lead', 'items.product']);
        $this->quotation->pricing_mode = $this->pricingMode;

        $pdf = Pdf::loadView('quotations.pdf.format' . $this->pdfFormat, [
            'quotation' => $this->quotation,
        ]);

        $mailFromAddress = config('mail.from.address', 'support@cctvcrm.com');
        $mailFromName = config('mail.from.name', config('app.name', 'CCTV CRM'));

        $safeCustomer = \Illuminate\Support\Str::slug($this->quotation->lead?->customer_name ?? 'Proposal');
        $filename = "Quotation_{$this->quotation->quotation_no}" . ($safeCustomer ? "_{$safeCustomer}" : '') . ".pdf";

        return $this->subject($this->emailSubject)
            ->from($mailFromAddress, $mailFromName)
            ->replyTo($mailFromAddress, $mailFromName)
            ->view('emails.quotation_with_pdf', [
                'quotation'     => $this->quotation,
                'customMessage' => $this->customMessage,
            ])
            ->attachData($pdf->output(), $filename, [
                'mime' => 'application/pdf',
            ]);
    }
}
