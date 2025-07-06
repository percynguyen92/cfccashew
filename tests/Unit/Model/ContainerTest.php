<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use App\Enums\CuttingTestType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ContainerTest extends TestCase
{
    use RefreshDatabase;

    public function test_container_belongs_to_bill(): void
    {
        $bill = Bill::factory()->create();
        $container = Container::factory()->for($bill)->create();

        $this->assertInstanceOf(Bill::class, $container->bill);
        $this->assertTrue($bill->is($container->bill));
    }

    public function test_container_has_one_cutting_test(): void
    {
        $bill = Bill::factory()->create();
        $container = Container::factory()->for($bill)->create();
        $cuttingTest = CuttingTest::factory()
            ->for($bill)
            ->for($container)
            ->state([
                'type' => CuttingTestType::CONTAINER_CUT->value,
            ])
            ->create();

        $this->assertInstanceOf(CuttingTest::class, $container->cuttingTest);
        $this->assertTrue($container->cuttingTest->is($cuttingTest));
    }
}
