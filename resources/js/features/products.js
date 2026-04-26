/**
 * Optimized Product Management with Popup Forms
 * Features: Create, Edit, View, Delete with improved UX
 */
document.addEventListener('DOMContentLoaded', () => {
    // Cache frequently used DOM elements
    const elements = {
        productsCard: document.querySelector('.products-card'),
        productModal: document.getElementById('productModal'),
        productForm: document.getElementById('productForm'),
        viewModal: document.getElementById('viewProductModal'),
        deleteModal: document.getElementById('deleteConfirmModal')
    };
    
    // State management
    let currentProductId = null;
    let currentAction = null;
    let activeModals = [];
    
    // Event delegation for better performance
    document.addEventListener('click', handleDocumentClick);
    
    function handleDocumentClick(e) {
        const target = e.target.closest('[data-action]');
        if (!target) return;
        
        const action = target.dataset.action;
        const productId = target.dataset.id;
        
        switch (action) {
            case 'create':
                openCreateModal();
                break;
            case 'edit':
                if (productId) openEditModal(productId);
                break;
            case 'view':
                if (productId) openViewModal(productId);
                break;
            case 'delete':
                if (productId) openDeleteModal(productId, target.dataset.name);
                break;
        }
    }

    // Form submission handler
    if (elements.productForm) {
        elements.productForm.addEventListener('submit', handleFormSubmit);
    }
    
    async function handleFormSubmit(e) {
        e.preventDefault();
        const submitBtn = elements.productForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Disable button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        
        try {
            const formData = new FormData(elements.productForm);
            
            // Ensure _method is included for PUT requests
            const methodInput = document.getElementById('formMethod');
            if (methodInput && methodInput.value === 'PUT') {
                formData.append('_method', 'PUT');
            }
            
            const response = await fetch(elements.productForm.action, {
                method: 'POST', // Always POST for file uploads
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            
            if (response.redirected) {
                window.location.href = response.url;
                return;
            }
            
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const result = await response.json();
                
                if (result.success) {
                    closeModal(elements.productModal);
                    showToast(result.message || 'Operation successful', 'success');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    showToast(result.message || 'Operation failed', 'error');
                    if (result.errors) {
                        displayErrors(result.errors);
                    }
                    // Re-enable button on error
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } else {
                // If not JSON, reload
                window.location.reload();
            }
        } catch (error) {
            console.error('Form submission error:', error);
            showToast('An error occurred. Please try again.', 'error');
            // Re-enable button on error
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    function displayErrors(errors) {
        const errBox = document.getElementById('modalErrors');
        if (!errBox) return;
        
        let errorList = '<div class="alert alert-danger"><strong>Please fix the following errors:</strong><ul class="mb-0 mt-2">';
        
        if (typeof errors === 'object') {
            Object.values(errors).flat().forEach(error => {
                errorList += `<li>${error}</li>`;
            });
        } else {
            errorList += `<li>${errors}</li>`;
        }
        
        errorList += '</ul></div>';
        errBox.innerHTML = errorList;
        errBox.classList.remove('d-none');
        
        // Scroll to errors
        errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Utility functions
    function generateSKU() {
        const skuInput = document.getElementById('mSku');
        if (skuInput) {
            skuInput.value = 'SKU-' + Math.random().toString(36).substr(2, 8).toUpperCase();
        }
    }
    
    function handleImageSelect(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        if (file.size > 2097152) {
            showToast('Image size should not exceed 2MB', 'error');
            return;
        }
        
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            showToast('Please select a valid image file (JPEG, PNG, GIF, WebP)', 'error');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = ev => {
            const preview = document.getElementById('mImgPreview');
            const previewWrap = document.getElementById('mImgPreviewWrap');
            const uploadZone = document.getElementById('uploadZone');
            
            if (preview) preview.src = ev.target.result;
            if (previewWrap) previewWrap.classList.remove('d-none');
            if (uploadZone) uploadZone.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
    
    function removeImage() {
        const imagesInput = document.getElementById('mImages');
        const previewWrap = document.getElementById('mImgPreviewWrap');
        const uploadZone = document.getElementById('uploadZone');
        const preview = document.getElementById('mImgPreview');
        
        if (imagesInput) imagesInput.value = '';
        if (previewWrap) previewWrap.classList.add('d-none');
        if (uploadZone) uploadZone.style.display = '';
        if (preview) preview.src = '';
    }
    
    function resetForm() {
        if (!elements.productForm) return;
        elements.productForm.reset();
        
        const isActive = document.getElementById('mIsActive');
        const manageStock = document.getElementById('mManageStock');
        const isFeatured = document.getElementById('mIsFeatured');
        
        if (isActive) isActive.checked = true;
        if (manageStock) manageStock.checked = true;
        if (isFeatured) isFeatured.checked = false;
        
        removeImage();
        clearErrors();
        resetWarehouseInputs();
    }
    
    function clearErrors() {
        const errBox = document.getElementById('modalErrors');
        if (errBox) {
            errBox.innerHTML = '';
            errBox.classList.add('d-none');
        }
    }
    
    function resetWarehouseInputs() {
        document.querySelectorAll('.wh-input').forEach(input => input.value = '0');
        document.querySelectorAll('.wh-loc').forEach(input => input.value = '');
    }

    // Loading overlay functions
    function showLoadingOverlay(modalElement) {
        // Remove existing overlay if any
        hideLoadingOverlay(modalElement);
        
        const modalBody = modalElement.querySelector('.modal-body');
        const modalContent = modalElement.querySelector('.modal-content');
        
        if (modalBody && modalContent) {
            // Add position relative to modal content if not already
            if (!modalContent.style.position) {
                modalContent.style.position = 'relative';
            }
            
            const overlay = document.createElement('div');
            overlay.className = 'modal-loading-overlay';
            overlay.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mb-0">Loading product data...</p>
                </div>
            `;
            overlay.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.9);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10;
                border-radius: inherit;
            `;
            
            // Add dark mode support
            if (document.documentElement.classList.contains('dark')) {
                overlay.style.background = 'rgba(30, 41, 59, 0.9)';
            }
            
            modalBody.style.position = 'relative';
            modalBody.appendChild(overlay);
        }
    }
    
    function hideLoadingOverlay(modalElement) {
        const overlay = modalElement.querySelector('.modal-loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }

    // Modal management functions
    function disposeModal(modalElement) {
        if (!modalElement) return;
        
        // Remove focus from elements inside modal
        if (document.activeElement) {
            document.activeElement.blur();
        }
        
        // Hide loading overlay
        hideLoadingOverlay(modalElement);
        
        // Get existing Bootstrap modal instance
        const existingModal = bootstrap.Modal.getInstance(modalElement);
        if (existingModal) {
            existingModal.dispose();
        }
        
        // Reset modal element state
        modalElement.classList.remove('show');
        modalElement.style.display = 'none';
        modalElement.setAttribute('aria-hidden', 'true');
        modalElement.removeAttribute('aria-modal');
        modalElement.removeAttribute('role');
        
        // Remove modal backdrop
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        
        // Reset body classes
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }
    
    function disposeAllModals() {
        Object.values(elements).forEach(element => {
            if (element && element.classList.contains('modal')) {
                disposeModal(element);
            }
        });
        
        activeModals.forEach(modal => {
            if (modal && modal._element) {
                disposeModal(modal._element);
            }
        });
        activeModals = [];
    }
    
    function openCreateModal() {
        if (!elements.productModal) {
            console.error('Product modal not found!');
            return;
        }
        
        currentAction = 'create';
        currentProductId = null;
        
        // Dispose all existing modals first
        disposeAllModals();
        
        // Reset form
        resetForm();
        
        // Set form for create
        elements.productForm.action = '/products';
        const methodInput = document.getElementById('formMethod');
        if (methodInput) {
            methodInput.value = 'POST';
        }
        
        updateModalHeader('<i class="bi bi-plus-circle me-2"></i>Add New Product', '<i class="bi bi-check-lg"></i> Save Product');
        
        // Show modal immediately (no loading for create)
        const modal = new bootstrap.Modal(elements.productModal, { 
            backdrop: 'static',  // Changed to static to prevent closing by clicking outside
            keyboard: true, 
            focus: true 
        });
        
        activeModals.push(modal);
        modal.show();
    }
    
    async function openEditModal(productId) {
        if (!elements.productModal) {
            console.error('Product modal not found!');
            return;
        }
        
        currentAction = 'edit';
        currentProductId = productId;
        
        // Dispose all existing modals
        disposeAllModals();
        
        // Reset form first
        resetForm();
        
        updateModalHeader('<i class="bi bi-pencil-square me-2"></i>Edit Product', '<i class="bi bi-check-lg"></i> Update Product');
        
        // Show modal
        const modal = new bootstrap.Modal(elements.productModal, { 
            backdrop: 'static',  // Changed to static to prevent closing by clicking outside
            keyboard: true, 
            focus: true 
        });
        
        activeModals.push(modal);
        modal.show();
        
        // Show loading overlay (not replacing content)
        showLoadingOverlay(elements.productModal);
        
        // Load product data
        try {
            const response = await fetch(`/products/${productId}/edit`, {
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest', 
                    'Accept': 'application/json' 
                }
            });
            
            if (!response.ok) {
                throw new Error(`Server returned ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            // Hide loading overlay
            hideLoadingOverlay(elements.productModal);
            
            // Populate form
            populateEditForm(data);
            
        } catch (error) {
            console.error('Error loading product:', error);
            
            // Hide loading overlay
            hideLoadingOverlay(elements.productModal);
            
            // Close modal and show error
            closeModal(elements.productModal);
            showToast('Failed to load product data. Please try again.', 'error');
        }
    }
    
    function populateEditForm(data) {
        const product = data.product;
        
        if (!product) {
            showToast('Product data not found', 'error');
            closeModal(elements.productModal);
            return;
        }
        
        // Set form action and method
        elements.productForm.action = `/products/${product.id}`;
        const methodInput = document.getElementById('formMethod');
        if (methodInput) {
            methodInput.value = 'PUT';
        }
        
        // Populate basic fields
        setFieldValue('mName', product.name);
        setFieldValue('mSku', product.sku);
        setFieldValue('mPrice', product.price);
        setFieldValue('mComparePrice', product.compare_price);
        setFieldValue('mCostPrice', product.cost_price);
        setFieldValue('mCategory', product.category_id);
        setFieldValue('mBrand', product.brand_id);
        setFieldValue('mShortDesc', product.short_description);
        setFieldValue('mDesc', product.description);
        
        // Set checkboxes
        setCheckboxValue('mIsActive', product.is_active);
        setCheckboxValue('mManageStock', product.manage_stock);
        setCheckboxValue('mIsFeatured', product.is_featured);
        
        // Populate warehouse stock
        if (data.warehouse_stock) {
            Object.entries(data.warehouse_stock).forEach(([whId, stockData]) => {
                const qtyInput = document.querySelector(`input[name="warehouse_stock[${whId}]"]`);
                const locInput = document.querySelector(`input[name="location_code[${whId}]"]`);
                
                if (qtyInput) qtyInput.value = stockData.quantity || 0;
                if (locInput) locInput.value = stockData.location_code || '';
            });
        }
        
        // Show existing image if available
        if (data.primary_image_url) {
            const preview = document.getElementById('mImgPreview');
            const previewWrap = document.getElementById('mImgPreviewWrap');
            const uploadZone = document.getElementById('uploadZone');
            
            if (preview) {
                preview.src = data.primary_image_url;
                // Add cache-busting to prevent old cached images
                if (!data.primary_image_url.includes('?')) {
                    preview.src += '?t=' + Date.now();
                }
            }
            if (previewWrap) previewWrap.classList.remove('d-none');
            if (uploadZone) uploadZone.style.display = 'none';
        }
    }
    
    // Helper functions
    function setFieldValue(id, value) {
        const element = document.getElementById(id);
        if (element && value !== undefined && value !== null) {
            element.value = value;
        }
    }
    
    function setCheckboxValue(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.checked = !!value;
        }
    }
    
    function updateModalHeader(title, buttonText) {
        const titleElement = document.getElementById('productModalLabel');
        const buttonElement = document.getElementById('mSubmitBtn');
        
        if (titleElement) titleElement.innerHTML = title;
        if (buttonElement) {
            buttonElement.innerHTML = buttonText;
            buttonElement.disabled = false;  // Ensure button is enabled
        }
    }
    
    function closeModal(modalElement) {
        if (!modalElement) return;
        
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
        
        // Clean up after hiding animation
        setTimeout(() => {
            disposeModal(modalElement);
        }, 300);
    }
    
    function showToast(message, type = 'info') {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast element
        const toastId = 'toast-' + Date.now();
        const bgClass = type === 'error' ? 'bg-danger' : type === 'success' ? 'bg-success' : 'bg-primary';
        
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        // Show and auto-remove toast
        const toastElement = document.getElementById(toastId);
        if (toastElement && typeof bootstrap !== 'undefined') {
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 3000
            });
            toast.show();
            
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        }
    }
    
    // View Modal Functions
    async function openViewModal(productId) {
        if (!elements.viewModal) return;
        
        // Dispose existing modals
        disposeAllModals();
        
        // Show modal
        const modal = new bootstrap.Modal(elements.viewModal, {
            backdrop: true,
            keyboard: true
        });
        activeModals.push(modal);
        modal.show();
        
        // Show loading in view modal
        const content = elements.viewModal.querySelector('.modal-body');
        if (content) {
            content.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading product details...</p>
                </div>
            `;
        }
        
        try {
            const response = await fetch(`/products/${productId}/edit`, {
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest', 
                    'Accept': 'application/json' 
                }
            });
            
            if (!response.ok) {
                throw new Error(`Server returned ${response.status}`);
            }
            
            const data = await response.json();
            populateViewModal(data);
            currentProductId = productId;
            
        } catch (error) {
            console.error('Error loading product:', error);
            const content = elements.viewModal.querySelector('.modal-body');
            if (content) {
                content.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                        <p class="mt-3 text-danger">Failed to load product details.</p>
                    </div>
                `;
            }
        }
    }
    
    function populateViewModal(data) {
        const product = data.product;
        const content = elements.viewModal.querySelector('.modal-body');
        
        if (!content || !product) return;
        
        const html = `
            <div class="row g-4">
                <div class="col-md-5 text-center">
                    ${product.primary_image_url 
                        ? `<img src="${escapeHtml(product.primary_image_url)}" alt="${escapeHtml(product.name)}" class="img-fluid rounded shadow-sm" style="max-height: 300px; object-fit: cover; width: 100%;">`
                        : `<div class="bg-light rounded d-flex align-items-center justify-content-center shadow-sm" style="height: 300px;">
                               <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                           </div>`
                    }
                </div>
                <div class="col-md-7">
                    <h4 class="mb-3">${escapeHtml(product.name)}</h4>
                    
                    <div class="mb-3">
                        <strong>SKU:</strong> <span class="badge bg-secondary">${escapeHtml(product.sku)}</span>
                    </div>
                    
                    ${product.short_description ? `<p class="text-muted">${escapeHtml(product.short_description)}</p>` : ''}
                    
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <small class="text-muted">Price</small>
                            <h5 class="text-primary mb-0">$${parseFloat(product.price || 0).toFixed(2)}</h5>
                        </div>
                        ${product.compare_price ? `
                        <div class="col-6">
                            <small class="text-muted">Compare Price</small>
                            <h5 class="text-decoration-line-through text-muted mb-0">$${parseFloat(product.compare_price).toFixed(2)}</h5>
                        </div>
                        ` : ''}
                    </div>
                    
                    <div class="mb-3">
                        <strong>Category:</strong> ${product.category ? escapeHtml(product.category.name) : 'N/A'}<br>
                        <strong>Brand:</strong> ${product.brand ? escapeHtml(product.brand.name) : 'N/A'}<br>
                        <strong>Status:</strong> 
                        <span class="badge ${product.is_active ? 'bg-success' : 'bg-danger'}">
                            ${product.is_active ? 'Active' : 'Inactive'}
                        </span>
                        ${product.is_featured ? '<span class="badge bg-warning ms-1">Featured</span>' : ''}
                    </div>
                    
                    ${data.warehouse_stock ? `
                    <div class="mb-3">
                        <strong>Warehouse Stock:</strong>
                        <table class="table table-sm table-bordered mt-2">
                            <thead class="table-light">
                                <tr>
                                    <th>Warehouse</th>
                                    <th>Quantity</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${Object.entries(data.warehouse_stock).map(([whId, stock]) => `
                                    <tr>
                                        <td>${escapeHtml(stock.warehouse_name || `Warehouse #${whId}`)}</td>
                                        <td><strong>${stock.quantity || 0}</strong></td>
                                        <td>${escapeHtml(stock.location_code || 'N/A')}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    ` : ''}
                    
                    ${product.description ? `
                    <div class="mb-3">
                        <strong>Description:</strong>
                        <p class="mt-1">${escapeHtml(product.description)}</p>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
        
        content.innerHTML = html;
        
        // Add event listener for edit button in view modal
        const editBtn = elements.viewModal.querySelector('.btn-edit-from-view');
        if (editBtn) {
            editBtn.onclick = () => {
                closeModal(elements.viewModal);
                if (currentProductId) {
                    setTimeout(() => openEditModal(currentProductId), 300);
                }
            };
        }
    }
    
    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Delete Modal Functions
    function openDeleteModal(productId, productName) {
        if (!elements.deleteModal) return;
        
        currentProductId = productId;
        
        const nameElement = document.getElementById('deleteProductName');
        if (nameElement) nameElement.textContent = productName || 'this product';
        
        // Reset delete button state
        const deleteBtn = elements.deleteModal.querySelector('#deleteProductForm button[type="submit"]');
        if (deleteBtn) {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = '<i class="bi bi-trash"></i> Delete';
        }
        
        // Dispose existing modals
        disposeAllModals();
        
        const modal = new bootstrap.Modal(elements.deleteModal, {
            backdrop: true,
            keyboard: true
        });
        activeModals.push(modal);
        modal.show();
    }
    
    // Handle delete form submission
    const deleteForm = document.getElementById('deleteProductForm');
    if (deleteForm) {
        deleteForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!currentProductId) return;
            
            const submitBtn = deleteForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
            
            try {
                const response = await fetch(`/products/${currentProductId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }
                
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const result = await response.json();
                    
                    if (result.success) {
                        closeModal(elements.deleteModal);
                        showToast(result.message || 'Product deleted successfully', 'success');
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        showToast(result.message || 'Failed to delete product', 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                } else {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Delete error:', error);
                showToast('An error occurred while deleting the product', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
    
    // Handle modal hidden events for proper cleanup
    document.addEventListener('hidden.bs.modal', function(event) {
        const modalElement = event.target;
        hideLoadingOverlay(modalElement);
        activeModals = activeModals.filter(m => m._element !== modalElement);
        disposeModal(modalElement);
    });
    
    // Clean up on page unload
    window.addEventListener('beforeunload', function() {
        disposeAllModals();
    });
    
    // Export functions to global scope for inline handlers
    window.generateSKU = generateSKU;
    window.handleImageSelect = handleImageSelect;
    window.removeImage = removeImage;
});