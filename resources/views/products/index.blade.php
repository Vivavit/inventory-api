@extends('layouts.app')
@section('title','Products')

@section('content')

<style>
    .product-header {
        justify-content: space-between;
        align-items: center;
        padding: 24px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,.04);
    }

    .product-actions {
        display: flex;
        gap: 8px;
    }

    .btn-outline-primary {
        color: var(--green);
        border-color: var(--green);
    }

    .btn-outline-primary:hover {
        background-color: var(--green);
        border-color: var(--green);
    }

    .btn-outline-danger {
        color: #ff6b6b;
        border-color: #ff6b6b;
    }

    .btn-outline-danger:hover {
        background-color: #ff6b6b;
        color: white;
        border-color: #ff6b6b;
    }
</style>
<div>
<!-- Page Header -->
    <div class="product-header">
    <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 24px;">
        <div>
        <h1 class="page-title">Products</h1>
        <p style="color: #999; margin: 0; font-size: 13px;">Manage and organize your product inventory</p>
    </div>
    
    {{-- Only show Add Product button for users with 'manage-products' permission --}}
    @can('manage-products')
    <a href="{{ route('products.create') }}" class="btn btn-primary btn-lg">
        <i class="bi bi-plus-lg"></i> Add Product
    </a>
    @endcan
    </div>


<!-- Products Table Card -->
    <div >

        @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Image</th>
                            <th>Product Info</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th style="width: 140px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            @php $stock = $product->inventoryLocations->sum('quantity'); @endphp
                            <tr>
                                <td>
                                    @if($product->primaryImage)
                                        <img src="{{ $product->primaryImage->url }}" alt="{{ $product->name }}" 
                                            style="width: 72px; height: 48px; object-fit: cover; border-radius: 6px;">
                                    @else
                                        <div style="width: 72px; height: 48px; background: var(--mint); display: flex; align-items: center; justify-content: center; border-radius: 6px; color: #ddd;">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #333; margin-bottom: 4px;">{{ $product->name }}</div>
                                    <small style="color: #999;">{{ Str::limit($product->short_description, 60) }}</small>
                                </td>
                                <td>
                                    <code style="background: var(--mint); padding: 2px 6px; border-radius: 4px; font-size: 11px;">{{ $product->sku }}</code>
                                </td>
                                <td>
                                    @if($product->category)
                                        <span class="badge badge-success">{{ $product->category->name }}</span>
                                    @else
                                        <span style="color: #999; font-size: 12px;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <strong style="color: var(--green);">{{ $stock }}</strong>
                                    <span style="color: #999;"> units</span>
                                </td>
                                <td>
                                    <strong style="color: var(--green);">${{ number_format($product->price, 2) }}</strong>
                                </td>
                                <td class="text-end">
                                    <div class="product-actions" style="justify-content: flex-end;">
                                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('manage-products')
                                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('products.destroy', $product) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Delete this product?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="display: flex; justify-content: center; margin-top: 24px;">
                {{ $products->links() }}
            </div>
        @else
            <div style="text-align: center; padding: 48px 24px;">
                <i class="bi bi-inbox" style="font-size: 48px; color: #ccc; display: block; margin-bottom: 12px;"></i>
                <p style="color: #999; margin: 0;">No products found. Create one to get started!</p>
            </div>
        @endif
    </div>
</div>

@endsection