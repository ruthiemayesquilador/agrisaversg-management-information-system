<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterProductsSkuNullable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('products', [
            'sku' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
            ],
            'part_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('products', [
            'sku' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'part_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
        ]);
    }
}
