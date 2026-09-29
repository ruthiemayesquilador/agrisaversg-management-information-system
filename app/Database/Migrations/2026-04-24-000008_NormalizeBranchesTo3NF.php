<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NormalizeBranchesTo3NF extends Migration
{
    private function tableExists(string $table): bool
    {
        $result = $this->db->query('SHOW TABLES LIKE ?', [$table]);
        return $result->getNumRows() > 0;
    }

    private function hasColumn(string $table, string $column): bool
    {
        if (! $this->tableExists($table)) {
            return false;
        }

        $result = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE ?", [$column]);
        return $result->getNumRows() > 0;
    }

    private function normalizeBranchName(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '';
        }

        $name = preg_replace('/\s+/', ' ', $name) ?? $name;
        $name = preg_replace('/\s+branch$/i', '', $name) ?? $name;
        $name = trim($name);

        return strtolower($name);
    }

    private function displayBranchName(string $normalized): string
    {
        if ($normalized === '') {
            return '';
        }

        $pretty = ucwords($normalized);
        return $pretty . ' Branch';
    }

    private function upsertBranch(string $rawName): ?int
    {
        $normalized = $this->normalizeBranchName($rawName);
        if ($normalized === '') {
            return null;
        }

        $row = $this->db->table('branches')
            ->where('normalized_name', $normalized)
            ->get()
            ->getRowArray();

        if ($row) {
            return (int) $row['branch_id'];
        }

        $this->db->table('branches')->insert([
            'branch_name' => $this->displayBranchName($normalized),
            'normalized_name' => $normalized,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $idRow = $this->db->query('SELECT LAST_INSERT_ID() AS id')->getRowArray();
        return isset($idRow['id']) ? (int) $idRow['id'] : null;
    }

    private function addBranchIdColumn(string $table, string $column): void
    {
        if (! $this->tableExists($table) || $this->hasColumn($table, $column)) {
            return;
        }

        $this->forge->addColumn($table, [
            $column => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
            ],
        ]);

        $this->db->query("CREATE INDEX `idx_{$table}_{$column}` ON `{$table}` (`{$column}`)");
    }

    private function backfillBranchId(string $table, string $pk, string $branchColumn, string $branchIdColumn): void
    {
        if (! $this->tableExists($table) || ! $this->hasColumn($table, $pk) || ! $this->hasColumn($table, $branchColumn) || ! $this->hasColumn($table, $branchIdColumn)) {
            return;
        }

        $rows = $this->db->table($table)
            ->select($pk . ', ' . $branchColumn)
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $branchId = $this->upsertBranch((string) ($row[$branchColumn] ?? ''));
            if ($branchId === null) {
                continue;
            }

            $this->db->table($table)
                ->where($pk, $row[$pk])
                ->update([$branchIdColumn => $branchId]);
        }
    }

    private function addForeignKeyIfMissing(string $table, string $column, string $constraintName): void
    {
        if (! $this->tableExists($table) || ! $this->hasColumn($table, $column) || ! $this->tableExists('branches')) {
            return;
        }

        $dbName = $this->db->getDatabase();
        $existing = $this->db->query(
            'SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$dbName, $table, $column]
        )->getResultArray();

        if (! empty($existing)) {
            return;
        }

        $this->db->query(
            "ALTER TABLE `{$table}` ADD CONSTRAINT `{$constraintName}` FOREIGN KEY (`{$column}`) REFERENCES `branches`(`branch_id`) ON UPDATE CASCADE ON DELETE SET NULL"
        );
    }

    public function up()
    {
        if (! $this->tableExists('branches')) {
            $this->forge->addField([
                'branch_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'branch_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => false,
                ],
                'normalized_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => false,
                ],
                'is_active' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('branch_id', true);
            $this->forge->addUniqueKey('branch_name', 'uq_branches_branch_name');
            $this->forge->addUniqueKey('normalized_name', 'uq_branches_normalized_name');
            $this->forge->createTable('branches', true);
        }

        // Add FK columns to branch-dependent tables.
        $this->addBranchIdColumn('users', 'branch_id');
        $this->addBranchIdColumn('products', 'branch_id');
        $this->addBranchIdColumn('sales', 'branch_id');
        $this->addBranchIdColumn('expenses', 'branch_id');
        $this->addBranchIdColumn('histories', 'branch_id');
        $this->addBranchIdColumn('product_transfers', 'from_branch_id');
        $this->addBranchIdColumn('product_transfers', 'to_branch_id');

        // Backfill branch IDs from current text columns.
        $this->backfillBranchId('users', 'id', 'branch', 'branch_id');
        $this->backfillBranchId('products', 'product_id', 'branch', 'branch_id');
        $this->backfillBranchId('sales', 'sale_id', 'branch', 'branch_id');
        $this->backfillBranchId('expenses', 'expense_id', 'branch', 'branch_id');
        $this->backfillBranchId('histories', 'history_id', 'branch', 'branch_id');
        $this->backfillBranchId('product_transfers', 'transfer_id', 'from_branch', 'from_branch_id');
        $this->backfillBranchId('product_transfers', 'transfer_id', 'to_branch', 'to_branch_id');

        // Add foreign keys last, after data is backfilled.
        $this->addForeignKeyIfMissing('users', 'branch_id', 'fk_users_branch_id');
        $this->addForeignKeyIfMissing('products', 'branch_id', 'fk_products_branch_id');
        $this->addForeignKeyIfMissing('sales', 'branch_id', 'fk_sales_branch_id');
        $this->addForeignKeyIfMissing('expenses', 'branch_id', 'fk_expenses_branch_id');
        $this->addForeignKeyIfMissing('histories', 'branch_id', 'fk_histories_branch_id');
        $this->addForeignKeyIfMissing('product_transfers', 'from_branch_id', 'fk_transfers_from_branch_id');
        $this->addForeignKeyIfMissing('product_transfers', 'to_branch_id', 'fk_transfers_to_branch_id');
    }

    public function down()
    {
        $dropFk = function (string $table, string $constraintName): void {
            if (! $this->tableExists($table)) {
                return;
            }

            try {
                $this->db->query("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraintName}`");
            } catch (\Throwable $e) {
                // Ignore missing constraints.
            }
        };

        $dropFk('users', 'fk_users_branch_id');
        $dropFk('products', 'fk_products_branch_id');
        $dropFk('sales', 'fk_sales_branch_id');
        $dropFk('expenses', 'fk_expenses_branch_id');
        $dropFk('histories', 'fk_histories_branch_id');
        $dropFk('product_transfers', 'fk_transfers_from_branch_id');
        $dropFk('product_transfers', 'fk_transfers_to_branch_id');

        if ($this->hasColumn('users', 'branch_id')) {
            $this->forge->dropColumn('users', 'branch_id');
        }
        if ($this->hasColumn('products', 'branch_id')) {
            $this->forge->dropColumn('products', 'branch_id');
        }
        if ($this->hasColumn('sales', 'branch_id')) {
            $this->forge->dropColumn('sales', 'branch_id');
        }
        if ($this->hasColumn('expenses', 'branch_id')) {
            $this->forge->dropColumn('expenses', 'branch_id');
        }
        if ($this->hasColumn('histories', 'branch_id')) {
            $this->forge->dropColumn('histories', 'branch_id');
        }
        if ($this->hasColumn('product_transfers', 'from_branch_id')) {
            $this->forge->dropColumn('product_transfers', 'from_branch_id');
        }
        if ($this->hasColumn('product_transfers', 'to_branch_id')) {
            $this->forge->dropColumn('product_transfers', 'to_branch_id');
        }

        if ($this->tableExists('branches')) {
            $this->forge->dropTable('branches');
        }
    }
}
