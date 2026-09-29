<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<?php
$salesInput = isset($sales) ? $sales : [];
$sales = is_array($salesInput) ? array_values(array_filter($salesInput, static fn ($row) => is_array($row))) : [];
$expensesInput = isset($expenses) ? $expenses : [];
$expenses = is_array($expensesInput) ? array_values(array_filter($expensesInput, static fn ($row) => is_array($row))) : [];
$customerBalancesInput = isset($customer_balances) ? $customer_balances : [];
$customer_balances = is_array($customerBalancesInput) ? array_values(array_filter($customerBalancesInput, static fn ($row) => is_array($row))) : [];

/** @var array<int, array<string, mixed>> $sales */
/** @var array<int, array<string, mixed>> $expenses */
/** @var array<int, array<string, mixed>> $customer_balances */

$successFlash = (string) (session()->getFlashdata('success') ?? '');
$errorFlash = (string) (session()->getFlashdata('error') ?? '');

$totalCashSales   = array_sum(array_column($sales, 'cash_sales'));
$cashRemitTotal   = array_sum(array_map(static function (array $e): float {
    $type = strtolower(trim((string) ($e['expense_type'] ?? '')));
    return $type === 'owner_draw' ? (float) ($e['amount'] ?? 0) : 0.0;
}, $expenses));
$operatingExpenses = array_sum(array_map(static function (array $e): float {
    $type = strtolower(trim((string) ($e['expense_type'] ?? '')));
    return $type === 'owner_draw' ? 0.0 : (float) ($e['amount'] ?? 0);
}, $expenses));
$totalExpenses    = $operatingExpenses;
$netIncome        = $totalCashSales - $operatingExpenses;
$endingBalance    = $netIncome - $cashRemitTotal;

$cashTotal    = array_sum(array_map(fn($s) => $s['payment_method'] === 'Cash'     ? (float)($s['cash_sales'] ?? 0) : 0, $sales));
$ewalletTotal = array_sum(array_map(fn($s) => $s['payment_method'] === 'E-Wallet' ? (float)($s['cash_sales'] ?? 0) : 0, $sales));
$chequeTotal  = array_sum(array_map(fn($s) => $s['payment_method'] === 'Cheque'   ? (float)($s['cash_sales'] ?? 0) : 0, $sales));
$pmTotal      = $cashTotal + $ewalletTotal + $chequeTotal;
$cashPct      = $pmTotal > 0 ? round($cashTotal    / $pmTotal * 100, 1) : 0;
$ewalletPct   = $pmTotal > 0 ? round($ewalletTotal / $pmTotal * 100, 1) : 0;
$chequePct    = $pmTotal > 0 ? round($chequeTotal  / $pmTotal * 100, 1) : 0;

$canCrossBranchView = (bool) ($canCrossBranchView ?? false);
$sales_search = (string) ($sales_search ?? '');
$expenses_search = (string) ($expenses_search ?? '');
$availableBranchesInput = $availableBranches ?? null;
$availableBranches = is_array($availableBranchesInput) ? $availableBranchesInput : [];
$activeBranchFilter = trim((string) ($activeBranchFilter ?? ''));
$activeBranchMode = trim((string) ($activeBranchMode ?? 'all'));
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

$queryParams = [];
if (($sales_search ?? '') !== '') {
    $queryParams['sales_search'] = $sales_search;
}
if (($expenses_search ?? '') !== '') {
    $queryParams['expenses_search'] = $expenses_search;
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

<div class="container-fluid px-2 accounting-container">
    <!-- Flash Messages -->
    <?php if ($successFlash !== ''): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc($successFlash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($errorFlash !== ''): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc($errorFlash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="accounting-topbar d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <div class="accounting-header-text">
            <h2 class="accounting-title mb-1">ACCOUNTING</h2>
            <p class="accounting-period mb-0">Period Covered: <span><?= date('m/d/y') ?></span></p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <?php if ($canCrossBranchView): ?>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-filter dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Filter branches" title="Branches">
                    <span class="btn-label"> Branch: <?= esc($branchButtonLabel) ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end branch-filter-menu">
                    <li><a class="dropdown-item <?= $activeBranchMode === 'mine' ? 'active' : '' ?>" href="<?= base_url('sales?branch=mine') ?>"><i class="bi bi-person-badge me-2"></i>My Products</a></li>
                    <li><a class="dropdown-item <?= $activeBranchMode === 'all' ? 'active' : '' ?>" href="<?= base_url('sales?branch=all') ?>"><i class="bi bi-geo-alt me-2"></i>All Branches</a></li>
                    <?php foreach ($filteredBranchOptions as $branchOption): ?>
                    <li>
                        <a class="dropdown-item <?= strcasecmp($activeBranchFilter, (string) $branchOption) === 0 ? 'active' : '' ?>"
                            href="<?= base_url('sales') . '?branch=' . rawurlencode((string) $branchOption) ?>">
                            <i class="bi bi-shop me-2"></i><?= esc($branchOption) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            <div class="dropdown export-choice-dropdown">
                <button class="btn btn-export-pdf dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Export" title="Export">
                    <i class="bi bi-download"></i><span class="btn-label">Export</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="<?= base_url('accounting/export/pdf') . $exportQuery ?>" target="_blank" rel="noopener">
                            <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Export as PDF
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= base_url('accounting/export/excel') . $exportQuery ?>">
                            <i class="bi bi-file-earmark-excel me-2 text-success"></i>Export as Excel
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Daily Sales Card -->
    <div class="card accounting-card mb-2">
        <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
            <h5 class="mb-0">Daily Sales</h5>
            <div class="accounting-actions d-flex align-items-center gap-2 w-100">
                <form method="GET" action="<?= base_url('sales') ?>" class="d-flex gap-1 accounting-search-form flex-grow-1">
                    <?php if ($canCrossBranchView): ?>
                        <input type="hidden" name="branch" value="<?= esc($activeBranchMode === 'branch' ? $activeBranchFilter : $activeBranchMode) ?>">
                    <?php endif; ?>
                    <div class="input-group manage-search-group">
                        <input type="text" name="sales_search" class="form-control manage-search-input" placeholder="Search..." value="<?= esc($sales_search ?? '') ?>">
                        <button class="btn manage-search-btn" type="button" data-bs-toggle="modal" data-bs-target="#salesFilterModal" aria-label="Filter" title="Filter"><i class="bi bi-funnel"></i></button>
                        <button class="btn manage-search-btn" type="submit" title="Search"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                <button class="btn btn-new-action" data-bs-toggle="modal" data-bs-target="#newSaleModal" aria-label="New Sale" title="New Sale">
                    <i class="bi bi-plus-circle"></i><span class="btn-label">New Sale</span>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Desktop Table View -->
            <div class="desktop-table">
                <table class="table table-sm accounting-table mb-0 js-sortable-table" id="salesDesktopTable">
                    <thead>
                        <tr>
                            <th>CSI</th>
                            <th>Date</th>
                            <th>Cash Sales</th>
                            <th>Payment Method</th>
                            <th>Account Receivable</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sales)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">No sales records found.</td></tr>
                        <?php else: foreach ($sales as $s): ?>
                        <tr>
                            <td><?= esc((string) ($s['csi'] ?? '')) ?></td>
                            <td><?= esc((string) ($s['sale_date'] ?? '')) ?></td>
                            <td><?= $s['cash_sales'] !== null ? '₱' . number_format((float)$s['cash_sales'], 2) : '—' ?></td>
                            <td><?= esc((string) ($s['payment_method'] ?? '—')) ?></td>
                            <td>₱<?= number_format((float)($s['account_receivable'] ?? 0), 2) ?></td>
                            <td>
                                <?php
                                    $rmk = $s['remarks'] ?? '';
                                    if ($rmk === '') { echo '—'; }
                                    elseif (mb_strlen($rmk) > 60) { echo esc(mb_substr($rmk, 0, 60)) . '…'; }
                                    else { echo esc((string) $rmk); }
                                ?>
                            </td>
                            <td class="text-nowrap">
                                <button class="btn btn-action btn-primary" data-bs-toggle="modal" data-bs-target="#viewSaleModal"
                                    data-id="<?= $s['sale_id'] ?>"
                                    data-csi="<?= esc((string) ($s['csi'] ?? '')) ?>"
                                    data-date="<?= esc((string) ($s['sale_date'] ?? '')) ?>"
                                    data-cash="<?= $s['cash_sales'] ?>"
                                    data-method="<?= esc((string) ($s['payment_method'] ?? '')) ?>"
                                    data-ar="<?= $s['account_receivable'] ?>"
                                    data-remarks="<?= esc((string) ($s['remarks'] ?? '')) ?>"><i class="bi bi-eye"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="mobile-card-view">
                <?php if (!empty($sales)): ?>
                    <div class="sales-table-wrapper">
                        <table class="sales-mobile-table">
                            <thead>
                                <tr>
                                    <th>CSI</th>
                                    <th>Date</th>
                                    <th>Cash Sales</th>
                                    <th>Method</th>
                                    <th>A/R</th>
                                    <th>Remarks</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="sales-table-body">
                                <?php foreach ($sales as $s): ?>
                                <tr class="sales-table-row" data-id="<?= $s['sale_id'] ?>">
                                    <td class="sales-cell-csi"><span class="highlight-blue"><?= esc((string) ($s['csi'] ?? '')) ?></span></td>
                                    <td class="sales-cell-date"><?= esc((string) ($s['sale_date'] ?? '')) ?></td>
                                    <td class="sales-cell-amount"><span class="highlight-orange">₱<?= number_format((float)$s['cash_sales'] ?? 0, 2) ?></span></td>
                                    <td class="sales-cell-method"><?= esc((string) ($s['payment_method'] ?? '—')) ?></td>
                                    <td class="sales-cell-ar">₱<?= number_format((float)$s['account_receivable'] ?? 0, 2) ?></td>
                                    <td class="sales-cell-remarks"><?= esc(mb_substr((string) ($s['remarks'] ?? '—'), 0, 20)) ?></td>
                                    <td class="sales-cell-actions">
                                        <div class="action-buttons">
                                            <button class="btn-action-icon" data-bs-toggle="modal" data-bs-target="#viewSaleModal"
                                                data-id="<?= $s['sale_id'] ?>"
                                                data-csi="<?= esc((string) ($s['csi'] ?? '')) ?>"
                                                data-date="<?= esc((string) ($s['sale_date'] ?? '')) ?>"
                                                data-cash="<?= $s['cash_sales'] ?>"
                                                data-method="<?= esc((string) ($s['payment_method'] ?? '')) ?>"
                                                data-ar="<?= $s['account_receivable'] ?>"
                                                data-remarks="<?= esc((string) ($s['remarks'] ?? '')) ?>" title="View"><i class="bi bi-eye"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Navigation -->
                    <div class="sales-pagination-nav">
                        <button class="pagination-arrow pagination-prev-arrow" type="button" disabled><i class="bi bi-chevron-left"></i></button>
                        <span class="pagination-info"><span class="current-page">1</span> / <span class="total-pages">1</span></span>
                        <button class="pagination-arrow pagination-next-arrow" type="button"><i class="bi bi-chevron-right"></i></button>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">No sales records found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Expenses Card -->
    <div class="card accounting-card mb-2">
        <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
            <h5 class="mb-0">Expenses</h5>
            <div class="accounting-actions d-flex align-items-center gap-2 w-100">
                <form method="GET" action="<?= base_url('sales') ?>" class="d-flex gap-1 accounting-search-form flex-grow-1">
                    <?php if ($canCrossBranchView): ?>
                        <input type="hidden" name="branch" value="<?= esc($activeBranchMode === 'branch' ? $activeBranchFilter : $activeBranchMode) ?>">
                    <?php endif; ?>
                    <div class="input-group manage-search-group">
                        <input type="text" name="expenses_search" class="form-control manage-search-input" placeholder="Search..." value="<?= esc($expenses_search ?? '') ?>">
                        <button class="btn manage-search-btn" type="button" data-bs-toggle="modal" data-bs-target="#expensesFilterModal" aria-label="Filter" title="Filter"><i class="bi bi-funnel"></i></button>
                        <button class="btn manage-search-btn" type="submit" title="Search"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                <button class="btn btn-new-action" data-bs-toggle="modal" data-bs-target="#newExpenseModal" aria-label="New Expense" title="New Expense">
                    <i class="bi bi-plus-circle"></i><span class="btn-label">New Expense</span>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Desktop Table View -->
            <div class="desktop-table">
                <table class="table table-sm accounting-table mb-0 js-sortable-table" id="expensesDesktopTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($expenses)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-3">No expense records found.</td></tr>
                        <?php else: foreach ($expenses as $e): ?>
                        <tr>
                            <td><?= esc((string) ($e['expense_date'] ?? '')) ?></td>
                            <td><?= esc((string) ((($e['expense_type'] ?? '') === 'owner_draw') ? 'Owner Draw' : 'Operating Expense')) ?></td>
                            <td><?= esc((string) ($e['description'] ?? '')) ?></td>
                            <td>₱<?= number_format((float)$e['amount'], 2) ?></td>
                            <td><?= esc((string) ($e['remarks'] ?? '—')) ?></td>
                            <td class="text-nowrap">
                                <button class="btn btn-action btn-primary" data-bs-toggle="modal" data-bs-target="#viewExpenseModal"
                                    data-id="<?= $e['expense_id'] ?>"
                                    data-date="<?= esc((string) ($e['expense_date'] ?? '')) ?>"
                                    data-amount="<?= $e['amount'] ?>"
                                    data-type="<?= esc((string) ($e['expense_type'] ?? 'expense')) ?>"
                                    data-description="<?= esc((string) ($e['description'] ?? '')) ?>"
                                    data-remarks="<?= esc((string) ($e['remarks'] ?? '')) ?>"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-action btn-orange" data-bs-toggle="modal" data-bs-target="#editExpenseModal"
                                    data-id="<?= $e['expense_id'] ?>"
                                    data-date="<?= esc((string) ($e['expense_date'] ?? '')) ?>"
                                    data-amount="<?= $e['amount'] ?>"
                                    data-type="<?= esc((string) ($e['expense_type'] ?? 'expense')) ?>"
                                    data-description="<?= esc((string) ($e['description'] ?? '')) ?>"
                                    data-remarks="<?= esc((string) ($e['remarks'] ?? '')) ?>"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-action btn-danger" data-bs-toggle="modal" data-bs-target="#deleteExpenseModal"
                                    data-id="<?= $e['expense_id'] ?>"
                                    data-description="<?= esc((string) ($e['description'] ?? '')) ?>"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                    <?php if (!empty($expenses)): ?>
                    <tfoot>
                        <tr class="table-light">
                            <td></td>
                            <td></td>
                            <td class="fw-semibold">Total</td>
                            <td class="fw-semibold">₱<?= number_format($totalExpenses, 2) ?></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="mobile-card-view">
                <?php if (!empty($expenses)): ?>
                    <?php foreach ($expenses as $e): ?>
                    <div class="mobile-data-card">
                        <div class="mobile-card-row">
                            <span class="mobile-card-label">Date:</span>
                            <span class="mobile-card-value"><?= esc((string) ($e['expense_date'] ?? '')) ?></span>
                        </div>
                        <div class="mobile-card-row">
                            <span class="mobile-card-label">Description:</span>
                            <span class="mobile-card-value"><?= esc((string) ($e['description'] ?? '')) ?></span>
                        </div>
                        <div class="mobile-card-row">
                            <span class="mobile-card-label">Type:</span>
                            <span class="mobile-card-value"><?= esc((string) ((($e['expense_type'] ?? '') === 'owner_draw') ? 'Owner Draw' : 'Operating Expense')) ?></span>
                        </div>
                        <div class="mobile-card-row">
                            <span class="mobile-card-label">Amount:</span>
                            <span class="mobile-card-value-highlight">₱<?= number_format((float)$e['amount'], 2) ?></span>
                        </div>
                        <div class="mobile-card-actions">
                            <button class="btn btn-action btn-primary" data-bs-toggle="modal" data-bs-target="#viewExpenseModal"
                                data-id="<?= $e['expense_id'] ?>"
                                data-date="<?= esc((string) ($e['expense_date'] ?? '')) ?>"
                                data-amount="<?= $e['amount'] ?>"
                                data-type="<?= esc((string) ($e['expense_type'] ?? 'expense')) ?>"
                                data-description="<?= esc((string) ($e['description'] ?? '')) ?>"
                                data-remarks="<?= esc((string) ($e['remarks'] ?? '')) ?>"><i class="bi bi-eye"></i></button>
                            <button class="btn btn-action btn-orange" data-bs-toggle="modal" data-bs-target="#editExpenseModal"
                                data-id="<?= $e['expense_id'] ?>"
                                data-date="<?= esc((string) ($e['expense_date'] ?? '')) ?>"
                                data-amount="<?= $e['amount'] ?>"
                                data-type="<?= esc((string) ($e['expense_type'] ?? 'expense')) ?>"
                                data-description="<?= esc((string) ($e['description'] ?? '')) ?>"
                                data-remarks="<?= esc((string) ($e['remarks'] ?? '')) ?>"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-action btn-danger" data-bs-toggle="modal" data-bs-target="#deleteExpenseModal"
                                data-id="<?= $e['expense_id'] ?>"
                                data-description="<?= esc((string) ($e['description'] ?? '')) ?>"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="mobile-total-row">
                        <span class="mobile-card-label">Total:</span>
                        <span class="mobile-total-value">₱<?= number_format($totalExpenses, 2) ?></span>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">No expense records found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Customer Balance Tracker -->
    <div class="card accounting-card mb-2">
        <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
            <h5 class="mb-0">Customer Balance Tracker</h5>
            <small class="text-muted">Outstanding receivables by customer</small>
        </div>
        <div class="card-body p-0">
            <div class="desktop-table">
                <table class="table table-sm accounting-table mb-0">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Address</th>
                            <th class="text-end">Outstanding Balance</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customer_balances)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">No outstanding customer balances.</td></tr>
                        <?php else: foreach ($customer_balances as $cb): ?>
                        <tr>
                            <td><?= esc((string) ($cb['customer_name'] ?? '—')) ?></td>
                            <td><?= esc((string) ($cb['customer_address'] ?? '—')) ?></td>
                            <td class="text-end fw-semibold">₱<?= number_format((float) ($cb['balance'] ?? 0), 2) ?></td>
                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-success js-open-collect-payment"
                                    data-bs-toggle="modal"
                                    data-bs-target="#collectPaymentModal"
                                    data-customer="<?= esc((string) ($cb['customer_name'] ?? '')) ?>"
                                    data-address="<?= esc((string) ($cb['customer_address'] ?? '')) ?>"
                                    data-balance="<?= (float) ($cb['balance'] ?? 0) ?>"
                                    title="Collect payment"
                                >
                                    <i class="bi bi-cash-coin"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Summary + Payment Methods -->
    <div class="row g-3 mb-4">
        <!-- Summary Report -->
        <div class="col-lg-6">
            <div class="card accounting-card h-100">
                <div class="card-body p-0">
                    <h5 class="summary-title px-3 pt-3 mb-3">Summary Report</h5>
                    <div>
                        <table class="table table-borderless summary-table mb-0">
                            <tbody>
                                <tr>
                                    <td class="summary-label">Cash Sales</td>
                                    <td class="summary-value text-end">₱<?= number_format($totalCashSales, 2) ?></td>
                                </tr>
                                <tr>
                                    <td class="summary-label">Operating Expenses</td>
                                    <td class="summary-value text-end">₱<?= number_format($totalExpenses, 2) ?></td>
                                </tr>
                                <tr>
                                    <td class="summary-label">Net Income (Before Draw)</td>
                                    <td class="summary-value text-end">₱<?= number_format($netIncome, 2) ?></td>
                                </tr>
                                <tr>
                                    <td class="summary-label">Cash Remit</td>
                                    <td class="summary-value text-end">₱<?= number_format($cashRemitTotal, 2) ?></td>
                                </tr>
                                <tr class="summary-total-row">
                                    <td class="summary-label">Ending Balance</td>
                                    <td class="summary-value text-end">₱<?= number_format($endingBalance, 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Method Breakdown -->
        <div class="col-lg-6">
            <div class="card accounting-card h-100">
                <div class="card-body">
                    <h5 class="mb-3">Payment Method Breakdown</h5>
                    <div class="row gy-3">
                        <div class="col-6 col-sm-4">
                            <div class="payment-box cash">
                                <div class="payment-icon">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                                <div class="payment-label">Cash</div>
                                <div class="payment-amount">₱<?= number_format($cashTotal, 2) ?></div>
                                <div class="payment-percent"><?= $cashPct ?>%</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="payment-box ewallet">
                                <div class="payment-icon">
                                    <i class="bi bi-phone"></i>
                                </div>
                                <div class="payment-label">E-Wallet</div>
                                <div class="payment-amount">₱<?= number_format($ewalletTotal, 2) ?></div>
                                <div class="payment-percent"><?= $ewalletPct ?>%</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="payment-box cheque">
                                <div class="payment-icon">
                                    <i class="bi bi-bank"></i>
                                </div>
                                <div class="payment-label">Cheque</div>
                                <div class="payment-amount">₱<?= number_format($chequeTotal, 2) ?></div>
                                <div class="payment-percent"><?= $chequePct ?>%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .accounting-container {
        padding-top: 1.5rem;
        max-width: 100%;
        width: 100%;
        box-sizing: border-box;
        overflow-x: hidden;
        margin: 0;
        padding-left: 0.25rem;
        padding-right: 0.25rem;
    }

    .page-content {
        padding-left: 12px;
        padding-right: 12px;
    }

    .accounting-title {
        font-size: 32px;
        font-weight: 700;
        letter-spacing: 0.03em;
        color: #1a1a1a;
    }

    .accounting-period {
        font-size: 16px;
        color: #999;
        font-weight: 500;
    }

    .accounting-period span {
        font-weight: 600;
        color: #555;
    }

    .btn-export-pdf {
        background: linear-gradient(135deg, #f97373 0%, #e63946 100%);
        color: #fff;
        border-radius: 12px;
        padding: 0.65rem 1.5rem;
        font-size: 0.95rem;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        transition: all 0.2s ease;
        min-height: 44px;
        min-width: 44px;
    }

    .btn-export-pdf:hover {
        background: linear-gradient(135deg, #e63946 0%, #d41344 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(249, 115, 115, 0.3);
    }

    .accounting-card {
        border-radius: 16px;
        border: 1px solid #f1f1f1;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        background: #fff;
        overflow: hidden;
        transition: box-shadow 0.2s ease;
        margin-bottom: 1rem;
        overflow-x: hidden;
        overflow-y: auto;
        padding: 0;
        box-sizing: border-box;
    }

    .accounting-card .card-header {
        background-color: #fff;
        border-bottom: 1px solid #f3f4f6;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        width: 100%;
        box-sizing: border-box;
    }

    .btn-new-action {
        background: linear-gradient(135deg, #FF8C42 0%, #FF7A2F 100%);
        color: #fff;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        transition: all 0.2s ease;
        min-height: 44px;
        min-width: 44px;
        padding: 0.65rem 1.25rem;
    }

    .btn-new-action:hover {
        background: #ff7a3d !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 140, 66, 0.3);
    }

    .btn-new-action i {
        font-size: 1rem;
    }

    .btn-filter {
        background: #667eea !important;
        color: #fff;
        border-radius: 12px;
        padding: 0.65rem 1.25rem;
        font-size: 0.95rem;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        transition: all 0.2s ease;
        min-height: 44px;
        min-width: 44px;
    }

    .btn-filter:hover {
        background: #5568d3 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .btn-filter i {
        font-size: 1rem;
    }

    .branch-filter-menu {
        max-height: 260px;
        overflow-y: auto;
    }

    /* Keep accounting edit actions visibly orange across breakpoints. */
    .accounting-table .btn.btn-action.btn-orange,
    .mobile-card-actions .btn.btn-action.btn-orange {
        background-color: #fd7e14 !important;
        border-color: #fd7e14 !important;
        color: #fff !important;
    }

    .accounting-table .btn.btn-action.btn-orange:hover,
    .mobile-card-actions .btn.btn-action.btn-orange:hover {
        background-color: #e06b0a !important;
        border-color: #e06b0a !important;
        color: #fff !important;
    }

    .btn-action-icon[data-bs-target="#editSaleModal"],
    .btn-action-icon[data-bs-target="#editExpenseModal"] {
        color: #fd7e14;
    }

    /* Filter Modal Styles */
    .filter-section {
        margin-bottom: 1.5rem;
    }

    .filter-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0066ff;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-checkboxes,
    .filter-radios {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filter-checkbox,
    .filter-radio {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        cursor: pointer;
        user-select: none;
        font-size: 0.95rem;
        color: #333;
        padding: 0.3rem 0;
    }

    .filter-checkbox input[type="checkbox"],
    .filter-radio input[type="radio"] {
        cursor: pointer;
        width: 20px;
        height: 20px;
        accent-color: #0066ff;
        min-width: 20px;
        min-height: 20px;
    }

    .filter-checkbox span,
    .filter-radio span {
        cursor: pointer;
    }

    .filter-checkbox:hover span,
    .filter-radio:hover span {
        color: #0066ff;
    }

    .filter-date-inputs {
        display: flex;
        gap: 1rem;
    }

    .date-input-group {
        flex: 1;
    }

    .date-input-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: #666;
        margin-bottom: 0.4rem;
    }

    .filter-date-from,
    .filter-date-to {
        width: 100%;
        padding: 0.7rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.95rem;
        min-height: 40px;
    }

    .filter-date-from:focus,
    .filter-date-to:focus {
        outline: none;
        border-color: #0066ff;
        box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1);
    }

    .modal-footer .btn-danger {
        background-color: #e53935;
        border-color: #e53935;
    }

    .modal-footer .btn-danger:hover {
        background-color: #c62828;
        border-color: #c62828;
    }

    /* Desktop Table */
    .desktop-table {
        display: block;
    }

    .mobile-card-view {
        display: none;
    }

    /* Mobile Card Styles */
    .mobile-card-view {
        padding: 0.5rem;
        overflow-x: hidden;
        width: 100%;
        box-sizing: border-box;
        max-width: 100%;
    }

    .mobile-data-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        box-sizing: border-box;
        width: 100%;
        overflow-x: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .mobile-card-row {
        display: grid;
        grid-template-columns: 80px 1fr;
        gap: 0.75rem 1rem;
        margin-bottom: 0.75rem;
        align-items: flex-start;
        overflow: hidden;
        word-break: break-word;
    }

    .mobile-card-row:last-of-type {
        margin-bottom: 0.75rem;
    }

    .mobile-card-label {
        font-weight: 700;
        color: #666;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        display: block;
    }

    .mobile-card-value {
        color: #1a1a1a;
        font-weight: 500;
        word-break: break-word;
        font-size: 0.95rem;
        display: block;
    }

    .mobile-card-value.csi-value {
        color: #0066ff;
        font-weight: 700;
        font-size: 1rem;
    }

    .mobile-card-value.amount-value {
        color: #ff8c42;
        font-weight: 700;
        font-size: 1rem;
    }

    .mobile-card-value-highlight {
        color: #ff8c42;
        font-weight: 700;
        font-size: 1rem;
    }

    .mobile-card-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: flex-start;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }

    .mobile-data-card .btn-action {
        flex: 0 0 auto;
        padding: 0.6rem 0.8rem;
        min-height: 40px;
        min-width: 40px;
    }

    .mobile-total-row {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 700;
        margin-top: 0.75rem;
    }

    .mobile-total-value {
        color: #ff8c42;
        font-size: 1.1rem;
    }

    /* Mobile Table Styles */
    .sales-table-wrapper {
        display: none;
        overflow-x: auto;
        max-width: 100%;
        width: 100%;
        box-sizing: border-box;
        -webkit-overflow-scrolling: touch;
    }

    .sales-mobile-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.8rem;
        table-layout: fixed;
    }

    .sales-mobile-table thead {
        background-color: #f3f4f6;
        border-bottom: 2px solid #e5e7eb;
    }

    .sales-mobile-table th {
        padding: 0.45rem 0.3rem;
        text-align: left;
        font-weight: 700;
        color: #333;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: normal;
        word-break: break-word;
        line-height: 1.2;
    }

    .sales-table-body {
        display: none;
    }

    .sales-table-body.active {
        display: table-body-group;
    }

    .sales-table-row {
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }

    .sales-table-row:hover {
        background-color: #f9fafb;
    }

    .sales-mobile-table td {
        padding: 0.35rem 0.3rem;
        vertical-align: middle;
        font-size: 0.7rem;
        word-break: break-word;
        overflow: hidden;
    }

    .sales-cell-csi {
        font-weight: 700;
        min-width: 55px;
        max-width: 70px;
    }

    .highlight-blue {
        color: #0066ff;
        font-weight: 700;
        display: block;
        word-break: break-word;
        max-width: 70px;
        font-size: 0.7rem;
    }

    .highlight-orange {
        color: #ff8c42;
        font-weight: 700;
        display: block;
        white-space: nowrap;
        font-size: 0.75rem;
    }

    .sales-cell-date {
        color: #333;
        font-size: 0.68rem;
        max-width: 50px;
        min-width: 48px;
    }

    .sales-cell-method {
        color: #666;
        font-size: 0.65rem;
        max-width: 55px;
        word-break: break-word;
        display: table-cell !important;
    }

    .sales-cell-amount {
        font-weight: 700;
        white-space: nowrap;
        min-width: 50px;
        font-size: 0.7rem;
    }

    .sales-cell-ar {
        color: #333;
        font-size: 0.55rem;
        display: none;
    }

    .sales-cell-remarks {
        color: #999;
        font-size: 0.65rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 40px;
        display: none;
    }

    .sales-cell-actions {
        padding: 0.4rem 0.3rem;
    }

    .action-buttons {
        display: flex;
        gap: 0;
        justify-content: center;
        min-width: 30px;
    }

    .sales-cell-actions {
        padding: 0.3rem 0.2rem;
        min-width: 35px;
        text-align: center;
    }

    .btn-action-icon {
        background: none;
        border: none;
        border-radius: 4px;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #0066ff;
        transition: all 0.2s ease;
        flex-shrink: 0;
        font-size: 1rem;
        padding: 0;
        min-width: 28px;
        min-height: 28px;
    }

    .btn-action-icon:nth-child(2),
    .btn-action-icon:nth-child(3) {
        display: none !important;
    }

    .btn-action-icon:hover {
        background-color: #f3f4f6;
        border-color: #9ca3af;
    }

    /* Pagination Navigation */
    .sales-pagination-nav {
        display: none;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        padding: 0.75rem 0.5rem;
        margin-top: 0.75rem;
        border-top: 1px solid #e5e7eb;
    }

    .pagination-arrow {
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #666;
        transition: all 0.2s ease;
        min-height: 40px;
        min-width: 40px;
        font-size: 1rem;
    }

    .pagination-arrow:hover:not(:disabled) {
        background-color: #f3f4f6;
        border-color: #9ca3af;
    }

    .pagination-arrow:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .pagination-info {
        font-size: 0.85rem;
        color: #666;
        font-weight: 600;
        min-width: 50px;
        text-align: center;
    }

    /* Pagination Dots */
    .mobile-pagination {
        display: none;
    }

    .accounting-table thead th {
        background-color: #f8f9fa;
        font-size: 0.9rem;
        font-weight: 700;
        color: #4b5563;
        border-bottom: 2px solid #e5e7eb;
        padding: 0.75rem 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .accounting-table tbody td {
        font-size: 0.95rem;
        padding: 1rem 1.25rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
        color: #333;
    }

    .accounting-table tbody td:first-child {
        color: #0066ff;
        font-weight: 700;
    }

    .accounting-table tbody tr:hover {
        background-color: #f9fafb;
    }

    .btn-action {
        padding: 0.5rem 0.7rem;
        font-size: 0.85rem;
        border-radius: 8px;
        transition: all 0.2s ease;
        border: none;
        min-height: 38px;
        min-width: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-action:hover {
        transform: translateY(-2px);
    }

    .btn-primary {
        background-color: #0d6efd;
        color: #fff;
    }

    .btn-primary:hover {
        background-color: #0a58ca;
    }

    .btn-orange {
        background-color: #fd7e14;
        border-color: #fd7e14;
        color: #fff;
    }

    .btn-orange:hover {
        background-color: #e06b0a;
        border-color: #e06b0a;
        color: #fff;
    }

    .btn-danger {
        background-color: #e53935;
        color: #fff;
    }

    .btn-danger:hover {
        background-color: #c62828;
        color: #fff;
    }

    .summary-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1a1a1a;
    }

    .summary-table {
        font-size: 0.95rem;
    }

    .summary-label {
        background-color: #f9fafb;
        padding-left: 1.5rem;
        font-weight: 600;
        color: #555;
    }

    .summary-value {
        padding-right: 1.5rem;
        font-weight: 600;
        color: #1a1a1a;
    }

    .summary-total-row .summary-label,
    .summary-total-row .summary-value {
        font-weight: 700;
        border-top: 2px solid #e5e7eb;
        padding-top: 0.75rem;
        color: #ff8c42;
    }

    .payment-box {
        border-radius: 14px;
        padding: 1.25rem 1rem;
        background-color: #f9fafb;
        text-align: center;
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }

    .payment-box:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .payment-icon {
        font-size: 2.2rem;
        margin-bottom: 0.75rem;
    }

    .payment-label {
        font-weight: 700;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
        color: #555;
    }

    .payment-amount {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1a1a1a;
    }

    .payment-percent {
        font-size: 0.85rem;
        color: #999;
        margin-top: 0.35rem;
    }

    .payment-box.cash .payment-icon { color: #16a34a; }
    .payment-box.cash { border-color: #d1fae5; background-color: #f0fdf4; }

    .payment-box.ewallet .payment-icon { color: #f59e0b; }
    .payment-box.ewallet { border-color: #fef3c7; background-color: #fffbeb; }

    .payment-box.cheque .payment-icon { color: #0ea5e9; }
    .payment-box.cheque { border-color: #cffafe; background-color: #f0f9fe; }

    /* Search & Filter Input Styles */
    .accounting-container .manage-search-group {
        width: 100%;
        max-width: 340px;
    }

    .accounting-actions .accounting-search-form {
        flex: 0 0 auto;
        width: auto;
    }

    .accounting-container .manage-search-input {
        min-height: 36px;
        padding: 0.45rem 0.65rem;
        font-size: 0.9rem;
    }

    .accounting-container .manage-search-btn {
        min-height: 36px;
        min-width: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.45rem 0.6rem;
    }

    .accounting-container .form-control,
    .accounting-container .form-select {
        min-height: 36px;
        padding: 0.45rem 0.65rem;
        font-size: 0.9rem;
    }

    /* ─── RESPONSIVE BREAKPOINTS ─── */

    /* Extra Small (320px - 479px) */
    @media (max-width: 479px) {
        /* Show/Hide Views */
        .desktop-table {
            display: none !important;
        }

        .mobile-card-view {
            display: block !important;
        }

        .sales-table-wrapper {
            display: block !important;
        }

        .sales-pagination-nav {
            display: flex !important;
        }
        .accounting-card .card-header {
            flex-direction: column;
            align-items: stretch;
            padding: 0.5rem;
            box-sizing: border-box;
        }

        .accounting-card .card-header h5 {
            width: 100%;
            text-align: left;
            margin-bottom: 0.6rem;
            font-size: 0.95rem;
        }

        .accounting-actions {
            width: 100%;
            flex-direction: column;
            gap: 0.3rem;
            box-sizing: border-box;
        }

        .accounting-search-form {
            width: 100%;
            box-sizing: border-box;
            max-width: none;
            flex: 1 1 auto;
        }

        .accounting-container .manage-search-group {
            width: 100%;
            max-width: none;
            box-sizing: border-box;
        }

        .accounting-container .manage-search-input {
            min-height: 40px;
            padding: 0.55rem 0.6rem;
            font-size: 0.90rem;
            box-sizing: border-box;
        }

        .accounting-container .manage-search-btn {
            min-height: 42px;
            min-width: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 0.6rem;
        }

        .btn-new-action {
            width: 100%;
            padding: 0.6rem 0.75rem;
            font-size: 0.9rem;
            min-height: 42px;
            box-sizing: border-box;
        }

        .btn-filter {
            width: 100%;
            padding: 0.6rem 0.75rem;
            font-size: 0.9rem;
            min-height: 42px;
            box-sizing: border-box;
        }

        .accounting-container {
            padding-inline: 0.5rem;
            padding-top: 1rem;
        }

        .accounting-topbar {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 1rem;
            box-sizing: border-box;
        }

        .accounting-header-text {
            width: 100%;
        }

        .accounting-title {
            font-size: 24px;
            margin-bottom: 0.4rem;
        }

        .accounting-period {
            font-size: 13.5px;
        }

        .btn-export-pdf {
            width: 44px;
            height: 44px;
            padding: 0;
            border-radius: 10px;
            font-size: 0;
            justify-content: center;
            gap: 0;
            align-self: flex-start;
        }

        .btn-export-pdf i {
            font-size: 1.25rem;
            color: #fff;
        }

        .btn-export-pdf .btn-label {
            display: none;
        }

        /* Mobile card styles */
        .mobile-data-card {
            padding: 0.6rem;
            margin-bottom: 0.6rem;
            border-radius: 10px;
            overflow: hidden;
            box-sizing: border-box;
            width: 100%;
        }

        .mobile-card-row {
            gap: 0.2rem 0.5rem;
            margin-bottom: 0.25rem;
            word-wrap: break-word;
            overflow-wrap: break-word;
            overflow: hidden;
        }

        .mobile-card-label {
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .mobile-card-value {
            font-size: 0.9rem;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .mobile-card-actions {
            gap: 0.35rem;
            margin-top: 0.6rem;
            padding-top: 0.4rem;
        }

        .mobile-data-card .btn-action {
            padding: 0.55rem 0.45rem;
            font-size: 0.75rem;
            flex: 1;
            min-width: 0;
            min-height: 36px;
        }

        .mobile-pagination {
            display: flex;
        }

        .summary-table {
            font-size: 0.9rem;
        }

        .summary-label,
        .summary-value {
            padding: 0.6rem 0.9rem;
        }

        .payment-box {
            padding: 1rem;
            border-radius: 10px;
        }

        .payment-icon {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .payment-label {
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .payment-amount {
            font-size: 1.05rem;
        }

        .payment-percent {
            font-size: 0.8rem;
        }

        /* Mobile Table Responsive */
        .sales-mobile-table th {
            font-size: 0.7rem;
            padding: 0.4rem 0.3rem;
            letter-spacing: 0.3px;
        }

        .sales-mobile-table td {
            font-size: 0.7rem;
            padding: 0.35rem 0.3rem;
        }

        .sales-cell-csi {
            font-weight: 700;
            max-width: 65px;
            min-width: 50px;
        }

        .highlight-blue {
            max-width: 65px;
            font-size: 0.7rem;
        }

        .sales-cell-date {
            font-size: 0.68rem;
            max-width: 50px;
            min-width: 45px;
        }

        .sales-cell-amount {
            font-size: 0.7rem;
            min-width: 48px;
        }

        .sales-cell-method {
            font-size: 0.65rem;
            max-width: 55px;
            display: table-cell !important;
        }

        .sales-cell-remarks {
            max-width: 30px;
        }

        .highlight-orange {
            font-size: 0.75rem;
        }

        .sales-table-wrapper {
            margin: 0;
            padding: 0;
        }

        .btn-action-icon {
            width: 26px;
            height: 26px;
            font-size: 0.95rem;
            border-radius: 3px;
        }

        .action-buttons {
            gap: 0;
            min-width: 28px;
        }

        .sales-cell-actions {
            min-width: 30px;
            padding: 0.25rem 0.2rem;
        }

        .sales-pagination-nav {
            display: flex !important;
            padding: 0.5rem 0.25rem;
            margin: 0 -0.5rem;
            gap: 0.75rem;
        }

        .pagination-arrow {
            width: 30px;
            height: 30px;
            font-size: 0.8rem;
            padding: 0 0.2rem;
        }

        .pagination-info {
            font-size: 0.7rem;
            min-width: 40px;
        }

    }

    /* Small (480px - 639px) */
    @media (min-width: 480px) and (max-width: 639px) {
        /* Show/Hide Views */
        .desktop-table {
            display: none !important;
        }

        .mobile-card-view {
            display: block !important;
        }

        .sales-table-wrapper {
            display: block !important;
        }

        .sales-pagination-nav {
            display: flex !important;
        }

        .accounting-title {
            font-size: 24px;
        }

        .accounting-period {
            font-size: 14px;
        }

        .accounting-card .card-header {
            padding: 0.7rem 1rem;
            gap: 0.5rem;
        }

        .accounting-actions {
            gap: 0.6rem;
        }

        .accounting-container .manage-search-group {
            width: 100%;
        }

        .btn-new-action,
        .btn-filter {
            padding: 0.6rem 0.8rem;
            font-size: 0.9rem;
            min-height: 40px;
        }

        .btn-new-action .btn-label,
        .btn-filter .btn-label {
            display: none;
        }

        /* Mobile view adjustments */
        .mobile-data-card {
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .sales-mobile-table th {
            font-size: 0.75rem;
            padding: 0.45rem 0.35rem;
            letter-spacing: 0.3px;
        }

        .sales-mobile-table td {
            font-size: 0.75rem;
            padding: 0.35rem 0.35rem;
        }

        .mobile-card-actions {
            gap: 0.4rem;
        }

        .mobile-data-card .btn-action {
            padding: 0.6rem 0.5rem;
            font-size: 0.8rem;
            min-height: 36px;
        }

        .summary-table {
            font-size: 0.95rem;
        }

        .payment-label,
        .payment-amount {
            font-size: 1rem;
        }

        .pagination-info {
            font-size: 0.8rem;
        }

        .pagination-arrow {
            width: 36px;
            height: 36px;
            font-size: 0.9rem;
        }
    }

    /* Tablet (640px - 767px) */
    @media (min-width: 640px) and (max-width: 767px) {
        /* Show/Hide Views */
        .desktop-table {
            display: none !important;
        }

        .mobile-card-view {
            display: block !important;
        }

        .accounting-topbar {
            gap: 1rem;
        }

        .accounting-title {
            font-size: 28px;
        }

        .accounting-period {
            font-size: 15px;
        }

        .accounting-actions {
            gap: 0.75rem;
        }

        .accounting-container .manage-search-group {
            width: auto;
            min-width: 180px;
        }

        .btn-new-action {
            padding: 0.6rem 0.8rem;
            font-size: 0;
            width: 44px;
            height: 44px;
            min-height: 44px;
        }

        .btn-new-action .btn-label {
            display: none;
        }

        .payment-box {
            padding: 1rem 0.75rem;
        }

        .payment-amount {
            font-size: 1.1rem;
        }
    }

    /* Desktop (768px+) */
    @media (min-width: 768px) {
        /* Show desktop table, hide mobile view */
        .desktop-table {
            display: block !important;
        }

        .mobile-card-view {
            display: none !important;
        }

        .sales-table-wrapper {
            display: none !important;
        }

        .sales-pagination-nav {
            display: none !important;
        }

        .btn-export-pdf {
            font-size: 0.95rem;
        }

        .btn-export-pdf .btn-label {
            display: inline;
        }

        .btn-new-action {
            font-size: 0.95rem;
        }

        .btn-new-action .btn-label {
            display: inline;
        }

        .accounting-table thead th {
            font-size: 0.9rem;
        }

        .accounting-table tbody td {
            font-size: 0.95rem;
        }

        .btn-action {
            font-size: 0.85rem;
        }
    }

    html, body {
        overflow-x: hidden !important;
        max-width: 100vw !important;
    }

    .page-content, .content-wrapper {
        overflow-x: hidden !important;
    }
</style>

<!-- Sales Filter Modal -->
<div class="modal fade" id="salesFilterModal" tabindex="-1" aria-labelledby="salesFilterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="salesFilterLabel">Filter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Columns Section -->
                <div class="filter-section">
                    <h6 class="filter-section-title">Columns</h6>
                    <div class="filter-checkboxes">
                        <label class="filter-checkbox">
                            <input type="checkbox" class="column-toggle" data-column="csi" checked>
                            <span>CSI</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" class="column-toggle" data-column="date" checked>
                            <span>Date</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" class="column-toggle" data-column="sales" checked>
                            <span>Cash Sales</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" class="column-toggle" data-column="method" checked>
                            <span>Method</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" class="column-toggle" data-column="ar" checked>
                            <span>Account Receivable</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" class="column-toggle" data-column="remarks" checked>
                            <span>Remarks</span>
                        </label>
                    </div>
                </div>

                <!-- Date Section -->
                <div class="filter-section">
                    <h6 class="filter-section-title">Dates</h6>
                    <div class="filter-checkboxes">
                        <label class="filter-checkbox">
                            <input type="checkbox" class="date-filter-toggle" data-filter="creation" checked>
                            <span>Date of creation</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" class="date-filter-toggle" data-filter="change">
                            <span>Date of change</span>
                        </label>
                    </div>
                </div>

                <!-- Period Section -->
                <div class="filter-section">
                    <h6 class="filter-section-title">Period</h6>
                    <div class="filter-radios">
                        <label class="filter-radio">
                            <input type="radio" name="period" value="week">
                            <span>Week</span>
                        </label>
                        <label class="filter-radio">
                            <input type="radio" name="period" value="month" checked>
                            <span>Month</span>
                        </label>
                        <label class="filter-radio">
                            <input type="radio" name="period" value="year">
                            <span>Year</span>
                        </label>
                    </div>
                </div>

                <!-- Date Range Section -->
                <div class="filter-section">
                    <h6 class="filter-section-title">Custom Date Range</h6>
                    <div class="filter-date-inputs">
                        <div class="date-input-group">
                            <label>From</label>
                            <input type="date" class="form-control filter-date-from" id="filterDateFrom">
                        </div>
                        <div class="date-input-group">
                            <label>To</label>
                            <input type="date" class="form-control filter-date-to" id="filterDateTo">
                        </div>
                    </div>
                </div>

                <div class="filter-section mb-0">
                    <h6 class="filter-section-title">Filter By</h6>
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-5">
                            <label for="salesFilterByField" class="form-label mb-1">Field</label>
                            <select id="salesFilterByField" class="form-select form-select-sm">
                                <option value="all">All Fields</option>
                                <option value="csi">CSI</option>
                                <option value="date">Date</option>
                                <option value="sales">Cash Sales</option>
                                <option value="method">Method</option>
                                <option value="ar">Account Receivable</option>
                                <option value="remarks">Remarks</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-7">
                            <label for="salesFilterByValue" class="form-label mb-1">Keyword</label>
                            <input type="text" id="salesFilterByValue" class="form-control form-control-sm" placeholder="Type a value to filter sales rows">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="applyFiltersBtn">Apply Filters</button>
                <button type="button" class="btn btn-danger" id="resetFiltersBtn">Reset</button>
            </div>
        </div>
    </div>
</div>

<!-- Expenses Filter Modal -->
<div class="modal fade" id="expensesFilterModal" tabindex="-1" aria-labelledby="expensesFilterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="expensesFilterLabel">Filter Expenses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="filter-section">
                    <h6 class="filter-section-title">Columns</h6>
                    <div class="filter-checkboxes">
                        <label class="filter-checkbox">
                            <input type="checkbox" class="expenses-column-toggle" data-column="date" checked>
                            <span>Date</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" class="expenses-column-toggle" data-column="description" checked>
                            <span>Description</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" class="expenses-column-toggle" data-column="amount" checked>
                            <span>Amount</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" class="expenses-column-toggle" data-column="remarks" checked>
                            <span>Remarks</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" class="expenses-column-toggle" data-column="action" checked>
                            <span>Action</span>
                        </label>
                    </div>
                </div>

                <div class="filter-section">
                    <h6 class="filter-section-title">Period</h6>
                    <div class="filter-radios">
                        <label class="filter-radio">
                            <input type="radio" name="expensesPeriod" value="week">
                            <span>Week</span>
                        </label>
                        <label class="filter-radio">
                            <input type="radio" name="expensesPeriod" value="month" checked>
                            <span>Month</span>
                        </label>
                        <label class="filter-radio">
                            <input type="radio" name="expensesPeriod" value="year">
                            <span>Year</span>
                        </label>
                    </div>
                </div>

                <div class="filter-section">
                    <h6 class="filter-section-title">Custom Date Range</h6>
                    <div class="filter-date-inputs">
                        <div class="date-input-group">
                            <label>From</label>
                            <input type="date" class="form-control filter-date-from" id="expensesFilterDateFrom">
                        </div>
                        <div class="date-input-group">
                            <label>To</label>
                            <input type="date" class="form-control filter-date-to" id="expensesFilterDateTo">
                        </div>
                    </div>
                </div>

                <div class="filter-section mb-0">
                    <h6 class="filter-section-title">Filter By</h6>
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-5">
                            <label for="expensesFilterByField" class="form-label mb-1">Field</label>
                            <select id="expensesFilterByField" class="form-select form-select-sm">
                                <option value="all">All Fields</option>
                                <option value="date">Date</option>
                                <option value="description">Description</option>
                                <option value="amount">Amount</option>
                                <option value="remarks">Remarks</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-7">
                            <label for="expensesFilterByValue" class="form-label mb-1">Keyword</label>
                            <input type="text" id="expensesFilterByValue" class="form-control form-control-sm" placeholder="Type a value to filter expense rows">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="applyExpensesFiltersBtn">Apply Filters</button>
                <button type="button" class="btn btn-danger" id="resetExpensesFiltersBtn">Reset</button>
            </div>
        </div>
    </div>
</div>

<?= view('accounting/new_sale') ?>
<?= view('accounting/view_sale') ?>
<?= view('accounting/edit_sale') ?>
<?= view('accounting/delete_sale') ?>
<?= view('accounting/new_expense') ?>
<?= view('accounting/view_expense') ?>
<?= view('accounting/edit_expense') ?>
<?= view('accounting/delete_expense') ?>
<?= view('accounting/collect_payment') ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Table pagination logic
    const tableWrapper = document.querySelector('.sales-table-wrapper');
    const paginationNav = document.querySelector('.sales-pagination-nav');
    
    if (tableWrapper) {
        const tableBody = tableWrapper.querySelector('.sales-table-body');
        const allRows = Array.from(tableBody.querySelectorAll('.sales-table-row'));
        const itemsPerPage = 5;
        let currentPage = 0;
        
        // Show/hide table on mobile
        function updateTableDisplay() {
            if (window.innerWidth < 768) {
                tableWrapper.style.display = 'block';
                paginationNav.style.display = 'flex';
            } else {
                tableWrapper.style.display = 'none';
                paginationNav.style.display = 'none';
            }
        }
        
        // Show specific page
        function showPage(pageNum) {
            const totalPages = Math.ceil(allRows.length / itemsPerPage);
            currentPage = Math.max(0, Math.min(pageNum, totalPages - 1));
            
            const startIdx = currentPage * itemsPerPage;
            const endIdx = startIdx + itemsPerPage;
            
            // Show/hide rows
            allRows.forEach((row, idx) => {
                row.style.display = (idx >= startIdx && idx < endIdx) ? 'table-row' : 'none';
            });
            
            // Update pagination info
            document.querySelector('.current-page').textContent = currentPage + 1;
            document.querySelector('.total-pages').textContent = totalPages;
            
            // Update buttons
            const prevBtn = document.querySelector('.pagination-prev-arrow');
            const nextBtn = document.querySelector('.pagination-next-arrow');
            prevBtn.disabled = currentPage === 0;
            nextBtn.disabled = endIdx >= allRows.length;
        }
        
        // Navigation handlers
        const prevPageBtn = document.querySelector('.pagination-prev-arrow');
        const nextPageBtn = document.querySelector('.pagination-next-arrow');
        if (prevPageBtn && nextPageBtn) {
            prevPageBtn.addEventListener('click', () => showPage(currentPage - 1));
            nextPageBtn.addEventListener('click', () => showPage(currentPage + 1));
        }
        
        // Initialize
        updateTableDisplay();
        showPage(0);
        
        // Handle resize
        window.addEventListener('resize', updateTableDisplay);
    }

    // Filter functionality
    const filterModal = document.getElementById('salesFilterModal');
    if (filterModal) {
        const applyBtn = document.getElementById('applyFiltersBtn');
        const resetBtn = document.getElementById('resetFiltersBtn');
        const columnToggles = document.querySelectorAll('.column-toggle');
        const dateFilterToggles = document.querySelectorAll('.date-filter-toggle');
        const periodRadios = document.querySelectorAll('input[name="period"]');
        const dateFromInput = document.getElementById('filterDateFrom');
        const dateToInput = document.getElementById('filterDateTo');
        const salesFilterByFieldInput = document.getElementById('salesFilterByField');
        const salesFilterByValueInput = document.getElementById('salesFilterByValue');

        const salesDesktopTable = document.getElementById('salesDesktopTable');
        const salesMobileTable = document.querySelector('.sales-mobile-table');
        const columnIndexMap = {
            csi: 0,
            date: 1,
            sales: 2,
            method: 3,
            ar: 4,
            remarks: 5,
            action: 6,
        };
        let selectedSalesFilterByField = 'all';
        let selectedSalesFilterByValue = '';
        let salesFiltersApplied = false;

        const getRowDate = (row) => {
            const dateCell = row.children[1];
            const value = (dateCell?.textContent || '').trim();
            const timestamp = Date.parse(value);
            return Number.isNaN(timestamp) ? null : new Date(timestamp);
        };

        const getPeriodRange = (periodValue) => {
            const now = new Date();
            const end = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59, 999);
            const start = new Date(end);

            if (periodValue === 'week') {
                start.setDate(start.getDate() - 6);
                start.setHours(0, 0, 0, 0);
                return { start, end };
            }

            if (periodValue === 'year') {
                start.setMonth(0, 1);
                start.setHours(0, 0, 0, 0);
                return { start, end };
            }

            start.setDate(1);
            start.setHours(0, 0, 0, 0);
            return { start, end };
        };

        const applySalesColumnVisibility = () => {
            columnToggles.forEach((toggle) => {
                const colKey = toggle.dataset.column;
                const colIndex = columnIndexMap[colKey];
                if (typeof colIndex === 'undefined') {
                    return;
                }

                [salesDesktopTable, salesMobileTable].forEach((table) => {
                    if (!table) {
                        return;
                    }

                    table.querySelectorAll('tr').forEach((row) => {
                        const cells = row.querySelectorAll('th, td');
                        if (cells[colIndex]) {
                            cells[colIndex].style.display = toggle.checked ? '' : 'none';
                        }
                    });
                });
            });
        };

        const applySalesDateFilter = () => {
            const checkedPeriod = document.querySelector('input[name="period"]:checked');
            const selectedPeriod = checkedPeriod ? checkedPeriod.value : 'month';

            const customFrom = dateFromInput.value ? new Date(dateFromInput.value + 'T00:00:00') : null;
            const customTo = dateToInput.value ? new Date(dateToInput.value + 'T23:59:59') : null;
            const { start: periodStart, end: periodEnd } = getPeriodRange(selectedPeriod);

            const desktopRows = salesDesktopTable
                ? Array.from(salesDesktopTable.querySelectorAll('tbody tr')).filter((row) => row.children.length >= 7)
                : [];
            const mobileRows = salesMobileTable
                ? Array.from(salesMobileTable.querySelectorAll('tbody tr.sales-table-row'))
                : [];

            if (!salesFiltersApplied) {
                desktopRows.forEach((row) => {
                    row.style.display = '';
                });
                mobileRows.forEach((row) => {
                    row.style.display = '';
                });
                return;
            }

            desktopRows.forEach((row, rowIndex) => {
                const rowDate = getRowDate(row);
                let visible = true;

                if (rowDate) {
                    if (customFrom || customTo) {
                        if (customFrom && rowDate < customFrom) {
                            visible = false;
                        }
                        if (customTo && rowDate > customTo) {
                            visible = false;
                        }
                    } else if (rowDate < periodStart || rowDate > periodEnd) {
                        visible = false;
                    }
                }

                if (visible) {
                    const fieldText = (() => {
                        if (selectedSalesFilterByField === 'all') {
                            return row.textContent.toLowerCase();
                        }

                        const fieldIndex = columnIndexMap[selectedSalesFilterByField];
                        if (typeof fieldIndex !== 'number' || !row.children[fieldIndex]) {
                            return '';
                        }

                        return (row.children[fieldIndex].textContent || '').trim().toLowerCase();
                    })();

                    if (selectedSalesFilterByValue && !fieldText.includes(selectedSalesFilterByValue)) {
                        visible = false;
                    }
                }

                row.style.display = visible ? '' : 'none';
                if (mobileRows[rowIndex]) {
                    mobileRows[rowIndex].style.display = visible ? '' : 'none';
                }
            });
        };

        const closeSalesFilterModal = () => {
            const instance = bootstrap.Modal.getOrCreateInstance(filterModal);
            instance.hide();
        };

        columnToggles.forEach((toggle) => {
            toggle.addEventListener('change', applySalesColumnVisibility);
        });

        // Apply filters
        if (applyBtn) {
            applyBtn.addEventListener('click', () => {
                salesFiltersApplied = true;
                selectedSalesFilterByField = salesFilterByFieldInput ? salesFilterByFieldInput.value : 'all';
                selectedSalesFilterByValue = salesFilterByValueInput ? salesFilterByValueInput.value.trim().toLowerCase() : '';
                applySalesColumnVisibility();
                applySalesDateFilter();
                closeSalesFilterModal();
            });
        }

        // Reset filters
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                salesFiltersApplied = false;
                columnToggles.forEach(t => t.checked = true);
                dateFilterToggles.forEach(t => {
                    t.checked = t.dataset.filter === 'creation';
                });
                const defaultSalesPeriod = document.querySelector('input[name="period"][value="month"]');
                if (defaultSalesPeriod) {
                    defaultSalesPeriod.checked = true;
                }
                dateFromInput.value = '';
                dateToInput.value = '';
                selectedSalesFilterByField = 'all';
                selectedSalesFilterByValue = '';
                if (salesFilterByFieldInput) {
                    salesFilterByFieldInput.value = 'all';
                }
                if (salesFilterByValueInput) {
                    salesFilterByValueInput.value = '';
                }

                applySalesColumnVisibility();
                applySalesDateFilter();
                closeSalesFilterModal();
            });
        }

        applySalesColumnVisibility();
        applySalesDateFilter();
    }

    const expensesFilterModal = document.getElementById('expensesFilterModal');
    if (expensesFilterModal) {
        const applyExpensesBtn = document.getElementById('applyExpensesFiltersBtn');
        const resetExpensesBtn = document.getElementById('resetExpensesFiltersBtn');
        const expensesColumnToggles = document.querySelectorAll('.expenses-column-toggle');
        const expensesDateFromInput = document.getElementById('expensesFilterDateFrom');
        const expensesDateToInput = document.getElementById('expensesFilterDateTo');
        const expensesFilterByFieldInput = document.getElementById('expensesFilterByField');
        const expensesFilterByValueInput = document.getElementById('expensesFilterByValue');
        const expensesDesktopTable = document.getElementById('expensesDesktopTable');

        const expensesColumnIndexMap = {
            date: 0,
            description: 1,
            amount: 2,
            remarks: 3,
            action: 4,
        };
        let selectedExpensesFilterByField = 'all';
        let selectedExpensesFilterByValue = '';
        let expensesFiltersApplied = false;

        const getPeriodRange = (periodValue) => {
            const now = new Date();
            const end = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59, 999);
            const start = new Date(end);

            if (periodValue === 'week') {
                start.setDate(start.getDate() - 6);
                start.setHours(0, 0, 0, 0);
                return { start, end };
            }

            if (periodValue === 'year') {
                start.setMonth(0, 1);
                start.setHours(0, 0, 0, 0);
                return { start, end };
            }

            start.setDate(1);
            start.setHours(0, 0, 0, 0);
            return { start, end };
        };

        const applyExpensesColumnVisibility = () => {
            if (!expensesDesktopTable) {
                return;
            }

            expensesColumnToggles.forEach((toggle) => {
                const colIndex = expensesColumnIndexMap[toggle.dataset.column];
                if (typeof colIndex === 'undefined') {
                    return;
                }

                expensesDesktopTable.querySelectorAll('tr').forEach((row) => {
                    const cells = row.querySelectorAll('th, td');
                    if (cells[colIndex]) {
                        cells[colIndex].style.display = toggle.checked ? '' : 'none';
                    }
                });
            });
        };

        const applyExpensesDateFilter = () => {
            if (!expensesDesktopTable) {
                return;
            }

            const selectedPeriod = document.querySelector('input[name="expensesPeriod"]:checked')?.value || 'month';
            const customFrom = expensesDateFromInput.value ? new Date(expensesDateFromInput.value + 'T00:00:00') : null;
            const customTo = expensesDateToInput.value ? new Date(expensesDateToInput.value + 'T23:59:59') : null;
            const { start: periodStart, end: periodEnd } = getPeriodRange(selectedPeriod);

            const rows = Array.from(expensesDesktopTable.querySelectorAll('tbody tr')).filter((row) => row.children.length === 5);
            if (!expensesFiltersApplied) {
                rows.forEach((row) => {
                    row.style.display = '';
                });
                return;
            }

            rows.forEach((row) => {
                if (row.classList.contains('table-light')) {
                    row.style.display = '';
                    return;
                }

                const dateValue = (row.children[0]?.textContent || '').trim();
                const ts = Date.parse(dateValue);
                if (Number.isNaN(ts)) {
                    row.style.display = '';
                    return;
                }

                const rowDate = new Date(ts);
                let visible = true;

                if (customFrom || customTo) {
                    if (customFrom && rowDate < customFrom) {
                        visible = false;
                    }
                    if (customTo && rowDate > customTo) {
                        visible = false;
                    }
                } else if (rowDate < periodStart || rowDate > periodEnd) {
                    visible = false;
                }

                if (visible) {
                    const fieldText = (() => {
                        if (selectedExpensesFilterByField === 'all') {
                            return row.textContent.toLowerCase();
                        }

                        const fieldIndex = expensesColumnIndexMap[selectedExpensesFilterByField];
                        if (typeof fieldIndex !== 'number' || !row.children[fieldIndex]) {
                            return '';
                        }

                        return (row.children[fieldIndex].textContent || '').trim().toLowerCase();
                    })();

                    if (selectedExpensesFilterByValue && !fieldText.includes(selectedExpensesFilterByValue)) {
                        visible = false;
                    }
                }

                row.style.display = visible ? '' : 'none';
            });
        };

        const closeExpensesModal = () => {
            bootstrap.Modal.getOrCreateInstance(expensesFilterModal).hide();
        };

        expensesColumnToggles.forEach((toggle) => {
            toggle.addEventListener('change', applyExpensesColumnVisibility);
        });

        if (applyExpensesBtn) {
            applyExpensesBtn.addEventListener('click', () => {
                expensesFiltersApplied = true;
                selectedExpensesFilterByField = expensesFilterByFieldInput ? expensesFilterByFieldInput.value : 'all';
                selectedExpensesFilterByValue = expensesFilterByValueInput ? expensesFilterByValueInput.value.trim().toLowerCase() : '';
                applyExpensesColumnVisibility();
                applyExpensesDateFilter();
                closeExpensesModal();
            });
        }

        if (resetExpensesBtn) {
            resetExpensesBtn.addEventListener('click', () => {
                expensesFiltersApplied = false;
                expensesColumnToggles.forEach((toggle) => {
                    toggle.checked = true;
                });
                const defaultPeriod = document.querySelector('input[name="expensesPeriod"][value="month"]');
                if (defaultPeriod) {
                    defaultPeriod.checked = true;
                }
                expensesDateFromInput.value = '';
                expensesDateToInput.value = '';
                selectedExpensesFilterByField = 'all';
                selectedExpensesFilterByValue = '';
                if (expensesFilterByFieldInput) {
                    expensesFilterByFieldInput.value = 'all';
                }
                if (expensesFilterByValueInput) {
                    expensesFilterByValueInput.value = '';
                }

                applyExpensesColumnVisibility();
                applyExpensesDateFilter();
                closeExpensesModal();
            });
        }

        applyExpensesColumnVisibility();
        applyExpensesDateFilter();
    }
});
</script>

<?php $this->endSection(); ?>
