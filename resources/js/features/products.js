/**
 * Clean Product Management Script
 */
document.addEventListener('DOMContentLoaded', () => {

    const elements = {
        productModal: document.getElementById('productModal'),
        productForm: document.getElementById('productForm'),
        viewModal: document.getElementById('viewProductModal'),
        deleteModal: document.getElementById('deleteConfirmModal')
    };

    let currentProductId = null;

    document.addEventListener('click', handleClick);

    function handleClick(e) {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;

        const action = btn.dataset.action;
        const id = btn.dataset.id;
        const name = btn.dataset.name;

        if (action === 'create') openCreateModal();
        if (action === 'edit') openEditModal(id);
        if (action === 'view') openViewModal(id);
        if (action === 'delete') openDeleteModal(id, name);
    }

    /* ================= CREATE ================= */
    function openCreateModal() {
        resetForm();
        elements.productForm.action = '/products';
        setMethod('POST');

        new bootstrap.Modal(elements.productModal).show();
    }

    /* ================= EDIT ================= */
    async function openEditModal(id) {
        currentProductId = id;
        resetForm();

        new bootstrap.Modal(elements.productModal).show();

        try {
            const res = await fetch(`/products/${id}/edit`, {
                headers: { 'Accept': 'application/json' }
            });

            const data = await res.json();
            fillForm(data.product, data);
            elements.productForm.action = `/products/${id}`;
            setMethod('PUT');

        } catch {
            showToast('Failed to load product', 'error');
        }
    }

    function fillForm(p, data) {
        setVal('mName', p.name);
        setVal('mSku', p.sku);
        setVal('mPrice', p.price);
        setVal('mDesc', p.description);
        setVal('mShortDesc', p.short_description);
        setVal('mComparePrice', p.compare_price);
        setVal('mCostPrice', p.cost_price);
        setVal('mCategory', p.category_id);
        setVal('mBrand', p.brand_id);

        setCheck('mIsActive', p.is_active);
        setCheck('mManageStock', p.manage_stock);
        setCheck('mIsFeatured', p.is_featured);

        // stock
        if (data.warehouse_stock) {
            Object.entries(data.warehouse_stock).forEach(([id, s]) => {
                const el = document.querySelector(`[name="warehouse_stock[${id}]"]`);
                if (el) el.value = s.quantity;
                const locEl = document.querySelector(`[name="location_code[${id}]"]`);
                if (locEl && s.location_code) locEl.value = s.location_code;
            });
        }

        // image preview
        if (data.primary_image_url) {
            const img = document.getElementById('mImgPreview');
            const wrap = document.getElementById('mImgPreviewWrap');
            if (img) img.src = data.primary_image_url;
            if (wrap) wrap.classList.remove('d-none');
        }
    }

    /* ================= SUBMIT ================= */
    if (elements.productForm) {
        elements.productForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(elements.productForm);

            if (getMethod() === 'PUT') {
                formData.append('_method', 'PUT');
            }

            try {
                const res = await fetch(elements.productForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await res.json();

                if (result.success) {
                    showToast('Saved!', 'success');
                    location.reload();
                } else {
                    showToast(result.message || 'Error', 'error');
                }

            } catch {
                showToast('Server error', 'error');
            }
        });
    }

    /* ================= DELETE ================= */
    function openDeleteModal(id, name) {
        currentProductId = id;

        document.getElementById('deleteProductName').textContent = name;

        new bootstrap.Modal(elements.deleteModal).show();
    }

    const deleteForm = document.getElementById('deleteProductForm');
    if (deleteForm) {
        deleteForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            try {
                const res = await fetch(`/products/${currentProductId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await res.json();

                if (result.success) {
                    showToast('Deleted!', 'success');
                    location.reload();
                }

            } catch {
                showToast('Delete failed', 'error');
            }
        });
    }

    /* ================= VIEW ================= */
    async function openViewModal(id) {
        const modal = new bootstrap.Modal(elements.viewModal);
        modal.show();

        const body = elements.viewModal.querySelector('.modal-body');
        body.innerHTML = 'Loading...';

        try {
            const res = await fetch(`/products/${id}/edit`);
            const data = await res.json();

            body.innerHTML = `
                <h4>${data.product.name}</h4>
                <p>${data.product.description || ''}</p>
                <strong>$${data.product.price}</strong>
            `;

        } catch {
            body.innerHTML = 'Error loading';
        }
    }

    /* ================= HELPERS ================= */
    function setVal(id, val) {
        const el = document.getElementById(id);
        if (el) el.value = val || '';
    }

    function setCheck(id, val) {
        const el = document.getElementById(id);
        if (el) el.checked = !!val;
    }

    function resetForm() {
        elements.productForm.reset();
        clearImage();
    }

    function setMethod(val) {
        document.getElementById('formMethod').value = val;
    }

    function getMethod() {
        return document.getElementById('formMethod').value;
    }

    function clearImage() {
        const img = document.getElementById('mImgPreview');
        const wrap = document.getElementById('mImgPreviewWrap');
        const input = document.getElementById('mImages');
        if (img) img.src = '';
        if (wrap) wrap.classList.add('d-none');
        if (input) input.value = '';
    }

    function showToast(msg, type = 'info') {
        alert(msg); // simple version
    }

    // ========== SKU Generation ==========
    window.generateSKU = function() {
        const skuInput = document.getElementById('mSku');
        if (!skuInput) return;
        
        const random = Math.random().toString(36).slice(2, 8).toUpperCase();
        skuInput.value = `PROD-${random}`;
    };

    // ========== Image Handling ==========
    window.handleImageSelect = function(event) {
        const file = event.target.files?.[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = ({ target }) => {
            const img = document.getElementById('mImgPreview');
            const wrap = document.getElementById('mImgPreviewWrap');
            if (img) img.src = target?.result || '';
            if (wrap) wrap.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    };

    window.removeImage = function() {
        clearImage();
    };

});