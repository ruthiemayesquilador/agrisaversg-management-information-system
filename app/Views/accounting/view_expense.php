<!-- ===================== VIEW EXPENSE MODAL ===================== -->
<div class="modal fade" id="viewExpenseModal" tabindex="-1" aria-labelledby="viewExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                <h5 class="modal-title text-white fw-bold" id="viewExpenseModalLabel">
                    <i class="bi bi-eye me-2"></i> View Expense
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="text-muted small mb-1 fw-semibold">Date</p>
                        <p class="fw-semibold fs-6 mb-0" id="ve_date">—</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1 fw-semibold">Amount</p>
                        <p class="fw-semibold fs-6 mb-0" id="ve_amount">—</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1 fw-semibold">Type</p>
                        <p class="fw-semibold fs-6 mb-0" id="ve_type">—</p>
                    </div>
                    <div class="col-12">
                        <p class="text-muted small mb-1 fw-semibold">Description</p>
                        <p class="fw-semibold fs-6 mb-0" id="ve_description">—</p>
                    </div>
                    <div class="col-12">
                        <p class="text-muted small mb-1 fw-semibold">Remarks</p>
                        <p class="fw-semibold fs-6 mb-0" id="ve_remarks">—</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- =================== END VIEW EXPENSE MODAL =================== -->

<script>
document.getElementById('viewExpenseModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    if (!btn) return;
    const d = btn.dataset;
    document.getElementById('ve_date').textContent        = d.date        || '—';
    document.getElementById('ve_amount').textContent      = d.amount      ? '₱' + d.amount : '—';
    document.getElementById('ve_type').textContent        = d.type        ? (d.type === 'owner_draw' ? 'Owner Draw (Cash Remit)' : 'Operating Expense') : '—';
    document.getElementById('ve_description').textContent = d.description || '—';
    document.getElementById('ve_remarks').textContent     = d.remarks     || '—';
});
</script>
