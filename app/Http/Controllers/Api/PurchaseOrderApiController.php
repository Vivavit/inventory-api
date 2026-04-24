<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseOrderResource;
use App\Http\Resources\PurchaseOrderCollection;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderApiController extends Controller
{
    protected PurchaseOrderService $purchaseOrderService;

    public function __construct(PurchaseOrderService $purchaseOrderService)
    {
        $this->purchaseOrderService = $purchaseOrderService;
    }

    /**
     * Display a listing of the resource.
     */
public function index(Request $request): PurchaseOrderCollection
{
    $user = Auth::user();
    $warehouses = $user->warehouses()->pluck('warehouses.id');

    $query = PurchaseOrder::select(
            'id',
            'po_number',
            'supplier_id',
            'warehouse_id',
            'status',
            'created_by',
            'created_at'
        )
        ->with([
            'supplier:id,name',
            'warehouse:id,name',
            'creator:id,name'
        ])
        ->withCount('items') // 👈 instead of loading all items
        ->when($user->user_type === 'staff', function ($q) use ($warehouses) {
            $q->whereIn('warehouse_id', $warehouses);
        })
        ->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        })
        ->when($request->filled('supplier_id'), function ($q) use ($request) {
            $q->where('supplier_id', $request->supplier_id);
        })
        ->when($request->filled('search'), function ($q) use ($request) {
            $q->where(function ($q2) use ($request) {
                $q2->where('po_number', 'like', '%' . $request->search . '%')
                    ->orWhere('reference_number', 'like', '%' . $request->search . '%')
                    ->orWhereHas('supplier', function ($q3) use ($request) {
                        $q3->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        })
        ->orderBy('created_at', 'desc');

    $perPage = $request->get('per_page', 15);

    return new PurchaseOrderCollection($query->paginate($perPage));
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Check permissions
        if (!$user->can('manage-inventory')) {
            return response()->json([
                'success' => false,
                'message' => 'Permission denied'
            ], 403);
        }

        // Check warehouse access
        $warehouse = Warehouse::findOrFail($request->warehouse_id);
        if ($user->user_type === 'staff' && !$user->warehouses->contains($warehouse->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied for this warehouse'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $payload = $request->all();
            if (empty($payload['order_date'])) {
                $payload['order_date'] = now()->toDateString();
            }

            $purchaseOrder = $this->purchaseOrderService->create($payload);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order created successfully',
                'data' => new PurchaseOrderResource($purchaseOrder)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        $user = Auth::user();

        // Check permissions
        if (!$user->can('manage-inventory')) {
            return response()->json([
                'success' => false,
                'message' => 'Permission denied'
            ], 403);
        }

        // Check warehouse access for staff
        if ($user->user_type === 'staff' && !$user->warehouses->contains($purchaseOrder->warehouse_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied for this warehouse'
            ], 403);
        }

        $purchaseOrder->load([
            'supplier',
            'warehouse',
            'creator',
            'items.product',
            'items.product.category',
            'items.product.brand',
            'items.product.images',
            'receivedItems'
        ]);

        return new PurchaseOrderResource($purchaseOrder);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'warehouse_id' => 'sometimes|exists:warehouses,id',
            'tax_rate' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:draft,pending,ordered,received',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Check permissions
        if (!$user->can('manage-inventory')) {
            return response()->json([
                'success' => false,
                'message' => 'Permission denied'
            ], 403);
        }

        // Check warehouse access for staff
        if ($user->user_type === 'staff' && !$user->warehouses->contains($purchaseOrder->warehouse_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied for this warehouse'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $payload = $request->all();
            if (! array_key_exists('items', $payload)) {
                $payload['items'] = $purchaseOrder->items()
                    ->get(['product_id', 'quantity', 'unit_price'])
                    ->map(fn ($item) => [
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                    ])
                    ->toArray();
            }
            if (empty($payload['order_date'])) {
                $payload['order_date'] = optional($purchaseOrder->order_date)->toDateString() ?? now()->toDateString();
            }

            $purchaseOrder = $this->purchaseOrderService->update($purchaseOrder, $payload);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order updated successfully',
                'data' => new PurchaseOrderResource($purchaseOrder)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $user = Auth::user();

        // Check permissions
        if (!$user->can('manage-inventory')) {
            return response()->json([
                'success' => false,
                'message' => 'Permission denied'
            ], 403);
        }

        // Check warehouse access for staff
        if ($user->user_type === 'staff' && !$user->warehouses->contains($purchaseOrder->warehouse_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied for this warehouse'
            ], 403);
        }

        try {
            $purchaseOrder->delete();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Receive stock for the purchase order
     */
    public function receive(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'received_items' => 'required|array|min:1',
            'received_items.*.item_id' => 'required|exists:purchase_order_items,id',
            'received_items.*.quantity' => 'required|integer|min:1',
            'received_items.*.received_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Check permissions
        if (!$user->can('manage-inventory')) {
            return response()->json([
                'success' => false,
                'message' => 'Permission denied'
            ], 403);
        }

        // Check warehouse access for staff
        if ($user->user_type === 'staff' && !$user->warehouses->contains($purchaseOrder->warehouse_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied for this warehouse'
            ], 403);
        }

        // Check if order can be received
        if ($purchaseOrder->status !== 'ordered') {
            return response()->json([
                'success' => false,
                'message' => 'Order must be in "ordered" status to receive stock'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $items = collect($request->received_items)->map(fn ($item) => [
                'id' => $item['item_id'],
                'received' => $item['quantity'],
            ])->toArray();

            $result = $this->purchaseOrderService->receive($purchaseOrder, $items);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock received successfully',
                'data' => new PurchaseOrderResource($result)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to receive stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the status of a purchase order
     */
    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,pending,ordered,received',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Check permissions
        if (!$user->can('manage-inventory')) {
            return response()->json([
                'success' => false,
                'message' => 'Permission denied'
            ], 403);
        }

        // Check warehouse access for staff
        if ($user->user_type === 'staff' && !$user->warehouses->contains($purchaseOrder->warehouse_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied for this warehouse'
            ], 403);
        }

        // Validate status transition
        $validTransitions = [
            'draft' => ['pending', 'ordered'],
            'pending' => ['ordered'],
            'ordered' => ['received'],
            'received' => []
        ];

        if (!in_array($request->status, $validTransitions[$purchaseOrder->status] ?? [])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status transition'
            ], 400);
        }

        try {
            $purchaseOrder->status = $request->status;
            $purchaseOrder->updated_by = $user->id;
            $purchaseOrder->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => new PurchaseOrderResource($purchaseOrder)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get purchase orders for the authenticated user
     */
    public function myOrders(Request $request): PurchaseOrderCollection
    {
        $user = Auth::user();

        if ($user->user_type === 'admin') {
            $query = PurchaseOrder::with(['supplier', 'warehouse', 'creator', 'items.product'])
                ->orderBy('created_at', 'desc');
        } else {
            $warehouses = $user->warehouses()->pluck('warehouses.id');

            $query = PurchaseOrder::with(['supplier', 'warehouse', 'creator', 'items.product'])
                ->whereIn('warehouse_id', $warehouses)
                ->orderBy('created_at', 'desc');
        }

        $perPage = $request->get('per_page', 15);

        return new PurchaseOrderCollection($query->paginate($perPage));
    }
}