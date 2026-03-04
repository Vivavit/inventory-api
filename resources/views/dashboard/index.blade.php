@extends('layouts.app')
@section('title','Dashboard')

@section('content')

<div class="custom-card mb-4"
     style="background:linear-gradient(135deg,#03624C,#0fb9b1);color:white;">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold">Welcome, {{ auth()->user()->name }}</h3>
            <small class="opacity-75">
                {{ now()->format('l, F j') }} • {{ now()->format('h:i A') }}
            </small>
        </div>
        <div class="text-end">
            <small>Total Inventory Value</small>
            <h3 class="fw-bold">${{ $stats['total_inventory_value'] }}</h3>
        </div>
    </div>
</div>

{{-- Key Metrics --}}
<div class="row g-4 mb-4">
    @foreach([
        ['Total Stock',$totalStock,'bi-box','var(--teal)'],
        ['Products',$stats['total_products'],'bi-collection','var(--blue)'],
        ['Out of Stock',$outOfStock,'bi-x-circle','var(--red)'],
        ['Low Stock',$lowOnStock,'bi-exclamation','var(--yellow)']
    ] as $card)
    <div class="col-lg-3 col-md-6">
        <div class="custom-card text-center">
            <i class="bi {{ $card[2] }} fs-1 mb-2" style="color:{{ $card[3] }}"></i>
            <h3 class="fw-bold">{{ $card[1] }}</h3>
            <div class="text-muted small">{{ $card[0] }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-4">
    {{-- Recent Products --}}
    <div class="col-lg-7">
        <div class="custom-card">
            <div class="section-title mb-3">Recent Products</div>
            <div style="max-height: 450px; overflow-y: auto;">
                <table class="table align-middle table-sm">
                    <thead class="text-muted small">
                        <tr>
                            <th style="width: 40%;">Name</th>
                            <th style="width: 15%;">Price</th>
                            <th style="width: 15%;">Stock</th>
                            <th style="width: 15%;">Sold</th>
                            <th style="width: 15%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($recentProducts as $product)
                        <tr onclick="location.href='{{ route('products.show',$product) }}'" 
                            style="cursor:pointer; transition: all 0.2s ease;"
                            onmouseover="this.style.backgroundColor='#f8f9fa';"
                            onmouseout="this.style.backgroundColor='transparent';">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($product->primaryImage)
                                        <img src="{{ $product->primaryImage->url }}" alt="{{ $product->name }}"
                                             style="width: 32px; height: 32px; object-fit: cover; border-radius: 4px;">
                                    @else
                                        <div style="width: 32px; height: 32px; background: #e9ecef; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-image" style="font-size: 12px; color: #ccc;"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <small class="fw-semibold d-block">{{ $product->name }}</small>
                                        <small class="text-muted">{{ $product->sku }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><small class="fw-semibold">${{ number_format($product->price,2) }}</small></td>
                            <td>
                                @php $stock = $product->total_stock ?? 0; @endphp
                                <small>{{ $stock }}</small>
                            </td>
                            <td><small class="text-green">{{ $product->sold_count ?? 0 }}</small></td>
                            <td>
                                @php $stock = $product->total_stock ?? 0; @endphp
                                @if($stock<=0)
                                    <span class="badge bg-danger">Out</span>
                                @elseif($stock<=10)
                                    <span class="badge bg-warning">Low</span>
                                @else
                                    <span class="badge bg-success">In</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-3">
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-light">
                    View All Products <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="col-lg-5">
        {{-- Top Selling Products --}}
        <div class="custom-card mb-4">
            <div class="section-title mb-3">Top Selling Products</div>
            <div style="max-height: 200px; overflow-y: auto;">
                @php
                    $topProducts = $recentProducts->sortByDesc('sold_count')->take(5);
                @endphp
                @forelse($topProducts as $product)
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2" style="border-bottom: 1px solid #e9ecef;">
                        <small class="fw-semibold" style="flex: 1;">{{ $product->name }}</small>
                        <span class="badge bg-success">{{ $product->sold_count ?? 0 }} sold</span>
                    </div>
                @empty
                    <small class="text-muted">No sales data available</small>
                @endforelse
            </div>
        </div>

        {{-- Low Stock Alert --}}
        <div class="custom-card">
            <div class="section-title mb-3">Low Stock Alert</div>
            <div style="max-height: 200px; overflow-y: auto;">
                @php
                    $lowStockProducts = $recentProducts->filter(function($p) { return ($p->total_stock ?? 0) <= 10 && ($p->total_stock ?? 0) > 0; })->take(5);
                @endphp
                @forelse($lowStockProducts as $product)
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2" style="border-bottom: 1px solid #e9ecef;">
                        <small class="fw-semibold" style="flex: 1;">{{ $product->name }}</small>
                        <span class="badge bg-warning">{{ $product->total_stock ?? 0 }} left</span>
                    </div>
                @empty
                    <small class="text-muted d-block text-success">✓ All products well stocked!</small>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
