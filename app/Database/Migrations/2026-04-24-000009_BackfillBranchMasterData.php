<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BackfillBranchMasterData extends Migration
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

    private function normalizeBranch(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '';
        }

        $name = preg_replace('/\s+/', ' ', $name) ?? $name;
        $name = preg_replace('/\s+branch$/i', '', $name) ?? $name;

        return strtolower(trim($name));
    }

    private function displayName(string $normalized): string
    {
        return ucwords($normalized) . ' Branch';
    }

    private function ensureBranch(string $branchText): ?int
    {
        $normalized = $this->normalizeBranch($branchText);
        if ($normalized === '') {
            return null;
        }

        $existing = $this->db->table('branches')
            ->select('branch_id')
            ->where('normalized_name', $normalized)
            ->get()
            ->getRowArray();

        if ($existing) {
            return (int) $existing['branch_id'];
        }

        $this->db->table('branches')->insert([
            'branch_name' => $this->displayName($normalized),
            'normalized_name' => $normalized,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $idRow = $this->db->query('SELECT LAST_INSERT_ID() AS id')->getRowArray();
        return isset($idRow['id']) ? (int) $idRow['id'] : null;
    }

    private function backfill(string $table, string $pk, string $sourceColumn, string $targetColumn): void
    {
        if (! $this->tableExists($table) || ! $this->hasColumn($table, $pk) || ! $this->hasColumn($table, $sourceColumn) || ! $this->hasColumn($table, $targetColumn)) {
            return;
        }

        $rows = $this->db->table($table)
            ->select($pk . ', ' . $sourceColumn)
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $branchId = $this->ensureBranch((string) ($row[$sourceColumn] ?? ''));
            if ($branchId === null) {
                continue;
            }

            $this->db->table($table)
                ->where($pk, $row[$pk])
                ->update([$targetColumn => $branchId]);
        }
    }

    public function up()
    {
        if (! $this->tableExists('branches')) {
            return;
        }

        $this->backfill('users', 'id', 'branch', 'branch_id');
        $this->backfill('products', 'product_id', 'branch', 'branch_id');
        $this->backfill('sales', 'sale_id', 'branch', 'branch_id');
        $this->backfill('expenses', 'expense_id', 'branch', 'branch_id');
        $this->backfill('histories', 'history_id', 'branch', 'branch_id');
        $this->backfill('product_transfers', 'transfer_id', 'from_branch', 'from_branch_id');
        $this->backfill('product_transfers', 'transfer_id', 'to_branch', 'to_branch_id');
    }

    public function down()
    {
        // Intentionally left blank: this is a data backfill migration.
    }
}
