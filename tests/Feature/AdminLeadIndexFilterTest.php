<?php

namespace Tests\Feature\Admin;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLeadIndexFilterTest extends TestCase
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

    public function test_index_filters_by_status_source_and_search(): void
    {
        $matching = Lead::create([
            'nama_customer' => 'Budi Meta',
            'no_hp' => '628111111111',
            'status_prospek' => 'Hot',
            'sumber_lead' => 'Meta Ads',
        ]);

        Lead::create([
            'nama_customer' => 'Budi Sheet',
            'no_hp' => '628122222222',
            'status_prospek' => 'Hot',
            'sumber_lead' => 'Google Sheets',
        ]);

        Lead::create([
            'nama_customer' => 'Siti Meta',
            'no_hp' => '628133333333',
            'status_prospek' => 'Cold',
            'sumber_lead' => 'Meta Ads',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.leads.index', [
            'status_filter' => 'Hot',
            'source_filter' => 'Meta Ads',
            'search' => 'Budi',
        ]));

        $response->assertOk();
        $response->assertViewHas('leads', function ($leads) use ($matching) {
            return $leads->total() === 1
                && $leads->first()->is($matching);
        });
    }

    public function test_index_filters_by_marketing_and_unassigned(): void
    {
        $assigned = Lead::create([
            'nama_customer' => 'Lead Terbagi',
            'no_hp' => '628211111111',
            'status_prospek' => 'New',
            'assigned_to' => $this->marketing->id,
            'assigned_at' => now(),
        ]);

        $unassigned = Lead::create([
            'nama_customer' => 'Lead Bebas',
            'no_hp' => '628222222222',
            'status_prospek' => 'New',
        ]);

        $byMarketing = $this->actingAs($this->admin)->get(route('admin.leads.index', [
            'marketing_filter' => (string) $this->marketing->id,
        ]));

        $byMarketing->assertOk();
        $byMarketing->assertViewHas('leads', function ($leads) use ($assigned) {
            return $leads->total() === 1
                && $leads->first()->is($assigned);
        });

        $onlyUnassigned = $this->actingAs($this->admin)->get(route('admin.leads.index', [
            'marketing_filter' => 'unassigned',
        ]));

        $onlyUnassigned->assertOk();
        $onlyUnassigned->assertViewHas('leads', function ($leads) use ($unassigned) {
            return $leads->total() === 1
                && $leads->first()->is($unassigned);
        });
    }

    public function test_index_filter_uses_full_status_enum_including_tidak_berminat(): void
    {
        $matching = Lead::create([
            'nama_customer' => 'Lead Menolak',
            'no_hp' => '628311111111',
            'status_prospek' => 'Tidak Berminat',
        ]);

        Lead::create([
            'nama_customer' => 'Lead Baru',
            'no_hp' => '628322222222',
            'status_prospek' => 'New',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.leads.index', [
            'status_filter' => 'Tidak Berminat',
        ]));

        $response->assertOk();
        $response->assertViewHas('leads', function ($leads) use ($matching) {
            return $leads->total() === 1
                && $leads->first()->is($matching);
        });
    }
}
