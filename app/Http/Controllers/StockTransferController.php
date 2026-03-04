<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    public function index()
    {
        return view('stock-transfers.index');
    }

    public function create()
    {
        return view('stock-transfers.create');
    }

    // Add other required methods
    public function store(Request $request)
    {
        return redirect()->route('stock-transfers.index');
    }

    public function show($id)
    {
        return view('stock-transfers.show');
    }

    public function edit($id)
    {
        return view('stock-transfers.edit');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('stock-transfers.index');
    }

    public function destroy($id)
    {
        return redirect()->route('stock-transfers.index');
    }
}
