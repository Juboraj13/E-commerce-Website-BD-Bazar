<?php
/**
 * cart.php — Shopping Cart View
 * Review items, adjust quantities, view subtotal, shipping fee and proceed to checkout.
 */
require_once __DIR__ . '/backend/config/config.php';

$pageTitle = 'Shopping Cart';

$cartItems = [];
$subtotal = 0.0;

try {
    if (is_logged_in()) {
        $stmt = $pdo->prepare("
            SELECT c.cart_id, c.quantity, p.id AS product_id, p.name, p.price, p.stock, p.image, cat.name AS category_name 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            LEFT JOIN categories cat ON p.category_id = cat.category_id 
            WHERE c.user_id = ? 
            ORDER BY c.cart_id DESC
        ");
        $stmt->execute([$_SESSION['user']['user_id']]);
        $cartItems = $stmt->fetchAll();
    } else {
        if (!empty($_SESSION['guest_cart']) && is_array($_SESSION['guest_cart'])) {
            $ids = array_keys($_SESSION['guest_cart']);
            if (!empty($ids)) {
                $inQuery = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare("
                    SELECT p.id AS product_id, p.name, p.price, p.stock, p.image, cat.name AS category_name 
                    FROM products p 
                    LEFT JOIN categories cat ON p.category_id = cat.category_id 
                    WHERE p.id IN ($inQuery)
                ");
                $stmt->execute($ids);
                $products = $stmt->fetchAll();

                foreach ($products as $prod) {
                    $qty = (int)$_SESSION['guest_cart'][$prod['product_id']];
                    $prod['quantity'] = $qty;
                    $prod['cart_id'] = $prod['product_id'];
                    $cartItems[] = $prod;
                }
            }
        }
    }

    // Calculate subtotal
    foreach ($cartItems as $item) {
        $subtotal += ((float)$item['price'] * (int)$item['quantity']);
    }

} catch (PDOException $e) {
    error_log('Cart page error: ' . $e->getMessage());
}

$shippingFee = ($subtotal >= FREE_SHIPPING_OVER || $subtotal == 0) ? 0.0 : SHIPPING_FEE;
$grandTotal = $subtotal + $shippingFee;

require_once __DIR__ . '/backend/includes/header.php';
?>

<!-- Cart Header -->
<div style="background-color: var(--secondary); color: #ffffff; padding: 2rem 0;">
    <div class="container">
        <h1 style="font-size: 2rem; font-weight: 700; color: #ffffff;">Shopping Cart</h1>
        <p style="color: #94a3b8; font-size: 0.9rem;">
            <?= count($cartItems) ?> unique item<?= count($cartItems) !== 1 ? 's' : '' ?> in your bag
        </p>
    </div>
</div>

<div class="container" style="padding-top: 2.5rem; padding-bottom: 4rem;">
    <?php if (empty($cartItems)): ?>
        <!-- Empty Cart State -->
        <div style="background-color: var(--bg-surface); border: 1px dashed var(--border-dark); border-radius: var(--radius-lg); padding: 5rem 2rem; text-align: center; max-width: 600px; margin: 0 auto;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--bg-subtle); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; font-size: 2rem; color: var(--text-muted);">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h2 style="font-size: 1.6rem; margin-bottom: 0.5rem;">Your Cart is Empty</h2>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Looks like you haven't added anything to your shopping cart yet.</p>
            <a href="<?= asset_url('products.php') ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-arrow-left"></i> Start Shopping Now
            </a>
        </div>
    <?php else: ?>
        <!-- Cart Table and Summary Layout -->
        <div style="display: grid; grid-template-columns: 1fr 360px; gap: 2.5rem; align-items: start;" class="cart-layout">
            
            <!-- Items List -->
            <div style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm);">
                <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); background-color: var(--bg-subtle); font-weight: 600; font-size: 0.95rem; color: var(--text-secondary); display: grid; grid-template-columns: 2.5fr 1fr 1.2fr 1fr 40px; gap: 1rem;">
                    <div>Product</div>
                    <div>Price</div>
                    <div>Quantity</div>
                    <div style="text-align: right;">Total</div>
                    <div></div>
                </div>

                <div id="cartItemsList">
                    <?php foreach ($cartItems as $item): 
                        $itemLineTotal = (float)$item['price'] * (int)$item['quantity'];
                    ?>
                        <div class="cart-item-row" id="cart-item-<?= (int)$item['product_id'] ?>" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: grid; grid-template-columns: 2.5fr 1fr 1.2fr 1fr 40px; gap: 1rem; align-items: center;">
                            <!-- Thumbnail & Title -->
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <a href="<?= asset_url('product.php?id=' . (int)$item['product_id']) ?>" style="flex-shrink: 0; width: 68px; height: 68px; border-radius: var(--radius-md); overflow: hidden; background: #f1f5f9; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center;">
                                    <img src="<?= product_image_url($item['image']) ?>" alt="<?= e($item['name']) ?>" style="width: 100%; height: 100%; object-fit: contain;">
                                </a>
                                <div>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;"><?= e($item['category_name'] ?? 'General') ?></span>
                                    <h4 style="font-size: 0.95rem; font-weight: 600; line-height: 1.3;">
                                        <a href="<?= asset_url('product.php?id=' . (int)$item['product_id']) ?>" style="color: var(--text-primary);"><?= e($item['name']) ?></a>
                                    </h4>
                                    <?php if ($item['stock'] < 5): ?>
                                        <span style="color: var(--danger); font-size: 0.775rem;">Only <?= (int)$item['stock'] ?> left!</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Unit Price -->
                            <div style="font-size: 0.95rem; font-weight: 500; color: var(--text-secondary);">
                                <?= format_price($item['price']) ?>
                            </div>

                            <!-- Quantity Controls -->
                            <div>
                                <div style="display: inline-flex; align-items: center; border: 1px solid var(--border); border-radius: var(--radius-sm); overflow: hidden; background: var(--bg-surface);">
                                    <button type="button" class="cart-qty-btn" onclick="changeRowQty(<?= (int)$item['product_id'] ?>, -1)" style="border:none; background:none; padding: 4px 8px; cursor: pointer; color: var(--text-secondary);"><i class="fas fa-minus" style="font-size: 0.75rem;"></i></button>
                                    <input type="text" id="qty-input-<?= (int)$item['product_id'] ?>" value="<?= (int)$item['quantity'] ?>" readonly style="width: 36px; text-align: center; border: none; font-size: 0.885rem; font-weight: 600;">
                                    <button type="button" class="cart-qty-btn" onclick="changeRowQty(<?= (int)$item['product_id'] ?>, 1)" style="border:none; background:none; padding: 4px 8px; cursor: pointer; color: var(--text-secondary);"><i class="fas fa-plus" style="font-size: 0.75rem;"></i></button>
                                </div>
                            </div>

                            <!-- Line Total -->
                            <div style="text-align: right; font-weight: 700; color: var(--primary);" id="line-total-<?= (int)$item['product_id'] ?>">
                                <?= format_price($itemLineTotal) ?>
                            </div>

                            <!-- Remove Button -->
                            <div style="text-align: right;">
                                <button type="button" onclick="deleteCartRow(<?= (int)$item['product_id'] ?>)" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1rem; transition: var(--transition);" title="Remove Item">
                                    <i class="fas fa-trash-can" style="color: #ef4444;"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; background-color: var(--bg-subtle);">
                    <a href="<?= asset_url('products.php') ?>" class="btn btn-outline btn-sm">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.75rem; box-shadow: var(--shadow-sm); position: sticky; top: 90px;">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">Order Summary</h3>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.85rem; font-size: 0.95rem; color: var(--text-secondary);">
                    <span>Subtotal</span>
                    <strong id="cartSummarySubtotal"><?= format_price($subtotal) ?></strong>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 0.85rem; font-size: 0.95rem; color: var(--text-secondary);">
                    <span>Delivery Fee</span>
                    <strong id="cartSummaryShipping">
                        <?= ($shippingFee == 0) ? '<span style="color:var(--success);">FREE</span>' : format_price($shippingFee) ?>
                    </strong>
                </div>

                <?php if ($subtotal < FREE_SHIPPING_OVER): ?>
                    <div style="background-color: var(--primary-light); color: var(--primary); padding: 0.65rem 0.85rem; border-radius: var(--radius-sm); font-size: 0.8rem; margin-bottom: 1.25rem;">
                        <i class="fas fa-info-circle"></i> Add <strong><?= format_price(FREE_SHIPPING_OVER - $subtotal) ?></strong> more for <strong>FREE Delivery</strong>!
                    </div>
                <?php endif; ?>

                <div style="display: flex; justify-content: space-between; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--border); font-size: 1.2rem; font-weight: 700; color: var(--secondary);">
                    <span>Total Amount</span>
                    <span style="color: var(--primary);" id="cartSummaryTotal"><?= format_price($grandTotal) ?></span>
                </div>

                <div style="margin-top: 1.75rem;">
                    <a href="<?= asset_url('checkout.php') ?>" class="btn btn-primary btn-block btn-lg" style="box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);">
                        Proceed to Checkout <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div style="margin-top: 1.25rem; text-align: center; font-size: 0.8rem; color: var(--text-muted); line-height: 1.5;">
                    <i class="fas fa-shield-alt" style="color: var(--success);"></i> 100% Safe & Secure Checkout<br>Cash on Delivery Guaranteed
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function changeRowQty(productId, change) {
    const input = document.getElementById(`qty-input-${productId}`);
    if (!input) return;
    const current = parseInt(input.value) || 1;
    const newQty = current + change;
    if (newQty <= 0) {
        deleteCartRow(productId);
        return;
    }
    updateCartQuantity(productId, newQty, (res) => {
        if (res.success) {
            input.value = newQty;
            location.reload(); // Refresh to recalculate server-side totals & shipping rules cleanly
        }
    });
}

function deleteCartRow(productId) {
    removeCartItem(productId, (res) => {
        if (res.success) {
            location.reload();
        }
    });
}
</script>

<?php require_once __DIR__ . '/backend/includes/footer.php'; ?>

