<!-- ===================== NEW SALE MODAL ===================== -->
<div class="modal fade" id="newSaleModal" tabindex="-1" aria-labelledby="newSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);">
                <h5 class="modal-title text-white fw-bold" id="newSaleModalLabel">
                    <i class="bi bi-plus-circle me-2"></i> New Sale
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= site_url('accounting/storeSale') ?>" method="post" id="newSaleForm" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 1.5rem;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="new_csi" class="form-label fw-semibold">CSI No. <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="new_csi" name="csi" placeholder="e.g. 001" required>
                            <div class="invalid-feedback">CSI No. is required.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="new_date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="new_date" name="date" required>
                            <div class="invalid-feedback">Date is required.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="new_cash_sales" class="form-label fw-semibold">Cash Sales (₱)</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="new_cash_sales" name="cash_sales" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="new_payment_method" class="form-label fw-semibold">Payment Method</label>
                            <select class="form-select" id="new_payment_method" name="payment_method">
                                <option value="" selected disabled>-- Select --</option>
                                <option value="Cash">Cash</option>
                                <option value="E-Wallet">E-Wallet</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="new_account_receivable" class="form-label fw-semibold">Account Receivable (₱)</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="new_account_receivable" name="account_receivable" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="new_remarks" class="form-label fw-semibold">Remarks</label>
                            <input type="text" class="form-control" id="new_remarks" name="remarks" placeholder="Optional remarks...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <div class="me-auto text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Fields marked <span class="text-danger">*</span> are required.
                    </div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);">
                        <i class="bi bi-check-circle me-1"></i> Save Sale
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END NEW SALE MODAL =================== -->

<script>
document.getElementById('newSaleModal').addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('newSaleForm');
    form.reset();
    form.classList.remove('was-validated');
});
document.getElementById('newSaleForm').addEventListener('submit', function (e) {
    if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    this.classList.add('was-validated');
});
</script>
