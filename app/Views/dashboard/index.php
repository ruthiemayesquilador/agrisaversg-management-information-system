<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<?php
$totalProducts = (int) ($totalProducts ?? 0);
$totalProductsPct = (int) ($totalProductsPct ?? 0);
$lowStocks = (int) ($lowStocks ?? 0);
$lowStocksPct = (int) ($lowStocksPct ?? 0);
$todaySalesCount = (int) ($todaySalesCount ?? 0);
$todaySalesTotal = (string) ($todaySalesTotal ?? '0.00');
$todaySalesPct = (int) ($todaySalesPct ?? 0);
$totalStockQty = (int) ($totalStockQty ?? 0);
$totalStockPct = (int) ($totalStockPct ?? 0);
$role = (string) ($role ?? '');
$usersInput = $users ?? [];
$users = is_array($usersInput) ? array_values(array_filter($usersInput, static fn ($row) => is_array($row))) : [];
$branchesInput = $branches ?? [];
$branches = is_array($branchesInput) ? array_values(array_filter($branchesInput, static fn ($row) => is_array($row))) : [];
$upcMappingsInput = $upcMappings ?? [];
$upcMappings = is_array($upcMappingsInput) ? array_values(array_filter($upcMappingsInput, static fn ($row) => is_array($row))) : [];
$canManageUpc = (bool) ($canManageUpc ?? false);
?>

<div class="container-fluid px-4 py-4">
    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('warning') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Products -->
        <div class="col-lg-3 col-md-6 col-6">
            <a href="<?= base_url('products') ?>" class="stat-card-link" aria-label="Go to products list">
                <div class="stat-card stat-card-blue">
                    <div class="stat-circle">
                        <svg class="circle-progress" viewBox="0 0 120 120">
                            <circle class="circle-bg" cx="60" cy="60" r="54"></circle>
                            <circle class="circle-fill blue-fill" cx="60" cy="60" r="54" 
                                    style="stroke-dashoffset: calc(339.292 - (339.292 * <?= $totalProductsPct ?>) / 100);"></circle>
                        </svg>
                        <div class="stat-number"><?= $totalProductsPct ?>%</div>
                    </div>
                    <div class="stat-label">Total Products</div>
                    <div class="stat-value"><?= $totalProducts ?></div>
                </div>
            </a>
        </div>

        <!-- Low Stocks Alert -->
        <div class="col-lg-3 col-md-6 col-6">
            <a href="<?= base_url('stocks') ?>" class="stat-card-link" aria-label="Go to stocks list">
                <div class="stat-card stat-card-green">
                    <div class="stat-circle">
                        <svg class="circle-progress" viewBox="0 0 120 120">
                            <circle class="circle-bg" cx="60" cy="60" r="54"></circle>
                            <circle class="circle-fill green-fill" cx="60" cy="60" r="54" 
                                    style="stroke-dashoffset: calc(339.292 - (339.292 * <?= $lowStocksPct ?>) / 100);"></circle>
                        </svg>
                        <div class="stat-number"><?= $lowStocksPct ?>%</div>
                    </div>
                    <div class="stat-label">Low Stocks Alert</div>
                    <div class="stat-value"><?= $lowStocks ?></div>
                </div>
            </a>
        </div>

        <!-- Today's Sales -->
        <div class="col-lg-3 col-md-6 col-6">
            <a href="<?= base_url('sales') ?>" class="stat-card-link" aria-label="Go to sales list">
                <div class="stat-card stat-card-red">
                    <div class="stat-circle">
                        <svg class="circle-progress" viewBox="0 0 120 120">
                            <circle class="circle-bg" cx="60" cy="60" r="54"></circle>
                            <circle class="circle-fill red-fill" cx="60" cy="60" r="54" 
                                    style="stroke-dashoffset: calc(339.292 - (339.292 * <?= $todaySalesPct ?>) / 100);"></circle>
                        </svg>
                        <div class="stat-number"><?= $todaySalesCount ?></div>
                    </div>
                    <div class="stat-label">Today's Sales</div>
                    <div class="stat-value">&#8369;<?= $todaySalesTotal ?></div>
                </div>
            </a>
        </div>

        <!-- Stock's Summary -->
        <div class="col-lg-3 col-md-6 col-6">
            <a href="<?= base_url('stocks') ?>" class="stat-card-link" aria-label="Go to stock summary">
                <div class="stat-card stat-card-purple">
                    <div class="stat-circle">
                        <svg class="circle-progress" viewBox="0 0 120 120">
                            <circle class="circle-bg" cx="60" cy="60" r="54"></circle>
                            <circle class="circle-fill purple-fill" cx="60" cy="60" r="54" 
                                    style="stroke-dashoffset: calc(339.292 - (339.292 * <?= $totalStockPct ?>) / 100);"></circle>
                        </svg>
                        <div class="stat-number"><?= $totalStockPct ?></div>
                    </div>
                    <div class="stat-label">Stock's Summary</div>
                    <div class="stat-value"><?= number_format($totalStockQty) ?></div>
                </div>
            </a>
        </div>
    </div>

    <!-- Banner/Advertisement -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-banner">
                <img src="<?= base_url('images/agri-banner.jpg') ?>" alt="Agri Savers Banner" class="img-fluid w-100">
            </div>
        </div>
    </div>

    <?php if (!empty($canManageUpc)): ?>
    <!-- UPC Mapping Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="manage-users-card">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
                    <h5 class="manage-users-title mb-0">UPC Table</h5>
                    <form action="<?= base_url('dashboard/upc/import') ?>" method="POST" enctype="multipart/form-data" class="upc-import-form" id="upcImportForm">
                        <?= csrf_field() ?>
                        <input type="file" class="d-none" id="upcCsvFile" name="upc_csv_file" accept=".csv,.txt" required>
                        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                            <button type="button" class="btn btn-upc-primary" id="upcImportBtn">
                                <i class="bi bi-upload me-1"></i>Import UPC CSV
                            </button>
                            <button type="button" class="btn btn-upc-light" data-bs-toggle="modal" data-bs-target="#addUpcModal">
                                <i class="bi bi-plus-circle me-1"></i>Add UPC
                            </button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive manage-table-wrapper">
                    <table class="table manage-users-table mb-0 js-sortable-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>SKU</th>
                                <th>UPC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($upcMappings)): ?>
                                <?php $rowNo = 1; ?>
                                <?php foreach ($upcMappings as $row): ?>
                                    <tr>
                                        <td><?= $rowNo++ ?></td>
                                        <td><?= esc((string) ($row['sku'] ?? '')) ?></td>
                                        <td><?= esc((string) ($row['upc'] ?? '')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">No UPC records found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addUpcModal" tabindex="-1" aria-labelledby="addUpcModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <form action="<?= base_url('dashboard/upc/add-single') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="addUpcModalLabel"><i class="bi bi-plus-circle me-2"></i>Add UPC Mapping</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="singleUpcSku" class="form-label fw-semibold">SKU</label>
                            <input type="text" class="form-control" id="singleUpcSku" name="sku" required>
                        </div>
                        <div class="mb-1">
                            <label for="singleUpcValue" class="form-label fw-semibold">UPC (12 digits)</label>
                            <input type="text" class="form-control" id="singleUpcValue" name="upc" maxlength="12" inputmode="numeric" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-upc-primary"><i class="bi bi-check2 me-1"></i>Save UPC</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const importBtn = document.getElementById('upcImportBtn');
            const fileInput = document.getElementById('upcCsvFile');
            const importForm = document.getElementById('upcImportForm');

            if (!importBtn || !fileInput || !importForm) {
                return;
            }

            importBtn.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', () => {
                if (fileInput.files && fileInput.files.length > 0) {
                    importForm.submit();
                }
            });
        })();
    </script>
    <?php endif; ?>

    <!-- Manage Users Table -->
    <?php if ($role === 'super_admin'): ?>
    <div class="row">
        <div class="col-12">
            <div class="manage-users-card">
                <!-- Card Header -->
                <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
                    <h5 class="manage-users-title mb-0">Manage Users</h5>
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group manage-search-group">
                            <input type="text" id="userSearchInput" class="form-control manage-search-input" placeholder="Search by username, email...">
                            <button class="btn manage-search-btn" type="button" data-bs-toggle="modal" data-bs-target="#usersFilterModal" aria-label="Filter" title="Filter"><i class="bi bi-funnel"></i></button>
                            <button class="btn manage-search-btn" type="button" onclick="filterUsers()"><i class="bi bi-search"></i></button>
                        </div>
                        <button class="btn btn-add-user" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="bi bi-plus-circle me-1"></i> Add User
                        </button>
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="table-responsive manage-table-wrapper">
                    <table class="table manage-users-table mb-0 js-sortable-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Email</th>
                                <th>Branch</th>
                                <th>Roles</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= esc((string) ($u['id'] ?? '')) ?></td>
                                    <td><?= esc((string) ($u['name'] ?? '')) ?></td>
                                    <td><?= esc((string) ($u['username'] ?? '')) ?></td>
                                    <td>••••••••</td>
                                    <td><?= esc((string) ($u['email'] ?? '')) ?></td>
                                    <td><?= esc((string) ($u['branch'] ?? '')) ?></td>
                                    <td>
                                        <span class="badge <?= $u['role'] === 'super_admin' ? 'bg-danger' : 'bg-primary' ?>">
                                            <?= esc((string) ($u['role'] ?? '')) ?>
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-sm btn-mu-view" title="View"
                                            onclick="openViewUser(<?= (int) ($u['id'] ?? 0) ?>, '<?= esc((string) ($u['name'] ?? ''), 'js') ?>', '<?= esc((string) ($u['username'] ?? ''), 'js') ?>', '<?= esc((string) ($u['email'] ?? ''), 'js') ?>', '<?= esc((string) ($u['branch'] ?? ''), 'js') ?>', '<?= esc((string) ($u['role'] ?? ''), 'js') ?>')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-mu-edit" title="Edit"
                                            onclick="openEditUser(<?= (int) ($u['id'] ?? 0) ?>, '<?= esc((string) ($u['name'] ?? ''), 'js') ?>', '<?= esc((string) ($u['username'] ?? ''), 'js') ?>', '<?= esc((string) ($u['email'] ?? ''), 'js') ?>', '<?= esc((string) ($u['branch'] ?? ''), 'js') ?>', '<?= esc((string) ($u['role'] ?? ''), 'js') ?>')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-mu-role" title="Delete"
                                            onclick="openDeleteUser(<?= (int) ($u['id'] ?? 0) ?>, '<?= esc((string) ($u['name'] ?? ''), 'js') ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center text-muted py-3">No users found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div id="userCardListContainer" class="user-card-list" style="display: none;">
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                        <div class="user-card-item"
                            data-search="<?= strtolower(esc((string) ($u['name'] ?? '')) . ' ' . esc((string) ($u['username'] ?? '')) . ' ' . esc((string) ($u['email'] ?? '')) . ' ' . esc((string) ($u['branch'] ?? '')) . ' ' . esc((string) ($u['role'] ?? ''))) ?>"
                            data-id="<?= strtolower((string) ($u['id'] ?? '')) ?>"
                            data-name="<?= strtolower(esc((string) ($u['name'] ?? ''))) ?>"
                            data-username="<?= strtolower(esc((string) ($u['username'] ?? ''))) ?>"
                            data-email="<?= strtolower(esc((string) ($u['email'] ?? ''))) ?>"
                            data-branch="<?= strtolower(esc((string) ($u['branch'] ?? ''))) ?>"
                            data-role="<?= strtolower(esc((string) ($u['role'] ?? ''))) ?>">
                            <div class="user-card-label">Name:</div>
                            <div class="user-card-value"><?= esc((string) ($u['name'] ?? '')) ?></div>

                            <div class="user-card-label">Username:</div>
                            <div class="user-card-value"><?= esc((string) ($u['username'] ?? '')) ?></div>

                            <div class="user-card-label">Email:</div>
                            <div class="user-card-value"><?= esc((string) ($u['email'] ?? '')) ?></div>

                            <div class="user-card-label">Branch:</div>
                            <div class="user-card-value"><?= esc((string) ($u['branch'] ?? '')) ?: '—' ?></div>

                            <div class="user-card-label">Role:</div>
                            <div class="user-card-value">
                                <span class="badge <?= $u['role'] === 'super_admin' ? 'bg-danger' : 'bg-primary' ?>">
                                    <?= esc((string) ($u['role'] ?? '')) ?>
                                </span>
                            </div>

                            <div class="user-card-actions">
                                <button class="btn btn-sm btn-mu-view" title="View"
                                    onclick="openViewUser(<?= (int) ($u['id'] ?? 0) ?>, '<?= esc((string) ($u['name'] ?? ''), 'js') ?>', '<?= esc((string) ($u['username'] ?? ''), 'js') ?>', '<?= esc((string) ($u['email'] ?? ''), 'js') ?>', '<?= esc((string) ($u['branch'] ?? ''), 'js') ?>', '<?= esc((string) ($u['role'] ?? ''), 'js') ?>')">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                <button class="btn btn-sm btn-mu-edit" title="Edit"
                                    onclick="openEditUser(<?= (int) ($u['id'] ?? 0) ?>, '<?= esc((string) ($u['name'] ?? ''), 'js') ?>', '<?= esc((string) ($u['username'] ?? ''), 'js') ?>', '<?= esc((string) ($u['email'] ?? ''), 'js') ?>', '<?= esc((string) ($u['branch'] ?? ''), 'js') ?>', '<?= esc((string) ($u['role'] ?? ''), 'js') ?>')">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-mu-role" title="Delete"
                                    onclick="openDeleteUser(<?= (int) ($u['id'] ?? 0) ?>, '<?= esc((string) ($u['name'] ?? ''), 'js') ?>')">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; color: #999; padding: 2rem;">
                            No users found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="manage-users-card">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
                    <h5 class="manage-users-title mb-0">Branch Master</h5>
                    <button class="btn btn-add-user" data-bs-toggle="modal" data-bs-target="#addBranchModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Branch
                    </button>
                </div>

                <div class="table-responsive manage-table-wrapper">
                    <table class="table manage-users-table mb-0 js-sortable-table" id="branchesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Branch Name</th>
                                <th>Status</th>
                                <th>Users</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($branches)): ?>
                                <?php foreach ($branches as $branch): ?>
                                    <tr>
                                        <td><?= esc((string) ($branch['branch_id'] ?? '')) ?></td>
                                        <td><?= esc((string) ($branch['branch_name'] ?? '')) ?></td>
                                        <td>
                                            <span class="badge <?= ((int) ($branch['is_active'] ?? 0) === 1) ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= ((int) ($branch['is_active'] ?? 0) === 1) ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td><?= esc((string) ($branch['user_count'] ?? 0)) ?></td>
                                        <td class="text-nowrap">
                                            <button class="btn btn-sm btn-mu-view" title="View"
                                                onclick="openViewBranch(<?= (int) ($branch['branch_id'] ?? 0) ?>, '<?= esc((string) ($branch['branch_name'] ?? ''), 'js') ?>', <?= (int) ($branch['is_active'] ?? 0) ?>, <?= (int) ($branch['user_count'] ?? 0) ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-mu-edit" title="Edit"
                                                onclick="openEditBranch(<?= (int) ($branch['branch_id'] ?? 0) ?>, '<?= esc((string) ($branch['branch_name'] ?? ''), 'js') ?>', <?= (int) ($branch['is_active'] ?? 0) ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-mu-role" title="Delete"
                                                onclick="openDeleteBranch(<?= (int) ($branch['branch_id'] ?? 0) ?>, '<?= esc((string) ($branch['branch_name'] ?? ''), 'js') ?>', <?= (int) ($branch['user_count'] ?? 0) ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No branches found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
    .dashboard-title {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a1a;
        letter-spacing: 1px;
    }

    .stat-card {
        background: white;
        border-radius: 24px;
        padding: 2rem 1.5rem;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }

    .stat-card-link {
        display: block;
        text-decoration: none;
        color: inherit;
        height: 100%;
    }

    .stat-card-link:focus-visible {
        outline: 3px solid rgba(13, 110, 253, 0.35);
        outline-offset: 4px;
        border-radius: 24px;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .stat-card-blue {
        border: 2px solid #4169E1;
    }

    .stat-card-green {
        border: 2px solid #00D084;
    }

    .stat-card-red {
        border: 2px solid #FF6B6B;
    }

    .stat-card-purple {
        border: 2px solid #9B7EDE;
    }

    .stat-circle {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto 1.5rem;
    }

    .circle-progress {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }

    .circle-bg {
        fill: none;
        stroke: #f0f0f0;
        stroke-width: 8;
    }

    .circle-fill {
        fill: none;
        stroke-width: 8;
        stroke-linecap: round;
        stroke-dasharray: 339.292;
        transition: stroke-dashoffset 1s ease;
    }

    .blue-fill {
        stroke: #4169E1;
    }

    .green-fill {
        stroke: #00D084;
    }

    .red-fill {
        stroke: #FF6B6B;
    }

    .purple-fill {
        stroke: #9B7EDE;
    }

    .stat-number {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 28px;
        font-weight: 700;
        color: #333;
    }

    .stat-label {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .stat-card-blue .stat-label {
        color: #4169E1;
    }

    .stat-card-green .stat-label {
        color: #00D084;
    }

    .stat-card-red .stat-label {
        color: #FF6B6B;
    }

    .stat-card-purple .stat-label {
        color: #9B7EDE;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #1a1a1a;
    }

    .dashboard-banner {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .dashboard-banner img {
        display: block;
        border-radius: 16px;
    }

    .upc-tools-card {
        background: #fff;
        border-radius: 16px;
        padding: 1.2rem 1.3rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.07);
        border: 1px solid #f0f0f0;
    }

    .upc-tools-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1f2937;
    }

    .upc-tools-actions {
        display: flex;
        flex-wrap: nowrap;
        gap: 0.55rem;
        height: 100%;
        align-items: center;
        justify-content: flex-start;
        white-space: nowrap;
        flex-shrink: 0;
        padding-top: 31px;
    }

    .upc-tools-row {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        flex-wrap: nowrap;
        overflow-x: auto;
    }

    .upc-import-form {
        flex: 1 1 auto;
        min-width: 420px;
    }

    .upc-import-form .input-group {
        flex-wrap: nowrap;
    }

    .upc-import-form .form-control,
    .upc-import-form .input-group .btn,
    .upc-tools-actions .btn {
        height: 46px;
        display: inline-flex;
        align-items: center;
    }

    .btn-upc-primary {
        background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);
        color: #fff;
        border: none;
    }

    .btn-upc-primary:hover {
        background: linear-gradient(135deg, #e65100 0%, #bf360c 100%);
        color: #fff;
    }

    .btn-upc-secondary {
        background: #0d6efd;
        color: #fff;
        border: none;
    }

    .btn-upc-secondary:hover {
        background: #0a58ca;
        color: #fff;
    }

    .btn-upc-light {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #495057;
    }

    .btn-upc-light:hover {
        background: #e9ecef;
        color: #343a40;
    }

    /* Manage Users Table */
    .manage-users-card {
        background: #fff;
        border-radius: 16px;
        padding: 1.25rem 1.5rem 0.5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.07);
    }

    /* Mobile table to card layout */
    @media (max-width: 767px) {
        .manage-users-card {
            padding: 1rem 1rem 1rem;
            background: #f9fafb;
        }

        .manage-users-table {
            display: none;
        }

        .manage-users-card .table-responsive {
            overflow: visible;
        }

        .user-card-list {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .user-card-item {
            background: #ffffff;
            border: none;
            border-radius: 14px;
            padding: 1.25rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.09);
            display: grid;
            grid-template-columns: 90px 1fr;
            gap: 0.75rem 1rem;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .user-card-item:active {
            transform: scale(0.98);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        }

        .user-card-item > * {
            font-size: 0.85rem;
        }

        .user-card-label {
            font-weight: 700;
            color: #666;
            text-transform: capitalize;
            letter-spacing: 0.3px;
        }

        .user-card-value {
            color: #222;
            word-break: break-word;
            font-weight: 500;
        }

        .user-card-value .badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.6rem;
            font-weight: 600;
        }

        .user-card-actions {
            grid-column: 1 / -1;
            display: flex;
            gap: 0.6rem;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f0f0f0;
        }
    }

    .manage-header-controls {
        display: flex;
        flex-direction: row;
        gap: 0.5rem;
        align-items: center;
    }

    .manage-users-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1a1a1a;
    }

    .manage-search-group {
        width: fit-content;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        flex: 1;
        min-width: 0;
        max-width: 100%;
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

    .btn-add-user {
        background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);
        color: #fff;
        font-weight: 600;
        font-size: 0.95rem;
        border: none;
        border-radius: 12px;
        padding: 0.65rem 1.5rem;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .btn-add-user:hover {
        background: linear-gradient(135deg, #e65100 0%, #bf360c 100%);
        color: #fff;
    }

    .manage-table-wrapper {
        overflow: visible;
    }

    .manage-users-table thead th {
        background: #f8f9fa;
        color: #555;
        font-weight: 600;
        font-size: 0.85rem;
        text-align: center;
        border-bottom: 2px solid #dee2e6;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .manage-users-table tbody td {
        text-align: center;
        vertical-align: middle;
        font-size: 0.875rem;
        border-bottom: 1px solid #f0f0f0;
        padding: 0.55rem 0.5rem;
    }

    .manage-users-table tbody tr:last-child td {
        border-bottom: none;
    }

    .manage-users-table tbody tr:hover {
        background: #fff8f0;
    }

    .btn-mu-view {
        background: #0d6efd;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 0.78rem;
        margin: 1px;
    }

    .btn-mu-view:hover { background: #0a58ca; color: #fff; }

    .btn-mu-edit {
        background: #f57c00;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 0.78rem;
        margin: 1px;
    }

    .btn-mu-edit:hover { background: #e65100; color: #fff; }

    .btn-mu-role {
        background: #e53935;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 0.78rem;
        margin: 1px;
    }

    .btn-mu-role:hover { background: #c62828; color: #fff; }

    /* ─── RESPONSIVE BREAKPOINTS ─── */
    
    /* Extra Small Devices (320px - 479px) */
    @media (max-width: 479px) {
        .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        /* Header Layout */
        .d-flex.align-items-center.justify-content-between.gap-3.mb-3.flex-wrap {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }

        .manage-users-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            width: 100%;
        }

        .d-flex.align-items-center.gap-2 {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
        }

        .manage-search-group {
            width: 100%;
            margin-bottom: 0;
            flex: 1;
            border-radius: 10px;
            overflow: hidden;
        }

        .manage-search-input {
            font-size: 0.9rem;
            padding: 0.6rem 0.75rem !important;
            height: auto;
        }

        .manage-search-btn {
            padding: 0.6rem 0.75rem;
            font-size: 0.9rem;
            background: #f8f9fa;
            border-left: 1px solid #dee2e6;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .manage-search-btn:active {
            background: #e9ecef;
        }

        .btn-add-user {
            padding: 0.6rem 0.75rem;
            font-size: 0 !important;
            width: 44px !important;
            height: 44px !important;
            margin: 0;
            border-radius: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            flex-shrink: 0;
            transition: all 0.2s ease;
            overflow: hidden;
            white-space: nowrap;
            color: transparent;
        }

        .btn-add-user i {
            font-size: 1.25rem;
            color: #fff;
        }

        .btn-add-user:active {
            transform: scale(0.95);
        }

        .dashboard-title {
            font-size: 20px;
            letter-spacing: 0.5px;
        }

        .stat-card {
            padding: 1rem 0.75rem;
            border-radius: 16px;
        }

        .stat-circle {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
        }

        .stat-number {
            font-size: 20px;
        }

        .stat-label {
            font-size: 13px;
            margin-bottom: 0.35rem;
        }

        .stat-value {
            font-size: 18px;
        }

        .dashboard-banner {
            border-radius: 12px;
            margin-bottom: 1rem;
        }

        .btn-mu-view, .btn-mu-edit, .btn-mu-role {
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
            margin: 0;
            flex: 1;
            border-radius: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            transition: all 0.2s ease;
        }

        .btn-mu-view:active {
            transform: translateY(1px);
        }

        .btn-mu-edit:active {
            transform: translateY(1px);
        }

        .btn-mu-role:active {
            transform: translateY(1px);
        }
    }

    /* Small Devices (480px - 639px) */
    @media (min-width: 480px) and (max-width: 639px) {
        .container-fluid {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .dashboard-title {
            font-size: 24px;
        }

        .stat-card {
            padding: 1.25rem 1rem;
        }

        .stat-circle {
            width: 90px;
            height: 90px;
            margin: 0 auto 1.25rem;
        }

        .stat-number {
            font-size: 22px;
        }

        .stat-label {
            font-size: 14px;
        }

        .stat-value {
            font-size: 20px;
        }

        .manage-search-group {
            width: auto;
            flex: 1;
            margin-bottom: 0;
        }

        .btn-add-user {
            padding: 0.6rem 0.75rem;
            font-size: 0 !important;
            width: 44px !important;
            height: 44px !important;
            margin: 0;
            flex-shrink: 0;
            overflow: hidden;
            white-space: nowrap;
            color: transparent;
        }

        .btn-add-user i {
            font-size: 1.25rem;
            color: #fff;
        }

        .d-flex.align-items-center.gap-2 {
            width: 100%;
            flex-direction: row;
            gap: 0.5rem;
        }

        .d-flex.align-items-center.justify-content-between.gap-3 {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }
    }

    /* Tablet & Medium (640px - 767px) */
    @media (min-width: 640px) and (max-width: 767px) {
        .stat-circle {
            width: 100px;
            height: 100px;
            margin: 0 auto 1.5rem;
        }

        .stat-number {
            font-size: 24px;
        }

        .stat-label {
            font-size: 15px;
        }

        .stat-value {
            font-size: 22px;
        }

        .manage-search-group {
            width: auto;
            min-width: 200px;
            flex: 0;
        }

        .btn-add-user {
            padding: 0.6rem 0.75rem;
            font-size: 0 !important;
            width: 44px !important;
            height: 44px !important;
            margin: 0;
            flex-shrink: 0;
            overflow: hidden;
            white-space: nowrap;
            color: transparent;
        }

        .btn-add-user i {
            font-size: 1.25rem;
            color: #fff;
        }

        .d-flex.align-items-center.gap-2 {
            flex-direction: row;
        }

        .d-flex.align-items-center.justify-content-between.gap-3 {
            flex-wrap: wrap;
        }
    }

    /* Desktop (768px+) - Reset button to full text */
    @media (min-width: 768px) {
        .btn-add-user {
            padding: 0.65rem 1.5rem;
            font-size: 0.95rem !important;
            width: auto !important;
            height: auto !important;
            color: #fff !important;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            white-space: nowrap;
            flex-shrink: 0;
            overflow: visible;
        }

        .btn-add-user i {
            font-size: 1rem;
            color: #fff;
        }

        .d-flex.align-items-center.gap-2 {
            flex-direction: row;
            gap: 1rem;
        }

        .manage-search-group {
            width: 100%;
            min-width: 250px;
            margin-bottom: 0;
        }
    }

    /* Modal Responsiveness */
    @media (max-width: 479px) {
        .modal-dialog {
            margin: 0.5rem;
        }

        .modal-content {
            border-radius: 12px;
        }

        .modal-body {
            padding: 0.75rem;
        }

        .modal-footer {
            padding: 0.75rem;
            gap: 0.5rem;
        }

        .modal-header {
            padding: 0.75rem;
        }

        .form-control, .form-select {
            font-size: 16px;
            padding: 0.4rem 0.6rem;
        }

        .modal-body .row {
            gap: 0.75rem;
        }

        .btn {
            padding: 0.4rem 0.75rem;
            font-size: 0.85rem;
        }
    }
</style>

<!-- ─── User Management Modals (Super Admin Only) ───────────────────────────── -->
<?php if ($role === 'super_admin'): ?>

<div class="modal fade" id="usersFilterModal" tabindex="-1" aria-labelledby="usersFilterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="usersFilterLabel">Filter Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="filter-section">
                    <h6 class="filter-section-title">Columns</h6>
                    <div class="filter-checkboxes">
                        <label class="filter-checkbox"><input type="checkbox" class="users-column-toggle" data-column="0" checked><span>ID</span></label>
                        <label class="filter-checkbox"><input type="checkbox" class="users-column-toggle" data-column="1" checked><span>Name</span></label>
                        <label class="filter-checkbox"><input type="checkbox" class="users-column-toggle" data-column="2" checked><span>Username</span></label>
                        <label class="filter-checkbox"><input type="checkbox" class="users-column-toggle" data-column="3" checked><span>Password</span></label>
                        <label class="filter-checkbox"><input type="checkbox" class="users-column-toggle" data-column="4" checked><span>Email</span></label>
                        <label class="filter-checkbox"><input type="checkbox" class="users-column-toggle" data-column="5" checked><span>Branch</span></label>
                        <label class="filter-checkbox"><input type="checkbox" class="users-column-toggle" data-column="6" checked><span>Roles</span></label>
                        <label class="filter-checkbox"><input type="checkbox" class="users-column-toggle" data-column="7" checked><span>Action</span></label>
                    </div>
                </div>

                <div class="filter-section mb-0">
                    <h6 class="filter-section-title">Role</h6>
                    <div class="filter-radios">
                        <label class="filter-radio"><input type="radio" name="usersRoleFilter" value="all" checked><span>All</span></label>
                        <label class="filter-radio"><input type="radio" name="usersRoleFilter" value="admin"><span>Admin</span></label>
                        <label class="filter-radio"><input type="radio" name="usersRoleFilter" value="super_admin"><span>Super Admin</span></label>
                    </div>
                </div>

                <div class="filter-section mb-0">
                    <h6 class="filter-section-title">Filter By</h6>
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-5">
                            <label for="usersFilterByField" class="form-label mb-1">Field</label>
                            <select id="usersFilterByField" class="form-select form-select-sm">
                                <option value="all">All Fields</option>
                                <option value="id">ID</option>
                                <option value="name">Name</option>
                                <option value="username">Username</option>
                                <option value="email">Email</option>
                                <option value="branch">Branch</option>
                                <option value="role">Role</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-7">
                            <label for="usersFilterByValue" class="form-label mb-1">Keyword</label>
                            <input type="text" id="usersFilterByValue" class="form-control form-control-sm" placeholder="Type a value to filter users">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="applyUsersFiltersBtn">Apply Filters</button>
                <button type="button" class="btn btn-danger" id="resetUsersFiltersBtn">Reset</button>
            </div>
        </div>
    </div>
</div>

<!-- ─── Add User Modal ─────────────────────────────────────────────────────── -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('dashboard/users/add') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body pt-2">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Juan Dela Cruz" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" placeholder="e.g. juandc" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. juan@agrisavers.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Branch</label>
                            <select name="branch" class="form-select">
                                <option value="">Select branch</option>
                                <?php if (!empty($branches)): ?>
                                    <?php foreach ($branches as $b): ?>
                                        <option value="<?= esc((string) ($b['branch_name'] ?? '')) ?>">
                                            <?= esc((string) ($b['branch_name'] ?? '')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-add-user"><i class="bi bi-plus-circle me-1"></i> Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ─── View User Modal ────────────────────────────────────────────────────── -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="viewUserModalLabel">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr><th style="width:35%">Name</th><td id="vu_name">—</td></tr>
                        <tr><th>Username</th><td id="vu_username">—</td></tr>
                        <tr><th>Email</th><td id="vu_email">—</td></tr>
                        <tr><th>Branch</th><td id="vu_branch">—</td></tr>
                        <tr><th>Role</th><td id="vu_role">—</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ─── Edit User Modal ────────────────────────────────────────────────────── -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('dashboard/users/update') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="eu_id">
                <div class="modal-body pt-2">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="eu_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" id="eu_username" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="eu_email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Branch</label>
                            <select name="branch" id="eu_branch" class="form-select">
                                <option value="">Select branch</option>
                                <?php if (!empty($branches)): ?>
                                    <?php foreach ($branches as $b): ?>
                                        <option value="<?= esc((string) ($b['branch_name'] ?? '')) ?>">
                                            <?= esc((string) ($b['branch_name'] ?? '')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                            <select name="role" id="eu_role" class="form-select" required>
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-mu-edit px-4"><i class="bi bi-pencil me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ─── Delete User Modal ─────────────────────────────────────────────────── -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" id="deleteUserModalLabel"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="du_name">this user</strong>? This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <a id="du_deleteBtn" href="#" class="btn btn-danger px-4"><i class="bi bi-trash me-1"></i> Delete</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addBranchModal" tabindex="-1" aria-labelledby="addBranchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="addBranchModalLabel">Add Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('dashboard/branches/add') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body pt-2">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Branch Name <span class="text-danger">*</span></label>
                            <input type="text" name="branch_name" class="form-control" placeholder="e.g. Camanan Branch" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-add-user"><i class="bi bi-plus-circle me-1"></i> Add Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="viewBranchModal" tabindex="-1" aria-labelledby="viewBranchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="viewBranchModalLabel">Branch Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr><th style="width:35%">ID</th><td id="vb_id">—</td></tr>
                        <tr><th>Branch Name</th><td id="vb_name">—</td></tr>
                        <tr><th>Status</th><td id="vb_status">—</td></tr>
                        <tr><th>Users</th><td id="vb_users">—</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editBranchModal" tabindex="-1" aria-labelledby="editBranchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="editBranchModalLabel">Edit Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('dashboard/branches/update') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="branch_id" id="eb_id">
                <div class="modal-body pt-2">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Branch Name <span class="text-danger">*</span></label>
                            <input type="text" name="branch_name" id="eb_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="is_active" id="eb_active" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-mu-edit px-4"><i class="bi bi-pencil me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteBranchModal" tabindex="-1" aria-labelledby="deleteBranchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" id="deleteBranchModalLabel"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Delete branch <strong id="db_name">this branch</strong>?</p>
                <p id="db_warning" class="text-danger small mb-0 d-none">This branch has assigned users/records and cannot be deleted.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <a id="db_deleteBtn" href="#" class="btn btn-danger px-4"><i class="bi bi-trash me-1"></i> Delete</a>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<script>
<?php if ($role === 'super_admin'): ?>
// ─── Responsive Table/Card View Toggle ───────────────────────────────────────

function updateUserTableDisplay() {
    const table = document.querySelector('.manage-users-table');
    const cardList = document.getElementById('userCardListContainer');
    const isSmallScreen = window.innerWidth < 768;

    if (isSmallScreen) {
        if (table) table.style.display = 'none';
        if (cardList) cardList.style.display = 'flex';
    } else {
        if (table) table.style.display = 'table';
        if (cardList) cardList.style.display = 'none';
    }
}

// Update on page load and window resize
updateUserTableDisplay();
window.addEventListener('resize', updateUserTableDisplay);

// ─── Modal helpers ───────────────────────────────────────────────────────────

function openViewUser(id, name, username, email, branch, role) {
    document.getElementById('vu_name').textContent     = name;
    document.getElementById('vu_username').textContent = username;
    document.getElementById('vu_email').textContent    = email;
    document.getElementById('vu_branch').textContent   = branch || '—';
    document.getElementById('vu_role').textContent     = role;
    new bootstrap.Modal(document.getElementById('viewUserModal')).show();
}

function openEditUser(id, name, username, email, branch, role) {
    document.getElementById('eu_id').value         = id;
    document.getElementById('eu_name').value       = name;
    document.getElementById('eu_username').value   = username;
    document.getElementById('eu_email').value      = email;
    const branchSelect = document.getElementById('eu_branch');
    if (branchSelect) {
        const hasOption = Array.from(branchSelect.options).some((option) => option.value === branch);
        if (!hasOption && branch) {
            const legacyOption = document.createElement('option');
            legacyOption.value = branch;
            legacyOption.textContent = branch;
            branchSelect.appendChild(legacyOption);
        }
        branchSelect.value = branch || '';
    }
    document.getElementById('eu_role').value       = role;
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function openDeleteUser(id, name) {
    document.getElementById('du_name').textContent    = name;
    document.getElementById('du_deleteBtn').href      = '<?= base_url('dashboard/users/delete/') ?>' + id;
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
}

function openViewBranch(id, name, isActive, userCount) {
    document.getElementById('vb_id').textContent = id;
    document.getElementById('vb_name').textContent = name || '—';
    document.getElementById('vb_status').textContent = Number(isActive) === 1 ? 'Active' : 'Inactive';
    document.getElementById('vb_users').textContent = userCount;
    new bootstrap.Modal(document.getElementById('viewBranchModal')).show();
}

function openEditBranch(id, name, isActive) {
    document.getElementById('eb_id').value = id;
    document.getElementById('eb_name').value = name || '';
    document.getElementById('eb_active').value = Number(isActive) === 1 ? '1' : '0';
    new bootstrap.Modal(document.getElementById('editBranchModal')).show();
}

function openDeleteBranch(id, name, userCount) {
    document.getElementById('db_name').textContent = name;
    const warning = document.getElementById('db_warning');
    const deleteBtn = document.getElementById('db_deleteBtn');
    deleteBtn.href = '<?= base_url('dashboard/branches/delete/') ?>' + id;

    if (Number(userCount) > 0) {
        warning.classList.remove('d-none');
        deleteBtn.classList.add('disabled');
        deleteBtn.setAttribute('aria-disabled', 'true');
        deleteBtn.addEventListener('click', preventDeleteClick, { once: true });
    } else {
        warning.classList.add('d-none');
        deleteBtn.classList.remove('disabled');
        deleteBtn.removeAttribute('aria-disabled');
    }

    new bootstrap.Modal(document.getElementById('deleteBranchModal')).show();
}

function preventDeleteClick(event) {
    event.preventDefault();
}

// ─── Live search/filter ──────────────────────────────────────────────────────

const userSearchInput = document.getElementById('userSearchInput');
if (userSearchInput) {
    userSearchInput.addEventListener('keyup', filterUsers);
}

const usersFilterState = {
    role: 'all',
    filterByField: 'all',
    filterByValue: '',
};

const usersTable = document.querySelector('.manage-users-table');
const usersColumnToggles = document.querySelectorAll('.users-column-toggle');
const usersRoleRadios = document.querySelectorAll('input[name="usersRoleFilter"]');
const usersFilterModalEl = document.getElementById('usersFilterModal');
const usersFilterByField = document.getElementById('usersFilterByField');
const usersFilterByValue = document.getElementById('usersFilterByValue');

function getUsersDesktopFieldText(row, field) {
    const cells = row.querySelectorAll('td');
    const fieldMap = {
        id: 0,
        name: 1,
        username: 2,
        email: 4,
        branch: 5,
    };

    if (field === 'role') {
        const roleBadge = row.querySelector('td:nth-child(7) .badge');
        return (roleBadge ? roleBadge.textContent : '').trim().toLowerCase();
    }

    if (field === 'all') {
        return row.textContent.toLowerCase();
    }

    const fieldIndex = fieldMap[field];
    if (typeof fieldIndex !== 'number' || !cells[fieldIndex]) {
        return '';
    }

    return (cells[fieldIndex].textContent || '').trim().toLowerCase();
}

function getUsersCardFieldText(card, field) {
    if (field === 'all') {
        return card.getAttribute('data-search') || '';
    }

    return card.getAttribute(`data-${field}`) || '';
}

function applyUsersColumnVisibility() {
    if (!usersTable) {
        return;
    }

    usersColumnToggles.forEach((toggle) => {
        const idx = parseInt(toggle.dataset.column || '-1', 10);
        if (idx < 0) {
            return;
        }
        usersTable.querySelectorAll('tr').forEach((row) => {
            const cells = row.querySelectorAll('th, td');
            if (cells[idx]) {
                cells[idx].style.display = toggle.checked ? '' : 'none';
            }
        });
    });
}

function filterUsers() {
    const query = (document.getElementById('userSearchInput')?.value || '').toLowerCase();
    const filterByField = usersFilterState.filterByField || 'all';
    const filterByValue = (usersFilterState.filterByValue || '').toLowerCase().trim();
    
    // Filter desktop table rows
    const rows = document.querySelectorAll('#userTableBody tr');
    rows.forEach(row => {
        const roleBadge = row.querySelector('td:nth-child(7) .badge');
        const rowRole = (roleBadge ? roleBadge.textContent : '').trim().toLowerCase();
        const queryMatch = row.textContent.toLowerCase().includes(query);
        const roleMatch = usersFilterState.role === 'all' || rowRole === usersFilterState.role;
        const fieldValue = getUsersDesktopFieldText(row, filterByField);
        const filterByMatch = !filterByValue || fieldValue.includes(filterByValue);
        row.style.display = (queryMatch && roleMatch && filterByMatch) ? '' : 'none';
    });

    // Filter mobile card items
    const cards = document.querySelectorAll('.user-card-item');
    cards.forEach(card => {
        const searchText = card.getAttribute('data-search') || '';
        const roleBadge = card.querySelector('.badge');
        const cardRole = (roleBadge ? roleBadge.textContent : '').trim().toLowerCase();
        const queryMatch = searchText.includes(query);
        const roleMatch = usersFilterState.role === 'all' || cardRole === usersFilterState.role;
        const fieldValue = getUsersCardFieldText(card, filterByField).toLowerCase();
        const filterByMatch = !filterByValue || fieldValue.includes(filterByValue);
        card.style.display = (queryMatch && roleMatch && filterByMatch) ? '' : 'none';
    });
}

function resetUsersFilter() {
    const input = document.getElementById('userSearchInput');
    if (!input) {
        return;
    }
    input.value = '';
    usersFilterState.role = 'all';
    usersFilterState.filterByField = 'all';
    usersFilterState.filterByValue = '';
    usersRoleRadios.forEach((radio) => {
        radio.checked = radio.value === 'all';
    });
    if (usersFilterByField) {
        usersFilterByField.value = 'all';
    }
    if (usersFilterByValue) {
        usersFilterByValue.value = '';
    }
    usersColumnToggles.forEach((toggle) => {
        toggle.checked = true;
    });
    applyUsersColumnVisibility();
    filterUsers();
    input.focus();
}

if (usersFilterModalEl) {
    const applyUsersFiltersBtn = document.getElementById('applyUsersFiltersBtn');
    const resetUsersFiltersBtn = document.getElementById('resetUsersFiltersBtn');

    if (applyUsersFiltersBtn) {
        applyUsersFiltersBtn.addEventListener('click', () => {
            const checkedRole = document.querySelector('input[name="usersRoleFilter"]:checked');
            usersFilterState.role = checkedRole ? checkedRole.value : 'all';
            usersFilterState.filterByField = usersFilterByField ? usersFilterByField.value : 'all';
            usersFilterState.filterByValue = usersFilterByValue ? usersFilterByValue.value.trim() : '';
            applyUsersColumnVisibility();
            filterUsers();
            bootstrap.Modal.getOrCreateInstance(usersFilterModalEl).hide();
        });
    }

    if (resetUsersFiltersBtn) {
        resetUsersFiltersBtn.addEventListener('click', () => {
            usersFilterState.role = 'all';
            usersFilterState.filterByField = 'all';
            usersFilterState.filterByValue = '';
            usersRoleRadios.forEach((radio) => {
                radio.checked = radio.value === 'all';
            });
            if (usersFilterByField) {
                usersFilterByField.value = 'all';
            }
            if (usersFilterByValue) {
                usersFilterByValue.value = '';
            }
            usersColumnToggles.forEach((toggle) => {
                toggle.checked = true;
            });
            applyUsersColumnVisibility();
            filterUsers();
            bootstrap.Modal.getOrCreateInstance(usersFilterModalEl).hide();
        });
    }

    applyUsersColumnVisibility();
}
<?php endif; ?>
</script>

<?php $this->endSection(); ?>
