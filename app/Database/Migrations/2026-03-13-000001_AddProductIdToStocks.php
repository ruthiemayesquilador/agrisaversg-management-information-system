<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductIdToStocks extends Migration
{
    private function hasColumn(string $table, string $column): bool
    {
        $result = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE ?", [$column]);
        return $result->getNumRows() > 0;
    }

    public function up()
    {
        // Check if column already exists before adding
        if (!$this->hasColumn('stocks', 'product_id')) {
            $this->forge->addColumn('stocks', [
                'product_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'comment'    => 'Reference to products table'
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->hasColumn('stocks', 'product_id')) {
            $this->forge->dropColumn('stocks', 'product_id');
        }
    }
}
