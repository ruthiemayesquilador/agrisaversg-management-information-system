<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<style>
    .categories-page-title {
        font-size: 34px;
        font-weight: 800;
        text-align: center;
        letter-spacing: 1px;
        color: #1a1a1a;
        margin-bottom: 28px;
        text-transform: uppercase;
    }

    .categories-card {
        background: #fff;
        border: 1.5px solid #d0d0d0;
        border-radius: 12px;
        padding: 20px 24px 24px;
        position: relative;
    }

    .btn-new-category {
        background-color: #FF8C00;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 9px 20px;
        font-size: 18px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: background 0.2s ease;
        float: right;
        margin-bottom: 14px;
    }

    .btn-new-category:hover {
        background-color: #e07800;
        color: #fff;
    }

    .categories-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
    }

    .categories-table thead th {
        background-color: #f9f9f9;
        color: #555;
        font-weight: 700;
        padding: 13px 18px;
        border-top: 1px solid #e0e0e0;
        border-bottom: 1px solid #e0e0e0;
        text-align: center;
        font-size: 18px;
    }

    .categories-table thead th:nth-child(2) {
        text-align: left;
    }

    .categories-table tbody tr {
        border-bottom: 1px solid #efefef;
    }

    .categories-table tbody tr:last-child {
        border-bottom: none;
    }

    .categories-table tbody td {
        padding: 13px 18px;
        color: #333;
        vertical-align: middle;
        text-align: center;
        font-size: 18px;
    }

    .categories-table tbody td:nth-child(2) {
        text-align: left;
    }

    .cat-num {
        font-weight: 500;
        color: #444;
    }

  
    .btn-action {
        border: none;
        border-radius: 6px;
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        cursor: pointer;
        transition: opacity 0.2s;
        text-decoration: none;
    }

    .btn-action:hover {
        opacity: 0.82;
    }

    /* .btn-view  { background-color: #2196F3; color: #fff; } */
    .btn-edit  { background-color: #FF9800; color: #fff; }
    .btn-delete{ background-color: #F44336; color: #fff; }

    .actions-cell {
        display: flex;
        gap: 5px;
        justify-content: center;
    }
</style>

<div class="container-fluid px-4 py-4">

    <h2 class="categories-page-title">Product Categories</h2>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="categories-card">

        <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
            <h6 class="mb-0" style="font-size: 18px; font-weight: 600;">Product Categories</h6>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group manage-search-group">
                            <input type="text" id="catSearchInput" class="form-control manage-search-input" placeholder="Search by category name...">
                            <button class="btn manage-search-btn" type="button" onclick="resetCategoriesFilter()"><i class="bi bi-funnel"></i></button>
                            <button class="btn manage-search-btn" type="button" onclick="filterCategories()"><i class="bi bi-search"></i></button>
                        </div>
                <button class="btn-new-category" data-bs-toggle="modal" data-bs-target="#newCategoryModal">
                    <i class="bi bi-plus-circle"></i> New Category
                </button>
            </div>
        </div>
        <div style="clear:both;"></div>

        <div class="table-scroll-wrap">
            <table class="categories-table js-sortable-table">
                <thead>
                    <tr>
                        <th>Category Number</th>
                        <th>Category Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="catTableBody">
                    <?php if (!empty($categories)) : ?>
                        <?php foreach ($categories as $index => $category) : ?>
                        <tr>
                            <td class="cat-num"><?= str_pad($index + 1, 1, '0', STR_PAD_LEFT) ?></td>
                            <td><?= esc($category['category_name']) ?></td>
                            <td>
                                <div class="actions-cell">
                                    <!-- View button removed -->
                                    <button class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                        data-id="<?= $category['category_id'] ?>"
                                        data-name="<?= esc($category['category_name']) ?>" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn-action btn-delete" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal"
                                        data-id="<?= $category['category_id'] ?>"
                                        data-name="<?= esc($category['category_name']) ?>" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">No categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>
<?= $this->include('categories/new_category') ?>

<?= $this->include('categories/edit_category') ?>

<?= $this->include('categories/delete_category') ?>

<script>
document.getElementById('catSearchInput').addEventListener('keyup', filterCategories);
function filterCategories() {
    const q = document.getElementById('catSearchInput').value.toLowerCase();
    document.querySelectorAll('#catTableBody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

function resetCategoriesFilter() {
    const input = document.getElementById('catSearchInput');
    input.value = '';
    filterCategories();
    input.focus();
}
</script>

<?php $this->endSection(); ?>