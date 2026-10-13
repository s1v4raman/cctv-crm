<?php

namespace Tests\Feature;

use App\Models\AmcContract;
use App\Models\InstalledEquipment;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\RmaClaim;
use App\Models\ServiceTicket;
use App\Models\Site;
use App\Models\SiteSurvey;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllModulesDeleteButtonsVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_lead_delete_button_works(): void
    {
        $lead = Lead::create([
            'customer_name' => 'To Delete Lead',
            'phone' => '9988776655',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('leads.destroy', $lead));
        $response->assertRedirect(route('leads.index'));
        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
    }

    public function test_quotation_delete_button_works(): void
    {
        $lead = Lead::create(['customer_name' => 'Quote Lead', 'phone' => '9988776655']);
        $quotation = Quotation::create([
            'lead_id' => $lead->id,
            'quotation_no' => 'QT-DEL-001',
            'quotation_date' => now(),
            'subtotal' => 1000,
            'discount' => 0,
            'tax_percent' => 18,
            'tax_amount' => 180,
            'total' => 1180,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('quotations.destroy', $quotation));
        $response->assertRedirect(route('quotations.index'));
        $this->assertDatabaseMissing('quotations', ['id' => $quotation->id]);
    }

    public function test_service_ticket_delete_button_works(): void
    {
        $lead = Lead::create(['customer_name' => 'Ticket Lead', 'phone' => '9988776655']);
        $ticket = ServiceTicket::create([
            'lead_id' => $lead->id,
            'ticket_no' => 'TCK-DEL-001',
            'title' => 'Offline camera issue',
            'issue_type' => 'camera_offline',
            'priority' => 'medium',
            'description' => 'Test issue description to delete',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('service-tickets.destroy', $ticket));
        $response->assertRedirect(route('service-tickets.index'));
        $this->assertDatabaseMissing('service_tickets', ['id' => $ticket->id]);
    }

    public function test_site_survey_delete_button_works(): void
    {
        $lead = Lead::create(['customer_name' => 'Survey Lead', 'phone' => '9988776655']);
        $survey = SiteSurvey::create([
            'lead_id' => $lead->id,
            'surveyed_by' => $this->admin->id,
            'survey_date' => now()->toDateString(),
            'status' => 'pending',
            'site_address' => 'Sample Site Address to test',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('site-surveys.destroy', $survey));
        $response->assertRedirect(route('site-surveys.index'));
        $this->assertDatabaseMissing('site_surveys', ['id' => $survey->id]);
    }

    public function test_installed_equipment_delete_button_works(): void
    {
        $lead = Lead::create(['customer_name' => 'Equip Lead', 'phone' => '9988776655']);
        $equipment = InstalledEquipment::create([
            'lead_id' => $lead->id,
            'equipment_name' => 'Dome Cam 4MP',
            'serial_number' => 'SN-DEL-999',
            'status' => 'active',
            'installation_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('equipment.destroy', $equipment));
        $response->assertRedirect(route('equipment.index'));
        $this->assertDatabaseMissing('installed_equipment', ['id' => $equipment->id]);
    }

    public function test_product_delete_button_works(): void
    {
        $product = Product::create([
            'name' => 'Test Product To Delete',
            'category' => 'Camera',
            'brand' => 'Hikvision',
            'unit_price' => 2500,
            'cost_price' => 1800,
            'stock_quantity' => 5,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('products.destroy', $product));
        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_supplier_delete_button_works(): void
    {
        $supplier = Supplier::create([
            'name' => 'Vendor Test Delete',
            'company_name' => 'Vendor Supplies Ltd',
            'phone' => '9888877777',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('suppliers.destroy', $supplier));
        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }

    public function test_purchase_order_delete_button_works(): void
    {
        $supplier = Supplier::create([
            'name' => 'PO Vendor',
            'phone' => '9888877777',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-DEL-001',
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'status' => 'draft',
            'total_amount' => 5000,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('purchase-orders.destroy', $po));
        $response->assertRedirect(route('purchase-orders.index'));
        $this->assertDatabaseMissing('purchase_orders', ['id' => $po->id]);
    }

    public function test_rma_claim_delete_button_works(): void
    {
        $lead = Lead::create(['customer_name' => 'RMA Lead', 'phone' => '9988776655']);
        $supplier = Supplier::create([
            'name' => 'RMA Supplier',
            'phone' => '9888877777',
        ]);
        $equipment = InstalledEquipment::create([
            'lead_id' => $lead->id,
            'equipment_name' => 'Faulty Camera',
            'serial_number' => 'SN-RMA-DEL',
            'status' => 'faulty',
            'installation_date' => now()->toDateString(),
        ]);

        $rma = RmaClaim::create([
            'rma_no' => 'RMA-DEL-001',
            'supplier_id' => $supplier->id,
            'equipment_id' => $equipment->id,
            'lead_id' => $lead->id,
            'created_by' => $this->admin->id,
            'product_name' => 'Faulty Camera',
            'faulty_serial_number' => 'SN-RMA-DEL',
            'issue_description' => 'Sensor malfunction',
            'status' => 'draft',
            'warranty_status_at_claim' => 'under_warranty',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('rma.destroy', $rma));
        $response->assertRedirect(route('rma.index'));
        $this->assertDatabaseMissing('rma_claims', ['id' => $rma->id]);
    }

    public function test_amc_contract_delete_button_works(): void
    {
        $lead = Lead::create(['customer_name' => 'AMC Lead', 'phone' => '9988776655']);
        $amc = AmcContract::create([
            'lead_id' => $lead->id,
            'contract_no' => 'AMC-DEL-001',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'visits_per_year' => 4,
            'status' => 'active',
            'value' => 12000,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('amcs.destroy', $amc));
        $response->assertRedirect(route('amcs.index'));
        $this->assertDatabaseMissing('amc_contracts', ['id' => $amc->id]);
    }

    public function test_site_delete_button_works(): void
    {
        $site = Site::create([
            'site_code' => 'SIT-DEL-001',
            'name' => 'Delete Test Site',
            'client_name' => 'Test Client',
            'address' => '123 Site Lane',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'pincode' => '560001',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('sites.destroy', $site));
        $response->assertRedirect(route('sites.index'));
        $this->assertDatabaseMissing('sites', ['id' => $site->id, 'deleted_at' => null]);
    }

    public function test_worker_delete_button_works(): void
    {
        $worker = Worker::create([
            'name' => 'Daily Wage Worker Delete',
            'phone' => '9888800000',
            'daily_rate' => 600,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('workers.destroy', $worker));
        $response->assertRedirect(route('workers.index'));
        $this->assertSoftDeleted('workers', ['id' => $worker->id]);
    }

    public function test_project_delete_button_works(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-DEL-001',
            'title' => 'Project To Delete',
            'project_type' => 'hardware_cctv',
            'status' => 'draft',
            'priority' => 'low',
            'budget' => 20000,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('projects.destroy', $project));
        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseMissing('projects', ['id' => $project->id, 'deleted_at' => null]);
    }

    public function test_admin_user_delete_button_works(): void
    {
        $staffUser = User::factory()->create([
            'role' => 'staff',
            'name' => 'Staff To Delete',
            'email' => 'staff.delete@example.com',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $staffUser));
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $staffUser->id]);
    }
}
