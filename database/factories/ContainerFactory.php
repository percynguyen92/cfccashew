<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\Container;
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
        $totalWeight = $this->faker->numberBetween(20000, 30000);
        $truckWeight = $this->faker->numberBetween(8000, 12000);
        $containerWeight = $this->faker->numberBetween(2000, 3000);
        
        // Calculate weights using the correct formulas
        $grossWeight = $totalWeight - $truckWeight - $containerWeight;
        
        return [
            'truck' => 'TRK-' . str_pad($this->faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'container_number' => 'CONT' . str_pad($this->faker->numberBetween(1000000, 9999999), 7, '0', STR_PAD_LEFT),
            'quantity_of_bags' => $quantityOfBags,
            'w_total' => $totalWeight,
            'w_truck' => $truckWeight,
            'w_container' => $containerWeight,
            'w_gross' => max(0, $grossWeight), // Ensure non-negative
            'w_tare' => null,
            'w_net' => null,
            'container_condition' => 'Nguyên vẹn',
            'seal_condition' => 'Nguyên vẹn',
            'note' => $this->faker->optional()->sentence(),
        ];
    }

    public function withBill(?Bill $bill = null): static
    {
        return $this->afterCreating(function (Container $container) use ($bill) {
            $targetBill = $bill ?? Bill::factory()->create();

            $container->bills()->syncWithoutDetaching([$targetBill->getKey()]);

            $tare = null;
            if (!is_null($container->quantity_of_bags) && $targetBill->w_jute_bag !== null) {
                $tare = $container->quantity_of_bags * (float) $targetBill->w_jute_bag;
            }

            $net = null;
            if (!is_null($container->w_gross) && $tare !== null && $targetBill->w_dunnage_dribag !== null) {
                $net = max(0, $container->w_gross - (int) $targetBill->w_dunnage_dribag - $tare);
            }

            $container->update([
                'w_tare' => $tare,
                'w_net' => $net,
            ]);
        });
    }
}
