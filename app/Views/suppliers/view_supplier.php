<!-- ===================== VIEW SUPPLIER MODAL ===================== -->
<div class="modal fade" id="viewSupplierModal" tabindex="-1" aria-labelledby="viewSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                <h5 class="modal-title text-white fw-bold" id="viewSupplierModalLabel">
                    <i class="bi bi-eye me-2"></i> View Supplier
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <div class="row g-3">
                    <div class="col-12">
                        <p class="text-muted small mb-1 fw-semibold">Supplier Name</p>
                        <p class="fw-semibold fs-6 mb-0" id="vsup_supplier">—</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1 fw-semibold">Brand</p>
                        <p class="fw-semibold fs-6 mb-0" id="vsup_brand_name">—</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1 fw-semibold">Importer</p>
                        <p class="fw-semibold fs-6 mb-0" id="vsup_importer">—</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- =================== END VIEW SUPPLIER MODAL =================== -->

<script>
document.getElementById('viewSupplierModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    if (!btn) return;
    const d = btn.dataset;
    document.getElementById('vsup_supplier').textContent   = d.supplier  || '—';
    document.getElementById('vsup_brand_name').textContent = d.brandName || '—';
    const imp = d.importer || '';
    const vsupImp = document.getElementById('vsup_importer');
    vsupImp.textContent = imp || '—';
    vsupImp.className   = 'fw-semibold fs-6 mb-0';
    if (imp === 'Local')    vsupImp.innerHTML = '<span class="badge rounded-pill bg-success">Local</span>';
    if (imp === 'Overseas') vsupImp.innerHTML = '<span class="badge rounded-pill bg-primary">Overseas</span>';
});
</script>
