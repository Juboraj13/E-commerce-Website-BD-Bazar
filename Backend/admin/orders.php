<?php
/**
 * orders.php — Admin Order Management & Status Workflow
 */
require_once __DIR__ . '/../config/config.php';

require_admin();

$adminTitle = 'Manage Orders';

// 1. Handle Order Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired.');
        redirect(asset_url('backend/admin/orders.php'));
    }

    $orderId = filter_var($_POST['order_id'] ?? 0, FILTER_VALIDATE_INT);
    $newStatus = $_POST['status'] ?? '';
    $allowedStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

    if ($orderId > 0 && in_array($newStatus, $allowedStatuses, true)) {
        try {
            // Check previous status
            $prevStmt = $pdo->prepare("SELECT status FROM `orders` WHERE `order_id` = ?");
            $prevStmt->execute([$orderId]);
            $prevStatus = $prevStmt->fetchColumn();

            // If changing to cancelled, restore stock
            if ($newStatus === 'cancelled' && $prevStatus !== 'cancelled') {
                $restoreStmt = $pdo->prepare("
                    UPDATE `products` p
                    JOIN `order_items` oi ON p.id = oi.product_id
                    SET p.stock = p.stock + oi.quantity
                    WHERE oi.order_id = ?
                ");
                $restoreStmt->execute([$orderId]);
            }

            $stmt = $pdo->prepare("UPDATE `orders` SET `status` = ? WHERE `order_id` = ?");
            $stmt->execute([$newStatus, $orderId]);

            set_flash('success', 'Order #' . $orderId . ' status changed to ' . ucfirst($newStatus) . '.');
        } catch (PDOException $e) {
            error_log('Admin order update error: ' . $e->getMessage());
            set_flash('error', 'Failed to update order status.');
        }
    }
    redirect(asset_url('backend/admin/orders.php'));
}

// 2. Filter Orders
$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

$where = [];
$params = [];

if (!empty($statusFilter)) {
    $where[] = "o.status = ?";
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $where[] = "(o.shipping_name LIKE ? OR o.shipping_phone LIKE ? OR o.order_id = ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = (int)$search;
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

try {
    $orderStmt = $pdo->prepare("
        SELECT o.*, u.name AS user_name, u.email AS user_email 
        FROM `orders` o 
        LEFT JOIN `users` u ON o.user_id = u.user_id 
        $whereSql 
        ORDER BY o.order_date DESC
    ");
    $orderStmt->execute($params);
    $orders = $orderStmt->fetchAll();

    // Fetch items for all listed orders
    $orderIds = array_column($orders, 'order_id');
    $itemsByOrder = [];

    if (!empty($orderIds)) {
        $inQuery = implode(',', array_fill(0, count($orderIds), '?'));
        $itemStmt = $pdo->prepare("
            SELECT oi.*, p.name, p.image 
            FROM `order_items` oi 
            JOIN `products` p ON oi.product_id = p.id 
            WHERE oi.order_id IN ($inQuery)
        ");
        $itemStmt->execute($orderIds);
        $allItems = $itemStmt->fetchAll();

        foreach ($allItems as $it) {
            $itemsByOrder[$it['order_id']][] = $it;
        }
    }

} catch (PDOException $e) {
    error_log('Orders fetch error: ' . $e->getMessage());
    $orders = [];
    $itemsByOrder = [];
}

require_once __DIR__ . '/admin_header.php';
?>

<!-- Filter & Search Controls -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
    <form action="<?= asset_url('backend/admin/orders.php') ?>" method="GET" style="display: flex; gap: 0.75rem; flex-wrap: wrap; flex: 1; max-width: 600px;">
        <input type="text" name="q" class="form-control" placeholder="Search order ID, recipient name or phone..." value="<?= e($search) ?>" style="flex: 1; min-width: 220px;">
        
        <select name="status" class="form-control" style="width: auto; min-width: 140px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="pending" <?= ($statusFilter === 'pending') ? 'selected' : '' ?>>Pending</option>
            <option value="processing" <?= ($statusFilter === 'processing') ? 'selected' : '' ?>>Processing</option>
            <option value="shipped" <?= ($statusFilter === 'shipped') ? 'selected' : '' ?>>Shipped</option>
            <option value="delivered" <?= ($statusFilter === 'delivered') ? 'selected' : '' ?>>Delivered</option>
            <option value="cancelled" <?= ($statusFilter === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
        </select>

        <button type="submit" class="btn btn-outline btn-sm">Filter</button>
        <?php if (!empty($search) || !empty($statusFilter)): ?>
            <a href="<?= asset_url('backend/admin/orders.php') ?>" class="btn btn-outline btn-sm" style="color: #ef4444;">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Orders Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3 class="admin-card-title">Customer Orders (<?= count($orders) ?>)</h3>
    </div>

    <div class="admin-table-wrap">
        <?php if (empty($orders)): ?>
            <div style="padding: 3.5rem; text-align: center; color: #64748b;">
                <i class="fas fa-box-open" style="font-size: 2.5rem; margin-bottom: 0.75rem; color: #cbd5e1;"></i>
                <p>No customer orders match your criteria.</p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer / Destination</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $ord): 
                        $orderItems = $itemsByOrder[$ord['order_id']] ?? [];
                        
                        $badgeStyle = 'background-color: #fef3c7; color: #92400e;';
                        if ($ord['status'] === 'processing') $badgeStyle = 'background-color: #eff6ff; color: #1e40af;';
                        if ($ord['status'] === 'shipped') $badgeStyle = 'background-color: #f3e8ff; color: #6b21a8;';
                        if ($ord['status'] === 'delivered') $badgeStyle = 'background-color: #ecfdf5; color: #065f46;';
                        if ($ord['status'] === 'cancelled') $badgeStyle = 'background-color: #fef2f2; color: #991b1b;';
                    ?>
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: #0f172a; font-size: 1rem;">
                                    #<?= (int)$ord['order_id'] ?>
                                </div>
                                <div style="font-size: 0.775rem; color: #64748b;">
                                    <?= date('d M Y, h:i A', strtotime($ord['order_date'])) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #0f172a;"><?= e($ord['shipping_name']) ?></div>
                                <div style="font-size: 0.8rem; color: #64748b;"><?= e($ord['shipping_phone']) ?></div>
                                <div style="font-size: 0.75rem; color: #94a3b8; max-width: 250px;"><?= e($ord['shipping_address']) ?></div>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 0.35rem; font-size: 0.85rem;">
                                    <?php foreach ($orderItems as $it): ?>
                                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                                            <span style="font-weight: 600;"><?= (int)$it['quantity'] ?>x</span>
                                            <span style="color: #334155;"><?= e($it['name']) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 800; color: #2563eb; font-size: 1.05rem;">
                                    <?= format_price($ord['total_amount']) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b;">
                                    <?= e($ord['payment_method']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="<?= $badgeStyle ?> font-size: 0.775rem;">
                                    <?= ucfirst(e($ord['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <!-- Quick Status Changer Form -->
                                <form action="<?= asset_url('backend/admin/orders.php') ?>" method="POST" style="display: flex; align-items: center; gap: 0.4rem;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?= (int)$ord['order_id'] ?>">
                                    <select name="status" class="form-control" style="width: auto; padding: 0.35rem 0.65rem; font-size: 0.85rem;" onchange="this.form.submit()">
                                        <option value="pending" <?= ($ord['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                                        <option value="processing" <?= ($ord['status'] === 'processing') ? 'selected' : '' ?>>Processing</option>
                                        <option value="shipped" <?= ($ord['status'] === 'shipped') ? 'selected' : '' ?>>Shipped</option>
                                        <option value="delivered" <?= ($ord['status'] === 'delivered') ? 'selected' : '' ?>>Delivered</option>
                                        <option value="cancelled" <?= ($ord['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>

