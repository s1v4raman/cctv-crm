<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectHardwareAndSoftwareModulesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_can_view_projects_index_with_type_segmentation_kpis(): void
    {
        Project::create([
            'project_code' => 'PRJ-HW-001',
            'title' => 'Face Recognition Terminal System',
            'project_type' => 'hardware_attendance',
            'company_name' => 'Metro Retail Corp',
            'status' => 'in_progress',
            'priority' => 'high',
            'budget' => 150000,
            'hardware_specs' => [
                'terminal_count' => 4,
                'camera_count' => 8,
                'device_brand' => 'ZKTeco ProFace X',
                'terminal_ip' => '192.168.1.100',
                'attendance_sync_mode' => 'face_recognition',
                'cloud_sync_enabled' => true,
            ],
        ]);

        Project::create([
            'project_code' => 'PRJ-SW-001',
            'title' => 'Client Portal Webpage',
            'project_type' => 'software_web',
            'company_name' => 'Apex Cloud Inc',
            'status' => 'completed',
            'priority' => 'medium',
            'budget' => 80000,
            'software_specs' => [
                'webpage_url' => 'https://portal.apexcloud.com',
                'tech_stack' => 'Laravel, Tailwind, MySQL',
            ],
        ]);

        $response = $this->actingAs($this->admin)->get(route('projects.index'));

        $response->assertStatus(200);
        $response->assertSee('Project Handling &amp; Tracking', false);
        $response->assertSee('Terminal Attendance');
        $response->assertSee('Webpage &amp; Apps', false);
        $response->assertSee('Metro Retail Corp');
        $response->assertSee('Apex Cloud Inc');
        $response->assertSee('ZKTeco ProFace X');
    }

    public function test_can_filter_projects_by_project_type(): void
    {
        Project::create([
            'project_code' => 'PRJ-HW-002',
            'title' => 'Biometric Terminal Rollout',
            'project_type' => 'hardware_attendance',
            'company_name' => 'Hardware Corp',
            'status' => 'in_progress',
            'priority' => 'urgent',
            'budget' => 200000,
        ]);

        Project::create([
            'project_code' => 'PRJ-SW-002',
            'title' => 'Corporate Webpage Revamp',
            'project_type' => 'software_web',
            'company_name' => 'Web Design Ltd',
            'status' => 'in_progress',
            'priority' => 'low',
            'budget' => 60000,
        ]);

        // Filter for hardware attendance only
        $hwResponse = $this->actingAs($this->admin)->get(route('projects.index', ['type' => 'hardware_attendance']));
        $hwResponse->assertStatus(200);
        $hwResponse->assertSee('Hardware Corp');
        $hwResponse->assertDontSee('Web Design Ltd');

        // Filter for software web only
        $swResponse = $this->actingAs($this->admin)->get(route('projects.index', ['type' => 'software_web']));
        $swResponse->assertStatus(200);
        $swResponse->assertSee('Web Design Ltd');
        $swResponse->assertDontSee('Hardware Corp');
    }

    public function test_can_create_hardware_attendance_project_with_specs(): void
    {
        $payload = [
            'company_name' => 'Omega Logistics Hub',
            'title' => 'Terminal Camera Attendance Integration',
            'project_type' => 'hardware_attendance',
            'project_code' => 'PRJ-2026-OMEGA',
            'status' => 'in_progress',
            'priority' => 'urgent',
            'progress_percentage' => 40,
            'budget' => 350000.00,
            'hardware_specs' => [
                'terminal_count' => 6,
                'camera_count' => 12,
                'device_brand' => 'Hikvision MinMoe Face Terminals',
                'terminal_ip' => '10.0.1.50 - 10.0.1.55',
                'attendance_sync_mode' => 'face_recognition',
                'cloud_sync_enabled' => true,
            ],
            'description' => 'Biometric turnstiles and CCTV integration at factory gate.',
        ];

        $response = $this->actingAs($this->admin)->post(route('projects.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'project_code' => 'PRJ-2026-OMEGA',
            'project_type' => 'hardware_attendance',
            'company_name' => 'Omega Logistics Hub',
        ]);

        $project = Project::where('project_code', 'PRJ-2026-OMEGA')->first();
        $this->assertEquals(6, $project->hardware_specs['terminal_count']);
        $this->assertEquals('Hikvision MinMoe Face Terminals', $project->hardware_specs['device_brand']);
    }

    public function test_can_create_software_webpage_project_with_specs(): void
    {
        $payload = [
            'company_name' => 'Alpha Cloud SaaS',
            'title' => 'Attendance Management Webpage & Mobile Portal',
            'project_type' => 'software_web',
            'project_code' => 'PRJ-2026-ALPHA',
            'status' => 'in_progress',
            'priority' => 'high',
            'progress_percentage' => 60,
            'budget' => 180000.00,
            'software_specs' => [
                'webpage_url' => 'https://alpha-portal.example.com',
                'repository_url' => 'https://github.com/alphasaas/attendance-web',
                'tech_stack' => 'Laravel 11, Tailwind, Alpine.js',
                'deployment_server' => 'Ubuntu Nginx Cloudflare',
            ],
            'description' => 'SaaS Webpage dashboard for live employee shifts and biometric attendance analytics.',
        ];

        $response = $this->actingAs($this->admin)->post(route('projects.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'project_code' => 'PRJ-2026-ALPHA',
            'project_type' => 'software_web',
            'company_name' => 'Alpha Cloud SaaS',
        ]);

        $project = Project::where('project_code', 'PRJ-2026-ALPHA')->first();
        $this->assertEquals('https://alpha-portal.example.com', $project->software_specs['webpage_url']);
        $this->assertEquals('Laravel 11, Tailwind, Alpine.js', $project->software_specs['tech_stack']);
    }

    public function test_project_show_displays_hardware_and_attendance_hub_link(): void
    {
        $project = Project::create([
            'project_code' => 'PRJ-SHOW-001',
            'title' => 'Factory Biometric Terminal Deploy',
            'project_type' => 'hardware_attendance',
            'company_name' => 'Sterling Heavy Industries',
            'status' => 'in_progress',
            'priority' => 'high',
            'hardware_specs' => [
                'terminal_count' => 10,
                'camera_count' => 20,
                'device_brand' => 'Matrix COSEC Face',
                'terminal_ip' => '192.168.10.50',
                'attendance_sync_mode' => 'face_recognition',
            ],
        ]);

        $response = $this->actingAs($this->admin)->get(route('projects.show', $project));

        $response->assertStatus(200);
        $response->assertSee('Terminal &amp; Camera Hardware', false);
        $response->assertSee('10 Units');
        $response->assertSee('Matrix COSEC Face');
        $response->assertSee(route('attendance.index'));
    }

    public function test_export_csv_includes_project_type_and_specs(): void
    {
        Project::create([
            'project_code' => 'PRJ-CSV-001',
            'title' => 'Terminal Camera Rollout',
            'project_type' => 'hardware_attendance',
            'company_name' => 'CSV Test Corp',
            'status' => 'completed',
            'priority' => 'medium',
            'budget' => 100000,
            'hardware_specs' => [
                'terminal_count' => 2,
                'camera_count' => 4,
                'device_brand' => 'Hikvision',
            ],
        ]);

        $response = $this->actingAs($this->admin)->get(route('projects.export-csv'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        
        $content = $response->streamedContent();
        $this->assertStringContainsString('Project Type', $content);
        $this->assertStringContainsString('Hardware Terminals/Cameras', $content);
        $this->assertStringContainsString('Terminal Camera & Attendance', $content);
        $this->assertStringContainsString('2 Terminals, 4 Cams (Hikvision)', $content);
    }
}
