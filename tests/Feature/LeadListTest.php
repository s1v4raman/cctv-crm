<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Quotation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The leads list: filter panel, letter filter, board view, CSV export and the actions
 * on ticked rows (status for office users, delete for admins only).
 */
class LeadListTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->technician = User::factory()->create(['role' => 'technician']);
    }

    public function test_filters_narrow_the_list(): void
    {
        Lead::create(['customer_name' => 'Anil Kumar', 'phone' => '9000000001', 'status' => 'won', 'source' => 'Referral']);
        Lead::create(['customer_name' => 'Bala Traders', 'phone' => '9000000002', 'status' => 'new', 'source' => 'Website']);
        $old = Lead::create(['customer_name' => 'Chitra Hospital', 'phone' => '9000000003', 'status' => 'new', 'gstin' => '33AAACT1234T1Z8']);
        $old->timestamps = false;
        $old->forceFill(['created_at' => now()->subDays(40), 'updated_at' => now()->subDays(40)])->save();

        $this->actingAs($this->staff)->get(route('leads.index', ['status' => ['won']]))
            ->assertOk()->assertSee('Anil Kumar')->assertDontSee('Bala Traders');

        $this->actingAs($this->staff)->get(route('leads.index', ['source' => ['Website']]))
            ->assertSee('Bala Traders')->assertDontSee('Anil Kumar');

        $this->actingAs($this->staff)->get(route('leads.index', ['letter' => 'C']))
            ->assertSee('Chitra Hospital')->assertDontSee('Bala Traders');

        $this->actingAs($this->staff)->get(route('leads.index', ['only' => ['untouched']]))
            ->assertSee('Chitra Hospital')->assertDontSee('Anil Kumar');

        $this->actingAs($this->staff)->get(route('leads.index', ['only' => ['business']]))
            ->assertSee('Chitra Hospital')->assertDontSee('Bala Traders');

        $this->actingAs($this->staff)->get(route('leads.index', ['view' => 'open', 'search' => 'bala']))
            ->assertSee('Bala Traders')->assertDontSee('Chitra Hospital')->assertSee('Open Leads');
    }

    public function test_quotation_filters_and_board_view(): void
    {
        $quoted = Lead::create(['customer_name' => 'Quoted Customer', 'phone' => '9000000011', 'status' => 'quoted']);
        Lead::create(['customer_name' => 'Fresh Customer', 'phone' => '9000000012']);
        Quotation::create([
            'lead_id' => $quoted->id, 'quotation_no' => 'QT-LIST-1', 'quotation_date' => Carbon::today(),
            'valid_until' => Carbon::today()->addDays(15), 'subtotal' => 1000, 'discount' => 0, 'tax_percent' => 18, 'tax_amount' => 180, 'total' => 1180, 'status' => 'sent',
        ]);

        $this->actingAs($this->staff)->get(route('leads.index', ['only' => ['quoted']]))
            ->assertSee('Quoted Customer')->assertDontSee('Fresh Customer');
        $this->actingAs($this->staff)->get(route('leads.index', ['only' => ['not_quoted']]))
            ->assertSee('Fresh Customer')->assertDontSee('Quoted Customer');

        $this->actingAs($this->staff)->get(route('leads.index', ['layout' => 'board']))
            ->assertOk()->assertSee('Quoted Customer')->assertSee('Fresh Customer')->assertSee('Contacted');
    }

    public function test_export_gives_the_filtered_leads_as_csv(): void
    {
        Lead::create(['customer_name' => 'Export Me', 'phone' => '9000000021', 'status' => 'won']);
        Lead::create(['customer_name' => 'Leave Me Out', 'phone' => '9000000022', 'status' => 'lost']);

        $response = $this->actingAs($this->staff)->get(route('leads.export', ['status' => ['won']]));
        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Export Me', $csv);
        $this->assertStringNotContainsString('Leave Me Out', $csv);

        $this->actingAs($this->technician)->get(route('leads.export'))->assertForbidden();
    }

    public function test_staff_change_status_of_ticked_leads_but_only_admins_delete(): void
    {
        $a = Lead::create(['customer_name' => 'Tick One', 'phone' => '9000000031']);
        $b = Lead::create(['customer_name' => 'Tick Two', 'phone' => '9000000032']);

        $this->actingAs($this->staff)->post(route('leads.bulk'), ['action' => 'status', 'status' => 'contacted', 'ids' => [$a->id, $b->id]])
            ->assertRedirect()->assertSessionHas('status', '2 leads marked contacted.');
        $this->assertSame(['contacted', 'contacted'], Lead::whereIn('id', [$a->id, $b->id])->pluck('status')->all());

        $this->actingAs($this->staff)->post(route('leads.bulk'), ['action' => 'delete', 'ids' => [$a->id]])->assertForbidden();
        $this->assertDatabaseHas('leads', ['id' => $a->id]);

        $this->actingAs($this->technician)->post(route('leads.bulk'), ['action' => 'status', 'status' => 'won', 'ids' => [$a->id]])->assertForbidden();

        $this->actingAs($this->admin)->post(route('leads.bulk'), ['action' => 'delete', 'ids' => [$a->id]])
            ->assertSessionHas('status', '1 lead deleted.');
        $this->assertDatabaseMissing('leads', ['id' => $a->id]);
    }

    public function test_technicians_see_the_list_without_office_tools(): void
    {
        $lead = Lead::create(['customer_name' => 'Read Only Lead', 'phone' => '9000000041']);

        $this->actingAs($this->technician)->get(route('leads.index'))
            ->assertOk()
            ->assertSee('Read Only Lead')
            ->assertDontSee('Create Lead')
            ->assertDontSee(route('leads.edit', $lead), false)
            ->assertDontSee(route('leads.export'), false);

        $this->actingAs($this->staff)->get(route('leads.index'))
            ->assertSee('Create Lead')
            ->assertSee(route('leads.export'), false);
    }
}
