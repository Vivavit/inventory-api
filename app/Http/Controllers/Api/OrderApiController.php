<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderCollection;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderApiController extends Controller
{
    /**
     * Display a listing of orders.
     * Accessible by: admin, staff
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Get accessible warehouses
        $warehouseIds = $user->warehouses()->pluck('warehouses.id');
        
        $query = Order::with(['user', 'warehouse', 'items.product'])
            ->whereIn('warehouse_id', $warehouseIds);

        // Filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('search') && $request->search) {
            $query->where('order_number', 'like', '%'.$request->search.'%');
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'message' => 'Orders retrieved successfully',
            'data' => new OrderCollection($orders),
        ]);
    }

    /**
     * Display orders for the authenticated customer (mobile app).
     */
    public function myOrders(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $query = Order::with(['warehouse', 'items.product'])
            ->where('user_id', $user->id);

        // Filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'message' => 'My orders retrieved successfully',
            'data' => new OrderCollection($orders),
        ]);
    }

    /**
     * Store a newly created order (mobile app - customer checkout).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $items = $validated['items'];
        $warehouseId = $validated['warehouse_id'];
        $notes = $validated['notes'] ?? null;

        // Verify warehouse is active
        $warehouse = Warehouse::where('id', $warehouseId)
            ->where('is_active', true)
            ->first();

        if (!$warehouse) {
            return response()->json([
                'status' => 'error',
                'message' => 'Warehouse not found or inactive',
            ], 404);
        }

        // Calculate totals and verify stock
        $orderItems = [];
        $subtotal = 0;

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            
            // Check stock in warehouse
            $warehouseProduct = WarehouseProduct::where('warehouse_id', $warehouseId)
                ->where('product_id', $item['product_id'])
                ->first();

            if (!$warehouseProduct || $warehouseProduct->quantity < $item['quantity']) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Insufficient stock for product: {$product->name}",
                    'available_stock' => $warehouseProduct?->quantity ?? 0,
                ], 400);
            }

            $itemSubtotal = $item['quantity'] * $item['price'];
            $subtotal += $itemSubtotal;

            $orderItems[] = [
                'product_id' => $item['product_id'],
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $itemSubtotal,
            ];
        }

        // Generate order number
        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());

        $order = DB::transaction(function () use ($orderNumber, $subtotal, $warehouseId, $notes, $orderItems, $items) {
            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => Auth::id(),
                'warehouse_id' => $warehouseId,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => 'pending',
                'notes' => $notes,
            ]);

            // Create order items and deduct stock
            foreach ($orderItems as $itemData) {
                $order->items()->create($itemData);

                // Deduct from warehouse stock
                $warehouseProduct = WarehouseProduct::where('warehouse_id', $warehouseId)
                    ->where('product_id', $itemData['product_id'])
                    ->first();

                $warehouseProduct->quantity -= $itemData['quantity'];
                $warehouseProduct->save();

                // Record inventory transaction
                InventoryTransaction::create([
                    'product_id' => $itemData['product_id'],
                    'warehouse_id' => $warehouseId,
                    'type' => 'sale',
                    'quantity' => -$itemData['quantity'],
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'created_by' => Auth::id(),
                ]);
            }

            return $order;
        });

        $order->load(['user', 'warehouse', 'items.product']);

        return response()->json([
            'status' => 'success',
            'message' => 'Order created successfully',
            'data' => new OrderResource($order->load(['items.product'])),
        ], 201);
    }

    /**
     * Display the specified order.
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        
        // Build query based on user type
        $query = Order::with(['user', 'warehouse', 'items.product']);

        // If staff, filter by accessible warehouses
        if ($user->hasRole('staff')) {
            $warehouseIds = $user->warehouses()->pluck('warehouses.id');
            $query->whereIn('warehouse_id', $warehouseIds);
        }

        $order = $query->find($id);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order retrieved successfully',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Update order status (admin/staff only).
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,processing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        
        $query = Order::whereHas('warehouse', function ($q) use ($user) {
            $q->whereIn('id', $user->warehouses()->pluck('warehouses.id'));
        });

        $order = $query->find($id);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found',
            ], 404);
        }

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // If cancelling, restore stock
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            DB::transaction(function () use ($order) {
                foreach ($order->items as $item) {
                    $warehouseProduct = WarehouseProduct::where('warehouse_id', $order->warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->first();

                    if ($warehouseProduct) {
                        $warehouseProduct->quantity += $item['quantity'];
                        $warehouseProduct->save();

                        // Record inventory transaction
                        InventoryTransaction::create([
                            'product_id' => $item->product_id,
                            'warehouse_id' => $order->warehouse_id,
                            'type' => 'adjustment',
                            'quantity' => $item['quantity'],
                            'reference_type' => Order::class,
                            'reference_id' => $order->id,
                            'created_by' => Auth::id(),
                            'notes' => 'Order cancelled - stock restored',
                        ]);
                    }
                }
            });
        }

        $order->status = $newStatus;
        $order->save();
        $order->load(['user', 'warehouse', 'items.product']);

        return response()->json([
            'status' => 'success',
            'message' => 'Order status updated successfully',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Cancel order (customer can cancel pending orders).
     */
    public function cancel(int $id): JsonResponse
    {
        $user = Auth::user();
        
        $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found',
            ], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only pending orders can be cancelled',
            ], 400);
        }

        // Restore stock
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $warehouseProduct = WarehouseProduct::where('warehouse_id', $order->warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if ($warehouseProduct) {
                    $warehouseProduct->quantity += $item['quantity'];
                    $warehouseProduct->save();

                    // Record inventory transaction
                    InventoryTransaction::create([
                        'product_id' => $item->product_id,
                        'warehouse_id' => $order->warehouse_id,
                        'type' => 'adjustment',
                        'quantity' => $item['quantity'],
                        'reference_type' => Order::class,
                        'reference_id' => $order->id,
                        'created_by' => Auth::id(),
                        'notes' => 'Order cancelled by customer - stock restored',
                    ]);
                }
            }
        });

        $order->status = 'cancelled';
        $order->save();
        $order->load(['user', 'warehouse', 'items.product']);

        return response()->json([
            'status' => 'success',
            'message' => 'Order cancelled successfully',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Get order statistics (admin/staff).
     */
    public function stats(Request $request): JsonResponse
    {
        $user = Auth::user();
        $warehouseIds = $user->warehouses()->pluck('warehouses.id');

        $query = Order::whereIn('warehouse_id', $warehouseIds);

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $stats = [
            'total_orders' => $query->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'processing' => (clone $query)->where('status', 'processing')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'total_revenue' => (clone $query)->where('status', '!=', 'cancelled')->sum('total'),
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Order statistics retrieved successfully',
            'data' => $stats,
        ]);
    }
}