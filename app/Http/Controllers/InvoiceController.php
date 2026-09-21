<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Invoice;
use App\Models\InstallationJob;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('quotation.lead');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('quotation.lead', function($l) use ($search) {
                      $l->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('site_address', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        return view('invoices.index', compact('invoices'));
    }

    public function create(InstallationJob $job)
    {
        if ($job->invoice) {
            return redirect()->route('invoices.show', $job->invoice)
                ->with('status', 'An invoice already exists for this job.');
        }

        if ($job->status !== 'completed') {
            return back()->with('status', 'Only completed jobs can be invoiced.');
        }

        $job->load('quotation.items', 'quotation.lead');

        return view('invoices.create', compact('job'));
    }

    public function store(StoreInvoiceRequest $request, InstallationJob $job)
    {
        if ($job->invoice) {
            return redirect()->route('invoices.show', $job->invoice);
        }

        $validated = $request->validated();
        $quotation = $job->quotation;

        $subtotal = (float) $quotation->subtotal;
        $discount = min((float) ($validated['discount'] ?? 0.0), $subtotal);
        $taxPercent = (float) ($validated['tax_percent'] ?? $quotation->tax_percent);
        $taxableAmount = $subtotal - $discount;
        $taxAmount = $taxableAmount * ($taxPercent / 100.0);
        $total = $taxableAmount + $taxAmount;

        $invoice = Invoice::create([
            'installation_job_id' => $job->id,
            'quotation_id' => $quotation->id,
            'invoice_no' => 'INV-' . now()->format('Ymd') . '-' . str_pad((string) (Invoice::count() + 1), 4, '0', STR_PAD_LEFT),
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'] ?? null,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'status' => 'unpaid',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('invoices.show', $invoice)->with('status', 'Invoice created.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('installationJob.quotation.lead', 'quotation.items', 'payments.recordedBy');
        return view('invoices.show', compact('invoice'));
    }

    public function storePayment(
        StorePaymentRequest $request,
        Invoice $invoice,
        \App\Services\PaymentReceiptService $receiptService
    ) {
        $validated = $request->validated();
        $receiptNo = Payment::generateReceiptNumber();
        $payment = null;

        DB::transaction(function () use ($validated, $invoice, $receiptNo, &$payment) {
            $payment = $invoice->payments()->create([
                ...$validated,
                'receipt_no'   => $receiptNo,
                'quotation_id' => $invoice->quotation_id,
                'recorded_by'  => auth()->id(),
            ]);

            $invoice->recalculatePaymentStatus();
        });

        if ($payment) {
            $receiptService->sendReceiptNotifications($payment);
        }

        return back()->with('status', "Payment of ₹{$payment->amount} recorded (Receipt #{$receiptNo}). Receipt dispatched to customer!");
    }

    public function destroyPayment(Payment $payment)
    {
        $invoice = $payment->invoice;
        $payment->delete();
        $invoice->recalculatePaymentStatus();

        return back()->with('status', 'Payment removed.');
    }
}
