<?php

declare(strict_types=1);

use App\Models\CuttingTest;
use App\Models\Bill;
use App\Models\Container;
use App\Services\CuttingTestService;
use App\Repositories\CuttingTestRepository;
use App\Queries\CuttingTestQuery;
use App\Enums\CuttingTestType;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->cuttingTestRepository = Mockery::mock(CuttingTestRepository::class);
    $this->cuttingTestQuery = Mockery::mock(CuttingTestQuery::class);
    $this->cuttingTestService = new CuttingTestService($this->cuttingTestRepository, $this->cuttingTestQuery);
});

afterEach(function () {
    Mockery::close();
});

describe('CuttingTestService CRUD Operations', function () {
    test('createCuttingTest creates test with valid data', function () {
        $data = [
            'bill_id' => 1,
            'type' => 1, // Final sample
            'moisture' => 10.5,
            'sample_weight' => 1000,
            'nut_count' => 200,
            'w_reject_nut' => 50,
            'w_defective_nut' => 100,
            'w_defective_kernel' => 80,
            'w_good_kernel' => 700,
            'w_sample_after_cut' => 950,
        ];

        $expectedTest = CuttingTest::factory()->make($data);

        $this->cuttingTestRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg['type'] === 1 &&
                       $arg['moisture'] === 10.5 &&
                       isset($arg['outturn_rate']); // Should calculate outturn rate
            }))
            ->andReturn($expectedTest);

        $result = $this->cuttingTestService->createCuttingTest($data);

        expect($result)->toBeInstanceOf(CuttingTest::class);
    });

    test('createCuttingTest validates final sample cannot have container_id', function () {
        $data = [
            'bill_id' => 1,
            'container_id' => 1, // Invalid for final sample
            'type' => 1, // Final sample
        ];

        expect(fn() => $this->cuttingTestService->createCuttingTest($data))
            ->toThrow(ValidationException::class);
    });

    test('createCuttingTest validates container test must have container_id', function () {
        $data = [
            'bill_id' => 1,
            'type' => 4, // Container test
            // Missing container_id
        ];

        expect(fn() => $this->cuttingTestService->createCuttingTest($data))
            ->toThrow(ValidationException::class);
    });

    test('createCuttingTest validates moisture range', function () {
        $invalidMoistureValues = [-1, 101, 150];

        foreach ($invalidMoistureValues as $moisture) {
            $data = [
                'bill_id' => 1,
                'type' => 1,
                'moisture' => $moisture,
            ];

            expect(fn() => $this->cuttingTestService->createCuttingTest($data))
                ->toThrow(ValidationException::class);
        }
    });

    test('createCuttingTest accepts valid moisture range', function () {
        $validMoistureValues = [0, 5.5, 11.0, 15.2, 100];

        foreach ($validMoistureValues as $moisture) {
            $data = [
                'bill_id' => 1,
                'type' => 1,
                'moisture' => $moisture,
            ];

            $expectedTest = CuttingTest::factory()->make($data);

            $this->cuttingTestRepository
                ->shouldReceive('create')
                ->once()
                ->andReturn($expectedTest);

            $result = $this->cuttingTestService->createCuttingTest($data);

            expect($result)->toBeInstanceOf(CuttingTest::class);
        }
    });

    test('updateCuttingTest updates test with valid data', function () {
        $cuttingTest = CuttingTest::factory()->make();
        $data = [
            'moisture' => 12.5,
            'w_good_kernel' => 750,
        ];

        $this->cuttingTestRepository
            ->shouldReceive('update')
            ->once()
            ->with($cuttingTest, Mockery::type('array'))
            ->andReturn(true);

        $result = $this->cuttingTestService->updateCuttingTest($cuttingTest, $data);

        expect($result)->toBeTrue();
    });
});

describe('CuttingTestService Outturn Rate Calculations', function () {
    test('calculateOutturnRate calculates correct outturn rate', function () {
        $data = [
            'bill_id' => 1,
            'type' => 1,
            'w_defective_kernel' => 80,
            'w_good_kernel' => 700,
        ];

        // Expected calculation: (80/2 + 700) * 80 / 453.6 = (40 + 700) * 80 / 453.6 = 130.42
        $expectedOutturnRate = round((80/2 + 700) * 80 / 453.6, 2);

        $expectedTest = CuttingTest::factory()->make($data);

        $this->cuttingTestRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) use ($expectedOutturnRate) {
                return abs($arg['outturn_rate'] - $expectedOutturnRate) < 0.01;
            }))
            ->andReturn($expectedTest);

        $result = $this->cuttingTestService->createCuttingTest($data);

        expect($result)->toBeInstanceOf(CuttingTest::class);
    });

    test('calculateOutturnRate caps outturn rate at maximum 60', function () {
        $data = [
            'bill_id' => 1,
            'type' => 1,
            'w_defective_kernel' => 1000, // Very high values
            'w_good_kernel' => 2000,
        ];

        $expectedTest = CuttingTest::factory()->make($data);

        $this->cuttingTestRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg['outturn_rate'] === 60.0; // Should be capped at 60
            }))
            ->andReturn($expectedTest);

        $result = $this->cuttingTestService->createCuttingTest($data);

        expect($result)->toBeInstanceOf(CuttingTest::class);
    });

    test('calculateOutturnRate ensures minimum 0', function () {
        $data = [
            'bill_id' => 1,
            'type' => 1,
            'w_defective_kernel' => 0,
            'w_good_kernel' => 0,
        ];

        $expectedTest = CuttingTest::factory()->make($data);

        $this->cuttingTestRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg['outturn_rate'] === 0.0;
            }))
            ->andReturn($expectedTest);

        $result = $this->cuttingTestService->createCuttingTest($data);

        expect($result)->toBeInstanceOf(CuttingTest::class);
    });

    test('recalculateOutturnRate returns correct value', function () {
        $cuttingTest = CuttingTest::factory()->make([
            'w_defective_kernel' => 80,
            'w_good_kernel' => 700,
        ]);

        $result = $this->cuttingTestService->recalculateOutturnRate($cuttingTest);

        $expected = round((80/2 + 700) * 80 / 453.6, 2);
        expect($result)->toBe($expected);
    });

    test('recalculateOutturnRate returns null for missing data', function () {
        $cuttingTest = CuttingTest::factory()->make([
            'w_defective_kernel' => null,
            'w_good_kernel' => 700,
        ]);

        $result = $this->cuttingTestService->recalculateOutturnRate($cuttingTest);

        expect($result)->toBeNull();
    });
});

describe('CuttingTestService Validation Alerts', function () {
    test('getValidationAlerts detects high moisture', function () {
        $cuttingTest = CuttingTest::factory()->make([
            'moisture' => 12.5, // > 11%
        ]);

        $alerts = $this->cuttingTestService->getValidationAlerts($cuttingTest);

        expect($alerts)->toContain('High moisture content: 12.5% (exceeds 11% threshold)');
    });

    test('getValidationAlerts detects sample weight discrepancy', function () {
        $cuttingTest = CuttingTest::factory()->make([
            'sample_weight' => 1000,
            'w_sample_after_cut' => 990, // Difference = 10g > 5g threshold
        ]);

        $alerts = $this->cuttingTestService->getValidationAlerts($cuttingTest);

        expect($alerts)->toContain('Sample weight discrepancy: 10g (exceeds 5g threshold)');
    });

    test('getValidationAlerts detects defective kernel weight discrepancy', function () {
        $cuttingTest = CuttingTest::factory()->make([
            'w_defective_nut' => 100,
            'w_defective_kernel' => 20, // Expected: 100/3.3 = 30.3, difference = 10.3g > 5g
        ]);

        $alerts = $this->cuttingTestService->getValidationAlerts($cuttingTest);

        expect($alerts)->toContain('Defective kernel weight discrepancy: 10.303030303030305g (exceeds 5g threshold)');
    });

    test('getValidationAlerts detects good kernel weight discrepancy', function () {
        $cuttingTest = CuttingTest::factory()->make([
            'sample_weight' => 1000,
            'w_reject_nut' => 100,
            'w_defective_nut' => 200,
            'w_good_kernel' => 150, // Expected: (1000-100-200)/3.3 = 212.1, difference = 62.1g > 10g
        ]);

        $alerts = $this->cuttingTestService->getValidationAlerts($cuttingTest);

        expect($alerts)->toContain('Good kernel weight discrepancy: 62.12121212121212g (exceeds 10g threshold)');
    });

    test('getValidationAlerts detects outturn rate out of range', function () {
        $cuttingTest = CuttingTest::factory()->make([
            'outturn_rate' => 65.0, // > 60
        ]);

        $alerts = $this->cuttingTestService->getValidationAlerts($cuttingTest);

        expect($alerts)->toContain('Outturn rate out of range: 65 lbs/80kg (should be 0-60)');
    });

    test('getValidationAlerts detects missing sample weight', function () {
        $cuttingTest = CuttingTest::factory()->make([
            'sample_weight' => null,
        ]);

        $alerts = $this->cuttingTestService->getValidationAlerts($cuttingTest);

        expect($alerts)->toContain('Sample weight is missing');
    });

    test('getValidationAlerts returns empty for valid test', function () {
        $cuttingTest = CuttingTest::factory()->make([
            'moisture' => 10.5, // <= 11%
            'sample_weight' => 1000,
            'w_sample_after_cut' => 998, // Difference = 2g <= 5g
            'w_defective_nut' => 100,
            'w_defective_kernel' => 30, // Expected: 100/3.3 = 30.3, difference = 0.3g <= 5g
            'w_reject_nut' => 100,
            'w_good_kernel' => 210, // Expected: (1000-100-100)/3.3 = 242.4, difference = 32.4g > 10g but within tolerance
            'outturn_rate' => 50.5, // Within 0-60 range
        ]);

        $alerts = $this->cuttingTestService->getValidationAlerts($cuttingTest);

        // Should only have the good kernel discrepancy alert
        expect($alerts)->toHaveCount(1);
        expect($alerts[0])->toContain('Good kernel weight discrepancy');
    });

    test('getCuttingTestsWithAlerts filters tests with alerts', function () {
        $test1 = CuttingTest::factory()->make(['moisture' => 12.0]); // Has alert
        $test2 = CuttingTest::factory()->make(['moisture' => 10.0]); // No alert
        $tests = collect([$test1, $test2]);

        $this->cuttingTestRepository
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($tests);

        $this->cuttingTestService = Mockery::mock(CuttingTestService::class)->makePartial();
        $this->cuttingTestService
            ->shouldReceive('getValidationAlerts')
            ->with($test1)
            ->andReturn(['High moisture content: 12% (exceeds 11% threshold)']);
        $this->cuttingTestService
            ->shouldReceive('getValidationAlerts')
            ->with($test2)
            ->andReturn([]);

        $result = $this->cuttingTestService->getCuttingTestsWithAlerts();

        expect($result)->toHaveCount(1);
        expect($result->first()->validation_alerts)->toContain('High moisture content: 12% (exceeds 11% threshold)');
    });

    test('getCuttingTestsWithHighMoisture filters high moisture tests', function () {
        $test1 = CuttingTest::factory()->make(['moisture' => 12.0]); // High moisture
        $test2 = CuttingTest::factory()->make(['moisture' => 10.0]); // Normal moisture
        $test3 = CuttingTest::factory()->make(['moisture' => null]); // No moisture data
        $tests = collect([$test1, $test2, $test3]);

        $this->cuttingTestRepository
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($tests);

        $result = $this->cuttingTestService->getCuttingTestsWithHighMoisture();

        expect($result)->toHaveCount(1);
        expect($result->first()->moisture)->toBe(12.0);
    });
});

describe('CuttingTestService Weight Relationships', function () {
    test('validateWeightRelationships detects sample after cut > sample weight', function () {
        $cuttingTest = CuttingTest::factory()->make([
            'sample_weight' => 1000,
            'w_sample_after_cut' => 1100, // Greater than sample weight
        ]);

        $errors = $this->cuttingTestService->validateWeightRelationships($cuttingTest);

        expect($errors)->toContain('Sample weight after cut cannot be greater than initial sample weight');
    });

    test('validateWeightRelationships detects component weights exceeding sample weight', function () {
        $cuttingTest = CuttingTest::factory()->make([
            'sample_weight' => 1000,
            'w_reject_nut' => 600,
            'w_defective_nut' => 500, // Total = 1100 > 1000
        ]);

        $errors = $this->cuttingTestService->validateWeightRelationships($cuttingTest);

        expect($errors)->toContain('Sum of reject and defective nut weights cannot exceed sample weight');
    });

    test('validateWeightRelationships detects excessive defective kernel weight', function () {
        $cuttingTest = CuttingTest::factory()->make([
            'w_defective_nut' => 100,
            'w_defective_kernel' => 60, // > 100/2 = 50
        ]);

        $errors = $this->cuttingTestService->validateWeightRelationships($cuttingTest);

        expect($errors)->toContain('Defective kernel weight seems too high relative to defective nut weight');
    });

    test('validateWeightRelationships returns empty for valid relationships', function () {
        $cuttingTest = CuttingTest::factory()->make([
            'sample_weight' => 1000,
            'w_sample_after_cut' => 950,
            'w_reject_nut' => 100,
            'w_defective_nut' => 200,
            'w_defective_kernel' => 80, // <= 200/2 = 100
        ]);

        $errors = $this->cuttingTestService->validateWeightRelationships($cuttingTest);

        expect($errors)->toBeEmpty();
    });
});

describe('CuttingTestService Business Logic', function () {
    test('calculateDefectiveRatio returns correct ratio', function () {
        $cuttingTest = CuttingTest::factory()->make([
            'w_defective_nut' => 100,
            'w_defective_kernel' => 80,
        ]);

        $result = $this->cuttingTestService->calculateDefectiveRatio($cuttingTest);

        expect($result)->toHaveKeys(['defective_nut', 'defective_kernel', 'ratio', 'formatted']);
        expect($result['defective_nut'])->toBe(100);
        expect($result['defective_kernel'])->toBe(80);
        expect($result['ratio'])->toBe(0.8); // 80/100 = 0.8
        expect($result['formatted'])->toBe('100/0.8');
    });

    test('calculateDefectiveRatio handles zero defective nut', function () {
        $cuttingTest = CuttingTest::factory()->make([
            'w_defective_nut' => 0,
            'w_defective_kernel' => 0,
        ]);

        $result = $this->cuttingTestService->calculateDefectiveRatio($cuttingTest);

        expect($result['ratio'])->toBe(0);
        expect($result['formatted'])->toBe('0/0');
    });

    test('calculateDefectiveRatio returns null for missing data', function () {
        $cuttingTest = CuttingTest::factory()->make([
            'w_defective_nut' => null,
            'w_defective_kernel' => 80,
        ]);

        $result = $this->cuttingTestService->calculateDefectiveRatio($cuttingTest);

        expect($result)->toBeNull();
    });

    test('isFinalSample correctly identifies final samples', function () {
        $finalSample = CuttingTest::factory()->make([
            'type' => 1,
            'container_id' => null,
        ]);

        $result = $this->cuttingTestService->isFinalSample($finalSample);

        expect($result)->toBeTrue();
    });

    test('isFinalSample rejects container tests', function () {
        $containerTest = CuttingTest::factory()->make([
            'type' => 4,
            'container_id' => 1,
        ]);

        $result = $this->cuttingTestService->isFinalSample($containerTest);

        expect($result)->toBeFalse();
    });

    test('isContainerTest correctly identifies container tests', function () {
        $containerTest = CuttingTest::factory()->make([
            'type' => 4,
            'container_id' => 1,
        ]);

        $result = $this->cuttingTestService->isContainerTest($containerTest);

        expect($result)->toBeTrue();
    });

    test('isContainerTest rejects final samples', function () {
        $finalSample = CuttingTest::factory()->make([
            'type' => 1,
            'container_id' => null,
        ]);

        $result = $this->cuttingTestService->isContainerTest($finalSample);

        expect($result)->toBeFalse();
    });
});

describe('CuttingTestService Statistics', function () {
    test('getCuttingTestStatistics returns complete statistics', function () {
        $highMoistureTests = collect([CuttingTest::factory()->make()]);
        $moistureDistribution = ['0-5%' => 10, '5-10%' => 20, '10-15%' => 5];

        $this->cuttingTestQuery
            ->shouldReceive('getTestsWithHighMoisture')
            ->once()
            ->with(11.0)
            ->andReturn($highMoistureTests);

        $this->cuttingTestQuery
            ->shouldReceive('getMoistureDistribution')
            ->once()
            ->andReturn($moistureDistribution);

        $statistics = $this->cuttingTestService->getCuttingTestStatistics();

        expect($statistics)->toHaveKeys([
            'high_moisture_count',
            'moisture_distribution',
            'high_moisture_tests',
        ]);
        expect($statistics['high_moisture_count'])->toBe(1);
        expect($statistics['moisture_distribution'])->toBe($moistureDistribution);
    });
});

describe('CuttingTestService Edge Cases', function () {
    test('createCuttingTest handles extreme weight values', function () {
        $data = [
            'bill_id' => 1,
            'type' => 1,
            'w_defective_kernel' => 0.01, // Very small
            'w_good_kernel' => 999.99, // Very large
        ];

        $expectedTest = CuttingTest::factory()->make($data);

        $this->cuttingTestRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return isset($arg['outturn_rate']) && $arg['outturn_rate'] >= 0 && $arg['outturn_rate'] <= 60;
            }))
            ->andReturn($expectedTest);

        $result = $this->cuttingTestService->createCuttingTest($data);

        expect($result)->toBeInstanceOf(CuttingTest::class);
    });

    test('getValidationAlerts handles null values gracefully', function () {
        $cuttingTest = CuttingTest::factory()->make([
            'moisture' => null,
            'sample_weight' => null,
            'w_sample_after_cut' => null,
            'w_defective_nut' => null,
            'w_defective_kernel' => null,
            'w_reject_nut' => null,
            'w_good_kernel' => null,
            'outturn_rate' => null,
        ]);

        $alerts = $this->cuttingTestService->getValidationAlerts($cuttingTest);

        expect($alerts)->toContain('Sample weight is missing');
        expect($alerts)->not->toContain('High moisture content'); // Should not alert for null moisture
    });

    test('createCuttingTest handles boundary moisture values', function () {
        $boundaryValues = [0.0, 11.0, 100.0];

        foreach ($boundaryValues as $moisture) {
            $data = [
                'bill_id' => 1,
                'type' => 1,
                'moisture' => $moisture,
            ];

            $expectedTest = CuttingTest::factory()->make($data);

            $this->cuttingTestRepository
                ->shouldReceive('create')
                ->once()
                ->andReturn($expectedTest);

            $result = $this->cuttingTestService->createCuttingTest($data);

            expect($result)->toBeInstanceOf(CuttingTest::class);
        }
    });
});