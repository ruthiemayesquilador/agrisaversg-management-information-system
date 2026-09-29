<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<?php
$user_role = isset($user_role) ? (string) $user_role : '';
$user_branch = isset($user_branch) ? (string) $user_branch : (string) (session()->get('branch') ?? '');
$userBranch = isset($userBranch) ? (string) $userBranch : $user_branch;
$canCrossBranchView = isset($canCrossBranchView) ? (bool) $canCrossBranchView : false;
if (!isset($pending_stock_requests) || !is_array($pending_stock_requests)) {
    $pending_stock_requests = [];
}
if (!isset($branches) || !is_array($branches)) {
    $branches = [];
}
if (!isset($availableBranches) || !is_array($availableBranches)) {
    $availableBranches = [];
}
if (!isset($transfers) || !is_array($transfers)) {
    $transfers = [];
}
$activeBranchMode = isset($activeBranchMode) ? (string) $activeBranchMode : 'mine';
$activeBranchFilter = isset($activeBranchFilter) ? (string) $activeBranchFilter : '';
$can_receive_transfer = isset($can_receive_transfer) ? (bool) $can_receive_transfer : false;
$can_dispatch_transfer = isset($can_dispatch_transfer) ? (bool) $can_dispatch_transfer : false;
$isMainBranch = isset($isMainBranch) ? (bool) $isMainBranch : false;
$search = isset($search) ? (string) $search : '';
$lowOnly = !empty($lowOnly);
$transferSourceBranch = isset($transferSourceBranch) ? (string) $transferSourceBranch : '';
?>

<style>
    .btn-filter {
        background: #fff;
        color: #555;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .btn-filter:hover {
        background: #f8f9fa;
        color: #0d6efd;
        border-color: #9ec5fe;
    }

    .btn-filter .btn-label {
        white-space: nowrap;
    }

    .branch-filter-menu {
        max-height: 260px;
        overflow-y: auto;
    }
</style>

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="text-center mb-4">
        <h2 class="suppliers-title">SUPPLIERS</h2>
    </div>

    <!-- Suppliers Card -->
    <div class="card suppliers-card shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <h5 class="mb-0">Supplier</h5>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group manage-search-group">
                    <input type="text" class="form-control manage-search-input" id="suppliersSearchInput" placeholder="Search by supplier, brand, importer...">
                    <button class="btn manage-search-btn" type="button" data-bs-toggle="modal" data-bs-target="#suppliersFilterModal" aria-label="Filter" title="Filter"><i class="bi bi-funnel"></i></button>
                    <button class="btn manage-search-btn" type="button" id="suppliersSearchBtn" aria-label="Search" title="Search"><i class="bi bi-search"></i></button>
                </div>
                <button class="btn btn-new-supplier" data-bs-toggle="modal" data-bs-target="#newSupplierModal">
                    <i class="bi bi-plus-circle"></i> New Supplier
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table suppliers-table mb-0 js-sortable-table" id="suppliersTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Supplier Name</th>
                            <th>Brand</th>
                            <th>Importer</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($suppliers)): ?>
                            <?php foreach ($suppliers as $index => $supplier): ?>
                                <tr>
                                    <td><?= str_pad($index + 1, 3, '0', STR_PAD_LEFT) ?></td>
                                    <td><?= esc((string) ($supplier['supplier_name'] ?? '')) ?></td>
                                    <td><?= esc((string) ($supplier['brand_name'] ?? '')) ?></td>
                                    <td>
                                        <?php if (!empty($supplier['importer'])): ?>
                                            <span class="badge rounded-pill <?= (string) ($supplier['importer'] ?? '') === 'Local' ? 'bg-success' : 'bg-primary' ?>" style="font-size:12px;">
                                                <?= esc((string) ($supplier['importer'] ?? '')) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-sm btn-action btn-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewSupplierModal"
                                                data-id="<?= esc((string) ($supplier['supplier_id'] ?? '')) ?>"
                                                data-supplier="<?= esc((string) ($supplier['supplier_name'] ?? '')) ?>"
                                                data-brand-name="<?= esc((string) ($supplier['brand_name'] ?? '')) ?>"
                                                data-importer="<?= esc((string) ($supplier['importer'] ?? '')) ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-action btn-warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editSupplierModal"
                                                data-id="<?= esc((string) ($supplier['supplier_id'] ?? '')) ?>"
                                                data-supplier="<?= esc((string) ($supplier['supplier_name'] ?? '')) ?>"
                                                data-brand-name="<?= esc((string) ($supplier['brand_name'] ?? '')) ?>"
                                                data-importer="<?= esc((string) ($supplier['importer'] ?? '')) ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-action btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteSupplierModal"
                                                data-id="<?= esc((string) ($supplier['supplier_id'] ?? '')) ?>"
                                                data-supplier="<?= esc((string) ($supplier['supplier_name'] ?? '')) ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No suppliers found. Click "New Supplier" to add one.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($user_role === 'admin' && !$isMainBranch): ?>
    <div class="card stock-request-card shadow-sm mt-4" style="border-left: 4px solid #0d6efd;">
        <div class="card-header d-flex align-items-center justify-content-between gap-3 flex-wrap" style="background: linear-gradient(135deg, #e7f1ff 0%, #dbe9ff 100%);">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-card-checklist" style="font-size: 1.3rem; color: #0d6efd;"></i>
                <div>
                    <h5 class="mb-0">Request Stock from Main Branch</h5>
                    <small class="text-muted">Create a new stock request from your branch.</small>
                </div>
            </div>
            <a href="<?= base_url('stocks/requestStock') ?>" class="btn btn-sm btn-warning">
                <i class="bi bi-hand-holding"></i> Request Stock
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #e0e0e0;">
                            <th>Action</th>
                            <th>Description</th>
                            <th>Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Submit Stock Request</td>
                            <td>Send a request to the main branch for replenishment.</td>
                            <td>
                                <a href="<?= base_url('stocks/requestStock') ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-hand-holding me-1"></i>Request Stock
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pending Stock Requests Card (for Main Branch/Super Admin) -->
    <?php if (($user_role === 'super_admin' || ($user_role === 'admin' && ($isMainBranch ?? false))) && !empty($pending_stock_requests)): ?>
    <div class="card stock-requests-card shadow-sm mt-4" style="border-left: 4px solid #ffc107;">
        <div class="card-header d-flex align-items-center justify-content-between gap-3 flex-wrap" style="background: linear-gradient(135deg, #fff8e1 0%, #fffacd 100%);">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-bell-fill" style="font-size: 1.3rem; color: #ff9800;"></i>
                <div>
                    <h5 class="mb-0">Pending Stock Requests from Branches</h5>
                    <small class="text-muted"><?= count($pending_stock_requests) ?> pending request<?= count($pending_stock_requests) !== 1 ? 's' : '' ?></small>
                </div>
            </div>
            <a href="<?= base_url('stocks/requests') ?>" class="btn btn-sm btn-warning">
                <i class="bi bi-arrow-right"></i> View All
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table requests-table mb-0">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #e0e0e0;">
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Requesting Branch</th>
                            <th>Quantity</th>
                            <th>Requested By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_stock_requests as $req): ?>
                        <tr>
                            <td>
                                <strong><?= esc((string) ($req['part_name'] ?? 'N/A')) ?></strong>
                                <?php if (!empty($req['part_no'])): ?>
                                    <br><small class="text-muted">Part No: <?= esc((string) ($req['part_no'] ?? '')) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= esc((string) ($req['sku'] ?? 'N/A')) ?></td>
                            <td><span class="badge bg-info"><?= esc((string) ($req['requesting_branch'] ?? '')) ?></span></td>
                            <td><strong><?= intval($req['requested_quantity'] ?? 0) ?></strong></td>
                            <td><?= esc((string) ($req['requested_by'] ?? '—')) ?></td>
                            <td><?= !empty($req['created_at']) ? date('M d, Y', strtotime($req['created_at'])) : 'N/A' ?></td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-action btn-info" 
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewStockRequestModal"
                                        data-id="<?= esc((string) ($req['request_id'] ?? '')) ?>"
                                        data-product="<?= esc((string) ($req['part_name'] ?? '')) ?>"
                                        data-branch="<?= esc((string) ($req['requesting_branch'] ?? '')) ?>"
                                        data-quantity="<?= esc((string) (intval($req['requested_quantity'] ?? 0))) ?>"
                                        data-requested-by="<?= esc((string) ($req['requested_by'] ?? '')) ?>"
                                        data-reason="<?= esc((string) ($req['reason'] ?? '')) ?>">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <form method="post" action="<?= base_url('stocks/approveRequest') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="request_id" value="<?= $req['request_id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-action btn-success" title="Approve Request" onclick="return confirm('Approve this stock request?')">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                                <form method="post" action="<?= base_url('stocks/cancelRequest') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="request_id" value="<?= $req['request_id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-action btn-danger" title="Cancel Request" onclick="return confirm('Cancel this stock request?')">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card transfer-card shadow-sm mt-4">
        <div class="card-header d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <h5 class="mb-0">Transfer Products Between Branches</h5>
            <?php
                $canCrossBranchView = (bool) ($canCrossBranchView ?? false);
                $activeBranchMode = trim((string) ($activeBranchMode ?? 'mine'));
                $activeBranchFilter = trim((string) ($activeBranchFilter ?? ''));
                $userBranch = trim((string) ($userBranch ?? session()->get('branch') ?? ''));
                $availableBranches = is_array($availableBranches ?? null) ? $availableBranches : [];

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
            ?>
            <?php if ($canCrossBranchView): ?>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-filter dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Filter branches" title="Branches">
                        <span class="btn-label"> Branch: <?= esc($branchButtonLabel) ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end branch-filter-menu">
                        <li><a class="dropdown-item <?= $activeBranchMode === 'mine' ? 'active' : '' ?>" href="<?= base_url('suppliers?branch=mine') ?>"><i class="bi bi-person-badge me-2"></i>My Products</a></li>
                        <li><a class="dropdown-item <?= $activeBranchMode === 'all' ? 'active' : '' ?>" href="<?= base_url('suppliers?branch=all') ?>"><i class="bi bi-geo-alt me-2"></i>All Branches</a></li>
                        <?php foreach ($filteredBranchOptions as $branchOption): ?>
                        <li>
                            <a class="dropdown-item <?= strcasecmp($activeBranchFilter, (string) $branchOption) === 0 ? 'active' : '' ?>"
                                href="<?= base_url('suppliers') . '?branch=' . rawurlencode((string) $branchOption) ?>">
                                <i class="bi bi-shop me-2"></i><?= esc((string) $branchOption) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table transfer-table mb-0" id="transfersTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>From Branch</th>
                            <th>To Branch</th>
                            <th>Recipient</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($transfers)): ?>
                            <?php foreach ($transfers as $index => $transfer): ?>
                                <tr>
                                    <td><?= str_pad($index + 1, 3, '0', STR_PAD_LEFT) ?></td>
                                    <td><?= esc((string) ($transfer['part_name'] ?? 'N/A')) ?></td>
                                    <td><?= esc((string) ($transfer['sku'] ?? 'N/A')) ?></td>
                                    <td><span class="badge bg-info"><?= esc((string) ($transfer['from_branch'] ?? '')) ?></span></td>
                                    <td><span class="badge bg-success"><?= esc((string) ($transfer['to_branch'] ?? '')) ?></span></td>
                                    <td><?= esc((string) ($transfer['recipient_name'] ?? '—')) ?></td>
                                    <td><strong><?= esc((string) ($transfer['quantity'] ?? '0')) ?></strong></td>
                                    <td>
                                        <?php
                                            $status = $transfer['status'] ?? 'pending';
                                            $statusClass = $status === 'completed' ? 'bg-success' : ($status === 'cancelled' ? 'bg-danger' : 'bg-warning');
                                            $currentBranch = strtolower(trim((string) ($user_branch ?? '')));
                                            $toBranch = strtolower(trim((string) ($transfer['to_branch'] ?? '')));
                                            $canReceive = ($can_receive_transfer ?? false) && $status === 'pending' && $currentBranch !== '' && $currentBranch === $toBranch;
                                            $canCancel = ($can_dispatch_transfer ?? false) && $status === 'pending';
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= esc(ucfirst($status)) ?></span>
                                    </td>
                                    <td><?= !empty($transfer['created_at']) ? date('M d, Y', strtotime($transfer['created_at'])) : 'N/A' ?></td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-sm btn-action btn-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewTransferModal"
                                                data-id="<?= $transfer['transfer_id'] ?? '' ?>"
                                                data-product="<?= esc((string) ($transfer['part_name'] ?? '')) ?>"
                                                data-from="<?= esc((string) ($transfer['from_branch'] ?? '')) ?>"
                                                data-to="<?= esc((string) ($transfer['to_branch'] ?? '')) ?>"
                                                data-recipient="<?= esc((string) ($transfer['recipient_name'] ?? '')) ?>"
                                                data-quantity="<?= esc((string) ($transfer['quantity'] ?? '')) ?>"
                                                data-status="<?= esc((string) ($transfer['status'] ?? '')) ?>"
                                                data-remarks="<?= esc((string) ($transfer['remarks'] ?? '')) ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if ($canReceive): ?>
                                            <button class="btn btn-sm btn-action btn-success update-transfer-btn"
                                                    data-id="<?= $transfer['transfer_id'] ?? '' ?>"
                                                    data-status="completed"
                                                    title="Receive Transfer">
                                                <i class="bi bi-box-arrow-in-down"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($canCancel): ?>
                                            <button class="btn btn-sm btn-action btn-danger update-transfer-btn"
                                                    data-id="<?= $transfer['transfer_id'] ?? '' ?>"
                                                    data-status="cancelled"
                                                    title="Cancel Transfer">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="newTransferModal" tabindex="-1" aria-labelledby="newTransferLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newTransferLabel">Create Product Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newTransferForm">
                    <div class="mb-3">
                        <label for="fromBranch" class="form-label">Source Branch <span class="text-danger">*</span></label>
                        <select class="form-select" id="fromBranch" name="from_branch" required>
                            <option value="">Select source branch</option>
                            <?php if (!empty($branches)): ?>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?= esc((string) $branch) ?>"><?= esc((string) $branch) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="productId" class="form-label">Product <span class="text-danger">*</span></label>
                        <select class="form-select" id="productId" name="product_id" required>
                            <option value="">First select source branch</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="currentStock" class="form-label">Current Stock</label>
                        <input type="text" class="form-control" id="currentStock" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="toBranch" class="form-label">Destination Branch <span class="text-danger">*</span></label>
                        <select class="form-select" id="toBranch" name="to_branch" required>
                            <option value="">Select destination branch</option>
                            <?php if (!empty($branches)): ?>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?= esc((string) $branch) ?>"><?= esc((string) $branch) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="recipientUserId" class="form-label">Recipient <span class="text-danger">*</span></label>
                        <select class="form-select" id="recipientUserId" name="recipient_user_id" required>
                            <option value="">Select destination branch first</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                    </div>

                    <div id="transferAlert" class="alert alert-danger d-none" role="alert"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="createTransferBtn">Create Transfer</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewTransferModal" tabindex="-1" aria-labelledby="viewTransferLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewTransferLabel">Transfer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2"><strong>Product:</strong> <span id="viewProduct">-</span></p>
                <p class="mb-2"><strong>From Branch:</strong> <span id="viewFromBranch">-</span></p>
                <p class="mb-2"><strong>To Branch:</strong> <span id="viewToBranch">-</span></p>
                <p class="mb-2"><strong>Recipient:</strong> <span id="viewRecipient">-</span></p>
                <p class="mb-2"><strong>Quantity:</strong> <span id="viewQuantity">-</span></p>
                <p class="mb-2"><strong>Status:</strong> <span id="viewStatus">-</span></p>
                <p class="mb-0"><strong>Remarks:</strong> <span id="viewRemarks">-</span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="transferStatusConfirmModal" tabindex="-1" aria-labelledby="transferStatusConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transferStatusConfirmLabel">Confirm Transfer Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="transferStatusConfirmText" class="mb-0">Are you sure you want to update this transfer?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" class="btn btn-primary" id="confirmTransferStatusBtn">Yes, continue</button>
            </div>
        </div>
    </div>
</div>

<!-- Stock Received Confirmation Modal -->
<div class="modal fade" id="stockReceivedModal" tabindex="-1" aria-labelledby="stockReceivedLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockReceivedLabel">Confirm Stock Received</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="stockReceivedProduct" class="form-label">Product</label>
                    <input type="text" class="form-control" id="stockReceivedProduct" disabled>
                </div>
                <div class="mb-3">
                    <label for="stockReceivedExpected" class="form-label">Expected Quantity</label>
                    <input type="number" class="form-control" id="stockReceivedExpected" disabled>
                </div>
                <div class="mb-3">
                    <label for="stockReceivedActual" class="form-label">Actual Quantity Received <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="stockReceivedActual" placeholder="Enter actual quantity" min="0" required>
                    <small class="form-text text-muted">Enter the exact amount you received (may differ from expected quantity)</small>
                </div>
                <div id="receivedVariance" class="alert alert-info d-none" role="alert">
                    <strong>Variance:</strong> <span id="varianceText"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmStockReceivedBtn">Confirm Receipt</button>
            </div>
        </div>
    </div>
</div>

<!-- View Stock Request Modal -->
<div class="modal fade" id="viewStockRequestModal" tabindex="-1" aria-labelledby="viewStockRequestLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewStockRequestLabel">Stock Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2"><strong>Product:</strong> <span id="viewRequestProduct">-</span></p>
                <p class="mb-2"><strong>Requesting Branch:</strong> <span id="viewRequestBranch">-</span></p>
                <p class="mb-2"><strong>Quantity Requested:</strong> <span id="viewRequestQuantity">-</span></p>
                <p class="mb-2"><strong>Requested By:</strong> <span id="viewRequestRequestedBy">-</span></p>
                <p class="mb-0"><strong>Reason:</strong> <span id="viewRequestReason">-</span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Suppliers Filter Modal -->
<div class="modal fade" id="suppliersFilterModal" tabindex="-1" aria-labelledby="suppliersFilterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="suppliersFilterLabel">Filter Suppliers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="filter-section">
                    <h6 class="filter-section-title">Columns</h6>
                    <div class="filter-checkboxes">
                        <label class="filter-checkbox">
                            <input type="checkbox" class="suppliers-column-toggle" data-column="0" checked>
                            <span>#</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" class="suppliers-column-toggle" data-column="1" checked>
                            <span>Supplier Name</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" class="suppliers-column-toggle" data-column="2" checked>
                            <span>Brand</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" class="suppliers-column-toggle" data-column="3" checked>
                            <span>Importer</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" class="suppliers-column-toggle" data-column="4" checked>
                            <span>Actions</span>
                        </label>
                    </div>
                </div>

                <div class="filter-section mb-0">
                    <h6 class="filter-section-title">Importer Type</h6>
                    <div class="filter-radios">
                        <label class="filter-radio">
                            <input type="radio" name="suppliersImporterFilter" value="all" checked>
                            <span>All</span>
                        </label>
                        <label class="filter-radio">
                            <input type="radio" name="suppliersImporterFilter" value="local">
                            <span>Local</span>
                        </label>
                        <label class="filter-radio">
                            <input type="radio" name="suppliersImporterFilter" value="imported">
                            <span>Imported</span>
                        </label>
                    </div>
                </div>

                <div class="filter-section mb-0 mt-3">
                    <h6 class="filter-section-title">Filter By</h6>
                    <div class="d-flex flex-column gap-2">
                        <select class="form-select" id="suppliersFilterByField">
                            <option value="all" selected>All fields</option>
                            <option value="supplier">Supplier Name</option>
                            <option value="brand">Brand</option>
                            <option value="importer">Importer</option>
                        </select>
                        <input type="text" class="form-control" id="suppliersFilterByValue" placeholder="Enter keyword">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="applySuppliersFiltersBtn">Apply Filters</button>
                <button type="button" class="btn btn-danger" id="resetSuppliersFiltersBtn">Reset</button>
            </div>
        </div>
    </div>
</div>

<style>
    .suppliers-title {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a1a;
        letter-spacing: 0.5px;
    }

    .suppliers-card {
        border: none;
        border-radius: 24px;
        overflow: hidden;
        background: white;
    }

    .suppliers-card .card-header {
        background: white;
        border-bottom: 2px solid #f0f0f0;
        padding: 1.25rem 1.5rem;
    }

    .btn-new-supplier {
        background: linear-gradient(135deg, #FF8C42 0%, #FF7A2F 100%);
        color: white;
        padding: 8px 20px;
        border: none;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }

    .btn-new-supplier:hover {
        background: linear-gradient(135deg, #FF7A2F 0%, #FF6820 100%);
        color: white;
        transform: translateY(-1px);
    }

    .btn-new-transfer {
        background: linear-gradient(135deg, #6C63FF 0%, #5A54D4 100%);
        color: white;
        padding: 8px 20px;
        border: none;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }

    .btn-new-transfer:hover {
        background: linear-gradient(135deg, #5A54D4 0%, #4A47C5 100%);
        color: white;
        transform: translateY(-1px);
    }

    .transfer-card {
        border: none;
        border-radius: 24px;
        overflow: hidden;
        background: white;
    }

    .transfer-card .card-header {
        background: white;
        border-bottom: 2px solid #f0f0f0;
        padding: 1.25rem 1.5rem;
    }

    .transfer-table {
        font-size: 14px;
    }

    .transfer-table thead th {
        background: #f8f9fa;
        color: #2a2a2a;
        font-weight: 600;
        font-size: 14px;
        padding: 14px 16px;
        border: none;
        text-align: left;
    }

    .transfer-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        border-top: 1px solid #e9ecef;
        font-size: 14px;
    }

    .suppliers-table {
        font-size: 15px;
    }

    .suppliers-table thead th {
        background: #f8f9fa;
        color: #2a2a2a;
        font-weight: 600;
        font-size: 15px;
        padding: 14px 16px;
        border: none;
        text-align: left;
    }

    .suppliers-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        border-top: 1px solid #e9ecef;
        font-size: 15px;
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

    @media (max-width: 768px) {
        .suppliers-title {
            font-size: 24px;
        }

        .suppliers-table thead th,
        .suppliers-table tbody td {
            padding: 12px 10px;
            font-size: 13px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const suppliersTable = document.getElementById('suppliersTable');
    const tbody = suppliersTable ? suppliersTable.querySelector('tbody') : null;
    const searchInput = document.getElementById('suppliersSearchInput');
    const searchBtn = document.getElementById('suppliersSearchBtn');

    const filterModal = document.getElementById('suppliersFilterModal');
    const applyBtn = document.getElementById('applySuppliersFiltersBtn');
    const resetBtn = document.getElementById('resetSuppliersFiltersBtn');
    const columnToggles = document.querySelectorAll('.suppliers-column-toggle');
    const importerRadios = document.querySelectorAll('input[name="suppliersImporterFilter"]');
    const suppliersFilterByField = document.getElementById('suppliersFilterByField');
    const suppliersFilterByValue = document.getElementById('suppliersFilterByValue');

    if (!suppliersTable || !tbody || !searchInput || !searchBtn) {
        return;
    }

    let selectedImporterFilter = 'all';
    let selectedSuppliersFilterByField = 'all';
    let selectedSuppliersFilterByValue = '';

    const getSupplierRows = function () {
        return Array.from(tbody.querySelectorAll('tr')).filter((row) => {
            return row.cells.length >= 5 && !row.classList.contains('no-results-row');
        });
    };

    const ensureNoResultsRow = function (searchValue) {
        let noResultsRow = tbody.querySelector('.no-results-row');
        if (!noResultsRow) {
            noResultsRow = document.createElement('tr');
            noResultsRow.className = 'no-results-row';
            noResultsRow.innerHTML = `
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="bi bi-search fs-1 d-block mb-2"></i>
                    No suppliers found matching "${searchValue}"
                </td>
            `;
            tbody.appendChild(noResultsRow);
        } else {
            const cell = noResultsRow.querySelector('td');
            if (cell) {
                cell.innerHTML = `
                    <i class="bi bi-search fs-1 d-block mb-2"></i>
                    No suppliers found matching "${searchValue}"
                `;
            }
        }
    };

    const removeNoResultsRow = function () {
        const row = tbody.querySelector('.no-results-row');
        if (row) {
            row.remove();
        }
    };

    const applySuppliersColumnVisibility = function () {
        if (!suppliersTable) {
            return;
        }

        columnToggles.forEach((toggle) => {
            const idx = parseInt(toggle.getAttribute('data-column') || '-1', 10);
            if (idx < 0) {
                return;
            }

            suppliersTable.querySelectorAll('tr').forEach((row) => {
                const cells = row.querySelectorAll('th, td');
                if (cells[idx]) {
                    cells[idx].style.display = toggle.checked ? '' : 'none';
                }
            });
        });
    };

    const applySuppliersTableFilters = function () {
        const searchTerm = (searchInput.value || '').toLowerCase().trim();
        const rows = getSupplierRows();
        let visibleCount = 0;

        rows.forEach((row) => {
            const supplierName = (row.cells[1]?.textContent || '').toLowerCase();
            const brand = (row.cells[2]?.textContent || '').toLowerCase();
            const importer = (row.cells[3]?.textContent || '').toLowerCase();

            const filterByValue = selectedSuppliersFilterByValue.toLowerCase();
            const byFieldText = (() => {
                if (selectedSuppliersFilterByField === 'supplier') return supplierName;
                if (selectedSuppliersFilterByField === 'brand') return brand;
                if (selectedSuppliersFilterByField === 'importer') return importer;
                return `${supplierName} ${brand} ${importer}`;
            })();

            const matchesSearch =
                searchTerm === ''
                || supplierName.includes(searchTerm)
                || brand.includes(searchTerm)
                || importer.includes(searchTerm);

            const matchesFilterBy = filterByValue === '' || byFieldText.includes(filterByValue);

            let matchesImporter = true;
            if (selectedImporterFilter === 'local') {
                matchesImporter = importer.includes('local');
            } else if (selectedImporterFilter === 'imported') {
                matchesImporter = importer.includes('imported');
            }

            const visible = matchesSearch && matchesImporter && matchesFilterBy;
            row.style.display = visible ? '' : 'none';
            if (visible) {
                visibleCount++;
            }
        });

        if (visibleCount === 0) {
            ensureNoResultsRow(searchInput.value);
        } else {
            removeNoResultsRow();
        }

        applySuppliersColumnVisibility();
    };

    const closeSuppliersFilterModal = function () {
        if (!filterModal) {
            return;
        }
        const instance = bootstrap.Modal.getOrCreateInstance(filterModal);
        instance.hide();
    };

    searchInput.addEventListener('input', applySuppliersTableFilters);
    searchBtn.addEventListener('click', applySuppliersTableFilters);
    searchInput.addEventListener('keypress', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            applySuppliersTableFilters();
        }
    });

    if (filterModal && applyBtn && resetBtn) {
        filterModal.addEventListener('show.bs.modal', function () {
            importerRadios.forEach((radio) => {
                radio.checked = radio.value === selectedImporterFilter;
            });
            if (suppliersFilterByField) {
                suppliersFilterByField.value = selectedSuppliersFilterByField;
            }
            if (suppliersFilterByValue) {
                suppliersFilterByValue.value = selectedSuppliersFilterByValue;
            }
        });

        columnToggles.forEach((toggle) => {
            toggle.addEventListener('change', applySuppliersColumnVisibility);
        });

        applyBtn.addEventListener('click', function () {
            const checkedRadio = document.querySelector('input[name="suppliersImporterFilter"]:checked');
            selectedImporterFilter = checkedRadio ? checkedRadio.value : 'all';
            selectedSuppliersFilterByField = suppliersFilterByField ? suppliersFilterByField.value : 'all';
            selectedSuppliersFilterByValue = suppliersFilterByValue ? suppliersFilterByValue.value.trim() : '';
            applySuppliersTableFilters();
            closeSuppliersFilterModal();
        });

        resetBtn.addEventListener('click', function () {
            selectedImporterFilter = 'all';
            importerRadios.forEach((radio) => {
                radio.checked = radio.value === 'all';
            });
            selectedSuppliersFilterByField = 'all';
            selectedSuppliersFilterByValue = '';
            if (suppliersFilterByField) {
                suppliersFilterByField.value = 'all';
            }
            if (suppliersFilterByValue) {
                suppliersFilterByValue.value = '';
            }
            columnToggles.forEach((toggle) => {
                toggle.checked = true;
            });
            applySuppliersTableFilters();
            closeSuppliersFilterModal();
        });
    }

    applySuppliersTableFilters();
});

document.addEventListener('DOMContentLoaded', function () {
    const fromBranchSelect = document.getElementById('fromBranch');
    const toBranchSelect = document.getElementById('toBranch');
    const productSelect = document.getElementById('productId');
    const recipientSelect = document.getElementById('recipientUserId');
    const currentStockInput = document.getElementById('currentStock');
    const newTransferForm = document.getElementById('newTransferForm');
    const createTransferBtn = document.getElementById('createTransferBtn');
    const transferAlert = document.getElementById('transferAlert');
    const transferStatusConfirmModalEl = document.getElementById('transferStatusConfirmModal');
    const transferStatusConfirmText = document.getElementById('transferStatusConfirmText');
    const confirmTransferStatusBtn = document.getElementById('confirmTransferStatusBtn');
    const transferStatusConfirmModal = transferStatusConfirmModalEl && window.bootstrap
        ? new bootstrap.Modal(transferStatusConfirmModalEl)
        : null;
    
    const stockReceivedModalEl = document.getElementById('stockReceivedModal');
    const stockReceivedModal = stockReceivedModalEl && window.bootstrap
        ? new bootstrap.Modal(stockReceivedModalEl)
        : null;
    const stockReceivedProduct = document.getElementById('stockReceivedProduct');
    const stockReceivedExpected = document.getElementById('stockReceivedExpected');
    const stockReceivedActual = document.getElementById('stockReceivedActual');
    const receivedVarianceDiv = document.getElementById('receivedVariance');
    const varianceText = document.getElementById('varianceText');
    const confirmStockReceivedBtn = document.getElementById('confirmStockReceivedBtn');
    
    let pendingTransferStatusAction = null;
    let pendingStockReceiveAction = null;

    if (!fromBranchSelect || !productSelect || !newTransferForm || !createTransferBtn || !transferAlert) {
        return;
    }

    fromBranchSelect.addEventListener('change', function () {
        const branch = this.value;
        productSelect.innerHTML = '<option value="">Select product</option>';
        currentStockInput.value = '';

        if (!branch) {
            return;
        }

        fetch(`<?= base_url('suppliers/get-products-by-branch') ?>?branch=${encodeURIComponent(branch)}`)
            .then((response) => response.json())
            .then((data) => {
                if (!data.success || !Array.isArray(data.data)) {
                    return;
                }

                data.data.forEach((product) => {
                    const option = document.createElement('option');
                    option.value = product.product_id;
                    option.textContent = `${product.part_name} (SKU: ${product.sku || 'N/A'})`;
                    option.dataset.stock = product.current_stock;
                    productSelect.appendChild(option);
                });
            })
            .catch(() => {
                transferAlert.textContent = 'Unable to load products for the selected branch.';
                transferAlert.classList.remove('d-none');
            });
    });

    productSelect.addEventListener('change', function () {
        if (this.selectedOptions[0]) {
            currentStockInput.value = this.selectedOptions[0].dataset.stock || '0';
        } else {
            currentStockInput.value = '';
        }
    });

    toBranchSelect?.addEventListener('change', function () {
        const branch = this.value;
        recipientSelect.innerHTML = '<option value="">Select recipient</option>';

        if (!branch) {
            recipientSelect.innerHTML = '<option value="">Select destination branch first</option>';
            return;
        }

        fetch(`<?= base_url('suppliers/get-recipients-by-branch') ?>?branch=${encodeURIComponent(branch)}`)
            .then((response) => response.json())
            .then((data) => {
                if (!data.success || !Array.isArray(data.data) || data.data.length === 0) {
                    recipientSelect.innerHTML = '<option value="">No admin recipient found for this branch</option>';
                    return;
                }

                data.data.forEach((user) => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = `${user.name} (${user.username})`;
                    recipientSelect.appendChild(option);
                });
            })
            .catch(() => {
                recipientSelect.innerHTML = '<option value="">Failed to load recipients</option>';
            });
    });

    createTransferBtn.addEventListener('click', async function () {
        transferAlert.classList.add('d-none');

        const formData = new FormData(newTransferForm);

        try {
            const response = await fetch(`<?= base_url('suppliers/store-transfer') ?>`, {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();

            if (!data.success) {
                transferAlert.textContent = data.message || 'Failed to create transfer.';
                transferAlert.classList.remove('d-none');
                return;
            }

            window.location.reload();
        } catch (error) {
            transferAlert.textContent = error.message || 'An unexpected error occurred.';
            transferAlert.classList.remove('d-none');
        }
    });

    document.querySelectorAll('[data-bs-target="#viewTransferModal"]').forEach((btn) => {
        btn.addEventListener('click', function () {
            document.getElementById('viewProduct').textContent = this.dataset.product || '-';
            document.getElementById('viewFromBranch').textContent = this.dataset.from || '-';
            document.getElementById('viewToBranch').textContent = this.dataset.to || '-';
            document.getElementById('viewRecipient').textContent = this.dataset.recipient || '-';
            document.getElementById('viewQuantity').textContent = this.dataset.quantity || '-';
            document.getElementById('viewRemarks').textContent = this.dataset.remarks || '-';

            const status = this.dataset.status || 'pending';
            const badgeClass = status === 'completed' ? 'bg-success' : (status === 'cancelled' ? 'bg-danger' : 'bg-warning');
            document.getElementById('viewStatus').innerHTML = `<span class="badge ${badgeClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
        });
    });

    document.querySelectorAll('[data-bs-target="#viewStockRequestModal"]').forEach((btn) => {
        btn.addEventListener('click', function () {
            document.getElementById('viewRequestProduct').textContent = this.dataset.product || '-';
            document.getElementById('viewRequestBranch').textContent = this.dataset.branch || '-';
            document.getElementById('viewRequestQuantity').textContent = this.dataset.quantity || '-';
            document.getElementById('viewRequestRequestedBy').textContent = this.dataset.requestedBy || '-';
            document.getElementById('viewRequestReason').textContent = this.dataset.reason || 'No reason provided';
        });
    });

    const showPageAlert = (message, type = 'info') => {
        const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;

        const container = document.querySelector('.container-fluid');
        if (!container) {
            return;
        }

        const alertDiv = document.createElement('div');
        alertDiv.innerHTML = alertHtml;
        container.insertBefore(alertDiv.firstElementChild, container.firstChild);

        setTimeout(() => {
            container.querySelector('.alert')?.remove();
        }, 5000);
    };

    const updateTransferStatus = async (transferId, newStatus, receivedQty = null) => {
        const formData = new FormData();
        formData.append('transfer_id', transferId);
        formData.append('status', newStatus);
        if (receivedQty !== null) {
            formData.append('received_qty', receivedQty);
        }

        try {
            const response = await fetch(`<?= base_url('suppliers/update-transfer-status') ?>`, {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();
            if (!data.success) {
                showPageAlert(data.message || 'Failed to update transfer status.', 'danger');
                return;
            }

            showPageAlert(`Transfer ${newStatus} successfully!`, 'success');
            setTimeout(() => window.location.reload(), 1200);
        } catch (error) {
            showPageAlert(error.message || 'An unexpected error occurred.', 'danger');
        }
    };

    document.querySelectorAll('.update-transfer-btn').forEach((btn) => {
        btn.addEventListener('click', function () {
            const transferId = this.dataset.id;
            const newStatus = this.dataset.status;
            const actionText = newStatus === 'cancelled' ? 'cancel' : newStatus;

            pendingTransferStatusAction = { transferId, newStatus };

            // For "completed" status (receive), show stock received modal
            if (newStatus === 'completed') {
                const row = this.closest('tr');
                const productName = row.querySelector('td:nth-child(2)')?.textContent || 'Unknown Product';
                const quantity = row.querySelector('td:nth-child(7)')?.textContent || '0';
                
                if (stockReceivedProduct) stockReceivedProduct.value = productName;
                if (stockReceivedExpected) stockReceivedExpected.value = quantity;
                if (stockReceivedActual) {
                    stockReceivedActual.value = quantity;
                    stockReceivedActual.focus();
                }
                if (receivedVarianceDiv) receivedVarianceDiv.classList.add('d-none');
                
                pendingStockReceiveAction = { transferId };
                
                if (stockReceivedModal) {
                    stockReceivedModal.show();
                    return;
                }
            }

            if (transferStatusConfirmText) {
                transferStatusConfirmText.textContent = `Are you sure you want to ${actionText} this transfer?`;
            }

            if (transferStatusConfirmModal) {
                transferStatusConfirmModal.show();
                return;
            }

            updateTransferStatus(transferId, newStatus);
        });
    });

    confirmTransferStatusBtn?.addEventListener('click', function () {
        if (!pendingTransferStatusAction) {
            return;
        }

        const { transferId, newStatus } = pendingTransferStatusAction;
        pendingTransferStatusAction = null;
        transferStatusConfirmModal?.hide();
        updateTransferStatus(transferId, newStatus);
    });

    // Stock Received Confirmation
    if (stockReceivedActual) {
        stockReceivedActual.addEventListener('input', function () {
            const expected = parseInt(stockReceivedExpected?.value || 0);
            const actual = parseInt(this.value || 0);
            
            if (expected && actual) {
                const variance = actual - expected;
                if (variance !== 0) {
                    varianceText.textContent = variance > 0 
                        ? `+${variance} units (Overage)` 
                        : `${variance} units (Shortage)`;
                    receivedVarianceDiv.classList.remove('d-none');
                } else {
                    receivedVarianceDiv.classList.add('d-none');
                }
            }
        });
    }

    confirmStockReceivedBtn?.addEventListener('click', function () {
        if (!pendingStockReceiveAction) {
            return;
        }

        const actualQty = parseInt(stockReceivedActual?.value || 0);
        if (isNaN(actualQty) || actualQty < 0) {
            showPageAlert('Please enter a valid quantity.', 'danger');
            return;
        }

        const { transferId } = pendingStockReceiveAction;
        pendingStockReceiveAction = null;
        stockReceivedModal?.hide();
        updateTransferStatus(transferId, 'completed', actualQty);
    });
});

</script>

<?= view('suppliers/new_supplier') ?>
<?= view('suppliers/view_supplier') ?>
<?= view('suppliers/edit_supplier') ?>
<?= view('suppliers/delete_supplier') ?>

<?php $this->endSection(); ?>
