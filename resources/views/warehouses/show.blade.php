@extends('layouts.app')

@section('title', $warehouse->name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1 fw-bold">{{ $warehouse->name }}</h1>
            <div class="d-flex align-items-center gap-2">
                <code class="small">{{ $warehouse->code }}</code>
                <span class="badge bg-{{ $warehouse->is_active ? 'success' : 'danger' }}">
                    {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
                </span>
                @if($warehouse->is_default)
                <span class="badge bg-primary">Default</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('warehouses.index') }}" class="btn btn-light btn-sm">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil"></i> Edit
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Warehouse Stats -->
        <div class="col-md-3">
            <div class="custom-card text-center mb-4">
                <i class="bi bi-box-seam h2 text-primary mb-3"></i>
                <div class="h3 mb-0">{{ $warehouse->inventoryLocations->count() }}</div>
                <small class="text-muted">Products</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="custom-card text-center mb-4">
                <i class="bi bi-people h2 text-primary mb-3"></i>
                <div class="h3 mb-0">{{ $warehouse->users->count() }}</div>
                <small class="text-muted">Staff</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="custom-card text-center mb-4">
                <i class="bi bi-box-arrow-up h2 text-primary mb-3"></i>
                <div class="h3 mb-0">{{ $warehouse->inventoryLocations->sum('quantity') }}</div>
                <small class="text-muted">Total Stock</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="custom-card text-center mb-4">
                <i class="bi bi-cash h2 text-primary mb-3"></i>
                <div class="h3 mb-0">
                    @php
                        $totalValue = 0;
                        foreach($warehouse->inventoryLocations as $location) {
                            if($location->product) {
                                $totalValue += $location->quantity * ($location->product->cost_price ?? 0);
                            }
                        }
                    @endphp
                    ${{ number_format($totalValue, 2) }}
                </div>
                <small class="text-muted">Inventory Value</small>
            </div>
        </div>

        <!-- Warehouse Info -->
        <div class="col-md-4">
            <div class="custom-card mb-4">
                <h6 class="fw-bold mb-3">Warehouse Information</h6>
                <div class="row g-2">
                    <div class="col-6">
                        <small class="text-muted">Type</small>
                        <div class="fw-medium">{{ ucfirst($warehouse->type) }}</div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Capacity</small>
                        <div class="fw-medium">{{ $warehouse->capacity ? number_format($warehouse->capacity) . ' sq. ft.' : 'N/A' }}</div>
                    </div>
                    <div class="col-12 mt-2">
                        <small class="text-muted">Address</small>
                        <div class="fw-medium">{{ $warehouse->address ?? 'Not specified' }}</div>
                    </div>
                    <div class="col-6 mt-2">
                        <small class="text-muted">Contact</small>
                        <div class="fw-medium">{{ $warehouse->contact_person ?? 'N/A' }}</div>
                    </div>
                    <div class="col-6 mt-2">
                        <small class="text-muted">Phone</small>
                        <div class="fw-medium">{{ $warehouse->phone ?? 'N/A' }}</div>
                    </div>
                    <div class="col-12 mt-2">
                        <small class="text-muted">Email</small>
                        <div class="fw-medium">{{ $warehouse->email ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="custom-card">
                <h6 class="fw-bold mb-3">Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('warehouses.edit', $warehouse) }}#stock" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle"></i> Manage Stock
                    </a>
                    <a href="{{ route('warehouses.edit', $warehouse) }}#users" class="btn btn-outline-primary">
                        <i class="bi bi-people"></i> Assign Staff
                    </a>
                    <a href="{{ route('products.create') }}" class="btn btn-outline-success">
                        <i class="bi bi-plus-lg"></i> Add Product
                    </a>
                </div>
            </div>
        </div>

        <!-- Stock in Warehouse -->
        <div class="col-md-8">
            <div class="custom-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Products in Warehouse</h6>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStockModal">
                            <i class="bi bi-plus-lg"></i> Add Stock
                        </button>
                        <a href="{{ route('warehouses.edit', $warehouse) }}#stock" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil"></i> Edit All
                        </a>
                    </div>
                </div>
                
                @if($warehouse->inventoryLocations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th class="text-end">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($warehouse->inventoryLocations as $location)
                            @php
                                $product = $location->product;
                                $value = $product ? $location->quantity * ($product->cost_price ?? 0) : 0;
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $product->name ?? 'N/A' }}</div>
                                </td>
                                <td><code>{{ $product->sku ?? 'N/A' }}</code></td>
                                <td>{{ $product->category->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="fw-bold">{{ $location->quantity }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $location->location_code ?? 'Default' }}</span>
                                </td>
                                <td>
                                    @if($location->quantity <= 0)
                                        <span class="badge bg-danger">Out</span>
                                    @elseif($location->quantity <= 10)
                                        <span class="badge bg-warning">Low</span>
                                    @else
                                        <span class="badge bg-success">In Stock</span>
                                    @endif
                                </td>
                                <td class="text-end fw-medium">
                                    ${{ number_format($value, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="6" class="fw-bold">Total Inventory Value</td>
                                <td class="text-end fw-bold">
                                    @php
                                        $totalValue = 0;
                                        foreach($warehouse->inventoryLocations as $location) {
                                            if($location->product) {
                                                $totalValue += $location->quantity * ($location->product->cost_price ?? 0);
                                            }
                                        }
                                    @endphp
                                    ${{ number_format($totalValue, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <p class="text-muted mt-3">No products in this warehouse</p>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStockModal">
                        <i class="bi bi-plus-lg"></i> Add First Product
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Stock Modal (same as in edit view) -->
<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Stock to Warehouse</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('warehouses.add-stock', $warehouse) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->name }} ({{ $product->sku }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location Code (Optional)</label>
                        <input type="text" name="location_code" class="form-control" placeholder="e.g., A-01-01">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize modals
    document.addEventListener('DOMContentLoaded', function() {
        const addStockModal = document.getElementById('addStockModal');
        if (addStockModal) {
            new bootstrap.Modal(addStockModal);
        }
    });
</script>
@endpush
@endsection