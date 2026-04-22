@extends('layouts.app')

@section('title', 'Add New Product')

@push('styles')
    @vite(['resources/css/features/product-form.css'])
@endpush

@section('content')
<div class="page-shell product-form-page">
    <section class="page-hero">
        <div>
            <p class="page-eyebrow">Catalog</p>
            <h1 class="page-title">Create a new product</h1>
            <p class="page-subtitle">Add a new inventory item with consistent pricing, media, warehouse allocation, and stock management settings.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to products
            </a>
        </div>
    </section>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="product-form-grid">
            <div class="section-stack">
                <x-card class="surface-card">
                    <h2 class="section-heading">Basic information</h2>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Product name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Wireless Mechanical Keyboard" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SKU <span class="required">*</span></label>
                            <div class="input-group">
                                <input type="text" name="sku" id="sku" class="form-control" value="{{ old('sku') }}" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="generateSKU()">Generate</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="required">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </x-card>

                <x-card class="surface-card">
                    <h2 class="section-heading">Pricing and content</h2>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Price <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price') }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Compare price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="compare_price" class="form-control" value="{{ old('compare_price') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cost price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="cost_price" class="form-control" value="{{ old('cost_price') }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Short description</label>
                            <textarea name="short_description" class="form-control" rows="2" placeholder="Brief product summary...">{{ old('short_description') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Detailed product information...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </x-card>

                <x-card class="surface-card">
                    <div class="card-header-row">
                        <div>
                            <h2 class="section-heading mb-0">Inventory allocation</h2>
                            <p class="card-subtitle">Set opening quantities and location codes for each warehouse.</p>
                        </div>
                    </div>

                    <div class="table-shell">
                        <div class="table-responsive">
                            <table class="stock-table">
                                <thead>
                                    <tr>
                                        <th>Warehouse</th>
                                        <th>Initial qty</th>
                                        <th>Location code</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($warehouses as $warehouse)
                                        <tr>
                                            <td>{{ $warehouse->name }}</td>
                                            <td>
                                                <input type="number" name="warehouse_stock[{{ $warehouse->id }}]" class="form-control" value="0" min="0">
                                            </td>
                                            <td>
                                                <input type="text" name="location_code[{{ $warehouse->id }}]" class="form-control" placeholder="A-01-01">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </x-card>
            </div>

            <div class="product-form-sidebar">
                <x-card class="surface-card">
                    <h2 class="section-heading">Product media</h2>
                    <div id="uploadArea" class="image-upload-area" onclick="document.getElementById('images').click()">
                        <i class="bi bi-image h2 d-block mb-2 text-muted"></i>
                        <p class="mb-1 fw-semibold">Click to upload image</p>
                        <span class="text-muted small">Max 2 MB · JPEG or PNG</span>
                    </div>
                    <input type="file" id="images" name="images[]" class="d-none" accept="image/*" onchange="handleImageSelection(event)">
                    <div id="imagePreviewContainer" class="mt-3 d-none text-center">
                        <div class="preview-box mb-3">
                            <img id="imagePreview" src="" alt="Image preview">
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="removeImage()">Remove image</button>
                    </div>
                </x-card>

                <x-card class="surface-card">
                    <h2 class="section-heading">Product settings</h2>
                    <div class="form-group">
                        <label class="form-label">Brand</label>
                        <select name="brand_id" class="form-select">
                            <option value="">No brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="settings-block">
                        <div class="settings-row">
                            <div>
                                <p class="settings-label mb-1">Active visibility</p>
                                <p class="text-muted small mb-0">Make the product available immediately.</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                            </div>
                        </div>
                        <div class="settings-row">
                            <div>
                                <p class="settings-label mb-1">Track stock levels</p>
                                <p class="text-muted small mb-0">Monitor quantities and warehouse allocation.</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="manage_stock" value="1" checked>
                            </div>
                        </div>
                        <div class="settings-row">
                            <div>
                                <p class="settings-label mb-1">Featured product</p>
                                <p class="text-muted small mb-0">Highlight this item in key surfaces.</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-3">
                        <i class="bi bi-plus-lg me-1"></i> Create product
                    </button>
                </x-card>

                <div class="tip-panel">
                    <h2 class="section-heading">Guidelines</h2>
                    <ul class="helper-list">
                        <li><strong>SKU:</strong> must be unique for system tracking.</li>
                        <li><strong>Pricing:</strong> compare price should exceed selling price.</li>
                        <li><strong>Stock:</strong> quantities can be adjusted per warehouse later.</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/features/product-form.js'])
@endpush
