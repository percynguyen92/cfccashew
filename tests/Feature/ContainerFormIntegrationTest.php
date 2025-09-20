<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Container;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContainerFormIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_container_creation_from_bill_detail_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $bill = Bill::factory()->create([
            'bill_number' => 'BL-2024-TEST',
            'seller' => 'Test Seller',
            'buyer' => 'Test Buyer',
        ]);

        // Test accessing the container creation form from bill detail
        $response = $this->get("/containers/create?bill_id={$bill->id}");
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Containers/Create')
                ->has('bill')
                ->where('bill_id', (string) $bill->id)
        );
    }

    public function test_container_edit_form_displays_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $bill = Bill::factory()->create();
        $container = Container::factory()->create([
            'bill_id' => $bill->id,
            'container_number' => 'TEST1234567',
            'truck' => 'TRK-TEST',
        ]);

        $response = $this->get("/containers/{$container->id}/edit");
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Containers/Edit')
                ->has('container')
        );
    }

    public function test_container_form_redirects_to_bill_detail_after_creation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $bill = Bill::factory()->create();

        $containerData = [
            'bill_id' => $bill->id,
            'truck' => 'TRK-REDIRECT-TEST',
            'container_number' => 'RDIR1234567',
            'w_gross' => 1000,
            'w_tare' => 200,
            'w_net' => 800,
        ];

        $response = $this->post('/containers', $containerData);
        
        // Should redirect to bill detail page
        $response->assertRedirect("/bills/{$bill->id}");
        $response->assertSessionHas('success', 'Container created successfully.');
    }

    public function test_container_form_redirects_to_bill_detail_after_update()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $bill = Bill::factory()->create();
        $container = Container::factory()->create(['bill_id' => $bill->id]);

        $updateData = [
            'bill_id' => $bill->id,
            'truck' => 'UPDATED-TRK',
            'w_gross' => 2000,
        ];

        $response = $this->put("/containers/{$container->id}", $updateData);
        
        // Should redirect to bill detail page
        $response->assertRedirect("/bills/{$bill->id}");
        $response->assertSessionHas('success', 'Container updated successfully.');
    }
}