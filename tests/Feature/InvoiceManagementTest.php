<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\InstallationJob;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $customer;
    private InstallationJob $job;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->customer = User::factory()->create(['role' => 'customer']);

        $lead = Lead::create([
            'customer_name' => 'Alice Customer',
            'phone' => '1234567890',
            'status' => 'won',
        ]);

        $quotation = Quotation::create([
            'lead_id' => $lead->id,
            'quotation_no' => 'QT-1234',
            'subtotal' => 1000.00,
            'discount' => 100.00,
            'tax_percent' => 18.00,
            'tax_amount' => 162.00,
            'total' => 1062.00,
            'status' => 'accepted',
        ]);

        $this->job = InstallationJob::create([
            'quotation_id' => $quotation->id,
            'job_no' => 'JOB-1234',
            'status' => 'completed',
        ]);
    }

    public function test_guests_and_customers_are_denied_access_to_invoices(): void
    {
        // Guests
        $this->get(route('invoices.index'))->assertRedirect(route('login'));
        $this->get(route('invoices.create', $this->job))->assertRedirect(route('login'));

        // Customers
        $this->actingAs($this->customer);
        $this->get(route('invoices.index'))->assertStatus(403);
        $this->get(route('invoices.create', $this->job))->assertStatus(403);
    }

    public function test_staff_and_admin_can_access_invoices(): void
    {
        $this->actingAs($this->staff);
        $this->get(route('invoices.index'))->assertOk();
        $this->get(route('invoices.create', $this->job))->assertOk();
    }

    public function test_cannot_create_invoice_for_non_completed_job(): void
    {
        $pendingJob = InstallationJob::create([
            'quotation_id' => $this->job->quotation_id,
            'job_no' => 'JOB-5678',
            'status' => 'pending',
        ]);

        $this->actingAs($this->staff);
        $response = $this->get(route('invoices.create', $pendingJob));
        $response->assertRedirect();
        $response->assertSessionHas('status', 'Only completed jobs can be invoiced.');
    }

    public function test_can_create_invoice_successfully(): void
    {
        $this->actingAs($this->staff);

        $response = $this->post(route('invoices.store', $this->job), [
            'invoice_date' => '2026-08-26',
            'due_date' => '2026-09-02',
            'discount' => 100.00,
            'tax_percent' => 18.00,
            'notes' => 'Test Invoice Notes',
        ]);

        $invoice = Invoice::first();
        $this->assertNotNull($invoice);
        $response->assertRedirect(route('invoices.show', $invoice));

        $this->assertDatabaseHas('invoices', [
            'installation_job_id' => $this->job->id,
            'subtotal' => 1000.00,
            'discount' => 100.00,
            'tax_percent' => 18.00,
            'status' => 'unpaid',
        ]);
    }

    public function test_payment_recording_transitions_invoice_status_correctly(): void
    {
        $invoice = Invoice::create([
            'installation_job_id' => $this->job->id,
            'quotation_id' => $this->job->quotation_id,
            'invoice_no' => 'INV-20260826-0001',
            'invoice_date' => '2026-08-26',
            'subtotal' => 1000.00,
            'discount' => 100.00,
            'tax_percent' => 18.00,
            'tax_amount' => 162.00,
            'total' => 1062.00,
            'status' => 'unpaid',
        ]);

        $this->actingAs($this->staff);

        // 1. Record a partial payment
        $response = $this->post(route('payments.store', $invoice), [
            'amount' => 500.00,
            'paid_on' => '2026-08-26',
            'method' => 'upi',
            'reference_no' => 'TXN12345',
        ]);

        $response->assertRedirect();
        $invoice->refresh();
        $this->assertEquals(500.00, $invoice->amount_paid);
        $this->assertEquals('partially_paid', $invoice->status);

        // 2. Record the remaining payment to settle the invoice
        $response = $this->post(route('payments.store', $invoice), [
            'amount' => 562.00,
            'paid_on' => '2026-08-26',
            'method' => 'cash',
        ]);

        $invoice->refresh();
        $this->assertEquals(1062.00, $invoice->amount_paid);
        $this->assertEquals('paid', $invoice->status);

        // 3. Remove a payment
        $payment = Payment::where('method', 'cash')->first();
        $this->assertNotNull($payment);

        $response = $this->delete(route('payments.destroy', $payment));
        $response->assertRedirect();

        $invoice->refresh();
        $this->assertEquals(500.00, $invoice->amount_paid);
        $this->assertEquals('partially_paid', $invoice->status);
    }
}
