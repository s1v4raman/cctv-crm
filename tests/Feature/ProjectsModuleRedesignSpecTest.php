<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\IpDevice;
use App\Models\IpDeviceRevealLog;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectMaterial;
use App\Models\ProjectWorkerAttendance;
use App\Models\Site;
use App\Models\User;
use App\Models\WagePayment;
use App\Models\WorkDay;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectsModuleRedesignSpecTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $technician;
    protected User $staff;
    protected Company $precisionCompany;
    protected Company $npCompany;
    protected Company $linepixCompany;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Core Role Users
        $this->admin = User::factory()->create([
            'name' => 'Admin Boss',
            'email' => 'admin@securevision.test',
            'role' => 'admin',
        ]);

        $this->technician = User::factory()->create([
            'name' => 'Lead Tech Siva',
            'email' => 'tech@securevision.test',
            'role' => 'technician',
        ]);

        $this->staff = User::factory()->create([
            'name' => 'Desk Staff Meena',
            'email' => 'staff@securevision.test',
            'role' => 'staff',
        ]);

        // 2. Retrieve Executing Companies seeded by migration
        $this->precisionCompany = Company::firstOrCreate(['name' => 'Precision IT Systems']);
        $this->npCompany = Company::firstOrCreate(['name' => 'NP Solutions']);
        $this->linepixCompany = Company::firstOrCreate(['name' => 'Linepix']);
    }

    /* =========================================================================
     * SECTION 1: SITES MANAGEMENT & GPS PARSING
     * ========================================================================= */

    public function test_can_create_site_with_coordinates_and_maps_url(): void
    {
        $response = $this->actingAs($this->admin)->post(route('sites.store'), [
            'name' => 'DLF Cybercity Tower B',
            'client_name' => 'DLF Assets Ltd',
            'client_phone' => '9840123456',
            'client_email' => 'facilities@dlf.test',
            'address' => 'Plot 12, Mount Poonamallee Rd, Manapakkam',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600125',
            'latitude' => 13.0125,
            'longitude' => 80.1834,
            'contact_person' => 'Ramesh Kumar',
            'contact_phone' => '9840998877',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sites', [
            'name' => 'DLF Cybercity Tower B',
            'client_name' => 'DLF Assets Ltd',
            'city' => 'Chennai',
            'latitude' => 13.0125,
            'longitude' => 80.1834,
        ]);

        $site = Site::where('name', 'DLF Cybercity Tower B')->first();
        $this->assertNotNull($site->site_code);
        $this->assertStringStartsWith('SIT-', $site->site_code);
    }

    public function test_site_creation_parses_google_maps_url_coordinates(): void
    {
        $mapsUrl = 'https://www.google.com/maps/@12.971598,77.594562,17z';

        $response = $this->actingAs($this->admin)->post(route('sites.store'), [
            'name' => 'Brigade Gateway Hub',
            'address' => 'Malleshwaram, Bangalore',
            'google_maps_url' => $mapsUrl,
        ]);

        $response->assertRedirect();
        $site = Site::where('name', 'Brigade Gateway Hub')->first();
        $this->assertNotNull($site);
        $this->assertEquals(12.971598, (float) $site->latitude);
        $this->assertEquals(77.594562, (float) $site->longitude);
    }

    public function test_sites_search_api_returns_autocomplete_json(): void
    {
        Site::create([
            'site_code' => 'SIT-9001',
            'name' => 'Tech Park Alpha',
            'client_name' => 'Infosys SEZ',
            'address' => 'Sholinganallur, OMR, Chennai',
        ]);

        $response = $this->actingAs($this->technician)->getJson(route('sites.search', ['q' => 'Tech Park']));
        $response->assertOk()
            ->assertJsonFragment(['name' => 'Tech Park Alpha', 'site_code' => 'SIT-9001']);
    }

    /* =========================================================================
     * SECTION 2: WORKERS & RUNNING FINANCIAL BALANCES
     * ========================================================================= */

    public function test_can_register_worker_and_quick_add(): void
    {
        // 1. Full form create
        $response = $this->actingAs($this->admin)->post(route('workers.store'), [
            'name' => 'Murugan K',
            'phone' => '9841122334',
            'daily_rate' => 850.00,
            'skills' => ['CCTV Cabling', 'Conduit Piping', 'Camera Mounting'],
            'notes' => 'Experienced technician assistant',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('workers', [
            'name' => 'Murugan K',
            'daily_rate' => 850.00,
            'is_active' => true,
        ]);

        // 2. Quick add API by technician
        $quickResponse = $this->actingAs($this->technician)->postJson(route('workers.quick-add'), [
            'name' => 'Velu M',
            'phone' => '9841998811',
            'daily_rate' => 750.00,
        ]);

        $quickResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('worker.name', 'Velu M');

        $this->assertDatabaseHas('workers', ['name' => 'Velu M', 'daily_rate' => 750.00]);
    }

    /* =========================================================================
     * SECTION 3: 4-STEP PROJECT WIZARD & DRAFT AUTOSAVE
     * ========================================================================= */

    public function test_technician_can_create_project_via_wizard_submitting_for_approval(): void
    {
        $site = Site::create([
            'site_code' => 'SIT-0042',
            'name' => 'Nexus Mall Velachery',
            'client_name' => 'Nexus Retail Ltd',
            'address' => '100 Feet Rd, Velachery, Chennai',
        ]);

        $postData = [
            'site_id' => $site->id,
            'title' => 'Nexus Mall 32-Cam IP CCTV Deployment',
            'project_type' => 'hardware_cctv',
            'lead_technician_id' => $this->technician->id,
            'budget' => 285000.00,
            'start_date' => now()->toDateString(),
            'deadline' => now()->addDays(14)->toDateString(),
            'priority' => 'high',
            'description' => '32 dome cameras, Cat6 cabling, NVR rack setup',
            'requirements' => [
                'cctv_cameras' => '32',
                'cable_meters' => '1500',
                'switch_ports' => '48',
                'server_rack' => '12U',
            ],
            'materials' => [
                [
                    'item_name' => 'Hikvision 4MP IP Dome Camera',
                    'quantity' => 32,
                    'unit' => 'pcs',
                    'source' => 'warehouse',
                    'notes' => 'DS-2CD1143G0-I',
                ],
                [
                    'item_name' => 'D-Link Cat6 Cable 305m Drum',
                    'quantity' => 5,
                    'unit' => 'box',
                    'source' => 'local_purchase',
                    'shop_name' => 'Ritchie St Electronics',
                    'notes' => 'Pure copper',
                ],
            ],
        ];

        $response = $this->actingAs($this->technician)->post(route('projects.store'), $postData);
        $response->assertRedirect();

        $project = Project::where('title', 'Nexus Mall 32-Cam IP CCTV Deployment')->first();
        $this->assertNotNull($project);
        $this->assertStringStartsWith('PRJ-', $project->project_code);
        $this->assertEquals($site->id, $project->site_id);
        $this->assertEquals('pending_approval', $project->status);
        $this->assertEquals('Nexus Retail Ltd', $project->company_name);
        $this->assertCount(2, $project->materials);

        $this->assertDatabaseHas('project_materials', [
            'project_id' => $project->id,
            'item_name' => 'Hikvision 4MP IP Dome Camera',
            'quantity' => 32,
            'source' => 'warehouse',
        ]);
    }

    public function test_can_save_project_as_draft(): void
    {
        $response = $this->actingAs($this->staff)->post(route('projects.store'), [
            'action' => 'save_draft',
            'title' => 'Preliminary CCTV Proposal for TechCorp',
            'project_type' => 'hardware_cctv',
            'priority' => 'medium',
        ]);

        $response->assertRedirect();
        $project = Project::where('title', 'Preliminary CCTV Proposal for TechCorp')->first();
        $this->assertNotNull($project);
        $this->assertEquals('draft', $project->status);
    }

    /* =========================================================================
     * SECTION 4: ADMIN APPROVAL WORKFLOW & EXECUTING COMPANY ASSIGNMENT
     * ========================================================================= */

    public function test_admin_can_approve_project_and_assign_executing_company(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-2026-0001',
            'title' => 'Apollo Hospital Biometrics Upgrade',
            'project_type' => 'hardware_attendance',
            'priority' => 'high',
            'status' => 'pending_approval',
            'budget' => 450000.00,
            'created_by' => $this->technician->id,
        ]);

        // Technician cannot approve (403 forbidden)
        $techAttempt = $this->actingAs($this->technician)->post(route('projects.approve', $project), [
            'company_id' => $this->precisionCompany->id,
            'per_metre_rate' => 8.50,
        ]);
        $techAttempt->assertForbidden();

        // Admin approves and assigns company
        $adminApproval = $this->actingAs($this->admin)->post(route('projects.approve', $project), [
            'company_id' => $this->precisionCompany->id,
            'per_metre_rate' => 9.00,
            'approved_value' => 450000.00,
            'po_number' => 'PO-APOLLO-9821',
        ]);

        $adminApproval->assertRedirect();
        $project->refresh();

        $this->assertEquals('approved', $project->status);
        $this->assertEquals($this->precisionCompany->id, $project->company_id);
        $this->assertEquals(9.00, (float) $project->per_metre_rate);
        $this->assertEquals($this->admin->id, $project->approved_by);
        $this->assertNotNull($project->approved_on);
        $this->assertEquals('PO-APOLLO-9821', $project->po_number);
    }

    public function test_admin_can_reject_project_with_reason(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-2026-0002',
            'title' => 'Under-budgeted residential job',
            'project_type' => 'hardware_cctv',
            'priority' => 'low',
            'status' => 'pending_approval',
            'budget' => 12000.00,
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('projects.reject', $project), [
            'rejection_reason' => 'Client margin is too low, minimum threshold is ₹25,000.',
        ]);

        $response->assertRedirect();
        $project->refresh();

        $this->assertEquals('rejected', $project->status);
        $this->assertEquals('Client margin is too low, minimum threshold is ₹25,000.', $project->rejection_reason);
    }

    /* =========================================================================
     * SECTION 5: DELIVERY CHALLANS (DC) & READY-TO-INVOICE CONSOLE
     * ========================================================================= */

    public function test_delivery_challan_upload_and_duplicate_dc_check_per_company(): void
    {
        Storage::fake('public');

        $project = Project::create([
            'project_code' => 'PRJ-2026-0003',
            'title' => 'Grand Hyatt Surveillance Overhaul',
            'project_type' => 'hardware_cctv',
            'company_id' => $this->npCompany->id,
            'status' => 'in_progress',
        ]);

        $file1 = UploadedFile::fake()->create('DC_NP_1001.pdf', 500, 'application/pdf');

        // 1. Upload valid Delivery Challan
        $response = $this->actingAs($this->technician)->post(route('projects.documents.store', $project), [
            'document_type' => 'delivery_challan',
            'title' => 'Material Dispatch Phase 1',
            'dc_number' => 'DC-NP-1001',
            'dc_date' => now()->toDateString(),
            'items_summary' => '16 Bullet Cameras, 500m Cat6',
            'file' => $file1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('project_documents', [
            'project_id' => $project->id,
            'document_type' => 'delivery_challan',
            'dc_number' => 'DC-NP-1001',
            'is_invoiced' => false,
        ]);

        // 2. Attempt duplicate DC number under same executing company
        $file2 = UploadedFile::fake()->create('Duplicate_DC.pdf', 300, 'application/pdf');
        $duplicateAttempt = $this->actingAs($this->technician)->post(route('projects.documents.store', $project), [
            'document_type' => 'delivery_challan',
            'title' => 'Second Dispatch Duplicate',
            'dc_number' => 'DC-NP-1001', // Same DC Number!
            'dc_date' => now()->toDateString(),
            'file' => $file2,
        ]);

        $duplicateAttempt->assertSessionHas('error');
    }

    public function test_ready_to_invoice_console_and_bulk_mark_invoiced(): void
    {
        Storage::fake('public');

        $project = Project::create([
            'project_code' => 'PRJ-2026-0004',
            'title' => 'L&T Construction Site Gate Cameras',
            'project_type' => 'hardware_cctv',
            'company_id' => $this->linepixCompany->id,
            'status' => 'in_progress',
        ]);

        $doc1 = ProjectDocument::create([
            'project_id' => $project->id,
            'title' => 'DC Phase A Dispatch',
            'document_type' => 'delivery_challan',
            'dc_number' => 'LPX-DC-501',
            'dc_date' => now()->subDays(3)->toDateString(),
            'file_name' => 'dc501.pdf',
            'file_path' => 'projects/test/dc501.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'storage_disk' => 'public',
            'uploaded_by' => $this->technician->id,
            'is_invoiced' => false,
        ]);

        $doc2 = ProjectDocument::create([
            'project_id' => $project->id,
            'title' => 'DC Phase B Dispatch',
            'document_type' => 'delivery_challan',
            'dc_number' => 'LPX-DC-502',
            'dc_date' => now()->subDays(1)->toDateString(),
            'file_name' => 'dc502.pdf',
            'file_path' => 'projects/test/dc502.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'storage_disk' => 'public',
            'uploaded_by' => $this->technician->id,
            'is_invoiced' => false,
        ]);

        // 1. Check To-Invoice view displays uninvoiced DCs
        $viewResponse = $this->actingAs($this->admin)->get(route('documents.to-invoice'));
        $viewResponse->assertOk()
            ->assertSee('LPX-DC-501')
            ->assertSee('LPX-DC-502');

        // 2. Mark batch as invoiced
        $markResponse = $this->actingAs($this->admin)->post(route('documents.mark-invoiced'), [
            'document_ids' => [$doc1->id, $doc2->id],
            'invoice_number' => 'INV-2026-0889',
            'invoiced_at' => now()->toDateString(),
        ]);

        $markResponse->assertRedirect();
        $this->assertEquals(true, $doc1->fresh()->is_invoiced);
        $this->assertEquals('INV-2026-0889', $doc1->fresh()->invoice_number);
        $this->assertEquals(true, $doc2->fresh()->is_invoiced);
    }

    /* =========================================================================
     * SECTION 6: WORK DAYS, DAILY RATE SNAPSHOTTING & CABLING EXTRAS
     * ========================================================================= */

    public function test_work_day_attendance_snapshots_worker_daily_rate_and_calculates_cabling(): void
    {
        $worker = Worker::create([
            'name' => 'Karthik Subramanian',
            'daily_rate' => 900.00,
            'is_active' => true,
        ]);

        $project = Project::create([
            'project_code' => 'PRJ-2026-0005',
            'title' => 'Express Avenue Retail Bank Cabling',
            'per_metre_rate' => 8.00, // ₹8 per metre
            'status' => 'in_progress',
        ]);

        $today = now()->startOfWeek(Carbon::MONDAY)->toDateString();

        // Worker worked full day (₹900) + pulled 250m cable (250 * 8 = ₹2,000) + ₹150 extra for height work
        // Expected total = 900 + 2000 + 150 = ₹3,050
        $response = $this->actingAs($this->technician)->post(route('projects.work-days.store', $project), [
            'work_date' => $today,
            'notes' => 'Ground floor server room trunking completed',
            'attendances' => [
                [
                    'worker_id' => $worker->id,
                    'attendance_type' => 'full_day',
                    'cabling_metres' => 250,
                    'extra_amount' => 150,
                    'extra_description' => 'Ceiling scaffolding hazard pay',
                ],
            ],
        ]);

        $this->assertNull(session('error'), 'Session error: ' . (session('error') ?? ''));
        if (session()->has('errors')) {
            $this->fail('Validation errors: ' . json_encode(session('errors')->all()));
        }
        $response->assertRedirect();

        $workDay = WorkDay::where('project_id', $project->id)->whereDate('work_date', $today)->first();
        $this->assertNotNull($workDay);

        $attendance = ProjectWorkerAttendance::where('work_day_id', $workDay->id)
            ->where('worker_id', $worker->id)
            ->first();

        $this->assertNotNull($attendance);
        $this->assertEquals(900.00, (float) $attendance->daily_rate_snapshot);
        $this->assertEquals(250.00, (float) $attendance->cabling_metres);
        $this->assertEquals(2000.00, (float) $attendance->cabling_amount); // 250 * 8
        $this->assertEquals(150.00, (float) $attendance->extra_amount);
        $this->assertEquals(3050.00, (float) $attendance->total_amount); // 900 + 2000 + 150
        $this->assertEquals(false, $attendance->is_paid); // Unpaid until Sunday settlement
    }

    /* =========================================================================
     * SECTION 7: SUNDAY PAYDAY WEEKLY WAGE CONSOLE & SETTLEMENT ENGINE
     * ========================================================================= */

    public function test_sunday_payday_cycle_ledger_math_and_settlement(): void
    {
        $worker = Worker::create([
            'name' => 'Senthil Kumar',
            'daily_rate' => 800.00,
            'is_active' => true,
        ]);

        $project = Project::create([
            'project_code' => 'PRJ-2026-0006',
            'title' => 'Warehouse CCTV & Network Cabling',
            'per_metre_rate' => 10.00,
            'status' => 'in_progress',
        ]);

        // Monday of current week
        $monday = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $tuesday = (clone $monday)->addDay();
        $wednesday = (clone $monday)->addDays(2);

        // Day 1: Monday - Full day (₹800) + 100m cable (100 * 10 = ₹1,000) = ₹1,800
        $wd1 = WorkDay::create(['project_id' => $project->id, 'work_date' => $monday->toDateString(), 'logged_by' => $this->technician->id]);
        ProjectWorkerAttendance::create([
            'work_day_id' => $wd1->id,
            'worker_id' => $worker->id,
            'daily_rate_snapshot' => 800.00,
            'attendance_type' => 'full_day',
            'cabling_metres' => 100,
            'cabling_amount' => 1000.00,
            'extra_amount' => 0,
            'total_amount' => 1800.00,
            'is_paid' => false,
        ]);

        // Day 2: Tuesday - Half day (₹400) = ₹400
        $wd2 = WorkDay::create(['project_id' => $project->id, 'work_date' => $tuesday->toDateString(), 'logged_by' => $this->technician->id]);
        ProjectWorkerAttendance::create([
            'work_day_id' => $wd2->id,
            'worker_id' => $worker->id,
            'daily_rate_snapshot' => 800.00,
            'attendance_type' => 'half_day',
            'cabling_metres' => 0,
            'cabling_amount' => 0,
            'extra_amount' => 0,
            'total_amount' => 400.00,
            'is_paid' => false,
        ]);

        // Wednesday: Mid-week cash advance of ₹500
        $advanceResponse = $this->actingAs($this->admin)->post(route('wages.advance'), [
            'worker_id' => $worker->id,
            'amount' => 500.00,
            'payment_date' => $wednesday->toDateString(),
            'payment_mode' => 'cash',
            'reference_notes' => 'Grocery advance',
        ]);
        $advanceResponse->assertRedirect();

        // Verify weekly calculations
        // Total Earned = 1800 + 400 = 2200
        // Advances = 500
        // Brought forward = 0
        // Total Due = 2200 - 500 = ₹1,700
        $saturdayEnd = (clone $monday)->addDays(5)->endOfDay()->toDateString();
        $weekEnd = (clone $monday)->endOfWeek(Carbon::SUNDAY)->toDateString();

        $earned = $worker->getEarnedInWeek($monday->toDateString(), $saturdayEnd);
        $advances = $worker->getAdvancesInWeek($monday->toDateString(), $weekEnd);
        $this->assertEquals(2200.00, $earned);
        $this->assertEquals(500.00, $advances);

        // Sunday settlement: Pay Full (₹1,700)
        $settleResponse = $this->actingAs($this->admin)->post(route('wages.settle'), [
            'worker_id' => $worker->id,
            'settlement_action' => 'pay_full',
            'week_start' => $monday->toDateString(),
            'payment_mode' => 'cash',
            'reference_notes' => 'Settled in full on Sunday',
        ]);

        $settleResponse->assertRedirect();

        // Verify both attendances are now marked paid/locked
        $attendances = ProjectWorkerAttendance::where('worker_id', $worker->id)->get();
        foreach ($attendances as $att) {
            $this->assertTrue((bool) $att->is_paid);
            $this->assertEquals($monday->toDateString(), $att->paid_week_start?->toDateString());
        }

        // Running balance for worker should now be 0
        $this->assertEquals(0.00, $worker->getRunningBalance());

        // Verify printable signature sheet renders
        $sheetResponse = $this->actingAs($this->admin)->get(route('wages.signature-sheet', ['week' => $monday->toDateString()]));
        $sheetResponse->assertOk()
            ->assertSee('Senthil Kumar')
            ->assertSee('Cash Signature Sheet', false);
    }

    /* =========================================================================
     * SECTION 8: ENCRYPTED IP DEVICE REGISTER & SECURE REVEAL LOGGING
     * ========================================================================= */

    public function test_ip_device_register_stores_encrypted_password_and_bulk_generates(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-2026-0007',
            'title' => 'ITC Grand Chola IP Surveillance',
            'status' => 'in_progress',
        ]);

        // 1. Single IP device creation
        $singleResponse = $this->actingAs($this->technician)->post(route('projects.devices.store', $project), [
            'device_name' => 'Master NVR Core',
            'device_type' => 'NVR',
            'ip_address' => '192.168.1.10',
            'subnet_mask' => '255.255.255.0',
            'gateway' => '192.168.1.1',
            'web_port' => 80,
            'rtsp_port' => 554,
            'username' => 'admin',
            'password' => 'SuperSecretPass@2026!',
            'status' => 'online',
        ]);

        $singleResponse->assertRedirect();

        $nvrDevice = IpDevice::where('project_id', $project->id)->where('ip_address', '192.168.1.10')->first();
        $this->assertNotNull($nvrDevice);
        // Password attribute is automatically decrypted by Eloquent cast, but raw database must be encrypted!
        $rawPasswordInDb = \DB::table('ip_devices')->where('id', $nvrDevice->id)->value('password');
        $this->assertNotEquals('SuperSecretPass@2026!', $rawPasswordInDb);
        $this->assertEquals('SuperSecretPass@2026!', $nvrDevice->password);

        // 2. Bulk sequential generator: create 8 cameras 192.168.1.101 to 192.168.1.108
        $bulkResponse = $this->actingAs($this->technician)->post(route('projects.devices.bulk-store', $project), [
            'start_ip' => '192.168.1.101',
            'count' => 8,
            'device_type' => 'IP Dome Camera',
            'name_prefix' => 'Corridor Cam',
            'start_number' => 1,
            'subnet_mask' => '255.255.255.0',
            'gateway' => '192.168.1.1',
            'username' => 'admin',
            'password' => 'CameraDefaultPass#123',
            'status' => 'pending',
        ]);

        $bulkResponse->assertRedirect();
        $this->assertEquals(9, IpDevice::where('project_id', $project->id)->count()); // 1 NVR + 8 Cams
        $this->assertDatabaseHas('ip_devices', [
            'project_id' => $project->id,
            'device_name' => 'Corridor Cam 01',
            'ip_address' => '192.168.1.101',
        ]);
        $this->assertDatabaseHas('ip_devices', [
            'project_id' => $project->id,
            'device_name' => 'Corridor Cam 08',
            'ip_address' => '192.168.1.108',
        ]);

        // 3. Subnet Conflict Prevention: trying to add existing IP fails
        $conflictAttempt = $this->actingAs($this->technician)->post(route('projects.devices.store', $project), [
            'device_name' => 'Duplicate IP Device',
            'ip_address' => '192.168.1.101',
        ]);
        $conflictAttempt->assertSessionHas('error');

        // 4. Secure Password Reveal Endpoint: logs action into IpDeviceRevealLog
        $revealResponse = $this->actingAs($this->technician)->postJson(route('projects.devices.reveal', [
            'project' => $project,
            'device' => $nvrDevice,
        ]));

        $revealResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('password', 'SuperSecretPass@2026!');

        $this->assertDatabaseHas('ip_device_reveal_logs', [
            'ip_device_id' => $nvrDevice->id,
            'user_id' => $this->technician->id,
        ]);

        // 5. CSV Export check
        $exportResponse = $this->actingAs($this->technician)->get(route('projects.devices.export', $project));
        $exportResponse->assertOk();
        $this->assertTrue(str_contains($exportResponse->headers->get('Content-Disposition') ?? '', 'attachment'));
    }
}
