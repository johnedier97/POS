<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleDetail>
 */
class SaleDetailFactory extends Factory
{
    protected $model = SaleDetail::class;

    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 10);
        $price = fake()->randomFloat(2, 5, 200);

        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'price' => $price,
            'cost' => fake()->randomFloat(2, 1, max(2, (int) $price - 1)),
            'subtotal' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (\App\Models\SaleDetail $detail) {
            $detail->subtotal = $detail->quantity * $detail->price;
        });
    }
}
