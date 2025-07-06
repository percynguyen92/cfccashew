<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BillTest extends TestCase
{
    use RefreshDatabase;

    public function test_bill_has_many_containers(): void
    {
        $bill = Bill::factory()->create();
        $bill->containers()->createMany(
            Container::factory()->count(2)->make()->toArray()
        );

        $this->assertCount(2, $bill->containers);
        $this->assertInstanceOf(Container::class, $bill->containers->first());
    }

    public function test_bill_has_many_cutting_tests(): void
    {
        $bill = Bill::factory()->create();
        $bill->cuttingTests()->createMany(
            CuttingTest::factory()->count(3)->make()->toArray()
        );

        $this->assertCount(3, $bill->cuttingTests);
        $this->assertInstanceOf(CuttingTest::class, $bill->cuttingTests->first());
    }
}
