<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLeadDuplicateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_and_see_duplicate_phone_badges(): void
    {
        $admin = User::create([
            'username' => 'duplicate-admin',
            'password' => 'password',
            'nama_lengkap' => 'Duplicate Admin',
            'role' => 'admin',
            'is_active' => true,
        ]);

        Lead::create([
            'nama_customer' => 'Duplicate One',
            'no_hp' => '08123456789',
        ]);
        Lead::create([
            'nama_customer' => 'Duplicate Two',
            'no_hp' => '+628123456789',
        ]);
        Lead::create([
            'nama_customer' => 'Unique Lead',
            'no_hp' => '081299999999',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.leads.index', [
            'duplicates' => '1',
        ]));

        $response->assertOk();
        $response->assertSee('Duplicate One');
        $response->assertSee('Duplicate Two');
        $response->assertDontSee('Unique Lead');
        $response->assertSee('Duplikat 2');
    }
}
