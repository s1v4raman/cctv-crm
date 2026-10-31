<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Add buttons show the shared plus icon (<x-icon.plus />) in front of a plain label. A "+" typed
 * into the label next to an icon shows up as two plus signs ("+ + Mark Attendance").
 */
class ButtonLabelPlusSignTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_view_types_a_plus_sign_in_front_of_a_label(): void
    {
        $offenders = [];
        foreach ((new Finder())->files()->in(resource_path('views'))->name('*.blade.php') as $file) {
            foreach (preg_split('/\R/', $file->getContents()) as $i => $line) {
                // Label text such as ">+ Add", a line starting "+ Record", or a quoted "'+ Assign'".
                if (preg_match('/(>|^)\s*(\+|&plus;|&#43;)\s?[A-Z][a-z]|[\'"]\+ [A-Z][a-z]/', $line)) {
                    $offenders[] = $file->getRelativePathname() . ':' . ($i + 1) . '  ' . trim($line);
                }
            }
        }

        $this->assertSame([], $offenders, "Use <x-icon.plus /> instead of typing \"+\" into these labels:\n" . implode("\n", $offenders));
    }

    public function test_project_work_days_button_has_one_plus(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'project_code' => 'PRJ-2026-0005',
            'title' => 'Anand Textiles 32-Camera CCTV',
            'project_type' => 'hardware_cctv',
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('<span>Mark Attendance</span>', false)
            ->assertDontSee('+ Mark Attendance')
            ->assertSee('<span>Upload Document / DC</span>', false)
            ->assertSee('<span>Record Field Usage</span>', false);
    }
}
