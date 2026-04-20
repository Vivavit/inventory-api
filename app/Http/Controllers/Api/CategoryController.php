<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     * Used for dropdowns in product form
     */
    public function index(Request $request)
    {
        try {
            $categories = Category::orderBy('name')->get(['id', 'name', 'slug', 'description']);

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch categories: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
            ], 500);
        }
    }
}
