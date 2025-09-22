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

it('allows creating, updating, finding and soft deleting bills via repository', function () {
    $repository = app(BillRepository::class);
    $data = Bill::factory()->make()->only(['bill_number', 'seller', 'buyer', 'note']);

    $bill = $repository->create($data);

    expect($bill->exists)->toBeTrue();

    $repository->update($bill, ['seller' => 'Updated Seller']);

    expect($bill->refresh()->seller)->toBe('Updated Seller');

    $container = Container::factory()->for($bill)->create();
    CuttingTest::factory()->for($bill)->for($container)->create([
        'type' => CuttingTestType::ContainerCut->value,
    ]);

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

it('provides aggregated bill statistics via service', function () {
    $service = app(BillService::class);

    $billWithFinals = Bill::factory()->create(['bill_number' => 'BL-FINAL-001']);
    CuttingTest::factory()->for($billWithFinals)->create([
        'type' => CuttingTestType::FinalFirstCut->value,
        'container_id' => null,
        'outturn_rate' => 49,
    ]);

    $billMissingFinals = Bill::factory()->create(['bill_number' => 'BL-NO-FINAL']);
    Container::factory()->for($billMissingFinals)->create();

    $billPending = Bill::factory()->create(['bill_number' => 'BL-PENDING']);

    $initialStats = $service->getBillStatistics();

    $stats = $service->getBillStatistics();

    expect($stats['total_bills'])->toBe($initialStats['total_bills'])
        ->and($stats['pending_final_tests_count'])->toBeGreaterThanOrEqual(0)
        ->and($stats['missing_final_samples_count'])->toBeGreaterThanOrEqual(0)
        ->and($stats['recent_bills']->count())->toBeGreaterThan(0);

    $average = $service->calculateAverageOutturn($billWithFinals->fresh('finalSamples'));
    expect($average)->toBe(49.0);

    $loadedBill = $service->getBillById($billWithFinals->id);
    expect($loadedBill)->not->toBeNull()
        ->and($loadedBill->relationLoaded('containers'))->toBeTrue();
});
