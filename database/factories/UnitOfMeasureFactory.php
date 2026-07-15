<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\UnitOfMeasure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnitOfMeasure>
 */
class UnitOfMeasureFactory extends Factory
{
    protected $model = UnitOfMeasure::class;

    public function definition(): array
    {
        $units = [
            ['name' => 'Unidad', 'abbreviation' => 'u'],
            ['name' => 'Kilogramo', 'abbreviation' => 'kg'],
            ['name' => 'Litro', 'abbreviation' => 'L'],
            ['name' => 'Gramo', 'abbreviation' => 'g'],
            ['name' => 'Caja', 'abbreviation' => 'caja'],
            ['name' => 'Paquete', 'abbreviation' => 'paq'],
            ['name' => 'Botella', 'abbreviation' => 'bot'],
        ];
        $unit = fake()->randomElement($units);

        return [
            'name' => $unit['name'],
            'abbreviation' => $unit['abbreviation'],
        ];
    }
}
