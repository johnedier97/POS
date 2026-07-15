<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
            'key' => fake()->unique()->word(),
            'value' => fake()->sentence(),
        ];
    }

    public function businessName(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'business_name',
            'value' => fake()->company(),
        ]);
    }

    public function footerNote(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'footer_note',
            'value' => 'Gracias por su compra',
        ]);
    }
}
