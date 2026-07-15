<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\CashRegisterSession;
use App\Models\Inventory;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class PosControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private CashRegisterSession $session;
    private PaymentMethod $cashMethod;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();

        $this->user = User::factory()->create();
        $this->session = CashRegisterSession::factory()->open()->create(['user_id' => $this->user->id]);
        $this->cashMethod = PaymentMethod::factory()->create(['name' => 'Efectivo', 'is_active' => true]);
    }

    public function test_simple_sale_creates_sale_and_reduces_inventory(): void
    {
        $product = Product::factory()->simple()->create(['price' => 50.00, 'cost' => 30.00]);
        Inventory::factory()->create([
            'product_id' => $product->id,
            'branch_id' => $this->session->cashRegister->branch_id,
            'stock' => 20,
        ]);

        $response = $this->actingAs($this->user)->postJson('/pos', [
            'sale' => ['type' => 'sale', 'total' => 100.00, 'customer_id' => null],
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'price' => 50.00, 'cost' => 30.00],
            ],
            'payments' => [
                ['payment_method_id' => $this->cashMethod->id, 'amount' => 100.00],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('sales', ['type' => 'sale', 'total' => 100.00]);
        $this->assertDatabaseHas('sale_details', ['product_id' => $product->id, 'quantity' => 2]);
        $this->assertDatabaseHas('payments', ['amount' => 100.00]);

        $stock = Inventory::where('product_id', $product->id)
            ->where('branch_id', $this->session->cashRegister->branch_id)
            ->value('stock');
        $this->assertEquals(18, (float) $stock);
    }

    public function test_sale_rejected_without_open_session(): void
    {
        $userWithoutSession = User::factory()->create();
        $product = Product::factory()->simple()->create();

        $response = $this->actingAs($userWithoutSession)->postJson('/pos', [
            'sale' => ['type' => 'sale', 'total' => 50.00],
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'price' => 50.00, 'cost' => 30.00],
            ],
        ]);

        $response->assertStatus(403);
    }

    public function test_composite_product_sale_discounts_ingredients(): void
    {
        $ingredientA = Product::factory()->simple()->create(['price' => 5.00, 'cost' => 2.00]);
        $ingredientB = Product::factory()->simple()->create(['price' => 8.00, 'cost' => 4.00]);

        Inventory::factory()->create([
            'product_id' => $ingredientA->id,
            'branch_id' => $this->session->cashRegister->branch_id,
            'stock' => 50,
        ]);
        Inventory::factory()->create([
            'product_id' => $ingredientB->id,
            'branch_id' => $this->session->cashRegister->branch_id,
            'stock' => 30,
        ]);

        $composite = Product::factory()->composite()->create(['price' => 30.00, 'cost' => 10.00]);
        ProductComponent::factory()->create([
            'parent_product_id' => $composite->id,
            'child_product_id' => $ingredientA->id,
            'quantity' => 3,
        ]);
        ProductComponent::factory()->create([
            'parent_product_id' => $composite->id,
            'child_product_id' => $ingredientB->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user)->postJson('/pos', [
            'sale' => ['type' => 'sale', 'total' => 60.00],
            'items' => [
                ['product_id' => $composite->id, 'quantity' => 2, 'price' => 30.00, 'cost' => 10.00],
            ],
            'payments' => [
                ['payment_method_id' => $this->cashMethod->id, 'amount' => 60.00],
            ],
        ]);

        $response->assertStatus(200);

        $stockA = Inventory::where('product_id', $ingredientA->id)
            ->where('branch_id', $this->session->cashRegister->branch_id)
            ->value('stock');
        $stockB = Inventory::where('product_id', $ingredientB->id)
            ->where('branch_id', $this->session->cashRegister->branch_id)
            ->value('stock');

        $this->assertEquals(44, (float) $stockA);
        $this->assertEquals(28, (float) $stockB);
    }

    public function test_sale_with_split_payments(): void
    {
        $cardMethod = PaymentMethod::factory()->create(['name' => 'Tarjeta de Crédito', 'is_active' => true]);
        $product = Product::factory()->simple()->create(['price' => 100.00, 'cost' => 60.00]);
        Inventory::factory()->create([
            'product_id' => $product->id,
            'branch_id' => $this->session->cashRegister->branch_id,
            'stock' => 10,
        ]);

        $response = $this->actingAs($this->user)->postJson('/pos', [
            'sale' => ['type' => 'sale', 'total' => 100.00],
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'price' => 100.00, 'cost' => 60.00],
            ],
            'payments' => [
                ['payment_method_id' => $this->cashMethod->id, 'amount' => 60.00],
                ['payment_method_id' => $cardMethod->id, 'amount' => 40.00],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('payments', ['amount' => 60.00]);
        $this->assertDatabaseHas('payments', ['amount' => 40.00]);
    }

    public function test_waste_sale_reduces_inventory_no_payments(): void
    {
        $product = Product::factory()->simple()->create(['price' => 50.00, 'cost' => 30.00]);
        Inventory::factory()->create([
            'product_id' => $product->id,
            'branch_id' => $this->session->cashRegister->branch_id,
            'stock' => 10,
        ]);

        $response = $this->actingAs($this->user)->postJson('/pos', [
            'sale' => ['type' => 'waste', 'total' => 0],
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3, 'price' => 0, 'cost' => 30.00],
            ],
            'payments' => [],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('sales', ['type' => 'waste']);

        $stock = Inventory::where('product_id', $product->id)->value('stock');
        $this->assertEquals(7, (float) $stock);
    }

    public function test_sale_validation_requires_items(): void
    {
        $response = $this->actingAs($this->user)->postJson('/pos', [
            'sale' => ['type' => 'sale', 'total' => 0],
            'items' => [],
        ]);

        $response->assertStatus(422);
    }
}
