<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_has_many_purchase_orders(): void
    {
        $supplier = Supplier::factory()->create();
        PurchaseOrder::factory()->count(2)->create(['supplier_id' => $supplier->id]);

        $this->assertCount(2, $supplier->purchases);
        $this->assertInstanceOf(PurchaseOrder::class, $supplier->purchases->first());
    }
}
