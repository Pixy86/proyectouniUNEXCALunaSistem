<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => \App\Models\Customer::factory(),
            // Reutilizamos métodos de pago existentes para evitar errores de duplicidad en 'nombre'
            'payment_method_id' => \App\Models\PaymentMethod::inRandomOrder()->first()?->id ?? \App\Models\PaymentMethod::factory(),
            'total' => $this->faker->numberBetween(50, 500),
            'paid_amount' => function (array $attributes) {
                return $attributes['total'];
            },
            'discount' => 0,
        ];
    }
}
