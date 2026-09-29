<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDescriptionToProducts extends Migration
{
    private function hasColumn(string $table, string $column): bool
    {
        $result = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE ?", [$column]);

        return $result->getNumRows() > 0;
    }

    public function up()
    {
        if (! $this->hasColumn('products', 'description')) {
            $this->forge->addColumn('products', [
                'description' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'part_name',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->hasColumn('products', 'description')) {
            $this->forge->dropColumn('products', 'description');
        }
    }
}
