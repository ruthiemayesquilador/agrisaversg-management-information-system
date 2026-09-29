<!-- ===================== DELETE EXPENSE MODAL ===================== -->
<div class="modal fade" id="deleteExpenseModal" tabindex="-1" aria-labelledby="deleteExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #f44336 0%, #c62828 100%);">
                <h5 class="modal-title text-white fw-bold" id="deleteExpenseModalLabel">
                    <i class="bi bi-trash me-2"></i> Delete Expense
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4 px-4">
                <div style="width:70px; height:70px; background:#fff0f0; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                    <i class="bi bi-trash fs-2" style="color:#f44336;"></i>
                </div>
                <h5 class="fw-bold mb-2">Are you sure?</h5>
                <p class="text-muted mb-1" style="font-size:14px;">You are about to delete expense:</p>
                <p class="fw-semibold mb-3" style="font-size:15px; color:#1a1a1a;"><span id="delete_expense_description"></span></p>
                <div class="alert alert-danger py-2 px-3" style="font-size:13px; border-radius:8px;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    This action <strong>cannot be undone</strong>.
                </div>
            </div>
            <div class="modal-footer border-top justify-content-center gap-3">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i> Cancel
                </button>
                <form id="deleteExpenseForm" action="<?= site_url('accounting/deleteExpense') ?>" method="post" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" id="delete_expense_id" name="id">
                    <button type="submit" class="btn text-white fw-semibold px-4" style="background: linear-gradient(135deg, #f44336 0%, #c62828 100%);">
                        <i class="bi bi-trash me-1"></i> Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- =================== END DELETE EXPENSE MODAL =================== -->

<script>
document.getElementById('deleteExpenseModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    if (!btn) return;
    document.getElementById('delete_expense_id').value              = btn.dataset.id          || '';
    document.getElementById('delete_expense_description').textContent = btn.dataset.description || 'this expense';
});
</script>