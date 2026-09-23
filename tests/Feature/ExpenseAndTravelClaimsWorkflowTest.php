<?php

namespace Tests\Feature;

use App\Models\ExpenseClaim;
use App\Models\InstallationJob;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\ServiceTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExpenseAndTravelClaimsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employee;
    private User $technician;
    private InstallationJob $job;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'     => 'Super Admin',
            'email'    => 'admin@cctv.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        $this->employee = User::factory()->create([
            'name'     => 'Kesavan Employee',
            'email'    => 'kesavan@gmail.com',
            'password' => Hash::make('kesavan123'),
            'role'     => 'staff',
        ]);

        $this->technician = User::factory()->create([
            'name'     => 'Bob Miller',
            'email'    => 'technician@cctv.com',
            'password' => Hash::make('password123'),
            'role'     => 'technician',
        ]);

        $lead = Lead::create([
            'customer_name' => 'Metro Tech Park',
            'phone'         => '8789076658',
            'email'         => 'facilities@metrotech.com',
            'status'        => 'won',
        ]);

        $quotation = Quotation::create([
            'lead_id'      => $lead->id,
            'quotation_no' => 'QT-2026-EXP01',
            'subtotal'     => 50000,
            'tax_amount'   => 9000,
            'total'        => 59000,
            'status'       => 'accepted',
        ]);

        $this->job = InstallationJob::create([
            'quotation_id'           => $quotation->id,
            'job_no'                 => 'JOB-2026-EXP01',
            'assigned_technician_id' => $this->technician->id,
            'status'                 => 'in_progress',
            'scheduled_date'         => now()->toDateString(),
        ]);
    }

    /**
     * 1. Technician can view the Expense & Travel Claims hub.
     */
    public function test_technician_can_view_expense_claims_hub(): void
    {
        $response = $this->actingAs($this->technician)->get(route('finance.expenses.index'));

        $response->assertOk();
        $response->assertSee('Field Expenses &amp; Travel Claims Hub', false);
        $response->assertSee('Claims This Month');
    }

    /**
     * 2. Technician can submit a fuel claim with automatic KM calculation.
     */
    public function test_technician_can_submit_fuel_claim_with_km_auto_calculation(): void
    {
        $response = $this->actingAs($this->technician)->post(route('finance.expenses.store'), [
            'expense_category'   => 'fuel_travel',
            'expense_date'       => now()->toDateString(),
            'travel_from'        => 'Central Office',
            'travel_to'          => 'Metro Tech Park Client Site',
            'travel_distance_km' => 25.0,
            'rate_per_km'        => 6.0,
            'amount'             => 150.00,
            'installation_job_id'=> $this->job->id,
            'description'        => 'Two-way travel for camera cabling & NVR mount.',
        ]);

        $response->assertRedirect(route('finance.expenses.index', ['tab' => 'my_claims']));
        
        $claim = ExpenseClaim::where('user_id', $this->technician->id)->first();
        $this->assertNotNull($claim);
        $this->assertEquals('fuel_travel', $claim->expense_category);
        $this->assertEquals(150.00, (float) $claim->amount);
        $this->assertEquals(25.0, (float) $claim->travel_distance_km);
        $this->assertEquals($this->job->id, $claim->installation_job_id);
        $this->assertEquals('pending', $claim->status);
    }

    /**
     * 3. Employee can submit hardware expense claim with receipt upload.
     */
    public function test_employee_can_submit_hardware_claim_with_receipt_upload(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('invoice_receipt.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->employee)->post(route('finance.expenses.store'), [
            'expense_category' => 'hardware_tools',
            'expense_date'     => now()->toDateString(),
            'amount'           => 1250.00,
            'description'      => 'Purchased 2x 8-port Gigabit switches from local supplier.',
            'receipt'          => $file,
        ]);

        $response->assertRedirect(route('finance.expenses.index', ['tab' => 'my_claims']));
        
        $claim = ExpenseClaim::where('user_id', $this->employee->id)->where('expense_category', 'hardware_tools')->first();
        $this->assertNotNull($claim);
        $this->assertEquals(1250.00, (float) $claim->amount);
        $this->assertNotNull($claim->receipt_path);
        Storage::disk('public')->assertExists($claim->receipt_path);
    }

    /**
     * 4. Employee can cancel their own pending claim.
     */
    public function test_employee_can_cancel_own_pending_claim(): void
    {
        $claim = ExpenseClaim::create([
            'claim_no'         => 'EXP-202609-001',
            'user_id'          => $this->employee->id,
            'expense_category' => 'food_lodging',
            'expense_date'     => now()->toDateString(),
            'amount'           => 350.00,
            'description'      => 'Night shift meal',
            'status'           => 'pending',
        ]);

        $response = $this->actingAs($this->employee)->delete(route('finance.expenses.cancel', $claim));

        $response->assertRedirect();
        $this->assertEquals('cancelled', $claim->fresh()->status);
    }

    /**
     * 5. Employee cannot cancel someone else's claim.
     */
    public function test_employee_cannot_cancel_others_claim(): void
    {
        $claim = ExpenseClaim::create([
            'claim_no'         => 'EXP-202609-002',
            'user_id'          => $this->technician->id,
            'expense_category' => 'toll_parking',
            'expense_date'     => now()->toDateString(),
            'amount'           => 80.00,
            'description'      => 'Toll plaza',
            'status'           => 'pending',
        ]);

        $response = $this->actingAs($this->employee)->delete(route('finance.expenses.cancel', $claim));

        $response->assertStatus(403);
        $this->assertEquals('pending', $claim->fresh()->status);
    }

    /**
     * 6. Admin can approve an expense claim.
     */
    public function test_admin_can_approve_claim(): void
    {
        $claim = ExpenseClaim::create([
            'claim_no'         => 'EXP-202609-003',
            'user_id'          => $this->technician->id,
            'expense_category' => 'fuel_travel',
            'expense_date'     => now()->toDateString(),
            'amount'           => 300.00,
            'description'      => 'Site survey visit',
            'status'           => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('finance.expenses.approve', $claim));

        $response->assertRedirect();
        $this->assertEquals('approved', $claim->fresh()->status);
        $this->assertEquals($this->admin->id, $claim->fresh()->actioned_by);
    }

    /**
     * 7. Admin can reject an expense claim with reason.
     */
    public function test_admin_can_reject_claim_with_reason(): void
    {
        $claim = ExpenseClaim::create([
            'claim_no'         => 'EXP-202609-004',
            'user_id'          => $this->technician->id,
            'expense_category' => 'other',
            'expense_date'     => now()->toDateString(),
            'amount'           => 900.00,
            'description'      => 'Miscellaneous charges',
            'status'           => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('finance.expenses.reject', $claim), [
            'rejection_reason' => 'Official GST receipt missing for expense above ₹500.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('rejected', $claim->fresh()->status);
        $this->assertEquals('Official GST receipt missing for expense above ₹500.', $claim->fresh()->rejection_reason);
    }

    /**
     * 8. Admin can disburse / mark approved claim as paid.
     */
    public function test_admin_can_mark_claim_as_paid(): void
    {
        $claim = ExpenseClaim::create([
            'claim_no'         => 'EXP-202609-005',
            'user_id'          => $this->employee->id,
            'expense_category' => 'hardware_tools',
            'expense_date'     => now()->toDateString(),
            'amount'           => 450.00,
            'description'      => 'RJ45 Crimping tool',
            'status'           => 'approved',
            'actioned_by'      => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('finance.expenses.pay', $claim), [
            'payment_method'    => 'upi',
            'payment_reference' => 'UPI-9921448821',
        ]);

        $response->assertRedirect();
        $this->assertEquals('paid', $claim->fresh()->status);
        $this->assertEquals('upi', $claim->fresh()->payment_method);
        $this->assertEquals('UPI-9921448821', $claim->fresh()->payment_reference);
        $this->assertNotNull($claim->fresh()->paid_at);
    }

    /**
     * 9. Non-admin cannot approve or reject claims.
     */
    public function test_non_admin_cannot_approve_or_pay_claims(): void
    {
        $claim = ExpenseClaim::create([
            'claim_no'         => 'EXP-202609-006',
            'user_id'          => $this->technician->id,
            'expense_category' => 'fuel_travel',
            'expense_date'     => now()->toDateString(),
            'amount'           => 120.00,
            'description'      => 'Fuel',
            'status'           => 'pending',
        ]);

        $this->actingAs($this->employee)
            ->patch(route('finance.expenses.approve', $claim))
            ->assertStatus(403);

        $this->actingAs($this->technician)
            ->post(route('finance.expenses.pay', $claim), ['payment_method' => 'cash'])
            ->assertStatus(403);
    }
}
