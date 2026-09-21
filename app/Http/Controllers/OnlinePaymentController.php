<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quotation;
use App\Services\PaymentGatewayService;
use App\Services\PaymentReceiptService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class OnlinePaymentController extends Controller
{
    /**
     * Public / Client checkout page for an Invoice.
     */
    public function checkoutInvoice(Invoice $invoice, PaymentGatewayService $gatewayService): View|RedirectResponse
    {
        $invoice->load(['quotation.lead', 'payments']);
        $lead = $invoice->quotation?->lead;

        if (!$lead) {
            abort(404, 'Customer details not found for this invoice.');
        }

        $balanceDue = $invoice->balanceDue();
        if ($balanceDue <= 0) {
            return redirect()->route('invoices.show', $invoice)
                ->with('status', "Invoice #{$invoice->invoice_no} has already been paid in full!");
        }

        $totalAmount = (float) $invoice->total;
        $payableAmount = (float) $balanceDue;
        $advanceAmount = round($payableAmount * 0.50, 2);

        $upiPayload = $gatewayService->generateUpiPayload(
            $payableAmount,
            $invoice->invoice_no,
            "Invoice {$invoice->invoice_no}"
        );

        return view('payments.checkout', [
            'invoice'       => $invoice,
            'quotation'     => $invoice->quotation,
            'lead'          => $lead,
            'totalAmount'   => $totalAmount,
            'payableAmount' => $payableAmount,
            'advanceAmount' => $advanceAmount,
            'fullAmount'    => $payableAmount,
            'balanceDue'    => $balanceDue,
            'amount'        => $payableAmount,
            'upiPayload'    => $upiPayload,
        ]);
    }

    /**
     * Public / Client checkout page for Quotation advance payment or full cash payment.
     */
    public function checkoutQuotation(Quotation $quotation, PaymentGatewayService $gatewayService): View|RedirectResponse
    {
        $quotation->load(['lead', 'items']);
        $lead = $quotation->lead;

        if (!$lead) {
            abort(404, 'Customer details not found for this quotation.');
        }

        $totalAmount = (float) $quotation->total;
        // Default 50% advance for quotations (customer can choose 50% advance or 100% full total)
        $advanceAmount = round($totalAmount * 0.50, 2);
        $payableAmount = $advanceAmount;

        $upiPayload = $gatewayService->generateUpiPayload(
            $payableAmount,
            $quotation->quotation_no,
            "Advance for Quote {$quotation->quotation_no}"
        );

        return view('payments.checkout', [
            'invoice'       => null,
            'quotation'     => $quotation,
            'lead'          => $lead,
            'totalAmount'   => $totalAmount,
            'payableAmount' => $payableAmount,
            'advanceAmount' => $advanceAmount,
            'fullAmount'    => $totalAmount,
            'balanceDue'    => $totalAmount,
            'amount'        => $payableAmount,
            'upiPayload'    => $upiPayload,
        ]);
    }

    /**
     * Initialize Razorpay Order Session.
     */
    public function createOrder(Request $request, PaymentGatewayService $gatewayService): JsonResponse
    {
        $validated = $request->validate([
            'amount'       => ['required', 'numeric', 'min:1'],
            'invoice_id'   => ['nullable', 'exists:invoices,id'],
            'quotation_id' => ['nullable', 'exists:quotations,id'],
        ]);

        $amount = (float) $validated['amount'];
        $receiptNo = Payment::generateReceiptNumber();

        $notes = [
            'invoice_id'   => $validated['invoice_id'] ?? null,
            'quotation_id' => $validated['quotation_id'] ?? null,
            'receipt_no'   => $receiptNo,
        ];

        $order = $gatewayService->createRazorpayOrder($amount, $receiptNo, $notes);

        return response()->json($order);
    }

    /**
     * Verify payment signature, record payment transaction, recalculate invoice, and send receipt.
     */
    public function verifyPayment(
        Request $request,
        PaymentGatewayService $gatewayService,
        PaymentReceiptService $receiptService
    ): JsonResponse {
        $validated = $request->validate([
            'amount'              => ['required', 'numeric', 'min:0.01'],
            'invoice_id'          => ['nullable', 'exists:invoices,id'],
            'quotation_id'        => ['nullable', 'exists:quotations,id'],
            'method'              => ['required', 'string'],
            'receipt_no'          => ['nullable', 'string'],
            'reference_no'        => ['nullable', 'string', 'max:100'],
            'razorpay_order_id'   => ['nullable', 'string'],
            'razorpay_payment_id' => ['nullable', 'string'],
            'razorpay_signature'  => ['nullable', 'string'],
        ]);

        // 1. If Razorpay method, verify HMAC signature
        if ($validated['method'] === 'razorpay') {
            if (empty($validated['razorpay_order_id']) || empty($validated['razorpay_payment_id']) || empty($validated['razorpay_signature'])) {
                return response()->json(['success' => false, 'message' => 'Missing Razorpay signature attributes.'], 422);
            }

            $isValid = $gatewayService->verifyRazorpaySignature(
                $validated['razorpay_order_id'],
                $validated['razorpay_payment_id'],
                $validated['razorpay_signature']
            );

            if (!$isValid) {
                return response()->json(['success' => false, 'message' => 'Razorpay payment signature verification failed.'], 400);
            }
        }

        $receiptNo = $validated['receipt_no'] ?: Payment::generateReceiptNumber();
        $invoice = !empty($validated['invoice_id']) ? Invoice::find($validated['invoice_id']) : null;
        $quotation = !empty($validated['quotation_id']) ? Quotation::find($validated['quotation_id']) : ($invoice?->quotation);

        $payment = DB::transaction(function () use ($validated, $receiptNo, $invoice, $quotation) {
            $isCash = ($validated['method'] === 'cash');
            $pay = Payment::create([
                'receipt_no'          => $receiptNo,
                'invoice_id'          => $invoice?->id,
                'quotation_id'        => $quotation?->id,
                'amount'              => (float) $validated['amount'],
                'paid_on'             => Carbon::today(),
                'method'              => $validated['method'],
                'reference_no'        => $validated['reference_no'] ?? $validated['razorpay_payment_id'] ?? ($isCash ? 'COD-' . date('YmdHis') : null),
                'gateway_order_id'    => $validated['razorpay_order_id'] ?? null,
                'gateway_payment_id'  => $validated['razorpay_payment_id'] ?? null,
                'gateway_signature'   => $validated['razorpay_signature'] ?? null,
                'payment_status'      => $isCash ? 'pending' : 'completed',
                'notes'               => $isCash 
                    ? "Customer opted to Pay via Cash on Installation / Site Delivery." 
                    : ("Online payment processed via " . ucfirst($validated['method'])),
                'recorded_by'         => auth()->id(),
            ]);

            // Update Invoice balance & status if payment linked to invoice
            if ($invoice && !$isCash) {
                $invoice->recalculatePaymentStatus();
            }

            // If Quotation advance or cash confirmation, automatically mark quotation as accepted
            if ($quotation && in_array($quotation->status, ['draft', 'sent'])) {
                $quotation->update([
                    'status'      => 'accepted',
                    'accepted_at' => now(),
                ]);

                $quotation->lead?->update(['status' => 'won']);

                $historyNote = $isCash
                    ? "Customer accepted quotation via portal with Cash on Installation (₹" . number_format((float)$validated['amount'], 2) . ")."
                    : "Customer paid online payment of ₹" . number_format((float)$validated['amount'], 2) . " (Receipt #{$receiptNo}).";

                $quotation->statusHistories()->create([
                    'changed_by'  => auth()->id(),
                    'from_status' => 'sent',
                    'to_status'   => 'accepted',
                    'notes'       => $historyNote,
                ]);
            }

            return $pay;
        });

        // 2. Dispatch automated receipts via WhatsApp, SMS, and Email
        $receiptService->sendReceiptNotifications($payment);

        return response()->json([
            'success'     => true,
            'message'     => 'Payment recorded successfully! Receipt generated.',
            'payment'     => $payment,
            'receipt_url' => route('payments.receipt.pdf', $payment),
        ]);
    }

    /**
     * Download or view official Payment Receipt PDF.
     */
    public function downloadReceipt(Payment $payment, PaymentReceiptService $receiptService): Response
    {
        $pdf = $receiptService->generateReceiptPdf($payment);
        $fileName = "Payment_Receipt_{$payment->receipt_no}.pdf";

        return $pdf->stream($fileName);
    }

    /**
     * Razorpay Asynchronous Webhook Receiver.
     */
    public function webhook(Request $request, PaymentGatewayService $gatewayService, PaymentReceiptService $receiptService): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature', '');
        $webhookSecret = $gatewayService->getSettings()->razorpay_webhook_secret;

        if ($webhookSecret && !empty($signature)) {
            $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
            if (!hash_equals($expectedSignature, $signature)) {
                Log::warning("Razorpay Webhook signature mismatch.");
                return response()->json(['status' => 'invalid_signature'], 400);
            }
        }

        $data = json_decode($payload, true);
        $event = $data['event'] ?? '';

        if ($event === 'payment.captured' || $event === 'order.paid') {
            $paymentEntity = $data['payload']['payment']['entity'] ?? [];
            $notes = $paymentEntity['notes'] ?? [];

            $orderId = $paymentEntity['order_id'] ?? null;
            $paymentId = $paymentEntity['id'] ?? null;
            $amount = isset($paymentEntity['amount']) ? ((float) $paymentEntity['amount']) / 100 : 0;
            $invoiceId = $notes['invoice_id'] ?? null;
            $quotationId = $notes['quotation_id'] ?? null;

            // Check if payment already recorded
            $exists = Payment::where('gateway_payment_id', $paymentId)->exists();
            if (!$exists && $amount > 0) {
                $invoice = $invoiceId ? Invoice::find($invoiceId) : null;
                $quotation = $quotationId ? Quotation::find($quotationId) : $invoice?->quotation;

                $payment = Payment::create([
                    'receipt_no'         => Payment::generateReceiptNumber(),
                    'invoice_id'         => $invoice?->id,
                    'quotation_id'       => $quotation?->id,
                    'amount'             => $amount,
                    'paid_on'            => Carbon::today(),
                    'method'             => 'razorpay',
                    'reference_no'       => $paymentId,
                    'gateway_order_id'   => $orderId,
                    'gateway_payment_id' => $paymentId,
                    'payment_status'     => 'completed',
                    'notes'              => "Captured asynchronously via Razorpay Webhook ({$event})",
                ]);

                if ($invoice) {
                    $invoice->recalculatePaymentStatus();
                }

                $receiptService->sendReceiptNotifications($payment);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
