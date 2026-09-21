<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VcardAndShareTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Lead $lead;
    private Quotation $quotation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->lead = Lead::create([
            'customer_name' => 'Rajesh Sharma',
            'email' => 'rajesh.sharma@example.com',
            'phone' => '9876543210',
            'site_address' => 'Plot 45, Industrial Area, Phase 2, Chennai',
            'source' => 'website',
            'status' => 'quoted',
        ]);

        $this->quotation = Quotation::create([
            'lead_id' => $this->lead->id,
            'quotation_no' => 'QT-20260907-0001',
            'quotation_date' => now(),
            'subtotal' => 25000,
            'discount' => 0,
            'tax_percent' => 18,
            'tax_amount' => 4500,
            'total' => 29500,
            'status' => 'draft',
        ]);
    }

    public function test_admin_can_download_lead_vcard_for_automatic_phone_contact_saving(): void
    {
        $response = $this->actingAs($this->admin)->get(route('leads.vcard', $this->lead));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/vcard; charset=utf-8');
        $this->assertStringContainsString('attachment; filename="Contact_Rajesh_Sharma.vcf"', $response->headers->get('Content-Disposition'));
        
        $content = $response->getContent();
        $this->assertStringContainsString('BEGIN:VCARD', $content);
        $this->assertStringContainsString('FN:Rajesh Sharma', $content);
        $this->assertStringContainsString('TEL;TYPE=CELL,VOICE:9876543210', $content);
        $this->assertStringContainsString('EMAIL;TYPE=INTERNET,WORK:rajesh.sharma@example.com', $content);
        $this->assertStringContainsString('END:VCARD', $content);
    }

    public function test_admin_can_download_quotation_customer_vcard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('quotations.vcard', $this->quotation));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/vcard; charset=utf-8');
        $content = $response->getContent();
        $this->assertStringContainsString('FN:Rajesh Sharma', $content);
        $this->assertStringContainsString('TEL;TYPE=CELL,VOICE:9876543210', $content);
    }

    public function test_quotation_view_contains_fast_action_buttons(): void
    {
        $response = $this->actingAs($this->admin)->get(route('quotations.show', $this->quotation));

        $response->assertOk();
        $response->assertSee('Send on WhatsApp');
        $response->assertSee('Save Contact to Phone');
        $response->assertSee('shareWhatsAppWithPdf');
    }

    public function test_lead_show_page_loads_all_relations_successfully(): void
    {
        $response = $this->actingAs($this->admin)->get(route('leads.show', $this->lead));

        $response->assertOk();
        $response->assertSee('Rajesh Sharma');
        $response->assertSee('9876543210');
        $response->assertSee('Save Contact');
    }
}
