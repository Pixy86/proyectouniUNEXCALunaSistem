<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Service;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombreProducto' => $this->faker->words(3, true),
            'descripcion' => $this->faker->sentence(),
            'sku' => $this->faker->unique()->bothify('PROD-####-????'),
            'stockActual' => $this->faker->numberBetween(10, 1000),
            'estado' => true,
        ];
    }
}
