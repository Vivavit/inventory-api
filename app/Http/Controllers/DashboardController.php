<?php

namespace App\Http\Controllers;

use App\Models\InventoryLocation;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;

class DashboardController extends Controller
{
    public function index()
    {
        $products = Product::with(['inventoryLocations'])->get();

        $totalStock = 0;
        $outOfStock = 0;
        $lowOnStock = 0;

        foreach ($products as $product) {
            $stock = $product->total_stock;
            $totalStock += $stock;

            if ($stock <= 0) {
                $outOfStock++;
            } elseif ($stock <= 10) {
                $lowOnStock++;
            }
        }

        $stats = [
            'total_products' => Product::count(),
            'total_warehouses' => Warehouse::where('is_active', true)->count(),
            'total_staff' => User::where('user_type', 'staff')->where('is_active', true)->count(),
            'low_stock_items' => $lowOnStock,
            'total_inventory_value' => $this->getInventoryValue(),
        ];

        $recentProducts = Product::with('inventoryLocations')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($product) {
                $product->total_stock = $product->inventoryLocations->sum('quantity');

                return $product;
            });

        $warehouses = Warehouse::withCount('inventoryLocations')
            ->where('is_active', true)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'recentProducts',
            'warehouses',
            'totalStock',
            'outOfStock',
            'lowOnStock'
        ));
    }

    private function getInventoryValue()
    {
        $totalValue = 0;
        $locations = InventoryLocation::with('product')->get();

        foreach ($locations as $location) {
            if ($location->product) {
                $totalValue += $location->quantity * ($location->product->cost_price ?? 0);
            }
        }

        return number_format($totalValue, 2);
    }
}
