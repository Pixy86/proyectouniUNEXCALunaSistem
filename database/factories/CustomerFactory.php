<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cedula_rif' => $this->faker->unique()->numerify('V-########'),
            'nombre' => $this->faker->firstName(),
            'apellido' => $this->faker->lastName(),
            'telefono' => $this->faker->phoneNumber(),
            'telefono_secundario' => $this->faker->optional()->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'direccion' => $this->faker->address(),
            'estado' => true,
        ];
    }
}
