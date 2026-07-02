<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use App\Models\WhatsappTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $marketing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->marketing = User::create([
            'username' => 'marketing-workflow',
            'password' => 'password',
            'nama_lengkap' => 'Marketing Workflow',
            'role' => 'marketing',
            'is_active' => true,
        ]);

        WhatsappTemplate::create([
            'nama_template' => 'Promo Khusus',
            'isi_template' => 'Halo {nama_customer}, promo khusus.',
            'is_active' => true,
            'urutan' => 1,
        ]);
    }

    public function test_tasks_exclude_deals_and_support_due_and_status_filters(): void
    {
        $overdueWarm = $this->lead([
            'nama_customer' => 'Overdue Warm',
            'status_prospek' => 'Warm',
            'tgl_next_followup' => now()->subDay(),
        ]);

        $this->lead([
            'nama_customer' => 'Overdue Deal',
            'status_prospek' => 'Deal',
            'tgl_next_followup' => now()->subDay(),
        ]);

        $this->lead([
            'nama_customer' => 'Today Cold',
            'status_prospek' => 'Cold',
            'tgl_next_followup' => now(),
        ]);

        $response = $this->actingAs($this->marketing)->get(route('marketing.tasks.today', [
            'due' => 'overdue',
            'status' => 'Warm',
        ]));

        $response->assertOk();
        $response->assertViewHas('leads', function ($leads) use ($overdueWarm) {
            return $leads->count() === 1 && $leads->first()->is($overdueWarm);
        });
        $response->assertSee('Pilih template');
        $response->assertSee('Promo Khusus');
        $response->assertSee('Selesaikan Follow-up');
    }

    public function test_dashboard_limits_priority_followups_to_five(): void
    {
        foreach (range(1, 7) as $index) {
            $this->lead([
                'nama_customer' => "Priority {$index}",
                'tgl_next_followup' => now()->subDays($index),
            ]);
        }

        $response = $this->actingAs($this->marketing)->get(route('marketing.dashboard'));

        $response->assertOk();
        $response->assertViewHas('todaysFollowups', fn ($leads) => $leads->count() === 5);
    }

    public function test_leads_can_be_filtered_by_due_state_and_source(): void
    {
        $matching = $this->lead([
            'nama_customer' => 'Legacy Overdue',
            'sumber_lead' => 'Spreadsheet Legacy 2024-2025',
            'tgl_next_followup' => now()->subDay(),
        ]);

        $this->lead([
            'nama_customer' => 'Meta Overdue',
            'sumber_lead' => 'Meta Ads',
            'tgl_next_followup' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->marketing)->get(route('marketing.leads.index', [
            'due' => 'overdue',
            'source' => 'Spreadsheet Legacy 2024-2025',
        ]));

        $response->assertOk();
        $response->assertViewHas('leads', function ($leads) use ($matching) {
            return $leads->total() === 1 && $leads->first()->is($matching);
        });
    }

    public function test_completing_followup_keeps_the_automatic_three_day_cycle(): void
    {
        $lead = $this->lead([
            'nama_customer' => 'Automatic Cycle',
            'fase_followup' => 0,
            'tgl_next_followup' => now(),
        ]);

        $response = $this->actingAs($this->marketing)->postJson(
            route('marketing.tasks.complete', $lead),
            [
                'status_prospek' => 'Warm',
                'catatan' => 'Customer sudah merespons.',
            ]
        );

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertSame(1, $lead->fresh()->fase_followup);
        $this->assertSame(today()->addDays(3)->toDateString(), $lead->fresh()->tgl_next_followup->toDateString());
        $this->assertSame('Warm', $lead->fresh()->status_prospek->value);
    }

    public function test_phone_formats_are_normalized_and_duplicates_are_counted(): void
    {
        $first = $this->lead(['no_hp' => '0812-3456-789']);
        $second = $this->lead(['no_hp' => '+62 812 3456 789']);

        $this->assertSame('628123456789', $first->fresh()->normalized_phone);
        $this->assertSame('628123456789', $second->fresh()->normalized_phone);
        $this->assertSame(2, $first->fresh()->duplicate_count);
    }

    public function test_no_response_stays_active_but_not_interested_leaves_task_queue(): void
    {
        $noResponse = $this->lead([
            'nama_customer' => 'Belum Menjawab',
            'status_prospek' => 'Tidak Respon',
            'tgl_next_followup' => now(),
        ]);

        $this->lead([
            'nama_customer' => 'Tidak Berminat Lagi',
            'status_prospek' => 'Tidak Berminat',
            'tgl_next_followup' => now(),
        ]);

        $response = $this->actingAs($this->marketing)->get(route('marketing.tasks.today'));

        $response->assertOk();
        $response->assertViewHas('leads', function ($leads) use ($noResponse) {
            return $leads->getCollection()->contains(fn ($lead) => $lead->is($noResponse))
                && $leads->getCollection()->doesntContain(fn ($lead) => $lead->nama_customer === 'Tidak Berminat Lagi');
        });
        $response->assertSee('Tidak Respon');
        $response->assertSee('Minta Harga');
        $response->assertSee('Jadwal Survei');
        $response->assertSee('Tidak Berminat');
    }

    public function test_closing_lead_as_deal_stops_the_followup_cycle(): void
    {
        $lead = $this->lead([
            'nama_customer' => 'Closing Deal',
            'fase_followup' => 1,
            'tgl_next_followup' => now(),
        ]);

        $response = $this->actingAs($this->marketing)->postJson(
            route('marketing.tasks.complete', $lead),
            [
                'status_prospek' => 'Deal',
                'catatan' => 'Customer setuju.',
            ]
        );

        $response->assertOk()->assertJson(['success' => true]);
        $fresh = $lead->fresh();
        $this->assertSame(1, $fresh->fase_followup, 'Fase tidak boleh naik saat lead ditutup');
        $this->assertNull($fresh->tgl_next_followup, 'Lead yang deal tidak boleh punya jadwal follow-up');
        $this->assertSame('Deal', $fresh->status_prospek->value);
    }

    public function test_closing_lead_as_not_interested_stops_the_followup_cycle(): void
    {
        $lead = $this->lead([
            'fase_followup' => 0,
            'tgl_next_followup' => now(),
        ]);

        $this->actingAs($this->marketing)->postJson(
            route('marketing.tasks.complete', $lead),
            ['status_prospek' => 'Tidak Berminat']
        )->assertOk();

        $fresh = $lead->fresh();
        $this->assertSame(0, $fresh->fase_followup);
        $this->assertNull($fresh->tgl_next_followup);
    }

    public function test_marketing_store_rejects_invalid_status_and_accepts_source(): void
    {
        $this->actingAs($this->marketing)->post(route('marketing.leads.store'), [
            'nama_customer' => 'Lead Invalid',
            'no_hp' => '628100000000',
            'status_prospek' => 'BukanStatus',
        ])->assertSessionHasErrors('status_prospek');

        $this->actingAs($this->marketing)->post(route('marketing.leads.store'), [
            'nama_customer' => 'Lead Valid',
            'no_hp' => '628100000001',
            'status_prospek' => 'New',
            'sumber_lead' => 'Pameran',
            'keterangan' => 'Ketemu di pameran properti',
        ])->assertRedirect(route('marketing.leads.index'));

        $this->assertDatabaseHas('leads', [
            'nama_customer' => 'Lead Valid',
            'sumber_lead' => 'Pameran',
            'keterangan' => 'Ketemu di pameran properti',
            'assigned_to' => $this->marketing->id,
        ]);
    }

    public function test_leads_index_avoids_n_plus_one_on_transferred_badge(): void
    {
        $this->lead(['nama_customer' => 'A']);
        $this->lead(['nama_customer' => 'B']);
        $this->lead(['nama_customer' => 'C']);

        $response = $this->actingAs($this->marketing)->get(route('marketing.leads.index'));

        $response->assertOk();
        // withCount harus menyuntikkan atribut agregat, bukan memicu query per-baris
        $response->assertViewHas('leads', function ($leads) {
            return $leads->getCollection()->every(
                fn ($lead) => array_key_exists('transferred_histories_count', $lead->getAttributes())
            );
        });
    }

    private function lead(array $attributes = []): Lead
    {
        return Lead::create(array_merge([
            'nama_customer' => 'Customer',
            'no_hp' => '628123456789',
            'status_prospek' => 'New',
            'fase_followup' => 0,
            'tgl_next_followup' => now(),
            'assigned_to' => $this->marketing->id,
            'assigned_at' => now(),
            'sumber_lead' => 'Meta Ads',
        ], $attributes));
    }
}
