<?php
$session = session();
$role = (string) ($session->get('role') ?? '');
$branch = (string) ($session->get('branch') ?? '');
$lowStockThreshold = 10;

$lowStockItems = [];
$unreadCount = 0;

if (!function_exists('productNotificationImageUrl')) {
    function productNotificationImageUrl(int $id, string $defaultImage): string
    {
        foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $ext) {
            $path = FCPATH . 'images/products/p' . $id . '.' . $ext;
            if (file_exists($path)) {
                return base_url('images/products/p' . $id . '.' . $ext);
            }
        }

        return $defaultImage;
    }
}

try {
    $db = \Config\Database::connect();
    $query = $db->table('products p')
        ->select('p.product_id, p.part_name, p.current_stock, p.sku, p.part_no, p.branch, c.category_name')
        ->join('categories c', 'c.category_id = p.category_id', 'left')
        ->where('p.current_stock <=', $lowStockThreshold)
        ->orderBy('p.current_stock', 'ASC')
        ->orderBy('p.part_name', 'ASC')
        ->limit(30);

    if ($role === 'admin' && $branch !== '' && $db->fieldExists('branch', 'products')) {
        $query->where('p.branch', $branch);
    }

    $lowStockItems = $query->get()->getResultArray();
    $unreadCount = count($lowStockItems);
} catch (\Throwable $e) {
    $lowStockItems = [];
    $unreadCount = 0;
}

$defaultNotificationImage = base_url('images/products/default.svg');
?>

<style>
    .notification-dropdown-wrapper {
        position: relative;
        display: inline-block;
    }

    .notification-badge-wrapper {
        position: relative;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .notification-bell {
        font-size: 24px;
        color: #FF8C42;
        transition: all 0.3s ease;
    }

    .notification-bell:hover {
        transform: scale(1.1);
    }

    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: #ff6b6b;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: bold;
        border: 2px solid white;
    }

    .notification-badge.hidden {
        display: none;
    }

    .notification-dropdown-wrapper .dropdown-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        background-color: white;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        width: 540px;
        min-width: 540px;
        max-width: 540px;
        max-height: calc(100vh - 110px) !important;
        overflow: hidden !important;
        display: none;
        z-index: 1050;
    }

    .dropdown-menu.show {
        display: block;
    }

    #notificationListContainer.notification-list {
        max-height: calc(100vh - 170px) !important;
        overflow-y: auto;
    }

    @media (min-width: 1200px) {
        .notification-dropdown-wrapper .dropdown-menu {
            width: 540px;
            min-width: 540px;
            max-width: 540px;
        }

        #notificationListContainer.notification-list {
            max-height: calc(100vh - 170px) !important;
        }
    }

    .notification-list::-webkit-scrollbar {
        width: 6px;
    }

    .notification-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .notification-list::-webkit-scrollbar-thumb {
        background: #bbbfc4;
        border-radius: 10px;
    }

    .notification-list::-webkit-scrollbar-thumb:hover {
        background: #999;
    }

    .notification-list ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .notification-list li {
        border-bottom: 1px solid #f0f0f0;
        margin: 0;
        padding: 0;
    }

    .notification-list li:last-child {
        border-bottom: none;
    }

    .notification-list a {
        display: flex;
        align-items: center;
        padding: 15px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        gap: 12px;
    }

    .notification-list a:hover {
        background-color: #f8f9fa;
    }

    .notification-list a img {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: cover;
    }

    .notification-list h3 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #333;
    }

    .notification-list p {
        margin: 4px 0 0 0;
        font-size: 12px;
        color: #999;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .small {
        font-size: 12px !important;
    }

    @media (max-width: 576px) {
        .notification-dropdown-wrapper .dropdown-menu {
            width: min(88vw, 340px);
            min-width: min(88vw, 340px);
            max-width: min(88vw, 340px);
            right: 0;
            left: auto;
            border-radius: 8px;
        }

        #notificationListContainer.notification-list {
            max-height: min(360px, calc(100vh - 150px)) !important;
        }

        .notification-list a {
            padding: 10px;
            gap: 8px;
        }

        .notification-list a img {
            width: 38px;
            height: 38px;
            border-radius: 6px;
        }

        .notification-list h3 {
            font-size: 12px;
            line-height: 1.25;
        }

        .notification-list p,
        .notification-list .small {
            font-size: 11px !important;
            line-height: 1.2;
        }
    }
</style>

<div class="notification-dropdown-wrapper">
    <div class="notification-badge-wrapper" id="notificationToggle">
        <i class="bi bi-bell notification-bell"></i>
        <span class="notification-badge <?= $unreadCount === 0 ? 'hidden' : '' ?>" id="notificationBadge">
            <?= min($unreadCount, 99) ?>
        </span>
    </div>

    <div class="dropdown-menu" id="notificationDropdown">
        <div class="notification-list mx-h-350 customscroll" id="notificationListContainer">
            <ul id="notificationList">
                <?php if (!empty($lowStockItems)): ?>
                    <?php foreach ($lowStockItems as $item): ?>
                        <?php
                            $productId = (int) ($item['product_id'] ?? 0);
                            $productName = (string) ($item['part_name'] ?? 'Unnamed Product');
                            $stockQty = (int) ($item['current_stock'] ?? 0);
                            $sku = trim((string) ($item['sku'] ?? ''));
                            $partNo = trim((string) ($item['part_no'] ?? ''));
                            $categoryName = trim((string) ($item['category_name'] ?? ''));
                            $itemBranch = trim((string) ($item['branch'] ?? ''));
                            $imageUrl = productNotificationImageUrl($productId, $defaultNotificationImage);
                            $itemSearch = $sku !== '' ? $sku : ($partNo !== '' ? $partNo : $productName);
                            $itemLink = base_url('stocks?' . http_build_query([
                                'low' => 1,
                                'search' => $itemSearch,
                            ]));
                        ?>
                        <li>
                            <a href="<?= esc($itemLink, 'attr') ?>" title="View this low-stock item">
                                <img src="<?= esc($imageUrl, 'attr') ?>" alt="<?= esc($productName) ?>" onerror="this.onerror=null;this.src='<?= esc($defaultNotificationImage, 'attr') ?>';">
                                <div style="flex: 1;">
                                    <h3>Low Stock: <?= esc($productName) ?></h3>
                                    <p class="small">Stock left: <?= $stockQty ?> pcs<?php if ($categoryName !== ''): ?> | Category: <?= esc($categoryName) ?><?php endif; ?></p>
                                    <?php if ($sku !== '' || $partNo !== '' || ($role === 'super_admin' && $itemBranch !== '')): ?>
                                        <p class="text-muted small mb-0">
                                            <?php if ($sku !== ''): ?>SKU: <?= esc($sku) ?><?php endif; ?>
                                            <?php if ($partNo !== '' && strcasecmp($partNo, $sku) !== 0): ?><?php if ($sku !== ''): ?> | <?php endif; ?>Part No: <?= esc($partNo) ?><?php endif; ?>
                                            <?php if ($role === 'super_admin' && $itemBranch !== ''): ?> | Branch: <?= esc($itemBranch) ?><?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>
                        <a href="<?= base_url('stocks?low=1') ?>">
                            <img src="<?= esc($defaultNotificationImage, 'attr') ?>" alt="No low stock alerts">
                            <div style="flex: 1;">
                                <h3 class="text-muted">No low stock alerts</h3>
                                <p class="text-muted small">All products are within safe stock levels</p>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationToggle = document.getElementById('notificationToggle');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    if (notificationToggle && notificationDropdown) {
        // Toggle dropdown on click
        notificationToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationDropdown.contains(e.target) && !notificationToggle.contains(e.target)) {
                notificationDropdown.classList.remove('show');
            }
        });

        // Close dropdown when clicking inside it
        notificationDropdown.addEventListener('click', function(e) {
            if (e.target.tagName === 'A') {
                notificationDropdown.classList.remove('show');
            }
        });
    }
});
</script>
