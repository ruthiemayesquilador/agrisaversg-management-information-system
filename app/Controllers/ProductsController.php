<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductsModel;
use App\Models\CategoriesModel;
use App\Models\StocksModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use ZipArchive;

class ProductsController extends BaseController
{
    protected ProductsModel   $model;
    protected CategoriesModel $catModel;


    public function __construct()
    {
        $this->model    = new ProductsModel();
        $this->catModel = new CategoriesModel();
    }

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        $userBranch = (string) (session()->get('branch') ?? '');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access to products.');
        }

        // Join products with categories and suppliers
        $db = \Config\Database::connect();
        $productsHasBranch = $this->tableHasBranchColumn('products');
        $productsHasStatus = $this->tableHasColumn('products', 'status');
        $tempUpcTableExists = $db->query('SHOW TABLES LIKE ?', ['temp_sku_upc'])->getNumRows() > 0;
        $query = $db->table('products p')
            ->select('p.*, c.category_name, s.supplier_name')
            ->join('categories c', 'c.category_id = p.category_id', 'left')
            ->join('suppliers s', 's.supplier_id = p.supplier_id', 'left');

        if ($tempUpcTableExists) {
            $query->select("(SELECT MIN(NULLIF(TRIM(tsu.upc), '')) FROM temp_sku_upc tsu WHERE tsu.sku = p.sku) AS resolved_upc", false);
        } else {
            $query->select('NULL AS resolved_upc', false);
        }
        
        // Filter by branch if admin user
        $requestedBranch = trim((string) $this->request->getGet('branch'));
        $activeBranchFilter = '';
        $activeBranchMode = 'all';
        $canCrossBranchView = $this->canViewCrossBranchProducts((string) $role, $userBranch);

        if ($productsHasBranch) {
            $scope = $this->resolveBranchScope((string) $role, $userBranch, $requestedBranch);
            if (($scope['branch'] ?? null) !== null) {
                $query->where('p.branch', (string) $scope['branch']);
            }

            $activeBranchFilter = (string) ($scope['activeBranchFilter'] ?? '');
            $activeBranchMode = (string) ($scope['mode'] ?? 'all');
        }
        
        $products = $query->orderBy('p.part_name', 'ASC')->get()->getResultArray();

        $categories = $this->catModel->orderBy('category_name', 'ASC')->findAll();
        $suppliers  = $db->table('suppliers')->select('supplier_id, supplier_name')->orderBy('supplier_name', 'ASC')->get()->getResultArray();

        return view('products/index', [
            'products' => $products,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'userBranch' => $userBranch,
            'currentRole' => (string) $role,
            'canCrossBranchView' => $canCrossBranchView,
            'availableBranches' => $canCrossBranchView ? $this->getAvailableProductBranches() : [],
            'activeBranchFilter' => $activeBranchFilter,
            'activeBranchMode' => $activeBranchMode,
        ]);
    }

    public function store()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('products')->with('error', 'Unauthorized to add products.');
        }

        $begInv = (int) $this->request->getPost('beg_inv') ?? 0;
        $data = [
            'category_id'   => (int) $this->request->getPost('category_id'),
            'supplier_id'   => ($this->request->getPost('supplier_id') !== '' ? (int) $this->request->getPost('supplier_id') : null),
            'sku'           => trim($this->request->getPost('sku')) ?: null,
            'part_no'       => trim($this->request->getPost('part_no')) ?: null,
            'part_name'     => trim($this->request->getPost('product_name')),
            'price'         => (float) $this->request->getPost('price'),
            'current_stock' => $begInv, // Initially set current_stock equal to beginning inventory
        ];
        if ($this->tableHasColumn('products', 'description')) {
            $data['description'] = trim((string) $this->request->getPost('description')) ?: null;
        }
        if ($this->tableHasColumn('products', 'label_brand')) {
            $data['label_brand'] = trim((string) $this->request->getPost('label_brand')) ?: null;
        }
        if ($this->tableHasColumn('products', 'label_short_name')) {
            $data['label_short_name'] = trim((string) $this->request->getPost('label_short_name')) ?: null;
        }
        if ($this->tableHasBranchColumn('products')) {
            $data['branch'] = session()->get('branch');
        }

        $branchScope = $this->tableHasBranchColumn('products') ? (string) (session()->get('branch') ?? '') : null;
        $duplicateSku = $this->findDuplicateSku($data['sku'], null, $branchScope);
        if ($duplicateSku) {
            return redirect()->to('products')->with('error', 'SKU "' . $data['sku'] . '" already exists. Please use a unique SKU.');
        }

        $duplicatePartNo = $this->findDuplicatePartNo($data['part_no'], null, $branchScope);
        if ($duplicatePartNo) {
            return redirect()->to('products')->with('error', 'Part No. "' . $data['part_no'] . '" already exists. Please use a unique Part No.');
        }

        try {
            $id = $this->model->insert($data, true);
        } catch (DatabaseException $e) {
            if (stripos($e->getMessage(), 'Duplicate entry') !== false && stripos($e->getMessage(), 'sku') !== false) {
                return redirect()->to('products')->with('error', 'SKU "' . ($data['sku'] ?? '') . '" already exists. Please use a unique SKU.');
            }

            throw $e;
        }

        // Create initial Stocks entry with the beginning inventory
        if ($begInv > 0) {
            $this->createInitialStockEntry($id, $begInv, $data);
        }

        // Handle image upload
        $image = $this->request->getFile('product_image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            try {
                // Ensure upload directory exists and is writable
                $uploadDir = FCPATH . 'images/products';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                
                $ext = $image->getExtension();
                if (!$image->move($uploadDir, 'p' . $id . '.' . $ext, true)) {
                    log_message('error', 'Failed to move product image for product ID: ' . $id);
                }
            } catch (\Exception $e) {
                log_message('error', 'Error handling product image upload: ' . $e->getMessage());
            }
        }

        $this->logHistorySafe(
            'Product Added',
            'Added product "' . ($data['part_name'] ?? 'Unknown') . '"',
            (int) $id,
            $data['part_name'] ?? null,
            $data['part_no'] ?? null,
            'Products'
        );

        return redirect()->to('products')->with('success', 'Product added successfully.');
    }

    public function update()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('products')->with('error', 'Unauthorized to update products.');
        }

        $id   = (int) $this->request->getPost('product_id');
        
        // Verify user has access to this product
        $product = $this->model->find($id);
        if (!$product) {
            return redirect()->to('products')->with('error', 'Product not found.');
        }
        if ($role === 'admin' && $this->tableHasBranchColumn('products') && (($product['branch'] ?? null) !== session()->get('branch'))) {
            return redirect()->to('products')->with('error', 'You can only update products from your branch.');
        }


        $data = [
            'category_id'   => (int) $this->request->getPost('category_id'),
            'supplier_id'   => ($this->request->getPost('supplier_id') !== '' ? (int) $this->request->getPost('supplier_id') : null),
            'sku'           => trim($this->request->getPost('sku')) ?: null,
            'part_no'       => trim($this->request->getPost('part_no')) ?: null,
            'part_name'     => trim($this->request->getPost('product_name')),
            'price'         => (float) $this->request->getPost('price'),
            // Note: current_stock and beg_inv are NOT updated here - they are managed by the Stocks module
        ];
        if ($this->tableHasColumn('products', 'description')) {
            $data['description'] = trim((string) $this->request->getPost('description')) ?: null;
        }
        if ($this->tableHasColumn('products', 'label_brand')) {
            $data['label_brand'] = trim((string) $this->request->getPost('label_brand')) ?: null;
        }
        if ($this->tableHasColumn('products', 'label_short_name')) {
            $data['label_short_name'] = trim((string) $this->request->getPost('label_short_name')) ?: null;
        }

        $branchScope = $this->tableHasBranchColumn('products')
            ? (string) ($product['branch'] ?? session()->get('branch') ?? '')
            : null;
        $duplicateSku = $this->findDuplicateSku($data['sku'], $id, $branchScope);
        if ($duplicateSku) {
            return redirect()->to('products')->with('error', 'SKU "' . $data['sku'] . '" already exists. Please use a unique SKU.');
        }

        $duplicatePartNo = $this->findDuplicatePartNo($data['part_no'], $id, $branchScope);
        if ($duplicatePartNo) {
            return redirect()->to('products')->with('error', 'Part No. "' . $data['part_no'] . '" already exists. Please use a unique Part No.');
        }

        try {
            $this->model->update($id, $data);
        } catch (DatabaseException $e) {
            if (stripos($e->getMessage(), 'Duplicate entry') !== false && stripos($e->getMessage(), 'sku') !== false) {
                return redirect()->to('products')->with('error', 'SKU "' . ($data['sku'] ?? '') . '" already exists. Please use a unique SKU.');
            }

            throw $e;
        }

        // Keep stocks fields aligned with latest product master values.
        $this->syncStocksForProduct($id, $data, $product);

        // Handle image upload
        $image = $this->request->getFile('product_image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            try {
                // Ensure upload directory exists and is writable
                $uploadDir = FCPATH . 'images/products';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                
                // Remove old image files for this product
                foreach (glob($uploadDir . '/p' . $id . '.*') as $old) {
                    if (is_file($old)) {
                        @unlink($old);
                    }
                }
                
                $ext = $image->getExtension();
                if (!$image->move($uploadDir, 'p' . $id . '.' . $ext, true)) {
                    log_message('error', 'Failed to move product image for product ID: ' . $id);
                }
            } catch (\Exception $e) {
                log_message('error', 'Error handling product image upload: ' . $e->getMessage());
            }
        }

        $this->logHistorySafe(
            'Product Updated',
            'Updated product "' . ($data['part_name'] ?? 'Unknown') . '"',
            (int) $id,
            $data['part_name'] ?? null,
            $data['part_no'] ?? null,
            'Products'
        );

        return redirect()->to('products')->with('success', 'Product updated successfully.');
    }

    public function delete()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('products')->with('error', 'Unauthorized to delete products.');
        }

        $id = (int) $this->request->getPost('product_id');
        
        // Verify user has access to this product
        $product = $this->model->find($id);
        if (!$product) {
            return redirect()->to('products')->with('error', 'Product not found.');
        }
        if ($role === 'admin' && $this->tableHasBranchColumn('products') && (($product['branch'] ?? null) !== session()->get('branch'))) {
            return redirect()->to('products')->with('error', 'You can only delete products from your branch.');
        }

        // Remove image files
        foreach (glob(FCPATH . 'images/products/p' . $id . '.*') as $file) {
            @unlink($file);
        }

        $this->model->delete($id);

        $this->logHistorySafe(
            'Product Deleted',
            'Deleted product "' . ($product['part_name'] ?? 'Unknown') . '"',
            (int) $id,
            $product['part_name'] ?? null,
            $product['part_no'] ?? null,
            'Products'
        );

        return redirect()->to('products')->with('success', 'Product deleted successfully.');
    }

    public function deleteAll()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'], true)) {
            return redirect()->to('products')->with('error', 'Unauthorized to delete all products.');
        }

        $branch = trim((string) session()->get('branch'));
        $scopeAllBranches = $role === 'super_admin';
        if (!$scopeAllBranches && $branch === '') {
            return redirect()->to('products')->with('error', 'Branch not found for this user.');
        }

        $db = \Config\Database::connect();

        try {
            $db->transBegin();

            if ($scopeAllBranches) {
                if ($this->tableHasColumn('stocks', 'product_id')) {
                    $db->table('stocks')->where('product_id IS NOT NULL', null, false)->delete();
                }

                $this->model->truncate();

                foreach (glob(FCPATH . 'images/products/p*.*') as $file) {
                    @unlink($file);
                }
            } else {
                $productIds = [];
                $products = $db->table('products')
                    ->select('product_id')
                    ->where('branch', $branch)
                    ->get()
                    ->getResultArray();

                foreach ($products as $row) {
                    $productIds[] = (int) ($row['product_id'] ?? 0);
                }

                if (!empty($productIds)) {
                    if ($this->tableHasColumn('stocks', 'product_id')) {
                        $db->table('stocks')->whereIn('product_id', $productIds)->delete();
                    }

                    foreach ($productIds as $productId) {
                        foreach (glob(FCPATH . 'images/products/p' . $productId . '.*') as $file) {
                            @unlink($file);
                        }
                    }

                    $db->table('products')->whereIn('product_id', $productIds)->delete();
                }
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Unable to delete all products.');
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to('products')->with('error', 'Failed to delete all products: ' . $e->getMessage());
        }

        $this->logHistorySafe(
            'Products Cleared',
            $scopeAllBranches
                ? 'Deleted all products across all branches'
                : ('Deleted all products for branch "' . $branch . '"'),
            null,
            null,
            null,
            'Products'
        );

        return redirect()->to('products')->with('success', $scopeAllBranches
            ? 'All products deleted successfully.'
            : 'All products for your branch deleted successfully.');
    }

    public function deleteSelected()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'], true)) {
            return redirect()->to('products')->with('error', 'Unauthorized to delete products.');
        }

        $rawIds = $this->request->getPost('product_ids');
        $ids = [];
        if (is_string($rawIds) && $rawIds !== '') {
            $decoded = json_decode($rawIds, true);
            if (is_array($decoded)) {
                $ids = $decoded;
            }
        } elseif (is_array($rawIds)) {
            $ids = $rawIds;
        }

        $ids = array_values(array_unique(array_filter(array_map(static function ($value): int {
            return (int) $value;
        }, $ids), static function (int $value): bool {
            return $value > 0;
        })));

        if (empty($ids)) {
            return redirect()->to('products')->with('error', 'No products selected.');
        }

        $db = \Config\Database::connect();

        if ($role === 'admin' && $this->tableHasBranchColumn('products')) {
            $branch = trim((string) session()->get('branch'));
            if ($branch === '') {
                return redirect()->to('products')->with('error', 'Branch not found for this user.');
            }

            $allowedIds = $db->table('products')
                ->select('product_id')
                ->where('branch', $branch)
                ->whereIn('product_id', $ids)
                ->get()
                ->getResultArray();

            $allowedIds = array_map(static function (array $row): int {
                return (int) ($row['product_id'] ?? 0);
            }, $allowedIds);

            if (count($allowedIds) !== count($ids)) {
                return redirect()->to('products')->with('error', 'Some selected products are outside your branch.');
            }
        }

        try {
            $db->transBegin();

            if ($this->tableHasColumn('stocks', 'product_id')) {
                $db->table('stocks')->whereIn('product_id', $ids)->delete();
            }

            foreach ($ids as $productId) {
                foreach (glob(FCPATH . 'images/products/p' . $productId . '.*') as $file) {
                    @unlink($file);
                }
            }

            $db->table('products')->whereIn('product_id', $ids)->delete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Unable to delete selected products.');
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to('products')->with('error', 'Failed to delete selected products: ' . $e->getMessage());
        }

        $this->logHistorySafe(
            'Products Deleted',
            'Deleted ' . count($ids) . ' selected product(s)',
            null,
            null,
            null,
            'Products'
        );

        return redirect()->to('products')->with('success', count($ids) . ' product(s) deleted successfully.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function downloadTemplate()
    {
        $headers = ['product_name', 'description', 'category_name', 'supplier_name', 'sku', 'part_no', 'price', 'beg_inv', 'stock_qty', 'status', 'image_filename'];
        $example = ['AIR CLEANER ASSY', '7.5 AW70', 'Engine Parts', 'GRACE', 'A1-001', 'PN-001', '250.00', '10', '10', 'available', 'A1-001.jpg'];

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="products_import_template.csv"');
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
            return redirect()->to('products')->with('error', 'Unauthorized to import products.');
        }

        $file = $this->request->getFile('csv_file');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return redirect()->to('products')->with('error', 'Please upload a valid CSV file.');
        }

        $ext = strtolower($file->getExtension());
        if (!in_array($ext, ['csv', 'txt'])) {
            return redirect()->to('products')->with('error', 'Only CSV files are accepted.');
        }

        $importImageMap = [];
        $imagesZip = $this->request->getFile('images_zip');
        $extractDir = null;

        if ($imagesZip && $imagesZip->isValid() && !$imagesZip->hasMoved() && $imagesZip->getName() !== '') {
            $zipExt = strtolower($imagesZip->getExtension());
            if ($zipExt !== 'zip') {
                return redirect()->to('products')->with('error', 'Product images must be uploaded as a .zip file.');
            }

            if (!class_exists(ZipArchive::class)) {
                return redirect()->to('products')->with('error', 'ZIP import is not available because ZipArchive is disabled on this server.');
            }

            $extractDir = WRITEPATH . 'uploads/product_import_' . uniqid('', true);
            if (!is_dir($extractDir) && !@mkdir($extractDir, 0755, true) && !is_dir($extractDir)) {
                return redirect()->to('products')->with('error', 'Unable to prepare temporary folder for image import.');
            }

            $zip = new ZipArchive();
            $openResult = $zip->open($imagesZip->getTempName());
            if ($openResult !== true) {
                $this->removeDirectoryRecursively($extractDir);
                return redirect()->to('products')->with('error', 'Unable to open the images ZIP file.');
            }

            if (!$zip->extractTo($extractDir)) {
                $zip->close();
                $this->removeDirectoryRecursively($extractDir);
                return redirect()->to('products')->with('error', 'Unable to extract images ZIP file.');
            }
            $zip->close();

            $allowedImageExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($extractDir, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $item) {
                if (!$item->isFile()) {
                    continue;
                }

                $fileExt = strtolower((string) $item->getExtension());
                if (!in_array($fileExt, $allowedImageExt, true)) {
                    continue;
                }

                $filename = $item->getFilename();
                $baseName = pathinfo($filename, PATHINFO_FILENAME);
                $lookupKeys = self::buildImageLookupKeys($baseName);

                foreach ($lookupKeys as $lookupKey) {
                    if (!isset($importImageMap[$lookupKey])) {
                        $importImageMap[$lookupKey] = [
                            'path' => $item->getPathname(),
                            'ext'  => $fileExt,
                        ];
                    }
                }
            }
        }

        $db = \Config\Database::connect();

        // Build lookup maps
        $catRows = $db->table('categories')->select('category_id, category_name')->get()->getResultArray();
        $catMap  = [];
        foreach ($catRows as $c) {
            $catMap[strtolower(trim($c['category_name']))] = (int)$c['category_id'];
        }

        $supRows = $db->table('suppliers')->select('supplier_id, supplier_name')->get()->getResultArray();
        $supMap  = [];
        foreach ($supRows as $s) {
            $supMap[strtolower(trim($s['supplier_name']))] = (int)$s['supplier_id'];
        }

        $path    = $file->getTempName();
        $handle  = fopen($path, 'r');
        $headers = fgetcsv($handle);
        if (!$headers) {
            if ($extractDir !== null) {
                $this->removeDirectoryRecursively($extractDir);
            }
            return redirect()->to('products')->with('error', 'CSV file is empty.');
        }

        $headers = array_map(static function ($h) {
            $h = (string) $h;
            $h = preg_replace('/^\xEF\xBB\xBF/', '', $h) ?? $h;
            return strtolower(trim($h));
        }, $headers);
        $map     = array_flip($headers);

        $inserted = 0;
        $errors   = [];
        $warnings = [];
        $branch   = session()->get('branch');
        $productsHasBranch = $this->tableHasBranchColumn('products');
        $productsHasStatus = $this->tableHasColumn('products', 'status');
        $uniquenessScope = $productsHasBranch ? (string) $branch : null;

        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (count(array_filter($row)) === 0) {
                    continue;
                }

                $getByAliases = static function (array $aliases) use ($map, $row): ?string {
                    foreach ($aliases as $alias) {
                        if (isset($map[$alias]) && isset($row[$map[$alias]])) {
                            return trim((string) $row[$map[$alias]]);
                        }
                    }

                    return null;
                };

                $parseNumber = static function (?string $value): ?float {
                    if ($value === null) {
                        return null;
                    }

                    $raw = trim((string) $value);
                    if ($raw === '') {
                        return null;
                    }

                    $normalized = preg_replace('/[^0-9.\-]/', '', $raw) ?? '';
                    if ($normalized === '' || !is_numeric($normalized)) {
                        return null;
                    }

                    return (float) $normalized;
                };

                $partName = $getByAliases(['product_name', 'part_name', 'name']);
                if (empty($partName)) {
                    $errors[] = 'Skipped row - Product Name is required.';
                    continue;
                }

                $description = $getByAliases(['description', 'product_description', 'details']);

                $catName  = strtolower((string) $getByAliases(['category_name']));
                $catId    = $catName !== '' ? ($catMap[$catName] ?? null) : null;

                $supName = strtolower((string) $getByAliases(['supplier_name']));
                $supId   = $supName !== '' ? ($supMap[$supName] ?? null) : null;

                $sku = $getByAliases(['sku']) ?: null;
                $partNo = $getByAliases(['part_no', 'part_number']) ?: null;
                $originalSku = $sku;
                $originalPartNo = $partNo;

                $statusRaw = $getByAliases(['status', 'stock_status']);
                $validStatuses = ['available', 'low stock', 'out of stock'];
                $status = (in_array(strtolower((string) $statusRaw), $validStatuses, true)) ? strtolower((string) $statusRaw) : 'available';

                $priceValue = $parseNumber($getByAliases(['price']));

                // Get beginning inventory; fall back to stock_qty for backward compatibility
                $begInvRaw = $parseNumber($getByAliases(['beg_inv', 'beginning_inventory']));
                $stockQtyRaw = $parseNumber($getByAliases(['stock_qty', 'current_stock']));
                $begInvValue = (int) round($begInvRaw ?? $stockQtyRaw ?? 0);

                if ($this->findDuplicateSku($sku, null, $uniquenessScope)) {
                    $sku = $this->makeUniqueSku($sku, $uniquenessScope);
                    $warnings[] = 'Adjusted duplicate SKU "' . ($originalSku ?? '') . '" to "' . ($sku ?? '') . '".';
                }
                if ($this->findDuplicatePartNo($partNo, null, $uniquenessScope)) {
                    $partNo = $this->makeUniquePartNo($partNo, $uniquenessScope);
                    $warnings[] = 'Adjusted duplicate Part No. "' . ($originalPartNo ?? '') . '" to "' . ($partNo ?? '') . '".';
                }

                $data = [
                    'category_id'   => $catId,
                    'supplier_id'   => $supId,
                    'sku'           => $sku,
                    'part_no'       => $partNo,
                    'part_name'     => $partName,
                    'price'         => $priceValue ?? 0,
                    'current_stock' => $begInvValue, // Initialize current_stock equal to beginning inventory
                ];
                if ($this->tableHasColumn('products', 'description')) {
                    $data['description'] = $description !== '' ? $description : null;
                }
                if (($productsHasStatus ?? false)) {
                    $data['status'] = $status;
                }
                if ($productsHasBranch) {
                    $data['branch'] = $branch;
                }


                try {
                    $newProductId = $this->model->insert($data, true);

                    // Create initial Stocks entry with the beginning inventory
                    if ($begInvValue > 0) {
                        $this->createInitialStockEntry($newProductId, $begInvValue, $data);
                    }

                    if (!empty($importImageMap)) {
                        $csvImageRef = $getByAliases(['image_filename', 'image_file', 'product_image', 'image']);
                        $this->importProductImageFromMap(
                            (int) $newProductId,
                            [
                                $csvImageRef,
                                $data['sku'] ?? null,
                                $data['part_no'] ?? null,
                                $data['part_name'] ?? null,
                                $originalSku,
                                $originalPartNo,
                            ],
                            $importImageMap
                        );
                    }

                    $this->logHistorySafe(
                        'Product Added',
                        'Imported product "' . ($data['part_name'] ?? 'Unknown') . '" from CSV',
                        (int) $newProductId,
                        $data['part_name'] ?? null,
                        $data['part_no'] ?? null,
                        'Products'
                    );

                    $inserted++;
                } catch (DatabaseException $e) {
                    if (stripos($e->getMessage(), 'Duplicate entry') !== false) {
                        $retrySku = $this->makeUniqueSku($data['sku'] ?? null, $uniquenessScope);
                        $retryPartNo = $this->makeUniquePartNo($data['part_no'] ?? null, $uniquenessScope);
                        $data['sku'] = $retrySku;
                        $data['part_no'] = $retryPartNo;
                        $warnings[] = 'Adjusted duplicate identifiers for "' . ($partName ?? 'Unknown') . '".';
                        $newProductId = $this->model->insert($data, true);

                        if ($begInvValue > 0) {
                            $this->createInitialStockEntry($newProductId, $begInvValue, $data);
                        }

                        if (!empty($importImageMap)) {
                            $csvImageRef = $getByAliases(['image_filename', 'image_file', 'product_image', 'image']);
                            $this->importProductImageFromMap(
                                (int) $newProductId,
                                [
                                    $csvImageRef,
                                    $data['sku'] ?? null,
                                    $data['part_no'] ?? null,
                                    $data['part_name'] ?? null,
                                    $originalSku,
                                    $originalPartNo,
                                ],
                                $importImageMap
                            );
                        }

                        $this->logHistorySafe(
                            'Product Added',
                            'Imported product "' . ($data['part_name'] ?? 'Unknown') . '" from CSV',
                            (int) $newProductId,
                            $data['part_name'] ?? null,
                            $data['part_no'] ?? null,
                            'Products'
                        );

                        $inserted++;
                        continue;
                    }

                    throw $e;
                }
            }
        } finally {
            fclose($handle);
            if ($extractDir !== null) {
                $this->removeDirectoryRecursively($extractDir);
            }
        }

        if ($inserted === 0 && !empty($errors)) {
            return redirect()->to('products')->with('error', 'No products imported. ' . implode(' | ', array_slice($errors, 0, 5)));
        }

        if (!empty($errors)) {
            session()->setFlashdata('warning', 'Skipped ' . count($errors) . ' row(s): ' . implode(' | ', array_slice($errors, 0, 5)));
        } elseif (!empty($warnings)) {
            session()->setFlashdata('warning', implode(' | ', array_slice($warnings, 0, 5)));
        }

        return redirect()->to('products')->with('success', "$inserted product(s) imported successfully.");
    }

    public function exportPdf()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access to product exports.');
        }

        $requestedBranch = trim((string) $this->request->getGet('branch'));

        $data = [
            'products' => $this->getScopedProductsForExport($role, $requestedBranch),
            'exportedAt' => date('Y-m-d H:i'),
        ];

        return view('products/export_pdf', $data);
    }

    public function exportExcel()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access to product exports.');
        }

        $requestedBranch = trim((string) $this->request->getGet('branch'));
        $products = $this->getScopedProductsForExport($role, $requestedBranch);

        $filename = 'products_export_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['product_name', 'category_name', 'supplier_name', 'sku', 'part_no', 'upc', 'price', 'stock_qty', 'status', 'branch']);

        foreach ($products as $p) {
            $stock = (int) ($p['current_stock'] ?? 0);
            $status = $stock <= 0 ? 'Out of Stock' : ($stock <= 5 ? 'Low Stock' : 'Available');

            fputcsv($out, [
                $p['part_name'] ?? '',
                $p['category_name'] ?? '',
                $p['supplier_name'] ?? '',
                $p['sku'] ?? '',
                $p['part_no'] ?? '',
                $p['resolved_upc'] ?? '',
                $p['price'] ?? 0,
                $stock,
                $status,
                $p['branch'] ?? '',
            ]);
        }

        fclose($out);
        exit;
    }

    private function getScopedProductsForExport(string $role, string $requestedBranch = ''): array
    {
        $db = \Config\Database::connect();
        $productsHasBranch = $this->tableHasBranchColumn('products');
        $userBranch = (string) (session()->get('branch') ?? '');
        $tempUpcTableExists = $db->query('SHOW TABLES LIKE ?', ['temp_sku_upc'])->getNumRows() > 0;

        $query = $db->table('products p')
            ->select('p.*, c.category_name, s.supplier_name')
            ->join('categories c', 'c.category_id = p.category_id', 'left')
            ->join('suppliers s', 's.supplier_id = p.supplier_id', 'left');

        if ($tempUpcTableExists) {
            $query->select("(SELECT MIN(NULLIF(TRIM(tsu.upc), '')) FROM temp_sku_upc tsu WHERE tsu.sku = p.sku) AS resolved_upc", false);
        } else {
            $query->select('NULL AS resolved_upc', false);
        }

        if ($productsHasBranch) {
            $scope = $this->resolveBranchScope($role, $userBranch, $requestedBranch);
            if (($scope['branch'] ?? null) !== null) {
                $query->where('p.branch', (string) $scope['branch']);
            }
        }

        return $query->orderBy('p.part_name', 'ASC')->get()->getResultArray();
    }

    /** Build lookup keys for image references, allowing exact and normalized matching. */
    private static function buildImageLookupKeys(?string $value): array
    {
        $value = trim((string) $value);
        if ($value === '') {
            return [];
        }

        $value = str_replace('\\', '/', $value);
        $value = pathinfo($value, PATHINFO_FILENAME);

        $exact = strtolower(trim($value));
        $normalized = preg_replace('/[^a-z0-9]/', '', $exact) ?? '';

        $keys = [];
        if ($exact !== '') {
            $keys[] = $exact;
        }
        if ($normalized !== '' && $normalized !== $exact) {
            $keys[] = $normalized;
        }

        return $keys;
    }

    /** Attach an extracted import image to a product using flexible candidate keys. */
    private function importProductImageFromMap(int $productId, array $candidates, array $imageMap): void
    {
        if ($productId <= 0 || empty($imageMap)) {
            return;
        }

        $matchedFile = null;
        foreach ($candidates as $candidate) {
            $lookupKeys = self::buildImageLookupKeys((string) $candidate);
            foreach ($lookupKeys as $lookupKey) {
                if (isset($imageMap[$lookupKey])) {
                    $matchedFile = $imageMap[$lookupKey];
                    break 2;
                }
            }
        }

        if (!$matchedFile || empty($matchedFile['path']) || empty($matchedFile['ext'])) {
            return;
        }

        $destinationDir = FCPATH . 'images/products';
        if (!is_dir($destinationDir) && !@mkdir($destinationDir, 0755, true) && !is_dir($destinationDir)) {
            return;
        }

        foreach (glob($destinationDir . '/p' . $productId . '.*') ?: [] as $oldImage) {
            @unlink($oldImage);
        }

        $destinationPath = $destinationDir . '/p' . $productId . '.' . strtolower((string) $matchedFile['ext']);
        @copy((string) $matchedFile['path'], $destinationPath);
    }

    /** Remove a temporary directory tree created for imports. */
    private function removeDirectoryRecursively(string $dirPath): void
    {
        if ($dirPath === '' || !is_dir($dirPath)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($dirPath);
    }

    /**
     * API endpoint to get products as JSON for Select2 dropdown
     * Searchable by SKU, Part Name, or Part No.
     */
    public function getProductsJson()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $role = session()->get('role');
        $search = trim($this->request->getGet('search') ?? '');
        $requestedBranch = trim((string) $this->request->getGet('branch'));
        $userBranch = (string) (session()->get('branch') ?? '');
        
        $query = $this->model->select('product_id, sku, part_no, part_name, price');
        
        // Filter by branch if admin user
        if ($this->tableHasBranchColumn('products')) {
            $scope = $this->resolveBranchScope((string) $role, $userBranch, $requestedBranch);
            if (($scope['branch'] ?? null) !== null) {
                $query->where('branch', (string) $scope['branch']);
            }
        }
        
        // Search by SKU, Part Name, or Part No.
        if (!empty($search)) {
            $query->groupStart()
                ->like('sku', $search)
                ->orLike('part_name', $search)
                ->orLike('part_no', $search)
                ->groupEnd();
        }
        
        $products = $query->orderBy('part_name', 'ASC')
                         ->limit(50)
                         ->findAll();
        
        $results = array_map(function($p) {
            return [
                'id'        => $p['product_id'],
                'text'      => $p['part_name'],
                'sku'       => $p['sku'] ?? '—',
                'part_no'   => $p['part_no'] ?? '—',
                'price'     => $p['price'] ?? 0,
                'image_url' => self::productImageUrl((int)$p['product_id']),
            ];
        }, $products);
        
        return $this->response->setJSON(['results' => $results]);
    }

    private static function normalizeIdentifier(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        return preg_replace('/[^a-z0-9]/', '', $value) ?? '';
    }

    private function normalizeBranchLabel(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;
        $value = preg_replace('/\s+branch$/i', '', $value) ?? $value;

        return trim($value);
    }

    private function canViewCrossBranchProducts(string $role, string $userBranch): bool
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
        $canCross = $this->canViewCrossBranchProducts($role, $userBranch);
        $requestedBranch = trim($requestedBranch);
        $requestedKey = strtolower($requestedBranch);

        if (! $canCross) {
            return [
                'mode' => 'mine',
                'branch' => $userBranch,
                'activeBranchFilter' => $userBranch,
            ];
        }

        // Cross-branch users default to own products unless explicitly switched.
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

    /**
     * Returns branch labels that can be used by cross-branch filters.
     * Reads from normalized branches table first, then falls back to distinct product branch labels.
     */
    private function getAvailableProductBranches(): array
    {
        $db = \Config\Database::connect();
        $options = [];

        if ($this->tableHasColumn('branches', 'branch_name')) {
            try {
                $branchRows = $db->table('branches')
                    ->select('branch_name')
                    ->where('branch_name IS NOT NULL')
                    ->where('branch_name !=', '')
                    ->orderBy('branch_name', 'ASC')
                    ->get()
                    ->getResultArray();

                foreach ($branchRows as $row) {
                    $label = trim((string) ($row['branch_name'] ?? ''));
                    if ($label !== '') {
                        $options[] = $label;
                    }
                }
            } catch (\Throwable $e) {
                // Fallback query below still covers branch options when this fails.
            }
        }

        if ($this->tableHasBranchColumn('products')) {
            $productRows = $db->table('products')
                ->select('branch')
                ->where('branch IS NOT NULL')
                ->where('branch !=', '')
                ->groupBy('branch')
                ->get()
                ->getResultArray();

            foreach ($productRows as $row) {
                $label = trim((string) ($row['branch'] ?? ''));
                if ($label !== '') {
                    $options[] = $label;
                }
            }
        }

        $deduped = [];
        foreach ($options as $option) {
            $normalized = $this->normalizeBranchLabel($option);
            if ($normalized === '' || isset($deduped[$normalized])) {
                continue;
            }

            $deduped[$normalized] = $option;
        }

        $values = array_values($deduped);
        usort($values, static fn ($a, $b) => strcasecmp((string) $a, (string) $b));

        return $values;
    }

    /** Split identifier into meaningful alphanumeric tokens (e.g. part numbers with separators/ranges). */
    private static function identifierTokens(?string $value): array
    {
        $value = strtolower(trim((string) $value));
        if ($value === '') {
            return [];
        }

        $tokens = preg_split('/[^a-z0-9]+/', $value) ?: [];
        $tokens = array_values(array_unique(array_filter($tokens, static function ($token) {
            return strlen($token) >= 3;
        })));

        return $tokens;
    }

    private function makeUniqueSku(?string $sku, ?string $branch = null): ?string
    {
        $sku = trim((string) $sku);
        if ($sku === '') {
            return null;
        }

        if (! $this->findDuplicateSku($sku, null, $branch)) {
            return $sku;
        }

        for ($i = 1; $i <= 50; $i++) {
            $candidate = $sku . '-' . $i;
            if (! $this->findDuplicateSku($candidate, null, $branch)) {
                return $candidate;
            }
        }

        return $sku . '-' . uniqid();
    }

    private function makeUniquePartNo(?string $partNo, ?string $branch = null): ?string
    {
        $partNo = trim((string) $partNo);
        if ($partNo === '') {
            return null;
        }

        if (! $this->findDuplicatePartNo($partNo, null, $branch)) {
            return $partNo;
        }

        for ($i = 1; $i <= 50; $i++) {
            $candidate = $partNo . '-' . $i;
            if (! $this->findDuplicatePartNo($candidate, null, $branch)) {
                return $candidate;
            }
        }

        return $partNo . '-' . uniqid();
    }

    private function findDuplicateSku(?string $sku, ?int $excludeProductId = null, ?string $branch = null): ?array
    {
        $sku = trim((string) $sku);
        if ($sku === '') {
            return null;
        }

        $db = \Config\Database::connect();

        $query = $this->model
            ->select('product_id, sku')
            ->where('LOWER(TRIM(sku)) = ' . $db->escape(strtolower($sku)), null, false);
        if ($branch !== null && $branch !== '' && $this->tableHasBranchColumn('products')) {
            $query->where('branch', $branch);
        }
        if ($excludeProductId !== null) {
            $query->where('product_id !=', $excludeProductId);
        }

        return $query->first();
    }

    private function findDuplicatePartNo(?string $partNo, ?int $excludeProductId = null, ?string $branch = null): ?array
    {
        $partNo = trim((string) $partNo);
        if ($partNo === '') {
            return null;
        }

        $db = \Config\Database::connect();

        $query = $this->model
            ->select('product_id, part_no')
            ->where('LOWER(TRIM(part_no)) = ' . $db->escape(strtolower($partNo)), null, false);
        if ($branch !== null && $branch !== '' && $this->tableHasBranchColumn('products')) {
            $query->where('branch', $branch);
        }
        if ($excludeProductId !== null) {
            $query->where('product_id !=', $excludeProductId);
        }

        return $query->first();
    }

    /**
     * Sync stocks SKU/part number/name/price with the updated product.
     * Also links unlinked stock rows if they clearly match by SKU, part number, or exact product name.
     */
    private function syncStocksForProduct(int $productId, array $newData, array $oldProduct): void
    {
        $db = \Config\Database::connect();

        $sku = trim((string) ($newData['sku'] ?? ''));
        $partNo = trim((string) ($newData['part_no'] ?? ''));
        $partName = trim((string) ($newData['part_name'] ?? ''));
        $price = $newData['price'] ?? null;

        $syncPayload = [
            'sku'      => $sku !== '' ? $sku : null,
            'part_num' => $partNo !== '' ? $partNo : null,
            'part_name'=> $partName !== '' ? $partName : null,
            'price'    => $price,
        ];

        // Always sync already-linked stock rows.
        $db->table('stocks')
            ->where('product_id', $productId)
            ->set($syncPayload)
            ->update();

        $skuKeys = array_values(array_unique(array_filter([
            self::normalizeIdentifier($sku),
            self::normalizeIdentifier((string) ($oldProduct['sku'] ?? '')),
        ])));

        $partKeys = array_values(array_unique(array_filter([
            self::normalizeIdentifier($partNo),
            self::normalizeIdentifier((string) ($oldProduct['part_no'] ?? '')),
        ])));

        $nameKeys = array_values(array_unique(array_filter([
            strtolower(trim((string) $partName)),
            strtolower(trim((string) ($oldProduct['part_name'] ?? ''))),
        ])));

        $partTokenSets = [];
        foreach ([$partNo, (string) ($oldProduct['part_no'] ?? '')] as $partValue) {
            $tokens = self::identifierTokens($partValue);
            if ($tokens !== []) {
                $partTokenSets[] = $tokens;
            }
        }

        $unlinkedRows = $db->table('stocks')
            ->select('stock_id, sku, part_num, part_name')
            ->where('product_id IS NULL')
            ->get()
            ->getResultArray();

        $linkIds = [];
        foreach ($unlinkedRows as $row) {
            $rowSkuKey = self::normalizeIdentifier((string) ($row['sku'] ?? ''));
            $rowPartKey = self::normalizeIdentifier((string) ($row['part_num'] ?? ''));
            $rowNameKey = strtolower(trim((string) ($row['part_name'] ?? '')));

            $matched = false;

            if (!$matched && $rowSkuKey !== '' && in_array($rowSkuKey, $skuKeys, true)) {
                $matched = true;
            }

            if (!$matched && $rowPartKey !== '' && !empty($partKeys)) {
                foreach ($partKeys as $partKey) {
                    if (strlen($partKey) < 6) {
                        continue;
                    }
                    if ($rowPartKey === $partKey || str_contains($rowPartKey, $partKey) || str_contains($partKey, $rowPartKey)) {
                        $matched = true;
                        break;
                    }
                }
            }

            if (!$matched && !empty($partTokenSets)) {
                $rowTokens = array_flip(self::identifierTokens((string) ($row['part_num'] ?? '')));
                foreach ($partTokenSets as $tokenSet) {
                    $allTokensPresent = true;
                    foreach ($tokenSet as $token) {
                        if (!isset($rowTokens[$token])) {
                            $allTokensPresent = false;
                            break;
                        }
                    }

                    if ($allTokensPresent) {
                        $matched = true;
                        break;
                    }
                }
            }

            if (!$matched && $rowNameKey !== '' && in_array($rowNameKey, $nameKeys, true)) {
                $matched = true;
            }

            if ($matched) {
                $linkIds[] = (int) $row['stock_id'];
            }
        }

        if ($linkIds !== []) {
            $db->table('stocks')
                ->whereIn('stock_id', $linkIds)
                ->set(array_merge($syncPayload, ['product_id' => $productId]))
                ->update();
        }
    }

    /** Returns the image URL for a product_id, or a default placeholder. */
    public static function productImageUrl(int $id): string
    {
        $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        foreach ($extensions as $ext) {
            $filepath = FCPATH . 'images/products/p' . $id . '.' . $ext;
            if (file_exists($filepath)) {
                // Return the relative URL path
                return '/images/products/p' . $id . '.' . $ext;
            }
        }
        // Return a default if no image found
        return '/images/products/default.png';
    }

    /**
     * Create an initial Stocks entry when a product is first created with Beginning Inventory.
     * This establishes the starting point for stock tracking.
     * Note: beg_inv is stored in stocks table.
     */
    private function createInitialStockEntry(int $productId, int $begInv, array $productData): void
    {
        if ($productId <= 0 || $begInv <= 0) {
            return;
        }

        $stocksModel = new StocksModel();
        $userBranch = (string) (session()->get('branch') ?? '');

        try {
            $stockData = [
                'product_id' => $productId,
                'sku' => (string) ($productData['sku'] ?? ''),
                'stock_num' => 1, // First stock entry for this product
                'part_num' => (string) ($productData['part_no'] ?? ''),
                'part_name' => (string) ($productData['part_name'] ?? ''),
                'price' => (float) ($productData['price'] ?? 0),
                'beg_inv' => $begInv,
                'stocks' => $begInv, // Initially, stocks = beginning inventory
                'items_in' => 0,
                'items_out' => 0,
                'in_remarks' => 'Initial stock entry',
                'out_remarks' => '',
            ];

            if ($this->tableHasBranchColumn('stocks')) {
                $stockData['branch'] = $userBranch;
            }

            $stocksModel->insert($stockData);
        } catch (\Exception $e) {
            log_message('warning', 'Failed to create initial stock entry for product ' . $productId . ': ' . $e->getMessage());
        }
    }
}
