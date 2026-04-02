@extends('layouts.app')

@section('title','Create Order')

@section('content')

<style>
    .page-header {
        padding: 24px 32px;
        border-radius: 16px;
        margin-bottom: 24px;
        animation: fadeInDown 0.5s ease;
    }

    .page-header h1 {
        margin: 0 0 4px 0;
        font-size: 24px;
        font-weight: 800;
    }

    .page-header p {
        margin: 0;
        opacity: 0.85;
        font-size: 13px;
    }

    .order-card {
        background: white;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid #E9FFFA;
        animation: fadeInUp 0.5s ease 0.2s backwards;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-control, .form-select {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 14px;
        transition: all 0.2s ease;
        background: white;
    }

    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: #03624C;
        box-shadow: 0 0 0 3px rgba(3, 98, 76, 0.1);
    }

    .item-row {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 12px;
        border: 1px solid #E9FFFA;
        transition: all 0.3s ease;
        animation: slideIn 0.3s ease;
    }

    .item-row:hover {
        border-color: #03624C;
        box-shadow: 0 4px 12px rgba(3, 98, 76, 0.08);
    }

    .product-select {
        cursor: pointer;
    }

    .btn-add-item {
        background: #03624C;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-add-item:hover {
        background: #024538;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(3, 98, 76, 0.3);
    }

    .btn-remove {
        background: white;
        color: #ef4444;
        border: 2px solid #ef4444;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-remove:hover {
        background: #ef4444;
        color: white;
        transform: translateY(-2px);
    }

    .order-summary {
        background: #E9FFFA;
        border-radius: 12px;
        padding: 24px;
        margin-top: 24px;
        border: 1px solid #d6f5ed;
    }

    .summary-line {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 14px;
        color: #64748b;
    }

    .summary-total {
        font-size: 20px;
        font-weight: 800;
        color: #03624C;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 2px solid #d6f5ed;
    }

    .quantity-input {
        text-align: center;
        font-weight: 600;
    }

    .quantity-input:focus {
        border-color: #03624C;
        box-shadow: 0 0 0 3px rgba(3, 98, 76, 0.1);
    }

    .product-price-display {
        padding: 12px 16px;
        background: white;
        border-radius: 8px;
        color: #03624C;
        font-weight: 600;
        text-align: center;
        border: 1px solid #E9FFFA;
    }

    .alert-custom {
        border-radius: 8px;
        border: none;
        padding: 16px;
        margin-bottom: 20px;
        animation: shake 0.5s ease;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }

    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }

    @media (max-width: 768px) {
        .item-row {
            padding: 16px;
        }
        .order-summary {
            padding: 20px;
        }
        .btn-remove {
            margin-top: 12px;
            width: 100%;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap" style="background: linear-gradient(135deg, #03624C, #0fb9b1); color: white;">
    <div>
        <h1>Create Order</h1>
        <p>Add items to the order</p>
    </div>
    <div>
        <a href="{{ route('orders.index') }}" class="btn-action btn-view" style="width: auto; padding: 12px 24px; font-size: 14px; text-decoration: none;">
            <i class="bi bi-arrow-left"></i> Back to Orders
        </a>
    </div>
</div>

<div class="order-card">
    @if($errors->any())
        <div class="alert alert-danger alert-custom">
            <h6 style="margin-bottom: 8px;"><i class="bi bi-exclamation-triangle"></i> Please fix these errors:</h6>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
        @csrf

        <!-- Warehouse -->
        <div class="mb-4">
            <label class="form-label">Warehouse</label>
            <select name="warehouse_id" class="form-select" required style="width: 100%;">
                <option value="">Select warehouse...</option>
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>
                @endforeach
            </select>
            <small class="text-muted" style="font-size: 12px; margin-top: 4px; display: block;">
                Select the warehouse for fulfillment
            </small>
        </div>

        <!-- Order Items -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <label class="form-label mb-0">Order Items</label>
                <button type="button" class="btn-add-item" id="addItem">
                    <i class="bi bi-plus-circle"></i> Add Product
                </button>
            </div>

            <div id="items">
                <div class="item-row item-row-template">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Product</label>
                            <select name="items[0][product_id]" class="form-select product-select" required>
                                <option value="">Select product...</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" data-price="{{ $p->price }}" data-stock="{{ $p->total_stock ?? 0 }}">
                                        {{ $p->name }} - {{ $p->sku }}
                                        @if($p->total_stock <= 0)
                                            <span style="color: #ef4444;"> (Out of stock)</span>
                                        @elseif($p->total_stock <= 10)
                                            <span style="color: #FFCC00;"> (Low: {{ $p->total_stock }})</span>
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="items[0][quantity]" class="form-control quantity-input" min="1" value="1" required>
                        </div>
                        <div class="col-md-2">
                            <div class="product-price-display">
                                $<span class="line-price">0.00</span>
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="button" class="btn-remove" style="display: none;">
                                <i class="bi bi-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="order-summary">
            <h6 style="margin-bottom: 16px; color: #03624C; font-weight: 700;">
                <i class="bi bi-calculator"></i> Order Summary
            </h6>
            <div class="summary-line">
                <span>Items</span>
                <span id="itemCount">0</span>
            </div>
            <div class="summary-line">
                <span>Subtotal</span>
                <span>$<span id="subtotal">0.00</span></span>
            </div>
            <div class="summary-line">
                <span>Tax</span>
                <span>$0.00</span>
            </div>
            <div class="summary-line summary-total">
                <span>Total</span>
                <span style="color: #03624C;">$<span id="total">0.00</span></span>
            </div>
        </div>

        <!-- Submit -->
        <div class="mt-4 d-flex justify-content-between align-items-center">
            <div style="color: #64748b; font-size: 13px;">
                <i class="bi bi-info-circle"></i> All fields required. Stock reserved on creation.
            </div>
            <button type="submit" class="action-btn action-btn-primary" style="width: auto; padding: 12px 32px;">
                <i class="bi bi-check-circle"></i> Create Order
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let idx = 1;
    const itemsContainer = document.getElementById('items');
    const template = document.querySelector('.item-row-template');

    function updateTotals() {
        let subtotal = 0;
        let itemCount = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseInt(row.querySelector('input[type="number"]')?.value) || 0;
            const price = parseFloat(row.querySelector('select option:checked')?.dataset.price) || 0;
            const lineTotal = qty * price;
            row.querySelector('.line-price').textContent = lineTotal.toFixed(2);
            itemCount += qty;
            subtotal += lineTotal;
        });

        document.getElementById('itemCount').textContent = itemCount;
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('total').textContent = subtotal.toFixed(2);
    }

    function addItem() {
        const newRow = template.cloneNode(true);
        newRow.querySelectorAll('select, input').forEach(function(el) {
            if (el.name) {
                el.name = el.name.replace(/items\[0\]/, 'items[' + idx + ']');
                if (el.type === 'number') el.value = 1;
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
            }
        });
        newRow.querySelector('.btn-remove').style.display = 'inline-flex';
        itemsContainer.appendChild(newRow);
        idx++;
        attachListenersToRow(newRow);
        updateTotals();

        // Animate
        newRow.style.opacity = '0';
        newRow.style.transform = 'translateX(-20px)';
        setTimeout(() => {
            newRow.style.transition = 'all 0.3s ease';
            newRow.style.opacity = '1';
            newRow.style.transform = 'translateX(0)';
        }, 10);
    }

    function removeItem(button) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length > 1) {
            button.closest('.item-row').style.opacity = '0';
            button.closest('.item-row').style.transform = 'translateX(20px)';
            setTimeout(() => button.closest('.item-row').remove(), 300);
            updateTotals();
            // Re-index
            document.querySelectorAll('.item-row').forEach((row, index) => {
                row.querySelectorAll('select, input').forEach(el => {
                    if (el.name) el.name = el.name.replace(/items\[\d+\]/, 'items[' + index + ']');
                });
            });
            idx = rows.length - 1;
        }
    }

    function attachListenersToRow(row) {
        row.querySelector('.product-select')?.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const price = parseFloat(selected.dataset.price) || 0;
            const qty = parseInt(row.querySelector('input[type="number"]').value) || 0;
            row.querySelector('.line-price').textContent = (price * qty).toFixed(2);
            updateTotals();
        });

        row.querySelector('.quantity-input')?.addEventListener('input', function() {
            const select = row.querySelector('.product-select');
            if (select?.value) {
                const price = parseFloat(select.options[select.selectedIndex].dataset.price) || 0;
                const qty = parseInt(this.value) || 0;
                row.querySelector('.line-price').textContent = (price * qty).toFixed(2);
            }
            updateTotals();
        });
    }

    function attachGlobalListeners() {
        document.getElementById('addItem').addEventListener('click', addItem);

        document.getElementById('items').addEventListener('click', function(e) {
            if (e.target.closest('.btn-remove')) {
                removeItem(e.target.closest('.btn-remove'));
            }
        });
    }

    attachGlobalListeners();
    attachListenersToRow(template);
    updateTotals();

    // Form submit loading
    document.getElementById('orderForm')?.addEventListener('submit', function(e) {
        const btn = this.querySelector('button[type="submit"]');
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
        btn.disabled = true;
    });

    // Animate initial row
    template.style.opacity = '0';
    template.style.transform = 'translateY(20px)';
    setTimeout(() => {
        template.style.transition = 'all 0.4s ease';
        template.style.opacity = '1';
        template.style.transform = 'translateY(0)';
    }, 200);
});
</script>

@endsection
