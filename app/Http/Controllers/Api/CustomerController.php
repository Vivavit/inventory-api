<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Lookup customer by phone number.
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:7',
        ]);

        $customer = Customer::where('phone', $request->phone)->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
                'customer' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'customer' => $customer,
        ]);
    }

    /**
     * Create a new customer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:7|unique:customers',
            'email' => 'nullable|email|unique:customers',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);

        $customer = Customer::create([
            'phone' => $request->phone,
            'email' => $request->email,
            'name' => $request->name,
            'address' => $request->address,
            'loyalty_points' => 0,
            'total_spent' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer created',
            'customer' => $customer,
        ], 201);
    }

    /**
     * Get customer by ID.
     */
    public function show(Customer $customer)
    {
        return response()->json([
            'success' => true,
            'customer' => $customer,
        ]);
    }

    /**
     * Update customer information.
     */
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);

        $customer->update([
            'email' => $request->email,
            'name' => $request->name,
            'address' => $request->address,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer updated',
            'customer' => $customer,
        ]);
    }

    /**
     * Add loyalty points to customer.
     */
    public function addLoyaltyPoints(Request $request, Customer $customer)
    {
        $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        $customer->increment('loyalty_points', $request->points);

        return response()->json([
            'success' => true,
            'message' => 'Loyalty points added',
            'customer' => $customer->fresh(),
        ]);
    }

    /**
     * Deduct loyalty points from customer.
     */
    public function deductLoyaltyPoints(Request $request, Customer $customer)
    {
        $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        if ($customer->loyalty_points < $request->points) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient loyalty points',
            ], 400);
        }

        $customer->decrement('loyalty_points', $request->points);

        return response()->json([
            'success' => true,
            'message' => 'Loyalty points deducted',
            'customer' => $customer->fresh(),
        ]);
    }
}

