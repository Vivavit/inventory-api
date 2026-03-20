@extends('layouts.app')
@section('title','Create Order')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Create Order</h3>
        <small class="text-muted">Create a sales order</small>
    </div>
    <a href="{{ route('orders.index') }}" class="btn btn-light">Back to Orders</a>
</div>

<div class="custom-card">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('orders.store') }}" method="POST">
        @csrf
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Warehouse</label>
                <select name="warehouse_id" class="form-control" required>
                    <option value="">Select warehouse</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="items">
            <div class="row g-2 align-items-end item-row">
                <div class="col-md-6">
                    <label class="form-label">Product</label>
                    <select name="items[0][product_id]" class="form-control product-select" required>
                        <option value="">Select product</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-price="{{ $p->price }}">{{ $p->name }} - {{ $p->sku }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="items[0][quantity]" class="form-control" min="1" value="1" required>
                </div>
                <div class="col-md-3 text-end">
                    <button type="button" class="btn btn-outline-danger btn-remove">Remove</button>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="button" class="btn btn-sm btn-outline-primary" id="addItem">Add Item</button>
        </div>

        <div class="mt-4 text-end">
            <button class="btn btn-primary">Create Order</button>
        </div>
    </form>
</div>

<script>
    (function(){
        let idx = 1;
        document.getElementById('addItem').addEventListener('click', function(){
            const items = document.getElementById('items');
            const row = document.querySelector('.item-row').cloneNode(true);
            row.querySelectorAll('select, input').forEach(function(el){
                if (el.name) {
                    el.name = el.name.replace(/items\[0\]/, 'items['+idx+']');
                    if (el.type === 'number') el.value = 1;
                    if (el.tagName === 'SELECT') el.selectedIndex = 0;
                }
            });
            items.appendChild(row);
            idx++;
        });

        document.getElementById('items').addEventListener('click', function(e){
            if (e.target.classList.contains('btn-remove')){
                const rows = document.querySelectorAll('.item-row');
                if (rows.length > 1){
                    e.target.closest('.item-row').remove();
                }
            }
        });
    })();
</script>

@endsection