<?php

declare(strict_types=1);

use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use App\Repositories\BillRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->repository = app(BillRepository::class);
});

describe('BillRepository Basic CRUD Operations', function () {
    it('creates a bill with all database fields', function () {
        $data = [
            'bill_number' => 'TEST-001',
            'seller' => 'Test Seller',
            'buyer' => 'Test Buyer',
            'w_dunnage_dribag' => 200,
            'w_jute_bag' => 1.5,
            'net_on_bl' => 10000,
            'quantity_of_bags_on_bl' => 100,
            'origin' => 'Vietnam',
            'inspection_start_date' => '2024-01-01',
            'inspection_end_date' => '2024-01-03',
            'inspection_location' => 'Port A',
            'sampling_ratio' => 10.0,
            'note' => 'Test note',
        ];

        $bill = $this->repository->create($data);

        expect($bill->exists)->toBeTrue()
            ->and($bill->bill_number)->toBe('TEST-001')
            ->and($bill->w_dunnage_dribag)->toBe(200)
            ->and($bill->w_jute_bag)->toBe('1.50')
            ->and($bill->net_on_bl)->toBe(10000)
            ->and($bill->quantity_of_bags_on_bl)->toBe(100)
            ->and($bill->origin)->toBe('Vietnam')
            ->and($bill->inspection_location)->toBe('Port A')
            ->and($bill->sampling_ratio)->toBe('10.00');
    });

    it('finds bill by id with relations', function () {
        $bill = Bill::factory()->create();
        $container = Container::factory()->create();
        $bill->containers()->attach($container->id);

        $result = $this->repository->findByIdWithRelations($bill->id, ['containers']);

        expect($result)->not->toBeNull()
            ->and($result->id)->toBe($bill->id)
            ->and($result->relationLoaded('containers'))->toBeTrue()
            ->and($result->containers)->toHaveCount(1);
    });

    it('updates bill with new field values', function () {
        $bill = Bill::factory()->create([
            'sampling_ratio' => 10.0,
            'origin' => 'Vietnam',
        ]);

        $updated = $this->repository->update($bill, [
            'sampling_ratio' => 15.0,
            'origin' => 'Indonesia',
            'inspection_location' => 'Updated Port',
        ]);

        expect($updated)->toBeTrue();
        $bill->refresh();
        expect($bill->sampling_ratio)->toBe('15.00')
            ->and($bill->origin)->toBe('Indonesia')
            ->and($bill->inspection_location)->toBe('Updated Port');
    });

    it('soft deletes bill', function () {
        $bill = Bill::factory()->create();

        $deleted = $this->repository->delete($bill);

        expect($deleted)->toBeTrue()
            ->and(Bill::find($bill->id))->toBeNull()
            ->and(Bill::withTrashed()->find($bill->id))->not->toBeNull()
            ->and(Bill::withTrashed()->find($bill->id)->trashed())->toBeTrue();
    });
});

describe('BillRepository New Query Methods', function () {
    it('finds bills by inspection date range', function () {
        $bill1 = Bill::factory()->create([
            'inspection_start_date' => '2024-01-01',
            'inspection_end_date' => '2024-01-03',
        ]);
        $bill2 = Bill::factory()->create([
            'inspection_start_date' => '2024-01-05',
            'inspection_end_date' => '2024-01-07',
        ]);
        $bill3 = Bill::factory()->create([
            'inspection_start_date' => '2024-02-01',
            'inspection_end_date' => '2024-02-03',
        ]);

        $startDate = new DateTime('2024-01-01');
        $endDate = new DateTime('2024-01-10');

        $results = $this->repository->findByInspectionDateRange($startDate, $endDate);

        expect($results)->toHaveCount(2)
            ->and($results->pluck('id'))->toContain($bill1->id, $bill2->id)
            ->and($results->pluck('id'))->not->toContain($bill3->id);
    });

    it('finds bills by inspection date range with only start date', function () {
        $bill1 = Bill::factory()->create([
            'inspection_start_date' => '2024-01-01',
        ]);
        $bill2 = Bill::factory()->create([
            'inspection_start_date' => '2023-12-31',
        ]);

        $startDate = new DateTime('2024-01-01');

        $results = $this->repository->findByInspectionDateRange($startDate);

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($bill1->id);
    });

    it('finds bills by origin', function () {
        $vietnamBill = Bill::factory()->create(['origin' => 'Vietnam']);
        $indonesiaBill = Bill::factory()->create(['origin' => 'Indonesia']);
        $indiaBill = Bill::factory()->create(['origin' => 'India']);

        $results = $this->repository->findByOrigin('Vietnam');

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($vietnamBill->id);

        $partialResults = $this->repository->findByOrigin('Indo');
        expect($partialResults)->toHaveCount(1)
            ->and($partialResults->first()->id)->toBe($indonesiaBill->id);
    });

    it('finds bills by sampling ratio range', function () {
        $bill1 = Bill::factory()->create(['sampling_ratio' => 5.0]);
        $bill2 = Bill::factory()->create(['sampling_ratio' => 10.0]);
        $bill3 = Bill::factory()->create(['sampling_ratio' => 15.0]);
        $bill4 = Bill::factory()->create(['sampling_ratio' => 20.0]);

        $results = $this->repository->findBySamplingRatioRange(8.0, 12.0);

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($bill2->id);

        $wideResults = $this->repository->findBySamplingRatioRange(5.0, 15.0);
        expect($wideResults)->toHaveCount(3)
            ->and($wideResults->pluck('id'))->toContain($bill1->id, $bill2->id, $bill3->id);
    });

    it('finds bill with complete relations efficiently', function () {
        $bill = Bill::factory()->create();
        $container = Container::factory()->create();
        $bill->containers()->attach($container->id);
        
        $containerTest = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'type' => 4,
        ]);
        
        $finalSample = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => null,
            'type' => 1,
        ]);

        $result = $this->repository->findWithCompleteRelations($bill->id);

        expect($result)->not->toBeNull()
            ->and($result->relationLoaded('containers'))->toBeTrue()
            ->and($result->relationLoaded('finalSamples'))->toBeTrue()
            ->and($result->relationLoaded('cuttingTests'))->toBeTrue()
            ->and($result->containers->first()->relationLoaded('cuttingTests'))->toBeTrue()
            ->and($result->containers)->toHaveCount(1)
            ->and($result->finalSamples)->toHaveCount(1)
            ->and($result->cuttingTests)->toHaveCount(2);
    });

    it('finds bills with high moisture containers', function () {
        $bill1 = Bill::factory()->create();
        $bill2 = Bill::factory()->create();
        
        $container1 = Container::factory()->create();
        $container2 = Container::factory()->create();
        
        $bill1->containers()->attach($container1->id);
        $bill2->containers()->attach($container2->id);
        
        // High moisture cutting test
        CuttingTest::factory()->create([
            'bill_id' => $bill1->id,
            'container_id' => $container1->id,
            'moisture' => 12.5,
        ]);
        
        // Normal moisture cutting test
        CuttingTest::factory()->create([
            'bill_id' => $bill2->id,
            'container_id' => $container2->id,
            'moisture' => 9.5,
        ]);

        $results = $this->repository->getBillsWithHighMoistureContainers(11.0);

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($bill1->id)
            ->and($results->first()->relationLoaded('containers'))->toBeTrue()
            ->and($results->first()->containers->first()->relationLoaded('cuttingTests'))->toBeTrue();
    });
});

describe('BillRepository Advanced Filtering', function () {
    it('filters bills with comprehensive filters', function () {
        $bill1 = Bill::factory()->create([
            'bill_number' => 'BL-001',
            'seller' => 'Seller A',
            'buyer' => 'Buyer A',
            'origin' => 'Vietnam',
            'inspection_location' => 'Port A',
            'inspection_start_date' => '2024-01-01',
            'inspection_end_date' => '2024-01-03',
            'sampling_ratio' => 10.0,
            'net_on_bl' => 5000.0,
            'quantity_of_bags_on_bl' => 50,
        ]);
        
        $bill2 = Bill::factory()->create([
            'bill_number' => 'BL-002',
            'seller' => 'Seller B',
            'buyer' => 'Buyer B',
            'origin' => 'Indonesia',
            'inspection_location' => 'Port B',
            'inspection_start_date' => '2024-02-01',
            'inspection_end_date' => '2024-02-03',
            'sampling_ratio' => 15.0,
            'net_on_bl' => 8000.0,
            'quantity_of_bags_on_bl' => 80,
        ]);

        // Test bill number filter
        $results = $this->repository->findWithFilters(['bill_number' => 'BL-001']);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($bill1->id);

        // Test seller filter
        $results = $this->repository->findWithFilters(['seller' => 'Seller A']);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($bill1->id);

        // Test origin filter
        $results = $this->repository->findWithFilters(['origin' => 'Vietnam']);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($bill1->id);

        // Test sampling ratio range
        $results = $this->repository->findWithFilters([
            'sampling_ratio_min' => 12.0,
            'sampling_ratio_max' => 20.0,
        ]);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($bill2->id);

        // Test net weight on BL range
        $results = $this->repository->findWithFilters([
            'net_on_bl_min' => 7000.0,
            'net_on_bl_max' => 9000.0,
        ]);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($bill2->id);

        // Test quantity of bags range
        $results = $this->repository->findWithFilters([
            'quantity_of_bags_on_bl_min' => 70,
            'quantity_of_bags_on_bl_max' => 90,
        ]);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($bill2->id);

        // Test inspection date range
        $results = $this->repository->findWithFilters([
            'inspection_start_from' => '2024-01-01',
            'inspection_start_to' => '2024-01-31',
        ]);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($bill1->id);
    });

    it('handles pagination correctly', function () {
        Bill::factory()->count(25)->create();

        $results = $this->repository->findWithFilters(['per_page' => 10]);

        expect($results->total())->toBe(25)
            ->and($results->perPage())->toBe(10)
            ->and($results->count())->toBe(10)
            ->and($results->hasPages())->toBeTrue();
    });
});

describe('BillRepository Performance Tests', function () {
    it('handles large datasets efficiently', function () {
        // Create a larger dataset
        $bills = Bill::factory()->count(100)->create();
        $containers = Container::factory()->count(200)->create();
        
        // Attach containers to bills
        foreach ($bills as $index => $bill) {
            $bill->containers()->attach($containers->slice($index * 2, 2)->pluck('id'));
        }

        $startTime = microtime(true);
        $results = $this->repository->findWithFilters(['per_page' => 20]);
        $endTime = microtime(true);

        expect($results->total())->toBe(100)
            ->and($results->count())->toBe(20)
            ->and($endTime - $startTime)->toBeLessThan(1.0); // Should complete within 1 second
    });

    it('optimizes queries with relationship counts', function () {
        $bill = Bill::factory()->create();
        $containers = Container::factory()->count(3)->create();
        $bill->containers()->attach($containers->pluck('id'));
        
        CuttingTest::factory()->count(2)->create([
            'bill_id' => $bill->id,
            'container_id' => null,
            'type' => 1,
        ]);

        $result = $this->repository->getAllWithCounts();

        expect($result->first()->containers_count)->toBe(3)
            ->and($result->first()->final_samples_count)->toBe(2);
    });
});

describe('BillRepository Relationship Handling', function () {
    it('handles many-to-many relationship with containers correctly', function () {
        $bill = Bill::factory()->create();
        $container1 = Container::factory()->create();
        $container2 = Container::factory()->create();
        
        $bill->containers()->attach([
            $container1->id,
            $container2->id,
        ]);

        $result = $this->repository->findByIdWithRelations($bill->id, ['containers']);

        expect($result->containers)->toHaveCount(2)
            ->and($result->containers->first()->pivot->bill_id)->toBe($bill->id);
    });

    it('retrieves bills missing final samples correctly', function () {
        $billWithAllSamples = Bill::factory()->create();
        $billMissingSamples = Bill::factory()->create();
        
        $container = Container::factory()->create();
        $billWithAllSamples->containers()->attach($container->id);
        $billMissingSamples->containers()->attach($container->id);
        
        // Create all final samples for first bill
        CuttingTest::factory()->create([
            'bill_id' => $billWithAllSamples->id,
            'container_id' => null,
            'type' => 1,
        ]);
        CuttingTest::factory()->create([
            'bill_id' => $billWithAllSamples->id,
            'container_id' => null,
            'type' => 2,
        ]);
        CuttingTest::factory()->create([
            'bill_id' => $billWithAllSamples->id,
            'container_id' => null,
            'type' => 3,
        ]);
        
        // Create only one final sample for second bill
        CuttingTest::factory()->create([
            'bill_id' => $billMissingSamples->id,
            'container_id' => null,
            'type' => 1,
        ]);

        $results = $this->repository->getBillsMissingFinalSamples();

        expect($results->pluck('id'))->toContain($billMissingSamples->id)
            ->and($results->pluck('id'))->not->toContain($billWithAllSamples->id);
    });
});

describe('BillRepository Query Performance and Large Dataset Tests', function () {
    it('handles complex queries with multiple joins efficiently', function () {
        // Create test data
        $bills = Bill::factory()->count(50)->create([
            'origin' => 'Vietnam',
            'sampling_ratio' => fake()->randomFloat(2, 5.0, 20.0),
            'inspection_start_date' => fake()->dateTimeBetween('2024-01-01', '2024-06-30'),
            'inspection_end_date' => fake()->dateTimeBetween('2024-07-01', '2024-12-31'),
        ]);

        $containers = Container::factory()->count(100)->create();
        
        // Attach containers to bills
        foreach ($bills as $index => $bill) {
            $bill->containers()->attach($containers->slice($index * 2, 2)->pluck('id'));
        }

        $startTime = microtime(true);
        
        // Test complex query with multiple filters
        $results = $this->repository->findWithFilters([
            'origin' => 'Vietnam',
            'sampling_ratio_min' => 5.0,
            'sampling_ratio_max' => 20.0,
            'inspection_start_from' => '2024-01-01',
            'inspection_end_to' => '2024-12-31',
            'per_page' => 20,
        ]);
        
        $endTime = microtime(true);

        expect($results->total())->toBeGreaterThan(0)
            ->and($endTime - $startTime)->toBeLessThan(2.0) // Should complete within 2 seconds
            ->and($results->first()->containers_count)->toBeGreaterThanOrEqual(0); // Should have count
    });

    it('optimizes inspection date range queries with indexes', function () {
        // Create bills with various inspection dates
        Bill::factory()->count(30)->create([
            'inspection_start_date' => '2024-01-15',
            'inspection_end_date' => '2024-01-20',
        ]);
        Bill::factory()->count(20)->create([
            'inspection_start_date' => '2024-02-15',
            'inspection_end_date' => '2024-02-20',
        ]);
        Bill::factory()->count(25)->create([
            'inspection_start_date' => '2024-03-15',
            'inspection_end_date' => '2024-03-20',
        ]);

        $startTime = microtime(true);
        
        $results = $this->repository->findByInspectionDateRange(
            new DateTime('2024-01-01'),
            new DateTime('2024-02-28')
        );
        
        $endTime = microtime(true);

        expect($results)->toHaveCount(50) // 30 + 20 bills in range
            ->and($endTime - $startTime)->toBeLessThan(1.0); // Should be fast with proper indexing
    });

    it('handles origin search with partial matching efficiently', function () {
        // Create bills with various origins
        Bill::factory()->count(15)->create(['origin' => 'Vietnam North']);
        Bill::factory()->count(10)->create(['origin' => 'Vietnam South']);
        Bill::factory()->count(12)->create(['origin' => 'Indonesia']);
        Bill::factory()->count(8)->create(['origin' => 'India']);

        $startTime = microtime(true);
        
        $vietnamResults = $this->repository->findByOrigin('Vietnam');
        
        $endTime = microtime(true);

        expect($vietnamResults)->toHaveCount(25) // 15 + 10 Vietnam bills
            ->and($endTime - $startTime)->toBeLessThan(0.5); // Should be very fast
    });

    it('verifies relationship loading accuracy with large datasets', function () {
        $bill = Bill::factory()->create();
        $containers = Container::factory()->count(10)->create();
        $bill->containers()->attach($containers->pluck('id'));
        
        // Create cutting tests for containers and final samples
        foreach ($containers->take(5) as $container) {
            CuttingTest::factory()->count(2)->create([
                'bill_id' => $bill->id,
                'container_id' => $container->id,
                'type' => 4,
            ]);
        }
        
        // Create final samples
        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => null,
            'type' => 1,
        ]);
        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => null,
            'type' => 2,
        ]);

        $result = $this->repository->findWithCompleteRelations($bill->id);

        expect($result->containers)->toHaveCount(10)
            ->and($result->finalSamples)->toHaveCount(2)
            ->and($result->cuttingTests)->toHaveCount(12) // 10 container tests + 2 final samples
            ->and($result->containers->filter(fn($c) => $c->cuttingTests->count() > 0))->toHaveCount(5);
    });

    it('tests sampling ratio range queries with edge cases', function () {
        // Create bills with specific sampling ratios
        $bill1 = Bill::factory()->create(['sampling_ratio' => 5.0]);
        $bill2 = Bill::factory()->create(['sampling_ratio' => 10.0]);
        $bill3 = Bill::factory()->create(['sampling_ratio' => 15.0]);
        $bill4 = Bill::factory()->create(['sampling_ratio' => 20.0]);
        $bill5 = Bill::factory()->create(['sampling_ratio' => 25.0]);

        // Test exact boundary matches
        $results = $this->repository->findBySamplingRatioRange(10.0, 20.0);
        expect($results)->toHaveCount(3)
            ->and($results->pluck('id'))->toContain($bill2->id, $bill3->id, $bill4->id);

        // Test single value range
        $results = $this->repository->findBySamplingRatioRange(15.0, 15.0);
        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($bill3->id);

        // Test wide range
        $results = $this->repository->findBySamplingRatioRange(1.0, 30.0);
        expect($results)->toHaveCount(5);
    });

    it('validates high moisture container query accuracy', function () {
        $bill1 = Bill::factory()->create();
        $bill2 = Bill::factory()->create();
        $bill3 = Bill::factory()->create();
        
        $container1 = Container::factory()->create();
        $container2 = Container::factory()->create();
        $container3 = Container::factory()->create();
        
        $bill1->containers()->attach($container1->id);
        $bill2->containers()->attach($container2->id);
        $bill3->containers()->attach($container3->id);
        
        // High moisture tests
        CuttingTest::factory()->create([
            'bill_id' => $bill1->id,
            'container_id' => $container1->id,
            'moisture' => 12.5,
        ]);
        CuttingTest::factory()->create([
            'bill_id' => $bill2->id,
            'container_id' => $container2->id,
            'moisture' => 13.8,
        ]);
        
        // Normal moisture test
        CuttingTest::factory()->create([
            'bill_id' => $bill3->id,
            'container_id' => $container3->id,
            'moisture' => 9.2,
        ]);

        $results = $this->repository->getBillsWithHighMoistureContainers(11.0);

        expect($results)->toHaveCount(2)
            ->and($results->pluck('id'))->toContain($bill1->id, $bill2->id)
            ->and($results->pluck('id'))->not->toContain($bill3->id);
        
        // Verify that only high moisture containers are loaded
        $firstResult = $results->first();
        expect($firstResult->containers)->toHaveCount(1)
            ->and($firstResult->containers->first()->cuttingTests->first()->moisture)->toBeGreaterThan(11.0);
    });

    it('tests query performance with large datasets and complex joins', function () {
        // Create a substantial dataset for performance testing
        $bills = Bill::factory()->count(200)->create([
            'origin' => fake()->randomElement(['Vietnam', 'Indonesia', 'India', 'Brazil']),
            'sampling_ratio' => fake()->randomFloat(2, 5.0, 25.0),
            'inspection_start_date' => fake()->dateTimeBetween('2024-01-01', '2024-06-30'),
            'inspection_end_date' => fake()->dateTimeBetween('2024-07-01', '2024-12-31'),
            'net_on_bl' => fake()->numberBetween(5000, 15000),
            'quantity_of_bags_on_bl' => fake()->numberBetween(50, 150),
        ]);

        $containers = Container::factory()->count(400)->create();
        
        // Attach containers to bills (2 containers per bill on average)
        foreach ($bills as $index => $bill) {
            $bill->containers()->attach($containers->slice($index * 2, 2)->pluck('id'));
        }

        // Create cutting tests for some containers
        foreach ($containers->take(100) as $container) {
            CuttingTest::factory()->create([
                'bill_id' => $bills->random()->id,
                'container_id' => $container->id,
                'moisture' => fake()->randomFloat(1, 8.0, 15.0),
            ]);
        }

        $startTime = microtime(true);
        
        // Test complex filtering with multiple conditions
        $results = $this->repository->findWithFilters([
            'origin' => 'Vietnam',
            'sampling_ratio_min' => 8.0,
            'sampling_ratio_max' => 20.0,
            'net_on_bl_min' => 7000,
            'net_on_bl_max' => 12000,
            'quantity_of_bags_on_bl_min' => 75,
            'quantity_of_bags_on_bl_max' => 125,
            'inspection_start_from' => '2024-01-01',
            'inspection_end_to' => '2024-12-31',
            'per_page' => 25,
        ]);
        
        $endTime = microtime(true);

        expect($results->total())->toBeGreaterThanOrEqual(0)
            ->and($endTime - $startTime)->toBeLessThan(3.0) // Should complete within 3 seconds even with large dataset
            ->and($results->perPage())->toBe(25);
    });

    it('validates relationship handling accuracy under load', function () {
        $bill = Bill::factory()->create();
        $containers = Container::factory()->count(20)->create();
        $bill->containers()->attach($containers->pluck('id'));
        
        // Create multiple cutting tests per container
        foreach ($containers as $container) {
            CuttingTest::factory()->count(3)->create([
                'bill_id' => $bill->id,
                'container_id' => $container->id,
                'type' => 4,
                'moisture' => fake()->randomFloat(1, 8.0, 15.0),
            ]);
        }
        
        // Create final samples
        for ($type = 1; $type <= 3; $type++) {
            CuttingTest::factory()->create([
                'bill_id' => $bill->id,
                'container_id' => null,
                'type' => $type,
            ]);
        }

        $startTime = microtime(true);
        $result = $this->repository->findWithCompleteRelations($bill->id);
        $endTime = microtime(true);

        expect($result->containers)->toHaveCount(20)
            ->and($result->finalSamples)->toHaveCount(3)
            ->and($result->cuttingTests)->toHaveCount(63) // 60 container tests + 3 final samples
            ->and($endTime - $startTime)->toBeLessThan(1.0) // Should load efficiently
            ->and($result->containers->every(fn($c) => $c->cuttingTests->count() === 3))->toBeTrue();
    });
});