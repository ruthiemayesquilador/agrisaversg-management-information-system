<!-- ===================== NEW EXPENSE MODAL ===================== -->
<div class="modal fade" id="newExpenseModal" tabindex="-1" aria-labelledby="newExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);">
                <h5 class="modal-title text-white fw-bold" id="newExpenseModalLabel">
                    <i class="bi bi-plus-circle me-2"></i> New Expense
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= site_url('accounting/storeExpense') ?>" method="post" id="newExpenseForm" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 1.5rem;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="new_expense_date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="new_expense_date" name="date" required>
                            <div class="invalid-feedback">Date is required.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="new_expense_amount" class="form-label fw-semibold">Amount (₱) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="new_expense_amount" name="amount" placeholder="0.00" required>
                            </div>
                            <div class="invalid-feedback">Amount is required.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="new_expense_type" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="new_expense_type" name="expense_type" required>
                                <option value="expense" selected>Operating Expense</option>
                                <option value="owner_draw">Owner Draw (Cash Remit)</option>
                            </select>
                            <div class="invalid-feedback">Type is required.</div>
                        </div>
                        <div class="col-12">
                            <label for="new_expense_description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="new_expense_description" name="description" placeholder="e.g. Shopee Parcel" required>
                            <div class="invalid-feedback">Description is required.</div>
                        </div>
                        <div class="col-12">
                            <label for="new_expense_remarks" class="form-label fw-semibold">Remarks</label>
                            <input type="text" class="form-control" id="new_expense_remarks" name="remarks" placeholder="Optional remarks...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <div class="me-auto text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Fields marked <span class="text-danger">*</span> are required.
                    </div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);">
                        <i class="bi bi-check-circle me-1"></i> Save Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END NEW EXPENSE MODAL =================== -->

<script>
document.getElementById('newExpenseModal').addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('newExpenseForm');
    form.reset();
    form.classList.remove('was-validated');
});
document.getElementById('newExpenseForm').addEventListener('submit', function (e) {
    if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    this.classList.add('was-validated');
});
</script>