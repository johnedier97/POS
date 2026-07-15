<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'sale_id' => Sale::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'amount' => fake()->randomFloat(2, 10, 500),
        ];
    }
}
