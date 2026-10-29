<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningAndAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_web_responses_contain_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');
    }

    public function test_public_payment_endpoints_no_longer_exist(): void
    {
        // Customers never pay on the site, so nobody can post a payment from outside
        $this->postJson('/api/payment/verify', ['amount' => 5000, 'method' => 'cash'])->assertNotFound();
        $this->postJson('/api/payment/create-order', ['amount' => 5000])->assertNotFound();
        $this->postJson('/webhook/razorpay', ['event' => 'payment.captured'])->assertNotFound();
    }

    public function test_invoice_recalculate_payment_status_only_counts_completed_payments(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Secure Org',
            'phone' => '9988776655',
            'email' => 'secure@org.com',
            'status' => 'won',
        ]);

        $quotation = Quotation::create([
            'lead_id' => $lead->id,
            'quotation_no' => 'QTN-SEC-001',
            'quotation_date' => now(),
            'valid_until' => now()->addDays(30),
            'subtotal' => 10000,
            'total' => 11800,
            'status' => 'accepted',
        ]);

        $job = \App\Models\InstallationJob::create([
            'quotation_id'   => $quotation->id,
            'job_no'         => 'JOB-SEC-001',
            'scheduled_date' => now(),
            'status'         => 'completed',
        ]);

        $invoice = Invoice::create([
            'installation_job_id' => $job->id,
            'invoice_no'          => 'INV-SEC-001',
            'quotation_id'        => $quotation->id,
            'invoice_date'        => now(),
            'subtotal'            => 10000,
            'tax_percent'         => 18,
            'tax_amount'          => 1800,
            'total'               => 11800,
            'amount_paid'         => 0,
            'status'              => 'unpaid',
        ]);

        // Pending Cash on Delivery payment should NOT mark the invoice as paid
        Payment::create([
            'receipt_no' => Payment::generateReceiptNumber(),
            'invoice_id' => $invoice->id,
            'amount' => 11800,
            'paid_on' => now(),
            'method' => 'cash',
            'payment_status' => 'pending',
            'notes' => 'COD pending collection',
        ]);

        $invoice->recalculatePaymentStatus();
        $invoice->refresh();

        $this->assertEquals(0, $invoice->amount_paid);
        $this->assertEquals('unpaid', $invoice->status);

        // Completed payment DOES update amount_paid and marks invoice as paid
        Payment::create([
            'receipt_no' => Payment::generateReceiptNumber(),
            'invoice_id' => $invoice->id,
            'amount' => 11800,
            'paid_on' => now(),
            'method' => 'razorpay',
            'payment_status' => 'completed',
            'notes' => 'Online paid',
        ]);

        $invoice->recalculatePaymentStatus();
        $invoice->refresh();

        $this->assertEquals(11800, $invoice->amount_paid);
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_staff_can_add_products_but_cannot_delete_them(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $product = Product::create([
            'name' => 'IP Bullet Camera 4MP',
            'sku' => 'CAM-BULLET-4MP',
            'category' => 'camera',
            'unit_price' => 3500,
            'cost_price' => 2200,
            'stock_quantity' => 15,
            'is_active' => true,
        ]);

        $this->actingAs($staff);

        // Can view products catalog & search
        $this->get(route('products.index'))->assertOk();
        $this->get(route('products.search', ['q' => 'Bullet']))->assertOk();

        // Staff add products; deleting stays admin-only
        $this->get(route('products.create'))->assertOk();
        $this->delete(route('products.destroy', $product))->assertStatus(403);
        $this->assertNotNull($product->fresh());
    }
}
