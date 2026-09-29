<?php

namespace App\Models;

use CodeIgniter\Model;

class HistoryModel extends Model
{
    protected $table            = 'history';
    protected $primaryKey       = 'history_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'module',
        'branch',
        'action',
        'item_id',
        'item_name',
        'part_no',
        'description',
        'timestamp',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates — managed manually via 'timestamp' field
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /** @var string[]|null */
    private ?array $tableColumnsCache = null;

    protected function initialize()
    {
        // Prefer existing live table, with safe fallback for older/newer schemas.
        if ($this->db->tableExists('history')) {
            $this->table = 'history';
        } elseif ($this->db->tableExists('histories')) {
            $this->table = 'histories';
        }
    }

    /** @return string[] */
    private function getTableColumns(): array
    {
        if ($this->tableColumnsCache !== null) {
            return $this->tableColumnsCache;
        }

        try {
            $this->tableColumnsCache = $this->db->getFieldNames($this->table) ?: [];
        } catch (\Throwable $e) {
            $this->tableColumnsCache = [];
        }

        return $this->tableColumnsCache;
    }

    private function hasColumn(string $column): bool
    {
        return in_array($column, $this->getTableColumns(), true);
    }

    private function selectOrNull(string $column, ?string $alias = null): string
    {
        $alias = $alias ?: $column;
        return $this->hasColumn($column)
            ? $column . ' AS ' . $alias
            : 'NULL AS ' . $alias;
    }

    /**
     * Returns history records filtered by date range, action keyword, module, and branch.
     *
     * @param string|null $dateFrom  Start date (Y-m-d)
     * @param string|null $dateTo    End date (Y-m-d)
     * @param string|null $action    Partial match on action column
     * @param string      $sortDir   'ASC' or 'DESC'
     * @param string|null $module    Exact match on module column
     * @param string|null $branch    Exact match on branch column
     */
    public function getFiltered(
        ?string $dateFrom = null,
        ?string $dateTo   = null,
        ?string $action   = null,
        string  $sortDir  = 'ASC',
        ?string $module   = null,
        ?string $branch   = null
    ): array {
        $builder = $this->db->table($this->table);
        $builder->select(implode(', ', [
            $this->selectOrNull('history_id'),
            $this->selectOrNull('user_id'),
            $this->selectOrNull('module'),
            $this->selectOrNull('branch'),
            $this->selectOrNull('action'),
            $this->selectOrNull('item_id'),
            $this->selectOrNull('item_name'),
            $this->selectOrNull('part_no'),
            $this->selectOrNull('description'),
            $this->selectOrNull('timestamp', 'date'),
        ]));

        if ($dateFrom && $this->hasColumn('timestamp')) {
            $builder->where('DATE(timestamp) >=', $dateFrom);
        }

        if ($dateTo && $this->hasColumn('timestamp')) {
            $builder->where('DATE(timestamp) <=', $dateTo);
        }

        if ($action && $this->hasColumn('action')) {
            $builder->like('action', $action);
        }

        if ($module && $this->hasColumn('module')) {
            $builder->where('module', $module);
        }

        if ($branch && $this->hasColumn('branch')) {
            $builder->where('branch', $branch);
        }

        if ($this->hasColumn('timestamp')) {
            $builder->orderBy('timestamp', $sortDir);
        } elseif ($this->hasColumn('history_id')) {
            $builder->orderBy('history_id', $sortDir);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Returns distinct branch values stored in history (for filter dropdown).
     */
    public function getDistinctBranches(): array
    {
        if (!$this->hasColumn('branch')) {
            return [];
        }

        return $this->db->table($this->table)
            ->select('branch')
            ->distinct()
            ->where('branch IS NOT NULL')
            ->where('branch !=', '')
            ->orderBy('branch', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Logs a new history entry. Branch is automatically captured from the current session.
     */
    public function log(string $action, string $description, ?int $userId = null, ?int $itemId = null, ?string $itemName = null, ?string $partNo = null, ?string $module = null): bool
    {
        $payload = [
            'user_id'     => $userId,
            'module'      => $module,
            'branch'      => session()->get('branch') ?? null,
            'action'      => $action,
            'item_id'     => $itemId,
            'item_name'   => $itemName,
            'part_no'     => $partNo,
            'description' => $description,
            'timestamp'   => date('Y-m-d H:i:s'),
        ];

        $columns = array_flip($this->getTableColumns());
        if (empty($columns)) {
            return false;
        }

        $filtered = array_intersect_key($payload, $columns);

        // Minimal columns required for a meaningful audit row.
        if (!array_key_exists('action', $filtered) || !array_key_exists('description', $filtered)) {
            return false;
        }

        return $this->insert($filtered) !== false;
    }
}
