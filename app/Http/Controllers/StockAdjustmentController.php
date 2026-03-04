<?php

namespace App\Http\Controllers;

use App\Models\InventoryLocation;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = StockAdjustment::with(['warehouse', 'createdBy', 'approvedBy'])
            ->latest()
            ->paginate(20);

        return view('stock-adjustments.index', compact('adjustments'));
    }

    public function create()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::where('manage_stock', true)
            ->where('is_active', true)
            ->with(['inventoryLocations'])
            ->get();

        return view('stock-adjustments.create', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:addition,deduction,correction,write_off',
            'reason' => 'required|in:damaged,expired,found,theft,counting_error,quality_control,other',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Generate adjustment number
            $adjustmentNumber = 'ADJ-'.date('Ymd').'-'.strtoupper(uniqid());

            // Create adjustment
            $adjustment = StockAdjustment::create([
                'adjustment_number' => $adjustmentNumber,
                'warehouse_id' => $validated['warehouse_id'],
                'type' => $validated['type'],
                'reason' => $validated['reason'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
                'status' => 'pending',
                'total_value' => 0,
            ]);

            $totalValue = 0;

            // Add items
            foreach ($validated['items'] as $itemData) {
                // Get current location
                $location = InventoryLocation::where([
                    'product_id' => $itemData['product_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'product_variant_id' => $itemData['variant_id'] ?? null,
                ])->first();

                $quantityBefore = $location ? $location->quantity : 0;
                $quantityAdjusted = $itemData['quantity'];
                $quantityAfter = $quantityBefore + $quantityAdjusted;

                // Ensure quantity doesn't go negative
                if ($quantityAfter < 0) {
                    throw new \Exception("Adjustment would result in negative stock for product ID: {$itemData['product_id']}");
                }

                // Calculate costs
                $unitCost = $itemData['unit_cost'] ?? ($location->average_cost ?? 0);
                $totalCost = abs($quantityAdjusted) * $unitCost;
                $totalValue += $totalCost;

                // Create adjustment item
                $adjustment->items()->create([
                    'product_id' => $itemData['product_id'],
                    'product_variant_id' => $itemData['variant_id'] ?? null,
                    'quantity_before' => $quantityBefore,
                    'quantity_adjusted' => $quantityAdjusted,
                    'quantity_after' => $quantityAfter,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Update total value
            $adjustment->update(['total_value' => $totalValue]);

            DB::commit();

            return redirect()->route('stock-adjustments.show', $adjustment)
                ->with('success', 'Stock adjustment created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to create adjustment: '.$e->getMessage())->withInput();
        }
    }

    public function show(StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->load([
            'warehouse',
            'createdBy',
            'approvedBy',
            'items.product',
            'items.variant',
        ]);

        return view('stock-adjustments.show', compact('stockAdjustment'));
    }

    public function approve(StockAdjustment $stockAdjustment)
    {
        if ($stockAdjustment->status !== 'pending') {
            return back()->with('error', 'Only pending adjustments can be approved.');
        }

        try {
            DB::beginTransaction();

            foreach ($stockAdjustment->items as $item) {
                // Get or create inventory location
                $location = InventoryLocation::firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'warehouse_id' => $stockAdjustment->warehouse_id,
                        'product_variant_id' => $item->product_variant_id,
                    ],
                    ['quantity' => 0, 'reserved_quantity' => 0]
                );

                // Update inventory
                $location->update(['quantity' => $item->quantity_after]);

                // Create transaction record
                InventoryTransaction::create([
                    'type' => 'adjustment',
                    'product_id' => $item->product_id,
                    'warehouse_id' => $stockAdjustment->warehouse_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity_change' => $item->quantity_adjusted,
                    'quantity_before' => $item->quantity_before,
                    'quantity_after' => $item->quantity_after,
                    'user_id' => auth()->id(),
                    'notes' => "Stock adjustment #{$stockAdjustment->adjustment_number} - {$stockAdjustment->reason}",
                    'reference_type' => 'stock_adjustment',
                    'reference_id' => $stockAdjustment->id,
                ]);
            }

            $stockAdjustment->update([
                'status' => 'completed',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Adjustment approved and completed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to approve adjustment: '.$e->getMessage());
        }
    }

    public function reject(StockAdjustment $stockAdjustment)
    {
        if ($stockAdjustment->status !== 'pending') {
            return back()->with('error', 'Only pending adjustments can be rejected.');
        }

        $stockAdjustment->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Adjustment rejected successfully!');
    }

    public function destroy(StockAdjustment $stockAdjustment)
    {
        if ($stockAdjustment->status !== 'pending') {
            return back()->with('error', 'Only pending adjustments can be deleted.');
        }

        $stockAdjustment->delete();

        return redirect()->route('stock-adjustments.index')
            ->with('success', 'Adjustment deleted successfully!');
    }
}
