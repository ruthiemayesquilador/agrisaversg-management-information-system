<?php
$session = session();
$role = $session->get('role');

$currentPath = uri_string();
?>

<div class="left-side-bar text-white vh-100 shadow-sm" style="background-color:#ff751f !important;">
    <div class="brand-logo text-center py-3 d-flex align-items-center justify-content-between px-3">
        
        <button class="btn sidebar-close-btn" style="font-size: 28px; padding: 5px; border: none; cursor: pointer; color: white; background: transparent; ">
            ☰
        </button>
    </div>


    <nav class="menu-block overflow-auto py-2 px-2" role="navigation" aria-label="Main navigation">
        <ul class="nav flex-column">

        <?php if ($role === 'super_admin'): ?>
            <!-- Super Admin Navigation -->
            <li class="nav-item mb-2">
                <a href="<?= base_url('dashboard') ?>"
                    class="nav-link text-white <?= $currentPath === 'dashboard' ? 'rounded fw-bold' : '' ?>"
                    style="<?= $currentPath === 'dashboard' ? 'background-color:rgba(0, 0, 0, 0.12) !important;' : '' ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= base_url('products') ?>"
                    class="nav-link text-white <?= $currentPath === 'products' ? 'rounded fw-bold' : '' ?>"
                    style="<?= $currentPath === 'products' ? 'background-color:rgba(0, 0, 0, 0.12) !important;' : '' ?>">
                    <i class="bi bi-box-seam"></i> Products
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= base_url('sales') ?>"
                    class="nav-link text-white <?= $currentPath === 'sales' ? 'rounded fw-bold' : '' ?>"
                    style="<?= $currentPath === 'sales' ? 'background-color:rgba(0, 0, 0, 0.12) !important;' : '' ?>">
                    <i class="bi bi-cart-check"></i> Accounting
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= base_url('stocks') ?>"
                    class="nav-link text-white <?= $currentPath === 'stocks' ? 'rounded fw-bold' : '' ?>"
                    style="<?= $currentPath === 'stocks' ? 'background-color:rgba(0, 0, 0, 0.12) !important;' : '' ?>">
                    <i class="bi bi-boxes"></i> Stocks
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= base_url('suppliers') ?>"
                    class="nav-link text-white <?= $currentPath === 'suppliers' ? 'rounded fw-bold' : '' ?>"
                    style="<?= $currentPath === 'suppliers' ? 'background-color:rgba(0, 0, 0, 0.12) !important;' : '' ?>">
                    <i class="bi bi-truck"></i> Suppliers
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= base_url('reports') ?>"
                    class="nav-link text-white <?= $currentPath === 'reports' ? 'rounded fw-bold' : '' ?>"
                    style="<?= $currentPath === 'reports' ? 'background-color:rgba(0, 0, 0, 0.12) !important;' : '' ?>">
                    <i class="bi bi-bar-chart-line"></i> Reports
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= base_url('history') ?>"
                    class="nav-link text-white <?= $currentPath === 'history' ? 'rounded fw-bold' : '' ?>"
                    style="<?= $currentPath === 'history' ? 'background-color:rgba(0, 0, 0, 0.12) !important;' : '' ?>">
                    <i class="bi bi-clock-history"></i> History
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= base_url('pos') ?>"
                    class="nav-link text-white <?= $currentPath === 'pos' ? 'rounded fw-bold' : '' ?>"
                    style="<?= $currentPath === 'pos' ? 'background-color:rgba(0, 0, 0, 0.12) !important;' : '' ?>">
                    <i class="bi bi-cash-register"></i> POS
                </a>
            </li>

        <?php else: ?>
            <!-- Admin / Default Navigation -->
            <li class="nav-item mb-2">
                <a href="<?= base_url('dashboard') ?>"
                    class="nav-link text-white <?= $currentPath === 'dashboard' ? 'rounded fw-bold' : '' ?>"
                    style="<?= $currentPath === 'dashboard' ? 'background-color:rgba(0, 0, 0, 0.12) !important;' : '' ?>"
                    aria-current="<?= $currentPath === 'dashboard' ? 'page' : '' ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= base_url('products') ?>"
                    class="nav-link text-white <?= $currentPath === 'products' ? 'rounded fw-bold' : '' ?>"
                    style="<?= $currentPath === 'products' ? 'background-color:rgba(0, 0, 0, 0.12) !important;' : '' ?>"
                    aria-current="<?= $currentPath === 'products' ? 'page' : '' ?>">
                    <i class="bi bi-box-seam"></i> Products
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= base_url('sales') ?>"
                    class="nav-link text-white <?= $currentPath === 'sales' ? 'rounded fw-bold' : '' ?>"
                    style="<?= $currentPath === 'sales' ? 'background-color:rgba(0, 0, 0, 0.12) !important;' : '' ?>"
                    aria-current="<?= $currentPath === 'sales' ? 'page' : '' ?>">
                    <i class="bi bi-cart-check"></i> Accounting
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= base_url('stocks') ?>"
                    class="nav-link text-white <?= $currentPath === 'stocks' ? 'rounded fw-bold' : '' ?>"
                    style="<?= $currentPath === 'stocks' ? 'background-color:rgba(0, 0, 0, 0.12) !important;' : '' ?>"
                    aria-current="<?= $currentPath === 'stocks' ? 'page' : '' ?>">
                    <i class="bi bi-boxes"></i> Stocks
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= base_url('suppliers/admin-transfers') ?>"
                    class="nav-link text-white <?= ($currentPath === 'suppliers' || $currentPath === 'suppliers/admin-transfers') ? 'rounded fw-bold' : '' ?>"
                    style="<?= ($currentPath === 'suppliers' || $currentPath === 'suppliers/admin-transfers') ? 'background-color:rgba(0, 0, 0, 0.12) !important;' : '' ?>"
                    aria-current="<?= ($currentPath === 'suppliers' || $currentPath === 'suppliers/admin-transfers') ? 'page' : '' ?>">
                    <i class="bi bi-truck"></i> Suppliers
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= base_url('reports') ?>"
                    class="nav-link text-white <?= $currentPath === 'reports' ? 'rounded fw-bold' : '' ?>"
                    style="<?= $currentPath === 'reports' ? 'background-color:rgba(0, 0, 0, 0.12) !important;' : '' ?>"
                    aria-current="<?= $currentPath === 'reports' ? 'page' : '' ?>">
                    <i class="bi bi-bar-chart-line"></i> Reports
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= base_url('history') ?>"
                    class="nav-link text-white <?= $currentPath === 'history' ? 'rounded fw-bold' : '' ?>"
                    style="<?= $currentPath === 'history' ? 'background-color:rgba(0, 0, 0, 0.12) !important;' : '' ?>"
                    aria-current="<?= $currentPath === 'history' ? 'page' : '' ?>">
                    <i class="bi bi-clock-history"></i> History
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= base_url('pos') ?>"
                    class="nav-link text-white <?= $currentPath === 'pos' ? 'rounded fw-bold' : '' ?>"
                    style="<?= $currentPath === 'pos' ? 'background-color:rgba(0, 0, 0, 0.12) !important;' : '' ?>"
                    aria-current="<?= $currentPath === 'pos' ? 'page' : '' ?>">
                    <i class="bi bi-cash-register"></i> POS
                </a>
            </li>
        <?php endif; ?>

        </ul>
    </nav>
</div>

<style>
    .sidebar-close-btn {
        display: block !important;
        transition: transform 0.3s ease;
    }

    .sidebar-close-btn:hover {
        transform: scale(1.1);
        opacity: 0.8;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const closeBtn = document.querySelector('.sidebar-close-btn');
        const sidebar = document.querySelector('.left-side-bar');
        const contentWrapper = document.getElementById('contentWrapper');
        const headerMenuIcon = document.querySelector('.menu-icon');

        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar?.classList.add('hidden');
                contentWrapper?.classList.add('full-width');
                // Show header menu icon when sidebar is hidden
                if (headerMenuIcon) {
                    headerMenuIcon.style.display = 'block';
                }
            });
        }
    });
</script>
