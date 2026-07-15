<?php

declare(strict_types=1);

namespace Tests\Feature\Regression;

use App\Models\CashRegisterSession;
use App\Models\Inventory;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OptimizationRegressionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_dashboard_group_by_returns_correct_data(): void
    {
        $today = Carbon::today();

        Sale::factory()->create([
            'type' => 'sale',
            'total' => 100.00,
            'created_at' => $today,
        ]);
        Sale::factory()->create([
            'type' => 'sale',
            'total' => 50.00,
            'created_at' => $today,
        ]);

        Cache::flush();
        $response = $this->actingAs($this->user)->get('/dashboard');

        $salesData = $response->viewData('salesData');
        $todayIndex = 6;
        $this->assertEquals(150.00, $salesData[$todayIndex]);
    }

    public function test_cache_refreshes_after_ttl(): void
    {
        Cache::flush();
        $this->actingAs($this->user)->get('/dashboard');
        $firstCached = Cache::get('dashboard:stats');

        Sale::factory()->create(['type' => 'sale', 'total' => 999.99, 'created_at' => Carbon::today()]);

        Cache::forget('dashboard:stats');
        $this->actingAs($this->user)->get('/dashboard');

        $secondCached = Cache::get('dashboard:stats');
        $this->assertNotEquals($firstCached['todaySales'], $secondCached['todaySales']);
    }

    public function test_user_role_eager_loading_no_n_plus_one(): void
    {
        $user = User::factory()->create();

        $userFromDb = User::find($user->id);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $role = $userFromDb->role;

        $roleQueries = count(array_filter(DB::getQueryLog(), fn ($q) => str_contains($q['query'], 'roles')));

        $this->assertNotNull($role);
        $this->assertEquals(0, $roleQueries, 'role debe cargarse eager, sin queries separadas');
    }

    public function test_inventory_index_no_n_plus_one_for_unit_of_measure(): void
    {
        $products = Product::factory()->count(5)->create();
        foreach ($products as $product) {
            Inventory::factory()->create(['product_id' => $product->id]);
        }

        $user = User::factory()->create(['role_id' => \App\Models\Role::factory()->admin()]);

        DB::enableQueryLog();
        $this->actingAs($user)->get('/inventory');
        $queries = DB::getQueryLog();

        $uomQueries = array_filter($queries, fn ($q) => str_contains($q['query'], 'unit_of_measures'));
        $this->assertLessThanOrEqual(1, count($uomQueries), 'unitOfMeasure debe cargarse eager, máximo 1 query');
    }

    public function test_process_inventory_batch_updates_multiple_products(): void
    {
        $session = CashRegisterSession::factory()->open()->create(['user_id' => $this->user->id]);
        PaymentMethod::factory()->create(['name' => 'Efectivo', 'is_active' => true]);
        $branchId = $session->cashRegister->branch_id;

        $products = [];
        for ($i = 0; $i < 3; $i++) {
            $p = Product::factory()->simple()->create(['price' => 20.00, 'cost' => 10.00]);
            Inventory::factory()->create(['product_id' => $p->id, 'branch_id' => $branchId, 'stock' => 20]);
            $products[] = $p;
        }

        $items = array_map(fn ($p) => [
            'product_id' => $p->id,
            'quantity' => 2,
            'price' => 20.00,
            'cost' => 10.00,
        ], $products);

        $response = $this->actingAs($this->user)->postJson('/pos', [
            'sale' => ['type' => 'sale', 'total' => 120.00],
            'items' => $items,
            'payments' => [
                ['payment_method_id' => PaymentMethod::first()->id, 'amount' => 120.00],
            ],
        ]);

        $response->assertStatus(200);

        foreach ($products as $p) {
            $stock = Inventory::where('product_id', $p->id)->where('branch_id', $branchId)->value('stock');
            $this->assertEquals(18, (float) $stock, "Product {$p->id} stock should be 18");
        }
    }
}
