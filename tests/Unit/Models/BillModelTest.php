<?php

use App\Enums\CuttingTestType;
use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertModelExists;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('loads related containers via pivot and cutting tests', function () {
    $bill = Bill::factory()->create();
    $container = Container::factory()->create();
    $bill->containers()->attach($container->id);

    $finalSample = CuttingTest::factory()->for($bill)->create([
        'type' => CuttingTestType::FinalFirstCut->value,
        'container_id' => null,
    ]);
    $containerTest = CuttingTest::factory()->for($bill)->for($container)->create([
        'type' => CuttingTestType::ContainerCut->value,
    ]);

    $bill->load(['containers', 'cuttingTests', 'finalSamples']);

    expect($bill->containers)->toHaveCount(1)
        ->and($bill->cuttingTests)->toHaveCount(2)
        ->and($bill->finalSamples)->toHaveCount(1)
        ->and($bill->finalSamples->first()->id)->toBe($finalSample->id)
        ->and($bill->cuttingTests->pluck('id'))->toContain($containerTest->id)
        ->and($bill->containers->first()->id)->toBe($container->id);
});

it('calculates average outturn from final samples', function () {
    $bill = Bill::factory()->create();

    CuttingTest::factory()->count(2)->for($bill)->create([
        'type' => CuttingTestType::FinalFirstCut->value,
        'container_id' => null,
        'outturn_rate' => 50,
    ]);
    CuttingTest::factory()->for($bill)->create([
        'type' => CuttingTestType::FinalSecondCut->value,
        'container_id' => null,
        'outturn_rate' => 52,
    ]);

    expect($bill->fresh()->average_outturn)->toBe(50.67);
});

it('generates slug and resolves binding correctly', function () {
    $bill = Bill::factory()->create(['bill_number' => 'BL-2024-001']);

    expect($bill->slug)->toBe($bill->id . '-BL-2024-001');

    $resolved = (new Bill())->resolveRouteBinding($bill->slug);

    assertModelExists($resolved);
    expect($resolved->is($bill))->toBeTrue();
});

