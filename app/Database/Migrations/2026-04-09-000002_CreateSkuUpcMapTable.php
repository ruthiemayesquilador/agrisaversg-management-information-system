<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSkuUpcMapTable extends Migration
{
    private function tableExists(string $table): bool
    {
        $result = $this->db->query('SHOW TABLES LIKE ?', [$table]);
        return $result->getNumRows() > 0;
    }

    public function up()
    {
        if (! $this->tableExists('sku_upc_map')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'sku' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => false,
                ],
                'upc' => [
                    'type'       => 'CHAR',
                    'constraint' => 12,
                    'null'       => false,
                    'comment'    => '12-digit UPC-A value without spaces',
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
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('sku', 'sku_upc_map_sku_unique');
            $this->forge->createTable('sku_upc_map', true);
        }

        $now = date('Y-m-d H:i:s');
        $rows = [
            ['sku' => 'B69.1', 'upc' => '000000012850'],
            ['sku' => 'A1', 'upc' => '000000000024'],
            ['sku' => 'A2.1', 'upc' => '000000000031'],
            ['sku' => 'A32', 'upc' => '000000000338'],
            ['sku' => 'A2', 'upc' => '000000002509'],
            ['sku' => 'A3', 'upc' => '000000002516'],
            ['sku' => 'A3.1', 'upc' => '000000000048'],
            ['sku' => 'A4', 'upc' => '000000000055'],
            ['sku' => 'A5', 'upc' => '000000000062'],
            ['sku' => 'A33', 'upc' => '000000000345'],
            ['sku' => 'A6', 'upc' => '000000000079'],
            ['sku' => 'A7', 'upc' => '000000000086'],
            ['sku' => 'A8', 'upc' => '000000000093'],
            ['sku' => 'A9', 'upc' => '000000000109'],
            ['sku' => 'A10', 'upc' => '000000000116'],
            ['sku' => 'A11', 'upc' => '000000000123'],
            ['sku' => 'A11.1', 'upc' => '000000000130'],
            ['sku' => 'A12', 'upc' => '000000000147'],
            ['sku' => 'A13', 'upc' => '000000000154'],
            ['sku' => 'A14', 'upc' => '000000000161'],
            ['sku' => 'A15', 'upc' => '000000000178'],
            ['sku' => 'A16', 'upc' => '000000000185'],
            ['sku' => 'A18', 'upc' => '000000000192'],
            ['sku' => 'A19', 'upc' => '000000000208'],
            ['sku' => 'A20', 'upc' => '000000000215'],
            ['sku' => 'A21', 'upc' => '000000000222'],
            ['sku' => 'A22', 'upc' => '000000000239'],
            ['sku' => 'A23', 'upc' => '000000000246'],
            ['sku' => 'A24', 'upc' => '000000000253'],
            ['sku' => 'A25', 'upc' => '000000000260'],
        ];

        foreach ($rows as $row) {
            $exists = $this->db->table('sku_upc_map')->where('sku', $row['sku'])->countAllResults();
            if ($exists === 0) {
                $this->db->table('sku_upc_map')->insert([
                    'sku'        => $row['sku'],
                    'upc'        => $row['upc'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down()
    {
        if ($this->tableExists('sku_upc_map')) {
            $this->forge->dropTable('sku_upc_map', true);
        }
    }
}
