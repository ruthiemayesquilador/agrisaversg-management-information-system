<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStocksTableFull extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'stock_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sku' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'stock_num' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
            ],
            'part_num' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
            ],
            'part_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
                'default'    => null,
            ],
            'stocks' => [
                'type'    => 'INT',
                'null'    => true,
                'default' => 0,
            ],
            'beg_inv' => [
                'type'    => 'INT',
                'null'    => true,
                'default' => 0,
            ],
            'items_in' => [
                'type'    => 'INT',
                'null'    => true,
                'default' => 0,
            ],
            'in_remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'items_out' => [
                'type'    => 'INT',
                'null'    => true,
                'default' => 0,
            ],
            'out_remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('stock_id', true);
        $this->forge->createTable('stocks', true); // IF NOT EXISTS
    }

    public function down()
    {
        $this->forge->dropTable('stocks');
    }
}
