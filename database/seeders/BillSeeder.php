<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use Illuminate\Database\Seeder;

class BillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample bills
        $bills = [
            [
                'bill_number' => 'BL-2024-001',
                'seller' => 'Cashew Farms Ltd',
                'buyer' => 'Global Nuts Inc',
                'note' => 'First shipment of the season',
            ],
            [
                'bill_number' => 'BL-2024-002',
                'seller' => 'Premium Cashews Co',
                'buyer' => 'European Distributors',
                'note' => 'High quality grade A cashews',
            ],
            [
                'bill_number' => 'BL-2024-003',
                'seller' => 'Tropical Harvest',
                'buyer' => 'Asian Markets Ltd',
                'note' => null,
            ],
            [
                'bill_number' => null,
                'seller' => 'Local Farmers Coop',
                'buyer' => 'Regional Processor',
                'note' => 'Bulk purchase from local farmers',
            ],
            [
                'bill_number' => 'BL-2024-005',
                'seller' => 'Organic Cashew Farms',
                'buyer' => 'Health Food Distributors',
                'note' => 'Certified organic cashews',
            ],
        ];

        foreach ($bills as $billData) {
            $bill = Bill::create($billData);

            // Create containers for each bill
            $containerCount = rand(1, 4);
            for ($i = 1; $i <= $containerCount; $i++) {
                $container = Container::create([
                    'bill_id' => $bill->id,
                    'truck' => 'TRK-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'container_number' => 'CONT' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                    'quantity_of_bags' => rand(50, 200),
                    'w_jute_bag' => 1.00,
                    'w_total' => rand(15000, 25000),
                    'w_truck' => rand(8000, 12000),
                    'w_container' => rand(2000, 3000),
                    'w_gross' => rand(20000, 30000),
                    'w_dunnage_dribag' => rand(100, 300),
                    'w_tare' => rand(2100, 3300),
                    'w_net' => rand(17000, 27000),
                    'note' => $i === 1 ? 'First container' : null,
                ]);

                // Create cutting test for container (type 4)
                if (rand(0, 1)) {
                    CuttingTest::create([
                        'bill_id' => $bill->id,
                        'container_id' => $container->id,
                        'type' => 4,
                        'moisture' => rand(80, 120) / 10, // 8.0 to 12.0%
                        'sample_weight' => 1000,
                        'nut_count' => rand(180, 220),
                        'w_reject_nut' => rand(50, 100),
                        'w_defective_nut' => rand(100, 150),
                        'w_defective_kernel' => rand(80, 120),
                        'w_good_kernel' => rand(650, 750),
                        'w_sample_after_cut' => rand(900, 950),
                        'outturn_rate' => rand(4500, 5500) / 100, // 45.00 to 55.00
                        'note' => null,
                    ]);
                }
            }

            // Create final sample cutting tests (types 1-3)
            $finalSampleCount = rand(1, 3);
            for ($type = 1; $type <= $finalSampleCount; $type++) {
                CuttingTest::create([
                    'bill_id' => $bill->id,
                    'container_id' => null,
                    'type' => $type,
                    'moisture' => rand(85, 115) / 10, // 8.5 to 11.5%
                    'sample_weight' => 1000,
                    'nut_count' => rand(190, 210),
                    'w_reject_nut' => rand(40, 80),
                    'w_defective_nut' => rand(90, 130),
                    'w_defective_kernel' => rand(70, 110),
                    'w_good_kernel' => rand(680, 780),
                    'w_sample_after_cut' => rand(920, 970),
                    'outturn_rate' => rand(4800, 5200) / 100, // 48.00 to 52.00
                    'note' => "Final sample test #{$type}",
                ]);
            }
        }
    }
}