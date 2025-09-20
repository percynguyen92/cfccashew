<?php

declare(strict_types=1);

use App\Models\Bill;
use App\Models\Container;
use App\Models\User;

test('container show page can be accessed by container number', function () {
    $user = User::factory()->create();
    $bill = Bill::factory()->create();
    $container = Container::factory()->create([
        'bill_id' => $bill->id,
        'container_number' => 'ONEU5619273',
    ]);

    $response = $this->actingAs($user)
        ->get("/containers/{$container->container_number}");

    $response->assertOk();
    $response->assertInertia(fn ($page) => 
        $page->component('Containers/Show')
            ->has('container')
            ->where('container.id', $container->id)
            ->where('container.container_number', 'ONEU5619273')
    );
});

test('container show page can be accessed by id for backward compatibility', function () {
    $user = User::factory()->create();
    $bill = Bill::factory()->create();
    $container = Container::factory()->create([
        'bill_id' => $bill->id,
        'container_number' => 'ONEU5619273',
    ]);

    $response = $this->actingAs($user)
        ->get("/containers/{$container->id}");

    $response->assertOk();
    $response->assertInertia(fn ($page) => 
        $page->component('Containers/Show')
            ->has('container')
            ->where('container.id', $container->id)
            ->where('container.container_number', 'ONEU5619273')
    );
});

test('container show page displays cutting tests', function () {
    $user = User::factory()->create();
    $bill = Bill::factory()->create();
    $container = Container::factory()->create([
        'bill_id' => $bill->id,
        'container_number' => 'ONEU5619273',
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
            ->has('container.cutting_tests', 1)
            ->where('container.cutting_tests.0.id', $cuttingTest->id)
            ->where('container.cutting_tests.0.moisture', '10.50')
            ->where('container.cutting_tests.0.outturn_rate', '45.20')
    );
});

test('container show page displays bill information', function () {
    $user = User::factory()->create();
    $bill = Bill::factory()->create([
        'bill_number' => 'TEST123',
        'seller' => 'Test Seller',
        'buyer' => 'Test Buyer',
    ]);
    $container = Container::factory()->create([
        'bill_id' => $bill->id,
        'container_number' => 'ONEU5619273',
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