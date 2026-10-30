<?php
/**
 * admin_header.php — Shared Header and Sidebar for Admin Panel
 */
require_once __DIR__ . '/../config/config.php';

// Strict Role Guard: Only administrator allowed
require_admin();

$adminScript = basename($_SERVER['PHP_SELF']);
$adminUser = current_user();

// Fetch unread messages count for badge
try {
    $unreadCount = (int)$pdo->query("SELECT COUNT(*) FROM `messages` WHERE `is_read` = 0")->fetchColumn();
    $pendingOrdersCount = (int)$pdo->query("SELECT COUNT(*) FROM `orders` WHERE `status` = 'pending'")->fetchColumn();
} catch (Exception $e) {
    $unreadCount = 0;
    $pendingOrdersCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminTitle ?? 'Admin Panel') ?> — <?= e(SITE_NAME) ?> Management</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= asset_url('frontend/css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('frontend/css/admin.css') ?>">
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-brand">
            <i class="fas fa-shield-halved" style="color: #3b82f6;"></i>
            BD <span>Bazar</span> Admin
        </div>

        <nav class="admin-nav">
            <a href="<?= asset_url('backend/admin/dashboard.php') ?>" class="admin-nav-link <?= ($adminScript === 'dashboard.php') ? 'active' : '' ?>">
                <i class="fas fa-gauge-high"></i> Dashboard
            </a>

            <a href="<?= asset_url('backend/admin/products.php') ?>" class="admin-nav-link <?= ($adminScript === 'products.php' || $adminScript === 'product_form.php') ? 'active' : '' ?>">
                <i class="fas fa-boxes-stacked"></i> Products
            </a>

            <a href="<?= asset_url('backend/admin/categories.php') ?>" class="admin-nav-link <?= ($adminScript === 'categories.php') ? 'active' : '' ?>">
                <i class="fas fa-layer-group"></i> Categories
            </a>

            <a href="<?= asset_url('backend/admin/orders.php') ?>" class="admin-nav-link <?= ($adminScript === 'orders.php') ? 'active' : '' ?>">
                <i class="fas fa-cart-shopping"></i> Orders
                <?php if ($pendingOrdersCount > 0): ?>
                    <span class="admin-nav-badge" style="background-color: #f59e0b;"><?= $pendingOrdersCount ?></span>
                <?php endif; ?>
            </a>

            <a href="<?= asset_url('backend/admin/users.php') ?>" class="admin-nav-link <?= ($adminScript === 'users.php') ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Customers
            </a>

            <a href="<?= asset_url('backend/admin/messages.php') ?>" class="admin-nav-link <?= ($adminScript === 'messages.php') ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i> Messages
                <?php if ($unreadCount > 0): ?>
                    <span class="admin-nav-badge"><?= $unreadCount ?></span>
                <?php endif; ?>
            </a>

            <div style="height: 1px; background: rgba(255,255,255,0.08); margin: 0.75rem 0;"></div>

            <a href="<?= asset_url('index.php') ?>" class="admin-nav-link" target="_blank">
                <i class="fas fa-arrow-up-right-from-square"></i> View Storefront
            </a>
        </nav>

        <div class="admin-footer">
            <a href="<?= asset_url('logout.php') ?>" class="admin-nav-link" style="color: #f87171;">
                <i class="fas fa-right-from-bracket"></i> Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-main">
        <!-- Admin Top Navigation Bar -->
        <header class="admin-topbar">
            <div class="admin-title-wrap" style="display: flex; align-items: center; gap: 1rem;">
                <button type="button" class="mobile-toggle" id="adminSidebarToggle" style="display: none;">
                    <i class="fas fa-bars"></i>
                </button>
                <h2><?= e($adminTitle ?? 'Dashboard') ?></h2>
            </div>

            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div class="admin-profile-pill">
                    <div class="admin-avatar">
                        <?= strtoupper(substr($adminUser['name'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 600; color: #0f172a; line-height: 1.2;"><?= e($adminUser['name']) ?></div>
                        <div style="font-size: 0.72rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Administrator</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Alert Messages inside Admin -->
        <div style="padding: 1.5rem 2rem 0 2rem;">
            <?php if ($success = get_flash('success')): ?>
                <div class="alert alert-success">
                    <div class="alert-content">
                        <i class="fas fa-circle-check"></i>
                        <span><?= e($success) ?></span>
                    </div>
                    <button type="button" class="alert-close">&times;</button>
                </div>
            <?php endif; ?>

            <?php if ($error = get_flash('error')): ?>
                <div class="alert alert-error">
                    <div class="alert-content">
                        <i class="fas fa-circle-exclamation"></i>
                        <span><?= e($error) ?></span>
                    </div>
                    <button type="button" class="alert-close">&times;</button>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-content">

