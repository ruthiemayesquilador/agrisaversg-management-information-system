<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductTransfersModel extends Model
{
    protected $table            = 'product_transfers';
    protected $primaryKey       = 'transfer_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'product_id',
        'from_branch',
        'from_branch_id',
        'to_branch',
        'to_branch_id',
        'recipient_user_id',
        'quantity',
        'status',
        'remarks',
        'transferred_by',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'product_id'   => 'required|is_natural_no_zero',
        'from_branch'  => 'required|min_length[2]|max_length[100]',
        'to_branch'    => 'required|min_length[2]|max_length[100]',
        'quantity'     => 'required|is_natural_no_zero',
        'status'       => 'in_list[pending,completed,cancelled]',
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

    /**
     * Get transfers with product details
     */
    public function getTransfersWithProducts($limit = null, $offset = 0)
    {
        $builder = $this->builder()
            ->select('product_transfers.*, products.part_name, products.sku, products.branch, suppliers.supplier_name')
            ->join('products', 'products.product_id = product_transfers.product_id', 'left')
            ->join('suppliers', 'suppliers.supplier_id = products.supplier_id', 'left');

        if ($limit) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get pending transfers
     */
    public function getPendingTransfers()
    {
        return $this->where('status', 'pending')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get transfers for specific branch
     */
    public function getTransfersForBranch($branch)
    {
        return $this->where('from_branch', $branch)
            ->orWhere('to_branch', $branch)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
