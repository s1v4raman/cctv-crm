<?php

namespace Tests\Feature;

use App\Models\InstalledEquipment;
use App\Models\Lead;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Models\Product;
use App\Models\User;
use App\Services\AlertNotificationService;
use App\Services\StockAlertService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BarcodeScannerAndStockAlertTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'  => 'admin',
            'email' => 'admin@testsecurity.com',
            'name'  => 'Super Admin',
        ]);

        $this->technician = User::factory()->create([
            'role'  => 'technician',
            'email' => 'tech@testsecurity.com',
            'name'  => 'Field Tech Alex',
        ]);
    }

    public function test_technician_dashboard_contains_mobile_camera_scanner_tool(): void
    {
        $response = $this->actingAs($this->technician)->get(route('technician.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('On-Site Hardware Barcode Scanner');
        $response->assertSee('Open Camera Scanner');
    }

    public function test_equipment_and_rma_create_views_contain_barcode_scanner_triggers(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Apex Retail Complex',
            'phone'         => '9876543210',
            'site_address'  => 'High Street Mall',
        ]);

        // Equipment create view
        $eqCreateResponse = $this->actingAs($this->admin)->get(route('equipment.create'));
        $eqCreateResponse->assertStatus(200);
        $eqCreateResponse->assertSee('Scan Barcode');

        // RMA create view
        $rmaCreateResponse = $this->actingAs($this->admin)->get(route('rma.create'));
        $rmaCreateResponse->assertStatus(200);
        $rmaCreateResponse->assertSee('Scan Barcode');
    }

    public function test_serial_lookup_api_finds_installed_equipment_by_serial(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Metro Cyber Park',
            'phone'         => '9112233445',
            'site_address'  => 'Tower B, 4th Floor',
        ]);

        $product = Product::create([
            'sku'                     => 'CAM-4K-DOME',
            'name'                    => 'Hikvision 4K Dome Camera',
            'category'                => 'IP Cameras',
            'unit'                    => 'pcs',
            'cost_price'              => 4500,
            'unit_price'              => 6200,
            'stock_quantity'          => 15,
            'min_stock_alert'         => 3,
            'default_warranty_months' => 24,
            'is_active'               => true,
        ]);

        $equipment = InstalledEquipment::create([
            'lead_id'                      => $lead->id,
            'product_id'                   => $product->id,
            'equipment_name'               => 'Main Entrance Dome',
            'serial_number'                => 'HIK-9988776655',
            'mac_address'                  => 'AA:BB:CC:11:22:33',
            'location_tag'                 => 'Main Gate',
            'installation_date'            => Carbon::now()->subMonths(3),
            'manufacturer_warranty_expiry' => Carbon::now()->addMonths(21),
            'service_warranty_expiry'      => Carbon::now()->addMonths(9),
            'status'                       => 'active',
        ]);

        $response = $this->actingAs($this->technician)->getJson(route('api.equipment.lookup-serial', ['serial' => 'HIK-9988776655']));

        $response->assertStatus(200);
        $response->assertJson([
            'found'          => true,
            'type'           => 'installed_equipment',
            'serial_number'  => 'HIK-9988776655',
            'mac_address'    => 'AA:BB:CC:11:22:33',
            'equipment_name' => 'Main Entrance Dome',
            'customer_name'  => 'Metro Cyber Park',
            'is_under_warranty' => true,
        ]);
    }

    public function test_serial_lookup_api_finds_catalog_product_by_sku(): void
    {
        $product = Product::create([
            'sku'                     => 'BNC-CONN-GOLD',
            'name'                    => 'BNC Connectors High Quality (Pack of 10)',
            'category'                => 'Accessories',
            'unit'                    => 'pack',
            'cost_price'              => 120,
            'unit_price'              => 200,
            'stock_quantity'          => 50,
            'min_stock_alert'         => 10,
            'default_warranty_months' => 12,
            'is_active'               => true,
        ]);

        $response = $this->actingAs($this->technician)->getJson(route('api.equipment.lookup-serial', ['serial' => 'BNC-CONN-GOLD']));

        $response->assertStatus(200);
        $response->assertJson([
            'found'          => true,
            'type'           => 'catalog_product',
            'product_name'   => 'BNC Connectors High Quality (Pack of 10)',
            'sku'            => 'BNC-CONN-GOLD',
            'stock_quantity' => 50,
        ]);
    }

    public function test_serial_lookup_api_returns_not_found_for_unknown_code(): void
    {
        $response = $this->actingAs($this->technician)->getJson(route('api.equipment.lookup-serial', ['serial' => 'UNKNOWN-99999']));

        $response->assertStatus(200);
        $response->assertJson([
            'found'  => false,
            'serial' => 'UNKNOWN-99999',
        ]);
    }

    public function test_inventory_adjust_stock_triggers_low_stock_alert_when_threshold_breached(): void
    {
        Mail::fake();

        $product = Product::create([
            'sku'                     => 'PWR-12V-5A',
            'name'                    => '12V 5A CCTV Central Power Supply Box',
            'category'                => 'Power Supplies',
            'unit'                    => 'pcs',
            'cost_price'              => 800,
            'unit_price'              => 1400,
            'stock_quantity'          => 8,
            'min_stock_alert'         => 5,
            'default_warranty_months' => 12,
            'is_active'               => true,
        ]);

        // Reduce stock from 8 to 4 (which is <= min_stock_alert 5)
        $response = $this->actingAs($this->admin)->post(route('inventory.adjust', $product), [
            'type'     => 'out',
            'quantity' => 4,
            'notes'    => 'Dispatched to project Alpha',
        ]);

        $response->assertRedirect();
        $this->assertEquals(4, $product->fresh()->stock_quantity);

        // Verify alert logged in notification_logs
        $log = NotificationLog::where('event_type', 'stock_low_threshold_alert')
            ->where('reference_type', Product::class)
            ->where('reference_id', $product->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('PWR-12V-5A', $log->message_body);
        $this->assertStringContainsString('4 pcs', $log->message_body);
    }

    public function test_installed_equipment_store_with_stock_deduct_triggers_low_stock_alert(): void
    {
        Mail::fake();

        $lead = Lead::create([
            'customer_name' => 'Sunrise Heights Society',
            'phone'         => '9888776655',
            'site_address'  => 'Gate 1, Sunrise Heights',
        ]);

        $product = Product::create([
            'sku'                     => 'HDD-1TB-WD',
            'name'                    => 'WD Purple 1TB Surveillance Hard Drive',
            'category'                => 'Storage',
            'unit'                    => 'pcs',
            'cost_price'              => 3500,
            'unit_price'              => 4500,
            'stock_quantity'          => 2,
            'min_stock_alert'         => 2,
            'default_warranty_months' => 36,
            'is_active'               => true,
        ]);

        // Deploy equipment and deduct stock (stock will drop from 2 to 1 <= min_stock_alert 2)
        $response = $this->actingAs($this->admin)->post(route('equipment.store'), [
            'lead_id'           => $lead->id,
            'product_id'        => $product->id,
            'equipment_name'    => 'NVR Primary HDD',
            'serial_number'     => 'WD-SN-99881122',
            'installation_date' => Carbon::today()->toDateString(),
            'status'            => 'active',
            'deduct_stock'      => '1',
        ]);

        $response->assertRedirect(route('equipment.index'));
        $this->assertEquals(1, $product->fresh()->stock_quantity);

        // Notification log created for low stock alert
        $log = NotificationLog::where('event_type', 'stock_low_threshold_alert')
            ->where('reference_type', Product::class)
            ->where('reference_id', $product->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('HDD-1TB-WD', $log->message_body);
    }

    public function test_stock_alert_service_deduplicates_alerts_within_24_hours(): void
    {
        Mail::fake();

        $product = Product::create([
            'sku'                     => 'CON-BNC-CRIMP',
            'name'                    => 'Crimp BNC Connectors (50 pack)',
            'category'                => 'Accessories',
            'unit'                    => 'pack',
            'cost_price'              => 500,
            'unit_price'              => 800,
            'stock_quantity'          => 2,
            'min_stock_alert'         => 5,
            'default_warranty_months' => 6,
            'is_active'               => true,
        ]);

        $stockAlertService = app(StockAlertService::class);

        // First call generates alert
        $logs1 = $stockAlertService->checkAndTriggerAlert($product);
        $this->assertNotEmpty($logs1);

        $initialCount = NotificationLog::where('event_type', 'stock_low_threshold_alert')
            ->where('reference_type', Product::class)
            ->where('reference_id', $product->id)
            ->count();
        $this->assertGreaterThan(0, $initialCount);

        // Second call immediately after should be deduplicated
        $logs2 = $stockAlertService->checkAndTriggerAlert($product);
        $this->assertEmpty($logs2);

        // Third call with force=true bypasses deduplication
        $logs3 = $stockAlertService->checkAndTriggerAlert($product, force: true);
        $this->assertNotEmpty($logs3);
    }

    public function test_automated_reminder_sweep_command_dispatches_low_stock_alerts(): void
    {
        Mail::fake();

        Product::create([
            'sku'                     => 'SW-8PORT-POE',
            'name'                    => '8-Port Gigabit PoE Switch',
            'category'                => 'Networking',
            'unit'                    => 'pcs',
            'cost_price'              => 2800,
            'unit_price'              => 4200,
            'stock_quantity'          => 1,
            'min_stock_alert'         => 3,
            'default_warranty_months' => 24,
            'is_active'               => true,
        ]);

        $exitCode = Artisan::call('alerts:send-reminders', ['--force' => true]);

        $this->assertEquals(0, $exitCode);

        $log = NotificationLog::where('event_type', 'stock_low_threshold_alert')
            ->where('subject', 'like', '%Low Stock%')
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('SW-8PORT-POE', $log->message_body);
    }
}
