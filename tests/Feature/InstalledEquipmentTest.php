<?php

namespace Tests\Feature;

use App\Models\InstalledEquipment;
use App\Models\InstallationJob;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstalledEquipmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_user_can_view_equipment_registry(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $lead = Lead::create([
            'customer_name' => 'Apex Corporate Hub',
            'phone' => '9876543210',
            'status' => 'won',
        ]);

        $equipment = InstalledEquipment::create([
            'lead_id' => $lead->id,
            'equipment_name' => 'Main Gate PTZ Camera 4K',
            'serial_number' => 'SN-PTZ-889900',
            'mac_address' => '00:1A:2B:3C:4D:5E',
            'location_tag' => 'North Gate',
            'installation_date' => now()->toDateString(),
            'manufacturer_warranty_expiry' => now()->addYears(2)->toDateString(),
            'service_warranty_expiry' => now()->addYear()->toDateString(),
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('equipment.index'));

        $response->assertOk();
        $response->assertSee('Installed Equipment & Serial Tracking', false);
        $response->assertSee('SN-PTZ-889900');
        $response->assertSee('Apex Corporate Hub');
        $response->assertSee('Main Gate PTZ Camera 4K');
    }

    public function test_can_register_equipment_with_auto_stock_deduction(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $lead = Lead::create([
            'customer_name' => 'City Hospital',
            'phone' => '9123456780',
            'status' => 'won',
        ]);

        $product = Product::create([
            'name' => '5MP ColorVu Dome Camera',
            'unit' => 'Nos',
            'cost_price' => 3000,
            'unit_price' => 4200,
            'stock_quantity' => 10,
            'default_warranty_months' => 36,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('equipment.store'), [
                'lead_id' => $lead->id,
                'product_id' => $product->id,
                'equipment_name' => 'ICU Entrance Dome',
                'serial_number' => 'SN-CV5M-102938',
                'mac_address' => '11:22:33:44:55:66',
                'location_tag' => 'Floor 2 ICU',
                'installation_date' => '2026-08-30',
                'status' => 'active',
                'deduct_stock' => '1',
            ]);

        $response->assertRedirect(route('equipment.index'));

        // Check equipment created with calculated 36 month warranty
        $equipment = InstalledEquipment::where('serial_number', 'SN-CV5M-102938')->first();
        $this->assertNotNull($equipment);
        $this->assertEquals($lead->id, $equipment->lead_id);
        $this->assertEquals('ICU Entrance Dome', $equipment->equipment_name);
        $this->assertEquals('2029-08-30', $equipment->manufacturer_warranty_expiry->format('Y-m-d'));
        $this->assertEquals('2027-08-30', $equipment->service_warranty_expiry->format('Y-m-d'));

        // Check stock was decremented from 10 to 9
        $this->assertEquals(9, $product->fresh()->stock_quantity);

        // Check stock movement was logged
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'job_installation',
            'quantity' => -1,
            'balance_after' => 9,
        ]);
    }

    public function test_can_view_equipment_show_page(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $lead = Lead::create([
            'customer_name' => 'Grand Hotel',
            'phone' => '9988776655',
            'site_address' => '123 Beach Road, Goa',
            'status' => 'won',
        ]);

        $equipment = InstalledEquipment::create([
            'lead_id' => $lead->id,
            'equipment_name' => 'Reception 360 Fisheye Camera',
            'serial_number' => 'SN-FISH-776655',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'installation_date' => now()->toDateString(),
            'manufacturer_warranty_expiry' => now()->addDays(15)->toDateString(), // Expiring soon
            'service_warranty_expiry' => now()->subDay()->toDateString(), // Expired
            'status' => 'active',
            'notes' => 'Static IP 192.168.1.150',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('equipment.show', $equipment));

        $response->assertOk();
        $response->assertSee('SN-FISH-776655');
        $response->assertSee('Grand Hotel');
        $response->assertSee('Reception 360 Fisheye Camera');
        $response->assertSee('⚠️ Expiring Soon');
        $response->assertSee('Static IP 192.168.1.150');
    }

    public function test_lead_show_page_displays_installed_equipment(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $lead = Lead::create([
            'customer_name' => 'Metro Supermarket',
            'phone' => '9876500000',
            'status' => 'won',
        ]);

        InstalledEquipment::create([
            'lead_id' => $lead->id,
            'equipment_name' => 'Aisle 1 Dome Camera',
            'serial_number' => 'SN-METRO-001',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('leads.show', $lead));

        $response->assertOk();
        $response->assertSee('Installed CCTV Hardware & Serial Numbers', false);
        $response->assertSee('SN-METRO-001');
        $response->assertSee('Aisle 1 Dome Camera');
    }

    public function test_technician_can_access_equipment_create_and_store(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);

        $lead = Lead::create([
            'customer_name' => 'Metro Supermarket',
            'phone' => '9876500000',
            'status' => 'won',
        ]);

        $createResponse = $this->actingAs($technician)->get(route('equipment.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('Register Installed Hardware', false);

        $storeResponse = $this->actingAs($technician)->post(route('equipment.store'), [
            'lead_id' => $lead->id,
            'equipment_name' => 'Cash Counter Camera',
            'serial_number' => 'SN-CASH-9988',
            'status' => 'active',
        ]);

        $storeResponse->assertRedirect(route('equipment.index'));
        $this->assertDatabaseHas('installed_equipment', [
            'serial_number' => 'SN-CASH-9988',
            'equipment_name' => 'Cash Counter Camera',
        ]);
    }

    public function test_customer_cannot_access_equipment_create(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get(route('equipment.create'));
        $response->assertStatus(403);
    }
}
