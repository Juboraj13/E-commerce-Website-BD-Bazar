<?php
/**
 * dashboard.php — Central Admin Dashboard & Analytical Overview
 */
require_once __DIR__ . '/../config/config.php';

$adminTitle = 'Dashboard Overview';

try {
    // 1. Metric Calculations
    $totalCustomers = (int)$pdo->query("SELECT COUNT(*) FROM `users` WHERE `role` = 'customer'")->fetchColumn();
    $totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
    $totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();
    $totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM `orders` WHERE `status` != 'cancelled'")->fetchColumn();
    $pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM `orders` WHERE `status` = 'pending'")->fetchColumn();
    $unreadMessages = (int)$pdo->query("SELECT COUNT(*) FROM `messages` WHERE `is_read` = 0")->fetchColumn();

    // 2. Fetch Latest 5 Orders
    $recentStmt = $pdo->query("
        SELECT o.*, u.name AS customer_name, u.email AS customer_email 
        FROM `orders` o 
        LEFT JOIN `users` u ON o.user_id = u.user_id 
        ORDER BY o.order_date DESC 
        LIMIT 5
    ");
    $recentOrders = $recentStmt->fetchAll();

} catch (PDOException $e) {
    error_log('Admin dashboard metric error: ' . $e->getMessage());
    $totalCustomers = $totalProducts = $totalOrders = $pendingOrders = $unreadMessages = 0;
    $totalRevenue = 0.0;
    $recentOrders = [];
}

require_once __DIR__ . '/admin_header.php';
?>

<!-- 6 Metric Statistics Cards Grid -->
<div class="stat-cards-grid">
    <!-- Total Revenue -->
    <div class="stat-card">
        <div>
            <div class="stat-val" style="color: #2563eb;"><?= format_price($totalRevenue) ?></div>
            <div class="stat-label">Total Revenue (COD)</div>
        </div>
        <div class="stat-icon" style="background-color: #eff6ff; color: #2563eb;">
            <i class="fas fa-hand-holding-dollar"></i>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="stat-card">
        <div>
            <div class="stat-val"><?= $totalOrders ?></div>
            <div class="stat-label">Total Orders Placed</div>
        </div>
        <div class="stat-icon" style="background-color: #ecfdf5; color: #10b981;">
            <i class="fas fa-cart-shopping"></i>
        </div>
    </div>

    <!-- Pending Orders -->
    <div class="stat-card">
        <div>
            <div class="stat-val" style="color: #f59e0b;"><?= $pendingOrders ?></div>
            <div class="stat-label">Orders Pending Action</div>
        </div>
        <div class="stat-icon" style="background-color: #fffbeb; color: #f59e0b;">
            <i class="fas fa-clock"></i>
        </div>
    </div>

    <!-- Total Products -->
    <div class="stat-card">
        <div>
            <div class="stat-val"><?= $totalProducts ?></div>
            <div class="stat-label">Active Catalog Items</div>
        </div>
        <div class="stat-icon" style="background-color: #f5f3ff; color: #8b5cf6;">
            <i class="fas fa-boxes-stacked"></i>
        </div>
    </div>

    <!-- Registered Customers -->
    <div class="stat-card">
        <div>
            <div class="stat-val"><?= $totalCustomers ?></div>
            <div class="stat-label">Registered Customers</div>
        </div>
        <div class="stat-icon" style="background-color: #fdf2f8; color: #ec4899;">
            <i class="fas fa-users"></i>
        </div>
    </div>

    <!-- Unread Inquiries -->
    <div class="stat-card">
        <div>
            <div class="stat-val" style="color: #ef4444;"><?= $unreadMessages ?></div>
            <div class="stat-label">Unread Customer Messages</div>
        </div>
        <div class="stat-icon" style="background-color: #fef2f2; color: #ef4444;">
            <i class="fas fa-envelope"></i>
        </div>
    </div>
</div>

<!-- Quick Action Shortcuts -->
<div style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
    <a href="<?= asset_url('backend/admin/product_form.php') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Product
    </a>
    <a href="<?= asset_url('backend/admin/orders.php') ?>" class="btn btn-secondary">
        <i class="fas fa-truck-fast"></i> Manage Orders (<?= $pendingOrders ?> pending)
    </a>
    <a href="<?= asset_url('backend/admin/categories.php') ?>" class="btn btn-outline">
        <i class="fas fa-tags"></i> Manage Categories
    </a>
    <a href="<?= asset_url('backend/admin/messages.php') ?>" class="btn btn-outline">
        <i class="fas fa-inbox"></i> View Inquiries (<?= $unreadMessages ?> unread)
    </a>
</div>

<!-- Recent Orders Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3 class="admin-card-title">Recent Customer Orders</h3>
        <a href="<?= asset_url('backend/admin/orders.php') ?>" style="font-size: 0.85rem; font-weight: 600; color: #2563eb;">
            View All Orders &rarr;
        </a>
    </div>

    <div class="admin-table-wrap">
        <?php if (empty($recentOrders)): ?>
            <div style="padding: 3rem; text-align: center; color: #64748b;">
                No orders recorded yet.
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $ro): 
                        $badgeStyle = 'background-color: #fef3c7; color: #92400e;';
                        if ($ro['status'] === 'processing') $badgeStyle = 'background-color: #eff6ff; color: #1e40af;';
                        if ($ro['status'] === 'shipped') $badgeStyle = 'background-color: #f3e8ff; color: #6b21a8;';
                        if ($ro['status'] === 'delivered') $badgeStyle = 'background-color: #ecfdf5; color: #065f46;';
                        if ($ro['status'] === 'cancelled') $badgeStyle = 'background-color: #fef2f2; color: #991b1b;';
                    ?>
                        <tr>
                            <td><strong>#<?= (int)$ro['order_id'] ?></strong></td>
                            <td>
                                <div style="font-weight: 600; color: #0f172a;"><?= e($ro['shipping_name']) ?></div>
                                <div style="font-size: 0.775rem; color: #64748b;"><?= e($ro['shipping_phone']) ?></div>
                            </td>
                            <td style="font-weight: 700; color: #2563eb;"><?= format_price($ro['total_amount']) ?></td>
                            <td><?= e($ro['payment_method']) ?></td>
                            <td>
                                <span class="badge" style="<?= $badgeStyle ?> font-size: 0.75rem;">
                                    <?= ucfirst(e($ro['status'])) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y, h:i A', strtotime($ro['order_date'])) ?></td>
                            <td style="text-align: right;">
                                <a href="<?= asset_url('backend/admin/orders.php?order_id=' . (int)$ro['order_id']) ?>" class="btn btn-outline btn-sm">
                                    Manage
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>

