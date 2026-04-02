@php
    $warehouses = App\Models\Warehouse::all();
    $primaryImage = $product->images->where('is_primary', true)->first() ?? $product->images->first();
    $allImages = $product->images;
@endphp

@extends('layouts.app')

@section('title', $product->name)
@section('page-title', $product->name)
@section('page-subtitle', 'Product Details')

@section('content')
<style>
    .product-card {
        background-color: #FFFFFF;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid #E9FFFA;
        margin-bottom: 24px;
        animation: fadeInUp 0.6s ease forwards;
        opacity: 0;
    }

    .product-image-main {
        width: 100%;
        height: 350px;
        object-fit: cover;
        border-radius: 12px;
        border: 3px solid #03624C;
        transition: all 0.3s ease;
    }

    .product-image-main:hover {
        transform: scale(1.02);
        box-shadow: 0 8px 24px rgba(3, 98, 76, 0.15);
    }

    .product-image-thumbnail {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        border: 2px solid #E9FFFA;
        transition: all 0.2s ease;
        margin-right: 8px;
    }

    .product-image-thumbnail:hover,
    .product-image-thumbnail.active {
        border-color: #03624C;
        transform: scale(1.1);
    }

    .image-thumbnails {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 12px;
    }

    .no-image-placeholder {
        width: 100%;
        height: 350px;
        background: linear-gradient(135deg, #E9FFFA, #d6f5ed);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #03624C;
        border: 2px dashed #03624C;
    }

    .info-label {
        color: #8E8E93;
        font-size: 13px;
        margin-bottom: 4px;
        font-weight: 500;
    }

    .info-value {
        font-weight: 600;
        color: #262626;
        font-size: 15px;
    }

    .product-name {
        font-size: 26px;
        font-weight: 700;
        color: #03624C;
        margin-bottom: 12px;
    }

    .product-sku {
        font-family: Monaco, Consolas, monospace;
        background: #E9FFFA;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        color: #03624C;
        font-weight: 600;
        display: inline-block;
    }

    .stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
    }

    .stock-in {
        background-color: rgba(52, 199, 89, 0.15);
        color: #34C759;
    }

    .stock-low {
        background-color: rgba(255, 204, 0, 0.2);
        color: #FFCC00;
    }

    .stock-out {
        background-color: rgba(255, 59, 48, 0.15);
        color: #FF3B31;
    }

    .stat-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 16px;
        text-align: center;
        border: 1px solid #E9FFFA;
        transition: all 0.3s ease;
    }

    .stat-box:hover {
        background: #E9FFFA;
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(3, 98, 76, 0.1);
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #03624C;
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 12px;
        color: #8E8E93;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #03624C;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 2px solid #E9FFFA;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .warehouse-item {
        background: #f8fafc;
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 10px;
        border-left: 3px solid #03624C;
        transition: all 0.2s ease;
    }

    .warehouse-item:hover {
        background: #E9FFFA;
        border-left-color: #0fb9b1;
        transform: translateX(4px);
    }

    .description-text {
        line-height: 1.8;
        color: #475569;
        font-size: 14px;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .custom-table thead {
        background-color: #03624C;
        color: white;
    }

    .custom-table th {
        padding: 14px 16px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
    }

    .custom-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #E9FFFA;
        vertical-align: middle;
    }

    .custom-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .badge-custom {
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-success {
        background-color: rgba(52, 199, 89, 0.15);
        color: #34C759;
    }

    .badge-warning {
        background-color: rgba(255, 204, 0, 0.2);
        color: #FFCC00;
    }

    .badge-danger {
        background-color: rgba(255, 59, 48, 0.15);
        color: #FF3B31;
    }

    .badge-neutral {
        background-color: #f1f5f9;
        color: #64748b;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }

    .action-btn-primary {
        background: #03624C;
        color: white;
    }

    .action-btn-primary:hover {
        background: #024538;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(3, 98, 76, 0.3);
    }

    .action-btn-secondary {
        background: white;
        color: #03624C;
        border: 2px solid #03624C;
    }

    .action-btn-secondary:hover {
        background: #03624C;
        color: white;
    }

    .action-btn-danger {
        background: white;
        color: #ef4444;
        border: 2px solid #ef4444;
    }

    .action-btn-danger:hover {
        background: #ef4444;
        color: white;
    }

    .quick-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @media (max-width: 768px) {
        .product-name {
            font-size: 20px;
        }
        .product-image-main {
            height: 250px;
        }
        .product-image-thumbnail {
            width: 60px;
            height: 60px;
        }
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<div class="product-container" style="opacity: 0; animation: fadeInUp 0.6s ease forwards;">
    <div class="row">
        <!-- Left Column: Images & Main Info -->
        <div class="col-lg-7">
            <div class="product-card">
                <!-- Image Gallery -->
                <div class="mb-4">
                    @if($allImages->count() > 0)
                        @php $mainImage = $allImages->first(); @endphp
                        <img id="mainImage"
                             src="{{ $mainImage->url }}"
                             alt="{{ $product->name }}"
                             class="product-image-main"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                        <div class="no-image-placeholder" style="display: none;">
                            <i class="bi bi-image"></i>
                            <span>No image available</span>
                        </div>

                        @if($allImages->count() > 1)
                            <div class="image-thumbnails">
                                @foreach($allImages as $image)
                                    <img src="{{ $image->url }}"
                                         alt="{{ $image->alt_text ?? $product->name }}"
                                         class="product-image-thumbnail {{ $loop->first ? 'active' : '' }}"
                                         onclick="changeMainImage(this)"
                                         onerror="this.style.display='none'">
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="no-image-placeholder">
                            <i class="bi bi-image"></i>
                            <span>No images uploaded</span>
                        </div>
                    @endif
                </div>

                <!-- Product Header -->
                <div class="mb-4">
                    <h1 class="product-name">{{ $product->name }}</h1>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="product-sku">{{ $product->sku }}</span>
                        @if($product->brand)
                            <span class="badge badge-neutral">
                                <i class="bi bi-building"></i> {{ $product->brand->name }}
                            </span>
                        @endif
                        @if($product->category)
                            <span class="badge badge-neutral">
                                <i class="bi bi-tag"></i> {{ $product->category->name }}
                            </span>
                        @endif
                    </div>

                    <!-- Quick Stats -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="stat-box">
                                <div class="stat-value">${{ number_format($product->price, 2) }}</div>
                                <div class="stat-label">Price</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-box">
                                <div class="stat-value">${{ number_format($product->cost_price ?? 0, 2) }}</div>
                                <div class="stat-label">Cost</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-box">
                                <div class="stat-value">{{ $product->images->count() }}</div>
                                <div class="stat-label">Images</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-box">
                                <div class="stat-value">{{ $product->variants->count() }}</div>
                                <div class="stat-label">Variants</div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Status -->
                    @php
                        $totalStock = $product->total_stock;
                        $lowStockThreshold = $product->default_low_stock_threshold ?? 10;
                        $stockClass = $totalStock <= 0 ? 'stock-out' : ($totalStock <= $lowStockThreshold ? 'stock-low' : 'stock-in');
                        $stockLabel = $totalStock <= 0 ? 'Out of Stock' : ($totalStock <= $lowStockThreshold ? 'Low Stock' : 'In Stock');
                    @endphp

                    <div class="mb-4">
                        <div class="info-label">Stock Status</div>
                        <span class="stock-badge {{ $stockClass }}">
                            <i class="bi bi-{{ $totalStock <= 0 ? 'x-circle' : ($totalStock <= $lowStockThreshold ? 'exclamation-triangle' : 'check-circle') }}"></i>
                            {{ $stockLabel }} - {{ $totalStock }} units
                        </span>
                    </div>

                    <!-- Description -->
                    @if($product->description)
                        <div class="mt-4">
                            <div class="info-label">Description</div>
                            <p class="description-text">{{ $product->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Sidebar -->
        <div class="col-lg-5">
            <!-- Product Information -->
            <div class="product-card">
                <h3 class="section-title">
                    <i class="bi bi-info-circle"></i> Product Information
                </h3>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="info-label">Weight</div>
                        <div class="info-value">{{ $product->weight ? $product->weight . ' kg' : 'N/A' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="info-label">Low Stock Alert</div>
                        <div class="info-value">{{ $product->default_low_stock_threshold ?? 10 }} units</div>
                    </div>
                    <div class="col-6">
                        <div class="info-label">Stock Management</div>
                        <div class="info-value">
                            @if($product->manage_stock)
                                <span class="badge-custom badge-success">Enabled</span>
                            @else
                                <span class="badge-custom badge-neutral">Disabled</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            @if($product->is_active)
                                <span class="badge-custom badge-success">Active</span>
                            @else
                                <span class="badge-custom badge-danger">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-label">Featured</div>
                        <div class="info-value">
                            @if($product->is_featured)
                                <span class="badge-custom badge-warning"><i class="bi bi-star-fill"></i> Featured</span>
                            @else
                                <span class="badge-custom badge-neutral">Standard</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-label">Has Variants</div>
                        <div class="info-value">
                            @if($product->has_variants)
                                <span class="badge-custom badge-success">Yes</span>
                            @else
                                <span class="badge-custom badge-neutral">No</span>
                            @endif
                        </div>
                    </div>
                </div>

                <hr style="border: none; border-top: 1px solid #E9FFFA; margin: 20px 0;">

                <!-- Financial Info -->
                <h3 class="section-title">
                    <i class="bi bi-cash-stack"></i> Financial Overview
                </h3>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="info-label">Selling Price</div>
                        <div class="info-value" style="color: #34C759; font-size: 18px;">${{ number_format($product->price, 2) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="info-label">Cost Price</div>
                        <div class="info-value" style="color: #FF3B31; font-size: 18px;">${{ number_format($product->cost_price ?? 0, 2) }}</div>
                    </div>
                    @if($product->compare_price)
                        <div class="col-6">
                            <div class="info-label">Compare Price</div>
                            <div class="info-value" style="text-decoration: line-through; color: #8E8E93;">${{ number_format($product->compare_price, 2) }}</div>
                        </div>
                    @endif
                    <div class="col-6">
                        <div class="info-label">Total Sold</div>
                        <div class="info-value" style="font-size: 18px; color: #03624C;">{{ $product->sold_count ?? 0 }}</div>
                    </div>
                </div>

                <hr style="border: none; border-top: 1px solid #E9FFFA; margin: 20px 0;">

                <!-- Actions -->
                <h3 class="section-title">
                    <i class="bi bi-gear"></i> Quick Actions
                </h3>
                <div class="quick-actions">
                    <a href="{{ route('products.edit', $product) }}" class="action-btn action-btn-secondary">
                        <i class="bi bi-pencil"></i> Edit Product
                    </a>
                    @can('manage-products')
                        <form action="{{ route('products.destroy', $product) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this product? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn action-btn-danger">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

            <!-- Views -->
            <div class="product-card">
                <h3 class="section-title">
                    <i class="bi bi-eye"></i> Views
                </h3>
                <div class="stat-box">
                    <div class="stat-value">{{ $product->views_count ?? 0 }}</div>
                    <div class="stat-label">Page Views</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Warehouse Stock -->
    <div class="product-card" style="margin-top: 24px; animation-delay: 0.2s;">
        <h3 class="section-title">
            <i class="bi bi-boxes"></i> Stock by Warehouse
        </h3>

        @if($product->warehouseProducts && $product->warehouseProducts->count() > 0)
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th><i class="bi bi-building"></i> Warehouse</th>
                            <th style="text-align: right;"><i class="bi bi-box"></i> Stock Quantity</th>
                            <th style="text-align: center;"><i class="bi bi-activity"></i> Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($product->warehouseProducts as $wp)
                            @php
                                $quantity = $wp->quantity;
                                $status = $quantity <= 0 ? 'out' : ($quantity <= ($product->default_low_stock_threshold ?? 10) ? 'low' : 'good');
                            @endphp
                            <tr>
                                <td><strong>{{ $wp->warehouse->name ?? 'N/A' }}</strong></td>
                                <td style="text-align: right;"><strong>{{ $quantity }}</strong> units</td>
                                <td style="text-align: center;">
                                    @if($status === 'out')
                                        <span class="badge-custom badge-danger">Out of Stock</span>
                                    @elseif($status === 'low')
                                        <span class="badge-custom badge-warning">Low Stock</span>
                                    @else
                                        <span class="badge-custom badge-success">In Stock</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5" style="color: #8E8E93;">
                <i class="bi bi-inbox" style="font-size: 48px;"></i>
                <p class="mt-2">No stock assigned to any warehouse</p>
                <a href="{{ route('products.edit', $product) }}" class="action-btn action-btn-primary" style="margin-top: 12px;">
                    <i class="bi bi-plus"></i> Add Stock
                </a>
            </div>
        @endif
    </div>

    <!-- Variants -->
    @if($product->variants->count() > 0)
        <div class="product-card" style="margin-top: 24px; animation-delay: 0.3s;">
            <h3 class="section-title">
                <i class="bi bi-collection"></i> Product Variants
            </h3>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Variant</th>
                            <th>SKU</th>
                            <th>Options</th>
                            <th style="text-align: right;">Price</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($product->variants as $variant)
                            <tr>
                                <td><strong>{{ $variant->name }}</strong></td>
                                <td><code style="background: #E9FFFA; padding: 4px 8px; border-radius: 4px;">{{ $variant->sku }}</code></td>
                                <td>
                                    @foreach($variant->options as $key => $value)
                                        <span class="badge-custom badge-neutral" style="margin-right: 6px;">{{ ucfirst($key) }}: {{ $value }}</span>
                                    @endforeach
                                </td>
                                <td style="text-align: right; font-weight: 600; color: #34C759;">${{ number_format($variant->price, 2) }}</td>
                                <td style="text-align: center;">
                                    @if($variant->is_active)
                                        <span class="badge-custom badge-success">Active</span>
                                    @else
                                        <span class="badge-custom badge-neutral">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
function changeMainImage(thumbnail) {
    const mainImage = document.getElementById('mainImage');
    mainImage.src = thumbnail.src;

    document.querySelectorAll('.product-image-thumbnail').forEach(img => {
        img.classList.remove('active');
    });
    thumbnail.classList.add('active');

    // Smooth fade transition
    mainImage.style.opacity = '0.7';
    setTimeout(() => mainImage.style.opacity = '1', 150);
}

document.addEventListener('DOMContentLoaded', function() {
    // Staggered animation for cards
    const cards = document.querySelectorAll('.product-card, .stat-box');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
});
</script>
@endsection
