<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Container;
use App\Models\CuttingTest;
use App\Models\User;
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
        $bill = Bill::factory()->create([
            'bill_number' => 'BL-2024-001',
            'seller' => 'Test Seller',
            'buyer' => 'Test Buyer',
        ]);

        Container::factory()->count(3)->create(['bill_id' => $bill->id]);

        $response = $this->get(route('bills.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Bills/Index')
                ->has('bills.data', 1)
                ->where('bills.data.0.bill_number', 'BL-2024-001')
                ->where('bills.data.0.seller', 'Test Seller')
                ->where('bills.data.0.buyer', 'Test Buyer')
                ->where('bills.data.0.containers_count', 3)
        );
    }

    public function test_bills_index_search_functionality()
    {
        Bill::factory()->create([
            'bill_number' => 'BL-2024-001',
            'seller' => 'Cashew Farms Ltd',
            'buyer' => 'Global Nuts Inc',
        ]);

        Bill::factory()->create([
            'bill_number' => 'BL-2024-002',
            'seller' => 'Premium Cashews Co',
            'buyer' => 'European Distributors',
        ]);

        // Search by bill number
        $response = $this->get(route('bills.index', ['search' => 'BL-2024-001']));
        $response->assertInertia(fn ($page) => 
            $page->has('bills.data', 1)
                ->where('bills.data.0.bill_number', 'BL-2024-001')
        );

        // Search by seller
        $response = $this->get(route('bills.index', ['search' => 'Premium']));
        $response->assertInertia(fn ($page) => 
            $page->has('bills.data', 1)
                ->where('bills.data.0.seller', 'Premium Cashews Co')
        );

        // Search by buyer
        $response = $this->get(route('bills.index', ['search' => 'European']));
        $response->assertInertia(fn ($page) => 
            $page->has('bills.data', 1)
                ->where('bills.data.0.buyer', 'European Distributors')
        );
    }

    public function test_bills_index_sorting_functionality()
    {
        Bill::factory()->create([
            'bill_number' => 'BL-2024-002',
            'seller' => 'B Seller',
        ]);

        Bill::factory()->create([
            'bill_number' => 'BL-2024-001',
            'seller' => 'A Seller',
        ]);

        // Sort by bill_number ascending
        $response = $this->get(route('bills.index', [
            'sort_by' => 'bill_number',
            'sort_direction' => 'asc'
        ]));

        $response->assertInertia(fn ($page) => 
            $page->where('bills.data.0.bill_number', 'BL-2024-001')
                ->where('bills.data.1.bill_number', 'BL-2024-002')
        );

        // Sort by seller descending
        $response = $this->get(route('bills.index', [
            'sort_by' => 'seller',
            'sort_direction' => 'desc'
        ]));

        $response->assertInertia(fn ($page) => 
            $page->where('bills.data.0.seller', 'B Seller')
                ->where('bills.data.1.seller', 'A Seller')
        );
    }

    public function test_bills_index_shows_average_outurn_from_final_samples()
    {
        $bill = Bill::factory()->create();

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

        $response = $this->get(route('bills.index'));

        $response->assertInertia(fn ($page) => 
            $page->where('bills.data.0.average_outurn', 51.00)
        );
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
        
        $bill = Bill::where('bill_number', 'BL-2024-TEST')->first();
        $response->assertRedirect(route('bills.show', $bill->id));
    }
}