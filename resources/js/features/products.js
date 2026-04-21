/**
 * resources/js/features/products.js
 */
document.addEventListener('DOMContentLoaded', () => {
    // Selectors
    const productsCard = document.querySelector('.products-card');
    const pageHeader = document.querySelector('.page-header');
    const emptyState = document.querySelector('.empty-state');
    const productModalEl = document.getElementById('productModal');
    const productForm = document.getElementById('productForm');
    
    // Delegation for product actions
    document.addEventListener('click', (e) => {
        // Add Product button
        if (e.target.closest('.btn-add-product')) {
            openCreateModal();
        }
        // Edit Product button or Image thumbnail
        else if (e.target.closest('.btn-edit-product') || e.target.closest('.product-thumbnail')) {
            const el = e.target.closest('.btn-edit-product') || e.target.closest('.product-thumbnail');
            const productId = el.dataset.id;
            if (productId) openEditModal(productId);
        }
    });

    // Handle delete confirmations using event delegation
    if (productsCard) {
        productsCard.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.matches('form[action*="products"]') && form.querySelector('input[name="_method"][value="DELETE"]')) {
                const productName = form.dataset.productName || 'this product';
                if (!confirm(`Are you sure you want to delete ${productName}?`)) {
                    e.preventDefault();
                }
            }
        });
    }

    // Modal helpers
    function generateSKU() {
        document.getElementById('mSku').value = 'SKU-' + Math.random().toString(36).substr(2,8).toUpperCase();
    }
    
    window.generateSKU = generateSKU; // Export to global for inline onclick if necessary (or rebind)

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
    
    window.handleImageSelect = handleImageSelect;
    
    function removeImage() {
        document.getElementById('mImages').value = '';
        document.getElementById('mImgPreviewWrap').classList.add('d-none');
        document.getElementById('uploadZone').style.display = '';
    }
    
    window.removeImage = removeImage;

    function resetForm() {
        if (!productForm) return;
        productForm.reset();

        document.getElementById('mIsActive').checked     = true;
        document.getElementById('mManageStock').checked  = true;
        document.getElementById('mIsFeatured').checked   = false;

        removeImage();

        const errBox = document.getElementById('modalErrors');
        if (errBox) {
            errBox.innerHTML = '';
            errBox.classList.add('d-none');
        }

        document.querySelectorAll('.wh-input').forEach(i => i.value = '0');
        document.querySelectorAll('.wh-loc').forEach(i   => i.value = '');
    }

    function openCreateModal() {
        if (!productModalEl) return;
        const existing = bootstrap.Modal.getInstance(productModalEl);
        if (existing) existing.dispose();

        cleanupModals();
        resetForm();

        productForm.action = '/products'; // Make sure this matches your route
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('productModalLabel').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add New Product';
        document.getElementById('mSubmitBtn').innerHTML = '<i class="bi bi-check-lg"></i> Save Product';

        new bootstrap.Modal(productModalEl, { backdrop: true, keyboard: true, focus: true }).show();
    }

    function openEditModal(productId) {
        if (!productModalEl) return;
        const existing = bootstrap.Modal.getInstance(productModalEl);
        if (existing) existing.dispose();

        cleanupModals();
        resetForm();

        document.getElementById('productModalLabel').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Product';
        document.getElementById('mSubmitBtn').innerHTML = '<i class="bi bi-check-lg"></i> Update Product';

        const modal = new bootstrap.Modal(productModalEl, { backdrop: true, keyboard: true, focus: true });
        modal.show();

        fetch(`/products/${productId}/edit`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            productForm.action = `/products/${productId}`;
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

            if (data.warehouse_stock) {
                Object.entries(data.warehouse_stock).forEach(([whId, qty]) => {
                    const inp = document.querySelector(`input[name="warehouse_stock[${whId}]"]`);
                    if (inp) inp.value = qty;
                });
            }
        })
        .catch(() => {
            const errBox = document.getElementById('modalErrors');
            if (errBox) {
                errBox.innerHTML = '<div class="alert alert-danger">Failed to load product data. Please try again.</div>';
                errBox.classList.remove('d-none');
            }
        });
    }

    if (productForm) {
        productForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const btn       = document.getElementById('mSubmitBtn');
            const origHTML  = btn.innerHTML;
            const errBox    = document.getElementById('modalErrors');

            btn.disabled    = true;
            btn.innerHTML   = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
            errBox.innerHTML = '';
            errBox.classList.add('d-none');

            fetch(productForm.action, {
                method: 'POST',
                body: new FormData(productForm),
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
    }

    function cleanupModals() {
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    }

    if (productModalEl) {
        productModalEl.addEventListener('hidden.bs.modal', function () {
            cleanupModals();
            resetForm();
        });
    }
});
