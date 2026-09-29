<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<div class="container-fluid px-3 py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 no-print">
        <h4 class="mb-0"><i class="bi bi-upc-scan me-2"></i>UPC Sticker Labels</h4>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" onclick="window.close()"><i class="bi bi-x-lg me-1"></i>Close</button>
            <button class="btn btn-primary" id="btnPrintAllLabels"><i class="bi bi-printer me-1"></i>Print All Labels</button>
        </div>
    </div>

    <?php if (empty($labels)): ?>
        <div class="alert alert-warning">No UPC labels available to print.</div>
    <?php else: ?>
        <div class="text-muted small mb-2 no-print">Showing <?= count($labels) ?> stickers using the same format as Products print label.</div>
        <div class="labels-grid" id="labelsGrid">
            <?php foreach ($labels as $item): ?>
                <?php
                    $name = (string) ($item['part_name'] ?? '');
                    $description = trim((string) ($item['description'] ?? ''));
                    $displayName = trim(trim($name) . ' ' . $description);
                    $sku = (string) ($item['sku'] ?? '');
                    $partNo = (string) ($item['part_no'] ?? '');
                    $upc = (string) ($item['upc'] ?? '');
                    $labelSku = $sku !== '' ? $sku : $partNo;
                ?>
                <div class="label-item" data-label-item="1"
                    data-name="<?= esc($displayName !== '' ? $displayName : $name, 'attr') ?>"
                    data-sku="<?= esc($labelSku, 'attr') ?>"
                    data-upc="<?= esc($upc, 'attr') ?>"
                    data-partno="<?= esc($partNo, 'attr') ?>">
                    <div class="sticker-label">
                        <div class="sticker-top">
                            <div class="sticker-top-left js-top-left"></div>
                            <div class="sticker-top-right js-top-right"></div>
                        </div>
                        <div class="sticker-part-name js-part-name"></div>
                        <div class="sticker-barcode-wrap js-barcode-wrap">
                            <svg class="js-barcode"></svg>
                        </div>
                        <div class="sticker-empty js-empty" style="display:none;">No SKU/Barcode value available</div>
                        <div class="sticker-bottom-code js-bottom-code"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    :root {
        --sticker-w: 40mm;
        --sticker-h: 30mm;
        --sticker-pad-x: 0.8mm;
        --sticker-pad-y: 0.8mm;
        --sticker-content-w: calc(var(--sticker-w) - (var(--sticker-pad-x) * 2));
        --sticker-inner-pad: 0.6mm;
        --sticker-inner-content-w: 26mm;
    }

    .labels-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(34mm, 1fr));
        gap: 10px;
    }

    .label-item {
        border: 1px dashed #d1d5db;
        border-radius: 8px;
        padding: 8px;
        background: #fff;
        break-inside: avoid;
    }

    .sticker-label {
        width: var(--sticker-w);
        height: var(--sticker-h);
        padding: var(--sticker-pad-y) var(--sticker-pad-x);
        background: #fff;
        border: 1px solid #ddd;
        margin: 0 auto;
    }

    .sticker-top {
        display: flex;
        align-items: flex-start;
        width: var(--sticker-inner-content-w);
        margin: 0 auto 0.2mm;
        padding: 0;
        box-sizing: border-box;
        font-size: 2.6mm;
        font-weight: 700;
        color: #1f2937;
    }

    .sticker-top-left {
        line-height: 1.1;
        max-width: 68%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sticker-top-right {
        white-space: nowrap;
        margin-left: auto;
        text-align: right;
    }

    .sticker-part-name {
        font-size: 2.2mm;
        font-weight: 600;
        color: #4b5563;
        width: var(--sticker-inner-content-w);
        margin: 0 auto 0.3mm;
        padding: 0;
        box-sizing: border-box;
        line-height: 1.18;
        min-height: 4.8mm;
        max-height: 4.8mm;
        text-align: left;
        overflow: hidden;
    }

    .sticker-barcode-wrap {
        display: flex;
        justify-content: center;
        align-items: center;
        width: var(--sticker-inner-content-w);
        height: 10mm;
        margin: 0 auto;
        padding: 0;
        box-sizing: border-box;
    }

    .js-barcode {
        width: 100%;
        height: 100%;
        display: block;
    }

    .sticker-bottom-code {
        text-align: center;
        font-size: 2.4mm;
        line-height: 1.05;
        letter-spacing: 0.1mm;
        font-weight: 700;
        margin-top: 0.2mm;
        white-space: nowrap;
    }

    .sticker-empty {
        text-align: center;
        color: #777;
        font-size: 2.4mm;
        padding-top: 6mm;
    }

    @media print {
        @page {
            size: var(--sticker-w) var(--sticker-h);
            margin: 0;
        }

        .left-side-bar,
        .header,
        .anitala-footer,
        .menu-icon,
        .no-print {
            display: none !important;
            visibility: hidden !important;
        }

        .main-container,
        .content-wrapper,
        .page-content,
        .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        .no-print {
            display: none !important;
        }

        body {
            background: #fff;
            margin: 0 !important;
            padding: 0 !important;
        }

        .labels-grid {
            display: block;
            gap: 0;
        }

        .label-item {
            border: 0;
            border-radius: 0;
            padding: 0;
            width: var(--sticker-w);
            height: var(--sticker-h);
            margin: 0;
            break-inside: avoid;
            page-break-after: always;
            page-break-inside: avoid;
        }

        .sticker-label {
            border: none;
            margin: 0;
            width: var(--sticker-w);
            height: var(--sticker-h);
            box-shadow: none;
        }

        .label-item:last-child {
            page-break-after: auto;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function normalizeUpcA(value) {
            const digits = (value || '').toString().replace(/\D/g, '');
            if (digits.length === 12) return digits;
            if (digits.length === 13 && digits.charAt(0) === '0') return digits.slice(1);
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
        }

        function buildLabelData(row) {
            const cleanName = (row.getAttribute('data-name') || '').trim();
            const cleanSku = (row.getAttribute('data-sku') || '').trim();
            const partNo = (row.getAttribute('data-partno') || '').trim();
            const upc = normalizeUpcA(row.getAttribute('data-upc') || '');

            const rightHeader = 'AGRISG';
            const headerSku = (cleanSku || partNo || '').toUpperCase();
            const topLeftText = (headerSku !== '' ? (headerSku + ' - SKU') : 'ITEM - SKU');
            const partNameText = (cleanName || '').toUpperCase();

            const generatedUpc = (typeof window.generateSystemUpcFromSku === 'function')
                ? normalizeUpcA(window.generateSystemUpcFromSku(cleanSku || headerSku))
                : '';

            const barcodeValue = upc || generatedUpc || cleanSku || partNo;
            const normalizedUpcValue = normalizeUpcA(barcodeValue);
            const finalBarcodeValue = normalizedUpcValue !== '' ? normalizedUpcValue : barcodeValue;
            const onlyDigits = finalBarcodeValue.replace(/\D/g, '');
            const barcodeFormat = normalizedUpcValue !== '' ? 'upc' : 'CODE128';

            const humanText = onlyDigits.length === 12
                ? `${onlyDigits.slice(0, 1)} ${onlyDigits.slice(1, 6)} ${onlyDigits.slice(6, 11)} ${onlyDigits.slice(11, 12)}`
                : (finalBarcodeValue ? finalBarcodeValue.toUpperCase() : '');

            return { topLeftText, rightHeader, partNameText, barcodeValue: finalBarcodeValue, barcodeFormat, humanText };
        }

        function renderSticker(row) {
            const data = buildLabelData(row);

            const topLeftEl = row.querySelector('.js-top-left');
            const topRightEl = row.querySelector('.js-top-right');
            const partNameEl = row.querySelector('.js-part-name');
            const barcodeWrapEl = row.querySelector('.js-barcode-wrap');
            const barcodeEl = row.querySelector('.js-barcode');
            const emptyEl = row.querySelector('.js-empty');
            const bottomCodeEl = row.querySelector('.js-bottom-code');

            topLeftEl.textContent = data.topLeftText || 'ITEM LABEL';
            topRightEl.textContent = data.rightHeader || 'AGRISG';
            partNameEl.textContent = data.partNameText || '';

            if (data.barcodeValue) {
                const useNativeUpcText = (data.barcodeFormat || '').toLowerCase() === 'upc';
                const barcodeWidth = useNativeUpcText ? 1.4 : 1.25;
                const barcodeHeight = useNativeUpcText ? 50 : 46;
                barcodeWrapEl.style.display = 'flex';
                emptyEl.style.display = 'none';

                if (typeof window.JsBarcode === 'function') {
                    try {
                        JsBarcode(barcodeEl, data.barcodeValue, {
                            format: data.barcodeFormat || 'CODE128',
                            width: barcodeWidth,
                            height: barcodeHeight,
                            margin: 2,
                            marginTop: 0,
                            marginBottom: 0,
                            marginLeft: 2,
                            marginRight: 2,
                            displayValue: useNativeUpcText,
                            textMargin: useNativeUpcText ? 1 : 0,
                            fontSize: useNativeUpcText ? 12 : 11
                        });
                    } catch (e) {
                        barcodeWrapEl.style.display = 'none';
                        emptyEl.style.display = 'block';
                        bottomCodeEl.style.display = 'block';
                        bottomCodeEl.textContent = data.humanText || data.barcodeValue || '';
                    }
                } else {
                    barcodeWrapEl.style.display = 'none';
                    emptyEl.style.display = 'block';
                    bottomCodeEl.style.display = 'block';
                    bottomCodeEl.textContent = data.humanText || data.barcodeValue || '';
                }

                if (useNativeUpcText) {
                    bottomCodeEl.style.display = 'none';
                } else {
                    bottomCodeEl.style.display = 'block';
                    bottomCodeEl.textContent = data.humanText || '';
                }
            } else {
                barcodeWrapEl.style.display = 'none';
                bottomCodeEl.style.display = 'none';
                emptyEl.style.display = 'block';
            }
        }

        document.querySelectorAll('[data-label-item="1"]').forEach(function (row) {
            try {
                renderSticker(row);
            } catch (e) {
                const emptyEl = row.querySelector('.js-empty');
                if (emptyEl) {
                    emptyEl.style.display = 'block';
                }
            }
        });

        const printAllBtn = document.getElementById('btnPrintAllLabels');
        if (printAllBtn) {
            printAllBtn.addEventListener('click', function () {
                window.print();
            });
        }

        <?php if (!empty($autoPrint)): ?>
        setTimeout(function () { window.print(); }, 120);
        <?php endif; ?>
    });
</script>

<?php $this->endSection(); ?>
