<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Lead;
use App\Models\Project;
use App\Models\ProjectWorkerAttendance;
use App\Models\Site;
use App\Models\SiteSurvey;
use App\Models\SiteSurveyPhoto;
use App\Models\User;
use App\Models\WorkDay;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

/**
 * Site survey photos, worker ID photos and punch selfies used to open for anyone who guessed the
 * number in the URL. Now: internal users see all, a worker only their own, a signed link until it expires.
 */
class PrivatePhotoAccessTest extends TestCase
{
    use RefreshDatabase;

    private Project $project;
    private Worker $worker;
    private ProjectWorkerAttendance $attendance;
    private SiteSurveyPhoto $sitePhoto;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $company = Company::create(['name' => 'CCTV Systems', 'code' => 'CS', 'is_active' => true]);
        $site = Site::create(['company_id' => $company->id, 'name' => 'Plant', 'site_code' => 'PL-01', 'address' => 'Chennai', 'is_active' => true]);
        $this->project = Project::create([
            'project_code' => 'PRJ-2026-PH1', 'title' => 'Warehouse Cameras', 'name' => 'Warehouse Cameras',
            'company_id' => $company->id, 'site_id' => $site->id, 'start_date' => '2026-10-01',
            'status' => 'in_progress', 'per_metre_rate' => 10.00,
        ]);

        $this->worker = $this->workerWithPhotos('Muthu', '9840011122');
        $this->attendance = $this->worker->attendances()->first();

        $lead = Lead::create(['customer_name' => 'Asha Traders', 'phone' => '9876501234', 'status' => 'new']);
        $survey = SiteSurvey::create([
            'lead_id' => $lead->id, 'surveyed_by' => User::factory()->create(['role' => 'technician'])->id,
            'survey_date' => '2026-10-01', 'status' => 'completed',
        ]);
        $this->sitePhoto = $survey->photos()->create([
            'filename' => 'gate-photo.jpg', 'original_name' => 'gate.jpg',
            'image_data' => base64_encode('site-photo-of-the-gate'), 'mime_type' => 'image/jpeg',
        ]);
    }

    /**
     * A worker with an ID photo and one punch selfie, kept only in the database like on Render.
     */
    private function workerWithPhotos(string $name, string $phone): Worker
    {
        $worker = Worker::create([
            'name' => $name, 'phone' => $phone, 'daily_rate' => 800, 'is_active' => true,
            'photo_path' => "worker_photos/worker_{$phone}.jpg",
            'photo_data' => 'data:image/jpeg;base64,' . base64_encode("id-photo-of-{$name}"),
        ]);

        $workDay = WorkDay::firstOrCreate(['project_id' => $this->project->id, 'work_date' => now()->toDateString()]);
        ProjectWorkerAttendance::create([
            'work_day_id' => $workDay->id, 'worker_id' => $worker->id, 'attendance_type' => 'full',
            'photo_path' => "attendance_photos/attendance_{$phone}.jpg",
            'photo_data' => 'data:image/jpeg;base64,' . base64_encode("selfie-of-{$name}"),
            'total_amount' => 800,
        ]);

        return $worker;
    }

    private function workerUser(Worker $worker): User
    {
        $user = User::factory()->create(['name' => $worker->name, 'role' => 'worker', 'email' => "worker_{$worker->phone}@cctv-crm.local"]);
        $worker->update(['user_id' => $user->id]);

        return $user;
    }

    /**
     * Every photo address and the bytes it serves.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    private function photoUrls(): array
    {
        return [
            'site survey photo' => [route('site-surveys.photos.show', $this->sitePhoto), 'site-photo-of-the-gate'],
            'punch selfie' => [route('workers.attendance.photo', $this->attendance), 'selfie-of-Muthu'],
            'worker ID photo' => [route('workers.photo', $this->worker), 'id-photo-of-Muthu'],
            'old selfie link' => [url('/storage/attendance_photos/attendance_9840011122.jpg'), 'selfie-of-Muthu'],
            'old ID photo link' => [url('/storage/worker_photos/worker_9840011122.jpg'), 'id-photo-of-Muthu'],
        ];
    }

    /**
     * The bytes sent back, whether read from disk or from the database copy.
     */
    private function body(TestResponse $response): string
    {
        $base = $response->baseResponse;

        return $base instanceof BinaryFileResponse
            ? (string) file_get_contents($base->getFile()->getPathname())
            : (string) $base->getContent();
    }

    public function test_guest_is_sent_to_sign_in_and_gets_no_photo(): void
    {
        foreach ($this->photoUrls() as $label => [$url, $bytes]) {
            $page = $this->get($url);
            $page->assertRedirect(route('login'));
            $this->assertStringNotContainsString($bytes, $this->body($page), $label);

            $json = $this->getJson($url);
            $json->assertUnauthorized();
            $this->assertStringNotContainsString($bytes, $this->body($json), $label);
        }
    }

    public function test_customer_cannot_open_any_photo(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        foreach ($this->photoUrls() as $label => [$url, $bytes]) {
            $response = $this->actingAs($customer)->get($url);
            $response->assertForbidden();
            $this->assertStringNotContainsString($bytes, $this->body($response), $label);
        }
    }

    public function test_office_users_and_technicians_can_open_every_photo(): void
    {
        foreach (['admin', 'staff', 'technician'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            foreach ($this->photoUrls() as $label => [$url, $bytes]) {
                $response = $this->actingAs($user)->get($url);
                $response->assertOk();
                $this->assertSame($bytes, $this->body($response), "{$role}: {$label}");
            }
        }
    }

    public function test_worker_opens_their_own_photos_but_not_another_workers(): void
    {
        $user = $this->workerUser($this->worker);
        $other = $this->workerWithPhotos('Ravi', '9840033344');
        $otherSelfie = $other->attendances()->first();

        // The portal shows the worker's own selfie, and that address opens for them
        $this->actingAs($user)->get(route('worker.portal'))
            ->assertOk()
            ->assertSee(route('workers.attendance.photo', $this->attendance), false);

        $own = $this->actingAs($user)->get(route('workers.attendance.photo', $this->attendance));
        $own->assertOk();
        $this->assertSame('selfie-of-Muthu', $this->body($own));

        $ownId = $this->actingAs($user)->get(route('workers.photo', $this->worker));
        $ownId->assertOk();
        $this->assertSame('id-photo-of-Muthu', $this->body($ownId));

        $blocked = [
            route('workers.attendance.photo', $otherSelfie),
            route('workers.photo', $other),
            route('site-surveys.photos.show', $this->sitePhoto),
            url('/storage/attendance_photos/attendance_9840033344.jpg'),
            url('/storage/worker_photos/worker_9840033344.jpg'),
        ];

        foreach ($blocked as $url) {
            $response = $this->actingAs($user)->get($url);
            $response->assertForbidden();
            $this->assertStringNotContainsString('Ravi', $this->body($response), $url);
            $this->assertStringNotContainsString('site-photo', $this->body($response), $url);
        }
    }

    public function test_signed_link_opens_a_photo_until_it_expires(): void
    {
        $links = [
            [URL::temporarySignedRoute('site-surveys.photos.show', now()->addMinutes(30), $this->sitePhoto), 'site-photo-of-the-gate'],
            [URL::temporarySignedRoute('workers.attendance.photo', now()->addMinutes(30), $this->attendance), 'selfie-of-Muthu'],
            [URL::temporarySignedRoute('workers.photo', now()->addMinutes(30), $this->worker), 'id-photo-of-Muthu'],
        ];

        foreach ($links as [$url, $bytes]) {
            $response = $this->get($url);
            $response->assertOk();
            $this->assertSame($bytes, $this->body($response));
        }

        $this->travel(31)->minutes();

        foreach ($links as [$url]) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_signed_link_cannot_be_edited_to_open_another_photo(): void
    {
        $other = $this->workerWithPhotos('Ravi', '9840033344');
        $otherSelfie = $other->attendances()->first();

        $url = URL::temporarySignedRoute('workers.attendance.photo', now()->addMinutes(30), $this->attendance);
        $edited = str_replace("/attendance-photos/{$this->attendance->id}?", "/attendance-photos/{$otherSelfie->id}?", $url);
        $this->assertNotSame($url, $edited);

        $response = $this->get($edited);
        $response->assertRedirect(route('login'));
        $this->assertStringNotContainsString('selfie-of-Ravi', $this->body($response));
    }

    public function test_photos_are_marked_private_for_shared_caches(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Twice each: the first answer comes from the database, the second from the copy on disk
        foreach ([1, 2] as $round) {
            if ($round === 2) {
                Storage::disk('public')->put('site-surveys/' . $this->sitePhoto->filename, 'site-photo-of-the-gate');
            }

            foreach ($this->photoUrls() as $label => [$url]) {
                $cacheControl = (string) $this->actingAs($admin)->get($url)->assertOk()->headers->get('Cache-Control');
                $this->assertStringContainsString('private', $cacheControl, "{$label} (round {$round})");
                $this->assertStringNotContainsString('public', $cacheControl, "{$label} (round {$round})");
            }
        }
    }

    public function test_old_storage_links_take_only_a_plain_file_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Storage::disk('public')->put('worker_photos/worker_9840011122.jpg', 'id-photo-of-Muthu');

        // A "/" or "%" in the name could reach other folders on the public disk or match any row
        // in the LIKE lookup. Such names miss the photo routes (Laravel's own storage route refuses them).
        $tricks = [
            '/storage/attendance_photos/..%2Fworker_photos%2Fworker_9840011122.jpg',
            '/storage/attendance_photos/%25.jpg',
            '/storage/worker_photos/%25',
        ];

        foreach ($tricks as $url) {
            $response = $this->actingAs($admin)->get($url);
            $this->assertContains($response->status(), [403, 404], $url);
            $this->assertStringNotContainsString('-of-Muthu', $this->body($response), $url);
        }
    }
}
