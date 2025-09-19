<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CuttingTest>
 */
class CuttingTestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->numberBetween(1, 4),
            'moisture' => $this->faker->randomFloat(2, 8.0, 12.0),
            'sample_weight' => 1000,
            'nut_count' => $this->faker->numberBetween(180, 220),
            'w_reject_nut' => $this->faker->numberBetween(50, 100),
            'w_defective_nut' => $this->faker->numberBetween(100, 150),
            'w_defective_kernel' => $this->faker->numberBetween(80, 120),
            'w_good_kernel' => $this->faker->numberBetween(650, 750),
            'w_sample_after_cut' => $this->faker->numberBetween(900, 950),
            'outturn_rate' => $this->faker->randomFloat(2, 45.00, 55.00),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
