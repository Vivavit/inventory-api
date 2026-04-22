document.addEventListener('DOMContentLoaded', () => {
    const skuInput = document.getElementById('sku');
    const uploadInput = document.getElementById('images');
    const uploadArea = document.getElementById('uploadArea');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const preview = document.getElementById('imagePreview');

    window.generateSKU = function generateSKU() {
        if (!skuInput) {
            return;
        }

        const random = Math.random().toString(36).slice(2, 8).toUpperCase();
        skuInput.value = `PROD-${random}`;
    };

    window.handleImageSelection = function handleImageSelection(event) {
        const file = event.target.files?.[0];
        if (!file || !preview || !previewContainer || !uploadArea) {
            return;
        }

        const reader = new FileReader();
        reader.onload = ({ target }) => {
            preview.src = target?.result || '';
            previewContainer.classList.remove('d-none');
            uploadArea.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    };

    window.removeImage = function removeImage() {
        if (!uploadInput || !previewContainer || !uploadArea) {
            return;
        }

        uploadInput.value = '';
        previewContainer.classList.add('d-none');
        uploadArea.classList.remove('d-none');
    };
});
