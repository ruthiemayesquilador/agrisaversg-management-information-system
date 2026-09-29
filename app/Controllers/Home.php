<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Database\BaseConnection;

class Home extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $db = \Config\Database::connect();
        $role = session()->get('role');
        $branch = session()->get('branch');
        $productsHasBranch = $this->tableHasBranchColumn('products');
        $stocksHasBranch   = $this->tableHasBranchColumn('stocks');
        $salesHasBranch    = $this->tableHasBranchColumn('sales');

        // Total products
        try {
            if ($role === 'admin' && $productsHasBranch) {
                $totalProducts = $db->table('products')->where('branch', $branch)->countAllResults();
            } else {
                $totalProducts = $db->table('products')->countAll();
            }
        } catch (\Exception $e) {
            $totalProducts = 0;
        }

        // Low stocks alert (stocks quantity <= 10)
        try {
            $lowStocksQuery = $this->buildDashboardStocksScopeQuery($db, (string) $role, (string) $branch, $productsHasBranch, $stocksHasBranch)
                ->select('COUNT(*) AS cnt', false)
                ->where('s.stocks <=', 10);

            $lowStocksRow = $lowStocksQuery->get()->getRow();
            $lowStocks = (int) ($lowStocksRow->cnt ?? 0);

            $lowStocksPct = $totalProducts > 0
                ? (int) min(round($lowStocks / max($totalProducts, 1) * 100), 100)
                : 0;
        } catch (\Exception $e) {
            $lowStocks    = 0;
            $lowStocksPct = 0;
        }

        // Today's sales count + total cash_sales
        try {
            $todayDate    = date('Y-m-d');
            $salesQuery = $db->table('sales')
                ->select('COUNT(*) AS cnt, COALESCE(SUM(cash_sales), 0) AS total', false)
                ->where('sale_date', $todayDate);
            if ($role === 'admin' && $salesHasBranch) {
                $salesQuery->where('branch', $branch);
            }
            $todaySalesRow = $salesQuery->get()->getRow();
            $todaySalesCount = (int) ($todaySalesRow->cnt   ?? 0);
            $todaySalesTotal = number_format((float) ($todaySalesRow->total ?? 0), 2);
        } catch (\Exception $e) {
            $todaySalesCount = 0;
            $todaySalesTotal = '0.00';
        }

        // Stocks summary – total quantity across all stock records
        try {
            $stocksSumQuery = $this->buildDashboardStocksScopeQuery($db, (string) $role, (string) $branch, $productsHasBranch, $stocksHasBranch)
                ->select('COALESCE(SUM(s.stocks), 0) AS total', false);

            $stocksSumRow  = $stocksSumQuery->get()->getRow();
            $totalStockQty = (int) ($stocksSumRow->total ?? 0);
        } catch (\Exception $e) {
            $totalStockQty = 0;
        }

        // All users for the Manage Users table (only for super_admin)
        $users = [];
        $branches = [];
        if ($role === 'super_admin') {
            $userModel = new UserModel();
            $users     = $userModel->orderBy('id', 'ASC')->findAll();

            if ($db->query('SHOW TABLES LIKE ?', ['branches'])->getNumRows() > 0) {
                $branches = $db->table('branches b')
                    ->select('b.branch_id, b.branch_name, b.normalized_name, b.is_active, COUNT(u.id) AS user_count', false)
                    ->join('users u', 'u.branch_id = b.branch_id', 'left')
                    ->groupBy('b.branch_id')
                    ->orderBy('b.branch_name', 'ASC')
                    ->get()
                    ->getResultArray();
            } else {
                $rawBranches = $db->table('users')
                    ->select('branch')
                    ->where('branch !=', '')
                    ->groupBy('branch')
                    ->orderBy('branch', 'ASC')
                    ->get()
                    ->getResultArray();

                foreach ($rawBranches as $index => $row) {
                    $name = trim((string) ($row['branch'] ?? ''));
                    if ($name === '') {
                        continue;
                    }

                    $userCount = (int) $db->table('users')->where('branch', $name)->countAllResults();
                    $branches[] = [
                        'branch_id' => $index + 1,
                        'branch_name' => $name,
                        'normalized_name' => strtolower(preg_replace('/\s+/', ' ', preg_replace('/\s+branch$/i', '', $name) ?? $name) ?? $name),
                        'is_active' => 1,
                        'user_count' => $userCount,
                    ];
                }
            }
        }

        $canManageUpc = $this->canAccessUpcTools((string) $role, (string) $branch);
        $upcMappings = [];
        if ($canManageUpc) {
            try {
                $upcTable = $this->resolveUpcTableName($db);
                if ($upcTable !== null) {
                    $upcMappings = $db->table($upcTable)
                        ->select('sku, upc')
                        ->orderBy('sku', 'ASC')
                        ->get()
                        ->getResultArray();
                }
            } catch (\Exception $e) {
                $upcMappings = [];
            }
        }

        $data = [
            'totalProducts'    => $totalProducts,
            'totalProductsPct' => min($totalProducts, 100),
            'lowStocks'        => $lowStocks,
            'lowStocksPct'     => $lowStocksPct,
            'todaySalesCount'  => $todaySalesCount,
            'todaySalesTotal'  => $todaySalesTotal,
            'todaySalesPct'    => min($todaySalesCount, 100),
            'totalStockQty'    => $totalStockQty,
            'totalStockPct'    => min($totalStockQty, 100),
            'users'            => $users,
            'branches'         => $branches,
            'role'             => $role,
            'canManageUpc'     => $canManageUpc,
            'upcMappings'      => $upcMappings,
        ];

        return view('dashboard/index', $data);
    }

    public function downloadUpcTemplate()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized to download UPC template.');
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="upc_import_template.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['sku', 'upc']);
        fputcsv($out, ['C169', '000000004916']);
        fclose($out);
        exit;
    }

    public function importUpcMap()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        $branch = (string) session()->get('branch');
        if (!$this->canAccessUpcTools((string) $role, $branch)) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized to import UPC map.');
        }

        $file = $this->request->getFile('upc_csv_file');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return redirect()->to('dashboard')->with('error', 'Please upload a valid UPC CSV file.');
        }

        $ext = strtolower((string) $file->getExtension());
        if (!in_array($ext, ['csv', 'txt'])) {
            return redirect()->to('dashboard')->with('error', 'Only CSV files are accepted for UPC import.');
        }

        $db = \Config\Database::connect();
        $upcTable = $this->resolveUpcTableName($db);
        if ($upcTable === null) {
            return redirect()->to('dashboard')->with('error', 'UPC table not found. Expected temp_sku_upc or sku_upc_map.');
        }

        $handle = fopen($file->getTempName(), 'r');
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return redirect()->to('dashboard')->with('error', 'CSV file is empty.');
        }

        $normalizeHeader = static function (?string $h): string {
            $h = (string) $h;
            $h = preg_replace('/^\xEF\xBB\xBF/', '', $h) ?? $h;
            return strtolower(trim($h));
        };

        $headers = array_map($normalizeHeader, $headers);
        $map = array_flip($headers);

        if (!isset($map['sku']) || !isset($map['upc'])) {
            fclose($handle);
            return redirect()->to('dashboard')->with('error', 'CSV must include sku and upc columns.');
        }

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, static fn($v) => trim((string)$v) !== '')) === 0) {
                continue;
            }

            $sku = trim((string) ($row[$map['sku']] ?? ''));
            $upcRaw = trim((string) ($row[$map['upc']] ?? ''));
            $upc = $this->normalizeUpcValue($upcRaw);

            if ($sku === '' || $upc === null) {
                $skipped++;
                continue;
            }

            $existing = $db->table($upcTable)
                ->select('sku, upc')
                ->where('sku', $sku)
                ->get()
                ->getRowArray();

            if ($existing) {
                if ((string) ($existing['upc'] ?? '') === $upc) {
                    $skipped++;
                    continue;
                }

                $db->table($upcTable)
                    ->where('sku', $sku)
                    ->update(['upc' => $upc]);
                $updated++;
                continue;
            }

            $db->table($upcTable)->insert([
                'sku' => $sku,
                'upc' => $upc,
            ]);
            $inserted++;
        }

        fclose($handle);

        return redirect()->to('dashboard')->with('success', "UPC import complete. Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped}.");
    }

    public function addSingleUpcMap()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        $branch = (string) session()->get('branch');
        if (!$this->canAccessUpcTools((string) $role, $branch)) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized to add UPC map.');
        }

        $sku = trim((string) $this->request->getPost('sku'));
        $upcRaw = trim((string) $this->request->getPost('upc'));
        $upc = $this->normalizeUpcValue($upcRaw);

        if ($sku === '' || $upc === null) {
            return redirect()->to('dashboard')->with('error', 'Please provide a valid SKU and 12-digit UPC.');
        }

        $db = \Config\Database::connect();
        $upcTable = $this->resolveUpcTableName($db);
        if ($upcTable === null) {
            return redirect()->to('dashboard')->with('error', 'UPC table not found. Expected temp_sku_upc or sku_upc_map.');
        }

        $existing = $db->table($upcTable)
            ->select('sku, upc')
            ->where('sku', $sku)
            ->get()
            ->getRowArray();

        if ($existing) {
            $db->table($upcTable)
                ->where('sku', $sku)
                ->update(['upc' => $upc]);
            return redirect()->to('dashboard')->with('success', 'UPC mapping updated.');
        }

        $db->table($upcTable)->insert([
            'sku' => $sku,
            'upc' => $upc,
        ]);

        return redirect()->to('dashboard')->with('success', 'UPC mapping added.');
    }

    public function exportUpcMapCsv()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized to export UPC map.');
        }

        $db = \Config\Database::connect();
        $upcTable = $this->resolveUpcTableName($db);
        if ($upcTable === null) {
            return redirect()->to('dashboard')->with('error', 'UPC table not found. Expected temp_sku_upc or sku_upc_map.');
        }

        $query = $db->table('products p')
            ->select('p.product_id, p.part_name, p.description, p.sku, p.part_no, NULLIF(TRIM(t.upc), "") AS upc, p.current_stock, p.branch', false)
            ->join($upcTable . ' t', 't.sku = p.sku', 'left')
            ->groupBy('p.product_id')
            ->orderBy('p.part_name', 'ASC');

        if ($role === 'admin' && $this->tableHasBranchColumn('products')) {
            $branch = (string) session()->get('branch');
            $query->groupStart()
                ->where('p.branch', $branch)
                ->orWhere('p.branch IS NULL', null, false)
                ->orWhere("TRIM(p.branch) = ''", null, false)
                ->groupEnd();
        }

        $rows = $query->get()->getResultArray();

        $filename = 'upc_barcode_export_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['product_id', 'part_name', 'sku', 'part_no', 'upc', 'current_stock', 'branch']);
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['product_id'] ?? '',
                $row['part_name'] ?? '',
                $row['sku'] ?? '',
                $row['part_no'] ?? '',
                $row['upc'] ?? '',
                $row['current_stock'] ?? 0,
                $row['branch'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    public function exportUpcMapPdf()
    {
        // PDF export should show sticker layout, same as sticker print format.
        return $this->exportUpcStickersPdf();
    }

    public function printUpcLabels()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized to print UPC labels.');
        }

        $db = \Config\Database::connect();
        $upcTable = $this->resolveUpcTableName($db);
        if ($upcTable === null) {
            return redirect()->to('dashboard')->with('error', 'UPC table not found. Expected temp_sku_upc or sku_upc_map.');
        }

        $query = $db->table('products p')
            ->select('p.product_id, p.part_name, p.description, p.sku, p.part_no, NULLIF(TRIM(t.upc), "") AS upc, p.current_stock, p.branch', false)
            ->join($upcTable . ' t', 't.sku = p.sku', 'inner')
            ->where("t.upc REGEXP '^[0-9]{12}$'", null, false)
            ->groupBy('p.product_id')
            ->orderBy('p.part_name', 'ASC')
            ->limit(300);

        if ($role === 'admin' && $this->tableHasBranchColumn('products')) {
            $branch = (string) session()->get('branch');
            $query->groupStart()
                ->where('p.branch', $branch)
                ->orWhere('p.branch IS NULL', null, false)
                ->orWhere("TRIM(p.branch) = ''", null, false)
                ->groupEnd();
        }

        $labels = $query->get()->getResultArray();

        return view('dashboard/upc_labels', ['labels' => $labels, 'autoPrint' => false, 'pdfMode' => false]);
    }

    public function exportUpcStickersPdf()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized to export UPC stickers PDF.');
        }

        $db = \Config\Database::connect();
        $upcTable = $this->resolveUpcTableName($db);
        if ($upcTable === null) {
            return redirect()->to('dashboard')->with('error', 'UPC table not found. Expected temp_sku_upc or sku_upc_map.');
        }

        $query = $db->table('products p')
            ->select('p.product_id, p.part_name, p.description, p.sku, p.part_no, NULLIF(TRIM(t.upc), "") AS upc, p.current_stock, p.branch', false)
            ->join($upcTable . ' t', 't.sku = p.sku', 'inner')
            ->where("t.upc REGEXP '^[0-9]{12}$'", null, false)
            ->groupBy('p.product_id')
            ->orderBy('p.part_name', 'ASC')
            ->limit(300);

        if ($role === 'admin' && $this->tableHasBranchColumn('products')) {
            $branch = (string) session()->get('branch');
            $query->groupStart()
                ->where('p.branch', $branch)
                ->orWhere('p.branch IS NULL', null, false)
                ->orWhere("TRIM(p.branch) = ''", null, false)
                ->groupEnd();
        }

        $labels = $query->get()->getResultArray();

        return view('dashboard/upc_labels', ['labels' => $labels, 'autoPrint' => false, 'pdfMode' => true]);
    }

    // ─── Add User ────────────────────────────────────────────────────────────

    public function addUser()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        if (session()->get('role') !== 'super_admin') {
            return redirect()->to('dashboard')->with('error', 'Unauthorized to add users.');
        }

        $userModel = new UserModel();

        $name = trim((string) $this->request->getPost('name'));
        $username = trim((string) $this->request->getPost('username'));

        $userId = $userModel->insert([
            'name'     => trim($this->request->getPost('name')),
            'username' => trim($this->request->getPost('username')),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'email'    => trim($this->request->getPost('email')),
            'branch'   => trim($this->request->getPost('branch')),
            'role'     => $this->request->getPost('role'),
        ], true);

        $this->logHistorySafe(
            'User Added',
            'Added user "' . ($username !== '' ? $username : $name) . '"',
            (int) $userId,
            $username !== '' ? $username : $name,
            null,
            'Auth'
        );

        return redirect()->to('dashboard')->with('success', 'User added successfully.');
    }

    // ─── Update User ─────────────────────────────────────────────────────────

    public function updateUser()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        if (session()->get('role') !== 'super_admin') {
            return redirect()->to('dashboard')->with('error', 'Unauthorized to update users.');
        }

        $userModel = new UserModel();
        $id        = (int) $this->request->getPost('id');
        $existing  = $userModel->find($id);

        $data = [
            'name'     => trim($this->request->getPost('name')),
            'username' => trim($this->request->getPost('username')),
            'email'    => trim($this->request->getPost('email')),
            'branch'   => trim($this->request->getPost('branch')),
            'role'     => $this->request->getPost('role'),
        ];

        $password = $this->request->getPost('password');
        if (!empty(trim($password))) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $userModel->update($id, $data);

        $userLabel = $data['username'] !== ''
            ? $data['username']
            : ((string) ($existing['username'] ?? $existing['name'] ?? ('User #' . $id)));

        $this->logHistorySafe(
            'User Updated',
            'Updated user "' . $userLabel . '"',
            (int) $id,
            $userLabel,
            null,
            'Auth'
        );

        return redirect()->to('dashboard')->with('success', 'User updated successfully.');
    }

    // ─── Delete User ─────────────────────────────────────────────────────────

    public function deleteUser(int $id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        if (session()->get('role') !== 'super_admin') {
            return redirect()->to('dashboard')->with('error', 'Unauthorized to delete users.');
        }

        $userModel = new UserModel();
        $existing  = $userModel->find((int) $id);
        $userModel->delete((int) $id);

        $userLabel = (string) ($existing['username'] ?? $existing['name'] ?? ('User #' . (int) $id));
        $this->logHistorySafe(
            'User Deleted',
            'Deleted user "' . $userLabel . '"',
            (int) $id,
            $userLabel,
            null,
            'Auth'
        );

        return redirect()->to('dashboard')->with('success', 'User deleted successfully.');
    }

    public function addBranch()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        if (session()->get('role') !== 'super_admin') {
            return redirect()->to('dashboard')->with('error', 'Unauthorized to add branches.');
        }

        $db = \Config\Database::connect();
        if ($db->query('SHOW TABLES LIKE ?', ['branches'])->getNumRows() === 0) {
            return redirect()->to('dashboard')->with('error', 'Branches table is not available.');
        }

        $branchName = trim((string) $this->request->getPost('branch_name'));
        $isActive = (int) $this->request->getPost('is_active') === 1 ? 1 : 0;
        $normalized = $this->normalizeBranchLabel($branchName);

        if ($normalized === '') {
            return redirect()->to('dashboard')->with('error', 'Branch name is required.');
        }

        $finalName = $this->formatBranchDisplayName($branchName, $normalized);

        $exists = $db->table('branches')
            ->where('normalized_name', $normalized)
            ->countAllResults();
        if ($exists > 0) {
            return redirect()->to('dashboard')->with('error', 'Branch already exists.');
        }

        $db->table('branches')->insert([
            'branch_name' => $finalName,
            'normalized_name' => $normalized,
            'is_active' => $isActive,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $branchId = (int) $db->insertID();
        $this->logHistorySafe(
            'Branch Added',
            'Added branch "' . $finalName . '"',
            $branchId,
            $finalName,
            null,
            'Branches'
        );

        return redirect()->to('dashboard')->with('success', 'Branch added successfully.');
    }

    public function updateBranch()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        if (session()->get('role') !== 'super_admin') {
            return redirect()->to('dashboard')->with('error', 'Unauthorized to update branches.');
        }

        $db = \Config\Database::connect();
        if ($db->query('SHOW TABLES LIKE ?', ['branches'])->getNumRows() === 0) {
            return redirect()->to('dashboard')->with('error', 'Branches table is not available.');
        }

        $branchId = (int) $this->request->getPost('branch_id');
        $branchName = trim((string) $this->request->getPost('branch_name'));
        $isActive = (int) $this->request->getPost('is_active') === 1 ? 1 : 0;
        $normalized = $this->normalizeBranchLabel($branchName);

        if ($branchId <= 0 || $normalized === '') {
            return redirect()->to('dashboard')->with('error', 'Invalid branch details.');
        }

        $branch = $db->table('branches')->where('branch_id', $branchId)->get()->getRowArray();
        if (!$branch) {
            return redirect()->to('dashboard')->with('error', 'Branch not found.');
        }

        $duplicate = $db->table('branches')
            ->where('normalized_name', $normalized)
            ->where('branch_id !=', $branchId)
            ->countAllResults();
        if ($duplicate > 0) {
            return redirect()->to('dashboard')->with('error', 'Another branch with the same name already exists.');
        }

        $oldName = (string) ($branch['branch_name'] ?? '');
        $newName = $this->formatBranchDisplayName($branchName, $normalized);

        $db->table('branches')->where('branch_id', $branchId)->update([
            'branch_name' => $newName,
            'normalized_name' => $normalized,
            'is_active' => $isActive,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($oldName !== '' && $newName !== '' && strcasecmp($oldName, $newName) !== 0) {
            $this->propagateBranchRename($db, $branchId, $oldName, $newName);
        }

        $this->logHistorySafe(
            'Branch Updated',
            'Updated branch "' . $newName . '"',
            $branchId,
            $newName,
            null,
            'Branches'
        );

        return redirect()->to('dashboard')->with('success', 'Branch updated successfully.');
    }

    public function deleteBranch(int $id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        if (session()->get('role') !== 'super_admin') {
            return redirect()->to('dashboard')->with('error', 'Unauthorized to delete branches.');
        }

        $branchId = (int) $id;
        if ($branchId <= 0) {
            return redirect()->to('dashboard')->with('error', 'Invalid branch ID.');
        }

        $db = \Config\Database::connect();
        if ($db->query('SHOW TABLES LIKE ?', ['branches'])->getNumRows() === 0) {
            return redirect()->to('dashboard')->with('error', 'Branches table is not available.');
        }

        $branch = $db->table('branches')->where('branch_id', $branchId)->get()->getRowArray();
        if (!$branch) {
            return redirect()->to('dashboard')->with('error', 'Branch not found.');
        }

        $inUse = 0;
        foreach (['users', 'products', 'sales', 'expenses', 'histories'] as $table) {
            if ($db->query('SHOW TABLES LIKE ?', [$table])->getNumRows() === 0) {
                continue;
            }

            $col = $db->query("SHOW COLUMNS FROM `{$table}` LIKE 'branch_id'")->getNumRows() > 0;
            if (!$col) {
                continue;
            }

            $inUse += (int) $db->table($table)->where('branch_id', $branchId)->countAllResults();
        }

        if ($db->query('SHOW TABLES LIKE ?', ['product_transfers'])->getNumRows() > 0) {
            $transfersInUse = (int) $db->table('product_transfers')
                ->groupStart()
                ->where('from_branch_id', $branchId)
                ->orWhere('to_branch_id', $branchId)
                ->groupEnd()
                ->countAllResults();
            $inUse += $transfersInUse;
        }

        if ($inUse > 0) {
            return redirect()->to('dashboard')->with('error', 'Branch cannot be deleted because it is used in existing records.');
        }

        $db->table('branches')->where('branch_id', $branchId)->delete();

        $branchName = (string) ($branch['branch_name'] ?? ('Branch #' . $branchId));
        $this->logHistorySafe(
            'Branch Deleted',
            'Deleted branch "' . $branchName . '"',
            $branchId,
            $branchName,
            null,
            'Branches'
        );

        return redirect()->to('dashboard')->with('success', 'Branch deleted successfully.');
    }

    private function buildDashboardStocksScopeQuery(BaseConnection $db, string $role, string $branch, bool $productsHasBranch, bool $stocksHasBranch)
    {
        $query = $db->table('stocks s');

        if ($role === 'admin') {
            if ($productsHasBranch && $stocksHasBranch) {
                $query->join('products p', 'p.product_id = s.product_id', 'left')
                    ->groupStart()
                    ->where('p.branch', $branch)
                    ->orWhere('s.branch', $branch)
                    ->groupEnd();
            } elseif ($productsHasBranch) {
                $query->join('products p', 'p.product_id = s.product_id', 'left')
                    ->where('p.branch', $branch);
            } elseif ($stocksHasBranch) {
                $query->where('s.branch', $branch);
            }
        }

        return $query;
    }

    private function resolveUpcTableName(BaseConnection $db): ?string
    {
        if ($db->query('SHOW TABLES LIKE ?', ['temp_sku_upc'])->getNumRows() > 0) {
            return 'temp_sku_upc';
        }

        if ($db->query('SHOW TABLES LIKE ?', ['sku_upc_map'])->getNumRows() > 0) {
            return 'sku_upc_map';
        }

        return null;
    }

    private function canAccessUpcTools(string $role, string $branch): bool
    {
        if (!in_array($role, ['admin', 'super_admin'], true)) {
            return false;
        }

        $normalized = strtolower(trim(preg_replace('/\s+/', ' ', $branch) ?? $branch));
        $isPonagui = (strpos($normalized, 'ponagui') !== false) || (strpos($normalized, 'polangui') !== false);

        return $isPonagui;
    }

    private function normalizeUpcValue(string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', trim($raw)) ?? '';
        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 13 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return preg_match('/^[0-9]{12}$/', $digits) ? $digits : null;
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

    private function formatBranchDisplayName(string $rawName, string $normalized): string
    {
        $trimmed = trim($rawName);
        if ($trimmed !== '' && preg_match('/\bbranch$/i', $trimmed)) {
            return preg_replace('/\s+/', ' ', $trimmed) ?? $trimmed;
        }

        return ucwords($normalized) . ' Branch';
    }

    private function propagateBranchRename(BaseConnection $db, int $branchId, string $oldName, string $newName): void
    {
        $pairs = [
            ['users', 'branch'],
            ['products', 'branch'],
            ['sales', 'branch'],
            ['expenses', 'branch'],
            ['histories', 'branch'],
            ['stocks', 'branch'],
        ];

        foreach ($pairs as [$table, $column]) {
            if ($db->query('SHOW TABLES LIKE ?', [$table])->getNumRows() === 0) {
                continue;
            }

            if ($db->query("SHOW COLUMNS FROM `{$table}` LIKE ?", [$column])->getNumRows() === 0) {
                continue;
            }

            if ($db->query("SHOW COLUMNS FROM `{$table}` LIKE 'branch_id'")->getNumRows() > 0) {
                $db->table($table)->where('branch_id', $branchId)->update([$column => $newName]);
            } else {
                $db->table($table)->where($column, $oldName)->update([$column => $newName]);
            }
        }

        if ($db->query('SHOW TABLES LIKE ?', ['product_transfers'])->getNumRows() > 0) {
            $db->table('product_transfers')->where('from_branch_id', $branchId)->update(['from_branch' => $newName]);
            $db->table('product_transfers')->where('to_branch_id', $branchId)->update(['to_branch' => $newName]);
        }
    }
}

