<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<div class="container-fluid px-3 py-3 upc-export-pdf">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 no-print">
        <h4 class="mb-0"><i class="bi bi-file-earmark-pdf me-2"></i>UPC Master List (PDF Export)</h4>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" onclick="window.close()"><i class="bi bi-x-lg me-1"></i>Close</button>
            <button class="btn btn-primary" id="btnPrintPdf"><i class="bi bi-printer me-1"></i>Print / Save PDF</button>
        </div>
    </div>

    <div class="pdf-meta mb-2">
        <div><strong>Generated:</strong> <?= esc($generatedAt ?? date('Y-m-d H:i')) ?></div>
        <div><strong>Branch:</strong> <?= esc(($branch ?? '') !== '' ? $branch : 'All') ?></div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered pdf-table mb-0">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Part Name</th>
                    <th>SKU</th>
                    <th>Part No</th>
                    <th>UPC</th>
                    <th>Stock</th>
                    <th>Branch</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rows)): ?>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= esc((string) ($r['product_id'] ?? '')) ?></td>
                        <?php
                            $partName = (string) ($r['part_name'] ?? '');
                            $description = trim((string) ($r['description'] ?? ''));
                            $displayName = trim(trim($partName) . ' ' . $description);
                        ?>
                        <td><?= esc($displayName !== '' ? $displayName : $partName) ?></td>
                        <td><?= esc((string) ($r['sku'] ?? '')) ?></td>
                        <td><?= esc((string) ($r['part_no'] ?? '')) ?></td>
                        <td><?= esc((string) ($r['upc'] ?? '')) ?></td>
                        <td><?= esc((string) ($r['current_stock'] ?? 0)) ?></td>
                        <td><?= esc((string) ($r['branch'] ?? '')) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted py-3">No UPC records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .pdf-meta {
        font-size: 13px;
        color: #4b5563;
        display: flex;
        gap: 18px;
        flex-wrap: wrap;
    }

    .pdf-table thead th {
        background: #f3f4f6;
        font-size: 12px;
        vertical-align: middle;
    }

    .pdf-table td {
        font-size: 11px;
        vertical-align: middle;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm;
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

        .pdf-table thead th {
            background: #f3f4f6 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnPrint = document.getElementById('btnPrintPdf');
    if (btnPrint) {
        btnPrint.addEventListener('click', function () {
            window.print();
        });
    }

    <?php if (!empty($autoPrint)): ?>
    setTimeout(function () { window.print(); }, 120);
    <?php endif; ?>
});
</script>

<?php $this->endSection(); ?>
