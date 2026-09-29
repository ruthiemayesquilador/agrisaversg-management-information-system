<!-- ===================== NEW SUPPLIER MODAL ===================== -->
<div class="modal fade" id="newSupplierModal" tabindex="-1" aria-labelledby="newSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);">
                <h5 class="modal-title text-white fw-bold" id="newSupplierModalLabel">
                    <i class="bi bi-plus-circle me-2"></i> New Supplier
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('suppliers/store') ?>" method="post" id="newSupplierForm" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 1.5rem;">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="ns_supplier_name" class="form-label fw-semibold">Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ns_supplier_name" name="supplier_name" placeholder="e.g. Kubota Philippines" required>
                            <div class="invalid-feedback">Supplier Name is required.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="ns_brand_name" class="form-label fw-semibold">Brand</label>
                            <input type="text" class="form-control" id="ns_brand_name" name="brand_name" placeholder="e.g. Kubota">
                        </div>
                        <div class="col-md-6">
                            <label for="ns_importer" class="form-label fw-semibold">Importer</label>
                            <select class="form-select" id="ns_importer" name="importer">
                                <option value="">— Select —</option>
                                <option value="Local">Local</option>
                                <option value="Overseas">Overseas</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <div class="me-auto text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Fields marked <span class="text-danger">*</span> are required.
                    </div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);">
                        <i class="bi bi-check-circle me-1"></i> Save Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END NEW SUPPLIER MODAL =================== -->

<script>
document.getElementById('newSupplierModal').addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('newSupplierForm');
    form.reset();
    form.classList.remove('was-validated');
});

document.getElementById('newSupplierForm').addEventListener('submit', function (e) {
    e.preventDefault();
    e.stopPropagation();
    
    if (!this.checkValidity()) {
        this.classList.add('was-validated');
        return;
    }
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    
    fetch('<?= site_url('suppliers/store') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Supplier added successfully!');
            bootstrap.Modal.getInstance(document.getElementById('newSupplierModal')).hide();
            location.reload(); // Reload to show new data
        } else {
            alert('Error: ' + (data.message || 'Failed to add supplier'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the supplier');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
});
</script>