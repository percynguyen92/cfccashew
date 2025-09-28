<?php

namespace Tests\Feature;

use App\Enums\CuttingTestType;
use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CuttingTestControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Bill $bill;

    protected Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->bill = Bill::factory()->create();
        $this->container = Container::factory()->create([
            'bill_id' => $this->bill->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_can_display_cutting_tests_index_page(): void
    {
        CuttingTest::factory()->count(3)->create([
            'bill_id' => $this->bill->id,
            'container_id' => $this->container->id,
            'type' => CuttingTestType::ContainerCut->value,
        ]);

        $response = $this->get('/cutting-tests');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('CuttingTests/Index')
                ->has('cutting_tests')
                ->has('pagination')
                ->has('filters')
        );
    }

    public function test_can_filter_cutting_tests_by_bill_number(): void
    {
        $targetBillNumber = 'FILTER-BILL-' . Str::random(5);
        $targetBill = Bill::factory()->create(['bill_number' => $targetBillNumber]);
        $otherBill = Bill::factory()->create(['bill_number' => 'OTHER-001']);

        $expectedTest = CuttingTest::factory()->create([
            'bill_id' => $targetBill->id,
            'type' => CuttingTestType::FinalFirstCut->value,
        ]);

        CuttingTest::factory()->create([
            'bill_id' => $otherBill->id,
            'type' => CuttingTestType::FinalFirstCut->value,
        ]);

        $response = $this->get('/cutting-tests?bill_number=' . $targetBillNumber);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('CuttingTests/Index')
        );

        $records = collect(json_decode(json_encode($response->viewData('page')), true)['props']['cutting_tests'] ?? []);
        $this->assertTrue($records->pluck('id')->contains($expectedTest->id));
    }

    public function test_can_filter_cutting_tests_by_type(): void
    {
        $bill = Bill::factory()->create(['bill_number' => 'TYPE-' . Str::random(6)]);

        $finalSample = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'type' => CuttingTestType::FinalFirstCut->value,
            'container_id' => null,
        ]);

        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => $this->container->id,
            'type' => CuttingTestType::ContainerCut->value,
        ]);

        $response = $this->get('/cutting-tests?bill_number=' . $bill->bill_number . '&test_type=final');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('CuttingTests/Index')
        );

        $records = collect(json_decode(json_encode($response->viewData('page')), true)['props']['cutting_tests'] ?? []);
        $this->assertTrue($records->pluck('id')->contains($finalSample->id));
    }

    public function test_can_filter_cutting_tests_by_moisture_range(): void
    {
        $bill = Bill::factory()->create(['bill_number' => 'MOIST-' . Str::random(6)]);

        $matchingTest = CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'type' => CuttingTestType::FinalFirstCut->value,
            'moisture' => 10.5,
        ]);

        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'type' => CuttingTestType::FinalSecondCut->value,
            'moisture' => 12.8,
        ]);

        $response = $this->get('/cutting-tests?bill_number=' . $bill->bill_number . '&moisture_min=10&moisture_max=11');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('CuttingTests/Index')
        );

        $records = collect(json_decode(json_encode($response->viewData('page')), true)['props']['cutting_tests'] ?? []);
        $this->assertTrue($records->pluck('id')->contains($matchingTest->id));
    }

    public function test_displays_create_cutting_test_form(): void
    {
        $response = $this->get('/cutting-tests/create');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('CuttingTests/Create')
        );
    }

    public function test_displays_create_cutting_test_form_with_bill_context(): void
    {
        $response = $this->get('/cutting-tests/create?bill_id=' . $this->bill->id);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('CuttingTests/Create')
                ->where('bill_id', (string) $this->bill->id)
                ->has('bill')
        );
    }

    public function test_displays_create_cutting_test_form_with_container_context(): void
    {
        $response = $this->get('/cutting-tests/create?bill_id=' . $this->bill->id . '&container_id=' . $this->container->id);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('CuttingTests/Create')
                ->where('bill_id', (string) $this->bill->id)
                ->where('container_id', (string) $this->container->id)
                ->has('bill')
                ->has('container')
        );
    }

    public function test_can_create_final_sample_cutting_test(): void
    {
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

        $response = $this->post('/cutting-tests', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cutting_tests', [
            'bill_id' => $this->bill->id,
            'container_id' => null,
            'type' => CuttingTestType::FinalFirstCut->value,
            'moisture' => 12.5,
            'note' => 'First final sample test',
        ]);
    }

    public function test_can_create_container_cutting_test(): void
    {
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

        $response = $this->post('/cutting-tests', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cutting_tests', [
            'bill_id' => $this->bill->id,
            'container_id' => $this->container->id,
            'type' => CuttingTestType::ContainerCut->value,
            'moisture' => 11.8,
            'note' => 'Container test',
        ]);
    }

    public function test_calculates_outturn_rate_automatically_when_creating_cutting_test(): void
    {
        $data = [
            'bill_id' => $this->bill->id,
            'type' => CuttingTestType::FinalFirstCut->value,
            'sample_weight' => 1000,
            'w_defective_kernel' => 40,
            'w_good_kernel' => 200,
        ];

        $this->post('/cutting-tests', $data);

        $cuttingTest = CuttingTest::where('bill_id', $this->bill->id)->first();

        $this->assertNotNull($cuttingTest);
        $this->assertSame('38.80', $cuttingTest->outturn_rate);
    }

    public function test_requires_bill_type_and_sample_weight_when_creating_cutting_test(): void
    {
        $response = $this->post('/cutting-tests', []);

        $response->assertSessionHasErrors([
            'bill_id',
            'type',
            'sample_weight',
        ]);
    }

    public function test_rejects_invalid_cutting_test_types(): void
    {
        $response = $this->post('/cutting-tests', [
            'bill_id' => $this->bill->id,
            'type' => 99,
            'sample_weight' => 1000,
        ]);

        $response->assertSessionHasErrors(['type']);
    }

    public function test_validates_sample_weight_boundaries(): void
    {
        $basePayload = [
            'bill_id' => $this->bill->id,
            'type' => CuttingTestType::FinalFirstCut->value,
        ];

        $this->post('/cutting-tests', $basePayload + ['sample_weight' => 1])
            ->assertSessionDoesntHaveErrors();

        $this->post('/cutting-tests', $basePayload + ['sample_weight' => 65_535])
            ->assertSessionDoesntHaveErrors();

        $this->post('/cutting-tests', $basePayload + ['sample_weight' => 0])
            ->assertSessionHasErrors(['sample_weight']);

        $this->post('/cutting-tests', $basePayload + ['sample_weight' => 65_536])
            ->assertSessionHasErrors(['sample_weight']);
    }

    public function test_validates_final_sample_tests_cannot_have_container_id(): void
    {
        $data = [
            'bill_id' => $this->bill->id,
            'container_id' => $this->container->id,
            'type' => CuttingTestType::FinalFirstCut->value,
            'sample_weight' => 1000,
        ];

        $response = $this->post('/cutting-tests', $data);

        $response->assertSessionHasErrors(['container_id']);
    }

    public function test_validates_container_tests_must_have_container_id(): void
    {
        $data = [
            'bill_id' => $this->bill->id,
            'type' => CuttingTestType::ContainerCut->value,
            'sample_weight' => 1000,
        ];

        $response = $this->post('/cutting-tests', $data);

        $response->assertSessionHasErrors(['container_id']);
    }

    public function test_validates_moisture_range(): void
    {
        $data = [
            'bill_id' => $this->bill->id,
            'type' => CuttingTestType::FinalFirstCut->value,
            'sample_weight' => 1000,
            'moisture' => 150,
        ];

        $response = $this->post('/cutting-tests', $data);

        $response->assertSessionHasErrors(['moisture']);
    }

    public function test_rejects_negative_moisture_values(): void
    {
        $data = [
            'bill_id' => $this->bill->id,
            'type' => CuttingTestType::FinalFirstCut->value,
            'sample_weight' => 1000,
            'moisture' => -0.01,
        ];

        $response = $this->post('/cutting-tests', $data);

        $response->assertSessionHasErrors(['moisture']);
    }

    public function test_can_display_cutting_test_details(): void
    {
        $cuttingTest = CuttingTest::factory()->create([
            'bill_id' => $this->bill->id,
            'type' => CuttingTestType::FinalFirstCut->value,
            'moisture' => 12.5,
            'note' => 'Test note',
        ]);

        $response = $this->get('/cutting-tests/' . $cuttingTest->id);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('CuttingTests/Show')
                ->where('cutting_test.id', $cuttingTest->id)
                ->where('cutting_test.moisture', '12.5')
                ->where('cutting_test.note', 'Test note')
        );
    }

    public function test_can_display_edit_cutting_test_form(): void
    {
        $cuttingTest = CuttingTest::factory()->create([
            'bill_id' => $this->bill->id,
            'type' => CuttingTestType::FinalFirstCut->value,
        ]);

        $response = $this->get('/cutting-tests/' . $cuttingTest->id . '/edit');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('CuttingTests/Edit')
                ->where('cutting_test.id', $cuttingTest->id)
        );
    }

    public function test_can_update_cutting_test(): void
    {
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

        $response = $this->put('/cutting-tests/' . $cuttingTest->id, $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cutting_tests', [
            'id' => $cuttingTest->id,
            'moisture' => 12.5,
            'note' => 'Updated note',
        ]);
    }

    public function test_can_delete_cutting_test(): void
    {
        $cuttingTest = CuttingTest::factory()->create([
            'bill_id' => $this->bill->id,
            'type' => CuttingTestType::FinalFirstCut->value,
        ]);

        $response = $this->delete('/cutting-tests/' . $cuttingTest->id);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('cutting_tests', [
            'id' => $cuttingTest->id,
        ]);
    }
}
