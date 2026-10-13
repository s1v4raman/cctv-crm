<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationLeadCreationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_select_lead_page_displays_add_new_customer_button(): void
    {
        $response = $this->actingAs($this->admin)->get(route('quotations.create-general'));

        $response->assertStatus(200);
        $response->assertSee('+ Add New Customer');
        $response->assertSee(route('leads.create', ['return_to' => 'quotation', 'for_quote' => 1]));
    }

    public function test_leads_create_displays_quotation_context_when_referred_from_quotation(): void
    {
        $response = $this->actingAs($this->admin)->get(route('leads.create', ['return_to' => 'quotation', 'for_quote' => 1]));

        $response->assertStatus(200);
        $response->assertSee('New Customer for Quotation');
        $response->assertSee('Back to Select Customer');
        $response->assertSee('Save &amp; Continue to Quotation', false);
    }

    public function test_creating_lead_from_quotation_flow_redirects_directly_to_quotation_builder(): void
    {
        $payload = [
            'customer_name' => 'Metro Retail Mall',
            'phone' => '+91 98888 77777',
            'email' => 'contact@metromall.example.com',
            'site_address' => 'Outer Ring Road, Bengaluru',
            'for_quote' => 1,
            'return_to' => 'quotation',
        ];

        $response = $this->actingAs($this->admin)->post(route('leads.store'), $payload);

        $lead = Lead::where('email', 'contact@metromall.example.com')->first();
        $this->assertNotNull($lead);

        $response->assertRedirect(route('quotations.create', $lead));
        $response->assertSessionHas('status');
    }

    public function test_creating_lead_directly_from_lead_module_does_not_redirect_to_quotation(): void
    {
        $payload = [
            'customer_name' => 'Regular Inbound Client',
            'phone' => '+91 97777 66666',
            'email' => 'client@regular.example.com',
            'site_address' => 'MG Road, Pune',
        ];

        $response = $this->actingAs($this->admin)->post(route('leads.store'), $payload);

        $lead = Lead::where('email', 'client@regular.example.com')->first();
        $this->assertNotNull($lead);

        // Direct lead creation must go to leads.show, NOT quotation
        $response->assertRedirect(route('leads.show', $lead));
        $response->assertSessionHas('status', 'Lead created.');
    }
}
