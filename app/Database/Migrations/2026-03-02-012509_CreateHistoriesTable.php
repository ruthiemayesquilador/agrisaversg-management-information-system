<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHistoriesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'history_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'description' => [
                'type' => 'TEXT',
            ],
            'timestamp' => [
                'type'    => 'DATETIME',
                'default' => date('Y-m-d H:i:s'),
            ],
        ]);

        $this->forge->addKey('history_id', true);
        $this->forge->createTable('histories');
    }

    public function down()
    {
        //
    }
}
