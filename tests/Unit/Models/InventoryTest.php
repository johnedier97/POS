<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_belongs_to_product(): void
    {
        $inventory = Inventory::factory()->create();
        $this->assertInstanceOf(Product::class, $inventory->product);
    }

    public function test_inventory_belongs_to_branch(): void
    {
        $inventory = Inventory::factory()->create();
        $this->assertInstanceOf(Branch::class, $inventory->branch);
    }

    public function test_low_stock_state(): void
    {
        $inventory = Inventory::factory()->lowStock()->create();
        $this->assertLessThan(5, $inventory->stock);
    }

    public function test_zero_stock_state(): void
    {
        $inventory = Inventory::factory()->zeroStock()->create();
        $this->assertEquals(0, $inventory->stock);
    }
}
