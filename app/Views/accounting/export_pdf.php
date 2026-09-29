<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accounting Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111;
            margin: 24px;
        }
        h1 {
            font-size: 20px;
            margin: 0 0 8px 0;
        }
        h2 {
            font-size: 15px;
            margin: 20px 0 8px 0;
        }
        .meta {
            font-size: 12px;
            color: #555;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 14px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f5f5f5;
        }
        .muted {
            color: #777;
        }
        .right {
            text-align: right;
        }
        @media print {
            body {
                margin: 12px;
            }
        }
    </style>
</head>
<body>
    <h1>Accounting Export</h1>
    <div class="meta">
        Exported at: <?= esc((string) ($exportedAt ?? date('Y-m-d H:i'))) ?>
        <?php if (!empty($sales_search)): ?>
            <span class="muted"> | Sales Filter: "<?= esc((string) $sales_search) ?>"</span>
        <?php endif; ?>
        <?php if (!empty($expenses_search)): ?>
            <span class="muted"> | Expenses Filter: "<?= esc((string) $expenses_search) ?>"</span>
        <?php endif; ?>
    </div>

    <h2>Daily Sales</h2>
    <table>
        <thead>
            <tr>
                <th>CSI</th>
                <th>Date</th>
                <th>Cash Sales</th>
                <th>Payment Method</th>
                <th>Account Receivable</th>
                <th>Remarks</th>
                <th>Branch</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($sales)): ?>
                <tr><td colspan="7" class="muted">No sales records found.</td></tr>
            <?php else: ?>
                <?php foreach ($sales as $s): ?>
                    <tr>
                        <td><?= esc((string) ($s['csi'] ?? '')) ?></td>
                        <td><?= esc((string) ($s['sale_date'] ?? '')) ?></td>
                        <td class="right"><?= $s['cash_sales'] !== null ? number_format((float) $s['cash_sales'], 2) : '' ?></td>
                        <td><?= esc((string) ($s['payment_method'] ?? '')) ?></td>
                        <td class="right"><?= $s['account_receivable'] !== null ? number_format((float) $s['account_receivable'], 2) : '' ?></td>
                        <td><?= esc((string) ($s['remarks'] ?? '')) ?></td>
                        <td><?= esc((string) ($s['branch'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Expenses</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Remarks</th>
                <th>Branch</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($expenses)): ?>
                <tr><td colspan="5" class="muted">No expense records found.</td></tr>
            <?php else: ?>
                <?php foreach ($expenses as $e): ?>
                    <tr>
                        <td><?= esc((string) ($e['expense_date'] ?? '')) ?></td>
                        <td><?= esc((string) ($e['expense_type'] ?? 'expense')) ?></td>
                        <td><?= esc((string) ($e['description'] ?? '')) ?></td>
                        <td class="right"><?= $e['amount'] !== null ? number_format((float) $e['amount'], 2) : '' ?></td>
                        <td><?= esc((string) ($e['remarks'] ?? '')) ?></td>
                        <td><?= esc((string) ($e['branch'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        window.print();
    </script>
</body>
</html>
