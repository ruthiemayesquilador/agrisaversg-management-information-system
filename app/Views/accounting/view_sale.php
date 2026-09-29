<!-- ===================== VIEW SALE MODAL ===================== -->
<div class="modal fade" id="viewSaleModal" tabindex="-1" aria-labelledby="viewSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                <h5 class="modal-title text-white fw-bold" id="viewSaleModalLabel">
                    <i class="bi bi-eye me-2"></i> View Sale
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="text-muted small mb-1 fw-semibold">CSI No.</p>
                        <p class="fw-semibold fs-6 mb-0" id="vs_csi">—</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1 fw-semibold">Date</p>
                        <p class="fw-semibold fs-6 mb-0" id="vs_date">—</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1 fw-semibold">Cash Sales</p>
                        <p class="fw-semibold fs-6 mb-0" id="vs_cash">—</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1 fw-semibold">Payment Method</p>
                        <p class="fw-semibold fs-6 mb-0" id="vs_method">—</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1 fw-semibold">Account Receivable</p>
                        <p class="fw-semibold fs-6 mb-0" id="vs_ar">—</p>
                    </div>
                    <div class="col-12">
                        <p class="text-muted small mb-1 fw-semibold">Remarks</p>
                        <div id="vs_remarks" style="white-space:pre-line; font-size:14px; background:#f8f9fa; border-radius:8px; padding:10px 14px; min-height:36px;">—</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- =================== END VIEW SALE MODAL =================== -->

<script>
document.getElementById('viewSaleModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    if (!btn) return;
    const d = btn.dataset;

    const formatPeso = (value) => {
        const parsed = Number.parseFloat(value ?? '0');
        const safe = Number.isFinite(parsed) ? parsed : 0;
        return '₱' + safe.toFixed(2);
    };

    document.getElementById('vs_csi').textContent     = d.csi     || '—';
    document.getElementById('vs_date').textContent    = d.date    || '—';
    document.getElementById('vs_cash').textContent    = formatPeso(d.cash);
    document.getElementById('vs_method').textContent  = d.method  || '—';
    document.getElementById('vs_ar').textContent      = formatPeso(d.ar);
    document.getElementById('vs_remarks').textContent = d.remarks || '—';
});
</script>
