<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of brands
     * Used for dropdowns in product form
     */
    public function index(Request $request)
    {
        try {
            $brands = Brand::orderBy('name')->get(['id', 'name', 'slug', 'description']);

            return response()->json([
                'success' => true,
                'data' => $brands,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch brands: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch brands',
            ], 500);
        }
    }
}
