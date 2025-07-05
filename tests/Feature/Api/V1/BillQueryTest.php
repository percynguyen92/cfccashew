<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\CuttingTestType;
use App\Models\Bill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_paginated_bills(): void
    {
        Bill::factory()->count(20)->create();

        $response = $this->getJson('/api/v1/bills');

        $response->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonCount(10, 'data'); // default pagination = 10
    }

    public function test_can_filter_by_buyer_and_bill_number(): void
    {
        $billA = Bill::factory()->create([
            'billNumber' => 'BILL-ABC123',
            'buyer' => 'CÔNG TY A',
        ]);

        Bill::factory()->create([
            'billNumber' => 'BILL-XYZ999',
            'buyer' => 'CÔNG TY B',
        ]);

        $response = $this->getJson('/api/v1/bills?filter[billNumber][eq]=BILL-ABC123&filter[buyer][eq]=CÔNG TY A');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.billNumber', $billA->billNumber)
            ->assertJsonPath('data.0.buyer', $billA->buyer);
    }

    public function test_can_sort_by_seller_desc(): void
    {
        Bill::factory()->create(['seller' => 'ZETA']);
        Bill::factory()->create(['seller' => 'ALPHA']);

        $response = $this->getJson('/api/v1/bills?sort=-seller');

        $response->assertOk();
        $this->assertEquals('ZETA', $response->json('data.0.seller'));
    }

    public function test_can_include_containers_and_cutting_tests(): void
    {
        $bill = Bill::factory()->withFullRelations()->create();

        $response = $this->getJson('/api/v1/bills?include=containers,cuttingTests');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'containers',
                    'cuttingTests',
                ]],
            ]);

        $this->assertCount(4, $response->json('data.0.containers'));
        $this->assertCount(7, $response->json('data.0.cuttingTests')); // 3 + 1x4 container_cut
    }

    public function test_cutting_tests_have_correct_types(): void
    {
        $bill = Bill::factory()->withFullRelations()->create();

        $response = $this->getJson("/api/v1/bills/{$bill->id}?include=cuttingTests,containers.cuttingTest");

        $response->assertOk();

        $cuttingTypes = collect($response->json('data.cuttingTests'))->pluck('type');
        $expectedTypes = [
            CuttingTestType::FINAL_SAMPLE_FIRST_CUT->value,
            CuttingTestType::FINAL_SAMPLE_SECOND_CUT->value,
            CuttingTestType::FINAL_SAMPLE_THIRD_CUT->value,
        ];

        // count CONTAINER_CUT (from containers)
        $containerCuts = collect($response->json('data.containers'))
            ->pluck('cuttingTest.type')
            ->filter(fn ($type) => $type === CuttingTestType::CONTAINER_CUT->value);

        $this->assertCount(4, $containerCuts);
        $this->assertTrue($cuttingTypes->contains($expectedTypes[0]));
        $this->assertTrue($cuttingTypes->contains($expectedTypes[1]));
        $this->assertTrue($cuttingTypes->contains($expectedTypes[2]));
    }
}
