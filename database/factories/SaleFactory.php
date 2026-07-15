<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CashRegisterSession;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        return [
            'session_id' => CashRegisterSession::factory(),
            'user_id' => User::factory(),
            'customer_id' => null,
            'type' => 'sale',
            'total' => 0,
            'is_electronic_invoiced' => false,
        ];
    }

    public function waste(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'waste',
        ]);
    }

    public function invoiced(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_electronic_invoiced' => true,
        ]);
    }
}
