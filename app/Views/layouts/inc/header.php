<?php
$request = service('request');
$session = session();
$userId = $session->get('user_id');
$username = $session->get('username') ?? 'Guest';
$profileSrc = base_url('uploads/profile_images/default-avatar.svg');
$isGuest = !$userId;
?>
<div class="header">
    <div class="header-left">
        <div class="menu-icon bi bi-list"></div>
    </div>
    <div class="header-center d-flex justify-content-center align-items-center flex-grow-1">
        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none" aria-label="Agri Savers G Home">
            <img src="<?= base_url('images/agri-savers-logo.png') ?>" alt="Agri Savers G Logo" class="mb-2" loading="lazy" style="width: 200px; height: auto; max-width: 100%;">
        </a>
    </div>
    <div class="header-right">
        <div class="notification-icon me-3">
            <?php echo view('notifications/badge'); ?>
        </div>
        <div class="user-info-dropdown">
            <div class="dropdown">
                <a class="dropdown-toggle user-profile-toggle" href="#" role="button">
                    <i class="bi bi-person-circle" style="font-size: 32px; color: #FF8C42;"></i>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right user-dropdown-menu">
                    <?php if (!$isGuest): ?>
                        <a class="dropdown-item" href="<?= site_url('profile') ?>">
                            <i class="bi bi-person"></i> Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="<?= site_url('auth/logout') ?>">
                            <i class="bi bi-box-arrow-right"></i> Log Out
                        </a>
                    <?php else: ?>
                        <a class="dropdown-item" href="<?= site_url('auth/login') ?>">
                            <i class="bi bi-box-arrow-in-right"></i> Log In
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .header {
        background: #ffffff;
        padding: 15px 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
        align-items: center;
        gap: 20px;
        position: sticky;
        top: 0;
        z-index: 100;
        width: 100%;
        box-sizing: border-box;
    }
    .header-center {
        display: flex;
        justify-content: center;
        align-items: center;
        justify-self: center;
    }
    .header-left, .header-right {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .header-left {
        justify-self: start;
    }

    .header-right {
        justify-self: end;
    }

    .menu-icon {
        font-size: 24px;
        cursor: pointer;
        color: #333;
        display: none;
        transition: color 0.3s ease;
    }

    .menu-icon:hover {
        color: #ff751f;
    }

    @media (max-width: 768px) {
        .menu-icon {
            display: block;
        }
    }



    .header-right {
        display: flex;
        align-items: center;
        gap: 25px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #FF8C42;
    }

    .user-profile-toggle {
        display: flex;
        align-items: center;
        gap: 10px;
        color: white !important;
        text-decoration: none;
        padding: 5px 10px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .user-profile-toggle:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .user-name {
        font-weight: 500;
        color: white;
        font-size: 14px;
    }

    .user-dropdown-menu {
        min-width: 200px !important;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        padding: 8px 0 !important;
        margin-top: 8px;
        display: none;
        position: absolute;
        right: 0;
    }

    .user-dropdown-menu.show {
        display: block;
    }

    .dropdown-item {
        padding: 12px 20px;
        color: #666 !important;
        font-size: 14px;
        transition: all 0.2s ease;
        border: none;
        background: transparent;
        width: 100%;
        text-align: left;
        cursor: pointer;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .dropdown-item i {
        width: 18px;
        text-align: center;
        color: #FF8C42;
        font-size: 16px;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa !important;
        color: #FF8C42 !important;
        padding-left: 25px;
    }

    .dropdown-item.text-danger {
        color: #dc3545 !important;
    }

    .dropdown-item.text-danger i {
        color: #dc3545;
    }

    .dropdown-item.text-danger:hover {
        background-color: #ffe5e5 !important;
        color: #dc3545 !important;
    }

    .dropdown-divider {
        margin: 8px 0;
        border: 0;
        border-top: 1px solid #e9ecef;
    }

    @media (max-width: 768px) {
        .header {
            padding: 12px 15px;
            grid-template-columns: auto 1fr auto;
            gap: 15px;
        }

        .user-name {
            display: none;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile dropdown functionality
    const profileToggle = document.querySelector('.user-profile-toggle');
    const userDropdownMenu = document.querySelector('.user-dropdown-menu');
    
    if (profileToggle && userDropdownMenu) {
        profileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            userDropdownMenu.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!profileToggle.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                userDropdownMenu.classList.remove('show');
            }
        });
    }
});
</script>
