<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\ProductComponent;
use App\Models\UnitOfMeasure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_belongs_to_unit_of_measure(): void
    {
        $product = Product::factory()->create();
        $this->assertInstanceOf(UnitOfMeasure::class, $product->unitOfMeasure);
    }

    public function test_product_has_many_components(): void
    {
        $product = Product::factory()->composite()->create();
        ProductComponent::factory()->count(3)->create(['parent_product_id' => $product->id]);

        $this->assertCount(3, $product->components);
        $this->assertInstanceOf(ProductComponent::class, $product->components->first());
    }

    public function test_composite_state_sets_is_composite_true(): void
    {
        $product = Product::factory()->composite()->create();
        $this->assertTrue($product->is_composite);
    }

    public function test_simple_state_sets_is_composite_false(): void
    {
        $product = Product::factory()->simple()->create();
        $this->assertFalse($product->is_composite);
    }

    public function test_fillable_attributes_are_mass_assignable(): void
    {
        $uom = UnitOfMeasure::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'cost' => 10.00,
            'price' => 25.00,
            'unit_of_measure_id' => $uom->id,
        ]);

        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals(10.00, $product->cost);
        $this->assertEquals(25.00, $product->price);
    }
}
