<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\CashRegisterSession;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_dashboard_loads_successfully(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas(['todaySales', 'inventoryAlerts', 'activeRegisters', 'days', 'salesData']);
    }

    public function test_chart_includes_days_with_zero_sales(): void
    {
        Sale::factory()->create([
            'type' => 'sale',
            'total' => 100.00,
            'created_at' => Carbon::today(),
        ]);

        Cache::flush();

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $salesData = $response->viewData('salesData');
        $days = $response->viewData('days');

        $this->assertCount(7, $salesData);
        $this->assertCount(7, $days);
        $this->assertContains(0.0, $salesData);
    }

    public function test_dashboard_caches_stats(): void
    {
        Cache::flush();

        $this->actingAs($this->user)->get('/dashboard');
        $this->assertTrue(Cache::has('dashboard:stats'));

        $cached = Cache::get('dashboard:stats');
        $this->assertArrayHasKey('todaySales', $cached);
        $this->assertArrayHasKey('inventoryAlerts', $cached);
        $this->assertArrayHasKey('activeRegisters', $cached);
    }

    public function test_inventory_alerts_count_low_stock(): void
    {
        Inventory::factory()->count(3)->lowStock()->create();
        Inventory::factory()->count(5)->create(['stock' => 100]);

        Cache::flush();

        $response = $this->actingAs($this->user)->get('/dashboard');
        $alerts = $response->viewData('inventoryAlerts');

        $this->assertEquals(3, $alerts);
    }
}
