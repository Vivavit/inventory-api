<?php

namespace Tests\Feature\Api;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Sanctum;
use Tests\TestCase;

class PurchaseOrderApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function it_can_list_purchase_orders_for_admin()
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        Sanctum::actingAs($admin, [], 'web');

        $response = $this->getJson('/api/purchase-orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'links',
                'meta'
            ]);
    }

    /** @test */
    public function it_can_create_purchase_order()
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $supplier = Supplier::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $product = \App\Models\Product::factory()->create();

        Sanctum::actingAs($admin, [], 'web');

        $payload = [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_price' => 100.00,
                    'tax_rate' => 0.1,
                    'discount' => 0,
                ]
            ],
            'tax_rate' => 0.1,
            'shipping_cost' => 50.00,
            'notes' => 'Test purchase order',
        ];

        $response = $this->postJson('/api/purchase-orders', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'po_number',
                    'supplier',
                    'warehouse',
                    'status',
                    'items',
                    'total_amount'
                ]
            ]);
    }

    /** @test */
    public function it_can_get_purchase_order_details()
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $purchaseOrder = PurchaseOrder::factory()->create();

        Sanctum::actingAs($admin, [], 'web');

        $response = $this->getJson('/api/purchase-orders/' . $purchaseOrder->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'po_number',
                    'supplier',
                    'warehouse',
                    'status',
                    'items',
                    'total_amount'
                ]
            ]);
    }

    /** @test */
    public function it_can_update_purchase_order()
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $purchaseOrder = PurchaseOrder::factory()->create();

        Sanctum::actingAs($admin, [], 'web');

        $payload = [
            'notes' => 'Updated notes',
            'status' => 'pending',
        ];

        $response = $this->putJson('/api/purchase-orders/' . $purchaseOrder->id, $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'po_number',
                    'notes',
                    'status',
                    'total_amount'
                ]
            ]);
    }

    /** @test */
    public function it_can_delete_purchase_order()
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $purchaseOrder = PurchaseOrder::factory()->create();

        Sanctum::actingAs($admin, [], 'web');

        $response = $this->deleteJson('/api/purchase-orders/' . $purchaseOrder->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }

    /** @test */
    public function it_can_receive_stock()
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'status' => 'ordered',
        ]);

        Sanctum::actingAs($admin, [], 'web');

        $payload = [
            'received_items' => [
                [
                    'item_id' => $purchaseOrder->items->first()->id,
                    'quantity' => 5,
                    'received_date' => now()->toDateString(),
                ]
            ]
        ];

        $response = $this->postJson('/api/purchase-orders/' . $purchaseOrder->id . '/receive', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'status',
                    'items',
                ]
            ]);
    }

    /** @test */
    public function it_can_update_status()
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'status' => 'draft',
        ]);

        Sanctum::actingAs($admin, [], 'web');

        $payload = [
            'status' => 'pending',
        ];

        $response = $this->patchJson('/api/purchase-orders/' . $purchaseOrder->id . '/status', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'status',
                ]
            ]);
    }

    /** @test */
    public function staff_can_view_their_purchase_orders()
    {
        $staff = User::factory()->create([
            'user_type' => 'staff',
        ]);

        $warehouse = \App\Models\Warehouse::factory()->create();
        $staff->warehouses()->attach($warehouse->id);

        Sanctum::actingAs($staff, [], 'web');

        $response = $this->getJson('/api/my-purchase-orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'links',
                'meta'
            ]);
    }
}