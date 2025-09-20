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
        $quantityOfBags = $this->faker->numberBetween(50, 200);
        $juteWeight = $this->faker->randomFloat(2, 1.00, 2.00);
        $totalWeight = $this->faker->numberBetween(20000, 30000);
        $truckWeight = $this->faker->numberBetween(8000, 12000);
        $containerWeight = $this->faker->numberBetween(2000, 3000);
        $dunnageWeight = $this->faker->numberBetween(100, 300);
        
        // Calculate weights using the correct formulas
        $grossWeight = $totalWeight - $truckWeight - $containerWeight;
        $tareWeight = $quantityOfBags * $juteWeight;
        $netWeight = $grossWeight - $dunnageWeight - $tareWeight;
        
        return [
            'truck' => 'TRK-' . str_pad($this->faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'container_number' => 'CONT' . str_pad($this->faker->numberBetween(1000000, 9999999), 7, '0', STR_PAD_LEFT),
            'quantity_of_bags' => $quantityOfBags,
            'w_jute_bag' => $juteWeight,
            'w_total' => $totalWeight,
            'w_truck' => $truckWeight,
            'w_container' => $containerWeight,
            'w_gross' => max(0, $grossWeight), // Ensure non-negative
            'w_dunnage_dribag' => $dunnageWeight,
            'w_tare' => $tareWeight,
            'w_net' => max(0, $netWeight), // Ensure non-negative
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
