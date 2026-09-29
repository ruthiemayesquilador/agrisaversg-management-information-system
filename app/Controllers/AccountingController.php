<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SalesModel;
use App\Models\ExpensesModel;

class AccountingController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access to accounting.');
        }

        $data = $this->getScopedAccountingData();

        return view('accounting/index', $data);
    }

    public function exportPdf()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access to accounting exports.');
        }

        $data = $this->getScopedAccountingData();
        $data['exportedAt'] = date('Y-m-d H:i');

        return view('accounting/export_pdf', $data);
    }

    public function exportExcel()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access to accounting exports.');
        }

        $data = $this->getScopedAccountingData();

        $filename = 'accounting_export_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');

        fputcsv($out, ['Accounting Export']);
        fputcsv($out, ['Exported At', date('Y-m-d H:i:s')]);
        fputcsv($out, ['Sales Search', $data['sales_search'] ?? '']);
        fputcsv($out, ['Expenses Search', $data['expenses_search'] ?? '']);
        fputcsv($out, []);

        fputcsv($out, ['Daily Sales']);
        fputcsv($out, ['CSI', 'Date', 'Cash Sales', 'Payment Method', 'Account Receivable', 'Remarks', 'Branch']);
        foreach ($data['sales'] as $s) {
            fputcsv($out, [
                $s['csi'] ?? '',
                $s['sale_date'] ?? '',
                $s['cash_sales'] ?? '',
                $s['payment_method'] ?? '',
                $s['account_receivable'] ?? '',
                $s['remarks'] ?? '',
                $s['branch'] ?? '',
            ]);
        }

        fputcsv($out, []);
        fputcsv($out, ['Expenses']);
        fputcsv($out, ['Date', 'Type', 'Description', 'Amount', 'Remarks', 'Branch']);
        foreach ($data['expenses'] as $e) {
            fputcsv($out, [
                $e['expense_date'] ?? '',
                $e['expense_type'] ?? 'expense',
                $e['description'] ?? '',
                $e['amount'] ?? '',
                $e['remarks'] ?? '',
                $e['branch'] ?? '',
            ]);
        }

        fclose($out);
        exit;
    }

    // ── Sales ──────────────────────────────────────────────────────────

    public function storeSale()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('sales')->with('error', 'Unauthorized to add sales.');
        }

        $csi = trim((string) $this->request->getPost('csi'));
        $saleDate = $this->request->getPost('date');

        $salesModel = new SalesModel();
        $saleData = [
            'csi'                => $csi,
            'sale_date'          => $saleDate,
            'cash_sales'         => $this->request->getPost('cash_sales') !== '' ? $this->request->getPost('cash_sales') : null,
            'payment_method'     => $this->request->getPost('payment_method') ?: null,
            'account_receivable' => $this->request->getPost('account_receivable') !== '' ? $this->request->getPost('account_receivable') : null,
            'remarks'            => $this->request->getPost('remarks') ?: null,
        ];
        if ($this->tableHasBranchColumn('sales')) {
            $saleData['branch'] = session()->get('branch');
        }

        $saleId = $salesModel->insert($saleData, true);

        $saleLabel = $csi !== '' ? $csi : ('Sale #' . (int) $saleId);
        $this->logHistorySafe(
            'Sale Added',
            'Added sale "' . $saleLabel . '" dated ' . ($saleDate ?: date('Y-m-d')),
            (int) $saleId,
            $saleLabel,
            null,
            'Sales'
        );

        return redirect()->to('sales')->with('success', 'Sale added successfully.');
    }

    public function updateSale()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        return redirect()->to('sales')->with('error', 'Editing Daily Sales entries is disabled.');

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('sales')->with('error', 'Unauthorized to update sales.');
        }

        $id         = $this->request->getPost('id');
        $salesModel = new SalesModel();
        
        // Verify user has access to this sale
        $sale = $salesModel->find($id);
        if (!$sale) {
            return redirect()->to('sales')->with('error', 'Sale not found.');
        }
        if ($role === 'admin' && $this->tableHasBranchColumn('sales') && (($sale['branch'] ?? null) !== session()->get('branch'))) {
            return redirect()->to('sales')->with('error', 'You can only update sales from your branch.');
        }
        
        $salesModel->update($id, [
            'csi'                => $this->request->getPost('csi'),
            'sale_date'          => $this->request->getPost('date'),
            'cash_sales'         => $this->request->getPost('cash_sales') !== '' ? $this->request->getPost('cash_sales') : null,
            'payment_method'     => $this->request->getPost('payment_method') ?: null,
            'account_receivable' => $this->request->getPost('account_receivable') !== '' ? $this->request->getPost('account_receivable') : null,
            'remarks'            => $this->request->getPost('remarks') ?: null,
        ]);

        $saleLabel = trim((string) $this->request->getPost('csi'));
        if ($saleLabel === '') {
            $saleLabel = (string) ($sale['csi'] ?? ('Sale #' . (int) $id));
        }

        $this->logHistorySafe(
            'Sale Updated',
            'Updated sale "' . $saleLabel . '"',
            (int) $id,
            $saleLabel,
            null,
            'Sales'
        );

        return redirect()->to('sales')->with('success', 'Sale updated successfully.');
    }

    public function collectPayment()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('sales')->with('error', 'Unauthorized to collect payments.');
        }

        $customerName = trim((string) $this->request->getPost('customer_name'));
        $paymentAmount = (float) ($this->request->getPost('payment_amount') ?? 0);
        $paymentMethod = trim((string) $this->request->getPost('payment_method'));
        $notes = trim((string) $this->request->getPost('notes'));

        if ($customerName === '') {
            return redirect()->to('sales')->with('error', 'Customer name is required.');
        }

        if ($paymentAmount <= 0) {
            return redirect()->to('sales')->with('error', 'Payment amount must be greater than zero.');
        }

        if ($paymentMethod === '') {
            $paymentMethod = 'Cash';
        }

        $db = \Config\Database::connect();
        $salesModel = new SalesModel();

        $builder = $db->table('sales')
            ->select('sale_id, csi, sale_date, cash_sales, account_receivable, payment_method, remarks, customer_address')
            ->where('customer_name', $customerName)
            ->where('COALESCE(account_receivable, 0) >', 0, false)
            ->orderBy('sale_date', 'ASC')
            ->orderBy('sale_id', 'ASC');

        if ($role === 'admin' && $this->tableHasBranchColumn('sales')) {
            $builder->where('branch', session()->get('branch'));
        }

        $openSales = $builder->get()->getResultArray();
        if (empty($openSales)) {
            return redirect()->to('sales')->with('error', 'No outstanding balance found for this customer.');
        }

        $totalOutstanding = array_reduce($openSales, static function (float $carry, array $row): float {
            return $carry + (float) ($row['account_receivable'] ?? 0);
        }, 0.0);

        if ($paymentAmount > $totalOutstanding) {
            return redirect()->to('sales')->with('error', 'Payment exceeds total outstanding balance.');
        }

        $remainingPayment = $paymentAmount;
        $appliedTotal = 0.0;
        $allocations = [];
        $customerAddress = '';

        $db->transBegin();
        try {
            foreach ($openSales as $sale) {
                if ($remainingPayment <= 0) {
                    break;
                }

                $currentAr = (float) ($sale['account_receivable'] ?? 0);
                if ($currentAr <= 0) {
                    continue;
                }

                $applyAmount = min($remainingPayment, $currentAr);
                $newAr = round(max(0, $currentAr - $applyAmount), 2);
                $newCash = round(((float) ($sale['cash_sales'] ?? 0)) + $applyAmount, 2);

                $paymentRemark = '[Payment Collected ' . date('Y-m-d H:i') . '] ₱' . number_format($applyAmount, 2) . ' via ' . $paymentMethod;
                if ($notes !== '') {
                    $paymentRemark .= ' | ' . $notes;
                }

                $existingRemarks = trim((string) ($sale['remarks'] ?? ''));
                $newRemarks = $existingRemarks !== '' ? ($existingRemarks . "\n" . $paymentRemark) : $paymentRemark;

                $salesModel->update((int) $sale['sale_id'], [
                    'cash_sales' => $newCash,
                    'account_receivable' => $newAr > 0 ? $newAr : null,
                    'payment_method' => $paymentMethod,
                    'remarks' => $newRemarks,
                ]);

                if ($customerAddress === '' && trim((string) ($sale['customer_address'] ?? '')) !== '') {
                    $customerAddress = trim((string) ($sale['customer_address'] ?? ''));
                }

                $allocations[] = [
                    'sale_id' => (int) $sale['sale_id'],
                    'csi' => (string) ($sale['csi'] ?? ''),
                    'amount' => $applyAmount,
                ];

                $appliedTotal += $applyAmount;
                $remainingPayment -= $applyAmount;
            }

            if ($appliedTotal <= 0) {
                throw new \RuntimeException('No payment was applied.');
            }

            $paymentReceiptNo = $this->generateReceiptNo('PAY');
            $appliedBreakdown = array_map(static function (array $row): string {
                return ($row['csi'] !== '' ? $row['csi'] : ('SALE#' . (int) ($row['sale_id'] ?? 0)))
                    . ':₱' . number_format((float) ($row['amount'] ?? 0), 2);
            }, $allocations);

            $paymentRemarks = 'Payment collection for ' . $customerName
                . ' | Applied to ' . count($allocations) . ' sale(s)'
                . ' | Breakdown: ' . implode(', ', $appliedBreakdown);
            if ($notes !== '') {
                $paymentRemarks .= ' | Notes: ' . $notes;
            }

            $paymentRow = [
                'csi' => $paymentReceiptNo,
                'sale_date' => date('Y-m-d'),
                'cash_sales' => $appliedTotal,
                'payment_method' => $paymentMethod,
                'account_receivable' => null,
                'remarks' => $paymentRemarks,
            ];

            if ($this->tableHasBranchColumn('sales')) {
                $paymentRow['branch'] = session()->get('branch');
            }
            if ($this->tableHasColumn('sales', 'customer_name')) {
                $paymentRow['customer_name'] = $customerName;
            }
            if ($this->tableHasColumn('sales', 'customer_address')) {
                $paymentRow['customer_address'] = $customerAddress !== '' ? $customerAddress : null;
            }
            if ($this->tableHasColumn('sales', 'gross_total')) {
                $paymentRow['gross_total'] = $appliedTotal;
            }
            if ($this->tableHasColumn('sales', 'discount_amount')) {
                $paymentRow['discount_amount'] = 0;
            }
            if ($this->tableHasColumn('sales', 'net_total')) {
                $paymentRow['net_total'] = $appliedTotal;
            }
            if ($this->tableHasColumn('sales', 'sale_type')) {
                $paymentRow['sale_type'] = 'payment';
            }
            if ($this->tableHasColumn('sales', 'parent_sale_id') && !empty($allocations)) {
                $paymentRow['parent_sale_id'] = (int) ($allocations[0]['sale_id'] ?? 0);
            }

            $paymentSaleId = $salesModel->insert($paymentRow, true);
            if (!$paymentSaleId) {
                throw new \RuntimeException('Unable to save payment receipt record.');
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Unable to collect payment.');
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to('sales')->with('error', 'Payment collection failed: ' . $e->getMessage());
        }

        $newOutstanding = max(0, round($totalOutstanding - $appliedTotal, 2));
        $this->logHistorySafe(
            'Payment Collected',
            'Collected ₱' . number_format($appliedTotal, 2) . ' from "' . $customerName . '" via ' . $paymentMethod
            . ' | Applied to ' . count($allocations) . ' sale(s) | Remaining balance: ₱' . number_format($newOutstanding, 2),
            (int) $paymentSaleId,
            $paymentReceiptNo,
            null,
            'Accounting'
        );

        session()->setTempdata('last_payment_receipt', [
            'receipt_no' => $paymentReceiptNo,
            'customer_name' => $customerName,
            'customer_address' => $customerAddress,
            'payment_amount' => $appliedTotal,
            'payment_method' => $paymentMethod,
            'notes' => $notes,
            'remaining_balance' => $newOutstanding,
            'allocations' => $allocations,
            'cashier' => (string) (session()->get('name') ?? session()->get('username') ?? 'Cashier'),
            'processed_at' => date('Y-m-d H:i:s'),
        ], 1800);

        return redirect()->to('accounting/payment-receipt/' . rawurlencode($paymentReceiptNo));
    }

    public function paymentReceipt(string $receiptNo = '')
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access to payment receipts.');
        }

        $receiptNo = strtoupper(trim($receiptNo));
        if ($receiptNo === '') {
            return redirect()->to('sales')->with('error', 'Receipt number is required.');
        }

        $salesModel = new SalesModel();
        $receiptRow = $salesModel->where('csi', $receiptNo)->first();
        if (!$receiptRow) {
            return redirect()->to('sales')->with('error', 'Payment receipt not found.');
        }

        if ($role === 'admin' && $this->tableHasBranchColumn('sales') && (($receiptRow['branch'] ?? null) !== session()->get('branch'))) {
            return redirect()->to('sales')->with('error', 'You can only access payment receipts from your branch.');
        }

        $lastReceipt = session()->getTempdata('last_payment_receipt');
        $allocations = [];
        if (is_array($lastReceipt) && (($lastReceipt['receipt_no'] ?? '') === $receiptNo)) {
            $allocations = is_array($lastReceipt['allocations'] ?? null) ? $lastReceipt['allocations'] : [];
        }

        $viewData = [
            'receipt_no' => $receiptNo,
            'customer_name' => (string) ($receiptRow['customer_name'] ?? ''),
            'customer_address' => (string) ($receiptRow['customer_address'] ?? ''),
            'payment_amount' => (float) ($receiptRow['cash_sales'] ?? 0),
            'payment_method' => (string) ($receiptRow['payment_method'] ?? 'Cash'),
            'processed_at' => (string) ($receiptRow['created_at'] ?? date('Y-m-d H:i:s')),
            'cashier' => (string) ((is_array($lastReceipt) && (($lastReceipt['receipt_no'] ?? '') === $receiptNo))
                ? ($lastReceipt['cashier'] ?? '')
                : (session()->get('name') ?? session()->get('username') ?? 'Cashier')),
            'notes' => (string) ((is_array($lastReceipt) && (($lastReceipt['receipt_no'] ?? '') === $receiptNo))
                ? ($lastReceipt['notes'] ?? '')
                : ''),
            'remaining_balance' => (float) ((is_array($lastReceipt) && (($lastReceipt['receipt_no'] ?? '') === $receiptNo))
                ? ($lastReceipt['remaining_balance'] ?? 0)
                : 0),
            'allocations' => $allocations,
            'branch' => (string) ($receiptRow['branch'] ?? session()->get('branch') ?? ''),
        ];

        return view('accounting/payment_receipt', $viewData);
    }

    public function deleteSale()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        return redirect()->to('sales')->with('error', 'Deleting Daily Sales entries is disabled.');

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('sales')->with('error', 'Unauthorized to delete sales.');
        }

        $id         = $this->request->getPost('id');
        $salesModel = new SalesModel();
        
        // Verify user has access to this sale
        $sale = $salesModel->find($id);
        if (!$sale) {
            return redirect()->to('sales')->with('error', 'Sale not found.');
        }
        if ($role === 'admin' && $this->tableHasBranchColumn('sales') && (($sale['branch'] ?? null) !== session()->get('branch'))) {
            return redirect()->to('sales')->with('error', 'You can only delete sales from your branch.');
        }
        
        $salesModel->delete($id);

        $saleLabel = (string) ($sale['csi'] ?? ('Sale #' . (int) $id));
        $this->logHistorySafe(
            'Sale Deleted',
            'Deleted sale "' . $saleLabel . '"',
            (int) $id,
            $saleLabel,
            null,
            'Sales'
        );

        return redirect()->to('sales')->with('success', 'Sale deleted successfully.');
    }

    // ── Expenses ───────────────────────────────────────────────────────

    public function storeExpense()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('sales')->with('error', 'Unauthorized to add expenses.');
        }

        $expenseDate = $this->request->getPost('date');
        $expenseDescription = trim((string) $this->request->getPost('description'));
        $expenseType = $this->normalizeExpenseType($this->request->getPost('expense_type'));

        $expensesModel = new ExpensesModel();
        $expenseData = [
            'expense_date' => $expenseDate,
            'expense_type' => $expenseType,
            'description'  => $expenseDescription,
            'amount'       => $this->request->getPost('amount'),
            'remarks'      => $this->request->getPost('remarks') ?: null,
        ];
        if ($this->tableHasBranchColumn('expenses')) {
            $expenseData['branch'] = session()->get('branch');
        }

        $expenseId = $expensesModel->insert($expenseData, true);

        $expenseLabel = $expenseDescription !== '' ? $expenseDescription : ('Expense #' . (int) $expenseId);
        $this->logHistorySafe(
            'Expense Added',
            'Added expense "' . $expenseLabel . '" dated ' . ($expenseDate ?: date('Y-m-d')),
            (int) $expenseId,
            $expenseLabel,
            null,
            'Accounting'
        );

        return redirect()->to('sales')->with('success', 'Expense added successfully.');
    }

    public function updateExpense()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('sales')->with('error', 'Unauthorized to update expenses.');
        }

        $id            = $this->request->getPost('id');
        $expensesModel = new ExpensesModel();
        
        // Verify user has access to this expense
        $expense = $expensesModel->find($id);
        if (!$expense) {
            return redirect()->to('sales')->with('error', 'Expense not found.');
        }
        if ($role === 'admin' && $this->tableHasBranchColumn('expenses') && (($expense['branch'] ?? null) !== session()->get('branch'))) {
            return redirect()->to('sales')->with('error', 'You can only update expenses from your branch.');
        }
        
        $expenseType = $this->normalizeExpenseType($this->request->getPost('expense_type'));

        $expensesModel->update($id, [
            'expense_date' => $this->request->getPost('date'),
            'expense_type' => $expenseType,
            'description'  => $this->request->getPost('description'),
            'amount'       => $this->request->getPost('amount'),
            'remarks'      => $this->request->getPost('remarks') ?: null,
        ]);

        $expenseLabel = trim((string) $this->request->getPost('description'));
        if ($expenseLabel === '') {
            $expenseLabel = (string) ($expense['description'] ?? ('Expense #' . (int) $id));
        }

        $this->logHistorySafe(
            'Expense Updated',
            'Updated expense "' . $expenseLabel . '"',
            (int) $id,
            $expenseLabel,
            null,
            'Accounting'
        );

        return redirect()->to('sales')->with('success', 'Expense updated successfully.');
    }

    public function deleteExpense()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('sales')->with('error', 'Unauthorized to delete expenses.');
        }

        $id            = $this->request->getPost('id');
        $expensesModel = new ExpensesModel();
        
        // Verify user has access to this expense
        $expense = $expensesModel->find($id);
        if (!$expense) {
            return redirect()->to('sales')->with('error', 'Expense not found.');
        }
        if ($role === 'admin' && $this->tableHasBranchColumn('expenses') && (($expense['branch'] ?? null) !== session()->get('branch'))) {
            return redirect()->to('sales')->with('error', 'You can only delete expenses from your branch.');
        }
        
        $expensesModel->delete($id);

        $expenseLabel = (string) ($expense['description'] ?? ('Expense #' . (int) $id));
        $this->logHistorySafe(
            'Expense Deleted',
            'Deleted expense "' . $expenseLabel . '"',
            (int) $id,
            $expenseLabel,
            null,
            'Accounting'
        );

        return redirect()->to('sales')->with('success', 'Expense deleted successfully.');
    }

    private function getScopedAccountingData(): array
    {
        $role = session()->get('role');
        $userBranch = (string) (session()->get('branch') ?? '');
        $salesModel = new SalesModel();
        $expensesModel = new ExpensesModel();
        $salesHasBranch = $this->tableHasBranchColumn('sales');
        $expensesHasBranch = $this->tableHasBranchColumn('expenses');

        $salesSearch = trim($this->request->getGet('sales_search') ?? '');
        $expensesSearch = trim($this->request->getGet('expenses_search') ?? '');
        $requestedBranch = trim((string) $this->request->getGet('branch'));
        $canCrossBranchView = $this->canViewCrossBranchAccounting((string) $role, $userBranch);
        $activeBranchFilter = '';
        $activeBranchMode = 'all';
        $branchFilter = null;

        if ($salesHasBranch || $expensesHasBranch) {
            $scope = $this->resolveBranchScope((string) $role, $userBranch, $requestedBranch);
            $activeBranchFilter = (string) ($scope['activeBranchFilter'] ?? '');
            $activeBranchMode = (string) ($scope['mode'] ?? 'all');
            $branchFilter = $scope['branch'] ?? null;
        }

        $salesQuery = $salesModel;
        if ($salesHasBranch && $branchFilter !== null && $branchFilter !== '') {
            $salesQuery = $salesQuery->where('branch', $branchFilter);
        }
        if ($this->tableHasColumn('sales', 'sale_type')) {
            $salesQuery = $salesQuery->groupStart()
                ->where('sale_type', 'sale')
                ->orWhere('sale_type IS NULL', null, false)
                ->groupEnd();
        }
        if ($this->tableHasColumn('sales', 'payment_method')) {
            $salesQuery = $salesQuery->groupStart()
                ->where('payment_method IS NULL', null, false)
                ->orWhere('payment_method !=', 'Refund')
                ->groupEnd()
                ->groupStart()
                ->where('payment_method IS NULL', null, false)
                ->orWhere('payment_method !=', 'Void')
                ->groupEnd();
        }
        if ($this->tableHasColumn('sales', 'csi')) {
            $salesQuery = $salesQuery->notLike('csi', 'RTN-', 'after')
                ->notLike('csi', 'VOID-', 'after');
        }
        if ($salesSearch !== '') {
            $salesQuery = $salesQuery->groupStart()
                ->like('csi', $salesSearch)
                ->orLike('sale_date', $salesSearch)
                ->orLike('remarks', $salesSearch)
                ->groupEnd();
        }

        $expensesQuery = $expensesModel;
        if ($expensesHasBranch && $branchFilter !== null && $branchFilter !== '') {
            $branch = (string) $branchFilter;
            $expensesQuery = $expensesQuery->groupStart()
                ->where('branch', $branch)
                ->orWhere('branch IS NULL', null, false)
                ->orWhere('branch', '')
                ->groupEnd();
        }
        if ($expensesSearch !== '') {
            $expensesQuery = $expensesQuery->groupStart()
                ->like('expense_date', $expensesSearch)
                ->orLike('description', $expensesSearch)
                ->orLike('remarks', $expensesSearch)
                ->groupEnd();
        }

        return [
            'sales' => $salesQuery->orderBy('sale_date', 'DESC')->findAll(),
            'expenses' => $expensesQuery->orderBy('expense_date', 'DESC')->findAll(),
            'sales_search' => $salesSearch,
            'expenses_search' => $expensesSearch,
            'customer_balances' => $this->getCustomerBalances($salesHasBranch, $branchFilter),
            'canCrossBranchView' => $canCrossBranchView,
            'availableBranches' => $canCrossBranchView ? $this->getAvailableAccountingBranches() : [],
            'activeBranchFilter' => $activeBranchFilter,
            'activeBranchMode' => $activeBranchMode,
            'userBranch' => $userBranch,
        ];
    }

    private function getCustomerBalances(bool $salesHasBranch, ?string $branchFilter = null): array
    {
        if (!$this->tableHasColumn('sales', 'customer_name')) {
            return [];
        }

        $db = \Config\Database::connect();
        $addressSelect = $this->tableHasColumn('sales', 'customer_address')
            ? 'COALESCE(MAX(customer_address), "") AS customer_address'
            : '"" AS customer_address';

        $builder = $db->table('sales')
            ->select('customer_name, ' . $addressSelect . ', SUM(COALESCE(account_receivable, 0)) AS balance', false)
            ->where('customer_name IS NOT NULL', null, false)
            ->where("TRIM(customer_name) != ''", null, false)
            ->groupBy('customer_name')
            ->having('SUM(COALESCE(account_receivable, 0)) >', 0)
            ->orderBy('balance', 'DESC');

        if ($salesHasBranch && $branchFilter !== null && $branchFilter !== '') {
            $builder->where('branch', $branchFilter);
        }

        return $builder->get()->getResultArray();
    }

    private function normalizeBranchLabel(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;
        $value = preg_replace('/\s+branch$/i', '', $value) ?? $value;

        return trim($value);
    }

    private function canViewCrossBranchAccounting(string $role, string $userBranch): bool
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
        $canCross = $this->canViewCrossBranchAccounting($role, $userBranch);
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

    /**
     * Returns branch labels that can be used by cross-branch filters.
     */
    private function getAvailableAccountingBranches(): array
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
                // Fallback queries below cover branch options when this fails.
            }
        }

        $sourceTables = [];
        if ($this->tableHasBranchColumn('sales')) {
            $sourceTables[] = 'sales';
        }
        if ($this->tableHasBranchColumn('expenses')) {
            $sourceTables[] = 'expenses';
        }

        foreach ($sourceTables as $table) {
            $rows = $db->table($table)
                ->select('DISTINCT(branch) AS branch')
                ->where('branch IS NOT NULL', null, false)
                ->where('branch !=', '')
                ->orderBy('branch', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $label = trim((string) ($row['branch'] ?? ''));
                if ($label !== '') {
                    $options[] = $label;
                }
            }
        }

        $seen = [];
        $deduped = [];
        foreach ($options as $label) {
            $key = $this->normalizeBranchLabel($label);
            if ($key === '' || isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $deduped[] = $label;
        }

        return $deduped;
    }

    private function normalizeExpenseType(?string $value): string
    {
        $value = strtolower(trim((string) $value));

        if ($value === 'owner_draw') {
            return 'owner_draw';
        }

        return 'expense';
    }

    private function generateReceiptNo(string $prefixLabel = 'PAY'): string
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
}

