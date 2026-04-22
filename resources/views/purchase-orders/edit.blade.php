@extends('layouts.app')

@section('title','Edit Purchase Order')

@section('content')

<style>
    .form-header {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        padding: 24px 32px;
        border-radius: 16px;
        margin-bottom: 24px;
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.2);
    }

    .form-header h1 {
        margin: 0 0 6px 0;
        font-size: 24px;
        font-weight: 800;
    }

    .form-header p {
        margin: 0;
        opacity: 0.9;
        font-size: 13px;
    }

    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e0e0e0;
        margin-bottom: 24px;
    }

    .form-section {
        padding: 24px;
        border-bottom: 1px solid #f0f0f0;
    }

    .form-section:last-child {
        border-bottom: none;
    }

    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #10b981;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-submit {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        padding: 12px 32px;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
    }

    .btn-cancel {
        background: #6c757d;
        color: white;
        padding: 12px 32px;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-cancel:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }

    .item-row {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        border: 1px solid #e9ecef;
    }

    .remove-item {
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .remove-item:hover {
        background: #c82333;
        transform: scale(1.1);
    }

    .add-item-btn {
        background: #28a745;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .add-item-btn:hover {
        background: #218838;
        transform: translateY(-1px);
    }

    html.dark .form-card,
    html.dark .item-row {
        background: #0f172a;
        border-color: #334155;
        box-shadow: none;
    }
    html.dark .form-section {
        border-color: #1f2937;
    }
    html.dark .section-title,
    html.dark label,
    html.dark .form-label,
    html.dark h2,
    html.dark h3,
    html.dark h4,
    html.dark h5,
    html.dark h6 {
        color: #f8fafc;
    }
    html.dark .text-muted,
    html.dark .small {
        color: #94a3b8 !important;
    }
    html.dark input,
    html.dark select,
    html.dark textarea {
        background: #020617 !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }
    html.dark input::placeholder,
    html.dark textarea::placeholder {
        color: #64748b;
    }
    html.dark .btn-cancel {
        background: #334155;
    }
    html.dark .btn-cancel:hover {
        background: #475569;
    }
</style>

<div class="container-fluid">
    <!-- Form Header -->
    <div class="form-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="bi bi-pencil-square me-2"></i>Edit Purchase Order</h1>
                <p>Update purchase order details and items</p>
            </div>
            <a href="{{ route('purchase-orders.index') }}" class="btn-cancel">
                <i class="bi bi-arrow-left me-2"></i>Back to Orders
            </a>
        </div>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('purchase-orders.update', $purchaseOrder) }}" method="POST" id="purchaseOrderForm">
        @csrf
        @method('PUT')

        <!-- Order Details -->
        <div class="form-card">
            <div class="form-section">
                <h3 class="section-title">
                    <i class="bi bi-info-circle"></i>
                    Order Details
                </h3>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="supplier_id" class="form-label fw-semibold">Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" id="supplier_id" class="form-select" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ $purchaseOrder->supplier_id == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }} ({{ $supplier->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="warehouse_id" class="form-label fw-semibold">Warehouse <span class="text-danger">*</span></label>
                        <select name="warehouse_id" id="warehouse_id" class="form-select" required>
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ $purchaseOrder->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="order_date" class="form-label fw-semibold">Order Date <span class="text-danger">*</span></label>
                        <input type="date" name="order_date" id="order_date" class="form-control"
                               value="{{ old('order_date', $purchaseOrder->order_date->format('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="expected_delivery_date" class="form-label fw-semibold">Expected Delivery Date</label>
                        <input type="date" name="expected_delivery_date" id="expected_delivery_date" class="form-control"
                               value="{{ old('expected_delivery_date', $purchaseOrder->expected_delivery_date?->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="form-card">
            <div class="form-section">
                <h3 class="section-title">
                    <i class="bi bi-box-seam"></i>
                    Order Items
                </h3>

                <div id="itemsContainer">
                    <!-- Items will be added here -->
                </div>

                <button type="button" class="add-item-btn" onclick="addItem()">
                    <i class="bi bi-plus-circle me-2"></i>Add Item
                </button>
            </div>
        </div>

        <!-- Notes -->
        <div class="form-card">
            <div class="form-section">
                <h3 class="section-title">
                    <i class="bi bi-sticky"></i>
                    Additional Notes
                </h3>

                <div class="row">
                    <div class="col-12">
                        <textarea name="notes" id="notes" class="form-control" rows="3"
                                  placeholder="Any additional notes for this purchase order...">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('purchase-orders.index') }}" class="btn-cancel">
                <i class="bi bi-x-circle me-2"></i>Cancel
            </a>
            <button type="submit" class="btn-submit">
                <i class="bi bi-check-circle me-2"></i>Update Purchase Order
            </button>
        </div>
    </form>
</div>

<script>
let itemCount = 0;

function addItem(productId = '', quantity = 1, unitPrice = 0) {
    itemCount++;
    const itemHtml = `
        <div class="item-row" id="item-${itemCount}">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                    <select name="items[${itemCount}][product_id]" class="form-select" required onchange="updateTotal(${itemCount})">
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" ${productId == '{{ $product->id }}' ? 'selected' : ''} data-price="{{ $product->cost_price ?? 0 }}">
                                {{ $product->name }} ({{ $product->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="items[${itemCount}][quantity]" class="form-control"
                           value="${quantity}" min="1" required onchange="updateTotal(${itemCount})">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Unit Price <span class="text-danger">*</span></label>
                    <input type="number" name="items[${itemCount}][unit_price]" class="form-control"
                           value="${unitPrice}" min="0" step="0.01" required onchange="updateTotal(${itemCount})">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Total</label>
                    <input type="number" class="form-control" id="total-${itemCount}" readonly
                           value="${(quantity * unitPrice).toFixed(2)}">
                </div>
                <div class="col-md-2">
                    <button type="button" class="remove-item" onclick="removeItem(${itemCount})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', itemHtml);
    updateTotals();
}

function removeItem(itemId) {
    document.getElementById(`item-${itemId}`).remove();
    updateTotals();
}

function updateTotal(itemId) {
    const productSelect = document.querySelector(`select[name="items[${itemId}][product_id]"]`);
    const unitPriceInput = document.querySelector(`input[name="items[${itemId}][unit_price]"]`);
    const quantityInput = document.querySelector(`input[name="items[${itemId}][quantity]"]`);

    // Auto-populate unit price from product data-price attribute
    if (productSelect.value) {
        const selectedOption = productSelect.querySelector(`option[value="${productSelect.value}"]`);
        const costPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        if (costPrice > 0 && unitPriceInput.value == 0) {
            unitPriceInput.value = costPrice.toFixed(2);
        }
    }

    const quantity = parseFloat(quantityInput.value) || 0;
    const unitPrice = parseFloat(unitPriceInput.value) || 0;
    const total = quantity * unitPrice;
    document.getElementById(`total-${itemId}`).value = total.toFixed(2);
    updateTotals();
}

function updateTotals() {
    // This would calculate totals if needed
    // For now, we'll let the backend handle calculations
}

// Add initial item if none exist
document.addEventListener('DOMContentLoaded', function() {
    @if($purchaseOrder->items->count() > 0)
        @foreach($purchaseOrder->items as $item)
            addItem('{{ $item->product_id }}', '{{ $item->quantity }}', '{{ $item->unit_price }}');
        @endforeach
    @else
        if (itemCount === 0) {
            addItem();
        }
    @endif
});
</script>

@endsection
