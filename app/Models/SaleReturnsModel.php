<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleReturnsModel extends Model
{
    protected $table            = 'sale_returns';
    protected $primaryKey       = 'return_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'sale_id',
        'sale_item_id',
        'product_id',
        'action_type',
        'qty',
        'unit_price',
        'return_amount',
        'exchange_amount',
        'net_adjustment',
        'reason',
        'processed_by',
        'branch',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $deletedField  = '';
}
