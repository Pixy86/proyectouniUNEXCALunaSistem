<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'placa' => $this->faker->unique()->bothify('???-####'),
            'marca' => $this->faker->randomElement(['Toyota', 'Chevrolet', 'Ford', 'Honda', 'Nissan']),
            'modelo' => $this->faker->word(),
            'color' => $this->faker->safeColorName(),
            'tipo_vehiculo' => $this->faker->randomElement(['Sedan', 'SUV', 'Pickup', 'Moto']),
            'estado' => true,
        ];
    }
}
