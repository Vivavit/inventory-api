<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignRoleRequest;
use App\Http\Requests\RevokeRoleRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserRolePermissionController extends Controller
{
    /**
     * Get user roles.
     */
    public function getRoles(User $user): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $user->roles,
        ]);
    }

    /**
     * Get user permissions.
     */
    public function getPermissions(User $user): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $user->getPermissionsViaRoles(),
        ]);
    }

    /**
     * Assign role to user.
     */
    public function assignRole(AssignRoleRequest $request, User $user): JsonResponse
    {
        $user->assignRole($request->role_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Role assigned to user successfully',
            'data' => [
                'user' => $user,
                'roles' => $user->load('roles')->roles,
            ],
        ]);
    }

    /**
     * Revoke role from user.
     */
    public function revokeRole(RevokeRoleRequest $request, User $user): JsonResponse
    {
        $user->removeRole($request->role_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Role revoked from user successfully',
            'data' => [
                'user' => $user,
                'roles' => $user->load('roles')->roles,
            ],
        ]);
    }

    /**
     * Sync user roles.
     */
    public function syncRoles(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->syncRoles($request->roles);

        return response()->json([
            'status' => 'success',
            'message' => 'User roles synchronized successfully',
            'data' => [
                'user' => $user,
                'roles' => $user->load('roles')->roles,
            ],
        ]);
    }

    /**
     * Check if user has permission.
     */
    public function hasPermission(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'permission' => 'required|string',
        ]);

        $hasPermission = $user->hasPermissionTo($request->permission);

        return response()->json([
            'status' => 'success',
            'data' => [
                'user_id' => $user->id,
                'permission' => $request->permission,
                'has_permission' => $hasPermission,
            ],
        ]);
    }

    /**
     * Check if user has role.
     */
    public function hasRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => 'required|string',
        ]);

        $hasRole = $user->hasRole($request->role);

        return response()->json([
            'status' => 'success',
            'data' => [
                'user_id' => $user->id,
                'role' => $request->role,
                'has_role' => $hasRole,
            ],
        ]);
    }
}
