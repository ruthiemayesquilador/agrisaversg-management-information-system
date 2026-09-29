<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CategoriesModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class CategoriesController extends BaseController
{
    protected CategoriesModel $model;

    public function __construct()
    {
        $this->model = new CategoriesModel();
    }

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access to categories.');
        }

        // All users see all categories (shared across all branches)
        $data['categories'] = $this->model->orderBy('category_name', 'ASC')->findAll();
        return view('categories/index', $data);
    }

    public function store()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return $this->redirectAfterCategoryAction()->with('error', 'Unauthorized to add categories.');
        }

        $name = trim($this->request->getPost('category_name'));
        if (empty($name)) {
            return $this->redirectAfterCategoryAction()->with('error', 'Category name is required.');
        }

        $categoryId = $this->model->insert([
            'category_name' => $name
        ], true);

        $this->logHistorySafe(
            'Category Added',
            'Added category "' . $name . '"',
            (int) $categoryId,
            $name,
            null,
            'Categories'
        );

        return $this->redirectAfterCategoryAction()->with('success', 'Category added successfully.');
    }

    public function update()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return $this->redirectAfterCategoryAction()->with('error', 'Unauthorized to update categories.');
        }

        $category_id   = (int) $this->request->getPost('category_id');
        
        // Verify category exists
        $category = $this->model->find($category_id);
        if (!$category) {
            return $this->redirectAfterCategoryAction()->with('error', 'Category not found.');
        }
        
        $name = trim($this->request->getPost('category_name'));

        if (empty($name)) {
            return $this->redirectAfterCategoryAction()->with('error', 'Category name is required.');
        }

        $this->model->update($category_id, ['category_name' => $name]);

        $this->logHistorySafe(
            'Category Updated',
            'Updated category to "' . $name . '"',
            (int) $category_id,
            $name,
            null,
            'Categories'
        );

        return $this->redirectAfterCategoryAction()->with('success', 'Category updated successfully.');
    }

    public function delete()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'])) {
            return $this->redirectAfterCategoryAction()->with('error', 'Unauthorized to delete categories.');
        }

        $category_id = (int) $this->request->getPost('category_id');
        
        // Verify category exists
        $category = $this->model->find($category_id);
        if (!$category) {
            return $this->redirectAfterCategoryAction()->with('error', 'Category not found.');
        }
        
        $itemName = (string) ($category['category_name'] ?? 'Unknown');

        $db = \Config\Database::connect();
        $productsUsingCategory = (int) $db->table('products')->where('category_id', $category_id)->countAllResults();
        if ($productsUsingCategory > 0) {
            return $this->redirectAfterCategoryAction()->with('error', 'Cannot delete category "' . $itemName . '" because it is used by existing products.');
        }

        try {
            $this->model->delete($category_id);
        } catch (DatabaseException $e) {
            return $this->redirectAfterCategoryAction()->with('error', 'Cannot delete category right now. It may still be referenced by products.');
        }

        $this->logHistorySafe(
            'Category Deleted',
            'Deleted category "' . $itemName . '"',
            (int) $category_id,
            $itemName,
            null,
            'Categories'
        );

        return $this->redirectAfterCategoryAction()->with('success', 'Category deleted successfully.');
    }

    private function redirectAfterCategoryAction()
    {
        $returnTo = trim((string) $this->request->getPost('return_to'));
        if ($returnTo === 'products') {
            return redirect()->to('products');
        }

        return redirect()->to('categories');
    }
}
