<?php

namespace App\Models;

use CodeIgniter\Model;

class SuppliersModel extends Model
{
    protected $table            = 'suppliers';
    protected $primaryKey       = 'supplier_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'supplier_name',
        'brand_name',
        'importer',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'supplier_name' => 'required|min_length[2]|max_length[255]',
        'brand_name'    => 'permit_empty|max_length[100]',
        'importer'      => 'permit_empty|in_list[Local,Overseas]',
    ];
    protected $validationMessages   = [
        'supplier_name' => [
            'required'   => 'Supplier Name is required.',
            'min_length' => 'Supplier Name must be at least 2 characters.',
            'max_length' => 'Supplier Name cannot exceed 255 characters.',
        ],
        'importer' => [
            'in_list' => 'Importer must be Local or Overseas.',
        ],
    ];
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
