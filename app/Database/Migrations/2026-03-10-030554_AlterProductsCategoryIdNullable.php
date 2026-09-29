<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterProductsCategoryIdNullable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('products', [
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('products', [
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
        ]);
    }
}
