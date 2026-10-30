<?php
/**
 * checkout.php — Secure Order Checkout with Database Transaction
 * Validates stock, locks rows with FOR UPDATE, creates order, inserts items, updates inventory.
 */
require_once __DIR__ . '/backend/config/config.php';

// Checkout requires logged-in customer
require_login();

$userId = $_SESSION['user']['user_id'];
$pageTitle = 'Checkout';

// Fetch current user cart items
$stmt = $pdo->prepare("
    SELECT c.cart_id, c.quantity, p.id AS product_id, p.name, p.price, p.stock, p.image 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    set_flash('warning', 'Your shopping cart is empty. Please add items before checking out.');
    redirect(asset_url('cart.php'));
}

// Calculate subtotal and shipping
$subtotal = 0.0;
foreach ($cartItems as $item) {
    $subtotal += ((float)$item['price'] * (int)$item['quantity']);
}
$shippingFee = ($subtotal >= FREE_SHIPPING_OVER || $subtotal == 0) ? 0.0 : SHIPPING_FEE;
$grandTotal = $subtotal + $shippingFee;

$errors = [];
$shippingName = $_POST['shipping_name'] ?? $_SESSION['user']['name'] ?? '';
$shippingPhone = $_POST['shipping_phone'] ?? $_SESSION['user']['phone'] ?? '';
$shippingAddress = $_POST['shipping_address'] ?? $_SESSION['user']['address'] ?? '';
$paymentMethod = $_POST['payment_method'] ?? 'Cash on Delivery';

// Process Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired. Please try again.');
        redirect(asset_url('checkout.php'));
    }

    $shippingName = trim($_POST['shipping_name'] ?? '');
    $shippingPhone = trim($_POST['shipping_phone'] ?? '');
    $shippingAddress = trim($_POST['shipping_address'] ?? '');

    // Server-side validation
    if (empty($shippingName) || mb_strlen($shippingName) < 3) {
        $errors['shipping_name'] = 'Full name must be at least 3 characters.';
    }
    if (empty($shippingPhone) || !preg_match('/^[\d\+\-\s]{10,18}$/', $shippingPhone)) {
        $errors['shipping_phone'] = 'Please enter a valid delivery contact number.';
    }
    if (empty($shippingAddress) || mb_strlen($shippingAddress) < 6) {
        $errors['shipping_address'] = 'Please provide a detailed delivery address (House, Road, City).';
    }

    if (empty($errors)) {
        try {
            // ==========================================
            // BEGIN DATABASE TRANSACTION
            // ==========================================
            $pdo->beginTransaction();

            // 1. Re-query and lock products with FOR UPDATE to prevent race conditions
            $verifiedSubtotal = 0.0;
            $itemsToProcess = [];

            $lockStmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ? FOR UPDATE");

            foreach ($cartItems as $cItem) {
                $lockStmt->execute([$cItem['product_id']]);
                $freshProduct = $lockStmt->fetch();

                if (!$freshProduct) {
                    throw new Exception('Product "' . $cItem['name'] . '" is no longer available.');
                }

                if ($freshProduct['stock'] < $cItem['quantity']) {
                    throw new Exception('Insufficient stock for "' . $freshProduct['name'] . '". Only ' . $freshProduct['stock'] . ' units remaining.');
                }

                $verifiedSubtotal += ((float)$freshProduct['price'] * (int)$cItem['quantity']);
                $itemsToProcess[] = [
                    'product_id' => (int)$freshProduct['id'],
                    'quantity'   => (int)$cItem['quantity'],
                    'unit_price' => (float)$freshProduct['price']
                ];
            }

            $verifiedShipping = ($verifiedSubtotal >= FREE_SHIPPING_OVER || $verifiedSubtotal == 0) ? 0.0 : SHIPPING_FEE;
            $verifiedGrandTotal = $verifiedSubtotal + $verifiedShipping;

            // 2. Insert into orders table
            $orderStmt = $pdo->prepare("
                INSERT INTO `orders` 
                (`user_id`, `total_amount`, `shipping_name`, `shipping_phone`, `shipping_address`, `payment_method`, `status`) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            $orderStmt->execute([
                $userId,
                $verifiedGrandTotal,
                $shippingName,
                $shippingPhone,
                $shippingAddress,
                $paymentMethod
            ]);

            $orderId = (int)$pdo->lastInsertId();

            // 3. Insert order items & reduce stock
            $itemStmt = $pdo->prepare("
                INSERT INTO `order_items` (`order_id`, `product_id`, `quantity`, `unit_price`) 
                VALUES (?, ?, ?, ?)
            ");

            $stockStmt = $pdo->prepare("
                UPDATE `products` SET `stock` = `stock` - ? WHERE `id` = ?
            ");

            foreach ($itemsToProcess as $item) {
                // Insert line item
                $itemStmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['unit_price']
                ]);

                // Reduce inventory stock
                $stockStmt->execute([
                    $item['quantity'],
                    $item['product_id']
                ]);
            }

            // 4. Clear user's cart
            $clearStmt = $pdo->prepare("DELETE FROM `cart` WHERE `user_id` = ?");
            $clearStmt->execute([$userId]);

            // 5. Commit Transaction
            $pdo->commit();

            set_flash('success', 'Your order #' . $orderId . ' has been successfully placed!');
            redirect(asset_url('order_success.php?id=' . $orderId));

        } catch (Exception $e) {
            // Roll back all changes on any error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Checkout transaction failed: ' . $e->getMessage());
            $errors['general'] = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/backend/includes/header.php';
?>

<!-- Checkout Header -->
<div style="background-color: var(--secondary); color: #ffffff; padding: 2rem 0;">
    <div class="container">
        <h1 style="font-size: 2rem; font-weight: 700; color: #ffffff;">Order Checkout</h1>
        <p style="color: #94a3b8; font-size: 0.9rem;">Review your items and enter shipping address</p>
    </div>
</div>

<div class="container" style="padding-top: 2.5rem; padding-bottom: 4rem;">
    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error">
            <div class="alert-content">
                <i class="fas fa-circle-exclamation"></i>
                <span><?= e($errors['general']) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <form action="<?= asset_url('checkout.php') ?>" method="POST" id="checkoutForm" novalidate>
        <?= csrf_field() ?>

        <div style="display: grid; grid-template-columns: 1.25fr 1fr; gap: 2.5rem; align-items: start;" class="checkout-layout">
            
            <!-- Left: Shipping & Payment Information -->
            <div style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-sm);">
                <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem; color: var(--secondary);">
                    <i class="fas fa-truck" style="color: var(--primary);"></i> Shipping Information
                </h2>

                <div class="form-group">
                    <label for="shipping_name" class="form-label">Recipient Full Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="shipping_name" name="shipping_name" class="form-control <?= isset($errors['shipping_name']) ? 'is-invalid' : '' ?>" value="<?= e($shippingName) ?>" required>
                    <?php if (isset($errors['shipping_name'])): ?>
                        <span class="form-error-msg"><?= e($errors['shipping_name']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="shipping_phone" class="form-label">Contact Phone Number <span style="color:var(--danger)">*</span></label>
                    <input type="tel" id="shipping_phone" name="shipping_phone" class="form-control <?= isset($errors['shipping_phone']) ? 'is-invalid' : '' ?>" placeholder="+88017XXXXXXXX" value="<?= e($shippingPhone) ?>" required>
                    <?php if (isset($errors['shipping_phone'])): ?>
                        <span class="form-error-msg"><?= e($errors['shipping_phone']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="shipping_address" class="form-label">Full Delivery Address <span style="color:var(--danger)">*</span></label>
                    <textarea id="shipping_address" name="shipping_address" rows="3" class="form-control <?= isset($errors['shipping_address']) ? 'is-invalid' : '' ?>" placeholder="House number, Road, Sector / Village, Thana, District" required><?= e($shippingAddress) ?></textarea>
                    <?php if (isset($errors['shipping_address'])): ?>
                        <span class="form-error-msg"><?= e($errors['shipping_address']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Payment Method Selection -->
                <div style="margin-top: 2rem; border-top: 1px solid var(--border); padding-top: 1.5rem;">
                    <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; color: var(--secondary);">
                        <i class="fas fa-credit-card" style="color: var(--primary);"></i> Payment Method
                    </h3>

                    <label style="display: flex; align-items: center; justify-content: space-between; border: 2px solid var(--primary); border-radius: var(--radius-md); padding: 1rem 1.25rem; background-color: var(--primary-light); cursor: pointer;">
                        <div style="display: flex; align-items: center; gap: 0.85rem;">
                            <input type="radio" name="payment_method" value="Cash on Delivery" checked style="accent-color: var(--primary); width: 18px; height: 18px;">
                            <div>
                                <strong style="color: var(--text-primary); font-size: 0.95rem;">Cash on Delivery (COD)</strong>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">Pay securely in cash upon receiving your parcel at your doorstep</div>
                            </div>
                        </div>
                        <i class="fas fa-hand-holding-dollar" style="font-size: 1.5rem; color: var(--primary);"></i>
                    </label>
                </div>
            </div>

            <!-- Right: Order Review Summary -->
            <div style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-sm); position: sticky; top: 90px;">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">Order Review</h3>

                <!-- Items Preview -->
                <div style="max-height: 240px; overflow-y: auto; margin-bottom: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach ($cartItems as $item): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; font-size: 0.875rem;">
                            <div style="display: flex; align-items: center; gap: 0.65rem;">
                                <div style="width: 44px; height: 44px; border-radius: var(--radius-sm); overflow: hidden; background: #f8fafc; flex-shrink: 0; border: 1px solid var(--border);">
                                    <img src="<?= product_image_url($item['image']) ?>" alt="" style="width: 100%; height: 100%; object-fit: contain;">
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--text-primary); max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?= e($item['name']) ?>
                                    </div>
                                    <div style="color: var(--text-muted); font-size: 0.775rem;">Qty: <?= (int)$item['quantity'] ?> &times; <?= format_price($item['price']) ?></div>
                                </div>
                            </div>
                            <div style="font-weight: 600; color: var(--text-primary);">
                                <?= format_price((float)$item['price'] * (int)$item['quantity']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Price Calculations -->
                <div style="border-top: 1px solid var(--border); padding-top: 1rem; display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.925rem; color: var(--text-secondary);">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Subtotal</span>
                        <strong><?= format_price($subtotal) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Delivery Fee</span>
                        <strong><?= ($shippingFee == 0) ? '<span style="color:var(--success);">FREE</span>' : format_price($shippingFee) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 800; color: var(--secondary); border-top: 1px solid var(--border); padding-top: 1rem; margin-top: 0.5rem;">
                        <span>Total Payable</span>
                        <span style="color: var(--primary);"><?= format_price($grandTotal) ?></span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 1.75rem; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);">
                    <i class="fas fa-lock"></i> Place Order Now
                </button>

                <div style="text-align: center; margin-top: 1rem; font-size: 0.8rem; color: var(--text-muted);">
                    <i class="fas fa-shield-halved"></i> By confirming, you agree to inspect package at delivery
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/backend/includes/footer.php'; ?>

