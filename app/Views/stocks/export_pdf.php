<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stocks Export</title>
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
        .meta {
            font-size: 12px;
            color: #555;
            margin-bottom: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background: #f5f5f5;
        }
        .muted {
            color: #777;
        }
        @media print {
            body {
                margin: 12px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <h1>Stocks Export</h1>
    <div class="meta">
        Exported at: <?= esc($exportedAt) ?>
        <?php if (!empty($search)): ?>
            <span class="muted"> | Filter: "<?= esc($search) ?>"</span>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>No.</th>
                <th>Part No.</th>
                <th>Part Name</th>
                <th>Price</th>
                <th>Stocks</th>
                <th>Beg. Inv.</th>
                <th>Items In</th>
                <th>Remarks (In)</th>
                <th>Items Out</th>
                <th>Remarks (Out)</th>
                <th>Branch</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($stocks)): ?>
                <tr>
                    <td colspan="12" class="muted">No stock records found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($stocks as $s): ?>
                    <tr>
                        <td><?= esc($s['sku'] ?? '') ?></td>
                        <td><?= esc($s['stock_num'] ?? '') ?></td>
                        <td><?= esc($s['part_num'] ?? '') ?></td>
                        <td><?= esc($s['part_name'] ?? '') ?></td>
                        <td><?= $s['price'] !== null ? number_format((float) $s['price'], 2) : '' ?></td>
                        <td><?= esc($s['stocks'] ?? 0) ?></td>
                        <td><?= esc($s['beg_inv'] ?? 0) ?></td>
                        <td><?= esc($s['items_in'] ?? 0) ?></td>
                        <td><?= esc($s['in_remarks'] ?? '') ?></td>
                        <td><?= esc($s['items_out'] ?? 0) ?></td>
                        <td><?= esc($s['out_remarks'] ?? '') ?></td>
                        <td><?= esc($s['branch'] ?? '') ?></td>
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
