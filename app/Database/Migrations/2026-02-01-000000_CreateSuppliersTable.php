<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSuppliersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'supplier_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'product_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'supplier_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'brand_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'date_arrival' => [
                'type'   => 'DATE',
                'null'   => true,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('supplier_id', true);
        $this->forge->createTable('suppliers');
    }

    public function down()
    {
        $this->forge->dropTable('suppliers');
    }
}
