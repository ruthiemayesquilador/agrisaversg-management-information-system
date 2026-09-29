<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<?php
if (!isset($pending_stock_requests) || !is_array($pending_stock_requests)) {
    $pending_stock_requests = [];
}
if (!isset($transfers) || !is_array($transfers)) {
    $transfers = [];
}
$user_branch = isset($user_branch) ? (string) $user_branch : (string) (session()->get('branch') ?? '');
$isMainBranch = isset($isMainBranch) ? (bool) $isMainBranch : false;
?>


<div class="container-fluid px-4">
    <div class="text-center mb-4">
        <h2 class="transfer-title">SUPPLIER TRANSFERS</h2>
        <p class="text-muted mb-0">Incoming branch transfers waiting for receiving confirmation.</p>
    </div>

    <!-- Pending Stock Requests Card (for Main Branch Admin) -->
    <?php if (($isMainBranch ?? false) && !empty($pending_stock_requests)): ?>
    <div class="card stock-requests-card shadow-sm mb-4" style="border-left: 4px solid #ffc107;">
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

    <div class="card transfer-card shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <h5 class="mb-0">Transfers To <?= esc($user_branch ?: 'Your Branch') ?></h5>
            <div class="input-group manage-search-group">
                <input type="text" class="form-control manage-search-input" id="transferSearchInput" placeholder="Search by product, SKU, or branch...">
                <button class="btn manage-search-btn" type="button" id="transferSearchBtn" aria-label="Search" title="Search"><i class="bi bi-search"></i></button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table transfer-table mb-0 js-sortable-table" id="adminTransfersTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>From Branch</th>
                            <th>To Branch</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($transfers)): ?>
                            <?php foreach ($transfers as $index => $transfer): ?>
                                <?php
                                    $status = (string) ($transfer['status'] ?? 'pending');
                                    $statusClass = match ($status) {
                                        'completed' => 'bg-success',
                                        'cancelled' => 'bg-danger',
                                        default => 'bg-warning',
                                    };

                                    $currentBranch = strtolower(trim((string) ($user_branch ?? '')));
                                    $toBranch = strtolower(trim((string) ($transfer['to_branch'] ?? '')));
                                    $canReceive = ($status === 'pending') && $currentBranch !== '' && $currentBranch === $toBranch;
                                ?>
                                <tr>
                                    <td><?= str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT) ?></td>
                                    <td><?= esc((string) ($transfer['part_name'] ?? 'N/A')) ?></td>
                                    <td><?= esc((string) ($transfer['sku'] ?? 'N/A')) ?></td>
                                    <td><span class="badge bg-info"><?= esc((string) ($transfer['from_branch'] ?? '')) ?></span></td>
                                    <td><span class="badge bg-success"><?= esc((string) ($transfer['to_branch'] ?? '')) ?></span></td>
                                    <td><strong><?= esc((string) ($transfer['quantity'] ?? '0')) ?></strong></td>
                                    <td><span class="badge <?= $statusClass ?>"><?= esc(ucfirst($status)) ?></span></td>
                                    <td><?= !empty($transfer['created_at']) ? date('M d, Y', strtotime($transfer['created_at'])) : 'N/A' ?></td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-sm btn-action btn-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewTransferModal"
                                                data-product="<?= esc((string) ($transfer['part_name'] ?? '')) ?>"
                                                data-sku="<?= esc((string) ($transfer['sku'] ?? '')) ?>"
                                                data-from="<?= esc((string) ($transfer['from_branch'] ?? '')) ?>"
                                                data-to="<?= esc((string) ($transfer['to_branch'] ?? '')) ?>"
                                                data-quantity="<?= esc((string) ($transfer['quantity'] ?? '')) ?>"
                                                data-status="<?= esc((string) $status) ?>"
                                                data-remarks="<?= esc((string) ($transfer['remarks'] ?? '')) ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <?php if ($canReceive): ?>
                                            <button class="btn btn-sm btn-action btn-success receive-transfer-btn"
                                                    data-id="<?= esc((string) ($transfer['transfer_id'] ?? '')) ?>"
                                                    title="Mark as Received">
                                                <i class="bi bi-box-arrow-in-down"></i> Receive
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="empty-row">
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No incoming transfers found for your branch.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
                <p class="mb-2"><strong>SKU:</strong> <span id="viewSku">-</span></p>
                <p class="mb-2"><strong>From Branch:</strong> <span id="viewFromBranch">-</span></p>
                <p class="mb-2"><strong>To Branch:</strong> <span id="viewToBranch">-</span></p>
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

<div class="modal fade" id="receiveConfirmModal" tabindex="-1" aria-labelledby="receiveConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiveConfirmLabel">Confirm Receive</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Mark this transfer as received?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmReceiveBtn">Received</button>
            </div>
        </div>
    </div>
</div>

<!-- View Stock Request Modal -->
<div class="modal fade" id="viewStockRequestModal" tabindex="-1" aria-labelledby="viewStockRequestLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewStockRequestLabel">Stock Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2"><strong>Product:</strong> <span id="viewRequestProduct">-</span></p>
                <p class="mb-2"><strong>Requesting Branch:</strong> <span id="viewRequestBranch">-</span></p>
                <p class="mb-2"><strong>Quantity:</strong> <span id="viewRequestQuantity">-</span></p>
                <p class="mb-2"><strong>Requested By:</strong> <span id="viewRequestedBy">-</span></p>
                <p class="mb-0"><strong>Reason:</strong> <span id="viewRequestReason">-</span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .transfer-title {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a1a;
        letter-spacing: 0.5px;
    }

    .transfer-card {
        border: none;
        border-radius: 24px;
        overflow: hidden;
        background: #fff;
    }

    .transfer-card .card-header {
        background: #fff;
        border-bottom: 2px solid #f0f0f0;
        padding: 1.25rem 1.5rem;
    }

    .transfer-table thead th {
        background: #f8f9fa;
        color: #2a2a2a;
        font-weight: 600;
        padding: 12px 14px;
        border: none;
    }

    .transfer-table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        border-top: 1px solid #eceff1;
    }

    .btn-action {
        border-radius: 6px;
        padding: 6px 10px;
        border: none;
        margin-right: 4px;
    }

    .manage-search-group {
        min-width: 320px;
    }

    .manage-search-group {
        min-width: 320px;
    }

    .stock-requests-card .table th {
        font-weight: 600;
        font-size: 0.875rem;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }

    .stock-requests-card .table td {
        vertical-align: middle;
        font-size: 0.875rem;
    }

    .requests-table .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.5rem;
    }

    @media (max-width: 768px) {
        .transfer-title {
            font-size: 24px;
        }

        .manage-search-group {
            min-width: 100%;
        }

        .receive-transfer-btn {
            margin-top: 6px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('adminTransfersTable');
    const searchInput = document.getElementById('transferSearchInput');
    const searchBtn = document.getElementById('transferSearchBtn');
    const confirmReceiveBtn = document.getElementById('confirmReceiveBtn');
    const receiveConfirmModalEl = document.getElementById('receiveConfirmModal');
    const receiveConfirmModal = receiveConfirmModalEl ? bootstrap.Modal.getOrCreateInstance(receiveConfirmModalEl) : null;
    let pendingReceiveTransferId = null;

    if (!table || !searchInput || !searchBtn) {
        return;
    }

    const tbody = table.querySelector('tbody');
    const initialEmpty = tbody.querySelector('.empty-row');

    const rows = () => Array.from(tbody.querySelectorAll('tr')).filter(function (row) {
        return !row.classList.contains('empty-row') && row.children.length > 1;
    });

    function ensureNoResultsRow(term) {
        let row = tbody.querySelector('.empty-row');
        if (!row) {
            row = document.createElement('tr');
            row.className = 'empty-row';
            row.innerHTML = '<td colspan="9" class="text-center text-muted py-4"><i class="bi bi-search fs-1 d-block mb-2"></i><span class="msg"></span></td>';
            tbody.appendChild(row);
        }
        const msg = row.querySelector('.msg');
        if (msg) {
            msg.textContent = 'No transfers found matching "' + term + '".';
        }
    }

    function removeNoResultsRow() {
        const row = tbody.querySelector('.empty-row');
        if (row && row !== initialEmpty) {
            row.remove();
        }
    }

    function applySearch() {
        const term = (searchInput.value || '').toLowerCase().trim();
        let visible = 0;

        rows().forEach(function (row) {
            const text = row.textContent.toLowerCase();
            const show = term === '' || text.includes(term);
            row.style.display = show ? '' : 'none';
            if (show) {
                visible++;
            }
        });

        if (visible === 0) {
            ensureNoResultsRow(searchInput.value.trim());
        } else {
            removeNoResultsRow();
        }
    }

    searchInput.addEventListener('input', applySearch);
    searchBtn.addEventListener('click', applySearch);
    searchInput.addEventListener('keypress', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            applySearch();
        }
    });

    document.querySelectorAll('[data-bs-target="#viewTransferModal"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('viewProduct').textContent = this.dataset.product || '-';
            document.getElementById('viewSku').textContent = this.dataset.sku || '-';
            document.getElementById('viewFromBranch').textContent = this.dataset.from || '-';
            document.getElementById('viewToBranch').textContent = this.dataset.to || '-';
            document.getElementById('viewQuantity').textContent = this.dataset.quantity || '-';
            document.getElementById('viewRemarks').textContent = this.dataset.remarks || '-';

            const status = this.dataset.status || 'pending';
            const statusBadge = '<span class="badge ' + (status === 'completed' ? 'bg-success' : (status === 'cancelled' ? 'bg-danger' : 'bg-warning')) + '">' + status.charAt(0).toUpperCase() + status.slice(1) + '</span>';
            document.getElementById('viewStatus').innerHTML = statusBadge;
        });
    });

    document.querySelectorAll('.receive-transfer-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const transferId = this.dataset.id;
            if (!transferId || !receiveConfirmModal) {
                return;
            }

            pendingReceiveTransferId = transferId;
            receiveConfirmModal.show();
        });
    });

    if (confirmReceiveBtn) {
        confirmReceiveBtn.addEventListener('click', async function () {
            if (!pendingReceiveTransferId) {
                return;
            }

            const formData = new FormData();
            formData.append('transfer_id', pendingReceiveTransferId);
            formData.append('status', 'completed');

            confirmReceiveBtn.disabled = true;
            confirmReceiveBtn.textContent = 'Processing...';

            try {
                const response = await fetch('<?= base_url('suppliers/update-transfer-status') ?>', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    receiveConfirmModal?.hide();
                    showNotice('Transfer marked as received.', 'success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 800);
                    return;
                }

                showNotice(data.message || 'Failed to mark transfer as received.', 'danger');
            } catch (error) {
                showNotice('Error: ' + error.message, 'danger');
            } finally {
                confirmReceiveBtn.disabled = false;
                confirmReceiveBtn.textContent = 'Received';
                pendingReceiveTransferId = null;
            }
        });
    }

    document.querySelectorAll('[data-bs-target="#viewStockRequestModal"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('viewRequestProduct').textContent = this.dataset.product || '-';
            document.getElementById('viewRequestBranch').textContent = this.dataset.branch || '-';
            document.getElementById('viewRequestQuantity').textContent = this.dataset.quantity || '-';
            document.getElementById('viewRequestedBy').textContent = this.dataset.requestedBy || '-';
            document.getElementById('viewRequestReason').textContent = this.dataset.reason || '-';
        });
    });

    function showNotice(message, type) {
        const container = document.querySelector('.container-fluid');
        if (!container) {
            return;
        }

        const alert = document.createElement('div');
        alert.className = 'alert alert-' + (type || 'info') + ' alert-dismissible fade show';
        alert.role = 'alert';
        alert.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        container.insertBefore(alert, container.firstChild);

        setTimeout(function () {
            alert.remove();
        }, 3500);
    }
});
</script>

<?php $this->endSection(); ?>
