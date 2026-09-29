<!-- ===================== EDIT CATEGORY MODAL ===================== -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #FF9800 0%, #f57c00 100%);">
                <h5 class="modal-title text-white fw-bold" id="editCategoryModalLabel">
                    <i class="fas fa-edit me-2"></i> Edit Category
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= base_url('categories/update') ?>" method="post" id="editCategoryForm" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" id="edit-category-id" name="category_id">

                <div class="modal-body" style="padding: 1.5rem;">

					<div class="mb-3">
						<label for="edit_category_name" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
						<input type="text" id="edit_category_name" name="category_name" class="form-control" required>
						<div class="invalid-feedback">Category name is required.</div>
					</div>

                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #FF9800 0%, #f57c00 100%);">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END EDIT CATEGORY MODAL =================== -->

<script>
// Populate Edit Modal on open
document.getElementById('editCategoryModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const d = btn.dataset;

    document.getElementById('edit-category-id').value       = d.id   || '';
    document.getElementById('edit_category_name').value     = d.name || '';
});

// Bootstrap validation for edit category form
document.getElementById('editCategoryForm').addEventListener('submit', function (e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});

// Reset form when modal is closed
document.getElementById('editCategoryModal').addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('editCategoryForm');
    form.reset();
    form.classList.remove('was-validated');
});
</script>
