<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectWorkerAttendance;
use App\Models\Site;
use App\Models\User;
use App\Models\WorkDay;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkerAttendanceMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Worker $worker;
    protected Project $project;
    protected Carbon $today;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->worker = Worker::create([
            'name' => 'Karthi',
            'phone' => '9876544954',
            'daily_rate' => 1000.00,
            'is_active' => true,
        ]);

        $company = Company::create(['name' => 'CCTV Systems', 'code' => 'CS', 'is_active' => true]);
        $site = Site::create([
            'company_id' => $company->id,
            'name' => 'Industrial Plant',
            'site_code' => 'IP-01',
            'address' => 'Chennai',
            'is_active' => true,
        ]);

        $this->project = Project::create([
            'project_code' => 'PRJ-2026-001',
            'title' => 'Warehouse Surveillance Upgrade',
            'name' => 'Warehouse Surveillance Upgrade',
            'company_id' => $company->id,
            'site_id' => $site->id,
            'start_date' => '2026-10-01',
            'status' => 'in_progress',
            'per_metre_rate' => 10.00,
        ]);

        $this->today = Carbon::parse('2026-10-26');
    }

    protected function publicDisk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        return $disk;
    }

    public function test_worker_profile_photo_upload_and_update(): void
    {
        // 1. Create worker with uploaded photo
        $photo = UploadedFile::fake()->create('karthi_profile.jpg', 50, 'image/jpeg');

        $response = $this->actingAs($this->admin)->post(route('workers.store'), [
            'name' => 'Ramesh Technician',
            'phone' => '9123456780',
            'daily_rate' => 850.00,
            'photo' => $photo,
            'is_active' => '1',
        ]);

        $response->assertRedirect();
        $createdWorker = Worker::where('phone', '9123456780')->firstOrFail();
        $this->assertNotNull($createdWorker->photo_path);
        $this->publicDisk()->assertExists($createdWorker->photo_path);
        $this->assertNotNull($createdWorker->photo_url);
        $this->assertTrue(str_contains($createdWorker->photo_url, 'worker-photos/') || str_contains($createdWorker->photo_url, 'storage/'));

        // 2. Update with replacement photo
        $newPhoto = UploadedFile::fake()->create('ramesh_new.png', 50, 'image/png');
        $oldPath = $createdWorker->photo_path;

        $updateResponse = $this->actingAs($this->admin)->put(route('workers.update', $createdWorker), [
            'name' => 'Ramesh Technician Updated',
            'phone' => '9123456780',
            'daily_rate' => 900.00,
            'photo' => $newPhoto,
            'is_active' => '1',
        ]);

        $updateResponse->assertRedirect();
        $createdWorker->refresh();
        $this->assertNotEquals($oldPath, $createdWorker->photo_path);
        $this->publicDisk()->assertExists($createdWorker->photo_path);
        $this->publicDisk()->assertMissing($oldPath);

        // 3. Remove photo
        $this->actingAs($this->admin)->put(route('workers.update', $createdWorker), [
            'name' => 'Ramesh Technician Updated',
            'daily_rate' => 900.00,
            'remove_photo' => '1',
        ]);

        $createdWorker->refresh();
        $this->assertNull($createdWorker->photo_path);
    }

    public function test_daily_attendance_punch_with_photo_cabling_and_cameras(): void
    {
        $punchPhoto = UploadedFile::fake()->create('punch_selfie.jpg', 50, 'image/jpeg');

        $response = $this->actingAs($this->admin)->post(route('workers.attendance.store'), [
            'worker_id' => $this->worker->id,
            'project_id' => $this->project->id,
            'work_date' => $this->today->toDateString(),
            'attendance_type' => 'full',
            'check_in_time' => '09:15',
            'cabling_metres' => 120.5,
            'cameras_installed' => 6,
            'extra_amount' => 200.00,
            'extra_description' => 'Late evening termination',
            'notes' => 'Completed perimeter camera wiring',
            'photo' => $punchPhoto,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $attendance = ProjectWorkerAttendance::where('worker_id', $this->worker->id)->firstOrFail();
        $this->assertEquals('full', $attendance->attendance_type);
        $this->assertEquals(120.5, (float) $attendance->cabling_metres);
        $this->assertEquals(6, $attendance->cameras_installed);
        $this->assertNotNull($attendance->photo_path);
        $this->publicDisk()->assertExists($attendance->photo_path);

        // Base Pay = 1000.00, Cabling Amount = 120.5 * 10 = 1205.00, Extra = 200.00 => Total = 2405.00
        $this->assertEquals(2405.00, (float) $attendance->total_amount);

        // Inspect Day View
        $viewResponse = $this->actingAs($this->admin)->get(route('workers.attendance.index', [
            'view' => 'day',
            'date' => $this->today->toDateString(),
        ]));

        $viewResponse->assertOk();
        $viewResponse->assertSee('Karthi');
        $viewResponse->assertSee('120.5');
        $viewResponse->assertSee('6');
        $viewResponse->assertSee('2,405.00');
    }

    public function test_weekly_matrix_view_aggregates_shifts(): void
    {
        $monday = Carbon::parse('2026-10-19'); // Monday
        $tuesday = Carbon::parse('2026-10-20'); // Tuesday

        $workDayMon = WorkDay::create(['project_id' => $this->project->id, 'work_date' => $monday->toDateString()]);
        $workDayTue = WorkDay::create(['project_id' => $this->project->id, 'work_date' => $tuesday->toDateString()]);

        ProjectWorkerAttendance::create([
            'work_day_id' => $workDayMon->id,
            'worker_id' => $this->worker->id,
            'attendance_type' => 'full',
            'cabling_metres' => 50.0,
            'cameras_installed' => 3,
            'total_amount' => 1500.00,
        ]);

        ProjectWorkerAttendance::create([
            'work_day_id' => $workDayTue->id,
            'worker_id' => $this->worker->id,
            'attendance_type' => 'half',
            'cabling_metres' => 25.0,
            'cameras_installed' => 1,
            'total_amount' => 750.00,
        ]);

        $response = $this->actingAs($this->admin)->get(route('workers.attendance.index', [
            'view' => 'week',
            'week' => $monday->toDateString(),
        ]));

        $response->assertOk();
        $response->assertSee('Karthi');
        $response->assertSee('1.5'); // Total Shifts (1 full + 0.5 half)
        $response->assertSee('75.0'); // 50 + 25 m
        $response->assertSee('4'); // 3 + 1 cameras
        $response->assertSee('2,250.00'); // 1500 + 750
    }

    public function test_monthly_timesheet_summary_view(): void
    {
        $day1 = Carbon::parse('2026-10-05');
        $day2 = Carbon::parse('2026-10-12');

        $wd1 = WorkDay::create(['project_id' => $this->project->id, 'work_date' => $day1->toDateString()]);
        $wd2 = WorkDay::create(['project_id' => $this->project->id, 'work_date' => $day2->toDateString()]);

        ProjectWorkerAttendance::create([
            'work_day_id' => $wd1->id,
            'worker_id' => $this->worker->id,
            'attendance_type' => 'full',
            'cabling_metres' => 100.0,
            'cameras_installed' => 5,
            'total_amount' => 2000.00,
            'is_paid' => true,
        ]);

        ProjectWorkerAttendance::create([
            'work_day_id' => $wd2->id,
            'worker_id' => $this->worker->id,
            'attendance_type' => 'full',
            'cabling_metres' => 80.0,
            'cameras_installed' => 4,
            'total_amount' => 1800.00,
            'is_paid' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('workers.attendance.index', [
            'view' => 'month',
            'month' => '2026-10',
        ]));

        $response->assertOk();
        $response->assertSee('Karthi');
        $response->assertSee('180.0'); // 100 + 80
        $response->assertSee('9'); // 5 + 4
        $response->assertSee('3,800.00'); // Total gross
        $response->assertSee('2,000.00'); // Settled / Paid
        $response->assertSee('1,800.00'); // Unpaid Balance
    }

    public function test_admin_can_delete_unsettled_attendance_and_cleans_photo(): void
    {
        $punchPhoto = UploadedFile::fake()->create('selfie.jpg', 50, 'image/jpeg');
        $storedPath = $punchPhoto->store('attendance_photos', 'public');

        $workDay = WorkDay::create(['project_id' => $this->project->id, 'work_date' => $this->today->toDateString()]);
        $attendance = ProjectWorkerAttendance::create([
            'work_day_id' => $workDay->id,
            'worker_id' => $this->worker->id,
            'attendance_type' => 'full',
            'photo_path' => $storedPath,
            'total_amount' => 1000.00,
            'is_paid' => false,
        ]);

        $this->publicDisk()->assertExists($storedPath);

        $response = $this->actingAs($this->admin)->delete(route('workers.attendance.destroy', $attendance));

        $response->assertRedirect();
        $this->assertDatabaseMissing('project_worker_attendances', ['id' => $attendance->id]);
        $this->publicDisk()->assertMissing($storedPath);
    }

    public function test_cross_server_photo_fallback_restores_image_from_database(): void
    {
        // Simulate a photo created on Render (exists in DB as base64, but NOT present on local disk)
        $fakeBase64 = 'data:image/jpeg;base64,' . base64_encode('fake-image-bytes-from-render');
        $workDay = WorkDay::create(['project_id' => $this->project->id, 'work_date' => $this->today->toDateString()]);
        $attendance = ProjectWorkerAttendance::create([
            'work_day_id' => $workDay->id,
            'worker_id' => $this->worker->id,
            'attendance_type' => 'full',
            'photo_path' => 'attendance_photos/attendance_test_missing_on_local.jpg',
            'photo_data' => $fakeBase64,
            'total_amount' => 1000.00,
        ]);

        // Verify file is missing from local disk
        $this->publicDisk()->assertMissing('attendance_photos/attendance_test_missing_on_local.jpg');

        // Requesting via streamPhoto endpoint should succeed, serve image, and cache to local disk
        $response = $this->get(route('workers.attendance.photo', $attendance));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
        $this->assertEquals('fake-image-bytes-from-render', $response->getContent());
        $this->publicDisk()->assertExists('attendance_photos/attendance_test_missing_on_local.jpg');

        // Delete disk file again to test storage fallback route
        $this->publicDisk()->delete('attendance_photos/attendance_test_missing_on_local.jpg');
        $storageFallbackResponse = $this->get('/storage/attendance_photos/attendance_test_missing_on_local.jpg');
        $storageFallbackResponse->assertOk();
        $this->assertEquals('fake-image-bytes-from-render', $storageFallbackResponse->getContent());
        $this->publicDisk()->assertExists('attendance_photos/attendance_test_missing_on_local.jpg');
    }

    public function test_attendance_page_loads_cleanly_when_worker_is_soft_deleted_or_null(): void
    {
        $workDay = WorkDay::create([
            'project_id' => $this->project->id,
            'work_date' => $this->today->toDateString(),
        ]);

        $attendance = ProjectWorkerAttendance::create([
            'work_day_id' => $workDay->id,
            'worker_id' => $this->worker->id,
            'attendance_type' => 'full',
            'daily_rate_snapshot' => 1000.00,
            'rate_applied' => 1000.00,
            'total_amount' => 1000.00,
            'check_in_at' => now(),
        ]);

        // Soft-delete the worker
        $this->worker->delete();
        $this->assertSoftDeleted('workers', ['id' => $this->worker->id]);

        // 1. Test Day View
        $response = $this->actingAs($this->admin)->get(route('workers.attendance.index', [
            'view' => 'day',
            'date' => $this->today->toDateString(),
        ]));
        $response->assertOk();
        $response->assertSee($this->worker->name);
        $response->assertSee('Archived');

        // 2. Test Week View
        $weekResponse = $this->actingAs($this->admin)->get(route('workers.attendance.index', [
            'view' => 'week',
            'week' => $this->today->startOfWeek()->toDateString(),
        ]));
        $weekResponse->assertOk();

        // 3. Test Month View
        $monthResponse = $this->actingAs($this->admin)->get(route('workers.attendance.index', [
            'view' => 'month',
            'month' => $this->today->format('Y-m'),
        ]));
        $monthResponse->assertOk();
    }
}
