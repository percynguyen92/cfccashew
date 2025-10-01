<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bill>
 */
class BillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bill_number' => 'BL-' . date('Y') . '-' . str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'seller' => $this->faker->company(),
            'buyer' => $this->faker->company(),
            'w_dunnage_dribag' => $this->faker->numberBetween(50, 250),
            'w_jute_bag' => $this->faker->randomFloat(2, 1.00, 2.50),
            'net_on_bl' => $this->faker->optional()->numberBetween(16000, 25000),
            'quantity_of_bags_on_bl' => $this->faker->optional()->numberBetween(100, 400),
            'origin' => $this->faker->optional()->country(),
            'inspection_start_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'inspection_end_date' => $this->faker->dateTimeBetween('now', '+1 week'),
            'inspection_location' => $this->faker->optional()->city(),
            'sampling_ratio' => $this->faker->randomFloat(2, 5, 20),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
