<?php

declare(strict_types=1);

use App\Enums\CuttingTestType;
use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use App\Repositories\CuttingTestRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->repository = app(CuttingTestRepository::class);
});

describe('CuttingTestRepository Basic CRUD Operations', function () {
    it('creates a cutting test with all database fields', function () {
        $bill = Bill::factory()->create();
        $container = Container::factory()->create();

        $data = [
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'type' => CuttingTestType::ContainerCut->value,
            'sample_weight' => 1000,
            'moisture' => 11.5,
            'w_sample_after_cut' => 995,
            'w_defective_nut' => 100,
            'w_defective_kernel' => 30,
            'w_reject_nut' => 50,
            'w_good_kernel' => 700,
            'outturn_rate' => 48.5,
            'note' => 'Test cutting test',
        ];

        $cuttingTest = $this->repository->create($data);

        expect($cuttingTest->exists)->toBeTrue()
            ->and($cuttingTest->bill_id)->toBe($bill->id)
            ->and($cuttingTest->container_id)->toBe($container->id)
            ->and($cuttingTest->type)->toBe(CuttingTestType::ContainerCut->value)
            ->and($cuttingTest->sample_weight)->toBe(1000)
            ->and($cuttingTest->moisture)->toBe('11.5')
            ->and($cuttingTest->outturn_rate)->toBe('48.50');
    });

    it('finds cutting test by id with relations', function () {
        $bill = Bill::factory()->create();
        $container = Container::factory()->create();
        $cuttingTest = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
        ]);

        $result = $this->repository->findByIdWithRelations($cuttingTest->id, ['bill', 'container']);

        expect($result)->not->toBeNull()
            ->and($result->id)->toBe($cuttingTest->id)
            ->and($result->relationLoaded('bill'))->toBeTrue()
            ->and($result->relationLoaded('container'))->toBeTrue()
            ->and($result->bill->id)->toBe($bill->id)
            ->and($result->container->id)->toBe($container->id);
    });

    it('updates cutting test with new field values', function () {
        $bill = Bill::factory()->create();
        $cuttingTest = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'moisture' => 10.5,
            'outturn_rate' => 45.0,
        ]);

        $updated = $this->repository->update($cuttingTest, [
            'moisture' => 12.0,
            'outturn_rate' => 47.5,
            'note' => 'Updated note',
        ]);

        expect($updated)->toBeTrue();
        $cuttingTest->refresh();
        expect($cuttingTest->moisture)->toBe('12.0')
            ->and($cuttingTest->outturn_rate)->toBe('47.50')
            ->and($cuttingTest->note)->toBe('Updated note');
    });

    it('soft deletes cutting test', function () {
        $bill = Bill::factory()->create();
        $cuttingTest = CuttingTest::factory()->create(['bill_id' => $bill->id]);

        $deleted = $this->repository->delete($cuttingTest);

        expect($deleted)->toBeTrue()
            ->and(CuttingTest::find($cuttingTest->id))->toBeNull()
            ->and(CuttingTest::withTrashed()->find($cuttingTest->id)->trashed())->toBeTrue();
    });
});

describe('CuttingTestRepository Type-Specific Queries', function () {
    it('gets cutting tests by bill ID', function () {
        $bill1 = Bill::factory()->create();
        $bill2 = Bill::factory()->create();
        $container = Container::factory()->create();

        $test1 = CuttingTest::factory()->create([
            'bill_id' => $bill1->id,
            'container_id' => $container->id,
            'type' => CuttingTestType::ContainerCut->value,
        ]);
        $test2 = CuttingTest::factory()->create([
            'bill_id' => $bill1->id,
            'container_id' => null,
            'type' => CuttingTestType::FinalFirstCut->value,
        ]);
        $test3 = CuttingTest::factory()->create([
            'bill_id' => $bill2->id,
            'container_id' => null,
            'type' => CuttingTestType::FinalFirstCut->value,
        ]);

        $results = $this->repository->getByBillId($bill1->id);

        expect($results)->toHaveCount(2)
            ->and($results->pluck('id'))->toContain($test1->id, $test2->id)
            ->and($results->pluck('id'))->not->toContain($test3->id)
            ->and($results->first()->relationLoaded('container'))->toBeTrue();
    });

    it('gets cutting tests by container ID', function () {
        $bill = Bill::factory()->create();
        $container1 = Container::factory()->create();
        $container2 = Container::factory()->create();

        $test1 = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container1->id,
        ]);
        $test2 = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container1->id,
        ]);
        $test3 = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container2->id,
        ]);

        $results = $this->repository->getByContainerId($container1->id);

        expect($results)->toHaveCount(2)
            ->and($results->pluck('id'))->toContain($test1->id, $test2->id)
            ->and($results->pluck('id'))->not->toContain($test3->id);
    });

    it('gets final samples by bill ID', function () {
        $bill = Bill::factory()->create();
        $container = Container::factory()->create();

        $finalFirst = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => null,
            'type' => CuttingTestType::FinalFirstCut->value,
        ]);
        $finalSecond = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => null,
            'type' => CuttingTestType::FinalSecondCut->value,
        ]);
        $containerTest = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'type' => CuttingTestType::ContainerCut->value,
        ]);

        $results = $this->repository->getFinalSamplesByBillId($bill->id);

        expect($results)->toHaveCount(2)
            ->and($results->pluck('id'))->toContain($finalFirst->id, $finalSecond->id)
            ->and($results->pluck('id'))->not->toContain($containerTest->id);
    });

    it('gets container tests by bill ID', function () {
        $bill = Bill::factory()->create();
        $container = Container::factory()->create();

        $containerTest = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'type' => CuttingTestType::ContainerCut->value,
        ]);
        $finalTest = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => null,
            'type' => CuttingTestType::FinalFirstCut->value,
        ]);

        $results = $this->repository->getContainerTestsByBillId($bill->id);

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($containerTest->id)
            ->and($results->first()->relationLoaded('container'))->toBeTrue();
    });

    it('gets tests by specific type with filters', function () {
        $bill1 = Bill::factory()->create();
        $bill2 = Bill::factory()->create();
        $container = Container::factory()->create();

        $test1 = CuttingTest::factory()->create([
            'bill_id' => $bill1->id,
            'container_id' => $container->id,
            'type' => CuttingTestType::ContainerCut->value,
            'moisture' => 12.5,
            'outturn_rate' => 48.0,
        ]);
        $test2 = CuttingTest::factory()->create([
            'bill_id' => $bill2->id,
            'container_id' => $container->id,
            'type' => CuttingTestType::ContainerCut->value,
            'moisture' => 9.5,
            'outturn_rate' => 52.0,
        ]);
        $test3 = CuttingTest::factory()->create([
            'bill_id' => $bill1->id,
            'container_id' => null,
            'type' => CuttingTestType::FinalFirstCut->value,
            'moisture' => 11.0,
        ]);

        // Test filtering by type and bill ID
        $results = $this->repository->getByTypeWithFilters(
            CuttingTestType::ContainerCut->value,
            ['bill_id' => $bill1->id]
        );

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($test1->id);

        // Test filtering by type and moisture range
        $results = $this->repository->getByTypeWithFilters(
            CuttingTestType::ContainerCut->value,
            ['moisture_min' => 10.0, 'moisture_max' => 13.0]
        );

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($test1->id);

        // Test filtering by type and outturn rate range
        $results = $this->repository->getByTypeWithFilters(
            CuttingTestType::ContainerCut->value,
            ['outturn_rate_min' => 50.0, 'outturn_rate_max' => 55.0]
        );

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($test2->id);
    });
});

describe('CuttingTestRepository Moisture and Alert Queries', function () {
    it('gets tests with high moisture', function () {
        $bill = Bill::factory()->create();
        $container = Container::factory()->create();

        $highMoistureTest = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'moisture' => 13.5,
        ]);
        $normalMoistureTest = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'moisture' => 9.5,
        ]);

        $results = $this->repository->getTestsWithHighMoisture(11.0);

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($highMoistureTest->id)
            ->and($results->first()->relationLoaded('bill'))->toBeTrue()
            ->and($results->first()->relationLoaded('container'))->toBeTrue();
    });

    it('gets tests with validation alerts', function () {
        $bill = Bill::factory()->create();
        $container = Container::factory()->create();

        // Test with sample weight discrepancy > 5
        $alertTest1 = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'sample_weight' => 1000,
            'w_sample_after_cut' => 990, // Discrepancy: 10 > 5
            'w_defective_nut' => 100,
            'w_defective_kernel' => 30,
            'w_reject_nut' => 50,
            'w_good_kernel' => 700,
        ]);

        // Test with defective kernel discrepancy > 5
        $alertTest2 = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'sample_weight' => 1000,
            'w_sample_after_cut' => 998,
            'w_defective_nut' => 132, // 132/3.3 = 40, but w_defective_kernel = 30, discrepancy = 10 > 5
            'w_defective_kernel' => 30,
            'w_reject_nut' => 50,
            'w_good_kernel' => 700,
        ]);

        // Test with no alerts
        $normalTest = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'sample_weight' => 1000,
            'w_sample_after_cut' => 998,
            'w_defective_nut' => 99,
            'w_defective_kernel' => 30,
            'w_reject_nut' => 50,
            'w_good_kernel' => 700,
        ]);

        $results = $this->repository->getTestsWithValidationAlerts();

        expect($results)->toHaveCount(2)
            ->and($results->pluck('id'))->toContain($alertTest1->id, $alertTest2->id)
            ->and($results->pluck('id'))->not->toContain($normalTest->id);
    });

    it('gets tests by specific validation alert type', function () {
        $bill = Bill::factory()->create();
        $container = Container::factory()->create();

        // Sample weight discrepancy alert
        $sampleWeightAlert = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'sample_weight' => 1000,
            'w_sample_after_cut' => 990, // Discrepancy: 10 > 5
            'w_defective_nut' => 99,
            'w_defective_kernel' => 30,
            'w_reject_nut' => 50,
            'w_good_kernel' => 700,
        ]);

        // Defective kernel discrepancy alert
        $defectiveKernelAlert = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'sample_weight' => 1000,
            'w_sample_after_cut' => 998,
            'w_defective_nut' => 132, // 132/3.3 = 40, but w_defective_kernel = 30, discrepancy = 10 > 5
            'w_defective_kernel' => 30,
            'w_reject_nut' => 50,
            'w_good_kernel' => 700,
        ]);

        $sampleWeightResults = $this->repository->getTestsByValidationAlertType('sample_weight_discrepancy');
        expect($sampleWeightResults)->toHaveCount(1)
            ->and($sampleWeightResults->first()->id)->toBe($sampleWeightAlert->id);

        $defectiveKernelResults = $this->repository->getTestsByValidationAlertType('defective_kernel_discrepancy');
        expect($defectiveKernelResults)->toHaveCount(1)
            ->and($defectiveKernelResults->first()->id)->toBe($defectiveKernelAlert->id);
    });
});

describe('CuttingTestRepository Advanced Filtering', function () {
    it('filters cutting tests with comprehensive filters', function () {
        $bill1 = Bill::factory()->create(['bill_number' => 'BL-001']);
        $bill2 = Bill::factory()->create(['bill_number' => 'BL-002']);
        $container1 = Container::factory()->create(['container_number' => 'CONT1234567']);
        $container2 = Container::factory()->create(['container_number' => 'CONT7654321']);

        $test1 = CuttingTest::factory()->create([
            'bill_id' => $bill1->id,
            'container_id' => $container1->id,
            'type' => CuttingTestType::ContainerCut->value,
            'moisture' => 12.5,
            'outturn_rate' => 48.0,
            'sample_weight' => 1000,
            'created_at' => '2024-01-15',
        ]);

        $test2 = CuttingTest::factory()->create([
            'bill_id' => $bill2->id,
            'container_id' => null,
            'type' => CuttingTestType::FinalFirstCut->value,
            'moisture' => 9.5,
            'outturn_rate' => 52.0,
            'sample_weight' => 800,
            'created_at' => '2024-02-15',
        ]);

        // Test bill number filter
        $results = $this->repository->findWithFilters(['bill_number' => 'BL-001']);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($test1->id);

        // Test container number filter
        $results = $this->repository->findWithFiltersAndAlerts(['container_number' => 'CONT123']);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($test1->id);

        // Test test type filter
        $results = $this->repository->findWithFilters(['test_type' => 'final']);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($test2->id);

        $results = $this->repository->findWithFilters(['test_type' => 'container']);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($test1->id);

        // Test moisture range filter
        $results = $this->repository->findWithFilters([
            'moisture_min' => 10.0,
            'moisture_max' => 15.0,
        ]);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($test1->id);

        // Test outturn rate range filter
        $results = $this->repository->findWithFiltersAndAlerts([
            'outturn_rate_min' => 50.0,
            'outturn_rate_max' => 55.0,
        ]);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($test2->id);

        // Test sample weight range filter
        $results = $this->repository->findWithFiltersAndAlerts([
            'sample_weight_min' => 900,
            'sample_weight_max' => 1100,
        ]);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($test1->id);

        // Test date range filter
        $results = $this->repository->findWithFilters([
            'date_from' => '2024-01-01',
            'date_to' => '2024-01-31',
        ]);
        expect($results->total())->toBe(1)
            ->and($results->first()->id)->toBe($test1->id);
    });

    it('handles pagination correctly', function () {
        $bill = Bill::factory()->create();
        CuttingTest::factory()->count(25)->create(['bill_id' => $bill->id]);

        $results = $this->repository->findWithFilters(['per_page' => 10]);

        expect($results->total())->toBe(25)
            ->and($results->perPage())->toBe(10)
            ->and($results->count())->toBe(10)
            ->and($results->hasPages())->toBeTrue();
    });

    it('filters with validation alerts flag', function () {
        $bill = Bill::factory()->create();
        $container = Container::factory()->create();

        // Test with validation alert (sample weight discrepancy only)
        $alertTest = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'sample_weight' => 1000,
            'w_sample_after_cut' => 990, // Discrepancy: 10 > 5
            'w_defective_nut' => 99, // 99/3.3 = 30, w_defective_kernel = 30, no discrepancy
            'w_defective_kernel' => 30,
            'w_reject_nut' => 50,
            'w_good_kernel' => 270, // (1000-50-99)/3.3 = 257.9, discrepancy = 257.9-270 = -12.1 < 10, no alert
        ]);

        // Test without validation alert
        $normalTest = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'sample_weight' => 1000,
            'w_sample_after_cut' => 998, // Discrepancy: 2 <= 5
            'w_defective_nut' => 99, // 99/3.3 = 30, w_defective_kernel = 30, no discrepancy
            'w_defective_kernel' => 30,
            'w_reject_nut' => 50,
            'w_good_kernel' => 270, // (1000-50-99)/3.3 = 257.9, discrepancy = 257.9-270 = -12.1 < 10, no alert
        ]);

        $results = $this->repository->findWithFiltersAndAlerts(['has_validation_alerts' => true]);

        expect($results->total())->toBeGreaterThanOrEqual(1)
            ->and($results->first()->has_validation_alerts)->toBeTrue();
    });
});

describe('CuttingTestRepository Efficient Relations', function () {
    it('gets final samples with optimized relations', function () {
        $bill = Bill::factory()->create([
            'bill_number' => 'BL-001',
            'seller' => 'Test Seller',
            'origin' => 'Vietnam',
        ]);

        $finalFirst = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => null,
            'type' => CuttingTestType::FinalFirstCut->value,
        ]);
        $finalSecond = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => null,
            'type' => CuttingTestType::FinalSecondCut->value,
        ]);

        $results = $this->repository->getFinalSamplesWithRelations($bill->id);

        expect($results)->toHaveCount(2)
            ->and($results->pluck('id'))->toContain($finalFirst->id, $finalSecond->id)
            ->and($results->first()->relationLoaded('bill'))->toBeTrue()
            ->and($results->first()->bill->bill_number)->toBe('BL-001');
    });

    it('gets container tests with optimized relations', function () {
        $bill = Bill::factory()->create([
            'bill_number' => 'BL-002',
            'seller' => 'Test Seller',
        ]);
        $container = Container::factory()->create([
            'container_number' => 'CONT1234567',
            'truck' => 'TRK-001',
        ]);

        $containerTest = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'type' => CuttingTestType::ContainerCut->value,
        ]);

        $results = $this->repository->getContainerTestsWithRelations($bill->id);

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($containerTest->id)
            ->and($results->first()->relationLoaded('bill'))->toBeTrue()
            ->and($results->first()->relationLoaded('container'))->toBeTrue()
            ->and($results->first()->bill->bill_number)->toBe('BL-002')
            ->and($results->first()->container->container_number)->toBe('CONT1234567');
    });

    it('gets tests for reporting with efficient joins', function () {
        $bill = Bill::factory()->create([
            'bill_number' => 'BL-003',
            'seller' => 'Report Seller',
            'buyer' => 'Report Buyer',
            'origin' => 'Indonesia',
        ]);
        $container = Container::factory()->create([
            'container_number' => 'REPT1234567',
            'truck' => 'RPT-001',
        ]);

        $test = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'type' => CuttingTestType::ContainerCut->value,
            'moisture' => 12.0,
        ]);

        $results = $this->repository->getTestsForReporting([
            'bill_id' => $bill->id,
            'type' => CuttingTestType::ContainerCut->value,
        ]);

        expect($results)->toHaveCount(1);
        $result = $results->first();
        expect($result->id)->toBe($test->id)
            ->and($result->bill_number)->toBe('BL-003')
            ->and($result->seller)->toBe('Report Seller')
            ->and($result->buyer)->toBe('Report Buyer')
            ->and($result->origin)->toBe('Indonesia')
            ->and($result->container_number)->toBe('REPT1234567')
            ->and($result->truck)->toBe('RPT-001');
    });
});

describe('CuttingTestRepository Performance Tests', function () {
    it('handles large datasets efficiently', function () {
        $bill = Bill::factory()->create();
        $container = Container::factory()->create();
        CuttingTest::factory()->count(100)->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
        ]);

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
        $cuttingTest = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
        ]);

        $result = $this->repository->findByIdWithRelations($cuttingTest->id, ['bill', 'container']);

        expect($result->relationLoaded('bill'))->toBeTrue()
            ->and($result->relationLoaded('container'))->toBeTrue()
            ->and($result->bill->id)->toBe($bill->id)
            ->and($result->container->id)->toBe($container->id);
    });

    it('handles complex filtering efficiently', function () {
        $bill = Bill::factory()->create(['bill_number' => 'PERF-001']);
        $container = Container::factory()->create(['container_number' => 'PERF1234567']);
        
        CuttingTest::factory()->count(50)->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'moisture' => fake()->randomFloat(1, 8.0, 15.0),
        ]);

        $startTime = microtime(true);
        $results = $this->repository->findWithFiltersAndAlerts([
            'bill_number' => 'PERF',
            'per_page' => 10,
        ]);
        $endTime = microtime(true);

        expect($results->total())->toBeGreaterThan(0)
            ->and($endTime - $startTime)->toBeLessThan(1.0); // Should complete within 1 second
    });

    it('tests validation alert detection performance with large datasets', function () {
        $bill = Bill::factory()->create();
        $container = Container::factory()->create();
        
        // Create tests with various validation scenarios
        CuttingTest::factory()->count(30)->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'sample_weight' => 1000,
            'w_sample_after_cut' => fake()->numberBetween(990, 998), // Some will have discrepancies > 5
            'w_defective_nut' => fake()->numberBetween(90, 110),
            'w_defective_kernel' => 30,
            'w_reject_nut' => 50,
            'w_good_kernel' => fake()->numberBetween(260, 280),
        ]);

        $startTime = microtime(true);
        $results = $this->repository->getTestsWithValidationAlerts();
        $endTime = microtime(true);

        expect($results->count())->toBeGreaterThanOrEqual(0)
            ->and($endTime - $startTime)->toBeLessThan(2.0); // Should complete within 2 seconds
    });

    it('optimizes reporting queries with joins', function () {
        $bills = Bill::factory()->count(10)->create([
            'bill_number' => fn() => 'RPT-' . fake()->unique()->numberBetween(1000, 9999),
            'seller' => fake()->company(),
            'buyer' => fake()->company(),
            'origin' => fake()->randomElement(['Vietnam', 'Indonesia', 'India']),
        ]);
        
        $containers = Container::factory()->count(20)->create([
            'container_number' => fn() => fake()->regexify('[A-Z]{4}[0-9]{7}'),
            'truck' => fn() => 'TRK-' . fake()->numberBetween(100, 999),
        ]);

        // Create cutting tests for reporting
        foreach ($bills as $bill) {
            foreach ($containers->take(2) as $container) {
                CuttingTest::factory()->create([
                    'bill_id' => $bill->id,
                    'container_id' => $container->id,
                    'type' => CuttingTestType::ContainerCut->value,
                    'moisture' => fake()->randomFloat(1, 8.0, 15.0),
                ]);
            }
        }

        $startTime = microtime(true);
        $results = $this->repository->getTestsForReporting();
        $endTime = microtime(true);

        expect($results->count())->toBeGreaterThan(0)
            ->and($endTime - $startTime)->toBeLessThan(1.5) // Should be efficient with joins
            ->and($results->first()->bill_number)->toContain('RPT-')
            ->and($results->first()->container_number)->toMatch('/^[A-Z]{4}[0-9]{7}$/');
    });

    it('validates enhanced filtering with alerts performance', function () {
        $bill = Bill::factory()->create(['bill_number' => 'ALERT-001']);
        $container = Container::factory()->create(['container_number' => 'ALRT1234567']);
        
        // Create tests with validation alerts
        CuttingTest::factory()->count(25)->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'sample_weight' => 1000,
            'w_sample_after_cut' => fake()->numberBetween(985, 995), // Some will trigger alerts
            'w_defective_nut' => fake()->numberBetween(95, 105),
            'w_defective_kernel' => 30,
            'w_reject_nut' => 50,
            'w_good_kernel' => fake()->numberBetween(265, 275),
            'moisture' => fake()->randomFloat(1, 9.0, 14.0),
        ]);

        $startTime = microtime(true);
        $results = $this->repository->findWithFiltersAndAlerts([
            'bill_number' => 'ALERT',
            'has_validation_alerts' => true,
            'per_page' => 15,
        ]);
        $endTime = microtime(true);

        expect($results->total())->toBeGreaterThanOrEqual(0)
            ->and($endTime - $startTime)->toBeLessThan(2.0); // Should handle alert filtering efficiently
    });

    it('tests type-specific filtering performance', function () {
        $bill = Bill::factory()->create();
        $container = Container::factory()->create();
        
        // Create various types of cutting tests
        CuttingTest::factory()->count(20)->create([
            'bill_id' => $bill->id,
            'container_id' => $container->id,
            'type' => CuttingTestType::ContainerCut->value,
        ]);
        
        CuttingTest::factory()->count(15)->create([
            'bill_id' => $bill->id,
            'container_id' => null,
            'type' => CuttingTestType::FinalFirstCut->value,
        ]);
        
        CuttingTest::factory()->count(10)->create([
            'bill_id' => $bill->id,
            'container_id' => null,
            'type' => CuttingTestType::FinalSecondCut->value,
        ]);

        $startTime = microtime(true);
        
        // Test container type filtering
        $containerResults = $this->repository->getByTypeWithFilters(
            CuttingTestType::ContainerCut->value,
            ['bill_id' => $bill->id]
        );
        
        // Test final sample filtering
        $finalResults = $this->repository->getByTypeWithFilters(
            CuttingTestType::FinalFirstCut->value,
            ['bill_id' => $bill->id]
        );
        
        $endTime = microtime(true);

        expect($containerResults)->toHaveCount(20)
            ->and($finalResults)->toHaveCount(15)
            ->and($endTime - $startTime)->toBeLessThan(1.0); // Should filter efficiently by type
    });

    it('handles concurrent relationship loading efficiently', function () {
        $bills = Bill::factory()->count(5)->create();
        $containers = Container::factory()->count(10)->create();
        
        foreach ($bills as $index => $bill) {
            foreach ($containers->slice($index * 2, 2) as $container) {
                CuttingTest::factory()->count(3)->create([
                    'bill_id' => $bill->id,
                    'container_id' => $container->id,
                    'type' => CuttingTestType::ContainerCut->value,
                ]);
            }
        }

        $startTime = microtime(true);
        
        // Test multiple relationship loading operations
        $results1 = $this->repository->getFinalSamplesWithRelations($bills->first()->id);
        $results2 = $this->repository->getContainerTestsWithRelations($bills->first()->id);
        $results3 = $this->repository->getTestsForReporting(['bill_id' => $bills->first()->id]);
        
        $endTime = microtime(true);

        expect($results2)->toHaveCount(6) // 2 containers * 3 tests each
            ->and($endTime - $startTime)->toBeLessThan(1.5); // Should handle multiple queries efficiently
    });
});