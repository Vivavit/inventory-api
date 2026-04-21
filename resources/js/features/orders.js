/**
 * Orders Page JavaScript
 * Clean, modular, and maintainable
 */

class OrdersManager {
  constructor() {
    this.productIndex = 1;
    this.editProductIndex = 0;
    this.currentViewOrderId = null;
    this.selectedOrderIds = new Set();

    this.initializeModals();
    this.attachEventListeners();
  }

  /**
   * Initialize Bootstrap modals
   */
  initializeModals() {
    this.orderModal = new bootstrap.Modal(
      document.getElementById('orderModal')
    );
    this.viewOrderModal = new bootstrap.Modal(
      document.getElementById('viewOrderModal')
    );
    this.editOrderModal = new bootstrap.Modal(
      document.getElementById('editOrderModal')
    );
  }

  /**
   * Attach all event listeners
   */
  attachEventListeners() {
    // Create order buttons
    document.getElementById('createOrderBtn')?.addEventListener('click', () =>
      this.openCreateOrderModal()
    );
    document.getElementById('emptyStateCreateBtn')?.addEventListener('click', () =>
      this.openCreateOrderModal()
    );

    // Form submissions
    document.getElementById('orderForm')?.addEventListener('submit', (e) =>
      this.handleOrderFormSubmit(e)
    );
    document.getElementById('editOrderForm')?.addEventListener('submit', (e) =>
      this.handleEditOrderFormSubmit(e)
    );

    // Add product button
    document.getElementById('addProductBtn')?.addEventListener('click', () =>
      this.addProductRow()
    );

    // Filters
    document.getElementById('applyFiltersBtn')?.addEventListener('click', () =>
      this.applyFilters()
    );
    document.getElementById('clearFiltersBtn')?.addEventListener('click', () =>
      this.clearFilters()
    );

    // Bulk actions
    document.getElementById('selectAllBtn')?.addEventListener('click', () =>
      this.toggleSelectAll()
    );
    document.getElementById('selectAllCheckbox')?.addEventListener('change', (e) =>
      this.handleSelectAllChange(e)
    );
    document.getElementById('exportExcelBtn')?.addEventListener('click', () =>
      this.exportExcel()
    );
    document.getElementById('printInvoicesBtn')?.addEventListener('click', () =>
      this.printInvoices()
    );

    // Order checkboxes
    document.querySelectorAll('.order-checkbox').forEach((checkbox) => {
      checkbox.addEventListener('change', () => this.updateSelectedCount());
    });

    // Row action buttons
    document.querySelectorAll('.view-order-btn').forEach((btn) => {
      btn.addEventListener('click', (e) =>
        this.viewOrder(e.target.closest('button').dataset.orderId)
      );
    });
    document.querySelectorAll('.edit-order-btn').forEach((btn) => {
      btn.addEventListener('click', (e) =>
        this.editOrder(e.target.closest('button').dataset.orderId)
      );
    });
    document.querySelectorAll('.print-order-btn').forEach((btn) => {
      btn.addEventListener('click', (e) =>
        this.printOrder(e.target.closest('button').dataset.orderId)
      );
    });

    // Print current invoice
    document.getElementById('printCurrentInvoiceBtn')?.addEventListener('click', () => {
      if (this.currentViewOrderId) {
        this.printOrder(this.currentViewOrderId);
      }
    });

    // Initial product row listeners
    this.attachProductEventListeners();
  }

  /**
   * Attach listeners to product rows
   */
  attachProductEventListeners() {
    document.querySelectorAll('#order-items-container .product-select').forEach((select) => {
      select.removeEventListener('change', this.handleProductChange.bind(this));
      select.addEventListener('change', this.handleProductChange.bind(this));
    });

    document.querySelectorAll('#order-items-container .quantity-input').forEach((input) => {
      input.removeEventListener('input', this.handleQuantityChange.bind(this));
      input.addEventListener('input', this.handleQuantityChange.bind(this));
    });

    document.querySelectorAll('#order-items-container .remove-product-btn').forEach((btn) => {
      btn.removeEventListener('click', this.removeProductRow.bind(this));
      btn.addEventListener('click', this.removeProductRow.bind(this));
    });
  }

  /**
   * Attach listeners to edit product rows
   */
  attachEditProductEventListeners() {
    document.querySelectorAll('#editOrderItemsContainer .edit-product-select').forEach((select) => {
      select.removeEventListener('change', this.handleEditProductChange.bind(this));
      select.addEventListener('change', this.handleEditProductChange.bind(this));
    });

    document.querySelectorAll('#editOrderItemsContainer .edit-quantity-input').forEach((input) => {
      input.removeEventListener('input', this.handleEditQuantityChange.bind(this));
      input.addEventListener('input', this.handleEditQuantityChange.bind(this));
    });

    document.querySelectorAll('#editOrderItemsContainer .remove-edit-product-btn').forEach((btn) => {
      btn.removeEventListener('click', this.removeEditProductRow.bind(this));
      btn.addEventListener('click', this.removeEditProductRow.bind(this));
    });
  }

  /**
   * Handle product selection change
   */
  handleProductChange(e) {
    const select = e.target;
    const row = select.closest('.product-row');
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price') || 0;
    const unitPriceField = row.querySelector('.unit-price');
    const quantityField = row.querySelector('.quantity-input');
    const subtotalField = row.querySelector('.subtotal');

    unitPriceField.value = `$${parseFloat(price).toFixed(2)}`;
    const quantity = parseInt(quantityField.value) || 0;
    const subtotal = quantity * parseFloat(price);
    subtotalField.value = `$${subtotal.toFixed(2)}`;
    this.updateTotal();
  }

  /**
   * Handle quantity change
   */
  handleQuantityChange(e) {
    const input = e.target;
    const row = input.closest('.product-row');
    const select = row.querySelector('.product-select');
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price') || 0;
    const quantity = parseInt(input.value) || 0;
    const subtotalField = row.querySelector('.subtotal');
    const subtotal = quantity * parseFloat(price);
    subtotalField.value = `$${subtotal.toFixed(2)}`;
    this.updateTotal();
  }

  /**
   * Handle edit product selection change
   */
  handleEditProductChange(e) {
    const select = e.target;
    const row = select.closest('.product-row');
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price') || 0;
    const unitPriceField = row.querySelector('.edit-unit-price');
    const quantityField = row.querySelector('.edit-quantity-input');
    const subtotalField = row.querySelector('.edit-subtotal');

    unitPriceField.value = `$${parseFloat(price).toFixed(2)}`;
    const quantity = parseInt(quantityField.value) || 0;
    const subtotal = quantity * parseFloat(price);
    subtotalField.value = `$${subtotal.toFixed(2)}`;
    this.updateEditTotal();
  }

  /**
   * Handle edit quantity change
   */
  handleEditQuantityChange(e) {
    const input = e.target;
    const row = input.closest('.product-row');
    const select = row.querySelector('.edit-product-select');
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price') || 0;
    const quantity = parseInt(input.value) || 0;
    const subtotalField = row.querySelector('.edit-subtotal');
    const subtotal = quantity * parseFloat(price);
    subtotalField.value = `$${subtotal.toFixed(2)}`;
    this.updateEditTotal();
  }

  /**
   * Remove product row
   */
  removeProductRow(e) {
    const button = e.target.closest('button');
    const row = button.closest('.product-row');
    const container = document.getElementById('order-items-container');

    if (container.querySelectorAll('.product-row').length > 1) {
      row.remove();
      this.updateTotal();
    } else {
      this.showNotification('At least one product is required.', 'error');
    }
  }

  /**
   * Remove edit product row
   */
  removeEditProductRow(e) {
    const button = e.target.closest('button');
    const row = button.closest('.product-row');
    const container = document.getElementById('editOrderItemsContainer');

    if (container.querySelectorAll('.product-row').length > 1) {
      row.remove();
      this.updateEditTotal();
    } else {
      this.showNotification('At least one product is required.', 'error');
    }
  }

  /**
   * Add new product row
   */
  addProductRow() {
    const container = document.getElementById('order-items-container');
    const newRow = this.createProductRow(this.productIndex, false);
    container.appendChild(newRow);
    this.productIndex++;
    this.attachProductEventListeners();
  }

  /**
   * Add new edit product row
   */
  addEditProductRow() {
    const container = document.getElementById('editOrderItemsContainer');
    const newRow = this.createProductRow(this.editProductIndex, true);
    container.appendChild(newRow);
    this.editProductIndex++;
    this.attachEditProductEventListeners();
  }

  /**
   * Create product row HTML element
   */
  createProductRow(index, isEdit = false) {
    const row = document.createElement('div');
    row.className = 'product-row';
    row.setAttribute('data-index', index);

    const classPrefix = isEdit ? 'edit-' : '';
    const nameSuffix = isEdit ? '' : '';

    row.innerHTML = `
      <div class="form-group">
        <label class="form-label">Product <span class="required">*</span></label>
        <select name="items[${index}][product_id]" class="form-select ${classPrefix}product-select" required>
          <option value="">Select Product</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Quantity <span class="required">*</span></label>
        <input type="number" name="items[${index}][quantity]" class="form-control ${classPrefix}quantity-input" value="1" min="1" required>
      </div>
      <div class="form-group">
        <label class="form-label">Unit Price</label>
        <input type="text" class="form-control ${classPrefix}unit-price" readonly style="background: var(--bg-tertiary);">
      </div>
      <div class="form-group">
        <label class="form-label">Subtotal</label>
        <input type="text" class="form-control ${classPrefix}subtotal" readonly style="background: var(--bg-tertiary);">
      </div>
      <button type="button" class="btn-remove-product ${classPrefix}remove-product-btn">
        <i class="bi bi-trash"></i> Remove
      </button>
    `;

    return row;
  }

  /**
   * Update create order total
   */
  updateTotal() {
    let total = 0;
    document.querySelectorAll('#order-items-container .subtotal').forEach((field) => {
      const value = field.value.replace('$', '');
      if (value) total += parseFloat(value);
    });
    document.getElementById('totalAmount').innerHTML = `$${total.toFixed(2)}`;
  }

  /**
   * Update edit order total
   */
  updateEditTotal() {
    let total = 0;
    document.querySelectorAll('#editOrderItemsContainer .edit-subtotal').forEach((field) => {
      const value = field.value.replace('$', '');
      if (value) total += parseFloat(value);
    });
    document.getElementById('editTotalAmount').innerHTML = `$${total.toFixed(2)}`;
  }

  /**
   * Open create order modal
   */
  openCreateOrderModal() {
    this.resetOrderForm();
    this.orderModal.show();
  }

  /**
   * Reset order form
   */
  resetOrderForm() {
    const container = document.getElementById('order-items-container');
    container.innerHTML = '';
    this.productIndex = 1;

    const initialRow = this.createProductRow(0, false);
    container.appendChild(initialRow);

    document.getElementById('orderForm').reset();
    document.getElementById('customer_id').value = '';
    document.getElementById('warehouse_id').value = '';

    this.attachProductEventListeners();
    this.updateTotal();
  }

  /**
   * Handle order form submission
   */
  handleOrderFormSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch(form.action, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
          .getAttribute('content'),
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          this.showNotification('Order created successfully!', 'success');
          this.orderModal.hide();
          setTimeout(() => location.reload(), 1500);
        } else {
          this.showNotification(data.message || 'Failed to create order', 'error');
          this.displayErrors(data.errors || {}, 'modalErrors');
        }
      })
      .catch(() => this.showNotification('An error occurred', 'error'));
  }

  /**
   * Handle edit order form submission
   */
  handleEditOrderFormSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch(form.action, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
          .getAttribute('content'),
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          this.showNotification('Order updated successfully!', 'success');
          this.editOrderModal.hide();
          setTimeout(() => location.reload(), 1500);
        } else {
          this.showNotification(data.message || 'Failed to update order', 'error');
          this.displayErrors(data.errors || {}, 'editModalErrors');
        }
      })
      .catch(() => this.showNotification('An error occurred', 'error'));
  }

  /**
   * View order details
   */
  viewOrder(orderId) {
    fetch(`/orders/${orderId}`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
      },
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          const order = data.order;
          this.currentViewOrderId = order.id;

          let html = `
            <div class="row g-4">
              <div class="col-lg-8">
                <div class="form-section">
                  <div class="form-section-title"><i class="bi bi-info-circle"></i> Order Information</div>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Order ID</label>
                      <div class="p-lg bg-light rounded">#${order.id}</div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Status</label>
                      <div class="status-badge status-${order.status}">${order.status}</div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Customer</label>
                      <div class="p-lg bg-light rounded">${order.user ? order.user.name : 'Guest'}</div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Warehouse</label>
                      <div class="p-lg bg-light rounded">${order.warehouse ? order.warehouse.name : 'N/A'}</div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Order Date</label>
                      <div class="p-lg bg-light rounded">${new Date(order.created_at).toLocaleDateString()}</div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Total</label>
                      <div class="p-lg bg-light rounded price-tag">$${parseFloat(order.total).toFixed(2)}</div>
                    </div>
                  </div>
                </div>
                <div class="form-section">
                  <div class="form-section-title"><i class="bi bi-cart"></i> Order Items</div>
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${order.items.map((item) => `
                        <tr>
                          <td>${item.product_name}</td>
                          <td>${item.quantity}</td>
                          <td>$${parseFloat(item.price).toFixed(2)}</td>
                          <td>$${parseFloat(item.subtotal).toFixed(2)}</td>
                        </tr>
                      `).join('')}
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="form-section">
                  <div class="form-section-title"><i class="bi bi-geo-alt"></i> Addresses</div>
                  <div class="form-group">
                    <label class="form-label">Shipping</label>
                    <div class="p-lg bg-light rounded">${order.shipping_address || 'Not provided'}</div>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Billing</label>
                    <div class="p-lg bg-light rounded">${order.billing_address || 'Not provided'}</div>
                  </div>
                </div>
                <div class="form-section">
                  <div class="form-section-title"><i class="bi bi-pencil"></i> Notes</div>
                  <div class="p-lg bg-light rounded">${order.notes || 'No notes'}</div>
                </div>
              </div>
            </div>
          `;

          document.getElementById('viewOrderContent').innerHTML = html;
          document.getElementById('viewOrderId').textContent = order.id;
          this.viewOrderModal.show();
        }
      })
      .catch(() => this.showNotification('Failed to load order', 'error'));
  }

  /**
   * Edit order
   */
  editOrder(orderId) {
    fetch(`/orders/${orderId}/edit`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
      },
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Implementation for edit order - similar structure to view
          this.loadEditOrderContent(data.order);
          this.editOrderModal.show();
        }
      })
      .catch(() => this.showNotification('Failed to load order for editing', 'error'));
  }

  /**
   * Load edit order content
   */
  loadEditOrderContent(order) {
    document.getElementById('editOrderId').textContent = order.id;
    document.getElementById('editOrderForm').action = `/orders/${order.id}`;

    // Populate form with order data - customize as needed
    if (document.getElementById('editCustomerId')) {
      document.getElementById('editCustomerId').value = order.user_id || '';
    }
    if (document.getElementById('editWarehouseId')) {
      document.getElementById('editWarehouseId').value = order.warehouse_id || '';
    }
    if (document.getElementById('editOrderStatus')) {
      document.getElementById('editOrderStatus').value = order.status;
    }
    if (document.getElementById('editShippingAddress')) {
      document.getElementById('editShippingAddress').value = order.shipping_address || '';
    }
    if (document.getElementById('editBillingAddress')) {
      document.getElementById('editBillingAddress').value = order.billing_address || '';
    }
    if (document.getElementById('editOrderNotes')) {
      document.getElementById('editOrderNotes').value = order.notes || '';
    }

    this.attachEditProductEventListeners();
    this.updateEditTotal();
  }

  /**
   * Print order/invoice
   */
  printOrder(orderId) {
    window.open(`/orders/${orderId}/print`, '_blank');
  }

  /**
   * Handle select all checkbox
   */
  handleSelectAllChange(e) {
    const isChecked = e.target.checked;
    document.querySelectorAll('.order-checkbox').forEach((checkbox) => {
      checkbox.checked = isChecked;
    });
    this.updateSelectedCount();
  }

  /**
   * Toggle select all
   */
  toggleSelectAll() {
    const checkbox = document.getElementById('selectAllCheckbox');
    checkbox.checked = !checkbox.checked;
    this.handleSelectAllChange({ target: checkbox });
  }

  /**
   * Update selected count
   */
  updateSelectedCount() {
    const count = document.querySelectorAll('.order-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count;
    this.selectedOrderIds.clear();
    document.querySelectorAll('.order-checkbox:checked').forEach((checkbox) => {
      this.selectedOrderIds.add(parseInt(checkbox.value));
    });
  }

  /**
   * Apply filters
   */
  applyFilters() {
    const params = new URLSearchParams();

    const status = document.getElementById('statusFilter')?.value;
    if (status) params.append('status', status);

    const customer = document.getElementById('customerFilter')?.value;
    if (customer) params.append('customer', customer);

    const warehouse = document.getElementById('warehouseFilter')?.value;
    if (warehouse) params.append('warehouse', warehouse);

    const fromDate = document.getElementById('fromDateFilter')?.value;
    if (fromDate) params.append('from_date', fromDate);

    const toDate = document.getElementById('toDateFilter')?.value;
    if (toDate) params.append('to_date', toDate);

    if (params.toString()) {
      window.location.href = `?${params.toString()}`;
    }
  }

  /**
   * Clear filters
   */
  clearFilters() {
    document.getElementById('statusFilter').value = '';
    document.getElementById('customerFilter').value = '';
    document.getElementById('warehouseFilter').value = '';
    document.getElementById('fromDateFilter').value = '';
    document.getElementById('toDateFilter').value = '';
    window.location.href = window.location.pathname;
  }

  /**
   * Export to Excel
   */
  exportExcel() {
    const ids = Array.from(this.selectedOrderIds).join(',');
    if (!ids) {
      this.showNotification('Please select at least one order', 'warning');
      return;
    }
    window.location.href = `/orders/export?ids=${ids}`;
  }

  /**
   * Print invoices
   */
  printInvoices() {
    const ids = Array.from(this.selectedOrderIds);
    if (!ids.length) {
      this.showNotification('Please select at least one order', 'warning');
      return;
    }
    ids.forEach((id) => {
      setTimeout(() => this.printOrder(id), 200);
    });
  }

  /**
   * Display form errors
   */
  displayErrors(errors, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    let html = '<div class="alert alert-danger">';
    Object.values(errors).forEach((errorList) => {
      if (Array.isArray(errorList)) {
        errorList.forEach((error) => {
          html += `<div>${error}</div>`;
        });
      } else {
        html += `<div>${errorList}</div>`;
      }
    });
    html += '</div>';

    container.innerHTML = html;
    container.classList.remove('d-none');
  }

  /**
   * Show notification
   */
  showNotification(message, type = 'info') {
    // Using toast or alert - adjust based on your notification system
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.textContent = message;

    document.body.appendChild(alertDiv);
    setTimeout(() => {
      alertDiv.remove();
    }, 3000);
  }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function () {
  new OrdersManager();
});
