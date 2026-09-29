<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'sale_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'csi' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'sale_date' => [
                'type' => 'DATE',
            ],
            'cash_sales' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
                'default'    => null,
            ],
            'payment_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
            ],
            'account_receivable' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
                'default'    => null,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('sale_id', true);
        $this->forge->createTable('sales', true); // true = IF NOT EXISTS
    }

    public function down()
    {
        $this->forge->dropTable('sales');
    }
}
