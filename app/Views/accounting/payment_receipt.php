<?php
$receiptNo = (string) ($receipt_no ?? '');
$customerName = (string) ($customer_name ?? '');
$customerAddress = (string) ($customer_address ?? '');
$paymentAmount = (float) ($payment_amount ?? 0);
$paymentMethod = (string) ($payment_method ?? 'Cash');
$processedAt = (string) ($processed_at ?? date('Y-m-d H:i:s'));
$cashier = (string) ($cashier ?? 'Cashier');
$notes = (string) ($notes ?? '');
$remainingBalance = (float) ($remaining_balance ?? 0);
$allocations = is_array($allocations ?? null) ? $allocations : [];
$branch = trim((string) ($branch ?? ''));
$branchAddress = $branch !== '' ? ($branch . ' BRANCH') : 'AGRI SAVERS G';
$logoUrl = base_url('images/agri-savers-logo.png');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt <?= esc($receiptNo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1f2937;
            padding: 1.25rem;
        }

        .receipt-shell {
            max-width: 980px;
            margin: 0 auto;
        }

        .receipt-toolbar {
            background: #fff;
            border-radius: 12px;
            padding: 0.85rem;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
            margin-bottom: 1rem;
        }

        .receipt-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .receipt-header {
            background: linear-gradient(135deg, #f57c00, #ef6c00);
            color: #fff;
            padding: 1rem 1.1rem;
        }

        .receipt-body {
            padding: 1rem 1.1rem;
        }

        .money {
            font-weight: 700;
        }

        .receipt-paper {
            width: 74mm;
            max-width: 74mm;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1mm 1.6mm 0.8mm;
            box-sizing: border-box;
            font-family: 'Courier New', monospace;
            color: #000;
            font-size: 10.5px;
            line-height: 1.2;
            font-weight: 600;
        }

        .print-fit {
            width: 100%;
        }

        .muted-small {
            color: #6b7280;
            font-size: 0.85rem;
        }

        .auto-print-note {
            color: #92400e;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 0.45rem 0.65rem;
            font-size: 0.85rem;
        }

        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            body {
                background: #fff;
                padding: 0;
                margin: 0;
            }

            .receipt-toolbar,
            .screen-only {
                display: none !important;
            }

            .receipt-shell,
            .receipt-card {
                max-width: 80mm;
                width: 80mm;
                margin: 0 auto;
                box-shadow: none;
                border-radius: 0;
            }

            .receipt-header,
            .receipt-body {
                padding: 0;
            }

            .receipt-body {
                display: flex;
                justify-content: center;
                align-items: flex-start;
            }

            .receipt-paper {
                border: none;
                border-radius: 0;
                margin: 0 auto;
                width: 72mm;
                max-width: 72mm;
                box-shadow: none;
            }

            /* Force smaller print output in Windows print preview/dialog */
            .print-fit {
                zoom: 0.9;
                margin-left: auto;
                margin-right: auto;
            }
        }
    </style>
</head>
<body>
<div class="receipt-shell">
    <div class="receipt-toolbar d-flex flex-wrap align-items-center gap-2 justify-content-between screen-only">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.location.href='<?= site_url('sales') ?>'">
                Back to Sales
            </button>
            <span class="muted-small">Payment Receipt: <strong><?= esc($receiptNo) ?></strong></span>
            <span class="auto-print-note">Auto printing to Windows printer...</span>
        </div>
    </div>

    <div class="receipt-card">
        <div class="receipt-header screen-only">
            <div class="fw-semibold">Payment collection saved and recorded in Sales.</div>
            <small>Printing starts automatically.</small>
        </div>
        <div class="receipt-body">
            <div id="receiptWrap" class="receipt-paper print-fit"></div>
        </div>
    </div>
</div>

<script>
(() => {
    const receiptData = {
        receiptNo: <?= json_encode($receiptNo) ?>,
        customerName: <?= json_encode($customerName) ?>,
        customerAddress: <?= json_encode($customerAddress) ?>,
        paymentAmount: <?= json_encode($paymentAmount) ?>,
        paymentMethod: <?= json_encode($paymentMethod) ?>,
        processedAt: <?= json_encode($processedAt) ?>,
        cashier: <?= json_encode($cashier) ?>,
        notes: <?= json_encode($notes) ?>,
        remainingBalance: <?= json_encode($remainingBalance) ?>,
        branchAddress: <?= json_encode($branchAddress) ?>,
        allocations: <?= json_encode($allocations) ?>,
        logoUrl: <?= json_encode($logoUrl) ?>,
    };

    const receiptWrap = document.getElementById('receiptWrap');

    const fmt = (num) => '₱' + Number(num || 0).toFixed(2);

    function escHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function buildReceiptHtml() {
        const paidAt = new Date(receiptData.processedAt);
        const cashierName = String(receiptData.cashier || 'Cashier').replace(/\s+/g, ' ').trim();
        const customerLabel = String(receiptData.customerName || 'Walk-in Customer').replace(/\s+/g, ' ').trim();
        const customerAddrLabel = String(receiptData.customerAddress || '-').replace(/\s+/g, ' ').trim();
        const dateText = isNaN(paidAt.getTime())
            ? receiptData.processedAt
            : paidAt.toLocaleString(undefined, {
                year: 'numeric',
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true,
            });
        const allocations = Array.isArray(receiptData.allocations) ? receiptData.allocations : [];
        const allocationCount = allocations.length;
        const barcodeValue = receiptData.receiptNo || 'PAY-0000-0000';

        const branchListText = 'POLANGUI BRANCH | NABUA BRANCH | SORSOGON BRANCH | MASBATE BRANCH | PILI BRANCH | CANAMAN BRANCH | VINZON BRANCH | LIBMANAN BRANCH | CANDELARIA BRANCH | ORMOC BRANCH | LEYTE BRANCH | BOHOL BRANCH';

        const barcodeSvg = buildPseudoBarcodeSvg(barcodeValue);
        const barcodeDataUrl = `data:image/svg+xml;utf8,${encodeURIComponent(barcodeSvg)}`;

        let allocationRows = '';
        if (allocations.length > 0) {
            allocations.forEach((row) => {
                const csi = row.csi || ('SALE#' + (row.sale_id || ''));
                allocationRows += `<div style="display:grid;grid-template-columns:1fr 68px;gap:6px;font-size:11px;font-weight:600;margin:0 0 1px;"><div>${escHtml(csi)}</div><div style="text-align:right;">${fmt(row.amount)}</div></div>`;
            });
        } else {
            allocationRows = '<div style="font-size:11px;">No applied sales</div>';
        }

        return `
<div style="text-align:center;margin:0 0 1mm;"><img src="${escHtml(receiptData.logoUrl || '')}" alt="Agri Savers G" style="max-width:30mm;max-height:10mm;height:auto;width:auto;object-fit:contain;"></div>
<div style="text-align:center;font-size:12px;font-weight:800;line-height:1.05;margin:0;">AGRI SAVERS G AGRICULTURAL<br>PRODUCT STORE</div>
<div style="text-align:center;font-size:11px;font-weight:800;margin:0;">${escHtml(receiptData.branchAddress || 'AGRI SAVERS G')}</div>
<div style="text-align:center;font-size:11px;margin:0 0 1mm;">NON-VAT REG TIN: 612-328-260-00000</div>
<hr style="border:none;border-top:1px solid #333;margin:3px 0;">
<div style="font-size:12px;font-weight:800;margin:0 0 1px;">Bill To:</div>
<div style="font-size:11px;padding-left:10px;line-height:1.18;">${escHtml(customerLabel)}</div>
<div style="font-size:11px;padding-left:10px;line-height:1.18;">${escHtml(customerAddrLabel)}</div>
<div style="display:grid;grid-template-columns:1fr auto;gap:6px;font-size:10.5px;font-weight:800;margin:1px 0;align-items:start;"><span><strong>Cashier:</strong> ${escHtml(cashierName)}</span><span style="white-space:nowrap;"><strong>Transaction #</strong> ${escHtml(receiptData.receiptNo)}</span></div>
<div style="font-size:11px;font-weight:800;margin:1px 0;"><strong>Order #</strong> BAL-PAYMENT</div>
<div style="font-size:11px;font-weight:800;margin:1px 0 2px;"><strong>Date:</strong> ${escHtml(dateText)}</div>
<hr style="border:none;border-top:1px solid #333;margin:3px 0;">
<div style="font-size:12px;font-weight:800;margin:1px 0 2px;">Applied Sales</div>
<div style="display:grid;grid-template-columns:1fr 68px;gap:6px;font-size:11px;font-weight:800;margin:0 0 1px;"><div>Prev Receipt #</div><div style="text-align:right;">Amount</div></div>
${allocationRows}
<div style="font-size:11px;margin:1px 0 1px;">${allocationCount} Receipt${allocationCount !== 1 ? 's' : ''} Applied</div>
<hr style="border:none;border-top:1px solid #333;margin:3px 0;">
<div style="display:flex;justify-content:space-between;gap:6px;font-size:11px;font-weight:800;margin:1px 0;"><span>Order Deposit:</span><span>${fmt(receiptData.paymentAmount)}</span></div>
<div style="display:flex;justify-content:space-between;gap:6px;font-size:12px;font-weight:700;margin:1px 0 2px;"><span>Receipt Total:</span><span>${fmt(receiptData.paymentAmount)}</span></div>
<hr style="border:none;border-top:1px solid #333;margin:3px 0;">
<div style="text-align:center;font-size:14px;font-weight:700;margin:2px 0 1px;">Tenders</div>
<div style="display:grid;grid-template-columns:1fr 68px;gap:6px;font-size:11px;font-weight:800;margin:0 0 1px;"><div>Payment Method</div><div style="text-align:right;">Amount</div></div>
<div style="display:grid;grid-template-columns:1fr 68px;gap:6px;font-size:11px;font-weight:600;margin:0 0 1px;"><div>${escHtml(receiptData.paymentMethod || 'Cash')}</div><div style="text-align:right;">${fmt(receiptData.paymentAmount)}</div></div>
<div style="display:flex;justify-content:space-between;gap:6px;font-size:11px;font-weight:600;margin:1px 0 2px;"><span>Change:</span><span>${fmt(0)}</span></div>
<hr style="border:none;border-top:1px solid #333;margin:3px 0;">
<div style="text-align:center;font-size:14px;font-weight:700;margin:2px 0 1px;">Order Summary</div>
<div style="text-align:center;font-size:11px;font-weight:700;margin:0 0 1px;">${allocationCount} Receipt${allocationCount !== 1 ? 's' : ''} Applied</div>
<div style="display:flex;justify-content:space-between;gap:6px;font-size:11px;margin:1px 0;"><span>Order Deposit:</span><span>${fmt(receiptData.paymentAmount)}</span></div>
<div style="display:flex;justify-content:space-between;gap:6px;font-size:11px;margin:1px 0;"><span>Change:</span><span>${fmt(0)}</span></div>
<div style="display:flex;justify-content:space-between;gap:6px;font-size:11px;margin:1px 0 2px;"><span>Order Balance Due:</span><span>${fmt(receiptData.remainingBalance)}</span></div>
<div style="display:flex;justify-content:center;align-items:center;margin:4px 0 2px;"><img src="${barcodeDataUrl}" alt="Barcode" style="width:60mm;height:10mm;image-rendering:pixelated;"></div>
<div style="text-align:center;font-size:11px;font-weight:400;letter-spacing:1px;margin:0 0 2px;">${escHtml(barcodeValue)}</div>
<div style="text-align:center;font-size:11px;font-weight:700;line-height:1.14;margin:3px 0 1px;">AGRICULTURAL PRODUCT STORE<br>AGRI SAVERS G</div>
<div style="text-align:center;font-size:9px;line-height:1.08;margin:0 0 2px;">${branchListText}</div>
<hr style="border:none;border-top:1px solid #333;margin:3px 0;">
<div style="font-size:11px;font-weight:800;margin:1px 0 1px;">Notes:</div>
<div style="font-size:11px;margin:1px 0 2px;">${escHtml(receiptData.notes || '-')}</div>
<div style="text-align:center;font-size:12px;font-weight:800;line-height:1.12;margin:2px 0 1px;">***THIS SERVES AS A PAYMENT RECEIPT***</div>
<div style="text-align:center;font-size:11px;font-weight:800;line-height:1.08;margin:0 0 1px;">BRING THIS RECEIPT INCASE OF EXCHANGE OF<br>MERCHANDISE WITHIN 7 DAY(S)</div>
<div style="text-align:center;font-size:12px;font-weight:800;line-height:1.06;margin:1px 0;">NO RECEIPT NO EXCHANGE!</div>
<div style="text-align:center;font-size:12px;font-weight:800;line-height:1.06;margin:1px 0 0;">THANK YOU AND COME AGAIN!</div>
`;
    }

    function buildPseudoBarcodeSvg(value) {
        const clean = String(value || '').replace(/\s+/g, '').toUpperCase();
        const source = clean !== '' ? clean : 'PAY0000';
        let x = 0;
        let rects = '';

        const addBar = (w) => {
            rects += `<rect x="${x}" y="0" width="${w}" height="38" fill="#000"/>`;
            x += w;
        };
        const addGap = (w) => { x += w; };

        addBar(2); addGap(1); addBar(1); addGap(1); addBar(2); addGap(2);

        for (let i = 0; i < source.length; i++) {
            const code = source.charCodeAt(i);
            const a = (code % 3) + 1;
            const b = ((code >> 1) % 3) + 1;
            const c = ((code >> 2) % 3) + 1;

            addBar(a);
            addGap(1);
            addBar(b);
            addGap(1);
            addBar(c);
            addGap(2);
        }

        addBar(2); addGap(1); addBar(1); addGap(1); addBar(2);
        x += 8;

        return `<svg xmlns="http://www.w3.org/2000/svg" width="${x}" height="38" viewBox="0 0 ${x} 38" preserveAspectRatio="none">${rects}</svg>`;
    }

    function buildReceiptText() {
        const allocations = Array.isArray(receiptData.allocations) ? receiptData.allocations : [];
        const lines = [];
        lines.push('AGRI SAVERS G AGRICULTURAL PRODUCT STORE');
        lines.push(String(receiptData.branchAddress || 'AGRI SAVERS G'));
        lines.push('NON-VAT REG TIN: 612-328-260-00000');
        lines.push('------------------------------------------');
        lines.push('Bill To:');
        lines.push('  ' + (receiptData.customerName || 'Walk-in Customer'));
        lines.push('  ' + (receiptData.customerAddress || '-'));
        lines.push('Cashier: ' + (receiptData.cashier || 'Cashier'));
        lines.push('Transaction#: ' + receiptData.receiptNo);
        lines.push('Order #: BAL-PAYMENT');
        lines.push('Date: ' + receiptData.processedAt);
        lines.push('------------------------------------------');
        lines.push('Applied Sales');
        lines.push('Prev Receipt#             Amount');
        if (allocations.length === 0) {
            lines.push('  No applied sales');
        } else {
            allocations.forEach((row) => {
                const csi = row.csi || ('SALE#' + (row.sale_id || ''));
                lines.push('  ' + csi + ' - ' + fmt(row.amount));
            });
        }
        lines.push('------------------------------------------');
        lines.push('Order Deposit: ' + fmt(receiptData.paymentAmount));
        lines.push('Receipt Total:  ' + fmt(receiptData.paymentAmount));
        lines.push('------------------------------------------');
        lines.push('Tenders');
        lines.push('Method: ' + (receiptData.paymentMethod || 'Cash'));
        lines.push('Amount: ' + fmt(receiptData.paymentAmount));
        lines.push('Change: ' + fmt(0));
        lines.push('------------------------------------------');
        lines.push('Order Summary');
        lines.push('Applied Receipts: ' + allocations.length);
        lines.push('Order Deposit: ' + fmt(receiptData.paymentAmount));
        lines.push('Change: ' + fmt(0));
        lines.push('Order Balance Due: ' + fmt(receiptData.remainingBalance));
        lines.push('Barcode: ' + receiptData.receiptNo);
        lines.push('AGRICULTURAL PRODUCT STORE');
        lines.push('AGRI SAVERS G');
        lines.push('POLANGUI BRANCH | NABUA BRANCH | SORSOGON BRANCH | MASBATE BRANCH | PILI BRANCH | CANAMAN BRANCH | VINZON BRANCH | LIBMANAN BRANCH | CANDELARIA BRANCH | ORMOC BRANCH | LEYTE BRANCH | BOHOL BRANCH');
        lines.push('------------------------------------------');
        lines.push('Notes: ' + (receiptData.notes || '-'));
        lines.push('***THIS SERVES AS A PAYMENT RECEIPT***');
        lines.push('BRING THIS RECEIPT INCASE OF EXCHANGE OF MERCHANDISE WITHIN 7 DAY(S)');
        lines.push('NO RECEIPT NO EXCHANGE!');
        lines.push('THANK YOU AND COME AGAIN!');
        return lines.join('\n');
    }

    receiptWrap.innerHTML = buildReceiptHtml();

    let printTriggered = false;
    const returnToSales = () => {
        window.location.href = '<?= site_url('sales') ?>';
    };

    const runAutoPrint = () => {
        if (printTriggered) {
            return;
        }
        printTriggered = true;
        setTimeout(() => {
            window.print();
        }, 250);
    };

    window.addEventListener('afterprint', returnToSales, { once: true });
    runAutoPrint();
})();
</script>
</body>
</html>
