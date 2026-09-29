<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockRequestsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'request_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'requesting_branch' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'product_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'requested_quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'default'    => 0,
            ],
            'reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'fulfilled', 'cancelled'],
                'default'    => 'pending',
            ],
            'requested_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'approved_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'fulfilled_at' => [
                'type' => 'DATETIME',
                'null' => true,
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

        $this->forge->addKey('request_id', true);
        $this->forge->addKey('product_id');
        $this->forge->addKey('requesting_branch');
        $this->forge->addKey('status');
        $this->forge->createTable('stock_requests', true);
    }

    public function down()
    {
        $this->forge->dropTable('stock_requests');
    }
}
