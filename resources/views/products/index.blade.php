@extends('layouts.app')

@section('title','Products')

@push('styles')
    @vite(['resources/css/features/products.css'])
@endpush

@section('content')

<div class="page-shell">
<section class="page-hero">
    <div>
        <p class="page-eyebrow">Catalog</p>
        <h1 class="page-title">Products</h1>
        <p class="page-subtitle">Manage product information, pricing, stock position, and quick edits from one consistent data table.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary btn-add-product">
            <i class="bi bi-plus-lg"></i> Add product
        </button>
    </div>
</section>

{{-- Table --}}
<div class="products-card">
    @if($products->count() > 0)
        <div class="table-responsive">
            <table class="products-table">
                <thead>
                    <tr>
                        <th style="width:76px;">Image</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        @php
                            $stock = $product->total_stock;
                            $sc    = $stock <= 0 ? 'lo' : ($stock <= 10 ? 'mid' : 'hi');
                        @endphp
                        <tr>
                            <td>
                                @if($product->primaryImage)
                                    <img src="{{ $product->primaryImage->url }}"
                                         alt="{{ $product->name }}"
                                         class="product-thumbnail"
                                         data-id="{{ $product->id }}"
                                         onerror="this.src='https://placehold.co/68x46/e9fff9/03624C?text=?'">
                                @else
                                    <div style="width:68px;height:46px;background:var(--bg-tertiary);border-radius:7px;display:flex;align-items:center;justify-content:center;color:var(--text-secondary);" class="product-thumbnail" data-id="{{ $product->id }}">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <p class="product-name">{{ $product->name }}</p>
                                <span class="product-desc">{{ Str::limit($product->short_description, 55) }}</span>
                            </td>
                            <td><span class="sku-tag">{{ $product->sku }}</span></td>
                            <td>
                                @if($product->category)
                                    <span class="cat-badge">{{ $product->category->name }}</span>
                                @else
                                    <span style="color:var(--text-tertiary);">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="stock-num {{ $sc }}">{{ $stock }}</span>
                                <span style="color:var(--text-secondary);font-size:11px;"> units</span>
                            </td>
                            <td><span class="price-val">${{ number_format($product->price,2) }}</span></td>
                            <td style="text-align:right;">
                                <div class="action-buttons">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-primary btn-sm btn-icon" title="View"><i class="bi bi-eye"></i></a>
                                    <button type="button" class="btn btn-outline btn-sm btn-icon btn-edit-product" data-id="{{ $product->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" style="display:inline;" data-product-name="{{ $product->name }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="display:flex;justify-content:center;padding:18px;">
            {{ $products->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <h5>No Products Yet</h5>
            <p>Create your first product to get started.</p>
            <button type="button" class="btn btn-primary mt-3 btn-add-product">
                <i class="bi bi-plus-lg"></i> Add Product
            </button>
        </div>
    @endif
</div>
</div>

{{-- ============================================================
     PRODUCT MODAL
     ============================================================ --}}
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Add New Product
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="productForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-body">
                    <div class="row g-4">

                        {{-- ── LEFT COLUMN ── --}}
                        <div class="col-lg-8">

                            {{-- Basic Info --}}
                            <div class="form-section">
                                <div class="form-section-title"><i class="bi bi-info-circle"></i> Basic Information</div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="flabel">Product Name <span class="req">*</span></label>
                                        <input type="text" name="name" id="mName" class="finput" placeholder="e.g. Wireless Mechanical Keyboard" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="flabel">SKU <span class="req">*</span></label>
                                        <div class="sku-row">
                                            <input type="text" name="sku" id="mSku" class="finput" required>
                                            <button type="button" class="btn-gen" onclick="window.generateSKU()">Generate</button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="flabel">Category</label>
                                        <select name="category_id" id="mCategory" class="fselect">
                                            <option value="">— Select Category —</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="flabel">Brand</label>
                                        <select name="brand_id" id="mBrand" class="fselect">
                                            <option value="">— No Brand —</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Pricing --}}
                            <div class="form-section">
                                <div class="form-section-title"><i class="bi bi-cash-stack"></i> Pricing</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="flabel">Selling Price <span class="req">*</span></label>
                                        <div class="input-prefix">
                                            <span>$</span>
                                            <input type="number" step="0.01" min="0" name="price" id="mPrice" placeholder="0.00" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="flabel">Compare Price</label>
                                        <div class="input-prefix">
                                            <span>$</span>
                                            <input type="number" step="0.01" min="0" name="compare_price" id="mComparePrice" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="flabel">Cost Price</label>
                                        <div class="input-prefix">
                                            <span>$</span>
                                            <input type="number" step="0.01" min="0" name="cost_price" id="mCostPrice" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Descriptions --}}
                            <div class="form-section">
                                <div class="form-section-title"><i class="bi bi-text-left"></i> Descriptions</div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="flabel">Short Description</label>
                                        <input type="text" name="short_description" id="mShortDesc" class="finput" placeholder="Brief product summary…">
                                    </div>
                                    <div class="col-12">
                                        <label class="flabel">Full Description</label>
                                        <textarea name="description" id="mDesc" class="finput ftextarea" rows="3" placeholder="Detailed product information…"></textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Warehouse Stock --}}
                            <div class="form-section">
                                <div class="form-section-title"><i class="bi bi-building"></i> Warehouse Stock Allocation</div>
                                <div class="table-responsive">
                                    <table class="wh-table">
                                        <thead>
                                            <tr>
                                                <th>Warehouse</th>
                                                <th>Initial Qty</th>
                                                <th>Location Code</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($warehouses as $wh)
                                            <tr>
                                                <td style="font-weight:600;color:var(--text-primary);">{{ $wh->name }}</td>
                                                <td><input type="number" name="warehouse_stock[{{ $wh->id }}]" class="wh-input" value="0" min="0"></td>
                                                <td><input type="text" name="location_code[{{ $wh->id }}]" class="wh-loc" placeholder="A-01-01"></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>{{-- /col-lg-8 --}}

                        {{-- ── RIGHT COLUMN ── --}}
                        <div class="col-lg-4">

                            {{-- Image --}}
                            <div class="form-section">
                                <div class="form-section-title"><i class="bi bi-image"></i> Product Image</div>
                                <div class="upload-zone" id="uploadZone" onclick="document.getElementById('mImages').click()">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                    <p>Click to upload</p>
                                    <p style="font-size:11px;opacity:.6;">JPEG, PNG · max 2 MB</p>
                                </div>
                                <input type="file" id="mImages" name="images[]" accept="image/*" style="display:none;" onchange="window.handleImageSelect(event)">
                                <div id="mImgPreviewWrap" class="d-none" style="text-align:center;">
                                    <div class="img-preview-wrap">
                                        <img id="mImgPreview" src="" alt="Preview">
                                        <button type="button" class="img-remove" onclick="window.removeImage()"><i class="bi bi-x"></i></button>
                                    </div>
                                </div>
                            </div>

                            {{-- Settings --}}
                            <div class="form-section">
                                <div class="form-section-title"><i class="bi bi-sliders"></i> Settings</div>

                                <div class="toggle-row">
                                    <div class="toggle-label">
                                        Active
                                        <small>Visible in storefront</small>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="mIsActive" value="1" checked>
                                    </div>
                                </div>

                                <div class="toggle-row">
                                    <div class="toggle-label">
                                        Track Stock
                                        <small>Manage inventory levels</small>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="manage_stock" id="mManageStock" value="1" checked>
                                    </div>
                                </div>

                                <div class="toggle-row">
                                    <div class="toggle-label">
                                        Featured
                                        <small>Highlight this product</small>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_featured" id="mIsFeatured" value="1">
                                    </div>
                                </div>
                            </div>

                            {{-- Tips --}}
                            <div style="background:var(--bg-tertiary);border:1px solid var(--border-color);border-radius:10px;padding:14px;font-size:12px;color:var(--text-secondary);">
                                <strong style="display:flex;align-items:center;gap:5px;margin-bottom:8px;color:var(--primary);"><i class="bi bi-lightbulb"></i> Tips</strong>
                                <ul style="margin:0;padding-left:16px;line-height:1.9;">
                                    <li>SKU must be unique across all products.</li>
                                    <li>Compare price should exceed selling price.</li>
                                    <li>Stock can be updated per warehouse later.</li>
                                </ul>
                            </div>

                        </div>{{-- /col-lg-4 --}}
                    </div>{{-- /row --}}

                    {{-- Validation errors --}}
                    <div id="modalErrors" class="d-none" style="margin-top:16px;"></div>
                </div>{{-- /modal-body --}}

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="mSubmitBtn">
                        <i class="bi bi-check-lg"></i> Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite(['resources/js/features/products.js'])
@endpush
