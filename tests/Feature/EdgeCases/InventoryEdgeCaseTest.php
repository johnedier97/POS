<?php

declare(strict_types=1);

namespace Tests\Feature\EdgeCases;

use App\Models\CashRegisterSession;
use App\Models\Inventory;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryEdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        CashRegisterSession::factory()->open()->create(['user_id' => $this->user->id]);
        PaymentMethod::factory()->create(['name' => 'Efectivo', 'is_active' => true]);
    }

    public function test_sale_allows_negative_stock(): void
    {
        $product = Product::factory()->simple()->create(['price' => 50.00, 'cost' => 30.00]);
        $session = $this->user->sessions()->first();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'branch_id' => $session->cashRegister->branch_id,
            'stock' => 2,
        ]);

        $response = $this->actingAs($this->user)->postJson('/pos', [
            'sale' => ['type' => 'sale', 'total' => 250.00],
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5, 'price' => 50.00, 'cost' => 30.00],
            ],
            'payments' => [
                ['payment_method_id' => PaymentMethod::first()->id, 'amount' => 250.00],
            ],
        ]);

        $response->assertStatus(200);

        $stock = Inventory::where('product_id', $product->id)->value('stock');
        $this->assertEquals(-3, (float) $stock);
    }

    public function test_three_level_composite_product(): void
    {
        $session = $this->user->sessions()->first();
        $branchId = $session->cashRegister->branch_id;

        $leaf = Product::factory()->simple()->create(['price' => 5.00, 'cost' => 2.00]);
        Inventory::factory()->create(['product_id' => $leaf->id, 'branch_id' => $branchId, 'stock' => 100]);

        $mid = Product::factory()->composite()->create(['price' => 20.00, 'cost' => 8.00]);
        ProductComponent::factory()->create([
            'parent_product_id' => $mid->id,
            'child_product_id' => $leaf->id,
            'quantity' => 2,
        ]);

        $top = Product::factory()->composite()->create(['price' => 50.00, 'cost' => 20.00]);
        ProductComponent::factory()->create([
            'parent_product_id' => $top->id,
            'child_product_id' => $mid->id,
            'quantity' => 3,
        ]);

        $response = $this->actingAs($this->user)->postJson('/pos', [
            'sale' => ['type' => 'sale', 'total' => 50.00],
            'items' => [
                ['product_id' => $top->id, 'quantity' => 1, 'price' => 50.00, 'cost' => 20.00],
            ],
            'payments' => [
                ['payment_method_id' => PaymentMethod::first()->id, 'amount' => 50.00],
            ],
        ]);

        $response->assertStatus(200);

        $stock = Inventory::where('product_id', $leaf->id)->value('stock');
        $this->assertEquals(94, (float) $stock);
    }

    public function test_decimal_precision_in_close_balance(): void
    {
        $session = CashRegisterSession::factory()->open()->create([
            'user_id' => $this->user->id,
            'initial_balance' => 100.33,
        ]);

        $response = $this->actingAs($this->user)->put("/shift/close/{$session->id}", [
            'final_reported_balance' => 100.34,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('cash_register_sessions', [
            'id' => $session->id,
            'status' => 'open',
        ]);
    }

    public function test_exact_decimal_balance_closes_successfully(): void
    {
        $session = CashRegisterSession::factory()->open()->create([
            'user_id' => $this->user->id,
            'initial_balance' => 100.33,
        ]);

        $response = $this->actingAs($this->user)->put("/shift/close/{$session->id}", [
            'final_reported_balance' => 100.33,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('cash_register_sessions', [
            'id' => $session->id,
            'status' => 'closed',
        ]);
    }

    public function test_payment_methods_fallback_when_empty(): void
    {
        \App\Models\PaymentMethod::query()->delete();

        $session = CashRegisterSession::factory()->open()->create(['user_id' => $this->user->id]);
        $product = Product::factory()->simple()->create(['price' => 50.00, 'cost' => 30.00]);
        Inventory::factory()->create([
            'product_id' => $product->id,
            'branch_id' => $session->cashRegister->branch_id,
            'stock' => 10,
        ]);

        $response = $this->actingAs($this->user)->get('/pos');
        $response->assertStatus(200);

        $this->assertDatabaseHas('payment_methods', ['name' => 'Efectivo']);
    }
}
