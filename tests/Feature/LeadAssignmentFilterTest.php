<?php

namespace Tests\Feature\Admin;

use App\Models\Lead;
use App\Models\LeadHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadAssignmentFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $marketing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'username' => 'admin-test',
            'password' => 'password',
            'nama_lengkap' => 'Admin Test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->marketing = User::create([
            'username' => 'marketing-test',
            'password' => 'password',
            'nama_lengkap' => 'Marketing Test',
            'role' => 'marketing',
            'is_active' => true,
        ]);
    }

    public function test_index_filters_unassigned_leads_by_status_source_and_search(): void
    {
        $matching = Lead::create([
            'nama_customer' => 'Budi Legacy',
            'no_hp' => '628111111111',
            'status_prospek' => 'New',
            'sumber_lead' => 'Spreadsheet Legacy 2024-2025',
        ]);

        Lead::create([
            'nama_customer' => 'Budi Meta',
            'no_hp' => '628122222222',
            'status_prospek' => 'New',
            'sumber_lead' => 'Meta Ads',
        ]);

        Lead::create([
            'nama_customer' => 'Siti Legacy',
            'no_hp' => '628133333333',
            'status_prospek' => 'Cold',
            'sumber_lead' => 'Spreadsheet Legacy 2024-2025',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.assignment.index', [
            'status_filter' => 'New',
            'source_filter' => 'Spreadsheet Legacy 2024-2025',
            'search' => 'Budi',
        ]));

        $response->assertOk();
        $response->assertViewHas('unassignedLeads', function ($leads) use ($matching) {
            return $leads->total() === 1
                && $leads->first()->is($matching);
        });
    }

    public function test_select_all_filtered_assigns_only_matching_unassigned_leads_and_records_history(): void
    {
        $matchingOne = Lead::create([
            'nama_customer' => 'Lead New Satu',
            'no_hp' => '628211111111',
            'status_prospek' => 'New',
            'sumber_lead' => 'Spreadsheet Legacy 2024-2025',
        ]);

        $matchingTwo = Lead::create([
            'nama_customer' => 'Lead New Dua',
            'no_hp' => '628222222222',
            'status_prospek' => 'New',
            'sumber_lead' => 'Spreadsheet Legacy 2024-2025',
        ]);

        $differentStatus = Lead::create([
            'nama_customer' => 'Lead Cold',
            'no_hp' => '628233333333',
            'status_prospek' => 'Cold',
            'sumber_lead' => 'Spreadsheet Legacy 2024-2025',
        ]);

        $alreadyAssigned = Lead::create([
            'nama_customer' => 'Lead Sudah Dibagi',
            'no_hp' => '628244444444',
            'status_prospek' => 'New',
            'sumber_lead' => 'Spreadsheet Legacy 2024-2025',
            'assigned_to' => $this->marketing->id,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.assignment.bulk'), [
            'select_all_filtered' => '1',
            'status_filter' => 'New',
            'source_filter' => 'Spreadsheet Legacy 2024-2025',
            'search' => '',
            'marketing_id' => $this->marketing->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', '2 lead berhasil di-assign.');

        $this->assertSame($this->marketing->id, $matchingOne->fresh()->assigned_to);
        $this->assertSame($this->marketing->id, $matchingTwo->fresh()->assigned_to);
        $this->assertNull($differentStatus->fresh()->assigned_to);
        $this->assertSame($this->marketing->id, $alreadyAssigned->fresh()->assigned_to);

        $this->assertSame(2, LeadHistory::where('action', 'assigned')->count());
    }

    public function test_index_filters_assigned_leads_by_status_and_search(): void
    {
        $matching = Lead::create([
            'nama_customer' => 'Andi Hot',
            'no_hp' => '628311111111',
            'status_prospek' => 'Hot',
            'assigned_to' => $this->marketing->id,
            'assigned_at' => now(),
        ]);

        Lead::create([
            'nama_customer' => 'Andi Cold',
            'no_hp' => '628322222222',
            'status_prospek' => 'Cold',
            'assigned_to' => $this->marketing->id,
            'assigned_at' => now(),
        ]);

        Lead::create([
            'nama_customer' => 'Rina Hot',
            'no_hp' => '628333333333',
            'status_prospek' => 'Hot',
            'assigned_to' => $this->marketing->id,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.assignment.index', [
            'assigned_status' => 'Hot',
            'assigned_search' => 'Andi',
        ]));

        $response->assertOk();
        $response->assertViewHas('assignedLeads', function ($leads) use ($matching) {
            return $leads->total() === 1
                && $leads->first()->is($matching);
        });
    }

    public function test_single_assign_records_history_and_transfer_bulk_uses_service(): void
    {
        $otherMarketing = User::create([
            'username' => 'marketing-two',
            'password' => 'password',
            'nama_lengkap' => 'Marketing Two',
            'role' => 'marketing',
            'is_active' => true,
        ]);

        $lead = Lead::create([
            'nama_customer' => 'Lead Single',
            'no_hp' => '628344444444',
            'status_prospek' => 'New',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.assignment.single', $lead), ['marketing_id' => $this->marketing->id])
            ->assertRedirect();

        $this->assertSame($this->marketing->id, $lead->fresh()->assigned_to);
        $this->assertSame(1, LeadHistory::where('lead_id', $lead->id)->where('action', 'assigned')->count());

        $this->actingAs($this->admin)
            ->post(route('admin.assignment.transfer'), [
                'lead_ids' => [$lead->id],
                'marketing_id' => $otherMarketing->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', '1 lead berhasil ditransfer.');

        $this->assertSame($otherMarketing->id, $lead->fresh()->assigned_to);
        $this->assertSame(1, LeadHistory::where('lead_id', $lead->id)->where('action', 'transferred')->count());
    }
}
