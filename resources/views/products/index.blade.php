@extends('layouts.app')

@section('title','Products')

@section('content')

<style>
    /* ── Page Header ── */
    .page-header {
        background: #03624C;
        color: white;
        padding: 28px 32px;
        border-radius: 14px;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(3,98,76,0.18);
    }
    .page-header h1 { margin: 0 0 4px; font-size: 24px; font-weight: 800; letter-spacing: -.3px; }
    .page-header p  { margin: 0; opacity: .75; font-size: 13px; }

    .btn-add-product {
        background: white;
        color: #03624C;
        padding: 9px 18px;
        font-size: 13px;
        font-weight: 700;
        border-radius: 8px;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: box-shadow .2s, transform .2s;
        white-space: nowrap;
    }
    .btn-add-product:hover { box-shadow: 0 4px 14px rgba(0,0,0,.15); transform: translateY(-1px); }

    /* ── Table Card ── */
    .products-card {
        background: white;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        border: 1px solid #eaf7f4;
        overflow: hidden;
    }

    .products-table { width: 100%; border-collapse: collapse; }
    .products-table thead { background: #03624C; color: white; }
    .products-table th {
        padding: 13px 16px;
        text-align: left;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .6px;
        border: none;
    }
    .products-table td {
        padding: 13px 16px;
        border-bottom: 1px solid #f0faf7;
        vertical-align: middle;
        font-size: 13px;
    }
    .products-table tbody tr:last-child td { border-bottom: none; }
    .products-table tbody tr:hover { background: #fafffe; }

    .product-thumbnail {
        width: 68px; height: 46px;
        object-fit: cover;
        border-radius: 7px;
        border: 2px solid #e2f5f0;
        cursor: pointer;
        transition: border-color .2s, transform .2s;
    }
    .product-thumbnail:hover { border-color: #03624C; transform: scale(1.08); }

    .product-name  { font-weight: 600; color: #1a2e28; margin: 0; font-size: 13px; }
    .product-desc  { color: #7a9c94; font-size: 11px; margin: 2px 0 0; }

    .sku-tag {
        font-family: 'Courier New', monospace;
        background: #e9fff9;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        color: #03624C;
        font-weight: 700;
    }

    .cat-badge {
        background: #f0faf7;
        color: #03624C;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid #c8efe5;
    }

    .stock-num { font-weight: 700; font-size: 14px; }
    .stock-num.hi   { color: #22c55e; }
    .stock-num.mid  { color: #f59e0b; }
    .stock-num.lo   { color: #ef4444; }

    .price-val { font-weight: 700; color: #03624C; font-size: 14px; }

    .action-buttons { display: flex; gap: 5px; justify-content: flex-end; }
    .btn-ico {
        width: 30px; height: 30px;
        border-radius: 6px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 13px;
        color: white;
        text-decoration: none;
        transition: opacity .15s, transform .15s;
    }
    .btn-ico:hover { opacity: .85; transform: translateY(-1px); color: white; }
    .ico-view   { background: #03624C; }
    .ico-edit   { background: #0891b2; }
    .ico-delete { background: #ef4444; }

    .empty-state { text-align: center; padding: 70px 20px; color: #94a3b8; }
    .empty-state i { font-size: 56px; display: block; margin-bottom: 12px; opacity: .4; }

    /* ── Modal ── */
    .modal-backdrop { z-index: 1040 !important; }
    .modal          { z-index: 1050 !important; }

    .modal-content  { border-radius: 14px; border: none; box-shadow: 0 24px 60px rgba(0,0,0,.22); }

    .modal-header {
        background: #03624C;
        color: white;
        border-radius: 14px 14px 0 0;
        border: none;
        padding: 18px 24px;
    }
    .modal-header .btn-close { filter: invert(1) opacity(.8); }
    .modal-title { font-weight: 700; font-size: 16px; }

    .modal-body   { padding: 24px; max-height: 74vh; overflow-y: auto; }
    .modal-footer { padding: 14px 24px; border-top: 1px solid #edf7f4; gap: 8px; }

    /* form sections inside modal */
    .form-section {
        border: 1px solid #e8f5f0;
        border-radius: 10px;
        padding: 18px;
        margin-bottom: 18px;
    }
    .form-section-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #03624C;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .flabel {
        font-weight: 600;
        color: #374151;
        font-size: 12px;
        margin-bottom: 5px;
        display: block;
    }
    .flabel .req { color: #ef4444; }

    .finput, .fselect, .ftextarea {
        width: 100%;
        border: 1.5px solid #e2e8f0;
        border-radius: 7px;
        padding: 9px 12px;
        font-size: 13px;
        transition: border-color .2s, box-shadow .2s;
        background: white;
        color: #1e293b;
    }
    .finput:focus, .fselect:focus, .ftextarea:focus {
        border-color: #03624C;
        box-shadow: 0 0 0 3px rgba(3,98,76,.08);
        outline: none;
    }
    .ftextarea { resize: vertical; }

    .input-prefix {
        display: flex;
        align-items: center;
        border: 1.5px solid #e2e8f0;
        border-radius: 7px;
        overflow: hidden;
        transition: border-color .2s, box-shadow .2s;
    }
    .input-prefix:focus-within {
        border-color: #03624C;
        box-shadow: 0 0 0 3px rgba(3,98,76,.08);
    }
    .input-prefix span {
        background: #f8fafc;
        padding: 9px 11px;
        font-size: 13px;
        color: #64748b;
        border-right: 1.5px solid #e2e8f0;
        flex-shrink: 0;
    }
    .input-prefix input {
        border: none;
        padding: 9px 12px;
        font-size: 13px;
        flex: 1;
        outline: none;
        color: #1e293b;
    }

    .sku-row { display: flex; gap: 6px; }
    .sku-row .finput { flex: 1; }
    .btn-gen {
        padding: 9px 14px;
        background: #f1f5f9;
        border: 1.5px solid #e2e8f0;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        white-space: nowrap;
        transition: background .15s;
    }
    .btn-gen:hover { background: #e2e8f0; }

    /* warehouse stock mini-table */
    .wh-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .wh-table th {
        padding: 7px 10px;
        background: #f8fafc;
        color: #64748b;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .4px;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
    }
    .wh-table td { padding: 8px 10px; border-bottom: 1px solid #f0f4f8; vertical-align: middle; }
    .wh-table tbody tr:last-child td { border-bottom: none; }
    .wh-input {
        width: 90px;
        border: 1.5px solid #e2e8f0;
        border-radius: 6px;
        padding: 6px 8px;
        font-size: 12px;
        text-align: center;
        transition: border-color .2s;
    }
    .wh-input:focus { border-color: #03624C; outline: none; }
    .wh-loc {
        width: 100%;
        border: 1.5px solid #e2e8f0;
        border-radius: 6px;
        padding: 6px 8px;
        font-size: 12px;
        transition: border-color .2s;
    }
    .wh-loc:focus { border-color: #03624C; outline: none; }

    /* toggle switches */
    .toggle-row { display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f4f8; }
    .toggle-row:last-child { border-bottom: none; padding-bottom: 0; }
    .toggle-label { font-size: 13px; color: #374151; font-weight: 500; }
    .toggle-label small { display: block; color: #94a3b8; font-size: 11px; font-weight: 400; }

    /* image upload */
    .upload-zone {
        border: 2px dashed #c8e6de;
        border-radius: 10px;
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        background: #f8fffe;
    }
    .upload-zone:hover { border-color: #03624C; background: #f0faf7; }
    .upload-zone i { font-size: 28px; color: #9ecfc4; display: block; margin-bottom: 6px; }
    .upload-zone p { margin: 0; font-size: 12px; color: #7a9c94; }

    .img-preview-wrap {
        position: relative;
        display: inline-block;
        margin-top: 12px;
    }
    .img-preview-wrap img {
        width: 110px;
        height: 75px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #c8e6de;
        display: block;
    }
    .img-remove {
        position: absolute;
        top: -6px; right: -6px;
        width: 20px; height: 20px;
        border-radius: 50%;
        background: #ef4444;
        color: white;
        border: none;
        font-size: 11px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
    }

    /* save / cancel buttons */
    .btn-save {
        background: #03624C;
        color: white;
        padding: 10px 22px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background .2s, transform .2s;
    }
    .btn-save:hover { background: #024a3a; transform: translateY(-1px); }
    .btn-cancel {
        background: white;
        color: #64748b;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        border: 1.5px solid #e2e8f0;
        cursor: pointer;
        transition: background .15s;
    }
    .btn-cancel:hover { background: #f8fafc; }
</style>

{{-- Page Header --}}
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1>Products</h1>
        <p>Manage and organise your product inventory</p>
    </div>
    <button type="button" class="btn-add-product" onclick="openCreateModal()">
        <i class="bi bi-plus-lg"></i> Add Product
    </button>
</div>

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
                                         onclick="openEditModal({{ $product->id }})"
                                         onerror="this.src='https://placehold.co/68x46/e9fff9/03624C?text=?'">
                                @else
                                    <div style="width:68px;height:46px;background:#e9fff9;border-radius:7px;display:flex;align-items:center;justify-content:center;color:#9ecfc4;">
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
                                    <span style="color:#ccc;">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="stock-num {{ $sc }}">{{ $stock }}</span>
                                <span style="color:#a0b8b2;font-size:11px;"> units</span>
                            </td>
                            <td><span class="price-val">${{ number_format($product->price,2) }}</span></td>
                            <td style="text-align:right;">
                                <div class="action-buttons">
                                    <a href="{{ route('products.show', $product) }}" class="btn-ico ico-view" title="View"><i class="bi bi-eye"></i></a>
                                    <button type="button" class="btn-ico ico-edit" onclick="openEditModal({{ $product->id }})" title="Edit"><i class="bi bi-pencil"></i></button>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete {{ addslashes($product->name) }}?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-ico ico-delete" title="Delete"><i class="bi bi-trash"></i></button>
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
            <h5 style="color:#475569;">No Products Yet</h5>
            <p style="font-size:13px;">Create your first product to get started.</p>
            <button type="button" class="btn-add-product" style="margin-top:10px;" onclick="openCreateModal()">
                <i class="bi bi-plus-lg"></i> Add Product
            </button>
        </div>
    @endif
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
                                            <button type="button" class="btn-gen" onclick="generateSKU()">Generate</button>
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
                                                <td style="font-weight:600;color:#1e293b;">{{ $wh->name }}</td>
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
                                <input type="file" id="mImages" name="images[]" accept="image/*" style="display:none;" onchange="handleImageSelect(event)">
                                <div id="mImgPreviewWrap" class="d-none" style="text-align:center;">
                                    <div class="img-preview-wrap">
                                        <img id="mImgPreview" src="" alt="Preview">
                                        <button type="button" class="img-remove" onclick="removeImage()"><i class="bi bi-x"></i></button>
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
                            <div style="background:#f8fffe;border:1px solid #d4ede7;border-radius:10px;padding:14px;font-size:12px;color:#4a7c6f;">
                                <strong style="display:flex;align-items:center;gap:5px;margin-bottom:8px;color:#03624C;"><i class="bi bi-lightbulb"></i> Tips</strong>
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
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-save" id="mSubmitBtn">
                        <i class="bi bi-check-lg"></i> Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ──────────────────────────────────────────
   Helpers
────────────────────────────────────────── */
function generateSKU() {
    document.getElementById('mSku').value = 'SKU-' + Math.random().toString(36).substr(2,8).toUpperCase();
}

function handleImageSelect(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        document.getElementById('mImgPreview').src = ev.target.result;
        document.getElementById('mImgPreviewWrap').classList.remove('d-none');
        document.getElementById('uploadZone').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function removeImage() {
    document.getElementById('mImages').value = '';
    document.getElementById('mImgPreviewWrap').classList.add('d-none');
    document.getElementById('uploadZone').style.display = '';
}

/* ──────────────────────────────────────────
   Reset form to blank Create state
────────────────────────────────────────── */
function resetForm() {
    const form = document.getElementById('productForm');
    form.reset();

    // toggles default
    document.getElementById('mIsActive').checked     = true;
    document.getElementById('mManageStock').checked  = true;
    document.getElementById('mIsFeatured').checked   = false;

    // image preview
    removeImage();

    // clear errors
    const errBox = document.getElementById('modalErrors');
    errBox.innerHTML = '';
    errBox.classList.add('d-none');

    // warehouse qty back to 0
    document.querySelectorAll('.wh-input').forEach(i => i.value = '0');
    document.querySelectorAll('.wh-loc').forEach(i   => i.value = '');
}

/* ──────────────────────────────────────────
   Open CREATE
────────────────────────────────────────── */
function openCreateModal() {
    const modalEl = document.getElementById('productModal');

    // destroy stale instance
    const existing = bootstrap.Modal.getInstance(modalEl);
    if (existing) existing.dispose();

    // clean up any leftover backdrops
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');

    resetForm();

    const form = document.getElementById('productForm');
    form.action = '{{ route("products.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('productModalLabel').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add New Product';
    document.getElementById('mSubmitBtn').innerHTML = '<i class="bi bi-check-lg"></i> Save Product';

    new bootstrap.Modal(modalEl, { backdrop: true, keyboard: true, focus: true }).show();
}

/* ──────────────────────────────────────────
   Open EDIT
────────────────────────────────────────── */
function openEditModal(productId) {
    const modalEl = document.getElementById('productModal');

    const existing = bootstrap.Modal.getInstance(modalEl);
    if (existing) existing.dispose();

    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');

    resetForm();

    document.getElementById('productModalLabel').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Product';
    document.getElementById('mSubmitBtn').innerHTML = '<i class="bi bi-check-lg"></i> Update Product';

    const modal = new bootstrap.Modal(modalEl, { backdrop: true, keyboard: true, focus: true });
    modal.show();

    fetch(`/products/${productId}/edit`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const form = document.getElementById('productForm');
        form.action = `/products/${productId}`;
        document.getElementById('formMethod').value = 'PUT';

        document.getElementById('mName').value          = data.name            ?? '';
        document.getElementById('mSku').value           = data.sku             ?? '';
        document.getElementById('mPrice').value         = data.price           ?? '';
        document.getElementById('mComparePrice').value  = data.compare_price   ?? '';
        document.getElementById('mCostPrice').value     = data.cost_price      ?? '';
        document.getElementById('mCategory').value      = data.category_id     ?? '';
        document.getElementById('mBrand').value         = data.brand_id        ?? '';
        document.getElementById('mShortDesc').value     = data.short_description ?? '';
        document.getElementById('mDesc').value          = data.description     ?? '';
        document.getElementById('mIsActive').checked    = !!data.is_active;
        document.getElementById('mManageStock').checked = !!data.manage_stock;
        document.getElementById('mIsFeatured').checked  = !!data.is_featured;

        // warehouse stock
        if (data.warehouse_stock) {
            Object.entries(data.warehouse_stock).forEach(([whId, qty]) => {
                const inp = document.querySelector(`input[name="warehouse_stock[${whId}]"]`);
                if (inp) inp.value = qty;
            });
        }
    })
    .catch(() => {
        document.getElementById('modalErrors').innerHTML =
            '<div class="alert alert-danger">Failed to load product data. Please try again.</div>';
        document.getElementById('modalErrors').classList.remove('d-none');
    });
}

/* ──────────────────────────────────────────
   Form submit (AJAX)
────────────────────────────────────────── */
document.getElementById('productForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form      = this;
    const btn       = document.getElementById('mSubmitBtn');
    const origHTML  = btn.innerHTML;
    const errBox    = document.getElementById('modalErrors');

    btn.disabled    = true;
    btn.innerHTML   = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
    errBox.innerHTML = '';
    errBox.classList.add('d-none');

    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => {
        if (r.redirected) { window.location.href = r.url; return; }
        return r.json();
    })
    .then(data => {
        if (!data) return;
        if (data.success || data.id) {
            window.location.reload();
            return;
        }
        if (data.errors) {
            const list = Object.values(data.errors).flat().map(e => `<li>${e}</li>`).join('');
            errBox.innerHTML = `<div class="alert alert-danger"><strong>Please fix:</strong><ul class="mb-0 mt-1">${list}</ul></div>`;
            errBox.classList.remove('d-none');
        } else if (data.message) {
            errBox.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            errBox.classList.remove('d-none');
        }
        btn.disabled  = false;
        btn.innerHTML = origHTML;
    })
    .catch(() => {
        errBox.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
        errBox.classList.remove('d-none');
        btn.disabled  = false;
        btn.innerHTML = origHTML;
    });
});

/* ──────────────────────────────────────────
   Clean up on close
────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('productModal').addEventListener('hidden.bs.modal', function () {
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
        resetForm();
    });
});
</script>
@endpush