<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<style>
    /* ── Hide footer on this page ── */
    .anitala-footer { display: none !important; }

    /* ── Page Header ── */
    .hist-page-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 22px;
    }
    .hist-page-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #FF8C42 0%, #FF6820 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 22px;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(255,140,66,0.28);
    }
    .hist-page-title {
        font-size: 24px;
        font-weight: 700;
        color: #1a1a1a;
        line-height: 1.15;
    }
    .hist-page-subtitle {
        font-size: 13px;
        color: #888;
        margin-top: 2px;
    }

    /* ── Filter Card ── */
    .hist-filter-card {
        border: 1px solid #e8e8e8;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    .hist-filter-card .card-body {
        padding: 18px 20px 16px;
    }
    .hist-filter-label {
        display: block;
        font-size: 11.5px;
        color: #777;
        margin-bottom: 4px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .hist-filter-input {
        font-size: 13.5px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        height: 36px;
        padding: 4px 10px;
        color: #333;
        background: #fafafa;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .hist-filter-input:focus {
        border-color: #FF8C42;
        box-shadow: 0 0 0 3px rgba(255,140,66,0.12);
        background: #fff;
        outline: none;
    }

    /* ── Buttons ── */
    .btn-hist-generate {
        background: linear-gradient(135deg, #FF8C42 0%, #FF6820 100%);
        color: #fff;
        font-size: 13.5px;
        font-weight: 600;
        border: none;
        border-radius: 8px;
        padding: 7px 18px;
        white-space: nowrap;
        height: 36px;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: opacity 0.15s, transform 0.15s;
        box-shadow: 0 3px 8px rgba(255,140,66,0.28);
    }
    .btn-hist-generate:hover { opacity: 0.9; color: #fff; transform: translateY(-1px); }

    .btn-hist-clear {
        background: #f5f5f5;
        color: #666;
        font-size: 13.5px;
        font-weight: 500;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 7px 14px;
        height: 36px;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: background 0.15s;
    }
    .btn-hist-clear:hover { background: #ececec; color: #333; }

    .btn-hist-export {
        background: #fff;
        color: #444;
        font-size: 13.5px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 7px 14px;
        white-space: nowrap;
        height: 36px;
    }
    .btn-hist-export:hover { background: #f5f5f5; }
    .btn-hist-export::after { display: none; }
    .btn-hist-export.dropdown-toggle::after { display: inline-block; }

    /* ── Checkbox ── */
    .hist-check .form-check-input:checked {
        background-color: #FF8C42;
        border-color: #FF8C42;
    }
    .hist-check .form-check-label {
        font-size: 13px;
        color: #555;
    }

    /* ── Results Card ── */
    .hist-table-card {
        border: 1px solid #e8e8e8;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .hist-table-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
    }
    .hist-table-card-header .hist-result-label {
        font-size: 14px;
        font-weight: 600;
        color: #1a1a1a;
    }
    .hist-result-count {
        font-size: 12px;
        color: #999;
        margin-left: 8px;
        font-weight: 400;
    }

    /* ── Table ── */
    .hist-table thead tr {
        background: #fafafa;
        border-bottom: 2px solid #f0f0f0;
    }
    .hist-table thead th {
        font-size: 12px;
        font-weight: 700;
        color: #FF8C42;
        background: #fafafa;
        padding: 11px 14px;
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }
    .hist-table thead th:hover .sort-icon { opacity: 1; }
    .hist-table thead th.th-sorted { color: #e06e28; }
    .sort-icon { opacity: 0.35; font-size: 10px; }
    .th-sorted .sort-icon { opacity: 1; }

    .hist-table tbody tr {
        border-bottom: 1px solid #f5f5f5;
        transition: background 0.12s;
    }
    .hist-table tbody tr:last-child { border-bottom: none; }
    .hist-table tbody tr:hover { background: #fff8f3; }

    .hist-table tbody td {
        font-size: 13.5px;
        color: #333;
        padding: 10px 14px;
        vertical-align: middle;
    }

    /* ── SKU Badge ── */
    .hist-sku-badge {
        font-size: 11px;
        font-weight: 600;
        background: #fff3ea;
        color: #d46a17;
        border-radius: 6px;
        padding: 2px 7px;
        letter-spacing: 0.3px;
        font-family: 'Courier New', monospace;
    }
    .hist-sku-empty {
        color: #ccc;
        font-size: 12px;
    }

    /* ── Product name ── */
    .hist-product-name {
        font-weight: 500;
        color: #1a1a1a;
    }

    /* ── Stock type badge ── */
    .hist-badge-stock {
        background: #fff3ea;
        color: #d46a17;
        font-size: 11.5px;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 6px;
    }

    /* ── On-hand pill ── */
    .hist-qty {
        font-weight: 600;
        color: #1a1a1a;
    }

    /* ── Empty state ── */
    .hist-empty {
        padding: 48px 20px;
        text-align: center;
        color: #aaa;
    }
    .hist-empty i { font-size: 36px; margin-bottom: 10px; display: block; }
    .hist-empty p { font-size: 14px; margin: 0; }

    /* ── Toggle icon ── */
    .hist-toggle-btn {
        background: none;
        border: none;
        color: #aaa;
        font-size: 14px;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }
    .hist-toggle-btn:hover { color: #555; }
</style>

<div class="container-fluid px-4 py-4">

    <!-- ── Page Header ── -->
    <div class="hist-page-header">
        <div class="hist-page-icon"><i class="bi bi-clock-history"></i></div>
        <div>
            <div class="hist-page-title">Activity History</div>
            <div class="hist-page-subtitle">Audit log of all actions performed in the system</div>
        </div>
    </div>

    <!-- ── Filter Card ── -->
    <div class="hist-filter-card" id="filterCard">
        <div class="card-body">
            <form method="get" action="<?= base_url('history') ?>" id="filterForm">
                <input type="hidden" name="generated" value="1">

                <!-- Row 1: Date, Action, Module -->
                <div class="row g-2 align-items-end mb-2">
                    <div class="col-auto" style="min-width:145px;">
                        <label class="hist-filter-label">Date From</label>
                        <input type="date" class="form-control hist-filter-input"
                               name="date_from" value="<?= esc($dateFrom ?? '') ?>">
                    </div>

                    <div class="col-auto" style="min-width:145px;">
                        <label class="hist-filter-label">Date To</label>
                        <input type="date" class="form-control hist-filter-input"
                               name="date_to" value="<?= esc($dateTo ?? '') ?>">
                    </div>

                    <div class="col-auto">
                        <label class="hist-filter-label">Module</label>
                        <!-- hidden input carries the value on submit -->
                        <input type="hidden" name="module" id="moduleFilter" value="<?= esc($module ?? '') ?>">
                        <div class="dropdown">
                            <button type="button"
                                    class="btn hist-filter-input dropdown-toggle d-flex align-items-center gap-2"
                                    data-bs-toggle="dropdown" aria-expanded="false"
                                    style="min-width:175px; justify-content:space-between;">
                                <span id="moduleLabel">
                                    <i class="bi bi-grid me-1"></i>
                                    <?= ($module ?? '') !== '' ? esc($module) : 'All Modules' ?>
                                </span>
                            </button>
                            <ul class="dropdown-menu shadow-sm" style="min-width:175px;">
                                <li>
                                    <a class="dropdown-item module-opt <?= ($module ?? '') === '' ? 'active' : '' ?>"
                                       href="#" data-val="">
                                        <i class="bi bi-grid me-2"></i>All Modules
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <?php
                                $modIcons = [
                                    'Products'   => 'bi-box-seam',
                                    'Categories' => 'bi-tag',
                                    'Sales'      => 'bi-cart',
                                    'Stocks'     => 'bi-stack',
                                    'Suppliers'  => 'bi-truck',
                                    'Auth'       => 'bi-person-lock',
                                ];
                                foreach (['Products','Categories','Sales','Stocks','Suppliers','Auth'] as $mod):
                                    $icon = $modIcons[$mod] ?? 'bi-circle';
                                ?>
                                <li>
                                    <a class="dropdown-item module-opt <?= ($module ?? '') === $mod ? 'active' : '' ?>"
                                       href="#" data-val="<?= $mod ?>">
                                        <i class="bi <?= $icon ?> me-2"></i><?= $mod ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <?php if ($isPolangui ?? false): ?>
                    <div class="col-auto">
                        <label class="hist-filter-label">Branch</label>
                        <input type="hidden" name="branch" id="branchFilter" value="<?= esc($branch ?? '') ?>">
                        <div class="dropdown">
                            <button type="button"
                                    class="btn hist-filter-input dropdown-toggle d-flex align-items-center gap-2"
                                    data-bs-toggle="dropdown" aria-expanded="false"
                                    style="min-width:185px; justify-content:space-between;">
                                <span id="branchLabel">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= ($branch ?? '') !== '' ? esc($branch) : 'All Branches' ?>
                                </span>
                            </button>
                            <ul class="dropdown-menu shadow-sm" style="min-width:185px; max-height:260px; overflow-y:auto;">
                                <li>
                                    <a class="dropdown-item branch-opt <?= ($branch ?? '') === '' ? 'active' : '' ?>"
                                       href="#" data-val="">
                                        <i class="bi bi-geo-alt me-2"></i>All Branches
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <?php
                                $branchList = [
                                    'Polangui',
                                    'Pili Branch',
                                    'Nabua',
                                    'Canaman',
                                    'Libmanan',
                                    'Vinzons',
                                    'Quezon',
                                    'Bohol',
                                    'Ormoc City',
                                    'Sorsogon',
                                    'Masbate',
                                ];
                                foreach ($branchList as $bl):
                                ?>
                                <li>
                                    <a class="dropdown-item branch-opt <?= ($branch ?? '') === $bl ? 'active' : '' ?>"
                                       href="#" data-val="<?= esc($bl) ?>">
                                        <i class="bi bi-geo-alt-fill me-2 text-warning"></i><?= esc($bl) ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="col-auto">
                        <label class="hist-filter-label">Action</label>
                        <input type="hidden" name="action" id="actionFilter" value="<?= esc($action ?? '') ?>">
                        <div class="dropdown">
                            <button type="button"
                                    class="btn hist-filter-input dropdown-toggle d-flex align-items-center gap-2"
                                    data-bs-toggle="dropdown" aria-expanded="false"
                                    style="min-width:175px; justify-content:space-between;">
                                <span id="actionLabel">
                                    <?php
                                    $actionLabels = [''        => 'All Actions',
                                                     'Added'   => 'Added',
                                                     'Updated' => 'Updated',
                                                     'Deleted' => 'Deleted'];
                                    $cur = $action ?? '';
                                    $matched = '';
                                    foreach ($actionLabels as $k => $v) {
                                        if ($k !== '' && stripos($cur, $k) !== false) { $matched = $k; break; }
                                    }
                                    ?>
                                    <i class="bi bi-funnel me-1"></i>
                                    <?= $matched !== '' ? esc($actionLabels[$matched]) : 'All Actions' ?>
                                </span>
                            </button>
                            <ul class="dropdown-menu shadow-sm" style="min-width:175px;">
                                <li>
                                    <a class="dropdown-item action-opt <?= ($action ?? '') === '' ? 'active' : '' ?>"
                                       href="#" data-val="">
                                        <i class="bi bi-funnel me-2"></i>All Actions
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <a class="dropdown-item action-opt <?= stripos($action ?? '', 'Added') !== false ? 'active' : '' ?>"
                                       href="#" data-val="Added">
                                        <i class="bi bi-plus-circle me-2 text-success"></i>Added
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item action-opt <?= stripos($action ?? '', 'Updated') !== false ? 'active' : '' ?>"
                                       href="#" data-val="Updated">
                                        <i class="bi bi-pencil me-2 text-warning"></i>Updated
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item action-opt <?= stripos($action ?? '', 'Deleted') !== false ? 'active' : '' ?>"
                                       href="#" data-val="Deleted">
                                        <i class="bi bi-trash me-2 text-danger"></i>Deleted
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Row 2: action buttons -->
                <div class="d-flex align-items-center justify-content-end flex-wrap gap-2 mt-1">
                    <div class="d-flex gap-2 align-items-center">
                        <a href="<?= base_url('history') ?>" class="btn btn-hist-clear">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                        <button type="submit" class="btn btn-hist-generate">
                            <i class="bi bi-lightning-fill"></i> Generate
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-hist-export dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-download me-1"></i> Export
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item" href="#" id="exportCsv">
                                        <i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i>Export as CSV
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" id="exportPdf">
                                        <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Export as PDF
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Results Table ── -->
    <div class="hist-table-card">
        <div class="hist-table-card-header">
            <span class="hist-result-label">
                <i class="bi bi-table me-2 text-warning"></i>Results
                <span class="hist-result-count"><?= count($histories ?? []) ?> record<?= count($histories ?? []) !== 1 ? 's' : '' ?></span>
            </span>
        </div>
        <div class="table-responsive">
            <table class="table hist-table mb-0" id="inventoryTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Module</th>
                        <?php if ($isPolangui ?? false): ?>
                        <th>Branch</th>
                        <?php endif; ?>
                        <th class="sortable-col" data-col="action">
                            Action <i class="bi bi-arrow-down-up sort-icon ms-1"></i>
                        </th>
                        <th>Part No</th>
                        <th>Item Name</th>
                        <th>Description</th>
                        <th class="sortable-col <?= ($sortDir ?? 'ASC') === 'ASC' ? 'th-sorted' : '' ?>" data-col="date">
                            Date <i class="bi bi-arrow-<?= ($sortDir ?? 'ASC') === 'ASC' ? 'up' : 'down' ?> sort-icon ms-1"></i>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($histories)): ?>
                        <?php foreach ($histories as $i => $row): ?>
                            <tr>
                                <td class="text-muted" style="font-size:12px;"><?= $i + 1 ?></td>
                                <td>
                                    <?php if (!empty($row['module'])): ?>
                                        <a href="#" class="badge rounded-pill module-filter-link"
                                           data-module="<?= esc($row['module']) ?>"
                                           style="font-size:11px;font-weight:600;background:#f0f4ff;color:#3d6acd;border:1px solid #d0daf8;text-decoration:none;cursor:pointer;"
                                           title="Click to filter by <?= esc($row['module']) ?>">
                                            <?= esc($row['module']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="hist-sku-empty">—</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($isPolangui ?? false): ?>
                                <td style="font-size:12.5px;color:#555;"><?= esc($row['branch'] ?? '—') ?></td>
                                <?php endif; ?>
                                <td>
                                    <span class="hist-badge-stock"><?= esc($row['action'] ?? '—') ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($row['part_no'])): ?>
                                        <span class="hist-sku-badge"><?= esc($row['part_no']) ?></span>
                                    <?php else: ?>
                                        <span class="hist-sku-empty">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="hist-product-name"><?= esc($row['item_name'] ?? '—') ?></td>
                                <td style="max-width:320px;white-space:normal;"><?= esc($row['description'] ?? '—') ?></td>
                                <td class="text-nowrap text-muted" style="font-size:12.5px;"><?= esc($row['date'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= ($isPolangui ?? false) ? 8 : 7 ?>">
                                <div class="hist-empty">
                                    <i class="bi bi-inbox"></i>
                                    <p>No history records found.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Branch dropdown ── */
    document.querySelectorAll('.branch-opt').forEach(function (item) {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            var val = this.dataset.val;
            document.getElementById('branchFilter').value = val;
            document.getElementById('branchLabel').innerHTML =
                '<i class="bi bi-geo-alt me-1"></i>' + (val !== '' ? val : 'All Branches');
            document.querySelectorAll('.branch-opt').forEach(function (o) { o.classList.remove('active'); });
            this.classList.add('active');
            document.getElementById('filterForm').submit();
        });
    });

    /* ── Action dropdown ── */
    document.querySelectorAll('.action-opt').forEach(function (item) {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            var val = this.dataset.val;
            document.getElementById('actionFilter').value = val;
            var icons = { '': 'bi-funnel', 'Added': 'bi-plus-circle', 'Updated': 'bi-pencil', 'Deleted': 'bi-trash' };
            var labels = { '': 'All Actions', 'Added': 'Added', 'Updated': 'Updated', 'Deleted': 'Deleted' };
            document.getElementById('actionLabel').innerHTML = '<i class="bi ' + (icons[val] || 'bi-funnel') + ' me-1"></i>' + labels[val];
            document.querySelectorAll('.action-opt').forEach(function (o) { o.classList.remove('active'); });
            this.classList.add('active');
            document.getElementById('filterForm').submit();
        });
    });

    /* ── Module dropdown (custom) ── */
    document.querySelectorAll('.module-opt').forEach(function (item) {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            var val = this.dataset.val;
            document.getElementById('moduleFilter').value = val;
            // Update button label
            var label = val === '' ? '<i class="bi bi-grid me-1"></i>All Modules'
                                   : '<i class="bi bi-grid me-1"></i>' + val;
            document.getElementById('moduleLabel').innerHTML = label;
            // Mark active
            document.querySelectorAll('.module-opt').forEach(function (o) { o.classList.remove('active'); });
            this.classList.add('active');
            // Auto-submit
            document.getElementById('filterForm').submit();
        });
    });

    /* ── Module badge click in table ── */
    document.querySelectorAll('.module-filter-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('moduleFilter').value = this.dataset.module;
            document.getElementById('filterForm').submit();
        });
    });

    /* ── Column sort ── */
    document.querySelectorAll('.sortable-col').forEach(function (th) {
        th.addEventListener('click', function () {
            var col     = th.dataset.col;
            var params  = new URLSearchParams(window.location.search);
            var curSort = params.get('sort_by');
            var curDir  = params.get('sort_dir') || 'asc';
            var newDir  = (curSort === col && curDir === 'asc') ? 'desc' : 'asc';
            params.set('sort_by', col);
            params.set('sort_dir', newDir);
            params.set('generated', '1');
            window.location.search = params.toString();
        });
    });

    /* ── CSV Export ── */
    document.getElementById('exportCsv')?.addEventListener('click', function (e) {
        e.preventDefault();
        var table = document.getElementById('inventoryTable');
        if (!table) return;
        var rows = Array.from(table.querySelectorAll('tr'));
        var csv  = rows.map(function (row) {
            return Array.from(row.querySelectorAll('th,td'))
                .map(function (cell) { return '"' + cell.innerText.replace(/"/g, '""').trim() + '"'; })
                .join(',');
        }).join('\n');
        var blob = new Blob([csv], { type: 'text/csv' });
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href = url; a.download = 'activity_history.csv'; a.click();
        URL.revokeObjectURL(url);
    });

    /* ── PDF Export ── */
    document.getElementById('exportPdf')?.addEventListener('click', function (e) {
        e.preventDefault();
        window.print();
    });
});
</script>

<?php $this->endSection(); ?>
