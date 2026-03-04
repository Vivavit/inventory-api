@extends('layouts.app')

@section('title', 'Add New Product')

@section('content')
<style>
    :root { --primary-color: #03624C; --bg-light: #f8faf9; }
    .custom-card { border: none; border-radius: 12px; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); background: #fff; padding: 1.5rem; margin-bottom: 1.5rem; }
    .section-title { font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--primary-color); border-bottom: 2px solid #eef2f1; padding-bottom: 10px; margin-bottom: 20px; }
    
    /* Image Upload Styling */
    .image-upload-area { border: 2px dashed #d1dbd8; border-radius: 12px; padding: 30px; text-align: center; cursor: pointer; transition: 0.3s; background: var(--bg-light); }
    .image-upload-area:hover { border-color: var(--primary-color); background: #e9fffb; }
    .preview-box { width: 150px; height: 150px; position: relative; border-radius: 12px; overflow: hidden; border: 2px solid var(--primary-color); margin: 0 auto; }
    .preview-box img { width: 100%; height: 100%; object-fit: cover; }
    
    /* Table Styling */
    .stock-table thead { background: var(--bg-light); }
    .stock-table th { font-size: 0.8rem; text-transform: uppercase; color: #6c757d; border: none; }
    
    /* Toggle Switch */
    .form-check-input:checked { background-color: var(--primary-color); border-color: var(--primary-color); }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1 fw-bold">Create New Product</h1>
            <p class="text-muted small">Add a new item to your inventory system</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-light btn-sm px-3 border">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <ul class="mb-0 small">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            {{-- Main Form Content --}}
            <div class="col-lg-8">
                <div class="custom-card">
                    <h6 class="section-title"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">Product Name *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Wireless Mechanical Keyboard" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">SKU *</label>
                            <div class="input-group">
                                <input type="text" name="sku" id="sku" class="form-control" value="{{ old('sku') }}" required>
                                <button type="button" class="btn btn-outline-dark" onclick="generateSKU()">Generate</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <h6 class="section-title"><i class="bi bi-tags me-2"></i>Pricing & Descriptions</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Price *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">$</span>
                                <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price') }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Compare Price</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">$</span>
                                <input type="number" step="0.01" name="compare_price" class="form-control" value="{{ old('compare_price') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Cost Price</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">$</span>
                                <input type="number" step="0.01" name="cost_price" class="form-control" value="{{ old('cost_price') }}">
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <label class="form-label fw-semibold small">Short Description</label>
                            <textarea name="short_description" class="form-control" rows="2" placeholder="Brief product summary...">{{ old('short_description') }}</textarea>
                        </div>
                        <div class="col-12 mt-3">
                            <label class="form-label fw-semibold small">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Full product details...">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <h6 class="section-title"><i class="bi bi-box-seam me-2"></i>Inventory Allocation</h6>
                    <div class="table-responsive">
                        <table class="table stock-table align-middle">
                            <thead>
                                <tr>
                                    <th>Warehouse</th>
                                    <th style="width: 150px;">Initial Qty</th>
                                    <th>Location Code</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($warehouses as $warehouse)
                                <tr>
                                    <td class="fw-medium text-dark">{{ $warehouse->name }}</td>
                                    <td>
                                        <input type="number" name="warehouse_stock[{{ $warehouse->id }}]" class="form-control form-control-sm stock-input" value="0" min="0">
                                    </td>
                                    <td>
                                        <input type="text" name="location_code[{{ $warehouse->id }}]" class="form-control form-control-sm" placeholder="A-01-01">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Sidebar Content --}}
            <div class="col-lg-4">
                {{-- Media Card --}}
                <div class="custom-card">
                    <h6 class="section-title">Product Image</h6>
                    <div id="uploadArea" class="image-upload-area" onclick="document.getElementById('images').click()">
                        <i class="bi bi-image text-muted h2"></i>
                        <p class="small mb-0">Click to upload image</p>
                        <span class="text-muted" style="font-size: 10px;">Max 2MB • JPEG, PNG</span>
                    </div>
                    <input type="file" id="images" name="images[]" class="d-none" accept="image/*" onchange="handleImageSelection(event)">
                    
                    <div id="imagePreviewContainer" class="mt-3 d-none text-center">
                        <div class="preview-box mb-2">
                            <img id="imagePreview" src="">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" onclick="removeImage()">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Settings Card --}}
                <div class="custom-card">
                    <h6 class="section-title">Settings</h6>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Brand</label>
                        <select name="brand_id" class="form-select">
                            <option value="">No Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                        <label class="form-check-label small fw-medium">Active Visibility</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="manage_stock" value="1" checked>
                        <label class="form-check-label small fw-medium">Track Stock Levels</label>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1">
                        <label class="form-check-label small fw-medium">Mark as Featured</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="background: var(--primary-color); border: none;">
                        <i class="bi bi-plus-lg me-1"></i> Create Product
                    </button>
                </div>

                {{-- Tips sidebar (From original code) --}}
                <div class="alert alert-light border shadow-sm">
                    <h6 class="small fw-bold text-dark"><i class="bi bi-lightbulb me-2"></i>Guidelines</h6>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-1"><strong>SKU:</strong> Must be unique for system tracking.</li>
                        <li class="mb-1"><strong>Price:</strong> Compare price should be higher than selling price.</li>
                        <li><strong>Stock:</strong> You can update stock per warehouse later.</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function generateSKU() {
        const random = Math.random().toString(36).substring(2, 8).toUpperCase();
        document.getElementById('sku').value = 'PROD-' + random;
    }

    function handleImageSelection(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
                document.getElementById('imagePreviewContainer').classList.remove('d-none');
                document.getElementById('uploadArea').classList.add('d-none');
            }
            reader.readAsDataURL(file);
        }
    }

    function removeImage() {
        document.getElementById('images').value = '';
        document.getElementById('imagePreviewContainer').classList.add('d-none');
        document.getElementById('uploadArea').classList.remove('d-none');
    }
</script>
@endpush