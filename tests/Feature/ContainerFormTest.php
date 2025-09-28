<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Container;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContainerFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_container_creation_form_validation()
    {
        $this->actingAs(\App\Models\User::factory()->create());
        $bill = Bill::factory()->create();

        // Test with valid data
        $validData = [
            'bill_id' => $bill->id,
            'truck' => 'TRK001',
            'container_number' => 'ABCD1234567',
            'quantity_of_bags' => 100,
            'w_jute_bag' => 1.50,
            'w_total' => 5000,
            'w_truck' => 15000,
            'w_container' => 2500,
            'w_gross' => 22500,
            'w_dunnage_dribag' => 100,
            'w_tare' => 17600.00,
            'w_net' => 4900.00,
            'note' => 'Test container',
        ];

        $response = $this->post('/containers', $validData);
        $response->assertRedirect();
        $this->assertDatabaseHas('containers', [
            'bill_id' => $bill->id,
            'container_number' => 'ABCD1234567',
            'truck' => 'TRK001',
        ]);
    }

    public function test_container_creation_validates_container_number_format()
    {
        $this->actingAs(\App\Models\User::factory()->create());
        $bill = Bill::factory()->create();

        // Test with invalid container number format
        $invalidData = [
            'bill_id' => $bill->id,
            'container_number' => 'INVALID123', // Invalid format
            'w_gross' => 1000,
        ];

        $response = $this->post('/containers', $invalidData);
        $response->assertSessionHasErrors(['container_number']);
    }

    public function test_container_update_form_validation()
    {
        $this->actingAs(\App\Models\User::factory()->create());
        $bill = Bill::factory()->create();
        $container = Container::factory()->create(['bill_id' => $bill->id]);

        $updateData = [
            'bill_id' => $bill->id,
            'truck' => 'UPDATED_TRK',
            'container_number' => 'UPDT1234567',
            'w_gross' => 2000,
            'w_tare' => 500,
            'w_net' => 1500,
        ];

        $response = $this->put("/containers/{$container->id}", $updateData);
        $response->assertRedirect();
        
        $container->refresh();
        $this->assertEquals('UPDATED_TRK', $container->truck);
        $this->assertEquals('UPDT1234567', $container->container_number);
    }

    public function test_container_weight_calculations_are_preserved()
    {
        $this->actingAs(\App\Models\User::factory()->create());
        $bill = Bill::factory()->create();

        // Test with new weight calculation formulas
        $data = [
            'bill_id' => $bill->id,
            'w_total' => 25000,
            'w_truck' => 15000,
            'w_container' => 2500,
            'quantity_of_bags' => 100,
            'w_jute_bag' => 1.50,
            'w_dunnage_dribag' => 100,
            // These should be calculated by frontend:
            // w_gross = w_total - w_truck - w_container = 25000 - 15000 - 2500 = 7500
            // w_tare = quantity_of_bags * w_jute_bag = 100 * 1.50 = 150
            // w_net = w_gross - w_dunnage_dribag - w_tare = 7500 - 100 - 150 = 7250
            'w_gross' => 7500,
            'w_tare' => 150,
            'w_net' => 7250,
        ];

        $response = $this->post('/containers', $data);
        $response->assertRedirect();
        
        // Get the container we just created by bill_id and specific attributes
        $container = Container::where('bill_id', $bill->id)
            ->where('w_total', 25000)
            ->where('quantity_of_bags', 100)
            ->first();
        
        $this->assertNotNull($container, 'Container should be created');
        
        // Verify the basic data is saved
        $this->assertEquals($bill->id, $container->bill_id);
        $this->assertEquals(25000, $container->w_total);
        $this->assertEquals(100, $container->quantity_of_bags);
        $this->assertEquals(1.50, $container->w_jute_bag);
        
        // The calculated weights should be saved as provided
        $this->assertEquals(7500, $container->w_gross);
        $this->assertEquals(150, $container->w_tare);
        $this->assertEquals(7250, $container->w_net);
    }

    public function test_container_creation_requires_valid_bill(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());

        $response = $this->post('/containers', [
            'container_number' => 'ABCD1234567',
        ]);

        $response->assertSessionHasErrors(['bill_id']);

        $response = $this->post('/containers', [
            'bill_id' => 9_999,
            'container_number' => 'ABCD1234567',
        ]);

        $response->assertSessionHasErrors(['bill_id']);
    }

    public function test_container_truck_identifier_cannot_exceed_twenty_characters(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());
        $bill = Bill::factory()->create();

        $response = $this->post('/containers', [
            'bill_id' => $bill->id,
            'truck' => str_repeat('X', 21),
        ]);

        $response->assertSessionHasErrors(['truck']);
    }

    public function test_container_weight_fields_respect_boundaries(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());
        $bill = Bill::factory()->create();

        $validPayload = [
            'bill_id' => $bill->id,
            'container_number' => 'ABCD1234567',
            'quantity_of_bags' => 0,
            'w_jute_bag' => 99.99,
            'w_total' => 0,
            'w_truck' => 0,
            'w_container' => 0,
            'w_dunnage_dribag' => 0,
        ];

        $this->post('/containers', $validPayload)->assertSessionDoesntHaveErrors();

        $invalidPayload = [
            'bill_id' => $bill->id,
            'container_number' => 'ABCD1234567',
            'w_total' => -1,
            'w_truck' => -1,
            'w_container' => -1,
            'w_dunnage_dribag' => -1,
            'w_jute_bag' => -0.01,
        ];

        $response = $this->post('/containers', $invalidPayload);
        $response->assertSessionHasErrors([
            'w_total',
            'w_truck',
            'w_container',
            'w_dunnage_dribag',
            'w_jute_bag',
        ]);

        $response = $this->post('/containers', [
            'bill_id' => $bill->id,
            'container_number' => 'ABCD1234567',
            'w_jute_bag' => 100,
        ]);

        $response->assertSessionHasErrors(['w_jute_bag']);
    }
}
