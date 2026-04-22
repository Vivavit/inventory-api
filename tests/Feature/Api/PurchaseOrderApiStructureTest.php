<?php

namespace Tests\Feature\Api;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Sanctum;
use Tests\TestCase;

class PurchaseOrderApiStructureTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function api_endpoints_exist_and_return_expected_structure()
    {
        // Test without authentication - should return 401
        $response = $this->getJson('/api/purchase-orders');
        $response->assertStatus(401);

        $response = $this->postJson('/api/purchase-orders', []);
        $response->assertStatus(401);

        $response = $this->getJson('/api/my-purchase-orders');
        $response->assertStatus(401);

        // Test with authentication - should return 403 for non-admin
        $user = \App\Models\User::factory()->create([
            'user_type' => 'staff',
        ]);

        Sanctum::actingAs($user, [], 'web');

        $response = $this->getJson('/api/purchase-orders');
        $response->assertStatus(403);

        $response = $this->postJson('/api/purchase-orders', []);
        $response->assertStatus(403);

        // Test my orders endpoint for staff
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

    /** @test */
    public function api_routes_are_defined_in_routes_file()
    {
        $routesContent = file_get_contents(base_path('routes/api.php'));

        // Check for purchase order routes
        $this->assertStringContainsString('Route::get(''/purchase-orders''', [PurchaseOrderApiController::class, 'index']);', $routesContent);
        $this->assertStringContainsString('Route::post(''/purchase-orders''', [PurchaseOrderApiController::class, 'store']);', $routesContent);
        $this->assertStringContainsString('Route::get(''/purchase-orders/{purchaseOrder}''', [PurchaseOrderApiController::class, 'show']);', $routesContent);
        $this->assertStringContainsString('Route::put(''/purchase-orders/{purchaseOrder}''', [PurchaseOrderApiController::class, 'update']);', $routesContent);
        $this->assertStringContainsString('Route::delete(''/purchase-orders/{purchaseOrder}''', [PurchaseOrderApiController::class, 'destroy']);', $routesContent);
        $this->assertStringContainsString('Route::post(''/purchase-orders/{purchaseOrder}/receive''', [PurchaseOrderApiController::class, 'receive']);', $routesContent);
        $this->assertStringContainsString('Route::patch(''/purchase-orders/{purchaseOrder}/status''', [PurchaseOrderApiController::class, 'updateStatus']);', $routesContent);
        $this->assertStringContainsString('Route::get(''/my-purchase-orders''', [PurchaseOrderApiController::class, 'myOrders']);', $routesContent);
    }
}