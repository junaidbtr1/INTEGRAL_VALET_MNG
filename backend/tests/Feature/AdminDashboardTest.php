<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_provides_numeric_totals_for_a_fresh_database(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_super_admin' => true, 'is_active' => true]));

        $this->getJson('/api/v1/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('data.stats.total_tickets_today', 0)
            ->assertJsonPath('data.stats.total_tickets_month', 0)
            ->assertJsonPath('data.stats.total_revenue_today', 0)
            ->assertJsonPath('data.stats.total_revenue_month', 0);
    }

    public function test_ordinary_users_cannot_access_system_totals(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_super_admin' => false, 'is_active' => true]));
        $this->getJson('/api/v1/admin/dashboard')->assertForbidden();
    }
}
