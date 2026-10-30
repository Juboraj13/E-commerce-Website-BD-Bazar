<?php
/**
 * orders.php — Customer Order History & Self-Service Tracking
 * Lists all orders placed by the authenticated customer with live items and status tracking.
 */
require_once __DIR__ . '/backend/config/config.php';

require_login();

$userId = $_SESSION['user']['user_id'];
$pageTitle = 'My Orders';

// Handle Order Cancellation (Only allowed for 'pending' orders)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_order') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired.');
        redirect(asset_url('orders.php'));
    }

    $cancelId = filter_var($_POST['order_id'] ?? 0, FILTER_VALIDATE_INT);
    if ($cancelId) {
        try {
            $pdo->beginTransaction();

            // Verify order exists, belongs to user, and is 'pending'
            $checkStmt = $pdo->prepare("SELECT order_id, status FROM `orders` WHERE `order_id` = ? AND `user_id` = ? FOR UPDATE");
            $checkStmt->execute([$cancelId, $userId]);
            $ord = $checkStmt->fetch();

            if ($ord && $ord['status'] === 'pending') {
                // Restore stock for all items in this order
                $restoreStmt = $pdo->prepare("
                    UPDATE `products` p
                    JOIN `order_items` oi ON p.id = oi.product_id
                    SET p.stock = p.stock + oi.quantity
                    WHERE oi.order_id = ?
                ");
                $restoreStmt->execute([$cancelId]);

                // Update order status to 'cancelled'
                $upStmt = $pdo->prepare("UPDATE `orders` SET `status` = 'cancelled' WHERE `order_id` = ?");
                $upStmt->execute([$cancelId]);

                $pdo->commit();
                set_flash('success', 'Order #' . $cancelId . ' has been cancelled, and item stock has been restored.');
            } else {
                $pdo->rollBack();
                set_flash('error', 'Only pending orders can be cancelled.');
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Order cancel error: ' . $e->getMessage());
            set_flash('error', 'Failed to cancel order.');
        }
    }
    redirect(asset_url('orders.php'));
}

// Fetch all orders for current user
try {
    $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `user_id` = ? ORDER BY `order_date` DESC");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();

    // Fetch items grouped by order_id
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
    error_log('Orders list error: ' . $e->getMessage());
    $orders = [];
    $itemsByOrder = [];
}

require_once __DIR__ . '/backend/includes/header.php';
?>

<!-- Orders Page Header -->
<div style="background-color: var(--secondary); color: #ffffff; padding: 2rem 0;">
    <div class="container">
        <h1 style="font-size: 2rem; font-weight: 700; color: #ffffff;">My Order History</h1>
        <p style="color: #94a3b8; font-size: 0.9rem;">Track order status, delivery progress and past purchases</p>
    </div>
</div>

<div class="container" style="padding-top: 2.5rem; padding-bottom: 4rem;">
    <?php if (empty($orders)): ?>
        <div style="background-color: var(--bg-surface); border: 1px dashed var(--border-dark); border-radius: var(--radius-lg); padding: 4rem 2rem; text-align: center; max-width: 600px; margin: 0 auto;">
            <div style="width: 70px; height: 70px; border-radius: 50%; background: var(--bg-subtle); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem auto; font-size: 1.75rem; color: var(--text-muted);">
                <i class="fas fa-box-open"></i>
            </div>
            <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;">No Orders Found</h2>
            <p style="color: var(--text-muted); margin-bottom: 1.75rem;">You haven't placed any orders yet. Start exploring our collections today!</p>
            <a href="<?= asset_url('products.php') ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-bag-shopping"></i> Browse Products
            </a>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1.5rem; max-width: 900px; margin: 0 auto;">
            <?php foreach ($orders as $order): 
                $items = $itemsByOrder[$order['order_id']] ?? [];
                
                // Status badge styling
                $badgeStyle = 'background-color: #fef3c7; color: #92400e;'; // pending (amber)
                if ($order['status'] === 'processing') $badgeStyle = 'background-color: #eff6ff; color: #1e40af;'; // blue
                if ($order['status'] === 'shipped') $badgeStyle = 'background-color: #f3e8ff; color: #6b21a8;'; // purple
                if ($order['status'] === 'delivered') $badgeStyle = 'background-color: #ecfdf5; color: #065f46;'; // green
                if ($order['status'] === 'cancelled') $badgeStyle = 'background-color: #fef2f2; color: #991b1b;'; // red
            ?>
                <div style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm);">
                    
                    <!-- Order Top Bar -->
                    <div style="padding: 1.25rem 1.5rem; background-color: var(--bg-subtle); border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
                            <div>
                                <span style="font-size: 0.775rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Order ID</span>
                                <div style="font-weight: 700; color: var(--text-primary); font-size: 1.05rem;">
                                    #<?= (int)$order['order_id'] ?>
                                </div>
                            </div>
                            <div>
                                <span style="font-size: 0.775rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Placed On</span>
                                <div style="font-size: 0.885rem; color: var(--text-secondary);">
                                    <?= date('d M Y, h:i A', strtotime($order['order_date'])) ?>
                                </div>
                            </div>
                            <div>
                                <span style="font-size: 0.775rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Total Amount</span>
                                <div style="font-weight: 700; color: var(--primary); font-size: 1.05rem;">
                                    <?= format_price($order['total_amount']) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div>
                            <span class="badge" style="<?= $badgeStyle ?> font-size: 0.825rem; padding: 0.4rem 0.85rem;">
                                <?= ucfirst(e($order['status'])) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Items Inside Order -->
                    <div style="padding: 1.25rem 1.5rem;">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php foreach ($items as $it): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="width: 52px; height: 52px; border-radius: var(--radius-sm); overflow: hidden; background: #f8fafc; border: 1px solid var(--border); flex-shrink: 0;">
                                            <img src="<?= product_image_url($it['image']) ?>" alt="" style="width: 100%; height: 100%; object-fit: contain;">
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: var(--text-primary); font-size: 0.95rem;">
                                                <a href="<?= asset_url('product.php?id=' . (int)$it['product_id']) ?>"><?= e($it['name']) ?></a>
                                            </div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                                Qty: <?= (int)$it['quantity'] ?> &times; <?= format_price($it['unit_price']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="font-weight: 600; color: var(--text-primary); font-size: 0.95rem;">
                                        <?= format_price((float)$it['unit_price'] * (int)$it['quantity']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Destination Address & Cancel Option -->
                        <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                            <div style="font-size: 0.825rem; color: var(--text-muted);">
                                <i class="fas fa-map-pin" style="color: var(--primary);"></i> Shipping to: <strong><?= e($order['shipping_name']) ?></strong>, <?= e($order['shipping_address']) ?> (<?= e($order['shipping_phone']) ?>)
                            </div>

                            <?php if ($order['status'] === 'pending'): ?>
                                <form action="<?= asset_url('orders.php') ?>" method="POST" onsubmit="return confirm('Are you sure you want to cancel Order #<?= (int)$order['order_id'] ?>? Stock will be restored.');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="cancel_order">
                                    <input type="hidden" name="order_id" value="<?= (int)$order['order_id'] ?>">
                                    <button type="submit" class="btn btn-outline btn-sm" style="color: var(--danger); border-color: #fca5a5;">
                                        <i class="fas fa-ban"></i> Cancel Order
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/backend/includes/footer.php'; ?>

