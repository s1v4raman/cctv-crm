<?php

namespace Tests\Feature;

use App\Models\InstalledEquipment;
use App\Models\Lead;
use App\Models\Product;
use App\Models\RmaClaim;
use App\Models\ServiceTicket;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RmaManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private Supplier $supplier;
    private Product $product;
    private Lead $lead;
    private InstalledEquipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);

        $this->supplier = Supplier::create([
            'name'           => 'Hikvision Central Depot',
            'company_name'   => 'Prama Hikvision India Pvt Ltd',
            'contact_person' => 'Vikram Malhotra',
            'email'          => 'rma@hikvision-india.com',
            'phone'          => '022-28491234',
            'city'           => 'Mumbai',
            'state'          => 'Maharashtra',
            'is_active'      => true,
        ]);

        $this->product = Product::create([
            'name'           => '4K 8MP DarkFighter Dome Camera',
            'sku'            => 'DS-2CD2185FWD-I',
            'model_no'       => 'DS-2CD2185FWD-I',
            'unit_price'     => 6800,
            'cost_price'     => 4900,
            'stock_quantity' => 10,
            'is_active'      => true,
        ]);

        $this->lead = Lead::create([
            'customer_name' => 'Apex Corporate Towers',
            'email'         => 'security@apextowers.in',
            'phone'         => '9876500001',
            'site_address'  => 'Tower B, Cyber City, Gurgaon',
            'status'        => 'won',
        ]);

        $this->equipment = InstalledEquipment::create([
            'lead_id'                      => $this->lead->id,
            'product_id'                   => $this->product->id,
            'equipment_name'               => 'Main Lobby Dome Cam 01',
            'serial_number'                => 'HKV-2026-98124',
            'mac_address'                  => '54:C8:01:A9:11:88',
            'location_tag'                 => 'Main Entrance Lobby',
            'installation_date'            => now()->subMonths(6),
            'manufacturer_warranty_expiry' => now()->addMonths(18),
            'service_warranty_expiry'      => now()->addMonths(6),
            'status'                       => 'active',
        ]);
    }

    public function test_user_can_view_rma_index_and_create_pages(): void
    {
        $response = $this->actingAs($this->admin)->get(route('rma.index'));
        $response->assertOk();
        $response->assertSee('Warranty Claims');

        $createResponse = $this->actingAs($this->admin)->get(route('rma.create', ['equipment_id' => $this->equipment->id]));
        $createResponse->assertOk();
        $createResponse->assertSee('Main Lobby Dome Cam 01');
        $createResponse->assertSee('HKV-2026-98124');
    }

    public function test_creating_rma_sets_equipment_status_to_under_repair(): void
    {
        $response = $this->actingAs($this->staff)->post(route('rma.store'), [
            'supplier_id'              => $this->supplier->id,
            'product_id'               => $this->product->id,
            'installed_equipment_id'   => $this->equipment->id,
            'lead_id'                  => $this->lead->id,
            'faulty_serial_number'     => $this->equipment->serial_number,
            'faulty_mac_address'       => $this->equipment->mac_address,
            'fault_category'           => 'sensor_defect',
            'warranty_status_at_claim' => 'under_warranty',
            'issue_description'        => 'Video feed displays purple noise artifacts when switching to day mode.',
            'vendor_rma_ref'           => 'HIK-RMA-9001',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('rma_claims', [
            'supplier_id'          => $this->supplier->id,
            'faulty_serial_number' => 'HKV-2026-98124',
            'fault_category'       => 'sensor_defect',
            'status'               => 'draft',
        ]);

        // Equipment status must be under_repair
        $this->assertEquals('under_repair', $this->equipment->fresh()->status);
    }

    public function test_dispatching_rma_updates_courier_info_and_status(): void
    {
        $rma = RmaClaim::create([
            'rma_no'                   => 'RMA-2026-0001',
            'supplier_id'              => $this->supplier->id,
            'product_id'               => $this->product->id,
            'installed_equipment_id'   => $this->equipment->id,
            'lead_id'                  => $this->lead->id,
            'faulty_serial_number'     => $this->equipment->serial_number,
            'fault_category'           => 'no_power',
            'warranty_status_at_claim' => 'under_warranty',
            'issue_description'        => 'No power on 12V DC input.',
            'status'                   => 'draft',
            'created_by'               => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('rma.dispatch', $rma), [
            'shipping_courier' => 'DTDC Express',
            'tracking_number'  => 'AWB-897123984',
            'dispatched_date'  => now()->format('Y-m-d'),
            'notes'            => 'Handed to courier pickup agent.',
        ]);

        $response->assertRedirect();

        $freshRma = $rma->fresh();
        $this->assertEquals('shipped_to_vendor', $freshRma->status);
        $this->assertEquals('DTDC Express', $freshRma->shipping_courier);
        $this->assertEquals('AWB-897123984', $freshRma->tracking_number);

        // Verify status log
        $this->assertDatabaseHas('rma_status_logs', [
            'rma_claim_id' => $rma->id,
            'from_status'  => 'draft',
            'to_status'    => 'shipped_to_vendor',
        ]);
    }

    public function test_recording_replacement_resolution_updates_equipment_serial_number(): void
    {
        $rma = RmaClaim::create([
            'rma_no'                   => 'RMA-2026-0001',
            'supplier_id'              => $this->supplier->id,
            'product_id'               => $this->product->id,
            'installed_equipment_id'   => $this->equipment->id,
            'lead_id'                  => $this->lead->id,
            'faulty_serial_number'     => $this->equipment->serial_number,
            'fault_category'           => 'sensor_defect',
            'warranty_status_at_claim' => 'under_warranty',
            'issue_description'        => 'Sensor defect',
            'status'                   => 'in_vendor_repair',
            'created_by'               => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('rma.resolution', $rma), [
            'resolution_type'             => 'replacement',
            'replacement_serial_number'   => 'HKV-2026-NEW-9999',
            'replacement_mac_address'     => '54:C8:01:FF:00:11',
            'replacement_warranty_expiry' => now()->addYears(2)->format('Y-m-d'),
            'received_from_vendor_date'   => now()->format('Y-m-d'),
            'vendor_repair_notes'         => 'Brand new sealed unit issued under warranty replacement.',
        ]);

        $response->assertRedirect();

        // Verify RMA claim status
        $freshRma = $rma->fresh();
        $this->assertEquals('replaced', $freshRma->status);
        $this->assertEquals('HKV-2026-NEW-9999', $freshRma->replacement_serial_number);

        // Verify that customer's InstalledEquipment was auto-updated with the replacement S/N
        $freshEquipment = $this->equipment->fresh();
        $this->assertEquals('HKV-2026-NEW-9999', $freshEquipment->serial_number);
        $this->assertEquals('54:C8:01:FF:00:11', $freshEquipment->mac_address);
        $this->assertEquals('active', $freshEquipment->status);
        $this->assertStringContainsString('Replaced under RMA RMA-2026-0001', $freshEquipment->notes);
    }

    public function test_recording_repaired_unit_resolution_resets_equipment_to_active(): void
    {
        $this->equipment->update(['status' => 'under_repair']);

        $rma = RmaClaim::create([
            'rma_no'                   => 'RMA-2026-0002',
            'supplier_id'              => $this->supplier->id,
            'product_id'               => $this->product->id,
            'installed_equipment_id'   => $this->equipment->id,
            'lead_id'                  => $this->lead->id,
            'faulty_serial_number'     => $this->equipment->serial_number,
            'fault_category'           => 'ir_led_failure',
            'warranty_status_at_claim' => 'under_warranty',
            'issue_description'        => 'IR cut filter repaired',
            'status'                   => 'in_vendor_repair',
            'created_by'               => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('rma.resolution', $rma), [
            'resolution_type'           => 'repaired_unit',
            'received_from_vendor_date' => now()->format('Y-m-d'),
            'vendor_repair_notes'       => 'Replaced IR board and tested overnight night-vision OK.',
        ]);

        $response->assertRedirect();

        $freshRma = $rma->fresh();
        $this->assertEquals('repaired', $freshRma->status);
        $this->assertEquals('active', $this->equipment->fresh()->status);
    }

    public function test_download_rma_vendor_delivery_challan_pdf(): void
    {
        $rma = RmaClaim::create([
            'rma_no'                   => 'RMA-2026-0001',
            'supplier_id'              => $this->supplier->id,
            'product_id'               => $this->product->id,
            'installed_equipment_id'   => $this->equipment->id,
            'lead_id'                  => $this->lead->id,
            'faulty_serial_number'     => $this->equipment->serial_number,
            'fault_category'           => 'sensor_defect',
            'warranty_status_at_claim' => 'under_warranty',
            'issue_description'        => 'Purple video noise artifacts.',
            'status'                   => 'shipped_to_vendor',
            'shipping_courier'         => 'Blue Dart',
            'tracking_number'          => 'BD9821389',
            'dispatched_date'          => now(),
            'created_by'               => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('rma.dispatch-pdf', $rma));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
