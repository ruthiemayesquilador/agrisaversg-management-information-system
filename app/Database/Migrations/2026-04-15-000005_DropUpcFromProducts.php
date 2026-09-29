<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropUpcFromProducts extends Migration
{
    private function hasColumn(string $table, string $column): bool
    {
        $result = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE ?", [$column]);

        return $result->getNumRows() > 0;
    }

    public function up()
    {
        if ($this->hasColumn('products', 'upc')) {
            $this->forge->dropColumn('products', 'upc');
        }
    }

    public function down()
    {
        if (! $this->hasColumn('products', 'upc')) {
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
    }
}
