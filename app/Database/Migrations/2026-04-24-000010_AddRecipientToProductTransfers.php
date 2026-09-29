<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRecipientToProductTransfers extends Migration
{
    private function tableExists(string $table): bool
    {
        $result = $this->db->query('SHOW TABLES LIKE ?', [$table]);
        return $result->getNumRows() > 0;
    }

    private function hasColumn(string $table, string $column): bool
    {
        if (! $this->tableExists($table)) {
            return false;
        }

        $result = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE ?", [$column]);
        return $result->getNumRows() > 0;
    }

    public function up()
    {
        if (! $this->tableExists('product_transfers') || $this->hasColumn('product_transfers', 'recipient_user_id')) {
            return;
        }

        $this->forge->addColumn('product_transfers', [
            'recipient_user_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
                'after' => 'to_branch_id',
            ],
        ]);

        $this->db->query('CREATE INDEX `idx_product_transfers_recipient_user_id` ON `product_transfers` (`recipient_user_id`)');

        if ($this->tableExists('users')) {
            $this->db->query(
                'ALTER TABLE `product_transfers` ADD CONSTRAINT `fk_product_transfers_recipient_user` FOREIGN KEY (`recipient_user_id`) REFERENCES `users`(`id`) ON UPDATE CASCADE ON DELETE SET NULL'
            );
        }
    }

    public function down()
    {
        if (! $this->tableExists('product_transfers') || ! $this->hasColumn('product_transfers', 'recipient_user_id')) {
            return;
        }

        try {
            $this->db->query('ALTER TABLE `product_transfers` DROP FOREIGN KEY `fk_product_transfers_recipient_user`');
        } catch (\Throwable $e) {
            // Ignore missing FK.
        }

        $this->forge->dropColumn('product_transfers', 'recipient_user_id');
    }
}
