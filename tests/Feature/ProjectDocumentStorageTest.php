<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectDocumentFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Uploaded documents must still open after the server disk is wiped on a restart.
 */
class ProjectDocumentStorageTest extends TestCase
{
    use RefreshDatabase;

    private const PDF = "%PDF-1.4\n% Delivery challan DC-118 for Anand Textiles\n%%EOF";

    private User $admin;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->project = Project::create([
            'project_code' => 'PRJ-2026-0005',
            'title' => 'Anand Textiles 32-Camera CCTV',
            'project_type' => 'hardware_cctv',
            'status' => 'in_progress',
            'created_by' => $this->admin->id,
        ]);
    }

    private function uploadDc(UploadedFile $file, array $extra = [])
    {
        return $this->actingAs($this->admin)->post(route('projects.documents.store', $this->project), array_merge([
            'document_type' => 'delivery_challan',
            'dc_number' => 'DC-118',
            'dc_date' => '2026-10-30',
            'items_summary' => '32x Bullet Camera, 2x NVR, 1500m CAT6',
            'file' => $file,
        ], $extra));
    }

    public function test_upload_keeps_a_database_copy_and_records_the_file_type(): void
    {
        $this->uploadDc(UploadedFile::fake()->createWithContent('DC-118.pdf', self::PDF))
            ->assertRedirect()
            ->assertSessionHas('status');

        $document = ProjectDocument::firstOrFail();
        $this->assertSame('pdf', $document->file_type);
        $this->assertSame('application/pdf', $document->mime_type);
        $this->assertSame('public', $document->storage_disk);
        $this->assertSame(self::PDF, base64_decode($document->storedFile->data));
        Storage::disk('public')->assertExists($document->file_path);
    }

    public function test_document_still_opens_after_the_disk_is_wiped(): void
    {
        $this->uploadDc(UploadedFile::fake()->createWithContent('DC-118.pdf', self::PDF));
        $document = ProjectDocument::firstOrFail();

        // What a restart on the host does to the uploads folder.
        Storage::disk('public')->deleteDirectory('projects');
        Storage::disk('public')->assertMissing($document->file_path);

        $view = $this->actingAs($this->admin)->get(route('projects.documents.view', $document));
        $view->assertOk();
        $this->assertSame(self::PDF, $view->getContent());
        $this->assertSame('application/pdf', $view->headers->get('content-type'));
        $this->assertStringStartsWith('inline', $view->headers->get('content-disposition'));
        $this->assertSame('nosniff', $view->headers->get('x-content-type-options'));

        $download = $this->actingAs($this->admin)->get(route('projects.documents.download', $document));
        $download->assertOk();
        $this->assertSame(self::PDF, $download->getContent());
        $this->assertSame('attachment; filename=DC-118.pdf', $download->headers->get('content-disposition'));
    }

    public function test_a_file_lost_before_the_fix_says_so_instead_of_showing_a_blank_page(): void
    {
        $document = ProjectDocument::create([
            'project_id' => $this->project->id,
            'title' => 'Delivery Challan #73',
            'document_type' => 'delivery_challan',
            'dc_number' => '73',
            'file_name' => 'dc_73.pdf',
            'file_path' => "projects/{$this->project->id}/documents/gone.pdf",
            'file_size' => 1024,
            'uploaded_by' => $this->admin->id,
        ]);

        foreach (['view', 'download'] as $action) {
            $this->actingAs($this->admin)->get(route("projects.documents.{$action}", $document))
                ->assertNotFound()
                ->assertSee('This file is no longer on the server')
                ->assertSee('Upload it again');
        }

        Storage::disk('public')->assertMissing($document->file_path);
    }

    public function test_only_pdfs_and_pictures_open_in_the_browser(): void
    {
        $this->uploadDc(UploadedFile::fake()->create('PO-118.docx', 20, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'), [
            'document_type' => 'purchase_order',
            'dc_number' => null,
        ])->assertSessionHas('status');

        $document = ProjectDocument::firstOrFail();
        $view = $this->actingAs($this->admin)->get(route('projects.documents.view', $document));
        $view->assertOk();
        $this->assertStringStartsWith('attachment', $view->headers->get('content-disposition'));
    }

    public function test_web_pages_and_programs_cannot_be_uploaded(): void
    {
        $this->uploadDc(UploadedFile::fake()->createWithContent('DC-118.html', '<script>alert(1)</script>'))
            ->assertSessionHasErrors('file');

        $this->assertSame(0, ProjectDocument::count());
    }

    public function test_project_page_shows_why_an_upload_was_rejected_and_the_file_size(): void
    {
        $this->uploadDc(UploadedFile::fake()->createWithContent('DC-118.pdf', self::PDF));

        $this->actingAs($this->admin)
            ->from(route('projects.show', ['project' => $this->project, 'tab' => 'documents']))
            ->followingRedirects()
            ->post(route('projects.documents.store', $this->project), [
                'document_type' => 'delivery_challan',
                'dc_number' => 'DC-119',
                'file' => UploadedFile::fake()->create('huge-scan.pdf', 25 * 1024, 'application/pdf'),
            ])
            ->assertSee('Document not uploaded:')
            ->assertSee(strlen(self::PDF) . ' B');
    }

    public function test_migration_copies_files_still_on_disk_and_drops_blank_placeholders(): void
    {
        $blank = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Count 1/Kids[3 0 R]>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 595 842]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000052 00000 n \n0000000101 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n178\n%%EOF";
        $make = fn (string $path) => ProjectDocument::create([
            'project_id' => $this->project->id,
            'title' => $path,
            'document_type' => 'delivery_challan',
            'file_name' => basename($path),
            'file_path' => $path,
            'uploaded_by' => $this->admin->id,
        ]);
        $kept = $make('projects/5/documents/real.pdf');
        $placeholder = $make('projects/5/documents/blank.pdf');
        $olderPlaceholder = $make('projects/5/documents/blank-older.pdf');
        $lost = $make('projects/5/documents/lost.pdf');
        Storage::disk('public')->put($kept->file_path, self::PDF);
        Storage::disk('public')->put($placeholder->file_path, $blank);
        Storage::disk('public')->put($olderPlaceholder->file_path, $blank . "\n\n");

        Schema::drop('project_document_files');
        (require database_path('migrations/2026_10_31_130000_create_project_document_files_table.php'))->up();

        $this->assertSame(self::PDF, $kept->fresh()->fileContents());
        $this->assertNull($placeholder->fresh()->fileContents());
        $this->assertNull($olderPlaceholder->fresh()->fileContents());
        $this->assertNull($lost->fresh()->fileContents());
        Storage::disk('public')->assertMissing($placeholder->file_path);
        Storage::disk('public')->assertMissing($olderPlaceholder->file_path);
        $this->assertSame(1, ProjectDocumentFile::count());
    }

    public function test_deleting_a_document_removes_the_database_copy(): void
    {
        $this->uploadDc(UploadedFile::fake()->createWithContent('DC-118.pdf', self::PDF));
        $document = ProjectDocument::firstOrFail();

        $this->actingAs($this->admin)->delete(route('projects.documents.destroy', $document))->assertRedirect();

        $this->assertSoftDeleted($document);
        $this->assertSame(0, ProjectDocumentFile::count());
        Storage::disk('public')->assertMissing($document->file_path);
    }
}
