<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectShowLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_show_html_structure(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Sivaraman']);
        $project = Project::create([
            'project_code' => 'PRJ-2026-TEST',
            'title' => 'A.C.S. Medical College',
            'project_type' => 'hardware_cctv',
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('projects.show', $project));
        $response->assertStatus(200);

        $html = $response->getContent();
        $posHeader = strpos($html, 'crm-topbar');
        $posBack = strpos($html, 'back-link');
        
        $this->assertNotFalse($posHeader, 'crm-topbar must exist');
        $this->assertNotFalse($posBack, 'back-link must exist');
        $this->assertLessThan($posBack, $posHeader, 'crm-topbar must appear BEFORE back-link in HTML');
    }

    public function test_document_download_does_not_fail_when_file_missing(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Sivaraman']);
        $project = Project::create([
            'project_code' => 'PRJ-2026-TEST2',
            'title' => 'A.C.S. Medical College',
            'project_type' => 'hardware_cctv',
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $document = ProjectDocument::create([
            'project_id' => $project->id,
            'title' => 'Delivery Challan #73',
            'document_type' => 'delivery_challan',
            'dc_number' => '73',
            'file_name' => 'dc_73.pdf',
            'file_path' => 'projects/' . $project->id . '/documents/test_missing.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'storage_disk' => 'public',
            'uploaded_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('projects.documents.download', [$project, $document]));
        $response->assertStatus(200);
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }
}
