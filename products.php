<?php
/**
 * products.php — Product Catalog, Search, Filtering, Sorting & Pagination
 */
require_once __DIR__ . '/backend/config/config.php';

$pageTitle = 'Explore Products';

// 1. Capture and sanitize query parameters
$search = trim($_GET['q'] ?? '');
$categoryId = !empty($_GET['category']) ? (int)$_GET['category'] : null;
$minPrice = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$inStockOnly = !empty($_GET['in_stock']) ? 1 : 0;
$featuredOnly = !empty($_GET['featured']) ? 1 : 0;
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = PER_PAGE; // 8 items per page
$offset = ($page - 1) * $perPage;

try {
    // 2. Fetch all categories for sidebar filter
    $categories = $pdo->query("
        SELECT c.*, COUNT(p.id) AS product_count 
        FROM categories c 
        LEFT JOIN products p ON c.category_id = p.category_id 
        GROUP BY c.category_id 
        ORDER BY c.name ASC
    ")->fetchAll();

    // 3. Build dynamic SQL WHERE conditions
    $where = [];
    $params = [];

    if (!empty($search)) {
        $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    if ($categoryId !== null) {
        $where[] = "p.category_id = ?";
        $params[] = $categoryId;
    }

    if ($minPrice !== null && $minPrice > 0) {
        $where[] = "p.price >= ?";
        $params[] = $minPrice;
    }

    if ($maxPrice !== null && $maxPrice > 0) {
        $where[] = "p.price <= ?";
        $params[] = $maxPrice;
    }

    if ($inStockOnly) {
        $where[] = "p.stock > 0";
    }

    if ($featuredOnly) {
        $where[] = "p.is_featured = 1";
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    // 4. Count total matching products for pagination
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p $whereSql");
    $countStmt->execute($params);
    $totalProducts = (int)$countStmt->fetchColumn();
    $totalPages = max(1, (int)ceil($totalProducts / $perPage));

    // 5. Determine sorting order
    $orderBy = 'p.id DESC';
    switch ($sort) {
        case 'price_asc':
            $orderBy = 'p.price ASC';
            break;
        case 'price_desc':
            $orderBy = 'p.price DESC';
            break;
        case 'name_asc':
            $orderBy = 'p.name ASC';
            break;
        case 'name_desc':
            $orderBy = 'p.name DESC';
            break;
        case 'oldest':
            $orderBy = 'p.id ASC';
            break;
        default:
            $orderBy = 'p.created_at DESC';
            break;
    }

    // 6. Fetch paginated products
    $sql = "
        SELECT p.*, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        $whereSql 
        ORDER BY $orderBy 
        LIMIT $perPage OFFSET $offset
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Catalog error: ' . $e->getMessage());
    $products = [];
    $totalProducts = 0;
    $totalPages = 1;
}

// Helper to preserve URL parameters in pagination links
function build_filter_url(array $overrides = []): string {
    $params = $_GET;
    foreach ($overrides as $key => $val) {
        if ($val === null) {
            unset($params[$key]);
        } else {
            $params[$key] = $val;
        }
    }
    return asset_url('products.php?' . http_build_query($params));
}

require_once __DIR__ . '/backend/includes/header.php';
?>

<!-- Catalog Header & Breadcrumb -->
<section style="background-color: var(--secondary); color: #ffffff; padding: 2.5rem 0;">
    <div class="container">
        <div style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.5rem;">
            <a href="<?= asset_url('index.php') ?>" style="color: #cbd5e1;">Home</a> &nbsp;/&nbsp;
            <span>Products</span>
            <?php if (!empty($search)): ?>
                &nbsp;/&nbsp; <span>Search: "<?= e($search) ?>"</span>
            <?php endif; ?>
        </div>
        <h1 style="font-size: 2.25rem; color: #ffffff; font-weight: 700;">
            <?php 
            if (!empty($search)) {
                echo 'Search Results for "' . e($search) . '"';
            } elseif ($categoryId !== null) {
                $currentCatName = 'Category';
                foreach ($categories as $c) {
                    if ($c['category_id'] === $categoryId) $currentCatName = $c['name'];
                }
                echo e($currentCatName);
            } else {
                echo 'All Products';
            }
            ?>
        </h1>
        <p style="color: #cbd5e1; font-size: 0.95rem; margin-top: 0.25rem;">
            Showing <?= count($products) ?> of <?= $totalProducts ?> available item<?= ($totalProducts !== 1) ? 's' : '' ?>
        </p>
    </div>
</section>

<!-- Main Catalog Container -->
<div class="container" style="padding-top: 2.5rem; padding-bottom: 4rem;">
    <div style="display: grid; grid-template-columns: 280px 1fr; gap: 2rem; align-items: start;" class="catalog-layout">
        
        <!-- Sidebar Filters -->
        <aside style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                <h3 style="font-size: 1.15rem; font-weight: 700;">Filters</h3>
                <a href="<?= asset_url('products.php') ?>" style="font-size: 0.825rem; color: var(--danger); font-weight: 500;">
                    Reset All
                </a>
            </div>

            <form action="<?= asset_url('products.php') ?>" method="GET" id="catalogFilterForm">
                <!-- Keep search query if present -->
                <?php if (!empty($search)): ?>
                    <input type="hidden" name="q" value="<?= e($search) ?>">
                <?php endif; ?>

                <!-- Category Filter -->
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-primary);">Categories</h4>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.885rem;">
                        <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                            <span>
                                <input type="radio" name="category" value="" <?= ($categoryId === null) ? 'checked' : '' ?> onchange="this.form.submit()"> All Categories
                            </span>
                        </label>
                        <?php foreach ($categories as $cat): ?>
                            <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer; color: <?= ($categoryId === (int)$cat['category_id']) ? 'var(--primary)' : 'var(--text-secondary)' ?>; font-weight: <?= ($categoryId === (int)$cat['category_id']) ? '600' : '400' ?>;">
                                <span>
                                    <input type="radio" name="category" value="<?= (int)$cat['category_id'] ?>" <?= ($categoryId === (int)$cat['category_id']) ? 'checked' : '' ?> onchange="this.form.submit()"> 
                                    <?= e($cat['name']) ?>
                                </span>
                                <span style="font-size: 0.775rem; background: var(--bg-subtle); padding: 2px 6px; border-radius: 9999px;"><?= (int)$cat['product_count'] ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Price Filter -->
                <div style="margin-bottom: 1.5rem; border-top: 1px solid var(--border); padding-top: 1.25rem;">
                    <h4 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-primary);">Price Range (৳)</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.75rem;">
                        <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?= $minPrice !== null ? e((string)$minPrice) : '' ?>" min="0">
                        <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?= $maxPrice !== null ? e((string)$maxPrice) : '' ?>" min="0">
                    </div>
                    <button type="submit" class="btn btn-outline btn-sm btn-block">Apply Price</button>
                </div>

                <!-- In Stock & Featured Filter -->
                <div style="margin-bottom: 1.5rem; border-top: 1px solid var(--border); padding-top: 1.25rem;">
                    <h4 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-primary);">Availability</h4>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.885rem; margin-bottom: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="in_stock" value="1" <?= $inStockOnly ? 'checked' : '' ?> onchange="this.form.submit()">
                        <span>In Stock Only</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.885rem; cursor: pointer;">
                        <input type="checkbox" name="featured" value="1" <?= $featuredOnly ? 'checked' : '' ?> onchange="this.form.submit()">
                        <span>Featured Items Only</span>
                    </label>
                </div>
            </form>
        </aside>

        <!-- Product Grid & Controls -->
        <div>
            <!-- Top Controls (Sorting & Result Counter) -->
            <div style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 0.85rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="font-size: 0.9rem; color: var(--text-muted);">
                    Showing <strong><?= count($products) ?></strong> of <strong><?= $totalProducts ?></strong> products
                </div>

                <!-- Sorting Dropdown -->
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <label for="catalog_sort" style="font-size: 0.885rem; color: var(--text-secondary); font-weight: 500;">Sort By:</label>
                    <select id="catalog_sort" class="form-control" style="width: auto; padding: 0.4rem 0.85rem; font-size: 0.875rem;" onchange="location.href=this.value;">
                        <option value="<?= build_filter_url(['sort' => 'newest', 'page' => 1]) ?>" <?= ($sort === 'newest') ? 'selected' : '' ?>>Newest Arrivals</option>
                        <option value="<?= build_filter_url(['sort' => 'price_asc', 'page' => 1]) ?>" <?= ($sort === 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="<?= build_filter_url(['sort' => 'price_desc', 'page' => 1]) ?>" <?= ($sort === 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="<?= build_filter_url(['sort' => 'name_asc', 'page' => 1]) ?>" <?= ($sort === 'name_asc') ? 'selected' : '' ?>>Product Name: A to Z</option>
                        <option value="<?= build_filter_url(['sort' => 'name_desc', 'page' => 1]) ?>" <?= ($sort === 'name_desc') ? 'selected' : '' ?>>Product Name: Z to A</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid or Empty State -->
            <?php if (empty($products)): ?>
                <div style="background-color: var(--bg-surface); border: 1px dashed var(--border-dark); border-radius: var(--radius-lg); padding: 4rem 2rem; text-align: center;">
                    <i class="fas fa-box-open" style="font-size: 3.5rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                    <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">No Matching Products Found</h3>
                    <p style="color: var(--text-muted); max-width: 450px; margin: 0 auto 1.5rem auto;">
                        We couldn't find any items matching your selected criteria. Try adjusting your filters or search keyword.
                    </p>
                    <a href="<?= asset_url('products.php') ?>" class="btn btn-primary">
                        <i class="fas fa-rotate-left"></i> Reset All Filters
                    </a>
                </div>
            <?php else: ?>
                <div class="product-grid" style="grid-template-columns: repeat(3, 1fr);">
                    <?php foreach ($products as $product): ?>
                        <article class="product-card">
                            <div class="product-badge-wrap">
                                <?php if ($product['is_featured']): ?>
                                    <span class="badge badge-featured">Featured</span>
                                <?php endif; ?>
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

                <!-- Pagination Component -->
                <?php if ($totalPages > 1): ?>
                    <div style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin-top: 3rem;">
                        <!-- Prev Button -->
                        <?php if ($page > 1): ?>
                            <a href="<?= build_filter_url(['page' => $page - 1]) ?>" class="btn btn-outline btn-sm">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <a href="<?= build_filter_url(['page' => $p]) ?>" class="btn <?= ($p === $page) ? 'btn-primary' : 'btn-outline' ?> btn-sm" style="min-width: 38px;">
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>

                        <!-- Next Button -->
                        <?php if ($page < $totalPages): ?>
                            <a href="<?= build_filter_url(['page' => $page + 1]) ?>" class="btn btn-outline btn-sm">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/backend/includes/footer.php'; ?>

