<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillFormValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_bill_creation_requires_bill_number_seller_and_buyer(): void
    {
        $response = $this->post(route('bills.store'), []);

        $response->assertSessionHasErrors([
            'bill_number',
            'seller',
            'buyer',
        ]);
    }

    public function test_bill_number_cannot_exceed_twenty_characters(): void
    {
        $payload = [
            'bill_number' => str_repeat('A', 21),
            'seller' => 'Valid Seller',
            'buyer' => 'Valid Buyer',
        ];

        $response = $this->post(route('bills.store'), $payload);

        $response->assertSessionHasErrors(['bill_number']);
    }

    public function test_bill_note_respects_maximum_length(): void
    {
        $payload = [
            'bill_number' => 'BL-VALID-001',
            'seller' => 'Seller Ltd',
            'buyer' => 'Buyer GmbH',
            'note' => str_repeat('A', 65_536),
        ];

        $response = $this->post(route('bills.store'), $payload);

        $response->assertSessionHasErrors(['note']);
    }
}
