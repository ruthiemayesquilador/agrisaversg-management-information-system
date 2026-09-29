<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ScopeProductIdentifiersToBranch extends Migration
{
    private function hasColumn(string $table, string $column): bool
    {
        $result = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE ?", [$column]);

        return $result->getNumRows() > 0;
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = $this->db->query(
            "SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1",
            [$table, $indexName]
        );

        return $result->getNumRows() > 0;
    }

    private function getSingleColumnUniqueIndexes(string $table, string $column): array
    {
        $result = $this->db->query(
            "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND NON_UNIQUE = 0",
            [$table, $column]
        );

        $indexes = [];
        foreach ($result->getResultArray() as $row) {
            $indexName = (string) ($row['INDEX_NAME'] ?? '');
            if ($indexName === '') {
                continue;
            }

            $countResult = $this->db->query(
                "SELECT COUNT(*) AS col_count FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?",
                [$table, $indexName]
            );
            $countRow = $countResult->getRowArray();
            $columnCount = (int) ($countRow['col_count'] ?? 0);

            if ($columnCount === 1) {
                $indexes[] = $indexName;
            }
        }

        return array_values(array_unique($indexes));
    }

    public function up()
    {
        if (! $this->hasColumn('products', 'branch')) {
            return;
        }

        if ($this->hasColumn('products', 'sku')) {
            foreach ($this->getSingleColumnUniqueIndexes('products', 'sku') as $indexName) {
                if ($indexName !== 'uq_products_branch_sku') {
                    $this->db->query("ALTER TABLE `products` DROP INDEX `{$indexName}`");
                }
            }

            if (! $this->indexExists('products', 'uq_products_branch_sku')) {
                $this->db->query("ALTER TABLE `products` ADD UNIQUE KEY `uq_products_branch_sku` (`branch`, `sku`)");
            }
        }

        if ($this->hasColumn('products', 'part_no')) {
            foreach ($this->getSingleColumnUniqueIndexes('products', 'part_no') as $indexName) {
                if ($indexName !== 'uq_products_branch_part_no') {
                    $this->db->query("ALTER TABLE `products` DROP INDEX `{$indexName}`");
                }
            }

            if (! $this->indexExists('products', 'uq_products_branch_part_no')) {
                $this->db->query("ALTER TABLE `products` ADD UNIQUE KEY `uq_products_branch_part_no` (`branch`, `part_no`)");
            }
        }
    }

    public function down()
    {
        if (! $this->hasColumn('products', 'branch')) {
            return;
        }

        if ($this->indexExists('products', 'uq_products_branch_sku')) {
            $this->db->query("ALTER TABLE `products` DROP INDEX `uq_products_branch_sku`");
        }

        if ($this->indexExists('products', 'uq_products_branch_part_no')) {
            $this->db->query("ALTER TABLE `products` DROP INDEX `uq_products_branch_part_no`");
        }

        if ($this->hasColumn('products', 'sku') && ! $this->indexExists('products', 'sku')) {
            $this->db->query("ALTER TABLE `products` ADD UNIQUE KEY `sku` (`sku`)");
        }

        if ($this->hasColumn('products', 'part_no') && ! $this->indexExists('products', 'part_no')) {
            $this->db->query("ALTER TABLE `products` ADD UNIQUE KEY `part_no` (`part_no`)");
        }
    }
}
