<?php

use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('links to bill and cutting tests', function () {
    $bill = Bill::factory()->create();
    $container = Container::factory()->for($bill)->create();
    CuttingTest::factory()->count(2)->for($bill)->for($container)->create();

    $container->load(['bill', 'cuttingTests']);

    expect($container->bill->is($bill))->toBeTrue()
        ->and($container->cuttingTests)->toHaveCount(2);
});

it('calculates derived weights correctly', function () {
    $container = Container::factory()->for(Bill::factory())->create([
        'w_total' => 25000,
        'w_truck' => 10000,
        'w_container' => 3000,
        'quantity_of_bags' => 100,
        'w_jute_bag' => 1.5,
        'w_dunnage_dribag' => 200,
    ]);

    expect($container->calculateGrossWeight())->toBe(12000.0)
        ->and($container->calculateTareWeight())->toBe(150.0)
        ->and($container->calculateNetWeight())->toBe(11650.0);
});

it('computes average moisture and outturn accessors', function () {
    $container = Container::factory()->for(Bill::factory())->create();
    CuttingTest::factory()->count(2)->for($container->bill)->for($container)->create([
        'moisture' => 10.5,
        'outturn_rate' => 48.2,
    ]);
    CuttingTest::factory()->for($container->bill)->for($container)->create([
        'moisture' => 12.1,
        'outturn_rate' => 50.0,
    ]);

    $fresh = $container->fresh();

    expect($fresh->average_moisture)->toBe(11.0)
        ->and($fresh->outturn_rate)->toBe(48.2);
});

it('resolves route binding by container number and id', function () {
    $container = Container::factory()->for(Bill::factory())->create();

    $resolvedByNumber = (new Container())->resolveRouteBinding($container->container_number);
    $resolvedById = (new Container())->resolveRouteBinding((string) $container->id);

    expect($resolvedByNumber->is($container))->toBeTrue()
        ->and($resolvedById->is($container))->toBeTrue();
});
