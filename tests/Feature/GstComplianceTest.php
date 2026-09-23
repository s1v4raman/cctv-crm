<?php

namespace Tests\Feature;

use App\Models\ExpenseClaim;
use App\Models\GatewaySetting;
use App\Models\GstFiling;
use App\Models\InstallationJob;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GstComplianceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin User']);
        $this->staff = User::factory()->create(['role' => 'staff', 'name' => 'Staff Member']);
        $this->technician = User::factory()->create(['role' => 'technician', 'name' => 'Field Technician']);

        // Set default company GST details
        $settings = GatewaySetting::getSettings();
        $settings->update([
            'company_trade_name' => 'CCTV Security Solutions',
            'company_legal_name' => 'CCTV Security Systems Pvt Ltd',
            'company_gstin'      => '33AAAAA0000A1Z5',
            'company_pan'        => 'AAAAA0000A',
            'company_state'      => 'Tamil Nadu',
            'company_state_code' => '33',
        ]);
    }

    public function test_non_admin_cannot_access_gst_compliance_center(): void
    {
        $this->actingAs($this->staff)
            ->get(route('finance.gst.index'))
            ->assertForbidden();

        $this->actingAs($this->technician)
            ->get(route('finance.gst.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_gst_dashboard_and_switch_tabs(): void
    {
        $response = $this->actingAs($this->admin)->get(route('finance.gst.index'));
        $response->assertOk();
        $response->assertSee('GST & Tax Compliance Center', false);
        $response->assertSee('GSTR-1 & ITC Hub', false);
        $response->assertSee('33AAAAA0000A1Z5');

        // Check tabs
        $this->actingAs($this->admin)->get(route('finance.gst.index', ['tab' => 'gstr1']))->assertOk()->assertSee('TABLE 4');
        $this->actingAs($this->admin)->get(route('finance.gst.index', ['tab' => 'itc']))->assertOk()->assertSee('GSTR-2B');
        $this->actingAs($this->admin)->get(route('finance.gst.index', ['tab' => 'hsn']))->assertOk()->assertSee('Table 12: HSN-Wise Summary');
        $this->actingAs($this->admin)->get(route('finance.gst.index', ['tab' => 'filings']))->assertOk()->assertSee('GST Return Filing & Challan Audit Log', false);
    }

    public function test_admin_can_update_company_gst_profile(): void
    {
        $response = $this->actingAs($this->admin)->post(route('finance.gst.settings.update'), [
            'company_trade_name' => 'Apex CCTV Systems',
            'company_legal_name' => 'Apex Security Solutions LLP',
            'company_gstin'      => '33BBBBB1111B1Z2',
            'company_pan'        => 'BBBBB1111B',
            'company_state'      => 'Tamil Nadu',
            'company_state_code' => '33',
            'company_address'    => '456 Commercial Road, Chennai',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('gateway_settings', [
            'company_trade_name' => 'Apex CCTV Systems',
            'company_gstin'      => '33BBBBB1111B1Z2',
        ]);
    }

    public function test_gstr1_segregates_b2b_vs_b2c_and_computes_intra_vs_inter_state_taxes(): void
    {
        $currentMonth = now()->format('Y-m');

        // 1. Create B2B Lead & Invoice (Intra-state with GSTIN)
        $b2bLead = Lead::create([
            'customer_name' => 'Tata Security Branch',
            'company_legal_name' => 'Tata Sons Private Limited',
            'gstin' => '33AAACT1234T1Z8',
            'state' => 'Tamil Nadu',
            'state_code' => '33',
            'phone' => '9876543210',
        ]);

        $productCamera = Product::create([
            'name' => '4MP IP Dome Camera',
            'hsn_code' => '8525',
            'tax_rate' => 18.00,
            'unit' => 'NOS',
            'unit_price' => 5000.00,
        ]);

        $b2bQuote = Quotation::create([
            'lead_id' => $b2bLead->id,
            'quotation_no' => 'QT-B2B-01',
            'subtotal' => 10000.00,
            'discount' => 0.00,
            'tax_percent' => 18.00,
            'tax_amount' => 1800.00,
            'total' => 11800.00,
            'status' => 'accepted',
        ]);

        QuotationItem::create([
            'quotation_id' => $b2bQuote->id,
            'product_id' => $productCamera->id,
            'item_name' => '4MP IP Dome Camera',
            'quantity' => 2,
            'unit' => 'NOS',
            'unit_price' => 5000.00,
            'total' => 10000.00,
        ]);

        $b2bJob = InstallationJob::create([
            'quotation_id' => $b2bQuote->id,
            'job_no' => 'JOB-B2B-01',
            'status' => 'completed',
        ]);

        $this->actingAs($this->admin)->post(route('invoices.store', $b2bJob), [
            'invoice_date' => now()->toDateString(),
            'tax_percent'  => 18.00,
        ]);

        // 2. Create B2C Lead & Invoice (Unregistered customer)
        $b2cLead = Lead::create([
            'customer_name' => 'Ramesh Kumar',
            'phone' => '9123456780',
            'state' => 'Tamil Nadu',
            'state_code' => '33',
        ]);

        $b2cQuote = Quotation::create([
            'lead_id' => $b2cLead->id,
            'quotation_no' => 'QT-B2C-01',
            'subtotal' => 4000.00,
            'discount' => 0.00,
            'tax_percent' => 18.00,
            'tax_amount' => 720.00,
            'total' => 4720.00,
            'status' => 'accepted',
        ]);

        $b2cJob = InstallationJob::create([
            'quotation_id' => $b2cQuote->id,
            'job_no' => 'JOB-B2C-01',
            'status' => 'completed',
        ]);

        $this->actingAs($this->admin)->post(route('invoices.store', $b2cJob), [
            'invoice_date' => now()->toDateString(),
            'tax_percent'  => 18.00,
        ]);

        // View GSTR-1 tab
        $response = $this->actingAs($this->admin)->get(route('finance.gst.index', ['month' => $currentMonth, 'tab' => 'gstr1']));
        $response->assertOk();
        $response->assertSee('33AAACT1234T1Z8');
        $response->assertSee('Tata Sons Private Limited');
        $response->assertSee('10,000.00'); // B2B Taxable
        $response->assertSee('4,000.00');  // B2C Taxable

        // View HSN tab
        $hsnResponse = $this->actingAs($this->admin)->get(route('finance.gst.index', ['month' => $currentMonth, 'tab' => 'hsn']));
        $hsnResponse->assertOk();
        $hsnResponse->assertSee('8525');
    }

    public function test_itc_calculation_and_gstr3b_setoff(): void
    {
        $currentMonth = now()->format('Y-m');

        // 1. Inward PO
        $supplier = Supplier::create([
            'name' => 'Hikvision Authorized Distributor',
            'gst_number' => '33AAACH9999H1Z0',
            'state' => 'Tamil Nadu',
        ]);

        $po = PurchaseOrder::create([
            'supplier_id'          => $supplier->id,
            'po_number'            => 'PO-2026-001',
            'supplier_invoice_no'  => 'HIK-INV-8899',
            'order_date'           => now()->toDateString(),
            'subtotal'             => 20000.00,
            'tax_percent'          => 18.00,
            'tax_amount'           => 3600.00,
            'total'                => 23600.00,
            'status'               => 'received',
            'is_itc_eligible'      => true,
        ]);

        // 2. Eligible Hardware Expense Claim
        ExpenseClaim::create([
            'claim_no'         => 'EXP-202609-001',
            'user_id'          => $this->technician->id,
            'expense_category' => 'hardware_tools',
            'expense_date'     => now()->toDateString(),
            'amount'           => 1180.00, // 1000 + 180 GST
            'status'           => 'approved',
            'description'      => 'Heavy duty drill bits and cable ties',
        ]);

        // Access ITC Tab
        $itcResponse = $this->actingAs($this->admin)->get(route('finance.gst.index', ['month' => $currentMonth, 'tab' => 'itc']));
        $itcResponse->assertOk();
        $itcResponse->assertSee('PO-2026-001');
        $itcResponse->assertSee('Hikvision Authorized Distributor');
        $itcResponse->assertSee('EXP-202609-001');
        $itcResponse->assertSee('3,600.00'); // PO ITC
    }

    public function test_record_filing_and_challan(): void
    {
        $currentMonth = now()->format('Y-m');

        $response = $this->actingAs($this->admin)->post(route('finance.gst.filing.record'), [
            'period'        => $currentMonth,
            'gstr1_status'  => 'filed',
            'gstr3b_status' => 'filed',
            'tax_paid'      => 4500.00,
            'challan_no'    => 'CHAL-33-2026-99',
            'cin_number'    => '33123456789012345',
            'filing_date'   => now()->toDateString(),
            'payment_mode'  => 'online_portal',
            'notes'         => 'Filed on GST portal with acknowledgement ARN AA3309260012345',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('gst_filings', [
            'period'       => $currentMonth,
            'gstr1_status' => 'filed',
            'gstr3b_status'=> 'filed',
            'challan_no'   => 'CHAL-33-2026-99',
        ]);
    }

    public function test_export_endpoints_return_correct_files(): void
    {
        $currentMonth = now()->format('Y-m');

        // 1. JSON Export
        $jsonResp = $this->actingAs($this->admin)->get(route('finance.gst.export-json', ['month' => $currentMonth]));
        $jsonResp->assertOk();
        $jsonResp->assertHeader('Content-Type', 'application/json');
        $this->assertArrayHasKey('gstin', json_decode($jsonResp->getContent(), true));

        // 2. GSTR-1 CSV Export
        $csvResp = $this->actingAs($this->admin)->get(route('finance.gst.export-csv', ['month' => $currentMonth]));
        $csvResp->assertOk();
        $this->assertStringContainsString('GSTR-1 OUTWARD SALES RETURN', $csvResp->getContent());

        // 3. ITC CSV Export
        $itcCsvResp = $this->actingAs($this->admin)->get(route('finance.gst.export-itc-csv', ['month' => $currentMonth]));
        $itcCsvResp->assertOk();
        $this->assertStringContainsString('INPUT TAX CREDIT (ITC)', $itcCsvResp->getContent());

        // 4. PDF Export
        $pdfResp = $this->actingAs($this->admin)->get(route('finance.gst.export-pdf', ['month' => $currentMonth]));
        $pdfResp->assertOk();
        $pdfResp->assertHeader('Content-Type', 'application/pdf');
    }
}
