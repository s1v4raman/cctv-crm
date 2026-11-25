<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentBarcodeMultiScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_equipment_create_page_renders_four_scanner_buttons_for_admin_and_staff(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('equipment.create'));
        $response->assertOk();

        // Single Hardware Scan bar with Mobile Scan & Barcode Machine buttons
        $response->assertSee('serial_number_input');
        $response->assertSee('mac_address_input');
        $response->assertSee("openBarcodeScanner('equipment-barcode-modal', 'serial_number_input'", false);
        $response->assertSee('Single Hardware Scan');
        $response->assertSee('Barcode Machine');
        $response->assertSee('Mobile Scan');

        // Modal component rendered
        $response->assertSee('equipment-barcode-modal');
        $response->assertSee('parseDeviceBarcode');
        $response->assertSee('All Data Captured in 1 Scan!');
    }

    public function test_sidebar_toggle_button_renders_with_svg_and_btn_icon(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();

        $response->assertSee('id="header-sidebar-toggle-btn"', false);
        $response->assertSee('btn-icon hidden lg:inline-flex', false);
        $response->assertSee('aria-label="Toggle sidebar"', false);
    }

    public function test_equipment_lookup_by_ean_finds_product(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $product = Product::create([
            'name' => 'Hikvision 4MP ColorVu Bullet Camera',
            'model_no' => 'DS-2CD2047G2-LU',
            'sku' => '8901234567890',
            'unit' => 'Nos',
            'cost_price' => 3200,
            'unit_price' => 4500,
            'stock_quantity' => 15,
            'default_warranty_months' => 24,
        ]);

        $response = $this->actingAs($user)->getJson('/api/equipment/lookup-serial?ean=8901234567890');
        $response->assertOk();
        $response->assertJson([
            'found' => true,
            'type' => 'catalog_product',
            'product_id' => $product->id,
            'sku' => '8901234567890',
            'model_no' => 'DS-2CD2047G2-LU',
        ]);
    }

    public function test_equipment_lookup_by_composite_barcode(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $product = Product::create([
            'name' => 'CP PLUS 4MP IP Dome',
            'model_no' => 'CP-UNC-DA41L3',
            'sku' => '8909999888878',
            'unit' => 'Nos',
            'cost_price' => 2800,
            'unit_price' => 3900,
            'stock_quantity' => 8,
            'default_warranty_months' => 24,
        ]);

        // Scanning composite barcode with S/N, MAC and valid EAN-13 in single string
        $compositeBarcode = 'SN:HK20261122001,MAC:BC:A8:A6:11:22:33,EAN:8909999888878';
        $response = $this->actingAs($user)->getJson('/api/equipment/lookup-serial?serial=' . urlencode($compositeBarcode));
        $response->assertOk();
        $response->assertJson([
            'found' => true,
            'type' => 'catalog_product',
            'product_id' => $product->id,
        ]);
    }

    public function test_equipment_lookup_unregistered_serial_does_not_guess_model_and_preserves_mac(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        // User scans CP Plus serial e.g. O2T6VS3PH8FFTANS with MAC BC:A8:A6:11:22:33
        $composite = 'S/N: O2T6VS3PH8FFTANS | MAC: BC:A8:A6:11:22:33';
        $response = $this->actingAs($user)->getJson('/api/equipment/lookup-serial?serial=' . urlencode($composite));
        $response->assertOk();
        $response->assertJson([
            'found' => false,
            'model_not_found' => true,
            'serial' => 'O2T6VS3PH8FFTANS',
            'mac' => 'BC:A8:A6:11:22:33',
            'model' => '',
            'equipment_name' => '',
            'product_id' => null,
        ]);
    }

    public function test_equipment_lookup_standalone_mac_does_not_pollute_serial(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->getJson('/api/equipment/lookup-serial?mac=F8:20:97:E3:33:91');
        $response->assertOk();
        $response->assertJson([
            'found' => false,
            'serial' => '',
            'mac' => 'F8:20:97:E3:33:91',
        ]);
    }
}
