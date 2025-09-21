<?php

use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use App\Models\User;
use App\Enums\CuttingTestType;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->bill = Bill::factory()->create();
    $this->container = Container::factory()->create(['bill_id' => $this->bill->id]);
});

it('can display cutting tests index page', function () {
    $cuttingTests = CuttingTest::factory()->count(3)->create([
        'bill_id' => $this->bill->id,
        'container_id' => $this->container->id,
        'type' => CuttingTestType::ContainerCut->value,
    ]);

    $response = $this->actingAs($this->user)
        ->get('/cutting-tests');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('CuttingTests/Index')
             ->has('cutting_tests')
             ->has('pagination')
             ->has('filters')
    );
});

it('can filter cutting tests by bill number', function () {
    $otherBill = Bill::factory()->create(['bill_number' => 'OTHER-001']);
    
    CuttingTest::factory()->create([
        'bill_id' => $this->bill->id,
        'type' => CuttingTestType::FinalFirstCut->value,
    ]);
    
    CuttingTest::factory()->create([
        'bill_id' => $otherBill->id,
        'type' => CuttingTestType::FinalFirstCut->value,
    ]);

    $response = $this->actingAs($this->user)
        ->get('/cutting-tests?bill_number=' . $this->bill->bill_number);

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('CuttingTests/Index')
             ->has('cutting_tests', 1)
    );
});

it('can filter cutting tests by type', function () {
    CuttingTest::factory()->create([
        'bill_id' => $this->bill->id,
        'type' => CuttingTestType::FinalFirstCut->value,
    ]);
    
    CuttingTest::factory()->create([
        'bill_id' => $this->bill->id,
        'container_id' => $this->container->id,
        'type' => CuttingTestType::ContainerCut->value,
    ]);

    $response = $this->actingAs($this->user)
        ->get('/cutting-tests?test_type=' . CuttingTestType::FinalFirstCut->value);

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('CuttingTests/Index')
             ->has('cutting_tests', 1)
    );
});

it('can filter cutting tests by moisture range', function () {
    CuttingTest::factory()->create([
        'bill_id' => $this->bill->id,
        'type' => CuttingTestType::FinalFirstCut->value,
        'moisture' => 10.5,
    ]);
    
    CuttingTest::factory()->create([
        'bill_id' => $this->bill->id,
        'type' => CuttingTestType::FinalSecondCut->value,
        'moisture' => 12.8,
    ]);

    $response = $this->actingAs($this->user)
        ->get('/cutting-tests?moisture_min=12&moisture_max=15');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('CuttingTests/Index')
             ->has('cutting_tests', 1)
    );
});

it('displays create cutting test form', function () {
    $response = $this->actingAs($this->user)
        ->get('/cutting-tests/create');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('CuttingTests/Create')
    );
});

it('displays create cutting test form with bill context', function () {
    $response = $this->actingAs($this->user)
        ->get('/cutting-tests/create?bill_id=' . $this->bill->id);

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('CuttingTests/Create')
             ->where('bill_id', $this->bill->id)
             ->has('bill')
    );
});

it('displays create cutting test form with container context', function () {
    $response = $this->actingAs($this->user)
        ->get('/cutting-tests/create?bill_id=' . $this->bill->id . '&container_id=' . $this->container->id);

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('CuttingTests/Create')
             ->where('bill_id', $this->bill->id)
             ->where('container_id', $this->container->id)
             ->has('bill')
             ->has('container')
    );
});

it('can create a final sample cutting test', function () {
    $data = [
        'bill_id' => $this->bill->id,
        'type' => CuttingTestType::FinalFirstCut->value,
        'moisture' => 12.5,
        'sample_weight' => 1000,
        'nut_count' => 150,
        'w_reject_nut' => 50,
        'w_defective_nut' => 100,
        'w_defective_kernel' => 30,
        'w_good_kernel' => 250,
        'w_sample_after_cut' => 995,
        'note' => 'First final sample test',
    ];

    $response = $this->actingAs($this->user)
        ->post('/cutting-tests', $data);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('cutting_tests', [
        'bill_id' => $this->bill->id,
        'container_id' => null,
        'type' => CuttingTestType::FinalFirstCut->value,
        'moisture' => 12.5,
        'note' => 'First final sample test',
    ]);
});

it('can create a container cutting test', function () {
    $data = [
        'bill_id' => $this->bill->id,
        'container_id' => $this->container->id,
        'type' => CuttingTestType::ContainerCut->value,
        'moisture' => 11.8,
        'sample_weight' => 1000,
        'nut_count' => 120,
        'w_reject_nut' => 40,
        'w_defective_nut' => 80,
        'w_defective_kernel' => 25,
        'w_good_kernel' => 200,
        'w_sample_after_cut' => 990,
        'note' => 'Container test',
    ];

    $response = $this->actingAs($this->user)
        ->post('/cutting-tests', $data);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('cutting_tests', [
        'bill_id' => $this->bill->id,
        'container_id' => $this->container->id,
        'type' => CuttingTestType::ContainerCut->value,
        'moisture' => 11.8,
        'note' => 'Container test',
    ]);
});

it('calculates outturn rate automatically when creating cutting test', function () {
    $data = [
        'bill_id' => $this->bill->id,
        'type' => CuttingTestType::FinalFirstCut->value,
        'sample_weight' => 1000,
        'w_defective_kernel' => 40,
        'w_good_kernel' => 200,
    ];

    $this->actingAs($this->user)
        ->post('/cutting-tests', $data);

    $cuttingTest = CuttingTest::where('bill_id', $this->bill->id)->first();
    
    // Expected: (40/2 + 200) * 80 / 453.6 = 38.67
    expect($cuttingTest->outturn_rate)->toBeFloat();
    expect($cuttingTest->outturn_rate)->toBeGreaterThan(35);
    expect($cuttingTest->outturn_rate)->toBeLessThan(40);
});

it('validates final sample tests cannot have container_id', function () {
    $data = [
        'bill_id' => $this->bill->id,
        'container_id' => $this->container->id, // Should not be allowed for final sample
        'type' => CuttingTestType::FinalFirstCut->value,
        'sample_weight' => 1000,
    ];

    $response = $this->actingAs($this->user)
        ->post('/cutting-tests', $data);

    $response->assertSessionHasErrors(['container_id']);
});

it('validates container tests must have container_id', function () {
    $data = [
        'bill_id' => $this->bill->id,
        'type' => CuttingTestType::ContainerCut->value,
        'sample_weight' => 1000,
        // Missing container_id
    ];

    $response = $this->actingAs($this->user)
        ->post('/cutting-tests', $data);

    $response->assertSessionHasErrors(['container_id']);
});

it('validates moisture range', function () {
    $data = [
        'bill_id' => $this->bill->id,
        'type' => CuttingTestType::FinalFirstCut->value,
        'sample_weight' => 1000,
        'moisture' => 150, // Invalid - over 100%
    ];

    $response = $this->actingAs($this->user)
        ->post('/cutting-tests', $data);

    $response->assertSessionHasErrors(['moisture']);
});

it('can display cutting test details', function () {
    $cuttingTest = CuttingTest::factory()->create([
        'bill_id' => $this->bill->id,
        'type' => CuttingTestType::FinalFirstCut->value,
        'moisture' => 12.5,
        'note' => 'Test note',
    ]);

    $response = $this->actingAs($this->user)
        ->get('/cutting-tests/' . $cuttingTest->id);

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('CuttingTests/Show')
             ->where('cutting_test.id', $cuttingTest->id)
             ->where('cutting_test.moisture', 12.5)
             ->where('cutting_test.note', 'Test note')
    );
});

it('can display edit cutting test form', function () {
    $cuttingTest = CuttingTest::factory()->create([
        'bill_id' => $this->bill->id,
        'type' => CuttingTestType::FinalFirstCut->value,
    ]);

    $response = $this->actingAs($this->user)
        ->get('/cutting-tests/' . $cuttingTest->id . '/edit');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('CuttingTests/Edit')
             ->where('cutting_test.id', $cuttingTest->id)
    );
});

it('can update cutting test', function () {
    $cuttingTest = CuttingTest::factory()->create([
        'bill_id' => $this->bill->id,
        'type' => CuttingTestType::FinalFirstCut->value,
        'moisture' => 10.0,
    ]);

    $data = [
        'bill_id' => $this->bill->id,
        'type' => CuttingTestType::FinalFirstCut->value,
        'moisture' => 12.5,
        'sample_weight' => 1000,
        'note' => 'Updated note',
    ];

    $response = $this->actingAs($this->user)
        ->put('/cutting-tests/' . $cuttingTest->id, $data);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('cutting_tests', [
        'id' => $cuttingTest->id,
        'moisture' => 12.5,
        'note' => 'Updated note',
    ]);
});

it('can delete cutting test', function () {
    $cuttingTest = CuttingTest::factory()->create([
        'bill_id' => $this->bill->id,
        'type' => CuttingTestType::FinalFirstCut->value,
    ]);

    $response = $this->actingAs($this->user)
        ->delete('/cutting-tests/' . $cuttingTest->id);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('cutting_tests', [
        'id' => $cuttingTest->id,
    ]);
});