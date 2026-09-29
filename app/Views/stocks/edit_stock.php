<!-- ===================== EDIT STOCK MODAL ===================== -->
<div class="modal fade" id="editStockModal" tabindex="-1" aria-labelledby="editStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);">
                <h5 class="modal-title text-white fw-bold" id="editStockModalLabel">
                    <i class="bi bi-pencil me-2"></i> Edit Stock
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('stocks/update') ?>" method="post" id="editStockForm" class="needs-validation" novalidate enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" id="es_id" name="id">
                <input type="hidden" id="es_product_id" name="product_id">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 1.5rem;">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="es_image" class="form-label fw-semibold">Stock Image</label>
                            <input type="file" class="form-control" id="es_image" name="stock_image" accept="image/*">
                            <small class="text-muted">Accepted formats: JPG, JPEG, PNG, WEBP, GIF. Leave empty to keep current image.</small>
                        </div>
                        <div class="col-md-4">
                            <label for="es_sku" class="form-label fw-semibold">SKU <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="es_sku" name="sku" required>
                            <div class="invalid-feedback">SKU is required.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="es_no" class="form-label fw-semibold">No.</label>
                            <input type="text" class="form-control" id="es_no" name="no">
                        </div>
                        <div class="col-md-4">
                            <label for="es_part_no" class="form-label fw-semibold">Part No.</label>
                            <input type="text" class="form-control" id="es_part_no" name="part_no">
                        </div>
                        <div class="col-12">
                            <label for="es_part_name" class="form-label fw-semibold">Part Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="es_part_name" name="part_name" required>
                            <div class="invalid-feedback">Part Name is required.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="es_price" class="form-label fw-semibold">Price (₱)</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="es_price" name="price" placeholder="0.00">
                            </div>
                            <small class="text-muted" id="es_price_help">When editing a linked stock, this updates the product's price (applies to all stocks of the product).</small>
                        </div>
                        <div class="col-md-6">
                            <label for="es_stocks" class="form-label fw-semibold">Stocks</label>
                            <input type="number" min="0" class="form-control" id="es_stocks" name="stocks" placeholder="0" style="font-weight: 600;">
                            <small class="text-muted">You can edit the total directly, or change the inventory fields below to recalculate it.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="es_items_in" class="form-label fw-semibold">Item's In</label>
                            <input type="number" min="0" class="form-control" id="es_items_in" name="items_in">
                        </div>
                        <div class="col-md-6">
                            <label for="es_items_out" class="form-label fw-semibold">Item's Out</label>
                            <input type="number" min="0" class="form-control" id="es_items_out" name="items_out">
                        </div>
                        <div class="col-md-6">
                            <label for="es_remarks_in" class="form-label fw-semibold">Remarks (In)</label>
                            <textarea class="form-control" id="es_remarks_in" name="remarks_in" rows="2" placeholder="e.g. Shipment New Arrival, Packing list reference"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="es_remarks_out" class="form-label fw-semibold">Remarks (Out)</label>
                            <textarea class="form-control" id="es_remarks_out" name="remarks_out" rows="2" placeholder="e.g. Delivered to Pili branch"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <div class="me-auto text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Fields marked <span class="text-danger">*</span> are required.
                    </div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);">
                        <i class="bi bi-save me-1"></i> Update Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END EDIT STOCK MODAL =================== -->

<script>
function computeEditStockTotals() {
    const itemsIn = document.getElementById('es_items_in');
    const itemsOut = document.getElementById('es_items_out');
    const stocks = document.getElementById('es_stocks');
    if (!itemsIn || !itemsOut || !stocks) {
        return;
    }
    const i = parseInt(itemsIn.value, 10) || 0;
    const o = parseInt(itemsOut.value, 10) || 0;
    stocks.value = Math.max(0, i - o);
}

function bindEditStockTotals() {
    const itemsIn = document.getElementById('es_items_in');
    const itemsOut = document.getElementById('es_items_out');
    if (!itemsIn || !itemsOut) {
        return;
    }
    ['input', 'change'].forEach(function (evt) {
        itemsIn.addEventListener(evt, computeEditStockTotals);
        itemsOut.addEventListener(evt, computeEditStockTotals);
    });
}

bindEditStockTotals();

document.getElementById('editStockModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    if (!btn) return;
    const d = btn.dataset;
    const linkedProductId = (d.productId || '').toString().trim();

    document.getElementById('es_id').value        = d.id       || '';
    document.getElementById('es_product_id').value = linkedProductId;
    document.getElementById('es_sku').value       = d.sku      || '';
    document.getElementById('es_no').value        = d.no       || '';
    document.getElementById('es_part_no').value   = d.partNo   || '';
    document.getElementById('es_part_name').value = d.partName || '';
    document.getElementById('es_price').value     = d.price    || '';
    document.getElementById('es_stocks').value    = d.stocks   || '';
    document.getElementById('es_items_in').value  = d.itemsIn  || '';
    document.getElementById('es_items_out').value = d.itemsOut || '';
    document.getElementById('es_remarks_in').value  = d.remarksIn  || '';
    document.getElementById('es_remarks_out').value = d.remarksOut || '';

    const isLinked = linkedProductId !== '';
    ['es_sku', 'es_part_no', 'es_part_name'].forEach(function (fieldId) {
        const input = document.getElementById(fieldId);
        if (!input) return;

        input.readOnly = isLinked;
        input.style.backgroundColor = isLinked ? '#f0f0f0' : '';

        if (isLinked) {
            input.title = 'Synced from linked product';
        } else {
            input.removeAttribute('title');
        }
    });

    computeEditStockTotals();
});
document.getElementById('editStockModal').addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('editStockForm');
    form.reset();
    form.classList.remove('was-validated');
    document.getElementById('es_product_id').value = '';
    ['es_sku', 'es_part_no', 'es_part_name'].forEach(function (fieldId) {
        const input = document.getElementById(fieldId);
        if (!input) return;
        input.readOnly = false;
        input.style.backgroundColor = '';
        input.removeAttribute('title');
    });
});
document.getElementById('editStockForm').addEventListener('submit', function (e) {
    if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    this.classList.add('was-validated');
});
</script>