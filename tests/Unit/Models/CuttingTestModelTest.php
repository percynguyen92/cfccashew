<?php

use App\Enums\CuttingTestType;
use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('associates to bill and container as expected', function () {
    $bill = Bill::factory()->create();
    $container = Container::factory()->for($bill)->create();

    $finalSample = CuttingTest::factory()->for($bill)->create([
        'type' => CuttingTestType::FinalSecondCut->value,
        'container_id' => null,
    ]);

    $containerTest = CuttingTest::factory()->for($bill)->for($container)->create([
        'type' => CuttingTestType::ContainerCut->value,
    ]);

    $finalSample->load('bill');
    $containerTest->load(['bill', 'container']);

    expect($finalSample->bill->is($bill))->toBeTrue()
        ->and($containerTest->container->is($container))->toBeTrue();
});

it('detects final samples and container tests', function () {
    $bill = Bill::factory()->create();
    $container = Container::factory()->for($bill)->create();

    $finalSample = CuttingTest::factory()->for($bill)->create([
        'type' => CuttingTestType::FinalThirdCut->value,
        'container_id' => null,
    ]);

    $containerTest = CuttingTest::factory()->for($bill)->for($container)->create([
        'type' => CuttingTestType::ContainerCut->value,
    ]);

    expect($finalSample->getTypeEnum())->toBe(CuttingTestType::FinalThirdCut)
        ->and($finalSample->isFinalSample())->toBeTrue()
        ->and($finalSample->isContainerTest())->toBeFalse()
        ->and($containerTest->isFinalSample())->toBeFalse()
        ->and($containerTest->isContainerTest())->toBeTrue();
});

