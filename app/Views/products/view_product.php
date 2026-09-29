<!-- ===================== VIEW PRODUCT MODAL ===================== -->
<div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #2d8bba 100%);">
                <h5 class="modal-title text-white fw-bold" id="viewProductModalLabel">
                    <i class="fas fa-eye me-2"></i> Product Details
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 75vh; overflow-y: auto; padding: 0;">
                <div class="row g-0">
                    <!-- Left: Image -->
                    <div class="col-md-4 d-flex align-items-center justify-content-center" style="background: #fff8f3; padding: 24px; border-right: 1px solid #f0e8df;">
                        <img id="vp-img" src="" alt="Product Image" style="width:100%; max-height:260px; object-fit:contain; border-radius:8px;">
                    </div>
                    <!-- Right: Details -->
                    <div class="col-md-8" style="padding: 28px 28px 20px;">
                        <h4 id="vp-name" class="fw-bold mb-1" style="color:#1a1a1a;"></h4>
                        <p id="vp-sku" class="text-muted mb-3" style="font-size:13px;"></p>

                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span id="vp-price" class="fw-bold" style="font-size:28px; color:#FF8C42;"></span>
                            <span id="vp-sold" class="badge" style="background:linear-gradient(135deg,#10B981,#059669); font-size:12px; padding:5px 12px; border-radius:12px;"></span>
                            <span id="vp-status-badge" class="badge" style="font-size:12px; padding:5px 12px; border-radius:12px;"></span>
                        </div>

                        <table class="table table-sm mb-3" style="font-size:14px;">
                            <tbody>
                                <tr>
                                    <td class="text-muted fw-semibold" style="width:40%; border:none; padding: 5px 0;">Category</td>
                                    <td id="vp-category" style="border:none; padding: 5px 0;"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold" style="border:none; padding: 5px 0;">Stock</td>
                                    <td id="vp-stock" style="border:none; padding: 5px 0;"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold" style="border:none; padding: 5px 0;">Supplier</td>
                                    <td id="vp-supplier" style="border:none; padding: 5px 0;"></td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Barcode -->
                        <div class="text-center mt-2 mb-1">
                            <div class="d-flex justify-content-between align-items-flex-start mb-1" style="font-size:12px; font-weight:700; color:#1f2937; max-width:220px; margin:0 auto;">
                                <div id="vp-bar-left" style="line-height:1.1;"></div>
                                <span id="vp-bar-right"></span>
                            </div>
                            <div id="vp-bar-part-name" style="font-size:11px; font-weight:600; color:#4b5563; max-width:220px; margin:0 auto 2px; text-align:left;"></div>
                            <svg id="vp-barcode" style="max-width:300px; display:block; margin:0 auto;"></svg>
                            <p id="vp-bar-empty" class="text-muted small mb-0" style="display:none;"><i class="bi bi-upc me-1"></i>No SKU set &mdash; barcode unavailable</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="vp-print-btn">
                    <i class="bi bi-printer me-1"></i> Print Label
                </button>
            </div>
        </div>
    </div>
</div>
<!-- ================== END VIEW PRODUCT MODAL ================== -->

<script>
document.getElementById('viewProductModal').addEventListener('shown.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const d = btn.dataset;
    const partName = (d.name || '').trim();
    const description = (d.description || '').trim();
    const displayName = [partName, description].filter(Boolean).join(' ');
    const sku = (d.sku || '').trim();
    const normalizeBarcodeType = (value) => {
        const cleaned = (value || '').toString().trim().toLowerCase().replace(/[^a-z0-9]/g, '');
        if (cleaned === 'upc' || cleaned === 'upca') {
            return 'upc';
        }
        return '';
    };

    const normalizeUpcA = (value) => {
        const digits = (value || '').toString().replace(/\D/g, '');
        if (digits.length === 12) {
            return digits;
        }
        if (digits.length === 13 && digits.charAt(0) === '0') {
            return digits.slice(1);
        }
        if (digits.length === 11) {
            let oddSum = 0;
            let evenSum = 0;
            for (let i = 0; i < digits.length; i++) {
                const n = parseInt(digits.charAt(i), 10);
                if ((i % 2) === 0) {
                    oddSum += n;
                } else {
                    evenSum += n;
                }
            }
            const checkDigit = (10 - (((oddSum * 3) + evenSum) % 10)) % 10;
            return digits + String(checkDigit);
        }
        return '';
    };

    const upc = normalizeUpcA(d.upc || '');
    const barcodeType = normalizeBarcodeType(d.barcodeType || '');
    const partNo = (d.partno || '').trim();
    const skuLabelBase = sku || partNo || 'N/A';
    const labelRightHeader = (d.labelBrand || '').trim() || 'AGRISG';
    const skuText = skuLabelBase + ' - SKU AGRISG';
    const partNoText = partNo ? ('Part No: ' + partNo) : 'Part No: N/A';

    document.getElementById('vp-img').src               = d.img      || '';
    document.getElementById('vp-name').textContent      = displayName || '';
    document.getElementById('vp-sku').textContent       = skuText + ' | ' + partNoText;
    document.getElementById('vp-price').textContent     = d.price    || '';
    document.getElementById('vp-sold').textContent      = d.sold     || '';
    document.getElementById('vp-category').textContent  = d.category || '';
    document.getElementById('vp-stock').textContent     = d.stock    || '';
    document.getElementById('vp-supplier').textContent  = d.supplier || '';
    document.getElementById('vp-bar-left').textContent  = skuLabelBase + ' - SKU';
    document.getElementById('vp-bar-part-name').textContent = displayName || '';
    document.getElementById('vp-bar-right').textContent = labelRightHeader;

    const statusBadge = document.getElementById('vp-status-badge');
    const status = d.status || 'Available';
    statusBadge.textContent = status;
    statusBadge.style.background = status === 'Available'
        ? 'linear-gradient(135deg,#3B82F6,#2563EB)'
        : status === 'Low Stock'
            ? 'linear-gradient(135deg,#F59E0B,#D97706)'
            : '#FEE2E2';
    statusBadge.style.color = status === 'Out of Stock' ? '#B91C1C' : '#FFFFFF';

    // Barcode
    const barSvg = document.getElementById('vp-barcode');
    const barMsg = document.getElementById('vp-bar-empty');
    const systemUpc = (typeof window.generateSystemUpcFromSku === 'function')
        ? normalizeUpcA(window.generateSystemUpcFromSku(sku || partNo))
        : '';
    const barcodeValue = upc || systemUpc || sku || partNo;
    const normalizedUpcValue = normalizeUpcA(barcodeValue);
    const isUpc = (barcodeType === 'upc' && normalizedUpcValue !== '') || /^\d{12}$/.test(barcodeValue);
    const barcodeFormat = isUpc ? 'upc' : 'CODE128';
    const finalBarcodeValue = isUpc ? (normalizedUpcValue || barcodeValue) : barcodeValue;
    barSvg.innerHTML = '';
    if (barcodeValue) {
        barMsg.style.display = 'none';
        barSvg.style.display = '';
        JsBarcode('#vp-barcode', finalBarcodeValue, {
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
    const price = d.price || '';
    document.getElementById('vp-print-btn').onclick = function () {
        printItemLabel(displayName || d.name || '', sku || partNo || '', price, {
            dcsCode: d.sku || d.partno || '',
            vendorCode: 'AGRISG - AGRI SAVERS G',
            description1: d.labelShortName || displayName || d.name || '',
            description2: d.partno || '',
            upc: upc || systemUpc,
            alu: d.partno || '',
            partNo: d.partno || '',
            brandCode: d.labelBrand || 'AGRISG',
            barcodeNumber: upc || systemUpc,
            barcodeType: barcodeType || ''
        });
    };
});
</script>
