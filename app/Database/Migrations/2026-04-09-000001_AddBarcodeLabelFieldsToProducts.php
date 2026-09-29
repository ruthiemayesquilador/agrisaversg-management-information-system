<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBarcodeLabelFieldsToProducts extends Migration
{
    private function hasColumn(string $table, string $column): bool
    {
        $result = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE ?", [$column]);

        return $result->getNumRows() > 0;
    }

    public function up()
    {
        $fields = [];

        if (! $this->hasColumn('products', 'barcode_number')) {
            $fields['barcode_number'] = [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'null'       => true,
                'after'      => 'sku',
                'comment'    => 'Printable barcode payload (UPC/CODE128 value)',
            ];
        }

        if (! $this->hasColumn('products', 'barcode_type')) {
            $fields['barcode_type'] = [
                'type'       => 'ENUM',
                'constraint' => ['upc', 'code128'],
                'null'       => true,
                'after'      => 'barcode_number',
                'comment'    => 'Preferred barcode symbology for labels',
            ];
        }

        if (! $this->hasColumn('products', 'barcode_check_digit')) {
            $fields['barcode_check_digit'] = [
                'type'       => 'CHAR',
                'constraint' => 1,
                'null'       => true,
                'after'      => 'barcode_type',
                'comment'    => 'Optional check digit for numeric barcodes',
            ];
        }

        if (! $this->hasColumn('products', 'label_brand')) {
            $fields['label_brand'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'barcode_check_digit',
                'comment'    => 'Short brand code printed on the label header',
            ];
        }

        if (! $this->hasColumn('products', 'label_short_name')) {
            $fields['label_short_name'] = [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'after'      => 'label_brand',
                'comment'    => 'Short printable item name for compact labels',
            ];
        }

        if (! $this->hasColumn('products', 'label_template')) {
            $fields['label_template'] = [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'after'      => 'label_short_name',
                'comment'    => 'Optional named label template override',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('products', $fields);
        }
    }

    public function down()
    {
        $columns = [
            'label_template',
            'label_short_name',
            'label_brand',
            'barcode_check_digit',
            'barcode_type',
            'barcode_number',
        ];

        foreach ($columns as $column) {
            if ($this->hasColumn('products', $column)) {
                $this->forge->dropColumn('products', $column);
            }
        }
    }
}
