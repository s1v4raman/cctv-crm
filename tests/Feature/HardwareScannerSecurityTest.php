<?php

namespace Tests\Feature;

use App\Models\InstalledEquipment;
use App\Models\InstallationJob;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HardwareScannerSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_lookup_with_only_ean_or_model_does_not_return_unrelated_installed_equipment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lead = Lead::create([
            'customer_name' => 'Unrelated Site',
            'phone'         => '9000000001',
            'site_address'  => '123 Main Rd',
        ]);

        InstalledEquipment::create([
            'lead_id'        => $lead->id,
            'equipment_name' => 'Unrelated Camera',
            'serial_number'  => 'UNREL-SN-001',
            'status'         => 'active',
        ]);

        // Only ean passed -> must NOT return the unrelated installed equipment
        $resEan = $this->actingAs($admin)->getJson(route('equipment.lookup', ['ean' => '8901234567890']));
        $resEan->assertOk();
        $resEan->assertJson(['found' => false, 'model_not_found' => true]);

        // Only model passed -> must NOT return the unrelated installed equipment
        $resModel = $this->actingAs($admin)->getJson(route('equipment.lookup', ['model' => 'NON-EXISTENT-MDL']));
        $resModel->assertOk();
        $resModel->assertJson(['found' => false, 'model_not_found' => true]);
    }

    public function test_lookup_does_not_guess_model_from_prefix_and_returns_up_to_3_candidates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (['CP-UNC-1', 'CP-UNC-2', 'CP-UNC-3', 'CP-UNC-4'] as $i => $mdl) {
            Product::create([
                'name'       => 'CP Plus Camera ' . ($i + 1),
                'model_no'   => $mdl,
                'sku'        => 'SKU-' . ($i + 1),
                'unit'       => 'Nos',
                'cost_price' => 1000,
                'unit_price' => 1500,
                'is_active'  => true,
            ]);
        }

        // Prefix O2T should NOT guess 'CP PLUS 6MP Network Camera'
        $resPrefix = $this->actingAs($admin)->getJson(route('equipment.lookup', ['serial' => 'O2T6VS3PH8FFTANS']));
        $resPrefix->assertOk()->assertJson([
            'found'           => false,
            'model_not_found' => true,
            'model'           => '',
            'equipment_name'  => '',
            'product_id'      => null,
        ]);

        // Partial model should return up to 3 candidates, never auto-selected
        $resCand = $this->actingAs($admin)->getJson(route('equipment.lookup', ['model' => 'CP-UNC']));
        $resCand->assertOk()->assertJson([
            'found'           => false,
            'model_not_found' => true,
            'model'           => '',
            'product_id'      => null,
        ]);
        $this->assertCount(3, $resCand->json('candidates'));
    }

    public function test_ean_check_digit_validation_and_normalization(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $product = Product::create([
            'name'       => 'Test Cam',
            'model_no'   => 'TC-100',
            'sku'        => '8901234567890', // valid EAN-13
            'unit'       => 'Nos',
            'cost_price' => 1000,
            'unit_price' => 1500,
            'is_active'  => true,
        ]);

        // Valid EAN-13 matches
        $this->actingAs($admin)
            ->getJson(route('equipment.lookup', ['ean' => '8901234567890']))
            ->assertOk()
            ->assertJson(['found' => true, 'product_id' => $product->id]);

        // Invalid check digit (last digit 1 instead of 0) is rejected as EAN
        $this->actingAs($admin)
            ->getJson(route('equipment.lookup', ['ean' => '8901234567891']))
            ->assertOk()
            ->assertJson(['found' => false, 'ean' => '']);

        // Serial and MAC normalization
        $lead = Lead::create(['customer_name' => 'Norm Site', 'phone' => '9888888888']);
        InstalledEquipment::create([
            'lead_id'        => $lead->id,
            'equipment_name' => 'Norm Cam',
            'serial_number'  => 'SN-ABC-123',
            'mac_address'    => 'AA:BB:CC:DD:EE:FF',
            'status'         => 'active',
        ]);

        $this->actingAs($admin)
            ->getJson(route('equipment.lookup', ['serial' => '  sn-abc-123  ']))
            ->assertOk()
            ->assertJson(['found' => true, 'serial_number' => 'SN-ABC-123']);

        $this->actingAs($admin)
            ->getJson(route('equipment.lookup', ['mac' => 'aa-bb-cc-dd-ee-ff']))
            ->assertOk()
            ->assertJson(['found' => true, 'mac_address' => 'AA:BB:CC:DD:EE:FF']);
    }

    public function test_role_authorization_and_json_filtering(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $techAssigned = User::factory()->create(['role' => 'technician']);
        $techOther = User::factory()->create(['role' => 'technician']);
        $worker = User::factory()->create(['role' => 'worker']);

        // Worker gets 403 on all scanner & lookup routes
        $this->actingAs($worker)->getJson(route('equipment.lookup', ['serial' => 'ANY']))->assertStatus(403);
        $this->actingAs($worker)->postJson(route('scanner.pairings.store'))->assertStatus(403);
        $this->actingAs($worker)->getJson(route('scanner.targets'))->assertStatus(403);
        $this->actingAs($worker)->get(route('mobile.scanner', ['token' => 'abc']))->assertStatus(403);

        $lead = Lead::create([
            'customer_name' => 'Secret Client',
            'phone'         => '9999911111',
            'site_address'  => 'Secret Address 42',
        ]);
        $quote = Quotation::create([
            'lead_id'      => $lead->id,
            'quotation_no' => 'QT-TEST-01',
            'total_amount' => 5000,
            'status'       => 'approved',
        ]);
        $job = InstallationJob::create([
            'quotation_id'           => $quote->id,
            'job_no'                 => 'JOB-TEST-01',
            'assigned_technician_id' => $techAssigned->id,
            'status'                 => 'assigned',
        ]);
        $product = Product::create([
            'name'           => 'Pro Cam',
            'model_no'       => 'PRO-1',
            'sku'            => 'PRO-SKU-1',
            'unit'           => 'Nos',
            'cost_price'     => 2000,
            'unit_price'     => 3500,
            'stock_quantity' => 25,
            'is_active'      => true,
        ]);
        InstalledEquipment::create([
            'lead_id'             => $lead->id,
            'installation_job_id' => $job->id,
            'product_id'          => $product->id,
            'equipment_name'      => 'Pro Cam',
            'serial_number'       => 'SN-ASSIGNED-1',
            'status'              => 'active',
        ]);

        // Office (staff/admin) sees product price/stock and customer phone/address
        $this->actingAs($staff)->getJson(route('equipment.lookup', ['serial' => 'PRO-SKU-1']))
            ->assertOk()->assertJson(['unit_price' => 3500, 'stock_quantity' => 25]);
        $this->actingAs($admin)->getJson(route('equipment.lookup', ['serial' => 'SN-ASSIGNED-1']))
            ->assertOk()->assertJson(['customer_phone' => '9999911111', 'customer_address' => 'Secret Address 42']);

        // Technician NEVER sees unit_price or stock_quantity
        $this->actingAs($techAssigned)->getJson(route('equipment.lookup', ['serial' => 'PRO-SKU-1']))
            ->assertOk()->assertJsonMissing(['unit_price', 'stock_quantity']);

        // Assigned technician sees customer phone & address
        $this->actingAs($techAssigned)->getJson(route('equipment.lookup', ['serial' => 'SN-ASSIGNED-1']))
            ->assertOk()->assertJson(['customer_phone' => '9999911111', 'customer_address' => 'Secret Address 42']);

        // Unassigned technician does NOT see customer phone & address
        $this->actingAs($techOther)->getJson(route('equipment.lookup', ['serial' => 'SN-ASSIGNED-1']))
            ->assertOk()
            ->assertJson(['registered_elsewhere' => true, 'customer_name' => 'registered at another site'])
            ->assertJsonMissing(['customer_phone', 'customer_address']);
    }

    public function test_pairing_ownership_queue_append_and_clear(): void
    {
        $tech1 = User::factory()->create(['role' => 'technician']);
        $tech2 = User::factory()->create(['role' => 'technician']);

        // Create pairing for tech1
        $pairRes = $this->actingAs($tech1)->postJson(route('scanner.pairings.store'));
        $pairRes->assertOk();
        $token1 = $pairRes->json('token');
        $this->assertEquals(40, strlen($token1));

        // Creating a second pairing invalidates the first
        $pairRes2 = $this->actingAs($tech1)->postJson(route('scanner.pairings.store'));
        $token2 = $pairRes2->json('token');
        $this->assertNotEquals($token1, $token2);
        $this->assertNull(Cache::get("scanner_pairing_{$token1}"));

        // show() does NOT mark token as connected
        $this->actingAs($tech1)->get(route('mobile.scanner', ['token' => $token2]))->assertOk();
        $this->assertFalse(Cache::has("scanner_connected_{$token2}"));

        // tech2 cannot access, push, or poll tech1's pairing
        $this->actingAs($tech2)->get(route('mobile.scanner', ['token' => $token2]))->assertStatus(403);
        $this->actingAs($tech2)->postJson(route('api.mobile-scanner.push'), ['token' => $token2, 'code' => 'EVIL'])->assertStatus(403);
        $this->actingAs($tech2)->getJson(route('api.mobile-scanner.poll', ['token' => $token2]))->assertStatus(403);

        // tech1 pushes two codes -> appended to queue
        $this->actingAs($tech1)->postJson(route('api.mobile-scanner.push'), ['token' => $token2, 'code' => 'CODE-ONE'])->assertOk();
        $this->actingAs($tech1)->postJson(route('api.mobile-scanner.push'), ['token' => $token2, 'code' => 'CODE-TWO'])->assertOk();

        // Poll returns both codes and clears queue
        $poll1 = $this->actingAs($tech1)->getJson(route('api.mobile-scanner.poll', ['token' => $token2]));
        $poll1->assertOk()->assertJson(['status' => 'scanned', 'code' => 'CODE-TWO']);
        $this->assertCount(2, $poll1->json('codes'));
        $this->assertEquals('CODE-ONE', $poll1->json('codes.0.code'));
        $this->assertEquals('CODE-TWO', $poll1->json('codes.1.code'));

        // Second poll sees cleared queue
        $poll2 = $this->actingAs($tech1)->getJson(route('api.mobile-scanner.poll', ['token' => $token2]));
        $poll2->assertOk()->assertJson(['status' => 'connected', 'codes' => []]);
    }

    public function test_register_form_validation_and_persistence(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $lead = Lead::create(['customer_name' => 'Reg Client', 'phone' => '9123456789']);
        $product = Product::create([
            'name'       => 'Reg Cam',
            'model_no'   => 'RC-1',
            'sku'        => 'RC-SKU-1',
            'unit'       => 'Nos',
            'cost_price' => 1000,
            'unit_price' => 1500,
        ]);

        // Invalid EAN check digit rejected
        $this->actingAs($staff)->post(route('equipment.store'), [
            'lead_id'        => $lead->id,
            'product_id'     => $product->id,
            'equipment_name' => 'Reg Cam',
            'serial_number'  => '  sn-reg-001 ',
            'mac_address'    => 'aa-bb-cc-11-22-33',
            'ean'            => '8901234567891',
            'raw_scan'       => 'S/N: sn-reg-001 | MAC: aa-bb-cc-11-22-33',
        ])->assertSessionHasErrors('ean');

        // Valid submission normalizes serial/mac and stores product_id, ean, raw_scan
        $this->actingAs($staff)->post(route('equipment.store'), [
            'lead_id'        => $lead->id,
            'product_id'     => $product->id,
            'equipment_name' => 'Reg Cam',
            'serial_number'  => '  sn-reg-001 ',
            'mac_address'    => 'aa-bb-cc-11-22-33',
            'ean'            => '8901234567890',
            'raw_scan'       => 'S/N: sn-reg-001 | MAC: aa-bb-cc-11-22-33',
        ])->assertRedirect();

        $this->assertDatabaseHas('installed_equipment', [
            'product_id'    => $product->id,
            'serial_number' => 'SN-REG-001',
            'mac_address'   => 'AA:BB:CC:11:22:33',
            'ean'           => '8901234567890',
        ]);
        $savedEq = InstalledEquipment::where('serial_number', 'SN-REG-001')->first();
        $this->assertEquals(['raw' => 'S/N: sn-reg-001 | MAC: aa-bb-cc-11-22-33'], $savedEq->raw_scan);

        // Duplicate serial (case-insensitive, trimmed) for same product rejected
        $this->actingAs($staff)->post(route('equipment.store'), [
            'lead_id'        => $lead->id,
            'product_id'     => $product->id,
            'equipment_name' => 'Reg Cam',
            'serial_number'  => 'SN-REG-001',
        ])->assertSessionHasErrors('serial_number');

        // Duplicate MAC rejected
        $this->actingAs($staff)->post(route('equipment.store'), [
            'lead_id'        => $lead->id,
            'product_id'     => $product->id,
            'equipment_name' => 'Reg Cam',
            'serial_number'  => 'SN-REG-002',
            'mac_address'    => 'AA:BB:CC:11:22:33',
        ])->assertSessionHasErrors('mac_address');
    }

    public function test_scanner_targets_search_and_modal_visibility_by_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $worker = User::factory()->create(['role' => 'worker']);
        Lead::create(['customer_name' => 'Alpha Towers', 'phone' => '9876543210', 'site_address' => 'MG Road']);

        $this->actingAs($admin)->getJson(route('scanner.targets', ['q' => 'Alpha']))
            ->assertOk()
            ->assertJsonFragment(['label' => 'Alpha Towers']);

        $this->actingAs($worker)->get(route('profile.edit'))
            ->assertOk()
            ->assertDontSee('id="hardwareScannerModal"', false);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('id="hardwareScannerModal"', false);
    }
}
