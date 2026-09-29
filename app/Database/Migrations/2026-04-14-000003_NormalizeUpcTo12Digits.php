<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NormalizeUpcTo12Digits extends Migration
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
        if ($this->tableExists('products') && $this->hasColumn('products', 'barcode_number')) {
            $this->db->query(
                "UPDATE `products`
                 SET `barcode_number` = CASE
                    WHEN `barcode_number` REGEXP '^[0-9]{12}$' THEN `barcode_number`
                    WHEN `barcode_number` REGEXP '^0[0-9]{12}$' THEN SUBSTRING(`barcode_number`, 2, 12)
                    ELSE NULL
                 END"
            );

            if ($this->hasColumn('products', 'barcode_type')) {
                $this->db->query(
                    "UPDATE `products`
                     SET `barcode_type` = CASE
                        WHEN `barcode_number` IS NOT NULL AND `barcode_number` REGEXP '^[0-9]{12}$' THEN 'upc'
                        WHEN `barcode_type` = 'code128' THEN 'code128'
                        ELSE NULL
                     END"
                );
            }
        }

        if ($this->tableExists('sku_upc_map') && $this->hasColumn('sku_upc_map', 'upc')) {
            $this->db->query(
                "UPDATE `sku_upc_map`
                 SET `upc` = CASE
                    WHEN `upc` REGEXP '^[0-9]{12}$' THEN `upc`
                    WHEN `upc` REGEXP '^0[0-9]{12}$' THEN SUBSTRING(`upc`, 2, 12)
                    ELSE NULL
                 END"
            );

            $this->db->query("DELETE FROM `sku_upc_map` WHERE `upc` IS NULL OR `upc` NOT REGEXP '^[0-9]{12}$'");
        }
    }

    public function down()
    {
        // Data normalization is intentionally non-reversible.
    }
}
