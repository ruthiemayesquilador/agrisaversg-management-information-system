<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="text-center mb-4">
        <h2 class="transfer-title">PRODUCT TRANSFERS</h2>
    </div>

    <!-- Product Transfers Card -->
    <div class="card transfer-card shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <h5 class="mb-0">Transfer Products Between Branches</h5>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group manage-search-group">
                    <input type="text" class="form-control manage-search-input" id="transferSearchInput" placeholder="Search by product name, SKU...">
                    <button class="btn manage-search-btn" type="button" id="transferSearchBtn" aria-label="Search" title="Search"><i class="bi bi-search"></i></button>
                </div>
                <?php if (!empty($can_dispatch_transfer)): ?>
                    <button class="btn btn-new-transfer" data-bs-toggle="modal" data-bs-target="#newTransferModal">
                        <i class="bi bi-arrow-left-right"></i> New Transfer
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table transfer-table mb-0 js-sortable-table" id="transfersTable">
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
                                    <td><?= esc($transfer['part_name'] ?? 'N/A') ?></td>
                                    <td><?= esc($transfer['sku'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge bg-info" style="font-size:11px;">
                                            <?= esc($transfer['from_branch'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success" style="font-size:11px;">
                                            <?= esc($transfer['to_branch'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td><?= esc($transfer['recipient_name'] ?? '—') ?></td>
                                    <td><strong><?= esc($transfer['quantity'] ?? 0) ?></strong></td>
                                    <td>
                                        <?php 
                                            $status = $transfer['status'] ?? 'pending';
                                            $statusClass = match($status) {
                                                'completed' => 'bg-success',
                                                'cancelled' => 'bg-danger',
                                                default => 'bg-warning'
                                            };
                                            $statusText = ucfirst($status);
                                            $currentBranch = strtolower(trim((string) ($user_branch ?? '')));
                                            $toBranch = strtolower(trim((string) ($transfer['to_branch'] ?? '')));
                                            $canReceive = ($can_receive_transfer ?? false) && $status === 'pending' && $currentBranch !== '' && $currentBranch === $toBranch;
                                            $canCancel = ($can_dispatch_transfer ?? false) && $status === 'pending';
                                        ?>
                                        <span class="badge <?= $statusClass ?>" style="font-size:11px;">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($transfer['created_at'] ?? '')) ?></td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-sm btn-action btn-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewTransferModal"
                                                data-id="<?= $transfer['transfer_id'] ?? '' ?>"
                                                data-product="<?= esc($transfer['part_name'] ?? '') ?>"
                                                data-from="<?= esc($transfer['from_branch'] ?? '') ?>"
                                                data-to="<?= esc($transfer['to_branch'] ?? '') ?>"
                                                data-recipient="<?= esc($transfer['recipient_name'] ?? '') ?>"
                                                data-quantity="<?= esc($transfer['quantity'] ?? '') ?>"
                                                data-status="<?= esc($transfer['status'] ?? '') ?>"
                                                data-remarks="<?= esc($transfer['remarks'] ?? '') ?>">
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
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No transfers found. Click "New Transfer" to create one.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- New Transfer Modal -->
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
                                    <option value="<?= esc($branch) ?>"><?= esc($branch) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback" id="fromBranchError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="productId" class="form-label">Product <span class="text-danger">*</span></label>
                        <select class="form-select" id="productId" name="product_id" required>
                            <option value="">First select source branch</option>
                        </select>
                        <div class="invalid-feedback" id="productError"></div>
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
                                    <option value="<?= esc($branch) ?>"><?= esc($branch) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback" id="toBranchError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="recipientUserId" class="form-label">Recipient <span class="text-danger">*</span></label>
                        <select class="form-select" id="recipientUserId" name="recipient_user_id" required>
                            <option value="">Select destination branch first</option>
                        </select>
                        <div class="invalid-feedback" id="recipientError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required placeholder="Enter quantity to transfer">
                        <div class="invalid-feedback" id="quantityError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Add any notes about this transfer..."></textarea>
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

<!-- View Transfer Modal -->
<div class="modal fade" id="viewTransferModal" tabindex="-1" aria-labelledby="viewTransferLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewTransferLabel">Transfer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Product</label>
                        <p id="viewProduct" class="text-muted">—</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status</label>
                        <p id="viewStatus">—</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">From Branch</label>
                        <p id="viewFromBranch" class="text-muted">—</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">To Branch</label>
                        <p id="viewToBranch" class="text-muted">—</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Recipient</label>
                        <p id="viewRecipient" class="text-muted">—</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Quantity</label>
                        <p id="viewQuantity" class="text-muted">—</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Remarks</label>
                        <p id="viewRemarks" class="text-muted">—</p>
                    </div>
                </div>
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
        background: white;
    }

    .transfer-card .card-header {
        background: white;
        border-bottom: 2px solid #f0f0f0;
        padding: 1.25rem 1.5rem;
    }

    .btn-new-transfer {
        background: linear-gradient(135deg, #FF8C42 0%, #FF7A2F 100%);
        color: white;
        padding: 8px 20px;
        border: none;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-new-transfer:hover {
        background: linear-gradient(135deg, #FF7A2F 0%, #FF6B1A 100%);
        color: white;
        transform: translateY(-2px);
    }

    .transfer-table {
        font-size: 13px;
    }

    .transfer-table thead {
        background: #f8f9fa;
    }

    .transfer-table th {
        font-weight: 600;
        color: #333;
        padding: 12px 15px;
        border-bottom: 1px solid #e0e0e0;
    }

    .transfer-table td {
        padding: 12px 15px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    .transfer-table tbody tr:hover {
        background-color: #f9f9f9;
    }

    .btn-action {
        padding: 4px 8px;
        font-size: 12px;
    }

    .manage-search-group {
        min-width: 300px;
    }

    .manage-search-input {
        font-size: 13px;
        border-radius: 20px 0 0 20px;
        border: 1px solid #ddd;
        padding: 8px 12px;
    }

    .manage-search-btn {
        border-radius: 0 20px 20px 0;
        border: 1px solid #ddd;
        padding: 8px 12px;
        color: #666;
        background: white;
    }

    .manage-search-btn:hover {
        background: #f0f0f0;
        color: #333;
    }

    .modal-content {
        border-radius: 12px;
        border: none;
    }

    .modal-header {
        background: #f8f9fa;
        border-bottom: 2px solid #e0e0e0;
    }

    .form-label {
        color: #333;
        margin-bottom: 6px;
    }

    .form-control,
    .form-select {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #FF8C42;
        box-shadow: 0 0 0 0.2rem rgba(255, 140, 66, 0.15);
    }

    .badge {
        padding: 4px 8px;
        font-size: 11px;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .manage-search-group {
            min-width: 100%;
        }

        .btn-new-transfer {
            width: 100%;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fromBranchSelect = document.getElementById('fromBranch');
        const productSelect = document.getElementById('productId');
        const toBranchSelect = document.getElementById('toBranch');
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
        let pendingTransferStatusAction = null;

        // Load products when source branch changes
        fromBranchSelect.addEventListener('change', function() {
            const branch = this.value;
            productSelect.innerHTML = '<option value="">Select product</option>';
            currentStockInput.value = '';

            if (branch) {
                fetch(`<?= base_url('suppliers/get-products-by-branch') ?>?branch=${encodeURIComponent(branch)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data) {
                            data.data.forEach(product => {
                                const option = document.createElement('option');
                                option.value = product.product_id;
                                option.textContent = `${product.part_name} (SKU: ${product.sku || 'N/A'})`;
                                option.dataset.stock = product.current_stock;
                                productSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error loading products:', error));
            }
        });

        // Update stock display when product changes
        productSelect.addEventListener('change', function() {
            if (this.selectedOptions[0]) {
                currentStockInput.value = this.selectedOptions[0].dataset.stock || '0';
            } else {
                currentStockInput.value = '';
            }
        });

        toBranchSelect.addEventListener('change', function() {
            const branch = this.value;
            recipientSelect.innerHTML = '<option value="">Select recipient</option>';

            if (!branch) {
                recipientSelect.innerHTML = '<option value="">Select destination branch first</option>';
                return;
            }

            fetch(`<?= base_url('suppliers/get-recipients-by-branch') ?>?branch=${encodeURIComponent(branch)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                        data.data.forEach(user => {
                            const option = document.createElement('option');
                            option.value = user.id;
                            option.textContent = `${user.name} (${user.username})`;
                            recipientSelect.appendChild(option);
                        });
                    } else {
                        recipientSelect.innerHTML = '<option value="">No admin recipient found for this branch</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading recipients:', error);
                    recipientSelect.innerHTML = '<option value="">Failed to load recipients</option>';
                });
        });

        // Create transfer
        createTransferBtn.addEventListener('click', async function() {
            transferAlert.classList.add('d-none');

            const formData = new FormData(newTransferForm);
            
            try {
                const response = await fetch(`<?= base_url('suppliers/store-transfer') ?>`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('Transfer created successfully!', 'success');
                    newTransferForm.reset();
                    productSelect.innerHTML = '<option value="">Select product</option>';
                    recipientSelect.innerHTML = '<option value="">Select destination branch first</option>';
                    currentStockInput.value = '';
                    document.getElementById('newTransferModal').closest('.modal').querySelector('.btn-close')?.click();
                    
                    // Reload transfers
                    setTimeout(() => location.reload(), 1500);
                } else {
                    transferAlert.textContent = data.message || 'Failed to create transfer';
                    transferAlert.classList.remove('d-none');
                }
            } catch (error) {
                transferAlert.textContent = 'Error: ' + error.message;
                transferAlert.classList.remove('d-none');
            }
        });

        // View transfer details
        document.querySelectorAll('[data-bs-target="#viewTransferModal"]').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('viewProduct').textContent = this.dataset.product;
                document.getElementById('viewFromBranch').textContent = this.dataset.from;
                document.getElementById('viewToBranch').textContent = this.dataset.to;
                document.getElementById('viewRecipient').textContent = this.dataset.recipient || '—';
                document.getElementById('viewQuantity').textContent = this.dataset.quantity;
                
                const status = this.dataset.status;
                const statusBadge = document.getElementById('viewStatus');
                statusBadge.innerHTML = `<span class="badge ${
                    status === 'completed' ? 'bg-success' : status === 'cancelled' ? 'bg-danger' : 'bg-warning'
                }">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
                
                document.getElementById('viewRemarks').textContent = this.dataset.remarks || '—';
            });
        });

        const updateTransferStatus = async (transferId, newStatus) => {
            const formData = new FormData();
            formData.append('transfer_id', transferId);
            formData.append('status', newStatus);

            try {
                const response = await fetch(`<?= base_url('suppliers/update-transfer-status') ?>`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showAlert(`Transfer ${newStatus} successfully!`, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message || 'Failed to update transfer', 'danger');
                }
            } catch (error) {
                showAlert('Error: ' + error.message, 'danger');
            }
        };

        // Update transfer status
        document.querySelectorAll('.update-transfer-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const transferId = this.dataset.id;
                const newStatus = this.dataset.status;
                const actionText = newStatus === 'cancelled' ? 'cancel' : newStatus;

                pendingTransferStatusAction = { transferId, newStatus };

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

        confirmTransferStatusBtn?.addEventListener('click', function() {
            if (!pendingTransferStatusAction) {
                return;
            }

            const { transferId, newStatus } = pendingTransferStatusAction;
            pendingTransferStatusAction = null;
            transferStatusConfirmModal?.hide();
            updateTransferStatus(transferId, newStatus);
        });

        function showAlert(message, type = 'info') {
            const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
            
            const container = document.querySelector('.container-fluid');
            const alertDiv = document.createElement('div');
            alertDiv.innerHTML = alertHtml;
            container.insertBefore(alertDiv.firstElementChild, container.firstChild);
            
            setTimeout(() => {
                container.querySelector('.alert')?.remove();
            }, 5000);
        }
    });
</script>

<?php $this->endSection(); ?>
