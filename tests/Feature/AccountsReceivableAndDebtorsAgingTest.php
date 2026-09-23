<?php

namespace Tests\Feature;

use App\Models\InstallationJob;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\NotificationLog;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountsReceivableAndDebtorsAgingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Finance Admin']);
        $this->staff = User::factory()->create(['role' => 'staff', 'name' => 'Sales Staff']);
        $this->technician = User::factory()->create(['role' => 'technician', 'name' => 'Field Tech']);
    }

    public function test_non_admin_is_forbidden_from_accounts_receivable(): void
    {
        $this->actingAs($this->staff)
            ->get(route('finance.receivables.index'))
            ->assertForbidden();

        $this->actingAs($this->technician)
            ->get(route('finance.receivables.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_debtors_aging_matrix_and_dso(): void
    {
        $leadA = Lead::create([
            'customer_name' => 'Tech Park Chennai',
            'phone'         => '9888877771',
            'email'         => 'accounts@techpark.in',
            'site_address'  => 'OMR, Chennai',
        ]);

        $leadB = Lead::create([
            'customer_name' => 'Sunrise Hospital',
            'phone'         => '9888877772',
            'email'         => 'billing@sunrise.org',
            'site_address'  => 'Anna Nagar, Chennai',
        ]);

        $quoteA = Quotation::create([
            'lead_id'        => $leadA->id,
            'quotation_no'   => 'QT-2026-001',
            'quotation_date' => now()->subDays(20),
            'subtotal'       => 42372.88,
            'total'          => 50000.00,
            'status'         => 'accepted',
        ]);

        $quoteB = Quotation::create([
            'lead_id'        => $leadB->id,
            'quotation_no'   => 'QT-2026-002',
            'quotation_date' => now()->subDays(65),
            'subtotal'       => 101694.92,
            'total'          => 120000.00,
            'status'         => 'accepted',
        ]);

        $jobA = InstallationJob::create([
            'quotation_id' => $quoteA->id,
            'job_no'       => 'JOB-2026-001',
            'status'       => 'completed',
        ]);

        $jobB = InstallationJob::create([
            'quotation_id' => $quoteB->id,
            'job_no'       => 'JOB-2026-002',
            'status'       => 'completed',
        ]);

        // Invoice 1: 15 days old (0-30 bucket)
        Invoice::create([
            'installation_job_id' => $jobA->id,
            'invoice_no'          => 'INV-2026-001',
            'quotation_id'        => $quoteA->id,
            'invoice_date'        => now()->subDays(15),
            'due_date'            => now()->addDays(15),
            'subtotal'            => 42372.88,
            'tax_amount'          => 7627.12,
            'total'               => 50000.00,
            'amount_paid'         => 10000.00,
            'status'              => 'partially_paid',
        ]);

        // Invoice 2: 45 days overdue (31-60 bucket)
        Invoice::create([
            'installation_job_id' => $jobB->id,
            'invoice_no'          => 'INV-2026-002',
            'quotation_id'        => $quoteB->id,
            'invoice_date'        => now()->subDays(60),
            'due_date'            => now()->subDays(45),
            'subtotal'            => 101694.92,
            'tax_amount'          => 18305.08,
            'total'               => 120000.00,
            'amount_paid'         => 0.00,
            'status'              => 'overdue',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('finance.receivables.index'));

        $response->assertOk();
        $response->assertSee('Accounts Receivable', false);
        $response->assertSee('Debtors Aging', false);
        $response->assertSee('Tech Park Chennai');
        $response->assertSee('Sunrise Hospital');
        $response->assertSee('160,000.00'); // Total outstanding (40,000 + 120,000)
    }

    public function test_export_aging_csv(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Metro Warehousing',
            'phone'         => '9944001122',
            'email'         => 'accounts@metroware.com',
            'site_address'  => 'Guindy, Chennai',
        ]);

        $quote = Quotation::create([
            'lead_id'        => $lead->id,
            'quotation_no'   => 'QT-2026-003',
            'quotation_date' => now()->subDays(40),
            'subtotal'       => 67796.61,
            'total'          => 80000.00,
            'status'         => 'accepted',
        ]);

        $job = InstallationJob::create([
            'quotation_id' => $quote->id,
            'job_no'       => 'JOB-2026-003',
            'status'       => 'completed',
        ]);

        Invoice::create([
            'installation_job_id' => $job->id,
            'invoice_no'          => 'INV-2026-003',
            'quotation_id'        => $quote->id,
            'invoice_date'        => now()->subDays(35),
            'due_date'            => now()->subDays(5),
            'subtotal'            => 67796.61,
            'tax_amount'          => 12203.39,
            'total'               => 80000.00,
            'amount_paid'         => 0.00,
            'status'              => 'overdue',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('finance.receivables.export-csv'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Debtors_Aging_Ledger_', $response->headers->get('content-disposition'));
    }

    public function test_export_aging_pdf(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Apex Tower',
            'phone'         => '9944003344',
            'email'         => 'billing@apextower.com',
            'site_address'  => 'T Nagar, Chennai',
        ]);

        $quote = Quotation::create([
            'lead_id'        => $lead->id,
            'quotation_no'   => 'QT-2026-004',
            'quotation_date' => now()->subDays(25),
            'subtotal'       => 25423.73,
            'total'          => 30000.00,
            'status'         => 'accepted',
        ]);

        $job = InstallationJob::create([
            'quotation_id' => $quote->id,
            'job_no'       => 'JOB-2026-004',
            'status'       => 'completed',
        ]);

        Invoice::create([
            'installation_job_id' => $job->id,
            'invoice_no'          => 'INV-2026-004',
            'quotation_id'        => $quote->id,
            'invoice_date'        => now()->subDays(20),
            'due_date'            => now()->addDays(10),
            'subtotal'            => 25423.73,
            'tax_amount'          => 4576.27,
            'total'               => 30000.00,
            'amount_paid'         => 0.00,
            'status'              => 'unpaid',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('finance.receivables.export-pdf'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_customer_statement_of_account_ledger_and_pdf(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Marina Bay Residences',
            'phone'         => '9123456780',
            'email'         => 'finance@marinabay.in',
            'site_address'  => 'ECR, Chennai',
        ]);

        $quote = Quotation::create([
            'lead_id'        => $lead->id,
            'quotation_no'   => 'QT-2026-005',
            'quotation_date' => now()->subDays(35),
            'subtotal'       => 84745.76,
            'total'          => 100000.00,
            'status'         => 'accepted',
        ]);

        $job = InstallationJob::create([
            'quotation_id' => $quote->id,
            'job_no'       => 'JOB-2026-005',
            'status'       => 'completed',
        ]);

        $inv = Invoice::create([
            'installation_job_id' => $job->id,
            'invoice_no'          => 'INV-2026-005',
            'quotation_id'        => $quote->id,
            'invoice_date'        => now()->subDays(30),
            'due_date'            => now()->subDays(15),
            'subtotal'            => 84745.76,
            'tax_amount'          => 15254.24,
            'total'               => 100000.00,
            'amount_paid'         => 40000.00,
            'status'              => 'partially_paid',
        ]);

        Payment::create([
            'receipt_no'     => 'REC-2026-001',
            'invoice_id'     => $inv->id,
            'quotation_id'   => $quote->id,
            'paid_on'        => now()->subDays(10),
            'amount'         => 40000.00,
            'method'         => 'bank_transfer',
            'reference_no'   => 'UTR123456789',
            'payment_status' => 'successful',
        ]);

        // View Customer Ledger in UI
        $response = $this->actingAs($this->admin)
            ->get(route('finance.receivables.customer', $lead->id));

        $response->assertOk();
        $response->assertSee('Marina Bay Residences');
        $response->assertSee('Statement of Account', false);
        $response->assertSee('100,000.00'); // Total Invoiced
        $response->assertSee('40,000.00');  // Total Paid
        $response->assertSee('60,000.00');  // Balance Due
        $response->assertSee('REC-2026-001');

        // View Customer Statement PDF
        $pdfResponse = $this->actingAs($this->admin)
            ->get(route('finance.receivables.customer.pdf', $lead->id));

        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_send_single_invoice_payment_reminder(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Royal Enclave',
            'phone'         => '9876543210',
            'email'         => 'admin@royalenclave.com',
            'site_address'  => 'Adyar, Chennai',
        ]);

        $quote = Quotation::create([
            'lead_id'        => $lead->id,
            'quotation_no'   => 'QT-2026-006',
            'quotation_date' => now()->subDays(45),
            'subtotal'       => 38135.59,
            'total'          => 45000.00,
            'status'         => 'accepted',
        ]);

        $job = InstallationJob::create([
            'quotation_id' => $quote->id,
            'job_no'       => 'JOB-2026-006',
            'status'       => 'completed',
        ]);

        $inv = Invoice::create([
            'installation_job_id' => $job->id,
            'invoice_no'          => 'INV-2026-006',
            'quotation_id'        => $quote->id,
            'invoice_date'        => now()->subDays(40),
            'due_date'            => now()->subDays(10),
            'subtotal'            => 38135.59,
            'tax_amount'          => 6864.41,
            'total'               => 45000.00,
            'amount_paid'         => 0.00,
            'status'              => 'overdue',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('finance.receivables.reminder', $inv->id), [
                'channel' => 'all',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('notification_logs', [
            'reference_id'    => $inv->id,
            'recipient_phone' => '9876543210',
        ]);
    }

    public function test_bulk_overdue_sweep_sends_reminders_and_deduplicates(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Elite Motors',
            'phone'         => '9876500000',
            'email'         => 'billing@elitemotors.in',
            'site_address'  => 'Velachery, Chennai',
        ]);

        $quote = Quotation::create([
            'lead_id'        => $lead->id,
            'quotation_no'   => 'QT-2026-007',
            'quotation_date' => now()->subDays(55),
            'subtotal'       => 63559.32,
            'total'          => 75000.00,
            'status'         => 'accepted',
        ]);

        $job1 = InstallationJob::create([
            'quotation_id' => $quote->id,
            'job_no'       => 'JOB-2026-007',
            'status'       => 'completed',
        ]);

        $job2 = InstallationJob::create([
            'quotation_id' => $quote->id,
            'job_no'       => 'JOB-2026-008',
            'status'       => 'completed',
        ]);

        // Invoice 1: Overdue, never reminded
        $inv1 = Invoice::create([
            'installation_job_id' => $job1->id,
            'invoice_no'          => 'INV-2026-007',
            'quotation_id'        => $quote->id,
            'invoice_date'        => now()->subDays(50),
            'due_date'            => now()->subDays(20),
            'subtotal'            => 63559.32,
            'tax_amount'          => 11440.68,
            'total'               => 75000.00,
            'amount_paid'         => 0.00,
            'status'              => 'overdue',
        ]);

        // Invoice 2: Overdue, but reminded yesterday (within 3-day deduplication window)
        $inv2 = Invoice::create([
            'installation_job_id' => $job2->id,
            'invoice_no'          => 'INV-2026-008',
            'quotation_id'        => $quote->id,
            'invoice_date'        => now()->subDays(50),
            'due_date'            => now()->subDays(20),
            'subtotal'            => 20000.00,
            'tax_amount'          => 0.00,
            'total'               => 20000.00,
            'amount_paid'         => 0.00,
            'status'              => 'overdue',
        ]);

        NotificationLog::create([
            'channel'         => 'whatsapp',
            'event_type'      => 'payment_overdue',
            'recipient_phone' => $lead->phone,
            'recipient_name'  => $lead->customer_name,
            'message_body'    => 'Reminder yesterday',
            'reference_type'  => Invoice::class,
            'reference_id'    => $inv2->id,
            'status'          => 'sent',
            'sent_at'         => now()->subDay(),
            'created_at'      => now()->subDay(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('finance.receivables.sweep'));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('notification_logs', [
            'reference_id' => $inv1->id,
            'event_type'   => 'payment_overdue',
        ]);
    }
}
