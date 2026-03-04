{{-- In products/show.blade.php --}}
@php
    // Add this at the top of your view to get warehouses
    $warehouses = App\Models\Warehouse::all();
    $primaryImage = $product->images->where('is_primary', true)->first() ?? $product->images->first();
@endphp

@extends('layouts.app')

@section('title', $product->name)
@section('page-title', $product->name)
@section('page-subtitle', 'Product Details')

@section('content')
<style>
    .product-image-main {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: 12px;
        margin-bottom: 15px;
        border: 3px solid #03624C;
    }
    .product-image-thumbnail {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        border: 2px solid #E9FFFA;
        transition: all 0.2s;
    }
    .product-image-thumbnail:hover,
    .product-image-thumbnail.active {
        border-color: #03624C;
        transform: scale(1.05);
    }
    .image-thumbnails {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }
    .no-main-image {
        width: 100%;
        height: 300px;
        background: linear-gradient(135deg, #E9FFFA, #D6F5ED);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #03624C;
        border: 2px dashed #03624C;
    }
    .no-main-image i {
        font-size: 3rem;
    }
    .custom-card {
        background-color: #FFFFFF;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid #E9FFFA;
    }
    .custom-text-primary {
        color: #03624C !important;
    }
    .custom-table {
        background-color: #FFFFFF;
        border-radius: 8px;
        overflow: hidden;
    }
    .custom-table thead {
        background-color: #03624C;
        color: white;
    }
    .custom-table th {
        border: none;
        padding: 16px;
    }
    .custom-table td {
        padding: 16px;
        border-bottom: 1px solid #E9FFFA;
    }
    .info-label {
        color: #8E8E93;
        font-size: 14px;
        margin-bottom: 4px;
    }
    .info-value {
        font-weight: 600;
        color: #262626;
        font-size: 16px;
    }
    .stock-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
    }
</style>

<div class="row">
    <!-- Product Info -->
    <div class="col-md-8">
        <div class="custom-card">
            <!-- Product Images -->
            <div class="row mb-4">
                <div class="col-md-6">
                    @if($primaryImage)
                        <img id="mainProductImage" 
                            src="{{ $primaryImage->url }}"
                            alt="{{ $product->name }}" 
                            class="product-image-main"
                            onerror="this.src='https://placehold.co/400x300/E9FFFA/03624C?text=No+Image'">
                    @endif
                </div>
                
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h3 class="mb-1">{{ $product->name }}</h3>
                            <div class="d-flex align-items-center gap-3 mt-2">
                                <span class="badge bg-secondary">{{ $product->sku }}</span>
                                @if($product->brand)
                                <span class="text-muted">{{ $product->brand->name }}</span>
                                @endif
                                @if($product->category)
                                <span class="text-muted">{{ $product->category->name }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="info-label">Price</div>
                            <div class="info-value">${{ number_format($product->price, 2) }}</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="info-label">Cost Price</div>
                            <div class="info-value">${{ number_format($product->cost_price ?? 0, 2) }}</div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="info-label">Stock Status</div>
                            <div class="d-flex align-items-center gap-3">
                                @php
                                    $totalStock = $product->inventoryLocations->sum('quantity') ?? 0;
                                    $lowStockThreshold = $product->default_low_stock_threshold ?? 10;
                                @endphp
                                
                                @if($totalStock <= 0)
                                    <span class="stock-badge" style="background-color: rgba(255, 59, 48, 0.1); color: #FF3B30;">
                                        Out of Stock
                                    </span>
                                @elseif($totalStock <= $lowStockThreshold)
                                    <span class="stock-badge" style="background-color: rgba(255, 204, 0, 0.1); color: #FFCC00;">
                                        Low Stock
                                    </span>
                                @else
                                    <span class="stock-badge" style="background-color: rgba(52, 199, 89, 0.1); color: #34C759;">
                                        In Stock
                                    </span>
                                @endif
                                <span class="h4 mb-0">{{ $totalStock }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Short Description -->
                    @if($product->short_description)
                    <div class="mb-3">
                        <div class="info-label">Short Description</div>
                        <p class="mb-0">{{ $product->short_description }}</p>
                    </div>
                    @endif

                    <!-- Full Description -->
                    <div class="mb-3">
                        <div class="info-label">Description</div>
                        <p>{{ $product->description ?? 'No description provided' }}</p>
                    </div>
                </div>
            </div>

            <!-- Stock by Warehouse -->
            <div class="mt-4">
                <h6 class="mb-3 custom-text-primary">Stock by Warehouse</h6>
                <div class="table-responsive">
                    <table class="table custom-table">
                        <thead>
                            <tr>
                                <th>Warehouse</th>
                                <th>Location</th>
                                <th>Quantity</th>
                                <th>Available</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($product->inventoryLocations as $location)
                            <tr>
                                <td>{{ $location->warehouse->name ?? 'N/A' }}</td>
                                <td>{{ $location->location_code ?? 'Default' }}</td>
                                <td>{{ $location->quantity }}</td>
                                <td>{{ $location->quantity - ($location->reserved_quantity ?? 0) }}</td>
                                <td>
                                    @php
                                        $available = $location->quantity - ($location->reserved_quantity ?? 0);
                                    @endphp
                                    @if($available <= 0)
                                        <span class="badge bg-danger">Empty</span>
                                    @elseif($available <= 10)
                                        <span class="badge bg-warning">Low</span>
                                    @else
                                        <span class="badge bg-success">Good</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem; color: #E9FFFA;"></i>
                                    <p class="text-muted mt-2">No stock locations found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Stats -->
    <div class="col-md-4">
        <!-- Quick Stats -->
        <div class="custom-card mb-4">
            <h6 class="custom-text-primary mb-3">Product Stats</h6>
            <div class="row g-2">
                @php
                    $totalStock = $product->inventoryLocations->sum('quantity') ?? 0;
                    $inventoryValue = $totalStock * ($product->cost_price ?? 0);
                @endphp
                <div class="col-6">
                    <div class="p-3 rounded" style="background-color: #E9FFFA;">
                        <div class="h4 mb-1">{{ $totalStock }}</div>
                        <small class="text-muted">Total Stock</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded" style="background-color: #E9FFFA;">
                        <div class="h4 mb-1">{{ $product->images->count() }}</div>
                        <small class="text-muted">Images</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded" style="background-color: #E9FFFA;">
                        <div class="h4 mb-1">${{ number_format($inventoryValue, 2) }}</div>
                        <small class="text-muted">Inventory Value</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded" style="background-color: #E9FFFA;">
                        <div class="h4 mb-1">{{ $product->views_count }}</div>
                        <small class="text-muted">Views</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Info -->
        <div class="custom-card mb-4">
            <h6 class="custom-text-primary mb-3">Product Information</h6>
            <div class="row">
                <div class="col-6 mb-3">
                    <div class="info-label">Weight</div>
                    <div class="info-value">{{ $product->weight ? $product->weight . ' kg' : 'N/A' }}</div>
                </div>
                <div class="col-6 mb-3">
                    <div class="info-label">Low Stock Alert</div>
                    <div class="info-value">{{ $product->default_low_stock_threshold ?? 10 }}</div>
                </div>
                <div class="col-6">
                    <div class="info-label">Manage Stock</div>
                    <div class="info-value">
                        @if($product->manage_stock)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </div>
                </div>
                <div class="col-6">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        @if($product->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="custom-card">
            <h6 class="custom-text-primary mb-3">Quick Actions</h6>
            <div class="d-grid gap-2">
                <a href="{{ route('products.create', $product) }}" class="btn btn-primary" style="background-color: #03624C; border-color: #03624C;">
                    <i class="bi bi-plus"></i> New Product
                </a>
                <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil"></i> Edit Product
                </a>
                <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100" 
                            onclick="return confirm('Are you sure you want to delete this product?')">
                        <i class="bi bi-trash"></i> Delete Product
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function changeMainImage(src, element) {
        document.getElementById('mainProductImage').src = src;
        
        // Remove active class from all thumbnails
        document.querySelectorAll('.product-image-thumbnail').forEach(thumb => {
            thumb.classList.remove('active');
        });
        
        // Add active class to clicked thumbnail
        element.classList.add('active');
    }
</script>
@endsection