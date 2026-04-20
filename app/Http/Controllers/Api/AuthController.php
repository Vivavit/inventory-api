<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->where('is_active', true)
            ->with(['warehouses' => function ($q) {
                $q->where('is_active', true);
            }])
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Determine warehouse information to return. staff users get a single
        // object (their default/only warehouse), while admins receive the full
        // list since the mobile app may need to display combined inventory.
        $warehouses = $user->warehouses;

        if ($warehouses->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No warehouse assigned to user. Contact administrator.',
            ], 403);
        }

        // Remove old tokens
        $user->tokens()->delete();

        $token = $user->createToken('flutter-token')->plainTextToken;

        $userData = $user->toArray();
        $userData['permissions'] = $user->getAllPermissions()->pluck('name')->toArray();

        $response = [
            'success' => true,
            'token' => $token,
            'user' => $userData,
            'message' => 'Login successful',
        ];

        if ($user->isAdmin()) {
            // return full array of warehouses
            $response['warehouses'] = $warehouses->map(function ($w) {
                return [
                    'id' => $w->id,
                    'name' => $w->name,
                    'code' => $w->code,
                ];
            });
        } else {
            $first = $warehouses->first();
            $response['warehouse'] = [
                'id' => $first->id,
                'name' => $first->name,
                'code' => $first->code,
            ];
        }

        return response()->json($response);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $warehouses = $user->warehouses;

        $userData = $user->toArray();
        $userData['permissions'] = $user->getAllPermissions()->pluck('name')->toArray();

        if ($user->isAdmin()) {
            $userData['warehouses'] = $warehouses;
        } else {
            $userData['warehouse'] = $warehouses->first();
        }

        return response()->json([
            'success' => true,
            'user' => $userData,
        ]);
    }
}
