<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductTransfersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'transfer_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'product_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'from_branch' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'to_branch' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'pending',
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'transferred_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
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

        $this->forge->addKey('transfer_id', true);
        $this->forge->createTable('product_transfers');
    }

    public function down()
    {
        $this->forge->dropTable('product_transfers');
    }
}

