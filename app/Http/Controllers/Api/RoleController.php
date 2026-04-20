<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request): JsonResponse
    {
        $roles = Role::with('permissions')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $roles,
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = Role::create($request->validated());

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        $role->load('permissions');

        return response()->json([
            'status' => 'success',
            'message' => 'Role created successfully',
            'data' => $role,
        ], 201);
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role): JsonResponse
    {
        $role->load('permissions');

        return response()->json([
            'status' => 'success',
            'data' => $role,
        ]);
    }

    /**
     * Update the specified role.
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        // Prevent user from updating their own role
        $userRole = Auth::user()->roles->first();
        if ($userRole && $userRole->id === $role->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot update your own role',
            ], 403);
        }

        $role->update($request->validated());

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        $role->load('permissions');

        return response()->json([
            'status' => 'success',
            'message' => 'Role updated successfully',
            'data' => $role,
        ]);
    }

    /**
     * Delete the specified role.
     */
    public function destroy(Role $role): JsonResponse
    {
        // Prevent user from deleting their own role
        $userRole = Auth::user()->roles->first();
        if ($userRole && $userRole->id === $role->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot delete your own role',
            ], 403);
        }

        $role->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Role deleted successfully',
        ]);
    }

    /**
     * Assign permission to role.
     */
    public function assignPermission(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $role->givePermissionTo($request->permission_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Permission assigned to role successfully',
            'data' => $role->load('permissions'),
        ]);
    }

    /**
     * Revoke permission from role.
     */
    public function revokePermission(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $role->revokePermissionTo($request->permission_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Permission revoked from role successfully',
            'data' => $role->load('permissions'),
        ]);
    }
}
