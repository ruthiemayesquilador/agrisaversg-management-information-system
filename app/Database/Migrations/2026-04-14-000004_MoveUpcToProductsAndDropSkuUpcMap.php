<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MoveUpcToProductsAndDropSkuUpcMap extends Migration
{
    private function hasColumn(string $table, string $column): bool
    {
        $result = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE ?", [$column]);

        return $result->getNumRows() > 0;
    }

    private function tableExists(string $table): bool
    {
        $result = $this->db->query('SHOW TABLES LIKE ?', [$table]);

        return $result->getNumRows() > 0;
    }

    public function up()
    {
        if ($this->tableExists('products') && ! $this->hasColumn('products', 'upc')) {
            $this->forge->addColumn('products', [
                'upc' => [
                    'type'       => 'CHAR',
                    'constraint' => 12,
                    'null'       => true,
                    'after'      => 'sku',
                    'comment'    => '12-digit UPC-A value',
                ],
            ]);
        }

        if (! $this->tableExists('products') || ! $this->hasColumn('products', 'upc')) {
            return;
        }

        if ($this->hasColumn('products', 'barcode_number')) {
            $this->db->query(
                "UPDATE `products`
                 SET `upc` = CASE
                    WHEN `barcode_number` REGEXP '^[0-9]{12}$' THEN `barcode_number`
                    WHEN `barcode_number` REGEXP '^0[0-9]{12}$' THEN SUBSTRING(`barcode_number`, 2, 12)
                    ELSE `upc`
                 END
                 WHERE `upc` IS NULL OR `upc` = ''"
            );
        }

        if ($this->tableExists('sku_upc_map')) {
            $this->db->query(
                "UPDATE `products` p
                 JOIN `sku_upc_map` m ON UPPER(TRIM(m.sku)) = UPPER(TRIM(p.sku))
                 SET p.`upc` = CASE
                    WHEN m.`upc` REGEXP '^[0-9]{12}$' THEN m.`upc`
                    WHEN m.`upc` REGEXP '^0[0-9]{12}$' THEN SUBSTRING(m.`upc`, 2, 12)
                    ELSE p.`upc`
                 END
                 WHERE p.`upc` IS NULL OR p.`upc` = ''"
            );
        }

        $this->db->query(
            "UPDATE `products`
             SET `upc` = CASE
                WHEN `upc` REGEXP '^[0-9]{12}$' THEN `upc`
                WHEN `upc` REGEXP '^0[0-9]{12}$' THEN SUBSTRING(`upc`, 2, 12)
                ELSE NULL
             END"
        );

        if ($this->hasColumn('products', 'barcode_type')) {
            $this->db->query(
                "UPDATE `products`
                 SET `barcode_type` = CASE
                    WHEN `upc` REGEXP '^[0-9]{12}$' THEN 'upc'
                    WHEN `barcode_type` = 'code128' THEN 'code128'
                    ELSE NULL
                 END"
            );
        }

        if ($this->tableExists('sku_upc_map')) {
            $this->forge->dropTable('sku_upc_map', true);
        }
    }

    public function down()
    {
        // Intentionally non-reversible.
    }
}
