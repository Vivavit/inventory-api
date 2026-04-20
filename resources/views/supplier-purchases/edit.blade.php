{{-- This extends the same layout as create --}}
@extends('layouts.app')

@section('content')
@parent
{{-- We'll reuse the create modal but load data via JS --}}
@endsection

@push('scripts')
<script>
// We'll create a function to open the edit modal and load purchase data
function openEditModal(purchaseId) {
    // Cleanup any existing modals
    cleanupModals();

    // Show loading state in the create modal (we'll reuse it for edit)
    const modalEl = document.getElementById('createModal');
    if (modalEl) {
        const modalBody = modalEl.querySelector('.modal-body');
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-success" role="status"></div>
                <p class="mt-2 text-muted" style="font-size:13px;">Loading purchase data…</p>
            </div>`;
        new bootstrap.Modal(modalEl, {backdrop:true,keyboard:true,focus:true}).show();
    }

    // Fetch the purchase data
    fetch(`/supplier-purchases/${purchaseId}/edit`, {
        headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
    })
    .then(r => r.json())
    .then(data => {
        if (!data) return;

        // Populate the form
        document.getElementById('createForm').action = "{{ route('supplier-purchases.update', ':id') }}".replace(':id', purchaseId);
        document.getElementById('createForm').method = 'POST';
        // Add hidden method field for PUT
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PUT';
        document.getElementById('createForm').appendChild(methodInput);

        // Reset form and set values
        resetCreateForm(); // This will reset and add one empty item row

        // Set header
        document.querySelector('#createModal .modal-title').textContent = 'Edit Purchase Order';
        document.getElementById('cSubmitBtn').innerHTML = '<i class="bi bi-check-lg"></i> Update Purchase';

        // Set basic fields
        document.getElementById('createForm').querySelector('[name="supplier_id"]').value = data.supplier_id ?? '';
        document.getElementById('createForm').querySelector('[name="order_date"]').value = data.order_date ?? '';
        document.getElementById('createForm').querySelector('[name="expected_delivery_date"]').value = data.expected_delivery_date ?? '';
        document.getElementById('createForm').querySelector('[name="warehouse_id"]').value = data.warehouse_id ?? '';
        document.getElementById('createForm').querySelector('[name="shipping_cost"]').value = data.shipping_cost ?? '';
        document.getElementById('createForm').querySelector('[name="payment_terms"]').value = data.payment_terms ?? '';
        document.getElementById('createForm').querySelector('[name="notes"]').value = data.notes ?? '';

        // Clear existing item rows and add rows for each item
        const tbody = document.getElementById('itemsTableBody');
        tbody.innerHTML = ''; // Clear

        data.items.forEach((item, index) => {
            if (index === 0) {
                // Update first row
                const row = tbody.insertRow();
                row.className = 'item-row';
                row.innerHTML = buildItemRow(index);
                // Set values
                row.querySelector('[name="items[0][product_id]"]').value = item.product_id;
                row.querySelector('[name="items[0][quantity]"]').value = item.quantity;
                row.querySelector('[name="items[0][unit_price]"]').value = item.unit_price;
                // Trigger recalc for this row
                recalcRow(row);
            } else {
                // Add additional rows
                tbody.insertAdjacentHTML('beforeend', buildItemRow(index));
                const row = tbody.lastElementChild;
                row.querySelector(`[name="items[${index}][product_id]"]`).value = item.product_id;
                row.querySelector(`[name="items[${index}][quantity]"]`).value = item.quantity;
                row.querySelector(`[name="items[${index}][unit_price]"]`).value = item.unit_price;
                recalcRow(row);
            }
        });

        // Update total
        recalcTotal();

        // Update rowIndex for future additions
        rowIndex = data.items.length;
    })
    .catch(() => {
        if (modalEl) {
            const modalBody = modalEl.querySelector('.modal-body');
            modalBody.innerHTML = '<div class="alert alert-danger m-0">Failed to load purchase data. Please try again.</div>';
        }
    });
}

// We need to adjust the createForm submit handler to handle PUT/PATCH for updates
document.getElementById('createForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('cSubmitBtn');
    const orig = btn.innerHTML;
    const errBox = document.getElementById('createErrors');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
    errBox.innerHTML = '';
    errBox.classList.add('d-none');

    const formData = new FormData(this);
    // If we have a _method field, we need to send it as PUT/PATCH
    const method = formData.get('_method');
    let fetchOptions = {
        method: method ? method : 'POST',
        body: new URLSearchParams(formData), // For Laravel, we can send as URLSearchParams
        headers: {
            'X-Requested-With':'XMLHttpRequest',
            'Accept':'application/json',
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    };

    fetch(this.action, fetchOptions)
    .then(r => { if (r.redirected) { window.location.href = r.url; return null; } return r.json(); })
    .then(data => {
        if (!data) return;
        if (data.success || data.id) { window.location.reload(); return; }
        if (data.errors) {
            const list = Object.values(data.errors).flat().map(e=>`<li>${e}</li>`).join('');
            errBox.innerHTML = `<div class="alert alert-danger"><strong>Please fix:</strong><ul class="mb-0 mt-1">${list}</ul></div>`;
            errBox.classList.remove('d-none');
        } else if (data.message) {
            errBox.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            errBox.classList.remove('d-none');
        }
        btn.disabled = false; btn.innerHTML = orig;
    })
    .catch(() => {
        errBox.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
        errBox.classList.remove('d-none');
        btn.disabled = false; btn.innerHTML = orig;
    });
});
</script>
@endpush