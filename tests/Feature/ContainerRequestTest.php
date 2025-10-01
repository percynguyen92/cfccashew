<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContainerRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_store_container_request_validates_new_fields(): void
    {
        $bill = Bill::factory()->create();

        $payload = [
            'bill_id' => $bill->id,
            'container_condition' => str_repeat('A', 256),
            'seal_condition' => str_repeat('A', 256),
            'w_gross' => -1,
            'w_tare' => -1,
            'w_net' => -1,
        ];

        $response = $this->post(route('containers.store'), $payload);

        $response->assertSessionHasErrors([
            'container_condition',
            'seal_condition',
            'w_gross',
            'w_tare',
            'w_net',
        ]);
    }

    public function test_update_container_request_validates_new_fields(): void
    {
        $bill = Bill::factory()->create();
        $container = \App\Models\Container::factory()->create(['bill_id' => $bill->id]);

        $payload = [
            'bill_id' => $bill->id,
            'container_condition' => 'Valid',
            'seal_condition' => str_repeat('A', 256),
        ];

        $response = $this->put(route('containers.update', $container), $payload);

        $response->assertSessionHasErrors(['seal_condition']);
    }

    public function test_container_creation_uses_bill_level_weights_if_not_provided(): void
    {
        $bill = Bill::factory()->create([
            'w_jute_bag' => 1.5,
            'w_dunnage_dribag' => 100,
        ]);

        $payload = [
            'bill_id' => $bill->id,
            'quantity_of_bags' => 100,
            'w_total' => 25000,
            'w_truck' => 10000,
            'w_container' => 3000,
        ];

        $response = $this->post(route('containers.store'), $payload);

        $response->assertSessionDoesntHaveErrors(['w_jute_bag', 'w_dunnage_dribag']);
    }
}
