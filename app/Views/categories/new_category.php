<!-- ===================== NEW CATEGORY MODAL ===================== -->
<div class="modal fade" id="newCategoryModal" tabindex="-1" aria-labelledby="newCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #FF8C42 0%, #FF7A2F 100%);">
                <h5 class="modal-title text-white fw-bold" id="newCategoryModalLabel">
                    <i class="fas fa-plus-circle me-2"></i> Add New Category
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= base_url('categories/store') ?>" method="post" id="newCategoryForm" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <div class="modal-body" style="padding: 1.5rem;">

					<div class="mb-3">
						<label for="category_name" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
						<input type="text" id="category_name" name="category_name" class="form-control" placeholder="e.g. Engine Parts, Brake System" required>
						<div class="invalid-feedback">Category name is required.</div>
					</div>

                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #FF8C42 0%, #FF7A2F 100%);">
                        <i class="fas fa-plus-circle me-1"></i> Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END NEW CATEGORY MODAL =================== -->

<script>
// Bootstrap validation for new category form
document.getElementById('newCategoryForm').addEventListener('submit', function (e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});

// Reset form when modal is closed
document.getElementById('newCategoryModal').addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('newCategoryForm');
    form.reset();
    form.classList.remove('was-validated');
});
</script>
