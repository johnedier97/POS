<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'image_path' => null,
            'cost' => fake()->randomFloat(2, 1, 100),
            'price' => fake()->randomFloat(2, 5, 200),
            'unit_of_measure_id' => UnitOfMeasure::factory(),
            'is_composite' => false,
        ];
    }

    public function simple(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_composite' => false,
        ]);
    }

    public function composite(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_composite' => true,
        ]);
    }
}
