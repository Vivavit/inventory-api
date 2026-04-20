<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryLocation;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;

class DashboardController extends Controller
{
    public function index()
    {
        $products = Product::with(['warehouseProducts', 'category'])->get();

        $totalStock = 0;
        $outOfStock = 0;
        $lowOnStock = 0;
        $totalValue = 0;

        foreach ($products as $product) {
            $stock = $product->total_stock;
            $totalStock += $stock;
            $totalValue += $stock * ($product->cost_price ?? 0);

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
            'total_inventory_value' => number_format($totalValue, 2),
            'total_sales_count' => Product::sum('sold_count') ?? 0,
        ];

        $recentProducts = Product::with(['warehouseProducts', 'images'])
            ->latest()
            ->take(5)
            ->get();

        $warehouses = Warehouse::withCount('inventoryLocations')
            ->where('is_active', true)
            ->get();

        $users = User::latest()->take(5)->get();
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $newUsersLast30 = User::where('created_at', '>=', now()->subDays(30))->count();

        $topSellingProducts = Product::with(['warehouseProducts', 'images'])
            ->where('sold_count', '>', 0)
            ->orderByDesc('sold_count')
            ->take(8)
            ->get();

        $lowStockProducts = Product::with('warehouseProducts')
            ->get()
            ->filter(function ($product) {
                return $product->total_stock > 0 && $product->total_stock <= 10;
            })
            ->sortBy('total_stock')
            ->take(5);

        // Analytics data for charts
        $productsByCategory = $this->getProductsByCategory();
        $stockByWarehouse = $this->getStockByWarehouse();
        $stockStatusDistribution = [
            'In Stock' => $stats['total_products'] - $outOfStock,
            'Low Stock' => $lowOnStock,
            'Out of Stock' => $outOfStock,
        ];

        return view('dashboard.index', compact(
            'stats',
            'recentProducts',
            'warehouses',
            'totalStock',
            'outOfStock',
            'lowOnStock',
            'users',
            'totalUsers',
            'activeUsers',
            'newUsersLast30',
            'topSellingProducts',
            'lowStockProducts',
            'productsByCategory',
            'stockByWarehouse',
            'stockStatusDistribution'
        ));
    }

    private function getProductsByCategory()
    {
        $categories = Category::withCount('products')->get();
        $data = [];
        foreach ($categories as $cat) {
            $data[$cat->name] = $cat->products_count;
        }

        return $data;
    }

    private function getStockByWarehouse()
    {
        $warehouses = Warehouse::with('inventoryLocations')->where('is_active', true)->get();
        $data = [];
        foreach ($warehouses as $warehouse) {
            $stock = $warehouse->inventoryLocations->sum('quantity');
            $data[$warehouse->name] = $stock;
        }

        return $data;
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
