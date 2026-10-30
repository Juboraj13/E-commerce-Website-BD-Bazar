<?php
/**
 * index.php — Homepage
 * Dynamic hero, categories, featured products, new arrivals and customer perks.
 */
require_once __DIR__ . '/backend/config/config.php';

$pageTitle = 'Home — Premium Online Shopping';

try {
    // 1. Fetch Categories with product counts
    $catStmt = $pdo->query("
        SELECT c.*, COUNT(p.id) AS product_count 
        FROM categories c 
        LEFT JOIN products p ON c.category_id = p.category_id 
        GROUP BY c.category_id 
        ORDER BY c.category_id ASC
    ");
    $categories = $catStmt->fetchAll();

    // 2. Fetch Featured Products (is_featured = 1)
    $featStmt = $pdo->query("
        SELECT p.*, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.is_featured = 1 
        ORDER BY p.id DESC 
        LIMIT 8
    ");
    $featuredProducts = $featStmt->fetchAll();

    // 3. Fetch New Arrivals
    $newStmt = $pdo->query("
        SELECT p.*, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        ORDER BY p.created_at DESC 
        LIMIT 8
    ");
    $newArrivals = $newStmt->fetchAll();

} catch (PDOException $e) {
    error_log('Error loading homepage data: ' . $e->getMessage());
    $categories = [];
    $featuredProducts = [];
    $newArrivals = [];
}

require_once __DIR__ . '/backend/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-content">
                <div class="hero-subtitle">
                    <i class="fas fa-sparkles"></i> Great Deals & Authentic Quality
                </div>
                <h1 class="hero-title">
                    Discover Latest <span>Gadgets, Fashion & Bikes</span>
                </h1>
                <p class="hero-description">
                    Upgrade your lifestyle with top-tier smartphones, trendy urban apparel, and precision road bicycles. Enjoy fast nationwide doorstep delivery and Cash on Delivery.
                </p>
                <div class="hero-cta-group">
                    <a href="<?= asset_url('products.php') ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i> Shop All Products
                    </a>
                    <a href="<?= asset_url('products.php?featured=1') ?>" class="btn btn-outline btn-lg" style="color:#ffffff; border-color:rgba(255,255,255,0.3);">
                        <i class="fas fa-fire"></i> Featured Items
                    </a>
                </div>
            </div>
            <div class="hero-image-wrap">
                <div class="hero-banner-card">
                    <img src="<?= asset_url('uploads/iphone-15-pro-max-blue-titanium.jpeg') ?>" alt="Featured Hero Product" loading="lazy">
                    <div style="margin-top: 1rem; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-size:0.8rem; color:#94a3b8; text-transform:uppercase;">Special Showcase</div>
                            <div style="font-weight:700; color:#ffffff; font-size:1.1rem;">iPhone 15 Pro Max</div>
                        </div>
                        <span class="badge badge-featured">Best Seller</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Value Perks Strip -->
<section class="perks-section">
    <div class="container">
        <div class="perks-grid">
            <div class="perk-item">
                <div class="perk-icon"><i class="fas fa-truck-fast"></i></div>
                <div>
                    <h3 class="perk-title">Free & Fast Delivery</h3>
                    <p class="perk-desc">Free delivery on orders over <?= format_price(FREE_SHIPPING_OVER) ?></p>
                </div>
            </div>
            <div class="perk-item">
                <div class="perk-icon"><i class="fas fa-hand-holding-dollar"></i></div>
                <div>
                    <h3 class="perk-title">Cash on Delivery</h3>
                    <p class="perk-desc">Pay safely with cash when items arrive</p>
                </div>
            </div>
            <div class="perk-item">
                <div class="perk-icon"><i class="fas fa-award"></i></div>
                <div>
                    <h3 class="perk-title">100% Genuine Quality</h3>
                    <p class="perk-desc">Authentic warranty-backed products</p>
                </div>
            </div>
            <div class="perk-item">
                <div class="perk-icon"><i class="fas fa-rotate-left"></i></div>
                <div>
                    <h3 class="perk-title">7-Day Easy Return</h3>
                    <p class="perk-desc">Hassle-free replacement policy</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Browse Categories Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Collections</span>
            <h2 class="section-title">Explore by Category</h2>
            <p class="section-subtitle">Find exactly what you're looking for curated across our top departments</p>
        </div>

        <div class="category-grid">
            <?php 
            $catIcons = [
                1 => 'fa-mobile-screen-button',
                2 => 'fa-shirt',
                3 => 'fa-bicycle',
                4 => 'fa-headphones',
                5 => 'fa-laptop'
            ];
            foreach ($categories as $cat): 
                $icon = $catIcons[$cat['category_id']] ?? 'fa-tags';
            ?>
                <a href="<?= asset_url('products.php?category=' . (int)$cat['category_id']) ?>" class="category-card">
                    <div class="category-icon-box">
                        <i class="fas <?= $icon ?>"></i>
                    </div>
                    <h3 class="category-name"><?= e($cat['name']) ?></h3>
                    <span class="category-count"><?= (int)$cat['product_count'] ?> Products</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="section" style="background-color: var(--bg-subtle);">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Popular Picks</span>
            <h2 class="section-title">Featured Products</h2>
            <p class="section-subtitle">Handpicked top performers loved by thousands of happy customers</p>
        </div>

        <div class="product-grid">
            <?php foreach ($featuredProducts as $product): ?>
                <article class="product-card">
                    <div class="product-badge-wrap">
                        <span class="badge badge-featured">Featured</span>
                        <?php if ($product['stock'] <= 0): ?>
                            <span class="badge badge-outstock">Out of Stock</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-image-box">
                        <a href="<?= asset_url('product.php?id=' . (int)$product['id']) ?>">
                            <img src="<?= product_image_url($product['image']) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                        </a>
                    </div>

                    <div class="product-body">
                        <span class="product-category"><?= e($product['category_name'] ?? 'General') ?></span>
                        <h3 class="product-title">
                            <a href="<?= asset_url('product.php?id=' . (int)$product['id']) ?>"><?= e($product['name']) ?></a>
                        </h3>
                        <div class="product-price-row">
                            <span class="product-price"><?= format_price($product['price']) ?></span>
                        </div>
                        <div class="product-card-footer">
                            <a href="<?= asset_url('product.php?id=' . (int)$product['id']) ?>" class="btn btn-outline btn-sm" style="flex:1;">
                                Details
                            </a>
                            <button type="button" class="btn btn-primary btn-sm add-to-cart-btn" data-product-id="<?= (int)$product['id'] ?>" <?= ($product['stock'] <= 0) ? 'disabled' : '' ?>>
                                <i class="fas fa-cart-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 2.5rem;">
            <a href="<?= asset_url('products.php') ?>" class="btn btn-primary btn-lg">
                View All Products <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Promotional Deal Banner -->
<section style="padding: 3rem 0; background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); color: #ffffff;">
    <div class="container" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:2rem;">
        <div>
            <span style="background: rgba(255,255,255,0.2); padding: 0.35rem 0.85rem; border-radius: 9999px; font-size:0.85rem; font-weight:600; text-transform:uppercase;">Special Offer</span>
            <h2 style="font-size: 2.2rem; margin: 0.75rem 0 0.5rem 0; color: #ffffff;">Save Up to 25% on Sports Bicycles</h2>
            <p style="color: #cbd5e1; max-width: 500px;">Get your hands on top Japanese & European hybrid road bicycles. Built for commuting and outdoor fitness.</p>
        </div>
        <div>
            <a href="<?= asset_url('products.php?category=3') ?>" class="btn btn-accent btn-lg" style="box-shadow: 0 4px 14px rgba(0,0,0,0.2);">
                <i class="fas fa-bicycle"></i> Explore Bicycles
            </a>
        </div>
    </div>
</section>

<!-- New Arrivals Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Fresh Additions</span>
            <h2 class="section-title">New Arrivals</h2>
            <p class="section-subtitle">Check out the newest items just added to our catalog</p>
        </div>

        <div class="product-grid">
            <?php foreach ($newArrivals as $product): ?>
                <article class="product-card">
                    <div class="product-badge-wrap">
                        <span class="badge badge-sale">New</span>
                    </div>

                    <div class="product-image-box">
                        <a href="<?= asset_url('product.php?id=' . (int)$product['id']) ?>">
                            <img src="<?= product_image_url($product['image']) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
                        </a>
                    </div>

                    <div class="product-body">
                        <span class="product-category"><?= e($product['category_name'] ?? 'General') ?></span>
                        <h3 class="product-title">
                            <a href="<?= asset_url('product.php?id=' . (int)$product['id']) ?>"><?= e($product['name']) ?></a>
                        </h3>
                        <div class="product-price-row">
                            <span class="product-price"><?= format_price($product['price']) ?></span>
                        </div>
                        <div class="product-card-footer">
                            <a href="<?= asset_url('product.php?id=' . (int)$product['id']) ?>" class="btn btn-outline btn-sm" style="flex:1;">
                                Details
                            </a>
                            <button type="button" class="btn btn-primary btn-sm add-to-cart-btn" data-product-id="<?= (int)$product['id'] ?>" <?= ($product['stock'] <= 0) ? 'disabled' : '' ?>>
                                <i class="fas fa-cart-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/backend/includes/footer.php'; ?>

