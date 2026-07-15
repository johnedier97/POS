<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Branch;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_order_belongs_to_supplier(): void
    {
        $order = PurchaseOrder::factory()->create();
        $this->assertInstanceOf(Supplier::class, $order->supplier);
    }

    public function test_purchase_order_belongs_to_branch(): void
    {
        $order = PurchaseOrder::factory()->create();
        $this->assertInstanceOf(Branch::class, $order->branch);
    }

    public function test_purchase_order_has_many_details(): void
    {
        $order = PurchaseOrder::factory()->create();
        PurchaseOrderDetail::factory()->count(3)->create(['purchase_order_id' => $order->id]);

        $this->assertCount(3, $order->details);
        $this->assertInstanceOf(PurchaseOrderDetail::class, $order->details->first());
    }

    public function test_pending_state(): void
    {
        $order = PurchaseOrder::factory()->pending()->create();
        $this->assertEquals('pending', $order->status);
    }

    public function test_received_state(): void
    {
        $order = PurchaseOrder::factory()->received()->create();
        $this->assertEquals('received', $order->status);
    }
}
