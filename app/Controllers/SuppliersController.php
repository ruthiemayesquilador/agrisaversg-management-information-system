<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SuppliersModel;
use App\Models\ProductsModel;
use App\Models\StocksModel;
use App\Models\ProductTransfersModel;
use App\Models\StockRequestsModel;
use CodeIgniter\HTTP\ResponseInterface;

class SuppliersController extends BaseController
{
    protected SuppliersModel $suppliersModel;
    protected ProductsModel $productsModel;
    protected StocksModel $stocksModel;
    protected ProductTransfersModel $transfersModel;

    public function __construct()
    {
        $this->suppliersModel = new SuppliersModel();
        $this->productsModel = new ProductsModel();
        $this->stocksModel = new StocksModel();
        $this->transfersModel = new ProductTransfersModel();
    }

    public function index()
    {
        $role = (string) (session()->get('role') ?? '');
        if ($role === 'admin') {
            return redirect()->to(base_url('suppliers/admin-transfers'));
        }

        $data['suppliers'] = $this->suppliersModel->orderBy('supplier_id', 'ASC')->findAll();

        $userBranch = (string) (session()->get('branch') ?? '');
        $requestedBranch = trim((string) $this->request->getGet('branch'));
        $canCrossBranchView = $this->canViewCrossBranchSuppliers($role, $userBranch);
        $activeBranchFilter = '';
        $activeBranchMode = 'all';
        $branchFilter = null;

        if ($canCrossBranchView) {
            $scope = $this->resolveBranchScope($role, $userBranch, $requestedBranch);
            $activeBranchFilter = (string) ($scope['activeBranchFilter'] ?? '');
            $activeBranchMode = (string) ($scope['mode'] ?? 'all');
            $branchFilter = $scope['branch'] ?? null;
        }

        $data['user_role'] = $role;
        $data['user_branch'] = $userBranch;
        $data['can_dispatch_transfer'] = $role === 'super_admin';
        $data['can_receive_transfer'] = $role === 'admin';
        $data['canCrossBranchView'] = $canCrossBranchView;
        $data['availableBranches'] = $canCrossBranchView ? $this->getBranchOptions() : [];
        $data['activeBranchFilter'] = $activeBranchFilter;
        $data['activeBranchMode'] = $activeBranchMode;
        $data['branches'] = $this->getBranchOptions();

        $transferQuery = $this->transfersModel->builder()
            ->select('product_transfers.*, products.part_name, products.sku, recipient_user.name AS recipient_name')
            ->join('products', 'products.product_id = product_transfers.product_id', 'left')
            ->join('users recipient_user', 'recipient_user.id = product_transfers.recipient_user_id', 'left');

        if ($role === 'admin' && $userBranch) {
            $transferQuery->groupStart()
                ->where('product_transfers.from_branch', $userBranch)
                ->orWhere('product_transfers.to_branch', $userBranch)
                ->groupEnd();
        }

        if ($canCrossBranchView && $branchFilter !== null && $branchFilter !== '') {
            $transferQuery->groupStart()
                ->where('product_transfers.from_branch', $branchFilter)
                ->orWhere('product_transfers.to_branch', $branchFilter)
                ->groupEnd();
        }

        $data['transfers'] = $transferQuery
            ->orderBy('product_transfers.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        // Get pending stock requests (only for super_admin or main branch admin)
        $data['pending_stock_requests'] = [];
        $isMainBranch = $this->isMainBranch($userBranch);
        if ($role === 'super_admin' || ($role === 'admin' && $isMainBranch)) {
            $stockRequestsModel = new StockRequestsModel();
            $data['pending_stock_requests'] = $stockRequestsModel
                ->select('stock_requests.*, p.part_name, p.sku, p.part_no')
                ->join('products p', 'p.product_id = stock_requests.product_id', 'left')
                ->where('stock_requests.status', 'pending')
                ->orderBy('stock_requests.created_at', 'DESC')
                ->limit(5)
                ->findAll();
        }
        $data['isMainBranch'] = $isMainBranch;

        return view('suppliers/index', $data);
    }

    /**
     * Get all suppliers or specific supplier by ID
     */
    public function get(?int $id = null)
    {
        if ($id) {
            $supplier = $this->suppliersModel->find($id);
            if (!$supplier) {
                return $this->response->setJSON(['success' => false, 'message' => 'Supplier not found'], 404);
            }
            return $this->response->setJSON(['success' => true, 'data' => $supplier]);
        }

        $suppliers = $this->suppliersModel->orderBy('supply_id', 'DESC')->findAll();
        return $this->response->setJSON(['success' => true, 'data' => $suppliers]);
    }

    /**
     * Read suppliers for DataTable
     */
    public function read()
    {
        $suppliers = $this->suppliersModel->orderBy('supply_id', 'DESC')->findAll();
        
        if (empty($suppliers)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No suppliers found']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $suppliers]);
    }

    /**
     * Store new supplier
     */
    public function store()
    {
        $data = [
            'supplier_name' => $this->request->getPost('supplier_name'),
            'brand_name'    => $this->request->getPost('brand_name'),
            'importer'      => $this->request->getPost('importer') ?: null,
        ];

        // Remove empty optional fields
        $data = array_filter($data, function ($value) {
            return $value !== '' && $value !== null;
        });

        if (!$this->suppliersModel->save($data)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to save supplier',
                'errors'  => $this->suppliersModel->errors()
            ], 400);
        }

        $supplierId = $this->suppliersModel->getInsertID();
        $itemName = $data['supplier_name'] ?? 'Unknown';
        $this->logHistorySafe(
            'Supplier Added',
            'Added supplier "' . $itemName . '"',
            (int) $supplierId,
            $itemName,
            null,
            'Suppliers'
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Supplier added successfully',
            'id'      => $supplierId
        ]);
    }

    /**
     * Update existing supplier
     */
    public function update()
    {
        $id = $this->request->getPost('id');
        
        if (!$id || !$this->suppliersModel->find($id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Supplier not found'], 404);
        }

        $data = [
            'supplier_id'   => $id,
            'supplier_name' => $this->request->getPost('supplier_name'),
            'brand_name'    => $this->request->getPost('brand_name'),
            'importer'      => $this->request->getPost('importer') ?: null,
        ];

        // Remove empty optional fields
        $data = array_filter($data, function ($value, $key) {
            return $value !== '' && $value !== null || $key === 'supplier_id';
        }, ARRAY_FILTER_USE_BOTH);

        if (!$this->suppliersModel->save($data)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update supplier',
                'errors'  => $this->suppliersModel->errors()
            ], 400);
        }

        $itemName = $data['supplier_name'] ?? 'Unknown';
        $this->logHistorySafe(
            'Supplier Updated',
            'Updated supplier "' . $itemName . '"',
            (int) $id,
            $itemName,
            null,
            'Suppliers'
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Supplier updated successfully'
        ]);
    }

    /**
     * Delete supplier
     */
    public function delete(?int $id = null)
    {
        if (!$id) {
            $id = $this->request->getPost('id');
        }

        $supplier = $this->suppliersModel->find($id);
        
        if (!$id || !$supplier) {
            return $this->response->setJSON(['success' => false, 'message' => 'Supplier not found'], 404);
        }

        $itemName = (string) ($supplier['supplier_name'] ?? 'Unknown');

        if ($this->suppliersModel->delete($id)) {
            $this->logHistorySafe(
                'Supplier Deleted',
                'Deleted supplier "' . $itemName . '"',
                (int) $id,
                $itemName,
                null,
                'Suppliers'
            );
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Supplier deleted successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to delete supplier'
        ], 400);
    }

    /**
     * Show product transfer page with products and branches
     */
    public function transferProducts()
    {
        $role = (string) (session()->get('role') ?? '');
        $userBranch = session()->get('branch') ?? null;
        $data['user_role'] = $role;
        $data['user_branch'] = $userBranch;
        $data['can_dispatch_transfer'] = $role === 'super_admin';
        $data['can_receive_transfer'] = $role === 'admin';
        
        $data['products'] = $userBranch ? $this->getProductsForBranch((string) $userBranch) : $this->productsModel->orderBy('part_name', 'ASC')->findAll();
        $data['branches'] = $this->getBranchOptions();
        
        // Get transfer history
        $transfersQuery = $this->transfersModel->builder()
            ->select('product_transfers.*, products.part_name, products.sku, recipient_user.name AS recipient_name')
            ->join('products', 'products.product_id = product_transfers.product_id', 'left')
            ->join('users recipient_user', 'recipient_user.id = product_transfers.recipient_user_id', 'left');

        if ($role === 'admin' && $userBranch) {
            $transfersQuery->groupStart()
                ->where('product_transfers.from_branch', $userBranch)
                ->orWhere('product_transfers.to_branch', $userBranch)
                ->groupEnd();
        }

        $data['transfers'] = $transfersQuery->orderBy('product_transfers.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();
        
        return view('suppliers/transfer_products', $data);
    }

    /**
     * Admin-side supplier transfer module (table only)
     */
    public function adminTransfers()
    {
        $role = (string) (session()->get('role') ?? '');
        if ($role !== 'admin') {
            return redirect()->to(base_url('suppliers'));
        }

        $userBranch = trim((string) (session()->get('branch') ?? ''));
        $normalizedUserBranch = $this->normalizeBranchLabel($userBranch);

        $transfers = $this->transfersModel->builder()
            ->select('product_transfers.*, products.part_name, products.sku')
            ->join('products', 'products.product_id = product_transfers.product_id', 'left')
            ->orderBy('product_transfers.created_at', 'DESC')
            ->limit(200)
            ->get()
            ->getResultArray();

        if ($normalizedUserBranch !== '') {
            $transfers = array_values(array_filter($transfers, function (array $transfer) use ($normalizedUserBranch): bool {
                $toBranch = $this->normalizeBranchLabel((string) ($transfer['to_branch'] ?? ''));
                return $toBranch !== '' && $toBranch === $normalizedUserBranch;
            }));
        } else {
            // Keep result empty when branch is missing to avoid exposing unrelated records.
            $transfers = [];
        }

        $data = [
            'user_role' => $role,
            'user_branch' => $userBranch,
            'can_receive_transfer' => true,
            'transfers' => array_slice($transfers, 0, 50),
        ];

        // Get pending stock requests (for main branch admin)
        $isMainBranch = $this->isMainBranch($userBranch);
        $data['pending_stock_requests'] = [];
        $data['isMainBranch'] = $isMainBranch;
        
        if ($isMainBranch) {
            $stockRequestsModel = new StockRequestsModel();
            $data['pending_stock_requests'] = $stockRequestsModel
                ->select('stock_requests.*, p.part_name, p.sku, p.part_no')
                ->join('products p', 'p.product_id = stock_requests.product_id', 'left')
                ->where('stock_requests.status', 'pending')
                ->orderBy('stock_requests.created_at', 'DESC')
                ->limit(5)
                ->findAll();
        }

        return view('suppliers/admin_transfers', $data);
    }

    /**
     * Get products by branch (AJAX)
     */
    public function getProductsByBranch()
    {
        $branch = $this->request->getGet('branch');
        
        if (!$branch) {
            return $this->response->setJSON(['success' => false, 'message' => 'Branch not specified']);
        }

        $products = $this->getProductsForBranch((string) $branch);

        return $this->response->setJSON(['success' => true, 'data' => $products]);
    }

    /**
     * Get admin recipients by destination branch (AJAX)
     */
    public function getRecipientsByBranch()
    {
        $branch = trim((string) $this->request->getGet('branch'));
        if ($branch === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Branch not specified']);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $this->getRecipientUsersForBranch($branch),
        ]);
    }

    /**
     * Store product transfer
     */
    public function storeTransfer()
    {
        $role = (string) (session()->get('role') ?? '');
        if ($role !== 'super_admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Only super admin can dispatch transfers.'], 403);
        }

        $productId = $this->request->getPost('product_id');
        $fromBranch = $this->request->getPost('from_branch');
        $toBranch = $this->request->getPost('to_branch');
        $quantity = (int) $this->request->getPost('quantity');
        $remarks = $this->request->getPost('remarks');
        $recipientUserId = (int) $this->request->getPost('recipient_user_id');
        $fromBranchId = $this->findBranchIdByName((string) $fromBranch);
        $toBranchId = $this->findBranchIdByName((string) $toBranch);

        if ($recipientUserId <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Recipient is required for destination branch.'], 400);
        }

        $validRecipients = $this->getRecipientUsersForBranch((string) $toBranch);
        $recipientIds = array_map(static fn(array $user): int => (int) ($user['id'] ?? 0), $validRecipients);
        if (!in_array($recipientUserId, $recipientIds, true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Selected recipient does not belong to the destination branch.'], 400);
        }

        // Validate product exists and belongs to from_branch
        $product = $this->productsModel->find($productId);
        if (!$product) {
            return $this->response->setJSON(['success' => false, 'message' => 'Product not found'], 404);
        }

        if ($this->tableHasBranchColumn('products') && $this->normalizeBranchLabel((string) ($product['branch'] ?? '')) !== $this->normalizeBranchLabel((string) $fromBranch)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Product does not belong to the source branch'], 400);
        }

        // Validate sufficient stock
        if ($product['current_stock'] < $quantity) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Insufficient stock. Available: ' . $product['current_stock']
            ], 400);
        }

        // Validate from_branch and to_branch are different
        if ($this->normalizeBranchLabel((string) $fromBranch) === $this->normalizeBranchLabel((string) $toBranch)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Source and destination branches must be different'], 400);
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $updatedStock = (int) ($product['current_stock'] ?? 0) - $quantity;
            $this->productsModel->update((int) $productId, [
                'current_stock' => $updatedStock,
            ]);

            $sourceStockQuery = $db->table('stocks')
                ->where('product_id', (int) $productId)
                ->orderBy('stock_id', 'ASC');
            if ($this->tableHasBranchColumn('stocks')) {
                $sourceStockQuery->where('branch', $fromBranch);
            }
            $sourceStock = $sourceStockQuery->get()->getRowArray();

            if ($sourceStock) {
                $sourceRemark = trim((string) ($sourceStock['out_remarks'] ?? ''));
                $dispatchRemark = 'Dispatched ' . $quantity . ' to ' . $toBranch;
                $db->table('stocks')->where('stock_id', (int) $sourceStock['stock_id'])->update([
                    'stocks'      => max(0, (int) ($sourceStock['stocks'] ?? 0) - $quantity),
                    'items_out'   => (int) ($sourceStock['items_out'] ?? 0) + $quantity,
                    'out_remarks' => $sourceRemark !== '' ? ($sourceRemark . "\n" . $dispatchRemark) : $dispatchRemark,
                ]);
            }

            $transferData = [
                'product_id'     => $productId,
                'from_branch'    => $fromBranch,
                'from_branch_id' => $fromBranchId,
                'to_branch'      => $toBranch,
                'to_branch_id'   => $toBranchId,
                'recipient_user_id' => $recipientUserId,
                'quantity'       => $quantity,
                'status'         => 'pending',
                'remarks'        => $remarks ?: 'Awaiting receiving confirmation from ' . $toBranch,
                'transferred_by' => session()->get('user_id'),
            ];

            if (!$this->transfersModel->save($transferData)) {
                throw new \RuntimeException('Failed to create transfer.');
            }

            $transferId = (int) $this->transfersModel->getInsertID();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Failed to dispatch transfer.');
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $this->transfersModel->errors(),
            ], 400);
        }
        
        // Log history
        $this->logHistorySafe(
            'Product Transfer Dispatched',
            'Dispatched ' . $quantity . ' units of ' . $product['part_name'] . ' from ' . $fromBranch . ' to ' . $toBranch . ' (awaiting receive)',
            (int) $transferId,
            'Transfer #' . $transferId,
            null,
            'Product Transfers'
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Transfer dispatched successfully. Destination branch must receive to complete.',
            'transfer_id' => $transferId
        ]);
    }

    /**
     * Store bulk product transfers
     */
    public function storeBulkTransfer()
    {
        $role = (string) (session()->get('role') ?? '');
        $dispatcherBranch = trim((string) (session()->get('branch') ?? ''));
        $dispatcherNormalizedBranch = $this->normalizeBranchLabel($dispatcherBranch);
        if ($role !== 'super_admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Only super admin can dispatch transfers.'], 403);
        }

        $toBranch = trim((string) $this->request->getPost('to_branch'));
        $toBranchId = $this->findBranchIdByName($toBranch);
        $recipientUserId = (int) $this->request->getPost('recipient_user_id');
        $itemsRaw = (string) $this->request->getPost('items');
        $items = json_decode($itemsRaw, true);

        if ($toBranch === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Destination branch is required.'], 400);
        }

        if ($recipientUserId <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Recipient is required for destination branch.'], 400);
        }

        $validRecipients = $this->getRecipientUsersForBranch($toBranch);
        $recipientIds = array_map(static fn(array $user): int => (int) ($user['id'] ?? 0), $validRecipients);
        if (!in_array($recipientUserId, $recipientIds, true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Selected recipient does not belong to the destination branch.'], 400);
        }

        if (!is_array($items) || empty($items)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Please select at least one stock item.'], 400);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $createdCount = 0;

        foreach ($items as $item) {
            $stockId = isset($item['stock_id']) ? (int) $item['stock_id'] : 0;
            $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 0;
            $sourceBranch = trim((string) ($item['source_branch'] ?? ''));

            if ($stockId <= 0 || $quantity <= 0) {
                $db->transRollback();
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid product or quantity found in selection.'], 400);
            }

            $stock = $db->table('stocks')
                ->where('stock_id', $stockId)
                ->get()
                ->getRowArray();

            if (!$stock) {
                $db->transRollback();
                return $this->response->setJSON(['success' => false, 'message' => 'One of the selected stock rows no longer exists.'], 404);
            }

            $stockBranch = trim((string) ($stock['branch'] ?? ''));
            if ($sourceBranch === '' && $stockBranch !== '') {
                $sourceBranch = $stockBranch;
            }

            if ($dispatcherNormalizedBranch !== '') {
                $stockNormalizedBranch = $this->normalizeBranchLabel($stockBranch !== '' ? $stockBranch : $sourceBranch);
                if ($stockNormalizedBranch !== '' && $stockNormalizedBranch !== $dispatcherNormalizedBranch) {
                    $db->transRollback();
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'You can only transfer stocks from your branch (' . $dispatcherBranch . ').'
                    ], 400);
                }

                $sourceBranch = $dispatcherBranch;
            }

            $available = (int) ($stock['stocks'] ?? 0);
            if ($available < $quantity) {
                $db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Insufficient stock for ' . ($stock['part_name'] ?? 'a selected stock') . '. Available: ' . $available
                ], 400);
            }

            $productId = (int) ($stock['product_id'] ?? 0);
            if ($productId <= 0) {
                $productLookup = $db->table('products')
                    ->select('product_id')
                    ->groupStart();

                $sku = trim((string) ($stock['sku'] ?? ''));
                $partNum = trim((string) ($stock['part_num'] ?? ''));

                if ($sku !== '') {
                    $productLookup->orWhere('sku', $sku);
                }
                if ($partNum !== '') {
                    $productLookup->orWhere('part_no', $partNum);
                }

                $productLookup->groupEnd();

                if ($sourceBranch !== '' && $this->tableHasBranchColumn('products')) {
                    $productLookup->where('branch', $sourceBranch);
                }

                $resolvedProduct = $productLookup->orderBy('product_id', 'ASC')->get()->getRowArray();
                $productId = (int) ($resolvedProduct['product_id'] ?? 0);

                if ($productId > 0) {
                    $db->table('stocks')->where('stock_id', $stockId)->update(['product_id' => $productId]);
                    $stock['product_id'] = $productId;
                }
            }

            if ($productId <= 0) {
                $db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Stock "' . ($stock['part_name'] ?? ('#' . $stockId)) . '" is not linked to any product record. Please edit the stock and link it to a product first.'
                ], 400);
            }

            $sourceRemark = trim((string) ($stock['out_remarks'] ?? ''));
            $transferOutRemark = 'Dispatched ' . $quantity . ' to ' . $toBranch;
            $db->table('stocks')->where('stock_id', $stockId)->update([
                'stocks'      => max(0, $available - $quantity),
                'items_out'   => (int) ($stock['items_out'] ?? 0) + $quantity,
                'out_remarks' => $sourceRemark !== '' ? ($sourceRemark . "\n" . $transferOutRemark) : $transferOutRemark,
            ]);

            $sourceProduct = $this->productsModel->find($productId);
            if ($sourceProduct) {
                $nextProductStock = max(0, (int) ($sourceProduct['current_stock'] ?? 0) - $quantity);
                $this->productsModel->update($productId, [
                    'current_stock' => $nextProductStock,
                ]);
            }

            $saved = $this->transfersModel->save([
                'product_id'     => $productId,
                'from_branch'    => $sourceBranch !== '' ? $sourceBranch : 'Unknown',
                'from_branch_id' => $this->findBranchIdByName($sourceBranch !== '' ? $sourceBranch : 'Unknown'),
                'to_branch'      => $toBranch,
                'to_branch_id'   => $toBranchId,
                'recipient_user_id' => $recipientUserId,
                'quantity'       => $quantity,
                'status'         => 'pending',
                'remarks'        => 'Awaiting receiving confirmation from ' . $toBranch,
                'transferred_by' => session()->get('user_id'),
            ]);

            if (!$saved) {
                $db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create one of the transfer records.',
                    'errors'  => $this->transfersModel->errors(),
                ], 400);
            }

            $createdCount++;
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Bulk transfer failed. Please try again.'], 500);
        }

        $this->logHistorySafe(
            'Bulk Product Transfer Initiated',
            'Initiated ' . $createdCount . ' transfer(s) to ' . $toBranch,
            null,
            'Bulk Transfers',
            null,
            'Product Transfers'
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Dispatched ' . $createdCount . ' transfer(s). Destination branch must receive to complete.',
            'count'   => $createdCount,
        ]);
    }

    /**
     * Get transfer history
     */
    public function getTransfers()
    {
        $branch = $this->request->getGet('branch');
        $status = $this->request->getGet('status');

        $query = $this->transfersModel->builder()
            ->select('product_transfers.*, products.part_name, products.sku, suppliers.supplier_name, recipient_user.name AS recipient_name')
            ->join('products', 'products.product_id = product_transfers.product_id', 'left')
            ->join('suppliers', 'suppliers.supplier_id = products.supplier_id', 'left')
            ->join('users recipient_user', 'recipient_user.id = product_transfers.recipient_user_id', 'left');

        if ($branch) {
            $query = $query->where('product_transfers.from_branch', $branch)
                ->orWhere('product_transfers.to_branch', $branch);
        }

        if ($status) {
            $query = $query->where('product_transfers.status', $status);
        }

        $transfers = $query->orderBy('product_transfers.created_at', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['success' => true, 'data' => $transfers]);
    }

    /**
     * Update transfer status
     */
    public function updateTransferStatus()
    {
        $role = (string) (session()->get('role') ?? '');
        $userBranch = trim((string) (session()->get('branch') ?? ''));
        $transferId = $this->request->getPost('transfer_id');
        $newStatus = $this->request->getPost('status');
        $receivedQty = $this->request->getPost('received_qty');

        if (!in_array($newStatus, ['completed', 'cancelled'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid status'], 400);
        }

        $transfer = $this->transfersModel->find($transferId);
        if (!$transfer) {
            return $this->response->setJSON(['success' => false, 'message' => 'Transfer not found'], 404);
        }

        if (($transfer['status'] ?? '') !== 'pending') {
            return $this->response->setJSON(['success' => false, 'message' => 'Only pending transfers can be updated.'], 400);
        }

        if ($newStatus === 'completed') {
            if ($role !== 'admin') {
                return $this->response->setJSON(['success' => false, 'message' => 'Only destination branch admin can receive transfers.'], 403);
            }

            $userBranchId = $this->findBranchIdByName($userBranch);
            $transferToBranchId = isset($transfer['to_branch_id']) ? (int) $transfer['to_branch_id'] : null;

            if (!empty($userBranchId) && !empty($transferToBranchId) && $userBranchId !== $transferToBranchId) {
                return $this->response->setJSON(['success' => false, 'message' => 'You can only receive transfers assigned to your branch.'], 403);
            }

            $normalizedUserBranch = $this->normalizeBranchLabel($userBranch);
            $normalizedToBranch = $this->normalizeBranchLabel((string) ($transfer['to_branch'] ?? ''));
            if ($normalizedUserBranch === '' || $normalizedToBranch === '' || $normalizedUserBranch !== $normalizedToBranch) {
                return $this->response->setJSON(['success' => false, 'message' => 'You can only receive transfers assigned to your branch.'], 403);
            }
        }

        if ($newStatus === 'cancelled' && $role !== 'super_admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Only super admin can cancel dispatched transfers.'], 403);
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            if ($newStatus === 'completed') {
                $this->applyTransferReceive((array) $transfer, $receivedQty);
            }

            if ($newStatus === 'cancelled') {
                $this->revertDispatchedTransfer((array) $transfer);
            }

            $this->transfersModel->update($transferId, ['status' => $newStatus]);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Failed to update transfer status.');
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()], 400);
        }

        // Log history
        $statusText = ucfirst($newStatus);
        $this->logHistorySafe(
            'Transfer Status Updated',
            'Transfer #' . $transferId . ' status changed to ' . $statusText,
            (int) $transferId,
            'Transfer #' . $transferId,
            null,
            'Product Transfers'
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Transfer status updated successfully'
        ]);
    }

    private function applyTransferReceive(array $transfer, ?string $receivedQtyStr = null): void
    {
        $transferId = (int) ($transfer['transfer_id'] ?? 0);
        $expectedQty = (int) ($transfer['quantity'] ?? 0);
        $receivedQty = $expectedQty;
        
        // Use actual received quantity if provided
        if ($receivedQtyStr !== null && $receivedQtyStr !== '') {
            $receivedQty = (int) $receivedQtyStr;
        }
        
        $qty = $receivedQty; // Use received quantity for all stock updates
        $toBranch = trim((string) ($transfer['to_branch'] ?? ''));
        $fromBranch = trim((string) ($transfer['from_branch'] ?? ''));
        $productId = (int) ($transfer['product_id'] ?? 0);

        if ($qty <= 0 || $toBranch === '' || $productId <= 0) {
            throw new \RuntimeException('Invalid transfer details for receiving.');
        }

        $sourceProduct = $this->productsModel->find($productId);
        if (!$sourceProduct) {
            throw new \RuntimeException('Source product not found for this transfer.');
        }

        $sku = trim((string) ($sourceProduct['sku'] ?? ''));
        $partNo = trim((string) ($sourceProduct['part_no'] ?? ''));

        $destProductQuery = $this->productsModel;
        if ($this->tableHasBranchColumn('products')) {
            $destProductQuery = $destProductQuery->where('branch', $toBranch);
        }
        if ($sku !== '') {
            $destProductQuery->where('sku', $sku);
        } elseif ($partNo !== '') {
            $destProductQuery->where('part_no', $partNo);
        } else {
            $destProductQuery->where('part_name', (string) ($sourceProduct['part_name'] ?? ''));
        }

        $destProduct = $destProductQuery->first();
        $destProductId = 0;

        if ($destProduct) {
            $destProductId = (int) ($destProduct['product_id'] ?? 0);
            $this->productsModel->update($destProductId, [
                'current_stock' => (int) ($destProduct['current_stock'] ?? 0) + $qty,
            ]);
        } else {
            // SKU is globally unique in many deployments; if branch-specific row is missing,
            // reuse an existing matching product before attempting to create a new one.
            $globalDestQuery = $this->productsModel;
            if ($sku !== '') {
                $globalDestQuery->where('sku', $sku);
            } elseif ($partNo !== '') {
                $globalDestQuery->where('part_no', $partNo);
            } else {
                $globalDestQuery->where('part_name', (string) ($sourceProduct['part_name'] ?? ''));
            }

            $globalDest = $globalDestQuery->first();

            if ($globalDest) {
                $destProductId = (int) ($globalDest['product_id'] ?? 0);
                $this->productsModel->update($destProductId, [
                    'current_stock' => (int) ($globalDest['current_stock'] ?? 0) + $qty,
                ]);
            } else {
                $newProduct = [
                    'branch' => $toBranch,
                    'category_id' => $sourceProduct['category_id'] ?? null,
                    'supplier_id' => $sourceProduct['supplier_id'] ?? null,
                    'sku' => $sourceProduct['sku'] ?? null,
                    'part_no' => $sourceProduct['part_no'] ?? null,
                    'part_name' => $sourceProduct['part_name'] ?? null,
                    'price' => $sourceProduct['price'] ?? null,
                    'current_stock' => $qty,
                ];

                if (!$this->productsModel->insert($newProduct)) {
                    $errors = $this->productsModel->errors();
                    $errorText = !empty($errors) ? implode(' ', array_values($errors)) : 'Unknown validation error.';
                    throw new \RuntimeException('Unable to create destination product while receiving transfer. ' . $errorText);
                }

                $destProductId = (int) $this->productsModel->getInsertID();
            }
        }

        $db = \Config\Database::connect();
        $destStockQuery = $db->table('stocks')
            ->where('product_id', $destProductId)
            ->orderBy('stock_id', 'ASC');
        if ($this->tableHasBranchColumn('stocks')) {
            $destStockQuery->where('branch', $toBranch);
        }
        $destStock = $destStockQuery->get()->getRowArray();

        // Build remarks including variance information if quantity differs
        $inRemark = 'Received transfer #' . $transferId . ' from ' . ($fromBranch !== '' ? $fromBranch : 'source branch');
        if ($receivedQty !== $expectedQty) {
            $variance = $receivedQty - $expectedQty;
            $varianceText = $variance > 0 ? "Overage: +{$variance} units" : "Shortage: {$variance} units";
            $inRemark .= ' | ' . $varianceText;
        }
        
        if ($destStock) {
            $db->table('stocks')->where('stock_id', (int) $destStock['stock_id'])->update([
                'stocks'     => (int) ($destStock['stocks'] ?? 0) + $qty,
                'items_in'   => (int) ($destStock['items_in'] ?? 0) + $qty,
                'in_remarks' => trim((string) ($destStock['in_remarks'] ?? '')) !== ''
                    ? ((string) ($destStock['in_remarks'] ?? '') . "\n" . $inRemark)
                    : $inRemark,
            ]);
        } else {
            $insertData = [
                'product_id' => $destProductId,
                'sku' => $sourceProduct['sku'] ?? null,
                'stock_num' => null,
                'part_num' => $sourceProduct['part_no'] ?? null,
                'part_name' => $sourceProduct['part_name'] ?? null,
                'price' => $sourceProduct['price'] ?? null,
                'stocks' => $qty,
                'beg_inv' => 0,
                'items_in' => $qty,
                'in_remarks' => $inRemark,
                'items_out' => 0,
                'out_remarks' => null,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->tableHasBranchColumn('stocks')) {
                $insertData['branch'] = $toBranch;
            }

            $db->table('stocks')->insert($insertData);
        }
    }

    private function revertDispatchedTransfer(array $transfer): void
    {
        $transferId = (int) ($transfer['transfer_id'] ?? 0);
        $qty = (int) ($transfer['quantity'] ?? 0);
        $fromBranch = trim((string) ($transfer['from_branch'] ?? ''));
        $productId = (int) ($transfer['product_id'] ?? 0);

        if ($qty <= 0 || $productId <= 0) {
            throw new \RuntimeException('Invalid transfer details for cancellation.');
        }

        $sourceProduct = $this->productsModel->find($productId);
        if ($sourceProduct) {
            $this->productsModel->update($productId, [
                'current_stock' => (int) ($sourceProduct['current_stock'] ?? 0) + $qty,
            ]);
        }

        $db = \Config\Database::connect();
        $sourceStockQuery = $db->table('stocks')
            ->where('product_id', $productId)
            ->orderBy('stock_id', 'ASC');
        if ($this->tableHasBranchColumn('stocks')) {
            $sourceStockQuery->where('branch', $fromBranch);
        }
        $sourceStock = $sourceStockQuery->get()->getRowArray();

        if ($sourceStock) {
            $restoreRemark = 'Transfer #' . $transferId . ' cancelled, restored ' . $qty;
            $db->table('stocks')->where('stock_id', (int) $sourceStock['stock_id'])->update([
                'stocks'     => (int) ($sourceStock['stocks'] ?? 0) + $qty,
                'items_in'   => (int) ($sourceStock['items_in'] ?? 0) + $qty,
                'in_remarks' => trim((string) ($sourceStock['in_remarks'] ?? '')) !== ''
                    ? ((string) ($sourceStock['in_remarks'] ?? '') . "\n" . $restoreRemark)
                    : $restoreRemark,
            ]);
        }
    }

    private function normalizeBranchLabel(string $branch): string
    {
        $branch = strtolower(trim($branch));
        if ($branch === '') {
            return '';
        }

        $branch = preg_replace('/\s+branch$/i', '', $branch) ?? $branch;
        $branch = preg_replace('/\s+/', ' ', $branch) ?? $branch;

        return trim($branch);
    }

    private function canViewCrossBranchSuppliers(string $role, string $userBranch): bool
    {
        if ($role === 'super_admin') {
            return true;
        }

        if ($role !== 'admin') {
            return false;
        }

        $normalized = $this->normalizeBranchLabel($userBranch);
        return in_array($normalized, ['polangui main', 'polangui'], true);
    }

    /**
     * Resolves branch filtering mode.
     * - mode=mine   => viewer's own branch
     * - mode=all    => no branch filter
     * - mode=branch => specific branch from request
     */
    private function resolveBranchScope(string $role, string $userBranch, string $requestedBranch): array
    {
        $canCross = $this->canViewCrossBranchSuppliers($role, $userBranch);
        $requestedBranch = trim($requestedBranch);
        $requestedKey = strtolower($requestedBranch);

        if (!$canCross) {
            return [
                'mode' => 'mine',
                'branch' => $userBranch,
                'activeBranchFilter' => $userBranch,
            ];
        }

        if ($requestedKey === '' || $requestedKey === 'mine') {
            return [
                'mode' => 'mine',
                'branch' => $userBranch,
                'activeBranchFilter' => $userBranch,
            ];
        }

        if ($requestedKey === 'all') {
            return [
                'mode' => 'all',
                'branch' => null,
                'activeBranchFilter' => '',
            ];
        }

        return [
            'mode' => 'branch',
            'branch' => $requestedBranch,
            'activeBranchFilter' => $requestedBranch,
        ];
    }

    private function findBranchIdByName(string $branch): ?int
    {
        $normalized = $this->normalizeBranchLabel($branch);
        $db = \Config\Database::connect();

        if ($normalized === '' || ! $this->tableExistsByName('branches')) {
            return null;
        }

        $row = $db->table('branches')
            ->select('branch_id')
            ->where('normalized_name', $normalized)
            ->get()
            ->getRowArray();

        return $row ? (int) $row['branch_id'] : null;
    }

    private function tableExistsByName(string $table): bool
    {
        $result = \Config\Database::connect()->query('SHOW TABLES LIKE ?', [$table]);
        return $result->getNumRows() > 0;
    }

    private function getBranchOptions(): array
    {
        $db = \Config\Database::connect();

        if ($this->tableExistsByName('branches')) {
            $rows = $db->table('branches')
                ->select('branch_name')
                ->where('is_active', 1)
                ->orderBy('branch_name', 'ASC')
                ->get()
                ->getResultArray();

            if (!empty($rows)) {
                return array_column($rows, 'branch_name');
            }
        }

        $rows = $this->productsModel->select('DISTINCT(branch) as branch')
            ->where('branch !=', '')
            ->orderBy('branch', 'ASC')
            ->findAll();

        $mapped = [];
        foreach ($rows as $row) {
            $normalized = $this->normalizeBranchLabel((string) ($row['branch'] ?? ''));
            if ($normalized === '') {
                continue;
            }
            $mapped[$normalized] = ucwords($normalized) . ' Branch';
        }

        return array_values($mapped);
    }

    private function getProductsForBranch(string $branch): array
    {
        $normalized = $this->normalizeBranchLabel($branch);
        if ($normalized === '') {
            return [];
        }

        $rows = $this->productsModel
            ->select('product_id, part_name, sku, current_stock, branch')
            ->orderBy('part_name', 'ASC')
            ->findAll();

        $filtered = array_values(array_filter($rows, function (array $product) use ($normalized): bool {
            return $this->normalizeBranchLabel((string) ($product['branch'] ?? '')) === $normalized;
        }));

        return array_map(static function (array $row): array {
            unset($row['branch']);
            return $row;
        }, $filtered);
    }

    private function getRecipientUsersForBranch(string $branch): array
    {
        $normalized = $this->normalizeBranchLabel($branch);
        if ($normalized === '') {
            return [];
        }

        $db = \Config\Database::connect();
        $admins = $db->table('users')
            ->select('id, name, username, branch')
            ->where('role', 'admin')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        return array_values(array_filter($admins, function (array $admin) use ($normalized): bool {
            return $this->normalizeBranchLabel((string) ($admin['branch'] ?? '')) === $normalized;
        }));
    }

    private function isMainBranch(string $branch): bool
    {
        // Consider "Polangui Main Branch" or similar as main branch
        return stripos($branch, 'main') !== false || stripos($branch, 'polangui') !== false;
    }
}
