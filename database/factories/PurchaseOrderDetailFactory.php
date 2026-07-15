<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrderDetail>
 */
class PurchaseOrderDetailFactory extends Factory
{
    protected $model = PurchaseOrderDetail::class;

    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 50);
        $unitCost = fake()->randomFloat(2, 1, 100);

        return [
            'purchase_order_id' => PurchaseOrder::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $quantity * $unitCost,
        ];
    }
}
