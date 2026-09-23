<?php

namespace Tests\Feature;

use App\Models\DailyCashReconciliation;
use App\Models\InstallationJob;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\PettyCashAccount;
use App\Models\PettyCashTransaction;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PettyCashAndFieldReconciliationTest extends TestCase
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
        $this->technician = User::factory()->create(['role' => 'technician', 'name' => 'Field Engineer']);
    }

    public function test_non_admin_is_forbidden_from_petty_cash_module(): void
    {
        $this->actingAs($this->staff)
            ->get(route('finance.petty_cash.index'))
            ->assertForbidden();

        $this->actingAs($this->technician)
            ->get(route('finance.petty_cash.index'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('finance.petty_cash.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_petty_cash_dashboard_and_balances(): void
    {
        $vault = PettyCashAccount::getMainVault();
        $vault->update(['current_balance' => 25000.00]);

        $techWallet = PettyCashAccount::getOrCreateWalletForUser($this->technician);
        $techWallet->update(['current_balance' => 4500.00]);

        $response = $this->actingAs($this->admin)
            ->get(route('finance.petty_cash.index'));

        $response->assertOk()
            ->assertSee('Petty Cash')
            ->assertSee('Field Float Reconciliation')
            ->assertSee('29,500.00') // Total Liquid Cash
            ->assertSee('25,000.00') // Vault Safe
            ->assertSee('4,500.00')  // Technician Float
            ->assertSee('Field Engineer');
    }

    public function test_admin_can_issue_float_advance_from_vault_to_technician(): void
    {
        $vault = PettyCashAccount::getMainVault();
        $vault->update(['current_balance' => 20000.00]);

        $techWallet = PettyCashAccount::getOrCreateWalletForUser($this->technician);
        $techWallet->update(['current_balance' => 1000.00]);

        $response = $this->actingAs($this->admin)
            ->post(route('finance.petty_cash.advance'), [
                'custodian_id'     => $this->technician->id,
                'amount'           => 5000.00,
                'transaction_date' => now()->toDateString(),
                'notes'            => 'Site mobilization float for North Zone installation',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(15000.00, $vault->fresh()->current_balance);
        $this->assertEquals(6000.00, $techWallet->fresh()->current_balance);

        $this->assertDatabaseHas('petty_cash_transactions', [
            'petty_cash_account_id'  => $vault->id,
            'destination_account_id' => $techWallet->id,
            'transaction_type'       => 'float_advance',
            'amount'                 => 5000.00,
        ]);
    }

    public function test_advance_fails_if_vault_has_insufficient_funds(): void
    {
        $vault = PettyCashAccount::getMainVault();
        $vault->update(['current_balance' => 1000.00]);

        $techWallet = PettyCashAccount::getOrCreateWalletForUser($this->technician);

        $response = $this->actingAs($this->admin)
            ->post(route('finance.petty_cash.advance'), [
                'custodian_id'     => $this->technician->id,
                'amount'           => 5000.00,
                'transaction_date' => now()->toDateString(),
                'notes'            => 'Advance request exceeding safe balance',
            ]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['amount']);

        $this->assertEquals(1000.00, $vault->fresh()->current_balance);
        $this->assertEquals(0.00, $techWallet->fresh()->current_balance);
    }

    public function test_recording_field_collection_increments_technician_wallet_and_links_job(): void
    {
        $techWallet = PettyCashAccount::getOrCreateWalletForUser($this->technician);
        $techWallet->update(['current_balance' => 1500.00]);

        $lead = Lead::create([
            'customer_name' => 'Metro Retailers',
            'phone'         => '9876543210',
            'site_address'  => 'T Nagar, Chennai',
        ]);

        $quote = Quotation::create([
            'lead_id'        => $lead->id,
            'quotation_no'   => 'QT-CASH-001',
            'quotation_date' => now(),
            'subtotal'       => 10000,
            'total'          => 11800,
            'status'         => 'accepted',
        ]);

        $job = InstallationJob::create([
            'job_no'         => 'JOB-2026-001',
            'quotation_id'   => $quote->id,
            'lead_id'        => $lead->id,
            'title'          => '4 Ch Dome Camera Installation',
            'status'         => 'in_progress',
            'scheduled_date' => now(),
        ]);

        $invoice = Invoice::create([
            'quotation_id'        => $quote->id,
            'installation_job_id' => $job->id,
            'invoice_no'          => 'INV-2026-099',
            'invoice_date'        => now(),
            'due_date'            => now()->addDays(7),
            'subtotal'            => 10000,
            'tax_amount'          => 1800,
            'total'               => 11800,
            'paid_amount'         => 0,
            'status'              => 'unpaid',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('finance.petty_cash.collection'), [
                'custodian_id'      => $this->technician->id,
                'amount'            => 3500.00,
                'transaction_date'  => now()->toDateString(),
                'customer_name'     => 'Metro Retailers',
                'job_id'            => $job->id,
                'invoice_id'        => $invoice->id,
                'receipt_reference' => 'RCPT-001',
                'notes'             => 'Cash collected on-site upon partial camera installation',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(5000.00, $techWallet->fresh()->current_balance);

        $this->assertDatabaseHas('petty_cash_transactions', [
            'petty_cash_account_id' => $techWallet->id,
            'transaction_type'      => 'field_collection',
            'amount'                => 3500.00,
            'vendor_payee_name'     => 'Metro Retailers',
            'related_job_id'        => $job->id,
            'related_invoice_id'    => $invoice->id,
        ]);
    }

    public function test_recording_direct_expense_deducts_balance_and_stores_receipt_attachment(): void
    {
        Storage::fake('public');

        $techWallet = PettyCashAccount::getOrCreateWalletForUser($this->technician);
        $techWallet->update(['current_balance' => 4000.00]);

        $receipt = UploadedFile::fake()->create('hardware_receipt.pdf', 120);

        $response = $this->actingAs($this->admin)
            ->post(route('finance.petty_cash.expense'), [
                'petty_cash_account_id' => $techWallet->id,
                'amount'                => 850.00,
                'transaction_date'      => now()->toDateString(),
                'category'              => 'hardware_conduits',
                'vendor_payee_name'     => 'Royal Electricals & Hardware',
                'bill_receipt_no'       => 'BILL-889',
                'notes'                 => 'Purchased PVC conduits and anchor bolts for site mounting',
                'receipt_photo'         => $receipt,
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(3150.00, $techWallet->fresh()->current_balance);

        $txn = PettyCashTransaction::where('category', 'hardware_conduits')->first();
        $this->assertNotNull($txn);
        $this->assertEquals(850.00, $txn->amount);
        $this->assertEquals('Royal Electricals & Hardware', $txn->vendor_payee_name);
        $this->assertNotNull($txn->receipt_photo_path);

        Storage::disk('public')->assertExists($txn->receipt_photo_path);
    }

    public function test_expense_fails_if_exceeds_available_balance(): void
    {
        $techWallet = PettyCashAccount::getOrCreateWalletForUser($this->technician);
        $techWallet->update(['current_balance' => 500.00]);

        $response = $this->actingAs($this->admin)
            ->post(route('finance.petty_cash.expense'), [
                'petty_cash_account_id' => $techWallet->id,
                'amount'                => 1200.00,
                'transaction_date'      => now()->toDateString(),
                'category'              => 'travel_fuel',
                'vendor_payee_name'     => 'HPCL Petrol Bunk',
                'notes'                 => 'Vehicle fuel refill',
            ]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['amount']);

        $this->assertEquals(500.00, $techWallet->fresh()->current_balance);
    }

    public function test_technician_can_handover_cash_to_office_safe_or_bank_deposit(): void
    {
        $vault = PettyCashAccount::getMainVault();
        $vault->update(['current_balance' => 10000.00]);

        $techWallet = PettyCashAccount::getOrCreateWalletForUser($this->technician);
        $techWallet->update(['current_balance' => 8000.00]);

        // Handover to vault
        $response = $this->actingAs($this->admin)
            ->post(route('finance.petty_cash.handover'), [
                'source_account_id' => $techWallet->id,
                'destination_type'  => 'main_vault',
                'amount'            => 5000.00,
                'transaction_date'  => now()->toDateString(),
                'notes'             => 'End-of-week cash handover from field collections',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(15000.00, $vault->fresh()->current_balance);
        $this->assertEquals(3000.00, $techWallet->fresh()->current_balance);

        // Direct Handover to Bank
        $responseBank = $this->actingAs($this->admin)
            ->post(route('finance.petty_cash.handover'), [
                'source_account_id' => $techWallet->id,
                'destination_type'  => 'bank_deposit',
                'bank_name'         => 'HDFC Bank - Current A/C 5020001234',
                'bank_ack_no'       => 'CDM-987654',
                'amount'            => 2000.00,
                'transaction_date'  => now()->toDateString(),
                'notes'             => 'Direct CDM cash deposit to company account',
            ]);

        $responseBank->assertRedirect();
        $this->assertEquals(1000.00, $techWallet->fresh()->current_balance);
    }

    public function test_physical_cash_reconciliation_calculates_denominations_and_variance(): void
    {
        $vault = PettyCashAccount::getMainVault();
        $vault->update(['current_balance' => 7850.00]);

        // Test 1: Exact Match (500x15 = 7500, 200x1 = 200, 100x1 = 100, 50x1 = 50 => Total 7850)
        $responseMatch = $this->actingAs($this->admin)
            ->post(route('finance.petty_cash.reconcile.store', $vault), [
                'reconciliation_date'  => now()->toDateString(),
                'reconciliation_notes' => 'Daily vault safe physical count',
                'denominations'        => [
                    '2000'  => 0,
                    '500'   => 15,
                    '200'   => 1,
                    '100'   => 1,
                    '50'    => 1,
                    '20'    => 0,
                    '10'    => 0,
                    '5'     => 0,
                    '2'     => 0,
                    '1'     => 0,
                    'coins' => 0,
                ],
            ]);

        $responseMatch->assertRedirect(route('finance.petty_cash.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('daily_cash_reconciliations', [
            'petty_cash_account_id'    => $vault->id,
            'system_expected_balance'  => 7850.00,
            'physical_counted_balance' => 7850.00,
            'variance_amount'          => 0.00,
            'variance_status'          => 'matched',
        ]);

        // Test 2: Shortage (Expected 7850, Physical count 500x15 = 7500 => Shortage 350)
        $responseShort = $this->actingAs($this->admin)
            ->post(route('finance.petty_cash.reconcile.store', $vault), [
                'reconciliation_date'  => now()->toDateString(),
                'reconciliation_notes' => 'Minor shortage in loose coins',
                'denominations'        => [
                    '500'   => 15,
                ],
            ]);

        $responseShort->assertRedirect(route('finance.petty_cash.index'));

        $this->assertDatabaseHas('daily_cash_reconciliations', [
            'petty_cash_account_id'    => $vault->id,
            'physical_counted_balance' => 7500.00,
            'variance_amount'          => -350.00,
            'variance_status'          => 'shortage',
        ]);
    }

    public function test_account_ledger_displays_running_balance_correctly(): void
    {
        $techWallet = PettyCashAccount::getOrCreateWalletForUser($this->technician);
        $vault = PettyCashAccount::getMainVault();
        $vault->update(['current_balance' => 50000.00]);

        // Transaction 1: Advance 5000
        $this->actingAs($this->admin)->post(route('finance.petty_cash.advance'), [
            'custodian_id'     => $this->technician->id,
            'amount'           => 5000.00,
            'transaction_date' => now()->subDays(2)->toDateString(),
            'notes'            => 'Weekly operational float',
        ]);

        // Transaction 2: Expense 1200
        $this->actingAs($this->admin)->post(route('finance.petty_cash.expense'), [
            'petty_cash_account_id' => $techWallet->id,
            'amount'                => 1200.00,
            'transaction_date'      => now()->subDay()->toDateString(),
            'category'              => 'hardware_conduits',
            'vendor_payee_name'     => 'Hardware Mart',
            'notes'                 => 'RJ45 connectors and patch cords',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('finance.petty_cash.ledger', [
                'account'   => $techWallet->id,
                'from_date' => now()->subDays(5)->toDateString(),
                'to_date'   => now()->addDay()->toDateString(),
            ]));

        $response->assertOk()
            ->assertSee('Account Statement')
            ->assertSee('Running Ledger')
            ->assertSee('Weekly operational float')
            ->assertSee('RJ45 connectors and patch cords')
            ->assertSee('5,000.00')
            ->assertSee('1,200.00')
            ->assertSee('3,800.00'); // Closing balance
    }

    public function test_admin_can_download_voucher_pdf(): void
    {
        $vault = PettyCashAccount::getMainVault();
        $vault->update(['current_balance' => 10000.00]);

        $txn = PettyCashTransaction::create([
            'voucher_no'            => PettyCashTransaction::generateVoucherNumber(),
            'petty_cash_account_id' => $vault->id,
            'transaction_type'      => 'direct_expense',
            'category'              => 'office_supplies',
            'amount'                => 450.00,
            'transaction_date'      => now(),
            'notes'                 => 'Stationery and thermal printer rolls for billing desk',
            'vendor_payee_name'     => 'City Stationers',
            'user_id'               => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('finance.petty_cash.voucher.pdf', $txn));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_admin_can_export_cashbook_pdf_and_csv(): void
    {
        $vault = PettyCashAccount::getMainVault();
        $vault->update(['current_balance' => 15000.00]);

        // PDF Cashbook Export
        $responsePdf = $this->actingAs($this->admin)
            ->get(route('finance.petty_cash.export-pdf'));

        $responsePdf->assertOk();
        $this->assertStringContainsString('application/pdf', $responsePdf->headers->get('Content-Type'));

        // CSV Export
        $responseCsv = $this->actingAs($this->admin)
            ->get(route('finance.petty_cash.export-csv'));

        $responseCsv->assertOk();
        $this->assertEquals('text/csv; charset=UTF-8', $responseCsv->headers->get('Content-Type'));
    }
}
