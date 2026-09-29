<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ReportsController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $db = \Config\Database::connect();
        $role = (string) (session()->get('role') ?? '');
        $branch = (string) (session()->get('branch') ?? '');

        $salesHasBranch = $this->tableHasBranchColumn('sales');
        $productsHasBranch = $this->tableHasBranchColumn('products');
        $stocksHasBranch = $this->tableHasBranchColumn('stocks');

        // ── Monthly sales totals – last 12 months ──
        $salesWhereSql = 'sale_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)';
        $salesParams = [];
        if ($role === 'admin' && $salesHasBranch) {
            $salesWhereSql .= ' AND branch = ?';
            $salesParams[] = $branch;
        }

        $salesRows = $db->query(" 
            SELECT DATE_FORMAT(sale_date, '%b %Y') AS label,
                   SUM(COALESCE(cash_sales, 0) + COALESCE(account_receivable, 0)) AS total
            FROM sales
            WHERE {$salesWhereSql}
            GROUP BY YEAR(sale_date), MONTH(sale_date)
            ORDER BY YEAR(sale_date), MONTH(sale_date)
        ", $salesParams)->getResultArray();
        $salesLabels = array_column($salesRows, 'label');
        $salesValues = array_map('floatval', array_column($salesRows, 'total'));

        // ── Categories with product count and total stock ──
        $productsJoinSql = 'p.category_id = c.category_id';
        $categoryParams = [];
        if ($role === 'admin' && $productsHasBranch) {
            $productsJoinSql .= ' AND p.branch = ?';
            $categoryParams[] = $branch;
        }

        $categories = $db->query(" 
            SELECT c.category_id, c.category_name,
                   COUNT(p.product_id)                 AS product_count,
                   COALESCE(SUM(p.current_stock), 0)   AS total_stock
            FROM categories c
            LEFT JOIN products p ON {$productsJoinSql}
            GROUP BY c.category_id, c.category_name
            ORDER BY product_count DESC
        ", $categoryParams)->getResultArray();

        // ── Per-category product breakdown for pie drill-down ──
        $productsWhereSql = 'category_id IS NOT NULL';
        $productsParams = [];
        if ($role === 'admin' && $productsHasBranch) {
            $productsWhereSql .= ' AND branch = ?';
            $productsParams[] = $branch;
        }

        $productsRows = $db->query(" 
            SELECT category_id,
                   part_name,
                   COALESCE(current_stock, 0) AS current_stock
            FROM products
            WHERE {$productsWhereSql}
            ORDER BY category_id, part_name
        ", $productsParams)->getResultArray();

        $catProducts = [];
        foreach ($productsRows as $row) {
            $cid = $row['category_id'];
            if (!isset($catProducts[$cid])) {
                $catProducts[$cid] = ['labels' => [], 'series' => []];
            }
            $catProducts[$cid]['labels'][] = $row['part_name'];
            $catProducts[$cid]['series'][] = (int) $row['current_stock'];
        }

        // ── Top 10 stock items ──
        if ($role === 'admin' && $productsHasBranch && $stocksHasBranch) {
            $topStocksRows = $db->query(
                "SELECT s.part_name,
                        COALESCE(s.items_in,  0) AS items_in,
                        COALESCE(s.items_out, 0) AS items_out,
                        COALESCE(s.stocks,    0) AS stocks
                 FROM stocks s
                 LEFT JOIN products p ON p.product_id = s.product_id
                 WHERE p.branch = ? OR s.branch = ?
                 ORDER BY s.stocks DESC
                 LIMIT 10",
                [$branch, $branch]
            )->getResultArray();
        } elseif ($role === 'admin' && $productsHasBranch) {
            $topStocksRows = $db->query(
                "SELECT s.part_name,
                        COALESCE(s.items_in,  0) AS items_in,
                        COALESCE(s.items_out, 0) AS items_out,
                        COALESCE(s.stocks,    0) AS stocks
                 FROM stocks s
                 LEFT JOIN products p ON p.product_id = s.product_id
                 WHERE p.branch = ?
                 ORDER BY s.stocks DESC
                 LIMIT 10",
                [$branch]
            )->getResultArray();
        } elseif ($role === 'admin' && $stocksHasBranch) {
            $topStocksRows = $db->query(
                "SELECT part_name,
                        COALESCE(items_in,  0) AS items_in,
                        COALESCE(items_out, 0) AS items_out,
                        COALESCE(stocks,    0) AS stocks
                 FROM stocks
                 WHERE branch = ?
                 ORDER BY stocks DESC
                 LIMIT 10",
                [$branch]
            )->getResultArray();
        } else {
            $topStocksRows = $db->query(
                "SELECT part_name,
                        COALESCE(items_in,  0) AS items_in,
                        COALESCE(items_out, 0) AS items_out,
                        COALESCE(stocks,    0) AS stocks
                 FROM stocks
                 ORDER BY stocks DESC
                 LIMIT 10"
            )->getResultArray();
        }
        $stockLabels   = array_column($topStocksRows, 'part_name');
        $stockItemsIn  = array_map('intval', array_column($topStocksRows, 'items_in'));
        $stockItemsOut = array_map('intval', array_column($topStocksRows, 'items_out'));
        $stockValues   = array_map('intval', array_column($topStocksRows, 'stocks'));

        return view('reports/index', [
            'salesLabels'   => $salesLabels,
            'salesValues'   => $salesValues,
            'categories'    => $categories,
            'catProducts'   => $catProducts,
            'stockLabels'   => $stockLabels,
            'stockItemsIn'  => $stockItemsIn,
            'stockItemsOut' => $stockItemsOut,
            'stockValues'   => $stockValues,
        ]);
    }

    // ── AJAX: return filtered sales data as JSON ──
    public function salesData()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $type = $this->request->getGet('type') ?? 'monthly';
        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');

        // Validate date format – only accept YYYY-MM-DD
        $dateRx = '/^\d{4}-\d{2}-\d{2}$/';
        $from = ($from && preg_match($dateRx, $from)) ? $from : null;
        $to   = ($to   && preg_match($dateRx, $to))   ? $to   : null;

        $db = \Config\Database::connect();
        $role = (string) (session()->get('role') ?? '');
        $branch = (string) (session()->get('branch') ?? '');
        $salesHasBranch = $this->tableHasBranchColumn('sales');

        if ($type === 'daily') {
            $from = $from ?? date('Y-m-d', strtotime('-29 days'));
            $to   = $to   ?? date('Y-m-d');

            $dailySql = "SELECT DATE_FORMAT(sale_date, '%d %b %Y') AS label,
                                SUM(COALESCE(cash_sales, 0) + COALESCE(account_receivable, 0)) AS total
                         FROM sales
                         WHERE sale_date BETWEEN ? AND ?";
            $dailyParams = [$from, $to];
            if ($role === 'admin' && $salesHasBranch) {
                $dailySql .= ' AND branch = ?';
                $dailyParams[] = $branch;
            }
            $dailySql .= ' GROUP BY sale_date ORDER BY sale_date';

            $rows = $db->query(
                $dailySql,
                $dailyParams
            )->getResultArray();
        } else {
            // monthly
            $from = $from ?? date('Y-m-d', strtotime('-12 months'));
            $to   = $to   ?? date('Y-m-d');

            $monthlySql = "SELECT DATE_FORMAT(sale_date, '%b %Y') AS label,
                                  SUM(COALESCE(cash_sales, 0) + COALESCE(account_receivable, 0)) AS total
                           FROM sales
                           WHERE sale_date BETWEEN ? AND ?";
            $monthlyParams = [$from, $to];
            if ($role === 'admin' && $salesHasBranch) {
                $monthlySql .= ' AND branch = ?';
                $monthlyParams[] = $branch;
            }
            $monthlySql .= ' GROUP BY YEAR(sale_date), MONTH(sale_date) ORDER BY YEAR(sale_date), MONTH(sale_date)';

            $rows = $db->query(
                $monthlySql,
                $monthlyParams
            )->getResultArray();
        }

        return $this->response->setJSON([
            'labels' => array_column($rows, 'label'),
            'values' => array_map('floatval', array_column($rows, 'total')),
        ]);
    }
}
