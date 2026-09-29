<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>


<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: #2a2a2a;
        margin: 0;
        white-space: nowrap;
    }

    .page-title-block {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .page-title-view-toggle {
        display: flex;
        align-items: center;
        gap: 0;
    }

    .btn-add-product {
        background: linear-gradient(135deg, #FF8C42 0%, #FF7A2F 100%);
        color: white;
        padding: 10px 16px;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-add-product:hover {
        background: linear-gradient(135deg, #FF7A2F 0%, #FF6820 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 140, 66, 0.3);
    }

    .btn-import-csv {
        background: linear-gradient(135deg, #28a745 0%, #218838 100%);
        color: white;
        padding: 10px 14px;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-import-csv:hover {
        background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }

    .btn-export-pdf {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: white;
        padding: 10px 14px;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-export-pdf:hover,
    .btn-export-pdf:focus {
        background: linear-gradient(135deg, #0a58ca 0%, #084298 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
    }

    .btn-view-toggle {
        background: #fff;
        color: #555;
        border: 1px solid #d9d9d9;
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .page-title-view-toggle .btn-view-toggle + .btn-view-toggle {
        margin-left: 4px;
    }

    .btn-view-toggle:hover {
        background: #f8f9fa;
        color: #0d6efd;
        border-color: #9ec5fe;
    }

    .btn-view-toggle.active {
        background: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }

    .btn-filter {
        background: white;
        color: #666;
        padding: 9px 12px;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-filter:hover {
        background: #f5f5f5;
        border-color: #FF8C42;
        color: #FF8C42;
    }

    .product-search-wrap {
        min-width: 250px;
        width: 290px;
        flex: 0 1 290px;
    }

    .manage-search-group {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .manage-search-input {
        border: 1px solid #e0e0e0;
        border-right: 0;
        padding: 9px 12px;
        font-size: 13px;
        border-radius: 0;
    }

    .manage-search-input:focus {
        border-color: #FF8C42;
        box-shadow: 0 0 0 0.2rem rgba(255, 140, 66, 0.15);
        position: relative;
        z-index: 2;
    }

    .manage-search-btn {
        border: 1px solid #e0e0e0;
        background: #fff;
        color: #666;
        min-width: 40px;
        padding: 0 10px;
        transition: all 0.2s ease;
    }

    .manage-search-btn:hover,
    .manage-search-btn:focus {
        border-color: #FF8C42;
        color: #FF8C42;
        background: #fff3f0;
        box-shadow: none;
    }

    .page-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: nowrap;
        justify-content: flex-end;
    }

    .page-actions-buttons {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: nowrap;
        margin-left: 8px;
    }

    .select-all-wrap {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border: 1px solid #f1c7b4;
        border-radius: 8px;
        background: #fff7f3;
        color: #7a3d1c;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }

    .select-all-wrap input {
        width: 18px;
        height: 18px;
        margin: 0;
        cursor: pointer;
    }

    .page-actions .btn-group > .btn {
        white-space: nowrap;
    }

    .product-select-wrap {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #fff;
        border-radius: 6px;
        padding: 2px 6px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        z-index: 2;
    }

    .product-select {
        margin: 0;
        cursor: pointer;
        width: 18px;
        height: 18px;
    }

    .product-select-all {
        margin: 0;
        cursor: pointer;
        width: 18px;
        height: 18px;
    }

    .products-table .col-select {
        width: 42px;
        text-align: center;
    }

    .products-table thead th.no-sort {
        padding-right: 0;
        cursor: default;
    }

    .products-table thead th.col-select::before,
    .products-table thead th.col-select::after,
    .products-table thead th.col-select.sortable::before,
    .products-table thead th.col-select.sortable::after,
    .products-table thead th.col-select.sorting_asc::before,
    .products-table thead th.col-select.sorting_asc::after,
    .products-table thead th.col-select.sorting_desc::before,
    .products-table thead th.col-select.sorting_desc::after {
        content: none;
    }

    .product-search-input {
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        padding: 10px 14px;
        font-size: 14px;
    }

    .product-search-input:focus {
        border-color: #FF8C42;
        box-shadow: 0 0 0 0.2rem rgba(255, 140, 66, 0.15);
    }

    .dropdown-menu {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        min-width: 200px;
        max-height: 280px;
        overflow-y: auto;
    }

    .dropdown-item {
        padding: 10px 16px;
        font-size: 14px;
        color: #666;
        transition: all 0.2s ease;
    }

    .dropdown-item:hover {
        background: #fff3f0;
        color: #FF8C42;
    }

    .dropdown-item i {
        margin-right: 8px;
        width: 14px;
    }

    .dropdown-item.manage-item {
        font-weight: 600;
    }

    .dropdown-item.manage-item.add {
        color: #198754;
    }

    .dropdown-item.manage-item.edit {
        color: #fd7e14;
    }

    .dropdown-item.manage-item.delete {
        color: #dc3545;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
        width: 100%;
        overflow-x: hidden;
    }

    .products-table-wrap {
        background: #fff;
        border: 1px solid #ececec;
        border-radius: 10px;
        overflow: hidden;
    }

    .products-table {
        margin-bottom: 0;
        font-size: 14px;
    }

    .products-table thead th {
        background: #f8f9fa;
        color: #2a2a2a;
        font-weight: 600;
        white-space: nowrap;
        border-bottom: 1px solid #ececec;
        padding: 10px 12px;
    }

    .products-table tbody td {
        vertical-align: middle;
        padding: 10px 12px;
        border-top: 1px solid #f1f1f1;
    }

    .products-table .table-product-thumb {
        width: 42px;
        height: 42px;
        border-radius: 6px;
        object-fit: cover;
        background: #f8f8f8;
    }

    .products-table .table-price {
        color: #FF8C42;
        font-weight: 700;
    }

    .products-table .table-actions {
        white-space: nowrap;
    }

    .products-table .table-action-btn {
        padding: 4px 10px;
        font-size: 12px;
        margin-right: 6px;
        border: none;
        border-radius: 6px;
        color: #fff;
    }

    .products-table .table-action-btn:last-child {
        margin-right: 0;
    }

    .products-table .table-action-btn.view {
        background: #2196f3;
    }

    .products-table .table-action-btn.edit {
        background: #ff751f;
    }

    .products-table .table-action-btn.delete {
        background: #f44336;
    }

    .products-table thead th.sortable,
    .products-table thead th.sorting_asc,
    .products-table thead th.sorting_desc {
        position: relative;
        padding-right: 1.35rem;
        cursor: pointer;
        user-select: none;
        transition: background-color 0.2s ease;
    }

    .products-table thead th.sortable:hover {
        background: #f0f1f2;
    }

    .products-table thead th.sortable::before,
    .products-table thead th.sortable::after,
    .products-table thead th.sorting_asc::before,
    .products-table thead th.sorting_asc::after,
    .products-table thead th.sorting_desc::before,
    .products-table thead th.sorting_desc::after {
        position: absolute;
        right: 0.5rem;
        font-size: 0.65rem;
        line-height: 1;
        color: #b8c2cc;
        pointer-events: none;
    }

    .products-table thead th.sortable::before,
    .products-table thead th.sorting_asc::before,
    .products-table thead th.sorting_desc::before {
        content: '▲';
        top: calc(50% - 0.55rem);
    }

    .products-table thead th.sortable::after,
    .products-table thead th.sorting_asc::after,
    .products-table thead th.sorting_desc::after {
        content: '▼';
        top: calc(50% + 0.05rem);
    }

    .products-table thead th.sorting_asc::before {
        color: #f57c00;
    }

    .products-table thead th.sorting_asc::after {
        color: #d3d9df;
    }

    .products-table thead th.sorting_desc::before {
        color: #d3d9df;
    }

    .products-table thead th.sorting_desc::after {
        color: #f57c00;
    }

    .product-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #f0f0f0;
        display: flex;
        flex-direction: column;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12);
    }

    .product-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: #f8f8f8;
    }

    .product-info {
        padding: 20px;
        background-color: #fff3bc;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .product-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .product-price {
        font-size: 24px;
        font-weight: 700;
        color: #FF8C42;
    }

    .product-sold {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .product-desc {
        font-size: 14px;
        color: #666;
        line-height: 1.5;
        margin-bottom: 15px;
        min-height: 42px;
    }

    .product-footer {
        display: flex;
        gap: 10px;
        padding-top: 15px;
        border-top: 1px solid #f0f0f0;
        margin-top: auto;
    }

    .btn-action {
        flex: 1;
        padding: 10px;
        border: 1px solid #e0e0e0;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 13px;
        font-weight: 500;
        color: #666;
    }

    .btn-action:hover {
        background: #f8f9fa;
        border-color: #FF8C42;
        color: #FF8C42;
    }

    .btn-action.primary {
        background: #2196f3;
        color: white;
        border: none;
    }

    .btn-action.primary:hover {
        background: #1976d2;
        color: white;
    }

    .btn-action.edit {
        background: #ff751f;
        color: white;
        border: none;
    }

    .btn-action.edit:hover {
        background: #f57c00;
        color: white;
    }

    .btn-action.delete {
        background: #f44336;
        color: white;
        border: none;
    }

    .btn-action.delete:hover {
        background: #c62828;
        color: white;
    }

    @media (max-width: 992px) {
        .page-header {
            flex-direction: column;
            gap: 15px;
            align-items: stretch;
        }

        .page-title {
            font-size: 24px;
        }

        .page-title-block {
            gap: 10px;
        }

        .page-actions {
            width: 100%;
            gap: 10px;
            justify-content: flex-end;
        }

        .product-search-wrap {
            min-width: 280px;
            width: auto;
            flex: 0 1 360px;
        }

        .page-actions-buttons {
            width: auto;
            flex-wrap: nowrap;
            flex: 0 0 auto;
            margin-left: 0;
        }

        .page-actions-buttons .btn-group {
            width: auto;
            flex: 0 0 auto;
        }

        .btn-filter,
        .btn-view-toggle,
        .btn-add-product,
        .btn-import-csv,
        .btn-export-pdf {
            width: auto;
            justify-content: center;
        }

        .products-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .product-image {
            height: 130px;
        }

        .product-info {
            padding: 12px;
        }

        .product-price {
            font-size: 18px;
        }

        .product-desc {
            font-size: 13px;
            margin-bottom: 10px;
            min-height: 36px;
        }

        .product-footer {
            gap: 6px;
            padding-top: 10px;
        }

        .btn-action {
            padding: 8px;
            font-size: 12px;
        }
    }

    @media (max-width: 768px) {
        .page-actions {
            align-items: center;
        }

        .page-actions-buttons {
            width: auto;
            flex-wrap: nowrap;
            margin-left: 0;
        }

        .page-actions-buttons .btn-group {
            width: auto;
            flex: 0 0 auto;
        }

        .product-search-wrap {
            width: 100%;
            flex: 1 1 100%;
        }

        .manage-search-btn {
            min-width: 42px;
            padding: 0;
        }

        .btn-filter,
        .btn-view-toggle,
        .btn-add-product,
        .btn-import-csv,
        .btn-export-pdf {
            width: 44px;
            min-width: 44px;
            height: 44px;
            padding: 0;
            justify-content: center;
            gap: 0;
            flex: 0 0 44px;
            border-radius: 10px;
        }

        .btn-filter .btn-label,
        .btn-view-toggle .btn-label,
        .btn-add-product .btn-label,
        .btn-import-csv .btn-label,
        .btn-export-pdf .btn-label {
            display: none;
        }

        .btn-filter::after,
        .btn-export-pdf::after {
            display: none;
        }

        .products-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .products-table {
            font-size: 13px;
        }

        .product-image {
            height: 120px;
        }

        .product-card {
            min-width: 0;
        }

        .product-footer {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 6px;
        }

        .btn-action {
            width: 100%;
            flex: 0 0 auto;
        }

        .products-table .table-action-btn {
            padding: 4px 8px;
            font-size: 11px;
        }
    }

    @media (max-width: 420px) {
        .products-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .product-image {
            height: 105px;
        }

        .product-price {
            font-size: 16px;
        }

        .product-desc {
            font-size: 12px;
        }

        .btn-action {
            width: 100%;
            flex: 0 0 auto;
            font-size: 11px;
            padding: 7px;
        }
    }
</style>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('warning')): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('warning') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php
$productsInput = isset($products) ? $products : null;
$products = is_array($productsInput) ? $productsInput : [];
$categoriesInput = isset($categories) ? $categories : null;
$categories = is_array($categoriesInput) ? $categoriesInput : [];
$canCrossBranchView = (bool) ($canCrossBranchView ?? false);
$availableBranchesInput = isset($availableBranches) ? $availableBranches : null;
$availableBranches = is_array($availableBranchesInput) ? $availableBranchesInput : [];
$activeBranchFilter = trim((string) ($activeBranchFilter ?? ''));
$activeBranchMode = trim((string) ($activeBranchMode ?? ($activeBranchFilter !== '' ? 'branch' : 'all')));
$userBranch = trim((string) ($userBranch ?? session()->get('branch') ?? ''));
$currentRole = trim((string) ($currentRole ?? session()->get('role') ?? ''));
$showBranchColumn = $canCrossBranchView;
$exportExcelUrl = base_url('products/export/excel');
$exportPdfUrl = base_url('products/export/pdf');

$normalizeBranchOption = static function (string $branch): string {
    $branch = strtolower(trim($branch));
    $branch = preg_replace('/\s+/', ' ', $branch) ?? $branch;
    $branch = preg_replace('/\s+branch$/i', '', $branch) ?? $branch;
    return trim($branch);
};

$userBranchKey = $normalizeBranchOption($userBranch);
$seenBranchKeys = [];
$filteredBranchOptions = [];
foreach ($availableBranches as $branchOption) {
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

if ($canCrossBranchView) {
    $qs = '';
    if ($activeBranchMode === 'mine') {
        $qs = '?branch=mine';
    } elseif ($activeBranchMode === 'branch' && $activeBranchFilter !== '') {
        $qs = '?branch=' . rawurlencode($activeBranchFilter);
    }

    $exportExcelUrl .= $qs;
    $exportPdfUrl .= $qs;
}
?>

<div class="page-header">
    <div class="page-title-block">
        <h1 class="page-title">Products & Spare Parts</h1>
        <div class="btn-group page-title-view-toggle products-view-toggle" role="group" aria-label="Product view mode">
            <button type="button" class="btn btn-view-toggle active" id="productsGridViewBtn" data-view="grid" title="Grid view">
                <i class="bi bi-grid"></i><span class="btn-label"></span>
            </button>
            <button type="button" class="btn btn-view-toggle" id="productsTableViewBtn" data-view="table" title="Table view">
                <i class="bi bi-table"></i><span class="btn-label"></span>
            </button>
        </div>
    </div>
    <div class="page-actions d-flex align-items-center gap-2">
        <form id="productsSearchForm" class="product-search-wrap" autocomplete="off">
            <div class="input-group manage-search-group">
                <input type="text" name="search" class="form-control manage-search-input" placeholder="Search by SKU, Part Name..." value="" id="productSearchInput">
                <button class="btn manage-search-btn" type="button" data-bs-toggle="modal" data-bs-target="#productsFilterModal" aria-label="Filter" title="Filter"><i class="bi bi-funnel"></i></button>
                <button class="btn manage-search-btn" type="submit" title="Search"><i class="bi bi-search"></i></button>
            </div>
        </form>
        <div class="page-actions-buttons">
            <?php if ($canCrossBranchView): ?>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-filter dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Filter branches" title="Branches">
                    <span class="btn-label"> Branch: <?= esc($branchButtonLabel) ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item <?= $activeBranchMode === 'mine' ? 'active' : '' ?>" href="<?= base_url('products?branch=mine') ?>"><i class="bi bi-person-badge me-2"></i>My Products</a></li>
                    <li><a class="dropdown-item <?= $activeBranchMode === 'all' ? 'active' : '' ?>" href="<?= base_url('products?branch=all') ?>"><i class="bi bi-geo-alt me-2"></i>All Branches</a></li>
                    <?php foreach ($filteredBranchOptions as $branchOption): ?>
                    <li>
                        <a class="dropdown-item <?= strcasecmp($activeBranchFilter, (string) $branchOption) === 0 ? 'active' : '' ?>"
                            href="<?= base_url('products') . '?branch=' . rawurlencode((string) $branchOption) ?>">
                            <i class="bi bi-shop me-2"></i><?= esc($branchOption) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-filter dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Filter categories" title="Categories">
                    <span class="btn-label"> Categories</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="px-2 pt-2 pb-1">
                        <input type="text" id="catSearchInput" class="form-control form-control-sm" placeholder="Search category..." autocomplete="off">
                    </li>
                    <li>
                        <button type="button" class="dropdown-item manage-item add" data-bs-toggle="modal" data-bs-target="#productAddCategoryModal">
                            <i class="fas fa-plus-circle"></i> New Category
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item manage-item edit" data-bs-toggle="modal" data-bs-target="#productEditCategoryModal">
                            <i class="fas fa-edit"></i> Edit Category
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item manage-item delete" data-bs-toggle="modal" data-bs-target="#productDeleteCategoryModal">
                            <i class="fas fa-trash-alt"></i> Delete Category
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item cat-filter active" href="#" data-cat=""><i class="fas fa-th-list"></i> All Categories</a></li>
                    <?php if (!empty($categories)): ?>
                    <li><hr class="dropdown-divider"></li>
                    <?php foreach ($categories as $c): ?>
                    <?php if (!is_array($c)) { continue; } ?>
                    <li class="cat-filter-item"><a class="dropdown-item cat-filter" href="#" data-cat="<?= (int) ($c['category_id'] ?? 0) ?>"><i class="fas fa-tag"></i> <?= esc((string) ($c['category_name'] ?? '')) ?></a></li>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    <li class="cat-no-results d-none px-3 py-2 text-muted small">No categories found.</li>
                </ul>
            </div>
            <button class="btn-add-product" data-bs-toggle="modal" data-bs-target="#addProductModal" aria-label="Add new product" title="Add New Product">
                <i class="fas fa-plus-circle"></i><span class="btn-label"> Add Product</span>
            </button>
            <button class="btn-import-csv" data-bs-toggle="modal" data-bs-target="#importProductModal" aria-label="Import CSV" title="Import CSV">
                <i class="fas fa-file-csv"></i><span class="btn-label"> Import CSV</span>
            </button>
            <?php if (in_array($currentRole, ['admin', 'super_admin'], true)): ?>
            <button class="btn btn-outline-danger" id="deleteSelectedBtn" data-bs-toggle="modal" data-bs-target="#deleteSelectedProductsModal" aria-label="Delete selected products" title="Delete selected products" disabled>
                <i class="fas fa-trash-alt"></i><span class="btn-label"> Delete</span>
            </button>
            <?php endif; ?>
            <div class="btn-group" role="group">
                <button class="btn btn-export-pdf dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Export" title="Export">
                    <i class="bi bi-download"></i><span class="btn-label">Export</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= $exportExcelUrl ?>"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Export Excel</a></li>
                    <li><a class="dropdown-item" href="<?= $exportPdfUrl ?>" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf me-2"></i>Export PDF</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="productsFilterModal" tabindex="-1" aria-labelledby="productsFilterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productsFilterLabel">Filter Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="filter-section mb-3">
                    <h6 class="filter-section-title">Stock Status</h6>
                    <div class="filter-radios">
                        <label class="filter-radio"><input type="radio" name="productsStatusFilter" value="all" checked><span>All</span></label>
                        <label class="filter-radio"><input type="radio" name="productsStatusFilter" value="available"><span>Available</span></label>
                        <label class="filter-radio"><input type="radio" name="productsStatusFilter" value="low stock"><span>Low Stock</span></label>
                        <label class="filter-radio"><input type="radio" name="productsStatusFilter" value="out of stock"><span>Out of Stock</span></label>
                    </div>
                </div>

                <div class="filter-section mb-0">
                    <h6 class="filter-section-title">Filter By</h6>
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-5">
                            <label for="productsFilterByField" class="form-label mb-1">Field</label>
                            <select id="productsFilterByField" class="form-select form-select-sm">
                                <option value="all">All Fields</option>
                                <option value="name">Part Name</option>
                                <option value="sku">SKU</option>
                                <option value="partno">Part No</option>
                                <option value="category">Category</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-7">
                            <label for="productsFilterByValue" class="form-label mb-1">Keyword</label>
                            <input type="text" id="productsFilterByValue" class="form-control form-control-sm" placeholder="Type a value to filter products">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="applyProductsFiltersBtn">Apply Filters</button>
                <button type="button" class="btn btn-danger" id="resetProductsFiltersBtn">Reset</button>
            </div>
        </div>
    </div>
</div>

<div class="products-grid" id="productsGridView">
<?php
    $defaultProductImage = base_url('images/products/default.svg');

    // Helper: find image URL for a product with cache-busting
    function productImgUrl(int $id, string $defaultImage): string {
        foreach (['jpg','jpeg','png','webp','gif'] as $ext) {
            $imagePath = FCPATH . 'images/products/p' . $id . '.' . $ext;
            if (file_exists($imagePath)) {
                // Add cache-busting query parameter using file modification time
                $mtime = filemtime($imagePath);
                return base_url('images/products/p' . $id . '.' . $ext) . '?v=' . $mtime;
            }
        }

        return $defaultImage;
    }
    // Helper: compute status from stock
    function stockStatus(int $qty): string {
        if ($qty <= 0) return 'Out of Stock';
        if ($qty <= 5) return 'Low Stock';
        return 'Available';
    }

    function normalizeBranchView(string $branch): string {
        $branch = strtolower(trim($branch));
        $branch = preg_replace('/\s+/', ' ', $branch) ?? $branch;
        $branch = preg_replace('/\s+branch$/i', '', $branch) ?? $branch;

        return trim($branch);
    }
?>
<?php if (!empty($products)): ?>
    <?php foreach ($products as $p): ?>
    <?php if (!is_array($p)) { continue; } ?>
    <?php
        $pid      = (int) $p['product_id'];
        $imgUrl   = productImgUrl($pid, $defaultProductImage);
        $status   = stockStatus((int) $p['current_stock']);
        $catName  = esc((string) ($p['category_name'] ?? ''));
        $sku      = trim((string) ($p['sku'] ?? ''));
        $partNo   = trim((string) ($p['part_no'] ?? ''));
        $description = trim((string) ($p['description'] ?? $p['product_description'] ?? ''));
        $displayName = trim(trim((string) ($p['part_name'] ?? '')) . ' ' . $description);
        $rowBranch = trim((string) ($p['branch'] ?? ''));
        $canManageProduct = $currentRole === 'super_admin'
            || ($currentRole === 'admin' && normalizeBranchView($rowBranch) === normalizeBranchView($userBranch));
        $search   = strtolower(trim((string) ($p['part_name'] ?? '') . ' ' . $description . ' ' . $sku . ' ' . $partNo));
        $priceStr = '₱' . number_format((float) $p['price'], 2);
    ?>
    <div class="product-card"
        data-cat="<?= $p['category_id'] ?>"
        data-search="<?= esc($search, 'attr') ?>"
        data-name="<?= esc(strtolower((string) ($p['part_name'] ?? '')), 'attr') ?>"
        data-sku="<?= esc(strtolower($sku), 'attr') ?>"
        data-partno="<?= esc(strtolower($partNo), 'attr') ?>"
        data-stock="<?= (int) ($p['current_stock'] ?? 0) ?>"
        data-category="<?= esc(strtolower((string) ($p['category_name'] ?? '')), 'attr') ?>"
        data-status="<?= esc(strtolower($status), 'attr') ?>">
        <?php if ($canManageProduct): ?>
        <label class="product-select-wrap" title="Select product">
            <input type="checkbox" class="product-select" data-id="<?= $pid ?>">
        </label>
        <?php endif; ?>
        <img src="<?= $imgUrl ?>" alt="<?= esc((string) ($p['part_name'] ?? '')) ?>" class="product-image" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?= esc($defaultProductImage, 'attr') ?>';">
        <div class="product-info">
            <div class="product-header">
                <span class="product-price"><?= $priceStr ?></span>
                <span class="badge rounded-pill <?= $status === 'Available' ? 'text-bg-success' : ($status === 'Low Stock' ? 'bg-warning text-dark' : 'bg-danger-subtle text-danger border border-danger-subtle') ?>" style="padding:4px 10px; font-size:11px; font-weight:600;"><?= $status ?></span>
            </div>
            <div class="product-desc">
                <?= esc($displayName !== '' ? $displayName : (string) ($p['part_name'] ?? '')) ?>
                <?php if ($sku !== ''): ?><br><small class="text-muted">SKU: <?= esc($sku) ?></small><?php endif; ?>
                <?php if ($partNo !== '' && strcasecmp($partNo, $sku) !== 0): ?><br><small class="text-muted">Part No: <?= esc($partNo) ?></small><?php endif; ?>
                <?php if ($showBranchColumn): ?><br><small class="text-muted">Branch: <?= esc($rowBranch !== '' ? $rowBranch : 'Unassigned') ?></small><?php endif; ?>
            </div>
            <div class="product-footer">
                <button class="btn-action primary" data-bs-toggle="modal" data-bs-target="#viewProductModal"
                    data-name="<?= esc((string) ($p['part_name'] ?? '')) ?>"
                    data-description="<?= esc($description) ?>"
                    data-sku="<?= esc($sku) ?>"
                    data-partno="<?= esc($partNo) ?>"
                    data-upc="<?= esc((string) ($p['resolved_upc'] ?? '')) ?>"
                    data-barcode-type="<?= esc((string) ($p['barcode_type'] ?? '')) ?>"
                    data-label-brand="<?= esc((string) ($p['label_brand'] ?? '')) ?>"
                    data-label-short-name="<?= esc((string) ($p['label_short_name'] ?? '')) ?>"
                    data-price="<?= esc($priceStr) ?>"
                    data-sold="<?= $p['current_stock'] ?> pcs"
                    data-category="<?= $catName ?>"
                    data-stock="<?= $p['current_stock'] ?> pcs"
                    data-supplier="<?= esc((string) ($p['supplier_name'] ?? '')) ?>"
                    data-status="<?= esc($status) ?>"
                    data-img="<?= $imgUrl ?>">
                    <i class="fas fa-eye"></i> View
                </button>
                <?php if ($canManageProduct): ?>
                <button class="btn-action edit" data-bs-toggle="modal" data-bs-target="#editProductModal"
                    data-id="<?= $pid ?>"
                    data-name="<?= esc((string) ($p['part_name'] ?? '')) ?>"
                    data-description="<?= esc($description) ?>"
                    data-catid="<?= $p['category_id'] ?>"
                    data-supplierid="<?= $p['supplier_id'] ?? '' ?>"
                    data-sku="<?= esc($sku) ?>"
                    data-upc="<?= esc((string) ($p['resolved_upc'] ?? '')) ?>"
                    data-partno="<?= esc($partNo) ?>"
                    data-price="<?= esc((string) ($p['price'] ?? '')) ?>"
                    data-beg-inv="<?= (int) ($p['beg_inv'] ?? 0) ?>"
                    data-current-stock="<?= (int) ($p['current_stock'] ?? 0) ?>"
                    data-stock="<?= $p['current_stock'] ?> pcs"
                    data-status="<?= esc($status) ?>"
                    data-img="<?= $imgUrl ?>">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn-action delete" data-bs-toggle="modal" data-bs-target="#deleteProductModal"
                    data-id="<?= $pid ?>"
                    data-name="<?= esc((string) ($p['part_name'] ?? '')) ?>">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="col-12 text-center text-muted py-5" style="grid-column:1/-1;">
        <i class="fas fa-box-open fa-3x mb-3" style="color:#ccc;"></i>
        <p>No products found. Click <strong>Add New Product</strong> to get started.</p>
    </div>
<?php endif; ?>
</div>

<div class="products-table-wrap d-none" id="productsTableView">
    <div class="table-responsive">
        <table class="table products-table">
            <thead>
                <tr>
                    <th class="col-select no-sort">
                        <?php if (in_array($currentRole, ['admin', 'super_admin'], true)): ?>
                        <input type="checkbox" id="selectAllProducts" class="product-select-all" aria-label="Select all products">
                        <?php else: ?>
                        Select
                        <?php endif; ?>
                    </th>
                    <th>Image</th>
                    <th>Part Name</th>
                    <th>SKU</th>
                    <th>Part No.</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <?php if ($showBranchColumn): ?><th>Branch</th><?php endif; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="<?= $showBranchColumn ? '11' : '10' ?>" class="text-center text-muted py-4">No products found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                <?php if (!is_array($p)) { continue; } ?>
                <?php
                    $pid      = (int) $p['product_id'];
                    $imgUrl   = productImgUrl($pid, $defaultProductImage);
                    $status   = stockStatus((int) $p['current_stock']);
                    $catName  = esc((string) ($p['category_name'] ?? ''));
                    $sku      = trim((string) ($p['sku'] ?? ''));
                    $partNo   = trim((string) ($p['part_no'] ?? ''));
                    $description = trim((string) ($p['description'] ?? $p['product_description'] ?? ''));
                    $displayName = trim(trim((string) ($p['part_name'] ?? '')) . ' ' . $description);
                    $rowBranch = trim((string) ($p['branch'] ?? ''));
                    $canManageProduct = $currentRole === 'super_admin'
                        || ($currentRole === 'admin' && normalizeBranchView($rowBranch) === normalizeBranchView($userBranch));
                    $search   = strtolower(trim((string) ($p['part_name'] ?? '') . ' ' . $description . ' ' . $sku . ' ' . $partNo));
                    $priceStr = '₱' . number_format((float) $p['price'], 2);
                ?>
                <tr class="product-row"
                    data-cat="<?= $p['category_id'] ?>"
                    data-search="<?= esc($search, 'attr') ?>"
                    data-name="<?= esc(strtolower((string) ($p['part_name'] ?? '')), 'attr') ?>"
                    data-sku="<?= esc(strtolower($sku), 'attr') ?>"
                    data-partno="<?= esc(strtolower($partNo), 'attr') ?>"
                    data-stock="<?= (int) ($p['current_stock'] ?? 0) ?>"
                    data-category="<?= esc(strtolower((string) ($p['category_name'] ?? '')), 'attr') ?>"
                    data-status="<?= esc(strtolower($status), 'attr') ?>">
                    <td class="col-select">
                        <?php if ($canManageProduct): ?>
                        <input type="checkbox" class="product-select" data-id="<?= $pid ?>">
                        <?php endif; ?>
                    </td>
                    <td><img src="<?= $imgUrl ?>" alt="<?= esc((string) ($p['part_name'] ?? '')) ?>" class="table-product-thumb" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?= esc($defaultProductImage, 'attr') ?>';"></td>
                    <td><?= esc($displayName !== '' ? $displayName : (string) ($p['part_name'] ?? '')) ?></td>
                    <td><?= esc($sku !== '' ? $sku : '—') ?></td>
                    <td><?= esc($partNo !== '' ? $partNo : '—') ?></td>
                    <td><?= $catName !== '' ? $catName : '—' ?></td>
                    <td class="table-price"><?= esc($priceStr) ?></td>
                    <td><?= (int) ($p['current_stock'] ?? 0) ?></td>
                    <td>
                        <span class="badge rounded-pill <?= $status === 'Available' ? 'text-bg-success' : ($status === 'Low Stock' ? 'bg-warning text-dark' : 'bg-danger-subtle text-danger border border-danger-subtle') ?>" style="padding:4px 10px; font-size:11px; font-weight:600;"><?= esc($status) ?></span>
                    </td>
                    <?php if ($showBranchColumn): ?><td><?= esc($rowBranch !== '' ? $rowBranch : 'Unassigned') ?></td><?php endif; ?>
                    <td class="table-actions">
                        <button class="table-action-btn view" data-bs-toggle="modal" data-bs-target="#viewProductModal"
                            data-name="<?= esc((string) ($p['part_name'] ?? '')) ?>"
                            data-description="<?= esc($description) ?>"
                            data-sku="<?= esc($sku) ?>"
                            data-partno="<?= esc($partNo) ?>"
                            data-upc="<?= esc((string) ($p['resolved_upc'] ?? '')) ?>"
                            data-barcode-type="<?= esc((string) ($p['barcode_type'] ?? '')) ?>"
                            data-label-brand="<?= esc((string) ($p['label_brand'] ?? '')) ?>"
                            data-label-short-name="<?= esc((string) ($p['label_short_name'] ?? '')) ?>"
                            data-price="<?= esc($priceStr) ?>"
                            data-sold="<?= $p['current_stock'] ?> pcs"
                            data-category="<?= $catName ?>"
                            data-stock="<?= $p['current_stock'] ?> pcs"
                            data-supplier="<?= esc((string) ($p['supplier_name'] ?? '')) ?>"
                            data-status="<?= esc($status) ?>"
                            data-img="<?= $imgUrl ?>"><i class="fas fa-eye"></i></button>
                        <?php if ($canManageProduct): ?>
                        <button class="table-action-btn edit" data-bs-toggle="modal" data-bs-target="#editProductModal"
                            data-id="<?= $pid ?>"
                            data-name="<?= esc((string) ($p['part_name'] ?? '')) ?>"
                            data-description="<?= esc($description) ?>"
                            data-catid="<?= $p['category_id'] ?>"
                            data-supplierid="<?= $p['supplier_id'] ?? '' ?>"
                            data-sku="<?= esc($sku) ?>"
                            data-upc="<?= esc((string) ($p['resolved_upc'] ?? '')) ?>"
                            data-partno="<?= esc($partNo) ?>"
                            data-price="<?= esc((string) ($p['price'] ?? '')) ?>"
                            data-stock="<?= $p['current_stock'] ?> pcs"
                            data-status="<?= esc($status) ?>"
                            data-img="<?= $imgUrl ?>"><i class="fas fa-edit"></i></button>
                        <button class="table-action-btn delete" data-bs-toggle="modal" data-bs-target="#deleteProductModal"
                            data-id="<?= $pid ?>"
                            data-name="<?= esc((string) ($p['part_name'] ?? '')) ?>"><i class="fas fa-trash-alt"></i></button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const productSearchInput = document.getElementById('productSearchInput');
const productsSearchForm = document.getElementById('productsSearchForm');
const productsFilterModalEl = document.getElementById('productsFilterModal');
const productsStatusRadios = document.querySelectorAll('input[name="productsStatusFilter"]');
const productsFilterByField = document.getElementById('productsFilterByField');
const productsFilterByValue = document.getElementById('productsFilterByValue');
const productsGridView = document.getElementById('productsGridView');
const productsTableView = document.getElementById('productsTableView');
const viewToggleButtons = document.querySelectorAll('.btn-view-toggle');
const PRODUCTS_VIEW_MODE_KEY = 'productsViewMode';

const productsFilterState = {
    category: '',
    status: 'all',
    filterByField: 'all',
    filterByValue: '',
};

const productsSortState = {
    column: null,
    direction: 'asc',
};

function getProductFilterFieldValue(card, field) {
    if (field === 'all') {
        return [
            card.dataset.name || '',
            card.dataset.sku || '',
            card.dataset.partno || '',
            card.dataset.category || '',
            card.dataset.status || '',
            card.dataset.search || '',
        ].join(' ').toLowerCase();
    }

    return (card.dataset[field] || '').toLowerCase();
}

function applyProductFilters() {
    const query = (productSearchInput?.value || '').toLowerCase().trim();

    document.querySelectorAll('.product-card, .product-row').forEach(item => {
        const cardStatus = (item.dataset.status || '').toLowerCase();
        const matchCategory = !productsFilterState.category || item.dataset.cat === productsFilterState.category;
        const matchStatus = productsFilterState.status === 'all' || cardStatus === productsFilterState.status;
        const matchSearch = !query || (item.dataset.search || '').includes(query);
        const fieldValue = getProductFilterFieldValue(item, productsFilterState.filterByField);
        const matchFilterBy = !productsFilterState.filterByValue || fieldValue.includes(productsFilterState.filterByValue);
        item.style.display = (matchCategory && matchStatus && matchSearch && matchFilterBy) ? '' : 'none';
    });
    
    // Apply sorting if we're in table view and sorting is enabled
    if (productsSortState.column) {
        sortProductTable(productsSortState.column, productsSortState.direction);
    }
}

function sortProductTable(column, direction) {
    const tbody = document.querySelector('.products-table tbody');
    if (!tbody) return;

    const rows = Array.from(document.querySelectorAll('.product-row')).filter(row => row.style.display !== 'none');
    
    rows.sort((a, b) => {
        let aValue = '';
        let bValue = '';

        switch (column) {
            case 'name':
                aValue = (a.dataset.name || '').toLowerCase();
                bValue = (b.dataset.name || '').toLowerCase();
                break;
            case 'sku':
                aValue = (a.dataset.sku || '').toLowerCase();
                bValue = (b.dataset.sku || '').toLowerCase();
                break;
            case 'partno':
                aValue = (a.dataset.partno || '').toLowerCase();
                bValue = (b.dataset.partno || '').toLowerCase();
                break;
            case 'category':
                aValue = (a.dataset.category || '').toLowerCase();
                bValue = (b.dataset.category || '').toLowerCase();
                break;
            case 'price':
                aValue = parseFloat(a.querySelector('.table-price')?.textContent.replace(/₱|,/g, '') || 0);
                bValue = parseFloat(b.querySelector('.table-price')?.textContent.replace(/₱|,/g, '') || 0);
                break;
            case 'stock':
                aValue = parseInt(a.dataset.stock || 0, 10);
                bValue = parseInt(b.dataset.stock || 0, 10);
                break;
            case 'status':
                aValue = (a.dataset.status || '').toLowerCase();
                bValue = (b.dataset.status || '').toLowerCase();
                break;
            default:
                return 0;
        }

        if (typeof aValue === 'string') {
            const comparison = aValue.localeCompare(bValue);
            return direction === 'asc' ? comparison : -comparison;
        } else {
            return direction === 'asc' ? aValue - bValue : bValue - aValue;
        }
    });

    // Re-append sorted rows to the table body
    rows.forEach(row => tbody.appendChild(row));
}

function setProductsView(mode) {
    const isTable = mode === 'table';

    if (productsGridView) {
        productsGridView.classList.toggle('d-none', isTable);
    }
    if (productsTableView) {
        productsTableView.classList.toggle('d-none', !isTable);
    }

    viewToggleButtons.forEach((btn) => {
        btn.classList.toggle('active', btn.dataset.view === mode);
    });

    try {
        localStorage.setItem(PRODUCTS_VIEW_MODE_KEY, mode);
    } catch (e) {
        // Ignore storage errors.
    }
    
    // Initialize table sorting when switching to table view
    if (isTable) {
        setTimeout(() => initializeTableSorting(), 0);
    }
}

function initializeTableSorting() {
    const tableHeaders = document.querySelectorAll('.products-table thead th');
    const sortableColumns = ['Part Name', 'SKU', 'Part No.', 'Category', 'Price', 'Stock', 'Status'];
    
    tableHeaders.forEach((header) => {
        if (header.classList.contains('no-sort')) {
            return;
        }
        const headerText = header.textContent.replace(/[↑↓↕▲▼]/g, '').trim();
        
        if (sortableColumns.includes(headerText)) {
            header.classList.add('sortable');
            
            // Remove any existing listeners to prevent duplicates
            const newHeader = header.cloneNode(true);
            header.parentNode.replaceChild(newHeader, header);
        }
    });
    
    // Add listeners to all sortable headers
    document.querySelectorAll('.products-table thead th.sortable').forEach(header => {
        header.addEventListener('click', handleHeaderClick);
    });
}

function handleHeaderClick(e) {
    const header = e.target.closest('th.sortable');
    if (!header) return;
    
    const headerText = header.textContent.replace(/[↑↓↕▲▼]/g, '').trim();
    const columnMap = {
        'Part Name': 'name',
        'SKU': 'sku',
        'Part No.': 'partno',
        'Category': 'category',
        'Price': 'price',
        'Stock': 'stock',
        'Status': 'status'
    };
    
    const column = columnMap[headerText];
    if (!column) return;
    
    // Toggle direction if clicking the same column
    if (productsSortState.column === column) {
        productsSortState.direction = productsSortState.direction === 'asc' ? 'desc' : 'asc';
    } else {
        productsSortState.column = column;
        productsSortState.direction = 'asc';
    }
    
    // Update header arrow indicators
    document.querySelectorAll('.products-table thead th.sortable').forEach(h => {
        h.classList.remove('sorting_asc', 'sorting_desc', 'sortable');
        h.classList.add('sortable');
    });
    
    if (productsSortState.column) {
        const activeHeader = Array.from(document.querySelectorAll('.products-table thead th.sortable')).find(h => {
            const text = h.textContent.replace(/[↑↓↕▲▼]/g, '').trim();
            return columnMap[text] === productsSortState.column;
        });
        if (activeHeader) {
            activeHeader.classList.remove('sorting_asc', 'sorting_desc');
            activeHeader.classList.add(productsSortState.direction === 'asc' ? 'sorting_asc' : 'sorting_desc');
        }
    }
    
    // Apply sorting
    sortProductTable(productsSortState.column, productsSortState.direction);
}

viewToggleButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
        setProductsView(btn.dataset.view === 'table' ? 'table' : 'grid');
    });
});

try {
    setProductsView(localStorage.getItem(PRODUCTS_VIEW_MODE_KEY) === 'table' ? 'table' : 'grid');
} catch (e) {
    setProductsView('grid');
}

// Category filter
document.querySelectorAll('.cat-filter').forEach(link => {
    link.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelectorAll('.cat-filter').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
        productsFilterState.category = this.dataset.cat || '';
        if (productsCategoryFilter) {
            productsCategoryFilter.value = productsFilterState.category;
        }
        applyProductFilters();
    });
});

// Category dropdown search
const catSearchInput = document.getElementById('catSearchInput');
if (catSearchInput) {
    catSearchInput.addEventListener('click', e => e.stopPropagation());
    // Clear search & show all items when dropdown closes
    document.querySelector('.btn-group .dropdown-toggle').closest('.btn-group').addEventListener('hidden.bs.dropdown', function () {
        catSearchInput.value = '';
        document.querySelectorAll('.cat-filter-item').forEach(li => li.classList.remove('d-none'));
        const noResults = document.querySelector('.cat-no-results');
        if (noResults) noResults.classList.add('d-none');
    });
    catSearchInput.addEventListener('keyup', function () {
        const q = this.value.toLowerCase().trim();
        const items = document.querySelectorAll('.cat-filter-item');
        let visible = 0;
        items.forEach(li => {
            const name = li.querySelector('a').textContent.toLowerCase();
            const show = !q || name.includes(q);
            li.classList.toggle('d-none', !show);
            if (show) visible++;
        });
        const noResults = document.querySelector('.cat-no-results');
        if (noResults) noResults.classList.toggle('d-none', visible > 0);
    });
}
if (productsSearchForm) {
    productsSearchForm.addEventListener('submit', function (e) {
        e.preventDefault();
        applyProductFilters();
    });
}

if (productsFilterModalEl) {
    const applyProductsFiltersBtn = document.getElementById('applyProductsFiltersBtn');
    const resetProductsFiltersBtn = document.getElementById('resetProductsFiltersBtn');

    productsFilterModalEl.addEventListener('show.bs.modal', () => {
        productsStatusRadios.forEach((radio) => {
            radio.checked = radio.value === productsFilterState.status;
        });
        if (productsFilterByField) {
            productsFilterByField.value = productsFilterState.filterByField;
        }
        if (productsFilterByValue) {
            productsFilterByValue.value = productsFilterState.filterByValue;
        }
    });

    if (applyProductsFiltersBtn) {
        applyProductsFiltersBtn.addEventListener('click', () => {
            productsFilterState.status = document.querySelector('input[name="productsStatusFilter"]:checked')?.value || 'all';
            productsFilterState.filterByField = productsFilterByField ? productsFilterByField.value : 'all';
            productsFilterState.filterByValue = (productsFilterByValue ? productsFilterByValue.value : '').trim().toLowerCase();

            applyProductFilters();
            bootstrap.Modal.getOrCreateInstance(productsFilterModalEl).hide();
        });
    }

    if (resetProductsFiltersBtn) {
        resetProductsFiltersBtn.addEventListener('click', () => {
            productsFilterState.status = 'all';
            productsFilterState.filterByField = 'all';
            productsFilterState.filterByValue = '';
            productsStatusRadios.forEach((radio) => {
                radio.checked = radio.value === 'all';
            });
            if (productsFilterByField) {
                productsFilterByField.value = 'all';
            }
            if (productsFilterByValue) {
                productsFilterByValue.value = '';
            }

            applyProductFilters();
            bootstrap.Modal.getOrCreateInstance(productsFilterModalEl).hide();
        });
    }
}

// Text search
if (productSearchInput) {
    productSearchInput.addEventListener('input', applyProductFilters);
}

// Fallback: collapse sidebar before opening Add Product modal.
const addProductBtn = document.querySelector('.btn-add-product');
const addProductModalEl = document.getElementById('addProductModal');
function collapseSidebarForAddProduct() {
    const sidebar = document.querySelector('.left-side-bar');
    const contentWrapper = document.getElementById('contentWrapper');
    const menuIcon = document.querySelector('.menu-icon');
    sidebar?.classList.add('hidden');
    contentWrapper?.classList.add('full-width');
    if (menuIcon) {
        menuIcon.style.display = 'block';
    }
}
if (addProductBtn) {
    addProductBtn.addEventListener('click', collapseSidebarForAddProduct);
}
if (addProductModalEl) {
    addProductModalEl.addEventListener('show.bs.modal', collapseSidebarForAddProduct);
}
</script>

<?= $this->include('products/view_product') ?>

<?= $this->include('products/add_product') ?>

<?= $this->include('products/edit_prooduct') ?>

<?= $this->include('products/delete_product') ?>

<!-- ===================== DELETE SELECTED PRODUCTS MODAL ===================== -->
<div class="modal fade" id="deleteSelectedProductsModal" tabindex="-1" aria-labelledby="deleteSelectedProductsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #f44336 0%, #c62828 100%);">
                <h5 class="modal-title text-white fw-bold" id="deleteSelectedProductsModalLabel">
                    <i class="fas fa-trash-alt me-2"></i> Delete Selected Products
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('products/delete-selected') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="product_ids" id="deleteSelectedIds" value="[]">
                <div class="modal-body p-4">
                    <p class="mb-2">Delete <strong id="deleteSelectedCount">0</strong> selected product(s)?</p>
                    <p class="text-muted small mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #f44336 0%, #c62828 100%);">
                        <i class="fas fa-trash-alt me-1"></i> Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END DELETE SELECTED PRODUCTS MODAL =================== -->

<!-- ===================== IMPORT PRODUCT CSV MODAL ===================== -->
<div class="modal fade" id="importProductModal" tabindex="-1" aria-labelledby="importProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #28a745 0%, #218838 100%);">
                <h5 class="modal-title text-white fw-bold" id="importProductModalLabel">
                    <i class="fas fa-file-csv me-2"></i> Import Products from CSV
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('products/import') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="text-muted mb-3">Upload a <strong>.csv</strong> file with the following columns:</p>
                    <code class="d-block bg-light p-2 rounded mb-3" style="font-size:12px;">product_name, description, category_name, supplier_name, sku, part_no, price, beg_inv, stock_qty, status, image_filename</code>
                    <p class="text-muted small mb-3"><i class="bi bi-info-circle me-1"></i> <code>category_name</code> must match an existing category exactly. <code>description</code> is optional. <code>beg_inv</code> or <code>beginning_inventory</code> is the initial stock count; if not provided, defaults to 0. <code>supplier_name</code> is optional. <code>image_filename</code> is optional and used when a ZIP file of images is uploaded.</p>
                    <div class="mb-3">
                        <label for="products_csv_file" class="form-label fw-semibold">Select CSV File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="products_csv_file" name="csv_file" accept=".csv,.txt" required>
                    </div>
                    <div class="mb-3">
                        <label for="products_images_zip" class="form-label fw-semibold">Product Images ZIP <span class="text-muted small">(optional)</span></label>
                        <input type="file" class="form-control" id="products_images_zip" name="images_zip" accept=".zip">
                        <div class="form-text">If provided, images are matched by <code>image_filename</code> first, then by SKU/Part No/Part Name file name.</div>
                    </div>
                    <a href="<?= base_url('products/template') ?>" class="text-decoration-none small">
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
<!-- =================== END IMPORT PRODUCT CSV MODAL =================== -->

<!-- ===================== ADD CATEGORY (FROM PRODUCTS) ===================== -->
<div class="modal fade" id="productAddCategoryModal" tabindex="-1" aria-labelledby="productAddCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #FF8C42 0%, #FF7A2F 100%);">
                <h5 class="modal-title text-white fw-bold" id="productAddCategoryModalLabel">
                    <i class="fas fa-plus-circle me-2"></i> Add Category
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('categories/store') ?>" method="post" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="products">
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="mb-3">
                        <label for="product_category_name" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" id="product_category_name" name="category_name" class="form-control" placeholder="e.g. Engine Parts, Brake System" required>
                        <div class="invalid-feedback">Category name is required.</div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #FF8C42 0%, #FF7A2F 100%);">
                        <i class="fas fa-plus-circle me-1"></i> Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END ADD CATEGORY (FROM PRODUCTS) =================== -->

<!-- ===================== EDIT CATEGORY (FROM PRODUCTS) ===================== -->
<div class="modal fade" id="productEditCategoryModal" tabindex="-1" aria-labelledby="productEditCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #FF9800 0%, #f57c00 100%);">
                <h5 class="modal-title text-white fw-bold" id="productEditCategoryModalLabel">
                    <i class="fas fa-edit me-2"></i> Edit Category
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('categories/update') ?>" method="post" id="productEditCategoryForm" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="products">
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="mb-3">
                        <label for="product_edit_category_id" class="form-label fw-semibold">Select Category <span class="text-danger">*</span></label>
                        <select id="product_edit_category_id" name="category_id" class="form-select" required>
                            <option value="">-- Select category to edit --</option>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $c): ?>
                                    <?php if (!is_array($c)) { continue; } ?>
                                    <option value="<?= (int) ($c['category_id'] ?? 0) ?>" data-name="<?= esc((string) ($c['category_name'] ?? ''), 'attr') ?>"><?= esc((string) ($c['category_name'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback">Please select a category.</div>
                    </div>
                    <div class="mb-2">
                        <label for="product_edit_category_name" class="form-label fw-semibold">New Category Name <span class="text-danger">*</span></label>
                        <input type="text" id="product_edit_category_name" name="category_name" class="form-control" placeholder="Enter updated category name" required>
                        <div class="invalid-feedback">Category name is required.</div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #FF9800 0%, #f57c00 100%);">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END EDIT CATEGORY (FROM PRODUCTS) =================== -->

<!-- ===================== DELETE CATEGORY (FROM PRODUCTS) ===================== -->
<div class="modal fade" id="productDeleteCategoryModal" tabindex="-1" aria-labelledby="productDeleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #f44336 0%, #c62828 100%);">
                <h5 class="modal-title text-white fw-bold" id="productDeleteCategoryModalLabel">
                    <i class="fas fa-trash-alt me-2"></i> Delete Category
                </h5>
                <button type="button" class="bi bi-x-lg text-white fs-4 border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('categories/delete') ?>" method="post" id="productDeleteCategoryForm" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="products">
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="alert alert-danger py-2 px-3" style="font-size:14px; border-radius:8px;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Deleting a category cannot be undone.
                    </div>
                    <div class="mb-2">
                        <label for="product_delete_category_id" class="form-label fw-semibold">Select Category <span class="text-danger">*</span></label>
                        <select id="product_delete_category_id" name="category_id" class="form-select" required>
                            <option value="">-- Select category to delete --</option>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $c): ?>
                                    <?php if (!is_array($c)) { continue; } ?>
                                    <option value="<?= (int) ($c['category_id'] ?? 0) ?>"><?= esc((string) ($c['category_name'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback">Please select a category.</div>
                    </div>
                    <small class="text-muted">Tip: Categories used by products cannot be deleted.</small>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white fw-semibold" style="background: linear-gradient(135deg, #f44336 0%, #c62828 100%);">
                        <i class="fas fa-trash-alt me-1"></i> Delete Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- =================== END DELETE CATEGORY (FROM PRODUCTS) =================== -->

<script>
// Bootstrap validation for forms added in Products page.
document.querySelectorAll('.needs-validation').forEach(form => {
    form.addEventListener('submit', function (e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        this.classList.add('was-validated');
    });
});

const productAddCategoryModal = document.getElementById('productAddCategoryModal');
if (productAddCategoryModal) {
    productAddCategoryModal.addEventListener('hidden.bs.modal', function () {
        const form = this.querySelector('form');
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
    });
}

const productDeleteCategoryModal = document.getElementById('productDeleteCategoryModal');
if (productDeleteCategoryModal) {
    productDeleteCategoryModal.addEventListener('hidden.bs.modal', function () {
        const form = this.querySelector('form');
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
    });
}

const productEditCategorySelect = document.getElementById('product_edit_category_id');
const productEditCategoryName = document.getElementById('product_edit_category_name');
if (productEditCategorySelect && productEditCategoryName) {
    productEditCategorySelect.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        productEditCategoryName.value = selectedOption ? (selectedOption.dataset.name || '') : '';
    });
}

const productEditCategoryModal = document.getElementById('productEditCategoryModal');
if (productEditCategoryModal) {
    productEditCategoryModal.addEventListener('hidden.bs.modal', function () {
        const form = this.querySelector('form');
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
    });
}

const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
const deleteSelectedCount = document.getElementById('deleteSelectedCount');
const deleteSelectedIdsInput = document.getElementById('deleteSelectedIds');
const selectAllProducts = document.getElementById('selectAllProducts');

const getSelectedProductIds = () => {
    const ids = Array.from(document.querySelectorAll('.product-select:checked'))
        .map(el => parseInt(el.dataset.id || '0', 10))
        .filter(id => Number.isFinite(id) && id > 0);
    return Array.from(new Set(ids));
};

const getSelectableProductIds = () => {
    const ids = Array.from(document.querySelectorAll('.product-select'))
        .map(el => parseInt(el.dataset.id || '0', 10))
        .filter(id => Number.isFinite(id) && id > 0);
    return Array.from(new Set(ids));
};

const syncSelection = (source) => {
    const id = source.dataset.id;
    if (!id) return;
    document.querySelectorAll(`.product-select[data-id="${CSS.escape(id)}"]`).forEach(el => {
        if (el !== source) {
            el.checked = source.checked;
        }
    });
};

const updateDeleteSelectedState = () => {
    if (!deleteSelectedBtn) return;
    const ids = getSelectedProductIds();
    deleteSelectedBtn.disabled = ids.length === 0;
    if (deleteSelectedCount) {
        deleteSelectedCount.textContent = String(ids.length);
    }
    if (deleteSelectedIdsInput) {
        deleteSelectedIdsInput.value = JSON.stringify(ids);
    }
    if (selectAllProducts) {
        const allIds = getSelectableProductIds();
        const isAllSelected = allIds.length > 0 && ids.length === allIds.length;
        selectAllProducts.checked = isAllSelected;
        selectAllProducts.indeterminate = ids.length > 0 && !isAllSelected;
    }
};

if (selectAllProducts) {
    selectAllProducts.addEventListener('change', function () {
        const shouldCheck = this.checked;
        document.querySelectorAll('.product-select').forEach(el => {
            el.checked = shouldCheck;
        });
        updateDeleteSelectedState();
    });
}

document.querySelectorAll('.product-select').forEach(el => {
    el.addEventListener('change', function () {
        syncSelection(this);
        updateDeleteSelectedState();
    });
});

const deleteSelectedModal = document.getElementById('deleteSelectedProductsModal');
if (deleteSelectedModal) {
    deleteSelectedModal.addEventListener('show.bs.modal', updateDeleteSelectedState);
}
</script>

<?php $this->endSection(); ?>