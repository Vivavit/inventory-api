<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupplierPurchaseTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Supplier $supplier;
    private Warehouse $warehouse;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::firstOrCreate(['name' => 'manage-inventory', 'guard_name' => 'api']);

        // Create admin roles for both guards
        $adminWebRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminApiRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);

        // Assign permissions to api admin role
        $adminApiRole->syncPermissions(['manage-inventory']);

        $this->admin = User::factory()->create(['user_type' => 'admin']);
        $this->admin->assignRole($adminWebRole); // Assign web admin role
        $this->admin->assignRole($adminApiRole); // Assign api admin role
        $this->supplier = Supplier::factory()->create(['is_active' => true]);
        $this->warehouse = Warehouse::factory()->create(['is_active' => true]);
        $this->product = Product::factory()->create(['is_active' => true]);

        // Create initial warehouse product stock
        WarehouseProduct::create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
        ]);
    }

    public function test_can_create_supplier_purchase()
    {
        Sanctum::actingAs($this->admin);

        $purchaseData = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => '2026-04-18',
            'expected_delivery_date' => '2026-04-25',
            'notes' => 'Test purchase',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'price' => 10.50,
                ]
            ]
        ];

        $response = $this->postJson('/api/supplier-purchases', $purchaseData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Supplier purchase created successfully',
                'data' => [
                    'supplier_id' => $this->supplier->id,
                    'warehouse_id' => $this->warehouse->id,
                    'status' => 'pending',
                    'total_amount' => 52.50, // 5 * 10.50
                ]
            ]);

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'pending',
            'total_amount' => 52.50,
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => 10.50,
            'total_price' => 52.50,
        ]);
    }

    public function test_can_confirm_purchase_and_increase_stock()
    {
        Sanctum::actingAs($this->admin);

        // Create a pending purchase
        $purchase = PurchaseOrder::create([
            'po_number' => 'PO-TEST-001',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'pending',
            'order_date' => '2026-04-18',
            'total_amount' => 100.00,
            'created_by' => $this->admin->id,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $purchase->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => 20.00,
            'total_price' => 100.00,
            'received_quantity' => 0,
        ]);

        // Confirm the purchase
        $response = $this->postJson("/api/supplier-purchases/{$purchase->id}/confirm");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Purchase confirmed and stock updated successfully',
            ]);

        // Check purchase status updated
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $purchase->id,
            'status' => 'completed',
        ]);

        // Check stock increased
        $this->assertDatabaseHas('warehouse_products', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 15, // 10 + 5
        ]);

        // Check item received quantity updated
        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $purchase->id,
            'received_quantity' => 5,
        ]);
    }

    public function test_cannot_confirm_non_pending_purchase()
    {
        Sanctum::actingAs($this->admin);

        // Create a completed purchase
        $purchase = PurchaseOrder::create([
            'po_number' => 'PO-TEST-002',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'completed',
            'order_date' => '2026-04-18',
            'total_amount' => 100.00,
            'created_by' => $this->admin->id,
        ]);

        // Try to confirm again
        $response = $this->postJson("/api/supplier-purchases/{$purchase->id}/confirm");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Only pending purchases can be confirmed',
            ]);
    }

    public function test_validation_errors_on_create()
    {
        Sanctum::actingAs($this->admin);

        $invalidData = [
            'supplier_id' => '', // Required
            'warehouse_id' => $this->warehouse->id,
            'order_date' => '2026-04-18',
            'items' => [], // Must have at least one item
        ];

        $response = $this->postJson('/api/supplier-purchases', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['supplier_id', 'items']);
    }
}
