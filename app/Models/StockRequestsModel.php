<?php

namespace App\Models;

use CodeIgniter\Model;

class StockRequestsModel extends Model
{
    protected $table            = 'stock_requests';
    protected $primaryKey       = 'request_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'requesting_branch',
        'product_id',
        'requested_quantity',
        'reason',
        'status',
        'requested_by',
        'approved_by',
        'fulfilled_at',
    ];

    protected bool $allowEmptyInserts = true;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = '';

    // Validation
    protected $validationRules      = [
        'requesting_branch' => 'required|max_length[100]',
        'product_id'        => 'required|integer',
        'requested_quantity' => 'required|integer|greater_than[0]',
        'status'            => 'in_list[pending,approved,fulfilled,cancelled]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
