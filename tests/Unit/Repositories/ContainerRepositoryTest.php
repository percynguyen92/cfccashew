<?php

declare(strict_types=1);

use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use App\Repositories\ContainerRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->repository = app(ContainerRepository::class);
});

describe('ContainerRepository Basic CRUD Operations', function () {
    it('creates a container with all database fields', function () {
        $data = [
            'truck' => 'TRK-001',
            'container_number' => 'ABCD1234567',
            'quantity_of_bags' => 100,
            'w_total' => 25000,
            'w_truck' => 10000,
            'w_container' => 3000,
            'w_gross' => 12000,
            'w_tare' => 150.0,
            'w_net' => 11650.0,
            'container_condition' => 'Nguyên vẹn',
            'seal_condition' => 'Nguyên vẹn',
            'note' => 'Test container',
        ];

        $container = $this->repository->create($data);

        expect($container->exists)->toBeTrue()
            ->and($container->truck)->toBe('TRK-001')
            ->and($container->container_number)->toBe('ABCD1234567')
            ->and($container->quantity_of_bags)->toBe(100)
            ->and($container->w_total)->toBe(25000)
            ->and($container->container_condition)->toBe('Nguyên vẹn')
            ->and($container->seal_condition)->toBe('Nguyên vẹn');
    });

    it('finds container by container number', function () {
        $container = Container::factory()->create([
            'container_number' => 'TEST1234567',
        ]);

        $result = $this->repository->findByContainerNumber('TEST1234567');

        expect($result)->not->toBeNull()
            ->and($result->id)->toBe($container->id)
            ->and($result->container_number)->toBe('TEST1234567');
    });

    it('finds container by container number or ID', function () {
        $container = Container::factory()->create([
            'container_number' => 'ABCD1234567',
        ]);

        // Test finding by container number
        $resultByNumber = $this->repository->findByContainerNumberOrId('ABCD1234567');
        expect($resultByNumber)->not->toBeNull()
            ->and($resultByNumber->id)->toBe($container->id);

        // Test finding by ID
        $resultById = $this->repository->findByContainerNumberOrId((string) $container->id);
        expect($resultById)->not->toBeNull()
            ->and($resultById->id)->toBe($container->id);

        // Test invalid format
        $resultInvalid = $this->repository->findByContainerNumberOrId('INVALID');
        expect($resultInvalid)->toBeNull();
    });

    it('updates container with new field values', function () {
        $container = Container::factory()->create([
            'container_condition' => 'Good',
            'seal_condition' => 'Intact',
            'w_total' => 20000.0,
        ]);

        $updated = $this->repository->update($container, [
            'container_condition' => 'Damaged',
            'seal_condition' => 'Broken',
            'w_total' => 22000,
        ]);

        expect($updated)->toBeTrue();
        $container->refresh();
        expect($container->container_condition)->toBe('Damaged')
            ->and($container->seal_condition)->toBe('Broken')
            ->and($container->w_total)->toBe(22000);
    });

    it('soft deletes container', function () {
        $container = Container::factory()->create();

        $deleted = $this->repository->delete($container);

        expect($deleted)->toBeTrue()
            ->and(Container::find($container->id))->toBeNull()
            ->and(Container::withTrashed()->find($container->id)->trashed())->toBeTrue();
    });
});

describe('ContainerRepository Condition Filtering', function () {
    it('finds containers by container condition', function () {
        $goodContainer = Container::factory()->create(['container_condition' => 'Good']);
        $damagedContainer = Container::factory()->create(['container_condition' => 'Damaged']);
        $excellentContainer = Container::factory()->create(['container_condition' => 'Excellent']);

        $results = $this->repository->findByContainerCondition('Good');

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($goodContainer->id)
            ->and($results->first()->relationLoaded('bills'))->toBeTrue()
            ->and($results->first()->relationLoaded('cuttingTests'))->toBeTrue();
    });

    it('finds containers by seal condition', function () {
        $intactContainer = Container::factory()->create(['seal_condition' => 'Intact']);
        $brokenContainer = Container::factory()->create(['seal_condition' => 'Broken']);
        $damagedContainer = Container::factory()->create(['seal_condition' => 'Damaged']);

        $results = $this->repository->findBySealCondition('Intact');

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($intactContainer->id);
    });

    it('finds containers by both conditions', function () {
        $container1 = Container::factory()->create([
            'container_condition' => 'Good',
            'seal_condition' => 'Intact',
        ]);
        $container2 = Container::factory()->create([
            'container_condition' => 'Good',
            'seal_condition' => 'Broken',
        ]);
        $container3 = Container::factory()->create([
            'container_condition' => 'Damaged',
            'seal_condition' => 'Intact',
        ]);

        $results = $this->repository->findByConditions('Good', 'Intact');

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($container1->id);
    });

    it('gets containers by condition status arrays', function () {
        $container1 = Container::factory()->create([
            'container_condition' => 'Good',
            'seal_condition' => 'Intact',
        ]);
        $container2 = Container::factory()->create([
            'container_condition' => 'Excellent',
            'seal_condition' => 'Broken',
        ]);
        $container3 = Container::factory()->create([
            'container_condition' => 'Damaged',
            'seal_condition' => 'Damaged',
        ]);

        $results = $this->repository->getContainersByConditionStatus(
            ['Good', 'Excellent'],
            ['Intact', 'Broken']
        );

        expect($results)->toHaveCount(2)
            ->and($results->pluck('id'))->toContain($container1->id, $container2->id)
            ->and($results->pluck('id'))->not->toContain($container3->id);
    });
});

describe('ContainerRepository Weight Range Filtering', function () {
    it('finds containers by total weight range', function () {
        $container1 = Container::factory()->create(['w_total' => 20000]);
        $container2 = Container::factory()->create(['w_total' => 25000]);
        $container3 = Container::factory()->create(['w_total' => 30000]);

        $results = $this->repository->findByTotalWeightRange(22000.0, 28000.0);

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($container2->id);
    });

    it('finds containers by gross weight range', function () {
        $container1 = Container::factory()->create(['w_gross' => 10000]);
        $container2 = Container::factory()->create(['w_gross' => 15000]);
        $container3 = Container::factory()->create(['w_gross' => 20000]);

        $results = $this->repository->findByGrossWeightRange(12000.0, 18000.0);

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($container2->id);
    });

    it('finds containers by net weight range', function () {
        $container1 = Container::factory()->create(['w_net' => 8000.0]);
        $container2 = Container::factory()->create(['w_net' => 12000.0]);
        $container3 = Container::factory()->create(['w_net' => 16000.0]);

        $results = $this->repository->findByNetWeightRange(10000.0, 14000.0);

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($container2->id);
    });
});

describe('ContainerRepository Moisture and Alert Queries', function () {
    it('gets containers with high moisture', function () {
        $bill = Bill::factory()->create();
        $container1 = Container::factory()->create();
        $container2 = Container::factory()->create();
        
        $bill->containers()->attach([$container1->id, $container2->id]);

        // High moisture cutting test
        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container1->id,
            'moisture' => 12.5,
        ]);

        // Normal moisture cutting test
        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container2->id,
            'moisture' => 9.5,
        ]);

        $results = $this->repository->getContainersWithHighMoisture(11.0);

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($container1->id)
            ->and($results->first()->relationLoaded('bills'))->toBeTrue()
            ->and($results->first()->relationLoaded('cuttingTests'))->toBeTrue();
    });

    it('gets containers with moisture alerts', function () {
        $bill = Bill::factory()->create();
        $container1 = Container::factory()->create();
        $container2 = Container::factory()->create();
        
        $bill->containers()->attach([$container1->id, $container2->id]);

        // Multiple high moisture tests for container1
        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container1->id,
            'moisture' => 13.2,
        ]);
        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container1->id,
            'moisture' => 11.8,
        ]);

        // Normal moisture test for container2
        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container2->id,
            'moisture' => 10.5,
        ]);

        $results = $this->repository->getContainersWithMoistureAlerts(11.0);

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($container1->id)
            ->and($results->first()->cuttingTests)->toHaveCount(2)
            ->and($results->first()->cuttingTests->first()->moisture)->toBeGreaterThan(11.0);
    });

    it('gets containers pending cutting tests', function () {
        $bill = Bill::factory()->create();
        $containerWithTests = Container::factory()->create();
        $containerPending = Container::factory()->create();
        
        $bill->containers()->attach([$containerWithTests->id, $containerPending->id]);

        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $containerWithTests->id,
        ]);

        $results = $this->repository->getContainersPendingCuttingTests();

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($containerPending->id)
            ->and($results->first()->relationLoaded('bills'))->toBeTrue();
    });
});

describe('ContainerRepository Weight Discrepancy Detection', function () {
    it('detects containers with weight discrepancies', function () {
        $container1 = Container::factory()->create([
            'w_total' => 25000,
            'w_truck' => 10000,
            'w_container' => 3000,
            'w_gross' => 12000, // Expected: 25000 - 10000 - 3000 = 12000 (no discrepancy)
            'w_net' => 11650.0,
        ]);

        $container2 = Container::factory()->create([
            'w_total' => 25000,
            'w_truck' => 10000,
            'w_container' => 3000,
            'w_gross' => 11990, // Expected: 12000, actual: 11990 (10kg discrepancy > 5kg threshold)
            'w_net' => 11640.0,
        ]);

        $results = $this->repository->getContainersWithWeightDiscrepancies();

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($container2->id);
    });
});

describe('ContainerRepository Advanced Filtering', function () {
    it('filters containers with comprehensive filters', function () {
        $bill = Bill::factory()->create(['bill_number' => 'BL-001']);
        
        $container1 = Container::factory()->create([
            'container_number' => 'CONT1234567',
            'truck' => 'TRK-001',
            'container_condition' => 'Good',
            'seal_condition' => 'Intact',
            'quantity_of_bags' => 100,
            'w_total' => 25000,
            'w_gross' => 12000,
            'w_net' => 11000.0,
            'w_truck' => 10000,
            'w_container' => 3000,
            'w_tare' => 150.0,
        ]);
        
        $container2 = Container::factory()->create([
            'container_number' => 'CONT7654321',
            'truck' => 'TRK-002',
            'container_condition' => 'Damaged',
            'seal_condition' => 'Broken',
            'quantity_of_bags' => 80,
            'w_total' => 20000,
            'w_gross' => 10000,
            'w_net' => 9000.0,
            'w_truck' => 8000,
            'w_container' => 2000,
            'w_tare' => 120.0,
        ]);

        $bill->containers()->attach([$container1->id, $container2->id]);

        // Test container number filter
        $results = $this->repository->findWithFilters(['container_number' => 'CONT123']);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($container1->id);

        // Test truck filter
        $results = $this->repository->findWithFilters(['truck' => 'TRK-001']);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($container1->id);

        // Test container condition filter
        $results = $this->repository->findWithFilters(['container_condition' => 'Good']);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($container1->id);

        // Test seal condition filter
        $results = $this->repository->findWithFilters(['seal_condition' => 'Broken']);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($container2->id);

        // Test quantity of bags range
        $results = $this->repository->findWithFilters([
            'quantity_of_bags_min' => 90,
            'quantity_of_bags_max' => 110,
        ]);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($container1->id);

        // Test weight ranges
        $results = $this->repository->findWithFilters([
            'w_total_min' => 22000,
            'w_total_max' => 28000,
        ]);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($container1->id);

        // Test bill number filter
        $results = $this->repository->findWithFilters(['bill_number' => 'BL-001']);
        expect($results->total())->toBe(2);

        // Test bill ID filter
        $results = $this->repository->findWithFilters(['bill_id' => $bill->id]);
        expect($results->total())->toBe(2);
    });

    it('filters containers with high moisture flag', function () {
        $bill = Bill::factory()->create();
        $container1 = Container::factory()->create();
        $container2 = Container::factory()->create();
        
        $bill->containers()->attach([$container1->id, $container2->id]);

        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container1->id,
            'moisture' => 12.5,
        ]);

        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container2->id,
            'moisture' => 9.5,
        ]);

        $results = $this->repository->findWithFilters([
            'high_moisture' => true,
            'moisture_threshold' => 11.0,
        ]);

        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($container1->id);
    });

    it('handles pagination correctly', function () {
        Container::factory()->count(25)->create();

        $results = $this->repository->findWithFilters(['per_page' => 10]);

        expect($results->total())->toBe(25)
            ->and($results->perPage())->toBe(10)
            ->and($results->count())->toBe(10)
            ->and($results->hasPages())->toBeTrue();
    });
});

describe('ContainerRepository Performance Tests', function () {
    it('handles large datasets efficiently', function () {
        $bill = Bill::factory()->create();
        $containers = Container::factory()->count(100)->create();
        
        $bill->containers()->attach($containers->pluck('id'));

        $startTime = microtime(true);
        $results = $this->repository->findWithFilters(['per_page' => 20]);
        $endTime = microtime(true);

        expect($results->total())->toBe(100)
            ->and($results->count())->toBe(20)
            ->and($endTime - $startTime)->toBeLessThan(1.0); // Should complete within 1 second
    });

    it('optimizes queries with relationship loading', function () {
        $bill = Bill::factory()->create();
        $container = Container::factory()->create();
        $bill->containers()->attach($container->id);
        
        CuttingTest::factory()->count(3)->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
        ]);

        $result = $this->repository->findByIdWithRelations($container->id, ['bills', 'cuttingTests']);

        expect($result->relationLoaded('bills'))->toBeTrue()
            ->and($result->relationLoaded('cuttingTests'))->toBeTrue()
            ->and($result->bills)->toHaveCount(1)
            ->and($result->cuttingTests)->toHaveCount(3);
    });
});

describe('ContainerRepository Relationship Handling', function () {
    it('gets containers by bill ID correctly', function () {
        $bill1 = Bill::factory()->create();
        $bill2 = Bill::factory()->create();
        
        $container1 = Container::factory()->create();
        $container2 = Container::factory()->create();
        $container3 = Container::factory()->create();
        
        $bill1->containers()->attach([$container1->id, $container2->id]);
        $bill2->containers()->attach([$container3->id]);

        $results = $this->repository->getByBillId($bill1->id);

        expect($results)->toHaveCount(2)
            ->and($results->pluck('id'))->toContain($container1->id, $container2->id)
            ->and($results->pluck('id'))->not->toContain($container3->id)
            ->and($results->first()->relationLoaded('bills'))->toBeTrue()
            ->and($results->first()->relationLoaded('cuttingTests'))->toBeTrue();
    });

    it('handles many-to-many relationship with bills correctly', function () {
        $bill1 = Bill::factory()->create();
        $bill2 = Bill::factory()->create();
        $container = Container::factory()->create();
        
        $bill1->containers()->attach($container->id);
        $bill2->containers()->attach($container->id);

        $result = $this->repository->findByIdWithRelations($container->id, ['bills']);

        expect($result->bills)->toHaveCount(2)
            ->and($result->bills->first()->pivot->bill_id)->toBe($bill1->id);
    });
});

describe('ContainerRepository Query Performance and Large Dataset Tests', function () {
    it('handles complex filtering with multiple weight ranges efficiently', function () {
        // Create containers with various weight configurations
        Container::factory()->count(30)->create([
            'w_total' => fake()->numberBetween(20000, 30000),
            'w_gross' => fake()->numberBetween(10000, 15000),
            'w_net' => fake()->numberBetween(8000, 12000),
            'w_truck' => fake()->numberBetween(8000, 12000),
            'w_container' => fake()->numberBetween(2000, 4000),
            'w_tare' => fake()->randomFloat(2, 100, 200),
            'container_condition' => fake()->randomElement(['Good', 'Excellent', 'Damaged']),
            'seal_condition' => fake()->randomElement(['Intact', 'Broken', 'Damaged']),
        ]);

        $startTime = microtime(true);
        
        $results = $this->repository->findWithFilters([
            'w_total_min' => 22000,
            'w_total_max' => 28000,
            'w_gross_min' => 11000,
            'w_gross_max' => 14000,
            'container_condition' => 'Good',
            'per_page' => 15,
        ]);
        
        $endTime = microtime(true);

        expect($results->total())->toBeGreaterThanOrEqual(0)
            ->and($endTime - $startTime)->toBeLessThan(1.5); // Should complete within 1.5 seconds
    });

    it('optimizes container number search with ISO format validation', function () {
        // Create containers with valid ISO format numbers
        $validContainers = Container::factory()->count(20)->create([
            'container_number' => fn() => fake()->regexify('[A-Z]{4}[0-9]{7}'),
        ]);
        
        // Create containers with invalid format numbers
        Container::factory()->count(10)->create([
            'container_number' => fn() => fake()->regexify('[A-Z]{3}[0-9]{6}'), // Invalid format
        ]);

        $startTime = microtime(true);
        
        // Test finding by valid container number
        $testContainer = $validContainers->first();
        $result = $this->repository->findByContainerNumberOrId($testContainer->container_number);
        
        $endTime = microtime(true);

        expect($result)->not->toBeNull()
            ->and($result->id)->toBe($testContainer->id)
            ->and($endTime - $startTime)->toBeLessThan(0.1); // Should be very fast
    });

    it('tests weight discrepancy detection accuracy with edge cases', function () {
        // Container with exact weight calculation (no discrepancy)
        $perfectContainer = Container::factory()->create([
            'w_total' => 25000,
            'w_truck' => 10000,
            'w_container' => 3000,
            'w_gross' => 12000, // 25000 - 10000 - 3000 = 12000
            'w_net' => 11850, // Required field
        ]);

        // Container with 5kg discrepancy (exactly at threshold)
        $thresholdContainer = Container::factory()->create([
            'w_total' => 25000,
            'w_truck' => 10000,
            'w_container' => 3000,
            'w_gross' => 12005, // Expected: 12000, actual: 12005, discrepancy: 5kg
            'w_net' => 11855, // Required field
        ]);

        // Container with 6kg discrepancy (above threshold)
        $discrepancyContainer = Container::factory()->create([
            'w_total' => 25000,
            'w_truck' => 10000,
            'w_container' => 3000,
            'w_gross' => 11993, // Expected: 12000, actual: 11993, discrepancy: 7kg > 5kg threshold
            'w_net' => 11843, // Required field
        ]);

        $results = $this->repository->getContainersWithWeightDiscrepancies();

        // Debug: Check the actual discrepancy calculation
        $expectedGross = $discrepancyContainer->w_total - $discrepancyContainer->w_truck - $discrepancyContainer->w_container;
        $actualDiscrepancy = abs($discrepancyContainer->w_gross - $expectedGross);
        
        expect($actualDiscrepancy)->toBeGreaterThan(5)
            ->and($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($discrepancyContainer->id);
    });

    it('validates moisture alert queries with multiple thresholds', function () {
        $bill = Bill::factory()->create();
        $containers = Container::factory()->count(5)->create();
        $bill->containers()->attach($containers->pluck('id'));

        // Create cutting tests with various moisture levels
        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $containers[0]->id,
            'moisture' => 15.2, // High
        ]);
        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $containers[1]->id,
            'moisture' => 11.1, // Just above 11%
        ]);
        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $containers[2]->id,
            'moisture' => 10.9, // Just below 11%
        ]);
        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $containers[3]->id,
            'moisture' => 8.5, // Low
        ]);

        // Test default threshold (11.0)
        $defaultResults = $this->repository->getContainersWithMoistureAlerts();
        expect($defaultResults)->toHaveCount(2);

        // Test custom threshold (12.0)
        $customResults = $this->repository->getContainersWithMoistureAlerts(12.0);
        expect($customResults)->toHaveCount(1);

        // Test very high threshold (16.0)
        $highResults = $this->repository->getContainersWithMoistureAlerts(16.0);
        expect($highResults)->toHaveCount(0);
    });

    it('verifies condition status filtering with multiple criteria', function () {
        // Create containers with various condition combinations
        $goodIntact = Container::factory()->create([
            'container_condition' => 'Good',
            'seal_condition' => 'Intact',
        ]);
        $goodBroken = Container::factory()->create([
            'container_condition' => 'Good',
            'seal_condition' => 'Broken',
        ]);
        $excellentIntact = Container::factory()->create([
            'container_condition' => 'Excellent',
            'seal_condition' => 'Intact',
        ]);
        $damagedDamaged = Container::factory()->create([
            'container_condition' => 'Damaged',
            'seal_condition' => 'Damaged',
        ]);

        // Test multiple container conditions
        $results = $this->repository->getContainersByConditionStatus(
            ['Good', 'Excellent'],
            []
        );
        expect($results)->toHaveCount(3)
            ->and($results->pluck('id'))->toContain($goodIntact->id, $goodBroken->id, $excellentIntact->id);

        // Test multiple seal conditions
        $results = $this->repository->getContainersByConditionStatus(
            [],
            ['Intact', 'Broken']
        );
        expect($results)->toHaveCount(3)
            ->and($results->pluck('id'))->toContain($goodIntact->id, $goodBroken->id, $excellentIntact->id);

        // Test both conditions
        $results = $this->repository->getContainersByConditionStatus(
            ['Good'],
            ['Intact']
        );
        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($goodIntact->id);
    });

    it('tests comprehensive filtering with date ranges', function () {
        $bill = Bill::factory()->create(['bill_number' => 'DATE-TEST']);
        
        $oldContainer = Container::factory()->create([
            'created_at' => '2024-01-15',
        ]);
        $recentContainer = Container::factory()->create([
            'created_at' => '2024-06-15',
        ]);
        $futureContainer = Container::factory()->create([
            'created_at' => '2024-12-15',
        ]);

        $bill->containers()->attach([$oldContainer->id, $recentContainer->id, $futureContainer->id]);

        // Test date range filtering
        $results = $this->repository->findWithFilters([
            'bill_number' => 'DATE-TEST',
            'created_from' => '2024-06-01',
            'created_to' => '2024-12-01',
        ]);

        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($recentContainer->id);
    });

    it('handles large dataset pagination efficiently', function () {
        $bill = Bill::factory()->create();
        $containers = Container::factory()->count(100)->create();
        $bill->containers()->attach($containers->pluck('id'));

        // Create cutting tests for some containers
        foreach ($containers->take(30) as $container) {
            CuttingTest::factory()->create([
                'bill_id' => $bill->id,
                'container_id' => $container->id,
                'moisture' => fake()->randomFloat(1, 8.0, 15.0),
            ]);
        }

        $startTime = microtime(true);
        
        // Test pagination with relationships
        $page1 = $this->repository->findWithFilters(['per_page' => 25]);
        $page2 = $this->repository->findWithFilters(['per_page' => 25, 'page' => 2]);
        
        $endTime = microtime(true);

        expect($page1->total())->toBe(100)
            ->and($page1->count())->toBe(25)
            ->and($page2->count())->toBe(25)
            ->and($page1->hasPages())->toBeTrue()
            ->and($endTime - $startTime)->toBeLessThan(2.0); // Should handle pagination efficiently
    });
});