<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\HistoryModel;

class HistoryController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $historyModel = new HistoryModel();

        $userBranch = session()->get('branch') ?? '';
        $isPolangui = stripos($userBranch, 'Polangui') !== false;

        $dateFrom = $this->request->getGet('date_from') ?? '';
        $dateTo   = $this->request->getGet('date_to')   ?? '';
        $action   = $this->request->getGet('action')    ?? '';
        $module   = $this->request->getGet('module')    ?? '';
        $branch   = $this->request->getGet('branch')    ?? '';
        // Default to latest first when sort direction is not provided.
        $sortDirParam = strtolower((string) ($this->request->getGet('sort_dir') ?? ''));
        $sortDir = $sortDirParam === 'asc' ? 'ASC' : 'DESC';

        // Non-Polangui users are restricted to their own branch
        if (!$isPolangui) {
            $branch = $userBranch;
        }

        $history = $historyModel->getFiltered(
            $dateFrom ?: null,
            $dateTo   ?: null,
            $action   ?: null,
            $sortDir,
            $module   ?: null,
            $branch   ?: null
        );

        // Fetch distinct branches for the filter dropdown (Polangui users only)
        $branches = $isPolangui ? $historyModel->getDistinctBranches() : [];

        $data = [
            'histories'  => $history,
            'dateFrom'   => $dateFrom,
            'dateTo'     => $dateTo,
            'action'     => $action,
            'module'     => $module,
            'branch'     => $branch,
            'sortDir'    => $sortDir,
            'isPolangui' => $isPolangui,
            'userBranch' => $userBranch,
            'branches'   => $branches,
        ];

        return view('history/index', $data);
    }
}
