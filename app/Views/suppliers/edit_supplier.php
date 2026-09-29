<!-- ===================== EDIT SUPPLIER MODAL ===================== -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);">
                <h5 class="modal-title text-white fw-bold" id="editSupplierModalLabel">
                    <i class="bi bi-pencil me-2"></i> Edit Supplier
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('suppliers/update') ?>" method="post" id="editSupplierForm" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" id="esup_id" name="id">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 1.5rem;">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="esup_supplier_name" class="form-label fw-semibold">Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="esup_supplier_name" name="supplier_name" required>
                            <div class="invalid-feedback">Supplier Name is required.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="esup_brand_name" class="form-label fw-semibold">Brand</label>
                            <input type="text" class="form-control" id="esup_brand_name" name="brand_name">
                        </div>
                        <div class="col-md-6">
                            <label for="esup_importer" class="form-label fw-semibold">Importer</label>
                            <select class="form-select" id="esup_importer" name="importer">
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
                        <i class="bi bi-save me-1"></i> Update Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END EDIT SUPPLIER MODAL =================== -->

<script>
document.getElementById('editSupplierModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    if (!btn) return;
    const d = btn.dataset;
    document.getElementById('esup_id').value            = d.id        || '';
    document.getElementById('esup_supplier_name').value = d.supplier  || '';
    document.getElementById('esup_brand_name').value    = d.brandName || '';
    document.getElementById('esup_importer').value      = d.importer  || '';
});

document.getElementById('editSupplierModal').addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('editSupplierForm');
    form.reset();
    form.classList.remove('was-validated');
});

document.getElementById('editSupplierForm').addEventListener('submit', function (e) {
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
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
    
    fetch('<?= site_url('suppliers/update') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Supplier updated successfully!');
            bootstrap.Modal.getInstance(document.getElementById('editSupplierModal')).hide();
            location.reload(); // Reload to show updated data
        } else {
            alert('Error: ' + (data.message || 'Failed to update supplier'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the supplier');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
});
</script>