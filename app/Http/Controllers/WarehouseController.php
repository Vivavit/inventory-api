<?php

namespace App\Http\Controllers;

use App\Models\InventoryLocation;
use App\Models\Product; // Add this import
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::with(['users', 'inventoryLocations'])->latest()->get();

        // Calculate stats
        $activeCount = $warehouses->where('is_active', true)->count();
        $mainWarehouseCount = $warehouses->where('type', 'main')->count();
        $totalStockItems = 0;

        foreach ($warehouses as $warehouse) {
            $totalStockItems += $warehouse->inventoryLocations->count();
        }

        return view('warehouses.index', compact(
            'warehouses',
            'activeCount',
            'mainWarehouseCount',
            'totalStockItems'
        ));
    }

    public function create()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $users = User::where('is_active', true)->get();

        return view('warehouses.create', compact('warehouses', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:warehouses',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'type' => 'required|in:main,branch,store,virtual',
            'capacity' => 'nullable|numeric|min:0',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => 'exists:users,id',
        ]);

        $warehouse = Warehouse::create($validated);

        if ($request->has('assigned_users')) {
            $warehouse->users()->sync($request->assigned_users);
        }

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse created successfully!');
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load(['users', 'inventoryLocations.product']);
        // Get products for the "Add Stock" modal
        $products = Product::where('is_active', true)->get();

        return view('warehouses.show', compact('warehouse', 'products'));
    }

    public function edit(Warehouse $warehouse)
    {
        $users = User::where('is_active', true)->get();
        $assignedUserIds = $warehouse->users->pluck('id')->toArray();

        // Get all products for adding stock
        $products = Product::where('is_active', true)
            ->where('manage_stock', true)
            ->with(['inventoryLocations' => function ($q) use ($warehouse) {
                $q->where('warehouse_id', $warehouse->id);
            }])
            ->get();

        return view('warehouses.edit', compact('warehouse', 'users', 'assignedUserIds', 'products'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:warehouses,code,'.$warehouse->id,
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'type' => 'required|in:main,branch,store,virtual',
            'capacity' => 'nullable|numeric|min:0',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'assigned_users' => 'nullable|array', // ✅ Make sure this is included
            'assigned_users.*' => 'exists:users,id',
        ]);

        $warehouse->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'address' => $validated['address'],
            'contact_person' => $validated['contact_person'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'type' => $validated['type'],
            'capacity' => $validated['capacity'],
            'is_default' => $request->has('is_default'),
            'is_active' => $request->has('is_active'),
        ]);

        $assignedUsers = $request->has('assigned_users') ? $request->assigned_users : [];
        $warehouse->users()->sync($assignedUsers);

        return redirect()->route('warehouses.show', $warehouse)
            ->with('success', 'Warehouse updated successfully!');
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->inventoryLocations()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete warehouse with existing inventory!');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse deleted successfully!');
    }

    // Add stock to warehouse
    public function addStock(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'location_code' => 'nullable|string|max:50',
        ]);

        $inventoryLocation = InventoryLocation::firstOrCreate(
            [
                'product_id' => $validated['product_id'],
                'warehouse_id' => $warehouse->id,
            ],
            ['quantity' => 0, 'reserved_quantity' => 0]
        );

        $inventoryLocation->increment('quantity', $validated['quantity']);

        if ($validated['location_code']) {
            $inventoryLocation->update(['location_code' => $validated['location_code']]);
        }

        return redirect()->back()
            ->with('success', 'Stock added successfully!');
    }

    // Remove stock from warehouse
    public function removeStock(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $inventoryLocation = InventoryLocation::where([
            'product_id' => $validated['product_id'],
            'warehouse_id' => $warehouse->id,
        ])->firstOrFail();

        if ($inventoryLocation->quantity < $validated['quantity']) {
            return redirect()->back()
                ->with('error', 'Insufficient stock!');
        }

        $inventoryLocation->decrement('quantity', $validated['quantity']);

        return redirect()->back()
            ->with('success', 'Stock removed successfully!');
    }

    // Toggle warehouse status
    public function toggleStatus(Warehouse $warehouse)
    {
        $warehouse->update(['is_active' => ! $warehouse->is_active]);

        $status = $warehouse->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Warehouse {$status} successfully!");
    }

    // Set as default warehouse
    public function setDefault(Warehouse $warehouse)
    {
        // Remove default from all warehouses
        Warehouse::where('is_default', true)->update(['is_default' => false]);

        // Set this as default
        $warehouse->update(['is_default' => true]);

        return redirect()->back()
            ->with('success', 'Default warehouse set successfully!');
    }

    // Update stock quantity
    public function updateStock(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $inventoryLocation = InventoryLocation::firstOrCreate(
            [
                'product_id' => $validated['product_id'],
                'warehouse_id' => $warehouse->id,
            ],
            ['quantity' => 0, 'reserved_quantity' => 0]
        );

        $inventoryLocation->update(['quantity' => $validated['quantity']]);

        return redirect()->back()
            ->with('success', 'Stock quantity updated!');
    }

    // Assign users to warehouse
    public function assignUsers(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => 'exists:users,id',
        ]);

        $warehouse->users()->sync($request->assigned_users ?? []);

        return redirect()->back()
            ->with('success', 'Staff assignments updated!');
    }
}
