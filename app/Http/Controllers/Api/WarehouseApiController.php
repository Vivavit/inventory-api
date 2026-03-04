<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;

class WarehouseApiController extends Controller
{
    public function index()
    {
        return Warehouse::where('is_active', true)
            ->with('users')
            ->get();
    }
}
