<?php

namespace Tests\Feature;

use App\Models\GatewaySetting;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\NotificationLog;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use App\Services\PaymentGatewayService;
use App\Services\PaymentReceiptService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OnlinePaymentAndReceiptTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;
    private Lead $lead;
    private Quotation $quotation;
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'  => 'admin',
            'email' => 'admin@securitysystems.com',
            'name'  => 'Admin User',
        ]);

        $this->customer = User::factory()->create([
            'role'  => 'customer',
            'email' => 'customer@techcorp.com',
            'name'  => 'TechCorp Manager',
        ]);

        $this->lead = Lead::create([
            'customer_name' => 'TechCorp Solutions',
            'phone'         => '9876543210',
            'email'         => 'customer@techcorp.com',
            'site_address'  => 'TechCorp Tower 4, Bangalore',
        ]);

        $this->quotation = Quotation::create([
            'lead_id'        => $this->lead->id,
            'quotation_no'   => 'QT-20260908-0099',
            'quotation_date' => Carbon::today(),
            'valid_until'    => Carbon::today()->addDays(15),
            'subtotal'       => 20000,
            'discount'       => 0,
            'tax_percent'    => 18,
            'tax_amount'     => 3600,
            'total'          => 23600,
            'status'         => 'sent',
        ]);

        QuotationItem::create([
            'quotation_id' => $this->quotation->id,
            'item_name'    => '4K Dome CCTV Camera (4 Pack)',
            'quantity'     => 4,
            'unit'         => 'pcs',
            'unit_price'   => 5000,
            'total'        => 20000,
        ]);

        $job = \App\Models\InstallationJob::create([
            'quotation_id'   => $this->quotation->id,
            'job_no'         => 'JOB-20260908-0001',
            'scheduled_date' => Carbon::today(),
            'status'         => 'completed',
        ]);

        $this->invoice = Invoice::create([
            'installation_job_id' => $job->id,
            'quotation_id'        => $this->quotation->id,
            'invoice_no'          => 'INV-20260908-0001',
            'invoice_date'        => Carbon::today(),
            'due_date'            => Carbon::today()->addDays(7),
            'subtotal'            => 20000,
            'discount'            => 0,
            'tax_percent'         => 18,
            'tax_amount'          => 3600,
            'total'               => 23600,
            'amount_paid'         => 0,
            'status'              => 'unpaid',
        ]);
    }

    public function test_upi_payload_generation_contains_valid_npci_uri_and_qr(): void
    {
        $gatewayService = app(PaymentGatewayService::class);
        $payload = $gatewayService->generateUpiPayload(11800.00, 'INV-20260908-0001', 'Advance CCTV Payment');

        $this->assertEquals(11800.00, $payload['amount']);
        $this->assertStringStartsWith('upi://pay?', $payload['upi_url']);
        $this->assertStringContainsString('am=11800.00', $payload['upi_url']);
        $this->assertStringContainsString('tr=INV-20260908-0001', $payload['upi_url']);
        $this->assertNotEmpty($payload['qr_image_url']);
    }

    public function test_public_checkout_page_renders_for_invoice_and_quotation(): void
    {
        // 1. Invoice Checkout
        $invResponse = $this->get(route('payment.checkout.invoice', $this->invoice));
        $invResponse->assertStatus(200);
        $invResponse->assertSee('INV-20260908-0001');
        $invResponse->assertSee('₹23,600.00');
        $invResponse->assertSee('Direct UPI QR Code');

        // 2. Quotation Advance Checkout
        $quoteResponse = $this->get(route('payment.checkout.quotation', $this->quotation));
        $quoteResponse->assertStatus(200);
        $quoteResponse->assertSee('QT-20260908-0099');
        $quoteResponse->assertSee('Advance Payment');
    }

    public function test_payment_create_order_api_returns_order_details(): void
    {
        $response = $this->postJson(route('payment.create-order'), [
            'invoice_id' => $this->invoice->id,
            'amount'     => 10000,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'order_id',
            'amount',
            'amount_paise',
            'currency',
            'key_id',
            'receipt',
        ]);
        $this->assertEquals(10000, $response->json('amount'));
        $this->assertEquals(1000000, $response->json('amount_paise'));
    }

    public function test_payment_verify_records_transaction_updates_invoice_and_dispatches_receipt(): void
    {
        Mail::fake();

        $verifyPayload = [
            'invoice_id'          => $this->invoice->id,
            'amount'              => 11800,
            'method'              => 'razorpay',
            'receipt_no'          => 'REC-20260908-TEST1',
            'razorpay_order_id'   => 'order_test_999888',
            'razorpay_payment_id' => 'pay_test_777666',
            'razorpay_signature'  => 'sim_sig_valid123',
        ];

        $response = $this->postJson(route('payment.verify'), $verifyPayload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Assert payment record was saved
        $payment = Payment::where('receipt_no', 'REC-20260908-TEST1')->first();
        $this->assertNotNull($payment);
        $this->assertEquals(11800, (float) $payment->amount);
        $this->assertEquals('razorpay', $payment->method);
        $this->assertEquals('pay_test_777666', $payment->gateway_payment_id);

        // Assert invoice status updated to partially_paid
        $this->invoice->refresh();
        $this->assertEquals(11800, (float) $this->invoice->amount_paid);
        $this->assertEquals('partially_paid', $this->invoice->status);
        $this->assertEquals(11800, (float) $this->invoice->balanceDue());

        // Assert receipt notification log created
        $log = NotificationLog::where('event_type', 'payment_receipt')
            ->where('reference_id', $payment->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('11,800.00', $log->message_body);
        $this->assertStringContainsString($this->invoice->invoice_no, $log->message_body);
    }

    public function test_quotation_advance_payment_verify_marks_quotation_accepted(): void
    {
        Mail::fake();

        $response = $this->postJson(route('payment.verify'), [
            'quotation_id'        => $this->quotation->id,
            'amount'              => 11800,
            'method'              => 'razorpay',
            'receipt_no'          => 'REC-20260908-ADV01',
            'razorpay_order_id'   => 'order_adv_123',
            'razorpay_payment_id' => 'pay_adv_456',
            'razorpay_signature'  => 'sim_sig_adv789',
        ]);

        $response->assertStatus(200);

        $this->quotation->refresh();
        $this->assertEquals('accepted', $this->quotation->status);
        $this->assertEquals('won', $this->quotation->lead->status);
    }

    public function test_payment_receipt_pdf_download_endpoint_streams_valid_pdf(): void
    {
        $payment = Payment::create([
            'receipt_no'   => 'REC-20260908-9999',
            'invoice_id'   => $this->invoice->id,
            'quotation_id' => $this->quotation->id,
            'amount'       => 23600,
            'paid_on'      => Carbon::today(),
            'method'       => 'upi',
            'reference_no' => 'UPI-UTR-99887766',
        ]);

        $response = $this->get(route('payments.receipt.pdf', $payment));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_manual_invoice_payment_creates_receipt_no_and_notifies_customer(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)->post(route('payments.store', $this->invoice), [
            'amount'       => 23600,
            'paid_on'      => Carbon::today()->toDateString(),
            'method'       => 'bank_transfer',
            'reference_no' => 'NEFT-HDFC-99112233',
            'notes'        => 'Settled via NEFT bank transfer',
        ]);

        $response->assertRedirect();
        $this->invoice->refresh();

        $this->assertEquals(23600, (float) $this->invoice->amount_paid);
        $this->assertEquals('paid', $this->invoice->status);

        $payment = $this->invoice->payments->first();
        $this->assertNotNull($payment);
        $this->assertStringStartsWith('REC-', $payment->receipt_no);

        // Verify receipt notification logged
        $log = NotificationLog::where('event_type', 'payment_receipt')
            ->where('reference_id', $payment->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('23,600.00', $log->message_body);
    }

    public function test_razorpay_webhook_captures_and_records_payment_asynchronously(): void
    {
        Mail::fake();

        $webhookPayload = [
            'event'   => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id'       => 'pay_webhook_12345',
                        'order_id' => 'order_webhook_67890',
                        'amount'   => 2360000, // 23600.00 in paise
                        'currency' => 'INR',
                        'status'   => 'captured',
                        'notes'    => [
                            'invoice_id' => $this->invoice->id,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson(route('webhook.razorpay'), $webhookPayload);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);

        $payment = Payment::where('gateway_payment_id', 'pay_webhook_12345')->first();
        $this->assertNotNull($payment);
        $this->assertEquals(23600, (float) $payment->amount);
        $this->assertEquals('razorpay', $payment->method);

        $this->invoice->refresh();
        $this->assertEquals('paid', $this->invoice->status);
    }
}
