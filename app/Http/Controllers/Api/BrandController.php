<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of brands.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Brand::withCount('products');
        
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        $brands = $query->paginate(15);
        
        return response()->json([
            'status' => 'success',
            'data' => $brands,
        ]);
    }

    /**
     * Store a newly created brand.
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        $brand = Brand::create($request->validated());
        $brand->loadCount('products');
        
        return response()->json([
            'status' => 'success',
            'message' => 'Brand created successfully',
            'data' => $brand,
        ], 201);
    }

    /**
     * Display the specified brand.
     */
    public function show(Brand $brand): JsonResponse
    {
        $brand->loadCount('products');
        
        return response()->json([
            'status' => 'success',
            'data' => $brand,
        ]);
    }

    /**
     * Update the specified brand.
     */
    public function update(UpdateBrandRequest $request, Brand $brand): JsonResponse
    {
        $brand->update($request->validated());
        $brand->loadCount('products');
        
        return response()->json([
            'status' => 'success',
            'message' => 'Brand updated successfully',
            'data' => $brand,
        ]);
    }

    /**
     * Delete the specified brand.
     */
    public function destroy(Brand $brand): JsonResponse
    {
        $brand->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Brand deleted successfully',
        ]);
    }
}
