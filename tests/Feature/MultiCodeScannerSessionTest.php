<?php

namespace Tests\Feature;

use App\Models\InstalledEquipment;
use App\Models\Lead;
use App\Models\Product;
use App\Models\ProductAlias;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiCodeScannerSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_multi_code_session_assembles_ean_serial_and_mac_and_splits_qr_text(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'name'       => '6MP Network Camera',
            'brand'      => 'CP PLUS',
            'model_no'   => 'CP-UNC-TA61L3C-LQ',
            'sku'        => 'CP-SKU-61',
            'ean'        => '8901234567890',
            'unit'       => 'Nos',
            'cost_price' => 3000,
            'unit_price' => 4500,
            'is_active'  => true,
        ]);

        // 1. Multi-code session with separate EAN, S/N, and MAC reads
        $res = $this->actingAs($admin)->postJson(route('equipment.lookup.post'), [
            'scans' => [
                ['raw' => '8901234567890', 'format' => 'ean_13'],
                ['raw' => 'O2T6VS3PH8FFTANS', 'format' => 'code_128'],
                ['raw' => 'F8:20:97:E3:33:91', 'format' => 'code_128'],
            ],
        ]);

        $res->assertOk()->assertJson([
            'found'         => true,
            'product_id'    => $product->id,
            'ean'           => '8901234567890',
            'serial'        => 'O2T6VS3PH8FFTANS',
            'mac'           => 'F8:20:97:E3:33:91',
            'product'       => [
                'id'    => $product->id,
                'name'  => '6MP Network Camera',
                'brand' => 'CP PLUS',
            ],
        ]);
        $this->assertContains('O2T6VS3PH8FFTANS', $res->json('serial_candidates'));

        // 2. QR text split on newlines, pipes, commas, semicolons, spaces
        $qrRes = $this->actingAs($admin)->postJson(route('equipment.lookup.post'), [
            'scans' => [
                ['raw' => "S/N: QR-998877\nMAC: AA:BB:CC:11:22:33|8901234567890", 'format' => 'qr_code'],
            ],
        ]);
        $qrRes->assertOk()->assertJson([
            'found'      => true,
            'product_id' => $product->id,
            'serial'     => 'QR-998877',
            'mac'        => 'AA:BB:CC:11:22:33',
            'ean'        => '8901234567890',
        ]);
    }

    public function test_bare_12_hex_is_possible_mac_not_auto_assigned(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $res = $this->actingAs($admin)->postJson(route('equipment.lookup.post'), [
            'scans' => [
                ['raw' => 'AABBCCDDEEFF', 'format' => 'code_128'],
            ],
        ]);

        $res->assertOk()->assertJson([
            'found'        => false,
            'mac'          => '',
            'possible_mac' => ['AA:BB:CC:DD:EE:FF'],
        ]);
    }

    public function test_alias_learning_and_admin_approve_or_merge(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $lead = Lead::create(['customer_name' => 'Alias Site', 'phone' => '9876543210']);

        $prodA = Product::create([
            'name' => 'Camera A', 'model_no' => 'CAM-A', 'sku' => 'SKU-A',
            'unit' => 'Nos', 'cost_price' => 1000, 'unit_price' => 1500, 'is_active' => true,
        ]);
        $prodB = Product::create([
            'name' => 'Camera B', 'model_no' => 'CAM-B', 'sku' => 'SKU-B',
            'unit' => 'Nos', 'cost_price' => 1200, 'unit_price' => 1800, 'is_active' => true,
        ]);

        // Save device with unknown model token -> creates pending scan_learned alias
        $storeRes = $this->actingAs($staff)->postJson(route('equipment.store'), [
            'lead_id'           => $lead->id,
            'product_id'        => $prodA->id,
            'equipment_name'    => 'Camera A',
            'serial_number'     => 'SN-LEARN-1',
            'scan_source'       => 'camera',
            'raw_scan'          => [['raw' => 'OEM-ALIAS-99', 'format' => 'code_128']],
            'learn_alias_token' => 'OEM-ALIAS-99',
            'learn_alias_type'  => 'model_code',
        ]);
        $storeRes->assertStatus(201)->assertJson(['success' => true]);

        $this->assertDatabaseHas('installed_equipment', [
            'serial_number' => 'SN-LEARN-1',
            'scan_source'   => 'camera',
        ]);

        $alias = ProductAlias::where('normalized_value', 'OEM-ALIAS-99')->first();
        $this->assertNotNull($alias);
        $this->assertEquals('pending', $alias->status);
        $this->assertEquals('scan_learned', $alias->source);

        // Pending alias is NOT matched yet
        $this->actingAs($staff)->postJson(route('equipment.lookup.post'), [
            'scans' => [['raw' => 'MODEL:OEM-ALIAS-99']],
        ])->assertOk()->assertJson(['found' => false, 'product_id' => null]);

        // Non-admin cannot approve
        $this->actingAs($staff)->post(route('admin.product-aliases.approve', $alias))->assertStatus(403);

        // Admin approves -> now matched
        $this->actingAs($admin)->post(route('admin.product-aliases.approve', $alias))->assertRedirect();
        $this->assertEquals('approved', $alias->fresh()->status);

        $this->actingAs($staff)->postJson(route('equipment.lookup.post'), [
            'scans' => [['raw' => 'MODEL:OEM-ALIAS-99']],
        ])->assertOk()->assertJson(['found' => true, 'product_id' => $prodA->id]);

        // Admin merges into prodB
        $this->actingAs($admin)->post(route('admin.product-aliases.merge', $alias), [
            'target_product_id' => $prodB->id,
        ])->assertRedirect();

        $this->actingAs($staff)->postJson(route('equipment.lookup.post'), [
            'scans' => [['raw' => 'MODEL:OEM-ALIAS-99']],
        ])->assertOk()->assertJson(['found' => true, 'product_id' => $prodB->id]);
    }

    public function test_old_modal_buttons_and_wrappers_still_present_across_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tech = User::factory()->create(['role' => 'technician']);

        $eqRes = $this->actingAs($admin)->get(route('equipment.index'));
        $eqRes->assertOk()
            ->assertSee('openHardwareScannerModal()', false)
            ->assertSee('id="hardwareScannerModal"', false)
            ->assertSee('window.switchHardwareScannerTab', false);

        $prodRes = $this->actingAs($admin)->get(route('products.index'));
        $prodRes->assertOk()
            ->assertSee('openHardwareScannerModal()', false)
            ->assertSee('Scan Aliases');

        $techRes = $this->actingAs($tech)->get(route('technician.dashboard'));
        $techRes->assertOk()
            ->assertSee('openHardwareScannerModal(', false)
            ->assertSee('Launch Hardware Scanner');
    }

    public function test_single_hardware_scan_identifies_both_serial_and_mac_and_renders_trigger(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $res = $this->actingAs($staff)->postJson(route('equipment.lookup.post'), [
            'scans' => [
                ['raw' => 'S/N: SN-HK2026-981247 | MAC: BC:A8:A6:11:22:33', 'format' => 'qr_code'],
            ],
        ]);

        $res->assertOk()->assertJson([
            'serial'        => 'SN-HK2026-981247',
            'serial_number' => 'SN-HK2026-981247',
            'mac'           => 'BC:A8:A6:11:22:33',
            'mac_address'   => 'BC:A8:A6:11:22:33',
        ]);

        $this->actingAs($staff)->get(route('equipment.create'))
            ->assertOk()
            ->assertSee('Single Hardware Scan');
    }
}
