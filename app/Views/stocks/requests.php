<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Requests - Agri Savers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-badge {
            font-size: 0.8em;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-approved { background-color: #d1ecf1; color: #0c5460; }
        .status-fulfilled { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-clipboard-list"></i> Stock Requests</h2>
                    <a href="<?= base_url('stocks') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Stocks
                    </a>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <?php if (empty($requests)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Stock Requests</h5>
                                <p class="text-muted">There are currently no stock requests to display.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Request ID</th>
                                            <th>Product</th>
                                            <th>SKU</th>
                                            <th>Requesting Branch</th>
                                            <th>Quantity</th>
                                            <th>Status</th>
                                            <th>Requested By</th>
                                            <th>Requested At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($requests as $request): ?>
                                            <tr>
                                                <td>#<?= $request['request_id'] ?></td>
                                                <td>
                                                    <strong><?= esc($request['part_name']) ?></strong>
                                                    <?php if (!empty($request['part_no'])): ?>
                                                        <br><small class="text-muted">Part No: <?= esc($request['part_no']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= esc($request['sku']) ?></td>
                                                <td><?= esc($request['requesting_branch']) ?></td>
                                                <td><?= number_format($request['requested_quantity']) ?></td>
                                                <td>
                                                    <span class="badge status-badge status-<?= strtolower($request['status']) ?>">
                                                        <?= ucfirst($request['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= esc($request['requested_by']) ?></td>
                                                <td><?= date('M d, Y H:i', strtotime($request['created_at'])) ?></td>
                                                <td>
                                                    <?php if ($request['status'] === 'pending' && $isMainBranch): ?>
                                                        <form method="post" action="<?= base_url('stocks/approveRequest') ?>" class="d-inline">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="request_id" value="<?= $request['request_id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this stock request?')">
                                                                <i class="fas fa-check"></i> Approve
                                                            </button>
                                                        </form>
                                                        <form method="post" action="<?= base_url('stocks/cancelRequest') ?>" class="d-inline">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="request_id" value="<?= $request['request_id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this stock request?')">
                                                                <i class="fas fa-times"></i> Cancel
                                                            </button>
                                                        </form>
                                                    <?php elseif ($request['status'] === 'approved' && $isMainBranch): ?>
                                                        <form method="post" action="<?= base_url('stocks/fulfillRequest') ?>" class="d-inline">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="request_id" value="<?= $request['request_id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Mark this request as fulfilled?')">
                                                                <i class="fas fa-check-double"></i> Fulfill
                                                            </button>
                                                        </form>
                                                    <?php elseif ($request['status'] === 'pending' && !$isMainBranch && $request['requesting_branch'] === $userBranch): ?>
                                                        <form method="post" action="<?= base_url('stocks/cancelRequest') ?>" class="d-inline">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="request_id" value="<?= $request['request_id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Cancel your stock request?')">
                                                                <i class="fas fa-times"></i> Cancel
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>