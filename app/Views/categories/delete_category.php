<!-- ===================== DELETE CATEGORY MODAL ===================== -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #f44336 0%, #c62828 100%);">
                <h5 class="modal-title text-white fw-bold" id="deleteCategoryModalLabel">
                    <i class="fas fa-trash-alt me-2"></i> Delete Category
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body text-center py-4 px-4">
                <div style="width:70px; height:70px; background:#fff0f0; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                    <i class="fas fa-trash-alt fa-2x" style="color:#f44336;"></i>
                </div>
                <h5 class="fw-bold mb-2">Are you sure?</h5>
                <p class="text-muted mb-1" style="font-size:15px;">You are about to delete:</p>
                <p id="delete-category-name" class="fw-semibold mb-3" style="font-size:16px; color:#1a1a1a;"></p>
                <div class="alert alert-danger py-2 px-3" style="font-size:14px; border-radius:8px;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    This action <strong>cannot be undone</strong>. The category will be permanently removed.
                </div>
            </div>

            <div class="modal-footer border-top justify-content-center gap-3">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <form id="deleteCategoryForm" action="<?= base_url('categories/delete') ?>" method="post" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" id="delete-category-id" name="category_id">
                    <button type="submit" class="btn text-white fw-semibold px-4" style="background: linear-gradient(135deg, #f44336 0%, #c62828 100%);">
                        <i class="fas fa-trash-alt me-1"></i> Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- ================== END DELETE CATEGORY MODAL ================== -->

<script>
document.getElementById('deleteCategoryModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const d = btn.dataset;

    document.getElementById('delete-category-id').value   = d.id   || '';
    document.getElementById('delete-category-name').textContent = d.name || 'this category';
});
</script>
