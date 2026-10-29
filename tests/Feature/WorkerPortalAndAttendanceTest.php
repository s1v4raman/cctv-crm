<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectWorkerAttendance;
use App\Models\TaskMaterialConsumption;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkerPortalAndAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_login_page_renders_daily_wage_worker_tab(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Daily Wage Worker');
        $response->assertSee('Daily Worker');
        $response->assertSee('Worker Full Name');
        $response->assertSee('Mobile Phone Number');
        $response->assertSee('/login/worker');
    }

    public function test_approved_daily_wage_worker_can_login_with_name_and_phone_only(): void
    {
        $workerName = 'Muthu Vel';
        $workerPhone = '9840123456';

        // The office has added (approved) this worker
        Worker::create([
            'name' => $workerName,
            'phone' => $workerPhone,
            'daily_rate' => 750.00,
            'is_active' => true,
        ]);

        $response = $this->post(route('login.worker'), [
            'name' => $workerName,
            'phone' => $workerPhone,
        ]);

        $response->assertRedirect(route('worker.portal'));

        // User account created and linked on first sign-in

        $this->assertDatabaseHas('users', [
            'name' => $workerName,
            'email' => "worker_{$workerPhone}@cctv-crm.local",
            'role' => 'worker',
        ]);

        $this->assertTrue(Auth::check());
        $this->assertEquals('worker', Auth::user()->role);
        $this->assertTrue(Auth::user()->isWorker());
    }

    public function test_authenticated_worker_can_access_worker_portal(): void
    {
        $worker = Worker::create([
            'name' => 'Raju Kumar',
            'phone' => '9876543210',
            'daily_rate' => 800.00,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'name' => $worker->name,
            'role' => 'worker',
            'email' => 'worker_9876543210@cctv-crm.local',
        ]);
        $worker->update(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('worker.portal'));

        $response->assertStatus(200);
        $response->assertSee('Raju Kumar');
        $response->assertSee('9876543210');
        $response->assertSee('Daily Attendance (Photo Punch)');
        $response->assertSee('Log Work (Cabling, Cameras, BOM)');
    }

    public function test_worker_can_punch_attendance_with_photo_file(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-TITAN-001',
            'title' => 'Titan Showroom CCTV Revamp',
            'status' => 'in_progress',
            'created_by' => $admin->id,
            'per_metre_rate' => 8.00,
        ]);

        $worker = Worker::create([
            'name' => 'Karthik Raja',
            'phone' => '9841122334',
            'daily_rate' => 750.00,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'name' => $worker->name,
            'role' => 'worker',
            'email' => 'worker_9841122334@cctv-crm.local',
        ]);
        $worker->update(['user_id' => $user->id]);

        $fakePhoto = UploadedFile::fake()->create('selfie_punch.jpg', 50, 'image/jpeg');

        $response = $this->actingAs($user)->post(route('worker.attendance.punch'), [
            'project_id' => $project->id,
            'attendance_type' => 'full_day',
            'photo' => $fakePhoto,
            'notes' => 'On site at main entrance gate',
        ]);

        $response->assertRedirect(route('worker.portal'));

        // Check attendance record created with photo
        $attendance = ProjectWorkerAttendance::where('worker_id', $worker->id)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('full_day', $attendance->attendance_type);
        $this->assertEquals(750.00, $attendance->total_amount);
        $this->assertNotNull($attendance->photo_path);
        Storage::disk('public')->assertExists($attendance->photo_path);
    }

    public function test_worker_can_punch_attendance_with_base64_camera_capture(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-APOLLO-002',
            'title' => 'Apollo Pharmacy Camera Installation',
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $worker = Worker::create([
            'name' => 'Senthil Nathan',
            'phone' => '9790112233',
            'daily_rate' => 850.00,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'name' => $worker->name,
            'role' => 'worker',
            'email' => 'worker_9790112233@cctv-crm.local',
        ]);
        $worker->update(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('worker.attendance.punch'), [
            'project_id' => $project->id,
            'attendance_type' => 'half_day',
            'photo_data' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=',
            'notes' => 'Half day morning shift',
        ]);

        $response->assertRedirect(route('worker.portal'));

        $attendance = ProjectWorkerAttendance::where('worker_id', $worker->id)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('half_day', $attendance->attendance_type);
        $this->assertEquals(425.00, $attendance->total_amount); // 850 * 0.5
        $this->assertNotNull($attendance->photo_path);
        Storage::disk('public')->assertExists($attendance->photo_path);
    }

    public function test_worker_can_log_cabling_cameras_and_bom_material_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-EXPRESS-003',
            'title' => 'Express Logistics Warehouse CCTV',
            'status' => 'in_progress',
            'created_by' => $admin->id,
            'per_metre_rate' => 10.00,
        ]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Cat6 Cable Pulling - Bay A & B',
            'status' => 'in_progress',
            'priority' => 'high',
            'phase' => 'cabling',
        ]);

        $worker = Worker::create([
            'name' => 'Murugan M',
            'phone' => '9884556677',
            'daily_rate' => 800.00,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'name' => $worker->name,
            'role' => 'worker',
            'email' => 'worker_9884556677@cctv-crm.local',
        ]);
        $worker->update(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('worker.work.log'), [
            'project_id' => $project->id,
            'project_task_id' => $task->id,
            'cabling_metres' => 150.5,
            'cameras_installed' => 6,
            'material_name' => 'RJ45 Cat6 Gold Connectors',
            'material_qty' => 12,
            'material_unit' => 'nos',
            'drum_or_batch_no' => 'Drum #D-08',
            'work_notes' => 'Pulled 150.5m Cat6 cable from rack to Bay B and terminated 6 dome cameras',
        ]);

        $response->assertRedirect(route('worker.portal'));

        // Attendance record updated
        $attendance = ProjectWorkerAttendance::where('worker_id', $worker->id)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals(150.5, (float)$attendance->cabling_metres);
        $this->assertEquals(6, $attendance->cameras_installed);
        // 800 (base wage) + 150.5 * 10 (1505) = 2305
        $this->assertEquals(2305.00, (float)$attendance->total_amount);
        $this->assertStringContainsString('Pulled 150.5m Cat6 cable', $attendance->work_summary);

        // TaskMaterialConsumption auto-logged for cabling
        $cablingConsumption = TaskMaterialConsumption::where('project_id', $project->id)
            ->where('item_name', 'like', '%Cat6 Cabling Pulled%')
            ->first();
        $this->assertNotNull($cablingConsumption);
        $this->assertEquals(150.5, (float)$cablingConsumption->quantity_used);
        $this->assertEquals('meters', $cablingConsumption->unit);

        // TaskMaterialConsumption auto-logged for extra hardware (RJ45)
        $hardwareConsumption = TaskMaterialConsumption::where('project_id', $project->id)
            ->where('item_name', 'RJ45 Cat6 Gold Connectors')
            ->first();
        $this->assertNotNull($hardwareConsumption);
        $this->assertEquals(12, (float)$hardwareConsumption->quantity_used);
        $this->assertEquals('Drum #D-08', $hardwareConsumption->drum_or_batch_no);
    }
}
