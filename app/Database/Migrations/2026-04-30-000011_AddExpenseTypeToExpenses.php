<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExpenseTypeToExpenses extends Migration
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

    public function up()
    {
        if (! $this->tableExists('expenses') || $this->hasColumn('expenses', 'expense_type')) {
            return;
        }

        $this->forge->addColumn('expenses', [
            'expense_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'expense',
                'after' => 'expense_date',
            ],
        ]);

        $this->db->query("UPDATE `expenses` SET `expense_type` = 'expense' WHERE `expense_type` IS NULL OR `expense_type` = ''");
    }

    public function down()
    {
        if (! $this->tableExists('expenses') || ! $this->hasColumn('expenses', 'expense_type')) {
            return;
        }

        $this->forge->dropColumn('expenses', 'expense_type');
    }
}
