<?php

namespace App\Http\Controllers;

use App\Models\InventoryLocation;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function lowStock()
    {
        $lowStockProducts = Product::with(['inventoryLocations', 'category'])
            ->where('reorder_level', '>', 0)
            ->get()
            ->filter(function ($product) {
                $totalStock = $product->inventoryLocations->sum('quantity');

                return $totalStock <= $product->reorder_level;
            })
            ->sortBy(function ($product) {
                $totalStock = $product->inventoryLocations->sum('quantity');

                return $product->reorder_level - $totalStock;
            });

        return view('reports.low-stock', compact('lowStockProducts'));
    }

    public function stockMovement(Request $request)
    {
        $query = InventoryTransaction::with(['product', 'warehouse', 'user'])
            ->latest();

        // Apply filters if provided
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->paginate(50);
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('reports.stock-movement', compact('transactions', 'warehouses'));
    }

    public function inventoryValue()
    {
        $inventoryValue = InventoryLocation::with(['product', 'warehouse'])
            ->get()
            ->groupBy('warehouse.name')
            ->map(function ($items) {
                $totalValue = $items->sum(function ($item) {
                    return $item->quantity * ($item->product->cost_price ?? 0);
                });
                $totalItems = $items->count();
                $totalQuantity = $items->sum('quantity');

                return [
                    'total_value' => $totalValue,
                    'total_items' => $totalItems,
                    'total_quantity' => $totalQuantity,
                    'items' => $items,
                ];
            });

        return view('reports.inventory-value', compact('inventoryValue'));
    }

    public function warehouseSummary()
    {
        $warehouses = Warehouse::with(['inventoryLocations.product'])
            ->where('is_active', true)
            ->get()
            ->map(function ($warehouse) {
                $totalValue = $warehouse->inventoryLocations->sum(function ($location) {
                    return $location->quantity * ($location->product->cost_price ?? 0);
                });

                $totalQuantity = $warehouse->inventoryLocations->sum('quantity');
                $productCount = $warehouse->inventoryLocations->count();

                $warehouse->total_value = $totalValue;
                $warehouse->total_quantity = $totalQuantity;
                $warehouse->product_count = $productCount;

                return $warehouse;
            });

        return view('reports.warehouse-summary', compact('warehouses'));
    }
}
