<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Quotation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Deals & Quotations list: views, filters, deals board, CSV export and the actions on
 * ticked rows (mark drafts sent for office users, delete drafts for admins only).
 */
class QuotationListTest extends TestCase
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

    private function quotation(string $no, string $customer, string $status, float $total, ?Carbon $validUntil = null): Quotation
    {
        $lead = Lead::create(['customer_name' => $customer, 'phone' => '98' . random_int(10000000, 99999999)]);

        return Quotation::create([
            'lead_id' => $lead->id, 'quotation_no' => $no, 'quotation_date' => Carbon::today(),
            'valid_until' => $validUntil ?? Carbon::today()->addDays(15), 'subtotal' => $total, 'discount' => 0,
            'tax_percent' => 0, 'tax_amount' => 0, 'total' => $total, 'status' => $status,
        ]);
    }

    public function test_views_and_filters_narrow_the_list(): void
    {
        $this->quotation('QT-OPEN-1', 'Arun Foods', 'sent', 50000, Carbon::today()->addDays(3));
        $this->quotation('QT-WON-1', 'Bharat Mills', 'accepted', 250000);
        $this->quotation('QT-LOST-1', 'Chennai Clinic', 'rejected', 80000);
        $this->quotation('QT-LATE-1', 'Delta Stores', 'draft', 12000, Carbon::today()->subDays(2));

        $this->actingAs($this->staff)->get(route('quotations.index', ['view' => 'won']))
            ->assertOk()->assertSee('Won Deals')->assertSee('QT-WON-1')->assertDontSee('QT-OPEN-1');

        $this->actingAs($this->staff)->get(route('quotations.index', ['view' => 'lost']))
            ->assertSee('QT-LOST-1')->assertDontSee('QT-WON-1');

        $this->actingAs($this->staff)->get(route('quotations.index', ['only' => ['expiring']]))
            ->assertSee('QT-OPEN-1')->assertDontSee('QT-LATE-1');

        $this->actingAs($this->staff)->get(route('quotations.index', ['only' => ['overdue']]))
            ->assertSee('QT-LATE-1')->assertDontSee('QT-OPEN-1');

        $this->actingAs($this->staff)->get(route('quotations.index', ['min_total' => 100000]))
            ->assertSee('QT-WON-1')->assertDontSee('QT-LOST-1');

        $this->actingAs($this->staff)->get(route('quotations.index', ['letter' => 'C']))
            ->assertSee('QT-LOST-1')->assertDontSee('QT-OPEN-1');

        $this->actingAs($this->staff)->get(route('quotations.index', ['search' => 'delta']))
            ->assertSee('QT-LATE-1')->assertDontSee('QT-WON-1');

        $this->actingAs($this->technician)->get(route('quotations.index', ['layout' => 'board']))
            ->assertOk()->assertSee('QT-WON-1')->assertSee('₹2,50,000')->assertDontSee('Create Quotation');
    }

    public function test_export_gives_the_filtered_quotations_as_csv(): void
    {
        $this->quotation('QT-CSV-IN', 'Export Customer', 'accepted', 1000);
        $this->quotation('QT-CSV-OUT', 'Other Customer', 'draft', 1000);

        $csv = $this->actingAs($this->staff)->get(route('quotations.export', ['status' => ['accepted']]))
            ->assertOk()->streamedContent();
        $this->assertStringContainsString('QT-CSV-IN', $csv);
        $this->assertStringNotContainsString('QT-CSV-OUT', $csv);

        $this->actingAs($this->technician)->get(route('quotations.export'))->assertForbidden();
    }

    public function test_ticked_drafts_are_marked_sent_and_only_admins_delete_drafts(): void
    {
        $draft = $this->quotation('QT-BULK-D', 'Draft Customer', 'draft', 1000);
        $accepted = $this->quotation('QT-BULK-A', 'Accepted Customer', 'accepted', 1000);

        $this->actingAs($this->staff)->post(route('quotations.bulk'), ['action' => 'sent', 'ids' => [$draft->id, $accepted->id]])
            ->assertSessionHas('status', '1 quotation marked sent. 1 skipped (only drafts can be marked sent).');
        $this->assertSame('sent', $draft->fresh()->status);
        $this->assertSame('accepted', $accepted->fresh()->status);
        $this->assertSame(1, $draft->statusHistories()->where('to_status', 'sent')->count());

        $other = $this->quotation('QT-BULK-X', 'Delete Customer', 'draft', 1000);
        $this->actingAs($this->staff)->post(route('quotations.bulk'), ['action' => 'delete', 'ids' => [$other->id]])->assertForbidden();
        $this->actingAs($this->technician)->post(route('quotations.bulk'), ['action' => 'sent', 'ids' => [$other->id]])->assertForbidden();

        $this->actingAs($this->admin)->post(route('quotations.bulk'), ['action' => 'delete', 'ids' => [$other->id, $accepted->id]])
            ->assertSessionHas('status', '1 quotation deleted. 1 skipped (only drafts can be deleted).');
        $this->assertDatabaseMissing('quotations', ['id' => $other->id]);
        $this->assertDatabaseHas('quotations', ['id' => $accepted->id]);
    }
}
