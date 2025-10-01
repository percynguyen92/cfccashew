<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_store_bill_request_validates_new_fields(): void
    {
        $payload = [
            'bill_number' => 'BL-TEST',
            'seller' => 'Test Seller',
            'buyer' => 'Test Buyer',
            'w_jute_bag' => 100.0, // max 99.99
            'w_dunnage_dribag' => -1,
            'net_on_bl' => -1,
            'quantity_of_bags_on_bl' => -1,
            'sampling_ratio' => 101, // max 100
            'inspection_start_date' => 'invalid-date',
        ];

        $response = $this->post(route('bills.store'), $payload);

        $response->assertSessionHasErrors([
            'w_jute_bag',
            'w_dunnage_dribag',
            'net_on_bl',
            'quantity_of_bags_on_bl',
            'sampling_ratio',
            'inspection_start_date',
        ]);
    }

    public function test_update_bill_request_validates_new_fields(): void
    {
        $bill = \App\Models\Bill::factory()->create();

        $payload = [
            'w_jute_bag' => -0.01,
            'sampling_ratio' => -5,
        ];

        $response = $this->put(route('bills.update', $bill), $payload);

        $response->assertSessionHasErrors([
            'w_jute_bag',
            'sampling_ratio',
        ]);
    }
}
