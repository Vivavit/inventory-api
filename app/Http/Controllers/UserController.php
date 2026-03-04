<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['warehouses', 'roles'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('users.create', compact('warehouses', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string',
            'user_type' => 'required|in:admin,staff',
            'is_active' => 'boolean', // Indicates if the user is active
            'warehouses' => 'nullable|array',
            'warehouses.*' => 'exists:warehouses,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'user_type' => $validated['user_type'],
            'is_active' => $request->has('is_active'),
        ]);

        // Automatically assign role based on user_type
        if ($validated['user_type'] === 'admin') {
            $user->assignRole('admin');
        } else {
            $user->assignRole('staff');
        }

        // Assign warehouses
        if ($request->has('warehouses')) {
            $warehouseData = [];
            foreach ($request->warehouses as $index => $warehouseId) {
                $warehouseData[$warehouseId] = [
                    'is_default' => ($index === 0), // First warehouse is default
                ];
            }
            $user->warehouses()->sync($warehouseData);
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully!');
    }

    public function show(User $user)
    {
        $user->load(['roles', 'warehouses', 'inventoryTransactions']);
        $transactions = $user->inventoryTransactions()
            ->with(['product', 'warehouse'])
            ->latest()
            ->paginate(20);

        return view('users.show', compact('user', 'transactions'));
    }

    public function edit(User $user)
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $userWarehouses = $user->warehouses->pluck('id')->toArray();

        return view('users.edit', compact('user', 'warehouses', 'userWarehouses'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string',
            'user_type' => 'required|in:admin,staff',
            'is_active' => 'boolean',
            'warehouses' => 'nullable|array',
            'warehouses.*' => 'exists:warehouses,id',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'user_type' => $validated['user_type'],
            'is_active' => $request->has('is_active'),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Update role based on user_type
        $user->syncRoles([]); // Remove existing roles
        if ($validated['user_type'] === 'admin') {
            $user->assignRole('admin');
        } else {
            $user->assignRole('staff');
        }

        // Update warehouses
        if ($request->has('warehouses')) {
            $warehouseData = [];
            foreach ($request->warehouses as $index => $warehouseId) {
                $warehouseData[$warehouseId] = [
                    'is_default' => ($index === 0), // First warehouse is default
                ];
            }
            $user->warehouses()->sync($warehouseData);
        } else {
            $user->warehouses()->detach();
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully!');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account!');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User {$status} successfully!");
    }
}
