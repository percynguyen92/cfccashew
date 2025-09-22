<?php

declare(strict_types=1);

use App\Models\Bill;
use App\Models\Container;
use App\Models\User;

test('container show page can be accessed by container number', function () {
    $user = User::factory()->create();
    $bill = Bill::factory()->create();
    $containerNumber = 'TEST' . random_int(1000000, 9999999);
    $container = Container::factory()->create([
        'bill_id' => $bill->id,
        'container_number' => $containerNumber,
    ]);

    $response = $this->actingAs($user)
        ->get("/containers/{$container->container_number}");

    $response->assertOk();
    $response->assertInertia(fn ($page) => 
        $page->component('Containers/Show')
            ->has('container')
            ->where('container.id', $container->id)
            ->where('container.container_number', $containerNumber)
    );
});

test('container show page can be accessed by id for backward compatibility', function () {
    $user = User::factory()->create();
    $bill = Bill::factory()->create();
    $containerNumber = 'BACK' . random_int(1000000, 9999999);
    $container = Container::factory()->create([
        'bill_id' => $bill->id,
        'container_number' => $containerNumber,
    ]);

    $response = $this->actingAs($user)
        ->get("/containers/{$container->id}");

    $response->assertOk();
    $response->assertInertia(fn ($page) => 
        $page->component('Containers/Show')
            ->has('container')
            ->where('container.id', $container->id)
            ->where('container.container_number', $containerNumber)
    );
});

test('container show page displays cutting tests', function () {
    $user = User::factory()->create();
    $bill = Bill::factory()->create();
    $containerNumber = 'CUTS' . random_int(1000000, 9999999);
    $container = Container::factory()->create([
        'bill_id' => $bill->id,
        'container_number' => $containerNumber,
    ]);

    // Create a cutting test for the container
    $cuttingTest = \App\Models\CuttingTest::factory()->create([
        'bill_id' => $bill->id,
        'container_id' => $container->id,
        'type' => 4, // Container test
        'moisture' => 10.5,
        'outturn_rate' => 45.2,
    ]);

    $response = $this->actingAs($user)
        ->get("/containers/{$container->container_number}");

    $response->assertOk();
    $response->assertInertia(fn ($page) => 
        $page->component('Containers/Show')
    );

    $props = json_decode(json_encode($response->viewData('page')), true);
    $tests = collect($props['props']['container']['cutting_tests'] ?? []);

    $record = $tests->firstWhere('id', $cuttingTest->id);
    expect($record)->not->toBeNull();
    expect($record['moisture'])->toBe('10.5');
    expect($record['outturn_rate'])->toBe('45.20');
});

test('container show page displays bill information', function () {
    $user = User::factory()->create();
    $bill = Bill::factory()->create([
        'bill_number' => 'TEST123',
        'seller' => 'Test Seller',
        'buyer' => 'Test Buyer',
    ]);
    $containerNumber = 'BILL' . random_int(1000000, 9999999);
    $container = Container::factory()->create([
        'bill_id' => $bill->id,
        'container_number' => $containerNumber,
    ]);

    $response = $this->actingAs($user)
        ->get("/containers/{$container->container_number}");

    $response->assertOk();
    $response->assertInertia(fn ($page) => 
        $page->component('Containers/Show')
            ->has('container.bill')
            ->where('container.bill.bill_number', 'TEST123')
            ->where('container.bill.seller', 'Test Seller')
            ->where('container.bill.buyer', 'Test Buyer')
    );
});
