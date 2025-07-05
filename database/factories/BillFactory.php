<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bill;
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
}
