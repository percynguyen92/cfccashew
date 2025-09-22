<?php

use App\Enums\CuttingTestType;
use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use App\Queries\CuttingTestQuery;
use App\Repositories\CuttingTestRepository;
use App\Services\CuttingTestService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('supports CRUD and filtering via cutting test repository', function () {
    $bill = Bill::factory()->create();
    $container = Container::factory()->for($bill)->create();
    $repository = app(CuttingTestRepository::class);

    $payload = CuttingTest::factory()->make([
        'bill_id' => $bill->id,
        'container_id' => $container->id,
        'type' => CuttingTestType::ContainerCut->value,
        'moisture' => 11.2,
    ])->getAttributes();

    $cuttingTest = $repository->create($payload);

    expect($cuttingTest->exists)->toBeTrue();

    $repository->update($cuttingTest, ['moisture' => 12.3]);
    expect((float) $cuttingTest->refresh()->moisture)->toBe(12.3);

    $withRelations = $repository->findByIdWithRelations($cuttingTest->id, ['bill', 'container']);
    expect($withRelations)->not->toBeNull()
        ->and($withRelations->bill->id)->toBe($bill->id)
        ->and($withRelations->container?->id)->toBe($container->id);

    $finalSample = CuttingTest::factory()->create([
        'bill_id' => $bill->id,
        'container_id' => null,
        'type' => CuttingTestType::FinalFirstCut->value,
        'moisture' => 9.8,
    ]);

    $filteredFinal = $repository->findWithFilters(['test_type' => 'final']);
    $filteredContainer = $repository->findWithFilters(['test_type' => 'container']);

    expect($filteredFinal)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($filteredFinal->total())->toBe(1)
        ->and($filteredFinal->first()->id)->toBe($finalSample->id)
        ->and($filteredContainer->total())->toBe(1)
        ->and($filteredContainer->first()->id)->toBe($cuttingTest->id);

    $repository->delete($cuttingTest);
    expect(CuttingTest::withTrashed()->find($cuttingTest->id)->trashed())->toBeTrue();
});

it('fetches cutting tests via query helpers', function () {
    $bill = Bill::factory()->create();
    $container = Container::factory()->for($bill)->create();

    $finalSample = CuttingTest::factory()->create([
        'bill_id' => $bill->id,
        'container_id' => null,
        'type' => CuttingTestType::FinalSecondCut->value,
        'moisture' => 9.5,
        'outturn_rate' => 47.0,
    ]);
    $containerTest = CuttingTest::factory()->create([
        'bill_id' => $bill->id,
        'container_id' => $container->id,
        'type' => CuttingTestType::ContainerCut->value,
        'moisture' => 12.2,
        'outturn_rate' => 49.5,
    ]);

    $query = app(CuttingTestQuery::class);

    $byBill = $query->getByBillId($bill->id);
    $finals = $query->getFinalSamplesByBillId($bill->id);
    $containers = $query->getContainerTestsByBillId($bill->id);
    $highMoisture = $query->getTestsWithHighMoisture(11.0);
    $distribution = $query->getMoistureDistribution();

    expect($byBill)->toHaveCount(2)
        ->and($finals->pluck('id'))->toContain($finalSample->id)
        ->and($containers->pluck('id'))->toContain($containerTest->id)
        ->and($highMoisture->pluck('id'))->toContain($containerTest->id)
        ->and($distribution['total_tests'])->toBeGreaterThanOrEqual(2)
        ->and($distribution['distribution']['high'])->toBeGreaterThanOrEqual(1);
});

it('validates, calculates, and aggregates via cutting test service', function () {
    $service = app(CuttingTestService::class);
    $bill = Bill::factory()->create();
    $container = Container::factory()->for($bill)->create();

    $payload = [
        'bill_id' => $bill->id,
        'container_id' => $container->id,
        'type' => CuttingTestType::ContainerCut->value,
        'sample_weight' => 1000,
        'moisture' => 12.0,
        'w_defective_nut' => 100,
        'w_defective_kernel' => 100,
        'w_good_kernel' => 700,
    ];

    $cuttingTest = $service->createCuttingTest($payload);

    expect((float) $cuttingTest->outturn_rate)->toBe(60.0);

    $ratio = $service->calculateDefectiveRatio($cuttingTest->fresh());
    expect($ratio)
        ->toBeArray()
        ->and($ratio['formatted'])->toBe('100/1');

    expect(fn () => $service->createCuttingTest(array_merge($payload, [
        'type' => CuttingTestType::ContainerCut->value,
        'container_id' => null,
    ])))->toThrow(ValidationException::class);

    $finalSample = $service->createCuttingTest([
        'bill_id' => $bill->id,
        'type' => CuttingTestType::FinalFirstCut->value,
        'sample_weight' => 1000,
        'w_defective_nut' => 80,
        'w_defective_kernel' => 80,
        'w_good_kernel' => 650,
    ]);

    expect($service->isFinalSample($finalSample))->toBeTrue()
        ->and($service->isContainerTest($cuttingTest))->toBeTrue();

    $stats = $service->getCuttingTestStatistics();
    expect($stats['high_moisture_count'])->toBeGreaterThanOrEqual(1)
        ->and($stats['moisture_distribution'])->toBeArray();
});
