<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<?php
// Normalize commonly used view variables to satisfy static analysis and avoid undefined notices
$stocks = $stocks ?? [];
$search = isset($search) ? (string) $search : '';
$branches = $branches ?? [];
$lowOnly = !empty($lowOnly);
$canCrossBranchView = isset($canCrossBranchView) ? (bool) $canCrossBranchView : false;
$transferSourceBranch = isset($transferSourceBranch) ? (string) $transferSourceBranch : '';
$userRole = isset($userRole) ? (string) $userRole : (string) (session()->get('role') ?? '');
$userBranch = isset($userBranch) ? (string) $userBranch : (string) (session()->get('branch') ?? '');
$isMainBranch = isset($isMainBranch) ? (bool) $isMainBranch : false;
?>

<div class="container-fluid px-3">
    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php $__flashSuccess = session()->getFlashdata('success'); ?>
            <?= esc(is_array($__flashSuccess) ? json_encode($__flashSuccess) : (string) $__flashSuccess) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php $__flashError = session()->getFlashdata('error'); ?>
            <?= esc(is_array($__flashError) ? json_encode($__flashError) : (string) $__flashError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="d-flex justify-content-center align-items-center mb-4">
        <h2 class="stocks-title">STOCKS</h2>
    </div>

    <!-- Stocks Details Card -->
    <div class="card stocks-details-card shadow-sm">
        <div class="card-header">
            <?php
                $canCrossBranchView = (bool) ($canCrossBranchView ?? false);
                $activeBranchMode = trim((string) ($activeBranchMode ?? 'mine'));
                $activeBranchFilter = trim((string) ($activeBranchFilter ?? ''));
                $userBranch = trim((string) ($userBranch ?? session()->get('branch') ?? ''));

                $normalizeBranchOption = static function (string $branch): string {
                    $branch = strtolower(trim($branch));
                    $branch = preg_replace('/\s+/', ' ', $branch) ?? $branch;
                    $branch = preg_replace('/\s+branch$/i', '', $branch) ?? $branch;
                    return trim($branch);
                };

                $userBranchKey = $normalizeBranchOption($userBranch);
                $seenBranchKeys = [];
                $filteredBranchOptions = [];
                foreach (($branches ?? []) as $branchOption) {
                    $branchOption = trim((string) $branchOption);
                    if ($branchOption === '') {
                        continue;
                    }

                    $branchKey = $normalizeBranchOption($branchOption);
                    if ($branchKey === '' || $branchKey === $userBranchKey || isset($seenBranchKeys[$branchKey])) {
                        continue;
                    }

                    $seenBranchKeys[$branchKey] = true;
                    $filteredBranchOptions[] = $branchOption;
                }

                $branchButtonLabel = 'All Branches';
                if ($activeBranchMode === 'mine') {
                    $branchButtonLabel = 'My Products';
                } elseif ($activeBranchMode === 'branch' && $activeBranchFilter !== '') {
                    $branchButtonLabel = $activeBranchFilter;
                }

                $queryParams = [];
                if (($search ?? '') !== '') {
                    $queryParams['search'] = $search;
                }
                if (!empty($lowOnly)) {
                    $queryParams['low'] = 1;
                }
                if ($canCrossBranchView) {
                    if ($activeBranchMode === 'mine') {
                        $queryParams['branch'] = 'mine';
                    } elseif ($activeBranchMode === 'branch' && $activeBranchFilter !== '') {
                        $queryParams['branch'] = $activeBranchFilter;
                    } elseif ($activeBranchMode === 'all') {
                        $queryParams['branch'] = 'all';
                    }
                }
                $exportQuery = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
            ?>
            <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
                <h5 class="mb-0">Spare Part's Inventory</h5>
                <div class="d-flex align-items-center gap-2">
                    <form method="GET" action="<?= base_url('stocks') ?>" class="d-flex gap-2" id="stocksSearchForm">
                        <?php if ($canCrossBranchView): ?>
                            <input type="hidden" name="branch" value="<?= esc($activeBranchMode === 'branch' ? $activeBranchFilter : $activeBranchMode) ?>">
                        <?php endif; ?>
                        <?php if (!empty($lowOnly)): ?>
                            <input type="hidden" name="low" value="1">
                        <?php endif; ?>
                        <div class="input-group manage-search-group">
                            <input type="text" name="search" class="form-control manage-search-input" placeholder="Search by SKU, Part Name..." value="<?= esc($search ?? '') ?>" id="stocksSearchInput">
                            <button class="btn manage-search-btn" type="button" data-bs-toggle="modal" data-bs-target="#stocksFilterModal" aria-label="Filter" title="Filter"><i class="bi bi-funnel"></i></button>
                            <button class="btn manage-search-btn" type="submit" title="Search"><i class="bi bi-search"></i></button>
                        </div>
                    </form>
                    <?php if ($canCrossBranchView): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Filter branches" title="Branches">
                                Branch: <?= esc($branchButtonLabel) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item <?= $activeBranchMode === 'mine' ? 'active' : '' ?>" href="<?= base_url('stocks?branch=mine') ?>"><i class="bi bi-person-badge me-2"></i>My Products</a></li>
                                <li><a class="dropdown-item <?= $activeBranchMode === 'all' ? 'active' : '' ?>" href="<?= base_url('stocks?branch=all') ?>"><i class="bi bi-geo-alt me-2"></i>All Branches</a></li>
                                <?php foreach ($filteredBranchOptions as $branchOption): ?>
                                <li>
                                    <a class="dropdown-item <?= ($activeBranchMode === 'branch' && strcasecmp($activeBranchFilter, (string) $branchOption) === 0) ? 'active' : '' ?>"
                                        href="<?= base_url('stocks') . '?branch=' . rawurlencode((string) $branchOption) ?>">
                                        <i class="bi bi-shop me-2"></i><?= esc($branchOption) ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($lowOnly)): ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2" title="Showing low-stock items only">
                            <i class="bi bi-exclamation-triangle me-1"></i> Low Stock Filter Active
                        </span>
                        <a href="<?= base_url('stocks') . ($canCrossBranchView ? ('?branch=' . rawurlencode($activeBranchMode === 'branch' ? $activeBranchFilter : $activeBranchMode)) : '') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Clear Low Filter
                        </a>
                    <?php endif; ?>
                    <button class="btn btn-new-stock" data-bs-toggle="modal" data-bs-target="#newStockModal">
                        <i class="bi bi-plus-circle"></i> New Stock
                    </button>
                    <button class="btn btn-transfer-selected" id="transferSelectedStocksBtn" type="button" disabled>
                        <i class="bi bi-arrow-left-right"></i> Transfer Selected (<span id="selectedStocksCount">0</span>)
                    </button>
                    <button class="btn btn-import-csv" data-bs-toggle="modal" data-bs-target="#importStockModal">
                        <i class="bi bi-file-earmark-arrow-up"></i> Import CSV
                    </button>
                    <?php if ($userRole === 'admin' && !$isMainBranch): ?>
                        <a href="<?= base_url('stocks/requests') ?>" class="btn btn-info">
                            <i class="bi bi-clipboard-list"></i> View Requests
                        </a>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#requestStockModal">
                            <i class="bi bi-hand-holding"></i> Request Stock
                        </button>
                    <?php elseif ($isMainBranch): ?>
                        <a href="<?= base_url('stocks/requests') ?>" class="btn btn-info">
                            <i class="bi bi-clipboard-list"></i> Manage Requests
                        </a>
                    <?php endif; ?>
                    <div class="dropdown export-choice-dropdown">
                        <button class="btn btn-export-pdf dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Export" title="Export">
                            <i class="bi bi-download"></i><span class="btn-label">Export</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?= base_url('stocks/export/pdf') . $exportQuery ?>" target="_blank" rel="noopener">
                                    <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Export as PDF
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= base_url('stocks/export/csv') . $exportQuery ?>">
                                    <i class="bi bi-file-earmark-excel me-2 text-success"></i>Export as Excel
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="legend-container">
                <div class="legend-title">Legend:</div>
                <div class="legend-items">
                    <div class="legend-item">
                        <span class="legend-color" style="background-color: yellow;"></span>
                        <span class="legend-text">Fill Color Yellow - New Entry</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background-color: grey;"></span>
                        <span class="legend-text">Fill Color Grey - Vacant SKU no.</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background-color: red;"></span>
                        <span class="legend-text">Fill Color Red - To remove w/ same item or wrong Part name & no.</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color" style="background-color: green;"></span>
                        <span class="legend-text">Fill Color Green - No need to put quantity, pls integrate</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table stocks-details-table mb-0 js-sortable-table" id="stocksDetailsTable">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAllTransferStocks" title="Select all visible stocks for transfer">
                            </th>
                            <th>Image</th>
                            <th>SKU</th>
                            <th>No.</th>
                            <th>Part No.</th>
                            <th>Part Name</th>
                            <th>Price</th>
                            <th>Stocks</th>
                            <th>Beg. Inv.</th>
                            <th>Item's In</th>
                            <th>Remarks (In)</th>
                            <th>Item's Out</th>
                            <th>Remarks (Out)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stocks)): ?>
                        <tr><td colspan="14" class="text-center text-muted py-3">No stock records found.</td></tr>
                        <?php else: foreach ($stocks as $s): ?>
                        <tr>
                            <td>
                                <?php
                                    $availableStocks = (int) ($s['stocks'] ?? 0);
                                    $productId = (int) ($s['product_id'] ?? 0);
                                    $sourceBranch = trim((string) ($transferSourceBranch ?? ($userBranch ?? '')));
                                    if ($sourceBranch === '') {
                                        $sourceBranch = (string) ($s['branch'] ?? ($s['product_branch'] ?? ''));
                                    }
                                    $isTransferDisabled = $availableStocks <= 0;
                                ?>
                                <input
                                    type="checkbox"
                                    class="transfer-stock-checkbox"
                                    value="<?= (int) $s['stock_id'] ?>"
                                    data-product-id="<?= $productId ?>"
                                    data-sku="<?= esc((string) ($s['sku'] ?? '')) ?>"
                                    data-part-name="<?= esc((string) ($s['part_name'] ?? '')) ?>"
                                    data-available="<?= $availableStocks ?>"
                                    data-source-branch="<?= esc($sourceBranch) ?>"
                                            <?= $isTransferDisabled ? 'disabled' : '' ?>
                                >
                            </td>
                            <td>
                                <img src="<?= \App\Controllers\StocksController::stockImageUrl($s['stock_id'], $s['product_id'] ?? null, $s['sku'] ?? null) ?>" alt="<?= esc((string) ($s['part_name'] ?? '')) ?>" class="stock-thumbnail" style="height: 40px; width: 40px; object-fit: cover; border-radius: 4px;">
                            </td>
                            <td><?= esc((string) ($s['sku'] ?? '')) ?></td>
                            <td><?= esc((string) ($s['stock_num'] ?? '—')) ?></td>
                            <td><?= esc((string) ($s['part_num'] ?? '—')) ?></td>
                            <td><?= esc((string) ($s['part_name'] ?? '')) ?></td>
                            <td><?= $s['price'] !== null ? '₱' . number_format((float)$s['price'], 2) : '—' ?></td>
                            <td><?= $s['stocks'] ?? 0 ?></td>
                            <td><?= $s['beg_inv'] ?? 0 ?></td>
                            <td><?= $s['items_in'] ?? 0 ?></td>
                            <td class="small text-muted"><?= esc((string) ($s['in_remarks'] ?? '—')) ?></td>
                            <td><?= $s['items_out'] ?? 0 ?></td>
                            <td class="small text-muted"><?= esc((string) ($s['out_remarks'] ?? '—')) ?></td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-action btn-info" data-bs-toggle="modal" data-bs-target="#viewStockModal"
                                    data-id="<?= (int) $s['stock_id'] ?>"
                                    data-sku="<?= esc((string) ($s['sku'] ?? '')) ?>"
                                    data-no="<?= esc((string) ($s['stock_num'] ?? '')) ?>"
                                    data-part-no="<?= esc((string) ($s['part_num'] ?? '')) ?>"
                                    data-part-name="<?= esc((string) ($s['part_name'] ?? '')) ?>"
                                    data-upc="<?= esc((string) ($s['product_upc'] ?? '')) ?>"
                                    data-barcode-type="<?= esc((string) ($s['product_barcode_type'] ?? '')) ?>"
                                    data-label-brand="<?= esc((string) ($s['product_label_brand'] ?? '')) ?>"
                                    data-label-short-name="<?= esc((string) ($s['product_label_short_name'] ?? '')) ?>"
                                    data-price="<?= $s['price'] ?>"
                                    data-stocks="<?= (int) ($s['stocks'] ?? 0) ?>"
                                    data-beg-inv="<?= (int) ($s['beg_inv'] ?? 0) ?>"
                                    data-items-in="<?= (int) ($s['items_in'] ?? 0) ?>"
                                    data-items-out="<?= (int) ($s['items_out'] ?? 0) ?>"
                                    data-remarks-in="<?= esc((string) ($s['in_remarks'] ?? '')) ?>"
                                    data-remarks-out="<?= esc((string) ($s['out_remarks'] ?? '')) ?>"
                                    data-image="<?= \App\Controllers\StocksController::stockImageUrl($s['stock_id'], $s['product_id'] ?? null, $s['sku'] ?? null) ?>"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-action btn-warning" data-bs-toggle="modal" data-bs-target="#editStockModal"
                                    data-id="<?= (int) $s['stock_id'] ?>"
                                    data-sku="<?= esc((string) ($s['sku'] ?? '')) ?>"
                                    data-no="<?= esc((string) ($s['stock_num'] ?? '')) ?>"
                                    data-part-no="<?= esc((string) ($s['part_num'] ?? '')) ?>"
                                    data-part-name="<?= esc((string) ($s['part_name'] ?? '')) ?>"
                                    data-price="<?= $s['price'] ?>"
                                    data-stocks="<?= (int) ($s['stocks'] ?? 0) ?>"
                                    data-beg-inv="<?= (int) ($s['beg_inv'] ?? 0) ?>"
                                    data-items-in="<?= (int) ($s['items_in'] ?? 0) ?>"
                                    data-items-out="<?= (int) ($s['items_out'] ?? 0) ?>"
                                    data-remarks-in="<?= esc((string) ($s['in_remarks'] ?? '')) ?>"
                                    data-remarks-out="<?= esc((string) ($s['out_remarks'] ?? '')) ?>"
                                    data-image="<?= \App\Controllers\StocksController::stockImageUrl($s['stock_id'], $s['product_id'] ?? null, $s['sku'] ?? null) ?>"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-action btn-danger" data-bs-toggle="modal" data-bs-target="#deleteStockModal"
                                    data-id="<?= (int) $s['stock_id'] ?>"
                                    data-sku="<?= esc((string) ($s['sku'] ?? '')) ?>"
                                    data-part-name="<?= esc((string) ($s['part_name'] ?? '')) ?>"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>

    
    .stocks-title {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a1a;
        letter-spacing: 0.5px;
    }

    .btn-new-stock {
        background: linear-gradient(135deg, #FF8C42 0%, #FF7A2F 100%);
        color: white;
        padding: 10px 24px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-new-stock:hover {
        background: linear-gradient(135deg, #FF7A2F 0%, #FF6820 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 140, 66, 0.3);
        color: white;
    }

    .btn-new-sale {
        background: linear-gradient(135deg, #FF8C42 0%, #FF7A2F 100%);
        color: white;
        padding: 8px 20px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }

    .btn-new-sale:hover {
        background: linear-gradient(135deg, #FF7A2F 0%, #FF6820 100%);
        color: white;
    }

    .stocks-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
    }

    .stocks-details-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        background: white;
    }

    .stocks-details-card .card-header {
        background: white;
        border-bottom: 2px solid #f0f0f0;
        padding: 1rem 1.25rem;
    }

    .legend-container {
        margin-top: 1rem;
        font-size: 14px;
    }

    .legend-title {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #2a2a2a;
    }

    .legend-items {
        display: flex;
        flex-wrap: wrap;
        /* flex-direction: column; */
        gap: 1rem;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .legend-color {
        width: 16px;
        height: 16px;
        border: 1px solid #ccc;
        display: inline-block;
        border-radius: 3px;
    }

    .legend-text {
        color: #555;
        font-size: 14px;
    }

    .stocks-table thead th {
        background: #f8f9fa;
        color: #2a2a2a;
        font-weight: 600;
        font-size: 14px;
        padding: 16px 20px;
        border: none;
        text-align: left;
    }

    .stocks-table tbody td {
        padding: 20px;
        vertical-align: middle;
        border-top: 1px solid #e9ecef;
        font-size: 14px;
        color: #6c757d;
    }

    .stocks-details-table {
        font-size: 15px;
    }

    .stocks-details-table thead th {
        background: #f8f9fa;
        color: #2a2a2a;
        font-weight: 600;
        font-size: 15px;
        padding: 12px 15px;
        border: none;
        text-align: left;
        white-space: nowrap;
        word-break: normal;
    }

    .stocks-details-table tbody td {
        padding: 12px 15px;
        vertical-align: middle;
        border-top: 1px solid #e9ecef;
        font-size: 15px;
        word-break: break-word;
    }


    .btn-action {
        border-radius: 6px;
        padding: 6px 10px;
        border: none;
        margin-right: 4px;
        transition: all 0.2s ease;
    }

    .btn-action.btn-info {
        background: #0d6efd;
        color: white;
    }

    .btn-action.btn-info:hover {
        background: #0d6efd;
        transform: translateY(-1px);
    }

    .btn-action.btn-warning {
        background: #FF8C42;
        color: white;
    }

    .btn-action.btn-warning:hover {
        background: #FF7A2F;
        transform: translateY(-1px);
    }

    .btn-action.btn-danger {
        background: #dc3545;
        color: white;
    }

    .btn-action.btn-danger:hover {
        background: #c82333;
        transform: translateY(-1px);
    }

    .table-responsive {
        overflow-x: auto;
    }

    @media (max-width: 768px) {
        .stocks-title {
            font-size: 24px;
        }

        .stocks-table thead th,
        .stocks-table tbody td {
            padding: 12px 10px;
            font-size: 12px;
        }

        .stocks-details-table thead th,
        .stocks-details-table tbody td {
            padding: 8px 10px;
            font-size: 13px;
        }

        .d-flex.align-items-center.justify-content-between {
            gap: 1rem !important;
            flex-direction: column;
            align-items: stretch !important;
        }

        .manage-search-group {
            min-width: 100%;
        }

        .btn-new-stock {
            width: 100%;
            justify-content: center;
        }
    }

    /* Extra small screens or when sidebar is visible on larger screens */
    @media (max-width: 1200px) {
        .stocks-details-table thead th,
        .stocks-details-table tbody td {
            padding: 10px 8px;
            font-size: 14px;
        }

        .stocks-details-table {
            font-size: 14px;
        }

        .manage-search-group {
            min-width: 200px;
        }

        .btn-new-stock {
            padding: 8px 16px;
            font-size: 13px;
        }
    }

    /* Ensure table fits properly when sidebar is displayed */
    .stocks-details-table {
        width: 100%;
    }

    .stock-thumbnail {
        max-width: 40px;
        max-height: 40px;
        flex-shrink: 0;
    }

    /* Adjust header layout for better fit */
    .d-flex.align-items-center.justify-content-between {
        flex-wrap: wrap !important;
    }

    .manage-search-group {
        min-width: 250px;
    }

    .btn-import-csv {
        background: linear-gradient(135deg, #28a745 0%, #218838 100%);
        color: white;
        padding: 10px 18px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-import-csv:hover {
        background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        color: white;
    }

    .btn-transfer-selected {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white;
        padding: 10px 18px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-transfer-selected:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    .btn-transfer-selected:not(:disabled) {
        background: linear-gradient(135deg, #5e57e0 0%, #4f49c8 100%);
    }

    .transfer-items-table td,
    .transfer-items-table th {
        font-size: 13px;
        vertical-align: middle;
    }

    .btn-export-csv {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: white;
        padding: 10px 18px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-export-csv:hover {
        background: linear-gradient(135deg, #0a58ca 0%, #084298 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        color: white;
    }

    .btn-export-pdf {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        padding: 10px 18px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-export-pdf:hover {
        background: linear-gradient(135deg, #c82333 0%, #a71d2a 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        color: white;
    }

    .filter-section {
        margin-bottom: 1.25rem;
    }

    .filter-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #444;
        margin-bottom: 0.75rem;
    }

    .filter-checkboxes,
    .filter-radios {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 0.5rem;
    }

    .filter-checkbox,
    .filter-radio {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        font-size: 0.9rem;
        color: #555;
    }

    .filter-checkbox input[type="checkbox"],
    .filter-radio input[type="radio"] {
        accent-color: #0066ff;
        width: 16px;
        height: 16px;
        cursor: pointer;
    }
</style>

<div class="modal fade" id="bulkTransferModal" tabindex="-1" aria-labelledby="bulkTransferLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkTransferLabel">Transfer Selected Stocks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="alert alert-info mb-0">
                            Selected stocks keep their own source branch automatically.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="bulkToBranch" class="form-label">Destination Branch</label>
                        <select id="bulkToBranch" class="form-select">
                            <option value="">Select destination branch</option>
                            <?php if (!empty($branches)): ?>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?= esc((string) $branch) ?>"><?= esc((string) $branch) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="bulkRecipientUser" class="form-label">Recipient</label>
                        <select id="bulkRecipientUser" class="form-select">
                            <option value="">Select destination branch first</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm transfer-items-table mb-0" id="bulkTransferItemsTable">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Part Name</th>
                                <th>Available</th>
                                <th>Transfer Qty</th>
                            </tr>
                        </thead>
                        <tbody id="bulkTransferItemsBody"></tbody>
                    </table>
                </div>

                <div id="bulkTransferAlert" class="alert alert-danger mt-3 d-none" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmBulkTransferBtn">Create Transfers</button>
            </div>
        </div>
    </div>
</div>

<?= view('stocks/new_stock') ?>
<?= view('stocks/view_stock') ?>
<?= view('stocks/edit_stock') ?>
<?= view('stocks/delete_stock') ?>

<!-- ===================== IMPORT STOCK CSV MODAL ===================== -->
<div class="modal fade" id="importStockModal" tabindex="-1" aria-labelledby="importStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #28a745 0%, #218838 100%);">
                <h5 class="modal-title text-white fw-bold" id="importStockModalLabel">
                    <i class="bi bi-file-earmark-arrow-up me-2"></i> Import Stocks from CSV
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('stocks/import') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="text-muted mb-3">Upload a <strong>.csv</strong> file with the following columns:</p>
                    <code class="d-block bg-light p-2 rounded mb-3" style="font-size:12px;">sku, no, part_no, part_name, price, stocks, items_in, remarks_in, items_out, remarks_out</code>
                    <div class="mb-3">
                        <label for="stocks_csv_file" class="form-label fw-semibold">Select CSV File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="stocks_csv_file" name="csv_file" accept=".csv,.txt" required>
                    </div>
                    <a href="<?= site_url('stocks/template') ?>" class="text-decoration-none small">
                        <i class="bi bi-download me-1"></i> Download template CSV
                    </a>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #28a745 0%, #218838 100%);">
                        <i class="bi bi-upload me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END IMPORT STOCK CSV MODAL =================== -->

<!-- ===================== REQUEST STOCK MODAL ===================== -->
<div class="modal fade" id="requestStockModal" tabindex="-1" aria-labelledby="requestStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);">
                <h5 class="modal-title text-white fw-bold" id="requestStockModalLabel">
                    <i class="bi bi-hand-holding me-2"></i> Request Stock from Main Branch
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('stocks/requestStock') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Request products from the main branch. Your request will be reviewed and approved by main branch administrators.
                    </div>

                    <div class="mb-3">
                        <label for="request_product_id" class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                        <select class="form-select" id="request_product_id" name="product_id" required>
                            <option value="">Select a product...</option>
                            <?php foreach ($stocks as $stock): ?>
                                <?php if (!empty($stock['product_id'])): ?>
                                    <option value="<?= esc((string) ($stock['product_id'] ?? '')) ?>" data-price="<?= esc((string) ($stock['price'] ?? '0')) ?>">
                                        <?= esc((string) ($stock['part_name'] ?? '')) ?> (SKU: <?= esc((string) ($stock['sku'] ?? '')) ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="request_quantity" class="form-label fw-semibold">Requested Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="request_quantity" name="quantity" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="request_reason" class="form-label fw-semibold">Reason (Optional)</label>
                        <textarea class="form-control" id="request_reason" name="reason" rows="3" placeholder="Please provide a reason for this stock request..."></textarea>
                    </div>

                    <div id="request_summary" class="mt-3 p-3 bg-light rounded" style="display: none;">
                        <h6>Request Summary:</h6>
                        <div id="request_product_info"></div>
                        <div id="request_quantity_info"></div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);">
                        <i class="bi bi-send me-1"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END REQUEST STOCK MODAL =================== -->

<!-- ===================== STOCKS FILTER MODAL ===================== -->
<div class="modal fade" id="stocksFilterModal" tabindex="-1" aria-labelledby="stocksFilterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stocksFilterLabel">Filter Stocks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="filter-section mb-3">
                    <h6 class="filter-section-title">Stock Level</h6>
                    <div class="filter-radios d-flex flex-column gap-2">
                        <label class="filter-radio d-flex align-items-center gap-2">
                            <input type="radio" name="stocksLevelFilter" value="all" checked>
                            <span>All stocks</span>
                        </label>
                        <label class="filter-radio d-flex align-items-center gap-2">
                            <input type="radio" name="stocksLevelFilter" value="low">
                            <span>Low stock only (<= 10)</span>
                        </label>
                        <label class="filter-radio d-flex align-items-center gap-2">
                            <input type="radio" name="stocksLevelFilter" value="out">
                            <span>Out of stock only (= 0)</span>
                        </label>
                    </div>
                </div>

                <div class="filter-section mb-3">
                    <h6 class="filter-section-title">Filter By</h6>
                    <div class="d-flex flex-column gap-2">
                        <select class="form-select" id="stocksFilterByField">
                            <option value="all" selected>All fields</option>
                            <option value="sku">SKU</option>
                            <option value="no">No.</option>
                            <option value="part_no">Part No.</option>
                            <option value="part_name">Part Name</option>
                        </select>
                        <input type="text" class="form-control" id="stocksFilterByValue" placeholder="Enter keyword (e.g., A41)">
                    </div>
                </div>

                <div class="filter-section mb-0">
                    <h6 class="filter-section-title">Columns</h6>
                    <div class="filter-checkboxes d-flex flex-column gap-2">
                        <label class="filter-checkbox d-flex align-items-center gap-2"><input type="checkbox" class="stocks-column-toggle" data-column="0" checked><span>Select</span></label>
                        <label class="filter-checkbox d-flex align-items-center gap-2"><input type="checkbox" class="stocks-column-toggle" data-column="1" checked><span>Image</span></label>
                        <label class="filter-checkbox d-flex align-items-center gap-2"><input type="checkbox" class="stocks-column-toggle" data-column="2" checked><span>SKU</span></label>
                        <label class="filter-checkbox d-flex align-items-center gap-2"><input type="checkbox" class="stocks-column-toggle" data-column="3" checked><span>No.</span></label>
                        <label class="filter-checkbox d-flex align-items-center gap-2"><input type="checkbox" class="stocks-column-toggle" data-column="4" checked><span>Part No.</span></label>
                        <label class="filter-checkbox d-flex align-items-center gap-2"><input type="checkbox" class="stocks-column-toggle" data-column="5" checked><span>Part Name</span></label>
                        <label class="filter-checkbox d-flex align-items-center gap-2"><input type="checkbox" class="stocks-column-toggle" data-column="6" checked><span>Price</span></label>
                        <label class="filter-checkbox d-flex align-items-center gap-2"><input type="checkbox" class="stocks-column-toggle" data-column="7" checked><span>Stocks</span></label>
                        <label class="filter-checkbox d-flex align-items-center gap-2"><input type="checkbox" class="stocks-column-toggle" data-column="8" checked><span>Beg. Inv.</span></label>
                        <label class="filter-checkbox d-flex align-items-center gap-2"><input type="checkbox" class="stocks-column-toggle" data-column="9" checked><span>Item's In</span></label>
                        <label class="filter-checkbox d-flex align-items-center gap-2"><input type="checkbox" class="stocks-column-toggle" data-column="10" checked><span>Remarks (In)</span></label>
                        <label class="filter-checkbox d-flex align-items-center gap-2"><input type="checkbox" class="stocks-column-toggle" data-column="11" checked><span>Item's Out</span></label>
                        <label class="filter-checkbox d-flex align-items-center gap-2"><input type="checkbox" class="stocks-column-toggle" data-column="12" checked><span>Remarks (Out)</span></label>
                        <label class="filter-checkbox d-flex align-items-center gap-2"><input type="checkbox" class="stocks-column-toggle" data-column="13" checked><span>Actions</span></label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="applyStocksFiltersBtn">Apply Filters</button>
                <button type="button" class="btn btn-danger" id="resetStocksFiltersBtn">Reset</button>
            </div>
        </div>
    </div>
</div>
<!-- =================== END STOCKS FILTER MODAL =================== -->

<script>
    (function () {
        const form = document.getElementById('stocksSearchForm');
        const input = document.getElementById('stocksSearchInput');
        if (!form || !input) {
            return;
        }

        let timer = null;
        input.addEventListener('input', function () {
            if (timer) {
                clearTimeout(timer);
            }
            timer = setTimeout(function () {
                form.submit();
            }, 300);
        });

        form.addEventListener('reset', function () {
            setTimeout(function () {
                form.submit();
            }, 0);
        });

        const stocksFilterModal = document.getElementById('stocksFilterModal');
        const transferSelectedStocksBtn = document.getElementById('transferSelectedStocksBtn');
        const selectedStocksCount = document.getElementById('selectedStocksCount');
        const selectAllTransferStocks = document.getElementById('selectAllTransferStocks');
        const transferCheckboxes = document.querySelectorAll('.transfer-stock-checkbox');
        const bulkTransferModalEl = document.getElementById('bulkTransferModal');
        const bulkToBranch = document.getElementById('bulkToBranch');
        const bulkRecipientUser = document.getElementById('bulkRecipientUser');
        const bulkTransferItemsBody = document.getElementById('bulkTransferItemsBody');
        const bulkTransferAlert = document.getElementById('bulkTransferAlert');
        const confirmBulkTransferBtn = document.getElementById('confirmBulkTransferBtn');
        const stocksLevelRadios = document.querySelectorAll('input[name="stocksLevelFilter"]');
        const stocksColumnToggles = document.querySelectorAll('.stocks-column-toggle');
        const applyStocksFiltersBtn = document.getElementById('applyStocksFiltersBtn');
        const resetStocksFiltersBtn = document.getElementById('resetStocksFiltersBtn');
        const stocksFilterByField = document.getElementById('stocksFilterByField');
        const stocksFilterByValue = document.getElementById('stocksFilterByValue');
        const stocksTable = document.getElementById('stocksDetailsTable');
        let selectedStocksLevelFilter = form.querySelector('input[name="low"]') ? 'low' : 'all';
        let selectedStocksTextField = 'all';
        let selectedStocksTextValue = '';

        const setLowFilterInput = function (enabled) {
            const existing = form.querySelector('input[name="low"]');
            if (enabled) {
                if (!existing) {
                    const lowInput = document.createElement('input');
                    lowInput.type = 'hidden';
                    lowInput.name = 'low';
                    lowInput.value = '1';
                    form.appendChild(lowInput);
                } else {
                    existing.value = '1';
                }
                return;
            }

            if (existing) {
                existing.remove();
            }
        };

        const getStockFromRow = function (row) {
            const cell = row.children[7];
            if (!cell) return 0;
            const n = parseInt((cell.textContent || '').replace(/[^0-9-]/g, ''), 10);
            return Number.isNaN(n) ? 0 : n;
        };

        const applyStocksLevelFilter = function () {
            if (!stocksTable) return;
            const bodyRows = stocksTable.querySelectorAll('tbody tr');

            const getFieldText = function (row, field) {
                const map = {
                    sku: 2,
                    no: 3,
                    part_no: 4,
                    part_name: 5,
                };

                if (field === 'all') {
                    return row.textContent || '';
                }

                const idx = map[field];
                if (typeof idx === 'undefined') {
                    return row.textContent || '';
                }

                return row.children[idx] ? (row.children[idx].textContent || '') : '';
            };

            bodyRows.forEach((row) => {
                if (row.children.length < 14) {
                    return;
                }

                const stockValue = getStockFromRow(row);
                let show = true;

                const candidate = getFieldText(row, selectedStocksTextField).toLowerCase();
                const hasTextFilter = selectedStocksTextValue !== '';
                const textMatch = !hasTextFilter || candidate.includes(selectedStocksTextValue.toLowerCase());

                if (selectedStocksLevelFilter === 'low') {
                    show = stockValue <= 10;
                } else if (selectedStocksLevelFilter === 'out') {
                    show = stockValue === 0;
                }

                row.style.display = (show && textMatch) ? '' : 'none';
            });
        };

        const applyStocksColumnVisibility = function () {
            if (!stocksTable) return;
            stocksColumnToggles.forEach((toggle) => {
                const idx = parseInt(toggle.getAttribute('data-column') || '-1', 10);
                if (idx < 0) return;
                stocksTable.querySelectorAll('tr').forEach((row) => {
                    const cells = row.querySelectorAll('th, td');
                    if (cells[idx]) {
                        cells[idx].style.display = toggle.checked ? '' : 'none';
                    }
                });
            });
        };

        const closeStocksFilterModal = function () {
            if (!stocksFilterModal) return;
            const instance = bootstrap.Modal.getOrCreateInstance(stocksFilterModal);
            instance.hide();
        };

        const getSelectedTransferCheckboxes = function () {
            return Array.from(document.querySelectorAll('.transfer-stock-checkbox:checked'));
        };

        const updateTransferSelectionState = function () {
            const selected = getSelectedTransferCheckboxes();
            const count = selected.length;

            if (selectedStocksCount) {
                selectedStocksCount.textContent = String(count);
            }
            if (transferSelectedStocksBtn) {
                transferSelectedStocksBtn.disabled = count === 0;
            }

            if (selectAllTransferStocks) {
                const enabled = Array.from(transferCheckboxes).filter((cb) => !cb.disabled);
                const checkedEnabled = enabled.filter((cb) => cb.checked);
                selectAllTransferStocks.checked = enabled.length > 0 && checkedEnabled.length === enabled.length;
                selectAllTransferStocks.indeterminate = checkedEnabled.length > 0 && checkedEnabled.length < enabled.length;
            }
        };

        transferCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', function () {
                if (!this.checked) {
                    updateTransferSelectionState();
                    return;
                }

                updateTransferSelectionState();
            });
        });

        if (selectAllTransferStocks) {
            selectAllTransferStocks.addEventListener('change', function () {
                const rows = Array.from(document.querySelectorAll('#stocksDetailsTable tbody tr'));
                rows.forEach((row) => {
                    if (row.style.display === 'none') return;
                    const checkbox = row.querySelector('.transfer-stock-checkbox');
                    if (!checkbox || checkbox.disabled) return;
                    checkbox.checked = this.checked;
                });

                updateTransferSelectionState();
            });
        }

        if (transferSelectedStocksBtn && bulkTransferModalEl) {
            transferSelectedStocksBtn.addEventListener('click', function () {
                const selected = getSelectedTransferCheckboxes();
                if (selected.length === 0) {
                    return;
                }

                if (bulkTransferItemsBody) {
                    bulkTransferItemsBody.innerHTML = '';
                    selected.forEach((item, index) => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${item.dataset.sku || '-'}</td>
                            <td>${item.dataset.partName || '-'}</td>
                            <td>${item.dataset.available || '0'}</td>
                            <td>
                                <input
                                    type="number"
                                    class="form-control form-control-sm bulk-transfer-qty"
                                    min="1"
                                    max="${item.dataset.available || '1'}"
                                    value="1"
                                    data-stock-id="${item.value}"
                                    data-product-id="${item.dataset.productId}"
                                    data-source-branch="${item.dataset.sourceBranch || ''}"
                                    data-max="${item.dataset.available || '1'}"
                                >
                            </td>
                        `;
                        bulkTransferItemsBody.appendChild(tr);
                    });
                }

                if (bulkToBranch) {
                    bulkToBranch.value = '';
                }

                if (bulkRecipientUser) {
                    bulkRecipientUser.innerHTML = '<option value="">Select destination branch first</option>';
                }

                if (bulkTransferAlert) {
                    bulkTransferAlert.classList.add('d-none');
                    bulkTransferAlert.textContent = '';
                }

                bootstrap.Modal.getOrCreateInstance(bulkTransferModalEl).show();
            });
        }

        if (bulkToBranch && bulkRecipientUser) {
            bulkToBranch.addEventListener('change', function () {
                const branch = this.value.trim();
                bulkRecipientUser.innerHTML = '<option value="">Select recipient</option>';

                if (!branch) {
                    bulkRecipientUser.innerHTML = '<option value="">Select destination branch first</option>';
                    return;
                }

                fetch(`<?= base_url('suppliers/get-recipients-by-branch') ?>?branch=${encodeURIComponent(branch)}`)
                    .then((response) => response.json())
                    .then((data) => {
                        if (!data.success || !Array.isArray(data.data) || data.data.length === 0) {
                            bulkRecipientUser.innerHTML = '<option value="">No admin recipient found for this branch</option>';
                            return;
                        }

                        data.data.forEach((user) => {
                            const option = document.createElement('option');
                            option.value = user.id;
                            option.textContent = `${user.name} (${user.username})`;
                            bulkRecipientUser.appendChild(option);
                        });
                    })
                    .catch(() => {
                        bulkRecipientUser.innerHTML = '<option value="">Failed to load recipients</option>';
                    });
            });
        }

        if (confirmBulkTransferBtn) {
            confirmBulkTransferBtn.addEventListener('click', async function () {
                const toBranch = bulkToBranch ? bulkToBranch.value.trim() : '';
                const recipientUserId = bulkRecipientUser ? bulkRecipientUser.value.trim() : '';
                const qtyInputs = Array.from(document.querySelectorAll('.bulk-transfer-qty'));

                const items = [];
                let hasInvalidQty = false;

                qtyInputs.forEach((input) => {
                    const quantity = parseInt(input.value || '0', 10);
                    const max = parseInt(input.dataset.max || '0', 10);
                    const stockId = parseInt(input.dataset.stockId || '0', 10);
                    const productId = parseInt(input.dataset.productId || '0', 10);

                    if (!Number.isInteger(quantity) || quantity <= 0 || quantity > max) {
                        hasInvalidQty = true;
                        return;
                    }

                    items.push({
                        stock_id: stockId,
                        product_id: productId,
                        source_branch: (input.dataset.sourceBranch || '').trim(),
                        quantity: quantity,
                    });
                });

                if (!toBranch) {
                    if (bulkTransferAlert) {
                        bulkTransferAlert.textContent = 'Destination branch is required.';
                        bulkTransferAlert.classList.remove('d-none');
                    }
                    return;
                }

                if (!recipientUserId) {
                    if (bulkTransferAlert) {
                        bulkTransferAlert.textContent = 'Recipient is required.';
                        bulkTransferAlert.classList.remove('d-none');
                    }
                    return;
                }

                if (items.length === 0 || hasInvalidQty) {
                    if (bulkTransferAlert) {
                        bulkTransferAlert.textContent = 'Please set a valid transfer quantity for every selected stock.';
                        bulkTransferAlert.classList.remove('d-none');
                    }
                    return;
                }

                const formData = new FormData();
                formData.append('to_branch', toBranch);
                formData.append('recipient_user_id', recipientUserId);
                formData.append('items', JSON.stringify(items));

                try {
                    const response = await fetch(`<?= base_url('suppliers/store-bulk-transfer') ?>`, {
                        method: 'POST',
                        body: formData,
                    });
                    const data = await response.json();

                    if (!data.success) {
                        if (bulkTransferAlert) {
                            bulkTransferAlert.textContent = data.message || 'Failed to create transfers.';
                            bulkTransferAlert.classList.remove('d-none');
                        }
                        return;
                    }

                    window.location.href = `<?= base_url('suppliers') ?>`;
                } catch (error) {
                    if (bulkTransferAlert) {
                        bulkTransferAlert.textContent = error.message || 'Unexpected error while creating transfers.';
                        bulkTransferAlert.classList.remove('d-none');
                    }
                }
            });
        }

        if (stocksFilterModal && applyStocksFiltersBtn && resetStocksFiltersBtn) {
            stocksFilterModal.addEventListener('show.bs.modal', function () {
                stocksLevelRadios.forEach((radio) => {
                    radio.checked = radio.value === selectedStocksLevelFilter;
                });
                if (stocksFilterByField) {
                    stocksFilterByField.value = selectedStocksTextField;
                }
                if (stocksFilterByValue) {
                    stocksFilterByValue.value = selectedStocksTextValue;
                }
            });

            stocksColumnToggles.forEach((toggle) => {
                toggle.addEventListener('change', applyStocksColumnVisibility);
            });

            if (applyStocksFiltersBtn) {
                applyStocksFiltersBtn.addEventListener('click', function () {
                    const hadLowFilter = !!form.querySelector('input[name="low"]');
                    const checkedRadio = document.querySelector('input[name="stocksLevelFilter"]:checked');
                    selectedStocksLevelFilter = checkedRadio ? checkedRadio.value : 'all';
                    selectedStocksTextField = stocksFilterByField ? stocksFilterByField.value : 'all';
                    selectedStocksTextValue = stocksFilterByValue ? stocksFilterByValue.value.trim() : '';

                    // Keep existing server-side low filter compatibility when chosen.
                    setLowFilterInput(selectedStocksLevelFilter === 'low');
                    const hasLowFilter = !!form.querySelector('input[name="low"]');

                    applyStocksLevelFilter();
                    applyStocksColumnVisibility();

                    closeStocksFilterModal();

                    // Sync with server only when switching into/out of low-only mode.
                    if (hadLowFilter !== hasLowFilter) {
                        form.submit();
                    }
                });
            }

            if (resetStocksFiltersBtn) {
                resetStocksFiltersBtn.addEventListener('click', function () {
                    const hadLowFilter = !!form.querySelector('input[name="low"]');
                    selectedStocksLevelFilter = 'all';
                    setLowFilterInput(false);

                    stocksLevelRadios.forEach((radio) => {
                        radio.checked = radio.value === 'all';
                    });

                    selectedStocksTextField = 'all';
                    selectedStocksTextValue = '';
                    if (stocksFilterByField) {
                        stocksFilterByField.value = 'all';
                    }
                    if (stocksFilterByValue) {
                        stocksFilterByValue.value = '';
                    }

                    stocksColumnToggles.forEach((toggle) => {
                        toggle.checked = true;
                    });

                    applyStocksLevelFilter();
                    applyStocksColumnVisibility();

                    closeStocksFilterModal();

                    // If we were on server-side low-only results, reload full list.
                    if (hadLowFilter) {
                        form.submit();
                    }
                });
            }

            applyStocksLevelFilter();
            applyStocksColumnVisibility();
        }

        // Request Stock Modal Functionality
        const requestProductSelect = document.getElementById('request_product_id');
        const requestQuantityInput = document.getElementById('request_quantity');
        const requestSummary = document.getElementById('request_summary');
        const requestProductInfo = document.getElementById('request_product_info');
        const requestQuantityInfo = document.getElementById('request_quantity_info');

        function updateRequestSummary() {
            const selectedOption = requestProductSelect.options[requestProductSelect.selectedIndex];
            const quantity = parseInt(requestQuantityInput.value) || 0;

            if (selectedOption.value && quantity > 0) {
                const productName = selectedOption.text;
                const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;

                requestProductInfo.innerHTML = `<strong>Product:</strong> ${productName}`;
                requestQuantityInfo.innerHTML = `<strong>Quantity:</strong> ${quantity} units`;
                requestSummary.style.display = 'block';
            } else {
                requestSummary.style.display = 'none';
            }
        }

        if (requestProductSelect) {
            requestProductSelect.addEventListener('change', updateRequestSummary);
        }

        if (requestQuantityInput) {
            requestQuantityInput.addEventListener('input', updateRequestSummary);
        }

        updateTransferSelectionState();
    })();
</script>

<?php $this->endSection(); ?>