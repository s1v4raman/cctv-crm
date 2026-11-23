<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_quotation_header_has_no_preview_button_and_has_add_project_button()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lead = Lead::create([
            'customer_name' => 'John Doe',
            'phone' => '9876543210',
            'status' => 'new',
            'created_by' => $admin->id,
        ]);

        $quotation = Quotation::create([
            'lead_id' => $lead->id,
            'quotation_no' => 'QT-20261123-0099',
            'status' => 'accepted',
            'subtotal' => 10000,
            'tax_amount' => 1800,
            'total_amount' => 11800,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('quotations.show', $quotation));
        $response->assertStatus(200);

        // Preview button must be removed from the header actions
        $response->assertDontSee('title="Preview Quotation in Live Page"', false);

        // Add Project button must be present in its place
        $response->assertSee('+ Add Project', false);
        $response->assertSee('projects/create?quotation_id=' . $quotation->id, false);
        $response->assertSee('lead_id=' . $lead->id, false);

        // Workflow pipeline banner must be present
        $response->assertSee('Stage 2 of 5:', false);
        $response->assertSee('Quotation', false);
    }

    public function test_quotation_header_shows_view_project_when_project_exists()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lead = Lead::create([
            'customer_name' => 'Alice Smith',
            'phone' => '9876543211',
            'status' => 'won',
            'created_by' => $admin->id,
        ]);

        $quotation = Quotation::create([
            'lead_id' => $lead->id,
            'quotation_no' => 'QT-20261123-0100',
            'status' => 'accepted',
            'subtotal' => 20000,
            'tax_amount' => 3600,
            'total_amount' => 23600,
            'created_by' => $admin->id,
        ]);

        $project = Project::create([
            'project_code' => 'PRJ-2026-0099',
            'title' => 'Alice Smith - CCTV Project',
            'lead_id' => $lead->id,
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('quotations.show', $quotation));
        $response->assertStatus(200);

        $response->assertSee('View Project #PRJ-2026-0099 &rarr;', false);
        $response->assertSee(route('projects.show', $project), false);
    }

    public function test_lead_header_shows_add_project_button()
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $lead = Lead::create([
            'customer_name' => 'Mega Mart',
            'phone' => '9876543212',
            'status' => 'quoted',
            'created_by' => $staff->id,
        ]);

        $response = $this->actingAs($staff)->get(route('leads.show', $lead));
        $response->assertStatus(200);

        $response->assertSee('+ Add Project', false);
        $response->assertSee(route('projects.create', ['lead_id' => $lead->id]), false);
        $response->assertSee('Stage 1 of 5:', false);
    }

    public function test_project_wizard_prepopulates_from_quotation()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lead = Lead::create([
            'customer_name' => 'Tech Park',
            'phone' => '9876543213',
            'site_address' => 'Avinashi Road, Coimbatore',
            'status' => 'won',
            'created_by' => $admin->id,
        ]);

        $product = Product::create([
            'name' => 'Bullet Camera 4MP',
            'name_key' => 'bullet-camera-4mp',
            'unit' => 'nos',
            'cost_price' => 1500,
            'unit_price' => 2500,
            'created_by' => $admin->id,
        ]);

        $quotation = Quotation::create([
            'lead_id' => $lead->id,
            'quotation_no' => 'QT-20261123-0101',
            'status' => 'accepted',
            'subtotal' => 10000,
            'tax_amount' => 1800,
            'total' => 11800,
            'created_by' => $admin->id,
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id' => $product->id,
            'item_name' => 'Bullet Camera 4MP',
            'description' => 'Bullet Camera 4MP',
            'quantity' => 4,
            'unit_price' => 2500,
            'total' => 10000,
        ]);

        $response = $this->actingAs($admin)->get(route('projects.create', [
            'quotation_id' => $quotation->id,
            'lead_id' => $lead->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Tech Park - CCTV Project', false);
        $response->assertSee('11800', false);
        $response->assertSee('Bullet Camera 4MP', false);
    }
}
