/**
 * Orders Page JavaScript
 * Clean, modular, well-organized
 * 
 * Handles:
 * - Modal management
 * - Form submissions
 * - Product row management
 * - Filtering & bulk actions
 * - Table interactions
 */

class OrdersManager {
  constructor() {
    this.productIndex = 1;
    this.editProductIndex = 0;
    this.selectedOrderIds = new Set();

    this.initializeModals();
    this.attachEventListeners();
  }

  /**
   * Initialize all modals
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
    // Create buttons
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

    // Product management
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

    // Row checkboxes
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

    this.attachProductEventListeners();
  }

  /**
   * Attach product event listeners (for dynamic rows)
   */
  attachProductEventListeners() {
    document.querySelectorAll('.product-select').forEach((select) => {
      select.removeEventListener('change', this.handleProductChange);
      select.addEventListener('change', (e) => this.handleProductChange(e));
    });

    document.querySelectorAll('.quantity-input').forEach((input) => {
      input.removeEventListener('change', this.handleQuantityChange);
      input.addEventListener('change', (e) => this.handleQuantityChange(e));
    });

    document.querySelectorAll('.remove-product-btn').forEach((btn) => {
      btn.removeEventListener('click', this.removeProductRow);
      btn.addEventListener('click', (e) => this.removeProductRow(e));
    });

    document.querySelectorAll('.edit-product-select').forEach((select) => {
      select.removeEventListener('change', this.handleEditProductChange);
      select.addEventListener('change', (e) => this.handleEditProductChange(e));
    });

    document.querySelectorAll('.edit-quantity-input').forEach((input) => {
      input.removeEventListener('change', this.handleEditQuantityChange);
      input.addEventListener('change', (e) => this.handleEditQuantityChange(e));
    });

    document.querySelectorAll('.remove-edit-product-btn').forEach((btn) => {
      btn.removeEventListener('click', this.removeEditProductRow);
      btn.addEventListener('click', (e) => this.removeEditProductRow(e));
    });
  }

  /**
   * Handle product selection change
   */
  handleProductChange = (e) => {
    const select = e.target;
    const row = select.closest('.product-row');
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price') || 0;

    row.querySelector('.unit-price').value = `$${parseFloat(price).toFixed(2)}`;

    const quantity = parseInt(row.querySelector('.quantity-input').value) || 0;
    const subtotal = quantity * parseFloat(price);
    row.querySelector('.subtotal').value = `$${subtotal.toFixed(2)}`;

    this.updateTotal();
  }

  /**
   * Handle quantity input change
   */
  handleQuantityChange = (e) => {
    const input = e.target;
    const row = input.closest('.product-row');
    const select = row.querySelector('.product-select');
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price') || 0;
    const quantity = parseInt(input.value) || 0;
    const subtotal = quantity * parseFloat(price);

    row.querySelector('.subtotal').value = `$${subtotal.toFixed(2)}`;
    this.updateTotal();
  }

  /**
   * Handle edit product selection change
   */
  handleEditProductChange = (e) => {
    const select = e.target;
    const row = select.closest('.product-row');
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price') || 0;

    row.querySelector('.edit-unit-price').value = `$${parseFloat(price).toFixed(2)}`;

    const quantity = parseInt(row.querySelector('.edit-quantity-input').value) || 0;
    const subtotal = quantity * parseFloat(price);
    row.querySelector('.edit-subtotal').value = `$${subtotal.toFixed(2)}`;

    this.updateEditTotal();
  }

  /**
   * Handle edit quantity input change
   */
  handleEditQuantityChange = (e) => {
    const input = e.target;
    const row = input.closest('.product-row');
    const select = row.querySelector('.edit-product-select');
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price') || 0;
    const quantity = parseInt(input.value) || 0;
    const subtotal = quantity * parseFloat(price);

    row.querySelector('.edit-subtotal').value = `$${subtotal.toFixed(2)}`;
    this.updateEditTotal();
  }

  /**
   * Remove product row
   */
  removeProductRow = (e) => {
    const button = e.target.closest('button');
    const row = button.closest('.product-row');
    const container = document.getElementById('order-items-container');

    if (container.querySelectorAll('.product-row').length > 1) {
      row.remove();
      this.updateTotal();
    } else {
      alert('At least one product is required.');
    }
  }

  /**
   * Remove edit product row
   */
  removeEditProductRow = (e) => {
    const button = e.target.closest('button');
    const row = button.closest('.product-row');
    const container = document.getElementById('editOrderItemsContainer');

    if (container.querySelectorAll('.product-row').length > 1) {
      row.remove();
      this.updateEditTotal();
    } else {
      alert('At least one product is required.');
    }
  }

  /**
   * Add new product row to order form
   */
  addProductRow() {
    const container = document.getElementById('order-items-container');
    const newRow = this.createProductRow(this.productIndex, false);
    container.appendChild(newRow);
    this.productIndex++;
    this.attachProductEventListeners();
  }

  /**
   * Create product row HTML
   */
  createProductRow(index, isEdit = false) {
    const row = document.createElement('div');
    row.className = 'product-row';
    row.setAttribute('data-index', index);

    const prefix = isEdit ? 'edit-' : '';
    const selectClass = isEdit ? 'edit-product-select' : 'product-select';
    const quantityClass = isEdit ? 'edit-quantity-input' : 'quantity-input';
    const unitPriceClass = isEdit ? 'edit-unit-price' : 'unit-price';
    const subtotalClass = isEdit ? 'edit-subtotal' : 'subtotal';
    const removeClass = isEdit ? 'remove-edit-product-btn' : 'remove-product-btn';

    row.innerHTML = `
      <div class="form-group">
        <label class="form-label">Product <span class="required">*</span></label>
        <select name="items[${index}][product_id]" class="form-select ${selectClass}" required>
          <option value="">Select Product</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Quantity <span class="required">*</span></label>
        <input type="number" name="items[${index}][quantity]" class="form-control ${quantityClass}" value="1" min="1" required>
      </div>
      <div class="form-group">
        <label class="form-label">Unit Price</label>
        <input type="text" class="form-control ${unitPriceClass}" readonly>
      </div>
      <div class="form-group">
        <label class="form-label">Subtotal</label>
        <input type="text" class="form-control ${subtotalClass}" readonly>
      </div>
      <button type="button" class="btn btn-sm btn-danger ${removeClass}">
        <i class="bi bi-trash"></i> Remove
      </button>
    `;

    return row;
  }

  /**
   * Update order total
   */
  updateTotal() {
    const container = document.getElementById('order-items-container');
    const subtotals = Array.from(container.querySelectorAll('.subtotal')).map(el =>
      parseFloat(el.value.replace('$', '')) || 0
    );
    const total = subtotals.reduce((sum, val) => sum + val, 0);
    const totalElement = document.getElementById('orderTotal');
    if (totalElement) {
      totalElement.textContent = `$${total.toFixed(2)}`;
    }
  }

  /**
   * Update edit order total
   */
  updateEditTotal() {
    const container = document.getElementById('editOrderItemsContainer');
    const subtotals = Array.from(container.querySelectorAll('.edit-subtotal')).map(el =>
      parseFloat(el.value.replace('$', '')) || 0
    );
    const total = subtotals.reduce((sum, val) => sum + val, 0);
    const totalElement = document.getElementById('editOrderTotal');
    if (totalElement) {
      totalElement.textContent = `$${total.toFixed(2)}`;
    }
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
    const form = document.getElementById('orderForm');
    form.reset();
    this.productIndex = 1;

    const container = document.getElementById('order-items-container');
    container.innerHTML = '';

    const firstRow = this.createProductRow(0, false);
    container.appendChild(firstRow);
    this.attachProductEventListeners();
    this.updateTotal();
  }

  /**
   * Handle order form submission
   */
  handleOrderFormSubmit(e) {
    e.preventDefault();
    // Form will submit normally with Laravel
    document.getElementById('orderForm').submit();
  }

  /**
   * Handle edit order form submission
   */
  handleEditOrderFormSubmit(e) {
    e.preventDefault();
    document.getElementById('editOrderForm').submit();
  }

  /**
   * View order
   */
  viewOrder(orderId) {
    fetch(`/orders/${orderId}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(res => res.json())
      .then(data => {
        // Populate view modal with order data
        console.log('View order:', data);
        this.viewOrderModal.show();
      })
      .catch(err => console.error('Error viewing order:', err));
  }

  /**
   * Edit order
   */
  editOrder(orderId) {
    fetch(`/orders/${orderId}/edit`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(res => res.json())
      .then(data => {
        // Populate edit modal with order data
        console.log('Edit order:', data);
        this.editOrderModal.show();
      })
      .catch(err => console.error('Error editing order:', err));
  }

  /**
   * Print order
   */
  printOrder(orderId) {
    window.location.href = `/orders/${orderId}/print`;
  }

  /**
   * Apply filters
   */
  applyFilters() {
    const params = new URLSearchParams({
      status: document.getElementById('statusFilter').value,
      customer: document.getElementById('customerFilter').value,
      warehouse: document.getElementById('warehouseFilter').value,
      from_date: document.getElementById('fromDateFilter').value,
      to_date: document.getElementById('toDateFilter').value,
    });

    window.location.href = `?${params.toString()}`;
  }

  /**
   * Clear filters
   */
  clearFilters() {
    window.location.href = window.location.pathname;
  }

  /**
   * Toggle select all checkboxes
   */
  toggleSelectAll() {
    const checkbox = document.getElementById('selectAllCheckbox');
    checkbox.checked = !checkbox.checked;
    this.handleSelectAllChange({ target: checkbox });
  }

  /**
   * Handle select all change
   */
  handleSelectAllChange(e) {
    const isChecked = e.target.checked;
    document.querySelectorAll('.order-checkbox').forEach((checkbox) => {
      checkbox.checked = isChecked;
      this.updateSelectedCount();
    });
  }

  /**
   * Update selected count
   */
  updateSelectedCount() {
    const selected = document.querySelectorAll('.order-checkbox:checked').length;
    const countElement = document.getElementById('selectedCount');
    if (countElement) {
      countElement.textContent = selected;
    }

    this.selectedOrderIds.clear();
    document.querySelectorAll('.order-checkbox:checked').forEach((checkbox) => {
      this.selectedOrderIds.add(checkbox.value);
    });
  }

  /**
   * Export to Excel
   */
  exportExcel() {
    if (this.selectedOrderIds.size === 0) {
      alert('Please select at least one order');
      return;
    }

    const ids = Array.from(this.selectedOrderIds).join(',');
    window.location.href = `/orders/export-excel?ids=${ids}`;
  }

  /**
   * Print invoices
   */
  printInvoices() {
    if (this.selectedOrderIds.size === 0) {
      alert('Please select at least one order');
      return;
    }

    const ids = Array.from(this.selectedOrderIds).join(',');
    window.open(`/orders/print-invoices?ids=${ids}`, '_blank');
  }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
  new OrdersManager();
});
