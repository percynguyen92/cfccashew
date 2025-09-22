<?php

use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use App\Queries\ContainerQuery;
use App\Repositories\ContainerRepository;
use App\Services\ContainerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('handles basic CRUD and lookups via container repository', function () {
    $bill = Bill::factory()->create();
    $repository = app(ContainerRepository::class);

    $payload = Container::factory()->for($bill)->make()->getAttributes();
    $payload['bill_id'] = $bill->id;

    $container = $repository->create($payload);

    expect($container->exists)->toBeTrue();

    $repository->update($container, ['truck' => 'TRK-999']);
    expect($container->refresh()->truck)->toBe('TRK-999');

    $foundByNumber = $repository->findByContainerNumber($container->container_number);
    $foundByIdentifier = $repository->findByContainerNumberOrId((string) $container->id);

    expect($foundByNumber?->id)->toBe($container->id)
        ->and($foundByIdentifier?->id)->toBe($container->id);

    $repository->delete($container);
    expect(Container::withTrashed()->find($container->id)->trashed())->toBeTrue();
});

it('retrieves containers using query filters', function () {
    $bill = Bill::factory()->create();
    $highMoistureContainer = Container::factory()->for($bill)->create([
        'container_number' => 'CONT1000001',
    ]);
    CuttingTest::factory()->for($bill)->for($highMoistureContainer)->create([
        'moisture' => 12.5,
    ]);

    $pendingContainer = Container::factory()->for(Bill::factory())->create([
        'container_number' => 'CONT2000002',
    ]);

    $searchContainer = Container::factory()->for(Bill::factory())->create([
        'container_number' => 'CONT3000003',
        'truck' => 'TRK-SEARCH',
    ]);

    $query = app(ContainerQuery::class);

    $highMoisture = $query->getContainersWithHighMoisture(11.0);
    $pending = $query->getContainersPendingCuttingTests();
    $paginated = $query->getAllPaginated(['container_number' => 'CONT300'], 10);

    expect($highMoisture->pluck('id'))->toContain($highMoistureContainer->id)
        ->and($pending->pluck('id'))->toContain($pendingContainer->id)
        ->and($paginated->total())->toBe(1)
        ->and($paginated->first()->container_number)->toBe('CONT3000003');
});

it('calculates derived data and statistics via container service', function () {
    $service = app(ContainerService::class);
    $bill = Bill::factory()->create();

    $payload = [
        'bill_id' => $bill->id,
        'truck' => 'TRK-100',
        'container_number' => 'CONT4000004',
        'quantity_of_bags' => 100,
        'w_jute_bag' => 1.5,
        'w_total' => 25000,
        'w_truck' => 10000,
        'w_container' => 3000,
        'w_dunnage_dribag' => 200,
        'note' => 'Initial payload',
    ];

    $container = $service->createContainer($payload);

    expect($container->w_gross)->toBe(12000)
        ->and((float) $container->w_tare)->toBe(150.0)
        ->and((float) $container->w_net)->toBe(11650.0);

    $service->updateContainer($container, array_merge($payload, [
        'w_total' => 26000,
        'note' => 'Updated payload',
    ]));

    expect($container->refresh()->w_gross)->toBe(13000)
        ->and((float) $container->w_net)->toBe(12650.0)
        ->and($service->getContainerByIdentifier($container->container_number)?->id)->toBe($container->id);

    CuttingTest::factory()->for($bill)->for($container)->create([
        'moisture' => 11.5,
        'outturn_rate' => 47.2,
    ]);

    expect($service->calculateAverageMoisture($container->fresh('cuttingTests')))->toBe(11.5)
        ->and($service->getOutturnRate($container))->toBe(47.2);

    Container::factory()->for($bill)->create([
        'container_number' => 'CONT5000005',
    ]);

    $stats = $service->getContainerStatistics();
    expect($stats['high_moisture_count'])->toBe(1)
        ->and($stats['pending_tests_count'])->toBe(1);
});
