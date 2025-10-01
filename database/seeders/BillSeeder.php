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
                'w_dunnage_dribag' => 150,
                'w_jute_bag' => 1.05,
                'net_on_bl' => 21500,
                'quantity_of_bags_on_bl' => 200,
                'origin' => 'Vietnam',
                'inspection_start_date' => now()->subDays(3),
                'inspection_end_date' => now()->subDay(),
                'inspection_location' => 'Ho Chi Minh City Warehouse',
                'sampling_ratio' => 12.5,
                'note' => 'First shipment of the season',
            ],
            [
                'bill_number' => 'BL-2024-002',
                'seller' => 'Premium Cashews Co',
                'buyer' => 'European Distributors',
                'w_dunnage_dribag' => 120,
                'w_jute_bag' => 1.10,
                'net_on_bl' => 20500,
                'quantity_of_bags_on_bl' => 180,
                'origin' => 'India',
                'inspection_start_date' => now()->subDays(5),
                'inspection_end_date' => now()->subDays(2),
                'inspection_location' => 'Da Nang Port',
                'sampling_ratio' => 10.0,
                'note' => 'High quality grade A cashews',
            ],
            [
                'bill_number' => 'BL-2024-003',
                'seller' => 'Tropical Harvest',
                'buyer' => 'Asian Markets Ltd',
                'w_dunnage_dribag' => 135,
                'w_jute_bag' => 1.08,
                'net_on_bl' => 19800,
                'quantity_of_bags_on_bl' => 170,
                'origin' => 'Ghana',
                'inspection_start_date' => now()->subDays(7),
                'inspection_end_date' => now()->subDays(4),
                'inspection_location' => 'Hai Phong Terminal',
                'sampling_ratio' => 11.0,
                'note' => null,
            ],
            [
                'bill_number' => null,
                'seller' => 'Local Farmers Coop',
                'buyer' => 'Regional Processor',
                'w_dunnage_dribag' => 140,
                'w_jute_bag' => 1.00,
                'net_on_bl' => 18500,
                'quantity_of_bags_on_bl' => 150,
                'origin' => 'Cambodia',
                'inspection_start_date' => now()->subDays(2),
                'inspection_end_date' => now()->subDay(),
                'inspection_location' => 'Border Warehouse',
                'sampling_ratio' => 9.5,
                'note' => 'Bulk purchase from local farmers',
            ],
            [
                'bill_number' => 'BL-2024-005',
                'seller' => 'Organic Cashew Farms',
                'buyer' => 'Health Food Distributors',
                'w_dunnage_dribag' => 100,
                'w_jute_bag' => 1.02,
                'net_on_bl' => 22500,
                'quantity_of_bags_on_bl' => 210,
                'origin' => 'Brazil',
                'inspection_start_date' => now()->subDays(6),
                'inspection_end_date' => now()->subDays(3),
                'inspection_location' => 'Organic Processing Plant',
                'sampling_ratio' => 8.0,
                'note' => 'Certified organic cashews',
            ],
        ];

        foreach ($bills as $billData) {
            $bill = Bill::create($billData);

            // Create containers for each bill
            $containerCount = rand(1, 4);
            for ($i = 1; $i <= $containerCount; $i++) {
                $container = Container::factory()
                    ->withBill($bill)
                    ->create([
                        'truck' => 'TRK-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                        'container_number' => 'CONT' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                        'quantity_of_bags' => rand(150, 280),
                        'w_total' => rand(19000, 26000),
                        'w_truck' => rand(8000, 12000),
                        'w_container' => rand(2000, 3000),
                        'w_gross' => rand(20000, 26000),
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
