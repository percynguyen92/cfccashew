<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Bill::factory()
            ->count(count: 10)
            ->create()
            ->each(callback: function (Bill $bill) {
                $containers = Container::factory()
                    ->count(count: rand(min: 4, max: 7))
                    ->for($bill)
                    ->create();

                foreach ($containers as $container) {
                    CuttingTest::factory()
                        ->for($container)
                        ->state([
                            'bill_id' => $bill->id,
                            'type' => 4
                        ])
                        ->create();
                }

                CuttingTest::factory()
                    ->count(3)
                    ->for($bill)
                    ->sequence(
                        ['type' => 1],
                        ['type' => 2],
                        ['type' => 3]
                    )
                    ->create();
            });
    }
}
