<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Permission::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $permissions = $query->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $permissions,
        ]);
    }

    /**
     * Store a newly created permission.
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = Permission::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Permission created successfully',
            'data' => $permission,
        ], 201);
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $permission,
        ]);
    }

    /**
     * Update the specified permission.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        $permission->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Permission updated successfully',
            'data' => $permission,
        ]);
    }

    /**
     * Delete the specified permission.
     */
    public function destroy(Permission $permission): JsonResponse
    {
        $permission->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Permission deleted successfully',
        ]);
    }
}
