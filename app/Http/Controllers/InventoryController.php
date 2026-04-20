<?php

namespace App\Http\Controllers;

use App\Models\InventoryLocation;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function adjust(Request $request, Product $product)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:addition,deduction',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $warehouse = Warehouse::find($validated['warehouse_id']);

        // Get or create inventory location
        $location = InventoryLocation::firstOrCreate(
            [
                'product_id' => $product->id,
                'warehouse_id' => $validated['warehouse_id'],
                'product_variant_id' => null,
            ],
            ['quantity' => 0, 'reserved_quantity' => 0]
        );

        // Calculate new quantity
        $quantityBefore = $location->quantity;
        $quantityChange = $validated['type'] === 'addition'
            ? $validated['quantity']
            : -$validated['quantity'];
        $quantityAfter = max(0, $quantityBefore + $quantityChange);

        // Update location
        $location->update(['quantity' => $quantityAfter]);

        // Create transaction record
        InventoryTransaction::create([
            'type' => $validated['type'],
            'product_id' => $product->id,
            'warehouse_id' => $validated['warehouse_id'],
            'quantity_change' => $quantityChange,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'user_id' => auth()->id(),
            'notes' => $validated['notes'],
        ]);

        return redirect()->back()->with('success', 'Stock adjusted successfully!');
    }

    public function transfer(Request $request, Product $product)
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        // Check source warehouse has enough stock
        $sourceLocation = InventoryLocation::where([
            'product_id' => $product->id,
            'warehouse_id' => $validated['from_warehouse_id'],
        ])->first();

        if (! $sourceLocation || $sourceLocation->available_quantity < $validated['quantity']) {
            return redirect()->back()->with('error', 'Insufficient stock in source warehouse!');
        }

        // Get or create destination location
        $destLocation = InventoryLocation::firstOrCreate(
            [
                'product_id' => $product->id,
                'warehouse_id' => $validated['to_warehouse_id'],
                'product_variant_id' => null,
            ],
            ['quantity' => 0, 'reserved_quantity' => 0]
        );

        // Perform transfer
        DB::transaction(function () use ($sourceLocation, $destLocation, $validated, $product) {
            // Update source
            $sourceBefore = $sourceLocation->quantity;
            $sourceAfter = $sourceBefore - $validated['quantity'];
            $sourceLocation->update(['quantity' => $sourceAfter]);

            // Update destination
            $destBefore = $destLocation->quantity;
            $destAfter = $destBefore + $validated['quantity'];
            $destLocation->update(['quantity' => $destAfter]);

            // Create transaction records
            InventoryTransaction::create([
                'type' => 'transfer_out',
                'product_id' => $product->id,
                'warehouse_id' => $validated['from_warehouse_id'],
                'quantity_change' => -$validated['quantity'],
                'quantity_before' => $sourceBefore,
                'quantity_after' => $sourceAfter,
                'user_id' => auth()->id(),
                'notes' => $validated['notes'].' (Transfer to '.$destLocation->warehouse->name.')',
            ]);

            InventoryTransaction::create([
                'type' => 'transfer_in',
                'product_id' => $product->id,
                'warehouse_id' => $validated['to_warehouse_id'],
                'quantity_change' => $validated['quantity'],
                'quantity_before' => $destBefore,
                'quantity_after' => $destAfter,
                'user_id' => auth()->id(),
                'notes' => $validated['notes'].' (Transfer from '.$sourceLocation->warehouse->name.')',
            ]);
        });

        return redirect()->back()->with('success', 'Stock transferred successfully!');
    }
}
