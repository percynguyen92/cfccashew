<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CuttingTest;
use App\Enums\CuttingTestType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CuttingTestFactory extends Factory
{
    protected $model = CuttingTest::class;

    public function definition(): array
    {
        $moisture = $this->faker->randomFloat(2, 8.5, 11.5);
        $sampleWeight = 1000;
        $nutCount = $this->faker->numberBetween(160, 230);
        $wRejectNut = $this->faker->numberBetween(80, 150);
        $wDefectiveNut = $this->faker->numberBetween(40, 120);
        $wDefectiveKernel = (int) round($wDefectiveNut / 3.3);
        $wGoodKernel = (int) round(($sampleWeight - $wRejectNut - $wDefectiveNut) / 3.3);
        $wSampleAfterCut = $this->faker->numberBetween(994, 997);
        $outturnRate = round((($wDefectiveKernel / 2 + $wGoodKernel) * 80) / 453.6, 2);

        return [
            'bill_id'              => 1,
            'container_id'         => null,
            'type' => $this->faker->randomElement(CuttingTestType::cases())->value,
            'moisture' => $moisture,
            'sample_weight' => $sampleWeight,
            'nut_count' => $nutCount,
            'w_reject_nut' => $wRejectNut,
            'w_defective_nut' => $wDefectiveNut,
            'w_defective_kernel' => $wDefectiveKernel,
            'w_good_kernel' => $wGoodKernel,
            'w_sample_after_cut' => $wSampleAfterCut,
            'outturn_rate' => $outturnRate
        ];
    }
}
