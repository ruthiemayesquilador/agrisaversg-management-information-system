<!-- ===================== EDIT SALE MODAL ===================== -->
<div class="modal fade" id="editSaleModal" tabindex="-1" aria-labelledby="editSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);">
                <h5 class="modal-title text-white fw-bold" id="editSaleModalLabel">
                    <i class="bi bi-pencil me-2"></i> Edit Sale
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= site_url('accounting/updateSale') ?>" method="post" id="editSaleForm" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" id="edit_sale_id" name="id">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 1.5rem;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_csi" class="form-label fw-semibold">CSI No. <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_csi" name="csi" required>
                            <div class="invalid-feedback">CSI No. is required.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_date" name="date" required>
                            <div class="invalid-feedback">Date is required.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_cash_sales" class="form-label fw-semibold">Cash Sales (₱)</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="edit_cash_sales" name="cash_sales" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_payment_method" class="form-label fw-semibold">Payment Method</label>
                            <select class="form-select" id="edit_payment_method" name="payment_method">
                                <option value="" disabled>-- Select --</option>
                                <option value="Cash">Cash</option>
                                <option value="E-Wallet">E-Wallet</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_account_receivable" class="form-label fw-semibold">Account Receivable (₱)</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="edit_account_receivable" name="account_receivable" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_remarks" class="form-label fw-semibold">Remarks</label>
                            <input type="text" class="form-control" id="edit_remarks" name="remarks">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <div class="me-auto text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Fields marked <span class="text-danger">*</span> are required.
                    </div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);">
                        <i class="bi bi-save me-1"></i> Update Sale
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END EDIT SALE MODAL =================== -->

<script>
document.getElementById('editSaleModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    if (!btn) return;
    const d = btn.dataset;
    document.getElementById('edit_sale_id').value               = d.id      || '';
    document.getElementById('edit_csi').value                   = d.csi     || '';
    document.getElementById('edit_date').value                  = d.date    || '';
    document.getElementById('edit_cash_sales').value            = d.cash    || '';
    document.getElementById('edit_account_receivable').value    = d.ar      || '';
    document.getElementById('edit_remarks').value               = d.remarks || '';
    const sel = document.getElementById('edit_payment_method');
    [...sel.options].forEach(o => o.selected = o.value === (d.method || ''));
});
document.getElementById('editSaleModal').addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('editSaleForm');
    form.reset();
    form.classList.remove('was-validated');
});
document.getElementById('editSaleForm').addEventListener('submit', function (e) {
    if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    this.classList.add('was-validated');
});
</script>
