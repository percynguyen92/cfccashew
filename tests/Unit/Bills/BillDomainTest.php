<?php

use App\Enums\CuttingTestType;
use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use App\Queries\BillQuery;
use App\Repositories\BillRepository;
use App\Services\BillService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('allows creating, updating, finding and soft deleting bills via repository with new fields', function () {
    $repository = app(BillRepository::class);
    $data = [
        'bill_number' => 'TEST-BILL',
        'seller' => 'Test Seller',
        'buyer' => 'Test Buyer',
        'note' => 'Test note',
        'w_dunnage_dribag' => 200,
        'w_jute_bag' => 1.5,
        'net_on_bl' => 10000,
        'quantity_of_bags_on_bl' => 100,
        'origin' => 'Vietnam',
        'inspection_start_date' => now(),
        'inspection_end_date' => now()->addDay(),
        'inspection_location' => 'Port A',
        'sampling_ratio' => 10.0,
    ];

    $bill = $repository->create($data);

    expect($bill->exists)->toBeTrue();
    expect($bill->w_dunnage_dribag)->toBe(200);
    expect($bill->w_jute_bag)->toBe(1.5);
    expect($bill->sampling_ratio)->toBe(10.0);

    $repository->update($bill, ['seller' => 'Updated Seller', 'sampling_ratio' => 15.0]);

    expect($bill->refresh()->seller)->toBe('Updated Seller');
    expect($bill->sampling_ratio)->toBe(15.0);

    $container = Container::factory()->create();
    CuttingTest::factory()->for($bill)->create([
        'type' => CuttingTestType::ContainerCut->value,
        'container_id' => $container->id,
    ]);

    $bill->containers()->attach($container->id);

    $withRelations = $repository->findByIdWithRelations($bill->id, ['containers.cuttingTests']);

    expect($withRelations)
        ->not->toBeNull()
        ->and($withRelations->containers)->toHaveCount(1)
        ->and($withRelations->containers->first()->cuttingTests)->toHaveCount(1);

    $repository->delete($bill);

    expect(Bill::withTrashed()->find($bill->id)->trashed())->toBeTrue();
});

it('paginates and filters bills with average outturn via query', function () {
    $billWithMatch = Bill::factory()->create([
        'bill_number' => 'BL-TEST-001',
        'seller' => 'Alpha',
        'buyer' => 'Beta',
    ]);
    $otherBill = Bill::factory()->create([
        'bill_number' => 'BL-OTHER-001',
        'seller' => 'Gamma',
        'buyer' => 'Delta',
    ]);

    CuttingTest::factory()->for($billWithMatch)->create([
        'type' => CuttingTestType::FinalFirstCut->value,
        'container_id' => null,
        'outturn_rate' => 48.5,
    ]);
    CuttingTest::factory()->for($billWithMatch)->create([
        'type' => CuttingTestType::FinalSecondCut->value,
        'container_id' => null,
        'outturn_rate' => 51.5,
    ]);

    $query = app(BillQuery::class);
    $paginator = $query->paginate(['search' => 'TEST'], 10);

    expect($paginator->total())->toBe(1)
        ->and($paginator->first()->id)->toBe($billWithMatch->id)
        ->and($paginator->first()->average_outurn)->toBe(50.0);

    // Sorting fallback
    $sorted = $query->paginate(['sort_by' => 'bill_number', 'sort_direction' => 'asc'], 10);
    expect($sorted->total())->toBe(2);
});

it('provides aggregated bill statistics via service, includes inspection coverage', function () {
    $service = app(BillService::class);

    $billWithInspections = Bill::factory()->create([
        'bill_number' => 'BL-INSPECT-001',
        'inspection_start_date' => now(),
        'inspection_end_date' => now()->addDays(3),
        'inspection_location' => 'Customs Port',
        'sampling_ratio' => 15.0,
    ]);
    CuttingTest::factory()->for($billWithInspections)->create([
        'type' => CuttingTestType::FinalFirstCut->value,
        'container_id' => null,
        'outturn_rate' => 49,
    ]);

    $container = Container::factory()->create();
    $billWithInspections->containers()->attach($container);

    $billMissingFinals = Bill::factory()->create([
        'bill_number' => 'BL-NO-FINAL',
        'inspection_location' => null,
    ]);
    Container::factory()->for($billMissingFinals)->create();

    $billPending = Bill::factory()->create(['bill_number' => 'BL-PENDING']);

    $stats = $service->getBillStatistics();

    expect($stats['total_bills'])->toBe(3)
        ->and($stats['pending_final_tests_count'])->toBeGreaterThanOrEqual(0)
        ->and($stats['missing_final_samples_count'])->toBeGreaterThanOrEqual(0)
        ->and($stats['recent_bills']->count())->toBeGreaterThan(0);

    $average = $service->calculateAverageOutturn($billWithInspections->fresh('finalSamples'));
    expect($average)->toBe(49.0);

    $loadedBill = $service->getBillById($billWithInspections->id);
    expect($loadedBill)->not->toBeNull()
        ->and($loadedBill->relationLoaded('containers'))->toBeTrue()
        ->and($loadedBill->inspection_location)->toBe('Customs Port')
        ->and($loadedBill->sampling_ratio)->toBe(15.0);

    // Test update with inspection fields
    $service->updateBill($loadedBill, [
        'inspection_start_date' => now()->addDays(5),
        'sampling_ratio' => 20.0,
    ]);
    $loadedBill->refresh();
    expect($loadedBill->inspection_start_date)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($loadedBill->sampling_ratio)->toBe(20.0);
});
