<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingColumnsToHistories extends Migration
{
    private function hasColumn(string $column): bool
    {
        $result = $this->db->query("SHOW COLUMNS FROM `histories` LIKE ?", [$column]);

        return $result->getNumRows() > 0;
    }

    public function up()
    {
        if (!$this->db->tableExists('histories')) {
            return;
        }

        $columns = [];

        if (!$this->hasColumn('module')) {
            $columns['module'] = [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ];
        }

        if (!$this->hasColumn('branch')) {
            $columns['branch'] = [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ];
        }

        if (!$this->hasColumn('item_id')) {
            $columns['item_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ];
        }

        if (!$this->hasColumn('item_name')) {
            $columns['item_name'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ];
        }

        if (!$this->hasColumn('part_no')) {
            $columns['part_no'] = [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ];
        }

        if ($columns !== []) {
            $this->forge->addColumn('histories', $columns);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('histories')) {
            return;
        }

        foreach (['module', 'branch', 'item_id', 'item_name', 'part_no'] as $column) {
            if ($this->hasColumn($column)) {
                $this->forge->dropColumn('histories', $column);
            }
        }
    }
}
