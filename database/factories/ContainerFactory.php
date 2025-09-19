<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Container>
 */
class ContainerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'truck' => 'TRK-' . str_pad($this->faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'container_number' => 'CONT' . str_pad($this->faker->numberBetween(1000000, 9999999), 7, '0', STR_PAD_LEFT),
            'quantity_of_bags' => $this->faker->numberBetween(50, 200),
            'w_jute_bag' => 1.00,
            'w_total' => $this->faker->numberBetween(15000, 25000),
            'w_truck' => $this->faker->numberBetween(8000, 12000),
            'w_container' => $this->faker->numberBetween(2000, 3000),
            'w_gross' => $this->faker->numberBetween(20000, 30000),
            'w_dunnage_dribag' => $this->faker->numberBetween(100, 300),
            'w_tare' => $this->faker->randomFloat(2, 2100, 3300),
            'w_net' => $this->faker->randomFloat(2, 17000, 27000),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
