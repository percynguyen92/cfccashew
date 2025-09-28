<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class BillControllerTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and authenticate
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_bills_index_page_loads_successfully()
    {
        $response = $this->get(route('bills.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Bills/Index')
                ->has('bills')
                ->has('filters')
        );
    }

    public function test_bills_index_displays_bills_with_container_count()
    {
        // Create a bill with containers
        $billNumber = 'COUNT-' . Str::upper(Str::random(6));
        $bill = Bill::factory()->create([
            'bill_number' => $billNumber,
            'seller' => 'Test Seller',
            'buyer' => 'Test Buyer',
        ]);

        Container::factory()->count(3)->create(['bill_id' => $bill->id]);

        $response = $this->get(route('bills.index', ['search' => $billNumber]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Bills/Index')
        );

        $records = collect(json_decode(json_encode($response->viewData('page')), true)['props']['bills']['data'] ?? []);
        $record = $records->firstWhere('bill_number', $billNumber);

        expect($record)->not->toBeNull();
        expect($record['seller'])->toBe('Test Seller');
        expect($record['buyer'])->toBe('Test Buyer');
        expect($record['containers_count'])->toBe(3);
    }

    public function test_bills_index_search_functionality()
    {
        $billNumberOne = 'SEARCH-' . Str::upper(Str::random(4));
        $sellerName = 'Seller ' . Str::random(5);
        $buyerName = 'Buyer ' . Str::random(5);

        $billOne = Bill::factory()->create([
            'bill_number' => $billNumberOne,
            'seller' => $sellerName,
            'buyer' => $buyerName,
        ]);

        $sellerSearch = 'Premium ' . Str::random(4);
        $buyerSearch = 'European ' . Str::random(4);

        $billTwo = Bill::factory()->create([
            'bill_number' => 'SEARCH-' . Str::upper(Str::random(4)),
            'seller' => $sellerSearch,
            'buyer' => $buyerSearch,
        ]);

        // Search by bill number
        $response = $this->get(route('bills.index', ['search' => $billNumberOne]));
        $records = collect(json_decode(json_encode($response->viewData('page')), true)['props']['bills']['data'] ?? []);
        expect($records->pluck('bill_number'))->toContain($billNumberOne);

        // Search by seller
        $response = $this->get(route('bills.index', ['search' => $sellerSearch]));
        $records = collect(json_decode(json_encode($response->viewData('page')), true)['props']['bills']['data'] ?? []);
        expect($records->pluck('seller'))->toContain($sellerSearch);

        // Search by buyer
        $response = $this->get(route('bills.index', ['search' => $buyerSearch]));
        $records = collect(json_decode(json_encode($response->viewData('page')), true)['props']['bills']['data'] ?? []);
        expect($records->pluck('buyer'))->toContain($buyerSearch);
    }

    public function test_bills_index_sorting_functionality()
    {
        $billB = Bill::factory()->create([
            'bill_number' => 'SORT-' . Str::upper(Str::random(4)),
            'seller' => 'Sort Seller B',
        ]);

        $billA = Bill::factory()->create([
            'bill_number' => 'SORT-' . Str::upper(Str::random(4)),
            'seller' => 'Sort Seller A',
        ]);

        // Sort by bill_number ascending limited to our test records
        $response = $this->get(route('bills.index', [
            'sort_by' => 'bill_number',
            'sort_direction' => 'asc',
            'search' => 'SORT-'
        ]));

        $records = collect(json_decode(json_encode($response->viewData('page')), true)['props']['bills']['data'] ?? []);
        $orderedBillNumbers = $records->pluck('bill_number')->values();
        $expectedOrder = collect([$billA->bill_number, $billB->bill_number])->sort()->values();
        expect($orderedBillNumbers->take(2)->values())->toEqual($expectedOrder);

        // Sort by seller descending limited to our test records
        $response = $this->get(route('bills.index', [
            'sort_by' => 'seller',
            'sort_direction' => 'desc',
            'search' => 'Sort Seller'
        ]));

        $records = collect(json_decode(json_encode($response->viewData('page')), true)['props']['bills']['data'] ?? []);
        $orderedSellers = $records->pluck('seller')->values();
        expect($orderedSellers->first())->toBe('Sort Seller B');
        expect($orderedSellers->get(1))->toBe('Sort Seller A');
    }

    public function test_bills_index_shows_average_outurn_from_final_samples()
    {
        $billNumber = 'OUT-' . Str::upper(Str::random(6));
        $bill = Bill::factory()->create(['bill_number' => $billNumber]);

        // Create final sample cutting tests (types 1-3)
        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => null,
            'type' => 1,
            'outturn_rate' => 50.00,
        ]);

        CuttingTest::factory()->create([
            'bill_id' => $bill->id,
            'container_id' => null,
            'type' => 2,
            'outturn_rate' => 52.00,
        ]);

        $response = $this->get(route('bills.index', ['search' => $billNumber]));

        $response->assertInertia(fn ($page) => 
            $page->component('Bills/Index')
        );

        $records = collect(json_decode(json_encode($response->viewData('page')), true)['props']['bills']['data'] ?? []);
        $record = $records->firstWhere('bill_number', $billNumber);
        expect($record)->not->toBeNull();
        expect((float) $record['average_outurn'])->toBe(51.0);
    }

    public function test_create_bill_page_loads_successfully()
    {
        $response = $this->get(route('bills.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Bills/Create')
        );
    }

    public function test_can_create_new_bill()
    {
        $billData = [
            'bill_number' => 'BL-2024-TEST',
            'seller' => 'Test Seller',
            'buyer' => 'Test Buyer',
            'note' => 'Test note',
        ];

        $response = $this->post(route('bills.store'), $billData);

        $this->assertDatabaseHas('bills', $billData);

        $response->assertRedirect(route('bills.index'));
        $response->assertSessionHas('createdBill');
    }
}
