<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CashRegister;
use App\Models\CashRegisterSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashRegisterSession>
 */
class CashRegisterSessionFactory extends Factory
{
    protected $model = CashRegisterSession::class;

    public function definition(): array
    {
        return [
            'cash_register_id' => CashRegister::factory(),
            'user_id' => User::factory(),
            'opened_at' => now(),
            'closed_at' => null,
            'initial_balance' => fake()->randomFloat(2, 0, 500),
            'final_calculated_balance' => 0,
            'final_reported_balance' => 0,
            'status' => 'open',
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
            'closed_at' => null,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'closed_at' => now(),
            'final_calculated_balance' => $attributes['initial_balance'] + fake()->randomFloat(2, 10, 500),
            'final_reported_balance' => $attributes['initial_balance'] + fake()->randomFloat(2, 10, 500),
        ]);
    }
}
