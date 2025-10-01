<?php

declare(strict_types=1);

use App\Models\Container;
use App\Models\Bill;
use App\Services\ContainerService;
use App\Repositories\ContainerRepository;
use App\Queries\ContainerQuery;
use InvalidArgumentException;

beforeEach(function () {
    $this->containerRepository = Mockery::mock(ContainerRepository::class);
    $this->containerQuery = Mockery::mock(ContainerQuery::class);
    $this->containerService = new ContainerService($this->containerRepository, $this->containerQuery);
});

afterEach(function () {
    Mockery::close();
});

describe('ContainerService CRUD Operations', function () {
    test('createContainer creates container with valid data', function () {
        $data = [
            'truck' => 'TRK-001',
            'container_number' => 'ABCD1234567',
            'quantity_of_bags' => 100,
            'w_total' => 25000,
            'w_truck' => 10000,
            'w_container' => 2500,
            'container_condition' => 'Nguyên vẹn',
            'seal_condition' => 'Nguyên vẹn',
            'bill_id' => 1,
        ];

        $expectedContainer = Container::factory()->make($data);
        $bill = Bill::factory()->make(['id' => 1]);

        $this->containerRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg['container_number'] === 'ABCD1234567' &&
                       $arg['w_gross'] === 12500; // 25000 - 10000 - 2500
            }))
            ->andReturn($expectedContainer);

        $expectedContainer->shouldReceive('bills->attach')->once()->with(1);
        $expectedContainer->shouldReceive('load')->once()->with('bills')->andReturnSelf();

        // Mock the updateCalculatedWeights method
        $this->containerService = Mockery::mock(ContainerService::class)->makePartial();
        $this->containerService->shouldReceive('updateCalculatedWeights')->once()->with($expectedContainer);

        $result = $this->containerService->createContainer($data);

        expect($result)->toBeInstanceOf(Container::class);
    });

    test('createContainer validates container number format', function () {
        $data = [
            'container_number' => 'INVALID123', // Invalid format
        ];

        expect(fn() => $this->containerService->createContainer($data))
            ->toThrow(InvalidArgumentException::class);
    });

    test('createContainer accepts valid container number formats', function () {
        $validNumbers = ['ABCD1234567', 'WXYZ9876543', 'TEST0000001'];

        foreach ($validNumbers as $containerNumber) {
            $data = [
                'container_number' => $containerNumber,
                'w_total' => 25000,
                'w_truck' => 10000,
                'w_container' => 2500,
            ];

            $expectedContainer = Container::factory()->make($data);

            $this->containerRepository
                ->shouldReceive('create')
                ->once()
                ->andReturn($expectedContainer);

            $expectedContainer->shouldReceive('load')->once()->with('bills')->andReturnSelf();

            $this->containerService = Mockery::mock(ContainerService::class)->makePartial();
            $this->containerService->shouldReceive('updateCalculatedWeights')->once();

            $result = $this->containerService->createContainer($data);

            expect($result)->toBeInstanceOf(Container::class);
        }
    });

    test('updateContainer updates container with valid data', function () {
        $container = Container::factory()->make();
        $data = [
            'container_condition' => 'Hư hỏng nhẹ',
            'seal_condition' => 'Bị phá',
            'bill_id' => 2,
        ];

        $container->shouldReceive('bills->sync')->once()->with([2]);

        $this->containerRepository
            ->shouldReceive('update')
            ->once()
            ->with($container, Mockery::on(function ($arg) {
                return !isset($arg['bill_id']); // bill_id should be removed
            }))
            ->andReturn(true);

        $this->containerService = Mockery::mock(ContainerService::class)->makePartial();
        $this->containerService->shouldReceive('updateCalculatedWeights')->once()->with($container);

        $result = $this->containerService->updateContainer($container, $data);

        expect($result)->toBeTrue();
    });
});

describe('ContainerService Weight Calculations', function () {
    test('calculateContainerWeights calculates all weights correctly', function () {
        $bill = Bill::factory()->make([
            'w_jute_bag' => 2.5,
            'w_dunnage_dribag' => 150,
        ]);

        $container = Container::factory()->make([
            'quantity_of_bags' => 100,
            'w_total' => 25000,
            'w_truck' => 10000,
            'w_container' => 2500,
        ]);

        $container->shouldReceive('bills->first')->once()->andReturn($bill);

        $calculations = $this->containerService->calculateContainerWeights($container);

        expect($calculations)->toHaveKeys(['w_gross', 'w_tare', 'w_net']);
        expect($calculations['w_gross'])->toBe(12500); // 25000 - 10000 - 2500
        expect($calculations['w_tare'])->toBe(250.0); // 100 * 2.5
        expect($calculations['w_net'])->toBe(12100.0); // 12500 - 150 - 250
    });

    test('calculateContainerWeights handles missing bill', function () {
        $container = Container::factory()->make();
        $container->shouldReceive('bills->first')->once()->andReturn(null);

        $calculations = $this->containerService->calculateContainerWeights($container);

        expect($calculations)->toHaveKey('error');
        expect($calculations['error'])->toBe('No associated bill found for weight calculations');
    });

    test('calculateContainerWeights handles partial data', function () {
        $bill = Bill::factory()->make([
            'w_jute_bag' => 2.5,
            'w_dunnage_dribag' => null, // Missing dunnage weight
        ]);

        $container = Container::factory()->make([
            'quantity_of_bags' => 100,
            'w_total' => 25000,
            'w_truck' => 10000,
            'w_container' => 2500,
        ]);

        $container->shouldReceive('bills->first')->once()->andReturn($bill);

        $calculations = $this->containerService->calculateContainerWeights($container);

        expect($calculations)->toHaveKeys(['w_gross', 'w_tare']);
        expect($calculations)->not->toHaveKey('w_net'); // Cannot calculate without dunnage weight
        expect($calculations['w_gross'])->toBe(12500);
        expect($calculations['w_tare'])->toBe(250.0);
    });

    test('calculateContainerWeights ensures non-negative weights', function () {
        $bill = Bill::factory()->make([
            'w_jute_bag' => 2.5,
            'w_dunnage_dribag' => 15000, // Very high dunnage weight
        ]);

        $container = Container::factory()->make([
            'quantity_of_bags' => 100,
            'w_total' => 25000,
            'w_truck' => 10000,
            'w_container' => 2500,
        ]);

        $container->shouldReceive('bills->first')->once()->andReturn($bill);

        $calculations = $this->containerService->calculateContainerWeights($container);

        expect($calculations['w_net'])->toBe(0.0); // Should not be negative
    });

    test('updateCalculatedWeights updates container with calculated values', function () {
        $container = Container::factory()->make();
        
        $this->containerService = Mockery::mock(ContainerService::class)->makePartial();
        $this->containerService
            ->shouldReceive('calculateContainerWeights')
            ->once()
            ->with($container)
            ->andReturn([
                'w_gross' => 12500,
                'w_tare' => 250.0,
                'w_net' => 12100.0,
            ]);

        $container->shouldReceive('save')->once()->andReturn(true);

        $result = $this->containerService->updateCalculatedWeights($container);

        expect($result)->toBeTrue();
        expect($container->w_gross)->toBe(12500);
        expect($container->w_tare)->toBe(250.0);
        expect($container->w_net)->toBe(12100.0);
    });
});

describe('ContainerService Weight Discrepancy Alerts', function () {
    test('getWeightDiscrepancyAlerts detects gross weight discrepancy', function () {
        $bill = Bill::factory()->make([
            'w_jute_bag' => 2.5,
            'w_dunnage_dribag' => 150,
        ]);

        $container = Container::factory()->make([
            'quantity_of_bags' => 100,
            'w_total' => 25000,
            'w_truck' => 10000,
            'w_container' => 2500,
            'w_gross' => 12000, // Stored value differs from calculated (12500)
            'w_tare' => 250,
            'w_net' => 11600,
        ]);

        $container->shouldReceive('bills->first')->twice()->andReturn($bill);

        $this->containerService = Mockery::mock(ContainerService::class)->makePartial();
        $this->containerService
            ->shouldReceive('calculateContainerWeights')
            ->once()
            ->with($container)
            ->andReturn([
                'w_gross' => 12500,
                'w_tare' => 250.0,
                'w_net' => 12100.0,
            ]);

        $alerts = $this->containerService->getWeightDiscrepancyAlerts($container);

        expect($alerts)->toContain('Gross weight discrepancy: calculated 12500kg vs stored 12000kg');
    });

    test('getWeightDiscrepancyAlerts detects missing weight data', function () {
        $bill = Bill::factory()->make();
        $container = Container::factory()->make([
            'w_total' => null,
            'w_truck' => null,
            'w_container' => 2500,
        ]);

        $container->shouldReceive('bills->first')->twice()->andReturn($bill);

        $this->containerService = Mockery::mock(ContainerService::class)->makePartial();
        $this->containerService
            ->shouldReceive('calculateContainerWeights')
            ->once()
            ->andReturn([]);

        $alerts = $this->containerService->getWeightDiscrepancyAlerts($container);

        expect($alerts)->toContain('Total weight is missing');
        expect($alerts)->toContain('Truck weight is missing');
    });

    test('getWeightDiscrepancyAlerts detects logical weight errors', function () {
        $bill = Bill::factory()->make();
        $container = Container::factory()->make([
            'w_gross' => 10000,
            'w_net' => 12000, // Net > Gross (impossible)
        ]);

        $container->shouldReceive('bills->first')->twice()->andReturn($bill);

        $this->containerService = Mockery::mock(ContainerService::class)->makePartial();
        $this->containerService
            ->shouldReceive('calculateContainerWeights')
            ->once()
            ->andReturn([]);

        $alerts = $this->containerService->getWeightDiscrepancyAlerts($container);

        expect($alerts)->toContain('Net weight cannot be greater than gross weight');
    });

    test('getContainersWithWeightDiscrepancies filters containers with alerts', function () {
        $container1 = Container::factory()->make(['w_total' => null]);
        $container2 = Container::factory()->make(['w_total' => 25000]);
        $containers = collect([$container1, $container2]);

        $this->containerRepository
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($containers);

        $this->containerService = Mockery::mock(ContainerService::class)->makePartial();
        $this->containerService
            ->shouldReceive('getWeightDiscrepancyAlerts')
            ->with($container1)
            ->andReturn(['Total weight is missing']);
        $this->containerService
            ->shouldReceive('getWeightDiscrepancyAlerts')
            ->with($container2)
            ->andReturn([]);

        $result = $this->containerService->getContainersWithWeightDiscrepancies();

        expect($result)->toHaveCount(1);
        expect($result->first()->weight_alerts)->toContain('Total weight is missing');
    });
});

describe('ContainerService Business Logic', function () {
    test('calculateAverageMoisture returns correct average', function () {
        $container = Container::factory()->make();
        
        $tests = collect([
            (object) ['moisture' => 10.5],
            (object) ['moisture' => 11.2],
            (object) ['moisture' => 9.8],
        ]);

        $container->shouldReceive('cuttingTests->whereNotNull->get')
            ->once()
            ->andReturn($tests);

        $average = $this->containerService->calculateAverageMoisture($container);

        expect($average)->toBe(10.5); // (10.5 + 11.2 + 9.8) / 3 = 10.5
    });

    test('calculateAverageMoisture returns null for no tests', function () {
        $container = Container::factory()->make();
        
        $container->shouldReceive('cuttingTests->whereNotNull->get')
            ->once()
            ->andReturn(collect());

        $average = $this->containerService->calculateAverageMoisture($container);

        expect($average)->toBeNull();
    });

    test('getOutturnRate returns outturn rate from cutting test', function () {
        $container = Container::factory()->make();
        
        $test = (object) ['outturn_rate' => 52.5];

        $container->shouldReceive('cuttingTests->whereNotNull->first')
            ->once()
            ->andReturn($test);

        $outturnRate = $this->containerService->getOutturnRate($container);

        expect($outturnRate)->toBe(52.5);
    });

    test('getOutturnRate returns null for no cutting test', function () {
        $container = Container::factory()->make();
        
        $container->shouldReceive('cuttingTests->whereNotNull->first')
            ->once()
            ->andReturn(null);

        $outturnRate = $this->containerService->getOutturnRate($container);

        expect($outturnRate)->toBeNull();
    });

    test('getContainersByCondition filters by condition', function () {
        $containers = collect([
            Container::factory()->make(['container_condition' => 'Nguyên vẹn']),
            Container::factory()->make(['container_condition' => 'Hư hỏng']),
            Container::factory()->make(['seal_condition' => 'Nguyên vẹn']),
        ]);

        $this->containerRepository
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($containers);

        $result = $this->containerService->getContainersByCondition('Nguyên vẹn');

        expect($result)->toHaveCount(2); // Both container and seal condition matches
    });
});

describe('ContainerService Statistics', function () {
    test('getContainerStatistics returns complete statistics', function () {
        $highMoistureContainers = collect([Container::factory()->make()]);
        $pendingTestsContainers = collect([Container::factory()->make()]);

        $this->containerQuery
            ->shouldReceive('getContainersWithHighMoisture')
            ->once()
            ->with(11.0)
            ->andReturn($highMoistureContainers);

        $this->containerQuery
            ->shouldReceive('getContainersPendingCuttingTests')
            ->once()
            ->andReturn($pendingTestsContainers);

        $statistics = $this->containerService->getContainerStatistics();

        expect($statistics)->toHaveKeys([
            'high_moisture_count',
            'pending_tests_count',
            'high_moisture_containers',
            'pending_tests_containers',
        ]);
        expect($statistics['high_moisture_count'])->toBe(1);
        expect($statistics['pending_tests_count'])->toBe(1);
    });
});

describe('ContainerService Edge Cases', function () {
    test('createContainer handles zero weights gracefully', function () {
        $data = [
            'w_total' => 0,
            'w_truck' => 0,
            'w_container' => 0,
        ];

        $expectedContainer = Container::factory()->make($data);

        $this->containerRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg['w_gross'] === 0; // 0 - 0 - 0 = 0
            }))
            ->andReturn($expectedContainer);

        $expectedContainer->shouldReceive('load')->once()->with('bills')->andReturnSelf();

        $this->containerService = Mockery::mock(ContainerService::class)->makePartial();
        $this->containerService->shouldReceive('updateCalculatedWeights')->once();

        $result = $this->containerService->createContainer($data);

        expect($result)->toBeInstanceOf(Container::class);
    });

    test('calculateContainerWeights handles extreme weight values', function () {
        $bill = Bill::factory()->make([
            'w_jute_bag' => 0.1, // Very small jute bag weight
            'w_dunnage_dribag' => 50000, // Very large dunnage weight
        ]);

        $container = Container::factory()->make([
            'quantity_of_bags' => 1000, // Many bags
            'w_total' => 100000,
            'w_truck' => 15000,
            'w_container' => 3000,
        ]);

        $container->shouldReceive('bills->first')->once()->andReturn($bill);

        $calculations = $this->containerService->calculateContainerWeights($container);

        expect($calculations['w_gross'])->toBe(82000); // 100000 - 15000 - 3000
        expect($calculations['w_tare'])->toBe(100.0); // 1000 * 0.1
        expect($calculations['w_net'])->toBe(0.0); // max(0, 82000 - 50000 - 100) = 0
    });

    test('validateContainerNumber rejects various invalid formats', function () {
        $invalidNumbers = [
            'ABC1234567',    // Only 3 letters
            'ABCDE1234567',  // 5 letters
            'ABCD123456',    // Only 6 digits
            'ABCD12345678',  // 8 digits
            'abcd1234567',   // Lowercase letters
            'ABCD123456A',   // Letter in digit position
            '1234ABCD567',   // Numbers in letter position
            '',              // Empty string
            'ABCD-1234567',  // Contains hyphen
        ];

        foreach ($invalidNumbers as $containerNumber) {
            $data = ['container_number' => $containerNumber];

            expect(fn() => $this->containerService->createContainer($data))
                ->toThrow(InvalidArgumentException::class);
        }
    });
});