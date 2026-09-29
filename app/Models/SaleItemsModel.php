<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleItemsModel extends Model
{
    protected $table            = 'sale_items';
    protected $primaryKey       = 'sale_item_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'sale_id',
        'product_id',
        'sku',
        'part_name',
        'unit_price',
        'qty',
        'returned_qty',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $deletedField  = '';
}
