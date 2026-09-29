<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\StocksModel;
use App\Models\StockRequestsModel;

class StocksController extends BaseController
{
    private const TRANSPARENT_PIXEL = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

    /** Normalize SKU for resilient matching (case-insensitive, punctuation-insensitive). */
    private static function normalizeSku(?string $sku): string
    {
        $sku = strtolower(trim((string) $sku));
        return preg_replace('/[^a-z0-9]/', '', $sku) ?? '';
    }

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access to stocks.');
        }

        $stocksModel = new StocksModel();
        $search = trim((string) $this->request->getGet('search'));
        $lowOnly = (string) $this->request->getGet('low') === '1';
        $requestedBranch = trim((string) $this->request->getGet('branch'));
        $data['search'] = $search;
        $data['lowOnly'] = $lowOnly;
        $data['userRole'] = $role;
        $data['userBranch'] = (string) (session()->get('branch') ?? '');
        $data['transferSourceBranch'] = (string) (session()->get('branch') ?? '');
        $data['canCrossBranchView'] = $this->canViewCrossBranchStocks((string) $role, $data['userBranch']);

        $scope = $this->resolveBranchScope((string) $role, (string) $data['userBranch'], $requestedBranch);
        $data['activeBranchMode'] = (string) ($scope['mode'] ?? 'mine');
        $data['activeBranchFilter'] = (string) ($scope['activeBranchFilter'] ?? '');

        $db = \Config\Database::connect();
        $branches = [];

        if ($db->query('SHOW TABLES LIKE ?', ['branches'])->getNumRows() > 0) {
            $branchRows = $db->table('branches')
                ->select('branch_name')
                ->where('is_active', 1)
                ->orderBy('branch_name', 'ASC')
                ->get()
                ->getResultArray();

            $branches = array_column($branchRows, 'branch_name');
        }

        if (empty($branches)) {
            $rawBranches = $db->table('products')
                ->select('DISTINCT(branch) as branch', false)
                ->where('branch IS NOT NULL', null, false)
                ->where('branch !=', '')
                ->orderBy('branch', 'ASC')
                ->get()
                ->getResultArray();

            $mapped = [];
            foreach ($rawBranches as $row) {
                $raw = trim((string) ($row['branch'] ?? ''));
                if ($raw === '') {
                    continue;
                }

                $normalized = strtolower(trim((string) (preg_replace('/\s+branch$/i', '', preg_replace('/\s+/', ' ', $raw) ?? $raw) ?? $raw)));
                if ($normalized === '') {
                    continue;
                }

                $mapped[$normalized] = ucwords($normalized) . ' Branch';
            }

            $branches = array_values($mapped);
            sort($branches, SORT_NATURAL | SORT_FLAG_CASE);
        }

        $data['branches'] = $branches;

        $query = $this->buildStocksQuery($stocksModel, (string) $role, $search, $lowOnly, $requestedBranch);
        $data['stocks'] = $query->findAll();

        return view('stocks/index', $data);
    }

    public function store()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('stocks')->with('error', 'Unauthorized to add stocks.');
        }

        $stocksModel = new StocksModel();
        $productId = (int) ($this->request->getPost('product_id') ?: 0);

        if ($productId <= 0) {
            return redirect()->to('stocks')->with('error', 'Please select a product.');
        }

        $product = $this->getProductSnapshot($productId);
        if (!$product) {
            return redirect()->to('stocks')->with('error', 'Selected product does not exist.');
        }

        if ($role === 'admin' && !empty($product['branch']) && $product['branch'] !== session()->get('branch')) {
            return redirect()->to('stocks')->with('error', 'You can only create stocks for products in your branch.');
        }
        
        $itemsIn  = $this->request->getPost('items_in') !== '' ? (int) $this->request->getPost('items_in') : 0;
        $itemsOut = $this->request->getPost('items_out') !== '' ? (int) $this->request->getPost('items_out') : 0;
        $stocks   = $this->resolveStockTotal($this->request->getPost('stocks'), $itemsIn, $itemsOut);
        $remarksIn = trim((string) ($this->request->getPost('remarks_in') ?: ''));
        if ($itemsIn <= 0 && $stocks > 0 && $remarksIn === '') {
            $itemsIn = $stocks;
            $remarksIn = 'Initial stock entry';
        }

        $stockNum = trim((string) $this->request->getPost('no'));
        $productSku = trim((string) ($product['sku'] ?? ''));
        $productPartNo = trim((string) ($product['part_no'] ?? ''));
        $productPartName = trim((string) ($product['part_name'] ?? ''));

        $data = [
            'product_id' => (int) $product['product_id'],
            'sku'        => $productSku !== '' ? $productSku : null,
            'stock_num'  => $stockNum !== '' ? $stockNum : ($productSku !== '' ? $productSku : null),
            'part_num'   => $productPartNo !== '' ? $productPartNo : null,
            'part_name'  => $productPartName !== '' ? $productPartName : ($this->request->getPost('part_name') ?: null),
            'price'      => ($product['price'] !== null && $product['price'] !== '') ? (float) $product['price'] : null,
            'stocks'     => $stocks,
            'items_in'   => $itemsIn,
            'in_remarks' => $remarksIn !== '' ? $remarksIn : null,
            'items_out'  => $itemsOut,
            'out_remarks'=> $this->request->getPost('remarks_out') ?: null,
        ];

        $existingStock = $this->findExistingStockForProduct(
            (int) $product['product_id'],
            $productSku,
            $productPartNo
        );
        if ($existingStock) {
            $id = (int) $existingStock['stock_id'];

            // Adding stocks to an existing item should accumulate totals, not overwrite them.
            $addedQty = max(0, $itemsIn > 0 ? $itemsIn : $stocks);
            if ($addedQty <= 0) {
                return redirect()->to('stocks')->with('error', 'Please provide a quantity to add.');
            }

            $existingStocks = (int) ($existingStock['stocks'] ?? 0);
            $existingItemsIn = (int) ($existingStock['items_in'] ?? 0);
            $existingRemarksIn = trim((string) ($existingStock['in_remarks'] ?? ''));

            $data['stocks'] = $existingStocks + $addedQty;
            $data['items_in'] = $existingItemsIn + $addedQty;
            $data['in_remarks'] = $this->appendRemarks($existingRemarksIn, $remarksIn !== '' ? $remarksIn : ('Added stock: +' . $addedQty));
            $data['items_out'] = (int) ($existingStock['items_out'] ?? 0);
            $data['out_remarks'] = $existingStock['out_remarks'] ?? null;

            $stocksModel->update($id, $data);

            $this->syncProductCurrentStock($data['product_id']);

            $this->logHistorySafe(
                'Stock Updated',
                'Added +' . $addedQty . ' stock to existing entry for "' . ($data['part_name'] ?? 'Unknown') . '"',
                $id,
                $data['part_name'] ?? null,
                $data['part_num'] ?? null,
                'Stocks'
            );

            return redirect()->to('stocks')->with('success', 'Stock added successfully. Existing product stock was accumulated.');
        }

        $id = $stocksModel->insert($data, true);

        $this->syncProductCurrentStock($data['product_id']);

        $this->logHistorySafe(
            'Stock Added',
            'Added stock entry for "' . ($data['part_name'] ?? 'Unknown') . '"',
            (int) $id,
            $data['part_name'] ?? null,
            $data['part_num'] ?? null,
            'Stocks'
        );

        return redirect()->to('stocks')->with('success', 'Stock added successfully.');
    }

    public function update()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('stocks')->with('error', 'Unauthorized to update stocks.');
        }

        $id          = $this->request->getPost('id');
        $stocksModel = new StocksModel();
        
        // Verify user has access to this stock
        $stock = $stocksModel->find($id);
        if (!$stock) {
            return redirect()->to('stocks')->with('error', 'Stock not found.');
        }
        if ($role === 'admin' && !$this->stockBelongsToCurrentBranch($stock)) {
            return redirect()->to('stocks')->with('error', 'You can only update stocks from your branch.');
        }
        
        $itemsIn  = $this->request->getPost('items_in') !== '' ? (int) $this->request->getPost('items_in') : 0;
        $itemsOut = $this->request->getPost('items_out') !== '' ? (int) $this->request->getPost('items_out') : 0;
        $stocks   = $this->resolveStockTotal($this->request->getPost('stocks'), $itemsIn, $itemsOut);
        $remarksIn = trim((string) ($this->request->getPost('remarks_in') ?: ''));
        if ($itemsIn <= 0 && $stocks > 0 && $remarksIn === '') {
            $itemsIn = $stocks;
            $remarksIn = 'Initial stock entry';
        }

        $postedProductId = (int) ($this->request->getPost('product_id') ?: 0);
        $linkedProductId = $postedProductId > 0 ? $postedProductId : (int) ($stock['product_id'] ?? 0);
        $product = $linkedProductId > 0 ? $this->getProductSnapshot($linkedProductId) : null;

        if ($linkedProductId > 0 && !$product) {
            return redirect()->to('stocks')->with('error', 'Linked product was not found. Please reselect a product.');
        }

        if ($role === 'admin' && $product && !empty($product['branch']) && $product['branch'] !== session()->get('branch')) {
            return redirect()->to('stocks')->with('error', 'You can only update stocks linked to your branch products.');
        }

        $stockNum = trim((string) $this->request->getPost('no'));

        // Helper to parse price from form input
        $parsePrice = function(?string $v): ?float {
            if ($v === null || $v === '') return null;
            $v = str_replace(',', '', $v);
            return is_numeric($v) ? (float)$v : null;
        };

        $postedPrice = $parsePrice($this->request->getPost('price'));

        if ($product) {
            $productSku = trim((string) ($product['sku'] ?? ''));
            $productPartNo = trim((string) ($product['part_no'] ?? ''));
            $productPartName = trim((string) ($product['part_name'] ?? ''));

            $data = [
                'product_id'  => (int) $product['product_id'],
                'sku'         => $productSku !== '' ? $productSku : null,
                'stock_num'   => $stockNum !== '' ? $stockNum : ($productSku !== '' ? $productSku : null),
                'part_num'    => $productPartNo !== '' ? $productPartNo : null,
                'part_name'   => $productPartName !== '' ? $productPartName : ($this->request->getPost('part_name') ?: null),
                'stocks'      => $stocks,
                'items_in'    => $itemsIn,
                'in_remarks'  => $remarksIn !== '' ? $remarksIn : null,
                'items_out'   => $itemsOut,
                'out_remarks' => $this->request->getPost('remarks_out') ?: null,
            ];

            // If price is provided, update it in the product (single source of truth)
            if ($postedPrice !== null) {
                $db = \Config\Database::connect();
                $db->table('products')
                    ->where('product_id', (int) $product['product_id'])
                    ->update(['price' => $postedPrice]);
            }
        } else {
            // Legacy rows without product link remain editable (price stays in stocks for backwards compatibility)
            $data = [
                'sku'         => $this->request->getPost('sku'),
                'stock_num'   => $stockNum !== '' ? $stockNum : null,
                'part_num'    => $this->request->getPost('part_no') ?: null,
                'part_name'   => $this->request->getPost('part_name'),
                'stocks'      => $stocks,
                'items_in'    => $itemsIn,
                'in_remarks'  => $remarksIn !== '' ? $remarksIn : null,
                'items_out'   => $itemsOut,
                'out_remarks' => $this->request->getPost('remarks_out') ?: null,
            ];
        }

        $oldProductId = (int) ($stock['product_id'] ?? 0);

        $stocksModel->update($id, $data);

        $newProductId = (int) ($data['product_id'] ?? 0);

        $this->syncProductCurrentStock($newProductId);
        if ($oldProductId > 0 && $oldProductId !== $newProductId) {
            $this->syncProductCurrentStock($oldProductId);
        }

        $this->logHistorySafe(
            'Stock Updated',
            'Updated stock entry for "' . ($data['part_name'] ?? ($stock['part_name'] ?? 'Unknown')) . '"',
            (int) $id,
            $data['part_name'] ?? ($stock['part_name'] ?? null),
            $data['part_num'] ?? ($stock['part_num'] ?? null),
            'Stocks'
        );

        return redirect()->to('stocks')->with('success', 'Stock updated successfully.');
    }

    public function delete()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('stocks')->with('error', 'Unauthorized to delete stocks.');
        }

        $id          = $this->request->getPost('id');
        $stocksModel = new StocksModel();
        
        // Verify user has access to this stock
        $stock = $stocksModel->find($id);
        if (!$stock) {
            return redirect()->to('stocks')->with('error', 'Stock not found.');
        }
        if ($role === 'admin' && !$this->stockBelongsToCurrentBranch($stock)) {
            return redirect()->to('stocks')->with('error', 'You can only delete stocks from your branch.');
        }

        // Remove image files
        foreach (glob(FCPATH . 'images/stocks/st' . $id . '.*') as $file) {
            @unlink($file);
        }

        $itemName = isset($stock['part_name']) ? (string) $stock['part_name'] : 'Unknown';
        $partNo = isset($stock['part_num']) && $stock['part_num'] !== null ? (string) $stock['part_num'] : null;

        $stocksModel->delete($id);

        $this->syncProductCurrentStock((int) ($stock['product_id'] ?? 0));

        $this->logHistorySafe(
            'Stock Deleted',
            'Deleted stock entry for "' . $itemName . '"',
            (int) $id,
            $itemName,
            $partNo,
            'Stocks'
        );

        return redirect()->to('stocks')->with('success', 'Stock deleted successfully.');
    }

    public function exportCsv()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access to stocks.');
        }

        $stocksModel = new StocksModel();
        $search = trim((string) $this->request->getGet('search'));
        $lowOnly = (string) $this->request->getGet('low') === '1';
        $requestedBranch = trim((string) $this->request->getGet('branch'));
        $stocks = $this->buildStocksQuery($stocksModel, (string) $role, $search, $lowOnly, $requestedBranch)->findAll();

        $filename = 'stocks_export_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['sku', 'no', 'part_no', 'part_name', 'price', 'stocks', 'items_in', 'remarks_in', 'items_out', 'remarks_out', 'branch']);
        foreach ($stocks as $s) {
            fputcsv($out, [
                $s['sku'] ?? '',
                $s['stock_num'] ?? '',
                $s['part_num'] ?? '',
                $s['part_name'] ?? '',
                $s['price'] ?? '',
                $s['stocks'] ?? 0,
                $s['items_in'] ?? 0,
                $s['in_remarks'] ?? '',
                $s['items_out'] ?? 0,
                $s['out_remarks'] ?? '',
                $s['branch'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    public function exportPdf()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access to stocks.');
        }

        $stocksModel = new StocksModel();
        $search = trim((string) $this->request->getGet('search'));
        $lowOnly = (string) $this->request->getGet('low') === '1';
        $requestedBranch = trim((string) $this->request->getGet('branch'));
        $data = [
            'stocks' => $this->buildStocksQuery($stocksModel, (string) $role, $search, $lowOnly, $requestedBranch)->findAll(),
            'search' => $search,
            'lowOnly' => $lowOnly,
            'exportedAt' => date('Y-m-d H:i'),
        ];

        return view('stocks/export_pdf', $data);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Find the product's image file path if it exists */
    private function getProductImagePath(int $productId): ?string
    {
        $prefixes = ['p', 'pr'];
        foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $ext) {
            foreach ($prefixes as $prefix) {
                $path = FCPATH . 'images/products/' . $prefix . $productId . '.' . $ext;
                if (file_exists($path)) {
                    return $path;
                }
            }
        }
        return null;
    }

    /** Copy product image to stock folder with stock_id naming */
    private function copyProductImageToStock(int $stockId, string $productImagePath): bool
    {
        if (!file_exists($productImagePath)) {
            return false;
        }

        $ext = strtolower(pathinfo($productImagePath, PATHINFO_EXTENSION));
        $stockImagePath = FCPATH . 'images/stocks/st' . $stockId . '.' . $ext;

        // Create stocks image directory if it doesn't exist
        $stockDir = FCPATH . 'images/stocks/';
        if (!is_dir($stockDir)) {
            @mkdir($stockDir, 0755, true);
        }

        return copy($productImagePath, $stockImagePath);
    }

    public function downloadTemplate()
    {
        $headers = ['sku', 'no', 'part_no', 'part_name', 'price', 'stocks', 'items_in', 'remarks_in', 'items_out', 'remarks_out'];
        $example = ['A1', '1', '1E6C45-04000', 'AIR CLEANER ASSY 7.5 AW70', '250.00', '10', '0', '', '0', ''];

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="stocks_import_template.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        fputcsv($out, $example);
        fclose($out);
        exit;
    }

    public function import()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('stocks')->with('error', 'Unauthorized to import stocks.');
        }

        $file = $this->request->getFile('csv_file');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return redirect()->to('stocks')->with('error', 'Please upload a valid CSV file.');
        }

        $ext = strtolower($file->getExtension());
        if (!in_array($ext, ['csv', 'txt'])) {
            return redirect()->to('stocks')->with('error', 'Only CSV files are accepted.');
        }

        $path    = $file->getTempName();
        $handle  = fopen($path, 'r');
        $headers = fgetcsv($handle); // skip header row

        if (!$headers) {
            return redirect()->to('stocks')->with('error', 'CSV file is empty.');
        }

        // Normalize header names and handle UTF-8 BOM/format variants.
        $normalizeHeader = static function (?string $h): string {
            $h = (string) $h;
            $h = preg_replace('/^\xEF\xBB\xBF/', '', $h) ?? $h;
            $h = strtolower(trim($h));
            $h = str_replace(["\r", "\n", "\t"], '', $h);
            $h = str_replace([' ', '-', '.', "'", '"'], '_', $h);
            $h = preg_replace('/_+/', '_', $h) ?? $h;
            return trim($h, '_');
        };

        $headers = array_map($normalizeHeader, $headers);
        $map     = array_flip($headers);

        $stocksModel = new StocksModel();
        $inserted    = 0;
        $skipped     = 0;

        // Load products for matching (scoped to admin branch when applicable)
        $db = \Config\Database::connect();
        $productsQuery = $db->table('products')
            ->select('product_id, sku, part_name, part_no, price, branch');

        if ($role === 'admin' && $this->tableHasBranchColumn('products')) {
            $productsQuery->where('branch', session()->get('branch'));
        }

        $allProducts = $productsQuery->get()->getResultArray();

        // Create lookup maps for fast product matching
        $productBySku = [];
        $productByPartNo = [];
        $productByPartName = [];
        foreach ($allProducts as $p) {
            if ($p['sku']) {
                $skuKey = self::normalizeSku((string) $p['sku']);
                if (!isset($productBySku[$skuKey])) {
                    $productBySku[$skuKey] = $p;
                }
            }
            if ($p['part_no']) {
                $partNoKey = self::normalizeSku((string) $p['part_no']);
                if ($partNoKey !== '' && !isset($productByPartNo[$partNoKey])) {
                    $productByPartNo[$partNoKey] = $p;
                }
            }
            if ($p['part_name']) {
                $nameKey = strtolower(trim($p['part_name']));
                if (!isset($productByPartName[$nameKey])) {
                    $productByPartName[$nameKey] = $p;
                }
            }
        }

        // Helper functions
        $toFloat = function(?string $v): ?float {
            if ($v === null || $v === '') return null;
            $v = str_replace(',', '', $v);
            return is_numeric($v) ? (float)$v : null;
        };
        $toInt = function(?string $v, int $default = 0): int {
            if ($v === null || $v === '') return $default;
            $v = str_replace(',', '', $v);
            return is_numeric($v) ? (int)$v : $default;
        };

        $getByAliases = static function (array $aliases, array $row, array $map): ?string {
            foreach ($aliases as $alias) {
                if (isset($map[$alias]) && array_key_exists($map[$alias], $row)) {
                    return trim((string) $row[$map[$alias]]);
                }
            }
            return null;
        };

        while (($row = fgetcsv($handle)) !== false) {
            // Skip completely empty rows
            if (count(array_filter($row)) === 0) {
                continue;
            }

            // Read CSV fields using aliases and positional fallback to preserve CSV data.
            $csvSku = $getByAliases(['sku', 'product_sku', 'item_sku'], $row, $map);
            $csvNo = $getByAliases(['no', 'stock_no', 'stock_num', 'number'], $row, $map);
            $csvPartNo = $getByAliases(['part_no', 'part_number', 'partno'], $row, $map);
            $csvPartName = $getByAliases(['part_name', 'partname', 'description'], $row, $map);
            $csvPrice = $getByAliases(['price', 'unit_price', 'cost'], $row, $map);
            $csvStocks = $getByAliases(['stocks', 'stock', 'qty', 'quantity'], $row, $map);
            $csvItemsIn = $getByAliases(['items_in', 'item_in'], $row, $map);
            $csvItemsOut = $getByAliases(['items_out', 'item_out'], $row, $map);
            $csvRemarksIn = $getByAliases(['remarks_in', 'in_remarks', 'remarksin'], $row, $map);
            $csvRemarksOut = $getByAliases(['remarks_out', 'out_remarks', 'remarksout'], $row, $map);

            // Fallback to standard CSV column positions when header names don't match.
            if (($csvSku === null || $csvSku === '') && isset($row[0])) { $csvSku = trim((string) $row[0]); }
            if (($csvNo === null || $csvNo === '') && isset($row[1])) { $csvNo = trim((string) $row[1]); }
            if (($csvPartNo === null || $csvPartNo === '') && isset($row[2])) { $csvPartNo = trim((string) $row[2]); }
            if (($csvPartName === null || $csvPartName === '') && isset($row[3])) { $csvPartName = trim((string) $row[3]); }
            if (($csvPrice === null || $csvPrice === '') && isset($row[4])) { $csvPrice = trim((string) $row[4]); }

            $partName = $csvPartName ?? '';
            $sku      = $csvSku ?? '';

            // Match by SKU for product-image linking.
            $skuMatchedProduct = null;
            if ($sku !== '') {
                $skuKey = self::normalizeSku($sku);
                $skuMatchedProduct = $productBySku[$skuKey] ?? null;
            }

            // If SKU did not match, try part number.
            $partNoMatchedProduct = null;
            if (!$skuMatchedProduct && !empty($csvPartNo)) {
                $partNoKey = self::normalizeSku((string) $csvPartNo);
                if ($partNoKey !== '') {
                    $partNoMatchedProduct = $productByPartNo[$partNoKey] ?? null;
                }
            }

            // Optional fallback by part name for data enrichment only (not image linking).
            $matchedProduct = $skuMatchedProduct ?: $partNoMatchedProduct;
            if (!$matchedProduct && $partName !== '') {
                $nameKey = strtolower(trim($partName));
                $matchedProduct = $productByPartName[$nameKey] ?? null;
            }

            // Only assign product_id on direct SKU or part-number matches.
            $productId = ($skuMatchedProduct || $partNoMatchedProduct) && $matchedProduct
                ? (int) $matchedProduct['product_id']
                : null;
            $itemsIn  = $toInt($csvItemsIn);
            $itemsOut = $toInt($csvItemsOut);
            $stocks   = $this->resolveStockTotal($csvStocks, $itemsIn, $itemsOut);

            // Build final values: if matched, enforce product master fields.
            if ($matchedProduct) {
                $finalSku = trim((string) ($matchedProduct['sku'] ?? ''));
                $finalPartNum = trim((string) ($matchedProduct['part_no'] ?? ''));
                $finalPartName = trim((string) ($matchedProduct['part_name'] ?? ''));
                $csvPriceValue = $toFloat($csvPrice);
                // Price goes to products table if provided in CSV
                $finalPrice = $csvPriceValue;

                if ($finalSku === '') {
                    $finalSku = (string) ($csvSku ?: ($csvPartNo ?: ('SKU-' . ($csvNo ?: ($inserted + $skipped + 1)))));
                }
                if ($finalPartNum === '') {
                    $finalPartNum = (string) ($csvPartNo ?: $finalSku);
                }
                if ($finalPartName === '') {
                    $finalPartName = (string) ($csvPartName ?: ('Item ' . $finalSku));
                }
            } else {
                $finalSku = (string) ($csvSku ?: ($csvPartNo ?: ('SKU-' . ($csvNo ?: ($inserted + $skipped + 1)))));
                $finalPartNum = (string) ($csvPartNo ?: $finalSku);
                $finalPartName = (string) ($csvPartName ?: ('Item ' . $finalSku));
                $finalPrice = $toFloat($csvPrice) ?? null;
            }

            // Allow import even with partial data
            $data = [
                'product_id'  => $productId,
                'sku'         => $finalSku,
                'stock_num'   => $csvNo ?: null,
                'part_num'    => $finalPartNum,
                'part_name'   => $finalPartName,
                'stocks'      => $stocks,
                'items_in'    => $itemsIn,
                'in_remarks'  => $csvRemarksIn ?: null,
                'items_out'   => $itemsOut,
                'out_remarks' => $csvRemarksOut ?: null,
            ];

            try {
                $existingStock = $this->findExistingStockForProduct((int) ($productId ?? 0), $finalSku, $finalPartNum);
                if ($existingStock) {
                    $currentIn = (int) ($existingStock['items_in'] ?? 0);
                    $currentOut = (int) ($existingStock['items_out'] ?? 0);
                    $currentStocks = (int) ($existingStock['stocks'] ?? 0);

                    $newItemsIn = $currentIn + $itemsIn;
                    $newItemsOut = $currentOut + $itemsOut;
                    $newStocks = max(0, $currentStocks + $itemsIn - $itemsOut);

                    $updateData = [
                        'product_id'  => $productId,
                        'sku'         => $finalSku,
                        'stock_num'   => $csvNo ?: ($existingStock['stock_num'] ?? null),
                        'part_num'    => $finalPartNum,
                        'part_name'   => $finalPartName,
                        'stocks'      => $newStocks,
                        'items_in'    => $newItemsIn,
                        'items_out'   => $newItemsOut,
                        'in_remarks'  => $this->appendRemarks((string) ($existingStock['in_remarks'] ?? ''), (string) ($csvRemarksIn ?? '')),
                        'out_remarks' => $this->appendRemarks((string) ($existingStock['out_remarks'] ?? ''), (string) ($csvRemarksOut ?? '')),
                    ];

                    $stocksModel->update((int) $existingStock['stock_id'], $updateData);

                    // If price in CSV and product is linked, update product price (single source of truth)
                    if ($finalPrice !== null && !empty($productId)) {
                        $db = \Config\Database::connect();
                        $db->table('products')
                            ->where('product_id', (int) $productId)
                            ->update(['price' => $finalPrice]);
                    }

                    if (!empty($productId)) {
                        $this->syncProductCurrentStock((int) $productId);
                    }

                    $this->logHistorySafe(
                        'Stock Updated',
                        'Updated stock entry for "' . ($finalPartName ?: 'Unknown') . '" from CSV',
                        (int) $existingStock['stock_id'],
                        $finalPartName ?: null,
                        $finalPartNum ?: null,
                        'Stocks'
                    );
                    $inserted++;
                } else {
                    $stocksModel->insert($data);
                    $newStockId = $stocksModel->insertID();

                    if (!empty($productId)) {
                        // If price in CSV and product is linked, update product price (single source of truth)
                        if ($finalPrice !== null) {
                            $db = \Config\Database::connect();
                            $db->table('products')
                                ->where('product_id', (int) $productId)
                                ->update(['price' => $finalPrice]);
                        }

                        $this->syncProductCurrentStock((int) $productId);
                    }

                    $this->logHistorySafe(
                        'Stock Added',
                        'Imported stock entry for "' . ($finalPartName ?: 'Unknown') . '" from CSV',
                        (int) $newStockId,
                        $finalPartName ?: null,
                        $finalPartNum ?: null,
                        'Stocks'
                    );

                    // Copy product image when import has a direct product match.
                    if ($productId) {
                        $productImagePath = $this->getProductImagePath((int) $productId);
                        if ($productImagePath) {
                            $this->copyProductImageToStock($newStockId, $productImagePath);
                        }
                    }

                    $inserted++;
                }
            } catch (\Exception $e) {
                $skipped++;
                log_message('error', 'Stock import error row ' . ($inserted + $skipped) . ': ' . $e->getMessage());
            }
        }
        fclose($handle);

        $msg = "$inserted stock(s) imported successfully.";
        if ($skipped > 0) {
            $msg .= " ($skipped row(s) skipped)";
        }

        return redirect()->to('stocks')->with('success', $msg);
    }

    /** Fetch product fields that must stay aligned in stocks rows. */
    private function getProductSnapshot(int $productId): ?array
    {
        if ($productId <= 0) {
            return null;
        }

        return \Config\Database::connect()
            ->table('products')
            ->select('product_id, branch, sku, part_no, part_name, price')
            ->where('product_id', $productId)
            ->get()
            ->getRowArray() ?: null;
    }

    /** Find an existing stock row for a product by linkage or by identifier match. */
    private function findExistingStockForProduct(int $productId, ?string $sku = null, ?string $partNo = null): ?array
    {
        $sku = trim((string) $sku);
        $partNo = trim((string) $partNo);

        if ($productId <= 0 && $sku === '' && $partNo === '') {
            return null;
        }

        $db = \Config\Database::connect();

        if ($productId > 0) {
            $linked = $db->table('stocks')
                ->select('stock_id, product_id, sku, part_num, stocks, items_in, items_out, in_remarks, out_remarks')
                ->where('product_id', $productId)
                ->orderBy('stock_id', 'ASC')
                ->get()
                ->getRowArray();

            if ($linked) {
                return $linked;
            }
        }

        $query = $db->table('stocks')
            ->select('stock_id, product_id, sku, part_num, stocks, items_in, items_out, in_remarks, out_remarks')
            ->groupStart();

        if ($sku !== '') {
            $query->orWhere('sku', $sku);
        }
        if ($partNo !== '') {
            $query->orWhere('part_num', $partNo);
        }

        $candidates = $query->groupEnd()
            ->orderBy('stock_id', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($candidates)) {
            return null;
        }

        // Prefer exact identifier matches when linking legacy unlinked rows.
        foreach ($candidates as $row) {
            if ($sku !== '' && trim((string) ($row['sku'] ?? '')) === $sku) {
                return $row;
            }
            if ($partNo !== '' && trim((string) ($row['part_num'] ?? '')) === $partNo) {
                return $row;
            }
        }

        return $candidates[0] ?? null;
    }

    /** Use the submitted total when present, otherwise derive it from inventory fields. */
    private function resolveStockTotal(?string $postedStocks, int $itemsIn, int $itemsOut): int
    {
        if ($postedStocks !== null && $postedStocks !== '') {
            return max(0, (int) $postedStocks);
        }

        return max(0, $itemsIn - $itemsOut);
    }

    /** Recalculate the product's current stock from all linked stock rows. */
    private function syncProductCurrentStock(int $productId): void
    {
        if ($productId <= 0 || !$this->tableHasColumn('products', 'current_stock')) {
            return;
        }

        $db = \Config\Database::connect();

        $product = $db->table('products')
            ->select('product_id, sku, part_no')
            ->where('product_id', $productId)
            ->get()
            ->getRowArray();

        if (!$product) {
            return;
        }

        $query = $db->table('stocks')
            ->select('COALESCE(SUM(stocks), 0) AS total', false)
            ->groupStart()
            ->where('product_id', $productId);

        // Legacy support: include unlinked rows that clearly belong to this product.
        $sku = trim((string) ($product['sku'] ?? ''));
        if ($sku !== '') {
            $query->orGroupStart()
                ->where('product_id IS NULL', null, false)
                ->where('sku', $sku)
                ->groupEnd();
        }

        $partNo = trim((string) ($product['part_no'] ?? ''));
        if ($partNo !== '') {
            $query->orGroupStart()
                ->where('product_id IS NULL', null, false)
                ->where('part_num', $partNo)
                ->groupEnd();
        }

        $row = $query->groupEnd()
            ->get()
            ->getRowArray();

        $db->table('products')
            ->where('product_id', $productId)
            ->update(['current_stock' => (int) ($row['total'] ?? 0)]);
    }

    private function appendRemarks(string $existing, string $incoming): string
    {
        $existing = trim($existing);
        $incoming = trim($incoming);

        if ($incoming === '') {
            return $existing;
        }

        if ($existing === '') {
            return $incoming;
        }

        return $existing . PHP_EOL . $incoming;
    }

    /** Returns the image URL for a stock_id, or a product image if product_id exists, or lookup by SKU, or a default placeholder. */
    public static function stockImageUrl(int $stockId, ?int $productId = null, ?string $sku = null): string
    {
        // If product_id provided, try to show product image
        if ($productId) {
            $productImageUrl = self::productImageUrl($productId);
            if ($productImageUrl !== '/images/products/default.png') {
                return $productImageUrl;
            }
        }
        
        // If SKU provided, try to find product by SKU
        if ($sku) {
            // Static cache to avoid re-querying products per row
            static $productSkuMap = null;
            
            if ($productSkuMap === null) {
                // Load all products once and build SKU -> product_id map
                $db = \Config\Database::connect();
                $products = $db->table('products')
                    ->select('product_id, sku')
                    ->get()
                    ->getResultArray();
                
                $productSkuMap = [];
                foreach ($products as $p) {
                    if ($p['sku']) {
                        $skuKey = self::normalizeSku((string) $p['sku']);
                        if (!isset($productSkuMap[$skuKey])) {
                            $productSkuMap[$skuKey] = (int)$p['product_id'];
                        }
                    }
                }
            }
            
            $skuKey = self::normalizeSku($sku);
            if (isset($productSkuMap[$skuKey])) {
                $productImageUrl = self::productImageUrl($productSkuMap[$skuKey]);
                if ($productImageUrl !== '/images/products/default.png') {
                    return $productImageUrl;
                }
            }
        }
        
        // Fallback to stock image
        foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $ext) {
            if (file_exists(FCPATH . 'images/stocks/st' . $stockId . '.' . $ext)) {
                return '/images/stocks/st' . $stockId . '.' . $ext;
            }
        }
        if (file_exists(FCPATH . 'images/stocks/default.png')) {
            return '/images/stocks/default.png';
        }
        if (file_exists(FCPATH . 'images/products/default.png')) {
            return '/images/products/default.png';
        }
        return self::TRANSPARENT_PIXEL;
    }

    /** Returns the image URL for a product_id from the products database */
    public static function productImageUrl(int $productId): string
    {
        $prefixes = ['p', 'pr'];
        foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $ext) {
            foreach ($prefixes as $prefix) {
                if (file_exists(FCPATH . 'images/products/' . $prefix . $productId . '.' . $ext)) {
                    return '/images/products/' . $prefix . $productId . '.' . $ext;
                }
            }
        }
        if (file_exists(FCPATH . 'images/products/default.png')) {
            return '/images/products/default.png';
        }
        return self::TRANSPARENT_PIXEL;
    }

    private function buildStocksQuery(StocksModel $stocksModel, string $role, string $search, bool $lowOnly = false, string $requestedBranch = '')
    {
        $db = \Config\Database::connect();
        $tempUpcTableExists = $db->query('SHOW TABLES LIKE ?', ['temp_sku_upc'])->getNumRows() > 0;
        $productsHasSku = $this->tableHasColumn('products', 'sku');
        $productsHasBranch = $this->tableHasBranchColumn('products');
        $stocksHasBranch = $this->tableHasBranchColumn('stocks');
        $userBranch = (string) (session()->get('branch') ?? '');
        $scope = $this->resolveBranchScope($role, $userBranch, $requestedBranch);
        $targetBranch = isset($scope['branch']) ? (string) $scope['branch'] : '';

        $selectParts = ['stocks.*'];
        $selectParts[] = 'COALESCE(p.price, NULL) AS price';  // Get price from products table
        $selectParts[] = $this->tableHasColumn('products', 'barcode_type')
            ? 'p.barcode_type AS product_barcode_type'
            : 'NULL AS product_barcode_type';
        $selectParts[] = $this->tableHasColumn('products', 'label_brand')
            ? 'p.label_brand AS product_label_brand'
            : 'NULL AS product_label_brand';
        $selectParts[] = $this->tableHasColumn('products', 'label_short_name')
            ? 'p.label_short_name AS product_label_short_name'
            : 'NULL AS product_label_short_name';
        $selectParts[] = $productsHasBranch
            ? 'p.branch AS product_branch'
            : 'NULL AS product_branch';

        $query = $stocksModel
            ->select(implode(', ', $selectParts), false)
            ->join('products p', 'p.product_id = stocks.product_id', 'left');

        if ($tempUpcTableExists && $productsHasSku) {
            $query->select("(SELECT MIN(NULLIF(TRIM(tsu.upc), '')) FROM temp_sku_upc tsu WHERE tsu.sku = p.sku) AS product_upc", false);
        } else {
            $query->select('NULL AS product_upc', false);
        }

        $query = $query->orderBy('CAST(stocks.stock_num AS UNSIGNED)', 'ASC', false)
            ->orderBy('stock_id', 'ASC');

        if ($targetBranch !== '') {
            if ($productsHasBranch && $stocksHasBranch) {
                $query->groupStart()
                    ->where('p.branch', $targetBranch)
                    ->orWhere('stocks.branch', $targetBranch)
                    ->groupEnd();
            } elseif ($productsHasBranch) {
                $query->where('p.branch', $targetBranch);
            } elseif ($stocksHasBranch) {
                $query->where('stocks.branch', $targetBranch);
            }
        }

        if ($search !== '') {
            $query->groupStart()
                ->like('stocks.sku', $search)
                ->orLike('stocks.part_name', $search)
                ->orLike('stocks.part_num', $search)
                ->orLike('stocks.stock_num', $search)
                ->groupEnd();
        }

        if ($lowOnly) {
            $query->where('stocks.stocks <=', 10);
        }

        return $query;
    }

    private function stockBelongsToCurrentBranch(array $stock): bool
    {
        $currentBranch = (string) (session()->get('branch') ?? '');
        if ($currentBranch === '') {
            return false;
        }

        $productId = (int) ($stock['product_id'] ?? 0);
        if ($productId > 0 && $this->tableHasBranchColumn('products')) {
            $product = $this->getProductSnapshot($productId);
            if ($product && !empty($product['branch'])) {
                return (string) $product['branch'] === $currentBranch;
            }
        }

        if ($this->tableHasBranchColumn('stocks') && !empty($stock['branch'])) {
            return (string) $stock['branch'] === $currentBranch;
        }

        // When no branch metadata exists, keep legacy behavior and do not block edits/deletes.
        return true;
    }

    private function normalizeBranchLabel(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;
        $value = preg_replace('/\s+branch$/i', '', $value) ?? $value;

        return trim($value);
    }

    private function canViewCrossBranchStocks(string $role, string $userBranch): bool
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
     * Resolves stock branch filtering mode.
     * - mode=mine   => viewer's own branch
     * - mode=all    => no branch filter
     * - mode=branch => specific branch from request
     */
    private function resolveBranchScope(string $role, string $userBranch, string $requestedBranch): array
    {
        $canCross = $this->canViewCrossBranchStocks($role, $userBranch);
        $requestedBranch = trim($requestedBranch);
        $requestedKey = strtolower($requestedBranch);

        if (! $canCross) {
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

    // ── Stock Requests ───────────────────────────────────────────────────────

    public function requests()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access to stock requests.');
        }

        $userBranch = (string) (session()->get('branch') ?? '');
        $isMainBranch = $this->isMainBranch($userBranch);

        $db = \Config\Database::connect();
        $stockRequestsModel = new StockRequestsModel();

        // Build query for stock requests
        $query = $stockRequestsModel
            ->select('stock_requests.*, p.part_name, p.sku, p.part_no, p.price, p.branch as product_branch')
            ->join('products p', 'p.product_id = stock_requests.product_id', 'left')
            ->orderBy('stock_requests.created_at', 'DESC');

        // Filter based on user role and branch
        if ($role === 'admin' && !$isMainBranch) {
            // Branch admin can only see their own requests
            $query->where('stock_requests.requesting_branch', $userBranch);
        }
        // Super admin and main branch can see all requests

        $data['requests'] = $query->findAll();
        $data['userRole'] = $role;
        $data['userBranch'] = $userBranch;
        $data['isMainBranch'] = $isMainBranch;

        return view('stocks/requests', $data);
    }

    public function requestStock()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('stocks')->with('error', 'Unauthorized to request stocks.');
        }

        $userBranch = (string) (session()->get('branch') ?? '');
        if ($this->isMainBranch($userBranch)) {
            return redirect()->to('stocks')->with('error', 'Main branch cannot request stocks from itself.');
        }

        $productId = (int) ($this->request->getPost('product_id') ?: 0);
        $quantity = (int) ($this->request->getPost('quantity') ?: 0);
        $reason = trim((string) ($this->request->getPost('reason') ?: ''));

        if ($productId <= 0) {
            return redirect()->to('stocks')->with('error', 'Please select a product.');
        }

        if ($quantity <= 0) {
            return redirect()->to('stocks')->with('error', 'Please specify a valid quantity.');
        }

        // Verify product exists and is available
        $product = $this->getProductSnapshot($productId);
        if (!$product) {
            return redirect()->to('stocks')->with('error', 'Selected product does not exist.');
        }

        $stockRequestsModel = new StockRequestsModel();

        $data = [
            'requesting_branch' => $userBranch,
            'product_id' => $productId,
            'requested_quantity' => $quantity,
            'reason' => $reason ?: null,
            'status' => 'pending',
            'requested_by' => session()->get('username') ?: session()->get('email'),
        ];

        $stockRequestsModel->insert($data);

        $this->logHistorySafe(
            'Stock Request Created',
            'Requested ' . $quantity . ' units of "' . ($product['part_name'] ?? 'Unknown') . '" from main branch',
            null,
            $product['part_name'] ?? null,
            $product['part_no'] ?? null,
            'Stock Requests'
        );

        return redirect()->to('stocks')->with('success', 'Stock request submitted successfully. Main branch will review your request.');
    }

    public function approveRequest()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        $userBranch = (string) (session()->get('branch') ?? '');

        if (!in_array($role, ['admin', 'super_admin']) || !$this->isMainBranch($userBranch)) {
            return redirect()->to('stocks/requests')->with('error', 'Unauthorized to approve stock requests.');
        }

        $requestId = (int) ($this->request->getPost('request_id') ?: 0);
        $stockRequestsModel = new StockRequestsModel();

        $request = $stockRequestsModel->find($requestId);
        if (!$request) {
            return redirect()->to('stocks/requests')->with('error', 'Request not found.');
        }

        if ($request['status'] !== 'pending') {
            return redirect()->to('stocks/requests')->with('error', 'Request has already been processed.');
        }

        // Update request status
        $stockRequestsModel->update($requestId, [
            'status' => 'approved',
            'approved_by' => session()->get('username') ?: session()->get('email'),
        ]);

        $product = $this->getProductSnapshot((int) $request['product_id']);

        $this->logHistorySafe(
            'Stock Request Approved',
            'Approved request for ' . $request['requested_quantity'] . ' units of "' . ($product['part_name'] ?? 'Unknown') . '" to ' . $request['requesting_branch'],
            $requestId,
            $product['part_name'] ?? null,
            $product['part_no'] ?? null,
            'Stock Requests'
        );

        return redirect()->to('stocks/requests')->with('success', 'Stock request approved. You can now create a transfer to fulfill this request.');
    }

    public function fulfillRequest()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        $userBranch = (string) (session()->get('branch') ?? '');

        if (!in_array($role, ['admin', 'super_admin']) || !$this->isMainBranch($userBranch)) {
            return redirect()->to('stocks/requests')->with('error', 'Unauthorized to fulfill stock requests.');
        }

        $requestId = (int) ($this->request->getPost('request_id') ?: 0);
        $stockRequestsModel = new StockRequestsModel();

        $request = $stockRequestsModel->find($requestId);
        if (!$request) {
            return redirect()->to('stocks/requests')->with('error', 'Request not found.');
        }

        if ($request['status'] !== 'approved') {
            return redirect()->to('stocks/requests')->with('error', 'Request must be approved before it can be fulfilled.');
        }

        // Mark as fulfilled
        $stockRequestsModel->update($requestId, [
            'status' => 'fulfilled',
            'fulfilled_at' => date('Y-m-d H:i:s'),
        ]);

        $product = $this->getProductSnapshot((int) $request['product_id']);

        $this->logHistorySafe(
            'Stock Request Fulfilled',
            'Fulfilled request for ' . $request['requested_quantity'] . ' units of "' . ($product['part_name'] ?? 'Unknown') . '" to ' . $request['requesting_branch'],
            $requestId,
            $product['part_name'] ?? null,
            $product['part_no'] ?? null,
            'Stock Requests'
        );

        return redirect()->to('stocks/requests')->with('success', 'Stock request marked as fulfilled.');
    }

    public function cancelRequest()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        $userBranch = (string) (session()->get('branch') ?? '');

        $requestId = (int) ($this->request->getPost('request_id') ?: 0);
        $stockRequestsModel = new StockRequestsModel();

        $request = $stockRequestsModel->find($requestId);
        if (!$request) {
            return redirect()->to('stocks/requests')->with('error', 'Request not found.');
        }

        // Check permissions: requestor can cancel their own pending requests, main branch can cancel any
        $canCancel = false;
        if ($request['status'] === 'pending') {
            if ($role === 'super_admin' || $this->isMainBranch($userBranch)) {
                $canCancel = true;
            } elseif ($role === 'admin' && $request['requesting_branch'] === $userBranch) {
                $canCancel = true;
            }
        }

        if (!$canCancel) {
            return redirect()->to('stocks/requests')->with('error', 'Unauthorized to cancel this request.');
        }

        $stockRequestsModel->update($requestId, ['status' => 'cancelled']);

        $product = $this->getProductSnapshot((int) $request['product_id']);

        $this->logHistorySafe(
            'Stock Request Cancelled',
            'Cancelled request for ' . $request['requested_quantity'] . ' units of "' . ($product['part_name'] ?? 'Unknown') . '" from ' . $request['requesting_branch'],
            $requestId,
            $product['part_name'] ?? null,
            $product['part_no'] ?? null,
            'Stock Requests'
        );

        return redirect()->to('stocks/requests')->with('success', 'Stock request cancelled.');
    }

    private function isMainBranch(string $branch): bool
    {
        // Consider "Polangui Main Branch" or similar as main branch
        return stripos($branch, 'main') !== false || stripos($branch, 'polangui') !== false;
    }
}

