<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Products Export</title>
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
    <h1>Products Export</h1>
    <div class="meta">
        Exported at: <?= esc($exportedAt) ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Category</th>
                <th>Supplier</th>
                <th>SKU</th>
                <th>Part No.</th>
                <th>UPC</th>
                <th>Price</th>
                <th>Stock Qty</th>
                <th>Status</th>
                <th>Branch</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="10" class="muted">No product records found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                    <?php $stock = (int) ($p['current_stock'] ?? 0); ?>
                    <?php $status = $stock <= 0 ? 'Out of Stock' : ($stock <= 5 ? 'Low Stock' : 'Available'); ?>
                    <tr>
                        <td><?= esc($p['part_name'] ?? '') ?></td>
                        <td><?= esc($p['category_name'] ?? '') ?></td>
                        <td><?= esc($p['supplier_name'] ?? '') ?></td>
                        <td><?= esc($p['sku'] ?? '') ?></td>
                        <td><?= esc($p['part_no'] ?? '') ?></td>
                        <td><?= esc($p['resolved_upc'] ?? '') ?></td>
                        <td><?= number_format((float) ($p['price'] ?? 0), 2) ?></td>
                        <td><?= esc($stock) ?></td>
                        <td><?= esc($status) ?></td>
                        <td><?= esc($p['branch'] ?? '') ?></td>
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
