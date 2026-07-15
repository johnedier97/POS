<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\ProductComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_belongs_to_child_product(): void
    {
        $component = ProductComponent::factory()->create();
        $this->assertInstanceOf(Product::class, $component->childProduct);
    }

    public function test_component_belongs_to_parent_product(): void
    {
        $parent = Product::factory()->composite()->create();
        $child = Product::factory()->simple()->create();
        $component = ProductComponent::factory()->create([
            'parent_product_id' => $parent->id,
            'child_product_id' => $child->id,
        ]);

        $this->assertEquals($parent->id, $component->parent_product_id);
        $this->assertEquals($child->id, $component->child_product_id);
    }
}
