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

    public function __construct(Quotation $quotation, ?string $customMessage = null, ?string $emailSubject = null, string $pdfFormat = '1')
    {
        $this->quotation = $quotation;
        $this->customMessage = $customMessage;
        $this->emailSubject = $emailSubject ?: "Official Quotation #{$quotation->quotation_no} - CCTV Installation & Security Proposal";
        $this->pdfFormat = in_array($pdfFormat, ['1', '2', '3']) ? $pdfFormat : '1';
    }

    public function build()
    {
        $this->quotation->load(['lead', 'items.product']);

        $pdf = Pdf::loadView('quotations.pdf.format' . $this->pdfFormat, [
            'quotation' => $this->quotation,
        ]);

        $mailFromAddress = config('mail.from.address', 'support@cctvcrm.com');
        $mailFromName = config('mail.from.name', config('app.name', 'CCTV CRM'));

        return $this->subject($this->emailSubject)
            ->from($mailFromAddress, $mailFromName)
            ->replyTo($mailFromAddress, $mailFromName)
            ->view('emails.quotation_with_pdf', [
                'quotation'     => $this->quotation,
                'customMessage' => $this->customMessage,
            ])
            ->attachData($pdf->output(), "Quotation-{$this->quotation->quotation_no}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }
}
