<?php

declare(strict_types=1);

use App\Models\Bill;
use App\Models\Container;
use App\Services\BillService;
use App\Repositories\BillRepository;
use App\Queries\BillQuery;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

beforeEach(function () {
    $this->billRepository = Mockery::mock(BillRepository::class);
    $this->billQuery = Mockery::mock(BillQuery::class);
    $this->billService = new BillService($this->billRepository, $this->billQuery);
});

afterEach(function () {
    Mockery::close();
});

describe('BillService CRUD Operations', function () {
    test('createBill creates bill with valid data', function () {
        $data = [
            'bill_number' => 'BL-2024-001',
            'seller' => 'Test Seller',
            'buyer' => 'Test Buyer',
            'w_dunnage_dribag' => 100,
            'w_jute_bag' => 2.5,
            'net_on_bl' => 20000,
            'quantity_of_bags_on_bl' => 200,
            'origin' => 'Vietnam',
            'inspection_start_date' => '2024-01-01 08:00:00',
            'inspection_end_date' => '2024-01-02 17:00:00',
            'inspection_location' => 'Ho Chi Minh City',
            'sampling_ratio' => 10.5,
        ];

        $expectedBill = Bill::factory()->make($data);
        
        $this->billRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) use ($data) {
                return $arg['bill_number'] === $data['bill_number'] &&
                       $arg['w_jute_bag'] === $data['w_jute_bag'] &&
                       $arg['sampling_ratio'] === $data['sampling_ratio'];
            }))
            ->andReturn($expectedBill);

        $result = $this->billService->createBill($data);

        expect($result)->toBeInstanceOf(Bill::class);
    });

    test('createBill validates inspection dates', function () {
        $data = [
            'bill_number' => 'BL-2024-001',
            'inspection_start_date' => '2024-01-02 08:00:00',
            'inspection_end_date' => '2024-01-01 17:00:00', // End before start
        ];

        expect(fn() => $this->billService->createBill($data))
            ->toThrow(ValidationException::class);
    });

    test('createBill validates future inspection dates', function () {
        $futureDate = Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
        
        $data = [
            'bill_number' => 'BL-2024-001',
            'inspection_start_date' => $futureDate,
        ];

        expect(fn() => $this->billService->createBill($data))
            ->toThrow(ValidationException::class);
    });

    test('createBill validates sampling ratio range', function () {
        $data = [
            'bill_number' => 'BL-2024-001',
            'sampling_ratio' => 150, // Invalid: > 100
        ];

        expect(fn() => $this->billService->createBill($data))
            ->toThrow(ValidationException::class);
    });

    test('createBill validates negative sampling ratio', function () {
        $data = [
            'bill_number' => 'BL-2024-001',
            'sampling_ratio' => -5, // Invalid: negative
        ];

        expect(fn() => $this->billService->createBill($data))
            ->toThrow(ValidationException::class);
    });

    test('createBill validates weight fields', function () {
        $data = [
            'bill_number' => 'BL-2024-001',
            'w_dunnage_dribag' => -50, // Invalid: negative
            'w_jute_bag' => 0, // Invalid: must be > 0
            'net_on_bl' => -1000, // Invalid: must be > 0
            'quantity_of_bags_on_bl' => 0, // Invalid: must be > 0
        ];

        expect(fn() => $this->billService->createBill($data))
            ->toThrow(ValidationException::class);
    });

    test('updateBill updates bill with valid data', function () {
        $bill = Bill::factory()->make();
        $data = [
            'seller' => 'Updated Seller',
            'sampling_ratio' => 15.5,
        ];

        $this->billRepository
            ->shouldReceive('update')
            ->once()
            ->with($bill, Mockery::type('array'))
            ->andReturn(true);

        $result = $this->billService->updateBill($bill, $data);

        expect($result)->toBeTrue();
    });

    test('getBillById returns bill with relations', function () {
        $billId = 1;
        $expectedBill = Bill::factory()->make(['id' => $billId]);

        $this->billRepository
            ->shouldReceive('findByIdWithRelations')
            ->once()
            ->with($billId, Mockery::type('array'))
            ->andReturn($expectedBill);

        $result = $this->billService->getBillById($billId);

        expect($result)->toBe($expectedBill);
    });
});

describe('BillService Weight Calculations', function () {
    test('updateContainerWeights recalculates weights for all containers', function () {
        $bill = Bill::factory()->make([
            'w_jute_bag' => 2.5,
            'w_dunnage_dribag' => 100,
        ]);

        $container1 = Container::factory()->make([
            'quantity_of_bags' => 100,
            'w_gross' => 15000,
        ]);
        $container2 = Container::factory()->make([
            'quantity_of_bags' => 150,
            'w_gross' => 20000,
        ]);

        $bill->setRelation('containers', collect([$container1, $container2]));

        // Mock the container methods
        $container1->shouldReceive('calculateTareWeight')->once()->andReturn(250);
        $container1->shouldReceive('calculateNetWeight')->once()->andReturn(14650);
        $container1->shouldReceive('save')->once()->andReturn(true);

        $container2->shouldReceive('calculateTareWeight')->once()->andReturn(375);
        $container2->shouldReceive('calculateNetWeight')->once()->andReturn(19525);
        $container2->shouldReceive('save')->once()->andReturn(true);

        $this->billService->updateContainerWeights($bill);

        expect($container1->w_tare)->toBe(250);
        expect($container1->w_net)->toBe(14650);
        expect($container2->w_tare)->toBe(375);
        expect($container2->w_net)->toBe(19525);
    });

    test('validateWeightCalculationFields identifies missing fields', function () {
        $bill = Bill::factory()->make([
            'w_jute_bag' => null,
            'w_dunnage_dribag' => 100,
        ]);

        $missingFields = $this->billService->validateWeightCalculationFields($bill);

        expect($missingFields)->toContain('w_jute_bag');
        expect($missingFields)->not->toContain('w_dunnage_dribag');
    });

    test('validateWeightCalculationFields returns empty for complete fields', function () {
        $bill = Bill::factory()->make([
            'w_jute_bag' => 2.5,
            'w_dunnage_dribag' => 100,
        ]);

        $missingFields = $this->billService->validateWeightCalculationFields($bill);

        expect($missingFields)->toBeEmpty();
    });
});

describe('BillService Business Logic', function () {
    test('calculateAverageOutturn returns correct average', function () {
        $bill = Bill::factory()->make();
        
        $finalSamples = collect([
            (object) ['outturn_rate' => 50.5],
            (object) ['outturn_rate' => 52.3],
            (object) ['outturn_rate' => 48.7],
        ]);

        $bill->shouldReceive('finalSamples->whereNotNull->get')
            ->once()
            ->andReturn($finalSamples);

        $average = $this->billService->calculateAverageOutturn($bill);

        expect($average)->toBe(50.5); // (50.5 + 52.3 + 48.7) / 3 = 50.5
    });

    test('calculateAverageOutturn returns null for no samples', function () {
        $bill = Bill::factory()->make();
        
        $bill->shouldReceive('finalSamples->whereNotNull->get')
            ->once()
            ->andReturn(collect());

        $average = $this->billService->calculateAverageOutturn($bill);

        expect($average)->toBeNull();
    });

    test('getBillsByOrigin filters by origin', function () {
        $origin = 'Vietnam';
        $bills = collect([
            Bill::factory()->make(['origin' => 'Vietnam']),
            Bill::factory()->make(['origin' => 'India']),
        ]);

        $this->billRepository
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($bills);

        $result = $this->billService->getBillsByOrigin($origin);

        expect($result)->toHaveCount(1);
        expect($result->first()->origin)->toBe('Vietnam');
    });

    test('getBillsByInspectionDateRange filters by date range', function () {
        $startDate = Carbon::parse('2024-01-01');
        $endDate = Carbon::parse('2024-01-31');
        
        $bills = collect([
            Bill::factory()->make(['inspection_start_date' => '2024-01-15']),
            Bill::factory()->make(['inspection_start_date' => '2024-02-15']),
        ]);

        $this->billRepository
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($bills);

        $result = $this->billService->getBillsByInspectionDateRange($startDate, $endDate);

        expect($result)->toHaveCount(1);
    });

    test('getBillsBySamplingRatio filters by ratio range', function () {
        $minRatio = 10.0;
        $maxRatio = 20.0;
        
        $bills = collect([
            Bill::factory()->make(['sampling_ratio' => 15.5]),
            Bill::factory()->make(['sampling_ratio' => 25.0]),
        ]);

        $this->billRepository
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($bills);

        $result = $this->billService->getBillsBySamplingRatio($minRatio, $maxRatio);

        expect($result)->toHaveCount(1);
        expect($result->first()->sampling_ratio)->toBe(15.5);
    });
});

describe('BillService Statistics', function () {
    test('getBillStatistics returns complete statistics', function () {
        $recentBills = collect([Bill::factory()->make()]);
        $pendingTests = collect([Bill::factory()->make()]);
        $missingFinalSamples = collect([Bill::factory()->make()]);
        $allBills = collect([Bill::factory()->make(), Bill::factory()->make()]);

        $this->billRepository
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($allBills);

        $this->billQuery
            ->shouldReceive('getRecentBills')
            ->once()
            ->with(5)
            ->andReturn($recentBills);

        $this->billQuery
            ->shouldReceive('getBillsPendingFinalTests')
            ->twice()
            ->andReturn($pendingTests);

        $this->billQuery
            ->shouldReceive('getBillsMissingFinalSamples')
            ->twice()
            ->andReturn($missingFinalSamples);

        $statistics = $this->billService->getBillStatistics();

        expect($statistics)->toHaveKeys([
            'total_bills',
            'recent_bills',
            'pending_final_tests_count',
            'missing_final_samples_count',
            'pending_final_tests',
            'missing_final_samples',
        ]);
        expect($statistics['total_bills'])->toBe(2);
        expect($statistics['pending_final_tests_count'])->toBe(1);
        expect($statistics['missing_final_samples_count'])->toBe(1);
    });
});

describe('BillService Edge Cases', function () {
    test('processInspectionDates handles null dates', function () {
        $data = [
            'bill_number' => 'BL-2024-001',
            'inspection_start_date' => null,
            'inspection_end_date' => null,
        ];

        $expectedBill = Bill::factory()->make($data);
        
        $this->billRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($expectedBill);

        $result = $this->billService->createBill($data);

        expect($result)->toBeInstanceOf(Bill::class);
    });

    test('createBill handles minimum valid sampling ratio', function () {
        $data = [
            'bill_number' => 'BL-2024-001',
            'sampling_ratio' => 0.01, // Minimum valid value
        ];

        $expectedBill = Bill::factory()->make($data);
        
        $this->billRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($expectedBill);

        $result = $this->billService->createBill($data);

        expect($result)->toBeInstanceOf(Bill::class);
    });

    test('createBill handles maximum valid sampling ratio', function () {
        $data = [
            'bill_number' => 'BL-2024-001',
            'sampling_ratio' => 100.0, // Maximum valid value
        ];

        $expectedBill = Bill::factory()->make($data);
        
        $this->billRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($expectedBill);

        $result = $this->billService->createBill($data);

        expect($result)->toBeInstanceOf(Bill::class);
    });
});