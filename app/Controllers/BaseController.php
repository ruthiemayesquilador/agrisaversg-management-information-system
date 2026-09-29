<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /** @var array<string, bool> */
    private static array $columnExistsCache = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /**
     * Write activity history safely without interrupting the main request flow.
     */
    protected function logHistorySafe(
        string $action,
        string $description,
        ?int $itemId = null,
        ?string $itemName = null,
        ?string $partNo = null,
        ?string $module = null
    ): void {
        $userId = (int) (session()->get('user_id') ?? session()->get('id') ?? 0);
        if ($userId <= 0) {
            return;
        }

        try {
            (new \App\Models\HistoryModel())->log(
                $action,
                $description,
                $userId,
                $itemId,
                $itemName,
                $partNo,
                $module
            );
        } catch (\Throwable $e) {
            log_message('error', 'History logging failed: {error}', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Returns true when the specified table has the requested column.
     */
    protected function tableHasColumn(string $table, string $column): bool
    {
        $cacheKey = strtolower($table . '.' . $column);
        if (array_key_exists($cacheKey, self::$columnExistsCache)) {
            return self::$columnExistsCache[$cacheKey];
        }

        try {
            $db = \Config\Database::connect();
            $fieldNames = array_map('strtolower', $db->getFieldNames($table));
            return self::$columnExistsCache[$cacheKey] = in_array(strtolower($column), $fieldNames, true);
        } catch (\Throwable $e) {
            log_message('warning', 'Failed checking schema for {table}.{column}: {error}', [
                'table'  => $table,
                'column' => $column,
                'error'  => $e->getMessage(),
            ]);
            return self::$columnExistsCache[$cacheKey] = false;
        }
    }

    protected function tableHasBranchColumn(string $table): bool
    {
        return $this->tableHasColumn($table, 'branch');
    }
}
