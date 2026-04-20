<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiWarehouseProductTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Ensure that an admin user receives consolidated inventory across all assigned warehouses.
     */
    public function test_admin_receives_all_warehouse_stock()
    {
        // create two warehouses and a product
        $w1 = Warehouse::create(['name' => 'W1', 'code' => 'W1', 'is_active' => true]);
        $w2 = Warehouse::create(['name' => 'W2', 'code' => 'W2', 'is_active' => true]);

        $product = Product::factory()->create(['is_active' => true]);

        WarehouseProduct::create(['warehouse_id' => $w1->id, 'product_id' => $product->id, 'quantity' => 5]);
        WarehouseProduct::create(['warehouse_id' => $w2->id, 'product_id' => $product->id, 'quantity' => 10]);

        $admin = User::factory()->create(['user_type' => 'admin']);
        $admin->warehouses()->attach([$w1->id, $w2->id]);

        Sanctum::actingAs($admin, [], 'web');

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.0.stock', 15);

        // ensure warehouses metadata returned
        $this->assertArrayHasKey('warehouses', $response->json());
        $this->assertCount(2, $response->json('warehouses'));
    }

    /**
     * Staff user tied to a single warehouse only sees that warehouse's products.
     */
    public function test_staff_sees_only_assigned_warehouse()
    {
        $w1 = Warehouse::create(['name' => 'W1', 'code' => 'W1', 'is_active' => true]);
        $w2 = Warehouse::create(['name' => 'W2', 'code' => 'W2', 'is_active' => true]);

        $product = Product::factory()->create(['is_active' => true]);

        WarehouseProduct::create(['warehouse_id' => $w1->id, 'product_id' => $product->id, 'quantity' => 7]);
        WarehouseProduct::create(['warehouse_id' => $w2->id, 'product_id' => $product->id, 'quantity' => 3]);

        $staff = User::factory()->create(['user_type' => 'staff']);
        $staff->warehouses()->attach([$w1->id]);

        Sanctum::actingAs($staff, [], 'web');

        $response = $this->getJson('/api/products');

        // dump JSON for debugging if it fails
        if ($response->json('data.0.stock') !== 7) {
            fwrite(STDERR, 'API response: '.json_encode($response->json(), JSON_PRETTY_PRINT)."\n");
        }

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.0.stock', 7)
            ->assertJsonPath('warehouse_id', $w1->id);

        $this->assertArrayNotHasKey('warehouses', $response->json());
    }
}
