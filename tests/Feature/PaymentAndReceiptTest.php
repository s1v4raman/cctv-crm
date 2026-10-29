<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Lead;
use App\Models\NotificationLog;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PaymentAndReceiptTest extends TestCase
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

        $response = $this->get(URL::signedRoute('payments.receipt.pdf', $payment));

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

    public function test_staff_record_a_gpay_payment_and_the_receipt_names_the_mode(): void
    {
        Mail::fake();

        $this->actingAs($this->admin)->post(route('payments.store', $this->invoice), [
            'amount'       => 10000,
            'paid_on'      => Carbon::today()->toDateString(),
            'method'       => 'gpay',
            'reference_no' => 'GPAY-UTR-4455',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $payment = $this->invoice->payments()->first();
        $this->assertSame('gpay', $payment->method);
        $this->assertSame('Google Pay (GPay)', $payment->formatted_method);
        $this->assertEquals('partially_paid', $this->invoice->fresh()->status);

        $log = NotificationLog::where('event_type', 'payment_receipt')->where('reference_id', $payment->id)->first();
        $this->assertStringContainsString('Google Pay (GPay)', $log->message_body);
    }

    public function test_every_listed_payment_mode_can_be_recorded(): void
    {
        Mail::fake();

        foreach (array_keys(Payment::METHODS) as $method) {
            $this->actingAs($this->admin)->post(route('payments.store', $this->invoice), [
                'amount'  => 1,
                'paid_on' => Carbon::today()->toDateString(),
                'method'  => $method,
            ])->assertSessionHasNoErrors();
        }

        $this->assertSame(count(Payment::METHODS), $this->invoice->payments()->count());
    }

    public function test_online_gateway_is_not_a_payment_mode_any_more(): void
    {
        $this->actingAs($this->admin)->post(route('payments.store', $this->invoice), [
            'amount'  => 100,
            'paid_on' => Carbon::today()->toDateString(),
            'method'  => 'razorpay',
        ])->assertSessionHasErrors('method');
    }

    public function test_invoice_page_offers_recording_a_payment_not_paying_online(): void
    {
        $response = $this->actingAs($this->admin)->get(route('invoices.show', $this->invoice));

        $response->assertOk()
            ->assertSee('Record Payment')
            ->assertSee('Google Pay (GPay)')
            ->assertSee('Credit card')
            ->assertSee('Debit card')
            ->assertDontSee('Pay Online')
            ->assertDontSee('/pay/invoice/', false);
    }

    public function test_customer_pay_pages_are_gone(): void
    {
        $this->get('/pay/invoice/' . $this->invoice->id)->assertNotFound();
        $this->get('/pay/quotation/' . $this->quotation->id)->assertNotFound();
    }

    public function test_quotation_whatsapp_message_has_no_payment_link(): void
    {
        $message = $this->quotation->getWhatsAppFormattedMessage();

        $this->assertStringContainsString('/view-quotation/', $message);
        $this->assertStringNotContainsString('/pay/', $message);
        $this->assertStringNotContainsString('Pay 50% Advance', $message);
    }
}
