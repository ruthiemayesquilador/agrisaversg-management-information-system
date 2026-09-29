<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExpensesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'expense_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'expense_date' => [
                'type' => 'DATE',
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('expense_id', true);
        $this->forge->createTable('expenses', true); // true = IF NOT EXISTS
    }

    public function down()
    {
        $this->forge->dropTable('expenses');
    }
}
