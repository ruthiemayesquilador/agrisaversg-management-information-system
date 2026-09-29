<!-- ===================== VIEW STOCK MODAL ===================== -->
<div class="modal fade" id="viewStockModal" tabindex="-1" aria-labelledby="viewStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                <h5 class="modal-title text-white fw-bold" id="viewStockModalLabel">
                    <i class="bi bi-eye me-2"></i> View Stock
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <div class="row g-3">
                    <div class="col-12 text-center mb-3">
                        <img id="vs_image" src="" alt="Stock Image" class="img-fluid" style="max-height: 200px; object-fit: cover; border-radius: 8px;">
                    </div>
                    <div class="col-12 text-center mb-2">
                        <div class="d-flex justify-content-between align-items-flex-start mb-1" style="font-size:12px; font-weight:700; color:#1f2937; max-width:220px; margin:0 auto;">
                            <div id="vs-bar-left" style="line-height:1.1;"></div>
                            <span id="vs-bar-right"></span>
                        </div>
                        <div id="vs-bar-part-name" style="font-size:11px; font-weight:600; color:#4b5563; max-width:220px; margin:0 auto 2px; text-align:left;"></div>
                        <svg id="vs_barcode" style="max-width:300px; display:block; margin:0 auto;"></svg>
                        <p id="vs_bar_empty" class="text-muted small mb-0" style="display:none;"><i class="bi bi-upc me-1"></i>No SKU set &mdash; barcode unavailable</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted small mb-1 fw-semibold">SKU</p>
                        <p class="fw-semibold fs-6 mb-0" id="vs_sku">—</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted small mb-1 fw-semibold">No.</p>
                        <p class="fw-semibold fs-6 mb-0" id="vs_no">—</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted small mb-1 fw-semibold">Part No.</p>
                        <p class="fw-semibold fs-6 mb-0" id="vs_part_no">—</p>
                    </div>
                    <div class="col-12">
                        <p class="text-muted small mb-1 fw-semibold">Part Name</p>
                        <p class="fw-semibold fs-6 mb-0" id="vs_part_name">—</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted small mb-1 fw-semibold">Price</p>
                        <p class="fw-semibold fs-6 mb-0" id="vs_price">—</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted small mb-1 fw-semibold">Stocks</p>
                        <p class="fw-semibold fs-6 mb-0" id="vs_stocks">—</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted small mb-1 fw-semibold">Beg. Inv.</p>
                        <p class="fw-semibold fs-6 mb-0" id="vs_beg_inv">—</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1 fw-semibold">Item's In</p>
                        <p class="fw-semibold fs-6 mb-0" id="vs_items_in">—</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1 fw-semibold">Item's Out</p>
                        <p class="fw-semibold fs-6 mb-0" id="vs_items_out">—</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1 fw-semibold">Remarks (In)</p>
                        <p class="fw-semibold fs-6 mb-0" id="vs_remarks_in">—</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1 fw-semibold">Remarks (Out)</p>
                        <p class="fw-semibold fs-6 mb-0" id="vs_remarks_out">—</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="vs_print_btn">
                    <i class="bi bi-printer me-1"></i> Print Label
                </button>
            </div>
        </div>
    </div>
</div>
<!-- =================== END VIEW STOCK MODAL =================== -->

<script>
document.getElementById('viewStockModal').addEventListener('shown.bs.modal', function (e) {
    const btn = e.relatedTarget;
    if (!btn) return;
    const d = btn.dataset;
    const sku = (d.sku || '').trim();
    const partNo = (d.partNo || '').trim();
    const skuLabelBase = sku || partNo || 'N/A';
    const labelRightHeader = (d.labelBrand || '').trim() || 'AGRISG';

    document.getElementById('vs_image').src               = d.image      || '';
    document.getElementById('vs_sku').textContent          = skuLabelBase + ' - SKU AGRISG';
    document.getElementById('vs-bar-left').textContent     = skuLabelBase + ' - SKU';
    document.getElementById('vs-bar-part-name').textContent= (d.partName || '').trim();
    document.getElementById('vs-bar-right').textContent    = labelRightHeader;
    document.getElementById('vs_no').textContent           = d.no         || '—';
    document.getElementById('vs_part_no').textContent      = partNo       || '—';
    document.getElementById('vs_part_name').textContent    = d.partName   || '—';
    document.getElementById('vs_price').textContent        = d.price      ? '₱' + d.price : '—';
    document.getElementById('vs_stocks').textContent       = d.stocks     || '—';
    document.getElementById('vs_beg_inv').textContent      = d.begInv     || '—';
    document.getElementById('vs_items_in').textContent     = d.itemsIn    || '—';
    document.getElementById('vs_items_out').textContent    = d.itemsOut   || '—';
    document.getElementById('vs_remarks_in').textContent   = d.remarksIn  || '—';
    document.getElementById('vs_remarks_out').textContent  = d.remarksOut || '—';

    // Render barcode
    const normalizeUpc12 = (value) => {
        const digits = (value || '').toString().replace(/\D/g, '');
        if (digits.length === 12) {
            return digits;
        }
        if (digits.length === 13 && digits.charAt(0) === '0') {
            return digits.slice(1);
        }
        return '';
    };

    const upc    = normalizeUpc12(d.upc || '');
    const barcodeType = (d.barcodeType || '').trim();
    const systemUpc = (typeof window.generateSystemUpcFromSku === 'function')
        ? normalizeUpc12(window.generateSystemUpcFromSku(sku || partNo))
        : '';
    const barcodeValue = upc || systemUpc || sku || partNo;
    const barcodeFormat = (barcodeType === 'upc' || /^\d{12}$/.test(barcodeValue))
        ? 'upc'
        : 'CODE128';
    const barSvg = document.getElementById('vs_barcode');
    const barMsg = document.getElementById('vs_bar_empty');
    barSvg.innerHTML = '';
    if (barcodeValue) {
        barMsg.style.display = 'none';
        barSvg.style.display = '';
        JsBarcode('#vs_barcode', barcodeValue, {
            format: barcodeFormat,
            width: 2,
            height: 55,
            margin: 0,
            marginTop: 0,
            marginBottom: 0,
            textMargin: 0,
            displayValue: true,
            fontSize: 13
        });
    } else {
        barSvg.style.display = 'none';
        barMsg.style.display = '';
    }

    // Wire up Print Label button
    const price = d.price ? '₱' + d.price : '';
    document.getElementById('vs_print_btn').onclick = function () {
        printItemLabel(d.partName || '', sku || partNo || '', price, {
            dcsCode: d.sku || d.partNo || '',
            vendorCode: 'AGRISG - AGRI SAVERS G',
            description1: d.labelShortName || d.partName || '',
            description2: d.partNo || '',
            upc: upc || systemUpc,
            alu: d.no || '',
            partNo: d.partNo || '',
            brandCode: d.labelBrand || 'AGRISG',
            barcodeNumber: upc || systemUpc,
            barcodeType: barcodeType || ''
        });
    };
});
</script>
