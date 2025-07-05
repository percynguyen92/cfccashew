<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\CuttingTestType;
use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CuttingTestTest extends TestCase
{
    use RefreshDatabase;

    public function test_cutting_test_belongs_to_bill(): void
    {
        $bill = Bill::factory()->create();
        $cuttingTest = CuttingTest::factory()->for($bill)->create();

        $this->assertInstanceOf(Bill::class, $cuttingTest->bill);
        $this->assertTrue($bill->is($cuttingTest->bill));
    }

    public function test_cutting_test_belongs_to_container(): void
    {
        $bill = Bill::factory()->create();
        $container = Container::factory()->for($bill)->create();
        $cuttingTest = CuttingTest::factory()
            ->for($bill)
            ->for($container)
            ->create();

        $this->assertInstanceOf(Container::class, $cuttingTest->container);
        $this->assertTrue($container->is($cuttingTest->container));
    }

    public function test_cutting_test_type_enum_casting(): void
    {
        $bill = Bill::factory()->create();
        $container = Container::factory()->for($bill)->create();
        $cuttingTest = CuttingTest::factory()->create([
            'type' => CuttingTestType::FINAL_SAMPLE_SECOND_CUT,
            'bill_id' => $bill->id,
            'container_id' => $container->id,
        ]);

        $this->assertInstanceOf(CuttingTestType::class, $cuttingTest->type);
        $this->assertEquals(CuttingTestType::FINAL_SAMPLE_SECOND_CUT, $cuttingTest->type);
        $this->assertSame(2, $cuttingTest->type->value);
    }

    public function test_cutting_test_accepts_all_enum_values(): void
    {
        foreach (CuttingTestType::cases() as $type) {
            $bill = Bill::factory()->create();
            $container = Container::factory()->for($bill)->create();
            $cuttingTest = CuttingTest::factory()->for($bill)->for($container)->create([
                'type' => $type,
            ]);

            $this->assertEquals($type, $cuttingTest->fresh()->type);
        }
    }

    public function test_cutting_test_requires_foreign_keys(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        CuttingTest::factory()->create([
            'bill_id' => null,
            'container_id' => null,
        ]);
    }
}
