@extends('layouts.app')
@section('title', 'Low Stock Report')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Low Stock Report</h3>
        <small class="text-muted">Products needing replenishment</small>
    </div>
    <div class="btn-group">
        <button onclick="window.print()" class="btn btn-light">
            <i class="bi bi-printer"></i> Print
        </button>
        <a href="{{ route('reports.stock-movement') }}" class="btn btn-light">
            <i class="bi bi-arrow-left-right"></i> Stock Movements
        </a>
    </div>
</div>

<div class="custom-card">
    <div class="section-title">Low Stock Items</div>
    
    @if($lowStockProducts->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="text-muted small">
                    <tr>
                        <th>Product</th>
                        <th>Current Stock</th>
                        <th>Reorder Level</th>
                        <th>Difference</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockProducts as $product)
                    @php
                        $totalStock = $product->inventoryLocations->sum('quantity');
                        $difference = $product->reorder_level - $totalStock;
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $product->name }}</div>
                            <small class="text-muted">SKU: {{ $product->sku }}</small>
                        </td>
                        <td>{{ $totalStock }}</td>
                        <td>{{ $product->reorder_level }}</td>
                        <td>
                            @if($difference > 0)
                                <span class="text-danger">-{{ $difference }}</span>
                            @else
                                <span class="text-success">{{ $difference }}</span>
                            @endif
                        </td>
                        <td>
                            @if($totalStock <= 0)
                                <span class="badge bg-danger">Out of Stock</span>
                            @elseif($totalStock <= $product->reorder_level)
                                <span class="badge bg-warning">Low Stock</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-light">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('manage-products')
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-light">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-5">
            <i class="bi bi-check-circle fs-1 text-success"></i>
            <p class="mt-3">No low stock items found!</p>
            <small class="text-muted">All products have sufficient inventory levels.</small>
        </div>
    @endif
</div>

@endsection