<?php
/**
 * order_success.php — Order Confirmation Screen
 * Displays order summary, status, delivery timeline and recipient info.
 */
require_once __DIR__ . '/backend/config/config.php';

require_login();

$orderId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$orderId || $orderId <= 0) {
    redirect(asset_url('orders.php'));
}

$userId = $_SESSION['user']['user_id'];

try {
    // Only fetch if order belongs to the logged-in user (prevents IDOR access)
    $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `order_id` = ? AND `user_id` = ?");
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch();

    if (!$order) {
        set_flash('error', 'Order not found or you do not have permission to view it.');
        redirect(asset_url('orders.php'));
    }

    // Fetch items for this order
    $itemsStmt = $pdo->prepare("
        SELECT oi.*, p.name, p.image, p.price AS current_price 
        FROM `order_items` oi 
        JOIN `products` p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll();

} catch (PDOException $e) {
    error_log('Order success error: ' . $e->getMessage());
    redirect(asset_url('orders.php'));
}

$pageTitle = 'Order Confirmed #' . $orderId;
require_once __DIR__ . '/backend/includes/header.php';
?>

<div class="container" style="padding-top: 3.5rem; padding-bottom: 5rem; max-width: 760px;">
    
    <!-- Success Banner Card -->
    <div style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 3rem 2.5rem; box-shadow: var(--shadow-md); text-align: center;">
        
        <div style="width: 80px; height: 80px; border-radius: 50%; background-color: #ecfdf5; color: var(--success); display: flex; align-items: center; justify-content: center; font-size: 2.75rem; margin: 0 auto 1.5rem auto;">
            <i class="fas fa-check"></i>
        </div>

        <h1 style="font-size: 2rem; font-weight: 700; color: var(--secondary); margin-bottom: 0.5rem;">
            Thank You! Order Placed Successfully
        </h1>

        <p style="color: var(--text-muted); font-size: 1.05rem; margin-bottom: 1.75rem;">
            Your order <strong>#<?= (int)$order['order_id'] ?></strong> has been received and is being processed by our fulfillment team.
        </p>

        <!-- Order Info Meta Box -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; background-color: var(--bg-subtle); border-radius: var(--radius-md); padding: 1.25rem; text-align: left; margin-bottom: 2rem;">
            <div>
                <span style="font-size: 0.775rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Order Date</span>
                <div style="font-size: 0.95rem; font-weight: 600; color: var(--text-primary); margin-top: 0.2rem;">
                    <?= date('d M Y, h:i A', strtotime($order['order_date'])) ?>
                </div>
            </div>
            <div>
                <span style="font-size: 0.775rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Payment Method</span>
                <div style="font-size: 0.95rem; font-weight: 600; color: var(--text-primary); margin-top: 0.2rem;">
                    <?= e($order['payment_method']) ?>
                </div>
            </div>
            <div>
                <span style="font-size: 0.775rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Order Status</span>
                <div style="margin-top: 0.2rem;">
                    <span class="badge" style="background-color: #fef3c7; color: #92400e;">
                        <?= ucfirst(e($order['status'])) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Shipping & Recipient Details -->
        <div style="text-align: left; background-color: #ffffff; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 2rem;">
            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.6rem; color: var(--text-primary);">
                <i class="fas fa-location-dot" style="color: var(--primary);"></i> Delivery Destination
            </h3>
            <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.5; margin: 0;">
                <strong>Recipient:</strong> <?= e($order['shipping_name']) ?> (<?= e($order['shipping_phone']) ?>)<br>
                <strong>Address:</strong> <?= e($order['shipping_address']) ?>
            </p>
        </div>

        <!-- Items Summary List -->
        <div style="text-align: left; margin-bottom: 2rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; color: var(--secondary);">Items Ordered</h3>
            <div style="border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden;">
                <?php foreach ($items as $item): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--border); background-color: var(--bg-surface);">
                        <div style="display: flex; align-items: center; gap: 0.85rem;">
                            <img src="<?= product_image_url($item['image']) ?>" alt="" style="width: 46px; height: 46px; object-fit: contain; border-radius: var(--radius-sm); background: #f8fafc; border: 1px solid var(--border);">
                            <div>
                                <div style="font-weight: 600; font-size: 0.925rem; color: var(--text-primary);"><?= e($item['name']) ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">Qty: <?= (int)$item['quantity'] ?> &times; <?= format_price($item['unit_price']) ?></div>
                            </div>
                        </div>
                        <div style="font-weight: 700; color: var(--primary); font-size: 0.95rem;">
                            <?= format_price((float)$item['unit_price'] * (int)$item['quantity']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div style="padding: 1rem 1.25rem; background-color: var(--bg-subtle); display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: 700; color: var(--secondary);">
                    <span>Total Amount Paid / Payable</span>
                    <span style="color: var(--primary);"><?= format_price($order['total_amount']) ?></span>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="<?= asset_url('orders.php') ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-box-open"></i> View My Order History
            </a>
            <a href="<?= asset_url('products.php') ?>" class="btn btn-outline btn-lg">
                <i class="fas fa-bag-shopping"></i> Continue Shopping
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/backend/includes/footer.php'; ?>

