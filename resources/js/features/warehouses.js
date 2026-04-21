/**
 * resources/js/features/warehouses.js
 * 
 * Logic for the Warehouses feature.
 */

document.addEventListener('DOMContentLoaded', () => {
    // Handle delete confirmations using event delegation
    const warehouseGrid = document.querySelector('.warehouse-grid');
    
    if (warehouseGrid) {
        warehouseGrid.addEventListener('submit', (e) => {
            const form = e.target;
            
            // Check if the form being submitted is a delete form
            if (form.matches('form[action*="warehouses"]')) {
                const methodInput = form.querySelector('input[name="_method"][value="DELETE"]');
                if (methodInput) {
                    const warehouseName = form.dataset.warehouseName || 'this warehouse';
                    if (!confirm(`Are you sure you want to delete warehouse: ${warehouseName}?`)) {
                        e.preventDefault(); // Stop form submission if canceled
                    }
                }
            }
        });
    }
});
