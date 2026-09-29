<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ExpensesModel;
use App\Models\ProductsModel;
use App\Models\SaleItemsModel;
use App\Models\SaleReturnsModel;
use App\Models\SalesModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

class PosController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access to POS.');
        }

        $model = new ProductsModel();
        $db    = \Config\Database::connect();
        $tempUpcTableExists = $db->query('SHOW TABLES LIKE ?', ['temp_sku_upc'])->getNumRows() > 0;

        $query = $db->table('products p')
            ->select('p.product_id, p.category_id, p.sku, p.part_no, p.part_name, p.description, p.price, p.current_stock, c.category_name')
            ->join('categories c', 'c.category_id = p.category_id', 'left');

        if ($tempUpcTableExists) {
            $query->select("NULLIF(TRIM(t.upc), '') AS resolved_upc", false)
                ->join('temp_sku_upc t', 't.sku = p.sku', 'left');
        } else {
            $query->select('NULL AS resolved_upc', false);
        }

        $this->applyProductBranchScope($query, 'p');

        $data['products']   = $query->groupBy('p.product_id')->orderBy('p.part_name', 'ASC')->get()->getResultArray();
        $data['categories'] = $db->table('categories')->select('category_id, category_name')->orderBy('category_name')->get()->getResultArray();
        $data['branch']     = session()->get('branch');

        return view('pos/index', $data);
    }

    /** JSON search — called via fetch as user types */
    public function search()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $role = session()->get('role');
        $q    = trim($this->request->getGet('q') ?? '');
        $db   = \Config\Database::connect();
        $tempUpcTableExists = $db->query('SHOW TABLES LIKE ?', ['temp_sku_upc'])->getNumRows() > 0;

        $query = $db->table('products p')
            ->select('p.product_id, p.sku, p.part_no, p.part_name, p.description, p.price, p.current_stock, c.category_name')
            ->join('categories c', 'c.category_id = p.category_id', 'left');

        if ($tempUpcTableExists) {
            $query->select("NULLIF(TRIM(t.upc), '') AS resolved_upc", false)
                ->join('temp_sku_upc t', 't.sku = p.sku', 'left');
        } else {
            $query->select('NULL AS resolved_upc', false);
        }

        $this->applyProductBranchScope($query, 'p');

        if ($q !== '') {
            $query->groupStart()
                ->like('p.part_name', $q)
                ->orLike('p.description', $q)
                ->orLike('p.sku', $q)
                ->orLike('p.part_no', $q);

            if ($tempUpcTableExists) {
                $query->orLike('t.upc', $q);
            }

            $query->groupEnd();
        }

        $products = $query->groupBy('p.product_id')->orderBy('p.part_name', 'ASC')->limit(40)->get()->getResultArray();

        return $this->response->setJSON($products);
    }

    /** JSON barcode lookup used by camera and USB scanner flows */
    public function scanLookup()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'status' => 'unauthorized'])->setStatusCode(401);
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return $this->response->setJSON(['success' => false, 'status' => 'forbidden'])->setStatusCode(403);
        }

        $code = trim((string) ($this->request->getGet('code') ?? ''));
        if ($code === '') {
            return $this->response->setJSON(['success' => false, 'status' => 'empty_code']);
        }

        $db = \Config\Database::connect();

        $product = $this->findProductByBarcode($db, $code, true);
        if ($product) {
            return $this->response->setJSON([
                'success' => true,
                'status'  => ((int) ($product['current_stock'] ?? 0) > 0) ? 'found' : 'out_of_stock',
                'product' => $product,
            ]);
        }

        // If not visible for current branch, tell the user it exists elsewhere.
        if ($role === 'admin') {
            $otherBranchProduct = $this->findProductByBarcode($db, $code, false);
            if ($otherBranchProduct) {
                return $this->response->setJSON([
                    'success' => false,
                    'status'  => 'other_branch',
                    'product' => [
                        'product_id' => $otherBranchProduct['product_id'] ?? null,
                        'part_name'  => $otherBranchProduct['part_name'] ?? '',
                        'sku'        => $otherBranchProduct['sku'] ?? '',
                        'branch'     => $otherBranchProduct['branch'] ?? '',
                    ],
                ]);
            }
        }

        $stockHint = $this->findStockHintByBarcode($db, $code);
        if ($stockHint) {
            return $this->response->setJSON([
                'success' => false,
                'status'  => 'stock_only',
                'stock'   => $stockHint,
            ]);
        }

        return $this->response->setJSON(['success' => false, 'status' => 'not_found']);
    }

    private function findProductByBarcode(BaseConnection $db, string $code, bool $limitToCurrentBranch = true): ?array
    {
        $variants = $this->barcodeVariants($code);
        $tempUpcTableExists = $db->query('SHOW TABLES LIKE ?', ['temp_sku_upc'])->getNumRows() > 0;

        $query = $db->table('products p')
            ->select('p.product_id, p.part_name, p.sku, p.part_no, p.price, p.current_stock, p.branch')
            ->join('categories c', 'c.category_id = p.category_id', 'left');

        if ($tempUpcTableExists) {
            $query->select("NULLIF(TRIM(t.upc), '') AS resolved_upc", false)
                ->join('temp_sku_upc t', 't.sku = p.sku', 'left');
        } else {
            $query->select('NULL AS resolved_upc', false);
        }

        if ($limitToCurrentBranch) {
            $this->applyProductBranchScope($query, 'p');
        }

        $query->groupStart()
            ->whereIn('p.sku', $variants)
            ->orWhereIn('p.part_no', $variants)
            ->orWhereIn('CAST(p.product_id AS CHAR)', $variants, false);

        if ($tempUpcTableExists) {
            $query->orWhereIn('t.upc', $variants);
        }

        $query->groupEnd();

        return $query->groupBy('p.product_id')
            ->orderBy('p.current_stock', 'DESC')
            ->get()
            ->getRowArray() ?: null;
    }

    private function findStockHintByBarcode(BaseConnection $db, string $code): ?array
    {
        $variants = $this->barcodeVariants($code);
        $tempUpcTableExists = $db->query('SHOW TABLES LIKE ?', ['temp_sku_upc'])->getNumRows() > 0;

        $skus = [];
        if ($tempUpcTableExists) {
            $rows = $db->table('temp_sku_upc')
                ->select('sku')
                ->whereIn('upc', $variants)
                ->groupBy('sku')
                ->get()
                ->getResultArray();
            foreach ($rows as $row) {
                $sku = trim((string) ($row['sku'] ?? ''));
                if ($sku !== '') {
                    $skus[] = $sku;
                }
            }
        }

        $query = $db->table('stocks s')
            ->select('s.stock_id, s.product_id, s.sku, s.part_num, s.part_name, s.stocks')
            ->groupStart()
            ->whereIn('s.sku', $variants)
            ->orWhereIn('s.part_num', $variants)
            ->orWhereIn('CAST(s.product_id AS CHAR)', $variants, false);

        if (!empty($skus)) {
            $query->orWhereIn('s.sku', $skus);
        }

        $query->groupEnd();

        $this->applyStockBranchScope($query, 's');

        return $query->orderBy('s.stocks', 'DESC')->get()->getRowArray() ?: null;
    }

    private function applyProductBranchScope(BaseBuilder $query, string $alias = 'p'): void
    {
        if (session()->get('role') !== 'admin' || !$this->tableHasBranchColumn('products')) {
            return;
        }

        $branch = trim((string) session()->get('branch'));
        if ($branch === '') {
            $query->where('1 = 0', null, false);
            return;
        }

        $query->where($alias . '.branch', $branch);
    }

    private function applyStockBranchScope(BaseBuilder $query, string $alias = 's'): void
    {
        if (session()->get('role') !== 'admin' || !$this->tableHasBranchColumn('stocks')) {
            return;
        }

        $branch = trim((string) session()->get('branch'));
        if ($branch === '') {
            $query->where('1 = 0', null, false);
            return;
        }

        $query->where($alias . '.branch', $branch);
    }

    private function barcodeVariants(string $code): array
    {
        $trimmed = trim($code);
        $digits  = preg_replace('/\D+/', '', $trimmed) ?? '';

        $variants = [$trimmed];
        if ($digits !== '') {
            $variants[] = $digits;
            $variants[] = ltrim($digits, '0');
            if (strlen($digits) === 13 && str_starts_with($digits, '0')) {
                $variants[] = substr($digits, 1);
            }
        }

        $variants = array_values(array_unique(array_filter(array_map('trim', $variants), static fn ($v) => $v !== '')));

        return !empty($variants) ? $variants : [$trimmed];
    }

    private function resolveBranchAddress(?string $branch): string
    {
        $branch = trim((string) $branch);
        if ($branch === '') {
            return '';
        }

        $normalize = static function (string $value): string {
            $value = strtolower(trim($value));
            $value = preg_replace('/\s+/', ' ', $value) ?? $value;
            $value = str_replace(' branch', '', $value);
            return trim($value);
        };

        $key = $normalize($branch);

        $addressMap = [
            'polangui' => 'BASUD POLANGUI ALBAY',
            // Add exact branch addresses here as needed.
        ];

        return $addressMap[$key] ?? $branch;
    }

    /** Process checkout — save sale + deduct stock */
    public function checkout()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(403);
        }

        $items    = $this->request->getPost('items');   // JSON string
        $payment  = (float) ($this->request->getPost('payment_amount') ?? 0);
        $method   = $this->request->getPost('payment_method') ?? 'Cash';
        $custName = trim($this->request->getPost('customer_name') ?? '');
        $custAddr = trim($this->request->getPost('customer_address') ?? '');
        $voucherCode = trim((string) ($this->request->getPost('voucher_code') ?? ''));
        $discountAmount = (float) ($this->request->getPost('discount_amount') ?? 0);

        if (empty($items)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cart is empty.']);
        }

        $itemsArr = json_decode($items, true);
        if (!$itemsArr || !is_array($itemsArr)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid cart data.']);
        }

        $model = new ProductsModel();
        $subTotal = 0;

        // Validate stock availability for every item first
        foreach ($itemsArr as $item) {
            $product = $model->find((int)$item['product_id']);
            if (!$product) {
                return $this->response->setJSON(['success' => false, 'message' => "Product ID {$item['product_id']} not found."]);
            }

            if ($role === 'admin' && $this->tableHasBranchColumn('products')) {
                $activeBranch = trim((string) (session()->get('branch') ?? ''));
                $productBranch = trim((string) ($product['branch'] ?? ''));

                if ($activeBranch === '' || strcasecmp($productBranch, $activeBranch) !== 0) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'One or more items are not available for your branch.',
                    ]);
                }
            }

            if ($product['current_stock'] < (int)$item['qty']) {
                return $this->response->setJSON(['success' => false, 'message' => "Insufficient stock for: {$product['part_name']}."]);
            }
            $subTotal += (float)$item['price'] * (int)$item['qty'];
        }

        if ($discountAmount < 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Discount cannot be negative.']);
        }

        if ($discountAmount > $subTotal) {
            return $this->response->setJSON(['success' => false, 'message' => 'Discount cannot be greater than subtotal.']);
        }

        $total = max(0, $subTotal - $discountAmount);

        if ($payment < 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Payment amount cannot be negative.']);
        }

        $amountPaid = min($payment, $total);
        $balanceDue = max(0, $total - $amountPaid);

        if ($custName === '' || $custAddr === '') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Customer name and address are required for all transactions.',
            ]);
        }

        $csi = $this->generateReceiptNo('RCP');

        // Deduct stock
        foreach ($itemsArr as $item) {
            $product = $model->find((int)$item['product_id']);
            $model->update((int)$item['product_id'], [
                'current_stock' => max(0, $product['current_stock'] - (int)$item['qty']),
            ]);

            $this->recordStockOutMovement(
                (int) $item['product_id'],
                (int) $item['qty'],
                'Customer purchase via ' . $csi
            );
        }

        // Build receipt lines for remarks
        $lines = [];
        foreach ($itemsArr as $item) {
            $sub     = (float)$item['price'] * (int)$item['qty'];
            $lines[] = "{$item['name']} x{$item['qty']} @ ₱" . number_format((float)$item['price'], 2) . " = ₱" . number_format($sub, 2);
        }
        $headerLines = [];
        if ($custName !== '') {
            $headerLines[] = 'Customer: ' . $custName;
        }
        if ($custAddr !== '') {
            $headerLines[] = 'Address: ' . $custAddr;
        }
        $headerLines[] = 'Paid: ₱' . number_format($amountPaid, 2);
        $headerLines[] = 'Balance: ₱' . number_format($balanceDue, 2);
        $headerLines[] = 'Subtotal: ₱' . number_format($subTotal, 2);
        $headerLines[] = 'Discount: ₱' . number_format($discountAmount, 2);
        $headerLines[] = 'Net Total: ₱' . number_format($total, 2);
        if ($voucherCode !== '') {
            $headerLines[] = 'Voucher: ' . $voucherCode;
        }

        $remarkStr = implode("\n", $headerLines);
        if ($remarkStr !== '') {
            $remarkStr .= "\n";
        }
        $remarkStr .= implode("\n", $lines);

        $db = \Config\Database::connect();

        $salesModel = new SalesModel();
        $saleData = [
            'csi'            => $csi,
            'sale_date'      => date('Y-m-d'),
            'cash_sales'     => $amountPaid,
            'payment_method' => $method,
            'account_receivable' => $balanceDue > 0 ? $balanceDue : null,
            'remarks'        => $remarkStr,
        ];

        if ($this->tableHasBranchColumn('sales')) {
            $saleData['branch'] = session()->get('branch');
        }
        if ($this->tableHasColumn('sales', 'customer_name')) {
            $saleData['customer_name'] = $custName !== '' ? $custName : null;
        }
        if ($this->tableHasColumn('sales', 'customer_address')) {
            $saleData['customer_address'] = $custAddr !== '' ? $custAddr : null;
        }
        if ($this->tableHasColumn('sales', 'gross_total')) {
            $saleData['gross_total'] = $subTotal;
        }
        if ($this->tableHasColumn('sales', 'discount_amount')) {
            $saleData['discount_amount'] = $discountAmount > 0 ? $discountAmount : 0;
        }
        if ($this->tableHasColumn('sales', 'net_total')) {
            $saleData['net_total'] = $total;
        }
        if ($this->tableHasColumn('sales', 'voucher_code')) {
            $saleData['voucher_code'] = $voucherCode !== '' ? $voucherCode : null;
        }
        if ($this->tableHasColumn('sales', 'sale_type')) {
            $saleData['sale_type'] = 'sale';
        }

        $saleId = $salesModel->insert($saleData, true);
        $this->persistSaleItems((int) $saleId, $itemsArr);

        $this->logHistorySafe(
            'Sale Added',
            'POS checkout created sale "' . $csi . '" with total ₱' . number_format((float) $total, 2),
            (int) $saleId,
            $csi,
            null,
            'POS'
        );

        $activeBranch = (string) session()->get('branch');

        return $this->response->setJSON([
            'success'       => true,
            'sale_id'       => $saleId,
            'receipt_no'    => $csi,
            'gross_total'   => $subTotal,
            'discount_amount' => $discountAmount,
            'net_total'     => $total,
            'voucher_code'  => $voucherCode,
            'total'         => $total,
            'tendered'      => $payment,
            'payment'       => $amountPaid,
            'balance'       => $balanceDue,
            'change'        => max(0, $payment - $total),
            'items'         => $itemsArr,
            'cashier'       => session()->get('name'),
            'branch'        => $activeBranch,
            'branch_address'=> $this->resolveBranchAddress($activeBranch),
            'date'          => date('F d, Y'),
            'time'          => date('h:i A'),
            'customer_name' => $custName,
            'customer_address' => $custAddr,
            'payment_method'=> $method,
        ]);
    }

    public function saleItemsLookup()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Forbidden'])->setStatusCode(403);
        }

        if (!$this->saleItemsTableExists()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Return module is not initialized. Run migrations first.']);
        }

        $receiptNo = trim((string) ($this->request->getGet('receipt_no') ?? ''));
        if ($receiptNo === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Receipt number is required.']);
        }

        $sale = $this->findSaleByReceiptNo($receiptNo);
        if (!$sale) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sale not found.']);
        }

        if ($role === 'admin' && $this->tableHasBranchColumn('sales') && (($sale['branch'] ?? null) !== session()->get('branch'))) {
            return $this->response->setJSON(['success' => false, 'message' => 'You can only process returns for your branch.'])->setStatusCode(403);
        }

        $db = \Config\Database::connect();
        $items = $db->table('sale_items si')
            ->select('si.sale_item_id, si.product_id, si.sku, si.part_name, si.unit_price, si.qty, si.returned_qty')
            ->where('si.sale_id', (int) $sale['sale_id'])
            ->orderBy('si.sale_item_id', 'ASC')
            ->get()
            ->getResultArray();

        // Backfill item rows for historical sales created before sale_items tracking.
        if (empty($items)) {
            $this->backfillSaleItemsFromRemarks($sale);
            $items = $db->table('sale_items si')
                ->select('si.sale_item_id, si.product_id, si.sku, si.part_name, si.unit_price, si.qty, si.returned_qty')
                ->where('si.sale_id', (int) $sale['sale_id'])
                ->orderBy('si.sale_item_id', 'ASC')
                ->get()
                ->getResultArray();
        }

        $items = array_values(array_filter(array_map(static function (array $item): ?array {
            $qty = (int) ($item['qty'] ?? 0);
            $returned = (int) ($item['returned_qty'] ?? 0);
            $remaining = max(0, $qty - $returned);
            if ($remaining <= 0) {
                return null;
            }

            $item['remaining_qty'] = $remaining;
            return $item;
        }, $items)));

        return $this->response->setJSON([
            'success' => true,
            'sale' => [
                'sale_id' => (int) $sale['sale_id'],
                'csi' => (string) ($sale['csi'] ?? ''),
                'customer_name' => (string) ($sale['customer_name'] ?? ''),
                'cash_sales' => (float) ($sale['cash_sales'] ?? 0),
                'account_receivable' => (float) ($sale['account_receivable'] ?? 0),
            ],
            'items' => $items,
        ]);
    }

    public function processReturn()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Forbidden'])->setStatusCode(403);
        }

        if (!$this->saleItemsTableExists() || !$this->saleReturnsTableExists()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Return module is not initialized. Run migrations first.']);
        }

        $receiptNo = trim((string) ($this->request->getPost('receipt_no') ?? ''));
        $itemsPayload = (string) ($this->request->getPost('items') ?? '');
        $requestedItems = json_decode($itemsPayload, true);
        $exchangeAmount = 0.0;
        $reason = trim((string) ($this->request->getPost('reason') ?? ''));

        if ($receiptNo === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Receipt is required.']);
        }

        $salesModel = new SalesModel();
        $sale = $this->findSaleByReceiptNo($receiptNo);
        if (!$sale) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sale not found.']);
        }

        if ($role === 'admin' && $this->tableHasBranchColumn('sales') && (($sale['branch'] ?? null) !== session()->get('branch'))) {
            return $this->response->setJSON(['success' => false, 'message' => 'You can only process returns for your branch.'])->setStatusCode(403);
        }

        $selectedItems = [];
        if (is_array($requestedItems) && !empty($requestedItems)) {
            foreach ($requestedItems as $entry) {
                $selectedItems[] = [
                    'sale_item_id' => (int) ($entry['sale_item_id'] ?? 0),
                    'qty' => (int) ($entry['qty'] ?? 0),
                ];
            }
        } else {
            $fallbackSaleItemId = (int) ($this->request->getPost('sale_item_id') ?? 0);
            $fallbackQty = (int) ($this->request->getPost('qty') ?? 0);
            if ($fallbackSaleItemId > 0 && $fallbackQty > 0) {
                $selectedItems[] = [
                    'sale_item_id' => $fallbackSaleItemId,
                    'qty' => $fallbackQty,
                ];
            }
        }

        if (empty($selectedItems)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Select at least one item with valid quantity.']);
        }

        $saleItemsModel = new SaleItemsModel();

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $returnsModel = new SaleReturnsModel();
            $totalReturnAmount = 0.0;
            $totalQty = 0;
            $restocked = [];
            $itemLabels = [];

            foreach ($selectedItems as $entry) {
                $saleItemId = (int) ($entry['sale_item_id'] ?? 0);
                $qty = (int) ($entry['qty'] ?? 0);
                if ($saleItemId <= 0 || $qty <= 0) {
                    throw new \RuntimeException('Invalid selected return item or quantity.');
                }

                $saleItem = $saleItemsModel
                    ->where('sale_item_id', $saleItemId)
                    ->where('sale_id', (int) $sale['sale_id'])
                    ->first();

                if (!$saleItem) {
                    throw new \RuntimeException('One selected item was not found in this receipt.');
                }

                $remainingQty = max(0, (int) ($saleItem['qty'] ?? 0) - (int) ($saleItem['returned_qty'] ?? 0));
                if ($qty > $remainingQty) {
                    throw new \RuntimeException('Return quantity exceeds available quantity for ' . ((string) ($saleItem['part_name'] ?? 'an item')) . '.');
                }

                $unitPrice = (float) ($saleItem['unit_price'] ?? 0);
                $lineReturnAmount = $unitPrice * $qty;

                $saleItemsModel->update((int) $saleItem['sale_item_id'], [
                    'returned_qty' => (int) ($saleItem['returned_qty'] ?? 0) + $qty,
                ]);

                $this->restockReturnedSaleItem(
                    $saleItem,
                    $qty,
                    'Return processed from ' . $receiptNo,
                    (string) ($sale['branch'] ?? '')
                );

                $returnsModel->insert([
                    'sale_id' => (int) $sale['sale_id'],
                    'sale_item_id' => (int) $saleItem['sale_item_id'],
                    'product_id' => (int) ($saleItem['product_id'] ?? 0),
                    'action_type' => 'return',
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'return_amount' => $lineReturnAmount,
                    'exchange_amount' => $exchangeAmount,
                    'net_adjustment' => -$lineReturnAmount,
                    'reason' => $reason !== '' ? $reason : null,
                    'processed_by' => (int) (session()->get('user_id') ?? session()->get('id') ?? 0),
                    'branch' => (string) (session()->get('branch') ?? ''),
                ]);

                $totalReturnAmount += $lineReturnAmount;
                $totalQty += $qty;
                $itemLabels[] = ((string) ($saleItem['part_name'] ?? $saleItem['sku'] ?? 'Item')) . ' x' . $qty;
                $restocked[] = [
                    'product_id' => (int) ($saleItem['product_id'] ?? 0),
                    'qty' => $qty,
                ];
            }

            $netAdjustment = -$totalReturnAmount;

            $saleAdjustment = $this->applyReturnToOriginalSale($salesModel, $sale, $totalReturnAmount, $exchangeAmount);
            $refundAmount = (float) ($saleAdjustment['refund_amount'] ?? 0);
            $arReduction = (float) ($saleAdjustment['receivable_reduction'] ?? 0);

            $adjustRef = $this->generateEventRef('RTN');
            $expenseId = null;
            if ($refundAmount > 0) {
                $expenseRemarks = 'Return from ' . $receiptNo
                    . ' | Ref: ' . $adjustRef
                    . ' | Items: ' . implode(', ', $itemLabels)
                    . ' | Returned: ₱' . number_format($totalReturnAmount, 2)
                    . ' | Balance reduced: ₱' . number_format($arReduction, 2)
                    . ' | Refund paid: ₱' . number_format($refundAmount, 2)
                    . ($reason !== '' ? ' | Reason: ' . $reason : '');

                $expenseId = $this->recordRefundExpense(
                    $refundAmount,
                    'Customer Refund - Return (' . $receiptNo . ')',
                    $expenseRemarks,
                    (string) ($sale['branch'] ?? '')
                );
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Unable to process return transaction.');
            }

            $db->transCommit();

            $this->logHistorySafe(
                'Sale Return',
                'Processed return for receipt "' . $receiptNo . '" (qty ' . $totalQty . ', balance reduced ₱' . number_format($arReduction, 2) . ', refund ₱' . number_format($refundAmount, 2) . ')',
                $expenseId !== null ? (int) $expenseId : (int) $sale['sale_id'],
                $adjustRef,
                null,
                $refundAmount > 0 ? 'Expenses' : 'POS'
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Return processed successfully.',
                'adjustment_receipt' => $adjustRef,
                'return_amount' => $totalReturnAmount,
                'refund_amount' => $refundAmount,
                'balance_reduction' => $arReduction,
                'exchange_amount' => $exchangeAmount,
                'net_adjustment' => $netAdjustment,
                'expense_id' => $expenseId,
                'restocked' => $restocked,
                'qty' => $totalQty,
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Return failed: ' . $e->getMessage(),
            ]);
        }
    }

    public function voidSale()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Forbidden'])->setStatusCode(403);
        }

        if (!$this->saleItemsTableExists() || !$this->saleReturnsTableExists()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Return module is not initialized. Run migrations first.']);
        }

        $receiptNo = trim((string) ($this->request->getPost('receipt_no') ?? ''));
        $reason = trim((string) ($this->request->getPost('reason') ?? ''));

        if ($receiptNo === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Receipt number is required.']);
        }

        $salesModel = new SalesModel();
        $sale = $this->findSaleByReceiptNo($receiptNo);
        if (!$sale) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sale not found.']);
        }

        if ($role === 'admin' && $this->tableHasBranchColumn('sales') && (($sale['branch'] ?? null) !== session()->get('branch'))) {
            return $this->response->setJSON(['success' => false, 'message' => 'You can only void sales for your branch.'])->setStatusCode(403);
        }

        $saleItemsModel = new SaleItemsModel();
        $items = $saleItemsModel->where('sale_id', (int) $sale['sale_id'])->findAll();
        if (empty($items)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No sale items found for this receipt.']);
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $returnsModel = new SaleReturnsModel();

            $voidAmount = 0.0;
            $restocked = [];

            foreach ($items as $item) {
                $qty = (int) ($item['qty'] ?? 0);
                $returned = (int) ($item['returned_qty'] ?? 0);
                $remaining = max(0, $qty - $returned);
                if ($remaining <= 0) {
                    continue;
                }

                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $lineAmount = $unitPrice * $remaining;
                $voidAmount += $lineAmount;

                $saleItemsModel->update((int) $item['sale_item_id'], [
                    'returned_qty' => $qty,
                ]);

                $this->restockReturnedSaleItem(
                    $item,
                    $remaining,
                    'Sale voided for ' . $receiptNo,
                    (string) ($sale['branch'] ?? '')
                );

                $returnsModel->insert([
                    'sale_id' => (int) $sale['sale_id'],
                    'sale_item_id' => (int) $item['sale_item_id'],
                    'product_id' => (int) ($item['product_id'] ?? 0),
                    'action_type' => 'void',
                    'qty' => $remaining,
                    'unit_price' => $unitPrice,
                    'return_amount' => $lineAmount,
                    'exchange_amount' => 0,
                    'net_adjustment' => -$lineAmount,
                    'reason' => $reason !== '' ? $reason : 'Sale voided',
                    'processed_by' => (int) (session()->get('user_id') ?? session()->get('id') ?? 0),
                    'branch' => (string) (session()->get('branch') ?? ''),
                ]);

                $restocked[] = [
                    'product_id' => (int) ($item['product_id'] ?? 0),
                    'qty' => $remaining,
                ];
            }

            if ($voidAmount <= 0) {
                throw new \RuntimeException('Sale is already fully returned/voided.');
            }

            $saleAdjustment = $this->applyReturnToOriginalSale($salesModel, $sale, $voidAmount, 0.0);
            $voidRefundAmount = (float) ($saleAdjustment['refund_amount'] ?? 0);
            $voidArReduction = (float) ($saleAdjustment['receivable_reduction'] ?? 0);

            $voidRef = $this->generateEventRef('VOID');
            $voidExpenseId = null;
            if ($voidRefundAmount > 0) {
                $voidExpenseRemarks = 'Voided receipt ' . $receiptNo
                    . ' | Ref: ' . $voidRef
                    . ' | Amount: ₱' . number_format($voidAmount, 2)
                    . ' | Balance reduced: ₱' . number_format($voidArReduction, 2)
                    . ' | Refund paid: ₱' . number_format($voidRefundAmount, 2)
                    . ($reason !== '' ? ' | Reason: ' . $reason : '');

                $voidExpenseId = $this->recordRefundExpense(
                    $voidRefundAmount,
                    'Customer Refund - Void (' . $receiptNo . ')',
                    $voidExpenseRemarks,
                    (string) ($sale['branch'] ?? '')
                );
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Unable to process sale void transaction.');
            }

            $db->transCommit();

            $this->logHistorySafe(
                'Sale Voided',
                'Voided receipt "' . $receiptNo . '" (amount ₱' . number_format($voidAmount, 2) . ', balance reduced ₱' . number_format($voidArReduction, 2) . ', refund ₱' . number_format($voidRefundAmount, 2) . ')',
                $voidExpenseId !== null ? (int) $voidExpenseId : (int) $sale['sale_id'],
                $voidRef,
                null,
                $voidRefundAmount > 0 ? 'Expenses' : 'POS'
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Sale voided successfully.',
                'void_receipt' => $voidRef,
                'void_amount' => $voidAmount,
                'refund_amount' => $voidRefundAmount,
                'balance_reduction' => $voidArReduction,
                'expense_id' => $voidExpenseId,
                'restocked' => $restocked,
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Void failed: ' . $e->getMessage(),
            ]);
        }
    }

    private function persistSaleItems(int $saleId, array $items): void
    {
        if (!$this->saleItemsTableExists() || $saleId <= 0) {
            return;
        }

        $saleItemsModel = new SaleItemsModel();
        foreach ($items as $item) {
            $saleItemsModel->insert([
                'sale_id' => $saleId,
                'product_id' => (int) ($item['product_id'] ?? 0),
                'sku' => (string) ($item['sku'] ?? ''),
                'part_name' => (string) ($item['name'] ?? ''),
                'unit_price' => (float) ($item['price'] ?? 0),
                'qty' => (int) ($item['qty'] ?? 0),
                'returned_qty' => 0,
            ]);
        }
    }

    private function recordRefundExpense(float $amount, string $description, string $remarks, string $fallbackBranch = ''): ?int
    {
        $amount = max(0, $amount);
        if ($amount <= 0) {
            return null;
        }

        $expensesModel = new ExpensesModel();
        $expenseData = [
            'expense_date' => date('Y-m-d'),
            'description' => $description,
            'amount' => $amount,
            'remarks' => $remarks,
        ];

        if ($this->tableHasBranchColumn('expenses')) {
            $branch = trim((string) (session()->get('branch') ?? ''));
            if ($branch === '') {
                $branch = trim($fallbackBranch);
            }
            $expenseData['branch'] = $branch !== '' ? $branch : null;
        }

        $expenseId = $expensesModel->insert($expenseData, true);
        if (!$expenseId) {
            $errors = $expensesModel->errors();
            $details = !empty($errors) ? implode('; ', array_values($errors)) : 'unknown expense insert failure';
            throw new \RuntimeException('Unable to record refund expense: ' . $details);
        }

        return (int) $expenseId;
    }

    private function generateEventRef(string $prefix): string
    {
        $prefix = strtoupper(trim($prefix));
        if ($prefix === '') {
            $prefix = 'REF';
        }

        return $prefix . '-' . date('Ymd-His') . '-' . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Apply return/void adjustments directly to the original sale totals.
     *
     * Credit from returns first reduces account receivable, then cash sales.
     * Any exchange top-up increases cash sales.
     */
    private function applyReturnToOriginalSale(SalesModel $salesModel, array $sale, float $returnAmount, float $exchangeAmount): array
    {
        $saleId = (int) ($sale['sale_id'] ?? 0);
        if ($saleId <= 0) {
            return [
                'refund_amount' => 0.0,
                'receivable_reduction' => 0.0,
                'cash_reduction' => 0.0,
                'extra_charge' => 0.0,
            ];
        }

        $returnAmount = max(0, $returnAmount);
        $exchangeAmount = max(0, $exchangeAmount);

        $creditAmount = max(0, $returnAmount - $exchangeAmount);
        $extraCharge = max(0, $exchangeAmount - $returnAmount);

        $currentCashSales = (float) ($sale['cash_sales'] ?? 0);
        $currentReceivable = (float) ($sale['account_receivable'] ?? 0);

        $newCashSales = $currentCashSales;
        $newReceivable = $currentReceivable;
        $receivableReduction = 0.0;
        $cashReduction = 0.0;

        if ($creditAmount > 0) {
            $receivableReduction = min($newReceivable, $creditAmount);
            $newReceivable -= $receivableReduction;

            $remainingCredit = $creditAmount - $receivableReduction;
            if ($remainingCredit > 0) {
                $cashReduction = min($newCashSales, $remainingCredit);
                $newCashSales = max(0, $newCashSales - $remainingCredit);
            }
        } elseif ($extraCharge > 0) {
            $newCashSales += $extraCharge;
        }

        $updateData = [
            'cash_sales' => $newCashSales,
            'account_receivable' => $newReceivable > 0 ? $newReceivable : null,
        ];

        if ($this->tableHasColumn('sales', 'net_total')) {
            $baseNet = array_key_exists('net_total', $sale) && $sale['net_total'] !== null
                ? (float) $sale['net_total']
                : ($currentCashSales + $currentReceivable);

            $updateData['net_total'] = max(0, $baseNet - $returnAmount + $exchangeAmount);
        }

        if ($this->tableHasColumn('sales', 'gross_total')) {
            $baseGross = array_key_exists('gross_total', $sale) && $sale['gross_total'] !== null
                ? (float) $sale['gross_total']
                : max(0, (float) ($updateData['net_total'] ?? ($currentCashSales + $currentReceivable)));

            $updateData['gross_total'] = max(0, $baseGross - $returnAmount);
        }

        $salesModel->update($saleId, $updateData);

        return [
            'refund_amount' => $cashReduction,
            'receivable_reduction' => $receivableReduction,
            'cash_reduction' => $cashReduction,
            'extra_charge' => $extraCharge,
        ];
    }

    private function appendStockRemark(?string $existing, string $note): string
    {
        $existing = trim((string) $existing);
        $note = trim($note);

        if ($note === '') {
            return $existing;
        }

        if ($existing === '') {
            return $note;
        }

        return $existing . "\n" . $note;
    }

    private function findStockRowForProduct(int $productId): ?array
    {
        if ($productId <= 0) {
            return null;
        }

        $db = \Config\Database::connect();
        $query = $db->table('stocks')
            ->where('product_id', $productId)
            ->orderBy('stock_id', 'ASC');

        if ($this->tableHasBranchColumn('stocks') && session()->get('branch')) {
            $branch = (string) session()->get('branch');
            $branchRow = $db->table('stocks')
                ->where('product_id', $productId)
                ->where('branch', $branch)
                ->orderBy('stock_id', 'ASC')
                ->get()
                ->getRowArray();

            if ($branchRow) {
                return $branchRow;
            }
        }

        return $query->get()->getRowArray() ?: null;
    }

    private function recordStockOutMovement(int $productId, int $quantity, string $note): void
    {
        if ($productId <= 0 || $quantity <= 0) {
            return;
        }

        $stock = $this->findStockRowForProduct($productId);
        if (!$stock) {
            return;
        }

        $db = \Config\Database::connect();
        $newStocks = max(0, (int) ($stock['stocks'] ?? 0) - $quantity);
        $db->table('stocks')->where('stock_id', (int) $stock['stock_id'])->update([
            'stocks'      => $newStocks,
            'items_out'   => (int) ($stock['items_out'] ?? 0) + $quantity,
            'out_remarks' => $this->appendStockRemark($stock['out_remarks'] ?? null, $note),
        ]);
    }

    private function recordStockInMovement(int $productId, int $quantity, string $note): void
    {
        if ($productId <= 0 || $quantity <= 0) {
            return;
        }

        $stock = $this->findStockRowForProduct($productId);
        if (!$stock) {
            return;
        }

        $db = \Config\Database::connect();
        $db->table('stocks')->where('stock_id', (int) $stock['stock_id'])->update([
            'stocks'      => (int) ($stock['stocks'] ?? 0) + $quantity,
            'items_in'    => (int) ($stock['items_in'] ?? 0) + $quantity,
            'in_remarks'  => $this->appendStockRemark($stock['in_remarks'] ?? null, $note),
        ]);
    }

    private function restockReturnedSaleItem(array $saleItem, int $quantity, string $note, string $preferredBranch = ''): void
    {
        if ($quantity <= 0) {
            return;
        }

        $productId = (int) ($saleItem['product_id'] ?? 0);
        if ($productId > 0) {
            $productModel = new ProductsModel();
            $product = $productModel->find($productId);
            if ($product) {
                $productModel->update($productId, [
                    'current_stock' => max(0, (int) ($product['current_stock'] ?? 0) + $quantity),
                ]);

                $this->recordStockInMovement($productId, $quantity, $note);
                return;
            }
        }

        $stockRow = $this->findStockRowBySaleItem($saleItem, $preferredBranch);
        if (!$stockRow) {
            return;
        }

        $resolvedProductId = (int) ($stockRow['product_id'] ?? 0);
        if ($resolvedProductId > 0) {
            $productModel = new ProductsModel();
            $product = $productModel->find($resolvedProductId);
            if ($product) {
                $productModel->update($resolvedProductId, [
                    'current_stock' => max(0, (int) ($product['current_stock'] ?? 0) + $quantity),
                ]);
            }
        }

        $db = \Config\Database::connect();
        $db->table('stocks')->where('stock_id', (int) $stockRow['stock_id'])->update([
            'stocks'      => (int) ($stockRow['stocks'] ?? 0) + $quantity,
            'items_in'    => (int) ($stockRow['items_in'] ?? 0) + $quantity,
            'in_remarks'  => $this->appendStockRemark($stockRow['in_remarks'] ?? null, $note),
        ]);
    }

    private function findStockRowBySaleItem(array $saleItem, string $preferredBranch = ''): ?array
    {
        $db = \Config\Database::connect();

        $productId = (int) ($saleItem['product_id'] ?? 0);
        $sku = trim((string) ($saleItem['sku'] ?? ''));
        $partName = trim((string) ($saleItem['part_name'] ?? ''));

        $baseQuery = $db->table('stocks')->select('stock_id, product_id, stocks, items_in, in_remarks');
        $matched = false;

        if ($productId > 0) {
            $baseQuery->where('product_id', $productId);
            $matched = true;
        } elseif ($sku !== '' && $this->tableHasColumn('stocks', 'sku')) {
            $baseQuery->where('sku', $sku);
            $matched = true;
        } elseif ($partName !== '' && $this->tableHasColumn('stocks', 'part_name')) {
            $baseQuery->where('part_name', $partName);
            $matched = true;
        }

        if (!$matched) {
            return null;
        }

        $branchColumn = $this->tableHasBranchColumn('stocks');
        $branch = trim((string) (session()->get('branch') ?? ''));
        if ($branch === '') {
            $branch = trim($preferredBranch);
        }

        if ($branchColumn && $branch !== '') {
            $branchQuery = clone $baseQuery;
            $branchRow = $branchQuery
                ->where('branch', $branch)
                ->orderBy('stock_id', 'ASC')
                ->get()
                ->getRowArray();

            if ($branchRow) {
                return $branchRow;
            }
        }

        return $baseQuery->orderBy('stock_id', 'ASC')->get()->getRowArray() ?: null;
    }

    private function saleItemsTableExists(): bool
    {
        try {
            return \Config\Database::connect()->tableExists('sale_items');
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function saleReturnsTableExists(): bool
    {
        try {
            return \Config\Database::connect()->tableExists('sale_returns');
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function findSaleByReceiptNo(string $receiptNo): ?array
    {
        $normalized = strtoupper(trim($receiptNo));
        if ($normalized === '') {
            return null;
        }

        $salesModel = new SalesModel();

        $sale = $salesModel->where('csi', $normalized)->first();
        if ($sale) {
            return $sale;
        }

        // Fallback for stored CSI values with spacing/casing inconsistencies.
        return $salesModel
            ->where('UPPER(TRIM(csi))', $normalized, false)
            ->first();
    }

    private function backfillSaleItemsFromRemarks(array $sale): void
    {
        if (!$this->saleItemsTableExists()) {
            return;
        }

        $saleId = (int) ($sale['sale_id'] ?? 0);
        if ($saleId <= 0) {
            return;
        }

        $remarks = (string) ($sale['remarks'] ?? '');
        if ($remarks === '') {
            return;
        }

        $lines = preg_split('/\r\n|\r|\n/', $remarks) ?: [];
        $parsedItems = [];
        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            if (!preg_match('/^(.*?)\sx(\d+)\s@\s*₱?([\d,]+(?:\.\d{1,2})?)\s=\s*₱?([\d,]+(?:\.\d{1,2})?)$/u', $line, $m)) {
                continue;
            }

            $partName = trim((string) ($m[1] ?? ''));
            $qty = (int) ($m[2] ?? 0);
            $unitPrice = (float) str_replace(',', '', (string) ($m[3] ?? '0'));
            if ($partName === '' || $qty <= 0) {
                continue;
            }

            $parsedItems[] = [
                'part_name' => $partName,
                'qty' => $qty,
                'unit_price' => $unitPrice,
            ];
        }

        if (empty($parsedItems)) {
            return;
        }

        $db = \Config\Database::connect();
        $saleItemsModel = new SaleItemsModel();
        $saleBranch = trim((string) ($sale['branch'] ?? ''));

        foreach ($parsedItems as $row) {
            $query = $db->table('products')
                ->select('product_id, sku, part_name')
                ->where('part_name', (string) $row['part_name']);

            if ($saleBranch !== '' && $this->tableHasBranchColumn('products')) {
                $query->groupStart()
                    ->where('branch', $saleBranch)
                    ->orWhere('branch IS NULL', null, false)
                    ->orWhere("TRIM(branch) = ''", null, false)
                    ->groupEnd();
            }

            $product = $query->orderBy('product_id', 'DESC')->limit(1)->get()->getRowArray();

            $saleItemsModel->insert([
                'sale_id' => $saleId,
                'product_id' => (int) ($product['product_id'] ?? 0),
                'sku' => (string) ($product['sku'] ?? ''),
                'part_name' => (string) ($row['part_name'] ?? ''),
                'unit_price' => (float) ($row['unit_price'] ?? 0),
                'qty' => (int) ($row['qty'] ?? 0),
                'returned_qty' => 0,
            ]);
        }
    }

    private function generateReceiptNo(string $prefixLabel = 'RCP'): string
    {
        $prefix = strtoupper(trim($prefixLabel)) . '-' . date('Ymd') . '-';
        $db = \Config\Database::connect();
        $last = $db->table('sales')
            ->like('csi', $prefix, 'after')
            ->orderBy('sale_id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $seq = $last ? ((int) substr((string) $last['csi'], strlen($prefix)) + 1) : 1;
        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function printers()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Forbidden'])->setStatusCode(403);
        }

        try {
            $os = strtoupper(substr(PHP_OS, 0, 3));
            if ($os === 'WIN') {
                $cmd = 'powershell -NoProfile -Command "Get-CimInstance Win32_Printer | Select-Object Name,Default | ConvertTo-Json -Compress" 2>&1';
                $raw = shell_exec($cmd) ?? '';
                $decoded = json_decode($raw, true);
                if (!is_array($decoded)) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Unable to read installed printers.', 'raw' => trim($raw)]);
                }

                // Normalize single-object output to array.
                if (isset($decoded['Name'])) {
                    $decoded = [$decoded];
                }

                $printers = [];
                $defaultPrinter = '';
                foreach ($decoded as $item) {
                    $name = trim((string) ($item['Name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $isDefault = !empty($item['Default']);
                    if ($isDefault) {
                        $defaultPrinter = $name;
                    }
                    $printers[] = ['name' => $name, 'default' => $isDefault];
                }

                return $this->response->setJSON([
                    'success' => true,
                    'printers' => $printers,
                    'default_printer' => $defaultPrinter,
                    'source_host' => gethostname() ?: php_uname('n'),
                ]);
            }

            $printers = [];
            $rawPrinters = shell_exec('lpstat -a 2>&1') ?? '';
            $rawDefault = shell_exec('lpstat -d 2>&1') ?? '';
            preg_match('/system default destination:\s*(.+)/i', $rawDefault, $m);
            $defaultPrinter = trim($m[1] ?? '');

            foreach (explode("\n", $rawPrinters) as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $name = trim(strtok($line, ' '));
                if ($name === '') {
                    continue;
                }
                $printers[] = ['name' => $name, 'default' => ($name === $defaultPrinter)];
            }

            return $this->response->setJSON([
                'success' => true,
                'printers' => $printers,
                'default_printer' => $defaultPrinter,
                'source_host' => gethostname() ?: php_uname('n'),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Printer discovery failed: ' . $e->getMessage()]);
        }
    }

    /** Direct print to system printer without browser dialog */
    public function directPrint()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Forbidden'])->setStatusCode(403);
        }

        $json = json_decode($this->request->getBody(), true);
        $html = (string) ($json['html'] ?? '');
        $text = trim((string) ($json['text'] ?? ''));
        $printerName = trim((string) ($json['printer_name'] ?? ''));
        $lineWidth = (int) ($json['line_width'] ?? 42);
        if ($lineWidth < 24) {
            $lineWidth = 24;
        }
        if ($lineWidth > 64) {
            $lineWidth = 64;
        }

        if ($html === '' && $text === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'No content to print']);
        }

        try {
            $uploadsDir = WRITEPATH . 'uploads/';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }

            if ($text === '') {
                $text = trim(html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }

            $text = preg_replace("/\r\n|\r|\n/", "\n", $text) ?? $text;
            // Strip control characters that some thermal printers can interpret as commands.
            $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text) ?? $text;
            if (strlen($text) > 20000) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Receipt content is too large to print safely.',
                ]);
            }

            // Avoid sending excessive trailing blank lines that can over-feed paper.
            $text = preg_replace("/(\n\s*){5,}$/", "\n\n", $text) ?? $text;
            $text = $this->wrapReceiptText($text, $lineWidth);
            $txtFile = $uploadsDir . 'receipt_' . time() . '_' . uniqid() . '.txt';
            file_put_contents($txtFile, $text);

            $os = strtoupper(substr(PHP_OS, 0, 3));
            $usedPrinter = $printerName;
            $rawOutput = '';

            if ($os === 'WIN') {
                $psTemplate = <<<'PS'
$ErrorActionPreference = 'Stop'
$textPath = '__TEXT_PATH__'
$requestedPrinter = '__PRINTER_NAME__'

if (-not (Test-Path $textPath)) { throw 'Print file was not created.' }

$defaultPrinter = Get-CimInstance Win32_Printer | Where-Object { $_.Default -eq $true } | Select-Object -First 1 -ExpandProperty Name
$printer = if ([string]::IsNullOrWhiteSpace($requestedPrinter)) { $defaultPrinter } else { $requestedPrinter }
if ([string]::IsNullOrWhiteSpace($printer)) { throw 'No default printer configured on server.' }

$allPrinters = Get-CimInstance Win32_Printer | Select-Object -ExpandProperty Name
if ($allPrinters -notcontains $printer) { throw ('Printer not found: ' + $printer) }

Add-Type -AssemblyName System.Drawing
$font = New-Object System.Drawing.Font('Consolas', 12)
$doc = New-Object System.Drawing.Printing.PrintDocument
$doc.PrinterSettings.PrinterName = $printer
if (-not $doc.PrinterSettings.IsValid) { throw ('Printer is invalid: ' + $printer) }

$lines = Get-Content -Path $textPath -Encoding UTF8
if ($null -eq $lines) { $lines = @('') }
if ($lines -isnot [System.Array]) { $lines = @($lines) }
$script:lineIndex = 0

$doc.add_PrintPage({
    param($sender, $e)
    $left = $e.MarginBounds.Left
    $y = $e.MarginBounds.Top
    $lineHeight = [Math]::Ceiling($font.GetHeight($e.Graphics)) + 1
    while ($script:lineIndex -lt $lines.Count) {
        $line = [string] $lines[$script:lineIndex]
        $e.Graphics.DrawString($line, $font, [System.Drawing.Brushes]::Black, $left, $y)
        $script:lineIndex++
        $y += $lineHeight
        if (($y + $lineHeight) -gt $e.MarginBounds.Bottom) {
            $e.HasMorePages = $true
            return
        }
    }
    $e.HasMorePages = $false
})

$doc.Print()
Write-Output ('PRINT_OK:' + $printer)
PS;

                $psScript = str_replace(
                    ['__TEXT_PATH__', '__PRINTER_NAME__'],
                    [str_replace("'", "''", $txtFile), str_replace("'", "''", $printerName)],
                    $psTemplate
                );

                $psFile = $uploadsDir . 'print_' . uniqid() . '.ps1';
                file_put_contents($psFile, $psScript);

                $cmd = 'powershell -NoProfile -ExecutionPolicy Bypass -File ' . escapeshellarg($psFile) . ' 2>&1';
                $rawOutput = shell_exec($cmd) ?? '';

                if (!preg_match('/PRINT_OK:(.+)/', $rawOutput, $matches)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Print job failed. ' . trim($rawOutput),
                    ]);
                }

                $usedPrinter = trim($matches[1]);
                @unlink($psFile);
            } else {
                $fileArg = escapeshellarg($txtFile);
                $printerArg = $printerName !== '' ? (' -d ' . escapeshellarg($printerName)) : '';
                if (shell_exec('which lp 2>/dev/null')) {
                    $cmd = 'lp' . $printerArg . ' ' . $fileArg . ' 2>&1';
                } else {
                    $cmd = 'lpr' . ($printerName !== '' ? (' -P ' . escapeshellarg($printerName)) : '') . ' ' . $fileArg . ' 2>&1';
                }
                $rawOutput = shell_exec($cmd) ?? '';
                if (stripos($rawOutput, 'error') !== false) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Print job failed. ' . trim($rawOutput)]);
                }
            }

            register_shutdown_function(function () use ($txtFile) {
                if (file_exists($txtFile)) {
                    @unlink($txtFile);
                }
            });

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Receipt sent to printer',
                'printer' => $usedPrinter,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Print error: ' . $e->getMessage(),
            ]);
        }
    }

    private function wrapReceiptText(string $text, int $width): string
    {
        $lines = preg_split('/\n/', $text) ?: [];
        $out = [];

        foreach ($lines as $line) {
            $line = rtrim((string) $line);
            if ($line === '') {
                $out[] = '';
                continue;
            }

            while (strlen($line) > $width) {
                $chunk = substr($line, 0, $width);
                $breakPos = strrpos($chunk, ' ');
                if ($breakPos === false || $breakPos < (int) floor($width * 0.5)) {
                    $breakPos = $width;
                }
                $out[] = rtrim(substr($line, 0, $breakPos));
                $line = ltrim(substr($line, $breakPos));
            }

            $out[] = $line;
        }

        return implode("\n", $out);
    }
}
