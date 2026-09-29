<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Agri Savers G' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        :root {
            --app-zoom: 1;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            zoom: var(--app-zoom);
        }

        .main-container {
            display: flex;
            min-height: calc(100vh / var(--app-zoom));
        }

        .left-side-bar {
            width: 280px;
            height: auto;
            min-height: calc(100vh / var(--app-zoom));
            background: #ff751f;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow-y: auto;
            transition: left 0.3s ease;
        }

        .left-side-bar.hidden {
            left: -280px;
        }

        .left-side-bar::-webkit-scrollbar {
            width: 8px;
        }

        .left-side-bar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .left-side-bar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 4px;
        }

        .content-wrapper {
            flex: 1;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }

        .content-wrapper.full-width {
            margin-left: 0;
            width: 100%;
        }

        .header {
            background: white;
            padding: 12px 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 999;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-content {
            padding: 20px;
            min-height: calc(100vh - 80px);
            width: 100%;
            overflow-x: hidden;
        }

        /* Ensure table fits within available space when sidebar is shown */
        .page-content .container-fluid {
            max-width: 100%;
            padding-left: 15px;
            padding-right: 15px;
        }

        .page-content .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 768px) {
            .left-side-bar {
                width: 220px;
            }

            .left-side-bar.hidden {
                left: -220px;
            }

            .content-wrapper {
                margin-left: 0;
            }

            .page-content {
                padding: 15px;
            }

            .header {
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            .left-side-bar {
                width: 200px;
            }

            .left-side-bar.hidden {
                left: -200px;
            }
        }

        /* ── Global Search Component ── */
        .manage-search-group {
            width: fit-content;
            min-width: 300px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
        }

        .manage-search-input {
            border: none !important;
            font-size: 0.9rem;
            box-shadow: none !important;
            background: transparent;
        }

        .manage-search-input:focus {
            box-shadow: none !important;
            outline: none;
        }

        .manage-search-btn {
            border: none;
            border-left: 1px solid #dee2e6;
            background: #fff;
            color: #888;
            font-size: 0.9rem;
            padding: 0.375rem 0.7rem;
        }

        .manage-search-btn:hover {
            background: #f5f5f5;
            color: #555;
        }

        /* DataTables-like sorting headers */
        .js-sortable-table thead th.sorting,
        .js-sortable-table thead th.sorting_asc,
        .js-sortable-table thead th.sorting_desc {
            position: relative;
            padding-right: 1.35rem;
            cursor: pointer;
            user-select: none;
        }

        .js-sortable-table thead th.sorting::before,
        .js-sortable-table thead th.sorting::after,
        .js-sortable-table thead th.sorting_asc::before,
        .js-sortable-table thead th.sorting_asc::after,
        .js-sortable-table thead th.sorting_desc::before,
        .js-sortable-table thead th.sorting_desc::after {
            position: absolute;
            right: 0.5rem;
            font-size: 0.65rem;
            line-height: 1;
            color: #b8c2cc;
            pointer-events: none;
        }

        .js-sortable-table thead th.sorting::before,
        .js-sortable-table thead th.sorting_asc::before,
        .js-sortable-table thead th.sorting_desc::before {
            content: '▲';
            top: calc(50% - 0.55rem);
        }

        .js-sortable-table thead th.sorting::after,
        .js-sortable-table thead th.sorting_asc::after,
        .js-sortable-table thead th.sorting_desc::after {
            content: '▼';
            top: calc(50% + 0.05rem);
        }

        .js-sortable-table thead th.sorting_asc::before {
            color: #f57c00;
        }

        .js-sortable-table thead th.sorting_asc::after {
            color: #d3d9df;
        }

        .js-sortable-table thead th.sorting_desc::before {
            color: #d3d9df;
        }

        .js-sortable-table thead th.sorting_desc::after {
            color: #f57c00;
        }

        .js-sortable-table thead th.no-sort {
            cursor: default;
            padding-right: 0.75rem;
        }

        .js-sortable-table thead th.no-sort::before,
        .js-sortable-table thead th.no-sort::after {
            content: none;
        }

        /* Global DataTables controls */
        .dataTables_wrapper .dataTables_length label {
            font-size: 0.9rem;
            color: #495057;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .dataTables_wrapper .row {
            margin-left: 0;
            margin-right: 0;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_info {
            margin-left: 0.75rem;
        }

        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_paginate {
            margin-right: 0.75rem;
        }

        .dataTables_wrapper .dataTables_length select {
            min-width: 84px;
        }

        .dataTables_wrapper .dataTables_length .js-dt-length-select-hidden {
            display: none !important;
        }

        .dataTables_wrapper .dataTables_length .js-dt-length-input {
            width: 96px;
            min-height: calc(1.5em + 0.5rem + 2px);
        }

        .dataTables_wrapper .dataTables_paginate .pagination {
            margin-bottom: 0;
            gap: 0.25rem;
        }

        .dataTables_wrapper .dataTables_paginate .page-item .page-link {
            border-radius: 0.4rem;
            font-size: 0.85rem;
            padding: 0.28rem 0.62rem;
        }

        .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
            background-color: #ff8c42;
            border-color: #ff8c42;
            color: #fff;
        }

        .dataTables_wrapper .dataTables_paginate .page-item.disabled .page-link {
            opacity: 0.55;
        }

        .dataTables_wrapper .dataTables_info {
            font-size: 0.85rem;
            color: #6c757d;
            padding-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Sidebar -->
        <?php echo view('layouts/inc/left-sidebar'); ?>

        <!-- Main Content -->
        <div class="content-wrapper" id="contentWrapper">
            <!-- Header -->
            <?php echo view('layouts/inc/header'); ?>

            <!-- Page Content -->
            <div class="page-content">
                <?= $this->renderSection('content') ?>
            </div>

            <!-- Footer -->
            <?php echo view('layouts/inc/footer'); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        function initGlobalDataTables() {
            if (typeof window.jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                return;
            }

            const enhanceLengthControl = (api) => {
                const wrapper = api.table().container();
                const lengthLabel = wrapper.querySelector('.dataTables_length label');
                const select = wrapper.querySelector('.dataTables_length select');

                if (!lengthLabel || !select) {
                    return;
                }

                select.classList.add('js-dt-length-select-hidden');

                if (lengthLabel.querySelector('.js-dt-length-input')) {
                    return;
                }

                const input = document.createElement('input');
                input.type = 'number';
                input.min = '1';
                input.step = '1';
                input.placeholder = 'Rows';
                input.className = 'form-control form-control-sm js-dt-length-input';

                const currentLength = api.page.len();
                input.value = currentLength > 0 ? String(currentLength) : '';

                const applyTypedLength = () => {
                    const parsedLength = parseInt(input.value, 10);
                    if (Number.isNaN(parsedLength) || parsedLength < 1) {
                        return;
                    }
                    api.page.len(parsedLength).draw(false);
                };

                input.addEventListener('change', applyTypedLength);
                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        applyTypedLength();
                    }
                });

                lengthLabel.insertBefore(input, select.nextSibling);
            };

            const skippedSelectors = [
                '.no-datatable',
                '.sales-mobile-table',
                '.summary-table',
                '.table-borderless',
                '#receipt-wrap table',
            ];

            document.querySelectorAll('.page-content table').forEach((table, index) => {
                if (table.dataset.datatableApplied === '1') {
                    return;
                }

                if (skippedSelectors.some((selector) => table.matches(selector) || table.closest(selector))) {
                    return;
                }

                const thead = table.querySelector('thead');
                if (!thead || !thead.querySelector('th')) {
                    return;
                }

                const headerColumnCount = thead.querySelectorAll('th').length;

                // Exclude non-data rows inside tbody from DataTables entry counts.
                table.querySelectorAll('tbody tr').forEach((row) => {
                    const cells = Array.from(row.children);
                    if (cells.length === 0) {
                        return;
                    }

                    const isHeaderLikeRow = cells.every((cell) => cell.tagName === 'TH');
                    const isSingleColspanRow = cells.length === 1
                        && cells[0].tagName === 'TD'
                        && Number(cells[0].getAttribute('colspan') || 0) >= headerColumnCount;

                    if (isHeaderLikeRow || isSingleColspanRow) {
                        row.remove();
                    }
                });

                if (!table.id) {
                    table.id = `globalDataTable_${index + 1}`;
                }

                const $table = jQuery(table);
                if (jQuery.fn.dataTable.isDataTable(table)) {
                    table.dataset.datatableApplied = '1';
                    table.classList.add('js-data-table-applied');
                    return;
                }

                $table.DataTable({
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                    searching: false,
                    ordering: true,
                    responsive: true,
                    autoWidth: false,
                    language: {
                        lengthMenu: 'Show _MENU_ entries',
                        paginate: {
                            previous: '<i class="bi bi-chevron-left"></i>',
                            next: '<i class="bi bi-chevron-right"></i>',
                        },
                    },
                    initComplete: function () {
                        enhanceLengthControl(this.api());
                    },
                    drawCallback: function () {
                        const api = this.api();
                        const wrapper = api.table().container();
                        const select = wrapper.querySelector('.dataTables_length select');
                        if (select) {
                            select.classList.add('js-dt-length-select-hidden');
                        }

                        const lengthInput = wrapper.querySelector('.js-dt-length-input');
                        if (lengthInput && document.activeElement !== lengthInput) {
                            const currentLength = api.page.len();
                            lengthInput.value = currentLength > 0 ? String(currentLength) : '';
                        }
                    },
                });

                table.dataset.datatableApplied = '1';
                table.classList.add('js-data-table-applied');
            });
        }

        function initSortableTables() {
            const parseSortableValue = (raw) => {
                const value = (raw || '').replace(/\s+/g, ' ').trim();
                if (value === '' || value === '-') {
                    return { type: 'text', value: '' };
                }

                const numericCandidate = value
                    .replace(/[\u20b1,$%]/g, '')
                    .replace(/,/g, '')
                    .trim();
                if (/^-?\d+(?:\.\d+)?$/.test(numericCandidate)) {
                    return { type: 'number', value: parseFloat(numericCandidate) };
                }

                const timestamp = Date.parse(value);
                if (!Number.isNaN(timestamp)) {
                    return { type: 'date', value: timestamp };
                }

                return { type: 'text', value: value.toLowerCase() };
            };

            const compareRows = (a, b, colIndex, direction) => {
                const aCell = a.cells[colIndex];
                const bCell = b.cells[colIndex];
                const aVal = parseSortableValue(aCell ? aCell.innerText : '');
                const bVal = parseSortableValue(bCell ? bCell.innerText : '');

                let result = 0;
                if ((aVal.type === 'number' || aVal.type === 'date') && (bVal.type === 'number' || bVal.type === 'date')) {
                    result = aVal.value - bVal.value;
                } else {
                    result = String(aVal.value).localeCompare(String(bVal.value), undefined, { numeric: true, sensitivity: 'base' });
                }

                if (result === 0) {
                    const aText = (a.innerText || '').toLowerCase();
                    const bText = (b.innerText || '').toLowerCase();
                    result = aText.localeCompare(bText, undefined, { numeric: true, sensitivity: 'base' });
                }

                return direction === 'desc' ? -result : result;
            };

            document.querySelectorAll('table.js-sortable-table').forEach((table, tableIndex) => {
                if (table.classList.contains('js-data-table-applied')) {
                    return;
                }

                const thead = table.querySelector('thead');
                const tbody = table.querySelector('tbody');
                if (!thead || !tbody) {
                    return;
                }

                const headers = Array.from(thead.querySelectorAll('th'));
                headers.forEach((th, index) => {
                    const text = (th.textContent || '').trim();
                    const actionHeader = /^actions?$/i.test(text);
                    const disabled = actionHeader || th.dataset.sortable === 'false' || th.classList.contains('no-sort');

                    if (disabled) {
                        th.classList.add('no-sort');
                        th.removeAttribute('tabindex');
                        th.removeAttribute('aria-sort');
                        return;
                    }

                    th.classList.add('sorting');
                    th.setAttribute('tabindex', '0');
                    th.setAttribute('aria-sort', 'none');
                    th.setAttribute('aria-controls', `sortable_table_${tableIndex}`);
                    th.setAttribute('aria-label', `${text}: activate to sort column ascending`);

                    const runSort = () => {
                        const current = th.dataset.sortDir === 'asc' ? 'asc' : (th.dataset.sortDir === 'desc' ? 'desc' : 'none');
                        const next = current === 'asc' ? 'desc' : 'asc';

                        headers.forEach((h) => {
                            if (h !== th && !h.classList.contains('no-sort')) {
                                h.classList.remove('sorting_asc', 'sorting_desc');
                                h.classList.add('sorting');
                                h.dataset.sortDir = 'none';
                                h.setAttribute('aria-sort', 'none');
                                const headerText = (h.textContent || '').trim();
                                h.setAttribute('aria-label', `${headerText}: activate to sort column ascending`);
                            }
                        });

                        th.classList.remove('sorting', 'sorting_asc', 'sorting_desc');
                        th.classList.add(next === 'asc' ? 'sorting_asc' : 'sorting_desc');
                        th.dataset.sortDir = next;
                        th.setAttribute('aria-sort', next === 'asc' ? 'ascending' : 'descending');
                        th.setAttribute('aria-label', `${text}: activate to sort column ${next === 'asc' ? 'descending' : 'ascending'}`);

                        const rows = Array.from(tbody.querySelectorAll('tr')).filter((row) => row.children.length === headers.length);
                        rows.sort((a, b) => compareRows(a, b, index, next));
                        rows.forEach((row) => tbody.appendChild(row));
                    };

                    th.addEventListener('click', runSort);
                    th.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            runSort();
                        }
                    });
                });
            });
        }

        // Generate UPC-A from SKU.
        // Priority: explicit known mapping -> deterministic fallback.
        window.generateSystemUpcFromSku = function (sku) {
            const raw = (sku || '').toString().trim().toUpperCase();
            if (!raw) {
                return '';
            }

            let body11 = '';

            if (body11 === '') {
                const digitsOnly = raw.replace(/\D/g, '');
                const numericPart = parseInt(digitsOnly || '0', 10);
                let letterSum = 0;
                for (let i = 0; i < raw.length; i++) {
                    const code = raw.charCodeAt(i);
                    if (code >= 65 && code <= 90) {
                        letterSum += (code - 64);
                    }
                }

                const itemCode = ((numericPart % 100000) + (letterSum * 297)) % 100000;
                body11 = '000000' + String(itemCode).padStart(5, '0');
            }

            let oddSum = 0;
            let evenSum = 0;
            for (let i = 0; i < body11.length; i++) {
                const n = parseInt(body11.charAt(i), 10);
                if ((i % 2) === 0) {
                    oddSum += n;
                } else {
                    evenSum += n;
                }
            }
            const checkDigit = (10 - (((oddSum * 3) + evenSum) % 10)) % 10;
            return body11 + String(checkDigit);
        };

        // Global: print a barcode label in a popup window
        function printItemLabel(name, sku, price, meta = {}) {
            const cleanName = (name || '').trim();
            const cleanSku = (sku && sku.trim() && sku.trim() !== '\u2014') ? sku.trim() : '';
            const partNo = ((meta && meta.partNo) || '').toString().trim();
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

            const upc = normalizeUpcA((meta && meta.upc) || '');
            const barcodeNumber = normalizeUpcA((meta && meta.barcodeNumber) || '');
            const explicitBarcodeType = normalizeBarcodeType(((meta && meta.barcodeType) || '').toString());
            const dcsCode = ((meta && meta.dcsCode) || '').toString().trim();
            const alu = ((meta && meta.alu) || '').toString().trim();
            const description1 = ((meta && meta.description1) || cleanName).toString().trim();
            const description2 = ((meta && meta.description2) || partNo).toString().trim();
            const vendorCodeRaw = ((meta && meta.vendorCode) || '').toString().trim();
            const brandCodeRaw = ((meta && meta.brandCode) || '').toString().trim();

            const rightHeader = (
                brandCodeRaw
                || (vendorCodeRaw !== '' ? vendorCodeRaw.split('-')[0].trim() : '')
                || 'AGRISG'
            ).toUpperCase();

            const headerSku = (cleanSku || alu || dcsCode || partNo || '').toUpperCase();
            const topLeftText = (headerSku !== '' ? (headerSku + ' - SKU') : 'ITEM - SKU');
            const partNameText = (description1 || cleanName || '').toUpperCase();

            const generatedUpc = normalizeUpcA(window.generateSystemUpcFromSku(cleanSku || headerSku));
            const barcodeValue = upc || barcodeNumber || generatedUpc || cleanSku || description2 || dcsCode;
            const normalizedUpcValue = normalizeUpcA(barcodeValue);
            const isUpc = (explicitBarcodeType === 'upc' && normalizedUpcValue !== '') || /^\d{12}$/.test(barcodeValue);
            const barcodeFormat = isUpc ? 'upc' : 'CODE128';
            const finalBarcodeValue = isUpc ? (normalizedUpcValue || barcodeValue) : barcodeValue;

            const onlyDigits = finalBarcodeValue.replace(/\D/g, '');

            const humanText = (() => {
                if (onlyDigits.length === 12) {
                    const v = onlyDigits;
                    return `${v.slice(0, 1)} ${v.slice(1, 6)} ${v.slice(6, 11)} ${v.slice(11, 12)}`;
                }
                if (finalBarcodeValue) {
                    return finalBarcodeValue.toUpperCase();
                }
                return '';
            })();

            const formatLower = (barcodeFormat || 'CODE128').toString().toLowerCase();
            const useNativeUpcText = (formatLower === 'upc');
            const barcodeWidth = useNativeUpcText ? 1.0 : 0.9;
            const barcodeHeight = 30;

            let barcodeSvgMarkup = '';
            if (finalBarcodeValue && typeof window.JsBarcode === 'function') {
                const tempSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                try {
                    JsBarcode(tempSvg, finalBarcodeValue, {
                        format: barcodeFormat || 'CODE128',
                        width: barcodeWidth,
                        height: barcodeHeight,
                        margin: 4,
                        displayValue: useNativeUpcText,
                        textMargin: useNativeUpcText ? 1 : 0,
                        fontSize: useNativeUpcText ? 8 : 7
                    });
                    tempSvg.removeAttribute('width');
                    tempSvg.removeAttribute('height');
                    tempSvg.setAttribute('preserveAspectRatio', 'xMinYMin meet');
                    barcodeSvgMarkup = tempSvg.outerHTML;
                } catch (e) {
                    barcodeSvgMarkup = '';
                }
            }

            const payload = {
                topLeftText,
                rightHeader,
                partNameText,
                barcodeValue: finalBarcodeValue,
                barcodeFormat,
                humanText,
                barcodeSvg: barcodeSvgMarkup,
                useNativeUpcText,
            };

                        const html = `<!DOCTYPE html><html><head><title>Label<\/title>
<style>
:root {
    --label-w: 28mm;
    --label-h: 20mm;
    --pad-x: 0.2mm;
    --pad-y: 0.3mm;
    --content-w: calc(var(--label-w) - (var(--pad-x) * 2));
    --inner-pad: 0.2mm;
    /* Inner content width controls the visual area used by header+partname+barcode
       Make this match the modal's narrow barcode area so header margins align. */
    --inner-content-w: calc(var(--content-w) - (var(--inner-pad) * 2));
}

* { margin: 0; padding: 0; box-sizing: border-box; }

@page {
    size: var(--label-w) var(--label-h);
    orientation: portrait;
    margin: 0;
    padding: 0;
}

@media print {
    body {
        margin: 0 !important;
        padding: 0 !important;
    }
}

html, body {
    width: var(--label-w);
    height: var(--label-h);
    margin: 0;
    padding: 0;
    overflow: hidden;
    background: #fff;
    font-family: Arial, sans-serif;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    display: flex;
    align-items: center;
    justify-content: center;
}

body {
    display: flex;
    align-items: center;
    justify-content: center;
}

.label {
    width: var(--label-w);
    height: var(--label-h);
    padding: var(--pad-y) var(--pad-x);
    background: #fff;
    border: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0;
}

.top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: var(--inner-content-w);
    margin: 0 auto 0.1mm;
    padding: 0;
    box-sizing: border-box;
    font-size: 1.8mm;
    font-weight: 700;
    color: #1f2937;
}

.top-left {
    line-height: 1.1;
    max-width: 45%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: left;
    flex-shrink: 1;
    flex-grow: 1;
}

.top-right {
    white-space: nowrap;
    margin-left: 0.3mm;
    text-align: right;
    flex-shrink: 0;
    min-width: 4mm;
    overflow: hidden;
    text-overflow: ellipsis;
}

.part-name {
    font-size: 1.5mm;
    font-weight: 600;
    color: #4b5563;
    width: var(--inner-content-w);
    margin: 0 auto 0.2mm;
    padding: 0;
    box-sizing: border-box;
    line-height: 1.18;
    min-height: 3mm;
    max-height: 3mm;
    text-align: left;
    overflow: hidden;
}

.barcode-wrap {
    display: flex;
    justify-content: center;
    align-items: center;
    width: var(--inner-content-w);
    height: 9mm;
    margin: 0 auto;
    padding: 0;
    box-sizing: border-box;
}

#bc {
    display: block;
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
}

.bottom-code {
    text-align: center;
    font-size: 1.6mm;
    line-height: 1.05;
    letter-spacing: 0.05mm;
    font-weight: 700;
    margin-top: 0.1mm;
    white-space: nowrap;
}

.empty {
    text-align: center;
    color: #777;
    font-size: 1.6mm;
    padding-top: 4mm;
}

@media screen {
    html, body {
        width: auto;
        height: auto;
        min-height: 100%;
        overflow: auto;
    }

    body {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 14px;
        background: #f5f5f5;
    }

    .label {
        border: 1px solid #d4d4d4;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    }
}

@media print {
    .label { border: 0; }
}
<\/style><\/head><body>
<div class="label">
    <div class="top">
        <div class="top-left" id="topLeft"><\/div>
        <div class="top-right" id="topRight"><\/div>
    <\/div>
    <div class="part-name" id="partName"><\/div>
    <div class="barcode-wrap" id="barcodeWrap"><svg id="bc"><\/svg><\/div>
    <div class="empty" id="emptyMsg" style="display:none;">No SKU/Barcode value available<\/div>
    <div class="bottom-code" id="bottomCode"><\/div>
<\/div>
<script>
const labelData = ${JSON.stringify(payload)};
window.onload = function handleLabelLoad() {
    document.getElementById('topLeft').textContent = labelData.topLeftText || 'ITEM LABEL';
    document.getElementById('topRight').textContent = labelData.rightHeader || 'AGRISG';
    const bottomCodeEl = document.getElementById('bottomCode');
    if (labelData.partNameText && labelData.partNameText !== '\u2014') {
        document.getElementById('partName').textContent = labelData.partNameText;
    }

    if (labelData.barcodeSvg) {
        const barcodeWrap = document.getElementById('barcodeWrap');
        barcodeWrap.innerHTML = labelData.barcodeSvg;
        if (labelData.useNativeUpcText) {
            bottomCodeEl.style.display = 'none';
        } else {
            bottomCodeEl.style.display = 'block';
            bottomCodeEl.textContent = labelData.humanText || '';
        }
    } else {
        document.getElementById('barcodeWrap').style.display = 'none';
        bottomCodeEl.style.display = 'none';
        document.getElementById('emptyMsg').style.display = 'block';
    }

    setTimeout(function () {
        window.print();
    }, 50);
};
<\/script>
<\/body><\/html>`;
            const win = window.open('', '_blank', 'width=560,height=420');
            if (win) {
                win.focus();
                win.document.open();
                win.document.write(html);
                win.document.close();
            }
        }

        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            initGlobalDataTables();
            initSortableTables();

            const menuIcon = document.querySelector('.menu-icon');
            const sidebar = document.querySelector('.left-side-bar');
            const contentWrapper = document.getElementById('contentWrapper');
            // Mobile/tablet-first default: keep sidebar collapsed on initial load.
            if (window.innerWidth <= 768) {
                sidebar?.classList.add('hidden');
                contentWrapper?.classList.add('full-width');
                if (menuIcon) {
                    menuIcon.style.display = 'block';
                }
            }

            if (menuIcon) {
                menuIcon.addEventListener('click', function() {
                    sidebar?.classList.toggle('hidden');
                    contentWrapper?.classList.toggle('full-width');
                    
                    // Hide menu icon when sidebar is shown
                    if (!sidebar?.classList.contains('hidden')) {
                        menuIcon.style.display = 'none';
                    }
                });
            }

            // Close sidebar when clicking menu items on mobile
            if (window.innerWidth <= 768) {
                document.querySelectorAll('.left-side-bar a').forEach(link => {
                    link.addEventListener('click', function() {
                        sidebar?.classList.add('hidden');
                        contentWrapper?.classList.add('full-width');
                        if (menuIcon) {
                            menuIcon.style.display = 'block';
                        }
                    });
                });
            }

            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar?.classList.remove('hidden');
                    contentWrapper?.classList.remove('full-width');
                    if (menuIcon) {
                        menuIcon.style.display = 'none';
                    }
                }
            });
        });
    </script>
</body>
</html>
