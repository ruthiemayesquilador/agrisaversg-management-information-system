<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemovePriceFromStocks extends Migration
{
    public function up()
    {
        // Remove price column from stocks table - price should only be in products table
        if ($this->db->fieldExists('price', 'stocks')) {
            $this->forge->dropColumn('stocks', 'price');
        }
    }

    public function down()
    {
        // Restore price column if migration is rolled back
        if (!$this->db->fieldExists('price', 'stocks')) {
            $this->forge->addColumn('stocks', [
                'price' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'part_name',
                ],
            ]);
        }
    }
}
