<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role_id' => \App\Models\Role::factory()->admin()]);
    }

    public function test_create_purchase_order(): void
    {
        $supplier = Supplier::factory()->create();
        $branch = Branch::factory()->create();
        $product = Product::factory()->simple()->create();

        $response = $this->actingAs($this->admin)->post('/purchases', [
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_cost' => 25.50,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('purchase_order_details', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);
    }

    public function test_receive_order_updates_inventory(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->simple()->create();

        $order = PurchaseOrder::factory()->pending()->create(['branch_id' => $branch->id]);
        PurchaseOrderDetail::factory()->create([
            'purchase_order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_cost' => 25.50,
            'total_cost' => 255.00,
        ]);

        $response = $this->actingAs($this->admin)->post("/purchases/{$order->id}/receive");
        $response->assertRedirect();

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $order->id,
            'status' => 'received',
        ]);

        $stock = Inventory::where('product_id', $product->id)
            ->where('branch_id', $branch->id)
            ->value('stock');
        $this->assertEquals(10, (float) $stock);
    }

    public function test_revert_order_reduces_inventory(): void
    {
        $branch = Branch::factory()->create();
        $product = Product::factory()->simple()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'stock' => 20,
        ]);

        $order = PurchaseOrder::factory()->received()->create(['branch_id' => $branch->id]);
        PurchaseOrderDetail::factory()->create([
            'purchase_order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_cost' => 10.00,
            'total_cost' => 50.00,
        ]);

        $response = $this->actingAs($this->admin)->post("/purchases/{$order->id}/revert");
        $response->assertRedirect();

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $order->id,
            'status' => 'pending',
        ]);

        $stock = Inventory::where('product_id', $product->id)
            ->where('branch_id', $branch->id)
            ->value('stock');
        $this->assertEquals(15, (float) $stock);
    }

    public function test_cannot_receive_already_received_order(): void
    {
        $order = PurchaseOrder::factory()->received()->create();
        $response = $this->actingAs($this->admin)->post("/purchases/{$order->id}/receive");
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_cannot_revert_non_received_order(): void
    {
        $order = PurchaseOrder::factory()->pending()->create();
        $response = $this->actingAs($this->admin)->post("/purchases/{$order->id}/revert");
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
