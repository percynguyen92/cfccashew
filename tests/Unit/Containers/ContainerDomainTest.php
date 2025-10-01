<?php

use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use App\Queries\ContainerQuery;
use App\Repositories\ContainerRepository;
use App\Services\ContainerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('handles basic CRUD and lookups via container repository with new fields and pivot', function () {
    $bill = Bill::factory()->create([
        'w_jute_bag' => 1.5,
        'w_dunnage_dribag' => 200,
    ]);
    $repository = app(ContainerRepository::class);

    $payload = [
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

    $container = $repository->create($payload + ['bill_id' => $bill->id]);

    expect($container->exists)->toBeTrue();
    expect($container->container_condition)->toBe('Nguyên vẹn');

    $repository->update($container, ['container_condition' => 'Damaged', 'seal_condition' => 'Broken']);

    expect($container->refresh()->container_condition)->toBe('Damaged');
    expect($container->seal_condition)->toBe('Broken');

    $foundByNumber = $repository->findByContainerNumber($container->container_number);
    $foundByIdentifier = $repository->findByContainerNumberOrId((string) $container->id);

    expect($foundByNumber?->id)->toBe($container->id)
        ->and($foundByIdentifier?->id)->toBe($container->id);

    $repository->delete($container);
    expect(Container::withTrashed()->find($container->id)->trashed())->toBeTrue();
});

it('retrieves containers using query filters, includes new condition fields if available', function () {
    $bill = Bill::factory()->create();
    $highMoistureContainer = Container::factory()->create([
        'bill_id' => $bill->id,
        'container_number' => 'CONT1000001',
        'container_condition' => 'Good',
        'seal_condition' => 'Intact',
    ]);
    $bill->containers()->attach($highMoistureContainer->id);
    CuttingTest::factory()->for($bill)->for($highMoistureContainer)->create([
        'moisture' => 12.5,
    ]);

    $pendingContainer = Container::factory()->create([
        'bill_id' => $bill->id,
        'container_number' => 'CONT2000002',
        'container_condition' => 'Damaged',
    ]);
    $bill->containers()->attach($pendingContainer->id);

    $searchContainer = Container::factory()->create([
        'bill_id' => $bill->id,
        'container_number' => 'CONT3000003',
        'truck' => 'TRK-SEARCH',
        'seal_condition' => 'Broken',
    ]);
    $bill->containers()->attach($searchContainer->id);

    $query = app(ContainerQuery::class);

    $highMoisture = $query->getContainersWithHighMoisture(11.0);
    $pending = $query->getContainersPendingCuttingTests();
    $paginated = $query->getAllPaginated(['container_number' => 'CONT300'], 10);

    expect($highMoisture->pluck('id'))->toContain($highMoistureContainer->id)
        ->and($pending->pluck('id'))->toContain($pendingContainer->id)
        ->and($paginated->total())->toBe(1)
        ->and($paginated->first()->container_number)->toBe('CONT3000003')
        ->and($highMoistureContainer->fresh()->container_condition)->toBe('Good')
        ->and($pendingContainer->fresh()->container_condition)->toBe('Damaged')
        ->and($searchContainer->fresh()->seal_condition)->toBe('Broken');
});

it('calculates derived data and statistics via container service using bill-level weights', function () {
    $service = app(ContainerService::class);
    $bill = Bill::factory()->create([
        'w_jute_bag' => 1.5,
        'w_dunnage_dribag' => 200,
    ]);

    $payload = [
        'bill_id' => $bill->id,
        'truck' => 'TRK-100',
        'container_number' => 'CONT4000004',
        'quantity_of_bags' => 100,
        'w_total' => 25000,
        'w_truck' => 10000,
        'w_container' => 3000,
        'w_gross' => 12000,
        'w_tare' => 150.0,
        'w_net' => 11650.0,
        'container_condition' => 'Nguyên vẹn',
        'seal_condition' => 'Nguyên vẹn',
        'note' => 'Initial payload',
    ];

    $container = $service->createContainer($payload);

    expect($container->w_gross)->toBe(12000)
        ->and($container->w_tare)->toBe(150.0)
        ->and($container->w_net)->toBe(11650.0)
        ->and($container->container_condition)->toBe('Nguyên vẹn')
        ->and($container->seal_condition)->toBe('Nguyên vẹn');

    $service->updateContainer($container, array_merge($payload, [
        'w_total' => 26000,
        'container_condition' => 'Damaged',
        'note' => 'Updated payload',
    ]));

    $container->refresh();
    expect($container->w_gross)->toBe(13000)
        ->and($container->w_net)->toBe(12650.0)
        ->and($container->container_condition)->toBe('Damaged')
        ->and($service->getContainerByIdentifier($container->container_number)?->id)->toBe($container->id);

    CuttingTest::factory()->for($bill)->for($container)->create([
        'moisture' => 11.5,
        'outturn_rate' => 47.2,
    ]);

    expect($service->calculateAverageMoisture($container->fresh('cuttingTests')))->toBe(11.5)
        ->and($service->getOutturnRate($container))->toBe(47.2);

    $anotherContainer = Container::factory()->create([
        'bill_id' => $bill->id,
        'container_number' => 'CONT5000005',
        'container_condition' => 'Good',
    ]);
    $bill->containers()->attach($anotherContainer->id);

    $stats = $service->getContainerStatistics();
    expect($stats['high_moisture_count'])->toBe(1)
        ->and($stats['pending_tests_count'])->toBe(1);
});
