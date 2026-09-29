<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPosReturnsAndVoucherFields extends Migration
{
    public function up()
    {
        if ($this->tableExists('sales')) {
            $fields = [];
            $existing = array_map('strtolower', $this->getFieldNamesSafe('sales'));

            if (!in_array('customer_name', $existing, true)) {
                $fields['customer_name'] = [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'default' => null,
                ];
            }

            if (!in_array('customer_address', $existing, true)) {
                $fields['customer_address'] = [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'default' => null,
                ];
            }

            if (!in_array('gross_total', $existing, true)) {
                $fields['gross_total'] = [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'null' => true,
                    'default' => null,
                ];
            }

            if (!in_array('discount_amount', $existing, true)) {
                $fields['discount_amount'] = [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'null' => true,
                    'default' => null,
                ];
            }

            if (!in_array('net_total', $existing, true)) {
                $fields['net_total'] = [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'null' => true,
                    'default' => null,
                ];
            }

            if (!in_array('voucher_code', $existing, true)) {
                $fields['voucher_code'] = [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'default' => null,
                ];
            }

            if (!in_array('parent_sale_id', $existing, true)) {
                $fields['parent_sale_id'] = [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                ];
            }

            if (!in_array('sale_type', $existing, true)) {
                $fields['sale_type'] = [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => true,
                    'default' => null,
                ];
            }

            if (!empty($fields)) {
                $this->forge->addColumn('sales', $fields);
            }
        }

        if (!$this->tableExists('sale_items')) {
            $this->forge->addField([
                'sale_item_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'sale_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'product_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'sku' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'default' => null,
                ],
                'part_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'default' => null,
                ],
                'unit_price' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                ],
                'qty' => [
                    'type' => 'INT',
                    'constraint' => 11,
                ],
                'returned_qty' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('sale_item_id', true);
            $this->forge->addKey('sale_id');
            $this->forge->addKey('product_id');
            $this->forge->createTable('sale_items', true);
        }

        if (!$this->tableExists('sale_returns')) {
            $this->forge->addField([
                'return_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'sale_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'sale_item_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                ],
                'product_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                ],
                'action_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                ],
                'qty' => [
                    'type' => 'INT',
                    'constraint' => 11,
                ],
                'unit_price' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                ],
                'return_amount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'default' => 0,
                ],
                'exchange_amount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'default' => 0,
                ],
                'net_adjustment' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'default' => 0,
                ],
                'reason' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'processed_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                ],
                'branch' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'default' => null,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('return_id', true);
            $this->forge->addKey('sale_id');
            $this->forge->addKey('product_id');
            $this->forge->createTable('sale_returns', true);
        }
    }

    public function down()
    {
        if ($this->tableExists('sale_returns')) {
            $this->forge->dropTable('sale_returns');
        }

        if ($this->tableExists('sale_items')) {
            $this->forge->dropTable('sale_items');
        }

        if ($this->tableExists('sales')) {
            $existing = array_map('strtolower', $this->getFieldNamesSafe('sales'));
            $drop = [];
            foreach (['customer_name', 'customer_address', 'gross_total', 'discount_amount', 'net_total', 'voucher_code', 'parent_sale_id', 'sale_type'] as $col) {
                if (in_array($col, $existing, true)) {
                    $drop[] = $col;
                }
            }
            if (!empty($drop)) {
                $this->forge->dropColumn('sales', $drop);
            }
        }
    }

    /** @return string[] */
    private function getFieldNamesSafe(string $table): array
    {
        try {
            $rows = $this->db->query('SHOW COLUMNS FROM `' . str_replace('`', '``', $table) . '`')->getResultArray();
            $fields = [];
            foreach ($rows as $row) {
                $name = (string) ($row['Field'] ?? '');
                if ($name !== '') {
                    $fields[] = $name;
                }
            }
            return $fields;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function tableExists(string $table): bool
    {
        try {
            $safe = str_replace(['%', '_'], ['\\%', '\\_'], $table);
            return $this->db->query('SHOW TABLES LIKE ?', [$safe])->getNumRows() > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
