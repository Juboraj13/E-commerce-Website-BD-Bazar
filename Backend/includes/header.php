<?php
/**
 * header.php — Shared Global Header Component
 */
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../config/config.php';
}

$pageTitle = isset($pageTitle) ? $pageTitle . ' — ' . SITE_NAME : SITE_NAME . ' — ' . SITE_TAGLINE;
$cartCount = get_cart_count($pdo);
$currentUser = current_user();
$currentScript = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(SITE_NAME) ?> — Premium Online Shopping Platform in Bangladesh with genuine products and fast home delivery.">
    <title><?= e($pageTitle) ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= asset_url('frontend/css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('frontend/css/responsive.css') ?>">
    <?php if (isset($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
<body>

    <!-- Top Promotion Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-contact">
                <a href="tel:<?= e(SITE_PHONE) ?>"><i class="fas fa-phone-alt"></i> <?= e(SITE_PHONE) ?></a>
                <a href="mailto:<?= e(SITE_EMAIL) ?>"><i class="fas fa-envelope"></i> <?= e(SITE_EMAIL) ?></a>
            </div>
            <div class="top-bar-promo">
                <i class="fas fa-truck-fast"></i>
                <span>Free delivery on orders over <?= format_price(FREE_SHIPPING_OVER) ?>! Cash on Delivery Available.</span>
            </div>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="site-header">
        <div class="container">
            <div class="nav-container">
                <!-- Mobile Menu Button -->
                <button type="button" class="mobile-toggle" id="mobileNavToggle" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Brand Logo -->
                <a href="<?= asset_url('index.php') ?>" class="brand-logo">
                    <i class="fas fa-bag-shopping"></i>
                    BD <span>Bazar</span>
                </a>

                <!-- Search Bar -->
                <form action="<?= asset_url('products.php') ?>" method="GET" class="search-form">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="q" class="search-input" placeholder="Search smartphones, laptops, fashion, bikes..." value="<?= e($_GET['q'] ?? '') ?>">
                    <button type="submit" class="search-submit-btn">Search</button>
                </form>

                <!-- Desktop Nav Links -->
                <nav class="nav-menu" id="siteNavMenu">
                    <a href="<?= asset_url('index.php') ?>" class="nav-link <?= ($currentScript === 'index.php') ? 'active' : '' ?>">Home</a>
                    <a href="<?= asset_url('products.php') ?>" class="nav-link <?= ($currentScript === 'products.php' || $currentScript === 'product.php') ? 'active' : '' ?>">Products</a>
                    <a href="<?= asset_url('about.php') ?>" class="nav-link <?= ($currentScript === 'about.php') ? 'active' : '' ?>">About Us</a>
                    <a href="<?= asset_url('contact.php') ?>" class="nav-link <?= ($currentScript === 'contact.php') ? 'active' : '' ?>">Contact</a>
                </nav>

                <!-- User & Cart Header Actions -->
                <div class="header-actions">
                    <!-- Cart Button with Live Badge -->
                    <a href="<?= asset_url('cart.php') ?>" class="cart-icon-btn" title="View Cart">
                        <i class="fas fa-cart-shopping"></i>
                        <span class="cart-badge" id="cartBadgeCount"><?= $cartCount ?></span>
                    </a>

                    <!-- User Account Dropdown -->
                    <div class="user-dropdown">
                        <?php if (is_logged_in()): ?>
                            <button type="button" class="user-toggle-btn" id="userDropdownToggle">
                                <i class="fas fa-user-circle"></i>
                                <span><?= e(explode(' ', $currentUser['name'])[0]) ?></span>
                                <i class="fas fa-chevron-down" style="font-size: 0.75rem;"></i>
                            </button>
                            <div class="user-dropdown-menu">
                                <div class="dropdown-header">
                                    <div class="user-name"><?= e($currentUser['name']) ?></div>
                                    <div class="user-email"><?= e($currentUser['email']) ?></div>
                                </div>
                                <?php if (is_admin()): ?>
                                    <a href="<?= asset_url('backend/admin/dashboard.php') ?>" class="dropdown-item">
                                        <i class="fas fa-gauge-high"></i> Admin Dashboard
                                    </a>
                                <?php endif; ?>
                                <a href="<?= asset_url('profile.php') ?>" class="dropdown-item">
                                    <i class="fas fa-id-card"></i> My Profile
                                </a>
                                <a href="<?= asset_url('orders.php') ?>" class="dropdown-item">
                                    <i class="fas fa-box-open"></i> My Orders
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="<?= asset_url('logout.php') ?>" class="dropdown-item text-danger">
                                    <i class="fas fa-right-from-bracket"></i> Sign Out
                                </a>
                            </div>
                        <?php else: ?>
                            <a href="<?= asset_url('login.php') ?>" class="btn btn-outline btn-sm">
                                <i class="fas fa-arrow-right-to-bracket"></i> Sign In
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Global Flash Notification Alerts -->
    <div class="container flash-container">
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

        <?php if ($warning = get_flash('warning')): ?>
            <div class="alert alert-warning">
                <div class="alert-content">
                    <i class="fas fa-triangle-exclamation"></i>
                    <span><?= e($warning) ?></span>
                </div>
                <button type="button" class="alert-close">&times;</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content Area Starts -->
    <main>

