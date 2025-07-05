<?php

namespace Database\Factories;

use App\Models\Bill;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContainerFactory extends Factory
{
    public function definition(): array
    {
        $quantity_of_bags = $this->faker->numberBetween(320, 370);
        $w_jute_bag = 1;
        $w_tare = $quantity_of_bags * $w_jute_bag;
        $w_total = $this->faker->numberBetween(4300, 4850)*10;
        $w_truck = $this->faker->numberBetween(1500, 1800)*10;
        $w_container = $this->faker->randomElement([3600, 3700, 3750, 3800]);
        $w_gross = $w_total - $w_truck - $w_container;
        $w_dunnage_dribag = $this->faker->numberBetween(40, 90);
        $w_net = $w_gross - $w_dunnage_dribag - $w_tare;

        return [
            'bill_id'           => 1,
            'truck'             => $this->faker->regexify('\d{2}[A-K]{1}\d{6}'),
            'container_number'  => $this->faker->regexify('[A-Z]{3}[UJZ]\d{7}'),
            'quantity_of_bags'  => $quantity_of_bags,
            'w_jute_bag'        => $w_jute_bag,
            'w_total'           => $w_total,
            'w_truck'           => $w_truck,
            'w_container'       => $w_container,
            'w_gross'           => $w_gross,
            'w_dunnage_dribag'  => $w_dunnage_dribag,
            'w_tare'            => $w_tare,
            'w_net'             => round($w_net, 2),
        ];
    }
}
