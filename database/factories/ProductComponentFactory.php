<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductComponent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductComponent>
 */
class ProductComponentFactory extends Factory
{
    protected $model = ProductComponent::class;

    public function definition(): array
    {
        return [
            'parent_product_id' => Product::factory()->composite(),
            'child_product_id' => Product::factory()->simple(),
            'quantity' => fake()->randomFloat(2, 0.1, 10),
        ];
    }
}
