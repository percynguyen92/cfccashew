<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use App\Enums\CuttingTestType;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillFactory extends Factory
{
    protected $model = Bill::class;

    public function definition(): array
    {
        return [
            'billNumber' => strtoupper($this->faker->bothify($this->faker->randomElement([
                'ONEYLOSF########',
                'GOSULOS########',
                'AFIC/SIN/######',
                'OBL-TEM#######',
                'OOLU##########',
                'ABJF##########',
                'DSLSIN######',
                'ONEYTEMF##########',
                'AFIC/DXB/######'
            ]))),
            'seller' => $this->faker->randomElement(['BK AGRO', 'ESSEM', 'INTERAGRO', 'SEDACO', 'PARGAN']),
            'buyer' => $this->faker->randomElement(['KIỀU LOAN', 'LAN CƯỜNG', 'HÀ ANH', 'PROSI', 'NGỌC KHANG', 'PHÚ THUỶ', 'QUANG BẢO', 'MINH LOAN', 'HOÀNG THIÊN'])
        ];
    }

    public function withFullRelations(): self
    {
        return $this
            ->has(
                Container::factory()
                ->count(4)
                ->has(
                    CuttingTest::factory()->state([
                        'type' => CuttingTestType::CONTAINER_CUT,
                    ]),
                    'cuttingTest'
                ),
                'containers'
            )
            ->has(
                CuttingTest::factory()
                    ->state(new \Illuminate\Support\Collection([
                        ['type' => CuttingTestType::FINAL_SAMPLE_FIRST_CUT],
                        ['type' => CuttingTestType::FINAL_SAMPLE_SECOND_CUT],
                        ['type' => CuttingTestType::FINAL_SAMPLE_THIRD_CUT],
                    ])->map(fn ($state) => CuttingTest::factory()->state($state))->all()),
                'cuttingTests'
            );
    }
}
