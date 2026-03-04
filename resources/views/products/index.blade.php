@extends('layouts.app')
@section('title','Products')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Products</h3>
        <small class="text-muted">Manage your product inventory</small>
    </div>
    
    {{-- Only show Add Product button for users with 'manage-products' permission --}}
    @can('manage-products')
    <a href="{{ route('products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Product
    </a>
    @endcan
</div>

<div class="custom-card">
    <div class="section-title mb-4">Product Catalog</div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th></th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Brand / Category</th>
                    <th>Stock</th>
                    <th>Price</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    @php $stock = $product->inventoryLocations->sum('quantity'); @endphp
                    <tr>
                        <td style="width:80px;">
                            @if($product->primaryImage)
                                <img src="{{ $product->primaryImage->url }}" alt="{{ $product->name }}" style="width:72px; height:48px; object-fit:cover; border-radius:6px;">
                            @else
                                <div style="width:72px; height:48px; background:#f1f3f5; display:flex; align-items:center; justify-content:center; border-radius:6px; color:#ccc;">
                                    <i class="bi bi-image"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $product->name }}</div>
                            <small class="text-muted">{{ Str::limit($product->short_description, 80) }}</small>
                        </td>
                        <td>{{ $product->sku }}</td>
                        <td>
                            @if($product->brand)
                                <div class="small">{{ $product->brand->name }}</div>
                            @endif
                            @if($product->category)
                                <div class="small text-muted">{{ $product->category->name }}</div>
                            @endif
                        </td>
                        <td>{{ $stock }} units</td>
                        <td>${{ number_format($product->price,2) }}</td>
                        <td class="text-end">
                            <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-light">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('manage-products')
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form action="{{ route('products.destroy', $product) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $products->links() }}
    </div>
</div>

@endsection