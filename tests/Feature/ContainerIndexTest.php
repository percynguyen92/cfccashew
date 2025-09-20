<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Container;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContainerIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_container_index_displays_containers()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $bill = Bill::factory()->create([
            'bill_number' => 'BL-2024-INDEX',
            'seller' => 'Index Seller',
            'buyer' => 'Index Buyer',
        ]);

        $container = Container::factory()->create([
            'bill_id' => $bill->id,
            'container_number' => 'INDX1234567',
            'truck' => 'TRK-INDEX',
        ]);

        $response = $this->get('/containers');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Containers/Index')
                ->has('containers')
                ->has('pagination')
        );
    }

    public function test_container_index_search_functionality()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $bill = Bill::factory()->create();
        Container::factory()->create([
            'bill_id' => $bill->id,
            'container_number' => 'SRCH1234567',
            'truck' => 'TRK-SEARCH',
        ]);

        $response = $this->get('/containers?container_number=SRCH');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Containers/Index')
                ->has('containers')
                ->has('filters')
                ->where('filters.container_number', 'SRCH')
        );
    }
}