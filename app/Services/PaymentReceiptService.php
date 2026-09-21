<?php

namespace App\Services;

use App\Models\GatewaySetting;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class PaymentReceiptService
{
    protected AlertNotificationService $alertService;

    public function __construct(AlertNotificationService $alertService)
    {
        $this->alertService = $alertService;
    }

    /**
     * Generate standard DomPDF instance for a payment receipt.
     */
    public function generateReceiptPdf(Payment $payment): \Barryvdh\DomPDF\PDF
    {
        $payment->loadMissing(['invoice.quotation.lead', 'invoice.quotation.items.product', 'quotation.lead', 'quotation.items.product']);

        $invoice = $payment->invoice;
        $quotation = $invoice?->quotation ?? $payment->quotation;
        $lead = $payment->getCustomerLead();

        $customerName = $lead?->customer_name ?? 'Valued Customer';
        $customerPhone = $lead?->phone ?? '';
        $customerEmail = $lead?->email ?? '';
        $customerAddress = $lead?->site_address ?? '';
        $items = $quotation?->items ?? collect();

        return Pdf::loadView('payments.receipt_pdf', compact(
            'payment',
            'invoice',
            'quotation',
            'lead',
            'customerName',
            'customerPhone',
            'customerEmail',
            'customerAddress',
            'items'
        ))->setPaper('a4');
    }

    /**
     * Automatically send payment confirmation & receipt links via WhatsApp, SMS, and Email.
     */
    public function sendReceiptNotifications(Payment $payment): array
    {
        $payment->loadMissing(['invoice.quotation.lead', 'quotation.lead']);
        $lead = $payment->getCustomerLead();

        if (!$lead) {
            return [];
        }

        $invoice = $payment->invoice;
        $settings = GatewaySetting::getSettings();

        $channels = [];
        if ($settings->auto_receipt_whatsapp_enabled) {
            $channels[] = 'whatsapp';
        }
        $channels[] = 'sms';
        if ($settings->auto_receipt_email_enabled) {
            $channels[] = 'email';
        }

        $balanceDue = $invoice ? $invoice->balanceDue() : 0.0;
        $invoiceNo = $invoice ? $invoice->invoice_no : ($payment->quotation ? $payment->quotation->quotation_no : 'Advance');

        $receiptUrl = route('payments.receipt.pdf', ['payment' => $payment]);

        $variables = [
            'customer_name'  => $lead->customer_name,
            'invoice_no'     => $invoiceNo,
            'amount_paid'    => '₹' . number_format((float)$payment->amount, 2),
            'payment_method' => $payment->formatted_method,
            'balance_due'    => '₹' . number_format((float)$balanceDue, 2),
            'link'           => $receiptUrl,
            'receipt_no'     => $payment->receipt_no ?? 'REC-' . $payment->id,
        ];

        try {
            $logs = $this->alertService->sendAlert(
                'payment_receipt',
                $lead,
                $variables,
                $payment,
                $channels
            );

            Log::info("Payment receipt notification dispatched for {$payment->receipt_no} (Amount: {$payment->amount})");
            return $logs;
        } catch (\Throwable $e) {
            Log::warning("Payment receipt alert dispatch error: " . $e->getMessage());
            return [];
        }
    }
}
