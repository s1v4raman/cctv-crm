<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentReceiptService;
use Illuminate\Http\Response;

class PaymentReceiptController extends Controller
{
    /**
     * Official payment receipt PDF, opened from the signed link sent on WhatsApp / email.
     */
    public function download(Payment $payment, PaymentReceiptService $receiptService): Response
    {
        $pdf = $receiptService->generateReceiptPdf($payment);
        $fileName = "Payment_Receipt_{$payment->receipt_no}.pdf";

        return $pdf->stream($fileName);
    }
}
