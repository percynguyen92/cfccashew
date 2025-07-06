<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use Database\Seeders\DatabaseSeeder;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_valid_structure(): void
    {
        // Run the seeder
        $this->seed(DatabaseSeeder::class);

        // Assert 10 bills created
        $this->assertCount(10, Bill::all());

        // Loop over each bill
        Bill::with(['containers.cuttingTest', 'cuttingTests'])->each(function (Bill $bill) {
            // Assert bill has 4–7 containers
            $this->assertGreaterThanOrEqual(4, $bill->containers->count(), "Bill ID {$bill->id} should have at least 4 containers");
            $this->assertLessThanOrEqual(7, $bill->containers->count(), "Bill ID {$bill->id} should have at most 7 containers");

            // Assert bill has 3 cutting tests with type 1, 2, 3
            $finalSampleTypes = $bill->cuttingTests
                ->pluck('type')
                ->map(fn($enum) => $enum->value)
                ->unique()
                ->sort()
                ->values();
            $this->assertEqualsCanonicalizing([1, 2, 3, 4], $finalSampleTypes->all(), "Bill ID {$bill->id} should have cutting tests of types 1, 2, and 3");

            // Assert each container has 1 cutting test of type 4
            foreach ($bill->containers as $container) {
                $this->assertNotNull($container->cuttingTest, "Container ID {$container->id} should have a cutting test");
                $this->assertEquals(4, $container->cuttingTest->type->value, "Container ID {$container->id} should have a cutting test of type 4");
                $this->assertEquals($bill->id, $container->cuttingTest->bill_id, "Container ID {$container->id} cutting test should belong to Bill ID {$bill->id}");
            }
        });

        // Optional: Total count assertions
        $this->assertEquals(10, Bill::count());
        $this->assertGreaterThanOrEqual(40, Container::count());
        $this->assertLessThanOrEqual(70, Container::count());
        $this->assertGreaterThanOrEqual(70, CuttingTest::count());
        $this->assertLessThanOrEqual(100, CuttingTest::count());
    }
}
