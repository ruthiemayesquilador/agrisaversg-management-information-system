<!-- ===================== EDIT EXPENSE MODAL ===================== -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);">
                <h5 class="modal-title text-white fw-bold" id="editExpenseModalLabel">
                    <i class="bi bi-pencil me-2"></i> Edit Expense
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= site_url('accounting/updateExpense') ?>" method="post" id="editExpenseForm" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" id="edit_expense_id" name="id">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 1.5rem;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_expense_date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_expense_date" name="date" required>
                            <div class="invalid-feedback">Date is required.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_expense_amount" class="form-label fw-semibold">Amount (₱) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="edit_expense_amount" name="amount" placeholder="0.00" required>
                            </div>
                            <div class="invalid-feedback">Amount is required.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_expense_type" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_expense_type" name="expense_type" required>
                                <option value="expense">Operating Expense</option>
                                <option value="owner_draw">Owner Draw (Cash Remit)</option>
                            </select>
                            <div class="invalid-feedback">Type is required.</div>
                        </div>
                        <div class="col-12">
                            <label for="edit_expense_description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_expense_description" name="description" required>
                            <div class="invalid-feedback">Description is required.</div>
                        </div>
                        <div class="col-12">
                            <label for="edit_expense_remarks" class="form-label fw-semibold">Remarks</label>
                            <input type="text" class="form-control" id="edit_expense_remarks" name="remarks">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <div class="me-auto text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Fields marked <span class="text-danger">*</span> are required.
                    </div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);">
                        <i class="bi bi-save me-1"></i> Update Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END EDIT EXPENSE MODAL =================== -->

<script>
document.getElementById('editExpenseModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    if (!btn) return;
    const d = btn.dataset;
    document.getElementById('edit_expense_id').value          = d.id          || '';
    document.getElementById('edit_expense_date').value        = d.date        || '';
    document.getElementById('edit_expense_amount').value      = d.amount      || '';
    document.getElementById('edit_expense_type').value        = d.type        || 'expense';
    document.getElementById('edit_expense_description').value = d.description || '';
    document.getElementById('edit_expense_remarks').value     = d.remarks     || '';
});
document.getElementById('editExpenseModal').addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('editExpenseForm');
    form.reset();
    form.classList.remove('was-validated');
});
document.getElementById('editExpenseForm').addEventListener('submit', function (e) {
    if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    this.classList.add('was-validated');
});
</script>