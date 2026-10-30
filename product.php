<?php
/**
 * product.php — Product Details & Specifications
 */
require_once __DIR__ . '/backend/config/config.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    redirect(asset_url('404.php'));
}

try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        redirect(asset_url('404.php'));
    }

    // Fetch related products from the same category
    $relStmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.category_id = ? AND p.id != ? 
        LIMIT 4
    ");
    $relStmt->execute([$product['category_id'], $id]);
    $relatedProducts = $relStmt->fetchAll();

} catch (PDOException $e) {
    error_log('Product Details Error: ' . $e->getMessage());
    redirect(asset_url('404.php'));
}

$pageTitle = $product['name'];
require_once __DIR__ . '/backend/includes/header.php';
?>

<!-- Breadcrumb -->
<div style="background-color: var(--bg-subtle); padding: 1rem 0; border-bottom: 1px solid var(--border);">
    <div class="container" style="font-size: 0.885rem; color: var(--text-muted);">
        <a href="<?= asset_url('index.php') ?>" style="color: var(--text-secondary);">Home</a> &nbsp;/&nbsp;
        <a href="<?= asset_url('products.php') ?>" style="color: var(--text-secondary);">Products</a> &nbsp;/&nbsp;
        <?php if (!empty($product['category_name'])): ?>
            <a href="<?= asset_url('products.php?category=' . (int)$product['category_id']) ?>" style="color: var(--text-secondary);"><?= e($product['category_name']) ?></a> &nbsp;/&nbsp;
        <?php endif; ?>
        <span style="color: var(--text-primary); font-weight: 500;"><?= e($product['name']) ?></span>
    </div>
</div>

<!-- Product Details Section -->
<div class="container" style="padding-top: 2.5rem; padding-bottom: 4rem;">
    <div style="display: grid; grid-template-columns: 1fr 1.15fr; gap: 3.5rem; align-items: start;" class="product-details-grid">
        
        <!-- Left: Product Image Box -->
        <div style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm); text-align: center;">
            <div style="width: 100%; max-height: 480px; overflow: hidden; border-radius: var(--radius-md); background: #f8fafc; display: flex; align-items: center; justify-content: center;">
                <img src="<?= product_image_url($product['image']) ?>" alt="<?= e($product['name']) ?>" id="mainProductImage" style="max-height: 460px; object-fit: contain; width: 100%;">
            </div>
        </div>

        <!-- Right: Product Information & Purchase Controls -->
        <div>
            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                <span class="badge" style="background-color: var(--primary-light); color: var(--primary);">
                    <?= e($product['category_name'] ?? 'General') ?>
                </span>
                <?php if ($product['is_featured']): ?>
                    <span class="badge badge-featured">Featured Choice</span>
                <?php endif; ?>
                <?php if ($product['stock'] > 0): ?>
                    <span class="badge badge-instock"><i class="fas fa-check"></i> In Stock (<?= (int)$product['stock'] ?> units)</span>
                <?php else: ?>
                    <span class="badge badge-outstock"><i class="fas fa-times"></i> Out of Stock</span>
                <?php endif; ?>
            </div>

            <h1 style="font-size: 2.15rem; font-weight: 700; color: var(--secondary); margin-bottom: 0.85rem; line-height: 1.25;">
                <?= e($product['name']) ?>
            </h1>

            <div style="display: flex; align-items: baseline; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--border);">
                <div style="font-size: 2rem; font-weight: 800; color: var(--primary);">
                    <?= format_price($product['price']) ?>
                </div>
                <div style="color: var(--text-muted); font-size: 0.885rem;">
                    (Price inclusive of all taxes)
                </div>
            </div>

            <p style="color: var(--text-secondary); line-height: 1.7; font-size: 1rem; margin-bottom: 2rem;">
                <?= nl2br(e($product['description'] ?? 'No description available for this product.')) ?>
            </p>

            <!-- Purchase Controls -->
            <div style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 2rem;">
                <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
                    
                    <!-- Quantity Input with +/- Buttons -->
                    <div>
                        <label for="productQuantityInput" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--text-secondary);">Quantity</label>
                        <div style="display: inline-flex; align-items: center; border: 1.5px solid var(--border); border-radius: var(--radius-md); overflow: hidden; background: var(--bg-surface);">
                            <button type="button" id="qtyMinusBtn" style="background: none; border: none; padding: 0.6rem 0.9rem; cursor: pointer; color: var(--text-secondary); font-size: 1rem;"><i class="fas fa-minus"></i></button>
                            <input type="number" id="productQuantityInput" value="1" min="1" max="<?= max(1, (int)$product['stock']) ?>" style="width: 50px; text-align: center; border: none; font-weight: 600; font-size: 1rem; outline: none;" <?= ($product['stock'] <= 0) ? 'disabled' : '' ?>>
                            <button type="button" id="qtyPlusBtn" style="background: none; border: none; padding: 0.6rem 0.9rem; cursor: pointer; color: var(--text-secondary); font-size: 1rem;"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>

                    <!-- Add To Cart Button -->
                    <div style="flex: 1; min-width: 200px; padding-top: 1.4rem;">
                        <button type="button" id="singleAddToCartBtn" class="btn btn-primary btn-lg btn-block" data-product-id="<?= (int)$product['id'] ?>" <?= ($product['stock'] <= 0) ? 'disabled' : '' ?>>
                            <i class="fas fa-cart-plus"></i> 
                            <?= ($product['stock'] > 0) ? 'Add to Shopping Cart' : 'Currently Unavailable' ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Value Highlights Box -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1rem; background-color: var(--bg-subtle);">
                <div style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.825rem; color: var(--text-secondary);">
                    <i class="fas fa-truck" style="color: var(--primary); font-size: 1.1rem;"></i>
                    <span>Cash on Delivery</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.825rem; color: var(--text-secondary);">
                    <i class="fas fa-shield-check" style="color: var(--success); font-size: 1.1rem;"></i>
                    <span>100% Genuine</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.825rem; color: var(--text-secondary);">
                    <i class="fas fa-rotate" style="color: var(--warning); font-size: 1.1rem;"></i>
                    <span>7-Day Return</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
        <div style="margin-top: 5rem; border-top: 1px solid var(--border); padding-top: 3rem;">
            <div class="section-header" style="text-align: left; margin-bottom: 2rem;">
                <span class="section-tag">Similar Items</span>
                <h2 class="section-title">You Might Also Like</h2>
            </div>

            <div class="product-grid">
                <?php foreach ($relatedProducts as $rel): ?>
                    <article class="product-card">
                        <div class="product-image-box">
                            <a href="<?= asset_url('product.php?id=' . (int)$rel['id']) ?>">
                                <img src="<?= product_image_url($rel['image']) ?>" alt="<?= e($rel['name']) ?>" loading="lazy">
                            </a>
                        </div>
                        <div class="product-body">
                            <span class="product-category"><?= e($rel['category_name']) ?></span>
                            <h3 class="product-title">
                                <a href="<?= asset_url('product.php?id=' . (int)$rel['id']) ?>"><?= e($rel['name']) ?></a>
                            </h3>
                            <div class="product-price-row">
                                <span class="product-price"><?= format_price($rel['price']) ?></span>
                            </div>
                            <div class="product-card-footer">
                                <a href="<?= asset_url('product.php?id=' . (int)$rel['id']) ?>" class="btn btn-outline btn-sm" style="flex:1;">Details</a>
                                <button type="button" class="btn btn-primary btn-sm add-to-cart-btn" data-product-id="<?= (int)$rel['id'] ?>" <?= ($rel['stock'] <= 0) ? 'disabled' : '' ?>>
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const qtyInput = document.getElementById('productQuantityInput');
    const plusBtn = document.getElementById('qtyPlusBtn');
    const minusBtn = document.getElementById('qtyMinusBtn');
    const addBtn = document.getElementById('singleAddToCartBtn');

    if (qtyInput && plusBtn && minusBtn) {
        plusBtn.addEventListener('click', () => {
            const max = parseInt(qtyInput.getAttribute('max')) || 999;
            let current = parseInt(qtyInput.value) || 1;
            if (current < max) {
                qtyInput.value = current + 1;
            } else {
                showToast(`Maximum available stock is ${max}`, 'warning');
            }
        });

        minusBtn.addEventListener('click', () => {
            let current = parseInt(qtyInput.value) || 1;
            if (current > 1) {
                qtyInput.value = current - 1;
            }
        });
    }

    if (addBtn && qtyInput) {
        addBtn.addEventListener('click', () => {
            const productId = addBtn.getAttribute('data-product-id');
            const qty = parseInt(qtyInput.value) || 1;
            addToCart(productId, qty);
        });
    }
});
</script>

<?php require_once __DIR__ . '/backend/includes/footer.php'; ?>

